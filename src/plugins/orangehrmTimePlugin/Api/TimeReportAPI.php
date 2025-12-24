<?php

namespace OrangeHRM\Time\Api;

use OrangeHRM\Core\Api\Rest\ReportAPI;
use OrangeHRM\Core\Api\V2\Exception\BadRequestException;
use OrangeHRM\Core\Report\Api\EndpointAwareReport;
use OrangeHRM\Time\Report\AttendanceReport;
use OrangeHRM\Time\Report\EmployeeReport;
use OrangeHRM\Time\Report\ProjectActivityReport;
use OrangeHRM\Time\Report\ProjectReport;

class TimeReportAPI extends ReportAPI
{
    public const TIME_REPORT_MAP = [
        'project' => ProjectReport::class,
        //activity_detailed -> detailed report of project report (Break down overview of project report)
        'activity_detailed' => ProjectActivityReport::class,
        'employee' => EmployeeReport::class,
        'attendance' => AttendanceReport::class,
    ];

    /**
     * @OA\Get(
     *     path="/api/v2/time/reports",
     *     tags={"Time/Timesheet Report"},
     *     summary="Get Time Reports",
     *     operationId="get-time-reports",
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(
     *                         property="headers",
     *                         type="array",
     *                         @OA\Items(
     *                             @OA\Property(property="name", type="string"),
     *                             @OA\Property(property="prop", type="string"),
     *                             @OA\Property(property="size", type="integer"),
     *                             @OA\Property(property="pin", type="string", nullable=true),
     *                             @OA\Property(
     *                                 property="cellProperties",
     *                                 type="object",
     *                                 @OA\Property(
     *                                     property="class",
     *                                     type="object",
     *                                     @OA\Property(property="cell-action", type="boolean")
     *                                 )
     *                             )
     *                         )
     *                     )
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="headers", type="string", nullable=true),
     *                 @OA\Property(property="filters", type="string", nullable=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Bad Request - Invalid report name",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="status", type="string", default="400"),
     *                 @OA\Property(property="message", type="string", default="Invalid report name")
     *             )
     *         )
     *     ),
     * )
     *
     * @inheritDoc
     * @throws BadRequestException
     */
    protected function getReport(): EndpointAwareReport
    {
        $reportName = $this->getReportName();
        if (!isset(TimeReportAPI::TIME_REPORT_MAP[$reportName])) {
            throw $this->getBadRequestException('Invalid report name');
        }
        $reportClass = TimeReportAPI::TIME_REPORT_MAP[$reportName];
        return new $reportClass();
    }
}
