<?php

declare(strict_types=1);

namespace Tests\Modules\User;

use App\Modules\User\DTO\CreateUserDTO;
use App\Modules\User\DTO\UpdateUserDTO;
use App\Modules\User\UserRepository;
use App\Modules\User\UserService;
use DomainException;
use PHPUnit\Framework\TestCase;

final class UserServiceTest extends TestCase
{
  public function testCreateUserLancaDomainExceptionQuandoEmailJaExiste(): void
  {
    $repo = $this->createMock(UserRepository::class);
    $repo->method('findByEmail')->willReturn([
      'id'    => 'existing-uuid',
      'email' => 'dup@example.com',
    ]);

    $service = new UserService($repo);

    $this->expectException(DomainException::class);
    $this->expectExceptionMessage('E-mail já cadastrado.');

    $service->createUser(new CreateUserDTO('Nome', 'dup@example.com', 'senha1234'));
  }

  public function testCreateUserPersisteERetornaUsuarioSemSenha(): void
  {
    $repo = $this->createMock(UserRepository::class);
    $repo->method('findByEmail')->willReturn(null);
    $repo->expects($this->once())->method('create')->with($this->callback(function (array $row): bool {
      return isset($row['id'], $row['name'], $row['email'], $row['password'])
        && $row['name'] === 'Ana'
        && $row['email'] === 'ana@example.com'
        && password_verify('senha1234', $row['password']);
    }));

    $returnedRow = [
      'id'         => '550e8400-e29b-41d4-a716-446655440000',
      'name'       => 'Ana',
      'email'      => 'ana@example.com',
      'created_at' => '2026-01-01 10:00:00',
      'updated_at' => '2026-01-01 10:00:00',
    ];
    $repo->method('findById')->willReturn($returnedRow);

    $service = new UserService($repo);
    $result = $service->createUser(new CreateUserDTO('Ana', 'ana@example.com', 'senha1234'));

    $this->assertSame($returnedRow['id'], $result['id']);
    $this->assertSame('Ana', $result['name']);
    $this->assertArrayNotHasKey('password', $result);
  }

  public function testListUsersRetornaDadosEMeta(): void
  {
    $repo = $this->createMock(UserRepository::class);
    $repo->method('count')->willReturn(25);
    $repo->method('findAll')->with(10, 10)->willReturn([
      ['id' => 'a', 'name' => 'U1', 'email' => 'u1@test.com', 'created_at' => 'x', 'updated_at' => 'y'],
    ]);

    $service = new UserService($repo);
    $out = $service->listUsers(2, 10);

    $this->assertCount(1, $out['data']);
    $this->assertSame(2, $out['meta']['page']);
    $this->assertSame(10, $out['meta']['per_page']);
    $this->assertSame(25, $out['meta']['total']);
    $this->assertSame(3, $out['meta']['total_pages']);
  }

  public function testUpdateUserRetornaNullQuandoIdNaoExiste(): void
  {
    $repo = $this->createMock(UserRepository::class);
    $repo->method('findById')->willReturn(null);

    $service = new UserService($repo);
    $dto = new UpdateUserDTO(name: 'Novo');

    $this->assertNull($service->updateUser('missing-uuid', $dto));
  }

  public function testUpdateUserLancaQuandoEmailPertenceAUOutroUsuario(): void
  {
    $repo = $this->createMock(UserRepository::class);
    $repo->method('findById')->willReturn([
      'id'    => 'my-id',
      'email' => 'meu@example.com',
    ]);
    $repo->method('findByEmail')->willReturn([
      'id'    => 'outro-id',
      'email' => 'outro@example.com',
    ]);

    $service = new UserService($repo);

    $this->expectException(DomainException::class);
    $this->expectExceptionMessage('E-mail já cadastrado.');

    $service->updateUser('my-id', new UpdateUserDTO(email: 'outro@example.com'));
  }
}
