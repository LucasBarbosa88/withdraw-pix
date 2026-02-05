# Withdraw PIX Service

Serviço de saque via PIX implementado com Hyperf Framework, Docker e MySQL.

## 🚀 Tecnologias

- **PHP 8.1+** (Hyperf 3.1)
- **MySQL 8.0**
- **Docker & Docker Compose**
- **Mailhog** (SMTP Testing)

## 🛠 Como Rodar

1. **Subir os containers:**
   ```bash
   docker-compose up -d
   ```

2. **Rodar as migrations:**
   ```bash
   docker-compose exec app php bin/hyperf.php migrate
   ```

3. **Acessar a aplicação:**
   - API: `http://localhost:9501`
   - Mailhog: `http://localhost:8025`

## 📡 API Endpoints

### Realizar Saque
`POST /account/{accountId}/balance/withdraw`

**Body:**
```json
{
   "method": "pix",
   "amount": 100.00,
   "pix": {
      "type": "email",
      "key": "usuario@exemplo.com"
   },
   "schedule": "2026-02-10 14:00:00" // Opcional
}
```