<?php

declare(strict_types=1);

namespace Tests\Http;

use App\Http\JsonErrorHandler;
use DomainException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class JsonErrorHandlerTest extends TestCase
{
  public function testFormatUsaMensagemPadraoEmPortuguesPara404(): void
  {
    $handler = new JsonErrorHandler();
    $payload = $handler->format(404);

    $this->assertSame(404, $payload['erro']['codigo_http']);
    $this->assertSame('Recurso não encontrado.', $payload['erro']['mensagem']);
    $this->assertNull($payload['erro']['detalhes']);
  }

  public function testFormatSobrescreveMensagemPadrao(): void
  {
    $handler = new JsonErrorHandler();
    $payload = $handler->format(404, 'Usuário não encontrado.');

    $this->assertSame('Usuário não encontrado.', $payload['erro']['mensagem']);
  }

  public function testFormatIncluiDetalhesQuandoInformados(): void
  {
    $handler = new JsonErrorHandler();
    $detalhes = ['email' => 'E-mail inválido.'];
    $payload = $handler->format(400, null, $detalhes);

    $this->assertSame($detalhes, $payload['erro']['detalhes']);
  }

  public function testSendEmiteJsonComEstruturaEsperada(): void
  {
    ob_start();
    $handler = new JsonErrorHandler();
    $handler->send(409, 'E-mail já cadastrado.');
    $output = ob_get_clean();

    $data = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
    $this->assertSame(409, $data['erro']['codigo_http']);
    $this->assertSame('E-mail já cadastrado.', $data['erro']['mensagem']);
  }

  public function testFromThrowableDomainExceptionEnvia409(): void
  {
    ob_start();
    $handler = new JsonErrorHandler();
    $handler->fromThrowable(new DomainException('E-mail já cadastrado.'));
    $output = ob_get_clean();

    $data = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
    $this->assertSame(409, $data['erro']['codigo_http']);
    $this->assertStringContainsString('E-mail já cadastrado', $data['erro']['mensagem']);
  }

  public function testFromThrowableGenericaEnvia500SemExporDetalhesQuandoAppDebugDesligado(): void
  {
    unset($_ENV['APP_DEBUG']);

    ob_start();
    $handler = new JsonErrorHandler();
    $handler->fromThrowable(new RuntimeException('Segredo interno'));
    $output = ob_get_clean();

    $data = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
    $this->assertSame(500, $data['erro']['codigo_http']);
    $this->assertStringNotContainsString('Segredo interno', $data['erro']['mensagem']);
  }

  public function testFromThrowableGenericaEnviaDetalheQuandoAppDebugLigado(): void
  {
    $_ENV['APP_DEBUG'] = 'true';

    ob_start();
    $handler = new JsonErrorHandler();
    $handler->fromThrowable(new RuntimeException('Segredo interno'));
    $output = ob_get_clean();

    unset($_ENV['APP_DEBUG']);

    $data = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
    $this->assertSame(500, $data['erro']['codigo_http']);
    $this->assertStringContainsString('Segredo interno', $data['erro']['mensagem']);
  }
}
