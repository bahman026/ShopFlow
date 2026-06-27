<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserStatusEnum;
use Carbon\Carbon;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @property positive-int $id
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $email
 * @property string $mobile
 * @property Carbon|null $mobile_verified_at
 * @property string|null $password
 * @property UserStatusEnum $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    /**
     * Domain used for the synthetic email given to OTP-only sign-ups (the
     * shared schema requires a unique, non-null email).
     */
    public const PLACEHOLDER_EMAIL_DOMAIN = '@mobile.shopflow.local';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'mobile',
        'mobile_verified_at',
        'password',
        'status',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'mobile_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => UserStatusEnum::class,
        ];
    }

    /**
     * Human-friendly name, falling back to the mobile number.
     */
    public function displayName(): string
    {
        $name = trim(($this->first_name ?? '').' '.($this->last_name ?? ''));

        return $name !== '' ? $name : $this->mobile;
    }

    public static function placeholderEmail(string $mobile): string
    {
        return $mobile.self::PLACEHOLDER_EMAIL_DOMAIN;
    }

    /**
     * Whether the email is the synthetic placeholder from an OTP sign-up.
     */
    public function hasPlaceholderEmail(): bool
    {
        return $this->email !== null && str_ends_with($this->email, self::PLACEHOLDER_EMAIL_DOMAIN);
    }
}
