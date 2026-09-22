export type FeaturedPhoto = {
    id: number;
    src: string;
    /** Describes the photograph for screen readers. */
    alt: string;
    caption: string;
    location: string;
    /** The photographer's name. Left out until it is known. */
    credit?: string;
};

export type ChapterDirectoryStats = {
    chapterCount: number;
    countryCount: number;
};

export type UpcomingMeetup = {
    id: number;
    title: string;
    starts_at: string;
    ends_at: string;
    timezone: string;
    group: { name: string; slug: string; city: string; country: string };
};
