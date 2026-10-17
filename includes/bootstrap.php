<?php
require_once __DIR__ . '/../config.php';

define('ROOT_DIR', dirname(__DIR__));
define('UPLOAD_DIR', ROOT_DIR . '/uploads/');

error_reporting(E_ALL);
ini_set('display_errors', DEBUG ? '1' : '0');

require_once __DIR__ . '/functions.php';

set_exception_handler('handle_fatal');
