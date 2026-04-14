<div wire:poll.1s class="flex items-center gap-2 bg-gray-50 px-3 py-1.5 rounded-full border border-gray-200">
    <span class="relative flex h-2 w-2">
        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
        <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
    </span>
    <span class="text-[10px] font-mono font-bold text-gray-500 uppercase tracking-wider">
        {{ now()->format('H:i:s') }} WIB
    </span>
</div>
