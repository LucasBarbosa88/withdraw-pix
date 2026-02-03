<?php

namespace App\Repository;

use App\Model\Account;
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

  public function lockById(string $accountId): ?Account
  {
    return Account::query()
      ->where('id', $accountId)
      ->lockForUpdate()
      ->first();
  }

  public function decreaseBalance(string $accountId, float $amount): void
  {
    Account::query()
      ->where('id', $accountId)
      ->update([
        'balance' => \Hyperf\DbConnection\Db::raw("balance - {$amount}")
      ]);
  }
}
