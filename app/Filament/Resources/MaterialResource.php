<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaterialResource\Pages;
use App\Models\Material;
use App\Models\User;
use App\Support\Permission;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MaterialResource extends Resource
{
    public const UNIT_TYPES = [
        'meter' => 'Meter',
        'piece' => 'Piece',
        'kg' => 'Kg',
        'roll' => 'Roll',
        'pack' => 'Pack',
    ];

    protected static ?string $model = Material::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Operations';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->maxLength(255),
                TextInput::make('sku')
                    ->maxLength(100),
                TextInput::make('category')
                    ->maxLength(100),
                Select::make('supplier_id')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload(),
                Select::make('unit_type')
                    ->options(self::UNIT_TYPES),
                TextInput::make('meters_per_roll')
                    ->label('Meters per Roll')
                    ->numeric()
                    ->step(0.001)
                    ->helperText('Use when this material is purchased or tracked by roll.'),
                TextInput::make('kg_per_roll')
                    ->label('Kg per Roll')
                    ->numeric()
                    ->step(0.001)
                    ->helperText('Use for heavy rolls where each roll has a known weight.'),
                TextInput::make('meters_per_piece')
                    ->label('Meters per Piece')
                    ->numeric()
                    ->step(0.001)
                    ->helperText('Use when one piece equals a known meter length.'),
                TextInput::make('available_quantity')
                    ->label('Available Quantity')
                    ->numeric()
                    ->step(0.001),
                TextInput::make('minimum_quantity_alert')
                    ->label('Minimum Quantity Alert')
                    ->numeric()
                    ->step(0.001),
                TextInput::make('cost_per_unit')
                    ->label('Cost per Unit')
                    ->numeric()
                    ->step(0.01)
                    ->visible(fn (): bool => static::canViewSensitiveCosts()),
                TextInput::make('color')
                    ->maxLength(100),
                Textarea::make('notes')
                    ->rows(3)
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sku')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category')
                    ->searchable(),
                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('unit_type')
                    ->badge(),
                TextColumn::make('meters_per_roll')
                    ->label('m/Roll')
                    ->numeric(decimalPlaces: 3)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('kg_per_roll')
                    ->label('kg/Roll')
                    ->numeric(decimalPlaces: 3)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('meters_per_piece')
                    ->label('m/Piece')
                    ->numeric(decimalPlaces: 3)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('available_quantity')
                    ->numeric(decimalPlaces: 3)
                    ->sortable(),
                TextColumn::make('minimum_quantity_alert')
                    ->label('Min Alert')
                    ->numeric(decimalPlaces: 3),
                IconColumn::make('is_low_stock')
                    ->label('Low Stock')
                    ->boolean(),
                TextColumn::make('total_material_value')
                    ->label('Total Value')
                    ->money(config('app.currency', 'EGP'))
                    ->visible(fn (): bool => static::canViewSensitiveCosts())
                    ->state(fn (Material $record): float => $record->total_material_value)
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderByRaw("(available_quantity * cost_per_unit) {$direction}")),
                TextColumn::make('cost_per_unit')
                    ->label('Cost / Unit')
                    ->money(config('app.currency', 'EGP'))
                    ->visible(fn (): bool => static::canViewSensitiveCosts()),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('unit_type')
                    ->options(self::UNIT_TYPES),
                TernaryFilter::make('is_active')
                    ->label('Active status')
                    ->boolean(),
                TernaryFilter::make('is_low_stock')
                    ->label('Low stock')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereColumn('available_quantity', '<=', 'minimum_quantity_alert'),
                        false: fn (Builder $query): Builder => $query->whereColumn('available_quantity', '>', 'minimum_quantity_alert'),
                        blank: fn (Builder $query): Builder => $query,
                    ),
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
            'index' => Pages\ListMaterials::route('/'),
            'create' => Pages\CreateMaterial::route('/create'),
            'edit' => Pages\EditMaterial::route('/{record}/edit'),
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
