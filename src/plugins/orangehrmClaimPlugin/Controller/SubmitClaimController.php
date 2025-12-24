<?php

namespace OrangeHRM\Claim\Controller;

use OrangeHRM\Admin\Service\PayGradeService;
use OrangeHRM\Core\Controller\AbstractVueController;
use OrangeHRM\Core\Traits\ServiceContainerTrait;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Core\Vue\Prop;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Framework\Services;

class SubmitClaimController extends AbstractVueController
{
    use ServiceContainerTrait;

    /**
     * @return PayGradeService
     */
    public function getPayGradeService(): PayGradeService
    {
        return $this->getContainer()->get(Services::PAY_GRADE_SERVICE);
    }

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $currencies = $this->getPayGradeService()->getCurrencyArray();
        $component = new Component('submit-claim-request');
        $component->addProp(new Prop('currencies', Prop::TYPE_ARRAY, $currencies));
        $this->setComponent($component);
    }
}
