<?php

namespace App\Repository;

use App\Model\Withdraw;

class WithdrawRepository
{
  public function create(array $data): Withdraw
  {
    return Withdraw::create($data);
  }

  public function findByUuid(string $uuid): ?Withdraw
  {
    return Withdraw::query()->where('uuid', $uuid)->first();
  }
}
