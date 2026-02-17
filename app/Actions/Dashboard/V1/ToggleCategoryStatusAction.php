<?php

namespace Modules\Menu\Actions\Dashboard\V1;

use Modules\Menu\Models\Category;

class ToggleCategoryStatusAction
{
    /**
     * Toggle category status.
     */
    public function execute(Category $category, bool $status): Category
    {
        $category->update([
            'status' => $status,
        ]);

        return $category->fresh();
    }
}
