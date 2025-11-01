<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentBadgeResource\Pages;
use App\Filament\Resources\StudentBadgeResource\RelationManagers;
use App\Models\StudentBadge;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StudentBadgeResource extends Resource
{
    protected static ?string $model = StudentBadge::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'شارات الطلاب';
    protected static ?string $modelLabel = 'اسم المورد';
    protected static ?string $pluralModelLabel = 'أسماء الموارد';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('student_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('badge_id')
                    ->required()
                    ->numeric(),
                Forms\Components\DateTimePicker::make('awarded_at')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('badge_id')
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
            'index' => Pages\ListStudentBadges::route('/'),
            'create' => Pages\CreateStudentBadge::route('/create'),
            'edit' => Pages\EditStudentBadge::route('/{record}/edit'),
        ];
    }
}
