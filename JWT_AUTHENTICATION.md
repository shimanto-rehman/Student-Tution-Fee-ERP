# JWT Authentication System

This document explains how to use the JWT (JSON Web Token) based authentication system for API calls, specifically designed for payment gateway integration.

## Overview

The system implements Bearer token authentication using JWT for securing API endpoints. Payment gateway partners (banks) must authenticate first to obtain a JWT token, which is then used for subsequent API requests.

## Setup Instructions

### 1. Configure JWT Secret Key

**Note:** This implementation uses a standalone JWT library with no external dependencies. No Composer installation is required!

Edit `application/config/config.php` and set a secure JWT secret key:

```php
$config['jwt_secret_key'] = 'your-secure-random-key-here';
```

Generate a secure key using:
```bash
openssl rand -hex 64
```

### 3. Configure API Clients

In `application/config/config.php`, add your payment gateway clients:

```php
$config['api_clients'] = [
    'mtb_gateway' => 'mtb_secret_key_12345',
    'bank_gateway' => 'bank_secret_key_67890'
];
```

**Important:** In production, store these credentials securely (database or encrypted file).

## API Usage

### Step 1: Obtain JWT Token

**Endpoint:** `POST /api/auth/login`

**Request:**
```json
{
    "client_id": "mtb_gateway",
    "client_secret": "mtb_secret_key_12345"
}
```

**Response:**
```json
{
    "success": true,
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "expires_in": 86400,
    "token_type": "Bearer"
}
```

**cURL Example:**
```bash
curl -X POST http://localhost/pioneer-dental/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "client_id": "mtb_gateway",
    "client_secret": "mtb_secret_key_12345"
  }'
```

### Step 2: Use Token for API Requests

Include the token in the `Authorization` header as a Bearer token:

**Header:**
```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

**cURL Example:**
```bash
curl -X POST http://localhost/pioneer-dental/api/due-bills/check \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NjIxMTgyMzQsImV4cCI6MTc2MjIwNDYzNCwiZGF0YSI6eyJjbGllbnRfaWQiOiJtdGJfZ2F0ZXdheSIsImlzc3VlZF9hdCI6IjIwMjUtMTEtMDIgMjI6MTc6MTQiLCJpcF9hZGRyZXNzIjoiOjoxIn19.HWehCOybXFT1ObQA8U2BL7h1CH941IqM0cpI_dq0CaQ" \
  -d '{
    "student_id": "123",
    "phone": "01708086211"
  }'
```

## Protected Endpoints

All API endpoints under `/api/*` (except `/api/auth/*`) require JWT authentication:

- `/api/mtb/billing-info`
- `/api/mtb/payment-callback`
- `/api/mtb/payment-status/*`
- `/api/due-bills/check`
- `/api/due-bills/get`
- `/api/paid-bills/check`
- `/api/paid-bills/get`
- `/api/generate-monthly-fees` - Generate monthly fees for all students

## Public Endpoints (No JWT Authentication Required)

- `/api/auth/login` - Get JWT token
- `/api/auth/validate` - Validate token (for testing)

## Token Validation

The system automatically validates tokens for all API requests. If validation fails, you'll receive:

```json
{
    "success": false,
    "message": "Invalid or expired token",
    "error": "Unauthorized"
}
```

**HTTP Status Code:** 401 Unauthorized

## Token Expiration

By default, tokens expire after 24 hours (86400 seconds). You can configure this in `application/config/config.php`:

```php
$config['jwt_expiration_time'] = 86400; // seconds
```

## Error Responses

### Missing Token
```json
{
    "success": false,
    "message": "Bearer token not provided",
    "error": "Unauthorized"
}
```

### Invalid/Expired Token
```json
{
    "success": false,
    "message": "Invalid or expired token",
    "error": "Unauthorized"
}
```

### Invalid Credentials (Login)
```json
{
    "success": false,
    "message": "Invalid client credentials"
}
```

## Accessing Token Data in Controllers

If you need to access the decoded token data in your controllers:

```php
// Get client ID from token
$client_id = $this->jwt_client_id;

// Get full token data
$token_data = $this->jwt_data;
```

## Security Best Practices

1. **Use HTTPS in Production:** Always use HTTPS to protect tokens in transit
2. **Rotate Secret Keys:** Regularly rotate JWT secret keys
3. **Secure Client Secrets:** Store client secrets securely (database, encrypted)
4. **Short Token Expiration:** Consider shorter expiration times for sensitive operations
5. **Monitor Token Usage:** Log authentication attempts and monitor for suspicious activity

## Testing

### Validate Token
```bash
curl -X GET http://localhost/pioneer-dental/api/auth/validate \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NjIxMTIzMTksImV4cCI6MTc2MjE5ODcxOSwiZGF0YSI6eyJjbGllbnRfaWQiOiJtdGJfZ2F0ZXdheSIsImlzc3VlZF9hdCI6IjIwMjUtMTEtMDIgMjA6Mzg6MzkiLCJpcF9hZGRyZXNzIjoiOjoxIn19.bFsGTldVC7mrMu2jQ4YQcxNUWrC51VxWqhzVkEBIJqU"
```

### Full Workflow Example
```bash
# 1. Login to get token
TOKEN=$(curl -s -X POST http://localhost/pioneer-dental/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"client_id":"mtb_gateway","client_secret":"mtb_secret_key_12345"}' \
  | jq -r '.token')

# 2. Use token for API request
curl -X POST http://localhost/pioneer-dental/api/due-bills/check \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"student_id":"123","phone":"01708086211"}'
```

## Troubleshooting

### "JWT secret key not configured"
Set `jwt_secret_key` in `application/config/config.php`.

### "Bearer token not provided"
Include the token in the Authorization header:
```
Authorization: Bearer YOUR_TOKEN
```

### Token expired
Request a new token from `/api/auth/login`.

## Files Modified/Created

1. `application/config/config.php` - Added JWT configuration
2. `application/config/hooks.php` - Added JWT authentication hook
3. `application/config/routes.php` - Added auth routes
4. `application/libraries/Jwt.php` - JWT library wrapper (NEW)
5. `application/libraries/Jwt_standalone.php` - Pure PHP JWT implementation (NEW - no dependencies)
6. `application/controllers/Api/Auth.php` - Authentication controller (NEW)
7. `application/hooks/Jwt_auth.php` - JWT validation hook (NEW)

**Note:** This implementation uses a standalone JWT library (`Jwt_standalone.php`) that implements JWT encoding/decoding in pure PHP with no external dependencies. No Composer or external packages are required!

