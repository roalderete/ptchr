<?php
/**
 * Active employees endpoint to report currently logged in employees.
 */

namespace OrangeHRM\CorporateDirectory\Api;

use OrangeHRM\Core\Api\V2\Endpoint;
use OrangeHRM\Core\Api\V2\EndpointResourceResult;
use OrangeHRM\Core\Api\V2\Model\ArrayModel;
use OrangeHRM\Authentication\Auth\User as AuthUser;

class ActiveEmployeesAPI extends Endpoint
{
    /**
     * @OA\Get(
     *     path="/api/v2/directory/active-employees",
     *     tags={"Directory/Employees"},
     *     summary="Get list of currently active employees (empNumber list)",
     *     operationId="get-active-employees",
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function getAll(): EndpointResourceResult
    {
        $sessionSavePath = ini_get('session.save_path') ?: sys_get_temp_dir();
        $employees = [];

        // try common session file pattern
        $pattern = rtrim($sessionSavePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'sess_*';
        foreach (glob($pattern) as $file) {
            if (!is_readable($file)) {
                continue;
            }
            $content = @file_get_contents($file);
            if ($content === false || $content === '') {
                continue;
            }

            $sessionVars = $this->parseSessionData($content);
            $isAuth = $sessionVars[AuthUser::IS_AUTHENTICATED] ?? null;
            $empNumber = $sessionVars[AuthUser::USER_EMPLOYEE_NUMBER] ?? null;

            if ($isAuth && $empNumber) {
                $employees[] = ['empNumber' => (int)$empNumber];
            }
        }

        // Make unique by empNumber
        $unique = [];
        foreach ($employees as $e) {
            $unique[$e['empNumber']] = $e;
        }

        return new EndpointResourceResult(ArrayModel::class, array_values($unique));
    }

    /**
     * Parse PHP session storage string into an associative array.
     * This is a best-effort parser that attempts to unserialize each value.
     *
     * @param string $data
     * @return array
     */
    private function parseSessionData(string $data): array
    {
        $return = [];
        $offset = 0;
        $length = strlen($data);

        while ($offset < $length) {
            $pos = strpos($data, '|', $offset);
            if ($pos === false) {
                break;
            }
            $key = substr($data, $offset, $pos - $offset);
            $offset = $pos + 1;

            // try to unserialize progressively until successful
            $rest = substr($data, $offset);
            $value = null;
            $success = false;
            // quick attempt: try to unserialize whole rest
            $try = @unserialize($rest);
            if ($try !== false || $rest === 'b:0;') {
                $value = $try;
                $return[$key] = $value;
                break; // consumed rest
            }

            // otherwise, incrementally increase length
            $scanLen = 0;
            while ($scanLen < strlen($rest)) {
                $scanLen++;
                $sub = substr($rest, 0, $scanLen);
                $try = @unserialize($sub);
                if ($try !== false || $sub === 'b:0;') {
                    $value = $try;
                    $return[$key] = $value;
                    $offset += $scanLen;
                    $success = true;
                    break;
                }
            }

            if (!$success) {
                // fallback: store raw string until next key occurrence (best-effort)
                $nextKeyPos = strpos($rest, '|');
                if ($nextKeyPos === false) {
                    $return[$key] = $rest;
                    break;
                }
                $return[$key] = substr($rest, 0, $nextKeyPos);
                $offset += $nextKeyPos;
            }
        }

        return $return;
    }
}
