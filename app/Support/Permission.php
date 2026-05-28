<?php

namespace App\Support;

class Permission
{
    public const USERS_MANAGE = 'users.manage';

    public const ROLES_MANAGE = 'roles.manage';

    public const SUPPLIERS_VIEW = 'suppliers.view';

    public const SUPPLIERS_CREATE = 'suppliers.create';

    public const SUPPLIERS_UPDATE = 'suppliers.update';

    public const SUPPLIERS_DELETE = 'suppliers.delete';

    public const MATERIALS_VIEW = 'materials.view';

    public const MATERIALS_CREATE = 'materials.create';

    public const MATERIALS_UPDATE = 'materials.update';

    public const MATERIALS_DELETE = 'materials.delete';

    public const COLLECTIONS_VIEW = 'collections.view';

    public const COLLECTIONS_CREATE = 'collections.create';

    public const COLLECTIONS_UPDATE = 'collections.update';

    public const COLLECTIONS_DELETE = 'collections.delete';

    public const PRODUCTS_VIEW = 'products.view';

    public const PRODUCTS_MANAGE = 'products.manage';

    public const BOM_VIEW = 'bom.view';

    public const BOM_MANAGE = 'bom.manage';

    public const PRODUCTION_BATCHES_VIEW = 'production_batches.view';

    public const PRODUCTION_BATCHES_MANAGE = 'production_batches.manage';

    public const STOCK_MOVEMENTS_VIEW = 'stock_movements.view';

    public const STOCK_MOVEMENTS_MANAGE = 'stock_movements.manage';

    public const SHOPIFY_EXPORTS_VIEW = 'shopify_exports.view';

    public const SHOPIFY_EXPORTS_MANAGE = 'shopify_exports.manage';

    public const COSTS_VIEW_SENSITIVE = 'costs.view_sensitive';

    public static function all(): array
    {
        return [
            self::USERS_MANAGE,
            self::ROLES_MANAGE,
            self::SUPPLIERS_VIEW,
            self::SUPPLIERS_CREATE,
            self::SUPPLIERS_UPDATE,
            self::SUPPLIERS_DELETE,
            self::MATERIALS_VIEW,
            self::MATERIALS_CREATE,
            self::MATERIALS_UPDATE,
            self::MATERIALS_DELETE,
            self::COLLECTIONS_VIEW,
            self::COLLECTIONS_CREATE,
            self::COLLECTIONS_UPDATE,
            self::COLLECTIONS_DELETE,
            self::PRODUCTS_VIEW,
            self::PRODUCTS_MANAGE,
            self::BOM_VIEW,
            self::BOM_MANAGE,
            self::PRODUCTION_BATCHES_VIEW,
            self::PRODUCTION_BATCHES_MANAGE,
            self::STOCK_MOVEMENTS_VIEW,
            self::STOCK_MOVEMENTS_MANAGE,
            self::SHOPIFY_EXPORTS_VIEW,
            self::SHOPIFY_EXPORTS_MANAGE,
            self::COSTS_VIEW_SENSITIVE,
        ];
    }
}
