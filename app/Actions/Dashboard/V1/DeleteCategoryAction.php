<?php

namespace Modules\Menu\Actions\Dashboard\V1;

use Modules\Menu\Models\Category;

class DeleteCategoryAction
{
    /**
     * Delete a category.
     */
    public function execute(Category $category): bool
    {
        return $category->delete();
    }
}
