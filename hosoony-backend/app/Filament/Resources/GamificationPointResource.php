<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GamificationPointResource\Pages;
use App\Filament\Resources\GamificationPointResource\RelationManagers;
use App\Models\GamificationPoint;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GamificationPointResource extends Resource
{
    protected static ?string $model = GamificationPoint::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'نقاط التحفيز';
    protected static ?string $modelLabel = 'اسم المورد';
    protected static ?string $pluralModelLabel = 'أسماء الموارد';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('student_id')
                    ->relationship('student', 'name')
                    ->required(),
                Forms\Components\TextInput::make('source_type')
                    ->required(),
                Forms\Components\TextInput::make('source_id')
                    ->numeric(),
                Forms\Components\TextInput::make('points')
                    ->required()
                    ->numeric(),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('awarded_at')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('source_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('source_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('points')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('awarded_at')
                    ->dateTime()
                    ->sortable(),
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
            'index' => Pages\ListGamificationPoints::route('/'),
            'create' => Pages\CreateGamificationPoint::route('/create'),
            'edit' => Pages\EditGamificationPoint::route('/{record}/edit'),
        ];
    }
}
