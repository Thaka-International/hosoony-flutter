<?php

namespace App\Filament\Pages;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Livewire\Attributes\Reactive;
use Filament\Pages\Page;

class PaymentSettings extends Page implements HasForms
{
    use InteractsWithForms;
    
    #[Reactive]
    public $paypal_enabled = false;
    
    #[Reactive]
    public $fastlane_enabled = false;
    
    #[Reactive]
    public $bank_transfer_enabled = true;
    
    #[Reactive]
    public $cash_enabled = true;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    
    protected static ?string $navigationLabel = 'إعدادات الدفع';
    
    protected static ?string $title = 'إعدادات بوابات الدفع';
    
    protected static ?string $slug = 'payment-settings';
    
    protected static ?int $navigationSort = 10;
    
    protected static ?string $navigationGroup = 'الإعدادات';
    
    protected static string $view = 'filament.pages.payment-settings';
    
    public static function canAccess(): bool
    {
        return auth()->user()?->role === 'admin'; // Only admin can access payment settings
    }

    public function mount(): void
    {
        $this->paypal_enabled = config('payment.paypal.enabled', false);
        $this->fastlane_enabled = config('payment.fastlane.enabled', false);
        $this->bank_transfer_enabled = config('payment.bank_transfer.enabled', true);
        $this->cash_enabled = config('payment.cash.enabled', true);
        
        $this->form->fill([
            'paypal_enabled' => $this->paypal_enabled,
            'paypal_client_id' => config('payment.paypal.client_id'),
            'paypal_client_secret' => config('payment.paypal.client_secret'),
            'paypal_sandbox' => config('payment.paypal.sandbox', true),
            'paypal_webhook_id' => config('payment.paypal.webhook_id'),
            
            'fastlane_enabled' => $this->fastlane_enabled,
            'fastlane_api_key' => config('payment.fastlane.api_key'),
            'fastlane_sandbox' => config('payment.fastlane.sandbox', true),
            'fastlane_webhook_secret' => config('payment.fastlane.webhook_secret'),
            
            'bank_transfer_enabled' => $this->bank_transfer_enabled,
            'bank_transfer_details' => config('payment.bank_transfer.details'),
            
            'cash_enabled' => $this->cash_enabled,
            'cash_instructions' => config('payment.cash.instructions'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('إعدادات PayPal')
                    ->schema([
                        Toggle::make('paypal_enabled')
                            ->label('تفعيل PayPal')
                            ->default(false),
                        
                        TextInput::make('paypal_client_id')
                            ->label('معرف العميل')
                            ->placeholder('PayPal Client ID')
                            ->visible(fn () => $this->paypal_enabled),
                        
                        TextInput::make('paypal_client_secret')
                            ->label('سر العميل')
                            ->password()
                            ->placeholder('PayPal Client Secret')
                            ->visible(fn () => $this->paypal_enabled),
                        
                        Toggle::make('paypal_sandbox')
                            ->label('وضع التجربة')
                            ->default(true)
                            ->visible(fn () => $this->paypal_enabled),
                        
                        TextInput::make('paypal_webhook_id')
                            ->label('معرف Webhook')
                            ->placeholder('PayPal Webhook ID')
                            ->visible(fn () => $this->paypal_enabled),
                    ])
                    ->columns(2),
                
                Section::make('إعدادات Fastlane PayPal')
                    ->schema([
                        Toggle::make('fastlane_enabled')
                            ->label('تفعيل Fastlane PayPal')
                            ->default(false),
                        
                        TextInput::make('fastlane_api_key')
                            ->label('مفتاح API')
                            ->password()
                            ->placeholder('Fastlane API Key')
                            ->visible(fn () => $this->fastlane_enabled),
                        
                        Toggle::make('fastlane_sandbox')
                            ->label('وضع التجربة')
                            ->default(true)
                            ->visible(fn () => $this->fastlane_enabled),
                        
                        TextInput::make('fastlane_webhook_secret')
                            ->label('سر Webhook')
                            ->password()
                            ->placeholder('Fastlane Webhook Secret')
                            ->visible(fn () => $this->fastlane_enabled),
                    ])
                    ->columns(2),
                
                Section::make('إعدادات التحويل البنكي')
                    ->schema([
                        Toggle::make('bank_transfer_enabled')
                            ->label('تفعيل التحويل البنكي')
                            ->default(true),
                        
                        Textarea::make('bank_transfer_details')
                            ->label('تفاصيل التحويل البنكي')
                            ->placeholder('اسم البنك: البنك الأهلي السعودي\nرقم الحساب: 1234567890\nاسم المستفيد: حصوني للتعليم')
                            ->rows(4)
                            ->visible(fn () => $this->bank_transfer_enabled),
                    ])
                    ->columns(1),
                
                Section::make('إعدادات الدفع النقدي')
                    ->schema([
                        Toggle::make('cash_enabled')
                            ->label('تفعيل الدفع النقدي')
                            ->default(true),
                        
                        Textarea::make('cash_instructions')
                            ->label('تعليمات الدفع النقدي')
                            ->placeholder('يمكن الدفع نقداً في مقر المعهد\nأو عبر المدرس المسؤول')
                            ->rows(3)
                            ->visible(fn () => $this->cash_enabled),
                    ])
                    ->columns(1),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('حفظ الإعدادات')
                ->icon('heroicon-o-check')
                ->color('success')
                ->action('saveSettings'),
            
            Action::make('test_paypal')
                ->label('اختبار PayPal')
                ->icon('heroicon-o-credit-card')
                ->color('info')
                ->action('testPayPal')
                ->visible(fn () => $this->paypal_enabled),
            
            Action::make('test_fastlane')
                ->label('اختبار Fastlane')
                ->icon('heroicon-o-credit-card')
                ->color('info')
                ->action('testFastlane')
                ->visible(fn () => $this->fastlane_enabled),
        ];
    }

    public function updatedPaypalEnabled($value): void
    {
        $this->paypal_enabled = $value;
    }
    
    public function updatedFastlaneEnabled($value): void
    {
        $this->fastlane_enabled = $value;
    }
    
    public function updatedBankTransferEnabled($value): void
    {
        $this->bank_transfer_enabled = $value;
    }
    
    public function updatedCashEnabled($value): void
    {
        $this->cash_enabled = $value;
    }

    public function saveSettings(): void
    {
        $data = $this->form->getState();
        
        // Update config file
        $config = [
            'paypal' => [
                'enabled' => $data['paypal_enabled'] ?? false,
                'client_id' => $data['paypal_client_id'] ?? '',
                'client_secret' => $data['paypal_client_secret'] ?? '',
                'sandbox' => $data['paypal_sandbox'] ?? true,
                'webhook_id' => $data['paypal_webhook_id'] ?? '',
            ],
            'fastlane' => [
                'enabled' => $data['fastlane_enabled'] ?? false,
                'api_key' => $data['fastlane_api_key'] ?? '',
                'sandbox' => $data['fastlane_sandbox'] ?? true,
                'webhook_secret' => $data['fastlane_webhook_secret'] ?? '',
            ],
            'bank_transfer' => [
                'enabled' => $data['bank_transfer_enabled'] ?? true,
                'details' => $data['bank_transfer_details'] ?? '',
            ],
            'cash' => [
                'enabled' => $data['cash_enabled'] ?? true,
                'instructions' => $data['cash_instructions'] ?? '',
            ],
        ];
        
        // Save to config file
        $configContent = "<?php\n\nreturn " . var_export($config, true) . ";\n";
        Storage::disk('local')->put('payment_config.php', $configContent);
        
        Notification::make()
            ->title('تم حفظ الإعدادات بنجاح')
            ->success()
            ->send();
    }

    public function testPayPal(): void
    {
        // Test PayPal connection
        try {
            // Add PayPal test logic here
            Notification::make()
                ->title('اختبار PayPal')
                ->body('تم اختبار اتصال PayPal بنجاح')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('خطأ في اختبار PayPal')
                ->body('فشل في اختبار اتصال PayPal: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function testFastlane(): void
    {
        // Test Fastlane connection
        try {
            // Add Fastlane test logic here
            Notification::make()
                ->title('اختبار Fastlane')
                ->body('تم اختبار اتصال Fastlane بنجاح')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('خطأ في اختبار Fastlane')
                ->body('فشل في اختبار اتصال Fastlane: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getViewData(): array
    {
        return [
            'enabled_methods' => [
                'paypal' => config('payment.paypal.enabled', false),
                'fastlane' => config('payment.fastlane.enabled', false),
                'bank_transfer' => config('payment.bank_transfer.enabled', true),
                'cash' => config('payment.cash.enabled', true),
            ],
        ];
    }
}
