<?php

namespace App\Http;

use DomainException;
use Throwable;

/**
 * Respostas JSON de erro padronizadas, com códigos HTTP e textos em português.
 */
final class JsonErrorHandler
{
  /** @var array<int, string> */
  private const MENSAGENS_PADRAO = [
    400 => 'Requisição inválida.',
    401 => 'Não autorizado.',
    403 => 'Acesso negado.',
    404 => 'Recurso não encontrado.',
    405 => 'Método não permitido.',
    409 => 'Conflito com o estado atual do recurso.',
    422 => 'Não foi possível processar os dados enviados.',
    429 => 'Muitas requisições. Tente novamente em instantes.',
    500 => 'Ocorreu um erro interno. Tente novamente mais tarde.',
    503 => 'Serviço temporariamente indisponível.',
  ];

  /**
   * Monta o array da resposta (útil em testes unitários sem enviar headers).
   *
   * @param array<string, mixed> $detalhes erros por campo ou contexto extra
   *
   * @return array{erro: array{codigo_http: int, mensagem: string, detalhes: array<string, mixed>|null}}
   */
  public function format(int $codigoHttp, ?string $mensagem = null, array $detalhes = []): array
  {
    $texto = $mensagem ?? (self::MENSAGENS_PADRAO[$codigoHttp] ?? 'Erro ao processar a requisição.');

    return [
      'erro' => [
        'codigo_http' => $codigoHttp,
        'mensagem'    => $texto,
        'detalhes'    => $detalhes === [] ? null : $detalhes,
      ],
    ];
  }

  /**
   * @param array<string, mixed> $detalhes
   */
  public function send(int $codigoHttp, ?string $mensagem = null, array $detalhes = []): void
  {
    $this->emit($this->format($codigoHttp, $mensagem, $detalhes), $codigoHttp);
  }

  /**
   * Erros de validação (campos) — HTTP 400 com mensagem explícita em PT-BR.
   *
   * @param array<string, mixed> $errosPorCampo ex.: retorno do Rakit firstOfAll()
   */
  public function sendValidationErrors(array $errosPorCampo): void
  {
    $this->send(
      400,
      'Os dados enviados não passaram na validação.',
      $errosPorCampo
    );
  }

  public function fromThrowable(Throwable $throwable): void
  {
    if ($throwable instanceof DomainException) {
      $this->send(409, $throwable->getMessage());

      return;
    }

    $this->send(500, $this->mensagemErroInterno($throwable));
  }

  private function mensagemErroInterno(Throwable $throwable): string
  {
    $debug = $_ENV['APP_DEBUG'] ?? '';
    if ($debug === 'true' || $debug === '1') {
      return 'Erro interno: ' . $throwable->getMessage();
    }

    return self::MENSAGENS_PADRAO[500];
  }

  /**
   * @param array{erro: array{codigo_http: int, mensagem: string, detalhes: mixed}} $payload
   */
  private function emit(array $payload, int $codigoHttp): void
  {
    if (!headers_sent()) {
      header('Content-Type: application/json; charset=utf-8');
    }
    http_response_code($codigoHttp);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
  }
}
