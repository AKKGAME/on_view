<div class="flex min-h-[80vh] items-center justify-center">
    <div class="w-full max-w-md rounded-2xl bg-slate-900/80 p-8 shadow-[0_0_50px_rgba(168,85,247,0.15)] border border-slate-800 backdrop-blur-md">
        <h2 class="mb-2 text-center text-3xl font-bold text-white font-gaming">WELCOME BACK</h2>
        <p class="mb-6 text-center text-slate-400">Continue your adventure.</p>

        <form wire:submit.prevent="login" class="space-y-4">
            <div>
                <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Phone Number</label>
                <input wire:model="phone" type="tel" class="w-full rounded bg-slate-950 border border-slate-800 p-3 text-white focus:border-purple-500 focus:outline-none transition">
                @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Password</label>
                <input wire:model="password" type="password" class="w-full rounded bg-slate-950 border border-slate-800 p-3 text-white focus:border-purple-500 focus:outline-none transition">
                @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <button type="submit" class="w-full rounded bg-purple-600 py-3 font-bold text-white hover:bg-purple-500 transition shadow-lg shadow-purple-600/20">
                LOGIN
            </button>
        </form>

        <div class="mt-4 text-center text-sm text-slate-500">
            New Player? 
            <a href="{{ route('register') }}" class="text-purple-400 hover:text-purple-300">Create Account</a>
        </div>
    </div>
</div>