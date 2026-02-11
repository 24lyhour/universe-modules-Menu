<?php

namespace Modules\Menu\Actions\Dashboard\V1;

use Modules\Menu\Models\MenuType;

class UpdateMenuTypeAction
{
    public function execute(MenuType $menuType, array $data): MenuType
    {
        $menuType->update($data);
        return $menuType->fresh();
    }
}
