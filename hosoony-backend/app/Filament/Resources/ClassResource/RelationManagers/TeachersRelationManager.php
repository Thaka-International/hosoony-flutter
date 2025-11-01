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

class TeachersRelationManager extends RelationManager
{
    protected static string $relationship = 'teachers';

    protected static ?string $title = 'المعلمون';

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Override canCreate to always allow creating teachers
     */
    protected function canCreate(): bool
    {
        return true;
    }

    public function form(Form $form): Form
    {
        return $form
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
                
                Forms\Components\TextInput::make('phone')
                    ->label('رقم الهاتف')
                    ->tel()
                    ->maxLength(255),
                
                Forms\Components\Select::make('role')
                    ->label('الدور')
                    ->options([
                        'teacher' => 'معلم',
                        'teacher_support' => 'معلم مساعد',
                    ])
                    ->required()
                    ->default('teacher'),
                
                Forms\Components\Select::make('status')
                    ->label('الحالة')
                    ->options([
                        'active' => 'نشط',
                        'inactive' => 'غير نشط',
                        'suspended' => 'معلق',
                    ])
                    ->required()
                    ->default('active'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('phone')
                    ->label('رقم الهاتف')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('role')
                    ->label('الدور')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'teacher' => 'معلم',
                        'teacher_support' => 'معلم مساعد',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'teacher' => 'success',
                        'teacher_support' => 'info',
                        default => 'gray',
                    }),
                
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
                    ->label('تاريخ التعيين')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('الدور')
                    ->options([
                        'teacher' => 'معلم',
                        'teacher_support' => 'معلم مساعد',
                    ]),
                
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'active' => 'نشط',
                        'inactive' => 'غير نشط',
                        'suspended' => 'معلق',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('إضافة معلم جديد')
                    ->icon('heroicon-o-plus')
                    ->color('success')
                    ->button()
                    ->visible(true)
                    ->authorize(true)
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['role'] = $data['role'] ?? 'teacher';
                        $data['gender'] = $this->ownerRecord->gender;
                        $data['class_id'] = $this->ownerRecord->id;
                        return $data;
                    })
                    ->after(function ($record, array $data): void {
                        Notification::make()
                            ->title('تم إضافة المعلم بنجاح')
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\AttachAction::make()
                    ->label('تعيين معلم موجود')
                    ->icon('heroicon-o-user-plus')
                    ->color('primary')
                    ->button()
                    ->visible(true)
                    ->authorize(true)
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['name', 'email'])
                    ->recordSelectOptionsQuery(fn (Builder $query) => $query->whereIn('role', ['teacher', 'teacher_support'])->where('class_id', null))
                    ->using(function ($record, $data) {
                        $record->update(['class_id' => $this->ownerRecord->id]);
                        Notification::make()
                            ->title('تم تعيين المعلم في الفصل بنجاح')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                
                Action::make('remove_from_class')
                    ->label('إزالة من الفصل')
                    ->icon('heroicon-o-user-minus')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['class_id' => null]);
                        Notification::make()
                            ->title('تم إزالة المعلم من الفصل')
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                    
                    BulkAction::make('activate')
                        ->label('تفعيل')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records) {
                            $records->each->update(['status' => 'active']);
                            Notification::make()
                                ->title('تم تفعيل المعلمين المحددين')
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
                                ->title('تم تعليق المعلمين المحددين')
                                ->warning()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('name', 'asc');
    }
}
