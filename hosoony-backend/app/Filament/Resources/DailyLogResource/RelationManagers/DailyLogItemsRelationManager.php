<?php

namespace App\Filament\Resources\DailyLogResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DailyLogItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'تفاصيل المهام';

    protected static ?string $modelLabel = 'مهمة';

    protected static ?string $pluralModelLabel = 'المهام';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('task_definition_id')
                    ->label('المهمة')
                    ->relationship('taskDefinition', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'معلق',
                        'in_progress' => 'قيد التنفيذ',
                        'completed' => 'مكتمل',
                        'skipped' => 'متخطى',
                    ])
                    ->required(),
                Forms\Components\Select::make('proof_type')
                    ->label('نوع الإثبات')
                    ->options([
                        'none' => 'بدون إثبات',
                        'note' => 'ملاحظة',
                        'audio' => 'صوتي',
                        'video' => 'فيديو',
                    ])
                    ->default('none'),
                Forms\Components\TextInput::make('quantity')
                    ->label('الكمية')
                    ->numeric()
                    ->default(1),
                Forms\Components\TextInput::make('duration_minutes')
                    ->label('المدة (بالدقائق)')
                    ->numeric(),
                Forms\Components\Textarea::make('notes')
                    ->label('ملاحظات')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('taskDefinition.name')
            ->columns([
                Tables\Columns\TextColumn::make('taskDefinition.name')
                    ->label('اسم المهمة')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('taskDefinition.type')
                    ->label('نوع المهمة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'hifz' => 'success',
                        'murajaah' => 'warning',
                        'tilawah' => 'info',
                        'tajweed' => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'hifz' => 'حفظ',
                        'murajaah' => 'مراجعة',
                        'tilawah' => 'تلاوة',
                        'tajweed' => 'تجويد',
                        'tafseer' => 'تفسير',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('taskDefinition.task_location')
                    ->label('الموقع')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'in_class' => 'primary',
                        'homework' => 'warning',
                        'home' => 'info',
                        'mosque' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'in_class' => 'أثناء الحلقة',
                        'homework' => 'واجب منزلي',
                        'home' => 'في المنزل',
                        'mosque' => 'في المسجد',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'in_progress' => 'info',
                        'pending' => 'warning',
                        'skipped' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'completed' => 'مكتمل',
                        'in_progress' => 'قيد التنفيذ',
                        'pending' => 'معلق',
                        'skipped' => 'متخطى',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('proof_type')
                    ->label('نوع الإثبات')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'none' => 'بدون',
                        'note' => 'ملاحظة',
                        'audio' => 'صوتي',
                        'video' => 'فيديو',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('الكمية')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('duration_minutes')
                    ->label('المدة (دقيقة)')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('taskDefinition.points_weight')
                    ->label('النقاط')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('notes')
                    ->label('ملاحظات')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->notes),
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
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'معلق',
                        'in_progress' => 'قيد التنفيذ',
                        'completed' => 'مكتمل',
                        'skipped' => 'متخطى',
                    ]),
                Tables\Filters\SelectFilter::make('taskDefinition.type')
                    ->label('نوع المهمة')
                    ->relationship('taskDefinition', 'type')
                    ->options([
                        'hifz' => 'حفظ',
                        'murajaah' => 'مراجعة',
                        'tilawah' => 'تلاوة',
                        'tajweed' => 'تجويد',
                        'tafseer' => 'تفسير',
                    ]),
            ])
            ->headerActions([
                // يمكن إضافة CreateAction لاحقاً إذا لزم الأمر
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}



