<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'orderItems';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->relationship('product', 'name')
                    ->required()
                    ->disabled(
                        fn($record) =>
                        $record?->order?->status !== ORDER_STATUS_DEFAULT
                    ),
                Forms\Components\TextInput::make('quantity')
                    ->numeric()
                    ->minValue(1)
                    ->required()
                    ->disabled(
                        fn($record) =>
                        $record?->order?->status !== ORDER_STATUS_DEFAULT
                    ),
                Forms\Components\TextInput::make('price')
                    ->numeric()
                    ->required()
                    ->disabled(
                        fn($record) =>
                        $record?->order?->status !== ORDER_STATUS_DEFAULT
                    ),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id'),
                Tables\Columns\TextColumn::make('product.name')->label(__('common.product')),
                Tables\Columns\TextColumn::make('quantity')->label(__('common.quantity')),
                Tables\Columns\TextColumn::make('price')->label(__('common.price'))->money('VND'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('common.created_at'))
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('common.updated_at'))
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),

                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product'),

                Tables\Columns\TextColumn::make('quantity'),

                Tables\Columns\TextColumn::make('price')
                    ->money('VND'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->disabled(
                    fn($livewire) =>
                    $livewire->ownerRecord->status !== ORDER_STATUS_DEFAULT
                ),
            ])
            ->actions([
            Tables\Actions\EditAction::make()
                ->disabled(
                    fn($record) =>
                    $record->order->status !== ORDER_STATUS_DEFAULT
                ),

            Tables\Actions\DeleteAction::make()
                ->disabled(
                    fn($record) =>
                    $record->order->status !== ORDER_STATUS_DEFAULT
                ),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
