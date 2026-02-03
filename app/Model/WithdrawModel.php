<?php

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

class Withdraw extends Model
{
  protected ?string $table = 'withdraws';

  protected array $fillable = [
    'uuid',
    'amount',
    'pix_key',
    'pix_type',
    'status',
  ];

  protected array $casts = [
    'amount' => 'float',
  ];
}
