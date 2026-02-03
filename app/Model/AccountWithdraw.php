<?php

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

class AccountWithdraw extends Model
{
  protected ?string $table = 'account_withdraw';

  protected array $fillable = [
    'id',
    'account_id',
    'method',
    'amount',
    'scheduled',
    'scheduled_for',
    'done',
    'error',
    'error_reason',
  ];

  public bool $timestamps = false;
}
