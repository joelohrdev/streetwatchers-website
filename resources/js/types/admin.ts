export type Option = {
    value: string;
    label: string;
};

export type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

/** A Laravel length-aware paginator as serialized to JSON. */
export type Paginated<T> = {
    data: T[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    per_page: number;
    from: number | null;
    to: number | null;
    total: number;
};

export type TimelineEntry = {
    id: number;
    action: string;
    old_status: string | null;
    new_status: string | null;
    reason: string | null;
    actor: string | null;
    created_at: string;
};
