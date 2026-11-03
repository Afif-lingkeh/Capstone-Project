<?php
// app/Services/LinkChecker/LinkCheckerService.php

namespace App\Services\LinkChecker;

use App\Enums\LinkCheckResult;
use App\Enums\LinkStatus;
use App\Models\LinkCheckLog;
use App\Models\OfficialLink;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LinkCheckerService
{
    /**
     * Mengecek satu tautan, menyimpan log, dan menandai tautan terverifikasi
     * yang bermasalah sebagai "perlu ditinjau ulang" (tidak tampil ke santri).
     */
    public function check(OfficialLink $link): LinkCheckLog
    {
        $startedAt = hrtime(true);
        $fetch = $this->fetch($link->url);
        $responseMs = (int) ((hrtime(true) - $startedAt) / 1_000_000);

        $result = $this->classify($link, $fetch['status'], $fetch['final_url']);

        return DB::transaction(function () use ($link, $fetch, $result, $responseMs) {
            $previousResult = $link->last_check_result;

            $log = $link->checkLogs()->create([
                'checked_at' => now(),
                'http_status' => $fetch['status'],
                'final_url' => $fetch['final_url'],
                'result' => $result,
                'response_ms' => $responseMs,
                'error_message' => $fetch['error'] ? Str::limit($fetch['error'], 250) : null,
            ]);

            $link->forceFill([
                'last_checked_at' => $log->checked_at,
                'last_check_result' => $result,
            ]);

            if ($link->status === LinkStatus::Verified && $this->shouldFlag($result, $previousResult)) {
                $link->status = LinkStatus::NeedsReview;

                Log::warning('Tautan resmi ditandai untuk ditinjau ulang.', [
                    'link_id' => $link->id,
                    'url' => $link->url,
                    'result' => $result->value,
                    'final_url' => $fetch['final_url'],
                ]);
            }

            $link->save();

            return $log;
        });
    }

    /**
     * Redirect diikuti manual (bukan otomatis oleh HTTP client) supaya
     * alamat akhir selalu diketahui, termasuk saat diuji dengan Http::fake().
     *
     * @return array{status: ?int, final_url: string, error: ?string}
     */
    private function fetch(string $url): array
    {
        $config = config('peduli.link_check');
        $current = $url;

        for ($hop = 0; $hop <= $config['max_redirects']; $hop++) {
            try {
                $response = Http::withoutRedirecting()
                    ->timeout($config['timeout'])
                    ->connectTimeout(10)
                    ->withUserAgent('PEDULI-LinkChecker/1.0 (+'.config('app.url').')')
                    ->accept('text/html,application/xhtml+xml')
                    ->get($current);
            } catch (ConnectionException $e) {
                return ['status' => null, 'final_url' => $current, 'error' => $e->getMessage()];
            }

            $location = $response->header('Location');

            if ($response->redirect() && $location !== '') {
                $current = $this->resolveUrl($current, $location);

                continue;
            }

            return ['status' => $response->status(), 'final_url' => $current, 'error' => null];
        }

        return ['status' => null, 'final_url' => $current, 'error' => 'Terlalu banyak pengalihan (redirect).'];
    }

    private function classify(OfficialLink $link, ?int $status, string $finalUrl): LinkCheckResult
    {
        // Dialihkan ke domain lain lebih penting diketahui daripada kode status halaman tujuan.
        if (! $this->isSameSite($link->domain, OfficialLink::domainFromUrl($finalUrl))) {
            return LinkCheckResult::DomainChanged;
        }

        return match (true) {
            $status === null => LinkCheckResult::Error,
            $status >= 200 && $status < 300 => LinkCheckResult::Ok,
            in_array($status, [404, 410], true) => LinkCheckResult::Dead,
            default => LinkCheckResult::Error, // 5xx, 403 (sering memblokir bot), 429, ...
        };
    }

    /**
     * pmb.ui.ac.id ↔ ui.ac.id dianggap situs yang sama (subdomain/induk).
     * ui.ac.id → ui-pendaftaran.com dianggap domain berubah.
     */
    private function isSameSite(string $registered, string $final): bool
    {
        return $final === $registered
            || Str::endsWith($final, '.'.$registered)
            || Str::endsWith($registered, '.'.$final);
    }

    /**
     * Gangguan sementara (Error) baru ditandai jika terjadi 2 kali berturut-turut,
     * karena situs kampus kadang lambat atau sedang perawatan.
     */
    private function shouldFlag(LinkCheckResult $result, ?LinkCheckResult $previous): bool
    {
        if ($result->requiresImmediateReview()) {
            return true;
        }

        return $result === LinkCheckResult::Error
            && in_array($previous, [LinkCheckResult::Error, LinkCheckResult::Dead], true);
    }

    /** Mengubah Location relatif ("/pmb", "baru.html", "//cdn...") menjadi URL lengkap. */
    private function resolveUrl(string $base, string $location): string
    {
        if (preg_match('#^https?://#i', $location)) {
            return $location;
        }

        $parts = parse_url($base);
        $scheme = $parts['scheme'] ?? 'https';
        $origin = $scheme.'://'.$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '');

        if (Str::startsWith($location, '//')) {
            return $scheme.':'.$location;
        }

        if (Str::startsWith($location, '/')) {
            return $origin.$location;
        }

        $directory = Str::beforeLast($parts['path'] ?? '/', '/');

        return $origin.$directory.'/'.$location;
    }
}
