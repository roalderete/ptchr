<?php

namespace OrangeHRM\Help\Controller;

use OrangeHRM\Core\Controller\AbstractVueController;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Framework\Http\Request;

class ViewSupportController extends AbstractVueController
{
    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $component = new Component('view-support');
        $this->setComponent($component);
    }
}
