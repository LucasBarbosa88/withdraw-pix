<?php

namespace App\Request;

use Hyperf\Validation\Request\FormRequest;

class WithdrawRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'amount'   => 'required|numeric|min:1',
      'pix_key'  => 'required|string|max:255',
      'pix_type' => 'required|in:cpf,cnpj,email,phone,random',
    ];
  }
}
