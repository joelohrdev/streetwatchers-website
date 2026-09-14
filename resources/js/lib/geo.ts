export type Coordinates = {
    latitude: number;
    longitude: number;
};

const EARTH_RADIUS_KM = 6371;

const toRadians = (degrees: number): number => (degrees * Math.PI) / 180;

/** Great-circle distance between two points in kilometres, using the haversine formula. */
export function distanceInKilometres(
    from: Coordinates,
    to: Coordinates,
): number {
    const latitudeDelta = toRadians(to.latitude - from.latitude);
    const longitudeDelta = toRadians(to.longitude - from.longitude);

    const a =
        Math.sin(latitudeDelta / 2) ** 2 +
        Math.cos(toRadians(from.latitude)) *
            Math.cos(toRadians(to.latitude)) *
            Math.sin(longitudeDelta / 2) ** 2;

    return 2 * EARTH_RADIUS_KM * Math.asin(Math.sqrt(a));
}

const kilometres = new Intl.NumberFormat(undefined, {
    style: 'unit',
    unit: 'kilometer',
    maximumFractionDigits: 0,
});

/** A short, human distance such as "Under 1 km away" or "1,240 km away". */
export function formatDistance(distance: number): string {
    return distance < 1
        ? 'Under 1 km away'
        : `${kilometres.format(distance)} away`;
}
