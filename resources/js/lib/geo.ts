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

const KILOMETRES_PER_MILE = 1.609344;

const miles = new Intl.NumberFormat(undefined, {
    style: 'unit',
    unit: 'mile',
    maximumFractionDigits: 0,
});

/**
 * A short, human distance in miles, such as "Under 1 mi away" or "770 mi away".
 * Distances are calculated in kilometres and converted here for display.
 */
export function formatDistance(kilometres: number): string {
    const distanceInMiles = kilometres / KILOMETRES_PER_MILE;

    return distanceInMiles < 1
        ? 'Under 1 mi away'
        : `${miles.format(distanceInMiles)} away`;
}
