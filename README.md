# BE Store — Sistema de Gestión Comercial

Sistema de gestión para local de accesorios y venta de celulares.
Desarrollado con Laravel 12, Livewire 4, MariaDB y Vite.

## Stack tecnológico

| Capa | Tecnología |
|------|-----------|
| Backend | PHP 8.2 + Laravel 12 |
| Frontend | Blade + Livewire 4 |
| Base de datos | MariaDB 10.11 |
| Assets | Vite 7 + CSS modular |
| Servidor | Apache 2 (bestore.local) |
| OS | Huayra Linux (Debian) |

## Módulos del sistema

- **Dashboard** — métricas del día, dólar blue, acceso rápido
- **Punto de venta** — búsqueda reactiva, carrito, 5 medios de pago, parte de pago en equipos
- **Productos** — CRUD con código de barras (pistola USB + cámara)
- **Equipos** — gestión por IMEI, precio USD → ARS automático
- **Clientes** — ABM con historial de compras
- **Compras** — registro de entrada de stock por proveedor
- **Ventas** — historial con filtros y vista detalle
- **Categorías** — ABM completo con ordenamiento
- **Proveedores** — ABM con historial
- **Usuarios** — roles admin/vendedor
- **Reportes** — ventas por período, categoría, medio de pago
- **Configuración** — recargos editables + sync dólar blue API

## Automatizaciones

```bash
# Sincronizar dólar blue (manual)
php artisan bestore:dolar

# Sincronizar productos desde Google Sheets (manual)
php artisan bestore:sync-sheets

# Modo dry-run (simular sin guardar)
php artisan bestore:sync-sheets --dry-run
```

El scheduler ejecuta ambos automáticamente:
- Dólar blue: todos los días a las 9:00 AM
- Google Sheets: todos los lunes a las 8:00 AM

## Instalación local

```bash
# 1. Clonar el repositorio
git clone https://github.com/eulaandres184-cyber/be-store.git
cd be-store

# 2. Instalar dependencias
composer install
npm install

# 3. Configurar entorno
cp .env.example .env
php artisan key:generate

# 4. Configurar base de datos en .env
# DB_CONNECTION=mysql
# DB_DATABASE=be_store
# DB_USERNAME=bestore_user
# DB_PASSWORD=tu_password

# 5. Migrar y sembrar
php artisan migrate
php artisan db:seed

# 6. Compilar assets
npm run build

# 7. Servidor (desarrollo)
php artisan serve
```

## Estructura de carpetas clave
cd ~/Documentos/be-store
php artisan view:clear
php artisan cache:clear
php artisan route:clear
npm run build

git add .
git commit -m "feat: sync completo con google sheets + productos reales

- Bot sync-sheets corregido con estructura real de la planilla
  * Columnas: A=TIPO, B=EFECTIVO, C=TARJETA
  * Hoja Fundas y Templados: dos bloques (col A-C y E-G)
  * Limpieza de precios formato argentino (\$6.900,00)
  * Modo --dry-run para verificar sin guardar
- Seeder ProductosSeeder: 20 fundas + 9 templados reales
- Modelo Proveedor: agregada \$table = 'proveedores'
- README profesional con instalación y estructura
- Todos los modelos con \$table explícita en español
- Comentarios en comandos y modelos"
git push origin produccion
git push origin main

