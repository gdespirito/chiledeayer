---
shaping: true
---

# Archivo de Chile — Shaping

## Decision Log

Decisiones tomadas durante el shaping, con contexto para referencia futura.

### DL1: Ubicación aproximada via Google Places autocomplete
**Opciones consideradas:**
- A) Área libre en el mapa (polígono/círculo dibujado por el usuario)
- B) Jerarquía administrativa (País > Región > Comuna seleccionable)
- C) Ambas

**Decisión:** Usar Google Places autocomplete. El usuario escribe (ej: "Providencia") y el autocompletado resuelve si es un área o un punto preciso. Google devuelve el tipo (locality, administrative_area, street_address, etc.) y el bounding box, lo que permite distinguir automáticamente entre ubicación precisa y aproximada sin lógica adicional.

**Razón:** Más natural que seleccionar de una jerarquía manual. Google ya resuelve la jerarquía (comuna, ciudad, región, país) y provee los boundaries. Evita mantener un catálogo propio de divisiones administrativas.

### DL2: Tagging de personas — Híbrido con catálogo
**Opciones consideradas:**
- A) Catálogo cerrado de personajes públicos + "desconocido"
- B) Etiqueta libre, confianza por consenso
- C) Catálogo de personajes públicos (crece colaborativamente) + etiqueta libre + "desconocido"

**Decisión:** C — Híbrido.

**Razón:** El catálogo de personajes públicos da consistencia (evita "Allende" vs "Salvador Allende" vs "Pdte. Allende"). La etiqueta libre permite cubrir personas menos conocidas sin burocracia. "Desconocido" con descripción opcional ("hombre con sombrero") para cuando no se sabe quién es.

### DL3: Almacenamiento de imágenes — Cloudflare R2
**Opciones consideradas:**
- A) S3/MinIO
- B) Storage local en K8s (persistent volumes)
- C) Cloudflare R2

**Decisión:** C — Cloudflare R2.

**Razón:** Object storage S3-compatible sin egress fees. Ideal para servir imágenes públicas con alto tráfico de lectura. Compatible con el SDK de S3 de Laravel (Flysystem).

### DL4: Comparación temporal — Metadata ahora, Street View después + fotos colaborativas del "ahora"
**Opciones consideradas:**
- A) Solo metadata descriptiva (orientación, coordenadas)
- B) Comparación lado a lado con Street View automática
- C) Metadata ahora, Street View después

**Decisión:** C + soporte para subir fotos del "ahora" colaborativamente.

**Razón:** La comparación con Street View requiere la API de Google Street View (con costo). Mejor guardar heading/pitch desde el inicio para habilitarlo después. Mientras tanto, los usuarios pueden subir sus propias fotos actuales del mismo lugar como comparación colaborativa.

### DL5: Wizard de carga — Lote con metadata compartida + edición individual
**Opciones consideradas:**
- A) Carga en lote con plantilla (metadata se aplica a todas)
- B) Carga una a una con "copiar de la anterior"
- C) Carga en lote + edición individual

**Decisión:** C.

**Razón:** Es el flujo más natural: "estas 15 fotos son todas de Calle Ahumada, 1940" → defines metadata compartida → el wizard te lleva foto por foto para lo específico (descripción, personas). Combina eficiencia con detalle.

### DL6: API — Keys por usuario, sin moderación
**Opciones consideradas:**
- A) API key de admin con cola de moderación
- B) API keys por usuario, fotos entran directo
- C) Solo API de lectura

**Decisión:** B — API keys por usuario via Sanctum. Las fotos entran directo al catálogo sin moderación.

**Razón:** El usuario quiere usar bots/IA para recopilar fotos de fuentes públicas. Cada usuario (incluido el admin) genera sus propios tokens desde su perfil. Las fotos quedan asociadas al usuario que las subió. Similar a como Jetstream maneja tokens, pero construido sobre Sanctum directamente.

### DL7: Comentarios y colaboración — Sistemas separados
**Opciones consideradas:**
- A) Separados: comentarios = conversación, metadata = campos estructurados editables
- B) Unificados: comentarios como mecanismo de colaboración
- C) Separados con sistema de sugerencias/votación

**Decisión:** A — Separados.

**Razón:** Más simple. La metadata queda limpia y estructurada desde el inicio. Los comentarios sirven para discusión libre. Cualquier usuario registrado puede editar campos de metadata directamente (wiki abierto), sin necesidad de un sistema de sugerencias/votación que añade complejidad.

### DL8: Permisos de edición — Wiki abierto con historial y notificación al autor
**Opciones consideradas:**
- A) Wiki abierto: cualquier registrado edita, historial de cambios para revertir
- B) Solo autor + admins
- C) Wiki abierto con prioridad del autor

**Decisión:** A + notificación al autor cuando alguien edita su foto.

**Razón:** El objetivo es colaborativo — lo importante es enriquecer la información. El historial de cambios (audit log) permite revertir vandalismo sin añadir burocracia. La notificación al autor le da visibilidad de los cambios sin bloquearlos.

### DL9: Mapa — Vista alternativa con slider temporal
**Opciones consideradas:**
- A) Mapa de exploración (pins/clusters)
- B) Mapa + línea de tiempo (slider)
- C) Mapa como vista alternativa, slider temporal para después

**Decisión:** C pero con slider temporal incluido desde el inicio.

**Razón:** El mapa es una vista alternativa a la galería principal. Incluye slider temporal desde el inicio porque es parte integral de la experiencia de exploración histórica.

### DL10: Fuente/crédito — Campo opcional
**Opciones consideradas:**
- A) Obligatorio
- B) Opcional
- C) No por ahora

**Decisión:** B — Campo opcional.

**Razón:** Importante para respeto a las fuentes y eventualmente temas de derechos, pero hacerlo obligatorio frenaría las contribuciones.

### DL11: Downvotes — Solo afectan visibilidad, no puntos
**Opciones consideradas:**
- A) Downvote resta puntos al autor
- B) Downvote solo afecta ranking/visibilidad
- C) Sin downvotes

**Decisión:** B — Solo afectan visibilidad.

**Razón:** Los downvotes con penalización de puntos generan problemas de abuso en comunidades abiertas. El downvote influye en el ordenamiento, pero los puntos del autor son siempre positivos (solo upvotes dan puntos).

### DL12: Puntos — Fijos por tipo de acción
**Opciones consideradas:**
- A) Fijos por tipo de acción
- B) Variables por contexto
- C) Fijos ahora, variables después

**Decisión:** A — Fijos por tipo de acción.

**Razón:** Simple y predecible. Si se necesita algo como "más puntos por primera foto de un lugar", se crea un nuevo tipo de acción con sus propios puntos fijos. Cada tipo de acción (`PointAction`) tiene un valor fijo configurable.

### DL13: Niveles — Tabla en base de datos
**Opciones consideradas:**
- A) Umbrales en tabla de DB (name, min_points, icon)
- B) Calculados por fórmula
- C) Hardcodeados en código

**Decisión:** A — Tabla en DB.

**Razón:** Flexibilidad para ajustar nombres y umbrales sin deploy. Simple de implementar.

### DL14: Badges — Clases handler automáticas
**Opciones consideradas:**
- A) Automático con criterios en clases PHP (interfaz BadgeCriteria)
- B) Automático con criterios en base de datos (reglas configurables)
- C) Manual por admin

**Decisión:** A — Clases handler.

**Razón:** Cada badge es una clase que implementa una interfaz `BadgeCriteria`, fácil de testear y de agregar nuevas. El reprocesamiento idempotente encaja: se corren todos los badge handlers para un usuario y se otorgan los que correspondan sin duplicar.

