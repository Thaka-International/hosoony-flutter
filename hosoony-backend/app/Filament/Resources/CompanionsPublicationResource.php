<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanionsPublicationResource\Pages;
use App\Filament\Resources\CompanionsPublicationResource\RelationManagers;
use App\Models\CompanionsPublication;
use App\Models\ClassModel;
use App\Domain\Companions\CompanionsBuilder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\ViewField;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class CompanionsPublicationResource extends Resource
{
    protected static ?string $model = CompanionsPublication::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'نشرات الرفيقات';

    protected static ?string $modelLabel = 'نشر الرفيقات';

    protected static ?string $pluralModelLabel = 'نشرات الرفيقات';

    protected static ?string $navigationGroup = 'إدارة الفصول';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('معلومات النشر')
                    ->schema([
                        Select::make('class_id')
                            ->label('الفصل')
                            ->options(function () {
                                $user = Auth::user();
                                $query = ClassModel::where('status', 'active');
                                
                                // فلترة حسب جنس المستخدم إذا لزم الأمر
                                if ($user->role === 'teacher' || $user->role === 'teacher_support') {
                                    $query->where('gender', $user->gender);
                                }
                                
                                return $query->pluck('name', 'id');
                            })
                            ->required()
                            ->searchable()
                            ->reactive(),

                        DatePicker::make('target_date')
                            ->label('تاريخ الهدف')
                            ->required()
                            ->default(now()->addDay())
                            ->minDate(now()),

                        Select::make('grouping')
                            ->label('نوع التجميع')
                            ->options([
                                'pairs' => 'ثنائيات',
                                'triplets' => 'ثلاثيات',
                            ])
                            ->required()
                            ->default('pairs'),

                        Select::make('algorithm')
                            ->label('خوارزمية التوزيع')
                            ->options([
                                'random' => 'عشوائي',
                                'rotation' => 'تدوير',
                                'manual' => 'يدوي',
                            ])
                            ->required()
                            ->default('rotation'),

                        Select::make('attendance_source')
                            ->label('مصدر الحضور')
                            ->options([
                                'all' => 'جميع الطالبات',
                                'committed_only' => 'الملتزمات فقط',
                            ])
                            ->required()
                            ->default(config('quran_lms.companions.default_attendance_source', 'committed_only')),
                    ])
                    ->columns(2),

                Section::make('الرفيقات المثبتة')
                    ->schema([
                        Repeater::make('locked_pairs')
                            ->label('الثنائيات/الثلاثيات المثبتة')
                            ->schema([
                                Repeater::make('students')
                                    ->label('معرفات الطالبات')
                                    ->schema([
                                        TextInput::make('student_id')
                                            ->label('معرف الطالبة')
                                            ->numeric()
                                            ->required(),
                                    ])
                                    ->defaultItems(2)
                                    ->minItems(2)
                                    ->maxItems(3)
                                    ->collapsible(),
                            ])
                            ->collapsible()
                            ->defaultItems(0),
                    ])
                    ->collapsible(),

                Section::make('الرفيقات الحالية')
                    ->schema([
                        Placeholder::make('pairings_display')
                            ->label('الرفيقات المولدة')
                            ->content(function ($record) {
                                if (!$record || !$record->pairings) {
                                    return 'لم يتم توليد الرفيقات بعد';
                                }
                                
                                $html = '<div class="space-y-2">';
                                foreach ($record->pairings as $index => $pair) {
                                    $html .= '<div class="p-2 bg-gray-100 rounded">';
                                    $html .= '<strong>المجموعة ' . ($index + 1) . ':</strong> ';
                                    $html .= implode(', ', $pair);
                                    $html .= '</div>';
                                }
                                $html .= '</div>';
                                
                                return new \Illuminate\Support\HtmlString($html);
                            })
                            ->visible(fn ($record) => $record && $record->pairings),
                    ])
                    ->collapsible(),

                Section::make('إعدادات Zoom')
                    ->schema([
                        TextInput::make('zoom_url_snapshot')
                            ->label('رابط Zoom')
                            ->url()
                            ->placeholder('سيتم نسخه من إعدادات الفصل'),

                        TextInput::make('zoom_password_snapshot')
                            ->label('كلمة مرور Zoom')
                            ->placeholder('سيتم نسخها من إعدادات الفصل'),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('class.name')
                    ->label('الفصل')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('target_date')
                    ->label('تاريخ الهدف')
                    ->date('Y-m-d')
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label('الحالة')
                    ->getStateUsing(function ($record) {
                        return $record->isPublished() ? 'منشور' : 'مسودة';
                    })
                    ->colors([
                        'success' => 'منشور',
                        'warning' => 'مسودة',
                    ]),

                IconColumn::make('auto_published')
                    ->label('نشر تلقائي')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),

                TextColumn::make('grouping')
                    ->label('نوع التجميع')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pairs' => 'ثنائيات',
                        'triplets' => 'ثلاثيات',
                    }),

                TextColumn::make('algorithm')
                    ->label('الخوارزمية')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'random' => 'عشوائي',
                        'rotation' => 'تدوير',
                        'manual' => 'يدوي',
                    }),

                TextColumn::make('published_at')
                    ->label('تاريخ النشر')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('publishedBy.name')
                    ->label('نشر بواسطة')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('class_id')
                    ->label('الفصل')
                    ->relationship('class', 'name'),

                Filter::make('target_date')
                    ->form([
                        DatePicker::make('target_date_from')
                            ->label('من تاريخ'),
                        DatePicker::make('target_date_until')
                            ->label('إلى تاريخ'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['target_date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('target_date', '>=', $date),
                            )
                            ->when(
                                $data['target_date_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('target_date', '<=', $date),
                            );
                    }),

                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'draft' => 'مسودة',
                        'published' => 'منشور',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if ($data['value'] === 'draft') {
                            return $query->whereNull('published_at');
                        }
                        if ($data['value'] === 'published') {
                            return $query->whereNotNull('published_at');
                        }
                        return $query;
                    }),

                SelectFilter::make('auto_published')
                    ->label('نشر تلقائي')
                    ->options([
                        '1' => 'نعم',
                        '0' => 'لا',
                    ]),
            ])
            ->actions([
                TableAction::make('generate')
                    ->label('توليد الرفيقات')
                    ->icon('heroicon-o-sparkles')
                    ->color('primary')
                    ->visible(fn ($record) => !$record->isPublished())
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $builder = app(CompanionsBuilder::class);
                        $result = $builder->build(
                            $record->class_id,
                            $record->target_date->format('Y-m-d'),
                            $record->grouping,
                            $record->algorithm,
                            $record->locked_pairs,
                            $record->attendance_source
                        );

                        $record->update([
                            'pairings' => $result['pairings'],
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('تم توليد الرفيقات بنجاح')
                            ->success()
                            ->send();
                    }),

                TableAction::make('assign_rooms_preview')
                    ->label('معاينة تخصيص الغرف')
                    ->icon('heroicon-o-home')
                    ->color('info')
                    ->visible(fn ($record) => $record->pairings && !$record->isPublished())
                    ->form([
                        TextInput::make('room_start')
                            ->label('رقم الغرفة الأولى')
                            ->numeric()
                            ->default(fn ($record) => $record->class->zoom_room_start)
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $builder = app(CompanionsBuilder::class);
                        $roomAssignments = $builder->assignRooms($record->pairings, $data['room_start']);

                        $preview = '<div class="space-y-2">';
                        foreach ($roomAssignments as $room => $students) {
                            $preview .= '<div class="p-2 bg-blue-100 rounded">';
                            $preview .= '<strong>الغرفة ' . $room . ':</strong> ';
                            $preview .= implode(', ', $students);
                            $preview .= '</div>';
                        }
                        $preview .= '</div>';

                        \Filament\Notifications\Notification::make()
                            ->title('معاينة تخصيص الغرف')
                            ->body(new \Illuminate\Support\HtmlString($preview))
                            ->info()
                            ->send();
                    }),

                TableAction::make('use_todays_zoom')
                    ->label('استخدام Zoom اليوم')
                    ->icon('heroicon-o-video-camera')
                    ->color('warning')
                    ->visible(fn ($record) => !$record->isPublished())
                    ->action(function ($record) {
                        $record->update([
                            'zoom_url_snapshot' => $record->class->zoom_url,
                            'zoom_password_snapshot' => $record->class->zoom_password,
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('تم نسخ إعدادات Zoom')
                            ->success()
                            ->send();
                    }),

                TableAction::make('publish')
                    ->label('نشر الآن')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->visible(fn ($record) => $record->pairings && !$record->isPublished())
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $builder = app(CompanionsBuilder::class);
                        $roomAssignments = $builder->assignRooms($record->pairings, $record->class->zoom_room_start);

                        $record->update([
                            'room_assignments' => $roomAssignments,
                            'zoom_url_snapshot' => $record->class->zoom_url,
                            'zoom_password_snapshot' => $record->class->zoom_password,
                            'published_at' => now(),
                            'published_by' => Auth::id(),
                            'auto_published' => false,
                        ]);

                        // إرسال الإشعارات
                        $notificationService = app(\App\Services\NotificationService::class);
                        $students = $record->class->students()->where('status', 'active')->get();

                        foreach ($roomAssignments as $roomNumber => $group) {
                            $groupStudents = $students->whereIn('id', $group);
                            
                            foreach ($groupStudents as $student) {
                                $companions = $groupStudents->where('id', '!=', $student->id);
                                $companionNames = $companions->pluck('name')->join(' و ');
                                
                                // بناء رسالة الإشعار حسب التنسيق المطلوب
                                $message = "رفيقتك/رفيقاتك: {$companionNames} — غرفة {$roomNumber}";
                                
                                if ($record->zoom_url_snapshot) {
                                    $message .= " — رابط Zoom {$record->zoom_url_snapshot}";
                                }
                                
                                if ($record->zoom_password_snapshot) {
                                    $message .= " — رمز الدخول: {$record->zoom_password_snapshot}";
                                }

                                $notification = \App\Models\Notification::create([
                                    'user_id' => $student->id,
                                    'title' => 'رفيقات اليوم',
                                    'message' => $message,
                                    'channel' => 'push',
                                    'sent_at' => now(),
                                ]);

                                $notificationService->sendNotification($notification);
                            }
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('تم نشر الرفيقات بنجاح')
                            ->success()
                            ->send();
                    }),

                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => Pages\ListCompanionsPublications::route('/'),
            'create' => Pages\CreateCompanionsPublication::route('/create'),
            'edit' => Pages\EditCompanionsPublication::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return in_array(Auth::user()->role, ['admin', 'teacher_support']);
    }

    public static function canCreate(): bool
    {
        return in_array(Auth::user()->role, ['admin', 'teacher_support']);
    }

    public static function canEdit($record): bool
    {
        return in_array(Auth::user()->role, ['admin', 'teacher_support']);
    }

    public static function canDelete($record): bool
    {
        return Auth::user()->role === 'admin';
    }
}