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
      'method'   => 'required|string|in:pix',
      'amount'   => 'required|numeric|min:0.01',
      'pix'      => 'required|array',
      'pix.type' => 'required|string|in:email',
      'pix.key'  => 'required|string|email|max:255',
      'schedule' => 'nullable|date|after:now',
    ];
  }

  public function messages(): array
  {
    return [
      'method.required'   => 'O método de saque é obrigatório',
      'method.in'         => 'Método de saque inválido',
      'amount.required'   => 'O valor é obrigatório',
      'amount.min'        => 'O valor mínimo é R$ 0,01',
      'pix.required'      => 'Os dados do PIX são obrigatórios',
      'pix.type.required' => 'O tipo da chave PIX é obrigatório',
      'pix.type.in'       => 'Tipo de chave PIX inválido',
      'pix.key.required'  => 'A chave PIX é obrigatória',
      'schedule.date'     => 'Data de agendamento inválida',
      'schedule.after'    => 'A data de agendamento deve ser futura',
    ];
  }
}
