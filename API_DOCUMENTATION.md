# Pioneer Dental API Documentation

## Due Bills API

### Endpoint: Get Due Bills for Student

Retrieve all due bills for a student by providing their student ID. Phone number is optional but recommended for secure access.

#### Endpoints

1. **POST** `/api/due-bills/check`
2. **GET** `/api/due-bills/get`

#### Request

**POST Method (with phone):**
```json
{
  "student_id": 123,
  "phone": "01708086211"
}
```

**POST Method (without phone):**
```json
{
  "student_id": 123
}
```

**GET Method (with phone):**
```
GET /api/due-bills/get?student_id=123&phone=01708086211
```

**GET Method (without phone):**
```
GET /api/due-bills/get?student_id=123
```

#### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| student_id | integer | Yes | The student's ID or Registration Number |
| phone | string | No | Student's or parent's phone number for verification (optional but recommended) |

#### Response

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
  "phone_verified": true
}
```

**Error Responses:**

**400 Bad Request - Missing student_id:**
```json
{
  "status": "error",
  "message": "student_id is required"
}
```

**400 Bad Request - Missing phone:**
```json
{
  "status": "error",
  "message": "phone number is required"
}
```

**404 Not Found:**
```json
{
  "status": "error",
  "message": "Student not found"
}
```

**401 Unauthorized:**
```json
{
  "status": "error",
  "message": "Phone number does not match our records"
}
```

#### Features

- ✅ Optional phone verification (checks both student and parent phone numbers when provided)
- ✅ Works without phone for public information sharing
- ✅ Secure access when phone is provided
- ✅ Returns only due bills (Unpaid or Partial status)
- ✅ Calculates overdue status based on due date
- ✅ Provides summary with total amounts
- ✅ CORS enabled for cross-origin requests
- ✅ Supports both GET and POST methods
- ✅ Handles JSON and form data input
- ✅ Comprehensive error handling

#### Example Usage

**Using cURL (POST):**
```bash
curl -X POST http://localhost/pioneer-dental/api/due-bills/check \
  -H "Content-Type: application/json" \
  -d '{"student_id": 123, "phone": "01708086211"}'
```

**Using cURL (GET):**
```bash
curl "http://localhost/pioneer-dental/api/due-bills/get?student_id=123&phone=01708086211"
```

**Using JavaScript Fetch (POST):**
```javascript
fetch('http://localhost/pioneer-dental/api/due-bills/check', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
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

**Using JavaScript Fetch (GET):**
```javascript
const studentId = 123;
const phone = '01708086211';

fetch(`http://localhost/pioneer-dental/api/due-bills/get?student_id=${studentId}&phone=${phone}`)
  .then(response => response.json())
  .then(data => console.log(data))
  .catch(error => console.error('Error:', error));
```

#### Notes

- **student_id** is ALWAYS required
  - Can be database ID (e.g., 525)
  - Or Registration Number (e.g., 2218)
  - API automatically detects which one is provided
- **phone** is OPTIONAL:
  - If provided: Must match either student's or parent's phone number
  - If not provided: Returns data without verification (public access)
- Only active fees with status = 1 are returned
- Bills are filtered by payment_status: 'Unpaid' or 'Partial'
- The `is_overdue` flag indicates if the due date has passed
- All amounts are in BDT (Bangladeshi Taka)
- The summary provides quick overview of total amounts owed
- Response includes `phone_verified` field to indicate verification status

