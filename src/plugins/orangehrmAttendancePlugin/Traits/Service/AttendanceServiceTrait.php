<?php

namespace OrangeHRM\Attendance\Traits\Service;

use OrangeHRM\Attendance\Service\AttendanceService;
use OrangeHRM\Core\Traits\ServiceContainerTrait;
use OrangeHRM\Framework\Services;

trait AttendanceServiceTrait
{
    use ServiceContainerTrait;

    /**
     * @return AttendanceService
     */
    protected function getAttendanceService(): AttendanceService
    {
        return $this->getContainer()->get(Services::ATTENDANCE_SERVICE);
    }
}
