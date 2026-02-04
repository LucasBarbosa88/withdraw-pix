<?php

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

class AccountWithdraw extends Model
{
  protected ?string $table = 'account_withdraw';

  public bool $incrementing = false;
  protected string $keyType = 'string';

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

  public function pix(): \Hyperf\Database\Model\Relations\HasOne
  {
      return $this->hasOne(AccountWithdrawPix::class, 'account_withdraw_id', 'id');
  }
}
