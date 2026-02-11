// Menu Module Types

export interface Menu {
    id: number;
    uuid: string;
    name: string;
    description: string | null;
    image_url: string | null;
    status: boolean;
    schedule_mode: string | null;
    schedule_days: string | null;
    schedule_start_time: string | null;
    schedule_end_time: string | null;
    schedule_start_date: string | null;
    schedule_end_date: string | null;
    schedule_status: boolean | null;
    created_at: string;
    updated_at: string;
}

export interface MenuStats {
    total: number;
    active: number;
    inactive: number;
}

export interface PaginationMeta {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
}

export interface PaginatedResponse<T> {
    data: T[];
    meta: PaginationMeta;
}

export interface MenuFilters {
    status?: string;
    search?: string;
}

export interface MenuFormData {
    name: string;
    description: string;
    image_url: string;
    status: boolean;
    schedule_mode: string;
    schedule_days: string;
    schedule_start_time: string;
    schedule_end_time: string;
    schedule_start_date: string;
    schedule_end_date: string;
    schedule_status: boolean;
}

export interface MenuIndexProps {
    menus: PaginatedResponse<Menu>;
    filters: MenuFilters;
    stats: MenuStats;
}

export interface MenuShowProps {
    menu: Menu;
}

export interface MenuEditProps {
    menu: Menu;
}
