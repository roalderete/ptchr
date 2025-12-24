<?php

namespace OrangeHRM\Time\Controller;

use OrangeHRM\Core\Controller\AbstractVueController;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;
use OrangeHRM\Core\Traits\Service\DateTimeHelperTrait;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Core\Vue\Prop;
use OrangeHRM\Entity\Timesheet;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Time\Traits\Service\TimesheetServiceTrait;

class MyTimesheetController extends AbstractVueController
{
    use AuthUserTrait;
    use DateTimeHelperTrait;
    use TimesheetServiceTrait;

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $this->createDefaultTimesheetIfNotExist();
        $component = new Component('my-timesheet');
        if ($request->query->has('startDate')) {
            $component->addProp(new Prop('start-date', Prop::TYPE_STRING, $request->query->get('startDate')));
        }
        $this->setComponent($component);
    }

    /**
     * @return void
     */
    private function createDefaultTimesheetIfNotExist(): void
    {
        $currentDate = $this->getDateTimeHelper()->getNow();
        $status = $this->getTimesheetService()->hasTimesheetForDate($this->getAuthUser()->getEmpNumber(), $currentDate);
        if (!$status) {
            $timesheet = new Timesheet();
            $timesheet->getDecorator()->setEmployeeByEmployeeNumber($this->getAuthUser()->getEmpNumber());
            $this->getTimesheetService()->createTimesheetByDate($timesheet, $currentDate);
        }
    }
}