### DL15: Precisión de fecha — Rango con precisión
**Opciones consideradas:**
- A) Rango con precisión: `year_from`, `year_to`, `date_precision` (exact, circa, decade, range, unknown)
- B) Solo año con flag de aproximado
- C) Texto libre + año estimado

**Decisión:** A — Rango con precisión.

**Razón:** Permite que el slider temporal del mapa funcione correctamente (una foto "circa 1920" aparece en el rango 1915-1925). Es queryable sin texto libre. Ejemplos: "1943" → from=1943, to=1943, precision=exact. "1920s" → from=1920, to=1929, precision=decade. "circa 1920" → from=1915, to=1925, precision=circa.

### DL16: Búsqueda — Laravel Scout con Algolia
**Opciones consideradas:**
- A) Búsqueda simple con LIKE/full-text de la DB
- B) Motor de búsqueda externo (Meilisearch/Algolia) via Laravel Scout
- C) Simple ahora, motor después

**Decisión:** B — Laravel Scout con Algolia (o Meilisearch como fallback local).

**Razón:** Algolia ofrece búsqueda rápida con typo-tolerance, faceted search (por lugar, época, persona), y relevancia configurable. Laravel Scout abstrae el driver, así que el código es el mismo independientemente del backend. Permite buscar por descripción, nombre de lugar, nombre de persona desde un solo input.

### DL17: Feed/Home — Landing curada con secciones
**Opciones consideradas:**
- A) Feed cronológico (más recientes primero)
- B) Feed por relevancia (mezcla de recientes, populares, sin metadata)
- C) Landing curada con secciones: "Recién agregadas", "Más populares", "Necesitan tu ayuda", "Foto del día" + feed infinito

**Decisión:** C — Landing curada con secciones.

**Razón:** Para un archivo colaborativo, mostrar fotos que necesitan ayuda incentiva la colaboración. La variedad de secciones hace la home más interesante que un feed plano y da múltiples puntos de entrada al contenido.

### DL18: Notificaciones — In-app + email, sin preferencias
**Opciones consideradas:**
- A) Solo in-app (campanita)
- B) Solo email
- C) Ambas con preferencias configurables por tipo

**Decisión:** In-app + email siempre, sin panel de preferencias.

**Razón:** Simple. Laravel Notifications soporta múltiples canales nativamente (`database` + `mail`). Sin preferencias evita UI adicional. Si en el futuro se necesita, agregar preferencias es solo un middleware sobre el canal de email.

### DL19: Administración — Moderación básica inline, panel después
**Opciones consideradas:**
- A) Moderación básica inline (botones solo visibles para admin)
- B) Panel de admin separado
- C) Básica ahora, panel después

**Decisión:** C — Moderación inline con campo `is_admin` en User + policies de Laravel. Panel después.

**Razón:** Un panel de admin completo es mucho trabajo para el inicio. Con policies y un campo booleano, las acciones de moderación (borrar fotos, borrar comentarios, banear usuarios) funcionan como botones inline visibles solo para admins.

### DL20: Tags temáticos — Tags libres con autocompletado
**Opciones consideradas:**
- A) Tags libres con autocompletado de tags existentes
- B) Taxonomía cerrada administrada por admins
- C) No por ahora

**Decisión:** A — Tags libres con autocompletado.

**Razón:** Simples de implementar (tabla `tags` + pivot), enriquecen la búsqueda en Algolia, y al ser wiki abierto cualquiera puede tagear. El autocompletado de tags existentes naturalmente converge a una taxonomía orgánica sin necesidad de administración manual.

### DL21: Duplicados — Detección por pHash + reporte manual
**Opciones consideradas:**
- A) Detección automática por perceptual hash (pHash) al subir, advierte sin bloquear
- B) Reporte manual por la comunidad
- C) Sin control

**Decisión:** A + B — Ambas.

**Razón:** El pHash es barato de calcular (se hace en el job de procesamiento de imágenes), detecta fotos iguales incluso con diferente resolución o recorte, y no bloquea la carga — solo advierte al usuario. El reporte manual complementa para casos que el hash no detecte (ej: misma escena desde ángulo ligeramente diferente).

### DL22: Edición de Places — Solo admins
**Decisión:** Solo admins pueden editar Places existentes (corregir coordenadas, nombre, etc.). Los usuarios registrados pueden crear nuevos Places o asociar fotos a Places existentes, pero no modificarlos.

**Razón:** Los Places son entidades compartidas — una edición incorrecta afecta todas las fotos asociadas. Mejor que solo admins puedan corregir.

### DL23: Promoción de personas a "pública" — Solo admins
**Decisión:** Solo admins pueden marcar una persona como "pública" (canónica). Cualquier registrado puede crear personas con etiqueta libre, pero la promoción a personaje público (con slug y nombre canónico) requiere admin.

**Razón:** El catálogo de personajes públicos necesita consistencia (evitar duplicados, nombres correctos). Admin actúa como curador.

### DL24: Foto del día — Automática
**Decisión:** Automática, basada en la foto más votada del día/semana que no haya sido foto del día antes.

**Razón:** Sin carga manual para admins. Se puede refinar el algoritmo después.

### DL25: Notificaciones — Todos los eventos relevantes
**Decisión:** Notificar al usuario cuando: editan metadata de su foto, comentan en su foto, votan su foto, lo taggean en una foto. Todas in-app + email.

**Razón:** El usuario necesita saber qué pasa con su contenido para mantenerse enganchado.

### DL26: Perfiles de usuario — Públicos
**Decisión:** Los perfiles son públicos. Cualquier visitante puede ver las fotos, puntos, nivel y badges de un usuario.

**Razón:** La transparencia incentiva la colaboración y el reconocimiento. Los puntos y badges pierden valor si nadie más los ve.

### DL27: Revertir ediciones — Historial visible, revert futuro
**Decisión:** El audit log es visible (se puede ver qué cambió y quién lo cambió). No hay botón de revertir por ahora, pero el modelo de datos (old/new values en revisions) lo soporta para el futuro.

**Razón:** Ver el historial cubre el 80% del caso de uso (saber qué pasó). El revert automático agrega complejidad de UI que no es prioritaria.

---

## Requirements (R)

