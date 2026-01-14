<?php

declare(strict_types=1);

$config = require __DIR__ . '/../bootstrap.php';

use Zcc\Core\Http\Request;
use Zcc\Core\Kernel;

$request = Request::fromGlobals();
$kernel = new Kernel($config);
$response = $kernel->handle($request);
$response->send();
