<?php

namespace Modules\Menu\Actions\Dashboard\V1;

use Illuminate\Support\Facades\Auth;
use Modules\Menu\Models\Category;

class UpdateCategoryAction
{
    /**
     * Update a category.
     */
    public function execute(Category $category, array $data): Category
    {
        $data['updated_by'] = Auth::id();

        $category->update($data);

        return $category->fresh();
    }
}
