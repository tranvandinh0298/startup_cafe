<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IngredientResource\Pages;
use App\Filament\Resources\IngredientResource\RelationManagers;
use App\Models\Ingredient;
use App\Traits\FilamentHelper;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class IngredientResource extends Resource
{
    use FilamentHelper;

    protected static ?string $model = Ingredient::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                Forms\Components\TextInput::make('name')
                    ->label(__('common.name'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('base_unit')
                    ->label(__('common.base_unit'))
                    ->options(INGREDIENT_BASE_UNIT_LABELS)
                    ->required(),
                Forms\Components\TextInput::make('package_size')
                    ->label(__('common.package_size'))
                    ->numeric()
                    ->required()
                    ->minValue(0),
                Forms\Components\TextInput::make('shelf_life_closed_days')
                    ->label(__('common.shelf_life_closed_days'))
                    ->numeric()
                    ->required()
                    ->minValue(0),
                Forms\Components\TextInput::make('shelf_life_opened_hours')
                    ->label(__('common.shelf_life_opened_hours'))
                    ->numeric()
                    ->required()
                    ->minValue(0),
                Forms\Components\TextInput::make('reorder_threshold')
                    ->label(__('common.reorder_threshold'))
                    ->numeric()
                    ->required()
                    ->minValue(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('common.name'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('package_size')
                    ->label(__('common.package_size'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('base_unit')
                    ->label(__('common.base_unit'))
                    ->formatStateUsing(fn(string $state): string => INGREDIENT_BASE_UNIT_LABELS[$state] ?? $state)
                    ->sortable()
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('shelf_life_closed_days')
                    ->label(__('common.shelf_life_closed_days'))
                    ->sortable()
                    ->searchable()
                    ->color(HIGHLIGHT_LEVEL_SUCCESS),
                Tables\Columns\BadgeColumn::make('shelf_life_opened_hours')
                    ->label(__('common.shelf_life_opened_hours'))
                    ->sortable()
                    ->searchable()
                    ->color(HIGHLIGHT_LEVEL_DANGER),
                Tables\Columns\TextColumn::make('reorder_threshold')
                    ->label(__('common.reorder_threshold'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('common.created_at'))
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('common.updated_at'))
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('id', 'desc');
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
            'index' => Pages\ListIngredients::route('/'),
            'create' => Pages\CreateIngredient::route('/create'),
            'edit' => Pages\EditIngredient::route('/{record}/edit'),
        ];
    }
}
