<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClassScheduleResource\Pages;
use App\Filament\Resources\ClassScheduleResource\RelationManagers;
use App\Models\ClassSchedule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClassScheduleResource extends Resource
{
    protected static ?string $model = ClassSchedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    
    protected static ?string $navigationLabel = 'جداول الفصول';
    
    protected static ?string $modelLabel = 'جدول';
    
    protected static ?string $pluralModelLabel = 'جداول الفصول';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات الجدول')
                    ->schema([
                        Forms\Components\Select::make('class_id')
                            ->label('الفصل')
                            ->relationship('class', 'name')
                            ->required()
                            ->searchable(),
                        
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
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('نشط')
                            ->required()
                            ->default(true),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('معلومات Zoom')
                    ->schema([
                        Forms\Components\TextInput::make('zoom_link')
                            ->label('رابط Zoom')
                            ->url()
                            ->placeholder('https://zoom.us/j/123456789')
                            ->helperText('رابط الاجتماع في Zoom'),
                        
                        Forms\Components\TextInput::make('zoom_meeting_id')
                            ->label('معرف الاجتماع')
                            ->placeholder('123 456 789')
                            ->helperText('معرف الاجتماع في Zoom'),
                        
                        Forms\Components\TextInput::make('zoom_password')
                            ->label('كلمة مرور الاجتماع')
                            ->password()
                            ->placeholder('كلمة المرور للانضمام للاجتماع'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('ملاحظات إضافية')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('class.name')
                    ->label('الفصل')
                    ->searchable()
                    ->sortable(),
                
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
                    ->time('H:i'),
                
                Tables\Columns\TextColumn::make('end_time')
                    ->label('وقت النهاية')
                    ->time('H:i'),
                
                Tables\Columns\TextColumn::make('zoom_link')
                    ->label('رابط Zoom')
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 30) {
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
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('class_id')
                    ->label('الفصل')
                    ->relationship('class', 'name')
                    ->searchable(),
                
                SelectFilter::make('day_of_week')
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
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                
                Action::make('join_zoom')
                    ->label('انضمام للاجتماع')
                    ->icon('heroicon-o-video-camera')
                    ->color('info')
                    ->url(fn (ClassSchedule $record): string => $record->zoom_link ?? '#')
                    ->openUrlInNewTab()
                    ->visible(fn (ClassSchedule $record): bool => !empty($record->zoom_link)),
                
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClassSchedules::route('/'),
            'create' => Pages\CreateClassSchedule::route('/create'),
            'view' => Pages\ViewClassSchedule::route('/{record}'),
            'edit' => Pages\EditClassSchedule::route('/{record}/edit'),
        ];
    }
}
