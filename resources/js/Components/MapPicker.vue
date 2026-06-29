<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from 'vue';
import 'leaflet/dist/leaflet.css';

const props = defineProps({
    lat: { type: Number, default: null },
    lng: { type: Number, default: null },
});
const emit = defineEmits(['update:lat', 'update:lng']);

const mapEl    = ref(null);
const searchEl = ref(null); // Google Places autocomplete target

const apiKey    = import.meta.env.VITE_GOOGLE_MAPS_KEY;
const useGoogle = !!(apiKey && apiKey !== 'YOUR_API_KEY_HERE');

// Leaflet search state
const lQuery    = ref('');
const lResults  = ref([]);
const lSearching = ref(false);
const lShowResults = ref(false);

const DEFAULT = { lat: 23.8103, lng: 90.4125 }; // Dhaka centre

let map            = null;
let marker         = null;
let L              = null;
let resizeObserver = null;

onMounted(async () => {
    if (useGoogle) await initGoogle();
    else           await initLeaflet();
});

// ── Google Maps ────────────────────────────────────────────────────────
async function initGoogle() {
    const { Loader }       = await import('@googlemaps/js-api-loader');
    const loader           = new Loader({ apiKey, version: 'weekly', libraries: ['places'] });
    const { Map }          = await loader.importLibrary('maps');
    const { Marker }       = await loader.importLibrary('marker');
    const { Autocomplete } = await loader.importLibrary('places');

    const center = props.lat && props.lng
        ? { lat: Number(props.lat), lng: Number(props.lng) }
        : DEFAULT;

    map = new Map(mapEl.value, {
        center,
        zoom: props.lat ? 15 : 12,
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: false,
    });

    if (props.lat && props.lng) placeGMarker(Marker, center);

    map.addListener('click', (e) => {
        const pos = { lat: e.latLng.lat(), lng: e.latLng.lng() };
        placeGMarker(Marker, pos);
        emitCoords(pos.lat, pos.lng);
    });

    const ac = new Autocomplete(searchEl.value, { fields: ['geometry'] });
    ac.addListener('place_changed', () => {
        const place = ac.getPlace();
        if (!place.geometry?.location) return;
        const pos = { lat: place.geometry.location.lat(), lng: place.geometry.location.lng() };
        map.setCenter(pos);
        map.setZoom(16);
        placeGMarker(Marker, pos);
        emitCoords(pos.lat, pos.lng);
    });
}

function placeGMarker(MarkerClass, pos) {
    if (marker) marker.setMap(null);
    marker = new MarkerClass({ position: pos, map, draggable: true });
    marker.addListener('dragend', (e) => emitCoords(e.latLng.lat(), e.latLng.lng()));
}

// ── Leaflet (fallback) ─────────────────────────────────────────────────
async function initLeaflet() {
    L = (await import('leaflet')).default;

    // Custom SVG pin — avoids Vite asset-path issues with default icons
    const pinIcon = L.divIcon({
        className: '',
        html: `<svg xmlns="http://www.w3.org/2000/svg" width="26" height="34" viewBox="0 0 26 34">
                 <path d="M13 0C5.82 0 0 5.82 0 13c0 9.75 13 21 13 21S26 22.75 26 13C26 5.82 20.18 0 13 0z" fill="#5B3DF5"/>
                 <circle cx="13" cy="13" r="5.5" fill="white"/>
               </svg>`,
        iconSize:   [26, 34],
        iconAnchor: [13, 34],
    });

    const center = props.lat && props.lng
        ? [Number(props.lat), Number(props.lng)]
        : [DEFAULT.lat, DEFAULT.lng];

    map = L.map(mapEl.value, { zoomControl: true }).setView(center, props.lat ? 15 : 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a>',
        maxZoom: 19,
    }).addTo(map);

    if (props.lat && props.lng) placeLMarker(center[0], center[1], pinIcon);

    map.on('click', (e) => {
        placeLMarker(e.latlng.lat, e.latlng.lng, pinIcon);
        emitCoords(e.latlng.lat, e.latlng.lng);
    });

    // Fix: map initialises inside v-show (display:none) and can't measure its
    // container. ResizeObserver fires when the step panel becomes visible and
    // invalidateSize() makes Leaflet re-fill the canvas correctly.
    resizeObserver = new ResizeObserver(() => {
        if (mapEl.value?.offsetWidth > 0) map.invalidateSize();
    });
    resizeObserver.observe(mapEl.value);
}

function placeLMarker(lat, lng, icon) {
    if (marker) marker.remove();
    marker = L.marker([lat, lng], { draggable: true, icon }).addTo(map);
    marker.on('dragend', (e) => {
        const ll = e.target.getLatLng();
        emitCoords(ll.lat, ll.lng);
    });
}

async function searchNominatim() {
    const q = lQuery.value.trim();
    if (!q) return;
    lSearching.value = true;
    lResults.value   = [];
    try {
        const res = await fetch(
            `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(q)}&format=json&limit=5`,
            { headers: { 'Accept-Language': 'en' } }
        );
        lResults.value    = await res.json();
        lShowResults.value = true;
    } finally {
        lSearching.value = false;
    }
}

