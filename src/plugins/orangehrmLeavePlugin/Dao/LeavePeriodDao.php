<?php

namespace OrangeHRM\Leave\Dao;

use Exception;
use OrangeHRM\Core\Dao\BaseDao;
use OrangeHRM\Core\Traits\Service\DateTimeHelperTrait;
use OrangeHRM\Entity\LeavePeriodHistory;
use OrangeHRM\Leave\Traits\Service\LeaveConfigServiceTrait;
use OrangeHRM\Leave\Traits\Service\LeaveEntitlementServiceTrait;
use OrangeHRM\Leave\Traits\Service\LeavePeriodServiceTrait;
use OrangeHRM\ORM\Exception\TransactionException;
use OrangeHRM\ORM\ListSorter;

class LeavePeriodDao extends BaseDao
{
    use LeavePeriodServiceTrait;
    use LeaveEntitlementServiceTrait;
    use LeaveConfigServiceTrait;
    use DateTimeHelperTrait;

    /**
     * @param LeavePeriodHistory $leavePeriodHistory
     * @return LeavePeriodHistory
     * @throws TransactionException
     */
    public function saveLeavePeriodHistory(LeavePeriodHistory $leavePeriodHistory): LeavePeriodHistory
    {
        $this->beginTransaction();
        try {
            $currentLeavePeriod = $this->getCurrentLeavePeriodStartDateAndMonth();

            $isLeavePeriodDefined = $this->getLeaveConfigService()->isLeavePeriodDefined();
            if ($isLeavePeriodDefined) {
                // Fetching current leave period before save new leave period
                $leavePeriodForToday = $this->getLeavePeriodService()->getCurrentLeavePeriodByDate(
                    $this->getDateTimeHelper()->getNow(),
                    true
                );
            } else {
                $this->getLeaveConfigService()->setLeavePeriodDefined(true);
            }
            $this->persist($leavePeriodHistory);

            if (!empty($currentLeavePeriod) && $isLeavePeriodDefined) {
                $oldStartMonth = $currentLeavePeriod->getStartMonth();
                $oldStartDay = $currentLeavePeriod->getStartDay();
                $newStartMonth = $leavePeriodHistory->getStartMonth();
                $newStartDay = $leavePeriodHistory->getStartDay();

                $strategy = $this->getLeaveEntitlementService()->getLeaveEntitlementStrategy();
                $strategy->handleLeavePeriodChange(
                    $leavePeriodForToday,
                    $oldStartMonth,
                    $oldStartDay,
                    $newStartMonth,
                    $newStartDay
                );
            }

            $this->commitTransaction();
        } catch (Exception $e) {
            $this->rollBackTransaction();
            throw new TransactionException($e);
        }

        return $leavePeriodHistory;
    }

    /**
     * @return LeavePeriodHistory|null
     */
    public function getCurrentLeavePeriodStartDateAndMonth(): ?LeavePeriodHistory
    {
        $q = $this->createQueryBuilder(LeavePeriodHistory::class, 'leavePeriod');
        $q->addOrderBy('leavePeriod.createdAt', ListSorter::DESCENDING);
        $q->addOrderBy('leavePeriod.id', ListSorter::DESCENDING);

        return $this->fetchOne($q);
    }

    /**
     * @return LeavePeriodHistory[]
     */
    public function getLeavePeriodHistoryList(): array
    {
        $q = $this->createQueryBuilder(LeavePeriodHistory::class, 'leavePeriod');
        $q->addOrderBy('leavePeriod.createdAt')
            ->addOrderBy('leavePeriod.id');

        return $q->getQuery()->execute();
    }
}
