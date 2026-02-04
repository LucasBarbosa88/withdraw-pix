<?php

namespace App\Crontab;

use App\Model\Account;
use App\Model\AccountWithdraw;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\DbConnection\Db;
use Carbon\Carbon;

#[Crontab(
  name: 'process_scheduled_withdraws',
  rule: '* * * * *',
  callback: 'execute',
  memo: 'Processa PIX agendados'
)]
class ProcessScheduledWithdraws
{
  public function execute(): void
  {
    $withdraws = AccountWithdraw::query()
      ->where('scheduled', true)
      ->where('done', false)
      ->where('error', false)
      ->where('scheduled_for', '<=', Carbon::now())
      ->limit(50)
      ->get();

    foreach ($withdraws as $withdraw) {
      Db::transaction(function () use ($withdraw) {

        $account = Account::query()
          ->where('id', $withdraw->account_id)
          ->lockForUpdate()
          ->first();

        if (! $account) {
          $withdraw->update([
            'done' => true,
            'error' => true,
            'error_reason' => 'Conta não encontrada',
          ]);
          return;
        }

        if ($account->balance < $withdraw->amount) {
          $withdraw->update([
            'done' => true,
            'error' => true,
            'error_reason' => 'Saldo insuficiente',
          ]);
          return;
        }

        $account->balance -= $withdraw->amount;
        $account->save();

        $withdraw->update([
          'done' => true,
        ]);
      });
    }
  }
}
