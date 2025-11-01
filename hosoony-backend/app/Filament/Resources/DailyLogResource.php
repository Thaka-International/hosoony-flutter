<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DailyLogResource\Pages;
use App\Filament\Resources\DailyLogResource\RelationManagers;
use App\Models\DailyLog;
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
use Filament\Tables\Actions\ExportAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DailyLogResource extends Resource
{
    protected static ?string $model = DailyLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'سجلات تسليم المهام';
    protected static ?string $modelLabel = 'سجل تسليم';
    protected static ?string $pluralModelLabel = 'سجلات تسليم المهام';
    protected static ?string $navigationGroup = 'إدارة الطلاب';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('student_id')
                    ->label('الطالب')
                    ->relationship('student', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\DatePicker::make('log_date')
                    ->label('تاريخ التسليم')
                    ->required()
                    ->default(now()),
                Forms\Components\Select::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'معلق',
                        'submitted' => 'تم التسليم',
                        'verified' => 'متحقق',
                        'rejected' => 'مرفوض',
                    ])
                    ->required()
                    ->default('submitted'),
                Forms\Components\TextInput::make('finish_order')
                    ->label('ترتيب الإنجاز')
                    ->numeric()
                    ->helperText('ترتيب الطالب في إتمام المهام لهذا اليوم'),
                Forms\Components\Select::make('verified_by')
                    ->label('تم التحقق من قبل')
                    ->relationship('verifier', 'name')
                    ->searchable()
                    ->preload()
                    ->disabled(fn ($record) => $record?->verified_by !== null),
                Forms\Components\DateTimePicker::make('verified_at')
                    ->label('تاريخ التحقق')
                    ->disabled(fn ($record) => $record?->verified_at !== null),
                Forms\Components\Textarea::make('notes')
                    ->label('ملاحظات')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.name')
                    ->label('اسم الطالب')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('student.class.name')
                    ->label('الفصل')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('log_date')
                    ->label('تاريخ التسليم')
                    ->date('Y-m-d')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('وقت التسليم')
                    ->dateTime('H:i:s')
                    ->sortable(),
                Tables\Columns\IconColumn::make('type')
                    ->label('نوع السجل')
                    ->icon('heroicon-o-document-text')
                    ->tooltip('سجل تسليم مهمة')
                    ->boolean(false)
                    ->default(true), // دائماً سجل مهمة
                Tables\Columns\TextColumn::make('items_count')
                    ->label('عدد المهام')
                    ->counts('items')
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('completed_items_count')
                    ->label('المهام المكتملة')
                    ->badge()
                    ->color('success')
                    ->getStateUsing(function (DailyLog $record): int {
                        return $record->items()->where('status', 'completed')->count();
                    }),
                Tables\Columns\TextColumn::make('total_points')
                    ->label('النقاط المكتسبة')
                    ->numeric()
                    ->badge()
                    ->color('warning')
                    ->getStateUsing(function (DailyLog $record): int {
                        return $record->items()
                            ->where('status', 'completed')
                            ->with('taskDefinition')
                            ->get()
                            ->sum(fn ($item) => $item->taskDefinition?->points_weight ?? 0);
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'verified' => 'success',
                        'submitted' => 'info',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'verified' => 'متحقق',
                        'submitted' => 'تم التسليم',
                        'pending' => 'معلق',
                        'rejected' => 'مرفوض',
                        default => $state,
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('finish_order')
                    ->label('ترتيب الإنجاز')
                    ->numeric()
                    ->badge()
                    ->color(fn ($state) => $state <= 3 ? 'success' : 'gray')
                    ->sortable(),
                Tables\Columns\TextColumn::make('verifier.name')
                    ->label('تم التحقق من قبل')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('verified_at')
                    ->label('تاريخ التحقق')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('تاريخ التحديث')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('student_id')
                    ->label('الطالب')
                    ->relationship('student', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('student.class_id')
                    ->label('الفصل')
                    ->relationship('student.class', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'معلق',
                        'submitted' => 'تم التسليم',
                        'verified' => 'متحقق',
                        'rejected' => 'مرفوض',
                    ]),
                Filter::make('verified')
                    ->label('متحقق')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('verified_at')),
                Filter::make('pending')
                    ->label('معلق - يحتاج تحقق')
                    ->query(fn (Builder $query): Builder => $query->whereNull('verified_at')),
                Filter::make('today')
                    ->label('سجلات اليوم')
                    ->query(fn (Builder $query): Builder => $query->whereDate('log_date', today())),
                Filter::make('this_week')
                    ->label('هذا الأسبوع')
                    ->query(fn (Builder $query): Builder => $query->whereBetween('log_date', [
                        now()->startOfWeek(),
                        now()->endOfWeek()
                    ])),
                Filter::make('this_month')
                    ->label('هذا الشهر')
                    ->query(fn (Builder $query): Builder => $query->whereMonth('log_date', now()->month)
                        ->whereYear('log_date', now()->year)),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('verify')
                    ->label('تحقق')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('تحقق من السجل')
                    ->modalDescription('هل تريد التحقق من هذا السجل اليومي؟')
                    ->action(function (DailyLog $record) {
                        $record->update([
                            'status' => 'verified',
                            'verified_by' => auth()->id(),
                            'verified_at' => now(),
                        ]);
                        
                        Notification::make()
                            ->title('تم التحقق من السجل')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (DailyLog $record): bool => $record->status !== 'verified'),
                Action::make('reopen')
                    ->label('إعادة فتح')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('إعادة فتح السجل')
                    ->modalDescription('هل تريد إعادة فتح هذا السجل اليومي؟')
                    ->action(function (DailyLog $record) {
                        $record->update([
                            'status' => 'pending',
                            'verified_by' => null,
                            'verified_at' => null,
                        ]);
                        
                        Notification::make()
                            ->title('تم إعادة فتح السجل')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (DailyLog $record): bool => $record->status === 'verified'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    BulkAction::make('verify')
                        ->label('تحقق من المحدد')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('تحقق من السجلات المحددة')
                        ->modalDescription('هل تريد التحقق من السجلات المحددة؟')
                        ->action(function (Collection $records) {
                            $records->each(function (DailyLog $record) {
                                $record->update([
                                    'status' => 'verified',
                                    'verified_by' => auth()->id(),
                                    'verified_at' => now(),
                                ]);
                            });
                            
                            Notification::make()
                                ->title("تم التحقق من {$records->count()} سجل")
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('reopen')
                        ->label('إعادة فتح المحدد')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('إعادة فتح السجلات المحددة')
                        ->modalDescription('هل تريد إعادة فتح السجلات المحددة؟')
                        ->action(function (Collection $records) {
                            $records->each(function (DailyLog $record) {
                                $record->update([
                                    'status' => 'pending',
                                    'verified_by' => null,
                                    'verified_at' => null,
                                ]);
                            });
                            
                            Notification::make()
                                ->title("تم إعادة فتح {$records->count()} سجل")
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('log_date', 'desc')
            ->headerActions([
                ExportAction::make()
                    ->label('تصدير')
                    ->icon('heroicon-o-arrow-down-tray'),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['student.class', 'items.taskDefinition', 'verifier']));
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\DailyLogItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDailyLogs::route('/'),
            'create' => Pages\CreateDailyLog::route('/create'),
            'edit' => Pages\EditDailyLog::route('/{record}/edit'),
        ];
    }
}
