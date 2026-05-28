<?php

namespace App\Services;

use App\Models\Collection;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ShopifyCsvExportService
{
    protected const HEADERS = [
        'Handle',
        'Title',
        'Body (HTML)',
        'Vendor',
        'Product Category',
        'Type',
        'Tags',
        'Published',
        'Option1 Name',
        'Option1 Value',
        'Option2 Name',
        'Option2 Value',
        'Variant SKU',
        'Variant Inventory Qty',
        'Variant Price',
        'Variant Compare At Price',
        'Variant Requires Shipping',
        'Variant Taxable',
        'Image Src',
        'Image Position',
        'Status',
    ];

    public function exportCollection(Collection $collection): StreamedResponse
    {
        $collection->loadMissing('products.productVariants');

        $errors = $this->validateProducts($collection);

        if ($errors !== []) {
            throw ValidationException::withMessages([
                'export' => $errors,
            ]);
        }

        $filename = sprintf('shopify-collection-%s.csv', Str::slug($collection->name));

        return response()->streamDownload(function () use ($collection): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, self::HEADERS);

            foreach ($collection->products as $product) {
                foreach ($product->productVariants as $variant) {
                    fputcsv($handle, $this->mapVariantToCsvRow($collection, $product, $variant));
                }
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    protected function validateProducts(Collection $collection): array
    {
        $errors = [];

        foreach ($collection->products as $product) {
            if (blank($product->handle)) {
                $errors[] = sprintf('Product "%s" is missing handle.', $product->name);
            }

            if (blank($product->name)) {
                $errors[] = sprintf('Product ID %d is missing title.', $product->id);
            }

            if ($product->productVariants->isEmpty()) {
                $errors[] = sprintf('Product "%s" has no variants to export.', $product->name);

                continue;
            }

            foreach ($product->productVariants as $variant) {
                if (blank($variant->sku)) {
                    $errors[] = sprintf('Variant in product "%s" is missing SKU.', $product->name);
                }

                if ((float) $variant->price <= 0) {
                    $errors[] = sprintf('Variant "%s" in product "%s" must have a price above 0.', $variant->sku ?: '(no SKU)', $product->name);
                }
            }
        }

        return $errors;
    }

    protected function mapVariantToCsvRow(Collection $collection, Product $product, ProductVariant $variant): array
    {
        return [
            $product->handle,
            $product->name,
            $product->description,
            'Emzi',
            '',
            $collection->name,
            trim($collection->season),
            $product->status === 'active' ? 'TRUE' : 'FALSE',
            'Size',
            $variant->size ?? 'Default',
            'Color',
            $variant->color ?? 'Default',
            $variant->sku,
            $variant->available_stock,
            number_format((float) $variant->price, 2, '.', ''),
            '',
            'TRUE',
            'TRUE',
            $product->image_url,
            $product->image_url ? 1 : '',
            $product->status,
        ];
    }
}
