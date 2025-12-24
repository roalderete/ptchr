<?php

namespace OrangeHRM\Attendance\Exception;

use Exception;

class AttendanceServiceException extends Exception
{
    /**
     * @return static
     */
    public static function punchOutAlreadyExist(): self
    {
        return new self('Cannot Proceed Punch Out Employee Already Punched Out');
    }

    /**
     * @return static
     */
    public static function punchInAlreadyExist(): self
    {
        return new self('Cannot Proceed Punch In Employee Already Punched In');
    }

    /**
     * @return static
     */
    public static function punchOutTimeBehindThanPunchInTime(): self
    {
        return new self('Punch Out Time Should Be Later Than Punch In Time');
    }

    /**
     * @return static
     */
    public static function punchInOverlapFound(): self
    {
        return new self('Punch-In Overlap Found');
    }

    /**
     * @return static
     */
    public static function punchOutOverlapFound(): self
    {
        return new self('Punch-Out Overlap Found');
    }

    /**
     * @return static
     */
    public static function invalidDateTime(): self
    {
        return new self('Provided Date And Time Invalid');
    }

    /**
     * @return static
     */
    public static function punchOutDateTimeNull(): self
    {
        return new self('Punch Out Date And Time Should Not Be Null');
    }

    /**
     * @return static
     */
    public static function deletableAttendanceRecordIdsEmpty(): self
    {
        return new self('No IDs Found');
    }

    /**
     * @return static
     */
    public static function invalidTimezoneDetails(): self
    {
        return new self('Valid Timezone Offset and Timezone Name Must Be Provided');
    }
}
