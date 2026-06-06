# 📊 Sistema de Auditoría - Historial de Cambios

## Descripción

El sistema de auditoría registra automáticamente todos los cambios realizados en:
- **Precios de productos** - Cambios de precio_efectivo y moneda
- **Configuración del sistema** - Recargos, porcentajes, valores del dólar
- **Historial del dólar** - Cotización de compra y venta

Esto permite:
✅ Auditoría completa sin alterar ventas anteriores
✅ Trazabilidad de quién cambió qué y cuándo
✅ Historial completo de precios históricos
✅ Reportes de cambios de configuración

---

## 📁 Estructura de Archivos

### Modelos
- `app/Models/HistorialPrecioProducto.php` - Registro de cambios de precios
- `app/Models/HistorialConfiguracion.php` - Registro de cambios en configuración
- `app/Models/HistorialDolar.php` - Registro de cotizaciones del dólar (actualizado)

### Observers (Listeners automáticos)
- `app/Observers/ProductoObserver.php` - Observa cambios en modelo Producto
- `app/Observers/ConfiguracionObserver.php` - Observa cambios en modelo Configuracion

### Componentes Livewire
- `app/Livewire/Auditoria/HistorialCambios.php` - Visualización del historial

### Vistas
- `resources/views/livewire/auditoria/historial-cambios.blade.php` - Interfaz del historial

### Migraciones
- `database/migrations/2026_06_06_000001_create_historial_precios_productos_table.php`
- `database/migrations/2026_06_06_000002_create_historial_configuracion_table.php`

---

## 🚀 Cómo Funciona

### 1. Cambios Automáticos (Sin intervención manual)

Cuando actualizas un **Producto**:
```php
$producto = Producto::find(1);
$producto->update(['precio_efectivo' => 150]); // Se registra automáticamente
```

Cuando actualizas **Configuración**:
```php
$config = Configuracion::find(1);
$config->update(['recargo_tarjeta' => 20]); // Se registra automáticamente
```

### 2. Registros Manuales (si lo necesitas)

**Registrar cambio de precio manualmente:**
```php
HistorialPrecioProducto::registrarCambio(
    productoId: 1,
    comercioId: 1,
    precioAnterior: 100,
    precioNuevo: 150,
    moneda: 'ARS',
    usuarioId: auth()->id(),
    razonCambio: 'Ajuste por inflación'
);
```

**Registrar cambio de configuración:**
```php
HistorialConfiguracion::registrarCambio(
    comercioId: 1,
    campo: 'recargo_tarjeta',
    valorAnterior: 15,
    valorNuevo: 20,
    usuarioId: auth()->id(),
    descripcion: 'Aumento de recargo por nuevas comisiones bancarias'
);
```

**Registrar cambio de dólar:**
```php
HistorialDolar::registrarCambio(
    valorCompra: 350,
    valorVenta: 360,
    fuente: 'API BCRA',
    comercioId: 1,
    usuarioId: auth()->id()
);
```

---

## 📋 Campos Auditados

### Producto
- `precio_efectivo` - Precio base
- `moneda` - Moneda (ARS/USD)

### Configuración
- `recargo_tarjeta` - Recargo tarjeta de crédito (%)
- `cuotas_4_recargo` - Recargo 4 cuotas (%)
- `cuotas_20_recargo` - Recargo 20 cuotas (%)
- `dolar_blue_hoy` - Valor dólar blue
- `dolar_oficial` - Valor dólar oficial

### Dólar
- `valor_compra` - Cotización de compra
- `valor_venta` - Cotización de venta
- `fuente` - Origen del dato (API, manual, etc)

---

## 🔍 Consultas Útiles

### Obtener historial de un producto
```php
$producto = Producto::find(1);
$cambios = $producto->historialPrecios()->get();

foreach ($cambios as $cambio) {
    echo "Precio: {$cambio->precio_anterior} → {$cambio->precio_nuevo}";
    echo "Usuario: {$cambio->usuario->name}";
    echo "Fecha: {$cambio->cambio_en}";
}
```

