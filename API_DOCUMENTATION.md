# Pioneer Dental API Documentation

**Last Updated:** 2025-11-03  
**API Version:** 1.0  
**Base URL:** `http://localhost/pioneer-dental/api` (replace with your production URL)

---

## 📋 Table of Contents

1. [What is This API?](#what-is-this-api)
2. [Getting Started for Beginners](#getting-started-for-beginners)
3. [Understanding JWT Authentication](#understanding-jwt-authentication)
4. [How to Get Your JWT Token](#how-to-get-your-jwt-token)
5. [API Endpoints Reference](#api-endpoints-reference)
6. [Step-by-Step Examples](#step-by-step-examples)
7. [Error Handling](#error-handling)
8. [Postman Collection](#postman-collection)

---

## What is This API?

The **Pioneer Dental API** allows payment gateways and external systems to:

- ✅ Check which bills students haven't paid yet (due bills)
- ✅ View bills that students have already paid
- ✅ Record payments from payment gateways
- ✅ Automatically generate monthly fees for all students
- ✅ Get billing information for payment processing

**Who uses this?**
- Payment gateway partners (like banks)
- Mobile app developers
- Integration partners
- Automated systems

---

## Getting Started for Beginners

### What You Need Before Starting

1. **API Credentials** (provided by Pioneer Dental)
   - `client_id`: Your partner ID (e.g., `mtb_gateway`)
   - `client_secret`: Your secret key (e.g., `mtb_secret_key_12345`)

2. **A Tool to Test APIs** (choose one):
   - **Postman** (Recommended for beginners) - Download from https://www.postman.com/downloads/
   - **cURL** (Command line tool)
   - **Your own application** (web app, mobile app, etc.)

3. **Basic Understanding**
   - APIs are like messengers that let different systems talk to each other
   - You send a "request" and get back a "response"
   - All requests use JSON format (like a structured text format)

### Quick Start (3 Steps)

**Step 1:** Get a JWT Token (like getting a ticket to enter)  
**Step 2:** Use that token in all your API requests  
**Step 3:** Make requests to get student billing information

That's it! Let's learn more details below.

---

## Understanding JWT Authentication

### What is JWT?

**JWT** stands for **JSON Web Token**. Think of it like a **temporary pass** that:
- Proves you're authorized to use the API
- Expires after 24 hours (you'll need to get a new one)
- Must be included in every API request (except the login request)

### Why Do We Use JWT?

1. **Security**: Only authorized partners can access the API
2. **Temporary Access**: Tokens expire, so if someone steals it, they can't use it forever
3. **Easy to Use**: Once you have a token, you just include it in each request

### How JWT Works (Simple Explanation)

```
┌─────────────────────────────────────────────────────────┐
│ Step 1: Request Token (Like getting a ticket)          │
│ POST /api/auth/login                                    │
│ Body: { client_id, client_secret }                      │
│                                                        │
│ Response: { token: "abc123xyz..." }                   │
└─────────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────────┐
│ Step 2: Use Token (Show your ticket)                   │
│ Any API Request                                         │
│ Header: Authorization: Bearer abc123xyz...             │
│                                                        │
│ Response: Student billing data                         │
└─────────────────────────────────────────────────────────┘
```

### Token Expiration

- **Default expiration**: 24 hours (86,400 seconds)
- After expiration, you'll get an error saying "Invalid or expired token"
- **Solution**: Just request a new token using `/api/auth/login`

---

## How to Get Your JWT Token

### Endpoint: `POST /api/auth/login`

**Purpose:** This is the ONLY endpoint that doesn't require a token. Use this to get your first token.

**Request Details:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `client_id` | string | Yes | Your partner/client ID (e.g., `mtb_gateway`) |
| `client_secret` | string | Yes | Your secret key provided by Pioneer Dental |

**Example Request (JSON Body):**
```json
{
    "client_id": "mtb_gateway",
    "client_secret": "mtb_secret_key_12345"
}
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NjIxMTgyMzQsImV4cCI6MTc2MjIwNDYzNCwiZGF0YSI6eyJjbGllbnRfaWQiOiJtdGJfZ2F0ZXdheSIsImlzc3VlZF9hdCI6IjIwMjUtMTEtMDIgMjI6MTc6MTQiLCJpcF9hZGRyZXNzIjoiOjoxIn19.HWehCOybXFT1ObQA8U2BL7h1CH941IqM0cpI_dq0CaQ",
  "expires_in": 86400,
  "token_type": "Bearer"
}
```

**What the Response Means:**

- `success: true` - Token was generated successfully
- `token` - **SAVE THIS!** You'll use this in all other API requests
- `expires_in` - Token expires in 86,400 seconds (24 hours)
- `token_type` - Always "Bearer" (means you'll use "Bearer " prefix)

**Error Response (401 Unauthorized):**
```json
{
  "success": false,
  "message": "Invalid client credentials"
}
```
This means your `client_id` or `client_secret` is wrong.

---

## Using Your Token

Once you have a token, you MUST include it in every API request (except `/api/auth/login`).

### How to Include Token in Requests

**Method: Add Authorization Header**

```
Authorization: Bearer YOUR_TOKEN_HERE
```

**Important Notes:**
- Replace `YOUR_TOKEN_HERE` with the actual token from login response
- Keep the word "Bearer" (with a space after it)
- Don't include quotes around the token

**Example:**
```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

---

## API Endpoints Reference

All endpoints below require JWT authentication (except `/api/auth/login`).

---

### 🔐 Authentication Endpoints

#### 1. Login (Get Token)

**`POST /api/auth/login`**

Get your JWT token to authenticate future requests.

**Authentication Required:** ❌ No (this is how you get authenticated!)

**Request:**
```bash
POST http://localhost/pioneer-dental/api/auth/login
Content-Type: application/json

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

---

#### 2. Validate Token (Testing Only)

**`GET /api/auth/validate`**

Check if your token is still valid. Useful for testing.

**Authentication Required:** ✅ Yes (Bearer token)

**Request:**
```bash
GET http://localhost/pioneer-dental/api/auth/validate
Authorization: Bearer YOUR_TOKEN_HERE
```

**Response:**
```json
{
  "success": true,
  "message": "Token is valid",
  "data": {
    "client_id": "mtb_gateway",
    "issued_at": "2025-11-02 22:17:14",
    "ip_address": "::1"
  }
}
```

---

### 📋 Due Bills Endpoints

These endpoints show bills that students **haven't paid yet** or have **partially paid**.

#### 3. Check Due Bills (POST)

**`POST /api/due-bills/check`**

Get all unpaid or partially paid bills for a student.

**Authentication Required:** ✅ Yes (Bearer token)

**Request Body:**
```json
{
  "student_id": 1829,
  "phone": "01711781724"
}
```

**Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `student_id` | integer/string | Yes* | Student's ID or Registration Number |
| `phone` | string | Yes* | Student's or parent's phone number |

*Note: Either `student_id` OR `phone` is required (or both for better security).

**Success Response (200 OK):**
```json
{
  "status": "success",
  "message": "Due bills retrieved successfully",
  "student": {
    "id": 1829,
    "name": "John Doe",
    "reg_no": 1842,
    "phone": "01711781724",
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

**Response Fields Explained:**

- `student` - Student information
- `bills` - Array of unpaid/partial bills
  - `bill_id` - Unique bill identifier
  - `bill_unique_id` - Human-readable bill ID (e.g., PD202511001)
  - `due_date` - When the bill is due
  - `payment_status` - "Unpaid" or "Partial"
  - `amount.base_amount` - Original fee amount
  - `amount.late_fee` - Late payment fee (if overdue)
  - `amount.total_amount` - Total to pay (base + late fee)
  - `is_overdue` - true if due date has passed
- `summary` - Totals across all bills

---

#### 4. Get Due Bills (GET)

**`GET /api/due-bills/get`**

Same as above, but using GET method with URL parameters (easier for simple testing).

**Authentication Required:** ✅ Yes (Bearer token)

**Request:**
```bash
GET http://localhost/pioneer-dental/api/due-bills/get?student_id=1829&phone=01711781724
Authorization: Bearer YOUR_TOKEN_HERE
```

**Query Parameters:**
- `student_id` (required*)
- `phone` (required*)

**Response:** Same as POST method above.

---

### ✅ Paid Bills Endpoints

These endpoints show bills that students have **fully paid** or **partially paid**.

#### 5. Check Paid Bills (POST)

**`POST /api/paid-bills/check`**

Get all paid or partially paid bills with payment history.

**Authentication Required:** ✅ Yes (Bearer token)

**Request Body:**
```json
{
  "student_id": 1829,
  "phone": "01711781724"
}
```

**Success Response (200 OK):**
```json
{
  "status": "success",
  "message": "Paid bills retrieved successfully",
  "student": {
    "id": 1829,
    "name": "John Doe",
    "reg_no": 1842,
    "phone": "01711781724",
    "parent_phone": "01712345678",
    "address": "House 77, Road 8"
  },
  "bills": [
    {
      "bill_id": 555,
      "month_year": "2025-10",
      "bill_month": 10,
      "bill_year": 2025,
      "bill_unique_id": "PD202510001",
      "due_date": "2025-10-20",
      "payment_status": "Paid",
      "amount": {
        "base_amount": 10000.00,
        "late_fee": 0.00,
        "total_amount": 10000.00,
        "total_paid": 10000.00,
        "remaining_due": 0.00,
        "currency": "BDT"
      },
      "paid_on_time": true,
      "payment_history": [
        {
          "payment_id": 901,
          "amount_paid": 10000.00,
          "payment_date": "2025-10-15",
          "payment_method": "gateway",
          "transaction_id": "TXN202510151234",
          "received_by": null
        }
      ]
    }
  ],
  "summary": {
    "total_bills": 1,
    "total_base_amount": 10000.00,
    "total_late_fee": 0.00,
    "total_amount_paid": 10000.00,
    "currency": "BDT"
  },
  "authentication": {
    "matched_by": "student_id_and_phone"
  }
}
```

**Response Fields Explained:**

- `bills[].payment_status` - "Paid" or "Partial"
- `amount.total_paid` - Total amount paid so far
- `amount.remaining_due` - Amount still owed (for partial payments)
- `paid_on_time` - true if first payment was before/on due date
- `payment_history` - Array of all payments made for this bill

---

#### 6. Get Paid Bills (GET)

**`GET /api/paid-bills/get`**

Same as above, but using GET method.

**Authentication Required:** ✅ Yes (Bearer token)

**Request:**
```bash
GET http://localhost/pioneer-dental/api/paid-bills/get?student_id=1829&phone=01711781724
Authorization: Bearer YOUR_TOKEN_HERE
```

**Response:** Same as POST method above.

---

### 💳 Payment Processing Endpoints

#### 7. Process Payment

**`POST /api/payments/process`**

Record one or multiple payments for a student. Used by payment gateways to record successful payments.

**Authentication Required:** ✅ Yes (Bearer token)

**Single Payment Example:**
```json
{
  "student_id": 1829,
  "bill_month": 11,
  "bill_year": 2025,
  "amount": 10150,
  "payment_method": "gateway",
  "external_ref": "PGW-REF-123456",
  "transaction_ref": "BANK-TXN-789012"
}
```

**Multiple Payments Example:**
```json
{
  "student_id": 1829,
  "payments": [
    {
      "bill_month": 9,
      "bill_year": 2025,
      "amount": 10000
    },
    {
      "bill_month": 10,
      "bill_year": 2025,
      "amount": 10150
    },
    {
      "bill_month": 11,
      "bill_year": 2025
      // amount omitted = pays full remaining due
    }
  ],
  "payment_method": "gateway",
  "external_ref": "BATCH-REF-7788",
  "transaction_ref": "BANK-TXN-999888"
}
```

**Parameters:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `student_id` | integer/string | Yes | Student ID or registration number |
| `payments` | array | Yes* | Array of payment items (for multiple payments) |
| `bill_month` | integer | Yes* | Month (1-12) - required if using single payment format |
| `bill_year` | integer | Yes* | Year (2020-2050) - required if using single payment format |
| `amount` | number | No | Payment amount. If omitted, pays full remaining due |
| `payment_method` | string | No | e.g., "gateway", "api", "cash" |
| `external_ref` | string | No | Your reference ID |
| `transaction_ref` | string | No | Bank-provided transaction reference |

*Note: Use either `payments` array OR single `bill_month`/`bill_year` fields.

**Success Response (200 OK):**
```json
{
  "status": "success",
  "transaction_id": "TXN202511031230459876",
  "transaction_ref": "BANK-TXN-789012",
  "student": {
    "id": 1829,
    "name": "John Doe",
    "reg_no": "REG123"
  },
  "results": [
    {
      "bill_id": 555,
      "bill_month": 11,
      "bill_year": 2025,
      "expected_total": 10150.00,
      "already_paid_before": 0.00,
      "amount_paid": 10150.00,
      "remaining_due_after": 0.00,
      "status": "success",
      "payment_id": 901
    }
  ]
}
```

**Partial Success Response (200 OK with status: "partial"):**
```json
{
  "status": "partial",
  "transaction_id": "TXN202511031245001234",
  "student": {
    "id": 1829,
    "name": "John Doe",
    "reg_no": "REG123"
  },
  "results": [
    {
      "bill_month": 9,
      "bill_year": 2025,
      "status": "error",
      "message": "Bill not found for student/month/year"
    },
    {
      "bill_id": 556,
      "bill_month": 10,
      "bill_year": 2025,
      "expected_total": 10150.00,
      "already_paid_before": 0.00,
      "amount_paid": 10150.00,
      "remaining_due_after": 0.00,
      "status": "success",
      "payment_id": 902
    }
  ]
}
```

**Response Fields Explained:**

- `status` - "success" (all paid), "partial" (some succeeded), or "error" (all failed)
- `transaction_id` - College-generated batch transaction ID
- `transaction_ref` - Bank-provided reference (if provided)
- `results[]` - Array showing result for each payment
  - `status: "success"` - Payment recorded successfully
  - `status: "error"` - Payment failed (check `message` field)
  - `status: "skipped"` - Bill already fully paid
- `payment_id` - ID of the created payment record

---

### 📅 Cron Job / Automation Endpoints

#### 8. Generate Monthly Fees

**`POST /api/generate-monthly-fees`**

Automatically generate monthly fees for ALL active students. Typically called via cron job.

**Authentication Required:** ✅ Yes (Bearer token)

**Request Body (all fields optional - uses current month/year if omitted):**
```json
{
  "month": 11,
  "year": 2025,
  "due_day": 20
}
```

**Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `month` | integer | No | Month (1-12). Default: current month |
| `year` | integer | No | Year (2020-2050). Default: current year |
| `due_day` | integer | No | Day of month when bill is due (1-31). Default: 20 |

**Success Response (200 OK):**
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
  "timestamp": "2025-11-03 20:30:48"
}
```

**Response Fields Explained:**

- `generated` - Number of bills successfully created
- `skipped` - Number of bills skipped (already exists or student inactive)
- `total_students` - Total number of active students
- `errors` - Array of error messages (if any)

**Cron Job Setup Example:**
```bash
# Run on the 2nd day of each month at 2:00 AM
0 2 2 * * curl -X POST "http://localhost/pioneer-dental/api/generate-monthly-fees" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"month":'$(date +\%m)',"year":'$(date +\%Y)',"due_day":20}' \
  >> /var/log/monthly_fees.log 2>&1
```

---

## Step-by-Step Examples

### Example 1: Complete Workflow (cURL)

**Step 1: Get Token**
```bash
TOKEN=$(curl -s -X POST http://localhost/pioneer-dental/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "client_id": "mtb_gateway",
    "client_secret": "mtb_secret_key_12345"
  }' | grep -o '"token":"[^"]*' | cut -d'"' -f4)

echo "Token: $TOKEN"
```

**Step 2: Check Due Bills**
```bash
curl -X POST http://localhost/pioneer-dental/api/due-bills/check \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "student_id": 1829,
    "phone": "01711781724"
  }'
```

**Step 3: Process Payment**
```bash
curl -X POST http://localhost/pioneer-dental/api/payments/process \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "student_id": 1829,
    "bill_month": 11,
    "bill_year": 2025,
    "amount": 10150,
    "payment_method": "gateway",
    "external_ref": "PGW-REF-123456",
    "transaction_ref": "BANK-TXN-789012"
  }'
```

---

### Example 2: JavaScript/Fetch

```javascript
// Step 1: Get Token
async function getToken() {
  const response = await fetch('http://localhost/pioneer-dental/api/auth/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      client_id: 'mtb_gateway',
      client_secret: 'mtb_secret_key_12345'
    })
  });
  
  const data = await response.json();
  if (!data.success) {
    throw new Error(data.message);
  }
  
  return data.token;
}

// Step 2: Check Due Bills
async function checkDueBills(token, studentId, phone) {
  const response = await fetch('http://localhost/pioneer-dental/api/due-bills/check', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`
    },
    body: JSON.stringify({
      student_id: studentId,
      phone: phone
    })
  });
  
  const data = await response.json();
  return data;
}

// Usage
(async () => {
  try {
  const token = await getToken();
    console.log('Token obtained:', token);
    
    const bills = await checkDueBills(token, 1829, '01711781724');
    console.log('Due bills:', bills);
  } catch (error) {
    console.error('Error:', error);
  }
})();
```

---

### Example 3: Python

```python
import requests
import json

BASE_URL = 'http://localhost/pioneer-dental/api'

# Step 1: Get Token
def get_token():
    url = f'{BASE_URL}/auth/login'
    headers = {'Content-Type': 'application/json'}
    data = {
        'client_id': 'mtb_gateway',
        'client_secret': 'mtb_secret_key_12345'
    }
    
    response = requests.post(url, headers=headers, json=data)
    response.raise_for_status()
    
    result = response.json()
    if not result.get('success'):
        raise Exception(result.get('message', 'Login failed'))
    
    return result['token']

# Step 2: Check Due Bills
def check_due_bills(token, student_id, phone):
    url = f'{BASE_URL}/due-bills/check'
    headers = {
        'Content-Type': 'application/json',
        'Authorization': f'Bearer {token}'
    }
    data = {
        'student_id': student_id,
        'phone': phone
    }
    
    response = requests.post(url, headers=headers, json=data)
    response.raise_for_status()
    
    return response.json()

# Usage
if __name__ == '__main__':
    try:
token = get_token()
        print(f'Token obtained: {token[:20]}...')
        
        bills = check_due_bills(token, 1829, '01711781724')
        print(f'Due bills: {json.dumps(bills, indent=2)}')
    except Exception as e:
        print(f'Error: {e}')
```

---

### Example 4: PHP

```php
<?php
$base_url = 'http://localhost/pioneer-dental/api';

// Step 1: Get Token
function getToken($base_url) {
    $url = $base_url . '/auth/login';
    $data = json_encode([
        'client_id' => 'mtb_gateway',
        'client_secret' => 'mtb_secret_key_12345'
    ]);
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200) {
        throw new Exception('Login failed');
    }
    
    $result = json_decode($response, true);
    if (!$result['success']) {
        throw new Exception($result['message']);
    }
    
    return $result['token'];
}

// Step 2: Check Due Bills
function checkDueBills($base_url, $token, $student_id, $phone) {
    $url = $base_url . '/due-bills/check';
    $data = json_encode([
        'student_id' => $student_id,
        'phone' => $phone
    ]);
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

// Usage
try {
    $token = getToken($base_url);
    echo "Token obtained: " . substr($token, 0, 20) . "...\n";
    
    $bills = checkDueBills($base_url, $token, 1829, '01711781724');
    echo "Due bills: " . json_encode($bills, JSON_PRETTY_PRINT) . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
```

---

## Error Handling

### Common HTTP Status Codes

| Status Code | Meaning | What to Do |
|-------------|---------|------------|
| **200** | Success | Request completed successfully |
| **400** | Bad Request | Check your request format (missing fields, wrong data types) |
| **401** | Unauthorized | Token missing, invalid, or expired. Get a new token. |
| **404** | Not Found | Student not found or endpoint doesn't exist |
| **500** | Server Error | Contact support - something went wrong on the server |

### Common Error Responses

#### 401 - Missing Token
```json
{
  "success": false,
  "message": "Bearer token not provided",
  "error": "Unauthorized"
}
```
**Solution:** Add `Authorization: Bearer YOUR_TOKEN` header.

#### 401 - Invalid/Expired Token
```json
{
  "success": false,
  "message": "Invalid or expired token",
  "error": "Unauthorized"
}
```
**Solution:** Request a new token using `/api/auth/login`.

#### 401 - Invalid Credentials
```json
{
  "success": false,
  "message": "Invalid client credentials"
}
```
**Solution:** Check your `client_id` and `client_secret`.

#### 400 - Missing Required Field
```json
{
  "status": "error",
  "message": "Either student_id or phone is required"
}
```
**Solution:** Include at least one identifier in your request.

#### 404 - Student Not Found
```json
{
  "status": "error",
  "message": "Student not found with provided credentials"
}
```
**Solution:** Verify student ID/phone number is correct.

#### 400 - Invalid Amount
```json
{
  "status": "error",
  "message": "Amount exceeds remaining due"
}
```
**Solution:** Check the due bills first to see the correct amount.

---

## Postman Collection

We've created ready-to-use Postman collections for you! See the `postman-collections/` folder in this project.

**How to Import:**
1. Open Postman
2. Click "Import" button (top left)
3. Select the JSON file from `postman-collections/` folder
4. All requests are pre-configured with:
   - Correct URLs
   - Headers
   - Example request bodies
   - Variable for your token

**Using the Collection:**
1. First, run the "Login" request to get your token
2. The token will be automatically saved in a collection variable
3. All other requests will use this token automatically

For detailed instructions, see `postman-collections/README.md`.

---

## Security Best Practices

### 🔒 For Production Use

1. **Use HTTPS**: Always use HTTPS in production (not HTTP)
2. **Secure Token Storage**: Store tokens securely in your application (don't log them)
3. **Token Rotation**: Implement automatic token refresh before expiration
4. **Rate Limiting**: Implement rate limiting in your application
5. **Validate Responses**: Always check response status codes before processing data
6. **Error Logging**: Log errors for debugging (but don't log tokens!)

### 🛡️ Token Security

- ❌ **Never** share your token publicly
- ❌ **Never** commit tokens to version control (Git)
- ❌ **Never** log tokens in plain text
- ✅ **Always** store tokens securely (encrypted if possible)
- ✅ **Always** use HTTPS when transmitting tokens
- ✅ **Always** refresh tokens before they expire

---

## Troubleshooting

### Problem: "Bearer token not provided"

**Solution:**
- Make sure you're including the `Authorization` header
- Format should be: `Authorization: Bearer YOUR_TOKEN`
- Don't forget the word "Bearer" (with a space)

### Problem: "Invalid or expired token"

**Solution:**
- Your token expired (24 hours). Get a new one using `/api/auth/login`
- Make sure you copied the entire token (they're long!)
- Check that you're not adding extra characters/spaces

### Problem: "Invalid client credentials"

**Solution:**
- Verify your `client_id` and `client_secret` are correct
- Check for typos (they're case-sensitive)
- Contact Pioneer Dental support if credentials aren't working

### Problem: "Student not found"

**Solution:**
- Verify student ID or phone number exists in the system
- Phone numbers must match exactly (including format)
- Try using both `student_id` AND `phone` for better results

### Problem: "Amount exceeds remaining due"

**Solution:**
- First, check due bills to see the exact amount owed
- Don't try to pay more than the remaining due amount
- For partial payments, make sure amount is less than remaining due

---

## Support & Additional Resources

- **API Test Cases**: See `API_TEST_CASES.md` for comprehensive test scenarios
- **JWT Setup Guide**: See `JWT_AUTHENTICATION.md` for detailed JWT configuration
- **Quick Start Guide**: See `QUICK_START.md` for testing instructions
- **Web Testing Interface**: `http://localhost/pioneer-dental/test_due_bills_api.html`

**For issues or questions:**
Contact the Pioneer Dental development team.

---

**Happy Integrating! 🚀**
