<x-filament-panels::page>
    {{-- Back Button (Optional) --}}
    <div class="mb-4">
        <a href="{{ \App\Filament\Resources\AnimeResource::getUrl('seasons', ['record' => $record->id]) }}" 
           class="text-sm text-gray-500 hover:text-primary-500 font-bold">
           ← Back to Seasons
        </a>
    </div>

    {{ $this->table }}
</x-filament-panels::page>