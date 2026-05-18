<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * RolMiddleware
 *
 * Verifica que el usuario autenticado tenga el rol requerido.
 * Uso en rutas: ->middleware('rol:admin')
 *
 * Roles disponibles:
 *   - admin    → acceso total al sistema
 *   - vendedor → solo POS, productos (lectura), clientes, ventas
 */
class RolMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $usuario = auth()->user();

        if (!$usuario) {
            return redirect()->route('login');
        }

        $rolUsuario = $usuario->rol ?? 'vendedor';

        // Admin tiene acceso a todo
        if ($rolUsuario === 'admin') {
            return $next($request);
        }

        // Verificar si el rol del usuario está en los roles permitidos
        if (in_array($rolUsuario, $roles)) {
            return $next($request);
        }

        // Sin permiso → redirigir al dashboard con mensaje
        session()->flash('error', 'No tenés permisos para acceder a esa sección.');
        return redirect()->route('dashboard');
    }
}
