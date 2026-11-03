<?php
// app/Enums/SessionMode.php

namespace App\Enums;

enum SessionMode: string
{
    case Online = 'online';
    case Offline = 'offline';

    public function label(): string
    {
        return match ($this) {
            self::Online => 'Daring',
            self::Offline => 'Luring',
        };
    }
}
