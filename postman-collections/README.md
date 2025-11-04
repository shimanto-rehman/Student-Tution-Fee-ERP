# Postman Collections for Pioneer Dental API

This folder contains ready-to-use Postman collections for testing the Pioneer Dental API.

## 📁 Files

- **`Pioneer_Dental_API.postman_collection.json`** - Complete API collection with all endpoints

## 🚀 Quick Start

### Step 1: Import Collection

1. **Download Postman** (if you don't have it)
   - Download from: https://www.postman.com/downloads/
   - It's free!

2. **Open Postman**

3. **Import the Collection**
   - Click **"Import"** button (top left)
   - Click **"Choose Files"** or drag and drop
   - Select `Pioneer_Dental_API.postman_collection.json`
   - Click **"Import"**

### Step 2: Configure Base URL

1. Click on the collection name: **"Pioneer Dental API Collection"**
2. Click on the **"Variables"** tab
3. Update `base_url` if needed:
   - **Development**: `http://localhost/pioneer-dental`
   - **Production**: `https://yourdomain.com`
4. Save (click outside the field)

### Step 3: Get Your Token

1. Expand **"Authentication"** folder
2. Click on **"Login - Get JWT Token"**
3. Click **"Send"** button
4. The token will be **automatically saved** to collection variable `jwt_token`

### Step 4: Use Other Endpoints

All other requests will automatically use the saved token. Just:
1. Select any request from other folders
2. Click **"Send"**
3. Done! ✅

---

## 📋 Collection Structure

The collection is organized into folders:

### 1. **Authentication**
   - **Login - Get JWT Token** - Get your authentication token (run this first!)
   - **Validate Token** - Check if your token is still valid

### 2. **Due Bills**
   - Check Due Bills (POST) - Get unpaid bills
   - Get Due Bills (GET) - Same, using GET method
   - Examples with different parameter combinations

### 3. **Paid Bills**
   - Check Paid Bills (POST) - Get paid bills with history
   - Get Paid Bills (GET) - Same, using GET method

### 4. **Payments**
   - Process Single Payment - Record one payment
   - Process Multiple Payments - Record multiple payments at once
   - Process Payment - Pay Full Amount - Example with amount omitted

### 5. **Cron Jobs / Automation**
   - Generate Monthly Fees - Create bills for all students
   - Generate Monthly Fees - Current Month - Example with defaults

---

## 🔧 How It Works

### Automatic Token Management

The collection uses **Postman scripts** to automatically:
1. Extract token from login response
2. Save it to collection variable `jwt_token`
3. Use it in all other requests automatically

**You don't need to manually copy/paste tokens!**

### Collection Variables

The collection uses these variables:

| Variable | Default Value | Description |
|----------|---------------|-------------|
| `base_url` | `http://localhost/pioneer-dental` | Base URL for all requests |
| `jwt_token` | (empty) | Your JWT token (auto-filled after login) |

**To change base URL:**
1. Click on collection name
2. Go to "Variables" tab
3. Update `base_url` value
4. All requests will use the new URL automatically

---

## 📝 Example Workflow

### Complete Payment Flow

1. **Get Token**
   - Run: `Authentication > Login - Get JWT Token`
   - Token is saved automatically ✅

2. **Check Due Bills**
   - Run: `Due Bills > Check Due Bills (POST)`
   - See what bills need to be paid
   - Note the `amount.total_amount` for each bill

3. **Process Payment**
   - Run: `Payments > Process Single Payment`
   - Update the request body with:
     - Student ID
     - Bill month/year
     - Amount (from step 2)
     - Transaction reference from your gateway
   - Send request

4. **Verify Payment**
   - Run: `Paid Bills > Check Paid Bills (POST)`
   - Confirm payment appears in history ✅

---

## 🎯 Tips for Beginners

### Testing Individual Requests

1. **Always login first** - Token expires after 24 hours
2. **Update request body** - Change student_id, phone, amounts, etc.
3. **Check response** - Look at the JSON response to see results
4. **Read descriptions** - Each request has helpful descriptions

### Understanding Responses

- **200 OK** = Success ✅
- **400 Bad Request** = Check your request format
- **401 Unauthorized** = Token expired, login again
- **404 Not Found** = Student/bill not found

### Common Edits

**Before sending, you might want to change:**

1. **Student ID/Phone** - Update in request body
2. **Amounts** - Update payment amounts
3. **Month/Year** - For generating fees or checking specific bills
4. **Base URL** - If testing on different server

---

## 🔒 Security Notes

- **Never commit tokens** to version control
- **Tokens expire after 24 hours** - Login again if you get 401 errors
- **Use HTTPS in production** - Update base_url to https://

---

## 🐛 Troubleshooting

### Problem: "Bearer token not provided"

**Solution:**
- Make sure you ran the "Login" request first
- Check that token was saved (look at collection variables)
- If token is empty, login again

### Problem: "Invalid or expired token"

**Solution:**
- Token expired (24 hours). Run "Login" request again
- Token will be automatically updated

### Problem: Requests not working

**Solution:**
- Check base_url is correct
- Make sure XAMPP is running (Apache + MySQL)
- Verify student_id/phone exists in database
- Check API documentation for required fields

---

## 📚 Additional Resources

- **Full API Documentation**: See `API_DOCUMENTATION.md` in project root
- **Quick Start Guide**: See `QUICK_START.md` in project root
- **Test Cases**: See `API_TEST_CASES.md` in project root

---

## 🎉 You're Ready!

Import the collection and start testing. The token management is automatic, so you can focus on testing the API endpoints.

**Happy Testing! 🚀**

