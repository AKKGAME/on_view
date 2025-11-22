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
    class="fixed top-20 right-4 z-[100] flex flex-col gap-3 w-full max-w-sm pointer-events-none">

    <template x-for="notification in notifications" :key="notification.id">
        <div x-data="{ show: false, timeout: null }"
             x-init="
                setTimeout(() => { show = true }, 50);
                timeout = setTimeout(() => { show = false; setTimeout(() => remove(notification.id), 300) }, notification.timeout);
             "
             x-show="show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="translate-x-full opacity-0 scale-90"
             x-transition:enter-end="translate-x-0 opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="translate-x-0 opacity-100 scale-100"
             x-transition:leave-end="translate-x-full opacity-0 scale-90"
             class="pointer-events-auto relative overflow-hidden rounded-xl border bg-[#0B0E14]/90 backdrop-blur-xl p-4 shadow-2xl pr-10"
             :class="{
                'border-green-500/50 shadow-green-500/20': notification.type === 'success',
                'border-red-500/50 shadow-red-500/20': notification.type === 'error',
                'border-yellow-500/50 shadow-yellow-500/20': notification.type === 'warning',
                'border-blue-500/50 shadow-blue-500/20': notification.type === 'info'
             }">
            
            <div class="absolute top-0 left-0 w-1 h-full"
                 :class="{
                    'bg-green-500': notification.type === 'success',
                    'bg-red-500': notification.type === 'error',
                    'bg-yellow-500': notification.type === 'warning',
                    'bg-blue-500': notification.type === 'info'
                 }"></div>

            <div class="flex gap-3">
                <div class="shrink-0">
                    <div class="h-10 w-10 rounded-full flex items-center justify-center bg-slate-800 border border-slate-700">
                        <template x-if="notification.type === 'success'">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </template>
                        <template x-if="notification.type === 'error'">
                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </template>
                        <template x-if="notification.type === 'warning'">
                            <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </template>
                        <template x-if="notification.type === 'info'">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </template>
                    </div>
                </div>

                <div>
                    <h4 class="font-bold text-white font-gaming tracking-wide" x-text="notification.title"></h4>
                    <p class="text-sm text-slate-400 mt-0.5" x-text="notification.message"></p>
                </div>
            </div>

            <button @click="show = false; setTimeout(() => remove(notification.id), 300)" class="absolute top-2 right-2 text-slate-500 hover:text-white transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>

            <div class="absolute bottom-0 left-0 h-1 w-full bg-slate-800">
                <div class="h-full w-full origin-left animate-[progress_3s_linear_forwards]"
                     :style="'animation-duration: ' + notification.timeout + 'ms'"
                     :class="{
                        'bg-green-500': notification.type === 'success',
                        'bg-red-500': notification.type === 'error',
                        'bg-yellow-500': notification.type === 'warning',
                        'bg-blue-500': notification.type === 'info'
                     }"></div>
            </div>
        </div>
    </template>
    
    <style>
        @keyframes progress { from { transform: scaleX(1); } to { transform: scaleX(0); } }
    </style>
</div>