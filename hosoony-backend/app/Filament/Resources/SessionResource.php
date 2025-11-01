<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SessionResource\Pages;
use App\Filament\Resources\SessionResource\RelationManagers;
use App\Models\Session;
use App\Models\ClassModel;
use App\Models\User;
use App\Services\NotificationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\ExportAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SessionResource extends Resource
{
    protected static ?string $model = Session::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'الجلسات';
    protected static ?string $modelLabel = 'اسم المورد';
    protected static ?string $pluralModelLabel = 'أسماء الموارد';
    
    public static function canViewAny(): bool
    {
        return in_array(auth()->user()?->role, ['admin', 'sub_admin']);
    }
    
    public static function canCreate(): bool
    {
        return in_array(auth()->user()?->role, ['admin', 'sub_admin']);
    }
    
    public static function canEdit($record): bool
    {
        return in_array(auth()->user()?->role, ['admin', 'sub_admin']);
    }
    
    public static function canDelete($record): bool
    {
        return auth()->user()?->role === 'admin'; // Only admin can delete sessions
    }
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('class_id')
                    ->relationship('class', 'name')
                    ->required(),
                Forms\Components\Select::make('teacher_id')
                    ->relationship('teacher', 'name')
                    ->required(),
                Forms\Components\TextInput::make('title')
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('starts_at')
                    ->required(),
                Forms\Components\DateTimePicker::make('ends_at'),
                Forms\Components\TextInput::make('status')
                    ->required(),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('class.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('teacher.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('starts_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ends_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
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
                SelectFilter::make('class_id')
                    ->label('الفصل')
                    ->relationship('class', 'name'),
                SelectFilter::make('teacher_id')
                    ->label('المعلم')
                    ->relationship('teacher', 'name'),
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'scheduled' => 'مجدولة',
                        'in_progress' => 'جارية',
                        'completed' => 'مكتملة',
                        'cancelled' => 'ملغية',
                    ]),
                Filter::make('upcoming')
                    ->label('الجلسات القادمة')
                    ->query(fn (Builder $query): Builder => $query->where('starts_at', '>', now())),
                Filter::make('today')
                    ->label('جلسات اليوم')
                    ->query(fn (Builder $query): Builder => $query->whereDate('starts_at', today())),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('send_reminder')
                    ->label('إرسال تذكير T-15')
                    ->icon('heroicon-o-bell')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('إرسال تذكير للجلسة')
                    ->modalDescription('هل تريد إرسال تذكير للطلاب قبل الجلسة بـ 15 دقيقة؟')
                    ->action(function (Session $record) {
                        $notificationService = app(NotificationService::class);
                        
                        // Get students in the class
                        $students = $record->class->students;
                        
                        foreach ($students as $student) {
                            $notificationService->sendNotification(
                                $student,
                                'تذكير جلسة',
                                "تذكير: جلسة {$record->title} ستبدأ خلال 15 دقيقة",
                                'reminder',
                                'in_app'
                            );
                        }
                        
                        Notification::make()
                            ->title('تم إرسال التذكير')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Session $record): bool => 
                        $record->starts_at > now() && 
                        $record->starts_at <= now()->addMinutes(15)
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    BulkAction::make('send_reminders')
                        ->label('إرسال تذكيرات T-15')
                        ->icon('heroicon-o-bell')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('إرسال تذكيرات للجلسات')
                        ->modalDescription('هل تريد إرسال تذكيرات للطلاب قبل الجلسات بـ 15 دقيقة؟')
                        ->action(function (Collection $records) {
                            $notificationService = app(NotificationService::class);
                            $sentCount = 0;
                            
                            foreach ($records as $session) {
                                if ($session->starts_at > now() && $session->starts_at <= now()->addMinutes(15)) {
                                    $students = $session->class->students;
                                    
                                    foreach ($students as $student) {
                                        $notificationService->sendNotification(
                                            $student,
                                            'تذكير جلسة',
                                            "تذكير: جلسة {$session->title} ستبدأ خلال 15 دقيقة",
                                            'reminder',
                                            'in_app'
                                        );
                                    }
                                    $sentCount++;
                                }
                            }
                            
                            Notification::make()
                                ->title("تم إرسال تذكيرات لـ {$sentCount} جلسة")
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('starts_at', 'asc')
            ->headerActions([
                ExportAction::make()
                    ->label('تصدير')
                    ->icon('heroicon-o-arrow-down-tray'),
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
            'index' => Pages\ListSessions::route('/'),
            'create' => Pages\CreateSession::route('/create'),
            'edit' => Pages\EditSession::route('/{record}/edit'),
        ];
    }
}
