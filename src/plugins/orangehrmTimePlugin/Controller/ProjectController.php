<?php

namespace OrangeHRM\Time\Controller;

use OrangeHRM\Core\Controller\AbstractVueController;
use OrangeHRM\Core\Traits\Controller\VueComponentPermissionTrait;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Core\Vue\Prop;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Time\Traits\Service\ProjectServiceTrait;

class ProjectController extends AbstractVueController
{
    use VueComponentPermissionTrait;
    use ProjectServiceTrait;

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $component = new Component('project-list');
        $unSelectableProjectIds = $this->getProjectService()
            ->getProjectDao()
            ->getUnselectableProjectIds();
        $component->addProp(new Prop('unselectable-ids', Prop::TYPE_ARRAY, $unSelectableProjectIds));
        $this->setComponent($component);
        $this->setPermissions(['time_projects', 'time_project_activities']);
    }
}
