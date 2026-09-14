<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import type * as Leaflet from 'leaflet';
import 'leaflet/dist/leaflet.css';
import 'leaflet.markercluster/dist/MarkerCluster.css';
import { onBeforeUnmount, onMounted, useTemplateRef, watch } from 'vue';
import type { Coordinates } from '@/lib/geo';
import { formatDistance } from '@/lib/geo';
import { show } from '@/routes/chapters';

export type MapChapter = Coordinates & {
    id: number;
    name: string;
    slug: string;
    city: string;
    country: string;
    members_count?: number;
    distance?: number | null;
};

const {
    chapters,
    position = null,
    focus = null,
    label = 'Map of StreetWatchers groups',
} = defineProps<{
    chapters: MapChapter[];
    /** The visitor's location. The map zooms to it and its nearest chapters. */
    position?: Coordinates | null;
    /** A chapter to centre on instead of fitting every marker, e.g. on a chapter page. */
    focus?: MapChapter | null;
    label?: string;
}>();

/**
 * OpenStreetMap's own tiles are fine for development but not for production traffic.
 * Set VITE_MAP_TILE_URL and VITE_MAP_TILE_ATTRIBUTION to a hosted tile provider before launch.
 */
const tileUrl =
    import.meta.env.VITE_MAP_TILE_URL ||
    'https://tile.openstreetmap.org/{z}/{x}/{y}.png';
const tileAttribution =
    import.meta.env.VITE_MAP_TILE_ATTRIBUTION ||
    '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';

/**
 * "tiles" draws street-level map images from the tile provider above. "outline" draws
 * public-domain Natural Earth country shapes with no tile provider. Add ?basemap=outline
 * or ?basemap=tiles to a page's URL to compare them; VITE_MAP_BASEMAP sets the default.
 */
type Basemap = 'tiles' | 'outline';

const OUTLINE_MAX_ZOOM = 6;

/** Matches the --color-paper token, for Leaflet styles that can't read CSS variables. */
const PAPER = '#f8f7f3';

function resolveBasemap(): Basemap {
    const requested =
        new URLSearchParams(window.location.search).get('basemap') ??
        import.meta.env.VITE_MAP_BASEMAP;

    return requested === 'outline' ? 'outline' : 'tiles';
}

const container = useTemplateRef<HTMLDivElement>('container');
let basemap: Basemap = 'tiles';

let L: typeof Leaflet | null = null;
let map: Leaflet.Map | null = null;
let clusters: Leaflet.MarkerClusterGroup | null = null;
let visitorMarker: Leaflet.Marker | null = null;
let focusMarker: Leaflet.Marker | null = null;

/** Build popup content from text nodes so member-supplied chapter names can never inject HTML. */
function popupContent(chapter: MapChapter): HTMLElement {
    const root = document.createElement('div');
    root.className = 'chapter-popup';

    const name = document.createElement('p');
    name.className = 'chapter-popup__name';
    name.textContent = chapter.name;

    const place = document.createElement('p');
    place.className = 'chapter-popup__meta';
    place.textContent = [
        `${chapter.city}, ${chapter.country}`,
        chapter.distance != null ? formatDistance(chapter.distance) : null,
    ]
        .filter(Boolean)
        .join(' · ');

    root.append(name, place);

    if (chapter.id !== focus?.id) {
        const link = document.createElement('a');
        link.className = 'chapter-popup__link';
        link.href = show.url(chapter.slug);
        link.textContent = 'View group →';
        link.addEventListener('click', (event) => {
            event.preventDefault();
            router.visit(link.href);
        });
        root.append(link);
    }

    return root;
}

/**
 * Draw Natural Earth country shapes as the base map, beneath the markers, with state and province
 * borders on top. Natural Earth only publishes those borders at this size for the US, Canada,
 * Australia, Brazil, Russia, India, China, Indonesia and South Africa.
 */
