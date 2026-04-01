<?php

namespace App\Modules\User;

use App\Modules\User\DTO\CreateUserDTO;
use Exception;

class UserController
{
  private UserService $service;
  private UserValidator $validator;

  public function __construct(UserService $service, UserValidator $validator)
  {
    $this->service = $service;
    $this->validator = $validator;
  }

  public function create(): void
  {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true) ?? [];

    $validation = $this->validator->validateCreate($data);

    if (!$validation['success']) {
      $this->jsonResponse($validation['errors'], 400); //bad request
      return;
    }

    try {
      $validData = $validation['data'];
      $dto = new CreateUserDTO(
        $validData['name'],
        $validData['email'],
        $validData['password']
      );

      $user = $this->service->createUser($dto);

      $this->jsonResponse([
        'message' => 'Usuário criado com sucesso!',
        'user'    => $user
      ], 201); //created

    } catch (Exception $e) {
      $this->jsonResponse(['error' => 'Erro interno: ' . $e->getMessage()], 500);
    }
  }

  private function jsonResponse(array $data, int $statusCode = 200): void
  {
    header('Content-Type: application/json');
    http_response_code($statusCode);
    echo json_encode($data);
  }
}