| ID | Requirement | Status |
|----|-------------|--------|
| **R0** | Plataforma pública de archivo fotográfico histórico de Chile con metadata rica y exploración geográfica/temporal | Core goal |
| **R1** | **Gestión de imágenes** | Must-have |
| R1.1 | Carga en lote con metadata compartida (lugar, época) + edición individual por foto (descripción, personas). Wizard guiado. Máximo 50 fotos por batch; más via API (DL5) | Must-have |
| R1.2 | Almacenamiento en Cloudflare R2 con múltiples resoluciones: thumbnail, medium, original (DL3) | Must-have |
| R1.3 | Campo opcional de fuente/crédito de la imagen (DL10) | Must-have |
| R1.4 | 🟡 Precisión de fecha: `year_from`, `year_to`, `date_precision` (exact, circa, decade, range, unknown). Soporta "1943", "circa 1920", "1920s", "entre 1930-1940" (DL15) | Must-have |
| R1.5 | 🟡 Detección de duplicados por perceptual hash (pHash) al subir — advierte sin bloquear + reporte manual de duplicados por la comunidad (DL21) | Must-have |
| R1.6 | 🟡 Tags temáticos libres con autocompletado de tags existentes (DL20) | Must-have |
| **R2** | **Geolocalización** | Must-have |
| R2.1 | Ubicación precisa: coordenadas (lat/lng) + orientación de cámara (heading/pitch) para fotos de calles/exteriores. Datos listos para futura integración con Street View (DL4) | Must-have |
| R2.2 | Ubicación aproximada: Google Places autocomplete que distingue automáticamente entre punto preciso y área (comuna, barrio, ciudad) según el tipo devuelto por la API (DL1) | Must-have |
| R2.3 | Al marcar ubicación de una nueva foto, recomendar lugares (`Place`) previamente creados en la plataforma | Must-have |
| **R3** | **Personas y tagging** | Must-have |
| R3.1 | Marcar personas en la foto con coordenadas x/y relativas a la imagen (estilo Facebook tag) | Must-have |
| R3.2 | Catálogo híbrido de personas: personajes públicos (slug, nombre canónico, crece colaborativamente) + etiqueta libre para personas menos conocidas + opción "desconocido" con descripción opcional (DL2) | Must-have |
| **R4** | **Colaboración** | Must-have |
| R4.1 | Wiki abierto: cualquier usuario registrado puede editar metadata de cualquier foto directamente (DL8) | Must-have |
| R4.2 | Historial de cambios: audit log polimórfico que guarda old/new values en cada edición de metadata. Visible para todos. Revert automático diferido a futuro (DL8, DL27) | Must-have |
| R4.3 | Notificaciones in-app + email: cuando editan metadata de tu foto, comentan, votan, o te taggean en una foto (DL8, DL18, DL25) | Must-have |
| R4.4 | Comentarios en fotos: solo usuarios registrados pueden comentar (DL7) | Must-have |
| R4.5 | Usuarios anónimos solo pueden ver (browse, buscar, filtrar, mapa). No pueden comentar, editar, votar ni subir | Must-have |
| R4.6 | Upvotes y downvotes en fotos. Upvotes dan puntos al autor. Downvotes solo afectan ranking/visibilidad, no restan puntos (DL11) | Must-have |
| **R5** | **Exploración** | Must-have |
| R5.1 | Vista de mapa interactivo con pins/clusters de fotos geolocalizadas (DL9) | Must-have |
| R5.2 | Slider temporal en la vista de mapa para filtrar por época (año/década) (DL9) | Must-have |
| R5.3 | Filtrar por lugar: ver todas las fotos tomadas en el mismo `Place` | Must-have |
| R5.4 | 🟡 Búsqueda full-text via Laravel Scout + Algolia: busca en descripción, lugar, personas, tags desde un solo input. Typo-tolerance y faceted search (DL16) | Must-have |
| R5.5 | 🟡 Home curada con secciones: "Recién agregadas", "Más populares", "Necesitan tu ayuda" (fotos sin metadata completa), "Foto del día" (automática, DL24) + feed infinito (DL17) | Must-have |
| **R6** | **API** | Must-have |
| R6.1 | API keys por usuario via Laravel Sanctum, gestionables desde el perfil del usuario (DL6) | Must-have |
| R6.2 | API de lectura pública (sin auth) + escritura autenticada con token. Las fotos subidas via API entran directo sin moderación, asociadas al usuario del token (DL6) | Must-have |
| **R7** | **Comparación temporal** | Nice-to-have |
| R7.1 | Usuarios pueden subir foto del "ahora" vinculada a una foto histórica como comparación colaborativa (DL4) | Nice-to-have |
| R7.2 | Modelo de datos preparado para integración futura con Google Street View: lat, lng, heading, pitch almacenados (DL4) | Nice-to-have |
| **R8** | **Perfil de usuario** | Must-have |
| R8.1 | Ver fotos cargadas por el usuario desde su perfil | Must-have |
| R8.2 | Gestión de API keys (crear, revocar) desde el perfil (DL6) | Must-have |
| **R9** | **Gamificación** | Must-have |
| R9.1 | Sistema de puntos con `PointTransaction` que registra: usuario, tipo de acción, puntos otorgados, referencia polimórfica al objeto que lo gatilló (foto, comentario, edit, etc.) (DL12) | Must-have |
| R9.2 | Tipos de acción puntuable (`PointAction`) con valor fijo configurable. Acciones base: subir imagen, colaborar metadata, subir foto "ahora", recibir upvote, primer comentario. Nuevas acciones se crean como nuevos tipos (DL12) | Must-have |
| R9.3 | Reprocesamiento idempotente: un command puede recalcular todos los puntos y badges desde cero recorriendo las tablas fuente, para poder agregar nuevas acciones puntuables retroactivamente (DL12) | Must-have |
| R9.4 | Niveles basados en puntos acumulados, definidos en tabla de DB (name, min_points, icon). Ajustables sin deploy (DL13) | Must-have |
| R9.5 | Badges con criterios en clases PHP (interfaz `BadgeCriteria`). Evaluación automática tras cada acción relevante. Extensible: agregar badge = agregar clase (DL14) | Must-have |
| R9.6 | Badges otorgan puntos bonus al desbloquearse. Puntos escalonados según dificultad del logro | Must-have |
| 🟡 **R10** | **Administración** | Must-have |
| 🟡 R10.1 | Moderación inline: borrar fotos (soft delete), borrar comentarios, banear usuarios. Acciones visibles solo para admins via campo `is_admin` + policies (DL19) | Must-have |
| 🟡 R10.2 | Admin puede editar Places existentes (corregir coordenadas, nombre). Usuarios normales solo crean o asocian (DL22) | Must-have |
| 🟡 R10.3 | Admin puede promover personas a "pública" (canónica con slug). Usuarios crean con etiqueta libre (DL23) | Must-have |
| 🟡 R10.4 | Panel de admin separado (futuro). Diseñar con policies para que la migración sea agregar vistas, no reestructurar permisos (DL19) | Nice-to-have |
| 🟡 **R11** | **Leaderboard y perfiles públicos** | Must-have |
| 🟡 R11.1 | Perfiles de usuario públicos: cualquier visitante ve fotos, puntos, nivel y badges de un usuario (DL26) | Must-have |
| 🟡 R11.2 | Ranking de usuarios por puntos (leaderboard). Usuarios baneados se excluyen | Must-have |
| 🟡 **R12** | **Seguridad y rate limiting** (de robustness review) | Must-have |
| 🟡 R12.1 | Validación de uploads: tipo MIME real (no solo extensión), límite de tamaño (20MB), procesado con Intervention Image que falla si no es imagen válida. Formatos: JPEG, PNG, WebP. TIFF solo via API | Must-have |
| 🟡 R12.2 | Rate limiting API: throttle por token Sanctum (60 uploads/hora por usuario). Rate limiting en comentarios | Must-have |
| 🟡 R12.3 | Rate limiting en Google Places autocomplete endpoint + cacheo de resultados | Must-have |
| 🟡 R12.4 | Puntos por edición de metadata: una sola vez por foto por usuario. Composite unique (user_id, action_type, actionable_type, actionable_id) en point_transactions | Must-have |
| 🟡 R12.5 | Throttle de notificaciones: máximo 1 email por foto por hora, agrupando cambios del mismo usuario | Must-have |
| 🟡 R12.6 | Pre-signed URLs de R2 con expiración + validación de content-type | Must-have |
| 🟡 R12.7 | Strip EXIF de fotos del "ahora" antes de almacenar (preservar orientación) | Must-have |
| 🟡 R12.8 | Soft delete en fotos: al borrar, ocultar foto + comments + tags. Point_transactions se mantienen (ya otorgados). Usuario baneado: bloquear login (403), ocultar del leaderboard + soft delete de sus fotos | Must-have |
| 🟡 R12.9 | Todas las rutas de contribución (upload, edit, comment, vote) requieren middleware `verified` (email verificado) | Must-have |

### Badges base

