#!/bin/bash
# ============================================================
# BE Store — Script de desarrollo automatizado
# Uso: ./bestore-dev.sh [comando]
# ============================================================

PROYECTO=~/Documentos/be-store
cd $PROYECTO

case "$1" in

  start)
    echo "🚀 Iniciando entorno de desarrollo..."
    # Terminal 1: compilador de assets en modo watch
    gnome-terminal --title="Vite Watch" -- bash -c "cd $PROYECTO && npm run dev; exec bash" &
    # Terminal 2: log de errores Laravel en tiempo real
    gnome-terminal --title="Laravel Log" -- bash -c "cd $PROYECTO && tail -f storage/logs/laravel.log; exec bash" &
    echo "✅ Vite corriendo. Apache sirve en http://bestore.local"
    ;;

  build)
    echo "🔨 Compilando assets para producción..."
    npm run build
    php artisan view:clear
    php artisan cache:clear
    echo "✅ Assets compilados"
    ;;

  save)
    MSG="${2:-"chore: cambios del $(date '+%d/%m/%Y %H:%M')"}"
    echo "💾 Guardando cambios: $MSG"
    git add .
    git commit -m "$MSG"
    git push origin produccion
    echo "✅ Cambios subidos a GitHub"
    ;;

  deploy)
    echo "🚀 Preparando para producción..."
    npm run build
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan optimize
    git add .
    git commit -m "build: deploy $(date '+%d/%m/%Y')"
    git push origin produccion
    git push origin main
    echo "✅ Listo para deploy"
    ;;

  backup)
    FECHA=$(date +%Y%m%d_%H%M)
    ARCHIVO=~/backups/bestore_$FECHA.sql
    mkdir -p ~/backups
    mysqldump -u bestore_user -pBeStore2024 be_store > $ARCHIVO
    echo "✅ Backup guardado en $ARCHIVO"
    ;;

  restore)
    if [ -z "$2" ]; then
      echo "Uso: ./bestore-dev.sh restore archivo.sql"
      ls ~/backups/*.sql 2>/dev/null || echo "No hay backups"
    else
      mysql -u bestore_user -pBeStore2024 be_store < $2
      echo "✅ Base de datos restaurada desde $2"
    fi
    ;;

  dolar)
    echo "💵 Sincronizando dólar blue..."
    php artisan bestore:dolar
    ;;

  migrate)
    echo "🗄 Ejecutando migraciones..."
    php artisan migrate
    php artisan db:seed --class=ProductosSeeder 2>/dev/null || true
    ;;

  fresh)
    echo "⚠ Reiniciando base de datos (ELIMINA TODOS LOS DATOS)..."
    read -p "¿Seguro? (escribí SI para confirmar): " confirm
    if [ "$confirm" = "SI" ]; then
      php artisan migrate:fresh --seed
      echo "✅ Base de datos reiniciada"
    else
      echo "Operación cancelada"
    fi
    ;;

  status)
    echo "═══════════════════════════════════"
    echo "  BE Store — Estado del sistema"
    echo "═══════════════════════════════════"
    echo "Apache:  $(systemctl is-active apache2)"
    echo "MariaDB: $(systemctl is-active mariadb)"
    echo "URL:     http://bestore.local"
    echo ""
    echo "Últimos commits:"
    git log --oneline -5
    echo ""
    echo "Migraciones:"
    php artisan migrate:status | tail -10
    ;;

  logs)
    echo "📋 Últimas 50 líneas del log de Laravel:"
    tail -50 storage/logs/laravel.log
    ;;

  *)
    echo "BE Store — Comandos disponibles:"
    echo ""
    echo "  ./bestore-dev.sh start       Iniciar Vite watch + log"
    echo "  ./bestore-dev.sh build       Compilar assets para producción"
    echo "  ./bestore-dev.sh save 'msg'  Guardar y subir cambios a GitHub"
    echo "  ./bestore-dev.sh deploy      Build completo + push a main"
    echo "  ./bestore-dev.sh backup      Backup de la base de datos"
    echo "  ./bestore-dev.sh restore f   Restaurar backup"
    echo "  ./bestore-dev.sh dolar       Sincronizar dólar blue"
    echo "  ./bestore-dev.sh migrate     Ejecutar migraciones"
    echo "  ./bestore-dev.sh fresh       Reiniciar BD (¡borra todo!)"
    echo "  ./bestore-dev.sh status      Ver estado del sistema"
    echo "  ./bestore-dev.sh logs        Ver logs de errores"
    ;;
esac
