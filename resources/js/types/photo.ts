import type { User } from './auth';

export type Place = {
    id: number;
    name: string;
    slug: string;
    type: 'precise' | 'approximate';
    latitude: number | null;
    longitude: number | null;
    country: string | null;
    region: string | null;
    city: string | null;
    photos_count?: number;
};

export type Tag = {
    id: number;
    name: string;
    slug: string;
    photos_count?: number;
};

export type PhotoFile = {
    id: number;
    variant: 'original' | 'medium' | 'thumb';
    url: string;
    width: number;
    height: number;
};

export type PersonPivot = {
    x: number | null;
    y: number | null;
    label: string | null;
};

export type Person = {
    id: number;
    name: string;
    type: 'public' | 'unknown';
    slug: string | null;
    bio: string | null;
    photos_count?: number;
    pivot?: PersonPivot;
};

export type ComparisonPhoto = {
    id: number;
    photo_id: number;
    user: {
        id: number;
        name: string;
    };
    description: string | null;
    taken_at: string | null;
    original_url: string | null;
    medium_url: string | null;
    thumb_url: string | null;
    created_at: string;
};

export type Photo = {
    id: number;
    description: string;
    year_from: number;
    year_to: number | null;
    date_precision: 'exact' | 'year' | 'decade' | 'circa';
    source_credit: string | null;
    heading: number | null;
    pitch: number | null;
    user: User;
    place: Place | null;
    files: PhotoFile[];
    tags: Tag[];
    persons?: Person[];
    comparisons?: ComparisonPhoto[];
    upvotes_count: number;
    downvotes_count: number;
    score: number;
    created_at: string;
    updated_at: string;
};

export type Comment = {
    id: number;
    body: string;
    user: {
        id: number;
        name: string;
    };
    created_at: string;
};

export type Level = {
    id: number;
    name: string;
    min_points: number;
    icon: string;
};

export type LeaderboardEntry = {
    user: {
        id: number;
        name: string;
    };
    total_points: number;
    level: Level | null;
    badges_count: number;
};

export type PaginatedData<T> = {
    data: T[];
    links: {
        first: string | null;
        last: string | null;
        prev: string | null;
        next: string | null;
    };
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
};
