<?php
// app/Console/Commands/CheckOfficialLinks.php

namespace App\Console\Commands;

use App\Enums\LinkStatus;
use App\Models\OfficialLink;
use App\Services\LinkChecker\LinkCheckerService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class CheckOfficialLinks extends Command
{
    protected $signature = 'links:check {--all : Cek semua tautan terverifikasi tanpa menunggu jadwalnya}';

    protected $description = 'Mengecek tautan resmi terverifikasi dan menandai yang mati atau berpindah domain';

    public function handle(LinkCheckerService $checker): int
    {
        $intervalDays = config('peduli.link_check.interval_days');
        $delayMs = config('peduli.link_check.delay_ms');

        $query = OfficialLink::query()
            ->where('status', LinkStatus::Verified)
            ->unless($this->option('all'), fn (Builder $q) => $q->where(fn (Builder $q) => $q
                ->whereNull('last_checked_at')
                ->orWhere('last_checked_at', '<=', now()->subDays($intervalDays))));

        $total = (clone $query)->count();

        if ($total === 0) {
            $this->info('Tidak ada tautan yang perlu dicek saat ini.');

            return self::SUCCESS;
        }

        $this->info("Mengecek {$total} tautan...");
        $summary = [];
        $flagged = 0;

        $query->chunkById(50, function ($links) use ($checker, $delayMs, &$summary, &$flagged) {
            foreach ($links as $link) {
                $log = $checker->check($link);

                $summary[$log->result->label()] = ($summary[$log->result->label()] ?? 0) + 1;

                if ($link->status === LinkStatus::NeedsReview) {
                    $flagged++;
                    $this->warn("  ⚠ {$link->url} → {$log->result->label()} (ditandai untuk ditinjau)");
                } else {
                    $this->line("  ✓ {$link->url} → {$log->result->label()}");
                }

                if ($delayMs > 0) {
                    usleep($delayMs * 1000);
                }
            }
        });

        $this->table(['Hasil', 'Jumlah'], collect($summary)->map(fn ($count, $label) => [$label, $count])->values());
        $this->info("Selesai. {$flagged} tautan ditandai untuk ditinjau ulang oleh admin.");

        return self::SUCCESS;
    }
}