async function addCountryOutlines(
    leaflet: typeof Leaflet,
    target: Leaflet.Map,
): Promise<void> {
    const load = async (
        url: string,
    ): Promise<GeoJSON.FeatureCollection | null> => {
        const response = await fetch(url);

        return response.ok
            ? ((await response.json()) as GeoJSON.FeatureCollection)
            : null;
    };

    const [countries, states] = await Promise.all([
        load('/maps/countries-110m.geojson'),
        load('/maps/states-50m.geojson'),
    ]);

    if (countries) {
        leaflet
            .geoJSON(countries, {
                interactive: false,
                style: {
                    color: PAPER,
                    weight: 1.2,
                    fillColor: '#e3e3e3',
                    fillOpacity: 1,
                },
            })
            .addTo(target);
    }

    if (states) {
        leaflet
            .geoJSON(states, {
                interactive: false,
                style: { color: PAPER, weight: 0.6, opacity: 0.9 },
            })
            .addTo(target);
    }
}

function renderMarkers(): void {
    if (!L || !map || !clusters) {
        return;
    }

    const leaflet = L;
    const chapterMarker = (chapter: MapChapter, isFocus: boolean) =>
        leaflet
            .marker([chapter.latitude, chapter.longitude], {
                title: chapter.name,
                alt: chapter.name,
                zIndexOffset: isFocus ? 500 : 0,
                icon: leaflet.divIcon({
                    className: '',
                    html: `<span class="chapter-marker${isFocus ? ' chapter-marker--focus' : ''}"></span>`,
                    iconSize: [18, 18],
                }),
            })
            .bindPopup(popupContent(chapter), { closeButton: false });

    clusters.clearLayers();
    clusters.addLayers(
        chapters
            .filter((chapter) => chapter.id !== focus?.id)
            .map((chapter) => chapterMarker(chapter, false)),
    );

    // The chapter being viewed sits outside the clusters so it never disappears into a neighbour's group.
    focusMarker?.remove();
    focusMarker = focus ? chapterMarker(focus, true).addTo(map) : null;
}

function renderVisitor(): void {
    if (!L || !map) {
        return;
    }

    visitorMarker?.remove();
    visitorMarker = position
        ? L.marker([position.latitude, position.longitude], {
              title: 'Your location',
              alt: 'Your location',
              interactive: false,
              zIndexOffset: 1000,
              icon: L.divIcon({
                  className: '',
                  html: '<span class="visitor-marker"></span>',
                  iconSize: [18, 18],
              }),
          }).addTo(map)
        : null;
}

/** Frame the visitor and their nearest chapters, the focused chapter, or every result. */
function frame(): void {
    if (!L || !map) {
        return;
    }

    if (focus) {
        map.setView(
            [focus.latitude, focus.longitude],
            basemap === 'outline' ? 5 : 11,
        );

        return;
    }

    const points: Leaflet.LatLngTuple[] = position
        ? [
              [position.latitude, position.longitude],
              ...chapters
                  .slice(0, 3)
                  .map((chapter): Leaflet.LatLngTuple => [
                      chapter.latitude,
                      chapter.longitude,
                  ]),
          ]
        : chapters.map((chapter): Leaflet.LatLngTuple => [
              chapter.latitude,
              chapter.longitude,
          ]);

    if (points.length === 0) {
        return;
    }

    map.fitBounds(L.latLngBounds(points), {
        padding: [40, 40],
        maxZoom: basemap === 'outline' ? OUTLINE_MAX_ZOOM : 10,
    });
}

