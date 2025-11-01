<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use App\Models\ClassModel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    
    protected static ?string $navigationLabel = 'المستخدمون';
    
    protected static ?string $modelLabel = 'مستخدم';
    
    protected static ?string $pluralModelLabel = 'المستخدمون';
    
    protected static ?int $navigationSort = 1;
    
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
        $userRole = auth()->user()?->role;
        
        // Admin can edit anyone
        if ($userRole === 'admin') {
            return true;
        }
        
        // Sub-admin can edit teachers, teacher_support, and students (but not other admins)
        if ($userRole === 'sub_admin') {
            return in_array($record->role, ['teacher', 'teacher_support', 'student']);
        }
        
        return false;
    }
    
    public static function canDelete($record): bool
    {
        $userRole = auth()->user()?->role;
        
        // Admin can delete anyone
        if ($userRole === 'admin') {
            return true;
        }
        
        // Sub-admin can delete teachers, teacher_support, and students (but not other admins)
        if ($userRole === 'sub_admin') {
            return in_array($record->role, ['teacher', 'teacher_support', 'student']);
        }
        
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات المستخدم')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('الاسم')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('email')
                            ->label('البريد الإلكتروني')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('password')
                            ->label('كلمة المرور')
                            ->password()
                            ->required(fn (string $context): bool => $context === 'create')
                            ->minLength(8)
                            ->dehydrated()
                            ->dehydrateStateUsing(fn ($state) => bcrypt($state ?: 'password')),
                        
                        Forms\Components\Select::make('role')
                            ->label('الدور')
                            ->options([
                                'admin' => 'مدير',
                                'sub_admin' => 'مدير مساعد',
                                'teacher' => 'معلم',
                                'teacher_support' => 'معلم مساعد',
                                'student' => 'طالب',
                            ])
                            ->required()
                            ->reactive(),
                        
                        Forms\Components\Select::make('gender')
                            ->label('الجنس')
                            ->options([
                                'male' => 'ذكر',
                                'female' => 'أنثى',
                            ])
                            ->required(),
                        
                        Forms\Components\TextInput::make('phone')
                            ->label('رقم الهاتف')
                            ->tel()
                            ->maxLength(255),
                        
                        Forms\Components\Select::make('class_id')
                            ->label('الفصل')
                            ->relationship('class', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(fn (Forms\Get $get): bool => in_array($get('role'), ['student', 'teacher', 'teacher_support'])),
                        
                        Forms\Components\Select::make('status')
                            ->label('الحالة')
                            ->options([
                                'active' => 'نشط',
                                'inactive' => 'غير نشط',
                                'suspended' => 'معلق',
                            ])
                            ->required()
                            ->default('active'),
                        
                        Forms\Components\TextInput::make('locale')
                            ->label('اللغة')
                            ->default('ar')
                            ->required(),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('معلومات ولي الأمر (للطلاب فقط)')
                    ->schema([
                        Forms\Components\TextInput::make('guardian_name')
                            ->label('اسم ولي الأمر')
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('guardian_phone')
                            ->label('هاتف ولي الأمر')
                            ->tel()
                            ->maxLength(255),
                    ])
                    ->columns(2)
                    ->visible(fn (Forms\Get $get): bool => $get('role') === 'student'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('role')
                    ->label('الدور')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'admin' => 'مدير',
                        'sub_admin' => 'مدير مساعد',
                        'teacher' => 'معلم',
                        'teacher_support' => 'معلم مساعد',
                        'student' => 'طالب',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'sub_admin' => 'warning',
                        'teacher' => 'success',
                        'teacher_support' => 'info',
                        'student' => 'warning',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('gender')
                    ->label('الجنس')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'male' => 'ذكر',
                        'female' => 'أنثى',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'male' => 'info',
                        'female' => 'success',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('class.name')
                    ->label('الفصل')
                    ->searchable()
                    ->sortable()
                    ->placeholder('غير محدد'),
                
                Tables\Columns\TextColumn::make('phone')
                    ->label('رقم الهاتف')
                    ->searchable()
                    ->placeholder('غير محدد'),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'نشط',
                        'inactive' => 'غير نشط',
                        'suspended' => 'معلق',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'warning',
                        'suspended' => 'danger',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('الدور')
                    ->options([
                        'admin' => 'مدير',
                        'sub_admin' => 'مدير مساعد',
                        'teacher' => 'معلم',
                        'teacher_support' => 'معلم مساعد',
                        'student' => 'طالب',
                    ]),
                
                SelectFilter::make('gender')
                    ->label('الجنس')
                    ->options([
                        'male' => 'ذكر',
                        'female' => 'أنثى',
                    ]),
                
                SelectFilter::make('class_id')
                    ->label('الفصل')
                    ->relationship('class', 'name')
                    ->searchable()
                    ->preload(),
                
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'active' => 'نشط',
                        'inactive' => 'غير نشط',
                        'suspended' => 'معلق',
                    ]),
                
                Filter::make('students_without_class')
                    ->label('طلاب بدون فصل')
                    ->query(fn (Builder $query): Builder => $query->where('role', 'student')->whereNull('class_id')),
                
                Filter::make('teachers_without_class')
                    ->label('معلمون بدون فصل')
                    ->query(fn (Builder $query): Builder => $query->whereIn('role', ['teacher', 'teacher_support'])->whereNull('class_id')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                
                Action::make('assign_to_class')
                    ->label('تعيين لفصل')
                    ->icon('heroicon-o-academic-cap')
                    ->color('info')
                    ->form([
                        Forms\Components\Select::make('class_id')
                            ->label('الفصل')
                            ->relationship('class', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->action(function (User $record, array $data): void {
                        $record->update($data);
                        Notification::make()
                            ->title('تم تعيين المستخدم للفصل')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (User $record): bool => in_array($record->role, ['student', 'teacher', 'teacher_support']) && is_null($record->class_id)),
                
                Action::make('remove_from_class')
                    ->label('إزالة من الفصل')
                    ->icon('heroicon-o-user-minus')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (User $record): void {
                        $record->update(['class_id' => null]);
                        Notification::make()
                            ->title('تم إزالة المستخدم من الفصل')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (User $record): bool => !is_null($record->class_id)),
                
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    BulkAction::make('assign_to_class')
                        ->label('تعيين لفصل')
                        ->icon('heroicon-o-academic-cap')
                        ->color('info')
                        ->form([
                            Forms\Components\Select::make('class_id')
                                ->label('الفصل')
                                ->relationship('class', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each->update($data);
                            Notification::make()
                                ->title('تم تعيين المستخدمين المحددين للفصل')
                                ->success()
                                ->send();
                        }),
                    
                    BulkAction::make('activate')
                        ->label('تفعيل')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records) {
                            $records->each->update(['status' => 'active']);
                            Notification::make()
                                ->title('تم تفعيل المستخدمين المحددين')
                                ->success()
                                ->send();
                        }),
                    
                    BulkAction::make('suspend')
                        ->label('تعليق')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function (Collection $records) {
                            $records->each->update(['status' => 'suspended']);
                            Notification::make()
                                ->title('تم تعليق المستخدمين المحددين')
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}