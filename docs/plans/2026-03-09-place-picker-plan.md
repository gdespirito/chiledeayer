# Place Picker con Google Maps — Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Allow users to assign locations to photos by searching existing places or creating new ones via Google Places autocomplete + Leaflet map confirmation.

**Architecture:** Reusable `PlacePicker.vue` component with two modes: internal search and Google Places creation with draggable Leaflet map. New `POST /api/places` endpoint creates places from Google data with `verified: false`. PlaceSuggestion system is removed.

**Tech Stack:** Vue 3, Leaflet.js, Google Places API (proxied), Laravel, Pest 4

---

### Task 1: Add `verified` column to places table

**Files:**
- Create: `database/migrations/XXXX_add_verified_to_places_table.php`
- Modify: `app/Models/Place.php`

**Step 1: Create migration**

Run:
```bash
php artisan make:migration add_verified_to_places_table --no-interaction
```

**Step 2: Write migration code**

Edit the generated migration:
```php
public function up(): void
{
    Schema::table('places', function (Blueprint $table) {
        $table->boolean('verified')->default(true)->after('city');
    });
}

public function down(): void
{
    Schema::table('places', function (Blueprint $table) {
        $table->dropColumn('verified');
    });
}
```

**Step 3: Add `verified` to Place model fillable**

In `app/Models/Place.php`, add `'verified'` to `$fillable` array and add cast:
```php
protected $fillable = [
    'name',
    'slug',
    'type',
    'latitude',
    'longitude',
    'google_place_id',
    'bounding_box',
    'country',
    'region',
    'city',
    'verified',
];
```

In `casts()`:
```php
'verified' => 'boolean',
```

**Step 4: Run migration**

Run: `php artisan migrate --no-interaction`

**Step 5: Lint and commit**

```bash
vendor/bin/pint --dirty --format agent
git add database/migrations/*_add_verified_to_places_table.php app/Models/Place.php
git commit -m "feat: add verified column to places table"
```

---

### Task 2: Create Place store endpoint

**Files:**
- Create: `app/Http/Controllers/PlaceStoreController.php`
- Create: `app/Http/Requests/StorePlaceRequest.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/PlaceStoreTest.php`

**Step 1: Write the failing test**

Run: `php artisan make:test --pest PlaceStoreTest --no-interaction`

Write `tests/Feature/PlaceStoreTest.php`:
```php
<?php

use App\Models\Place;
use App\Models\User;

it('creates a place from google places data', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/api/places', [
            'google_place_id' => 'ChIJL68sLWXFYpYRGqb3B5OOOOA',
            'name' => 'Plaza de Armas de Santiago',
            'latitude' => -33.4372,
            'longitude' => -70.6506,
            'city' => 'Santiago',
            'region' => 'Región Metropolitana',
            'country' => 'Chile',
        ])
        ->assertSuccessful()
        ->assertJsonPath('place.name', 'Plaza de Armas de Santiago');

    $this->assertDatabaseHas('places', [
        'google_place_id' => 'ChIJL68sLWXFYpYRGqb3B5OOOOA',
        'name' => 'Plaza de Armas de Santiago',
        'verified' => false,
    ]);
});

it('reuses existing place by google_place_id', function () {
    $user = User::factory()->create();
    $existing = Place::factory()->create([
        'google_place_id' => 'ChIJL68sLWXFYpYRGqb3B5OOOOA',
        'name' => 'Plaza de Armas',
        'verified' => true,
    ]);

    $this->actingAs($user)
        ->postJson('/api/places', [
            'google_place_id' => 'ChIJL68sLWXFYpYRGqb3B5OOOOA',
            'name' => 'Plaza de Armas de Santiago',
            'latitude' => -33.4372,
            'longitude' => -70.6506,
        ])
        ->assertSuccessful()
        ->assertJsonPath('place.id', $existing->id);

    expect(Place::where('google_place_id', 'ChIJL68sLWXFYpYRGqb3B5OOOOA')->count())->toBe(1);
});

it('requires google_place_id', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/api/places', [
            'name' => 'Test Place',
            'latitude' => -33.4,
            'longitude' => -70.6,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('google_place_id');
});

it('requires authentication', function () {
    $this->postJson('/api/places', [
        'google_place_id' => 'test',
        'name' => 'Test',
        'latitude' => -33.4,
        'longitude' => -70.6,
    ])->assertUnauthorized();
});
```

**Step 2: Run test to verify it fails**

Run: `php artisan test --compact tests/Feature/PlaceStoreTest.php`
Expected: FAIL (route not defined)

**Step 3: Create form request**

