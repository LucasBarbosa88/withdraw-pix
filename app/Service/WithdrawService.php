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

  #[Inject]
  protected MailService $mailService;

  public function create(
    string $accountId,
    float $amount,
    string $pixKey,
    string $pixType,
    string $method = 'pix',
    ?string $schedule = null
  ): array {
    if ($amount <= 0) {
      throw new RuntimeException('Valor inválido');
    }

    return \Hyperf\DbConnection\Db::transaction(function () use ($accountId, $amount, $pixKey, $pixType, $method, $schedule) {

      $account = $this->repository->lockAccount($accountId);
      if (!$account) {
        throw new RuntimeException('Conta não encontrada');
      }

      $isScheduled = !empty($schedule);

      if (!$isScheduled) {
        if ($account->balance < $amount) {
          throw new RuntimeException('Saldo insuficiente');
        }
        $this->repository->decreaseBalance($accountId, $amount);
      }

      $withdrawId = (string) Str::uuid();
      $withdraw = $this->repository->create([
        'id' => $withdrawId,
        'account_id' => $accountId,
        'amount' => $amount,
        'method' => $method,
        'scheduled' => $isScheduled,
        'scheduled_for' => $isScheduled ? date('Y-m-d H:i:s', strtotime($schedule)) : null,
        'done' => !$isScheduled,
        'error' => false,
        'processed_at' => !$isScheduled ? date('Y-m-d H:i:s') : null,
      ]);

      $this->repository->createPix([
        'account_withdraw_id' => $withdrawId,
        'type' => $pixType,
        'key' => $pixKey,
      ]);

      if (!$isScheduled) {
        try {
          $this->sendWithdrawEmail($pixKey, $pixType, $amount);
        } catch (\Throwable $e) {
        }
      }

      return [
        'id' => $withdraw->id,
        'status' => $isScheduled ? 'SCHEDULED' : 'COMPLETED',
        'amount' => $withdraw->amount,
        'scheduled_for' => $withdraw->scheduled_for,
      ];
    });
  }

  private function sendWithdrawEmail(string $pixKey, string $pixType, float $amount): void
  {
    $email = strtoupper($pixType) === 'EMAIL' ? $pixKey : null;

    if ($email) {
      $this->mailService->sendWithdrawConfirmation(
        $email,
        $amount,
        $pixKey,
        $pixType
      );
    }
  }

  #[Inject]
  protected \Psr\Log\LoggerInterface $logger;

  public function processScheduledWithdraws(): void
  {
    $withdraws = $this->repository->getPendingScheduledWithdraws();

    if ($withdraws->count() > 0) {
      $this->logger->info("Cron: Processando {$withdraws->count()} saques agendados.");
    }

    foreach ($withdraws as $withdraw) {
      try {
        \Hyperf\DbConnection\Db::transaction(function () use ($withdraw) {
          $account = $this->repository->lockAccount($withdraw->account_id);

          if (!$account || $account->balance < $withdraw->amount) {
            $this->repository->markAsError($withdraw->id, 'Saldo insuficiente');
            $this->logger->warning("Cron: Saque {$withdraw->id} falhou. Saldo insuficiente.");
            return;
          }

          $this->repository->decreaseBalance($withdraw->account_id, $withdraw->amount);
          $this->repository->markAsDone($withdraw->id);
          $this->logger->info("Cron: Saque {$withdraw->id} processado com sucesso. Valor: {$withdraw->amount}");

          if ($withdraw->pix) {
            try {
              $this->sendWithdrawEmail(
                $withdraw->pix->key,
                $withdraw->pix->type,
                $withdraw->amount
              );
            } catch (\Throwable $e) {
              $this->logger->error("Cron: Erro ao enviar email saque {$withdraw->id}: " . $e->getMessage());
            }
          }
        });
      } catch (\Throwable $e) {
        $this->logger->error("Cron: Erro crítico saque {$withdraw->id}: " . $e->getMessage());
      }
    }
  }
}
