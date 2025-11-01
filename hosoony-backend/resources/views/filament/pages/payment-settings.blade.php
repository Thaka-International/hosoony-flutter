<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Payment Methods Status -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                حالة طرق الدفع المتاحة
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($this->getViewData()['enabled_methods'] as $method => $enabled)
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            @switch($method)
                                @case('paypal')
                                    PayPal
                                    @break
                                @case('fastlane')
                                    Fastlane PayPal
                                    @break
                                @case('bank_transfer')
                                    التحويل البنكي
                                    @break
                                @case('cash')
                                    الدفع النقدي
                                    @break
                            @endswitch
                        </span>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                            {{ $enabled ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                            {{ $enabled ? 'مفعل' : 'معطل' }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Payment Settings Form -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            {{ $this->form }}
        </div>
    </div>
</x-filament-panels::page>


