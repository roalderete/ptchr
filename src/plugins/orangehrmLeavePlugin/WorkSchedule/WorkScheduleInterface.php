<?php

namespace OrangeHRM\Leave\WorkSchedule;

use DateTime;
use OrangeHRM\Admin\Dto\WorkShiftStartAndEndTime;

interface WorkScheduleInterface
{
    /**
     * @param int|null $empNumber
     */
    public function setEmpNumber(?int $empNumber): void;

    /**
     * @return float e.g. 8, 8.25
     */
    public function getWorkShiftLength(): float;

    /**
     * @return WorkShiftStartAndEndTime
     */
    public function getWorkShiftStartEndTime(): WorkShiftStartAndEndTime;

    /**
     * @param DateTime $day
     * @param bool $fullDay
     * @return bool
     */
    public function isNonWorkingDay(DateTime $day, bool $fullDay): bool;

    /**
     * @param DateTime $day
     * @return bool
     */
    public function isHalfDay(DateTime $day): bool;

    /**
     * @param DateTime $day
     * @return bool
     */
    public function isHoliday(DateTime $day): bool;

    /**
     * @param DateTime $day
     * @return bool
     */
    public function isHalfDayHoliday(DateTime $day): bool;
}
