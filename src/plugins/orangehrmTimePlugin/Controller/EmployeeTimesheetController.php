<?php

namespace OrangeHRM\Time\Controller;

use OrangeHRM\Core\Controller\AbstractVueController;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Core\Vue\Prop;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Core\Traits\UserRoleManagerTrait;
use OrangeHRM\Pim\Traits\Service\EmployeeServiceTrait;
use OrangeHRM\Core\Controller\Common\NoRecordsFoundController;
use OrangeHRM\Core\Controller\Exception\RequestForwardableException;

class EmployeeTimesheetController extends AbstractVueController
{
    use UserRoleManagerTrait;
    use EmployeeServiceTrait;

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        if ($request->attributes->has('id')) {
            $empNumber = $request->attributes->getInt('id');
            if (!$this->getUserRoleManagerHelper()->isEmployeeAccessible($empNumber)) {
                throw new RequestForwardableException(NoRecordsFoundController::class . '::handle');
            }

            $component = new Component('view-employee-timesheet');
            if ($request->query->has('startDate')) {
                $component->addProp(new Prop('start-date', Prop::TYPE_STRING, $request->query->get('startDate')));
            }

            $component->addProp(
                new Prop(
                    'employee',
                    Prop::TYPE_OBJECT,
                    $this->getEmployeeService()->getEmployeeAsArray($empNumber)
                )
            );
        } else {
            $component = new Component('employee-timesheet');
        }

        $this->setComponent($component);
    }
}
