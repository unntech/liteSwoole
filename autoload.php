<?php

use App\framework\LiApp;

const IN_LitePhp = true;
define('DT_ROOT', str_replace("\\", '/', __DIR__ ));
define('APP_START_TIME', time());
require_once DT_ROOT . '/vendor/autoload.php';

LiPhp\Lite::setRootPath(DT_ROOT);
LiApp::initialize();

require_once DT_ROOT . '/include/common.php';
set_exception_handler('exception_handler');

