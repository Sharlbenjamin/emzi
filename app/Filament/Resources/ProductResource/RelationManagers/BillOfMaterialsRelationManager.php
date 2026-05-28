<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Models\User;
use App\Support\Permission;
use Filament\Facades\Filament;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BillOfMaterialsRelationManager extends RelationManager
{
    protected static string $relationship = 'billOfMaterials';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('material_id')
                    ->relationship('material', 'name')
                    ->searchable()
                    ->preload(),
                TextInput::make('quantity_required')
                    ->numeric()
                    ->step(0.001),
                TextInput::make('wastage_percentage')
                    ->numeric()
                    ->step(0.01)
                    ->default(0),
                Placeholder::make('actual_required_preview')
                    ->label('Actual Required Quantity')
                    ->content(function ($get): string {
                        $required = (float) ($get('quantity_required') ?? 0);
                        $wastage = (float) ($get('wastage_percentage') ?? 0);
                        $actual = $required + ($required * ($wastage / 100));

                        return number_format($actual, 3);
                    }),
                Textarea::make('notes')
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('material_id')
            ->columns([
                TextColumn::make('material.name')
                    ->label('Material')
                    ->searchable(),
                TextColumn::make('quantity_required')
                    ->numeric(decimalPlaces: 3),
                TextColumn::make('wastage_percentage')
                    ->suffix('%')
                    ->numeric(decimalPlaces: 2),
                TextColumn::make('actual_required_quantity')
                    ->label('Actual Required')
                    ->numeric(decimalPlaces: 3),
                TextColumn::make('line_cost')
                    ->label('Line Cost')
                    ->money('USD')
                    ->visible(fn (): bool => $this->canViewSensitiveCosts()),
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

    public function getTableHeading(): ?string
    {
        $cost = $this->ownerRecord->calculateProductionCost();

        if (! $this->canViewSensitiveCosts()) {
            return 'Bill of Materials';
        }

        return sprintf('Bill of Materials (Production Cost: $%s)', number_format($cost, 2));
    }

    protected function canViewSensitiveCosts(): bool
    {
        $user = Filament::auth()->user();

        if (! $user instanceof User) {
            return false;
        }

        return $user->can(Permission::COSTS_VIEW_SENSITIVE);
    }
}
