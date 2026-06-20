<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property positive-int $id
 * @property string $heading
 * @property string $content
 * @property int $order
 * @property string|null $position
 */
class Faq extends Model
{
    use HasFactory;

    protected $fillable = [
        'heading',
        'content',
        'order',
        'position',
    ];
}
