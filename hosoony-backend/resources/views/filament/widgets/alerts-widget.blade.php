<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            تنبيهات النظام
        </x-slot>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- تنبيهات 7 أيام -->
            <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-blue-800 dark:text-blue-200 mb-2">
                    خلال 7 أيام
                </h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">اشتراكات منتهية:</span>
                        <span class="font-semibold text-blue-600 dark:text-blue-400">
                            {{ $this->getViewData()['sevenDayAlerts']['expiringSubscriptions'] }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">مدفوعات مستحقة:</span>
                        <span class="font-semibold text-blue-600 dark:text-blue-400">
                            {{ $this->getViewData()['sevenDayAlerts']['upcomingPayments'] }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- تنبيهات 3 أيام -->
            <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-yellow-800 dark:text-yellow-200 mb-2">
                    خلال 3 أيام
                </h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">اشتراكات منتهية:</span>
                        <span class="font-semibold text-yellow-600 dark:text-yellow-400">
                            {{ $this->getViewData()['threeDayAlerts']['expiringSubscriptions'] }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">مدفوعات مستحقة:</span>
                        <span class="font-semibold text-yellow-600 dark:text-yellow-400">
                            {{ $this->getViewData()['threeDayAlerts']['upcomingPayments'] }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- تنبيهات يوم واحد -->
            <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-red-800 dark:text-red-200 mb-2">
                    خلال يوم واحد
                </h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">اشتراكات منتهية:</span>
                        <span class="font-semibold text-red-600 dark:text-red-400">
                            {{ $this->getViewData()['oneDayAlerts']['expiringSubscriptions'] }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">مدفوعات مستحقة:</span>
                        <span class="font-semibold text-red-600 dark:text-red-400">
                            {{ $this->getViewData()['oneDayAlerts']['upcomingPayments'] }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">جلسات قادمة:</span>
                        <span class="font-semibold text-red-600 dark:text-red-400">
                            {{ $this->getViewData()['oneDayAlerts']['upcomingSessions'] }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>


