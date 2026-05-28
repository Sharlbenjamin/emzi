<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductVariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'productVariants';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('sku')
                    ->maxLength(100),
                TextInput::make('size')
                    ->maxLength(50),
                TextInput::make('color')
                    ->maxLength(100),
                TextInput::make('price')
                    ->numeric()
                    ->step(0.01),
                TextInput::make('available_stock')
                    ->numeric()
                    ->default(0),
                TextInput::make('reserved_stock')
                    ->numeric()
                    ->default(0),
                TextInput::make('shopify_variant_id')
                    ->maxLength(255),
                Toggle::make('is_active')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('sku')
            ->columns([
                TextColumn::make('sku')
                    ->searchable(),
                TextColumn::make('size'),
                TextColumn::make('color'),
                TextColumn::make('price')
                    ->money(config('app.currency', 'EGP'))
                    ->sortable(),
                TextColumn::make('available_stock')
                    ->sortable(),
                TextColumn::make('reserved_stock')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
