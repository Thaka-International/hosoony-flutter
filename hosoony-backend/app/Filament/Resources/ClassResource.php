<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClassResource\Pages;
use App\Filament\Resources\ClassResource\RelationManagers;
use App\Models\ClassModel;
use App\Models\Program;
use App\Models\User;
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

class ClassResource extends Resource
{
    protected static ?string $model = ClassModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    
    protected static ?string $navigationLabel = 'الفصول';
    
    protected static ?string $modelLabel = 'فصل';
    
    protected static ?string $pluralModelLabel = 'الفصول';
    
    public static function canViewAny(): bool
    {
        return in_array(auth()->user()?->role, ['admin', 'sub_admin', 'teacher_support']);
    }
    
    public static function canCreate(): bool
    {
        return in_array(auth()->user()?->role, ['admin', 'sub_admin', 'teacher_support']);
    }
    
    public static function canEdit($record): bool
    {
        return in_array(auth()->user()?->role, ['admin', 'sub_admin', 'teacher_support']);
    }
    
    public static function canDelete($record): bool
    {
        return auth()->user()?->role === 'admin'; // Only admin can delete classes
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات الفصل')
                    ->schema([
                        Forms\Components\Select::make('program_id')
                            ->label('البرنامج')
                            ->relationship('program', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        
                        Forms\Components\Select::make('weekday_schedule_id')
                            ->label('جدول الأسبوع')
                            ->relationship('weekdaySchedule', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('اختر جدول الأسبوع لهذا الفصل'),
                        
                        Forms\Components\TextInput::make('name')
                            ->label('اسم الفصل')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('الوصف')
                            ->rows(3),
                        
                        Forms\Components\Select::make('gender')
                            ->label('الجنس')
                            ->options([
                                'male' => 'ذكور',
                                'female' => 'إناث',
                            ])
                            ->required(),
                        
                        Forms\Components\TextInput::make('max_students')
                            ->label('العدد الأقصى للطلاب')
                            ->numeric()
                            ->required()
                            ->default(20),
                        
                        Forms\Components\TextInput::make('current_students')
                            ->label('العدد الحالي للطلاب')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false),
                        
                        Forms\Components\Select::make('status')
                            ->label('الحالة')
                            ->options([
                                'active' => 'نشط',
                                'inactive' => 'غير نشط',
                                'completed' => 'مكتمل',
                            ])
                            ->required()
                            ->default('active'),
                        
                        Forms\Components\DatePicker::make('start_date')
                            ->label('تاريخ البداية')
                            ->required(),
                        
                        Forms\Components\DatePicker::make('end_date')
                            ->label('تاريخ النهاية')
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('إعدادات Zoom')
                    ->schema([
                        Forms\Components\TextInput::make('zoom_url')
                            ->label('رابط Zoom')
                            ->url()
                            ->placeholder('https://zoom.us/j/123456789'),

                        Forms\Components\TextInput::make('zoom_password')
                            ->label('كلمة مرور Zoom')
                            ->placeholder('كلمة المرور (اختياري)'),

                        Forms\Components\TextInput::make('zoom_room_start')
                            ->label('رقم الغرفة الأولى')
                            ->numeric()
                            ->default(1)
                            ->required(),
                    ])
                    ->columns(3)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم الفصل')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('program.name')
                    ->label('البرنامج')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('gender')
                    ->label('الجنس')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'male' => 'ذكور',
                        'female' => 'إناث',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'male' => 'info',
                        'female' => 'success',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('current_students')
                    ->label('الطلاب الحاليون')
                    ->numeric()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('max_students')
                    ->label('الحد الأقصى')
                    ->numeric()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'نشط',
                        'inactive' => 'غير نشط',
                        'completed' => 'مكتمل',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'warning',
                        'completed' => 'info',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('start_date')
                    ->label('تاريخ البداية')
                    ->date('Y-m-d')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('end_date')
                    ->label('تاريخ النهاية')
                    ->date('Y-m-d')
                    ->sortable(),

                Tables\Columns\TextColumn::make('zoom_url')
                    ->label('رابط Zoom')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('zoom_room_start')
                    ->label('رقم الغرفة الأولى')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('program_id')
                    ->label('البرنامج')
                    ->relationship('program', 'name')
                    ->searchable()
                    ->preload(),
                
                SelectFilter::make('gender')
                    ->label('الجنس')
                    ->options([
                        'male' => 'ذكور',
                        'female' => 'إناث',
                    ]),
                
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'active' => 'نشط',
                        'inactive' => 'غير نشط',
                        'completed' => 'مكتمل',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                
                Action::make('enroll_students')
                    ->label('تسجيل الطلاب')
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    ->url(fn (ClassModel $record): string => route('filament.admin.resources.users.index', ['tableFilters[class_id][value]' => $record->id]))
                    ->openUrlInNewTab(),
                
                Action::make('assign_teacher')
                    ->label('تعيين معلم')
                    ->icon('heroicon-o-user')
                    ->color('info')
                    ->url(fn (ClassModel $record): string => route('filament.admin.resources.users.index', ['tableFilters[role][value]' => 'teacher', 'tableFilters[class_id][value]' => $record->id]))
                    ->openUrlInNewTab(),
                
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
                            $records->each->update(['status' => 'active']);
                            Notification::make()
                                ->title('تم تفعيل الفصول المحددة')
                                ->success()
                                ->send();
                        }),
                    
                    BulkAction::make('deactivate')
                        ->label('إلغاء التفعيل')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->action(function (Collection $records) {
                            $records->each->update(['status' => 'inactive']);
                            Notification::make()
                                ->title('تم إلغاء تفعيل الفصول المحددة')
                                ->warning()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\StudentsRelationManager::class,
            RelationManagers\TeachersRelationManager::class,
            RelationManagers\SchedulesRelationManager::class,
            RelationManagers\TaskAssignmentsRelationManager::class,
            RelationManagers\WeeklyTaskSchedulesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClasses::route('/'),
            'create' => Pages\CreateClass::route('/create'),
            'view' => Pages\ViewClass::route('/{record}'),
            'edit' => Pages\EditClass::route('/{record}/edit'),
        ];
    }
}
