<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Dashboard;
use App\Livewire\PuntoDeVenta;
use App\Livewire\Productos\ListaProductos;
use App\Livewire\Productos\FormProducto;
use App\Livewire\Equipos\ListaEquipos;
use App\Livewire\Equipos\FormEquipo;
use App\Livewire\Ventas\HistorialVentas;
use App\Livewire\Compras\ListaCompras;
use App\Livewire\Compras\FormCompra;
use App\Livewire\Reportes;
use App\Livewire\Configuracion;
use App\Livewire\Usuarios\ListaUsuarios;
use App\Livewire\Proveedores\ListaProveedores;

Route::get('/', fn() => redirect()->route('login'));

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard',         Dashboard::class)->name('dashboard');
    Route::get('/ventas/nueva',      PuntoDeVenta::class)->name('pos');
    Route::get('/productos',         ListaProductos::class)->name('productos');
    Route::get('/productos/nuevo',   FormProducto::class)->name('productos.nuevo');
    Route::get('/productos/{id}',    FormProducto::class)->name('productos.editar');
    Route::get('/equipos',           ListaEquipos::class)->name('equipos');
    Route::get('/equipos/nuevo',     FormEquipo::class)->name('equipos.nuevo');
    Route::get('/equipos/{id}',      FormEquipo::class)->name('equipos.editar');
    Route::get('/ventas',            HistorialVentas::class)->name('ventas');
    Route::get('/compras',           ListaCompras::class)->name('compras');
    Route::get('/compras/nueva',     FormCompra::class)->name('compras.nueva');
    Route::get('/reportes',          Reportes::class)->name('reportes');
    Route::get('/configuracion',     Configuracion::class)->name('configuracion');
    Route::get('/usuarios',          ListaUsuarios::class)->name('usuarios');
    Route::get('/proveedores',       ListaProveedores::class)->name('proveedores');
});

require __DIR__.'/auth.php';
