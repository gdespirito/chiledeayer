# Place Picker con Google Maps — Design Doc

## Goal

Permitir a usuarios asignar ubicacion a fotos buscando en places existentes, o agregando nuevos places via Google Maps con mapa de confirmacion.

## Context

- `Place` ya tiene `latitude`, `longitude`, `google_place_id` en la tabla
- Google Places autocomplete ya esta integrado como proxy en el backend (`/api/google-places/autocomplete` y `/details`)
- Leaflet ya se usa en el mapa global (`/map`)
- Actualmente el formulario de Create no tiene UI de seleccion de lugar, y Show.vue usa una busqueda interna con formulario de sugerencia

## Design

### Componente: `PlacePicker.vue`

Componente reutilizable con dos modos:

**Modo 1 — Busqueda interna:**
- Input autocomplete que busca en `/api/places/search`
- Lista de resultados clickeables
- Boton "+ Agregar lugar nuevo" si no encuentra

**Modo 2 — Crear place con Google:**
- Input autocomplete Google Places (proxy existente en backend)
- Al seleccionar una sugerencia de Google, aparece un mini mapa Leaflet con pin draggable para ajustar posicion
- Boton "Confirmar ubicacion" crea el Place y lo asigna

Emite `place_id` (existente o recien creado).

### Backend

- Nuevo endpoint o extension del existente: recibe datos de Google Places, hace `Place::firstOrCreate` por `google_place_id` con `verified: false`
- Nueva migracion: agregar columna `verified` (boolean, default `true` para no romper places existentes)
- Endpoints existentes `metadataUpdate` y `store` se adaptan para recibir datos de Google y crear/reutilizar Place

### Donde se usa

- **Create.vue** — reemplaza el campo `place_id` sin UI actual
- **Show.vue** — reemplaza la busqueda interna + formulario de sugerencia

### Se elimina

- `PlaceSuggestion` model, migracion, controller, form request, tests y ruta
- UI de sugerencia de lugares en Show.vue

### Lo que NO cambia

- Tabla `places` (solo se agrega `verified`)
- Mapa global (`/map`), paginas de `/places/{slug}`, facets de busqueda

### Moderacion

Places creados por usuarios tienen `verified: false`. Son visibles y funcionales, pero un admin puede revisarlos y marcarlos como verificados en el futuro.

## Decisions

- **Leaflet para el mapa** (no Google Maps JS SDK) — ya lo tenemos, gratis, sin limites
- **Google Places API solo para autocomplete/details** — ya integrado como proxy
- **Place::firstOrCreate por google_place_id** — evita duplicados
- **verified: false por default en places de usuario** — moderacion lazy
- **Eliminar PlaceSuggestion** — reemplazado por creacion directa de Places
