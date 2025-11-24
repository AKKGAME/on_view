<div class="max-w-5xl mx-auto py-6 px-4 md:px-6 pb-28 md:pb-10">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 md:mb-8 gap-4">
        <div>
            <h1 class="text-2xl md:text-4xl font-bold text-white font-gaming tracking-wide">
                COIN <span class="text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 to-orange-500">SHOP</span>
            </h1>
            <p class="text-slate-400 text-xs md:text-sm mt-1">Top up to unlock premium content.</p>
        </div>
    </div>

    <!-- Ads Banner -->
    <div class="mb-6 md:mb-10 transform hover:-translate-y-1 transition duration-300">
        @livewire('earn-coins')
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8">
        
        <!-- LEFT: Top Up Form -->
        <div class="lg:col-span-2 space-y-6">
            
            <div class="bg-slate-900/80 backdrop-blur-xl p-5 md:p-8 rounded-3xl border border-slate-800 shadow-2xl relative overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-purple-600/10 blur-3xl rounded-full -mr-16 -mt-16 pointer-events-none"></div>

                <h3 class="text-lg md:text-xl font-bold text-white mb-4 md:mb-6 flex items-center gap-2 relative z-10">
                    <div class="bg-purple-500/20 p-1.5 rounded-lg">
                        <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    Deposit Request
                </h3>
                
                <form wire:submit.prevent="submit" class="space-y-5 relative z-10">
                    
                    <!-- Payment Method Selector (Dynamic) -->
                    <div>
                        <label class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-3 block">Select Method</label>
                        <div class="grid grid-cols-2 gap-3 md:gap-4">
                            @forelse($availableMethods as $method)
                                @php
                                    // Color Mapping
                                    $colorMap = [
                                        'blue' => ['border' => 'border-blue-500', 'bg' => 'bg-blue-600/10', 'text' => 'text-blue-400', 'icon' => 'text-blue-500'],
                                        'yellow' => ['border' => 'border-yellow-500', 'bg' => 'bg-yellow-600/10', 'text' => 'text-yellow-400', 'icon' => 'text-yellow-500'],
                                        'red' => ['border' => 'border-red-500', 'bg' => 'bg-red-600/10', 'text' => 'text-red-400', 'icon' => 'text-red-500'],
                                        'green' => ['border' => 'border-green-500', 'bg' => 'bg-green-600/10', 'text' => 'text-green-400', 'icon' => 'text-green-500'],
                                        'purple' => ['border' => 'border-purple-500', 'bg' => 'bg-purple-600/10', 'text' => 'text-purple-400', 'icon' => 'text-purple-500'],
                                    ];
                                    $colors = $colorMap[$method->color_class] ?? $colorMap['blue'];
                                    $isSelected = $payment_method === $method->slug;
                                @endphp

                                <div wire:click="$set('payment_method', '{{ $method->slug }}')" 
                                     class="cursor-pointer relative group p-3 md:p-4 rounded-2xl border-2 transition-all duration-300 flex flex-col items-center gap-2
                                     {{ $isSelected ? $colors['bg'] . ' ' . $colors['border'] : 'bg-slate-950 border-slate-800 hover:border-slate-600' }}">
                                    
                                    <div class="w-full flex justify-between items-center">
                                        <span class="font-bold text-sm md:text-base {{ $isSelected ? $colors['text'] : 'text-slate-300' }}">
                                            {{ $method->name }}
                                        </span>
                                        @if($isSelected) 
                                            <svg class="w-4 h-4 {{ $colors['icon'] }}" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg> 
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-2 text-center text-slate-500 py-4 border border-dashed border-slate-800 rounded-xl">
                                    No payment methods available.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Dynamic Info Box (With Copy Button) -->
                    @if($transfer_account_number)
                        <div class="bg-slate-950/50 p-3 md:p-4 rounded-xl border border-slate-800/50 flex flex-col gap-3" 
                             x-data="{ copied: false }">
                            
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="bg-slate-800 p-1.5 rounded text-slate-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                    <span class="text-slate-400 text-sm">Transfer to:</span>
                                </div>
                                <span class="text-yellow-500 text-xs font-bold bg-yellow-500/10 px-2 py-1 rounded">Rate: 1 MMK = 1 Coin</span>
                            </div>

                            <div class="flex items-center justify-between bg-slate-900 border border-slate-800 rounded-lg p-3">
                                <div>
                                    <p class="text-white font-bold text-lg tracking-wider font-mono">{{ $transfer_account_number }}</p>
                                    <p class="text-xs text-slate-500">{{ $transfer_account_name }}</p>
                                </div>
                                
                                <!-- Copy Button -->
                                <button @click="navigator.clipboard.writeText('{{ $transfer_account_number }}'); copied = true; setTimeout(() => copied = false, 2000)" 
                                        class="p-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 transition flex items-center gap-2">
                                    <span x-show="!copied">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                                    </span>
                                    <span x-show="copied" class="text-green-400 text-xs font-bold flex items-center gap-1" style="display: none;">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        Copied
                                    </span>
                                </button>
                            </div>
                        </div>
                    @endif

                    <!-- Inputs Group -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                        <div>
                            <label class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-2 block">ပမာဏ (ကျပ်)</label>
                            <div class="relative">
                                <span class="absolute left-4 top-3.5 text-slate-500 font-bold text-sm">Ks</span>
                                <input wire:model="amount" type="number" placeholder="1000" inputmode="numeric"
                                    class="w-full bg-slate-950 border border-slate-800 rounded-xl py-3 pl-10 pr-4 text-white focus:border-purple-500 focus:ring-1 focus:ring-purple-500 outline-none transition font-bold text-base md:text-lg">
                            </div>
                            @error('amount') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-2 block">လုပ်ငန်းစဥ်နံပါတ် အနောက် ၆ လုံး</label>
                            <input wire:model="phone_last_digits" type="text" placeholder="123456" maxlength="6" inputmode="numeric"
                                class="w-full bg-slate-950 border border-slate-800 rounded-xl py-3 px-4 text-white focus:border-purple-500 focus:ring-1 focus:ring-purple-500 outline-none transition font-bold tracking-widest text-base md:text-lg">
                            @error('phone_last_digits') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- File Upload -->
                    <div>
                        <label class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-2 block">Screenshot</label>
                        <label class="flex flex-col items-center justify-center w-full h-28 md:h-32 border-2 border-slate-800 border-dashed rounded-2xl cursor-pointer bg-slate-950/50 hover:bg-slate-900 hover:border-purple-500 transition group relative overflow-hidden active:scale-[0.99]">
                            @if($screenshot)
                                <div class="z-20 flex items-center gap-2 text-green-400 bg-slate-900/90 px-3 py-1.5 rounded-full shadow-lg backdrop-blur-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <span class="font-bold text-xs md:text-sm">Image Ready</span>
                                </div>
                                <img src="{{ $screenshot->temporaryUrl() }}" class="absolute inset-0 w-full h-full object-cover opacity-50">
                            @else
                                <div class="flex flex-col items-center justify-center pt-5 pb-6 relative z-10">
                                    <svg class="w-6 h-6 md:w-8 md:h-8 mb-2 text-slate-500 group-hover:text-purple-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                    <p class="mb-1 text-xs md:text-sm text-slate-400"><span class="font-bold text-purple-400">Tap to upload</span> screenshot</p>
                                </div>
                            @endif
                            <input wire:model="screenshot" type="file" class="hidden" accept="image/*" />
                        </label>
                        <div wire:loading wire:target="screenshot" class="w-full mt-2">
                            <div class="text-xs text-purple-400 mb-1 flex items-center gap-2">
                                <svg class="animate-spin h-3 w-3 text-purple-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Uploading...
                            </div>
                        </div>
                        @error('screenshot') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <button type="submit" 
                        class="w-full py-3.5 md:py-4 rounded-xl bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-500 hover:to-blue-500 text-white font-bold text-base md:text-lg shadow-lg shadow-purple-600/20 transform active:scale-95 transition flex items-center justify-center gap-2">
                        <span wire:loading.remove>Confirm Payment</span>
                        <span wire:loading>Processing...</span>
                    </button>

                </form>
            </div>
        </div>

        <!-- RIGHT: History -->
        <div class="space-y-4">
            <h3 class="text-base md:text-lg font-bold text-white font-gaming px-2 flex justify-between items-center">
                <span>Recent History</span>
                <span class="text-xs font-normal text-slate-500 bg-slate-800 px-2 py-1 rounded-full">Last 10</span>
            </h3>
            
            <div class="space-y-3 max-h-[400px] md:max-w-[600px] overflow-y-auto pr-1 custom-scrollbar">
                @foreach($history as $req)
                    <div class="bg-slate-900 p-3 md:p-4 rounded-xl border border-slate-800 hover:border-slate-700 transition flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center
                                {{ $req->status === 'approved' ? 'bg-green-500/10 text-green-500' : ($req->status === 'rejected' ? 'bg-red-500/10 text-red-500' : 'bg-yellow-500/10 text-yellow-500') }}">
                                @if($req->status === 'approved') <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                @elseif($req->status === 'rejected') <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                @else <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                @endif
                            </div>
                            <div>
                                <div class="text-white font-bold text-sm md:text-base">+{{ number_format($req->amount) }}</div>
                                <div class="text-[10px] md:text-xs text-slate-500 uppercase">{{ $req->payment_method }} • {{ $req->created_at->format('M d') }}</div>
                            </div>
                        </div>
                        <div class="text-xs font-bold px-2 py-1 rounded
                            {{ $req->status === 'approved' ? 'text-green-400 bg-green-900/20' : ($req->status === 'rejected' ? 'text-red-400 bg-red-900/20' : 'text-yellow-400 bg-yellow-900/20') }}">
                            {{ ucfirst($req->status) }}
                        </div>
                    </div>
                @endforeach
                
                @if($history->isEmpty())
                    <div class="text-center py-8 bg-slate-900/50 rounded-xl border border-slate-800 border-dashed">
                        <p class="text-slate-500 text-xs">No transaction history.</p>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>