| Badge | Criterio | Puntos (relativos) |
|-------|----------|:-------------------:|
| Primera Foto | Subir tu primera imagen | Bajo |
| Primer Comentario | Comentar por primera vez | Bajo |
| Colaborador | Editar metadata de una foto ajena por primera vez | Bajo |
| Cartógrafo | Geolocalizar una foto sin ubicación por primera vez | Bajo |
| Fotógrafo del Presente | Subir tu primera foto "del ahora" | Bajo |
| Archivista | Subir 50 fotos | Medio |
| Historiador | Subir 100 fotos | Alto |
| Popular | Recibir 100 upvotes acumulados | Medio |

*Puntos exactos se definirán en implementación. Historiador > Archivista por ser un logro mayor.*

---

## Shapes

### A: "Servicios directos"

Gamificación vía llamadas explícitas a un `GamificationService` desde controllers/services.

| Part | Mechanism |
|------|-----------|
| **A1** | **Modelo de fotos**: tabla `photos` con columnas explícitas (year, description, heading, pitch, place_id, user_id, source_credit). Nullable donde no aplique |
| **A2** | **Lugares**: modelo `Place` con coordenadas, google_place_id, nombre, tipo (preciso/aproximado), bounding box. Fotos referencian `place_id` |
| **A3** | **Personas**: modelo `Person` (name, type: public/unknown, slug). Tabla pivot `person_photo` con coordenadas x/y del tag en la foto |
| **A4** | **Gamificación directa**: cada controller/service que ejecuta una acción puntuable llama a `GamificationService->award('photo_upload', $user, $photo)`. El service crea el `PointTransaction` y evalúa badges |
| **A5** | **Reprocesamiento**: command `points:recalculate` que borra `point_transactions` del usuario y recorre todas las tablas fuente (photos, comments, edits) recreando transacciones |
| **A6** | **Imágenes R2**: job en cola genera variantes (thumb, medium, original) al subir. URLs almacenadas en tabla `photo_files` |
| **A7** | **Audit log**: tabla `revisions` polimórfica que guarda old/new values en cada edición de metadata |
| **A8** | **Votos**: tabla `votes` (user_id, photo_id, type: up/down). Upvotes generan punto via A4 |
| **A9** | **API Sanctum**: mismos controllers con `->json()` para API, tokens gestionables desde perfil |

### B: "Eventos de dominio" ✅ Seleccionado

Gamificación, notificaciones y efectos secundarios desacoplados via eventos Laravel.

| Part | Mechanism |
|------|-----------|
| **B1** | **Modelo de fotos**: tabla `photos` con columnas explícitas (year_from, year_to, date_precision, description, heading, pitch, place_id, user_id, source_credit, phash). Nullable donde no aplique |
| **B2** | **Lugares**: modelo `Place` con coordenadas (lat/lng), google_place_id, nombre, tipo (precise/approximate), bounding box (ne_lat, ne_lng, sw_lat, sw_lng). Fotos referencian `place_id` |
| **B3** | **Personas**: modelo `Person` (name, type enum: public/unknown, slug, bio). Tabla pivot `person_photo` con coordenadas x/y del tag en la foto + label opcional |
| **B4** | **Eventos de dominio**: cada acción dispara un evento Laravel (PhotoUploaded, MetadataEdited, PhotoVoted, CommentCreated, PersonTagged, etc.). Los eventos son el punto de enganche para todo lo derivado |
| **B5** | **Gamificación via listeners**: listeners escuchan eventos y crean `PointTransaction` + evalúan badges. La lógica de puntos NO está en los controllers |
| **B6** | **Notificaciones via listeners**: listeners de MetadataEdited, CommentCreated, PhotoVoted, PersonTagged envían notificación al dueño de la foto (in-app + email). Desacoplado de la gamificación |
| **B7** | **Reprocesamiento**: command que recorre tablas fuente, dispara los eventos en modo "replay" (flag `$replay = true` para no re-notificar ni re-enviar email), y recalcula puntos/badges desde cero |
| **B8** | **Imágenes R2**: job en cola genera variantes (thumb, medium, original) + calcula pHash para detección de duplicados. URLs almacenadas en tabla `photo_files` |
| **B9** | **Audit log**: tabla `revisions` polimórfica que guarda old/new values en cada edición de metadata |
| **B10** | **Votos**: tabla `votes` (user_id, photo_id, type: up/down). Upvote dispara evento `PhotoVoted` |
| **B11** | **API Sanctum**: mismos controllers con `->json()` para API, tokens gestionables desde perfil |
| **B12** | 🟡 **Tags**: tabla `tags` (name, slug) + pivot `photo_tag`. Autocompletado de tags existentes al editar |
| **B13** | 🟡 **Búsqueda Algolia**: Laravel Scout indexa photos con descripción, lugar, personas, tags. Facets por lugar, época, tags |
| **B14** | 🟡 **Duplicados**: pHash calculado en B8. Al subir, query por Hamming distance < umbral. UI advierte sin bloquear. Reporte manual via tabla `reports` (user_id, photo_id, reason, duplicate_of_id) |
| **B15** | 🟡 **Moderación**: campo `is_admin` en User. Policies Laravel para borrar fotos/comentarios, banear usuarios. Acciones inline en la UI |
| **B16** | 🟡 **Admin Places**: solo admins pueden editar Places existentes (coordenadas, nombre). Usuarios crean o asocian (DL22) |
| **B17** | 🟡 **Admin Personas**: solo admins pueden promover persona a type=public. Usuarios crean con type=unknown o etiqueta libre (DL23) |
| **B18** | 🟡 **Foto del día**: query automática — foto más votada del día/semana que no haya sido foto del día antes. Campo `featured_at` en photos (DL24) |
| **B19** | 🟡 **Perfiles públicos**: perfil de usuario visible para todos con fotos, puntos, nivel, badges (DL26) |
| **B20** | 🟡 **Leaderboard**: ranking de usuarios por puntos totales (cached `total_points` en users) |
| **B21** | 🟡 **Mapa interactivo**: vista de mapa con pins/clusters via query geoespacial (lat/lng de Place). Slider temporal filtra por year_from/year_to. Popup con thumbnail + link (DL9) |
| **B22** | 🟡 **Foto comparación "ahora"**: tabla `comparison_photos` (photo_id FK, user_id, file path en R2). Vincula foto actual a foto histórica. Dispara ComparisonUploaded event |
| **B23** | 🟡 **Seguridad cross-cutting**: validación MIME + Intervention Image (R12.1), rate limiting via throttle middleware (R12.2-R12.3), pre-signed URLs con expiración (R12.6), EXIF stripping (R12.7), middleware `verified` en rutas de contribución (R12.9) |
| **B24** | 🟡 **Ban system**: campo `is_banned` en User. Middleware que retorna 403 si baneado. Soft delete de fotos del baneado. Excluir de leaderboard (R12.8) |
| **B25** | 🟡 **Throttle de notificaciones**: cache key `notif:{photo_id}:{hour}` con TTL 1 hora. Agrupa cambios en una sola notificación por foto por ventana (R12.5) |

---

## Fit Check: R × B (shape seleccionado)

