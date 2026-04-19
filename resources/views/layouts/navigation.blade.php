<nav x-data="{ open: false }" class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <!-- Logo -->
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 mr-8 shrink-0">
                    <div class="w-9 h-9 bg-brand-800 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <div class="leading-tight">
                        <span class="text-sm font-bold text-gray-900 block">CLT Layup</span>
                        <span class="text-[10px] font-bold uppercase tracking-[0.15em] text-brand-700">Manager</span>
                    </div>
                </a>

                <!-- Navigation Links -->
                <div class="hidden sm:flex items-center h-full gap-0.5">
                    <a href="{{ route('dashboard') }}" class="relative h-full flex items-center px-3 text-sm font-medium transition-colors {{ request()->routeIs('dashboard') ? 'text-brand-800' : 'text-gray-500 hover:text-gray-900' }}">
                        Overview
                        @if(request()->routeIs('dashboard'))
                            <span class="absolute bottom-0 left-3 right-3 h-0.5 bg-brand-800 rounded-full"></span>
                        @endif
                    </a>
                    <a href="{{ route('suppliers.index') }}" class="relative h-full flex items-center px-3 text-sm font-medium transition-colors {{ request()->routeIs('suppliers.*') ? 'text-brand-800' : 'text-gray-500 hover:text-gray-900' }}">
                        Suppliers
                        @if(request()->routeIs('suppliers.*'))
                            <span class="absolute bottom-0 left-3 right-3 h-0.5 bg-brand-800 rounded-full"></span>
                        @endif
                    </a>
                </div>
            </div>

            <!-- Right side -->
            <div class="hidden sm:flex sm:items-center gap-3">
                <!-- Notification Bell -->
                <div x-data="{
                    notifOpen: false,
                    notifications: @js(Auth::user()->notifications->take(20)->map(fn($n) => ['id' => $n->id, 'data' => $n->data, 'read_at' => $n->read_at, 'created_at' => $n->created_at->diffForHumans()])->values()),
                    unreadCount: {{ Auth::user()->unreadNotifications->count() }},
                    csrfToken: '{{ csrf_token() }}',
                    async markAllRead() {
                        await fetch('{{ route('notifications.markRead') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json' } });
                        this.notifications.forEach(n => n.read_at = true);
                        this.unreadCount = 0;
                    },
                    async markRead(notif) {
                        if (!notif.read_at) {
                            await fetch('/notifications/' + notif.id + '/read', { method: 'POST', headers: { 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json' } });
                            notif.read_at = true;
                            this.unreadCount = Math.max(0, this.unreadCount - 1);
                        }
                        if (notif.data.url) window.location.href = notif.data.url;
                    },
                    async dismiss(notif) {
                        await fetch('/notifications/' + notif.id, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json' } });
                        this.notifications = this.notifications.filter(n => n.id !== notif.id);
                        if (!notif.read_at) this.unreadCount = Math.max(0, this.unreadCount - 1);
                    }
                }" class="relative">
                    <button @click="notifOpen = !notifOpen" class="relative p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                        </svg>
                        <span x-show="unreadCount > 0" x-cloak class="absolute top-1 right-1 w-4 h-4 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center" x-text="unreadCount > 9 ? '9+' : unreadCount"></span>
                    </button>

                    <div x-show="notifOpen"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                         x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                         @click.away="notifOpen = false"
                         class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-xl border border-gray-200 shadow-lg overflow-hidden z-50"
                         style="display: none;">
                        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                            <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
                            <button x-show="unreadCount > 0" @click="markAllRead()" class="text-xs font-medium text-brand-800 hover:text-brand-900 transition-colors">Mark all read</button>
                        </div>

                        <div class="max-h-80 overflow-y-auto divide-y divide-gray-50">
                            <template x-for="notif in notifications" :key="notif.id">
                                <div class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 transition-colors cursor-pointer" :class="notif.read_at ? 'bg-white' : 'bg-brand-50/50'" @click="markRead(notif)">
                                    <div class="mt-0.5 shrink-0">
                                        <template x-if="notif.data.action === 'created'">
                                            <div class="w-7 h-7 rounded-full bg-green-100 flex items-center justify-center">
                                                <svg class="w-3.5 h-3.5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                            </div>
                                        </template>
                                        <template x-if="notif.data.action === 'updated'">
                                            <div class="w-7 h-7 rounded-full bg-blue-100 flex items-center justify-center">
                                                <svg class="w-3.5 h-3.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                                            </div>
                                        </template>
                                        <template x-if="notif.data.action === 'deleted'">
                                            <div class="w-7 h-7 rounded-full bg-red-100 flex items-center justify-center">
                                                <svg class="w-3.5 h-3.5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </div>
                                        </template>
                                        <template x-if="!['created','updated','deleted'].includes(notif.data.action)">
                                            <div class="w-7 h-7 rounded-full bg-gray-100 flex items-center justify-center">
                                                <svg class="w-3.5 h-3.5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </div>
                                        </template>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm text-gray-900" :class="notif.read_at ? '' : 'font-medium'" x-text="notif.data.message"></p>
                                        <p class="text-xs text-gray-400 mt-0.5" x-text="notif.created_at"></p>
                                    </div>
                                    <div class="shrink-0 flex items-center gap-1.5" @click.stop>
                                        <template x-if="notif.data.url">
                                            <a :href="notif.data.url" @click="markRead(notif)" class="text-gray-400 hover:text-brand-800 transition-colors" title="View">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                                            </a>
                                        </template>
                                        <button @click="dismiss(notif)" class="text-gray-300 hover:text-red-500 transition-colors" title="Dismiss">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                            <div x-show="notifications.length === 0" class="px-4 py-8 text-center">
                                <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                                <p class="text-sm text-gray-400">No notifications yet</p>
                            </div>
                        </div>
                    </div>
                </div>

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-gray-50 transition-colors">
                            @php
                                $nameParts = explode(' ', Auth::user()->name);
                                $initials = strtoupper(substr($nameParts[0], 0, 1)) . (isset($nameParts[1]) ? strtoupper(substr($nameParts[1], 0, 1)) : '');
                            @endphp
                            <div class="w-8 h-8 rounded-full bg-brand-800 flex items-center justify-center text-white text-[11px] font-bold tracking-tight">
                                {{ $initials }}
                            </div>
                            <span class="text-sm font-medium text-gray-700 hidden lg:inline">{{ Auth::user()->name }}</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Mobile Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Navigation -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-gray-200">
        <div class="pt-2 pb-3 space-y-1 px-4">
            <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('dashboard') ? 'text-brand-800 bg-brand-50' : 'text-gray-600' }}">Overview</a>
            <a href="{{ route('suppliers.index') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('suppliers.*') ? 'text-brand-800 bg-brand-50' : 'text-gray-600' }}">Suppliers</a>
        </div>
        <div class="pt-4 pb-3 border-t border-gray-200 px-4">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-brand-800 flex items-center justify-center text-white text-sm font-bold">
                    @php
                        $mobileNameParts = explode(' ', Auth::user()->name);
                        $mobileInitials = strtoupper(substr($mobileNameParts[0], 0, 1)) . (isset($mobileNameParts[1]) ? strtoupper(substr($mobileNameParts[1], 0, 1)) : '');
                    @endphp
                    {{ $mobileInitials }}
                </div>
                <div>
                    <div class="text-base font-semibold text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>
            </div>
            <x-responsive-nav-link :href="route('profile.edit')">{{ __('Profile') }}</x-responsive-nav-link>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                    {{ __('Log Out') }}
                </x-responsive-nav-link>
            </form>
        </div>
    </div>
</nav>
