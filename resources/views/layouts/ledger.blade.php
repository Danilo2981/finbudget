<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>{{ config('app.name', 'Banco Capital - Presupuesto') }}</title>
    
    <!-- Scripts & Fonts -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet" />
    
    @livewireStyles

    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "surface-container-low": "#f2f3ff",
                        "surface": "#faf8ff",
                        "on-tertiary-container": "#29a678",
                        "on-error": "#ffffff",
                        "surface-container-highest": "#dae2fd",
                        "surface-container": "#eaedff",
                        "on-secondary-container": "#57657a",
                        "tertiary-fixed-dim": "#68dba9",
                        "on-primary": "#ffffff",
                        "error-container": "#ffdad6",
                        "inverse-surface": "#283044",
                        "on-background": "#131b2e",
                        "on-tertiary": "#ffffff",
                        "error": "#ba1a1a",
                        "on-error-container": "#93000a",
                        "surface-container-high": "#e2e7ff",
                        "on-primary-container": "#7594ca",
                        "tertiary": "#001c10",
                        "on-tertiary-fixed-variant": "#005137",
                        "on-primary-fixed": "#001b3d",
                        "background": "#faf8ff",
                        "tertiary-container": "#003321",
                        "secondary-container": "#d5e3fc",
                        "primary": "#001736",
                        "secondary": "#515f74",
                        "outline": "#747780",
                        "inverse-primary": "#a9c7ff",
                        "surface-tint": "#405f91",
                        "secondary-fixed-dim": "#b9c7df",
                        "secondary-fixed": "#d5e3fc",
                        "on-primary-fixed-variant": "#264778",
                        "outline-variant": "#c4c6d0",
                        "inverse-on-surface": "#eef0ff",
                        "on-secondary-fixed-variant": "#3a485b",
                        "on-tertiary-fixed": "#002114",
                        "surface-bright": "#faf8ff",
                        "on-secondary": "#ffffff",
                        "surface-dim": "#d2d9f4",
                        "primary-fixed-dim": "#a9c7ff",
                        "surface-variant": "#dae2fd",
                        "on-surface": "#131b2e",
                        "on-surface-variant": "#43474f",
                        "on-secondary-fixed": "#0d1c2e",
                        "tertiary-fixed": "#85f8c4",
                        "primary-container": "#002b5b",
                        "surface-container-lowest": "#ffffff",
                        "primary-fixed": "#d6e3ff"
                    },
                    fontFamily: {
                        "headline": ["Inter"],
                        "body": ["Inter"],
                        "label": ["Inter"]
                    },
                },
            },
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        body { font-family: 'Inter', sans-serif; }
        .tabular-nums { font-variant-numeric: tabular-nums; }
    </style>
</head>