### Obtener últimos cambios de configuración
```php
$cambios = HistorialConfiguracion::query()
    ->where('comercio_id', 1)
    ->where('campo', 'recargo_tarjeta')
    ->orderByDesc('cambio_en')
    ->limit(10)
    ->get();
```

### Obtener cambios de los últimos 30 días
```php
$cambios = HistorialPrecioProducto::query()
    ->delComercio(1)
    ->recientes(30)
    ->get();
```

### Obtener cambios por usuario
```php
$cambios = HistorialConfiguracion::query()
    ->delComercio(1)
    ->delUsuario(auth()->id())
    ->orderByDesc('cambio_en')
    ->get();
```

---

## 📊 Información del Historial

### HistorialPrecioProducto
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | ID único |
| producto_id | FK | Producto modificado |
| comercio_id | FK | Comercio propietario |
| precio_anterior | DECIMAL | Precio anterior |
| precio_nuevo | DECIMAL | Precio nuevo |
| moneda_anterior | VARCHAR | Moneda anterior |
| moneda_nueva | VARCHAR | Moneda nueva |
| usuario_id | FK | Usuario que realizó el cambio |
| razon_cambio | VARCHAR | Motivo del cambio |
| cambio_en | TIMESTAMP | Cuándo ocurrió |
| created_at | TIMESTAMP | Cuándo se registró |
| updated_at | TIMESTAMP | Última actualización |

### HistorialConfiguracion
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | ID único |
| comercio_id | FK | Comercio |
| campo | VARCHAR | Campo modificado |
| valor_anterior | VARCHAR | Valor anterior |
| valor_nuevo | VARCHAR | Valor nuevo |
| usuario_id | FK | Usuario que realizó el cambio |
| descripcion | TEXT | Descripción del cambio |
| cambio_en | TIMESTAMP | Cuándo ocurrió |

---

## 🎯 Ventajas

### 1. **Sin impacto en ventas anteriores**
Las ventas registran el precio al momento de la compra. Los cambios futuros no afectan histórico.

### 2. **Trazabilidad completa**
Sabe quién cambió qué, cuándo y potencialmente por qué.

### 3. **Auditoría**
Perfecto para reportes, análisis y cumplimiento normativo.

### 4. **Análisis de precios**
Puede ver evolución de precios a lo largo del tiempo.

### 5. **Información de costos**
Sigue la evolución del dólar y cómo impactó en precios.

---

## 🔧 Para Desarrolladores

### Agregar nuevo campo a auditar

1. **Actualizar ConfiguracionObserver.php:**
```php
private const CAMPOS_AUDITABLES = [
    'recargo_tarjeta',
    'nuevo_campo', // ← Agregar aquí
];
```

2. **Actualizar HistorialConfiguracion::getCamposAuditables():**
```php
public static function getCamposAuditables(): array
{
    return [
        'recargo_tarjeta' => 'Recargo Tarjeta',
        'nuevo_campo' => 'Nombre del nuevo campo',
    ];
}
```

### Auditar otro modelo

1. **Crear observer** para el modelo en `app/Observers/MiModeloObserver.php`
2. **Registrar en AppServiceProvider.php:**
```php
MiModelo::observe(MiModeloObserver::class);
```

---

## 📈 Reportes Sugeridos

Puedes crear reports usando este historial:

1. **Evolución de precios** - Gráficos de cambios de precio
2. **Impacto del dólar** - Cómo afectó el dólar los precios
3. **Cambios de configuración** - Auditoría de cambios de recargos
4. **Actividad por usuario** - Quién hizo más cambios
5. **Productos más modificados** - Cuáles tuvieron más cambios de precio

---

## ⚠️ Consideraciones

- El historial crece con el tiempo. Considere archivado después de 2 años.
- Los queries sobre historial pueden ser lentos sin índices. Ya están incluidos.
- La auditoría se basa en `auth()->id()` - asegúrate que siempre haya usuario autenticado.

---

**Sistema de Auditoría creado: 6 de junio de 2026**
