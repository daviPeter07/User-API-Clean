<?php

namespace App\Modules\User;

use Bramus\Router\Router;

final class UserRoute
{
  public static function register(Router $router): void
  {
    $repository = new UserRepository();
    $validator = new UserValidator();
    $service = new UserService($repository);
    $controller = new UserController($service, $validator);

    $router->post('/api/users', function () use ($controller) {
      $controller->create();
    });
  }
}
