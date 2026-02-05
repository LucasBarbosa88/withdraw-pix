<?php

namespace App\Controller;

use App\Request\WithdrawRequest;
use App\Service\WithdrawService;
use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ResponseInterface;

class WithdrawController extends AbstractController
{
  #[Inject]
  protected WithdrawService $service;

  public function store(WithdrawRequest $request, string $accountId): ResponseInterface
  {
    try {
      $data = $request->validated();

      $result = $this->service->create(
        accountId: $accountId,
        amount: (float) $data['amount'],
        pixKey: $data['pix']['key'],
        pixType: $data['pix']['type'],
        method: $data['method'],
        schedule: $data['schedule'] ?? null
      );

      return $this->response->json([
        'success' => true,
        'data' => $result,
      ]);
    } catch (\Throwable $e) {
      return $this->response->json([
        'success' => false,
        'message' => $e->getMessage(),
      ])->withStatus(500);
    }
  }
}
