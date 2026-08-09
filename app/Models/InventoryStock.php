<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Resource as ResourceModel;
use Database\Factories\InventoryStockFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $location_id
 * @property int $resource_id
 * @property float $quantity
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Location $location
 * @property ResourceModel $resource
 */
#[Fillable(['location_id', 'resource_id', 'quantity', 'status'])]
class InventoryStock extends Model
{
    /** @use HasFactory<InventoryStockFactory> */
    use HasFactory;

    public const STATUS_OPTIMAL = 'Optimo';

    public const STATUS_LOW = 'Bajo';

    public const STATUS_CRITICAL = 'Critico';

    protected function casts(): array
    {
        return [
            'quantity' => 'float',
        ];
    }

    /**
     * Get the location where this stock is stored.
     *
     * @return BelongsTo<Location, $this>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the resource this stock tracks.
     *
     * @return BelongsTo<ResourceModel, $this>
     */
    public function resource(): BelongsTo
    {
        return $this->belongsTo(ResourceModel::class);
    }
}
