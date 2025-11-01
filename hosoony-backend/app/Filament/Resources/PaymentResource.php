<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Resources\PaymentResource\RelationManagers;
use App\Models\Payment;
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
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'المدفوعات';
    protected static ?string $modelLabel = 'اسم المورد';
    protected static ?string $pluralModelLabel = 'أسماء الموارد';
    
    public static function canViewAny(): bool
    {
        return auth()->user()?->role === 'admin'; // Only admin can view payments
    }
    
    public static function canCreate(): bool
    {
        return auth()->user()?->role === 'admin';
    }
    
    public static function canEdit($record): bool
    {
        return auth()->user()?->role === 'admin';
    }
    
    public static function canDelete($record): bool
    {
        return auth()->user()?->role === 'admin';
    }
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('student_id')
                    ->relationship('student', 'name')
                    ->required(),
                Forms\Components\Select::make('subscription_id')
                    ->relationship('subscription', 'id'),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('currency')
                    ->required(),
                Forms\Components\TextInput::make('payment_method')
                    ->required(),
                Forms\Components\TextInput::make('transaction_id'),
                Forms\Components\TextInput::make('status')
                    ->required(),
                Forms\Components\DatePicker::make('due_date')
                    ->required(),
                Forms\Components\DatePicker::make('paid_date'),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subscription.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('currency')
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->searchable(),
                Tables\Columns\TextColumn::make('transaction_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid_date')
                    ->date()
                    ->sortable(),
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
                SelectFilter::make('student_id')
                    ->label('الطالب')
                    ->relationship('student', 'name'),
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'معلق',
                        'completed' => 'مكتمل',
                        'failed' => 'فشل',
                        'refunded' => 'مسترد',
                    ]),
                SelectFilter::make('payment_method')
                    ->label('طريقة الدفع')
                    ->options([
                        'cash' => 'نقدي',
                        'bank_transfer' => 'تحويل بنكي',
                        'credit_card' => 'بطاقة ائتمان',
                        'online' => 'أونلاين',
                    ]),
                Filter::make('overdue')
                    ->label('متأخرة')
                    ->query(fn (Builder $query): Builder => $query->where('due_date', '<', now())->where('status', 'pending')),
                Filter::make('this_month')
                    ->label('هذا الشهر')
                    ->query(fn (Builder $query): Builder => $query->whereMonth('created_at', now()->month)),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('mark_completed')
                    ->label('تأكيد الدفع')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('تأكيد الدفع')
                    ->modalDescription('هل تريد تأكيد هذا الدفع؟')
                    ->action(function (Payment $record) {
                        $record->update([
                            'status' => 'completed',
                            'paid_date' => now(),
                        ]);
                        
                        Notification::make()
                            ->title('تم تأكيد الدفع')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Payment $record): bool => $record->status === 'pending'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    BulkAction::make('mark_completed')
                        ->label('تأكيد المحدد')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('تأكيد المدفوعات المحددة')
                        ->modalDescription('هل تريد تأكيد المدفوعات المحددة؟')
                        ->action(function (Collection $records) {
                            $records->each(function (Payment $record) {
                                if ($record->status === 'pending') {
                                    $record->update([
                                        'status' => 'completed',
                                        'paid_date' => now(),
                                    ]);
                                }
                            });
                            
                            Notification::make()
                                ->title("تم تأكيد {$records->count()} دفعة")
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
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
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
