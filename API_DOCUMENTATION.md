# Pioneer Dental API Documentation

**Last Updated:** 2025-11-02

---

## Table of Contents

1. [Overview](#overview)
2. [Authentication](#authentication)
3. [Quick Start](#quick-start)
4. [API Endpoints](#api-endpoints)
5. [Response Formats](#response-formats)
6. [Error Handling](#error-handling)
7. [Examples](#examples)

---

## Overview

The Pioneer Dental API provides a secure RESTful interface for accessing student billing information. All API endpoints use API Key authentication.

**Base URL:** `http://localhost/pioneer-dental/api`

**Authentication:** API Key (X-API-Key header or api_key query parameter)

---

## Authentication

### Overview

All API endpoints require an API Key for authentication. The API key must be included in either:
- **Header:** `X-API-Key: YOUR_API_KEY_HERE`
- **Query Parameter:** `?api_key=YOUR_API_KEY_HERE`

### Configuration

The API key is configured in `application/config/config.php`:

```php
$config['api_secret_key'] = 'your-api-key-here';
```

**Important:** Change this to a secure random string in production!

Generate a secure key using:
```bash
openssl rand -hex 64
```

---

## Quick Start

### Example API Call

```bash
curl -X POST http://localhost/pioneer-dental/api/due-bills/check \
  -H "Content-Type: application/json" \
  -H "X-API-Key: yDuOlgKWYrYNjuEbfscZHOmnx0y532V7qQyTTqyY6p+T6UK7R3gIRZWAZwB2p6BXptXrbnycIj/rTj8P/DQHHQ==" \
  -d '{"student_id": 123, "phone": "01708086211"}'
```

### Alternative: Using Query Parameter

```bash
curl -X POST "http://localhost/pioneer-dental/api/due-bills/check?api_key=YOUR_API_KEY_HERE" \
  -H "Content-Type: application/json" \
  -d '{"student_id": 123, "phone": "01708086211"}'
```

---

## API Endpoints

### Due Bills Endpoints

#### POST /api/due-bills/check

Retrieve all due bills for a student.

**Authentication:** API Key required

**Headers:**
```
Content-Type: application/json
X-API-Key: YOUR_API_KEY_HERE
```

**Request Body:**
```json
{
  "student_id": 123,
  "phone": "01708086211"
}
```

**Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| student_id | integer | Yes | Student's ID or Registration Number |
| phone | string | No | Student's or parent's phone number for verification |

**Success Response (200 OK):**
```json
{
  "status": "success",
  "message": "Due bills retrieved successfully",
  "student": {
    "id": 123,
    "name": "John Doe",
    "reg_no": 1842,
    "phone": "01708086211",
    "parent_phone": "01712345678",
    "address": "House 77, Road 8"
  },
  "bills": [
    {
      "bill_id": 1,
      "month_year": "2025-11",
      "bill_month": 11,
      "bill_year": 2025,
      "bill_unique_id": "PD202511001",
      "due_date": "2025-11-20",
      "payment_status": "Unpaid",
      "amount": {
        "base_amount": 10000.00,
        "late_fee": 150.00,
        "total_amount": 10150.00,
        "currency": "BDT"
      },
      "is_overdue": true
    }
  ],
  "summary": {
    "total_bills": 1,
    "total_amount": 10150.00,
    "total_late_fee": 150.00,
    "currency": "BDT"
  },
  "authentication": {
    "matched_by": "student_id_and_phone"
  }
}
```

**Example:**
```bash
curl -X POST http://localhost/pioneer-dental/api/due-bills/check \
  -H "Content-Type: application/json" \
  -H "X-API-Key: YOUR_API_KEY_HERE" \
  -d '{"student_id": 123, "phone": "01708086211"}'
```

#### GET /api/due-bills/get

Retrieve due bills using GET method.

**Authentication:** API Key required

**Example:**
```bash
curl -X GET "http://localhost/pioneer-dental/api/due-bills/get?student_id=123&phone=01708086211&api_key=YOUR_API_KEY_HERE"
```

---

### Paid Bills Endpoints

#### POST /api/paid-bills/check

Retrieve all paid bills for a student.

**Authentication:** API Key required

**Example:**
```bash
curl -X POST http://localhost/pioneer-dental/api/paid-bills/check \
  -H "Content-Type: application/json" \
  -H "X-API-Key: YOUR_API_KEY_HERE" \
  -d '{"student_id": 123, "phone": "01708086211"}'
```

#### GET /api/paid-bills/get

Retrieve paid bills using GET method.

**Example:**
```bash
curl -X GET "http://localhost/pioneer-dental/api/paid-bills/get?student_id=123&phone=01708086211&api_key=YOUR_API_KEY_HERE"
```

---

### Payment Gateway Endpoints

#### POST /api/mtb/billing-info

Get billing information for MTB payment gateway.

**Authentication:** API Key required

#### POST /api/mtb/payment-callback

Handle payment callback from MTB gateway.

**Authentication:** API Key required

#### GET /api/mtb/payment-status/:transaction_id

Check payment status.

**Authentication:** API Key required

---

### Cron Job Endpoints

#### POST /api/generate-monthly-fees

Generate monthly fees for all students.

**Authentication:** API Key required

**Request Body (optional):**
```json
{
  "month": 11,
  "year": 2025,
  "due_day": 20
}
```

**Example:**
```bash
curl -X POST http://localhost/pioneer-dental/api/generate-monthly-fees \
  -H "X-API-Key: yDuOlgKWYrYNjuEbfscZHOmnx0y532V7qQyTTqyY6p+T6UK7R3gIRZWAZwB2p6BXptXrbnycIj/rTj8P/DQHHQ==" \
  -H "Content-Type: application/json" \
  -d '{"month": 11, "year": 2025, "due_day": 20}'
```

**Response:**
```json
{
  "success": true,
  "message": "Monthly fees generated successfully for November 2025",
  "data": {
    "month": 11,
    "year": 2025,
    "month_name": "November",
    "due_day": 20,
    "generated": 150,
    "skipped": 5,
    "total_students": 155,
    "errors": []
  },
  "timestamp": "2025-11-02 20:30:48"
}
```

**Cron Job Example:**
```bash
0 2 2 * * curl -X POST "http://localhost/pioneer-dental/api/generate-monthly-fees" \
  -H "X-API-Key: YOUR_API_KEY_HERE" >> /var/log/monthly_fees.log 2>&1
```

---

## Response Formats

### Success Response Format

```json
{
  "status": "success",
  "message": "Operation completed successfully",
  "data": {
    // Response data here
  }
}
```

### Error Response Format

```json
{
  "success": false,
  "message": "Error description",
  "error": "ERROR_CODE"
}
```

---

## Error Handling

### Common HTTP Status Codes

| Status Code | Description |
|-------------|-------------|
| 200 | Success |
| 400 | Bad Request |
| 401 | Unauthorized |
| 404 | Not Found |
| 500 | Internal Server Error |

### Error Responses

#### 401 Unauthorized - Missing API Key

```json
{
  "success": false,
  "message": "API key not provided",
  "error": "Unauthorized"
}
```

#### 401 Unauthorized - Invalid API Key

```json
{
  "success": false,
  "message": "Invalid API key",
  "error": "Unauthorized"
}
```

#### 400 Bad Request

```json
{
  "success": false,
  "message": "student_id is required"
}
```

#### 404 Not Found

```json
{
  "status": "error",
  "message": "Student not found"
}
```

---

## Examples

### JavaScript Example

```javascript
fetch('http://localhost/pioneer-dental/api/due-bills/check', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-API-Key': 'YOUR_API_KEY_HERE'
  },
  body: JSON.stringify({
    student_id: 123,
    phone: '01708086211'
  })
})
.then(response => response.json())
.then(data => console.log(data))
.catch(error => console.error('Error:', error));
```

### Python Example

```python
import requests

url = 'http://localhost/pioneer-dental/api/due-bills/check'
headers = {
    'Content-Type': 'application/json',
    'X-API-Key': 'YOUR_API_KEY_HERE'
}
data = {
    'student_id': 123,
    'phone': '01708086211'
}

response = requests.post(url, headers=headers, json=data)
print(response.json())
```

### PHP Example

```php
<?php
$url = 'http://localhost/pioneer-dental/api/due-bills/check';
$headers = [
    'Content-Type: application/json',
    'X-API-Key: YOUR_API_KEY_HERE'
];
$data = json_encode([
    'student_id' => 123,
    'phone' => '01708086211'
]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

$response = curl_exec($ch);
curl_close($ch);

print_r(json_decode($response, true));
?>
```

---

## Security Best Practices

1. **Use HTTPS in Production:** Always use HTTPS to protect API keys in transit
2. **Rotate API Keys:** Regularly rotate API keys
3. **Secure Key Storage:** Store API keys securely
4. **Monitor Usage:** Log API access and monitor for suspicious activity
5. **Limit Access:** Restrict API key distribution to authorized parties only

---

## Support

For issues or questions, please contact the development team.

**API Version:** 1.0

Testing:
http://localhost/pioneer-dental/api/generate-monthly-fees?X-API-Key=yDuOlgKWYrYNjuEbfscZHOmnx0y532V7qQyTTqyY6p+T6UK7R3gIRZWAZwB2p6BXptXrbnycIj/rTj8P/DQHHQ==
