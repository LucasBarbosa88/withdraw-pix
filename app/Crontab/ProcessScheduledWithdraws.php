<?php

namespace App\Crontab;

use App\Service\WithdrawService;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Di\Annotation\Inject;

#[Crontab(
  name: 'process_scheduled_withdraws',
  rule: '* * * * *',
  callback: 'execute',
  memo: 'Processa PIX agendados'
)]
class ProcessScheduledWithdraws
{
  #[Inject]
  protected WithdrawService $service;

  public function execute(): void
  {
    $this->service->processScheduledWithdraws();
  }
}
