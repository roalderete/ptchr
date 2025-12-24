<?php

namespace OrangeHRM\Time\Report;

use OrangeHRM\Attendance\Traits\Service\AttendanceServiceTrait;
use OrangeHRM\Core\Api\CommonParams;
use OrangeHRM\Core\Api\V2\ParameterBag;
use OrangeHRM\Core\Report\ReportData;
use OrangeHRM\Core\Traits\Service\NumberHelperTrait;
use OrangeHRM\I18N\Traits\Service\I18NHelperTrait;
use OrangeHRM\Time\Dto\AttendanceReportSearchFilterParams;

class AttendanceReportData implements ReportData
{
    use AttendanceServiceTrait;
    use NumberHelperTrait;
    use I18NHelperTrait;

    /**
     * @var AttendanceReportSearchFilterParams
     */
    private AttendanceReportSearchFilterParams $filterParams;

    public function __construct(AttendanceReportSearchFilterParams $filterParams)
    {
        $this->filterParams = $filterParams;
    }

    /**
     * @inheritDoc
     */
    public function normalize(): array
    {
        $employeeAttendanceRecords = $this->getAttendanceService()
            ->getAttendanceDao()
            ->getAttendanceReportCriteriaList($this->filterParams);

        $result = [];
        foreach ($employeeAttendanceRecords as $employeeAttendanceRecord) {
            $termination = $employeeAttendanceRecord['terminationId'];
            $result[] = [
                AttendanceReport::PARAMETER_EMPLOYEE_NAME => $termination === null ? $employeeAttendanceRecord['fullName'] : $employeeAttendanceRecord['fullName'] . ' ' . $this->getI18NHelper()->transBySource('(Past Employee)'),
                AttendanceReport::PARAMETER_TIME => $this->getNumberHelper()
                    ->numberFormat((float)$employeeAttendanceRecord['total'] / 3600, 2)
            ];
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getMeta(): ?ParameterBag
    {
        $total = $this->getAttendanceService()
            ->getAttendanceDao()
            ->getTotalAttendanceDuration($this->filterParams);

        return new ParameterBag(
            [
                CommonParams::PARAMETER_TOTAL => $this->getAttendanceService()
                    ->getAttendanceDao()
                    ->getAttendanceReportCriteriaListCount($this->filterParams),
                'sum' => [
                    'hours' => floor($total / 3600),
                    'minutes' => ($total / 60) % 60,
                    'label' => $this->getNumberHelper()->numberFormat($total / 3600, 2),
                ],
            ]
        );
    }
}
