# Table Tennis Match Preview

Aplicación web de escritorio-first que muestra información objetiva de dos jugadores de tenis de mesa antes de un partido. Diseñada para entrenadores, analistas, comentaristas y aficionados.

## Características

- **Inicio** — Resumen con conteo de partidos, jugadores y torneos
- **Previa del Partido** — Vista completa enfrentando jugadores con estadísticas, últimos 7 partidos, historial H2H y vídeos de YouTube
- **Detalle del Partido** — Resultado final con desglose set por set
- **Comparación de Jugadores** — Comparación lado a lado con filtro por género, búsqueda, H2H e histórico de ranking
- **Vídeos de YouTube** — Buscar y explorar vídeos del canal oficial de la WTT por jugador
- Datos importados desde las APIs de ITTF y WTT mediante pipelines automatizados

## Capturas de Pantalla

> *Coming soon — V1 screenshots will be added after initial deployment*

## Requisitos Previos

| Herramienta | Versión |
|---|---|
| PHP | 8.4+ |
| Composer | 2.x |
| MySQL | 8.x+ |
| Node.js | 18+ |
| npm | 9+ |
| Python | 3.9+ (solo para herramientas de scraping) |

## Instalación

### 1. Clonar el repositorio

```bash
git clone https://github.com/pachorsc/TT_match.git
cd TT_match
```

### 2. Instalar dependencias PHP

```bash
composer install
```

### 3. Instalar dependencias Node.js

```bash
npm install
```

### 4. Crear archivo de entorno

```bash
cp .env.example .env
```

### 5. Generar clave de aplicación

```bash
php artisan key:generate
```

### 6. Configurar la base de datos en `.env`

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tt_match
DB_USERNAME=root
DB_PASSWORD=
```

### 7. Crear la base de datos

```bash
mysql -u root -p -e "CREATE DATABASE tt_match CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 8. Configurar claves API en `.env`

```
YOUTUBE_API_KEY=tu_clave_de_youtube_aqui
```

> **YouTube Data API v3**: Crea un proyecto en https://console.cloud.google.com/apis/credentials, habilita la YouTube Data API v3 y genera una clave API. Sin esto, la búsqueda de vídeos devolverá resultados vacíos (el resto de la app funcionará igual).

```
WTT_API_KEY=tu_clave_api_de_wtt
WTT_SEC_API_KEY=tu_clave_secundaria_de_wtt
```

> **API Gateway de la WTT**: Estas claves se encuentran en el código fuente del sitio web de World Table Tennis (worldtabletennis.com). Son necesarias para importar rankings. Puedes obtenerlas inspeccionando las peticiones de red en el sitio web.

### 9. Ejecutar migraciones y seeders

```bash
php artisan migrate --seed
```

### 10. Compilar assets del frontend

```bash
npm run build
```

### 11. Iniciar el servidor de desarrollo

```bash
php artisan serve
```

Visita `http://localhost:8000` en tu navegador.

## Importar Datos Reales

Los seeders proporcionan datos de ejemplo. Para poblar la base de datos con rankings reales:

### Rankings WTT (World Table Tennis)

```bash
# Top 100 individual masculino
php artisan wtt:import-ranking --gender men --limit 100

# Top 100 individual femenino
php artisan wtt:import-ranking --gender women --limit 100
```

### Datos del Portal ITTF (partidos, perfiles)

Requiere una cuenta en el portal de resultados de ITTF (para scraping del historial de partidos):

```bash
cd tools/ittf
pip install -r requirements.txt
python ittf.py login --username TU_USUARIO --password TU_CONTRASEÑA
python ittf.py fetch rankings --gender men
python ittf.py fetch rankings --gender women
```

### Partidos WTT

```bash
cd tools/wtt_matches
# Consulta los scripts individuales para su uso
```

## Ejecutar Tests

```bash
php artisan test
```

## Estilo de Código

```bash
./vendor/bin/pint --test
```

## Compilación del Frontend

```bash
npm run build
```

## Documentación

| Archivo | Descripción |
|---|---|
| [PROJECT.md](PROJECT.md) | Visión general del proyecto y características |
| [TECH_STACK.md](TECH_STACK.md) | Stack tecnológico y restricciones |
| [DOMAIN.md](DOMAIN.md) | Modelo de dominio y relaciones |
| [CODING_STANDARDS.md](CODING_STANDARDS.md) | Estándares y convenciones de código |
| [UI_GUIDELINES.md](UI_GUIDELINES.md) | Guías de diseño de UI |
| [ROADMAP.md](ROADMAP.md) | Hoja de ruta de desarrollo |
| [SCHEDULE.md](SCHEDULE.md) | Configuración de cron jobs |

## Tecnologías

| Capa | Tecnología |
|---|---|
| Backend | PHP 8.4+, Laravel 12 |
| Frontend | Laravel Blade, Tailwind CSS v4 |
| Base de datos | MySQL |
| Iconos | Heroicons |
| Scripts | Python (scraping, ETL, automatización) |

## Estructura del Proyecto

```
TT_match/
├── app/
│   ├── Console/              # Comandos de Artisan
│   ├── Http/                 # Controladores, Middleware, Form Requests
│   ├── Models/               # Modelos Eloquent
│   └── Services/             # Lógica de negocio
├── config/                   # Archivos de configuración de Laravel
├── database/
│   ├── factories/            # Fábricas de modelos
│   ├── migrations/           # Migraciones de base de datos
│   └── seeders/              # Pobladores de base de datos
├── resources/
│   ├── css/                  # Hojas de estilo
│   ├── js/                   # JavaScript
│   └── views/                # Plantillas Blade
├── routes/                   # Definiciones de rutas
├── tests/                    # Tests PHPUnit
├── tools/                    # Scripts Python (scraping, ETL)
│   ├── ittf/                 # Scraping e importación de datos ITTF
│   ├── wtt_ranking/          # Scraper de rankings WTT
│   └── wtt_matches/          # Scraper de partidos WTT
├── .env.example              # Plantilla de variables de entorno
├── AGENTS.md                 # Instrucciones para agentes AI
├── CODING_STANDARDS.md       # Estándares de código
├── DOMAIN.md                 # Modelo de dominio
├── LICENSE                   # Licencia MIT
├── PROJECT.md                # Visión general del proyecto
├── README.md                 # Este archivo
├── ROADMAP.md                # Hoja de ruta
├── SCHEDULE.md               # Configuración de cron jobs
├── TECH_STACK.md             # Stack tecnológico
└── UI_GUIDELINES.md          # Guías de diseño de UI
```

## Contribuir

1. Lee toda la documentación del proyecto antes de hacer cambios
2. Crea una rama de funcionalidad desde main
3. Sigue los estándares PSR-12
4. Mantén los controladores delgados, usa servicios para la lógica de negocio
5. Usa Blade Components para elementos UI reutilizables
6. Escribe tests para las clases de servicio
7. Ejecuta `php artisan test` antes de enviar
8. Ejecuta `./vendor/bin/pint --test` para verificar el estilo de código
9. Ejecuta `npm run build` para verificar que el frontend compila
10. Envía un pull request

## Licencia

MIT — consulta [LICENSE](LICENSE) para más detalles.
