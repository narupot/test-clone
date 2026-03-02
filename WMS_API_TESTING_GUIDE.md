# WMS API Testing Guide

## 📋 API Information
- **URL**: `http://localhost/api/wms/truck-plan`
- **API Key**: `wms_sk_7f8e2c9a3b1d4e6f9h2j5k8m1n4q7r9t`
- **Method**: POST (for truck-plan), GET (for logs)

---

## 🚀 Quick Test Commands

### Test 1: Using cURL via Header
```bash
curl -X POST "http://localhost/api/wms/truck-plan" \
  -H "Content-Type: application/json" \
  -H "X-API-Key: wms_sk_7f8e2c9a3b1d4e6f9h2j5k8m1n4q7r9t" \
  -d '{
    "Pickuptime": "2025-10-20 04:00",
    "TruckPlan": [
      { "MainOrder": "SMM260108225455", "Location": "A0-1" },
      { "MainOrder": "SMM260108225456", "Location": "A1-1" },
      { "MainOrder": "SMM260108225457", "Location": "A2-1" }
    ]
  }'
```

### Test 2: Using cURL via Query Parameter
```bash
curl -X POST "http://localhost/api/wms/truck-plan?api_key=wms_sk_7f8e2c9a3b1d4e6f9h2j5k8m1n4q7r9t" \
  -H "Content-Type: application/json" \
  -d '{
    "Pickuptime": "2025-10-20 04:00",
    "TruckPlan": [
      { "MainOrder": "SMM260108225455", "Location": "A0-1" }
    ]
  }'
```

### Test 3: Without API Key (Should get 401)
```bash
curl -X POST "http://localhost/api/wms/truck-plan" \
  -H "Content-Type: application/json" \
  -d '{"Pickuptime": "2025-10-20 04:00", "TruckPlan": []}'
```

### Test 4: Get Logs
```bash
curl -X GET "http://localhost/api/wms/logs?api_key=wms_sk_7f8e2c9a3b1d4e6f9h2j5k8m1n4q7r9t" \
  -H "Content-Type: application/json"
```

### Test 5: Get Logs with Filter
```bash
curl -X GET "http://localhost/api/wms/logs?api_key=wms_sk_7f8e2c9a3b1d4e6f9h2j5k8m1n4q7r9t&status=success&per_page=10" \
  -H "Content-Type: application/json"
```

### Test 6: Get Specific Log
```bash
curl -X GET "http://localhost/api/wms/logs/1?api_key=wms_sk_7f8e2c9a3b1d4e6f9h2j5k8m1n4q7r9t" \
  -H "Content-Type: application/json"
```

---

## 🔧 Using PowerShell Test Script

```powershell
# Run all tests
powershell -ExecutionPolicy Bypass -File "test-wms-api.ps1"
```

---

## 📊 Expected Response

### Success Response (200)
```json
{
  "status": "success",
  "message": "Truck plan processed",
  "log_id": 1,
  "summary": {
    "total_orders": 3,
    "updated": 3,
    "failed": 0,
    "pickup_time": "2025-10-20 04:00",
    "process_status": "success"
  },
  "failed_orders": []
}
```

### Error: Missing API Key (401)
```json
{
  "status": "error",
  "message": "Invalid or missing API Key"
}
```

### Error: Invalid Payload (422)
```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "Pickuptime": ["The Pickuptime field is required."],
    "TruckPlan": ["The TruckPlan field is required."]
  }
}
```

---

## 📝 Payload Format

### Create/Update Truck Plan
```json
{
  "Pickuptime": "YYYY-MM-DD HH:mm",
  "TruckPlan": [
    {
      "MainOrder": "Order ID or formatted_id",
      "Location": "Location code (e.g., A0-1, B1-2)"
    }
  ]
}
```

**Example:**
```json
{
  "Pickuptime": "2025-10-20 04:00",
  "TruckPlan": [
    { "MainOrder": "SMM260108225455", "Location": "A0-1" },
    { "MainOrder": "SMM260108225456", "Location": "A1-1" },
    { "MainOrder": "SMM260108225457", "Location": "A2-1" },
    { "MainOrder": "SMM260108225458", "Location": "A3-1" }
  ]
}
```

---

## 🔒 Security Notes

1. **API Key Management**:
   - Keep API Key in `.env` file (DO NOT commit to git)
   - Change API Key regularly
   - Use different keys for dev/staging/production

2. **Storage Location**:
   - `.env`: `WMS_API_KEY=wms_sk_7f8e2c9a3b1d4e6f9h2j5k8m1n4q7r9t`

3. **Rate Limiting** (Optional):
   - Consider adding rate limiting middleware
   - Current setup: Simple API Key validation

---

## 📚 API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/wms/truck-plan` | Create/Update truck plan |
| GET | `/api/wms/logs` | Get all logs |
| GET | `/api/wms/logs/{id}` | Get specific log |

---

## 🛠️ Testing Files

- **test-wms-api.ps1**: PowerShell test script (Windows)
- **test-wms-api.sh**: Bash test script (Linux/Mac)
- **test-wms-api-curl.sh**: Simple cURL commands

---

## ✅ Checklist

- [x] Middleware created: `ValidateWmsApiKey`
- [x] Routes protected with middleware
- [x] API Key stored in `.env`
- [x] Test scripts created
- [x] Documentation provided

**Status**: Ready for testing! 🚀
