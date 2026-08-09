<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ResourceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $measurement_unit
 * @property bool $is_consumable
 * @property float $critical_threshold
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Collection<int, InventoryStock> $stocks
 */
#[Fillable(['name', 'measurement_unit', 'is_consumable', 'critical_threshold'])]
class Resource extends Model
{
    /** @use HasFactory<ResourceFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_consumable' => 'boolean',
            'critical_threshold' => 'float',
        ];
    }

    /**
     * Get the inventory stocks tracking this resource.
     *
     * @return HasMany<InventoryStock, $this>
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(InventoryStock::class);
    }
}
