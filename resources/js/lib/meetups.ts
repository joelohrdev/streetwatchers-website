/**
 * Meetup times are shown in the meetup's own time zone, so a 10am walk in Glasgow reads as 10am to
 * everyone, wherever they are viewing from.
 */

/** "Sat 4 Oct", in the meetup's time zone. */
export function formatMeetupDay(iso: string, timeZone: string): string {
    return new Intl.DateTimeFormat(undefined, {
        weekday: 'short',
        day: 'numeric',
        month: 'short',
        timeZone,
    }).format(new Date(iso));
}

/** "Saturday 4 October 2026", in the meetup's time zone. */
export function formatMeetupLongDay(iso: string, timeZone: string): string {
    return new Intl.DateTimeFormat(undefined, {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        timeZone,
    }).format(new Date(iso));
}

/** "10:00–12:00 BST", in the meetup's time zone, with the zone named so visitors elsewhere aren't misled. */
export function formatMeetupHours(
    startsAt: string,
    endsAt: string,
    timeZone: string,
): string {
    const time = (options: Intl.DateTimeFormatOptions = {}) =>
        new Intl.DateTimeFormat(undefined, {
            hour: 'numeric',
            minute: '2-digit',
            timeZone,
            ...options,
        });

    return `${time().format(new Date(startsAt))}–${time({ timeZoneName: 'short' }).format(new Date(endsAt))}`;
}
