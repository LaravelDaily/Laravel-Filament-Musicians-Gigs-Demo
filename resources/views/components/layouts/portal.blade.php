<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $companyName = \App\Models\Setting::get('company_name', config('app.name', 'Mod Society'));
    @endphp
    <title>{{ $title ?? 'Musician Portal' }} - {{ $companyName }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>
<body class="min-h-screen bg-white antialiased dark:bg-zinc-800">
    <flux:header container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:brand href="{{ route('portal.dashboard') }}" name="{{ $companyName }}" class="max-lg:hidden">
            <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
                <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
            </x-slot>
        </flux:brand>

        <flux:navbar class="-mb-px max-lg:hidden">
            <flux:navbar.item icon="home" href="{{ route('portal.dashboard') }}" :current="request()->routeIs('portal.dashboard')">
                Dashboard
            </flux:navbar.item>
            <flux:navbar.item icon="clock" href="{{ route('portal.gigs.past') }}" :current="request()->routeIs('portal.gigs.past')">
                Past Gigs
            </flux:navbar.item>
            <flux:navbar.item icon="user" href="{{ route('portal.profile') }}" :current="request()->routeIs('portal.profile')">
                My Profile
            </flux:navbar.item>
        </flux:navbar>

        <flux:spacer />

        <flux:dropdown position="bottom" align="end">
            <flux:profile :name="auth()->user()->name" :initials="auth()->user()->initials()" />
            <flux:menu>
                <flux:menu.item icon="user" href="{{ route('portal.profile') }}">My Profile</flux:menu.item>
                <flux:menu.separator />
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                        Log Out
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    <flux:sidebar collapsible="mobile" class="border-r border-zinc-200 bg-zinc-50 lg:hidden dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.header>
            <flux:brand href="{{ route('portal.dashboard') }}" name="{{ $companyName }}">
                <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
                    <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
                </x-slot>
            </flux:brand>
            <flux:sidebar.collapse class="lg:hidden" />
        </flux:sidebar.header>

        <flux:sidebar.nav>
            <flux:sidebar.item icon="home" href="{{ route('portal.dashboard') }}" :current="request()->routeIs('portal.dashboard')">
                Dashboard
            </flux:sidebar.item>
            <flux:sidebar.item icon="clock" href="{{ route('portal.gigs.past') }}" :current="request()->routeIs('portal.gigs.past')">
                Past Gigs
            </flux:sidebar.item>
            <flux:sidebar.item icon="user" href="{{ route('portal.profile') }}" :current="request()->routeIs('portal.profile')">
                My Profile
            </flux:sidebar.item>
        </flux:sidebar.nav>

        <flux:sidebar.spacer />

        <flux:sidebar.nav>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <flux:sidebar.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full text-left">
                    Log Out
                </flux:sidebar.item>
            </form>
        </flux:sidebar.nav>
    </flux:sidebar>

    <flux:main container class="py-6">
        {{ $slot }}
    </flux:main>

    @fluxScripts
</body>
</html>
