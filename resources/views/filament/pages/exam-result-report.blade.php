<x-filament-panels::page>
    <x-filament-panels::form wire:submit="downloadExcel">
        {{ $this->form }}

        <div class="flex justify-start">
            @foreach ($this->getFormActions() as $action)
                {{ $action }}
            @endforeach
        </div>
    </x-filament-panels::form>
</x-filament-panels::page>
