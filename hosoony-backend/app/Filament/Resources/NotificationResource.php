<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationResource\Pages;
use App\Filament\Resources\NotificationResource\RelationManagers;
use App\Models\Notification;
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
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class NotificationResource extends Resource
{
    protected static ?string $model = Notification::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell';
    
    protected static ?string $navigationLabel = 'الإشعارات';
    
    protected static ?string $modelLabel = 'إشعار';
    
    protected static ?string $pluralModelLabel = 'الإشعارات';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات الإشعار')
                    ->schema([
                        Forms\Components\Select::make('target_type')
                            ->label('نوع الهدف')
                            ->options([
                                'user' => 'مستخدم واحد',
                                'class' => 'فصل دراسي',
                                'all_students' => 'جميع الطلاب',
                                'all_teachers' => 'جميع المعلمين',
                                'all_users' => 'جميع المستخدمين',
                            ])
                            ->required()
                            ->default('user')
                            ->reactive()
                            ->afterStateUpdated(function (callable $set) {
                                $set('user_id', null);
                                $set('class_id', null);
                            }),
                        
                        Forms\Components\Select::make('user_id')
                            ->label('المستخدم')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->visible(fn (Forms\Get $get): bool => $get('target_type') === 'user')
                            ->required(fn (Forms\Get $get): bool => $get('target_type') === 'user'),
                        
                        Forms\Components\Select::make('class_id')
                            ->label('الفصل الدراسي')
                            ->relationship('class', 'name')
                            ->searchable()
                            ->visible(fn (Forms\Get $get): bool => $get('target_type') === 'class')
                            ->required(fn (Forms\Get $get): bool => $get('target_type') === 'class'),
                        
                        Forms\Components\TextInput::make('title')
                            ->label('عنوان الإشعار')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\Textarea::make('message')
                            ->label('نص الإشعار')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                        
                        Forms\Components\Select::make('type')
                            ->label('نوع الإشعار')
                            ->options([
                                'info' => 'معلومات',
                                'success' => 'نجاح',
                                'warning' => 'تحذير',
                                'error' => 'خطأ',
                                'reminder' => 'تذكير',
                            ])
                            ->required()
                            ->default('info'),
                        
                        Forms\Components\Select::make('channel')
                            ->label('قناة الإرسال')
                            ->options([
                                'in_app' => 'داخل التطبيق',
                                'email' => 'البريد الإلكتروني',
                                'sms' => 'رسالة نصية',
                                'push' => 'إشعار فوري',
                            ])
                            ->required()
                            ->default('in_app')
                            ->reactive(),
                        
                        Forms\Components\Select::make('status')
                            ->label('الحالة')
                            ->options([
                                'pending' => 'معلق',
                                'sent' => 'مرسل',
                                'failed' => 'فشل',
                                'read' => 'مقروء',
                            ])
                            ->required()
                            ->default('pending'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('إعدادات الإرسال')
                    ->schema([
                        Forms\Components\DateTimePicker::make('sent_at')
                            ->label('تاريخ الإرسال')
                            ->nullable(),
                        
                        Forms\Components\DateTimePicker::make('read_at')
                            ->label('تاريخ القراءة')
                            ->nullable(),
                        
                        Forms\Components\Textarea::make('data')
                            ->label('بيانات إضافية (JSON)')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('بيانات إضافية للإشعار بصيغة JSON'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('إعدادات البريد الإلكتروني')
                    ->schema([
                        Forms\Components\TextInput::make('email_subject')
                            ->label('موضوع البريد الإلكتروني')
                            ->visible(fn (Forms\Get $get): bool => $get('channel') === 'email')
                            ->maxLength(255),
                        
                        Forms\Components\Textarea::make('email_template')
                            ->label('قالب البريد الإلكتروني')
                            ->visible(fn (Forms\Get $get): bool => $get('channel') === 'email')
                            ->rows(4)
                            ->columnSpanFull()
                            ->helperText('يمكن استخدام متغيرات مثل {{name}} و {{message}}'),
                    ])
                    ->visible(fn (Forms\Get $get): bool => $get('channel') === 'email'),
                
                Forms\Components\Section::make('إعدادات الرسائل النصية')
                    ->schema([
                        Forms\Components\TextInput::make('sms_template')
                            ->label('قالب الرسالة النصية')
                            ->visible(fn (Forms\Get $get): bool => $get('channel') === 'sms')
                            ->maxLength(160)
                            ->columnSpanFull()
                            ->helperText('الحد الأقصى 160 حرف للرسائل النصية'),
                    ])
                    ->visible(fn (Forms\Get $get): bool => $get('channel') === 'sms'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('target_description')
                    ->label('الهدف')
                    ->getStateUsing(fn (Notification $record): string => $record->getTargetDescription())
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->limit(30),
                
                Tables\Columns\TextColumn::make('type')
                    ->label('النوع')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'info' => 'معلومات',
                        'success' => 'نجاح',
                        'warning' => 'تحذير',
                        'error' => 'خطأ',
                        'reminder' => 'تذكير',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'info' => 'info',
                        'success' => 'success',
                        'warning' => 'warning',
                        'error' => 'danger',
                        'reminder' => 'gray',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('channel')
                    ->label('القناة')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'in_app' => 'داخل التطبيق',
                        'email' => 'البريد الإلكتروني',
                        'sms' => 'رسالة نصية',
                        'push' => 'إشعار فوري',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'in_app' => 'primary',
                        'email' => 'info',
                        'sms' => 'warning',
                        'push' => 'success',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'معلق',
                        'sent' => 'مرسل',
                        'failed' => 'فشل',
                        'read' => 'مقروء',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'sent' => 'success',
                        'failed' => 'danger',
                        'read' => 'info',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('sent_at')
                    ->label('تاريخ الإرسال')
                    ->dateTime()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('read_at')
                    ->label('تاريخ القراءة')
                    ->dateTime()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('target_type')
                    ->label('نوع الهدف')
                    ->options([
                        'user' => 'مستخدم واحد',
                        'class' => 'فصل دراسي',
                        'all_students' => 'جميع الطلاب',
                        'all_teachers' => 'جميع المعلمين',
                        'all_users' => 'جميع المستخدمين',
                    ]),
                
                SelectFilter::make('type')
                    ->label('نوع الإشعار')
                    ->options([
                        'info' => 'معلومات',
                        'success' => 'نجاح',
                        'warning' => 'تحذير',
                        'error' => 'خطأ',
                        'reminder' => 'تذكير',
                    ]),
                
                SelectFilter::make('channel')
                    ->label('قناة الإرسال')
                    ->options([
                        'in_app' => 'داخل التطبيق',
                        'email' => 'البريد الإلكتروني',
                        'sms' => 'رسالة نصية',
                        'push' => 'إشعار فوري',
                    ]),
                
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'معلق',
                        'sent' => 'مرسل',
                        'failed' => 'فشل',
                        'read' => 'مقروء',
                    ]),
                
                Filter::make('unread')
                    ->label('غير مقروء')
                    ->query(fn (Builder $query): Builder => $query->whereNull('read_at')),
                
                Filter::make('sent_today')
                    ->label('مرسل اليوم')
                    ->query(fn (Builder $query): Builder => $query->whereDate('sent_at', today())),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                
                Action::make('send_now')
                    ->label('إرسال الآن')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->action(function (Notification $record) {
                        $notificationService = app(NotificationService::class);
                        $targetUsers = $record->getTargetUsers();
                        $sent = 0;
                        $failed = 0;
                        
                        foreach ($targetUsers as $user) {
                            // Create individual notification for each user
                            $individualNotification = Notification::create([
                                'user_id' => $user->id,
                                'target_type' => 'user',
                                'title' => $record->title,
                                'message' => $record->message,
                                'type' => $record->type,
                                'channel' => $record->channel,
                                'data' => $record->data,
                                'email_subject' => $record->email_subject,
                                'email_template' => $record->email_template,
                                'sms_template' => $record->sms_template,
                                'status' => 'pending',
                            ]);
                            
                            $result = $notificationService->sendNotification($individualNotification);
                            if ($result) {
                                $sent++;
                            } else {
                                $failed++;
                            }
                        }
                        
                        // Mark original notification as sent
                        $record->update(['status' => 'sent', 'sent_at' => now()]);
                        
                        FilamentNotification::make()
                            ->title("تم إرسال الإشعار لـ $sent مستخدم، فشل $failed إشعار")
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Notification $record): bool => $record->status === 'pending'),
                
                Action::make('mark_read')
                    ->label('تحديد كمقروء')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->action(function (Notification $record) {
                        $record->update(['read_at' => now(), 'status' => 'read']);
                        FilamentNotification::make()
                            ->title('تم تحديد الإشعار كمقروء')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Notification $record): bool => $record->status === 'sent' && !$record->read_at),
                
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    BulkAction::make('send_selected')
                        ->label('إرسال المحدد')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('success')
                        ->action(function (Collection $records) {
                            $notificationService = app(NotificationService::class);
                            $sent = 0;
                            $failed = 0;
                            
                            foreach ($records as $record) {
                                if ($record->status === 'pending') {
                                    $result = $notificationService->sendNotification($record);
                                    if ($result) {
                                        $record->update(['status' => 'sent', 'sent_at' => now()]);
                                        $sent++;
                                    } else {
                                        $record->update(['status' => 'failed']);
                                        $failed++;
                                    }
                                }
                            }
                            
                            FilamentNotification::make()
                                ->title("تم إرسال $sent إشعار، فشل $failed إشعار")
                                ->success()
                                ->send();
                        }),
                    
                    BulkAction::make('mark_read')
                        ->label('تحديد كمقروء')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->action(function (Collection $records) {
                            $records->each->update(['read_at' => now(), 'status' => 'read']);
                            FilamentNotification::make()
                                ->title('تم تحديد الإشعارات كمقروءة')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListNotifications::route('/'),
            'create' => Pages\CreateNotification::route('/create'),
            'view' => Pages\ViewNotification::route('/{record}'),
            'edit' => Pages\EditNotification::route('/{record}/edit'),
        ];
    }
}
