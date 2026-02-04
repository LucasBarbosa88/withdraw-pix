<?php

namespace App\Service;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Guzzle\ClientFactory;
use Psr\Log\LoggerInterface;

class MailService
{
    #[Inject]
    protected ConfigInterface $config;

    #[Inject]
    protected ClientFactory $clientFactory;

    #[Inject]
    protected LoggerInterface $logger;

    public function sendWithdrawConfirmation(string $toEmail, float $amount, string $pixKey, string $pixType): bool
    {
        $subject = 'Confirmação de Saque PIX';

        $body = $this->buildWithdrawEmailBody($amount, $pixKey, $pixType);

        return $this->send($toEmail, $subject, $body);
    }

    public function send(string $to, string $subject, string $body): bool
    {
        $host = $this->config->get('mail.host', 'localhost');
        $port = $this->config->get('mail.port', 1025);
        $fromAddress = $this->config->get('mail.from.address', 'noreply@withdraw-pix.com');
        $fromName = $this->config->get('mail.from.name', 'Withdraw PIX');

        try {
            $errno = 0;
            $errstr = '';
            $socket = @fsockopen($host, $port, $errno, $errstr, 30);

            if (!$socket) {
                $this->logger->error("Erro ao conectar ao servidor de email: {$errstr} ({$errno})");
                return false;
            }

            $this->readResponse($socket);
            $this->sendCommand($socket, "EHLO localhost\r\n");
            $this->sendCommand($socket, "MAIL FROM:<{$fromAddress}>\r\n");
            $this->sendCommand($socket, "RCPT TO:<{$to}>\r\n");
            $this->sendCommand($socket, "DATA\r\n");

            $message = "From: {$fromName} <{$fromAddress}>\r\n";
            $message .= "To: {$to}\r\n";
            $message .= "Subject: {$subject}\r\n";
            $message .= "MIME-Version: 1.0\r\n";
            $message .= "Content-Type: text/html; charset=UTF-8\r\n";
            $message .= "\r\n";
            $message .= $body;
            $message .= "\r\n.\r\n";

            $this->sendCommand($socket, $message);
            $this->sendCommand($socket, "QUIT\r\n");

            fclose($socket);

            $this->logger->info("Email enviado com sucesso para: {$to}");
            return true;
        } catch (\Throwable $e) {
            $this->logger->error("Erro ao enviar email: " . $e->getMessage());
            return false;
        }
    }

    private function sendCommand($socket, string $command): string
    {
        fwrite($socket, $command);
        return $this->readResponse($socket);
    }

    private function readResponse($socket): string
    {
        $response = '';
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') {
                break;
            }
        }
        return $response;
    }

    private function buildWithdrawEmailBody(float $amount, string $pixKey, string $pixType): string
    {
        $formattedAmount = number_format($amount, 2, ',', '.');
        $date = date('d/m/Y H:i:s');

        return <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmação de Saque PIX</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0; text-align: center;">✅ Saque PIX Realizado</h1>
    </div>
    
    <div style="background: #f9f9f9; padding: 30px; border: 1px solid #ddd; border-top: none; border-radius: 0 0 10px 10px;">
        <p style="font-size: 16px;">Olá!</p>
        
        <p style="font-size: 16px;">Seu saque via PIX foi realizado com sucesso. Confira os detalhes:</p>
        
        <div style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #667eea;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 10px 0; font-weight: bold; color: #666;">Valor:</td>
                    <td style="padding: 10px 0; font-size: 18px; color: #27ae60; font-weight: bold;">R$ {$formattedAmount}</td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; font-weight: bold; color: #666;">Tipo da Chave:</td>
                    <td style="padding: 10px 0;">{$pixType}</td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; font-weight: bold; color: #666;">Chave PIX:</td>
                    <td style="padding: 10px 0;">{$pixKey}</td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; font-weight: bold; color: #666;">Data/Hora:</td>
                    <td style="padding: 10px 0;">{$date}</td>
                </tr>
            </table>
        </div>
        
        <p style="font-size: 14px; color: #666;">Se você não reconhece esta operação, entre em contato imediatamente com nosso suporte.</p>
        
        <hr style="border: none; border-top: 1px solid #ddd; margin: 20px 0;">
        
        <p style="font-size: 12px; color: #999; text-align: center;">
            Este é um email automático, por favor não responda.
        </p>
    </div>
</body>
</html>
HTML;
    }
}
