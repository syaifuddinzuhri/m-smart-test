<x-filament-panels::page>
    <form class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        {{ $this->form }}
    </form>

    @livewire(\App\Filament\Supervisor\Widgets\ExamMonitorWidget::class, [
        'filters' => $data,
    ])
</x-filament-panels::page>
