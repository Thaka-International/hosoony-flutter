<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivitySubmissionResource\Pages;
use App\Filament\Resources\ActivitySubmissionResource\RelationManagers;
use App\Models\ActivitySubmission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ActivitySubmissionResource extends Resource
{
    protected static ?string $model = ActivitySubmission::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    
    protected static ?string $navigationLabel = 'تسليمات الأنشطة';
    
    protected static ?string $modelLabel = 'تسليم نشاط';
    
    protected static ?string $pluralModelLabel = 'تسليمات الأنشطة';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('activity_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('student_id')
                    ->required()
                    ->numeric(),
                Forms\Components\Textarea::make('submission_content')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('attachment_url'),
                Forms\Components\TextInput::make('status')
                    ->required(),
                Forms\Components\TextInput::make('grade')
                    ->numeric(),
                Forms\Components\Textarea::make('feedback')
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('submitted_at')
                    ->required(),
                Forms\Components\DateTimePicker::make('graded_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('activity_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('student_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('attachment_url')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('grade')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('submitted_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('graded_at')
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
            'index' => Pages\ListActivitySubmissions::route('/'),
            'create' => Pages\CreateActivitySubmission::route('/create'),
            'edit' => Pages\EditActivitySubmission::route('/{record}/edit'),
        ];
    }
}
