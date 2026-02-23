<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum DunningLevel: string implements HasLabel, HasColor
{
    case Reminder = 'reminder';
    case FirstWarning = 'first_warning';
    case SecondWarning = 'second_warning';

    public function getLabel(): string
    {
        return match ($this) {
            self::Reminder => 'Zahlungserinnerung',
            self::FirstWarning => '1. Mahnung',
            self::SecondWarning => '2. Mahnung',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Reminder => 'warning',
            self::FirstWarning => 'danger',
            self::SecondWarning => 'danger',
        };
    }

    public function next(): ?self
    {
        return match ($this) {
            self::Reminder => self::FirstWarning,
            self::FirstWarning => self::SecondWarning,
            self::SecondWarning => null,
        };
    }
}
