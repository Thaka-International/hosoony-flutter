<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeesPlanResource\Pages;
use App\Filament\Resources\FeesPlanResource\RelationManagers;
use App\Models\FeesPlan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FeesPlanResource extends Resource
{
    protected static ?string $model = FeesPlan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'خطط الرسوم';
    protected static ?string $modelLabel = 'اسم المورد';
    protected static ?string $pluralModelLabel = 'أسماء الموارد';
    
    public static function canViewAny(): bool
    {
        return auth()->user()?->role === 'admin'; // Only admin can view fees plans
    }
    
    public static function canCreate(): bool
    {
        return auth()->user()?->role === 'admin';
    }
    
    public static function canEdit($record): bool
    {
        return auth()->user()?->role === 'admin';
    }
    
    public static function canDelete($record): bool
    {
        return auth()->user()?->role === 'admin';
    }
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('currency')
                    ->required(),
                Forms\Components\TextInput::make('billing_period')
                    ->required(),
                Forms\Components\TextInput::make('duration_months')
                    ->numeric(),
                Forms\Components\Toggle::make('is_active')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('currency')
                    ->searchable(),
                Tables\Columns\TextColumn::make('billing_period')
                    ->searchable(),
                Tables\Columns\TextColumn::make('duration_months')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListFeesPlans::route('/'),
            'create' => Pages\CreateFeesPlan::route('/create'),
            'edit' => Pages\EditFeesPlan::route('/{record}/edit'),
        ];
    }
}