Run: `php artisan make:request StorePlaceRequest --no-interaction`

Write `app/Http/Requests/StorePlaceRequest.php`:
```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePlaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'google_place_id' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'min:-90', 'max:90'],
            'longitude' => ['required', 'numeric', 'min:-180', 'max:180'],
            'city' => ['nullable', 'string', 'max:255'],
            'region' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
        ];
    }
}
```

**Step 4: Create controller**

Run: `php artisan make:controller PlaceStoreController --invokable --no-interaction`

Write `app/Http/Controllers/PlaceStoreController.php`:
```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlaceRequest;
use App\Models\Place;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class PlaceStoreController extends Controller
{
    public function __invoke(StorePlaceRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $place = Place::firstOrCreate(
            ['google_place_id' => $validated['google_place_id']],
            [
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'city' => $validated['city'] ?? null,
                'region' => $validated['region'] ?? null,
                'country' => $validated['country'] ?? 'Chile',
                'type' => 'precise',
                'verified' => false,
            ],
        );

        return response()->json(['place' => $place]);
    }
}
```

**Step 5: Add route**

In `routes/web.php`, add inside the `auth+verified` group:
```php
Route::post('api/places', PlaceStoreController::class)->name('places.store');
```

Add import at top:
```php
use App\Http\Controllers\PlaceStoreController;
```

**Step 6: Run tests**

Run: `php artisan test --compact tests/Feature/PlaceStoreTest.php`
Expected: PASS (4 tests)

**Step 7: Lint and commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Http/Controllers/PlaceStoreController.php app/Http/Requests/StorePlaceRequest.php routes/web.php tests/Feature/PlaceStoreTest.php
git commit -m "feat: add POST /api/places endpoint for Google Places creation"
```

---

### Task 3: Build PlacePicker.vue component

**Files:**
- Create: `resources/js/components/PlacePicker.vue`

**Step 1: Create the component**

Write `resources/js/components/PlacePicker.vue`:
```vue
<script setup lang="ts">
import { MapPin, Search, X } from 'lucide-vue-next';
import L from 'leaflet';
import { nextTick, onBeforeUnmount, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { Place } from '@/types';

type Props = {
    modelValue: number | null;
    currentPlace?: Place | null;
};

type GooglePrediction = {
    place_id: string;
    description: string;
    structured_formatting: {
        main_text: string;
        secondary_text: string;
    };
};

type EmittedPlace = {
    id: number;
};

const props = withDefaults(defineProps<Props>(), {
    currentPlace: null,
});

const emit = defineEmits<{
    'update:modelValue': [value: number | null];
    placed: [place: EmittedPlace];
}>();

// Mode: 'idle' | 'search' | 'google'
const mode = ref<'idle' | 'search' | 'google'>('idle');

// Internal place search
const internalQuery = ref('');
const internalResults = ref<Place[]>([]);
const internalLoading = ref(false);
let internalTimer: ReturnType<typeof setTimeout> | null = null;

watch(internalQuery, (query) => {
    if (internalTimer) clearTimeout(internalTimer);
    if (query.length < 2) {
        internalResults.value = [];
        return;
    }
    internalLoading.value = true;
    internalTimer = setTimeout(async () => {
        const res = await fetch(
            `/api/places/search?query=${encodeURIComponent(query)}`,
        );
        internalResults.value = await res.json();
        internalLoading.value = false;
    }, 300);
});

function selectExistingPlace(place: Place): void {
    emit('update:modelValue', place.id);
    emit('placed', { id: place.id });
    close();
}

// Google Places search
const googleQuery = ref('');
const googleResults = ref<GooglePrediction[]>([]);
const googleLoading = ref(false);
let googleTimer: ReturnType<typeof setTimeout> | null = null;

watch(googleQuery, (query) => {
    if (googleTimer) clearTimeout(googleTimer);
    if (query.length < 2) {
        googleResults.value = [];
        return;
    }
    googleLoading.value = true;
    googleTimer = setTimeout(async () => {
        const res = await fetch(
            `/api/google-places/autocomplete?query=${encodeURIComponent(query)}`,
        );
        const data = await res.json();
        googleResults.value = data.predictions ?? [];
        googleLoading.value = false;
    }, 300);
});

// Map confirmation
const showMap = ref(false);
const mapContainer = ref<HTMLElement | null>(null);
let map: L.Map | null = null;
let marker: L.Marker | null = null;
const selectedGoogle = ref<{
    google_place_id: string;
    name: string;
    latitude: number;
    longitude: number;
    city: string;
    region: string;
    country: string;
} | null>(null);
const creatingPlace = ref(false);

async function selectGooglePrediction(prediction: GooglePrediction): Promise<void> {
    const res = await fetch(
        `/api/google-places/details?place_id=${encodeURIComponent(prediction.place_id)}`,
    );
    const data = await res.json();
    const result = data.result;

    if (!result?.geometry?.location) return;

    const components = result.address_components ?? [];
    const city =
        components.find((c: { types: string[] }) =>
            c.types.includes('locality'),
        )?.long_name ?? '';
    const region =
        components.find((c: { types: string[] }) =>
            c.types.includes('administrative_area_level_1'),
        )?.long_name ?? '';
    const country =
        components.find((c: { types: string[] }) =>
            c.types.includes('country'),
        )?.long_name ?? 'Chile';

    selectedGoogle.value = {
        google_place_id: prediction.place_id,
        name: result.name ?? prediction.structured_formatting.main_text,
        latitude: result.geometry.location.lat,
        longitude: result.geometry.location.lng,
        city,
        region,
        country,
    };

    googleResults.value = [];
    showMap.value = true;

    await nextTick();
    initMap(selectedGoogle.value.latitude, selectedGoogle.value.longitude);
}

function initMap(lat: number, lng: number): void {
    if (!mapContainer.value) return;
    if (map) {
        map.remove();
        map = null;
    }

    map = L.map(mapContainer.value).setView([lat, lng], 16);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    marker = L.marker([lat, lng], { draggable: true }).addTo(map);
    marker.on('dragend', () => {
        if (!marker || !selectedGoogle.value) return;
        const pos = marker.getLatLng();
        selectedGoogle.value.latitude = pos.lat;
        selectedGoogle.value.longitude = pos.lng;
    });
}

async function confirmPlace(): Promise<void> {
    if (!selectedGoogle.value) return;
    creatingPlace.value = true;

    const res = await fetch('/api/places', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-XSRF-TOKEN': decodeURIComponent(
                document.cookie
                    .split('; ')
                    .find((c) => c.startsWith('XSRF-TOKEN='))
                    ?.split('=')[1] ?? '',
            ),
        },
        body: JSON.stringify(selectedGoogle.value),
    });

    const data = await res.json();
    creatingPlace.value = false;

    if (data.place) {
        emit('update:modelValue', data.place.id);
        emit('placed', { id: data.place.id });
        close();
    }
}

