<?php
// app/Support/Navigation.php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Route;

class Navigation
{
    /**
     * @return list<array{label: string, route: string, match: list<string>, icon: string, section: ?string, mobile: ?string, description: ?string}>
     */
    public static function for(User $user): array
    {
        return collect(config('navigation'))
            ->only($user->getRoleNames()->all())
            ->flatten(1)
            ->filter(fn (array $item) => Route::has($item['route']))
            ->unique('route')
            ->map(fn (array $item) => [
                'label' => $item['label'],
                'route' => $item['route'],
                'match' => (array) ($item['match'] ?? $item['route']),
                'icon' => $item['icon'] ?? 'circle',
                'section' => $item['section'] ?? null,
                'mobile' => $item['mobile'] ?? null,
                'description' => $item['description'] ?? null,
            ])
            ->values()
            ->all();
    }
}