onMounted(async () => {
    // Leaflet needs the browser, so it loads here rather than during server-side rendering.
    const leaflet = (await import('leaflet')).default;

    // leaflet.markercluster attaches itself to a global L instead of importing Leaflet.
    (window as Window & { L?: typeof Leaflet }).L = leaflet;
    await import('leaflet.markercluster');

    if (!container.value) {
        return;
    }

    L = leaflet;
    basemap = resolveBasemap();
    map = leaflet.map(container.value, {
        scrollWheelZoom: false,
        worldCopyJump: basemap === 'tiles',
        minZoom: 2,
        maxZoom: basemap === 'outline' ? OUTLINE_MAX_ZOOM : 19,
        // Country shapes don't repeat like tiles, so keep the view on a single copy of the world.
        ...(basemap === 'outline'
            ? {
                  maxBounds: leaflet.latLngBounds([-60, -185], [85, 190]),
                  maxBoundsViscosity: 1,
              }
            : {}),
    });
    map.setView([20, 0], 2);

    if (basemap === 'outline') {
        container.value.classList.add('chapter-map--outline');
        map.attributionControl.addAttribution(
            'Map shapes: <a href="https://www.naturalearthdata.com/">Natural Earth</a>',
        );
        void addCountryOutlines(leaflet, map);
    } else {
        leaflet
            .tileLayer(tileUrl, { attribution: tileAttribution, maxZoom: 19 })
            .addTo(map);
    }

    clusters = leaflet.markerClusterGroup({
        showCoverageOnHover: false,
        maxClusterRadius: 50,
        iconCreateFunction: (cluster) => {
            const count = cluster.getChildCount();
            const size = count < 10 ? 34 : count < 50 ? 42 : 50;

            return leaflet.divIcon({
                className: '',
                html: `<span class="chapter-cluster" style="width:${size}px;height:${size}px">${count}</span>`,
                iconSize: [size, size],
            });
        },
    });
    map.addLayer(clusters);

    renderMarkers();
    renderVisitor();
    frame();
});

watch(
    () => [chapters, position, focus] as const,
    () => {
        renderMarkers();
        renderVisitor();
        frame();
    },
);

onBeforeUnmount(() => {
    map?.remove();
    map = null;
});
</script>

<template>
    <div
        ref="container"
        role="region"
        :aria-label="label"
        class="chapter-map bg-hairline/40 relative z-0 h-full w-full"
    />
</template>

<style scoped>
/*
 * Greyscale tiles keep the map in the site's black-and-white palette. The filter goes on each
 * tile rather than the whole tile pane, which makes Chrome draw hairline gaps between tiles.
 */
.chapter-map :deep(.leaflet-tile) {
    filter: grayscale(1) contrast(1.05);
}

/* Outline mode has no tiles, so the sea is the page's paper colour. */
.chapter-map.chapter-map--outline {
    background: var(--color-paper);
}

/* The root element is the Leaflet container; this outranks leaflet.css's own font. */
.chapter-map {
    font-family: inherit;
}

.chapter-map :deep(.chapter-marker),
.chapter-map :deep(.visitor-marker) {
    display: block;
    width: 18px;
    height: 18px;
    border-radius: 9999px;
    border: 3px solid #fff;
    background: #000;
    box-shadow: 0 1px 4px rgb(0 0 0 / 0.45);
}

.chapter-map :deep(.chapter-marker--focus) {
    outline: 3px solid #000;
    outline-offset: 2px;
}

.chapter-map :deep(.visitor-marker) {
    background: #fff;
    border-color: #000;
    box-shadow: 0 0 0 6px rgb(0 0 0 / 0.15);
}

.chapter-map :deep(.chapter-cluster) {
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 9999px;
    border: 3px solid #fff;
    background: #000;
    color: #fff;
    font-size: 12px;
    font-weight: 700;
    box-shadow: 0 1px 4px rgb(0 0 0 / 0.45);
}

.chapter-map :deep(.leaflet-popup-content-wrapper) {
    border-radius: 0;
    box-shadow: 0 2px 10px rgb(0 0 0 / 0.2);
}

.chapter-map :deep(.leaflet-popup-content) {
    margin: 14px 16px;
}

.chapter-map :deep(.chapter-popup__name) {
    margin: 0;
    font-weight: 800;
    font-size: 13px;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.chapter-map :deep(.chapter-popup__meta) {
    margin: 4px 0 0;
    color: #616161;
    font-size: 12px;
}

.chapter-map :deep(.chapter-popup__link) {
    display: inline-block;
    margin-top: 10px;
    color: #000;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    border-bottom: 2px solid #000;
}
</style>