<body class="bg-surface text-on-surface antialiased">
    <!-- SideNavBar -->
    <aside class="bg-[#001736] dark:bg-[#000d1f] h-screen w-64 fixed left-0 top-0 overflow-y-auto shadow-2xl shadow-black/20 z-50 flex flex-col py-6">
        <div class="px-6 mb-10">
            <h1 class="text-xl font-bold tracking-tighter text-white">Banco Capital</h1>
            <p class="text-[10px] uppercase tracking-[0.2em] text-[#85f8c4] opacity-70">FinBudget Vault</p>
        </div>
        <nav class="flex-1 space-y-1">
            <a class="flex items-center gap-3 px-4 py-3 text-slate-400 opacity-80 hover:bg-white/10 hover:text-white transition-all duration-200" href="#">
                <span class="material-symbols-outlined" data-icon="dashboard">dashboard</span>
                <span class="text-sm">Dashboard</span>
            </a>
            <a class="{{ request()->routeIs('historical-financials') ? 'flex items-center gap-3 px-4 py-3 text-[#85f8c4] border-l-2 border-[#85f8c4] bg-white/5 transition-all duration-200' : 'flex items-center gap-3 px-4 py-3 text-slate-400 opacity-80 hover:bg-white/10 hover:text-white transition-all duration-200' }}" href="{{ route('historical-financials') }}">
                <span class="material-symbols-outlined" data-icon="history" {{ request()->routeIs('historical-financials') ? 'style=font-variation-settings:\'FILL\' 1;' : '' }}>history</span>
                <span class="text-sm {{ request()->routeIs('historical-financials') ? 'font-semibold' : '' }}">Histórico Financiero</span>
            </a>
            <a class="{{ request()->routeIs('historical-portfolio') ? 'flex items-center gap-3 pl-8 pr-4 py-2 text-[#85f8c4] border-l-2 border-[#85f8c4] bg-white/5 transition-all duration-200' : 'flex items-center gap-3 pl-8 pr-4 py-2 text-slate-400 opacity-80 hover:bg-white/10 hover:text-white transition-all duration-200' }}" href="{{ route('historical-portfolio') }}">
                <span class="material-symbols-outlined text-sm" data-icon="account_balance" {{ request()->routeIs('historical-portfolio') ? 'style="font-variation-settings: \'FILL\' 1;"' : '' }}>account_balance</span>
                <span class="text-xs {{ request()->routeIs('historical-portfolio') ? 'font-semibold' : '' }}">Histórico Cartera de Créditos</span>
            </a>
            <a class="{{ request()->routeIs('recup-prov') ? 'flex items-center gap-3 px-4 py-3 text-[#85f8c4] border-l-2 border-[#85f8c4] bg-white/5 transition-all duration-200' : 'flex items-center gap-3 px-4 py-3 text-slate-400 opacity-80 hover:bg-white/10 hover:text-white transition-all duration-200' }}" href="{{ route('recup-prov') }}">
                <span class="material-symbols-outlined" data-icon="shield_with_heart" {{ request()->routeIs('recup-prov') ? 'style="font-variation-settings: \'FILL\' 1;"' : '' }}>shield_with_heart</span>
                <span class="text-sm {{ request()->routeIs('recup-prov') ? 'font-semibold' : '' }}">Recuperaciones y Provisiones</span>
            </a>
            <a class="{{ request()->routeIs('proy-cart-cre') ? 'flex items-center gap-3 px-4 py-3 text-[#85f8c4] border-l-2 border-[#85f8c4] bg-white/5 transition-all duration-200' : 'flex items-center gap-3 px-4 py-3 text-slate-400 opacity-80 hover:bg-white/10 hover:text-white transition-all duration-200' }}" href="{{ route('proy-cart-cre') }}">
                <span class="material-symbols-outlined" data-icon="show_chart" {{ request()->routeIs('proy-cart-cre') ? 'style="font-variation-settings: \'FILL\' 1;"' : '' }}>show_chart</span>
                <span class="text-sm {{ request()->routeIs('proy-cart-cre') ? 'font-semibold' : '' }}">Proyección Cartera</span>
            </a>
            <a class="{{ request()->routeIs('portfolio') ? 'flex items-center gap-3 pl-8 pr-4 py-2 text-[#85f8c4] border-l-2 border-[#85f8c4] bg-white/5 transition-all duration-200' : 'flex items-center gap-3 pl-8 pr-4 py-2 text-slate-400 opacity-80 hover:bg-white/10 hover:text-white transition-all duration-200' }}" href="{{ route('portfolio') }}">
                <span class="material-symbols-outlined text-sm" data-icon="tune" {{ request()->routeIs('portfolio') ? 'style="font-variation-settings: \'FILL\' 1;"' : '' }}>tune</span>
                <span class="text-xs {{ request()->routeIs('portfolio') ? 'font-semibold' : '' }}">Parámetros de Proyección</span>
            </a>
            <!-- Active State: Budgets -->
            <a class="{{ request()->routeIs('budget') ? 'flex items-center gap-3 px-4 py-3 text-[#85f8c4] border-l-2 border-[#85f8c4] bg-white/5 transition-all duration-200' : 'flex items-center gap-3 px-4 py-3 text-slate-400 opacity-80 hover:bg-white/10 hover:text-white transition-all duration-200' }}" href="{{ route('budget') }}">
                <span class="material-symbols-outlined" data-icon="account_balance_wallet" {{ request()->routeIs('budget') ? 'style="font-variation-settings: \'FILL\' 1;"' : '' }}>account_balance_wallet</span>
                <span class="text-sm {{ request()->routeIs('budget') ? 'font-semibold' : '' }}">Simulador Presupuestal</span>
            </a>
            <a class="{{ request()->routeIs('master.budget') ? 'flex items-center gap-3 px-4 py-3 text-[#85f8c4] border-l-2 border-[#85f8c4] bg-white/5 transition-all duration-200' : 'flex items-center gap-3 px-4 py-3 text-slate-400 opacity-80 hover:bg-white/10 hover:text-white transition-all duration-200' }}" href="{{ route('master.budget') }}">
                <span class="material-symbols-outlined" data-icon="query_stats" {{ request()->routeIs('master.budget') ? 'style="font-variation-settings: \'FILL\' 1;"' : '' }}>query_stats</span>
                <span class="text-sm {{ request()->routeIs('master.budget') ? 'font-semibold' : '' }}">Presupuesto Maestro</span>
            </a>
            <a class="flex items-center gap-3 px-4 py-3 text-slate-400 opacity-80 hover:bg-white/10 hover:text-white transition-all duration-200" href="#">
                <span class="material-symbols-outlined" data-icon="analytics">analytics</span>
                <span class="text-sm">Escenarios (Próximamente)</span>
            </a>
        </nav>
        <div class="mt-auto px-4 space-y-1">
            <a class="flex items-center gap-3 px-4 py-2 text-slate-400 opacity-80 hover:bg-white/10 hover:text-white transition-all duration-200" href="#">
                <span class="material-symbols-outlined text-sm" data-icon="logout">logout</span>
                <span class="text-xs">Cerrar Sesión</span>
            </a>
        </div>
    </aside>

    <!-- TopNavBar -->
    <header class="bg-[#faf8ff] dark:bg-[#0a0a0c] sticky top-0 z-40 ml-64 border-b border-slate-200/15 dark:border-slate-800/15">
        <div class="flex justify-between items-center h-16 px-8 w-full">
            <div class="flex items-center gap-4 flex-1">
                <div class="relative w-full max-w-md">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm" data-icon="search">search</span>
                    <input class="w-full bg-surface-container-highest/30 border-none rounded-lg py-2 pl-10 pr-4 text-sm focus:ring-1 focus:ring-primary transition-all placeholder:text-slate-400" placeholder="Buscar escenarios..." type="text" />
                </div>
            </div>
            <div class="flex items-center gap-6">
                <div class="flex items-center gap-3 pl-2">
                    <div class="text-right">
                        <p class="text-xs font-bold text-on-surface">Director Financiero</p>
                        <p class="text-[10px] text-slate-500 uppercase tracking-tighter">Admin</p>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Canvas -->
    <main class="ml-64 p-10 min-h-[calc(100-4rem)]">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
