<?php

namespace Modules\Menu\Actions\Dashboard\V1;

use Modules\Menu\Models\MenuType;

class CreateMenuTypeAction
{
    public function execute(array $data): MenuType
    {
        return MenuType::create($data);
    }
}
