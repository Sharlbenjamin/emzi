<?php

use App\Support\Permission;
use App\Support\Role;

return [
    'roles' => Role::all(),

    'module_permissions' => [
        'users' => [
            Permission::USERS_MANAGE,
            Permission::ROLES_MANAGE,
        ],
        'suppliers' => [
            Permission::SUPPLIERS_VIEW,
            Permission::SUPPLIERS_CREATE,
            Permission::SUPPLIERS_UPDATE,
            Permission::SUPPLIERS_DELETE,
        ],
        'materials' => [
            Permission::MATERIALS_VIEW,
            Permission::MATERIALS_CREATE,
            Permission::MATERIALS_UPDATE,
            Permission::MATERIALS_DELETE,
            Permission::COSTS_VIEW_SENSITIVE,
        ],
        'collections' => [
            Permission::COLLECTIONS_VIEW,
            Permission::COLLECTIONS_CREATE,
            Permission::COLLECTIONS_UPDATE,
            Permission::COLLECTIONS_DELETE,
        ],
        'products' => [
            Permission::PRODUCTS_VIEW,
            Permission::PRODUCTS_MANAGE,
        ],
        'bill_of_materials' => [
            Permission::BOM_VIEW,
            Permission::BOM_MANAGE,
        ],
        'production_batches' => [
            Permission::PRODUCTION_BATCHES_VIEW,
            Permission::PRODUCTION_BATCHES_MANAGE,
        ],
        'stock_movements' => [
            Permission::STOCK_MOVEMENTS_VIEW,
            Permission::STOCK_MOVEMENTS_MANAGE,
        ],
        'shopify_exports' => [
            Permission::SHOPIFY_EXPORTS_VIEW,
            Permission::SHOPIFY_EXPORTS_MANAGE,
        ],
    ],

    'role_permissions' => [
        Role::SUPER_ADMIN => Permission::all(),
        Role::BRAND_MANAGER => [
            Permission::COLLECTIONS_VIEW,
            Permission::COLLECTIONS_CREATE,
            Permission::COLLECTIONS_UPDATE,
            Permission::COLLECTIONS_DELETE,
            Permission::PRODUCTS_VIEW,
            Permission::PRODUCTS_MANAGE,
            Permission::PRODUCTION_BATCHES_VIEW,
            Permission::PRODUCTION_BATCHES_MANAGE,
            Permission::SHOPIFY_EXPORTS_VIEW,
            Permission::SHOPIFY_EXPORTS_MANAGE,
            Permission::COSTS_VIEW_SENSITIVE,
        ],
        Role::PRODUCTION_MANAGER => [
            Permission::PRODUCTS_VIEW,
            Permission::BOM_VIEW,
            Permission::BOM_MANAGE,
            Permission::PRODUCTION_BATCHES_VIEW,
            Permission::PRODUCTION_BATCHES_MANAGE,
            Permission::MATERIALS_VIEW,
            Permission::COLLECTIONS_VIEW,
            Permission::COSTS_VIEW_SENSITIVE,
        ],
        Role::INVENTORY_MANAGER => [
            Permission::SUPPLIERS_VIEW,
            Permission::SUPPLIERS_CREATE,
            Permission::SUPPLIERS_UPDATE,
            Permission::MATERIALS_VIEW,
            Permission::MATERIALS_CREATE,
            Permission::MATERIALS_UPDATE,
            Permission::MATERIALS_DELETE,
            Permission::STOCK_MOVEMENTS_VIEW,
            Permission::STOCK_MOVEMENTS_MANAGE,
            Permission::COSTS_VIEW_SENSITIVE,
        ],
        Role::DATA_ENTRY => [
            Permission::SUPPLIERS_VIEW,
            Permission::SUPPLIERS_CREATE,
            Permission::SUPPLIERS_UPDATE,
            Permission::MATERIALS_VIEW,
            Permission::MATERIALS_CREATE,
            Permission::MATERIALS_UPDATE,
            Permission::COLLECTIONS_VIEW,
            Permission::COLLECTIONS_CREATE,
            Permission::COLLECTIONS_UPDATE,
        ],
        Role::VIEWER => [
            Permission::SUPPLIERS_VIEW,
            Permission::MATERIALS_VIEW,
            Permission::COLLECTIONS_VIEW,
            Permission::PRODUCTS_VIEW,
            Permission::BOM_VIEW,
            Permission::PRODUCTION_BATCHES_VIEW,
            Permission::STOCK_MOVEMENTS_VIEW,
            Permission::SHOPIFY_EXPORTS_VIEW,
        ],
    ],
];
