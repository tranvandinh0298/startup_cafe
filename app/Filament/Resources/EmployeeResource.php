<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Filament\Resources\EmployeeResource\RelationManagers;
use App\Models\Employee;
use App\Traits\FilamentHelper;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmployeeResource extends Resource
{
    use FilamentHelper;

    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('common.name'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->label(__('common.phone'))
                    ->required()
                    ->numeric()
                    ->maxLength(255),
                Forms\Components\Select::make('role')
                    ->label(__('common.role'))
                    ->options(EMPLOYEE_ROLE_LABELS)
                    ->required(),
                Forms\Components\TextInput::make('hourly_rate')
                    ->label(__('common.hourly_rate'))
                    ->required()
                    ->numeric()
                    ->maxLength(255),
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
                Tables\Columns\TextColumn::make('phone')
                    ->label(__('common.phone'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('role')
                    ->label(__('common.role'))
                    ->sortable()
                    ->searchable()
                    ->color(fn(string $state): string => EMPLOYEE_ROLE_COLORS[$state] ?? $state)
                    ->formatStateUsing(fn(string $state): string => EMPLOYEE_ROLE_LABELS[$state] ?? $state),
                Tables\Columns\BadgeColumn::make('status')
                    ->label(__('common.status'))
                    ->sortable()
                    ->searchable()
                    ->color(fn(string $state): string => RECORD_STATUS_COLORS[$state] ?? $state)
                    ->formatStateUsing(fn(string $state): string => RECORD_STATUS_LABELS[$state] ?? $state),
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
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
