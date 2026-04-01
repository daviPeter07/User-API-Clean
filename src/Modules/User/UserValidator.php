<?php

namespace App\Modules\User;

use Rakit\Validation\Validator;

class UserValidator
{
  private Validator $validator;

  public function __construct()
  {
    $this->validator = new Validator();
  }

  private function commonMessages(): array
  {
    return [
      'required' => 'O campo :attribute é obrigatório.',
      'email'    => 'O campo :attribute deve ser um email válido.',
      'min'      => 'O campo :attribute deve ter no mínimo :min caracteres.',
      'max'      => 'O campo :attribute deve ter no máximo :max caracteres.',
      'integer'  => 'O campo :attribute deve ser um número inteiro.',
      'regex'    => 'O campo :attribute tem formato inválido.',
    ];
  }

  public function validateCreate(array $data): array
  {
    $validation = $this->validator->make($data, [
      'name'     => 'required|min:3',
      'email'    => 'required|email',
      'password' => 'required|min:8',
    ]);

    $validation->setMessages($this->commonMessages());
    $validation->validate();

    if ($validation->fails()) {
      return [
        'success' => false,
        'errors'  => $validation->errors()->firstOfAll(),
      ];
    }

    return [
      'success' => true,
      'data'    => $validation->getValidData(),
    ];
  }

  public function validateListQuery(array $query): array
  {
    $data = [
      'page'     => isset($query['page']) ? (int) $query['page'] : 1,
      'per_page' => isset($query['per_page']) ? (int) $query['per_page'] : 10,
    ];

    $validation = $this->validator->make($data, [
      'page'     => 'required|integer|min:1',
      'per_page' => 'required|integer|min:1|max:100',
    ]);

    $validation->setMessages($this->commonMessages());
    $validation->validate();

    if ($validation->fails()) {
      return [
        'success' => false,
        'errors'  => $validation->errors()->firstOfAll(),
      ];
    }

    return [
      'success' => true,
      'data'    => $validation->getValidData(),
    ];
  }

  public function validateId(string $id): array
  {
    $validation = $this->validator->make(
      ['id' => $id],
      [
        'id' => 'required|regex:/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
      ]
    );

    $validation->setMessages($this->commonMessages());
    $validation->validate();

    if ($validation->fails()) {
      return [
        'success' => false,
        'errors'  => $validation->errors()->firstOfAll(),
      ];
    }

    return [
      'success' => true,
      'data'    => ['id' => $id],
    ];
  }

  public function validateUpdate(array $data): array
  {
    $rules = [];
    if (array_key_exists('name', $data)) {
      $rules['name'] = 'required|min:3';
    }
    if (array_key_exists('email', $data)) {
      $rules['email'] = 'required|email';
    }
    if (array_key_exists('password', $data)) {
      $rules['password'] = 'required|min:8';
    }

    if ($rules === []) {
      return [
        'success' => false,
        'errors'  => ['body' => 'Informe ao menos um campo: name, email ou password.'],
      ];
    }

    $validation = $this->validator->make($data, $rules);
    $validation->setMessages($this->commonMessages());
    $validation->validate();

    if ($validation->fails()) {
      return [
        'success' => false,
        'errors'  => $validation->errors()->firstOfAll(),
      ];
    }

    return [
      'success' => true,
      'data'    => $validation->getValidData(),
    ];
  }
}
