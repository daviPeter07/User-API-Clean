<?php

namespace App\Modules\User;

use App\Http\JsonErrorHandler;
use Bramus\Router\Router;

final class UserRoute
{
  private const UUID_SEGMENT = '([0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12})';

  public static function register(Router $router): void
  {
    $repository = new UserRepository();
    $validator = new UserValidator();
    $service = new UserService($repository);
    $errors = new JsonErrorHandler();
    $controller = new UserController($service, $validator, $errors);

    $base = '/api/users';

    $router->get($base, function () use ($controller) {
      $controller->index();
    });

    $router->post($base, function () use ($controller) {
      $controller->create();
    });

    $withId = $base . '/' . self::UUID_SEGMENT;

    $router->get($withId, function (string $id) use ($controller) {
      $controller->show($id);
    });

    $router->put($withId, function (string $id) use ($controller) {
      $controller->update($id);
    });

    $router->patch($withId, function (string $id) use ($controller) {
      $controller->update($id);
    });

    $router->delete($withId, function (string $id) use ($controller) {
      $controller->destroy($id);
    });
  }
}