| Req | Requirement | Status | B |
|-----|-------------|--------|---|
| R0 | Plataforma pública de archivo fotográfico histórico de Chile con metadata rica y exploración geográfica/temporal | Core goal | ✅ |
| R1.1 | Carga en lote con metadata compartida + edición individual | Must-have | ✅ |
| R1.2 | Almacenamiento en Cloudflare R2 con múltiples resoluciones | Must-have | ✅ |
| R1.3 | Campo opcional de fuente/crédito | Must-have | ✅ |
| R1.4 | Precisión de fecha: year_from, year_to, date_precision | Must-have | ✅ |
| R1.5 | Detección de duplicados por pHash + reporte manual | Must-have | ✅ |
| R1.6 | Tags temáticos libres con autocompletado | Must-have | ✅ |
| R2.1 | Ubicación precisa con heading/pitch | Must-have | ✅ |
| R2.2 | Ubicación aproximada via Google Places autocomplete | Must-have | ✅ |
| R2.3 | Recomendar lugares previamente creados | Must-have | ✅ |
| R3.1 | Marcar personas en la foto con coordenadas x/y | Must-have | ✅ |
| R3.2 | Catálogo híbrido de personas | Must-have | ✅ |
| R4.1 | Wiki abierto: cualquier registrado edita metadata | Must-have | ✅ |
| R4.2 | Historial de cambios (audit log) | Must-have | ✅ |
| R4.3 | Notificaciones: edición, comentario, voto, tag (in-app + email) | Must-have | ✅ |
| R4.4 | Comentarios en fotos (solo registrados) | Must-have | ✅ |
| R4.5 | Usuarios anónimos solo pueden ver | Must-have | ✅ |
| R4.6 | Upvotes/downvotes, upvotes dan puntos, downvotes solo ranking | Must-have | ✅ |
| R5.1 | Mapa interactivo con pins/clusters | Must-have | ✅ |
| R5.2 | Slider temporal en vista de mapa | Must-have | ✅ |
| R5.3 | Filtrar por lugar | Must-have | ✅ |
| R5.4 | Búsqueda full-text via Scout + Algolia | Must-have | ✅ |
| R5.5 | Home curada con secciones | Must-have | ✅ |
| R6.1 | API keys por usuario via Sanctum | Must-have | ✅ |
| R6.2 | API lectura pública + escritura autenticada sin moderación | Must-have | ✅ |
| R7.1 | Subir foto "del ahora" vinculada a foto histórica | Nice-to-have | ✅ |
| R7.2 | Modelo de datos listo para Street View futuro | Nice-to-have | ✅ |
| R8.1 | Ver fotos del usuario desde su perfil | Must-have | ✅ |
| R8.2 | Gestión de API keys desde perfil | Must-have | ✅ |
| R9.1 | PointTransaction con referencia polimórfica | Must-have | ✅ |
| R9.2 | Tipos de acción puntuable con valor fijo | Must-have | ✅ |
| R9.3 | Reprocesamiento idempotente de puntos y badges | Must-have | ✅ |
| R9.4 | Niveles en tabla de DB | Must-have | ✅ |
| R9.5 | Badges con clases handler (BadgeCriteria) | Must-have | ✅ |
| R9.6 | Badges otorgan puntos bonus escalonados | Must-have | ✅ |
| R10.1 | Moderación inline (borrar fotos/comentarios, banear usuarios) | Must-have | ✅ |
| R10.2 | Admin puede editar Places existentes | Must-have | ✅ |
| R10.3 | Admin puede promover personas a "pública" | Must-have | ✅ |
| R10.4 | Panel de admin separado (futuro) | Nice-to-have | ✅ |
| R11.1 | Perfiles de usuario públicos | Must-have | ✅ |
| R11.2 | Leaderboard de usuarios por puntos | Must-have | ✅ |

| R12.1 | Validación uploads: MIME real, 20MB, Intervention Image | Must-have | ✅ |
| R12.2 | Rate limiting API (60/hora) y comentarios | Must-have | ✅ |
| R12.3 | Rate limiting Google Places + cacheo | Must-have | ✅ |
| R12.4 | Puntos por edición: una vez por foto por usuario (unique) | Must-have | ✅ |
| R12.5 | Throttle notificaciones email: 1/foto/hora | Must-have | ✅ |
| R12.6 | Pre-signed URLs R2 con expiración | Must-have | ✅ |
| R12.7 | Strip EXIF de fotos "ahora" | Must-have | ✅ |
| R12.8 | Soft delete fotos + ban excluye de leaderboard | Must-have | ✅ |
| R12.9 | Middleware `verified` en rutas de contribución | Must-have | ✅ |

**Shape B seleccionado.** Los controllers solo disparan eventos de dominio. Listeners manejan puntos, badges y notificaciones independientemente. Agregar una nueva consecuencia a una acción = agregar un listener sin tocar el controller. El reprocesamiento idempotente es limpio (replay de eventos con flag).

---

## Detail B: Breadboard

### UI Affordances

| ID | Place | Affordance | Type | Wires Out |
|----|-------|-----------|------|-----------|
| **Gestión de fotos** |
| U1 | Upload Wizard | Drop zone / file picker (múltiples archivos) | Input | → N1 (upload temporal) |
| U2 | Upload Wizard | Formulario metadata compartida: lugar (Google Places autocomplete), rango de fecha, precisión, fuente/crédito, tags | Form | → N2 (buscar lugar), → N5 (buscar tags) |
| U3 | Upload Wizard | Lista de fotos subidas con thumbnail + estado de procesamiento | Display | ← N1 |
| U4 | Upload Wizard | Formulario por foto: descripción, override de lugar/fecha si difiere del lote | Form | → N3 (guardar foto) |
| U5 | Upload Wizard | Aviso de posible duplicado (cuando pHash coincide) | Display | ← N4 (check duplicado) |
| **Detalle de foto** |
| U6 | Photo Detail | Imagen principal con zoom | Display | |
| U7 | Photo Detail | Metadata: descripción, lugar (link), fecha, fuente, tags, autor (link) | Display | |
| U8 | Photo Detail | Botón editar metadata (registrados) → abre formulario inline | Action | → N6 (guardar edición) |
| U9 | Photo Detail | Zona de tags de personas: click en la foto para marcar coordenadas + asignar/quitar persona | Input | → N7 (buscar persona), → N8 (crear/eliminar tag) |
| U10 | Photo Detail | Lista de personas taggeadas con links al perfil de persona | Display | |
| U11 | Photo Detail | Botones upvote / downvote con contador | Action | → N9 (registrar voto) |
| U12 | Photo Detail | Sección de comentarios (solo registrados pueden escribir) | Form + Display | → N10 (crear comentario) |
| U13 | Photo Detail | Botón "Subir foto del ahora" (registrados) | Action | → N11 (upload comparación) |
| U14 | Photo Detail | Foto del "ahora" lado a lado si existe | Display | |
| U15 | Photo Detail | Botón reportar duplicado (registrados) | Action | → N12 (crear reporte) |
| U16 | Photo Detail | Historial de cambios (link o expandible) | Display | ← N13 |
| U17 | Photo Detail | Acciones admin: borrar foto, borrar comentarios (solo is_admin) | Action | → N14 |
| **Exploración** |
| U18 | Home | Sección "Recién agregadas" (últimas N fotos) | Display | ← N15 |
| U19 | Home | Sección "Más populares" (por upvotes) | Display | ← N15 |
| U20 | Home | Sección "Necesitan tu ayuda" (fotos sin metadata completa) | Display | ← N15 |
| U21 | Home | Sección "Foto del día" (automática: más votada que no haya sido destacada) | Display | ← N15 |
| U22 | Home | Feed infinito debajo de secciones | Display | ← N15 |
| U23 | Search | Input de búsqueda global (header) | Input | → N16 (Algolia query) |
| U24 | Search Results | Resultados con facets (lugar, época, tags) | Display | ← N16 |
| U25 | Map View | Mapa interactivo con pins/clusters | Display | ← N17 |
| U26 | Map View | Slider temporal (rango de años) | Input | → N17 (filtrar por época) |
| U27 | Map View | Popup al click en pin: thumbnail + título + link a detalle | Display | |
| U28 | Place Detail | Listado de fotos del mismo lugar + mapa centrado | Display | ← N18 |
| U29 | Place Detail | Acciones admin: editar nombre, coordenadas, tipo del Place | Action | → N30 (solo is_admin) |
| **Personas** |
| U30 | Person Detail | Perfil de persona: nombre, tipo (público/desconocido), bio, fotos donde aparece | Display | ← N19 |
| U31 | Person Detail | Acción admin: promover a "pública" (asignar slug, nombre canónico) | Action | → N31 (solo is_admin) |
| **Perfil de usuario** |
| U32 | User Profile | Mis fotos subidas (grid paginado) — perfil público visible para todos | Display | ← N20 |
| U33 | User Profile | Estadísticas: puntos, nivel, badges | Display | ← N21 |
| U34 | User Profile (propio) | Gestión de API keys: crear, ver, revocar tokens | Form + Display | → N22 |
| U35 | User Profile (propio) | Notificaciones: campanita con contador + panel desplegable | Display | ← N23 |
| **Gamificación** |
| U36 | Photo Detail / Profile | Badge desbloqueado: toast/modal al obtener un badge nuevo | Display | ← Event |
| U37 | User Profile | Lista de badges obtenidos + progreso hacia los siguientes | Display | ← N21 |
| U38 | Leaderboard | Ranking de usuarios por puntos | Display | ← N24 |
| **Admin** |
| U39 | User Profile (admin view) | Botón banear usuario | Action | → N14 |

