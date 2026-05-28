<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductSetResource\Pages;
use App\Models\ProductSet;
use App\Models\ProductVariant;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductSetResource extends Resource
{
    protected static ?string $model = ProductSet::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Catalog';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->maxLength(255),
                Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'active' => 'Active',
                        'archived' => 'Archived',
                    ]),
                TextInput::make('set_price')
                    ->numeric()
                    ->step(0.01),
                TextInput::make('cost_price')
                    ->numeric()
                    ->step(0.01),
                Toggle::make('is_active')
                    ->default(true),
                Toggle::make('can_sell_as_set')
                    ->label('Can Sell As Set')
                    ->default(true),
                Toggle::make('can_sell_items_separately')
                    ->label('Can Sell Items Separately')
                    ->default(true),
                Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),
                Repeater::make('items')
                    ->relationship()
                    ->label('Set Items')
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
                            }),
                        TextInput::make('quantity')
                            ->numeric()
                            ->default(1),
                        TextInput::make('separate_sale_price')
                            ->numeric()
                            ->step(0.01)
                            ->helperText('Price if sold individually in set context.'),
                        TextInput::make('set_allocation_price')
                            ->numeric()
                            ->step(0.01)
                            ->helperText('How much of set price this item carries.'),
                        Textarea::make('notes')
                            ->rows(2),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Textarea::make('notes')
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('set_price')
                    ->money(config('app.currency', 'EGP'))
                    ->sortable(),
                TextColumn::make('items_separate_total')
                    ->label('Separate Total')
                    ->money(config('app.currency', 'EGP')),
                TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Products')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'active' => 'Active',
                        'archived' => 'Archived',
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
            'index' => Pages\ListProductSets::route('/'),
            'create' => Pages\CreateProductSet::route('/create'),
            'edit' => Pages\EditProductSet::route('/{record}/edit'),
        ];
    }
}
