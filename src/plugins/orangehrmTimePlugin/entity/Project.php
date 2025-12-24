<?php

namespace OrangeHRM\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use OrangeHRM\Entity\Decorator\DecoratorTrait;
use OrangeHRM\Entity\Decorator\ProjectDecorator;

/**
 * @method ProjectDecorator getDecorator()
 *
 * @ORM\Table(name="ohrm_project")
 * @ORM\Entity
 *
 */
class Project
{
    use DecoratorTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="project_id", type="integer", length=13)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     */
    private int $id;

    /**
     * @var Customer
     *
     * @ORM\ManyToOne(targetEntity="OrangeHRM\Entity\Customer", inversedBy="projects", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="customer_id", nullable=false)
     */
    private Customer $customer;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=true)
     */
    private ?string $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="string", length=256, nullable=true)
     */
    private ?string $description;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_deleted", type="boolean", options={"default" : 0})
     */
    private bool $deleted = false;

    /**
     * @ORM\ManyToMany(targetEntity="OrangeHRM\Entity\Employee", cascade={"persist", "remove"})
     * @ORM\JoinTable(
     *     name="ohrm_project_admin",
     *     joinColumns={@ORM\JoinColumn(name="project_id", referencedColumnName="project_id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="emp_number", referencedColumnName="emp_number")}
     * )
     */
    private iterable $projectAdmins;

    public function __construct()
    {
        $this->projectAdmins = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param  int  $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return Customer
     */
    public function getCustomer(): Customer
    {
        return $this->customer;
    }

    /**
     * @param  Customer  $customer
     */
    public function setCustomer(Customer $customer): void
    {
        $this->customer = $customer;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param  string|null  $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param  string|null  $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return bool
     */
    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    /**
     * @param  bool  $isDeleted
     */
    public function setDeleted(bool $isDeleted): void
    {
        $this->deleted = $isDeleted;
    }

    /**
     * @return iterable|Employee[]
     */
    public function getProjectAdmins(): iterable
    {
        return $this->projectAdmins;
    }
}
