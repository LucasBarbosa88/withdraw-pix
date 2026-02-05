<?php

declare(strict_types=1);

namespace App\Exception\Handler;

use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Validation\ValidationException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class ValidationExceptionHandler extends ExceptionHandler
{
  public function handle(Throwable $throwable, ResponseInterface $response)
  {
    $this->stopPropagation();
    /** @var ValidationException $throwable */
    $body = $throwable->validator->errors()->first();
    $errors = $throwable->validator->errors()->all();

    return $response->withStatus(422)
      ->withHeader('Content-Type', 'application/json')
      ->withBody(new SwooleStream(json_encode([
        'success' => false,
        'message' => $body,
        'errors' => $throwable->validator->errors(),
      ], JSON_UNESCAPED_UNICODE)));
  }

  public function isValid(Throwable $throwable): bool
  {
    return $throwable instanceof ValidationException;
  }
}
