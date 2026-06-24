<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\SettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property positive-int $id
 * @property string $key
 * @property string|null $label
 * @property string|null $content
 * @property bool $autoload
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Setting extends Model
{
    /** @use HasFactory<SettingFactory> */
    use HasFactory;

    protected $fillable = [
        'key',
        'label',
        'content',
        'autoload',
    ];

    protected $casts = [
        'autoload' => 'boolean',
    ];
}
