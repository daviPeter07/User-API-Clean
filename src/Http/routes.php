<?php

declare(strict_types=1);

namespace App\Http;

use App\Database\Connect;
use App\Modules\User\UserRoute;
use Bramus\Router\Router;

$router = new Router();

$router->get('/', function (): void {
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode([
    'message' => 'API is running',
    'swagger' => '/swagger/index.html',
    'openapi' => '/swagger/openapi.yaml',
  ], JSON_UNESCAPED_UNICODE);
});

$router->get('/docs', function (): void {
  header('Location: /swagger/index.html', true, 302);
  exit;
});

$router->get('/swagger', function (): void {
  header('Location: /swagger/index.html', true, 302);
  exit;
});

$router->get('/test-db', function (): void {
  header('Content-Type: application/json; charset=utf-8');
  $pdo = Connect::getInstance();
  if ($pdo) {
    echo json_encode(['message' => 'DB Connected'], JSON_UNESCAPED_UNICODE);
  }
});

UserRoute::register($router);

$router->run();
