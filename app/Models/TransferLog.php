<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TransferLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $from_location_id
 * @property int $to_location_id
 * @property int $resource_id
 * @property float $quantity
 * @property int|null $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Location $locationFrom
 * @property Location $locationTo
 * @property resource $resource
 * @property User|null $user
 */
#[Fillable(['from_location_id', 'to_location_id', 'resource_id', 'quantity', 'user_id'])]
class TransferLog extends Model
{
    /** @use HasFactory<TransferLogFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'quantity' => 'float',
        ];
    }

    /**
     * Get the location the resource came from.
     *
     * @return BelongsTo<Location, $this>
     */
    public function locationFrom(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'from_location_id');
    }

    /**
     * Get the location the resource went to.
     *
     * @return BelongsTo<Location, $this>
     */
    public function locationTo(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'to_location_id');
    }

    /**
     * Get the transferred resource.
     *
     * @return BelongsTo<resource, $this>
     */
    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }

    /**
     * Get the user who performed the transfer, if any.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