function pickNominatim(result) {
    const lat = parseFloat(result.lat);
    const lng = parseFloat(result.lon);
    const pinIcon = L.divIcon({
        className: '',
        html: `<svg xmlns="http://www.w3.org/2000/svg" width="26" height="34" viewBox="0 0 26 34">
                 <path d="M13 0C5.82 0 0 5.82 0 13c0 9.75 13 21 13 21S26 22.75 26 13C26 5.82 20.18 0 13 0z" fill="#5B3DF5"/>
                 <circle cx="13" cy="13" r="5.5" fill="white"/>
               </svg>`,
        iconSize: [26, 34], iconAnchor: [13, 34],
    });
    map.setView([lat, lng], 16);
    placeLMarker(lat, lng, pinIcon);
    emitCoords(lat, lng);
    lQuery.value       = result.display_name.split(',').slice(0, 3).join(',').trim();
    lResults.value     = [];
    lShowResults.value = false;
}

// ── Shared ─────────────────────────────────────────────────────────────
function emitCoords(lat, lng) {
    emit('update:lat', parseFloat(lat.toFixed(7)));
    emit('update:lng', parseFloat(lng.toFixed(7)));
}

// Sync marker when parent edits lat/lng inputs directly
watch([() => props.lat, () => props.lng], ([lat, lng]) => {
    if (!map || !lat || !lng) return;
    if (useGoogle) {
        marker?.setPosition({ lat: Number(lat), lng: Number(lng) });
        map.setCenter({ lat: Number(lat), lng: Number(lng) });
    } else {
        marker?.setLatLng([Number(lat), Number(lng)]);
        map.setView([Number(lat), Number(lng)], map.getZoom());
    }
});

onBeforeUnmount(() => {
    resizeObserver?.disconnect();
    if (!useGoogle && map) map.remove();
});
</script>

<template>
    <div class="overflow-hidden rounded-xl border border-border">

        <!-- Search bar -->
        <div class="relative border-b border-border bg-white dark:bg-slate-900 px-3 py-2.5">
            <div class="flex items-center gap-2">
                <svg class="shrink-0 text-muted-foreground" width="14" height="14" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>

                <!-- Google Places autocomplete input -->
                <input v-if="useGoogle"
                    ref="searchEl"
                    type="text"
                    placeholder="Search for an address or place…"
                    class="w-full bg-transparent text-sm text-foreground placeholder-muted-foreground focus:outline-none"
                />

                <!-- Leaflet / Nominatim search input -->
                <template v-else>
                    <input
                        v-model="lQuery"
                        type="text"
                        placeholder="Search for an address or place…"
                        class="w-full bg-transparent text-sm text-foreground placeholder-muted-foreground focus:outline-none"
                        @keydown.enter.prevent="searchNominatim"
                        @blur="setTimeout(() => lShowResults = false, 200)"
                        @focus="lResults.length && (lShowResults = true)"
                    />
                    <button type="button" @click="searchNominatim"
                        class="shrink-0 rounded-md bg-admin-accent/10 px-2.5 py-1 text-xs font-medium text-admin-accent transition-colors hover:bg-admin-accent/20"
                        :class="lSearching && 'opacity-50 pointer-events-none'">
                        {{ lSearching ? '…' : 'Search' }}
                    </button>
                </template>
            </div>

            <!-- Nominatim results dropdown -->
            <div v-if="!useGoogle && lShowResults && lResults.length"
                class="absolute left-0 right-0 top-full z-50 mt-px max-h-52 overflow-y-auto rounded-b-xl border border-border bg-white dark:bg-slate-900 shadow-xl">
                <button v-for="r in lResults" :key="r.place_id" type="button"
                    @mousedown.prevent="pickNominatim(r)"
                    class="flex w-full items-start gap-2 px-3 py-2.5 text-left text-xs transition-colors hover:bg-slate-50 dark:hover:bg-white/[0.04]">
                    <svg class="mt-0.5 shrink-0 text-admin-accent" width="11" height="11" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                    </svg>
                    <span class="text-foreground line-clamp-2">{{ r.display_name }}</span>
                </button>
            </div>
        </div>

        <!-- Map canvas (used by both Google & Leaflet) -->
        <div ref="mapEl" style="height: 300px; width: 100%;" />

        <!-- Coords readout -->
        <div v-if="lat && lng"
            class="flex items-center gap-3 border-t border-border bg-slate-50 dark:bg-white/[0.02] px-4 py-2 text-xs text-muted-foreground">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                class="shrink-0 text-admin-accent">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
            </svg>
            <span>{{ Number(lat).toFixed(6) }}, {{ Number(lng).toFixed(6) }}</span>
            <span class="text-slate-300">•</span>
            <span>{{ useGoogle ? 'Click map or drag pin to adjust' : 'Click map or drag pin · OpenStreetMap' }}</span>
        </div>
        <div v-else
            class="flex items-center gap-2 border-t border-border bg-slate-50 dark:bg-white/[0.02] px-4 py-2 text-xs text-muted-foreground">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            Search or click the map to pin a location
        </div>

    </div>
</template>

<style>
.leaflet-container { cursor: crosshair !important; }
.leaflet-dragging .leaflet-container { cursor: crosshair !important; }
</style>
