<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>BE Store — {{ $title ?? 'Gestión' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('estilos')
    @livewireStyles
    <style>
        /* ── Layout con sidebar ── */
        .bs-layout { display: flex; min-height: 100vh; }

        /* Sidebar */
        .bs-sidebar {
            width: 220px;
            background: var(--bs-black);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 40;
            overflow-y: auto;
            flex-shrink: 0;
        }
        .bs-sidebar-logo {
            padding: 1.25rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,.1);
        }
        .bs-sidebar-logo .logo-text { color: #fff; font-size: 1.2rem; font-weight: 700; }
        .bs-sidebar-logo .logo-text span { color: #A8C6E0; font-weight: 400; }
        .bs-sidebar-logo .logo-user { font-size: .72rem; color: #7A8FA6; margin-top: .2rem; display: flex; align-items: center; gap: 5px; }
        .bs-sidebar-logo .logo-dot  { width: 6px; height: 6px; border-radius: 50%; background: #82E0AA; }
        .bs-sidebar-logo .logo-rol  { background: rgba(255,255,255,.1); padding: 1px 7px; border-radius: 10px; font-size: .65rem; color: #A8C6E0; }

        /* Grupos del menú */
        .bs-nav-group { padding: .5rem 0; border-bottom: 1px solid rgba(255,255,255,.06); }
        .bs-nav-label { font-size: .62rem; text-transform: uppercase; letter-spacing: .08em; color: #4A5568; padding: .4rem 1rem .2rem; font-weight: 600; }
        .bs-nav-item {
            display: flex; align-items: center; gap: .6rem;
            padding: .5rem 1rem;
            font-size: .82rem; color: #94A3B8;
            text-decoration: none;
            transition: var(--bs-transition);
            border-left: 3px solid transparent;
        }
        .bs-nav-item:hover  { background: rgba(255,255,255,.06); color: #fff; }
        .bs-nav-item.active { background: rgba(74,144,217,.15); color: #A8C6E0; border-left-color: #4A90D9; }
        .bs-nav-item svg    { width: 16px; height: 16px; flex-shrink: 0; }

        /* Logout en el fondo del sidebar */
        .bs-sidebar-footer {
            margin-top: auto;
            padding: .75rem 1rem;
            border-top: 1px solid rgba(255,255,255,.08);
        }
        .bs-sidebar-footer form button {
            width: 100%; padding: .45rem; background: rgba(255,255,255,.07);
            border: 1px solid rgba(255,255,255,.1); border-radius: 6px;
            color: #94A3B8; font-size: .8rem; cursor: pointer;
            transition: var(--bs-transition);
        }
        .bs-sidebar-footer form button:hover { background: rgba(192,57,43,.3); color: #FCA5A5; }

        /* Contenido principal */
        .bs-main { margin-left: 220px; flex: 1; min-height: 100vh; display: flex; flex-direction: column; }
        .bs-topbar-mini {
            background: var(--bs-white);
            border-bottom: 1px solid var(--bs-gray-border);
            padding: .6rem 1.25rem;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 30;
            box-shadow: var(--bs-shadow-sm);
        }
        .bs-page-breadcrumb { font-size: .82rem; color: var(--bs-muted); }
        .bs-page-breadcrumb strong { color: var(--bs-dark); }
        .bs-content { padding: 1.25rem 1.5rem 3rem; flex: 1; }

        /* Flash error permisos */
        .bs-flash-error { background: #FFF0F0; border-bottom: 1px solid #FECACA; padding: .5rem 1.25rem; font-size: .82rem; color: #C0392B; display: flex; align-items: center; gap: .5rem; }

        /* Móvil */
        .bs-menu-toggle { display: none; background: none; border: none; color: var(--bs-dark); cursor: pointer; padding: .25rem; }
        .bs-mobile-nav  { display: none; }

        @media(max-width: 768px) {
            .bs-sidebar   { transform: translateX(-100%); transition: transform .25s; }
            .bs-sidebar.open { transform: translateX(0); }
            .bs-main      { margin-left: 0; }
            .bs-menu-toggle { display: block; }
            .bs-mobile-nav {
                display: flex; position: fixed;
                bottom: 0; left: 0; right: 0;
                background: var(--bs-black); border-top: 1px solid rgba(255,255,255,.1); z-index: 50;
            }
            .bs-mobile-nav a { flex: 1; display: flex; flex-direction: column; align-items: center; padding: .5rem .25rem; font-size: .62rem; color: #7A8FA6; gap: 2px; }
            .bs-mobile-nav a.active, .bs-mobile-nav a:hover { color: #A8C6E0; }
            .bs-mobile-nav svg { width: 20px; height: 20px; }
            .bs-content { padding: 1rem .75rem 5rem; }
            .bs-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 39; display: none; }
            .bs-overlay.show { display: block; }
        }
    </style>
</head>
<body>
@php $rol = auth()->user()->rol ?? 'vendedor'; $esAdmin = $rol === 'admin'; @endphp

<div class="bs-layout">

{{-- ── SIDEBAR ── --}}
<aside class="bs-sidebar" id="sidebar">
    <div class="bs-sidebar-logo">
        <div class="logo-text">BE <span>Store</span></div>
        <div class="logo-user">
            <span class="logo-dot"></span>
            {{ auth()->user()->name }}
            <span class="logo-rol">{{ $esAdmin ? 'Admin' : 'Vendedor' }}</span>
        </div>
    </div>

    {{-- Grupo: operaciones diarias --}}
    <div class="bs-nav-group">
        <div class="bs-nav-label">Operaciones</div>
        <a href="{{ route('dashboard') }}" class="bs-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Inicio
        </a>
        <a href="{{ route('pos') }}" class="bs-nav-item {{ request()->routeIs('pos') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            Nueva venta
        </a>
        <a href="{{ route('ventas') }}" class="bs-nav-item {{ request()->routeIs('ventas') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            Historial ventas
        </a>
        <a href="{{ route('devoluciones') }}" class="bs-nav-item {{ request()->routeIs('devoluciones') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            Devoluciones
        </a>
        <a href="{{ route('documentos') }}" class="bs-nav-item {{ request()->routeIs('documentos*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Documentos
        </a>
    </div>

    {{-- Grupo: catálogo --}}
    <div class="bs-nav-group">
        <div class="bs-nav-label">Catálogo</div>
        <a href="{{ route('productos') }}" class="bs-nav-item {{ request()->routeIs('productos*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            Productos
        </a>
        <a href="{{ route('equipos') }}" class="bs-nav-item {{ request()->routeIs('equipos*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            Equipos
        </a>
        @if($esAdmin)
        <a href="{{ route('categorias') }}" class="bs-nav-item {{ request()->routeIs('categorias*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
            Categorías
        </a>
        @endif
        <a href="{{ route('clientes') }}" class="bs-nav-item {{ request()->routeIs('clientes*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Clientes
        </a>
    </div>

    {{-- Grupo: administración (solo admin) --}}
    @if($esAdmin)
    <div class="bs-nav-group">
        <div class="bs-nav-label">Administración</div>
        <a href="{{ route('compras') }}" class="bs-nav-item {{ request()->routeIs('compras*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            Compras
        </a>
        <a href="{{ route('proveedores') }}" class="bs-nav-item {{ request()->routeIs('proveedores') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
            Proveedores
        </a>
        <a href="{{ route('reportes') }}" class="bs-nav-item {{ request()->routeIs('reportes') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            Reportes
        </a>
        <a href="{{ route('usuarios') }}" class="bs-nav-item {{ request()->routeIs('usuarios') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/></svg>
            Usuarios
        </a>
        <a href="{{ route('configuracion') }}" class="bs-nav-item {{ request()->routeIs('configuracion') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3" stroke-width="2"/></svg>
            Configuración
        </a>
    </div>
    @endif

    <div class="bs-sidebar-footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">↩ Cerrar sesión</button>
        </form>
    </div>
</aside>

{{-- Overlay móvil --}}
<div class="bs-overlay" id="sidebar-overlay" onclick="toggleSidebar()"></div>

{{-- ── CONTENIDO PRINCIPAL ── --}}
<div class="bs-main">

    {{-- Topbar mini --}}
    <header class="bs-topbar-mini">
        <div style="display:flex;align-items:center;gap:.75rem">
            <button class="bs-menu-toggle" onclick="toggleSidebar()">
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div class="bs-page-breadcrumb">
                BE Store / <strong>{{ $title ?? 'Inicio' }}</strong>
            </div>
        </div>
        <div style="font-size:.78rem;color:var(--bs-muted)">
            {{ now()->format('d/m/Y') }}
        </div>
    </header>

    {{-- Flash de permisos --}}
    @if(session('error'))
    <div class="bs-flash-error">🔒 {{ session('error') }}</div>
    @endif

    {{-- Contenido --}}
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
        <a href="{{ route('documentos') }}" class="{{ request()->routeIs('documentos*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Docs
        </a>
    </nav>
</div>
</div>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebar-overlay').classList.toggle('show');
}
</script>

@stack('scripts')
@livewireScripts
</body>
</html>
