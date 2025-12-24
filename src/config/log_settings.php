<?php

$logFilePath = realpath(__DIR__ . '/../log') . DIRECTORY_SEPARATOR . 'orangehrm.log';

ini_set('log_errors', true);
ini_set('error_log', $logFilePath);
