<?php

namespace OrangeHRM\Leave\Menu;

use OrangeHRM\Core\Menu\MenuConfigurator;
use OrangeHRM\Core\Traits\ControllerTrait;
use OrangeHRM\Core\Traits\ModuleScreenHelperTrait;
use OrangeHRM\Entity\MenuItem;
use OrangeHRM\Entity\Screen;
use OrangeHRM\Framework\Http\Request;

class DetailedLeaveRequestMenuConfigurator implements MenuConfigurator
{
    use ModuleScreenHelperTrait;
    use ControllerTrait;

    /**
     * @inheritDoc
     */
    public function configure(Screen $screen): ?MenuItem
    {
        $screen = 'viewLeaveList';
        $request = $this->getCurrentRequest();
        if ($request instanceof Request) {
            $mode = $request->query->get('mode');
            if ($mode == 'my-leave') {
                $screen = 'viewMyLeaveList';
            }
        }
        $this->getCurrentModuleAndScreen()->overrideScreen($screen);
        return null;
    }
}
