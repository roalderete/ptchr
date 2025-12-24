<?php

namespace OrangeHRM\Claim\Controller;

use OrangeHRM\Core\Controller\AbstractVueController;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Framework\Http\Request;

class ViewMyClaimController extends AbstractVueController
{
    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $component = new Component('my-claim');
        $this->setComponent($component);
    }
}