### Non-UI Affordances

| ID | Affordance | Type | Wires Out |
|----|-----------|------|-----------|
| **Fotos** |
| N1 | Upload temporal a R2 (pre-signed URL) | Service | → R2 bucket |
| N2 | Buscar/crear Place (Google Places API + DB local) | Service | → Google API, → DB places |
| N3 | Guardar foto: crear Photo + asociar Place + tags + dispatch PhotoUploaded | Action | → DB, → Event |
| N4 | Check duplicado por pHash (Hamming distance) | Query | → DB photos.phash |
| N5 | Buscar tags existentes (autocompletado) | Query | → DB tags |
| N6 | Guardar edición de metadata + crear Revision + dispatch MetadataEdited | Action | → DB, → Event |
| N7 | Buscar persona (autocompletado: públicas primero, luego libres) | Query | → DB persons |
| N8 | Crear/eliminar person tag en foto (coordenadas x/y + person_id) + dispatch PersonTagged | Action | → DB, → Event |
| N9 | Registrar voto (upsert) + dispatch PhotoVoted | Action | → DB, → Event |
| N10 | Crear comentario + dispatch CommentCreated | Action | → DB, → Event |
| N11 | Upload foto "ahora" vinculada a foto histórica + dispatch ComparisonUploaded | Action | → R2, → DB, → Event |
| N12 | Crear reporte de duplicado | Action | → DB reports |
| N13 | Listar revisiones de una foto (audit log) | Query | → DB revisions |
| N14 | Admin: soft delete foto / borrar comentario / banear usuario | Action | → DB |
| **Exploración** |
| N15 | Queries home: recientes, populares, incompletas, foto del día (featured_at) | Query | → DB photos |
| N16 | Búsqueda Algolia via Scout | Query | → Algolia index |
| N17 | Query geoespacial: fotos por bounding box + rango de años | Query | → DB photos (lat/lng via place) |
| N18 | Fotos por place_id | Query | → DB photos |
| N19 | Persona con fotos donde aparece | Query | → DB persons + person_photo |
| **Usuario** |
| N20 | Fotos de un usuario (público) | Query | → DB photos |
| N21 | Stats del usuario: puntos totales, nivel actual, badges | Query | → DB point_transactions, levels, user_badges |
| N22 | CRUD tokens Sanctum | Action | → DB personal_access_tokens |
| N23 | Notificaciones del usuario (database + mark as read) | Query | → DB notifications |
| N24 | Ranking de usuarios por puntos | Query | → DB users (cached total_points) |
| **Eventos y listeners** |
| N25 | Listener: AwardPoints (escucha todos los eventos puntuables) → crea PointTransaction + actualiza total_points | Listener | → DB point_transactions, users |
| N26 | Listener: EvaluateBadges (escucha todos los eventos) → corre BadgeCriteria handlers | Listener | → DB user_badges |
| N27 | Listener: NotifyPhotoOwner (escucha MetadataEdited, PersonTagged, CommentCreated, PhotoVoted) → notification in-app + email | Listener | → DB notifications, → Mail |
| N28 | Job: ProcessPhotoUpload → genera variantes (thumb/medium) + calcula pHash | Job | → R2, → DB photo_files |
| N29 | Command: points:recalculate → replay eventos con flag $replay=true | Command | → Events (replay) |
| **Admin** |
| N30 | Admin: actualizar Place (nombre, coordenadas, tipo) | Action | → DB places |
| N31 | Admin: promover persona a type=public (asignar slug, nombre canónico) | Action | → DB persons |

### Wiring (por Place)

**Upload Wizard:**
```
User drops files → U1 → N1 (upload temporal R2)
                        → N28 (job: genera variantes + pHash)
                        → N4 (check duplicado) → U5 (aviso si match)
User llena metadata compartida → U2 → N2 (buscar/crear Place)
                                    → N5 (buscar tags)
User edita cada foto → U4 → N3 (guardar) → PhotoUploaded event
                                           → N25 (puntos)
                                           → N26 (badges)
```

**Photo Detail:**
```
Visitante ve foto → U6, U7, U10, U14, U16
Registrado edita metadata → U8 → N6 → MetadataEdited event
                                       → N25 (puntos al editor)
                                       → N26 (badges)
                                       → N27 (notifica al autor: in-app + email)
Registrado tagea persona → U9 → N7 (buscar) → N8 → PersonTagged event
                                                     → N27 (notifica al autor)
Registrado quita tag → U9 → N8 (eliminar)
Registrado vota → U11 → N9 → PhotoVoted event
                              → N25 (puntos al autor si upvote)
                              → N27 (notifica al autor)
Registrado comenta → U12 → N10 → CommentCreated event
                                  → N25 (puntos)
                                  → N26 (badges)
                                  → N27 (notifica al autor)
Registrado sube foto "ahora" → U13 → N11 → ComparisonUploaded event
                                            → N25 (puntos)
                                            → N26 (badges)
Registrado reporta duplicado → U15 → N12
Admin modera → U17 → N14
```

**Home:**
```
Visitante entra → U18, U19, U20, U21 ← N15 (queries)
Scroll → U22 ← N15 (paginated feed)
```

**Search:**
```
User escribe → U23 → N16 (Algolia) → U24 (resultados + facets)
```

**Map:**
```
User navega mapa → U25 ← N17 (geoespacial por bounding box)
User mueve slider → U26 → N17 (filtra por año)
User click pin → U27 → link a Photo Detail
```

**Place Detail:**
```
Visitante ve lugar → U28 ← N18 (fotos del lugar)
Admin edita lugar → U29 → N30
```

**Person Detail:**
```
Visitante ve persona → U30 ← N19 (persona + fotos)
Admin promueve a pública → U31 → N31
```

**Profile:**
```
Cualquier visitante ve perfil → U32 ← N20, U33 ← N21, U37 ← N21
Owner gestiona tokens → U34 → N22
Owner ve notificaciones → U35 ← N23
Admin banea usuario → U39 → N14
```

**Gamificación (background):**
```
Cualquier evento → N25 (AwardPoints) → PointTransaction + total_points
                 → N26 (EvaluateBadges) → user_badges → U36 (toast)
Admin recalcula → N29 (command) → replay eventos → N25, N26
```

**Leaderboard:**
```
Visitante ve ranking → U38 ← N24 (users por total_points, excluye baneados)
```

---

## Robustness Review

### Security Scan

