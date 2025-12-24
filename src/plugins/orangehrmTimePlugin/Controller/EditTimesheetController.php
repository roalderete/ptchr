<?php

namespace OrangeHRM\Time\Controller;

use OrangeHRM\Core\Authorization\Controller\CapableViewController;
use OrangeHRM\Core\Controller\AbstractVueController;
use OrangeHRM\Core\Controller\Common\NoRecordsFoundController;
use OrangeHRM\Core\Controller\Exception\RequestForwardableException;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;
use OrangeHRM\Core\Traits\UserRoleManagerTrait;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Core\Vue\Prop;
use OrangeHRM\Entity\Timesheet;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Time\Traits\Service\TimesheetServiceTrait;

class EditTimesheetController extends AbstractVueController implements CapableViewController
{
    use AuthUserTrait;
    use TimesheetServiceTrait;
    use UserRoleManagerTrait;

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        // TODO: show 404 if no id
        if ($request->attributes->has('id')) {
            $timesheetId = $request->attributes->getInt('id');
            $component = new Component('edit-timesheet');
            $component->addProp(new Prop('timesheet-id', Prop::TYPE_NUMBER, $timesheetId));

            $timesheet = $this->getTimesheetService()->getTimesheetDao()->getTimesheetById($timesheetId);
            $timesheetOwnerEmpNumber = $timesheet->getEmployee()->getEmpNumber();
            $currentUserEmpNumber = $this->getAuthUser()->getEmpNumber();
            if ($timesheetOwnerEmpNumber === $currentUserEmpNumber) {
                $component->addProp(new Prop('my-timesheet', Prop::TYPE_BOOLEAN, true));
            }
        }

        $this->setComponent($component);
    }

    /**
     * @inheritDoc
     */
    public function isCapable(Request $request): bool
    {
        if ($request->attributes->has('id')) {
            $timesheet = $this->getTimesheetService()
                ->getTimesheetDao()
                ->getTimesheetById($request->attributes->getInt('id'));
            if ($timesheet instanceof Timesheet) {
                if ($this->getUserRoleManagerHelper()->isSelfByEmpNumber($timesheet->getEmployee()->getEmpNumber())
                    && $timesheet->getState() === 'APPROVED') {
                    return false;
                }
                return $this->getUserRoleManagerHelper()
                    ->isEmployeeAccessible($timesheet->getEmployee()->getEmpNumber());
            }
            throw new RequestForwardableException(NoRecordsFoundController::class . '::handle');
        }
        return true;
    }
}
