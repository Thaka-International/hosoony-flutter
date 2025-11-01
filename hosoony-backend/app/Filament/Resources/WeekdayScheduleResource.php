<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WeekdayScheduleResource\Pages;
use App\Models\WeekdaySchedule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WeekdayScheduleResource extends Resource
{
    protected static ?string $model = WeekdaySchedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'جداول الأسبوع';
    protected static ?string $modelLabel = 'جدول الأسبوع';
    protected static ?string $pluralModelLabel = 'جداول الأسبوع';
    protected static ?string $navigationGroup = 'إدارة الجداول';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('اسم الجدول')
                    ->required()
                    ->maxLength(255),
                
                Forms\Components\Textarea::make('description')
                    ->label('الوصف')
                    ->columnSpanFull(),
                
                Forms\Components\Repeater::make('schedule')
                    ->label('جدول الأيام')
                    ->schema([
                        Forms\Components\Select::make('day')
                            ->label('اليوم')
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
                            ->default(true),
                    ])
                    ->columns(4)
                    ->reorderable()
                    ->collapsible(),
                
                Forms\Components\Toggle::make('is_active')
                    ->label('نشط')
                    ->default(true),
                
                Forms\Components\Toggle::make('is_default')
                    ->label('جدول افتراضي')
                    ->helperText('سيتم استخدام هذا الجدول كافتراضي للفصول الجديدة'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم الجدول')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('description')
                    ->label('الوصف')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),
                
                Tables\Columns\TextColumn::make('schedule')
                    ->label('الأيام المجدولة')
                    ->formatStateUsing(function ($state) {
                        if (!$state) return 'لا توجد أيام';
                        
                        // تحويل string إلى array إذا لزم الأمر
                        if (is_string($state)) {
                            $state = json_decode($state, true);
                        }
                        
                        if (!is_array($state)) {
                            return 'تنسيق غير صحيح';
                        }
                        
                        $days = [
                            'sunday' => 'الأحد',
                            'monday' => 'الاثنين',
                            'tuesday' => 'الثلاثاء',
                            'wednesday' => 'الأربعاء',
                            'thursday' => 'الخميس',
                            'friday' => 'الجمعة',
                            'saturday' => 'السبت',
                        ];
                        
                        $activeDays = [];
                        
                        // التحقق من التنسيق - إذا كان array من objects
                        if (isset($state[0]) && is_array($state[0])) {
                            // تنسيق: [{"day":"sunday","start_time":"07:00:00",...}]
                            foreach ($state as $dayData) {
                                if (isset($dayData['day']) && ($dayData['is_active'] ?? true)) {
                                    $day = $dayData['day'];
                                    $activeDays[] = $days[$day] ?? $day;
                                }
                            }
                        } else {
                            // تنسيق: {"sunday":{"start_time":"07:00:00",...}}
                            foreach ($state as $day => $times) {
                                if (is_array($times) && ($times['is_active'] ?? true)) {
                                    $activeDays[] = $days[$day] ?? $day;
                                }
                            }
                        }
                        
                        return implode(', ', $activeDays);
                    }),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),
                
                Tables\Columns\IconColumn::make('is_default')
                    ->label('افتراضي')
                    ->boolean(),
                
                Tables\Columns\TextColumn::make('classes_count')
                    ->label('عدد الفصول')
                    ->counts('classes'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->placeholder('جميع الجداول')
                    ->trueLabel('نشطة فقط')
                    ->falseLabel('غير نشطة فقط'),
                
                Tables\Filters\TernaryFilter::make('is_default')
                    ->label('الافتراضية')
                    ->placeholder('جميع الجداول')
                    ->trueLabel('افتراضية فقط')
                    ->falseLabel('غير افتراضية'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('عرض'),
                Tables\Actions\EditAction::make()
                    ->label('تعديل'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد'),
                ]),
            ])
            ->defaultSort('name', 'asc');
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
            'index' => Pages\ListWeekdaySchedules::route('/'),
            'create' => Pages\CreateWeekdaySchedule::route('/create'),
            'edit' => Pages\EditWeekdaySchedule::route('/{record}/edit'),
        ];
    }
}