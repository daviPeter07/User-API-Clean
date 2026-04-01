<?php

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = $uri === false ? '/' : $uri;
$file = __DIR__ . $uri;

if ($uri !== '/' && is_file($file)) {
  return false;
}

require __DIR__ . '/index.php';
