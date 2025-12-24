<?php

namespace OrangeHRM\Time\TimeSheetPeriod;

abstract class TimesheetPeriod
{
    /**
     * @param string $startDay
     * @return mixed
     */
    abstract public function setTimesheetPeriodAndStartDate(string $startDay);

    /**
     * @param string $currentDate
     * @param $xml
     * @return mixed
     */
    abstract public function calculateDaysInTheTimesheetPeriod(string $currentDate, $xml);
}
