<?php

namespace API\index;

require_once("{$_SERVER['DOCUMENT_ROOT']}/vendor/autoload.php");

$real_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = "{$_SERVER['DOCUMENT_ROOT']}{$real_path}.php";

if (file_exists($uri)) {
    require_once($uri);

    die();
}

echo '
    <div style="width: 700px;">
        <h1 style="margin: 0 auto; display: block; width: 200px;">404 error</h1>
    </div>
';
