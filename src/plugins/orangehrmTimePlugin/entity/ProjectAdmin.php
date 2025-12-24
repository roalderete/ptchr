<?php

namespace OrangeHRM\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="ohrm_project_admin")
 * @ORM\Entity
 */
class ProjectAdmin
{
    /**
     * @var Project
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="OrangeHRM\Entity\Project", cascade={"persist"})
     * @ORM\JoinColumn(name="project_id", referencedColumnName="project_id", nullable=false)
     */
    private Project $project;

    /**
     * @var Employee
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="OrangeHRM\Entity\Employee", cascade={"persist"})
     * @ORM\JoinColumn(name="emp_number", referencedColumnName="emp_number", nullable=false)
     */
    private Employee $employee;
}
