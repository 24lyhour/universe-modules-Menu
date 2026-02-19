<?php

namespace Modules\Menu\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Menu\Actions\Dashboard\V1\ToggleCategoryStatusAction;
use Modules\Menu\Models\Category;

class CategoryStatusController extends Controller
{
    public function __construct(
        protected ToggleCategoryStatusAction $toggleCategoryStatusAction,
    ) {}

    /**
     * Toggle category status.
     */
    public function __invoke(Request $request, Category $category): RedirectResponse
    {
        $this->toggleCategoryStatusAction->execute($category, $request->boolean('status'));

        return redirect()->back();
    }
}
