<?php

namespace OrangeHRM\Leave\Menu;

use OrangeHRM\Core\Menu\MenuConfigurator;
use OrangeHRM\Core\Traits\ModuleScreenHelperTrait;
use OrangeHRM\Entity\MenuItem;
use OrangeHRM\Entity\Screen;

class LeaveTypeMenuConfigurator implements MenuConfigurator
{
    use ModuleScreenHelperTrait;

    /**
     * @inheritDoc
     */
    public function configure(Screen $screen): ?MenuItem
    {
        $this->getCurrentModuleAndScreen()->overrideScreen('leaveTypeList');
        return null;
    }
}
