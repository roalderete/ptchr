<?php

namespace OrangeHRM\CorporateDirectory\Api;

use OrangeHRM\Core\Api\CommonParams;
use OrangeHRM\Core\Api\V2\CrudEndpoint;
use OrangeHRM\Core\Api\V2\Endpoint;
use OrangeHRM\Core\Api\V2\EndpointCollectionResult;
use OrangeHRM\Core\Api\V2\EndpointResourceResult;
use OrangeHRM\Core\Api\V2\EndpointResult;
use OrangeHRM\Core\Api\V2\ParameterBag;
use OrangeHRM\Core\Api\V2\RequestParams;
use OrangeHRM\Core\Api\V2\Model\ArrayModel;
use OrangeHRM\Core\Api\V2\Validator\ParamRule;
use OrangeHRM\Core\Api\V2\Validator\ParamRuleCollection;
use OrangeHRM\Core\Api\V2\Validator\Rule;
use OrangeHRM\Core\Api\V2\Validator\Rules;
use OrangeHRM\Core\Traits\UserRoleManagerTrait;
use OrangeHRM\CorporateDirectory\Api\Model\EmployeeDirectoryDetailedModel;
use OrangeHRM\CorporateDirectory\Api\Model\EmployeeDirectoryModel;
use OrangeHRM\CorporateDirectory\Dto\EmployeeDirectorySearchFilterParams;
use OrangeHRM\CorporateDirectory\Service\EmployeeDirectoryService;
use OrangeHRM\Entity\Employee;

class EmployeeDirectoryAPI extends Endpoint implements CrudEndpoint
{
    use UserRoleManagerTrait;

    public const FILTER_EMP_NUMBER = 'empNumber';
    public const FILTER_NAME_OR_ID = 'nameOrId';
    public const FILTER_JOB_TITLE_ID = 'jobTitleId';
    public const FILTER_LOCATION_ID = 'locationId';
    public const FILTER_MODEL = 'model';
    public const PARAM_RULE_FILTER_NAME_OR_ID_MAX_LENGTH = 100;
    public const MODEL_DEFAULT = 'default';
    public const MODEL_DETAILED = 'detailed';
    public const MODEL_MAP = [
        self::MODEL_DEFAULT => EmployeeDirectoryModel::class,
        self::MODEL_DETAILED => EmployeeDirectoryDetailedModel::class,
    ];

    /**
     * @OA\Get(
     *     path="/api/v2/directory/employees/{empNumber}",
     *     tags={"Directory/Employees"},
     *     summary="Get an Employee Directory Listing",
     *     operationId="get-an-employee-directory-listing",
     *     @OA\PathParameter(
     *         name="empNumber",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="model",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={OrangeHRM\CorporateDirectory\Api\EmployeeDirectoryAPI::MODEL_DEFAULT, OrangeHRM\CorporateDirectory\Api\EmployeeDirectoryAPI::MODEL_DETAILED, OrangeHRM\CorporateDirectory\Api\EmployeeDirectoryAPI::MODEL_DETAILED},
     *             default=OrangeHRM\CorporateDirectory\Api\EmployeeDirectoryAPI::MODEL_DEFAULT
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     oneOf={
     *                         @OA\Schema(ref="#/components/schemas/CorporateDirectory-EmployeeDirectoryModel"),
     *                         @OA\Schema(ref="#/components/schemas/CorporateDirectory-EmployeeDirectoryDetailedModel"),
     *                     }
     *                 )
     *             ),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     )
     * )
     * @inheritDoc
     */
    public function getOne(): EndpointResourceResult
    {
        $empNumber = $this->getRequestParams()->getInt(
            RequestParams::PARAM_TYPE_ATTRIBUTE,
            CommonParams::PARAMETER_EMP_NUMBER
        );
        $employee = $this->getEmployeeDirectoryService()->getEmployeeDirectoryDao()->getEmployeeByEmpNumber($empNumber);
        $this->throwRecordNotFoundExceptionIfNotExist($employee, Employee::class);

        return new EndpointResourceResult($this->getModelClass(), $employee);
    }

    /**
     * @return EmployeeDirectoryService
     */
    public function getEmployeeDirectoryService(): EmployeeDirectoryService
    {
        return new EmployeeDirectoryService();
    }

    /**
     * @return string
     */
    protected function getModelClass(): string
    {
        $model = $this->getRequestParams()->getString(
            RequestParams::PARAM_TYPE_QUERY,
            self::FILTER_MODEL,
            self::MODEL_DEFAULT
        );
        return self::MODEL_MAP[$model];
    }


