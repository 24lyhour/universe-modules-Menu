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
    menuItems: PaginatedResponse<Menu>;
    filters: MenuFilters;
    stats: MenuStats;
}

export interface MenuShowProps {
    menu: Menu;
}

export interface MenuEditProps {
    menu: Menu;
}

// Category Types
export interface Category {
    id: number;
    uuid: string;
    name: string;
    description: string | null;
    menu_id: number | null;
    image_url: string | null;
    sort_order: number;
    status: boolean;
    created_at: string;
    updated_at: string;
}

export interface CategoryStats {
    total: number;
    active: number;
    inactive: number;
}

export interface CategoryFilters {
    status?: string;
    search?: string;
}

export interface CategoryFormData {
    name: string;
    description: string;
    menu_id: number | null;
    image_url: string;
    sort_order: number;
    status: boolean;
}

export interface MenuOption {
    id: number;
    name: string;
}

export interface CategoryIndexProps {
    categories: PaginatedResponse<Category>;
    filters: CategoryFilters;
    stats: CategoryStats;
}

export interface CategoryCreateProps {
    menus: MenuOption[];
}

export interface CategoryEditProps {
    category: Category;
    menus: MenuOption[];
}

export interface CategoryDeleteProps {
    category: Category;
}

// Menu Type Types
export interface MenuType {
    id: number;
    uuid: string;
    name: string;
    description: string | null;
    image_url: string | null;
    sort_order: number;
    status: boolean;
    created_at: string;
    updated_at: string;
}

export interface MenuTypeStats {
    total: number;
    active: number;
    inactive: number;
}

export interface MenuTypeFilters {
    status?: string;
    search?: string;
}

export interface MenuTypeFormData {
    name: string;
    description: string;
    image_url: string;
    sort_order: number;
    status: boolean;
}

export interface MenuTypeIndexProps {
    menuTypes: PaginatedResponse<MenuType>;
    filters: MenuTypeFilters;
    stats: MenuTypeStats;
}

export interface MenuTypeCreateProps {}

export interface MenuTypeEditProps {
    menuType: MenuType;
}

export interface MenuTypeDeleteProps {
    menuType: MenuType;
}
