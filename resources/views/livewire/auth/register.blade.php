<div class="flex min-h-[80vh] items-center justify-center">
    <div class="w-full max-w-md rounded-2xl bg-slate-900/80 p-8 shadow-[0_0_50px_rgba(168,85,247,0.15)] border border-slate-800 backdrop-blur-md">
        <h2 class="mb-2 text-center text-3xl font-bold text-white font-gaming">CREATE ACCOUNT</h2>
        <p class="mb-6 text-center text-slate-400">Join the guild & start earning XP!</p>

        <form wire:submit.prevent="register" class="space-y-4">
            <div>
                <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Player Name</label>
                <input wire:model="name" type="text" class="w-full rounded bg-slate-950 border border-slate-800 p-3 text-white focus:border-purple-500 focus:outline-none transition">
                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Phone Number</label>
                <input wire:model="phone" type="tel" class="w-full rounded bg-slate-950 border border-slate-800 p-3 text-white focus:border-purple-500 focus:outline-none transition">
                @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Referral Code (Optional)</label>
                <input wire:model="referral_code" type="text" placeholder="Enter code if you have one" 
                class="w-full rounded bg-slate-950 border border-slate-800 p-3 text-white focus:border-purple-500 focus:outline-none transition placeholder:text-slate-700 uppercase">
                @error('referral_code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Password</label>
                <input wire:model="password" type="password" class="w-full rounded bg-slate-950 border border-slate-800 p-3 text-white focus:border-purple-500 focus:outline-none transition">
                @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Confirm Password</label>
                <input wire:model="password_confirmation" type="password" class="w-full rounded bg-slate-950 border border-slate-800 p-3 text-white focus:border-purple-500 focus:outline-none transition">
            </div>

            <button type="submit" class="w-full rounded bg-purple-600 py-3 font-bold text-white hover:bg-purple-500 transition shadow-lg shadow-purple-600/20">
                INITIALIZE START
            </button>
        </form>

        <div class="mt-4 text-center text-sm text-slate-500">
            Already have an account? 
            <a href="{{ route('login') }}" class="text-purple-400 hover:text-purple-300">Login</a>
        </div>
    </div>
</div>