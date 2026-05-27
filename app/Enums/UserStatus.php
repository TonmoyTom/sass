<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * UserStatus Enum
 */
enum UserStatus: string
{
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';
    case PENDING = 'pending';
    case BANNED = 'banned';

    public function label(): string
    {
        return match($this) {
            self::ACTIVE => 'Active',
            self::SUSPENDED => 'Suspended',
            self::PENDING => 'Pending Approval',
            self::BANNED => 'Banned',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::ACTIVE => 'green',
            self::SUSPENDED => 'orange',
            self::PENDING => 'yellow',
            self::BANNED => 'red',
        };
    }
}
