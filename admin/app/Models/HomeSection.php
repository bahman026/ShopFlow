<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\HomeSectionTypeEnum;
use Carbon\Carbon;
use Database\Factories\HomeSectionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * One ordered section of the storefront home page. The frontend renders each
 * row by its `type`, parameterised by `config` (see docs/STOREFRONT).
 *
 * @property positive-int $id
 * @property HomeSectionTypeEnum $type
 * @property string|null $title
 * @property array<string, mixed>|null $config
 * @property int $order
 * @property bool $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class HomeSection extends Model
{
    /** @use HasFactory<HomeSectionFactory> */
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'config',
        'order',
        'status',
    ];

    protected $casts = [
        'type' => HomeSectionTypeEnum::class,
        'config' => 'array',
        'order' => 'integer',
        'status' => 'boolean',
    ];
}
