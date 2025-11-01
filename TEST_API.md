# How to Test the Due Bills API

## Sample Data from Database

Based on the database, here are some sample students you can use for testing:

| Student ID | Name | Phone | Parent Phone | Reg No |
|------------|------|-------|--------------|--------|
| 1 | Promit Saha | 01708086211 | 919433140231 | 1842 |
| 2 | Sakiba Islam | 01629830303 | 01670942000 | 1762 |
| 3 | Israt Jahan Tumpa | 01975650052 | 01558191458 | 1832 |

## Method 1: Using cURL (Command Line)

### GET Method
```bash
curl "http://localhost/pioneer-dental/api/due-bills/get?student_id=1&phone=01708086211"
```

### POST Method
```bash
curl -X POST http://localhost/pioneer-dental/api/due-bills/check \
  -H "Content-Type: application/json" \
  -d '{"student_id": 1, "phone": "01708086211"}'
```

## Method 2: Using Browser (GET only)

Simply paste this URL in your browser:
```
http://localhost/pioneer-dental/api/due-bills/get?student_id=1&phone=01708086211
```

## Method 3: Using Postman or Insomnia

### GET Request
- **Method**: GET
- **URL**: `http://localhost/pioneer-dental/api/due-bills/get`
- **Query Parameters**:
  - `student_id`: 1
  - `phone`: 01708086211

### POST Request
- **Method**: POST
- **URL**: `http://localhost/pioneer-dental/api/due-bills/check`
- **Headers**:
  - `Content-Type`: application/json
- **Body** (raw JSON):
  ```json
  {
    "student_id": 1,
    "phone": "01708086211"
  }
  ```

## Method 4: Using JavaScript (HTML Test Page)

Open your browser console and run:

```javascript
fetch('http://localhost/pioneer-dental/api/due-bills/get?student_id=1&phone=01708086211')
  .then(response => response.json())
  .then(data => console.log(data))
  .catch(error => console.error('Error:', error));
```

## Method 5: Using PHP (For Quick Testing)

Create a test file `test_api.php` in your project root:

```php
<?php
// test_api.php
$base_url = 'http://localhost/pioneer-dental';

// Test GET method
$url = $base_url . '/api/due-bills/get?student_id=1&phone=01708086211';
$response = file_get_contents($url);
echo "GET Response:\n";
echo $response;
echo "\n\n";

// Test POST method
$data = json_encode(['student_id' => 1, 'phone' => '01708086211']);
$ch = curl_init($base_url . '/api/due-bills/check');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
curl_close($ch);
echo "POST Response:\n";
echo $response;
```

Run it with:
```bash
php test_api.php
```

## Expected Responses

### Success Response (if student has due bills)
```json
{
  "status": "success",
  "message": "Due bills retrieved successfully",
  "student": {
    "id": 1,
    "name": "Promit Saha",
    "reg_no": 1842,
    "phone": "01708086211",
    "parent_phone": "919433140231",
    "address": "77/8Bhupen roy road behala,kol"
  },
  "bills": [...],
  "summary": {
    "total_bills": 2,
    "total_amount": 20300.00,
    "total_late_fee": 300.00,
    "currency": "BDT"
  }
}
```

### Error Response (Wrong Phone)
```json
{
  "status": "error",
  "message": "Phone number does not match our records"
}
```

### Error Response (Student Not Found)
```json
{
  "status": "error",
  "message": "Student not found"
}
```

## Testing Different Scenarios

### 1. Correct Student ID + Correct Phone
```bash
curl "http://localhost/pioneer-dental/api/due-bills/get?student_id=1&phone=01708086211"
```
✅ Should return success with bills

### 2. Correct Student ID + Correct Parent Phone
```bash
curl "http://localhost/pioneer-dental/api/due-bills/get?student_id=1&phone=919433140231"
```
✅ Should return success with bills

### 3. Correct Student ID + Wrong Phone
```bash
curl "http://localhost/pioneer-dental/api/due-bills/get?student_id=1&phone=99999999999"
```
❌ Should return error: "Phone number does not match our records"

### 4. Wrong Student ID
```bash
curl "http://localhost/pioneer-dental/api/due-bills/get?student_id=99999&phone=01708086211"
```
❌ Should return error: "Student not found"

### 5. Missing Parameters
```bash
curl "http://localhost/pioneer-dental/api/due-bills/get?student_id=1"
```
❌ Should return error: "phone number is required"

## Troubleshooting

### Issue: API returns 404
**Solution**: Make sure your `.htaccess` file is properly configured and URL rewriting is enabled.

### Issue: API returns empty result
**Possible causes**:
1. Student has no due bills (all paid)
2. All bills are deleted (status = 0)

**Solution**: Check the database directly:
```sql
SELECT * FROM student_fees WHERE student_id = 1 AND payment_status IN ('Unpaid', 'Partial') AND status = '1';
```

### Issue: CORS errors in browser
**Solution**: The API already includes CORS headers. If you still get errors, check browser console for specific error messages.

## Quick Test Script

Save this as `quick_test.sh` and run it:

```bash
#!/bin/bash

echo "=== Testing Due Bills API ==="
echo ""
echo "Test 1: Valid request (GET)"
curl -s "http://localhost/pioneer-dental/api/due-bills/get?student_id=1&phone=01708086211" | python -m json.tool
echo ""
echo ""
echo "Test 2: Wrong phone (GET)"
curl -s "http://localhost/pioneer-dental/api/due-bills/get?student_id=1&phone=99999999999" | python -m json.tool
echo ""
echo ""
echo "Test 3: Valid request (POST)"
curl -s -X POST http://localhost/pioneer-dental/api/due-bills/check \
  -H "Content-Type: application/json" \
  -d '{"student_id":1,"phone":"01708086211"}' | python -m json.tool
```

Make it executable and run:
```bash
chmod +x quick_test.sh
./quick_test.sh
```

