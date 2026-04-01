<?php

namespace App\Http;

use DomainException;
use Throwable;

final class JsonErrorHandler
{
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

  public function send(int $codigoHttp, ?string $mensagem = null, array $detalhes = []): void
  {
    $this->emit($this->format($codigoHttp, $mensagem, $detalhes), $codigoHttp);
  }

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

  private function emit(array $payload, int $codigoHttp): void
  {
    if (!headers_sent()) {
      header('Content-Type: application/json; charset=utf-8');
    }
    http_response_code($codigoHttp);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
  }
}
