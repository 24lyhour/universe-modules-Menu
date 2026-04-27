<?php

namespace Modules\Menu\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Modules\Menu\Http\Requests\Dashboard\V1\MuteMenuRequest;
use Modules\Menu\Http\Resources\Dashboard\V1\MenuResource;
use Modules\Menu\Models\Menu;
use Momentum\Modal\Modal;

class MenuMuteController extends Controller
{
    /**
     * Open the mute / unmute modal.
     */
    public function show(Menu $menu): Modal
    {
        $menu->load('mutedBy');

        return Inertia::modal('menu::dashboard/Menu/Mute', [
            'menu' => MenuResource::make($menu)->resolve(),
            'presets' => collect(Menu::mutePresets())
                ->map(fn ($p, $key) => ['key' => $key, 'label' => $p['label']])
                ->values(),
        ])->baseRoute('menu.menus.index');
    }

    /**
     * Apply a preset mute (1h, 2h, 4h, today, 1d, forever).
     */
    public function mute(MuteMenuRequest $request, Menu $menu): RedirectResponse
    {
        $custom = $request->filled('muted_until')
            ? Carbon::parse($request->input('muted_until'))
            : null;

        $menu->muteForPreset(
            presetKey: $request->input('preset'),
            reason: $request->input('reason'),
            custom: $custom,
        );

        return redirect()
            ->route('menu.menus.index')
            ->with('success', "{$menu->name} muted.");
    }

    /**
     * Lift the mute.
     */
    public function unmute(Menu $menu): RedirectResponse
    {
        $menu->unmute();

        return redirect()
            ->route('menu.menus.index')
            ->with('success', "{$menu->name} unmuted.");
    }
}
