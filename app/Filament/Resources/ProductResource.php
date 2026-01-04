<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Filament\Resources\ProductResource\RelationManagers\ProductIngredientsRelationManager;
use App\Models\Product;
use App\Traits\FilamentHelper;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    use FilamentHelper;

    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('common.name'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('category_id')
                    ->label(__('common.category'))
                    ->relationship('category', 'name')
                    ->required(),
                Forms\Components\TextInput::make('selling_price')
                    ->label(__('common.selling_price'))
                    ->required()
                    ->numeric()
                    ->minValue(0),
                Forms\Components\Select::make('status')
                    ->label(__('common.status'))
                    ->required()
                    ->options(RECORD_STATUS_LABELS),
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
                Tables\Columns\BadgeColumn::make('category.name')
                    ->label(__('common.category'))
                    ->sortable()
                    ->searchable()
                    ->color(HIGHLIGHT_LEVEL_PRIMARY),
                Tables\Columns\BadgeColumn::make('status')
                    ->label(__('common.status'))
                    ->sortable()
                    ->searchable()
                    ->color(fn(string $state): string => RECORD_STATUS_COLORS[$state] ?? $state)
                    ->formatStateUsing(fn(string $state): string => RECORD_STATUS_LABELS[$state] ?? $state),
                Tables\Columns\TextColumn::make('selling_price')
                    ->label(__('common.selling_price'))
                    ->money('vnd')
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
            ProductIngredientsRelationManager::class,
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
}
