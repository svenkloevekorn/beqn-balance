<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PaymentMethod: string implements HasLabel
{
    case BankTransfer = 'bank_transfer';
    case Cash = 'cash';
    case PayPal = 'paypal';
    case CreditCard = 'credit_card';
    case DirectDebit = 'direct_debit';

    public function getLabel(): string
    {
        return match ($this) {
            self::BankTransfer => 'Überweisung',
            self::Cash => 'Bar',
            self::PayPal => 'PayPal',
            self::CreditCard => 'Kreditkarte',
            self::DirectDebit => 'Lastschrift',
        };
    }
}
