<?php

namespace App\Crontab;

use App\Service\WithdrawService;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Di\Annotation\Inject;

#[Crontab(
    name: "ProcessWithdraw",
    rule: "* * * * *",
    callback: "execute",
    memo: "Processa PIX agendados"
)]

class ProcessWithdrawTask
{
    #[Inject]
    protected WithdrawService $service;

    public function execute()
    {
        $this->service->processScheduledWithdraws();
    }
}
