<?php

namespace Database\Seeders;

use App\Models\BillOfMaterial;
use App\Models\Collection;
use App\Models\Material;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductionBatch;
use App\Models\ProductionBatchItem;
use App\Models\ProductionBatchMaterialOrder;
use App\Models\ProductSet;
use App\Models\ProductSetItem;
use App\Models\ProductVariant;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class EmziDemoSeeder extends Seeder
{
    public function run(): void
    {
        $supplierA = Supplier::query()->create([
            'name' => 'Nile Textiles',
            'phone' => '+20-101-111-1111',
            'email' => 'sales@niletextiles.example',
            'country' => 'Egypt',
            'city' => 'Cairo',
            'notes' => 'Main jersey fabric supplier. Fast restocks.',
            'is_active' => true,
        ]);

        $supplierB = Supplier::query()->create([
            'name' => 'Delta Trims',
            'phone' => '+20-102-222-2222',
            'email' => 'orders@deltatrims.example',
            'country' => 'Egypt',
            'city' => 'Alexandria',
            'notes' => 'Ribs, labels, drawcords.',
            'is_active' => true,
        ]);

        $blackJersey = Material::query()->create([
            'name' => 'Cotton Jersey Black',
            'sku' => 'MAT-JER-BLK',
            'category' => 'Fabric',
            'supplier_id' => $supplierA->id,
            'unit_type' => 'meter',
            'available_quantity' => 1200,
            'minimum_quantity_alert' => 300,
            'cost_per_unit' => 3.4,
            'color' => 'Black',
            'notes' => 'Primary body fabric.',
            'is_active' => true,
        ]);

        $wineRib = Material::query()->create([
            'name' => 'Wine Rib Knit',
            'sku' => 'MAT-RIB-WINE',
            'category' => 'Trim',
            'supplier_id' => $supplierB->id,
            'unit_type' => 'meter',
            'available_quantity' => 180,
            'minimum_quantity_alert' => 250,
            'cost_per_unit' => 2.15,
            'color' => 'Wine',
            'notes' => 'Collar and cuff rib for hoodies.',
            'is_active' => true,
        ]);

        $collection = Collection::query()->create([
            'name' => 'Core Drop 01',
            'season' => 'FW26',
            'launch_date' => now()->toDateString(),
            'description' => 'Foundational pieces for daily wear.',
            'is_active' => true,
        ]);

        $tee = Product::query()->create([
            'collection_id' => $collection->id,
            'name' => 'Core Heavy Tee',
            'handle' => 'core-heavy-tee',
            'description' => 'Heavy cotton tee with structured drape.',
            'sku' => 'EMZ-TEE-CORE',
            'status' => 'active',
            'image_url' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab',
            'base_price' => 39,
            'notes' => 'Best seller intro product for onboarding demos.',
        ]);

        $hoodie = Product::query()->create([
            'collection_id' => $collection->id,
            'name' => 'Wine Signature Hoodie',
            'handle' => 'wine-signature-hoodie',
            'description' => 'Brushed fleece hoodie with signature rib details.',
            'sku' => 'EMZ-HOOD-WINE',
            'status' => 'active',
            'image_url' => 'https://images.unsplash.com/photo-1556821840-3a63f95609a7',
            'base_price' => 89,
            'notes' => 'Used to demonstrate multi-material BOM planning.',
        ]);

        ProductImage::query()->create([
            'product_id' => $tee->id,
            'image_url' => 'https://images.unsplash.com/photo-1489987707025-afc232f7ea0f',
            'alt_text' => 'Core Heavy Tee front view',
            'position' => 1,
            'is_primary' => true,
        ]);

        ProductImage::query()->create([
            'product_id' => $tee->id,
            'image_url' => 'https://images.unsplash.com/photo-1512436991641-6745cdb1723f',
            'alt_text' => 'Core Heavy Tee side detail',
            'position' => 2,
            'is_primary' => false,
        ]);

        ProductImage::query()->create([
            'product_id' => $hoodie->id,
            'image_url' => 'https://images.unsplash.com/photo-1509631179647-0177331693ae',
            'alt_text' => 'Wine Signature Hoodie hero shot',
            'position' => 1,
            'is_primary' => true,
        ]);

        $teeVariantM = ProductVariant::query()->create([
            'product_id' => $tee->id,
            'sku' => 'EMZ-TEE-CORE-BLK-M',
            'size' => 'M',
            'color' => 'Black',
            'price' => 39,
            'available_stock' => 60,
            'reserved_stock' => 8,
            'is_active' => true,
        ]);

        $hoodieVariantL = ProductVariant::query()->create([
            'product_id' => $hoodie->id,
            'sku' => 'EMZ-HOOD-WINE-L',
            'size' => 'L',
            'color' => 'Wine',
            'price' => 89,
            'available_stock' => 25,
            'reserved_stock' => 4,
            'is_active' => true,
        ]);

        BillOfMaterial::query()->create([
            'product_id' => $tee->id,
            'material_id' => $blackJersey->id,
            'quantity_required' => 1.5,
            'wastage_percentage' => 7.5,
            'notes' => 'Body + neck tape allowance',
        ]);

        BillOfMaterial::query()->create([
            'product_id' => $hoodie->id,
            'material_id' => $blackJersey->id,
            'quantity_required' => 2.4,
            'wastage_percentage' => 9.0,
            'notes' => 'Body panels and hood',
        ]);

        BillOfMaterial::query()->create([
            'product_id' => $hoodie->id,
            'material_id' => $wineRib->id,
            'quantity_required' => 0.65,
            'wastage_percentage' => 6.0,
            'notes' => 'Cuffs, hem, and neckline',
        ]);

        $set = ProductSet::query()->create([
            'name' => 'Starter Pack - Tee + Hoodie',
            'description' => 'Can be sold as bundle or as standalone products.',
            'is_active' => true,
            'can_sell_as_set' => true,
            'can_sell_items_separately' => true,
            'set_price' => 119,
            'cost_price' => 56,
            'status' => 'active',
            'notes' => 'Demonstrates set pricing logic in panel.',
        ]);

        ProductSetItem::query()->create([
            'product_set_id' => $set->id,
            'product_id' => $tee->id,
            'product_variant_id' => $teeVariantM->id,
            'quantity' => 1,
            'separate_sale_price' => 39,
            'set_allocation_price' => 34,
            'notes' => 'Discounted allocation within bundle.',
        ]);

        ProductSetItem::query()->create([
            'product_set_id' => $set->id,
            'product_id' => $hoodie->id,
            'product_variant_id' => $hoodieVariantL->id,
            'quantity' => 1,
            'separate_sale_price' => 89,
            'set_allocation_price' => 85,
            'notes' => 'Higher weight in set value.',
        ]);

        $batch = ProductionBatch::query()->create([
            'batch_number' => 'BATCH-FW26-001',
            'status' => 'planned',
            'start_date' => now()->toDateString(),
            'expected_completion_date' => now()->addDays(14)->toDateString(),
            'notes' => 'Demo batch with multiple products and supplier shortage signals.',
        ]);

        ProductionBatchItem::query()->create([
            'production_batch_id' => $batch->id,
            'product_id' => $tee->id,
            'product_variant_id' => $teeVariantM->id,
            'quantity_planned' => 180,
            'quantity_completed' => 0,
            'unit_cost_snapshot' => $tee->production_cost,
            'notes' => 'Large tee run for launch inventory.',
        ]);

        ProductionBatchItem::query()->create([
            'production_batch_id' => $batch->id,
            'product_id' => $hoodie->id,
            'product_variant_id' => $hoodieVariantL->id,
            'quantity_planned' => 90,
            'quantity_completed' => 0,
            'unit_cost_snapshot' => $hoodie->production_cost,
            'notes' => 'Limited hoodie run.',
        ]);

        ProductionBatchMaterialOrder::query()->create([
            'production_batch_id' => $batch->id,
            'material_id' => $wineRib->id,
            'supplier_id' => $supplierB->id,
            'required_quantity' => 62.01,
            'ordered_quantity' => 45.0,
            'received_quantity' => 0.0,
            'status' => 'pending_order',
            'ordered_at' => now()->toDateString(),
            'expected_delivery_date' => now()->addDays(5)->toDateString(),
            'notes' => 'Shortfall remains; call supplier for remaining quantity.',
        ]);
    }
}
