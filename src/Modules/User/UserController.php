<?php

namespace App\Modules\User;

use App\Modules\User\DTO\CreateUserDTO;
use App\Modules\User\DTO\UpdateUserDTO;
use Exception;

/**
 * Camada fina: valida entrada, chama o service, devolve HTTP — equivalente aos seus handle* no Express.
 */
class UserController
{
  private UserService $service;
  private UserValidator $validator;

  public function __construct(UserService $service, UserValidator $validator)
  {
    $this->service = $service;
    $this->validator = $validator;
  }

  public function index(): void
  {
    try {
      $validation = $this->validator->validateListQuery($_GET);
      if (!$validation['success']) {
        $this->jsonResponse($validation['errors'], 400);

        return;
      }

      $data = $validation['data'];
      $result = $this->service->listUsers((int) $data['page'], (int) $data['per_page']);
      $this->jsonResponse($result, 200);
    } catch (Exception $e) {
      $this->jsonResponse(['error' => 'Erro interno: ' . $e->getMessage()], 500);
    }
  }

  public function show(string $id): void
  {
    try {
      $validation = $this->validator->validateId($id);
      if (!$validation['success']) {
        $this->jsonResponse($validation['errors'], 400);

        return;
      }

      $user = $this->service->getUserById($validation['data']['id']);
      if ($user === null) {
        $this->jsonResponse(['error' => 'Usuário não encontrado.'], 404);

        return;
      }

      $this->jsonResponse($user, 200);
    } catch (Exception $e) {
      $this->jsonResponse(['error' => 'Erro interno: ' . $e->getMessage()], 500);
    }
  }

  public function create(): void
  {
    try {
      $validation = $this->validator->validateCreate($this->readJsonBody());
      if (!$validation['success']) {
        $this->jsonResponse($validation['errors'], 400);

        return;
      }

      $valid = $validation['data'];
      $dto = new CreateUserDTO($valid['name'], $valid['email'], $valid['password']);
      $user = $this->service->createUser($dto);

      $this->jsonResponse([
        'message' => 'Usuário criado com sucesso!',
        'user'    => $user,
      ], 201);
    } catch (\DomainException $e) {
      $this->jsonResponse(['error' => $e->getMessage()], 409);
    } catch (Exception $e) {
      $this->jsonResponse(['error' => 'Erro interno: ' . $e->getMessage()], 500);
    }
  }

  public function update(string $id): void
  {
    try {
      $idValidation = $this->validator->validateId($id);
      if (!$idValidation['success']) {
        $this->jsonResponse($idValidation['errors'], 400);

        return;
      }

      $bodyValidation = $this->validator->validateUpdate($this->readJsonBody());
      if (!$bodyValidation['success']) {
        $this->jsonResponse($bodyValidation['errors'], 400);

        return;
      }

      $body = $bodyValidation['data'];
      $dto = new UpdateUserDTO(
        $body['name'] ?? null,
        $body['email'] ?? null,
        $body['password'] ?? null,
      );

      $user = $this->service->updateUser($idValidation['data']['id'], $dto);
      if ($user === null) {
        $this->jsonResponse(['error' => 'Usuário não encontrado.'], 404);

        return;
      }

      $this->jsonResponse($user, 200);
    } catch (\DomainException $e) {
      $this->jsonResponse(['error' => $e->getMessage()], 409);
    } catch (Exception $e) {
      $this->jsonResponse(['error' => 'Erro interno: ' . $e->getMessage()], 500);
    }
  }

  public function destroy(string $id): void
  {
    try {
      $validation = $this->validator->validateId($id);
      if (!$validation['success']) {
        $this->jsonResponse($validation['errors'], 400);

        return;
      }

      if (!$this->service->deleteUser($validation['data']['id'])) {
        $this->jsonResponse(['error' => 'Usuário não encontrado.'], 404);

        return;
      }

      $this->responseNoContent();
    } catch (Exception $e) {
      $this->jsonResponse(['error' => 'Erro interno: ' . $e->getMessage()], 500);
    }
  }

  private function readJsonBody(): array
  {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);

    return is_array($data) ? $data : [];
  }

  private function jsonResponse(array $data, int $statusCode = 200): void
  {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
  }

  private function responseNoContent(): void
  {
    http_response_code(204);
  }
}
