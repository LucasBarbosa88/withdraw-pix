<?php

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

class AccountWithdrawPix extends Model
{
    protected ?string $table = 'account_withdraw_pix';

    protected string $primaryKey = 'account_withdraw_id';

    public bool $incrementing = false;

    protected string $keyType = 'string';

    protected array $fillable = [
        'account_withdraw_id',
        'type',
        'key',
    ];

    public bool $timestamps = false;

    public function withdraw(): \Hyperf\Database\Model\Relations\BelongsTo
    {
        return $this->belongsTo(AccountWithdraw::class, 'account_withdraw_id', 'id');
    }
}
