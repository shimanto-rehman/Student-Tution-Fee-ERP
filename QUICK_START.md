# 🚀 Quick Start: Testing Your Due Bills API

## ✅ Your API is LIVE and Working!

### 🎯 Fastest Way to Test (30 seconds)

**Just open this in your browser:**
```
http://localhost/pioneer-dental/test_due_bills_api.html
```

This gives you a beautiful web interface to test all API endpoints!

---

## 📋 Quick Reference

### Endpoints

| Method | Endpoint | Purpose |
|--------|----------|---------|
| **GET** | `/api/due-bills/get?student_id=X&phone=Y` | Get due bills via URL parameters |
| **POST** | `/api/due-bills/check` | Get due bills via JSON body |

### Sample Test Data

| Student ID | Name | Phone |
|------------|------|-------|
| 1 | Promit Saha | 01708086211 |
| 2 | Sakiba Islam | 01629830303 |
| 3 | Israt Jahan Tumpa | 01975650052 |

---

## 🧪 Test Methods

### Method 1: Browser (Easiest)
```
http://localhost/pioneer-dental/test_due_bills_api.html
```

### Method 2: Direct URL
```
http://localhost/pioneer-dental/api/due-bills/get?student_id=1&phone=01708086211
```

### Method 3: cURL
```bash
# GET
curl "http://localhost/pioneer-dental/api/due-bills/get?student_id=1&phone=01708086211"

# POST
curl -X POST http://localhost/pioneer-dental/api/due-bills/check \
  -H "Content-Type: application/json" \
  -d '{"student_id":1,"phone":"01708086211"}'
```

### Method 4: Postman/Insomnia
- Import the example requests from `TEST_API.md`

---

## 📊 Expected Responses

### ✅ Success (No Due Bills)
```json
{
  "status": "success",
  "student": {...},
  "bills": [],
  "summary": {
    "total_bills": 0,
    "total_amount": 0
  }
}
```

### ❌ Error (Wrong Phone)
```json
{
  "status": "error",
  "message": "Phone number does not match our records"
}
```

---

## 📁 Documentation Files

| File | Purpose |
|------|---------|
| `test_due_bills_api.html` | Interactive web UI for testing |
| `TEST_API.md` | Complete testing guide with examples |
| `API_DOCUMENTATION.md` | Full API documentation |

---

## 🐛 Troubleshooting

**Problem:** Getting 404 errors
- **Solution:** Make sure your web server (XAMPP) is running

**Problem:** No bills returned
- **Solution:** This is normal if the student has no due bills. Try generating some fees first.

**Problem:** Phone verification fails
- **Solution:** Check the database to verify the phone number matches exactly

---

## ✨ Features Tested & Working

- ✅ Phone verification (student & parent phone)
- ✅ Student ID lookup
- ✅ Due bills filtering (Unpaid/Partial only)
- ✅ Summary calculations
- ✅ Error handling
- ✅ CORS enabled
- ✅ Both GET and POST methods
- ✅ JSON responses

---

## 🎉 Ready to Use!

Your API is production-ready! Just share the endpoint with your users:

**Public URL:** (Replace with your domain)
```
https://yourdomain.com/api/due-bills/get?student_id=X&phone=Y
```

**API Documentation:** 
```
https://yourdomain.com/api_documentation.md
```

