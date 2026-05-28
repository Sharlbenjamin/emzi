<?php

namespace App\Support;

class Role
{
    public const SUPER_ADMIN = 'Super Admin';

    public const BRAND_MANAGER = 'Brand Manager';

    public const PRODUCTION_MANAGER = 'Production Manager';

    public const INVENTORY_MANAGER = 'Inventory Manager';

    public const DATA_ENTRY = 'Data Entry';

    public const VIEWER = 'Viewer';

    public static function all(): array
    {
        return [
            self::SUPER_ADMIN,
            self::BRAND_MANAGER,
            self::PRODUCTION_MANAGER,
            self::INVENTORY_MANAGER,
            self::DATA_ENTRY,
            self::VIEWER,
        ];
    }
}