function open(): void {
    mode.value = 'search';
    internalQuery.value = '';
    internalResults.value = [];
}

function switchToGoogle(): void {
    mode.value = 'google';
    googleQuery.value = internalQuery.value;
    googleResults.value = [];
    showMap.value = false;
    selectedGoogle.value = null;
}

function close(): void {
    mode.value = 'idle';
    internalQuery.value = '';
    internalResults.value = [];
    googleQuery.value = '';
    googleResults.value = [];
    showMap.value = false;
    selectedGoogle.value = null;
    if (map) {
        map.remove();
        map = null;
    }
}

onBeforeUnmount(() => {
    if (map) {
        map.remove();
        map = null;
    }
});
</script>

<template>
    <div class="space-y-2">
        <!-- Idle state: show current place or add button -->
        <template v-if="mode === 'idle'">
            <div v-if="currentPlace" class="space-y-1">
                <p class="text-sm text-muted-foreground">
                    {{ currentPlace.name }}
                    <template v-if="currentPlace.city || currentPlace.region">
                        <br />
                        <span class="text-xs">
                            {{
                                [
                                    currentPlace.city,
                                    currentPlace.region,
                                    currentPlace.country,
                                ]
                                    .filter(Boolean)
                                    .join(', ')
                            }}
                        </span>
                    </template>
                </p>
                <button
                    class="text-xs text-muted-foreground hover:underline"
                    @click="open"
                >
                    Cambiar lugar
                </button>
            </div>
            <button
                v-else
                class="text-sm text-muted-foreground hover:underline"
                @click="open"
            >
                + Agregar ubicación
            </button>
        </template>

        <!-- Search mode: internal places -->
        <template v-if="mode === 'search'">
            <Input
                v-model="internalQuery"
                type="text"
                placeholder="Buscar lugar..."
                class="text-sm"
                autofocus
            />
            <div
                v-if="internalResults.length > 0"
                class="max-h-40 overflow-y-auto rounded-md border"
            >
                <button
                    v-for="place in internalResults"
                    :key="place.id"
                    class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm hover:bg-accent"
                    @click="selectExistingPlace(place)"
                >
                    <MapPin class="size-3 shrink-0 text-muted-foreground" />
                    <span>{{ place.name }}</span>
                    <span
                        v-if="place.city"
                        class="text-xs text-muted-foreground"
                    >
                        {{ place.city }}
                    </span>
                </button>
            </div>
            <p
                v-else-if="internalQuery.length >= 2 && !internalLoading"
                class="text-xs text-muted-foreground"
            >
                No se encontraron lugares.
            </p>
            <div class="flex items-center gap-2">
                <button
                    class="text-xs font-medium text-primary hover:underline"
                    @click="switchToGoogle"
                >
                    + Agregar lugar nuevo
                </button>
                <button
                    class="text-xs text-muted-foreground hover:underline"
                    @click="close"
                >
                    Cancelar
                </button>
            </div>
        </template>

        <!-- Google mode: Google Places autocomplete + map -->
        <template v-if="mode === 'google'">
            <div v-if="!showMap" class="space-y-2">
                <Label>Buscar en Google Maps</Label>
                <div class="relative">
                    <Search
                        class="pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        v-model="googleQuery"
                        type="text"
                        placeholder="Ej: Plaza de Armas, Santiago"
                        class="pl-9 text-sm"
                        autofocus
                    />
                </div>
                <div
                    v-if="googleResults.length > 0"
                    class="max-h-48 overflow-y-auto rounded-md border"
                >
                    <button
                        v-for="prediction in googleResults"
                        :key="prediction.place_id"
                        class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm hover:bg-accent"
                        @click="selectGooglePrediction(prediction)"
                    >
                        <MapPin
                            class="size-3 shrink-0 text-muted-foreground"
                        />
                        <div>
                            <span class="font-medium">
                                {{
                                    prediction.structured_formatting.main_text
                                }}
                            </span>
                            <span class="text-xs text-muted-foreground">
                                {{
                                    prediction.structured_formatting
                                        .secondary_text
                                }}
                            </span>
                        </div>
                    </button>
                </div>
                <p
                    v-if="googleQuery.length >= 2 && !googleLoading && googleResults.length === 0"
                    class="text-xs text-muted-foreground"
                >
                    Sin resultados de Google.
                </p>
            </div>

            <!-- Map confirmation -->
            <div v-if="showMap && selectedGoogle" class="space-y-2">
                <p class="text-sm font-medium">
                    {{ selectedGoogle.name }}
                </p>
                <p class="text-xs text-muted-foreground">
                    Arrastra el pin para ajustar la ubicación exacta.
                </p>
                <div
                    ref="mapContainer"
                    class="h-48 w-full rounded-md border"
                />
                <div class="flex gap-2">
                    <Button
                        size="sm"
                        :disabled="creatingPlace"
                        @click="confirmPlace"
                    >
                        Confirmar ubicación
                    </Button>
                    <Button size="sm" variant="ghost" @click="close">
                        Cancelar
                    </Button>
                </div>
            </div>

            <button
                v-if="!showMap"
                class="text-xs text-muted-foreground hover:underline"
                @click="close"
            >
                Cancelar
            </button>
        </template>
    </div>
