<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PerformanceMonthlyResource\Pages;
use App\Filament\Resources\PerformanceMonthlyResource\RelationManagers;
use App\Models\PerformanceMonthly;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PerformanceMonthlyResource extends Resource
{
    protected static ?string $model = PerformanceMonthly::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'الأداء الشهري';
    protected static ?string $modelLabel = 'اسم المورد';
    protected static ?string $pluralModelLabel = 'أسماء الموارد';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('student_id')
                    ->relationship('student', 'name')
                    ->required(),
                Forms\Components\TextInput::make('year')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('month')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('total_points')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('rank')
                    ->numeric(),
                Forms\Components\TextInput::make('attendance_percentage')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('year')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('month')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_points')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rank')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('attendance_percentage')
                    ->numeric()
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
            'index' => Pages\ListPerformanceMonthlies::route('/'),
            'create' => Pages\CreatePerformanceMonthly::route('/create'),
            'edit' => Pages\EditPerformanceMonthly::route('/{record}/edit'),
        ];
    }
}
