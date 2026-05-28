<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use App\Models\User;
use App\Support\Permission;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Catalog';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('collection_id')
                    ->relationship('collection', 'name')
                    ->searchable()
                    ->preload(),
                TextInput::make('name')
                    ->maxLength(255),
                TextInput::make('handle')
                    ->maxLength(255),
                TextInput::make('sku')
                    ->maxLength(100),
                Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'active' => 'Active',
                        'archived' => 'Archived',
                    ]),
                TextInput::make('shopify_product_id')
                    ->maxLength(255),
                TextInput::make('image_url')
                    ->url()
                    ->maxLength(2048),
                TextInput::make('base_price')
                    ->numeric()
                    ->step(0.01),
                Textarea::make('description')
                    ->rows(5)
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
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('collection.name')
                    ->label('Collection')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sku')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('product_variants_count')
                    ->counts('productVariants')
                    ->label('Variants')
                    ->sortable(),
                TextColumn::make('base_price')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('production_cost')
                    ->label('Production Cost')
                    ->money('USD')
                    ->visible(fn (): bool => static::canViewSensitiveCosts())
                    ->state(fn (Product $record): float => $record->production_cost),
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
            RelationManagers\ProductVariantsRelationManager::class,
            RelationManagers\BillOfMaterialsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    protected static function canViewSensitiveCosts(): bool
    {
        $user = Filament::auth()->user();

        if (! $user instanceof User) {
            return false;
        }

        return $user->can(Permission::COSTS_VIEW_SENSITIVE);
    }
}
