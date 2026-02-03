<?php

namespace App\Service;

use App\Repository\WithdrawRepository;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Stringable\Str;
use RuntimeException;

class WithdrawService
{
  #[Inject]
  protected WithdrawRepository $repository;

  public function create(float $amount, string $pixKey, string $pixType): array
  {
    if ($amount <= 0) {
      throw new RuntimeException('Valor inválido');
    }

    $withdraw = $this->repository->create([
      'uuid'     => (string) Str::uuid(),
      'amount'   => $amount,
      'pix_key'  => $pixKey,
      'pix_type' => $pixType,
      'status'   => 'PENDING',
    ]);

    return [
      'uuid'   => $withdraw->uuid,
      'status' => $withdraw->status,
    ];
  }
}