    /**
     * @inheritDoc
     */
    public function getValidationRuleForGetOne(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(
                CommonParams::PARAMETER_EMP_NUMBER,
                new Rule(Rules::ENTITY_ID_EXISTS, [Employee::class])
            ),
            $this->getModelParamRule(),
        );
    }

    /**
     * @return ParamRule
     */
    protected function getModelParamRule(): ParamRule
    {
        return $this->getValidationDecorator()->notRequiredParamRule(
            new ParamRule(
                self::FILTER_MODEL,
                new Rule(Rules::IN, [array_keys(self::MODEL_MAP)])
            )
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v2/directory/employees",
     *     tags={"Directory/Employees"},
     *     summary="Get the Employee Directory",
     *     operationId="get-the-employee-directory",
     *     @OA\Parameter(
     *         name="empNumber",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="nameOrId",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="jobTitleId",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="locationId",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="model",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={
     *                 OrangeHRM\CorporateDirectory\Api\EmployeeDirectoryAPI::MODEL_DEFAULT,
     *                 OrangeHRM\CorporateDirectory\Api\EmployeeDirectoryAPI::MODEL_DETAILED
     *             },
     *             default=OrangeHRM\CorporateDirectory\Api\EmployeeDirectoryAPI::MODEL_DEFAULT
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     oneOf={
     *                         @OA\Schema(ref="#/components/schemas/CorporateDirectory-EmployeeDirectoryModel"),
     *                         @OA\Schema(ref="#/components/schemas/CorporateDirectory-EmployeeDirectoryDetailedModel"),
     *                     }
     *                 )
     *             ),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     )
     * )
     * @inheritDoc
     */
    public function getAll(): EndpointCollectionResult
    {
        if ($this->getRequest()->getAttributes()->get('_active')) {
            $sessionSavePath = ini_get('session.save_path') ?: sys_get_temp_dir();
            $employees = [];

            $pattern = rtrim($sessionSavePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'sess_*';

            $gcMaxLifetime = (int)ini_get('session.gc_maxlifetime') ?: 1440;
            $now = time();

            $presenceTtl = $this->getRequestParams()->getIntOrNull(RequestParams::PARAM_TYPE_QUERY, 'presence_ttl') ?? min($gcMaxLifetime, 60);

            $debugRequested = $this->getRequestParams()->getBoolean(RequestParams::PARAM_TYPE_QUERY, 'debug_active_sessions', false);
            $debugEntries = [];

            $cachedResult = null;

            $foundCurrEmp = null;
            $foundBy = null;

            $httpSession = $this->getRequest()->getHttpRequest()->getSession();
            if ($httpSession && $httpSession->has(\OrangeHRM\Authentication\Auth\User::IS_AUTHENTICATED) && $httpSession->get(\OrangeHRM\Authentication\Auth\User::IS_AUTHENTICATED) && $httpSession->has(\OrangeHRM\Authentication\Auth\User::USER_EMPLOYEE_NUMBER)) {
                $foundCurrEmp = (int)$httpSession->get(\OrangeHRM\Authentication\Auth\User::USER_EMPLOYEE_NUMBER);
                $foundBy = 'http_session';
            }

            // If still not found, try reading the current session file directly (via session id)
            if (is_null($foundCurrEmp)) {
                try {
                    $sid = null;
                    if ($httpSession && method_exists($httpSession, 'getId')) {
                        $sid = $httpSession->getId();
                    }
                    if ($sid) {
                        $sessionFile = rtrim($sessionSavePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'sess_' . $sid;
                        if (is_readable($sessionFile)) {
                            $fileContent = @file_get_contents($sessionFile);
                            if ($fileContent !== false && $fileContent !== '') {
                                if (preg_match('/user\.user_employee_number[^0-9]*([0-9]+)/', $fileContent, $m)) {
                                    $foundCurrEmp = (int)$m[1];
                                    $foundBy = 'session_file';
                                }
                            }
                        }
                    }
                } catch (\Throwable $e) {
                }
            }

            // Fallback to Auth User singleton (more robust in some contexts)
            if (is_null($foundCurrEmp)) {
                try {
                    $authUser = \OrangeHRM\Authentication\Auth\User::getInstance();
                    $authEmp = $authUser->getEmpNumber();
                    if (!is_null($authEmp)) {
                        $foundCurrEmp = (int)$authEmp;
                        $foundBy = 'auth_user_singleton';
                    }
                } catch (\Throwable $e) {
                }
            }

            if (!is_null($foundCurrEmp)) {
                // merge into cached result or fallback employees
                if ($cachedResult !== null) {
                    $exists = false;
                    foreach ($cachedResult as $r) {
                        if (($r['empNumber'] ?? null) === $foundCurrEmp) {
                            $exists = true;
                            break;
                        }
                    }
                    if (!$exists) {
                        $cachedResult[] = ['empNumber' => $foundCurrEmp];
                    }
                } else {
                    $employees[] = ['empNumber' => $foundCurrEmp];
                }

                if ($debugRequested) {
                    $debugEntries[] = ['file' => null, 'reason' => 'current_request_session', 'empNumber' => $foundCurrEmp, 'matched_by' => $foundBy];
                }
            }

            if ($cachedResult !== null) {
                $employees = $cachedResult;
            }

            // iterate files and only consider recently-modified session files (only when not cached)
            if ($cachedResult === null) {
                foreach (glob($pattern) as $file) {
                    if (!is_readable($file)) {
                        continue;
                    }
                    $mtime = @filemtime($file) ?: 0;
                    $age = $now - $mtime;
                    if ($age > $presenceTtl) {
                        // ignore stale session file (older than presence ttl)
                        if ($debugRequested) {
                            $debugEntries[] = [
                                'file' => basename($file),
                                'reason' => 'stale',
                                'mtime' => $mtime,
                                'age' => $age,
                                'presence_ttl' => $presenceTtl,
                            ];
                        }
                        continue;
                    }

                    $content = @file_get_contents($file);
                    if ($content === false || $content === '') {
                        if ($debugRequested) {
                            $debugEntries[] = [
                                'file' => basename($file),
                                'reason' => 'empty_or_unreadable',
                                'mtime' => $mtime,
                                'age' => $age,
                            ];
                        }
                        continue;
                    }

                    // Simple best-effort session parser similar to the standalone API
                    $sessionVars = [];
                    $offset = 0;
                    $length = strlen($content);
                    while ($offset < $length) {
                        $pos = strpos($content, '|', $offset);
                        if ($pos === false) {
                            break;
                        }
                        $key = substr($content, $offset, $pos - $offset);
                        $offset = $pos + 1;
                        $rest = substr($content, $offset);
                        $try = @unserialize($rest);
                        if ($try !== false || $rest === 'b:0;') {
                            $sessionVars[$key] = $try;
                            break;
                        }
                        $scanLen = 0;
                        $value = null;
                        $success = false;
                        while ($scanLen < strlen($rest)) {
                            $scanLen++;
                            $sub = substr($rest, 0, $scanLen);
                            $try = @unserialize($sub);
                            if ($try !== false || $sub === 'b:0;') {
                                $value = $try;
                                $sessionVars[$key] = $value;
                                $offset += $scanLen;
                                $success = true;
                                break;
                            }
                        }
                        if (!$success) {
                            $nextKeyPos = strpos($rest, '|');
                            if ($nextKeyPos === false) {
                                $sessionVars[$key] = $rest;
                                break;
                            }
                            $sessionVars[$key] = substr($rest, 0, $nextKeyPos);
                            $offset += $nextKeyPos;
                        }
                    }

                    $isAuth = $sessionVars[\OrangeHRM\Authentication\Auth\User::IS_AUTHENTICATED] ?? null;
                    $empNumber = $sessionVars[\OrangeHRM\Authentication\Auth\User::USER_EMPLOYEE_NUMBER] ?? null;

                    // Fallback: if unserialize didn't find values, try raw regex scan
                    $matchedByRegex = false;
                    if ((!$isAuth || !$empNumber) && is_string($content)) {
                        if (preg_match('/user\.is_authenticated[^0-9]*([01])/', $content, $am)) {
                            $isAuth = (bool)intval($am[1]);
                            $matchedByRegex = true;
                        }
                        if (preg_match('/user\.user_employee_number[^0-9]*([0-9]+)/', $content, $bm)) {
                            $empNumber = (int)$bm[1];
                            $matchedByRegex = true;
                        }
                    }

                    if ($isAuth && $empNumber) {
                        $employees[] = ['empNumber' => (int)$empNumber];
                        if ($debugRequested) {
                            $debugEntries[] = [
                                'file' => basename($file),
                                'reason' => 'matched',
                                'empNumber' => (int)$empNumber,
                                'isAuth' => (bool)$isAuth,
                                'mtime' => $mtime,
                                'age' => $age,
                                'matched_by_regex' => $matchedByRegex,
                            ];
                        }
                    } else {
                        if ($debugRequested) {
                            $debugEntries[] = [
                                'file' => basename($file),
                                'reason' => 'no_match',
                                'empNumber' => $empNumber,
                                'isAuth' => (bool)$isAuth,
                                'mtime' => $mtime,
                                'age' => $age,
                                'matched_by_regex' => $matchedByRegex,
                            ];
                        }
                    }
                }
            }

            $unique = [];
            foreach ($employees as $e) {
                $unique[$e['empNumber']] = $e;
            }

            $scannedFiles = glob($pattern);
            $scannedFilesCount = is_array($scannedFiles) ? count($scannedFiles) : 0;
            $processedFilesCount = 0;
            foreach ($scannedFiles ?? [] as $file) {
                if (is_readable($file) && filesize($file) > 0) {
                    $processedFilesCount++;
                }
            }

            $meta = [
                CommonParams::PARAMETER_TOTAL => count($unique),
                'session_save_path' => $sessionSavePath,
                'scanned_files' => $scannedFilesCount,
                'scanned_processed_files' => $processedFilesCount,
                'gc_max_lifetime' => $gcMaxLifetime,
                'presence_ttl' => $presenceTtl,
            ];
            if ($debugRequested) {
                $meta['debug_sessions'] = $debugEntries;
            }

            return new EndpointCollectionResult(
                ArrayModel::class,
                array_values($unique),
                new ParameterBag($meta)
            );
        }
        $employeeDirectoryParamHolder = new EmployeeDirectorySearchFilterParams();
        $this->setSortingAndPaginationParams($employeeDirectoryParamHolder);

        $empNumber = $this->getRequestParams()->getIntOrNull(
            RequestParams::PARAM_TYPE_QUERY,
            self::FILTER_EMP_NUMBER
        );
        if (!is_null($empNumber)) {
            $employeeDirectoryParamHolder->setEmpNumbers([$empNumber]);
        }
        $employeeDirectoryParamHolder->setNameOrId(
            $this->getRequestParams()->getStringOrNull(
                RequestParams::PARAM_TYPE_QUERY,
                self::FILTER_NAME_OR_ID
            )
        );
        $employeeDirectoryParamHolder->setJobTitleId(
            $this->getRequestParams()->getIntOrNull(
                RequestParams::PARAM_TYPE_QUERY,
                self::FILTER_JOB_TITLE_ID
            )
        );
        $employeeDirectoryParamHolder->setLocationId(
            $this->getRequestParams()->getIntOrNull(
                RequestParams::PARAM_TYPE_QUERY,
                self::FILTER_LOCATION_ID
            )
        );

        $employees = $this->getEmployeeDirectoryService()->getEmployeeDirectoryDao()->getEmployeeList(
            $employeeDirectoryParamHolder
        );
        $count = $this->getEmployeeDirectoryService()->getEmployeeDirectoryDao()->getEmployeeCount(
            $employeeDirectoryParamHolder
        );
        return new EndpointCollectionResult(
            $this->getModelClass(),
            $employees,
            new ParameterBag([CommonParams::PARAMETER_TOTAL => $count])
        );
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForGetAll(): ParamRuleCollection
    {
        $collection = new ParamRuleCollection(
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::FILTER_EMP_NUMBER,
                    new Rule(Rules::ENTITY_ID_EXISTS, [Employee::class])
                )
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::FILTER_NAME_OR_ID,
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [null, self::PARAM_RULE_FILTER_NAME_OR_ID_MAX_LENGTH]),
                )
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::FILTER_JOB_TITLE_ID,
                    new Rule(Rules::POSITIVE),
                )
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::FILTER_LOCATION_ID,
                    new Rule(Rules::POSITIVE),
                )
            ),
            $this->getModelParamRule(),
            ...$this->getSortingAndPaginationParamsRules(EmployeeDirectorySearchFilterParams::ALLOWED_SORT_FIELDS)
        );
        $collection->addExcludedParamKey('_active');
        return $collection;
    }

    /**
     * @inheritDoc
     */
    public function create(): EndpointResourceResult
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForCreate(): ParamRuleCollection
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function delete(): EndpointResourceResult
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForDelete(): ParamRuleCollection
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function update(): EndpointResult
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForUpdate(): ParamRuleCollection
    {
        throw $this->getNotImplementedException();
    }
}
