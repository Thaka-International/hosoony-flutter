<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProgramResource\Pages;
use App\Filament\Resources\ProgramResource\RelationManagers;
use App\Models\Program;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProgramResource extends Resource
{
    protected static ?string $model = Program::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    
    protected static ?string $navigationLabel = 'البرامج';
    
    protected static ?string $modelLabel = 'برنامج';
    
    protected static ?string $pluralModelLabel = 'البرامج';
    
    public static function canViewAny(): bool
    {
        return in_array(auth()->user()?->role, ['admin', 'sub_admin']);
    }
    
    public static function canCreate(): bool
    {
        return auth()->user()?->role === 'admin'; // Only admin can create programs
    }
    
    public static function canEdit($record): bool
    {
        return auth()->user()?->role === 'admin'; // Only admin can edit programs
    }
    
    public static function canDelete($record): bool
    {
        return auth()->user()?->role === 'admin'; // Only admin can delete programs
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات البرنامج')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('اسم البرنامج')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('وصف البرنامج')
                            ->rows(4)
                            ->columnSpanFull(),
                        
                        Forms\Components\Select::make('status')
                            ->label('الحالة')
                            ->options([
                                'active' => 'نشط',
                                'inactive' => 'غير نشط',
                                'archived' => 'مؤرشف',
                            ])
                            ->required()
                            ->default('active'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('تفاصيل البرنامج')
                    ->schema([
                        Forms\Components\TextInput::make('duration_months')
                            ->label('المدة (بالأشهر)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(60),
                        
                        Forms\Components\TextInput::make('price')
                            ->label('السعر')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01),
                        
                        Forms\Components\Select::make('currency')
                            ->label('العملة')
                            ->options([
                                'SAR' => 'ريال سعودي',
                                'USD' => 'دولار أمريكي',
                                'EUR' => 'يورو',
                            ])
                            ->required()
                            ->default('SAR'),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم البرنامج')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'نشط',
                        'inactive' => 'غير نشط',
                        'archived' => 'مؤرشف',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'warning',
                        'archived' => 'gray',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('duration_months')
                    ->label('المدة (شهر)')
                    ->numeric()
                    ->sortable()
                    ->placeholder('غير محدد'),
                
                Tables\Columns\TextColumn::make('price')
                    ->label('السعر')
                    ->money('SAR')
                    ->sortable()
                    ->placeholder('غير محدد'),
                
                Tables\Columns\TextColumn::make('currency')
                    ->label('العملة')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'SAR' => 'ريال سعودي',
                        'USD' => 'دولار أمريكي',
                        'EUR' => 'يورو',
                        default => $state,
                    }),
                
                Tables\Columns\TextColumn::make('classes_count')
                    ->label('عدد الفصول')
                    ->counts('classes')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('total_students')
                    ->label('إجمالي الطلاب')
                    ->getStateUsing(fn (Program $record): int => $record->total_students)
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'active' => 'نشط',
                        'inactive' => 'غير نشط',
                        'archived' => 'مؤرشف',
                    ]),
                
                SelectFilter::make('currency')
                    ->label('العملة')
                    ->options([
                        'SAR' => 'ريال سعودي',
                        'USD' => 'دولار أمريكي',
                        'EUR' => 'يورو',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListPrograms::route('/'),
            'create' => Pages\CreateProgram::route('/create'),
            'view' => Pages\ViewProgram::route('/{record}'),
            'edit' => Pages\EditProgram::route('/{record}/edit'),
        ];
    }
}
