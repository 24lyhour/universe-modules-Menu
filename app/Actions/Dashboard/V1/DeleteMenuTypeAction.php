<?php

namespace Modules\Menu\Actions\Dashboard\V1;

use Modules\Menu\Models\MenuType;

class DeleteMenuTypeAction
{
    public function execute(MenuType $menuType): bool
    {
        return $menuType->delete();
    }
}
