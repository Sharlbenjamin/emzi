<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockMovementResource\Pages;
use App\Models\StockMovement;
use Filament\Facades\Filament;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StockMovementResource extends Resource
{
    protected static ?string $model = StockMovement::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationGroup = 'Inventory';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('type')
                    ->options(array_combine(StockMovement::TYPES, array_map(fn (string $type): string => ucwords(str_replace('_', ' ', $type)), StockMovement::TYPES)))
                    ->required()
                    ->live(),
                Select::make('material_id')
                    ->relationship('material', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn (Get $get): bool => in_array($get('type'), ['material_in', 'material_out', 'adjustment'], true))
                    ->required(fn (Get $get): bool => in_array($get('type'), ['material_in', 'material_out'], true)),
                Select::make('product_variant_id')
                    ->relationship('productVariant', 'sku')
                    ->searchable()
                    ->preload()
                    ->visible(fn (Get $get): bool => in_array($get('type'), ['product_in', 'product_reserved', 'product_unreserved', 'adjustment'], true))
                    ->required(fn (Get $get): bool => in_array($get('type'), ['product_in', 'product_reserved', 'product_unreserved'], true)),
                TextInput::make('quantity')
                    ->numeric()
                    ->step(0.001)
                    ->required(),
                TextInput::make('unit_cost')
                    ->numeric()
                    ->step(0.01)
                    ->minValue(0),
                TextInput::make('reason')
                    ->maxLength(255),
                TextInput::make('reference_type')
                    ->maxLength(255),
                TextInput::make('reference_id')
                    ->numeric(),
                Textarea::make('notes')
                    ->rows(3)
                    ->columnSpanFull(),
                Hidden::make('created_by')
                    ->default(fn (): ?int => Filament::auth()->user()?->getAuthIdentifier()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge(),
                TextColumn::make('material.name')
                    ->label('Material')
                    ->searchable(),
                TextColumn::make('productVariant.sku')
                    ->label('Variant SKU')
                    ->searchable(),
                TextColumn::make('quantity')
                    ->numeric(decimalPlaces: 3),
                TextColumn::make('unit_cost')
                    ->money('USD'),
                TextColumn::make('reason')
                    ->limit(30),
                TextColumn::make('creator.name')
                    ->label('Created By'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options(array_combine(StockMovement::TYPES, array_map(fn (string $type): string => ucwords(str_replace('_', ' ', $type)), StockMovement::TYPES))),
            ])
            ->headerActions([
                CreateAction::make(),
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
            'index' => Pages\ListStockMovements::route('/'),
            'create' => Pages\CreateStockMovement::route('/create'),
        ];
    }
}
