<?php

namespace App\Repository;

use App\Model\Account;
use App\Model\AccountWithdraw;
use App\Model\AccountWithdrawPix;
use Hyperf\Database\Model\Builder;
use Hyperf\DbConnection\Db;

class WithdrawRepository
{
  public function create(array $data): AccountWithdraw
  {
    return AccountWithdraw::create($data);
  }

  public function createPix(array $data): AccountWithdrawPix
  {
    return AccountWithdrawPix::create($data);
  }

  public function findByUuid(string $uuid): ?AccountWithdraw
  {
    return AccountWithdraw::query()->where('id', $uuid)->first();
  }

  public function lockAccount(string $accountId): ?Account
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
        'balance' => Db::raw("balance - {$amount}")
      ]);
  }

  public function getPendingScheduledWithdraws(): \Hyperf\Database\Model\Collection
  {
    return AccountWithdraw::query()
      ->where('scheduled', true)
      ->where('done', false)
      ->where('error', false)
      ->where('scheduled_for', '<=', date('Y-m-d H:i:s'))
      ->with('pix')
      ->get();
  }

  public function markAsDone(string $withdrawId): void
  {
    AccountWithdraw::query()
      ->where('id', $withdrawId)
      ->update([
        'done' => true,
        'processed_at' => date('Y-m-d H:i:s'),
      ]);
  }

  public function markAsError(string $withdrawId, string $reason): void
  {
    AccountWithdraw::query()
      ->where('id', $withdrawId)
      ->update([
        'error' => true,
        'error_reason' => $reason,
        'processed_at' => date('Y-m-d H:i:s'),
      ]);
  }
}
