<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from 'vue';
import { Loader } from '@googlemaps/js-api-loader';

const props = defineProps({
    lat: { type: Number, default: null },
    lng: { type: Number, default: null },
});

const emit = defineEmits(['update:lat', 'update:lng']);

const mapEl    = ref(null);
const searchEl = ref(null);
const apiKey   = import.meta.env.VITE_GOOGLE_MAPS_KEY;
const noKey    = !apiKey || apiKey === 'YOUR_API_KEY_HERE';

let map        = null;
let marker     = null;
let autocomplete = null;

const DEFAULT_CENTER = { lat: 23.8103, lng: 90.4125 }; // Dhaka

onMounted(async () => {
    if (noKey) return;

    const loader = new Loader({
        apiKey,
        version: 'weekly',
        libraries: ['places'],
    });

    const { Map }    = await loader.importLibrary('maps');
    const { Marker } = await loader.importLibrary('marker');
    const { Autocomplete } = await loader.importLibrary('places');

    const center = props.lat && props.lng
        ? { lat: Number(props.lat), lng: Number(props.lng) }
        : DEFAULT_CENTER;

    map = new Map(mapEl.value, {
        center,
        zoom: props.lat ? 15 : 12,
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: false,
        styles: [
            { featureType: 'poi', stylers: [{ visibility: 'off' }] },
        ],
    });

    // Place marker at initial coords if editing
    if (props.lat && props.lng) {
        placeMarker(Marker, center);
    }

    // Click anywhere to pin
    map.addListener('click', (e) => {
        const pos = { lat: e.latLng.lat(), lng: e.latLng.lng() };
        placeMarker(Marker, pos);
        emitCoords(pos.lat, pos.lng);
    });

    // Places autocomplete bound to the search input
    autocomplete = new Autocomplete(searchEl.value, {
        fields: ['geometry', 'formatted_address', 'name'],
    });

    autocomplete.addListener('place_changed', () => {
        const place = autocomplete.getPlace();
        if (!place.geometry?.location) return;
        const pos = {
            lat: place.geometry.location.lat(),
            lng: place.geometry.location.lng(),
        };
        map.setCenter(pos);
        map.setZoom(16);
        placeMarker(Marker, pos);
        emitCoords(pos.lat, pos.lng);
    });
});

function placeMarker(MarkerClass, pos) {
    if (marker) marker.setMap(null);
    marker = new MarkerClass({ position: pos, map, draggable: true });
    marker.addListener('dragend', (e) => {
        emitCoords(e.latLng.lat(), e.latLng.lng());
    });
}

function emitCoords(lat, lng) {
    emit('update:lat', parseFloat(lat.toFixed(7)));
    emit('update:lng', parseFloat(lng.toFixed(7)));
}

// If parent updates lat/lng from the text inputs, move the marker
watch([() => props.lat, () => props.lng], ([lat, lng]) => {
    if (!map || !lat || !lng) return;
    const pos = { lat: Number(lat), lng: Number(lng) };
    map.setCenter(pos);
    // We don't have MarkerClass in scope here — just move existing marker
    if (marker) marker.setPosition(pos);
});

onBeforeUnmount(() => {
    if (marker) marker.setMap(null);
});
</script>

<template>
    <!-- No API key state -->
    <div v-if="noKey"
        class="flex h-[340px] flex-col items-center justify-center gap-3 rounded-xl border-2 border-dashed border-border bg-slate-50 dark:bg-white/[0.02] text-sm text-muted-foreground">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
            class="text-slate-300" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
        </svg>
        <div class="text-center">
            <p class="font-medium text-foreground">Google Maps not configured</p>
            <p class="mt-1 text-xs text-slate-400">Add your key to <code class="rounded bg-slate-100 dark:bg-white/10 px-1 py-0.5">.env</code> →
                <code class="rounded bg-slate-100 dark:bg-white/10 px-1 py-0.5">VITE_GOOGLE_MAPS_KEY=your_key</code>
            </p>
        </div>
    </div>

    <!-- Map -->
    <div v-else class="overflow-hidden rounded-xl border border-border">
        <!-- Search bar -->
        <div class="border-b border-border bg-white dark:bg-slate-900 px-3 py-2.5">
            <div class="relative flex items-center gap-2">
                <svg class="shrink-0 text-muted-foreground" width="14" height="14" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input
                    ref="searchEl"
                    type="text"
                    placeholder="Search for an address or place…"
                    class="w-full bg-transparent text-sm text-foreground placeholder-muted-foreground focus:outline-none"
                />
            </div>
        </div>

        <!-- Map canvas -->
        <div ref="mapEl" class="h-[300px] w-full" />

        <!-- Coords readout -->
        <div v-if="lat && lng"
            class="flex items-center gap-3 border-t border-border bg-slate-50 dark:bg-white/[0.02] px-4 py-2 text-xs text-muted-foreground">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                class="text-admin-accent shrink-0">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
            </svg>
            <span>{{ Number(lat).toFixed(6) }}, {{ Number(lng).toFixed(6) }}</span>
            <span class="text-slate-300">•</span>
            <span>Click map or drag pin to adjust</span>
        </div>
        <div v-else
            class="flex items-center gap-2 border-t border-border bg-slate-50 dark:bg-white/[0.02] px-4 py-2 text-xs text-muted-foreground">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            Search for a location or click the map to place a pin
        </div>
    </div>
</template>
