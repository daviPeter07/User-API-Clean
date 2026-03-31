<?php

namespace App\Config;

use Bramus\Router\Router;
use App\Database\Connect;

$router = new Router();

$router->get('/', function () {
  header('Content-Type: application/json');
  echo json_encode(['message' => 'API is running']);
});

$router->get('/test-db', function () {
  header('Content-Type: application/json');
  $pdo = Connect::getInstance();

  if ($pdo) {
    echo json_encode(['message' => 'DB Connected']);
  }
});

$router->run();
