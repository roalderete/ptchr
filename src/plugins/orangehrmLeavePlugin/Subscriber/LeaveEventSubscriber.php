<?php

namespace OrangeHRM\Leave\Subscriber;

use InvalidArgumentException;
use OrangeHRM\Core\Service\EmailService;
use OrangeHRM\Framework\Event\AbstractEventSubscriber;
use OrangeHRM\Leave\Event\LeaveAllocate;
use OrangeHRM\Leave\Event\LeaveApply;
use OrangeHRM\Leave\Event\LeaveApprove;
use OrangeHRM\Leave\Event\LeaveAssign;
use OrangeHRM\Leave\Event\LeaveCancel;
use OrangeHRM\Leave\Event\LeaveEvent;
use OrangeHRM\Leave\Event\LeaveReject;
use OrangeHRM\Leave\Event\LeaveStatusChange;

class LeaveEventSubscriber extends AbstractEventSubscriber
{
    private ?EmailService $emailService = null;

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            LeaveEvent::APPLY => [['onAllocateEvent', 0]],
            LeaveEvent::ASSIGN => [['onAllocateEvent', 0]],
            LeaveEvent::APPROVE => [['onStatusChangeEvent', 0]],
            LeaveEvent::CANCEL => [['onStatusChangeEvent', 0]],
            LeaveEvent::REJECT => [['onStatusChangeEvent', 0]],
        ];
    }

    /**
     * @return EmailService
     */
    public function getEmailService(): EmailService
    {
        if (!$this->emailService instanceof EmailService) {
            $this->emailService = new EmailService();
        }
        return $this->emailService;
    }

    /**
     * @param LeaveAllocate $allocateEvent
     */
    public function onAllocateEvent(LeaveAllocate $allocateEvent): void
    {
        $leaveRequest = $allocateEvent->getDetailedLeaveRequest();
        $leaveRequest->getLeaves();
        if ($allocateEvent instanceof LeaveApply) {
            $emailName = 'leave.apply';
        } elseif ($allocateEvent instanceof LeaveAssign) {
            $emailName = 'leave.assign';
        } else {
            throw new InvalidArgumentException('Invalid instance of `' . LeaveAllocate::class . '` provided');
        }

        $workflow = $allocateEvent->getWorkflow();
        $recipientRoles = $workflow->getDecorator()->getRolesToNotify();
        $performerRole = strtolower($workflow->getRole());

        $this->getEmailService()->queueEmailNotifications($emailName, $recipientRoles, $performerRole, $allocateEvent);
    }

    /**
     * @param LeaveStatusChange $statusChangeEvent
     */
    public function onStatusChangeEvent(LeaveStatusChange $statusChangeEvent): void
    {
        if ($statusChangeEvent instanceof LeaveApprove) {
            $emailName = 'leave.approve';
        } elseif ($statusChangeEvent instanceof LeaveCancel) {
            $emailName = 'leave.cancel';
        } elseif ($statusChangeEvent instanceof LeaveReject) {
            $emailName = 'leave.reject';
        } else {
            throw new InvalidArgumentException('Invalid instance of `' . LeaveAllocate::class . '` provided');
        }

        $workflow = $statusChangeEvent->getWorkflow();
        $recipientRoles = $workflow->getDecorator()->getRolesToNotify();
        $performerRole = strtolower($workflow->getRole());

        $this->getEmailService()->queueEmailNotifications(
            $emailName,
            $recipientRoles,
            $performerRole,
            $statusChangeEvent
        );
    }
}
