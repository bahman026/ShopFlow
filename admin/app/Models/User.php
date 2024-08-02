<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RolesEnum;
use Carbon\Carbon;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string $email
 * @property string $mobile
 * @property Carbon $mobile_verified_at
 * @property string $national_id
 * @property string $login_token
 * @property positive-int $status
 * @property Carbon|null $email_verified_at
 * @property Carbon|null $login_token_expire_time
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class User extends Authenticatable implements FilamentUser, HasName
{
    use HasFactory;
    use HasRoles;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'mobile',
        'national_id',
        'email',
        'password',
        'status',
        'login_token',
        'login_token_expire_time',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getFilamentName(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasRole([RolesEnum::SUPER_ADMIN->value, RolesEnum::ADMIN->value]);
    }
}
