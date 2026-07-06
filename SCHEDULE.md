# Configuración del Scheduler

Este proyecto usa el planificador de Laravel para tareas recurrentes de importación de datos.

## Comandos Disponibles

| Comando | Descripción |
|---|---|
| `php artisan wtt:sync-matches {tournamentId}` | Sincronizar partidos de un torneo desde la WTT |
| `php artisan wtt:import-ranking` | Importar rankings desde la API Gateway de la WTT |

## Configuración

### 1. Verificar que el comando funciona

```bash
php artisan wtt:sync-matches 3242
php artisan wtt:import-ranking --gender men --limit 100
```

### 2. Configurar cron job

Agrega la siguiente entrada al crontab de tu servidor:

```cron
* * * * * cd /ruta/a/tu/proyecto && php artisan schedule:run >> /dev/null 2>&1
```

Esto se ejecuta cada minuto. El planificador de Laravel determina qué tareas ejecutar según la programación definida en `app/Console/Kernel.php`.

### 3. Verificar

Revisa los logs o ejecuta:

```bash
php artisan schedule:list
```

> **Nota:** `schedule:run` de Laravel es seguro para ejecutar cada minuto — solo ejecuta tareas cuyo tiempo programado haya vencido. Ejecutarlo con mayor frecuencia (ej. cada 5 minutos) no causará ejecuciones duplicadas.

## Notas para Producción

- Reemplaza `/ruta/a/tu/proyecto` con la ruta real en tu servidor
- Asegúrate de que el usuario del cron tenga permisos de escritura en `storage/logs/`
- Configura `APP_ENV=production` y `APP_DEBUG=false` en `.env` para producción
