<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class ProductImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'productImages';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('image_url')
                    ->url()
                    ->maxLength(2048),
                TextInput::make('alt_text')
                    ->maxLength(255),
                TextInput::make('position')
                    ->numeric(),
                Toggle::make('is_primary'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('image_url')
            ->columns([
                ImageColumn::make('image_url')
                    ->label('Preview'),
                TextColumn::make('alt_text'),
                TextColumn::make('position')
                    ->sortable(),
                IconColumn::make('is_primary')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('uploadImages')
                    ->label('Upload Images')
                    ->icon('heroicon-o-photo')
                    ->form([
                        FileUpload::make('images')
                            ->label('Images')
                            ->image()
                            ->multiple()
                            ->disk('public')
                            ->directory('products'),
                        TextInput::make('alt_text')
                            ->label('Alt Text')
                            ->maxLength(255),
                        Toggle::make('mark_first_as_primary')
                            ->label('Mark first image as primary')
                            ->default(false),
                    ])
                    ->action(function (array $data): void {
                        $product = $this->getOwnerRecord();
                        $images = $data['images'] ?? [];

                        if ($images === []) {
                            return;
                        }

                        $currentPosition = (int) ($product->productImages()->max('position') ?? 0);
                        $markFirstAsPrimary = (bool) ($data['mark_first_as_primary'] ?? false);

                        if ($markFirstAsPrimary) {
                            $product->productImages()->update(['is_primary' => false]);
                        }

                        foreach ($images as $index => $path) {
                            $product->productImages()->create([
                                'image_url' => Storage::url($path),
                                'alt_text' => $data['alt_text'] ?? null,
                                'position' => $currentPosition + $index + 1,
                                'is_primary' => $markFirstAsPrimary && $index === 0,
                            ]);
                        }
                    }),
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
