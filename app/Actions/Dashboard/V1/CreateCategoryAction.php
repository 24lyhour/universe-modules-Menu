<?php

namespace Modules\Menu\Actions\Dashboard\V1;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\Menu\Models\Category;

class CreateCategoryAction
{
    /**
     * Create a new category.
     */
    public function execute(array $data): Category
    {
        $data['uuid'] = (string) Str::uuid();
        $data['created_by'] = Auth::id();

        return Category::create($data);
    }
}