| ID | Vector | Description | Severity | Mitigated? | By |
|----|--------|-------------|----------|:----------:|----|
| SEC-1 | Upload de archivos maliciosos | Usuarios suben archivos no-imagen, muy grandes, o con payloads | High | ✅ | R12.1: validación MIME real + Intervention Image + límite 20MB |
| SEC-2 | API flood sin rate limiting | Bot sube miles de fotos via API sin límite | High | ✅ | R12.2: throttle 60 uploads/hora por token Sanctum |
| SEC-3 | XSS via metadata | Texto libre en descripción, personas, tags, comentarios | High | ✅ | Vue escapa `{{ }}` por defecto. No usar `v-html` con contenido de usuario |
| SEC-4 | Abuso de Google Places API | Requests masivos al autocomplete generan costos | Medium | ✅ | R12.3: rate limiting en endpoint + cacheo de resultados |
| SEC-5 | Manipulación de votos | Múltiples cuentas para votar sus propias fotos | Medium | Partial | R12.9 exige email verificado. Detección adicional queda fuera de scope |
| SEC-6 | Gaming de puntos por ediciones | Editar mismo campo 100 veces para farmear | Medium | ✅ | R12.4: unique constraint, puntos por edición una vez por foto por usuario |
| SEC-7 | Spam de notificaciones | Ediciones/comentarios rápidos inundan email del autor | Medium | ✅ | R12.5: throttle 1 email/foto/hora, agrupa cambios |
| SEC-8 | IDOR en acciones admin | Usuario normal ejecuta acciones de admin | High | ✅ | Policies Laravel + tests exhaustivos de autorización |
| SEC-9 | Pre-signed URLs de R2 | URLs sin expiración o sin validación | Medium | ✅ | R12.6: expiración + content-type validation |
| SEC-10 | EXIF data leaking | Fotos "ahora" exponen GPS y datos personales | Low | ✅ | R12.7: strip EXIF preservando orientación |
| SEC-11 | Spam de comentarios | Bot spamea comentarios | Medium | ✅ | R12.2: rate limiting en comentarios + R12.9 email verificado |

### Gap Analysis

| ID | Gap | Severity | Resolution |
|----|-----|----------|------------|
| G1 | Soft delete vs hard delete: cascade policy al borrar foto | High | → R12.8: soft delete, ocultar foto + comments + tags. Points se mantienen |
| G2 | Unicidad de point_transactions para idempotencia | High | → R12.4: composite unique key |
| G3 | Revocación de badges al perder criterio | Medium | Accepted: badges no se revocan. Una vez obtenido se mantiene |
| G4 | Deduplicación de notificaciones por email | Medium | → R12.5: throttle por foto/hora |
| G5 | Formatos de imagen aceptados | Medium | Accepted: JPEG, PNG, WebP en upload. TIFF solo via API |
| G6 | Tamaño máximo de batch en wizard | Low | Accepted: límite 50 por batch. Más via API |
| G7 | Indexación Algolia eventual consistency | Low | Accepted: Scout indexa en job de procesamiento |
| G8 | Email verificado antes de contribuir | Medium | → R12.9: middleware `verified` en rutas de contribución |
| G9 | Puntos y leaderboard de usuario baneado | Medium | → R12.8: excluir del leaderboard, soft delete fotos |

### Second Gap Analysis

| ID | Gap | Severity | Resolution |
|----|-----|----------|------------|
| G2-1 | R12.4 unique constraint: al reprocesar (points:recalculate), el command debe borrar transacciones antes de recrear para no chocar con el unique | Low | Accepted: el command hace `DELETE WHERE user_id = ?` antes de replay. Es parte del diseño de R9.3 |
| G2-2 | R12.5 throttle de email: ¿dónde se implementa? Si es en el listener, necesita tracking de "último email por foto" | Low | Accepted: tabla `notification_throttles` o cache key `notif:{photo_id}:{user_id}` con TTL 1 hora |
| G2-3 | R12.8 soft delete + Algolia: fotos soft-deleted deben removerse del índice de Algolia | Medium | → Scout ya soporta esto: `SoftDeletes` trait + config `soft_delete` en Scout. Agregar al modelo |
| G2-4 | R12.8 usuario baneado: ¿puede seguir logueándose? ¿Ve un mensaje de ban? | Medium | Accepted: middleware que chequea `is_banned` y retorna 403 con mensaje. No puede loguearse |

No hay gaps críticos nuevos. Las mitigaciones no introducen riesgos adicionales significativos.

### Automated Audit

**Verdict: PASS WITH NOTES**

Notes resueltas:
- R12.x sin Shape B parts → agregados B23 (seguridad cross-cutting), B24 (ban system), B25 (throttle notificaciones)
- R5.1/R5.2 sin Shape B part → agregado B21 (mapa interactivo)
- R7.1 sin Shape B part → agregado B22 (foto comparación "ahora")
- DL9, DL24, DL27 sin referencia en R → agregadas referencias en R5.1, R5.2, R5.5, R4.2
- G2-4 (ban login block) fuera de R12.8 → actualizado R12.8 para incluir bloqueo de login
- G6 (batch limit 50) sin R → agregado a R1.1

### Gate Check

1. ✅ No quedan findings Critical sin mitigar
2. ✅ No quedan findings High sin mitigar
3. ✅ No quedan gaps Critical sin resolver
4. ✅ Todos los nuevos R's (R12.1-R12.9) están en la tabla y fit check
5. ✅ Todos los riesgos aceptados tienen rationale documentado (G3, G5, G6, G7)
6. ✅ Segunda gap analysis completada
7. ✅ Audit automatizado ejecutado — PASS WITH NOTES, notas resueltas

**Resultado: LISTO PARA SLICING**

---

## Slices

### V1: Foto básica (upload + display + R2)
**Parts:** B1, B8 (parcial)
**Affordances:** U1 (single file), U3, U6, U7, U18 (parcial), N1, N3, N28
**Requirements:** R1.1 (parcial), R1.2, R1.3, R1.4

- Modelo `Photo` con todas las columnas (year_from, year_to, date_precision, description, heading, pitch, place_id, user_id, source_credit, phash)
- Modelo `Place` (estructura base, sin Google Places aún)
- Modelo `Tag` (estructura base)
- Upload single de foto a Cloudflare R2
- Job `ProcessPhotoUpload`: genera variantes (thumb, medium, original)
- Tabla `photo_files` para URLs de variantes
- Página de detalle de foto (display metadata + imagen)
- Home básica con últimas fotos (grid paginado)

**Demo:** "Subo una foto con descripción y fecha, la veo en detalle y en la home"

---

### V2: Lugares + tags
**Parts:** B2, B12
**Affordances:** U2 (parcial), U7, U28, N2, N5, N18
**Requirements:** R2.1, R2.2, R2.3, R1.6, R5.3

- Google Places autocomplete integrado en frontend
- Creación de Place con tipo (precise/approximate), coordenadas (lat/lng), google_place_id, bounding box
- Recomendar Places existentes al asociar ubicación (query por proximidad/nombre)
- Página de detalle de Place (fotos del lugar + mapa centrado en el lugar)
- Tags: tabla `tags` (name, slug) + pivot `photo_tag`, autocompletado, display en foto
- Link a Place y tags desde la página de detalle de foto

**Demo:** "Asocio fotos a lugares con Google Maps, agrego tags, navego por lugar"

---

### V3: Wizard de carga en lote
**Parts:** B1 (wizard), B8 (pHash), B14
**Affordances:** U1-U5, N1, N3, N4, N28
**Requirements:** R1.1, R1.5