</template>
```

**Step 2: Lint and commit**

```bash
npm run lint && npm run format
git add resources/js/components/PlacePicker.vue
git commit -m "feat: add PlacePicker component with Google Maps + Leaflet"
```

---

### Task 4: Integrate PlacePicker into photos/Show.vue

**Files:**
- Modify: `resources/js/pages/photos/Show.vue`

**Step 1: Replace place search code with PlacePicker**

Remove from `<script setup>`:
- `showPlaceSearch`, `placeQuery`, `placeResults`, `placeSearchLoading`, `placeSearchTimer`, the `watch(placeQuery, ...)`, `selectPlace()`, `showSuggestForm`, `suggestForm`, `submitSuggestion()`

Add import:
```typescript
import PlacePicker from '@/components/PlacePicker.vue';
```

Replace the entire `<!-- Place -->` section in the template (lines ~557-739) with:
```vue
<!-- Place -->
<div class="space-y-1">
    <div class="flex items-center gap-2 text-sm font-medium">
        <MapPin class="size-4 text-muted-foreground" />
        Lugar
    </div>
    <template v-if="auth?.user">
        <PlacePicker
            :model-value="photo.place?.id ?? null"
            :current-place="photo.place"
            @placed="
                (p) =>
                    router.put(
                        metadataUpdate.url(photo.id),
                        { place_id: p.id },
                        { preserveScroll: true },
                    )
            "
        />
    </template>
    <template v-else>
        <p v-if="photo.place" class="text-sm text-muted-foreground">
            {{ photo.place.name }}
            <template v-if="photo.place.city || photo.place.region">
                <br />
                <span class="text-xs">
                    {{
                        [photo.place.city, photo.place.region, photo.place.country]
                            .filter(Boolean)
                            .join(', ')
                    }}
                </span>
            </template>
        </p>
        <button
            v-else
            class="text-sm text-muted-foreground hover:underline"
            @click="router.visit(login.url())"
        >
            + Agregar ubicación
        </button>
    </template>
