<?php

namespace Modules\Menu\Http\Middleware;

use App\Services\MenuService;
use Closure;
use Illuminate\Http\Request;

/**
 * Registers the Menu module's sidebar entries when a dashboard request
 * comes in. Doing this here (instead of in MenuServiceProvider::boot())
 * keeps API and console requests lighter — they never touch the sidebar.
 *
 * Idempotent: a per-process guard prevents duplicates when the middleware
 * runs more than once in the same lifecycle (e.g. test suites).
 */
class DashboardMiddlewareHandle
{
    protected static bool $registered = false;

    public function handle(Request $request, Closure $next)
    {
        // Only register on dashboard requests — the sidebar isn't rendered
        // anywhere else, so registering would be wasted work.
        if ($request->is('dashboard', 'dashboard/*')) {
            $this->registerMenuItems();
        }

        return $next($request);
    }

    protected function registerMenuItems(): void
    {
        if (static::$registered) {
            return;
        }

        MenuService::addMenuItem(
            menu: 'primary',
            id: 'menu',
            title: __('Menu'),
            url: route('menu.menus.index'),
            icon: 'UtensilsCrossed',
            order: 60,
            permissions: 'menus.view_any',
            route: 'menu.*'
        );

        MenuService::addSubmenuItem(
            'primary', 'menu',
            __('Menus'), route('menu.menus.index'),
            10, 'menus.view_any', 'menu.menus.*', 'UtensilsCrossed'
        );
        MenuService::addSubmenuItem(
            'primary', 'menu',
            __('Categories'), route('menu.categories.index'),
            20, 'categories.view_any', 'menu.categories.*', 'Layers'
        );
        MenuService::addSubmenuItem(
            'primary', 'menu',
            __('Menu Types'), route('menu.menu-types.index'),
            30, 'menu_types.view_any', 'menu.menu-types.*', 'ListOrdered'
        );

        static::$registered = true;
    }
}
