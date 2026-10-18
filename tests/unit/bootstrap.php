<?php

require_once __DIR__ . '/../../vendor/autoload.php';

\define('EPI_IMPRESSUM_BASE', '/wordpress/wp-content/plugins/impressum');
\define('EPI_IMPRESSUM_FILE', __FILE__);
\define('EPI_IMPRESSUM_VERSION', '3.1.0');

require_once __DIR__ . '/helpers/wp-error.php';
require_once __DIR__ . '/helpers/container.php';
require_once __DIR__ . '/helpers/wp-rest.php';
