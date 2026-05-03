<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>BE Store — {{ $title ?? 'Gestión' }}</title>
    <style>
        .bs-page-nav {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            margin-bottom: 1.2rem;
            padding: .65rem 0;
            border-bottom: 1px solid rgba(15, 23, 42, .08);
        }
        .bs-page-nav a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: .6rem .9rem;
            border-radius: .8rem;
            text-decoration: none;
            font-size: .82rem;
            color: #334155;
            background: #F8FAFC;
            transition: all .15s ease;
            border: 1px solid transparent;
        }
        .bs-page-nav a:hover {
            background: #E2E8F0;
            color: #0F172A;
        }
        .bs-page-nav a.active {
            background: #0F172A;
            color: #fff;
            border-color: #0F172A;
        }
        @media (max-width: 900px) {
            .bs-page-nav { display: none; }
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{ $estilos ?? '' }}
    @livewireStyles
</head>
<body>

<header class="bs-topbar">
    <div class="bs-logo">BE <span>Store</span></div>
    <div class="bs-user">
        <div class="bs-dot"></div>
        {{ auth()->user()->name }}
        <form method="POST" action="{{ route('logout') }}" style="margin:0">
            @csrf
            <button type="submit" class="bs-logout">Salir</button>
        </form>
    </div>
</header>

<main class="bs-content">
    @unless(request()->routeIs('dashboard'))
        <nav class="bs-page-nav">
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Inicio</a>
            <a href="{{ route('pos') }}" class="{{ request()->routeIs('pos') ? 'active' : '' }}">Venta</a>
            <a href="{{ route('productos') }}" class="{{ request()->routeIs('productos*') ? 'active' : '' }}">Productos</a>
            <a href="{{ route('equipos') }}" class="{{ request()->routeIs('equipos*') ? 'active' : '' }}">Equipos</a>
            <a href="{{ route('ventas') }}" class="{{ request()->routeIs('ventas') ? 'active' : '' }}">Ventas</a>
            <a href="{{ route('compras') }}" class="{{ request()->routeIs('compras*') ? 'active' : '' }}">Compras</a>
            <a href="{{ route('reportes') }}" class="{{ request()->routeIs('reportes') ? 'active' : '' }}">Reportes</a>
            <a href="{{ route('configuracion') }}" class="{{ request()->routeIs('configuracion') ? 'active' : '' }}">Configuración</a>
            <a href="{{ route('usuarios') }}" class="{{ request()->routeIs('usuarios') ? 'active' : '' }}">Usuarios</a>
            <a href="{{ route('proveedores') }}" class="{{ request()->routeIs('proveedores') ? 'active' : '' }}">Proveedores</a>
        </nav>
    @endunless
    {{ $slot }}
</main>

<nav class="bs-mobile-nav">
    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        Inicio
    </a>
    <a href="{{ route('pos') }}" class="{{ request()->routeIs('pos') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        Venta
    </a>
    <a href="{{ route('productos') }}" class="{{ request()->routeIs('productos*') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
        Stock
    </a>
    <a href="{{ route('reportes') }}" class="{{ request()->routeIs('reportes') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        Reportes
    </a>
    <a href="{{ route('configuracion') }}" class="{{ request()->routeIs('configuracion') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3" stroke-width="2"/></svg>
        Config
    </a>
</nav>

@livewireScripts
{{ $scripts ?? '' }}
</body>
</html>
