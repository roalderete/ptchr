<?php

namespace OrangeHRM\Time\Menu;

use OrangeHRM\Core\Menu\MenuConfigurator;
use OrangeHRM\Core\Traits\ModuleScreenHelperTrait;
use OrangeHRM\Entity\MenuItem;
use OrangeHRM\Entity\Screen;

class DetailedProjectActivityReportMenuConfigurator implements MenuConfigurator
{
    use ModuleScreenHelperTrait;

    /**
     * @inheritDoc
     */
    public function configure(Screen $screen): ?MenuItem
    {
        $this->getCurrentModuleAndScreen()->overrideScreen('displayProjectReportCriteria');
        return null;
    }
}
