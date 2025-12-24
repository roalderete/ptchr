<?php

namespace OrangeHRM\Leave\Dao;

use OrangeHRM\Core\Dao\BaseDao;
use OrangeHRM\Entity\LeaveRequest;
use OrangeHRM\Entity\LeaveType;
use OrangeHRM\Leave\Dto\LeaveTypeSearchFilterParams;
use OrangeHRM\ORM\Paginator;

class LeaveTypeDao extends BaseDao
{
    /**
     * @return LeaveType[]
     */
    public function getLeaveTypeList(): array
    {
        $q = $this->createQueryBuilder(LeaveType::class, 'leaveType');
        $q->andWhere('leaveType.deleted = :deleted')
            ->setParameter('deleted', false);
        $q->orderBy('leaveType.name');

        return $q->getQuery()->execute();
    }

    /**
     * @param int $id
     * @return LeaveType|null
     */
    public function getLeaveTypeById(int $id): ?LeaveType
    {
        return $this->getRepository(LeaveType::class)->find($id);
    }

    /**
     * @param int[] $ids
     * @return int[]
     */
    public function getExistingLeaveTypeIds(array $ids): array
    {
        $qb = $this->createQueryBuilder(LeaveType::class, 'leaveType');

        $qb->select('leaveType.id')
            ->andWhere($qb->expr()->in('leaveType.id', ':ids'))
            ->andWhere($qb->expr()->eq('leaveType.deleted', ':deleted'))
            ->setParameter('ids', $ids)
            ->setParameter('deleted', false);

        return $qb->getQuery()->getSingleColumnResult();
    }

    /**
     * @param LeaveType $leaveType
     * @return LeaveType
     */
    public function saveLeaveType(LeaveType $leaveType): LeaveType
    {
        $this->persist($leaveType);
        return $leaveType;
    }

    /**
     * @param int[] $idsToDelete
     * @return int
     */
    public function deleteLeaveType(array $idsToDelete): int
    {
        $q = $this->createQueryBuilder(LeaveType::class, 'leaveType');
        $q->update();
        $q->where($q->expr()->in('leaveType.id', ':ids'))
            ->setParameter('ids', $idsToDelete);
        $q->set('leaveType.deleted', ':deleted')
            ->setParameter('deleted', true);

        return $q->getQuery()->execute();
    }

    /**
     * @return LeaveType[]
     */
    public function getDeletedLeaveTypeList(): array
    {
        $q = $this->createQueryBuilder(LeaveType::class, 'leaveType');
        $q->andWhere('leaveType.deleted = :deleted')
            ->setParameter('deleted', true);
        $q->orderBy('leaveType.name');

        return $q->getQuery()->execute();
    }

    /**
     * @param $leaveTypeName
     * @return LeaveType|null
     */
    public function getLeaveTypeByName($leaveTypeName): ?LeaveType
    {
        $q = $this->createQueryBuilder(LeaveType::class, 'leaveType');
        $q->andWhere('leaveType.name = :name')
            ->setParameter('name', $leaveTypeName);
        $q->andWhere('leaveType.deleted = :deleted')
            ->setParameter('deleted', false);

        return $this->fetchOne($q);
    }

    /**
     * @param int $leaveTypeId
     * @return LeaveType|null
     */
    public function undeleteLeaveType(int $leaveTypeId): ?LeaveType
    {
        $leaveType = $this->getLeaveTypeById($leaveTypeId);
        if ($leaveType instanceof LeaveType) {
            $leaveType->setDeleted(false);
            $this->persist($leaveType);
        }
        return $leaveType;
    }

    /**
     * Search Leave Type
     *
     * @param LeaveTypeSearchFilterParams $leaveTypeSearchParams
     * @return LeaveType[]
     */
    public function searchLeaveType(LeaveTypeSearchFilterParams $leaveTypeSearchParams): array
    {
        $paginator = $this->getSearchLeaveTypePaginator($leaveTypeSearchParams);
        return $paginator->getQuery()->execute();
    }

    /**
     * @param LeaveTypeSearchFilterParams $leaveTypeSearchParams
     * @return Paginator
     */
    private function getSearchLeaveTypePaginator(LeaveTypeSearchFilterParams $leaveTypeSearchParams): Paginator
    {
        $q = $this->createQueryBuilder(LeaveType::class, 'leaveType');
        $this->setSortingAndPaginationParams($q, $leaveTypeSearchParams);

        if (!empty($leaveTypeSearchParams->getName())) {
            $q->andWhere('leaveType.name = :name');
            $q->setParameter('name', $leaveTypeSearchParams->getName());
        }
        $q->andWhere('leaveType.deleted = :deleted')
            ->setParameter('deleted', false);
        return $this->getPaginator($q);
    }

    /**
     * Get Count of Search Query
     *
     * @param LeaveTypeSearchFilterParams $leaveTypeSearchParams
     * @return int
     */
    public function getSearchLeaveTypesCount(LeaveTypeSearchFilterParams $leaveTypeSearchParams): int
    {
        $paginator = $this->getSearchLeaveTypePaginator($leaveTypeSearchParams);
        return $paginator->count();
    }

    /**
     * @param int $empNumber
     * @param int $leaveTypeId
     * @return bool
     */
    public function hasEmployeeAllocatedLeavesForLeaveType(int $empNumber, int $leaveTypeId): bool
    {
        $q = $this->createQueryBuilder(LeaveRequest::class, 'lr')
            ->andWhere('lr.employee = :empNumber')
            ->setParameter('empNumber', $empNumber)
            ->andWhere('lr.leaveType = :leaveTypeId')
            ->setParameter('leaveTypeId', $leaveTypeId);
        return $this->getPaginator($q)->count() > 0;
    }
}
