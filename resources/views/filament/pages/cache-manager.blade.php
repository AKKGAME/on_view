<x-filament::page>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        
        {{-- 1. Clear All Section --}}
        <x-filament::section>
            <x-slot name="heading">
                Clear System Cache
            </x-slot>
            <x-slot name="description">
                Use this to clear all application caches. Recommended during development or debugging.
            </x-slot>

            <div class="flex items-center justify-end">
                <x-filament::button wire:click="clearAllCache" color="danger" size="lg" icon="heroicon-m-trash">
                    Clear All Caches
                </x-filament::button>
            </div>
        </x-filament::section>

        {{-- 2. Specific Cache Section --}}
        <x-filament::section>
            <x-slot name="heading">
                Specific Actions
            </x-slot>
            
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <x-filament::button wire:click="clearRoute" color="warning" size="md" outlined>
                    Clear Routes
                </x-filament::button>

                <x-filament::button wire:click="clearConfig" color="warning" size="md" outlined>
                    Clear Config
                </x-filament::button>

                <x-filament::button wire:click="clearView" color="warning" size="md" outlined>
                    Clear Views
                </x-filament::button>
            </div>
        </x-filament::section>

        {{-- 3. Production Optimization --}}
        <x-filament::section>
            <x-slot name="heading">
                Production Optimization
            </x-slot>
            <x-slot name="description">
                Rebuild all caches for better performance. Run this after deployment.
            </x-slot>

            <div class="flex items-center justify-end">
                <x-filament::button wire:click="cacheEverything" color="success" size="lg" icon="heroicon-m-bolt">
                    Optimize App
                </x-filament::button>
            </div>
        </x-filament::section>

    </div>
</x-filament::page>