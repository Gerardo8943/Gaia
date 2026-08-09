<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\LocationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $type
 * @property bool $is_pressurized
 * @property int $occupants
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Collection<int, InventoryStock> $stocks
 */
#[Fillable(['name', 'type', 'is_pressurized', 'occupants'])]
class Location extends Model
{
    /** @use HasFactory<LocationFactory> */
    use HasFactory;

    public const TYPE_HABITABLE = 'Habitable';

    public const TYPE_VEHICLE = 'Vehiculo';

    public const TYPE_EXTERIOR = 'Exterior';

    protected function casts(): array
    {
        return [
            'is_pressurized' => 'boolean',
            'occupants' => 'integer',
        ];
    }

    /**
     * Get the inventory stocks stored at this location.
     *
     * @return HasMany<InventoryStock, $this>
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(InventoryStock::class);
    }
}
