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
use App\Models\WeeklyTaskSchedule;
use App\Models\ClassTaskAssignment;
use Carbon\Carbon;

class WeeklyTaskSchedulesRelationManager extends RelationManager
{
    protected static string $relationship = 'weeklyTaskSchedules';

    protected static ?string $title = 'الخطة الأسبوعية';

    protected static ?string $recordTitleAttribute = 'task_date';

    protected static ?string $modelLabel = 'جدول أسبوعي';

    protected static ?string $pluralModelLabel = 'الخطط الأسبوعية';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات الأسبوع')
                    ->schema([
                        Forms\Components\DatePicker::make('week_start_date')
                            ->label('تاريخ بداية الأسبوع')
                            ->required()
                            ->default(now()->startOfWeek())
                            ->displayFormat('Y-m-d')
                            ->helperText('اختر تاريخ بداية الأسبوع'),
                        
                        Forms\Components\DatePicker::make('week_end_date')
                            ->label('تاريخ نهاية الأسبوع')
                            ->required()
                            ->default(now()->endOfWeek())
                            ->displayFormat('Y-m-d')
                            ->helperText('اختر تاريخ نهاية الأسبوع'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('تفاصيل المهمة')
                    ->schema([
                        Forms\Components\Select::make('day_of_week')
                            ->label('يوم الأسبوع')
                            ->options([
                                'sunday' => 'الأحد',
                                'monday' => 'الإثنين',
                                'tuesday' => 'الثلاثاء',
                                'wednesday' => 'الأربعاء',
                                'thursday' => 'الخميس',
                                'friday' => 'الجمعة',
                                'saturday' => 'السبت',
                            ])
                            ->required()
                            ->searchable(),
                        
                        Forms\Components\DatePicker::make('task_date')
                            ->label('تاريخ المهمة')
                            ->required()
                            ->displayFormat('Y-m-d')
                            ->helperText('التاريخ الفعلي للمهمة'),
                        
                        Forms\Components\Select::make('class_task_assignment_id')
                            ->label('المهمة')
                            ->relationship(
                                'classTaskAssignment',
                                'id',
                                fn (Builder $query) => $query->where('class_id', $this->ownerRecord->id)
                                    ->where('is_active', true)
                                    ->with('taskDefinition')
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->taskDefinition->name ?? '')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('اختر المهمة من المهام المربوطة بالفصل'),
                        
                        Forms\Components\Textarea::make('task_details')
                            ->label('التفاصيل')
                            ->placeholder('مثال: صفحة 23، من الآية 1 إلى 5...')
                            ->rows(3)
                            ->helperText('أدخل تفاصيل المهمة لهذا اليوم (نص حر)')
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('task_date')
            ->modifyQueryUsing(function (Builder $query) {
                return $query->with(['classTaskAssignment.taskDefinition'])
                    ->orderBy('task_date', 'asc')
                    ->orderBy('day_of_week', 'asc');
            })
            ->columns([
                Tables\Columns\TextColumn::make('day_of_week')
                    ->label('اليوم')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'sunday' => 'الأحد',
                        'monday' => 'الإثنين',
                        'tuesday' => 'الثلاثاء',
                        'wednesday' => 'الأربعاء',
                        'thursday' => 'الخميس',
                        'friday' => 'الجمعة',
                        'saturday' => 'السبت',
                        default => $state,
                    })
                    ->badge()
                    ->color('info')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('task_date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('classTaskAssignment.taskDefinition.name')
                    ->label('المهمة')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('task_details')
                    ->label('التفاصيل')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->task_details)
                    ->wrap(),
                
                Tables\Columns\TextColumn::make('week_start_date')
                    ->label('بداية الأسبوع')
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('week_end_date')
                    ->label('نهاية الأسبوع')
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
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
                        'monday' => 'الإثنين',
                        'tuesday' => 'الثلاثاء',
                        'wednesday' => 'الأربعاء',
                        'thursday' => 'الخميس',
                        'friday' => 'الجمعة',
                        'saturday' => 'السبت',
                    ]),
                
                Tables\Filters\Filter::make('week_start_date')
                    ->form([
                        Forms\Components\DatePicker::make('week_start_date')
                            ->label('بداية الأسبوع'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['week_start_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('week_start_date', $date),
                            );
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('إضافة جدول أسبوعي')
                    ->icon('heroicon-o-plus')
                    ->color('success')
                    ->button()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['class_id'] = $this->ownerRecord->id;
                        $data['created_by'] = auth()->id();
                        return $data;
                    }),
                
                Action::make('copy_to_next_week')
                    ->label('نسخ للأسبوع القادم')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('primary')
                    ->button()
                    ->requiresConfirmation()
                    ->modalHeading('نسخ الجدول للأسبوع القادم')
                    ->modalDescription('سيتم نسخ جميع سجلات الأسبوع الحالي للأسبوع القادم مع تحديث التواريخ تلقائياً.')
                    ->action(function () {
                        $currentWeekStart = now()->startOfWeek();
                        $currentWeekEnd = now()->endOfWeek();
                        
                        // جلب جميع سجلات الأسبوع الحالي
                        $currentSchedules = $this->ownerRecord->weeklyTaskSchedules()
                            ->where('week_start_date', $currentWeekStart->format('Y-m-d'))
                            ->where('week_end_date', $currentWeekEnd->format('Y-m-d'))
                            ->get();
                        
                        if ($currentSchedules->isEmpty()) {
                            Notification::make()
                                ->title('لا توجد سجلات للنسخ')
                                ->warning()
                                ->send();
                            return;
                        }
                        
                        // حساب الأسبوع القادم
                        $nextWeekStart = $currentWeekStart->copy()->addWeek();
                        $nextWeekEnd = $currentWeekEnd->copy()->addWeek();
                        
                        $copiedCount = 0;
                        
                        foreach ($currentSchedules as $schedule) {
                            // حساب التاريخ الجديد للمهمة (نفس يوم الأسبوع)
                            $daysDiff = $schedule->task_date->diffInDays($schedule->week_start_date);
                            $newTaskDate = $nextWeekStart->copy()->addDays($daysDiff);
                            
                            WeeklyTaskSchedule::create([
                                'class_id' => $schedule->class_id,
                                'week_start_date' => $nextWeekStart->format('Y-m-d'),
                                'week_end_date' => $nextWeekEnd->format('Y-m-d'),
                                'day_of_week' => $schedule->day_of_week,
                                'task_date' => $newTaskDate->format('Y-m-d'),
                                'class_task_assignment_id' => $schedule->class_task_assignment_id,
                                'task_details' => $schedule->task_details,
                                'created_by' => auth()->id(),
                            ]);
                            
                            $copiedCount++;
                        }
                        
                        Notification::make()
                            ->title("تم نسخ {$copiedCount} سجل للأسبوع القادم")
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('تعديل'),
                
                Tables\Actions\DeleteAction::make()
                    ->label('حذف')
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد')
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('task_date', 'asc')
            ->emptyStateHeading('لا توجد خطط أسبوعية')
            ->emptyStateDescription('ابدأ بإضافة جدول أسبوعي للمهام.')
            ->emptyStateIcon('heroicon-o-calendar')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('إضافة جدول أسبوعي')
                    ->icon('heroicon-o-plus')
                    ->color('success')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['class_id'] = $this->ownerRecord->id;
                        $data['created_by'] = auth()->id();
                        return $data;
                    }),
            ]);
    }
}


