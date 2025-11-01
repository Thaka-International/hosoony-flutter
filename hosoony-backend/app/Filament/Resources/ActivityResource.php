<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityResource\Pages;
use App\Filament\Resources\ActivityResource\RelationManagers;
use App\Models\Activity;
use App\Models\ClassModel;
use App\Models\User;
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

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    
    protected static ?string $navigationLabel = 'الأنشطة';
    
    protected static ?string $modelLabel = 'نشاط';
    
    protected static ?string $pluralModelLabel = 'الأنشطة';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات النشاط')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('عنوان النشاط')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('وصف النشاط')
                            ->rows(3)
                            ->columnSpanFull(),
                        
                        Forms\Components\Select::make('type')
                            ->label('نوع النشاط')
                            ->options([
                                'daily_task' => 'مهمة يومية',
                                'assignment' => 'واجب',
                                'quiz' => 'اختبار قصير',
                                'exam' => 'امتحان',
                            ])
                            ->required()
                            ->reactive(),
                        
                        Forms\Components\Select::make('status')
                            ->label('الحالة')
                            ->options([
                                'draft' => 'مسودة',
                                'published' => 'منشور',
                                'completed' => 'مكتمل',
                                'cancelled' => 'ملغي',
                            ])
                            ->required()
                            ->default('draft'),
                        
                        Forms\Components\TextInput::make('points')
                            ->label('النقاط')
                            ->numeric()
                            ->required()
                            ->default(10)
                            ->minValue(1),
                        
                        Forms\Components\DatePicker::make('due_date')
                            ->label('تاريخ الاستحقاق')
                            ->nullable(),
                        
                        Forms\Components\Textarea::make('instructions')
                            ->label('التعليمات')
                            ->rows(3)
                            ->columnSpanFull(),
                        
                        Forms\Components\Textarea::make('requirements')
                            ->label('المتطلبات')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('إعدادات المهام اليومية')
                    ->schema([
                        Forms\Components\Toggle::make('is_daily')
                            ->label('مهمة يومية')
                            ->reactive()
                            ->helperText('هل هذا نشاط يومي يجب على الطلاب إنجازه يومياً؟'),
                        
                        Forms\Components\Toggle::make('is_recurring')
                            ->label('مهمة متكررة')
                            ->visible(fn (Forms\Get $get): bool => $get('is_daily'))
                            ->helperText('هل تتكرر هذه المهمة يومياً؟'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('تعيين الفصول')
                    ->schema([
                        Forms\Components\CheckboxList::make('classes')
                            ->label('الفصول المخصصة')
                            ->relationship('classes', 'name')
                            ->searchable()
                            ->required()
                            ->columns(2)
                            ->helperText('اختر الفصول التي سيتم تعيين هذا النشاط لها'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('type')
                    ->label('النوع')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'daily_task' => 'مهمة يومية',
                        'assignment' => 'واجب',
                        'quiz' => 'اختبار قصير',
                        'exam' => 'امتحان',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'daily_task' => 'success',
                        'assignment' => 'info',
                        'quiz' => 'warning',
                        'exam' => 'danger',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('المنشئ')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('classes.name')
                    ->label('الفصول')
                    ->badge()
                    ->separator(',')
                    ->limit(2),
                
                Tables\Columns\TextColumn::make('points')
                    ->label('النقاط')
                    ->numeric()
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('is_daily')
                    ->label('يومي')
                    ->boolean(),
                
                Tables\Columns\IconColumn::make('is_recurring')
                    ->label('متكرر')
                    ->boolean(),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'مسودة',
                        'published' => 'منشور',
                        'completed' => 'مكتمل',
                        'cancelled' => 'ملغي',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'published' => 'success',
                        'completed' => 'info',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('due_date')
                    ->label('تاريخ الاستحقاق')
                    ->date('Y-m-d')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('نوع النشاط')
                    ->options([
                        'daily_task' => 'مهمة يومية',
                        'assignment' => 'واجب',
                        'quiz' => 'اختبار قصير',
                        'exam' => 'امتحان',
                    ]),
                
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'draft' => 'مسودة',
                        'published' => 'منشور',
                        'completed' => 'مكتمل',
                        'cancelled' => 'ملغي',
                    ]),
                
                SelectFilter::make('created_by')
                    ->label('المنشئ')
                    ->relationship('creator', 'name')
                    ->searchable()
                    ->preload(),
                
                Filter::make('daily_activities')
                    ->label('المهام اليومية فقط')
                    ->query(fn (Builder $query): Builder => $query->where('is_daily', true)),
                
                Filter::make('recurring_activities')
                    ->label('المهام المتكررة فقط')
                    ->query(fn (Builder $query): Builder => $query->where('is_recurring', true)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                
                Action::make('assign_to_classes')
                    ->label('تعيين لفصول')
                    ->icon('heroicon-o-academic-cap')
                    ->color('info')
                    ->form([
                        Forms\Components\CheckboxList::make('classes')
                            ->label('الفصول')
                            ->relationship('classes', 'name')
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (Activity $record, array $data): void {
                        $record->classes()->sync($data['classes']);
                        Notification::make()
                            ->title('تم تعيين النشاط للفصول المحددة')
                            ->success()
                            ->send();
                    }),
                
                Action::make('publish')
                    ->label('نشر')
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->action(function (Activity $record): void {
                        $record->update(['status' => 'published']);
                        Notification::make()
                            ->title('تم نشر النشاط')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Activity $record): bool => $record->status === 'draft'),
                
                Action::make('complete')
                    ->label('إكمال')
                    ->icon('heroicon-o-check-circle')
                    ->color('info')
                    ->action(function (Activity $record): void {
                        $record->update(['status' => 'completed']);
                        Notification::make()
                            ->title('تم إكمال النشاط')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Activity $record): bool => $record->status === 'published'),
                
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    BulkAction::make('publish')
                        ->label('نشر')
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records) {
                            $records->each->update(['status' => 'published']);
                            Notification::make()
                                ->title('تم نشر الأنشطة المحددة')
                                ->success()
                                ->send();
                        }),
                    
                    BulkAction::make('complete')
                        ->label('إكمال')
                        ->icon('heroicon-o-check-circle')
                        ->color('info')
                        ->action(function (Collection $records) {
                            $records->each->update(['status' => 'completed']);
                            Notification::make()
                                ->title('تم إكمال الأنشطة المحددة')
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
            'index' => Pages\ListActivities::route('/'),
            'create' => Pages\CreateActivity::route('/create'),
            'view' => Pages\ViewActivity::route('/{record}'),
            'edit' => Pages\EditActivity::route('/{record}/edit'),
        ];
    }
}
