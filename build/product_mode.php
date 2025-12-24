<?php

require realpath(__DIR__ . '/../src/vendor/autoload.php');

use OrangeHRM\Config\Config;

echo Config::PRODUCT_MODE;
