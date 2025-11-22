<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>{{ $title ?? 'OnView Anime' }}</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Outfit', sans-serif; }
        .font-gaming { font-family: 'Rajdhani', sans-serif; }
        
        html { scroll-behavior: smooth; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #05050A; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }
        
        .safe-area-bottom { padding-bottom: env(safe-area-inset-bottom); }
        .safe-area-top { padding-top: env(safe-area-inset-top); }
    </style>
</head>
<body class="bg-[#05050A] text-white antialiased selection:bg-purple-500 selection:text-white overflow-x-hidden">

    <aside class="hidden md:flex flex-col w-[80px] fixed left-0 top-0 h-full bg-[#05050A]/90 border-r border-white/5 backdrop-blur-xl z-50 items-center py-8 justify-between">
        
        <a href="{{ route('home') }}" class="group relative h-10 w-10 rounded-xl bg-gradient-to-br from-purple-600 to-indigo-600 shadow-[0_0_15px_rgba(124,58,237,0.4)] flex items-center justify-center text-white font-bold text-xs tracking-wider hover:scale-110 transition duration-300">
            OV
        </a>

        <nav class="flex flex-col gap-6 w-full items-center">
            <a href="{{ route('home') }}" class="group relative flex items-center justify-center w-12 h-12 rounded-xl transition-all duration-300 {{ request()->routeIs('home') ? 'text-white bg-white/5 border border-white/10 shadow-[inset_0_0_10px_rgba(255,255,255,0.05)]' : 'text-slate-500 hover:text-white hover:bg-white/5' }}">
                <svg class="w-6 h-6 transition-transform duration-300 group-hover:scale-110 {{ request()->routeIs('home') ? 'drop-shadow-[0_0_5px_rgba(168,85,247,0.8)]' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                @if(request()->routeIs('home')) <div class="absolute -left-[1px] top-1/2 -translate-y-1/2 h-5 w-[3px] bg-purple-500 rounded-r-full shadow-[0_0_10px_#a855f7]"></div> @endif
                <span class="absolute left-14 bg-slate-900 border border-white/10 text-white text-xs font-bold px-3 py-1.5 rounded-lg opacity-0 group-hover:opacity-100 transition-all duration-300 translate-x-[-10px] group-hover:translate-x-0 pointer-events-none whitespace-nowrap z-50 shadow-xl">Home</span>
            </a>

            <a href="{{ route('explore') }}" class="group relative flex items-center justify-center w-12 h-12 rounded-xl transition-all duration-300 {{ request()->routeIs('explore') ? 'text-white bg-white/5 border border-white/10 shadow-[inset_0_0_10px_rgba(255,255,255,0.05)]' : 'text-slate-500 hover:text-white hover:bg-white/5' }}">
                <svg class="w-6 h-6 transition-transform duration-300 group-hover:scale-110 {{ request()->routeIs('explore') ? 'drop-shadow-[0_0_5px_rgba(59,130,246,0.8)]' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                @if(request()->routeIs('explore')) <div class="absolute -left-[1px] top-1/2 -translate-y-1/2 h-5 w-[3px] bg-blue-500 rounded-r-full shadow-[0_0_10px_#3b82f6]"></div> @endif
                <span class="absolute left-14 bg-slate-900 border border-white/10 text-white text-xs font-bold px-3 py-1.5 rounded-lg opacity-0 group-hover:opacity-100 transition-all duration-300 translate-x-[-10px] group-hover:translate-x-0 pointer-events-none whitespace-nowrap z-50 shadow-xl">Explore</span>
            </a>

            <a href="{{ route('leaderboard') }}" class="group relative flex items-center justify-center w-12 h-12 rounded-xl transition-all duration-300 {{ request()->routeIs('leaderboard') ? 'text-white bg-white/5 border border-white/10 shadow-[inset_0_0_10px_rgba(255,255,255,0.05)]' : 'text-slate-500 hover:text-white hover:bg-white/5' }}">
                <svg class="w-6 h-6 transition-transform duration-300 group-hover:scale-110 {{ request()->routeIs('leaderboard') ? 'drop-shadow-[0_0_5px_rgba(234,179,8,0.8)]' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                @if(request()->routeIs('leaderboard')) <div class="absolute -left-[1px] top-1/2 -translate-y-1/2 h-5 w-[3px] bg-yellow-500 rounded-r-full shadow-[0_0_10px_#eab308]"></div> @endif
                <span class="absolute left-14 bg-slate-900 border border-white/10 text-white text-xs font-bold px-3 py-1.5 rounded-lg opacity-0 group-hover:opacity-100 transition-all duration-300 translate-x-[-10px] group-hover:translate-x-0 pointer-events-none whitespace-nowrap z-50 shadow-xl">Ranking</span>
            </a>

            <a href="{{ route('topup') }}" class="group relative flex items-center justify-center w-12 h-12 rounded-xl transition-all duration-300 {{ request()->routeIs('topup') ? 'text-white bg-white/5 border border-white/10 shadow-[inset_0_0_10px_rgba(255,255,255,0.05)]' : 'text-slate-500 hover:text-white hover:bg-white/5' }}">
                <svg class="w-6 h-6 transition-transform duration-300 group-hover:scale-110 {{ request()->routeIs('topup') ? 'drop-shadow-[0_0_5px_rgba(236,72,153,0.8)]' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                @if(request()->routeIs('topup')) <div class="absolute -left-[1px] top-1/2 -translate-y-1/2 h-5 w-[3px] bg-pink-500 rounded-r-full shadow-[0_0_10px_#ec4899]"></div> @endif
                <span class="absolute left-14 bg-slate-900 border border-white/10 text-white text-xs font-bold px-3 py-1.5 rounded-lg opacity-0 group-hover:opacity-100 transition-all duration-300 translate-x-[-10px] group-hover:translate-x-0 pointer-events-none whitespace-nowrap z-50 shadow-xl">Coin Shop</span>
            </a>

            @auth
                <a href="{{ route('profile') }}" class="group relative flex items-center justify-center w-12 h-12 rounded-xl transition-all duration-300 {{ request()->routeIs('profile') ? 'text-white bg-white/5 border border-white/10 shadow-[inset_0_0_10px_rgba(255,255,255,0.05)]' : 'text-slate-500 hover:text-white hover:bg-white/5' }}">
                    <svg class="w-6 h-6 transition-transform duration-300 group-hover:scale-110 {{ request()->routeIs('profile') ? 'drop-shadow-[0_0_5px_rgba(34,197,94,0.8)]' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    @if(request()->routeIs('profile')) <div class="absolute -left-[1px] top-1/2 -translate-y-1/2 h-5 w-[3px] bg-green-500 rounded-r-full shadow-[0_0_10px_#22c55e]"></div> @endif
                    <span class="absolute left-14 bg-slate-900 border border-white/10 text-white text-xs font-bold px-3 py-1.5 rounded-lg opacity-0 group-hover:opacity-100 transition-all duration-300 translate-x-[-10px] group-hover:translate-x-0 pointer-events-none whitespace-nowrap z-50 shadow-xl">Profile</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="group relative flex items-center justify-center w-12 h-12 rounded-xl transition-all duration-300 text-slate-500 hover:text-white hover:bg-white/5">
                    <svg class="w-6 h-6 transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                    <span class="absolute left-14 bg-slate-900 border border-white/10 text-white text-xs font-bold px-3 py-1.5 rounded-lg opacity-0 group-hover:opacity-100 transition-all duration-300 translate-x-[-10px] group-hover:translate-x-0 pointer-events-none whitespace-nowrap z-50 shadow-xl">Login</span>
                </a>
            @endauth
        </nav>

        @auth
            <div class="h-10 w-10 rounded-full bg-slate-800/50 border border-white/10 flex items-center justify-center text-xs font-bold text-slate-300 relative">
                {{ substr(auth()->user()->name, 0, 1) }}
                <div class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-500 rounded-full border-2 border-[#05050A]"></div>
            </div>
        @endauth
    </aside>


    <header class="hidden md:flex fixed top-0 right-0 left-[80px] h-20 bg-[#05050A]/90 backdrop-blur-xl border-b border-white/5 z-40 items-center justify-between px-8 transition-all duration-300">
        
        <h1 class="text-xl font-bold text-white font-gaming tracking-wide">
            @if(request()->routeIs('home')) Dashboard
            @elseif(request()->routeIs('explore')) Explore Anime
            @elseif(request()->routeIs('topup')) Coin Shop
            @elseif(request()->routeIs('request')) Request Anime
            @elseif(request()->routeIs('leaderboard')) Global Ranking
            @elseif(request()->routeIs('profile')) My Profile
            @else OnView Anime @endif
        </h1>

        @auth
        <div class="flex items-center gap-4 bg-white/5 border border-white/10 rounded-xl p-1.5 pr-2">
            
            <div class="pr-2 border-r border-white/10">
                @livewire('user-notifications')
            </div>

            <div class="flex flex-col items-end w-28 px-2 border-r border-white/10">
                <div class="flex justify-between w-full text-[10px] font-bold uppercase text-slate-400 mb-1">
                    <span>Lvl {{ floor(auth()->user()->xp / 1000) + 1 }}</span>
                    <span>{{ (auth()->user()->xp % 1000) / 10 }}%</span>
                </div>
                <div class="h-1.5 w-full bg-slate-800 rounded-full overflow-hidden">
                    <div class="h-full bg-blue-500 rounded-full shadow-[0_0_10px_rgba(59,130,246,0.5)]" style="width: {{ (auth()->user()->xp % 1000) / 10 }}%"></div>
                </div>
            </div>

            <a href="{{ route('topup') }}" class="flex items-center gap-3 group hover:bg-white/5 rounded-lg px-2 py-1 transition cursor-pointer">
                <div class="w-8 h-8 rounded-full bg-yellow-500/10 flex items-center justify-center text-yellow-500 border border-yellow-500/20 shadow-[0_0_15px_rgba(234,179,8,0.2)]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.736 6.979C9.208 6.193 9.996 6 10 6c.002 0 .788.207 1.252.993C11.717 7.76 12 8.852 12 10c0 1.148-.283 2.24-.748 3.027C10.788 13.793 10.002 14 10 14c-.004 0-.792-.193-1.264-.973C8.283 12.24 8 11.148 8 10c0-1.148.283-2.24.736-3.021z" clip-rule="evenodd" /></svg>
                </div>
                <div class="flex flex-col">
                    <span class="text-[10px] text-slate-400 font-bold uppercase leading-none">Balance</span>
                    <span class="text-sm font-bold text-white font-gaming leading-tight">{{ number_format(auth()->user()->coins) }}</span>
                </div>
                <div class="bg-purple-600 h-5 w-5 rounded flex items-center justify-center text-white text-[10px] font-bold ml-1 shadow-lg group-hover:bg-purple-500 transition">
                    +
                </div>
            </a>

        </div>
        @else
        <a href="{{ route('login') }}" class="px-6 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white text-sm font-bold transition shadow-lg shadow-purple-900/20">
            Login
        </a>
        @endauth
    </header>


    <div class="md:hidden fixed top-0 w-full h-16 bg-[#05050A]/90 backdrop-blur-xl border-b border-white/5 z-40 px-4 flex items-center justify-between safe-area-top">
        
        <a href="{{ route('home') }}" class="flex items-center gap-2">
            <div class="h-8 w-8 rounded-lg bg-gradient-to-br from-purple-600 to-indigo-600 flex items-center justify-center text-[10px] font-bold text-white shadow-lg shadow-purple-500/20">OV</div>
            <span class="text-lg font-bold text-white font-gaming tracking-wide">On<span class="text-purple-500">View</span></span>
        </a>

        <div class="flex items-center gap-3">
            
            @auth
                
                <a href="{{ route('topup') }}" class="flex items-center gap-2 bg-white/5 border border-white/10 px-2 py-1 rounded-lg backdrop-blur-md active:scale-95 transition">
                    <div class="w-6 h-6 rounded-full bg-yellow-500/10 flex items-center justify-center text-yellow-500 border border-yellow-500/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.736 6.979C9.208 6.193 9.996 6 10 6c.002 0 .788.207 1.252.993C11.717 7.76 12 8.852 12 10c0 1.148-.283 2.24-.748 3.027C10.788 13.793 10.002 14 10 14c-.004 0-.792-.193-1.264-.973C8.283 12.24 8 11.148 8 10c0-1.148.283-2.24.736-3.021z" clip-rule="evenodd" /></svg>
                    </div>
                    <span class="font-bold text-white text-xs font-gaming mr-1">{{ number_format(auth()->user()->coins) }}</span>
                    <div class="bg-purple-600 h-4 w-4 rounded flex items-center justify-center text-[8px] text-white font-bold shadow-lg">+</div>
                </a>

                <a href="{{ route('request') }}" class="relative p-2 rounded-xl hover:bg-white/10 transition group">
                    <svg class="w-6 h-6 text-slate-400 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                </a>

                @livewire('user-notifications')

            @else
                <a href="{{ route('login') }}" class="px-4 py-1.5 rounded-full bg-purple-600/20 text-purple-400 border border-purple-500/20 text-xs font-bold hover:bg-purple-600 hover:text-white transition">LOGIN</a>
            @endauth

        </div>
    </div>


    <nav class="md:hidden fixed bottom-0 w-full bg-[#05050A]/90 backdrop-blur-2xl border-t border-white/5 z-50 pb-safe safe-area-bottom">
        <div class="grid grid-cols-5 h-[70px] items-center px-1">
            
            <a href="{{ route('home') }}" class="relative flex flex-col items-center gap-1.5 transition-all duration-300 group {{ request()->routeIs('home') ? 'text-white' : 'text-slate-500 hover:text-slate-300' }}">
                @if(request()->routeIs('home')) <div class="absolute -top-3 w-8 h-1 bg-purple-500 rounded-b-full shadow-[0_0_10px_rgba(168,85,247,0.8)]"></div> @endif
                <svg class="w-6 h-6 transition-transform duration-300 group-active:scale-90 {{ request()->routeIs('home') ? 'drop-shadow-[0_0_8px_rgba(168,85,247,0.6)]' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span class="text-[9px] font-bold tracking-wide">Home</span>
            </a>

            <a href="{{ route('explore') }}" class="relative flex flex-col items-center gap-1.5 transition-all duration-300 group {{ request()->routeIs('explore') ? 'text-white' : 'text-slate-500 hover:text-slate-300' }}">
                @if(request()->routeIs('explore')) <div class="absolute -top-3 w-8 h-1 bg-blue-500 rounded-b-full shadow-[0_0_10px_rgba(59,130,246,0.8)]"></div> @endif
                <svg class="w-6 h-6 transition-transform duration-300 group-active:scale-90 {{ request()->routeIs('explore') ? 'drop-shadow-[0_0_8px_rgba(59,130,246,0.6)]' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <span class="text-[9px] font-bold tracking-wide">Search</span>
            </a>

             <a href="{{ route('leaderboard') }}" class="relative flex flex-col items-center gap-1.5 transition-all duration-300 group {{ request()->routeIs('leaderboard') ? 'text-white' : 'text-slate-500 hover:text-slate-300' }}">
                @if(request()->routeIs('leaderboard')) <div class="absolute -top-3 w-8 h-1 bg-yellow-500 rounded-b-full shadow-[0_0_10px_rgba(234,179,8,0.8)]"></div> @endif
                <svg class="w-6 h-6 transition-transform duration-300 group-active:scale-90 {{ request()->routeIs('leaderboard') ? 'drop-shadow-[0_0_8px_rgba(234,179,8,0.6)]' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                <span class="text-[9px] font-bold tracking-wide">Rank</span>
            </a>

            <a href="{{ route('topup') }}" class="relative flex flex-col items-center gap-1.5 transition-all duration-300 group {{ request()->routeIs('topup') ? 'text-white' : 'text-slate-500 hover:text-slate-300' }}">
                @if(request()->routeIs('topup')) <div class="absolute -top-3 w-8 h-1 bg-pink-500 rounded-b-full shadow-[0_0_10px_rgba(236,72,153,0.8)]"></div> @endif
                <svg class="w-6 h-6 transition-transform duration-300 group-active:scale-90 {{ request()->routeIs('topup') ? 'drop-shadow-[0_0_8px_rgba(236,72,153,0.6)]' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="text-[9px] font-bold tracking-wide">Shop</span>
            </a>

            @auth
                <a href="{{ route('profile') }}" class="relative flex flex-col items-center gap-1.5 transition-all duration-300 group {{ request()->routeIs('profile') ? 'text-white' : 'text-slate-500 hover:text-slate-300' }}">
                    @if(request()->routeIs('profile')) <div class="absolute -top-3 w-8 h-1 bg-green-500 rounded-b-full shadow-[0_0_10px_rgba(34,197,94,0.8)]"></div> @endif
                    <svg class="w-6 h-6 transition-transform duration-300 group-active:scale-90 {{ request()->routeIs('profile') ? 'drop-shadow-[0_0_8px_rgba(34,197,94,0.6)]' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    <span class="text-[9px] font-bold tracking-wide">Me</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="relative flex flex-col items-center gap-1.5 transition-all duration-300 group text-slate-500 hover:text-slate-300">
                    <svg class="w-6 h-6 transition-transform duration-300 group-active:scale-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                    <span class="text-[9px] font-bold tracking-wide">Login</span>
                </a>
            @endauth

        </div>
    </nav>


    <main class="md:pl-[80px] pt-20 md:pt-24 pb-24 min-h-screen relative overflow-hidden">
        
        <div class="fixed top-0 left-0 w-full h-full overflow-hidden -z-10 pointer-events-none">
            <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-purple-900/10 rounded-full blur-[100px]"></div>
            <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-blue-900/10 rounded-full blur-[100px]"></div>
            <div class="absolute inset-0 opacity-[0.03]" style="background-image: linear-gradient(#fff 1px, transparent 1px), linear-gradient(90deg, #fff 1px, transparent 1px); background-size: 40px 40px;"></div>
        </div>

        <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 md:mt-8">
            {{ $slot }}
        </div>
    </main>

    <div x-data="{ 
            notifications: [],
            add(e) {
                this.notifications.push({
                    id: Date.now(),
                    type: e.detail.type || 'info',
                    title: e.detail.title,
                    message: e.detail.message,
                    timeout: e.detail.timeout || 3000
                });
            },
            remove(id) {
                this.notifications = this.notifications.filter(n => n.id !== id);
            }
        }"
        @notify.window="add($event)"
        class="fixed z-[100] flex flex-col gap-3 pointer-events-none
               top-20 left-4 right-4 md:left-auto md:right-4 md:top-24 md:w-full md:max-w-sm">

        <template x-for="notification in notifications" :key="notification.id">
            <div x-data="{ show: false }"
                 x-init="
                    setTimeout(() => { show = true }, 50);
                    setTimeout(() => { show = false; setTimeout(() => remove(notification.id), 300) }, notification.timeout);
                 "
                 x-show="show"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 -translate-y-4 md:translate-x-full md:translate-y-0"
                 x-transition:enter-end="opacity-100 translate-y-0 md:translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 md:translate-x-0"
                 x-transition:leave-end="opacity-0 -translate-y-4 md:translate-x-full md:translate-y-0"
                 class="pointer-events-auto relative overflow-hidden rounded-xl border bg-[#0B0E14]/95 backdrop-blur-xl p-4 shadow-2xl pr-10 flex items-start gap-3"
                 :class="{
                    'border-green-500/50 shadow-[0_0_15px_rgba(34,197,94,0.2)]': notification.type === 'success',
                    'border-red-500/50 shadow-[0_0_15px_rgba(239,68,68,0.2)]': notification.type === 'error',
                    'border-yellow-500/50 shadow-[0_0_15px_rgba(234,179,8,0.2)]': notification.type === 'warning',
                    'border-blue-500/50 shadow-[0_0_15px_rgba(59,130,246,0.2)]': notification.type === 'info'
                 }">
                
                <div class="absolute top-0 left-0 w-1 h-full"
                     :class="{
                        'bg-green-500': notification.type === 'success',
                        'bg-red-500': notification.type === 'error',
                        'bg-yellow-500': notification.type === 'warning',
                        'bg-blue-500': notification.type === 'info'
                     }"></div>

                <div class="shrink-0">
                    <div class="h-9 w-9 md:h-10 md:w-10 rounded-full flex items-center justify-center bg-slate-800 border border-slate-700">
                        <template x-if="notification.type === 'success'">
                            <svg class="w-4 h-4 md:w-5 md:h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </template>
                        <template x-if="notification.type === 'error'">
                            <svg class="w-4 h-4 md:w-5 md:h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </template>
                        <template x-if="notification.type === 'warning'">
                            <svg class="w-4 h-4 md:w-5 md:h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </template>
                        <template x-if="notification.type === 'info'">
                            <svg class="w-4 h-4 md:w-5 md:h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </template>
                    </div>
                </div>

                <div class="min-w-0 flex-1">
                    <h4 class="font-bold text-white font-gaming tracking-wide text-sm md:text-base truncate" x-text="notification.title"></h4>
                    <p class="text-xs md:text-sm text-slate-400 mt-0.5 break-words leading-snug" x-text="notification.message"></p>
                </div>

                <button @click="show = false; setTimeout(() => remove(notification.id), 300)" class="absolute top-2 right-2 text-slate-500 hover:text-white p-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </template>
    </div>

    <div x-data="{ openOracle: false }" 
         x-on:keydown.escape.window="openOracle = false"
         class="relative z-50">

        <button 
            @click="openOracle = true" 
            class="fixed bottom-24 right-4 md:bottom-8 md:right-8 z-40 group"
            aria-label="Summon Oracle"
        >
            <div class="absolute inset-0 bg-purple-600 rounded-full opacity-20 group-hover:opacity-40 animate-ping"></div>
            
            <div class="relative w-14 h-14 md:w-16 md:h-16 bg-slate-900/90 backdrop-blur-md border border-purple-500/50 rounded-full flex items-center justify-center shadow-[0_0_30px_rgba(168,85,247,0.5)] group-hover:scale-110 group-hover:shadow-[0_0_50px_rgba(168,85,247,0.8)] transition-all duration-300 ease-out overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-tr from-purple-900/50 via-blue-900/30 to-transparent"></div>
                <span class="text-2xl md:text-3xl relative z-10 animate-float">🧙‍♂️</span>
            </div>
        </button>

        <template x-teleport="body">
            <div x-show="openOracle" 
                 style="display: none;"
                 class="fixed inset-0 z-[100] flex items-end sm:items-center justify-center sm:p-6"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">

                <div @click="openOracle = false" class="absolute inset-0 bg-slate-950/90 backdrop-blur-sm transition-opacity"></div>

                <div class="relative w-full h-[100dvh] sm:h-[85vh] sm:max-w-4xl bg-slate-900 sm:rounded-3xl border-t sm:border border-purple-500/30 shadow-2xl overflow-hidden flex flex-col"
                     x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-500"
                     x-transition:enter-start="translate-y-full sm:translate-y-10 sm:scale-95 opacity-0"
                     x-transition:enter-end="translate-y-0 sm:translate-y-0 sm:scale-100 opacity-100"
                     x-transition:leave="transition cubic-bezier(0.16, 1, 0.3, 1) duration-300"
                     x-transition:leave-start="translate-y-0 sm:translate-y-0 sm:scale-100 opacity-100"
                     x-transition:leave-end="translate-y-full sm:translate-y-10 sm:scale-95 opacity-0">

                    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-800/60 bg-slate-900/50 backdrop-blur-md z-20 shrink-0">
                        <h2 class="text-lg font-bold text-white tracking-wider flex items-center gap-2">
                            <span class="text-xl">🧙‍♂️</span> THE ORACLE
                        </h2>
                        
                        <button @click="openOracle = false" class="p-2 rounded-full text-slate-400 hover:bg-white/10 hover:text-white transition">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <div class="flex-1 min-h-0 overflow-hidden relative bg-slate-950">
                         <livewire:oracle />
                    </div>

                </div>
            </div>
        </template>
    </div>

</body>
</html>