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
};

export type Tag = {
    id: number;
    name: string;
    slug: string;
};

export type PhotoFile = {
    id: number;
    variant: 'original' | 'medium' | 'thumb';
    url: string;
    width: number;
    height: number;
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
    created_at: string;
    updated_at: string;
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
