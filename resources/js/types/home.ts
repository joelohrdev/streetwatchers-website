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
