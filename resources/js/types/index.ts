// Menu Module Types

export interface Menu {
    id: number;
    uuid: string;
    name: string;
    description: string | null;
    image_url: string | null;
    outlet_id: number | null;
    menu_type_id: number | null;
    outlet_name: string | null;
    menu_type_name: string | null;
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
    outlet_id: number | null;
    menu_type_id: number | null;
    status: boolean;
    schedule_mode: string;
    schedule_days: string;
    schedule_start_time: string;
    schedule_end_time: string;
    schedule_start_date: string;
    schedule_end_date: string;
    schedule_status: boolean;
}

export interface OutletOption {
    id: number;
    name: string;
}

export interface MenuTypeOption {
    id: number;
    name: string;
}

export interface MenuIndexProps {
    menuItems: PaginatedResponse<Menu>;
    filters: MenuFilters;
    stats: MenuStats;
}

export interface MenuShowProps {
    menu: Menu;
}

export interface MenuCreateProps {
    outlets: OutletOption[];
    menuTypes: MenuTypeOption[];
}

export interface MenuEditProps {
    menu: Menu;
    outlets: OutletOption[];
    menuTypes: MenuTypeOption[];
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
    products_count?: number;
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

export interface CategoryProduct {
    id: number;
    name: string;
    sku: string | null;
    price: number;
    sale_price: number | null;
    status: string;
    image_url: string | null;
    pivot: {
        price_override: number | null;
        sort_order: number;
        is_available: boolean;
    };
}

export interface CategoryShowProps {
    category: Category;
    products: CategoryProduct[];
}

// Menu Type Types
export interface MenuType {
    id: number;
    uuid: string;
    name: string;
    description: string | null;
    image_url: string | null;
    outlet_id: number | null;
    outlet_name: string | null;
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
    outlet_id: number | null;
    sort_order: number;
    status: boolean;
}

export interface MenuTypeIndexProps {
    menuTypes: PaginatedResponse<MenuType>;
    filters: MenuTypeFilters;
    stats: MenuTypeStats;
}

export interface MenuTypeCreateProps {
    outlets: OutletOption[];
}

export interface MenuTypeEditProps {
    menuType: MenuType;
    outlets: OutletOption[];
}

export interface MenuTypeDeleteProps {
    menuType: MenuType;
}
