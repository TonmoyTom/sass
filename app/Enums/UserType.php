<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * UserType Enum
 *
 * Defines the types of users in the central database.
 * Used for type-safe checks and dropdowns.
 */
enum UserType: string
{
    case SUPER_ADMIN = 'super_admin';
    case SELLER = 'seller';
    case TENANT_OWNER = 'tenant_owner';
    case STAFF = 'staff';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Admin',
            self::SELLER => 'Seller',
            self::TENANT_OWNER => 'Tenant Owner',
            self::STAFF => 'staff',
        };
    }

    /**
     * Get icon for UI.
     */
    public function icon(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'shield',
            self::SELLER => 'user',
            self::TENANT_OWNER => 'building',
        };
    }

    /**
     * Get all values as array.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
