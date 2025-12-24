<?php

namespace OrangeHRM\Time\Service;

use OrangeHRM\Core\Traits\Service\ConfigServiceTrait;
use OrangeHRM\Time\Factory\TimesheetPeriodFactory;

class TimesheetPeriodService
{
    use ConfigServiceTrait;

    public const DEFAULT_TIMESHEET_START_DATE = 1;

    /**
     * @param string $currentDate
     * @return mixed
     */
    public function getDefinedTimesheetPeriod(string $currentDate)
    {
        // TODO
        $xmlString = $this->getConfigService()->getTimeSheetPeriodConfig();
        $xml = simplexml_load_string($xmlString);
        return $this->getDaysOfTheTimesheetPeriod($xml, $currentDate);
    }

    /**
     * @param $xml
     * @param string $currentDate
     * @return mixed
     */
    public function getDaysOfTheTimesheetPeriod($xml, string $currentDate)
    {
        // TODO
        $timesheetPeriodFactory = new TimesheetPeriodFactory();
        $timesheetPeriodObject = $timesheetPeriodFactory->createTimesheetPeriod($xml->ClassName);
        return $timesheetPeriodObject->calculateDaysInTheTimesheetPeriod($currentDate, $xml);
    }

    /**
     * @return bool
     */
    public function isTimesheetPeriodDefined(): bool
    {
        return $this->getConfigService()->isTimesheetPeriodDefined();
    }

    /**
     * @param string $startDay
     * @return void
     */
    public function setTimesheetPeriod(string $startDay): void
    {
        $timesheetPeriodFactory = new TimesheetPeriodFactory();
        $timesheetPeriodObject = $timesheetPeriodFactory->setTimesheetPeriod();
        $xml = $timesheetPeriodObject->setTimesheetPeriodAndStartDate($startDay);
        $this->getConfigService()->setTimeSheetPeriodSetValue(true);
        $this->getConfigService()->setTimeSheetPeriodConfig($xml);
    }

    /**
     * @return string
     */
    public function getTimesheetHeading(): string
    {
        // TODO
        $xmlString = $this->getConfigService()->getTimeSheetPeriodConfig();
        $xml = simplexml_load_string($xmlString);
        return $xml->Heading;
    }

    /**
     * @return string
     */
    public function getTimesheetStartDate(): string
    {
        $xmlString = $this->getConfigService()->getTimeSheetPeriodConfig();
        $xml = simplexml_load_string($xmlString);
        return (string)$xml->StartDate;
    }
}
