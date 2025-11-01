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
use App\Models\DailyTaskDefinition;
use App\Models\ClassTaskAssignment;

class TaskAssignmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'taskAssignments';

    protected static ?string $title = 'المهام الموكلة';

    protected static ?string $recordTitleAttribute = 'taskDefinition.name';

    protected static ?string $modelLabel = 'مهمة موكلة';

    protected static ?string $pluralModelLabel = 'مهام موكلة';

    /**
     * Override canCreate to always allow creating task assignments
     */
    protected function canCreate(): bool
    {
        return true;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('daily_task_definition_id')
                    ->label('المهمة')
                    ->options(function ($record) {
                        // Get assigned task IDs (excluding current record when editing)
                        $query = $this->ownerRecord->taskAssignments();
                        
                        if ($record) {
                            $query->where('id', '!=', $record->id);
                        }
                        
                        $assignedTaskIds = $query->pluck('daily_task_definition_id')->toArray();
                        
                        // Get all active tasks
                        $allTasks = DailyTaskDefinition::active()->get();
                        
                        // Filter out already assigned tasks, but include current task if editing
                        $availableTasks = $allTasks->filter(function ($task) use ($assignedTaskIds, $record) {
                            if ($record && $task->id == $record->daily_task_definition_id) {
                                return true; // Include current task when editing
                            }
                            return !in_array($task->id, $assignedTaskIds);
                        });
                        
                        return $availableTasks->pluck('name', 'id');
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->helperText(fn ($record) => $record 
                        ? 'يمكنك تغيير المهمة أو الاحتفاظ بالحالية'
                        : 'سيتم إخفاء المهام المضافة بالفعل للفصل'),
                
                Forms\Components\TextInput::make('order')
                    ->label('ترتيب المهمة')
                    ->numeric()
                    ->default(0)
                    ->required(),
                
                Forms\Components\Toggle::make('is_active')
                    ->label('نشط')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('taskDefinition.name')
            ->modifyQueryUsing(function (Builder $query) {
                // تأكد من تحميل العلاقات
                return $query->with(['taskDefinition']);
            })
            ->columns([
                Tables\Columns\TextColumn::make('taskDefinition.name')
                    ->label('اسم المهمة')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('taskDefinition.type')
                    ->label('نوع المهمة')
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
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'hifz' => 'success',
                        'murajaah' => 'info',
                        'tilawah' => 'warning',
                        'tajweed' => 'danger',
                        'tafseer' => 'gray',
                        'other' => 'secondary',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('taskDefinition.task_location')
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
                
                Tables\Columns\TextColumn::make('taskDefinition.points_weight')
                    ->label('وزن النقاط')
                    ->numeric()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('taskDefinition.duration_minutes')
                    ->label('المدة (دقيقة)')
                    ->numeric()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('order')
                    ->label('الترتيب')
                    ->numeric()
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('taskDefinition.type')
                    ->label('نوع المهمة')
                    ->options([
                        'hifz' => 'حفظ',
                        'murajaah' => 'مراجعة',
                        'tilawah' => 'تلاوة',
                        'tajweed' => 'تجويد',
                        'tafseer' => 'تفسير',
                        'other' => 'أخرى',
                    ]),
                
                Tables\Filters\SelectFilter::make('taskDefinition.task_location')
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
            ->headerActions([
                // زر إضافة مهمة موجودة
                Tables\Actions\CreateAction::make()
                    ->label('إضافة مهمة موجودة')
                    ->icon('heroicon-o-plus')
                    ->color('success')
                    ->button()
                    ->visible(true)
                    ->authorize(true)
                    ->mutateFormDataUsing(function (array $data): array {
                        // Set default order if not provided
                        if (!isset($data['order'])) {
                            $maxOrder = $this->ownerRecord->taskAssignments()->max('order') ?? 0;
                            $data['order'] = $maxOrder + 1;
                        }
                        // Ensure class_id is set
                        $data['class_id'] = $this->ownerRecord->id;
                        return $data;
                    })
                    ->using(function (array $data): \Illuminate\Database\Eloquent\Model {
                        return $this->ownerRecord->taskAssignments()->create($data);
                    })
                    ->after(function ($record, array $data): void {
                        Notification::make()
                            ->title('تم إضافة المهمة بنجاح')
                            ->success()
                            ->send();
                    }),
                
                // زر إضافة مهمة جديدة كلياً
                Action::make('create_new_task')
                    ->label('تعريف مهمة جديدة')
                    ->icon('heroicon-o-plus-circle')
                    ->color('primary')
                    ->button()
                    ->modalHeading('تعريف مهمة جديدة')
                    ->modalSubmitActionLabel('حفظ وربط بالفصل')
                    ->form([
                        Forms\Components\TextInput::make('name')
                            ->label('اسم المهمة')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('الوصف')
                            ->columnSpanFull()
                            ->rows(3),
                        
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
                            ->searchable()
                            ->default('hifz'),
                        
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
                            ->default(1)
                            ->minValue(0),
                        
                        Forms\Components\TextInput::make('duration_minutes')
                            ->label('المدة بالدقائق')
                            ->numeric()
                            ->minValue(0),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('نشط')
                            ->default(true)
                            ->required(),
                        
                        Forms\Components\TextInput::make('order')
                            ->label('ترتيب المهمة في الفصل')
                            ->numeric()
                            ->default(function () {
                                return $this->ownerRecord->taskAssignments()->max('order') ?? 0;
                            })
                            ->helperText('ترتيب هذه المهمة ضمن مهام الفصل'),
                    ])
                    ->action(function (array $data): void {
                        // Create the task definition first
                        $taskDefinition = DailyTaskDefinition::create([
                            'name' => $data['name'],
                            'description' => $data['description'] ?? null,
                            'type' => $data['type'],
                            'task_location' => $data['task_location'],
                            'points_weight' => $data['points_weight'] ?? 1,
                            'duration_minutes' => $data['duration_minutes'] ?? null,
                            'is_active' => $data['is_active'] ?? true,
                        ]);
                        
                        // Then create the assignment for this class
                        $maxOrder = $this->ownerRecord->taskAssignments()->max('order') ?? 0;
                        $order = $data['order'] ?? ($maxOrder + 1);
                        
                        ClassTaskAssignment::create([
                            'class_id' => $this->ownerRecord->id,
                            'daily_task_definition_id' => $taskDefinition->id,
                            'order' => $order,
                            'is_active' => true,
                        ]);
                        
                        Notification::make()
                            ->title('تم إنشاء المهمة وربطها بالفصل بنجاح')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('تعديل'),
                
                Action::make('toggle_active')
                    ->label(fn ($record) => $record->is_active ? 'تعطيل' : 'تفعيل')
                    ->icon(fn ($record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn ($record) => $record->is_active ? 'danger' : 'success')
                    ->action(function ($record) {
                        $wasActive = $record->is_active;
                        $record->update(['is_active' => !$wasActive]);
                        Notification::make()
                            ->title(!$wasActive ? 'تم تفعيل المهمة' : 'تم تعطيل المهمة')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading(fn ($record) => $record->is_active ? 'تعطيل المهمة' : 'تفعيل المهمة')
                    ->modalDescription(fn ($record) => $record->is_active 
                        ? 'هل أنت متأكد من تعطيل هذه المهمة؟'
                        : 'هل أنت متأكد من تفعيل هذه المهمة؟'),
                
                Action::make('move_up')
                    ->label('نقل لأعلى')
                    ->icon('heroicon-o-arrow-up')
                    ->color('gray')
                    ->action(function ($record) {
                        $previousRecord = $this->ownerRecord->taskAssignments()
                            ->where('order', '<', $record->order)
                            ->orderBy('order', 'desc')
                            ->first();
                        
                        if ($previousRecord) {
                            $tempOrder = $record->order;
                            $record->update(['order' => $previousRecord->order]);
                            $previousRecord->update(['order' => $tempOrder]);
                            
                            Notification::make()
                                ->title('تم نقل المهمة لأعلى')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('المهمة بالفعل في الأعلى')
                                ->warning()
                                ->send();
                        }
                    }),
                
                Action::make('move_down')
                    ->label('نقل لأسفل')
                    ->icon('heroicon-o-arrow-down')
                    ->color('gray')
                    ->action(function ($record) {
                        $nextRecord = $this->ownerRecord->taskAssignments()
                            ->where('order', '>', $record->order)
                            ->orderBy('order', 'asc')
                            ->first();
                        
                        if ($nextRecord) {
                            $tempOrder = $record->order;
                            $record->update(['order' => $nextRecord->order]);
                            $nextRecord->update(['order' => $tempOrder]);
                            
                            Notification::make()
                                ->title('تم نقل المهمة لأسفل')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('المهمة بالفعل في الأسفل')
                                ->warning()
                                ->send();
                        }
                    }),
                
                Tables\Actions\DeleteAction::make()
                    ->label('حذف')
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد')
                        ->requiresConfirmation()
                        ->modalHeading('حذف المهام المحددة')
                        ->modalDescription('هل أنت متأكد من حذف المهام المحددة؟ هذا الإجراء لا يمكن التراجع عنه.'),
                    
                    BulkAction::make('activate')
                        ->label('تفعيل المحدد')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('تفعيل المهام المحددة')
                        ->modalDescription('هل أنت متأكد من تفعيل المهام المحددة؟')
                        ->action(function (Collection $records) {
                            $count = $records->count();
                            $records->each->update(['is_active' => true]);
                            Notification::make()
                                ->title("تم تفعيل {$count} مهمة")
                                ->success()
                                ->send();
                        }),
                    
                    BulkAction::make('deactivate')
                        ->label('تعطيل المحدد')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('تعطيل المهام المحددة')
                        ->modalDescription('هل أنت متأكد من تعطيل المهام المحددة؟')
                        ->action(function (Collection $records) {
                            $count = $records->count();
                            $records->each->update(['is_active' => false]);
                            Notification::make()
                                ->title("تم تعطيل {$count} مهمة")
                                ->warning()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('order', 'asc')
            ->emptyStateHeading('لا توجد مهام موكلة')
            ->emptyStateDescription('ابدأ بإضافة مهمة جديدة من التعريفات المتاحة.')
            ->emptyStateIcon('heroicon-o-clipboard-document-list')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('إضافة مهمة جديدة')
                    ->icon('heroicon-o-plus')
                    ->color('success')
                    ->mutateFormDataUsing(function (array $data): array {
                        if (!isset($data['order'])) {
                            $maxOrder = $this->ownerRecord->taskAssignments()->max('order') ?? 0;
                            $data['order'] = $maxOrder + 1;
                        }
                        return $data;
                    }),
            ])
            ->defaultSort('order', 'asc');
    }
}