</div>
```

**Step 2: Remove unused imports**

Remove `watch` from vue import (if no longer used elsewhere — check first).
Remove `Label` import if no longer used elsewhere. Remove `metadataUpdate` import only if not used elsewhere. Check each before removing.

**Step 3: Lint, build, and commit**

```bash
npm run lint && npm run format
npm run build
git add resources/js/pages/photos/Show.vue
git commit -m "feat: integrate PlacePicker into photo detail page"
```

---

### Task 5: Integrate PlacePicker into photos/Create.vue

**Files:**
- Modify: `resources/js/pages/photos/Create.vue`

**Step 1: Add PlacePicker to the form**

Add import:
```typescript
import PlacePicker from '@/components/PlacePicker.vue';
```

Add after the `<!-- Date precision -->` section and before `<!-- Source credit -->`:
```vue
<!-- Place -->
<div class="grid gap-2">
    <Label>Ubicación <span class="text-muted-foreground">(opcional)</span></Label>
    <PlacePicker
        v-model="form.place_id"
        @placed="(p) => (form.place_id = p.id)"
    />
    <InputError :message="form.errors.place_id" />
</div>
```

**Step 2: Lint, build, and commit**

```bash
npm run lint && npm run format
npm run build
git add resources/js/pages/photos/Create.vue
git commit -m "feat: add PlacePicker to photo upload form"
```

---

### Task 6: Remove PlaceSuggestion system

**Files:**
- Delete: `app/Http/Controllers/PlaceSuggestionController.php`
- Delete: `app/Http/Requests/StorePlaceSuggestionRequest.php`
- Delete: `app/Models/PlaceSuggestion.php`
- Delete: `tests/Feature/PlaceSuggestionTest.php`
- Create: `database/migrations/XXXX_drop_place_suggestions_table.php`
- Modify: `routes/web.php`

**Step 1: Create drop migration**

Run:
```bash
php artisan make:migration drop_place_suggestions_table --no-interaction
```

Write:
```php
public function up(): void
{
    Schema::dropIfExists('place_suggestions');
}

public function down(): void
{
    Schema::create('place_suggestions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->foreignId('photo_id')->nullable()->constrained()->nullOnDelete();
        $table->string('name');
        $table->string('city')->nullable();
        $table->string('region')->nullable();
        $table->string('country')->nullable()->default('Chile');
        $table->text('notes')->nullable();
        $table->string('status')->default('pending');
        $table->foreignId('place_id')->nullable()->constrained()->nullOnDelete();
        $table->timestamps();
    });
}
```

**Step 2: Remove route from web.php**

Remove this line from `routes/web.php`:
```php
Route::post('api/place-suggestions', [PlaceSuggestionController::class, 'store'])->name('place-suggestions.store');
```

Remove the import:
```php
use App\Http\Controllers\PlaceSuggestionController;
```

**Step 3: Delete files**

```bash
rm app/Http/Controllers/PlaceSuggestionController.php
rm app/Http/Requests/StorePlaceSuggestionRequest.php
rm app/Models/PlaceSuggestion.php
rm tests/Feature/PlaceSuggestionTest.php
```

**Step 4: Run migration**

Run: `php artisan migrate --no-interaction`

**Step 5: Run all tests**

Run: `php artisan test --compact`
Expected: All pass (PlaceSuggestionTest no longer exists)

**Step 6: Lint and commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "refactor: remove PlaceSuggestion in favor of direct Place creation"
```

---

### Task 7: Final verification and deploy

**Step 1: Run full test suite**

Run: `php artisan test --compact`
Expected: All tests pass

**Step 2: Run full CI check**

Run: `composer run ci:check`

**Step 3: Build frontend**

Run: `npm run build`

**Step 4: Test visually**

Use Playwright or browser to verify:
1. `/photos/{id}` — PlacePicker shows, can search internal places, can add via Google
2. `/photos/create` — PlacePicker appears in form, can select place before upload
3. Map at `/map` still works with places

**Step 5: Push and verify deploy**

```bash
git push
gh run watch --exit-status
```

Verify in production:
```bash
kubectl exec deployment/chiledeayer -n chiledeayer --context admin@freshwork -- php artisan migrate --force --no-interaction
```
