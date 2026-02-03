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

  public function store(WithdrawRequest $request): ResponseInterface
  {
    $data = $request->validated();

    $result = $this->service->create(
      (float) $data['amount'],
      $data['pix_key'],
      $data['pix_type']
    );

    return $this->response->json([
      'success' => true,
      'data' => $result,
    ]);
  }
}