- Drop zone multi-archivo (hasta 50 fotos por batch)
- Formulario metadata compartida (lugar, rango de fecha, precisión, fuente/crédito, tags)
- Edición individual por foto (descripción, override lugar/fecha si difiere del lote)
- pHash calculado en `ProcessPhotoUpload` job
- Detección de duplicados: query por Hamming distance, UI advierte sin bloquear
- Refactorizar upload single de V1 para reusar componentes

**Demo:** "Subo 10 fotos de Calle Ahumada 1940 eficientemente, me avisa si hay duplicados"

---

### V4: Colaboración + eventos de dominio
**Parts:** B4, B9, B10, B14 (reporte)
**Affordances:** U8, U11, U12, U15, U16, N6, N9, N10, N12, N13
**Requirements:** R4.1, R4.2, R4.4, R4.5, R4.6, R12.9

- Infraestructura de eventos de dominio: PhotoUploaded, MetadataEdited, CommentCreated, PhotoVoted, PersonTagged, ComparisonUploaded
- Edición wiki-open de metadata por cualquier registrado (formulario inline)
- Audit log: tabla `revisions` polimórfica (old_values, new_values JSON), display historial
- Comentarios: crear y mostrar (solo registrados)
- Upvotes/downvotes: tabla `votes`, contadores, downvote solo ranking
- Reporte de duplicados: tabla `reports` (user_id, photo_id, reason, duplicate_of_id)
- Middleware `verified` en todas las rutas de contribución
- Retroactivamente disparar PhotoUploaded en uploads de V1-V3

**Demo:** "Otro usuario edita mi foto, comenta, vota. Veo el historial de cambios"

---

### V5: Personas y tagging
**Parts:** B3, B17
**Affordances:** U9, U10, U30, U31, N7, N8, N19, N31
**Requirements:** R3.1, R3.2, R10.3

- Modelo `Person` (name, type enum: public/unknown, slug nullable, bio)
- Pivot `person_photo` con coordenadas x/y relativas a imagen + label opcional
- UI: click en foto para marcar coordenadas, asignar persona existente o crear nueva
- Quitar tag de persona
- Búsqueda/autocompletado de personas (públicas primero, luego libres)
- Página de detalle de persona (bio + fotos donde aparece)
- Admin: promover persona a type=public (asignar slug, nombre canónico)
- Evento PersonTagged integrado con sistema de V4

**Demo:** "Marco personas en fotos estilo Facebook, navego por persona"

---

### V6: Gamificación
**Parts:** B5, B7, B18, B20
**Affordances:** U21, U36, U37, U38, N25, N26, N29, N24
**Requirements:** R9.1, R9.2, R9.3, R9.4, R9.5, R9.6, R11.2

- Modelo `PointAction` (key, label, points — seeder con acciones base)
- Modelo `PointTransaction` (user_id, point_action_id, points, actionable_type, actionable_id — composite unique)
- Modelo `Level` (name, min_points, icon — seeder con niveles base)
- Modelo `Badge` (key, name, description, points_awarded) + `UserBadge` pivot (user_id, badge_id, awarded_at)
- Interfaz `BadgeCriteria` + 8 handlers iniciales
- Listener `AwardPoints`: escucha eventos, crea PointTransaction, actualiza total_points en users
- Listener `EvaluateBadges`: escucha eventos, corre handlers, otorga badges
- `total_points` columna cacheada en users
- Leaderboard: ranking por total_points
- Command `points:recalculate`: borra transactions + replay eventos (flag $replay=true)
- Foto del día: campo `featured_at` en photos, query automática
- Toast/modal al desbloquear badge

**Demo:** "Subo fotos, gano puntos, desbloqueo badges, aparezco en el ranking"

---

### V7: Notificaciones + perfiles públicos
**Parts:** B6, B19, B25
**Affordances:** U32, U33, U35, U37, N20, N21, N23, N27
**Requirements:** R4.3, R8.1, R11.1, R12.5

- Notificaciones in-app: canal `database`, campanita con contador no leídas, panel desplegable
- Notificaciones email: canal `mail`
- Listener `NotifyPhotoOwner`: escucha MetadataEdited, CommentCreated, PhotoVoted, PersonTagged
- Throttle: cache key `notif:{photo_id}:{hour}`, máximo 1 email por foto por hora
- Perfiles de usuario públicos: fotos subidas (grid), puntos, nivel actual, badges obtenidos
- Ruta pública `/users/{user}` para ver perfil de cualquier usuario

**Demo:** "Me notifican cuando interactúan con mis fotos. Otros ven mi perfil público"

---

### V8: Búsqueda + home curada
**Parts:** B13, B18 (parcial)
**Affordances:** U18-U24, N15, N16
**Requirements:** R5.4, R5.5

- Laravel Scout + Algolia: configurar, indexar photos con descripción, place name, person names, tags
- Input de búsqueda global en header
- Página de resultados con facets (lugar, época, tags)
- Home curada: secciones "Recién agregadas", "Más populares", "Necesitan tu ayuda", "Foto del día"
- Feed infinito debajo de secciones (Inertia infinite scroll)
- Reemplazar home básica de V1

**Demo:** "Busco 'Valparaíso' y filtro por década. La home muestra secciones curadas"

---

### V9: Mapa interactivo
**Parts:** B21
**Affordances:** U25, U26, U27, N17
**Requirements:** R5.1, R5.2

- Vista de mapa con Leaflet o Mapbox
- Pins de fotos geolocalizadas con clustering por zoom
- Query geoespacial: fotos por bounding box del viewport
- Slider temporal: filtro por rango de años (year_from/year_to)
- Popup en pin: thumbnail + título + link a detalle

**Demo:** "Exploro Chile en el mapa, filtro por década con el slider"

---

### V10: API + seguridad + admin
**Parts:** B11, B15, B16, B23, B24
**Affordances:** U17, U29, U34, U39, N14, N22, N30
**Requirements:** R6.1, R6.2, R8.2, R10.1, R10.2, R12.1, R12.2, R12.3, R12.6, R12.7, R12.8

- Gestión de API tokens Sanctum: UI en perfil para crear, ver, revocar
- API endpoints: lectura pública (photos, places, persons, search) + escritura autenticada (upload, edit)
- Rate limiting: throttle 60 uploads/hora por token, rate limit en comentarios y Google Places
- Seguridad: validación MIME real + Intervention Image, EXIF strip en fotos "ahora", pre-signed URLs con expiración
- Admin moderación inline: borrar foto (soft delete), borrar comentario, editar Places (coordenadas, nombre)
- Ban system: campo `is_banned` en User, middleware que retorna 403, soft delete de fotos del baneado, excluir de leaderboard

**Demo:** "Gestiono API keys, subo vía API, admin modera contenido"

---

### V11: Foto del ahora (comparación)
**Parts:** B22
**Affordances:** U13, U14, N11
**Requirements:** R7.1, R7.2

- Tabla `comparison_photos` (id, photo_id FK, user_id FK, file paths en R2)
- Botón "Subir foto del ahora" en detalle de foto (solo registrados)
- Upload a R2 + generar variantes
- Display lado a lado: foto histórica + foto actual
- Evento ComparisonUploaded → gamificación (puntos + badge "Fotógrafo del Presente")

**Demo:** "Subo foto actual del mismo lugar, se muestra lado a lado con la histórica"

---

### Dependencias

```
V1 ──→ V2 ──→ V3
  │         ↗
  └──→ V4 ──→ V5
         │
         └──→ V6 ──→ V7
                │
                └──→ V8

V2 ──────────→ V9

V4 ──→ V10

V1 + V4 + V6 ──→ V11
```

**Camino crítico:** V1 → V4 → V6 (habilita gamificación, que muchos slices necesitan)
**Paralelizable:** V2/V3 pueden avanzar en paralelo con V4/V5 después de V1
