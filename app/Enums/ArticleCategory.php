<?php
// app/Enums/ArticleCategory.php

namespace App\Enums;

enum ArticleCategory: string
{
    case Snbp = 'snbp';
    case Snbt = 'snbt';
    case Mandiri = 'mandiri';
    case Beasiswa = 'beasiswa';
    case LuarNegeri = 'luar-negeri';

    public function label(): string
    {
        return match ($this) {
            self::Snbp => 'SNBP',
            self::Snbt => 'SNBT',
            self::Mandiri => 'Jalur Mandiri',
            self::Beasiswa => 'Beasiswa',
            self::LuarNegeri => 'Kuliah Luar Negeri',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Snbp, self::Snbt => 'blue',
            self::Mandiri => 'violet',
            self::Beasiswa => 'gold',
            self::LuarNegeri => 'teal',
        };
    }
}
