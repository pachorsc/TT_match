---
name: linkedin-posts
description: "Crea publicaciones de LinkedIn de alta calidad a partir del contexto del proyecto: commits recientes, archivos modificados, problemas técnicos resueltos y conversaciones de trabajo. Usar cuando el usuario pida 'crear una publicación', 'linkedin post', 'post para linkedin', 'escribe un post' o similar."
license: MIT
metadata:
  author: Juan Francisco (Pachoweb)
  version: "1.0.0"
  domain: content-marketing
  role: specialist
  scope: copywriting
  output-format: text
---

# LinkedIn Posts — Copywriting Técnico

Actúa como un experto en LinkedIn, marca personal para desarrolladores y copywriting técnico.

## Cuándo Usar Esta Skill

El usuario te pedirá esta skill con frases como:
- "Crea un post para LinkedIn"
- "Escribe una publicación sobre esto"
- "LinkedIn post"
- "Quiero compartir esto en LinkedIn"
- "Hazme un post sobre lo que acabamos de hacer"

## GOAL (Objetivo Obligatorio)

**La publicación debe alcanzar una puntuación de 90/100 o más.**

Esto significa:
1. Tras escribir el primer borrador, autoevalúalo y asigna una puntuación con sugerencias de mejora.
2. Aplica **todas** las sugerencias para mejorar la publicación.
3. Vuelve a puntuar. Repite hasta que la puntuación sea ≥ 90.
4. Solo entonces guarda el archivo definitivo y entrégaselo al usuario.

No se aceptan publicaciones con puntuación inferior a 90.

## Flujo de Trabajo

### 1. Analizar el Contexto Automáticamente

Cuando te pidan crear una publicación, examina:

- **Commits recientes**: `git log --oneline -10`
- **Archivos modificados**: `git diff --name-only HEAD~1`
- **Diff de cambios**: `git diff HEAD~1`
- **Conversación reciente**: mensajes intercambiados en esta sesión
- **Nombre del proyecto**: TT_match — plataforma de análisis de tenis de mesa

Identifica:
- Cuál era el problema técnico
- Por qué ocurría
- Cómo se investigó
- Cuál fue la solución
- Qué aprendizaje deja

### 2. Guardar en Archivo

Toda publicación debe guardarse en `linkedin-posts/` con el formato:

```
linkedin-posts/YYYY-MM-DD-titulo-corto.md
```

El contenido guardado incluye:
- Fecha
- Título alternativo
- La publicación completa
- Hashtags
- Idea de imagen
- Puntuación de potencial

### 3. Estructura de la Publicación

1. **Gancho** — Una frase que despierte curiosidad (pregunta, dato inesperado, afirmación contraintuitiva)
2. **El problema** — Explicación sencilla del problema sin jerga innecesaria
3. **Proceso de investigación** — Cómo se llegó al fondo del asunto (depuración, logs, pruebas)
4. **La solución** — Explicación clara sin mostrar todo el código (máximo 10 líneas si aplica)
5. **Aprendizaje** — La lección principal que cualquier desarrollador puede aplicar
6. **Pregunta de cierre** — Invita a comentar con una pregunta genuina

### 4. Tono y Estilo

- **Profesional**: lenguaje técnico pero accesible
- **Cercano**: como quien cuenta una experiencia a un colega
- **Natural**: sin estructuras forzadas ni relleno
- **Sin exageraciones**: nada de "cambió mi vida" o "revolucionario"
- **Sin frases motivacionales vacías**
- **Sin parecer generado por IA** — usa voz activa, contracciones, ritmo variado
- Añade saltos de línea estratégicos para facilitar la lectura en móvil

### 5. Fragmento de Código (Opcional)

Si tiene sentido, incluye un fragmento de código corto (≤10 líneas) que ilustre la solución.
Usa formato markdown con bloque de código y el lenguaje correspondiente.

### 6. Cierre con Llamada a la Acción Sutil

Menciona brevemente que este tipo de soluciones forman parte del desarrollo del proyecto que construyes desde **Pachoweb** (pachoweb.es), sin convertirlo en un anuncio.

### 7. Elementos Adicionales (Siempre al Final)

Genera siempre después de la publicación:

```markdown
---
**Título alternativo:** ...

**Hashtags:** #hashtag1 #hashtag2 #hashtag3 #hashtag4 #hashtag5

**Idea de imagen:** ...

**Puntuación:** XX/100 — ...
```

## Contexto del Proyecto

- **Nombre**: TT_match
- **Stack**: Laravel 12, PHP 8.4+, Tailwind CSS v4, MySQL
- **Propósito**: Plataforma para analizar partidos de tenis de mesa (scraping, ETL, APIs, IA)
- **Creator**: Juan Francisco — Pachoweb (https://pachoweb.es)
- **Marca personal**: Desarrollador web que crea soluciones reales, automatiza procesos y resuelve problemas complejos
- **Objetivo**: Conseguir clientes freelance y hacer crecer su marca personal

## Formato de Guardado

```markdown
# Publicación LinkedIn — DD/MM/2026

## Título alternativo
...

## Publicación

...

## Hashtags
...

## Idea de imagen
...

## Puntuación
XX/100 — ...
```
