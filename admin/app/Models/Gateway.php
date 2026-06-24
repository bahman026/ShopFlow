<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\GatewayForEnum;
use Carbon\Carbon;
use Database\Factories\GatewayFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property positive-int $id
 * @property string $key
 * @property string $name
 * @property GatewayForEnum $for
 * @property string|null $username
 * @property string|null $password
 * @property string|null $gate_id
 * @property bool $active
 * @property int $priority
 * @property string|null $coding
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Image|null $image
 */
class Gateway extends Model
{
    /** @use HasFactory<GatewayFactory> */
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'for',
        'username',
        'password',
        'gate_id',
        'active',
        'priority',
        'coding',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'for' => GatewayForEnum::class,
        'active' => 'boolean',
        'password' => 'encrypted',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Gateway $gateway): void {
            $gateway->image?->delete();
        });
    }

    public function image(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageable');
    }
}
