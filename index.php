<?php

use OrangeHRM\Config\Config;

require realpath(__DIR__ . '/src/vendor/autoload.php');

/* For logging PHP errors */
include_once('./src/config/log_settings.php');

if (!Config::isInstalled()) {
    header('Location: ./installer/index.php');
} else {
    header("Location: ./web/index.php/auth/login");
}
