<?php

namespace OrangeHRM\Claim\Controller;

use OrangeHRM\Core\Controller\AbstractVueController;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Core\Vue\Prop;
use OrangeHRM\Framework\Http\Request;

class ViewAssignClaimController extends AbstractVueController
{
    use AuthUserTrait;

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $component = new Component('employee-claim');
        $component->addProp(new Prop('emp-number', Prop::TYPE_NUMBER, $this->getAuthUser()->getEmpNumber()));
        $this->setComponent($component);
    }
}
