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

  #[Inject]
  protected \Hyperf\Validation\Contract\ValidatorFactoryInterface $validationFactory;

  public function store(string $accountId): ResponseInterface
  {
    try {
      $data = $this->request->all();

      $validator = $this->validationFactory->make($data, [
        'method' => 'required|string|in:pix',
        'amount' => 'required|numeric|min:0.01',
        'pix' => 'required|array',
        'pix.type' => 'required|string|in:email',
        'pix.key' => 'required|string|email|max:255',
        'schedule' => 'nullable|date|after:now',
      ]);

      if ($validator->fails()) {
        return $this->response->json([
          'success' => false,
          'message' => $validator->errors()->first(),
          'errors' => $validator->errors()
        ])->withStatus(422);
      }

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
        'trace' => explode("\n", $e->getTraceAsString())[0]
      ])->withStatus(500);
    }
  }
}
