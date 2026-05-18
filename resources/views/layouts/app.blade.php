<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>BE Store — {{ $title ?? 'Gestión' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('estilos')
    @livewireStyles
</head>
<body>

{{-- Topbar --}}
<header class="bs-topbar">
    <div class="bs-logo">BE <span>Store</span></div>
    <div class="bs-user">
        <div class="bs-dot"></div>
        {{ auth()->user()->name }}
        @if((auth()->user()->rol ?? 'vendedor') === 'admin')
            <span style="background:rgba(255,255,255,.15);padding:1px 7px;border-radius:10px;font-size:.68rem">Admin</span>
        @else
            <span style="background:rgba(255,255,255,.1);padding:1px 7px;border-radius:10px;font-size:.68rem">Vendedor</span>
        @endif
        <form method="POST" action="{{ route('logout') }}" style="margin:0">
            @csrf
            <button type="submit" class="bs-logout">Salir</button>
        </form>
    </div>
</header>

{{-- Navegación rápida desktop --}}
<nav class="bs-quicknav">
    <a href="{{ route('dashboard') }}"  class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">🏠 Inicio</a>
    <span class="bs-quicknav-sep">|</span>
    <a href="{{ route('pos') }}"        class="{{ request()->routeIs('pos') ? 'active' : '' }}">🛒 Nueva venta</a>
    <a href="{{ route('productos') }}"  class="{{ request()->routeIs('productos*') ? 'active' : '' }}">📦 Productos</a>
    <a href="{{ route('equipos') }}"    class="{{ request()->routeIs('equipos*') ? 'active' : '' }}">📱 Equipos</a>
    <a href="{{ route('clientes') }}"   class="{{ request()->routeIs('clientes*') ? 'active' : '' }}">👥 Clientes</a>
    <a href="{{ route('ventas') }}"     class="{{ request()->routeIs('ventas') ? 'active' : '' }}">📋 Ventas</a>
    <a href="{{ route('devoluciones') }}" class="{{ request()->routeIs('devoluciones') ? 'active' : '' }}">🔄 Devoluciones</a>
    <a href="{{ route('documentos') }}" class="{{ request()->routeIs('documentos*') ? 'active' : '' }}">📄 Documentos</a>

    {{-- Solo admin --}}
    @if((auth()->user()->rol ?? 'vendedor') === 'admin')
    <span class="bs-quicknav-sep">|</span>
    <a href="{{ route('categorias') }}"   class="{{ request()->routeIs('categorias*') ? 'active' : '' }}">📂 Categorías</a>
    <a href="{{ route('compras') }}"      class="{{ request()->routeIs('compras*') ? 'active' : '' }}">🛒 Compras</a>
    <a href="{{ route('proveedores') }}"  class="{{ request()->routeIs('proveedores') ? 'active' : '' }}">🚚 Proveedores</a>
    <a href="{{ route('reportes') }}"     class="{{ request()->routeIs('reportes') ? 'active' : '' }}">📊 Reportes</a>
    <a href="{{ route('usuarios') }}"     class="{{ request()->routeIs('usuarios') ? 'active' : '' }}">👤 Usuarios</a>
    <a href="{{ route('configuracion') }}" class="{{ request()->routeIs('configuracion') ? 'active' : '' }}">⚙️ Config</a>
    @endif
</nav>

{{-- Flash de error de permisos --}}
@if(session('error'))
<div style="background:#FFF0F0;border-bottom:1px solid #FECACA;padding:.6rem 1rem;font-size:.82rem;color:#C0392B;text-align:center">
    🔒 {{ session('error') }}
</div>
@endif

<main class="bs-content">
    {{ $slot }}
</main>

{{-- Nav móvil --}}
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
    <a href="{{ route('ventas') }}" class="{{ request()->routeIs('ventas') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        Ventas
    </a>
    <a href="{{ route('configuracion') }}" class="{{ request()->routeIs('configuracion') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3" stroke-width="2"/></svg>
        Config
    </a>
</nav>

@stack('scripts')
@livewireScripts
</body>
</html>
