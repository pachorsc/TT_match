# Configuración del Scheduler en Hostinger

## 1. Verificar que el comando funciona

```bash
cd /home/u123456789/domains/tudominio.com/public_html
php artisan wtt:sync-matches 3242
```

## 2. Agregar cron job en cPanel

1. Entra a **cPanel** → **Cron Jobs**
2. Elige `Once Per Hour` (o configura: `0 * * * *`)
3. En **Command** pega:

```
cd /home/u123456789/domains/tudominio.com/public_html && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1
```

**Nota:** Reemplaza la ruta por la real de tu proyecto en Hostinger.

> Si Hostinger solo permite cada 5 minutos no hay problema: `php artisan schedule:run` no ejecuta nada si el comando no debe correr en ese minuto. No genera sobrecarga.
