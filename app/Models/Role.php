<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = [
        'name',
        'permissions',
        'is_super_admin',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'is_super_admin' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public static array $resources = [
        'customers' => 'Kunden',
        'suppliers' => 'Lieferanten',
        'articles' => 'Artikel',
        'categories' => 'Kategorien',
        'invoices' => 'Rechnungen',
        'incoming_invoices' => 'Eingangsrechnungen',
        'quotes' => 'Angebote',
        'delivery_notes' => 'Lieferscheine',
        'settings' => 'Einstellungen',
        'users' => 'Benutzer & Rollen',
    ];

    public static array $abilities = [
        'view' => 'Anzeigen',
        'create' => 'Erstellen',
        'update' => 'Bearbeiten',
        'delete' => 'Loeschen',
    ];

    public function hasPermission(string $resource, string $ability): bool
    {
        if ($this->is_super_admin) {
            return true;
        }

        $permissions = $this->permissions ?? [];

        return ! empty($permissions[$resource][$ability]);
    }
}
