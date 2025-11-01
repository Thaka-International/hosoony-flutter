<?php

namespace App\Filament\Resources\ClassResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class SchedulesRelationManager extends RelationManager
{
    protected static string $relationship = 'schedules';

    protected static ?string $title = 'جداول الفصل';

    protected static ?string $recordTitleAttribute = 'day_of_week';

    /**
     * Override canCreate to always allow creating schedules
     */
    protected function canCreate(): bool
    {
        return true;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('day_of_week')
                    ->label('يوم الأسبوع')
                    ->options([
                        'sunday' => 'الأحد',
                        'monday' => 'الاثنين',
                        'tuesday' => 'الثلاثاء',
                        'wednesday' => 'الأربعاء',
                        'thursday' => 'الخميس',
                        'friday' => 'الجمعة',
                        'saturday' => 'السبت',
                    ])
                    ->required(),
                
                Forms\Components\TimePicker::make('start_time')
                    ->label('وقت البداية')
                    ->required(),
                
                Forms\Components\TimePicker::make('end_time')
                    ->label('وقت النهاية')
                    ->required(),
                
                Forms\Components\TextInput::make('zoom_link')
                    ->label('رابط Zoom')
                    ->url()
                    ->placeholder('https://zoom.us/j/123456789'),
                
                Forms\Components\TextInput::make('zoom_meeting_id')
                    ->label('معرف الاجتماع')
                    ->placeholder('123 456 789'),
                
                Forms\Components\TextInput::make('zoom_password')
                    ->label('كلمة مرور الاجتماع')
                    ->password(),
                
                Forms\Components\Textarea::make('notes')
                    ->label('ملاحظات')
                    ->rows(3),
                
                Forms\Components\Toggle::make('is_active')
                    ->label('نشط')
                    ->required()
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('day_of_week')
            ->columns([
                Tables\Columns\TextColumn::make('day_of_week')
                    ->label('يوم الأسبوع')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'sunday' => 'الأحد',
                        'monday' => 'الاثنين',
                        'tuesday' => 'الثلاثاء',
                        'wednesday' => 'الأربعاء',
                        'thursday' => 'الخميس',
                        'friday' => 'الجمعة',
                        'saturday' => 'السبت',
                        default => $state,
                    })
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('start_time')
                    ->label('وقت البداية')
                    ->time('H:i')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('end_time')
                    ->label('وقت النهاية')
                    ->time('H:i')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('zoom_link')
                    ->label('رابط Zoom')
                    ->limit(20)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 20) {
                            return null;
                        }
                        return $state;
                    }),
                
                Tables\Columns\TextColumn::make('zoom_meeting_id')
                    ->label('معرف الاجتماع')
                    ->placeholder('غير محدد'),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),
                
                Tables\Columns\TextColumn::make('notes')
                    ->label('ملاحظات')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('day_of_week')
                    ->label('يوم الأسبوع')
                    ->options([
                        'sunday' => 'الأحد',
                        'monday' => 'الاثنين',
                        'tuesday' => 'الثلاثاء',
                        'wednesday' => 'الأربعاء',
                        'thursday' => 'الخميس',
                        'friday' => 'الجمعة',
                        'saturday' => 'السبت',
                    ]),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('نشط')
                    ->boolean()
                    ->trueLabel('نشط فقط')
                    ->falseLabel('غير نشط فقط')
                    ->native(false),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('إضافة جدول')
                    ->icon('heroicon-o-plus')
                    ->color('success')
                    ->button()
                    ->visible(true)
                    ->authorize(true)
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['class_id'] = $this->ownerRecord->id;
                        return $data;
                    })
                    ->after(function ($record, array $data): void {
                        Notification::make()
                            ->title('تم إضافة الجدول بنجاح')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                
                Action::make('toggle_active')
                    ->label(fn ($record) => $record->is_active ? 'إلغاء التفعيل' : 'تفعيل')
                    ->icon(fn ($record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn ($record) => $record->is_active ? 'warning' : 'success')
                    ->action(function ($record) {
                        $record->update(['is_active' => !$record->is_active]);
                        Notification::make()
                            ->title($record->is_active ? 'تم تفعيل الجدول' : 'تم إلغاء تفعيل الجدول')
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    BulkAction::make('activate')
                        ->label('تفعيل')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records) {
                            $records->each->update(['is_active' => true]);
                            Notification::make()
                                ->title('تم تفعيل الجداول المحددة')
                                ->success()
                                ->send();
                        }),
                    
                    BulkAction::make('deactivate')
                        ->label('إلغاء التفعيل')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->action(function (Collection $records) {
                            $records->each->update(['is_active' => false]);
                            Notification::make()
                                ->title('تم إلغاء تفعيل الجداول المحددة')
                                ->warning()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('day_of_week', 'asc');
    }
}
