<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DailyTaskDefinitionResource\Pages;
use App\Filament\Resources\DailyTaskDefinitionResource\RelationManagers;
use App\Models\DailyTaskDefinition;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DailyTaskDefinitionResource extends Resource
{
    protected static ?string $model = DailyTaskDefinition::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'تعريفات المهام اليومية';
    protected static ?string $modelLabel = 'تعريف المهمة اليومية';
    protected static ?string $pluralModelLabel = 'تعريفات المهام اليومية';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('اسم المهمة')
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->label('الوصف')
                    ->columnSpanFull(),
                Forms\Components\Select::make('type')
                    ->label('نوع المهمة')
                    ->required()
                    ->options([
                        'hifz' => 'حفظ',
                        'murajaah' => 'مراجعة',
                        'tilawah' => 'تلاوة',
                        'tajweed' => 'تجويد',
                        'tafseer' => 'تفسير',
                        'other' => 'أخرى',
                    ])
                    ->searchable(),
                Forms\Components\Select::make('task_location')
                    ->label('مكان المهمة')
                    ->required()
                    ->options([
                        'in_class' => 'أثناء الحلقة',
                        'homework' => 'واجب منزلي',
                    ])
                    ->default('in_class'),
                Forms\Components\TextInput::make('points_weight')
                    ->label('وزن النقاط')
                    ->required()
                    ->numeric()
                    ->default(1),
                Forms\Components\TextInput::make('duration_minutes')
                    ->label('المدة بالدقائق')
                    ->numeric(),
                Forms\Components\Toggle::make('is_active')
                    ->label('نشط')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم المهمة')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('نوع المهمة')
                    ->searchable()
                    ->formatStateUsing(function (string $state): string {
                        return match ($state) {
                            'hifz' => 'حفظ',
                            'murajaah' => 'مراجعة',
                            'tilawah' => 'تلاوة',
                            'tajweed' => 'تجويد',
                            'tafseer' => 'تفسير',
                            'other' => 'أخرى',
                            default => $state,
                        };
                    }),
                Tables\Columns\TextColumn::make('task_location')
                    ->label('مكان المهمة')
                    ->formatStateUsing(function (string $state): string {
                        return match ($state) {
                            'in_class' => 'أثناء الحلقة',
                            'homework' => 'واجب منزلي',
                            default => $state,
                        };
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'in_class' => 'success',
                        'homework' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('points_weight')
                    ->label('وزن النقاط')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('duration_minutes')
                    ->label('المدة بالدقائق')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('تاريخ التحديث')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('نوع المهمة')
                    ->options([
                        'hifz' => 'حفظ',
                        'murajaah' => 'مراجعة',
                        'tilawah' => 'تلاوة',
                        'tajweed' => 'تجويد',
                        'tafseer' => 'تفسير',
                        'other' => 'أخرى',
                    ]),
                Tables\Filters\SelectFilter::make('task_location')
                    ->label('مكان المهمة')
                    ->options([
                        'in_class' => 'أثناء الحلقة',
                        'homework' => 'واجب منزلي',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->placeholder('جميع المهام')
                    ->trueLabel('نشطة فقط')
                    ->falseLabel('غير نشطة فقط'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('تعديل'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد'),
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
            'index' => Pages\ListDailyTaskDefinitions::route('/'),
            'create' => Pages\CreateDailyTaskDefinition::route('/create'),
            'edit' => Pages\EditDailyTaskDefinition::route('/{record}/edit'),
        ];
    }
}
