<?php

namespace App\Modules\User;

use App\Http\JsonErrorHandler;
use App\Modules\User\DTO\CreateUserDTO;
use App\Modules\User\DTO\UpdateUserDTO;
use Exception;

class UserController
{
  private UserService $service;
  private UserValidator $validator;
  private JsonErrorHandler $errors;

  public function __construct(
    UserService $service,
    UserValidator $validator,
    JsonErrorHandler $errors,
  ) {
    $this->service = $service;
    $this->validator = $validator;
    $this->errors = $errors;
  }

  public function index(): void
  {
    try {
      $validation = $this->validator->validateListQuery($_GET);
      if (!$validation['success']) {
        $this->errors->sendValidationErrors($validation['errors']);

        return;
      }

      $data = $validation['data'];
      $result = $this->service->listUsers((int) $data['page'], (int) $data['per_page']);
      $this->jsonResponse($result, 200);
    } catch (Exception $e) {
      $this->errors->fromThrowable($e);
    }
  }

  public function show(string $id): void
  {
    try {
      $validation = $this->validator->validateId($id);
      if (!$validation['success']) {
        $this->errors->sendValidationErrors($validation['errors']);

        return;
      }

      $user = $this->service->getUserById($validation['data']['id']);
      if ($user === null) {
        $this->errors->send(404, 'Usuário não encontrado.');

        return;
      }

      $this->jsonResponse($user, 200);
    } catch (Exception $e) {
      $this->errors->fromThrowable($e);
    }
  }

  public function create(): void
  {
    try {
      $validation = $this->validator->validateCreate($this->readJsonBody());
      if (!$validation['success']) {
        $this->errors->sendValidationErrors($validation['errors']);

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
      $this->errors->fromThrowable($e);
    } catch (Exception $e) {
      $this->errors->fromThrowable($e);
    }
  }

  public function update(string $id): void
  {
    try {
      $idValidation = $this->validator->validateId($id);
      if (!$idValidation['success']) {
        $this->errors->sendValidationErrors($idValidation['errors']);

        return;
      }

      $bodyValidation = $this->validator->validateUpdate($this->readJsonBody());
      if (!$bodyValidation['success']) {
        $this->errors->sendValidationErrors($bodyValidation['errors']);

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
        $this->errors->send(404, 'Usuário não encontrado.');

        return;
      }

      $this->jsonResponse($user, 200);
    } catch (\DomainException $e) {
      $this->errors->fromThrowable($e);
    } catch (Exception $e) {
      $this->errors->fromThrowable($e);
    }
  }

  public function destroy(string $id): void
  {
    try {
      $validation = $this->validator->validateId($id);
      if (!$validation['success']) {
        $this->errors->sendValidationErrors($validation['errors']);

        return;
      }

      if (!$this->service->deleteUser($validation['data']['id'])) {
        $this->errors->send(404, 'Usuário não encontrado.');

        return;
      }

      $this->responseNoContent();
    } catch (Exception $e) {
      $this->errors->fromThrowable($e);
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
    if (!headers_sent()) {
      header('Content-Type: application/json; charset=utf-8');
    }
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
  }

  private function responseNoContent(): void
  {
    http_response_code(204);
  }
}
