# 🚀 Guía de Deployment en Railway

Pasos detallados para publicar **BeStore** en Railway Hosting.

---

## ✅ Requisitos Previos

1. **Cuenta en Railway**: [railway.app](https://railway.app)
2. **GitHub conectado** con el repositorio de BeStore
3. **Variables de entorno** documentadas
4. **Base de datos MySQL** configurada
5. **Dominio personalizado** (opcional)

---

## 📋 Paso 1: Crear Proyecto en Railway

1. Ve a [railway.app](https://railway.app)
2. Inicia sesión con GitHub
3. Click en **"New Project"**
4. Selecciona **"Deploy from GitHub repo"**
5. Busca y selecciona `eulaandres184-cyber/be-store`
6. Autoriza el acceso a Railway

---

## 🗄️ Paso 2: Agregar Base de Datos MySQL

1. En tu proyecto Railway, click en **"Add"** (ícono +)
2. Selecciona **"MySQL"** de las bases de datos disponibles
3. Railway crea automáticamente la instancia
4. Anota los datos de conexión que aparecen

**Datos importantes que necesitarás:**
- Host (Hostname)
- Port (Puerto)
- Database
- User
- Password

---

## 🔑 Paso 3: Configurar Variables de Entorno

En Railway:

1. Ve a tu proyecto y haz click en el servicio de **Laravel**
2. Abre la pestaña **"Variables"**
3. Agrega las siguientes variables (copia exactamente los nombres):

```env
APP_NAME=BeStore
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tudominio.com

# Base de Datos (reemplaza con valores de Railway)
DB_CONNECTION=mysql
DB_HOST=<HOST_DE_RAILWAY>
DB_PORT=3306
DB_DATABASE=<DATABASE_NAME>
DB_USERNAME=<USERNAME>
DB_PASSWORD=<PASSWORD>

# Sesiones
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Cache
CACHE_STORE=database

# Queue
QUEUE_CONNECTION=database

# Logs
LOG_CHANNEL=stack
LOG_LEVEL=info

# AFIP (si usas integración AFIP)
AFIP_AMBIENTE=produccion
AFIP_CERT_PATH=storage/afip/cert.pem
AFIP_KEY_PATH=storage/afip/key.pem

# Mail (opcional)
MAIL_MAILER=log
MAIL_FROM_ADDRESS=noreply@bestore.com
MAIL_FROM_NAME=BeStore

# Otros
REDIS_HOST=localhost
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### ⚠️ Variables Críticas

| Variable | Valor |
|----------|-------|
| `APP_KEY` | Genera con `php artisan key:generate` localmente |
| `DB_HOST` | IP/Hostname de Railway MySQL |
| `DB_PASSWORD` | Contraseña MySQL segura |
| `AFIP_AMBIENTE` | `produccion` o `testing` |

---

## 🔐 Paso 4: Agregar Certificados AFIP (si aplica)

Si usas integración AFIP en producción:

1. Copia tus archivos:
   - `cert.pem` 
   - `key.pem`

2. En Railway, ve a **Variables** del servicio Laravel
3. Agrega como variables de texto los certificados:
   ```
   AFIP_CERT_CONTENT=<contenido del cert.pem>
   AFIP_KEY_CONTENT=<contenido del key.pem>
   ```

4. En `app/Providers/AppServiceProvider.php`, agrega al boot():
   ```php
   if (env('AFIP_CERT_CONTENT')) {
       Storage::put('afip/cert.pem', env('AFIP_CERT_CONTENT'));
       Storage::put('afip/key.pem', env('AFIP_KEY_CONTENT'));
   }
   ```

---

## 🛠️ Paso 5: Configurar el Build

Railway detecta Laravel automáticamente, pero asegúrate:

1. En el servicio Laravel, ve a **"Settings"**
2. Verifica el **Start Command**:
   ```
   php artisan serve
   ```
   
3. O mejor, usa Supervisor con:
   ```
   supervisord -c /etc/supervisor/conf.d/supervisord.conf
   ```

4. En **Build Command**, debe ejecutar:
   ```
   npm install && npm run build && php artisan migrate --force
   ```

---

## 🚀 Paso 6: Deploy Inicial

1. Railway detecta cambios en `main` automáticamente
2. Cuando hagas push a GitHub, Railway inicia el build
3. Espera a que se complete (5-10 minutos típicamente)

**Monitorea el Deploy:**
- Ve a la pestaña **"Deployments"**
- Verifica que no haya errores
- Revisa los logs si falla

---

## ✨ Paso 7: Configurar Dominio Personalizado (Opcional)

1. En el servicio Laravel, ve a **"Settings"**
2. Click en **"Domains"**
3. Agrega tu dominio personalizado (ej: `bestore.com`)
4. Railway genera un certificado SSL automático

**En tu proveedor de dominio (GoDaddy, Namecheap, etc.):**
- Actualiza los DNS records hacia Railway
- Espera propagación (15-30 minutos)

---

## 🔍 Paso 8: Verificar que Todo Funcione

1. Accede a `https://tudominio.com`
2. Verifica login
3. Prueba operaciones básicas:
   - Listar productos
   - Crear venta
   - Generar reportes
4. Revisa logs: **Deployments → View Logs**

---

## 📝 Problemas Comunes y Soluciones

### ❌ Error: "SQLSTATE[HY000]: General error"

**Causa:** Base de datos no migrada

**Solución:**
```bash
# En tu computadora local
php artisan migrate --force --env=production
```

O en Railway, ejecuta:
```bash
php artisan migrate --force
```

---

### ❌ Error: "Class not found"

**Causa:** Vendor no instalado correctamente

**Solución:**
1. En Railway, **Rebuild** el proyecto
2. Verifica que `composer.json` esté en la raíz

---

### ❌ Error: "Assets not loading" (CSS/JS vacío)

**Causa:** Vite no compiló

**Solución:**
```bash
# Localmente
npm run build
git add public/build/
git commit -m "Rebuild assets"
git push
```

Luego Railway rebuildeará automáticamente.

---

### ❌ Error: "502 Bad Gateway"

**Causa:** Aplicación crasheó

**Solución:**
1. Revisa los logs en Railway
2. Verifica variables de entorno
3. Comprueba base de datos está conectada

---

## 🔄 Configurar Auto-Deploy desde Rama Específica

Por defecto, Railway deploya desde `main`. Para cambiar a `produccion`:

1. En Railway, **Settings** del servicio
2. Busca **"GitHub Repo"**
3. Cambia la rama a `produccion`

Ahora cada push a `produccion` triggereará un deploy.

---

## 📊 Monitoreo en Producción

### Logs
```bash
# Desde Railway UI
Deployments → View Logs
```

### Métricas
- CPU Usage
- Memory Usage
- Request Rate
- Build Duration

Monitorea desde el dashboard de Railway.

---

## 🔄 CI/CD: Deploy Automático

Crea un workflow GitHub Actions para auto-deploy (opcional):

**`.github/workflows/deploy.yml`**
```yaml
name: Deploy to Railway

on:
  push:
    branches: [produccion]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Deploy to Railway
        run: |
          npm i -g @railway/cli
          railway up --service bestore-app
        env:
          RAILWAY_TOKEN: ${{ secrets.RAILWAY_TOKEN }}
```

---

## ✅ Checklist Final

- [ ] Repositorio conectado a Railway
- [ ] MySQL agregada y conectada
- [ ] Variables de entorno configuradas
- [ ] Base de datos migrada
- [ ] Assets compilados (npm run build)
- [ ] Certificados AFIP en lugar (si aplica)
- [ ] Dominio personalizado agregado
- [ ] SSL/TLS automático configurado
- [ ] Logs revisados sin errores
- [ ] Login funciona
- [ ] Datos de prueba cargados
- [ ] Backups configurados

---

## 🆘 Soporte

Si algo falla:

1. Revisa **Logs** en Railway UI
2. Comprueba **Database Connection** desde Laravel
3. Verifica **Environment Variables**
4. Contacta al soporte de Railway

**Documentación útil:**
- [Railway Docs](https://docs.railway.app)
- [Laravel on Railway](https://docs.railway.app/guides/laravel)
- [MySQL on Railway](https://docs.railway.app/databases/mysql)

---

**¡Listo! 🎉 Tu aplicación BeStore está en producción en Railway.**
