<?php

use Illuminate\Support\Facades\Route;

// ── Importaciones de componentes Livewire ──────────────────
use App\Livewire\Dashboard;
use App\Livewire\PuntoDeVenta;
use App\Livewire\Productos\ListaProductos;
use App\Livewire\Productos\FormProducto;
use App\Livewire\Equipos\ListaEquipos;
use App\Livewire\Equipos\FormEquipo;
use App\Livewire\Ventas\HistorialVentas;
use App\Livewire\Ventas\Devoluciones;
use App\Livewire\Compras\ListaCompras;
use App\Livewire\Compras\FormCompra;
use App\Livewire\Reportes;
use App\Livewire\Configuracion;
use App\Livewire\Usuarios\ListaUsuarios;
use App\Livewire\Proveedores\ListaProveedores;
use App\Livewire\Categorias\ListaCategorias;
use App\Livewire\Categorias\FormCategoria;
use App\Livewire\Clientes\ListaClientes;
use App\Livewire\Clientes\FormCliente;
use App\Livewire\Documentos\EmitirDocumento;
use App\Livewire\Documentos\ListaDocumentos;
use App\Livewire\Documentos\Presupuesto;
use App\Http\Controllers\DocumentoController;

Route::get('/', fn() => redirect()->route('login'));

// ── Rutas accesibles por TODOS los usuarios autenticados ───
Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    // Punto de venta — acceso total
    Route::get('/ventas/nueva', PuntoDeVenta::class)->name('pos');

    // Productos — vendedor puede ver pero no crear/editar
    Route::get('/productos', ListaProductos::class)->name('productos');

    // Equipos — vendedor puede ver
    Route::get('/equipos', ListaEquipos::class)->name('equipos');

    // Clientes — acceso total
    Route::get('/clientes',       ListaClientes::class)->name('clientes');

    // Ventas — acceso total
    Route::get('/ventas',        HistorialVentas::class)->name('ventas');
    Route::get('/devoluciones',  Devoluciones::class)->name('devoluciones');

    // Documentos — acceso total
    Route::get('/documentos',                  ListaDocumentos::class)->name('documentos');
    Route::get('/documentos/presupuesto',      Presupuesto::class)->name('documentos.presupuesto');
    Route::get('/documentos/emitir/{ventaId}', EmitirDocumento::class)->name('documentos.emitir');
    Route::get('/documentos/{id}/ver',         [DocumentoController::class, 'ver'])->name('documentos.ver');
    Route::get('/documentos/{id}/whatsapp',    [DocumentoController::class, 'whatsapp'])->name('documentos.whatsapp');
});

// ── Rutas solo para ADMIN ──────────────────────────────────
Route::middleware(['auth', 'rol:admin'])->group(function () {

    // Productos — crear y editar
    Route::get('/productos/nuevo',   FormProducto::class)->name('productos.nuevo');
    Route::get('/productos/{id}',    FormProducto::class)->name('productos.editar');

    // Equipos — crear y editar
    Route::get('/equipos/nuevo',     FormEquipo::class)->name('equipos.nuevo');
    Route::get('/equipos/{id}',      FormEquipo::class)->name('equipos.editar');

    // Categorías — solo admin
    Route::get('/categorias',        ListaCategorias::class)->name('categorias');
    Route::get('/categorias/nueva',  FormCategoria::class)->name('categorias.nueva');
    Route::get('/categorias/{id}',   FormCategoria::class)->name('categorias.editar');

    // Compras — solo admin
    Route::get('/compras',           ListaCompras::class)->name('compras');
    Route::get('/compras/nueva',     FormCompra::class)->name('compras.nueva');

    // Clientes — solo admin
    Route::get('/clientes/nuevo',    FormCliente::class)->name('clientes.nuevo');
    Route::get('/clientes/{id}',     FormCliente::class)->name('clientes.editar');

    // Reportes — solo admin
    Route::get('/reportes',          Reportes::class)->name('reportes');

    // Configuración — solo admin
    Route::get('/configuracion',     Configuracion::class)->name('configuracion');

    // Usuarios — solo admin
    Route::get('/usuarios',          ListaUsuarios::class)->name('usuarios');

    // Proveedores — solo admin
    Route::get('/proveedores',       ListaProveedores::class)->name('proveedores');
});

require __DIR__ . '/auth.php';
