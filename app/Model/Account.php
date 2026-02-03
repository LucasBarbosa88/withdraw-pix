<?php

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

class Account extends Model
{
  protected ?string $table = 'account';

  protected array $fillable = [
    'id',
    'name',
    'balance',
  ];

  protected array $casts = [
    'balance' => 'float',
  ];

  public bool $timestamps = false;
}
