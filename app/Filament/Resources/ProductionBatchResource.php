<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductionBatchResource\Pages;
use App\Models\ProductionBatch;
use App\Models\ProductVariant;
use Filament\Forms\Components\DatePicker;
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
                    ->required()
                    ->maxLength(100)
                    ->unique(ignoreRecord: true),
                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live(),
                Select::make('product_variant_id')
                    ->label('Product Variant')
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
                    ->minValue(1)
                    ->required(),
                TextInput::make('quantity_completed')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->required(),
                Select::make('status')
                    ->options([
                        'planned' => 'Planned',
                        'in_production' => 'In Production',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->required(),
                DatePicker::make('start_date'),
                DatePicker::make('expected_completion_date'),
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
                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('productVariant.sku')
                    ->label('Variant SKU'),
                TextColumn::make('quantity_planned')
                    ->sortable(),
                TextColumn::make('quantity_completed')
                    ->sortable(),
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
}
