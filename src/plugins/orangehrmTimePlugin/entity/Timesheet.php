<?php

namespace OrangeHRM\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use OrangeHRM\Entity\Decorator\DecoratorTrait;
use OrangeHRM\Entity\Decorator\TimesheetDecorator;

/**
 * @method TimesheetDecorator getDecorator()
 *
 * @ORM\Table(name="ohrm_timesheet")
 * @ORM\Entity
 */
class Timesheet
{
    use DecoratorTrait;

    public const STATE_INITIAL = "INITIAL";
    public const RESET_ACTION = "RESET";

    /**
     * @var int
     *
     * @ORM\Column(name="timesheet_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $id;

    /**
     * @var string
     *
     * @ORM\Column(name="state", type="string", length=255, nullable=false)
     */
    private string $state;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="start_date", type="date", nullable=false)
     */
    private DateTime $startDate;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="end_date", type="date", nullable=false)
     */
    private DateTime $endDate;

    /**
     * @var Employee
     *
     * @ORM\ManyToOne(targetEntity="OrangeHRM\Entity\Employee")
     * @ORM\JoinColumn(name="employee_id", referencedColumnName="emp_number")
     */
    private Employee $employee;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * @param string $state
     */
    public function setState(string $state): void
    {
        $this->state = $state;
    }

    /**
     * @return DateTime
     */
    public function getStartDate(): DateTime
    {
        return $this->startDate;
    }

    /**
     * @param DateTime $startDate
     */
    public function setStartDate(DateTime $startDate): void
    {
        $this->startDate = $startDate;
    }

    /**
     * @return DateTime
     */
    public function getEndDate(): DateTime
    {
        return $this->endDate;
    }

    /**
     * @param DateTime $endDate
     */
    public function setEndDate(DateTime $endDate): void
    {
        $this->endDate = $endDate;
    }

    /**
     * @return Employee
     */
    public function getEmployee(): Employee
    {
        return $this->employee;
    }

    /**
     * @param Employee $employee
     */
    public function setEmployee(Employee $employee): void
    {
        $this->employee = $employee;
    }
}
