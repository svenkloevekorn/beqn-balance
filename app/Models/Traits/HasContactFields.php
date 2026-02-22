<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;

trait HasContactFields
{
    public function fullAddress(): Attribute
    {
        return Attribute::get(function () {
            return collect([
                $this->street,
                trim(($this->zip ?? '') . ' ' . ($this->city ?? '')),
                $this->country,
            ])->filter()->implode(', ');
        });
    }
}
