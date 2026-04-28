<?php

namespace Modules\Menu\Enums;

/**
 * Single source of truth for every permission the Menu module owns.
 *
 * Use ::value (e.g. PermissionEnum::MENUS_VIEW_ANY->value) wherever
 * Spatie permission names are required: middleware, route guards,
 * MenuService registrations, FormRequests, Policies. Never bare strings.
 *
 * RolesAndPermissionsSeeder reads ::values() to seed the database.
 */
enum PermissionEnum: string
{
    // ----- menus -----
    case MENUS_VIEW = 'menus.view';
    case MENUS_VIEW_ANY = 'menus.view_any';
    case MENUS_CREATE = 'menus.create';
    case MENUS_UPDATE = 'menus.update';
    case MENUS_DELETE = 'menus.delete';
    case MENUS_RESTORE = 'menus.restore';
    case MENUS_FORCE_DELETE = 'menus.force_delete';
    case MENUS_TOGGLE_STATUS = 'menus.toggle_status';
    case MENUS_SCHEDULE = 'menus.schedule';
    case MENUS_MUTE = 'menus.mute';
    case MENUS_UNMUTE = 'menus.unmute';
    case MENUS_EXPORT = 'menus.export';
    case MENUS_IMPORT = 'menus.import';

    // ----- menu_types -----
    case MENU_TYPES_VIEW = 'menu_types.view';
    case MENU_TYPES_VIEW_ANY = 'menu_types.view_any';
    case MENU_TYPES_CREATE = 'menu_types.create';
    case MENU_TYPES_UPDATE = 'menu_types.update';
    case MENU_TYPES_DELETE = 'menu_types.delete';
    case MENU_TYPES_RESTORE = 'menu_types.restore';
    case MENU_TYPES_FORCE_DELETE = 'menu_types.force_delete';
    case MENU_TYPES_TOGGLE_STATUS = 'menu_types.toggle_status';

    // ----- categories (a.k.a. menu_categories on the table side) -----
    case CATEGORIES_VIEW = 'categories.view';
    case CATEGORIES_VIEW_ANY = 'categories.view_any';
    case CATEGORIES_CREATE = 'categories.create';
    case CATEGORIES_UPDATE = 'categories.update';
    case CATEGORIES_DELETE = 'categories.delete';
    case CATEGORIES_RESTORE = 'categories.restore';
    case CATEGORIES_FORCE_DELETE = 'categories.force_delete';
    case CATEGORIES_TOGGLE_STATUS = 'categories.toggle_status';
    case CATEGORIES_REORDER = 'categories.reorder';
    case CATEGORIES_SYNC_PRODUCTS = 'categories.sync_products';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $c): string => $c->value, self::cases());
    }
}
