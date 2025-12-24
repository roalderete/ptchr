<?php

namespace OrangeHRM\Leave\Dao;

use DateTime;
use OrangeHRM\Core\Dao\BaseDao;
use OrangeHRM\Entity\WorkWeek;

class WorkWeekDao extends BaseDao
{
    /**
     * @param WorkWeek $workWeek
     * @return WorkWeek
     */
    public function saveWorkWeek(WorkWeek $workWeek): WorkWeek
    {
        $this->persist($workWeek);
        return $workWeek;
    }

    /**
     * @param int $id
     * @return WorkWeek|null
     */
    public function getWorkWeekById(int $id): ?WorkWeek
    {
        return $this->getRepository(WorkWeek::class)->find($id);
    }

    /**
     * @param DateTime $date
     * @param bool $isFullDay
     * @return bool
     */
    public function isNonWorkingDay(DateTime $date, bool $isFullDay = true): bool
    {
        $q = $this->createQueryBuilder(WorkWeek::class, 'workWeek');
        /** @var WorkWeek $workWeek */
        $workWeek = $this->fetchOne($q);

        $getter = 'get' . $date->format('l');
        if ($isFullDay) {
            return ($workWeek->$getter() == WorkWeek::WORKWEEK_LENGTH_NON_WORKING_DAY);
        } else {
            return ($workWeek->$getter() == WorkWeek::WORKWEEK_LENGTH_HALF_DAY);
        }
    }
}
