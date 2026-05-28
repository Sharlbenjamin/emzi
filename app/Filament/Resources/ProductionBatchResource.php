<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductionBatchResource\Pages;
use App\Models\ProductionBatch;
use App\Models\ProductVariant;
use App\Services\ProductionBatchService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class ProductionBatchResource extends Resource
{
    protected static ?string $model = ProductionBatch::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Production';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('batch_number')
                    ->maxLength(100),
                Select::make('status')
                    ->options([
                        'planned' => 'Planned',
                        'in_production' => 'In Production',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
                DatePicker::make('start_date'),
                DatePicker::make('expected_completion_date'),
                Repeater::make('items')
                    ->relationship()
                    ->label('Batch Products')
                    ->live(debounce: 500)
                    ->defaultItems(1)
                    ->schema([
                        Select::make('product_id')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->live(),
                        Select::make('product_variant_id')
                            ->label('Variant')
                            ->options(function (Get $get): array {
                                $productId = $get('product_id');

                                if (! $productId) {
                                    return [];
                                }

                                return ProductVariant::query()
                                    ->where('product_id', $productId)
                                    ->orderBy('sku')
                                    ->pluck('sku', 'id')
                                    ->all();
                            })
                            ->searchable(),
                        TextInput::make('quantity_planned')
                            ->numeric()
                            ->default(0),
                        TextInput::make('quantity_completed')
                            ->numeric()
                            ->default(0),
                        TextInput::make('unit_cost_snapshot')
                            ->label('Unit Cost Snapshot')
                            ->numeric()
                            ->step(0.01)
                            ->helperText('Optional override for this line item cost.'),
                        Textarea::make('notes')
                            ->rows(2),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Placeholder::make('batch_summary')
                    ->label('Live Batch Summary')
                    ->content(fn (Get $get): HtmlString => static::buildSummaryHtml($get('items'))),
                Repeater::make('materialOrders')
                    ->relationship()
                    ->label('Material Orders for This Batch')
                    ->schema([
                        Select::make('material_id')
                            ->relationship('material', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('supplier_id')
                            ->relationship('supplier', 'name')
                            ->searchable()
                            ->preload(),
                        TextInput::make('required_quantity')
                            ->numeric()
                            ->step(0.001),
                        TextInput::make('ordered_quantity')
                            ->numeric()
                            ->step(0.001),
                        TextInput::make('received_quantity')
                            ->numeric()
                            ->step(0.001),
                        Select::make('status')
                            ->options([
                                'pending_order' => 'Pending Order',
                                'ordered' => 'Ordered',
                                'received' => 'Received',
                                'covered' => 'Covered by stock',
                            ]),
                        DatePicker::make('ordered_at'),
                        DatePicker::make('expected_delivery_date'),
                        Textarea::make('notes')
                            ->rows(2),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->columnSpanFull(),
                Textarea::make('notes')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('batch_number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Products in Batch')
                    ->sortable(),
                TextColumn::make('total_planned_units')
                    ->label('Total Planned Units'),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('start_date')
                    ->date(),
                TextColumn::make('expected_completion_date')
                    ->date(),
                TextColumn::make('completed_at')
                    ->dateTime(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'planned' => 'Planned',
                        'in_production' => 'In Production',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductionBatches::route('/'),
            'create' => Pages\CreateProductionBatch::route('/create'),
            'edit' => Pages\EditProductionBatch::route('/{record}/edit'),
        ];
    }

    protected static function buildSummaryHtml(?array $items): HtmlString
    {
        $summary = app(ProductionBatchService::class)->buildPreviewSummaryFromFormItems($items ?? []);
        $currency = config('app.currency', 'EGP');

        if (empty($summary['products'])) {
            return new HtmlString('<p class="text-gray-500">Add product items to see live material and cost planning.</p>');
        }

        $productLines = collect($summary['products'])
            ->map(fn (array $line): string => sprintf(
                '<li><strong>%s</strong>: %d pcs planned, unit cost %s %0.2f, line %s %0.2f</li>',
                e($line['product_name'] ?? 'Product'),
                (int) ($line['planned_units'] ?? 0),
                e($currency),
                (float) ($line['unit_cost'] ?? 0),
                e($currency),
                (float) ($line['line_cost'] ?? 0),
            ))
            ->implode('');

        $materialLines = collect($summary['materials'])
            ->map(fn (array $line): string => sprintf(
                '<li><strong>%s</strong>: need %0.3f, available %0.3f, shortfall %0.3f, cost %s %0.2f</li>',
                e($line['material_name'] ?? 'Material'),
                (float) ($line['required_quantity'] ?? 0),
                (float) ($line['available_quantity'] ?? 0),
                (float) ($line['shortfall'] ?? 0),
                e($currency),
                (float) ($line['line_cost'] ?? 0),
            ))
            ->implode('');

        $supplierLines = collect($summary['suppliers_to_call'])
            ->map(fn (array $line): string => sprintf(
                '<li><strong>%s</strong> for %s (%0.3f short)</li>',
                e($line['supplier_name'] ?? 'Supplier'),
                e($line['material_name'] ?? 'Material'),
                (float) ($line['shortfall'] ?? 0),
            ))
            ->implode('');

        $html = sprintf(
            '<div class="space-y-2 text-sm">
                <p><strong>Total planned units:</strong> %d</p>
                <p><strong>Total product cost:</strong> %s %0.2f</p>
                <p><strong>Total material cost:</strong> %s %0.2f</p>
                <p><strong>Products in batch:</strong></p><ul class="list-disc pl-5">%s</ul>
                <p><strong>Material usage forecast:</strong></p><ul class="list-disc pl-5">%s</ul>
                <p><strong>Suppliers to call:</strong></p><ul class="list-disc pl-5">%s</ul>
            </div>',
            (int) ($summary['total_planned_units'] ?? 0),
            e($currency),
            (float) ($summary['total_production_cost'] ?? 0),
            e($currency),
            (float) ($summary['total_material_cost'] ?? 0),
            $productLines,
            $materialLines,
            $supplierLines !== '' ? $supplierLines : '<li>No additional ordering needed.</li>',
        );

        return new HtmlString($html);
    }
}
