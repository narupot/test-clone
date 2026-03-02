<!DOCTYPE html>
<html>
<head>
    <title>WMS Pickup API Test</title>
    <style>
        body { font-family: Arial; max-width: 1000px; margin: 20px; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; }
        textarea { width: 100%; height: 150px; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; }
        .response { background: #f0f0f0; padding: 15px; margin-top: 10px; }
        pre { background: #222; color: #0f0; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>WMS Pickup API Test</h1>
    
    <div class="section">
        <h2>1. Update Truck Plan</h2>
        <textarea id="pickupData">{
  "Pickuptime": "2025-10-20 04:00",
  "TruckPlan": [
    { "MainOrder": "SMM260108225455", "Location": "A0-1" },
    { "MainOrder": "SMM260108225456", "Location": "A1-1" }
  ]
}</textarea>
        <br><br>
        <button onclick="testUpdateTruckPlan()">Send Request</button>
        <div id="response1" class="response" style="display:none;">
            <h4>Response:</h4>
            <pre id="respText1"></pre>
        </div>
    </div>

    <div class="section">
        <h2>2. Get All Logs</h2>
        <label>Status Filter: <input type="text" id="statusFilter" placeholder="success/partial/failed"></label>
        <label>Per Page: <input type="number" id="perPage" value="20"></label>
        <br><br>
        <button onclick="testGetLogs()">Get Logs</button>
        <div id="response2" class="response" style="display:none;">
            <h4>Response:</h4>
            <pre id="respText2"></pre>
        </div>
    </div>

    <div class="section">
        <h2>3. Get Log Detail</h2>
        <label>Log ID: <input type="number" id="logId" placeholder="1" value="1"></label>
        <br><br>
        <button onclick="testGetLogDetail()">Get Detail</button>
        <div id="response3" class="response" style="display:none;">
            <h4>Response:</h4>
            <pre id="respText3"></pre>
        </div>
    </div>

    <script src="/js/wms-api.js"></script>
    <script>
        function testUpdateTruckPlan() {
            const data = JSON.parse(document.getElementById('pickupData').value);
            fetch('/api/wms/truck-plan', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(r => r.json())
            .then(d => {
                document.getElementById('response1').style.display = 'block';
                document.getElementById('respText1').textContent = JSON.stringify(d, null, 2);
            })
            .catch(e => alert('Error: ' + e));
        }

        function testGetLogs() {
            const status = document.getElementById('statusFilter').value;
            const perPage = document.getElementById('perPage').value;
            let url = '/api/wms/logs?per_page=' + perPage;
            if (status) url += '&status=' + status;
            
            fetch(url)
                .then(r => r.json())
                .then(d => {
                    document.getElementById('response2').style.display = 'block';
                    document.getElementById('respText2').textContent = JSON.stringify(d, null, 2);
                });
        }

        function testGetLogDetail() {
            const logId = document.getElementById('logId').value;
            fetch('/api/wms/logs/' + logId)
                .then(r => r.json())
                .then(d => {
                    document.getElementById('response3').style.display = 'block';
                    document.getElementById('respText3').textContent = JSON.stringify(d, null, 2);
                });
        }
    </script>
</body>
</html># 🎉 WMS Pickup API - Implementation Complete

## ✅ สิ่งที่ทำเรียบร้อย

### 1️⃣ Database & Models
- ✅ Migration: `add_wms_location_to_order_detail` - เพิ่ม column `wms_location` ในตาราง `order_detail`
- ✅ Migration: `create_wms_pickup_logs_table` - สร้างตาราง `wms_pickup_logs`
- ✅ Model: `WmsPickupLog` - พร้อมตั้งค่า fillable, casts

### 2️⃣ API Controllers
- ✅ `WmsController.php` - ที่มี 3 methods:
  - `updateTruckPlan()` - รับข้อมูล JSON และทำการอัพเดท order location
  - `getPickupLogs()` - ดึงรายการ log พร้อม filter
  - `getLogDetail()` - ดึง log detail ด้วย ID

### 3️⃣ Routes
- ✅ `POST /api/wms/truck-plan` - อัพเดท truck plan
- ✅ `GET /api/wms/logs` - ดึงรายการ log
- ✅ `GET /api/wms/logs/{id}` - ดึง log detail

### 4️⃣ Documentation
- ✅ `WMS_PICKUP_API.md` - API Documentation ฉบับสมบูรณ์
- ✅ `IMPLEMENTATION_SUMMARY.md` - สรุปการ implement

### 5️⃣ Frontend & Examples
- ✅ `wms-api.js` - JavaScript library สำหรับเรียก API
- ✅ `wms_api_examples.php` - ตัวอย่างการใช้ PHP
- ✅ `pickup-management.blade.php` - Web UI สำหรับจัดการ

---

## 🔄 Workflow

```
WMS System
    ↓
POST /api/wms/truck-plan (JSON)
    {
      "Pickuptime": "2025-10-20 04:00",
      "TruckPlan": [
        { "MainOrder": "SMM260108225455", "Location": "A0-1" },
        { "MainOrder": "SMM260108225456", "Location": "A1-1" },
        ...
      ]
    }
    ↓
WmsController@updateTruckPlan
    - Validate input
    - For each order:
      - Find Order by formatted_id or id
      - Update order_detail.wms_location
      - Track success/failure
    - Create WmsPickupLog record
    ↓
Return JSON Response
    {
      "status": "success",
      "message": "Truck plan processed",
      "log_id": 1,
      "summary": {
        "total_orders": 4,
        "updated": 4,
        "failed": 0,
        "process_status": "success"
      }
    }
```

---

## 📋 Request/Response Examples

### Request
```bash
curl -X POST "http://localhost/api/wms/truck-plan" \
  -H "Content-Type: application/json" \
  -d '{
    "Pickuptime": "2025-10-20 04:00",
    "TruckPlan": [
      { "MainOrder": "SMM260108225455", "Location": "A0-1" },
      { "MainOrder": "SMM260108225456", "Location": "A1-1" },
      { "MainOrder": "SMM260108225457", "Location": "A2-1" },
      { "MainOrder": "SMM260108225458", "Location": "A3-1" }
    ]
  }'
```

### Success Response (200)
```json
{
  "status": "success",
  "message": "Truck plan processed",
  "log_id": 1,
  "summary": {
    "total_orders": 4,
    "updated": 4,
    "failed": 0,
    "pickup_time": "2025-10-20 04:00",
    "process_status": "success"
  },
  "failed_orders": []
}
```

### Partial Success Response (200)
```json
{
  "status": "success",
  "message": "Truck plan processed",
  "log_id": 2,
  "summary": {
    "total_orders": 4,
    "updated": 3,
    "failed": 1,
    "pickup_time": "2025-10-20 04:00",
    "process_status": "partial"
  },
  "failed_orders": [
    {
      "order": "SMM260108225458",
      "location": "A3-1",
      "reason": "Order not found"
    }
  ]
}
```

---

## 📊 Database Schema

### wms_pickup_logs table
```
id               BIGINT (Primary Key)
pickup_time      DATETIME
truck_plan       JSON
total_orders     INT
updated_count    INT
failed_count     INT
failed_orders    TEXT (JSON)
request_data     TEXT (JSON)
status           VARCHAR(255) [success|partial|failed]
created_at       TIMESTAMP
updated_at       TIMESTAMP
```

### order_detail table (Updated)
```
... existing columns ...
wms_location     VARCHAR(255) NULL  ← NEW
```

---

## 🚀 How to Use

### 1. Send Truck Plan via API
```javascript
const response = await fetch('/api/wms/truck-plan', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
  },
  body: JSON.stringify({
    "Pickuptime": "2025-10-20 04:00",
    "TruckPlan": [
      { "MainOrder": "SMM260108225455", "Location": "A0-1" }
    ]
  })
});

const data = await response.json();
console.log(data); // ดูผลลัพธ์
```

### 2. Get All Logs
```javascript
const response = await fetch('/api/wms/logs?status=success&per_page=20');
const data = await response.json();
console.log(data);
```

### 3. Get Log Detail
```javascript
const response = await fetch('/api/wms/logs/1');
const data = await response.json();
console.log(data);
```

### 4. Use Web UI
```
http://localhost/wms/pickup-management
```
- ส่วน "Send Truck Plan" - สำหรับส่งข้อมูล
- ส่วน "View Logs" - สำหรับดูประวัติ log พร้อม filter

---

## 📁 Files Created

```
project/
├── app/
│   └── WmsPickupLog.php                    [NEW]
│
├── app/Http/Controllers/Api/
│   └── WmsController.php                   [NEW]
│
├── database/migrations/
│   ├── 2026_01_18_143234_add_wms_location_to_order_detail.php   [NEW]
│   └── 2026_01_18_143253_create_wms_pickup_logs_table.php       [NEW]
│
├── routes/
│   └── api.php                             [UPDATED]
│
├── resources/views/wms/
│   └── pickup-management.blade.php         [NEW]
│
├── public/js/
│   └── wms-api.js                          [NEW]
│
├── examples/
│   └── wms_api_examples.php                [NEW]
│
├── WMS_PICKUP_API.md                       [NEW - API Documentation]
├── IMPLEMENTATION_SUMMARY.md               [NEW - Implementation Guide]
└── README_WMS_IMPLEMENTATION.md            [THIS FILE]
```

---

## 🔍 Features Breakdown

### updateTruckPlan() Method
- ✅ Validates Pickuptime (format: YYYY-MM-DD HH:mm)
- ✅ Validates TruckPlan array structure
- ✅ Searches orders by formatted_id or id
- ✅ Updates order_detail.wms_location for all items
- ✅ Tracks success/failure counts
- ✅ Stores failed order details with reasons
- ✅ Logs everything in wms_pickup_logs table
- ✅ Returns detailed response with summary

### getPickupLogs() Method
- ✅ Filter by status (success/partial/failed)
- ✅ Filter by date range (from_date, to_date)
- ✅ Pagination support (per_page parameter)
- ✅ Ordered by created_at DESC

### getLogDetail() Method
- ✅ Retrieves single log with full details
- ✅ Returns 404 if log not found
- ✅ Includes truck_plan and failed_orders

---

## 🧪 Testing

### Using cURL
```bash
# Test update
curl -X POST http://localhost/api/wms/truck-plan \
  -H "Content-Type: application/json" \
  -d '{"Pickuptime":"2025-10-20 04:00","TruckPlan":[{"MainOrder":"SMM260108225455","Location":"A0-1"}]}'

# Get logs
curl http://localhost/api/wms/logs

# Get specific log
curl http://localhost/api/wms/logs/1
```

### Using Postman
1. New POST request
2. URL: `http://localhost/api/wms/truck-plan`
3. Headers: `Content-Type: application/json`
4. Body: Raw JSON (see examples above)

### Using JavaScript Console
```javascript
// ใช้ wms-api.js ที่ loaded ในหน้า
updateTruckPlan();           // ส่งข้อมูล
getPickupLogs();             // ดึง logs
getLogDetail(1);             // ดึง log detail
displayLogsTable();          // แสดง logs ในตาราง
```

---

## ⚙️ Configuration

### API Endpoints Location
- File: `routes/api.php`
- Prefix: `/api/`
- Middleware: API group (default: no auth required)

### Controller Location
- File: `app/Http/Controllers/Api/WmsController.php`
- Namespace: `App\Http\Controllers\Api`

### Model Location
- File: `app/WmsPickupLog.php`
- Namespace: `App`

---

## 🛡️ Validation Rules

| Field | Rules |
|-------|-------|
| Pickuptime | required, date_format:Y-m-d H:i |
| TruckPlan | required, array |
| TruckPlan.*.MainOrder | required, string |
| TruckPlan.*.Location | required, string |

---

## 🎯 Status Codes & Meanings

| Code | Status | Meaning |
|------|--------|---------|
| 200 | OK | Request successful |
| 422 | Unprocessable Entity | Validation failed |
| 404 | Not Found | Resource not found (for log detail) |
| 500 | Internal Server Error | Server error |

---

## 📝 Log Storage Details

### What Gets Stored
- ✅ pickup_time - เวลาที่ได้รับ
- ✅ truck_plan - ข้อมูล TruckPlan (JSON)
- ✅ total_orders - จำนวน order ทั้งหมด
- ✅ updated_count - จำนวนที่อัพเดทสำเร็จ
- ✅ failed_count - จำนวนที่ล้มเหลว
- ✅ failed_orders - รายละเอียด order ที่ล้มเหลว (JSON)
- ✅ request_data - ข้อมูล request เต็ม (JSON)
- ✅ status - สถานะ (success/partial/failed)

### Audit Trail
ทุกการดำเนินการ log ไว้ในตาราง wms_pickup_logs เพื่อ:
- ตรวจสอบข้อมูลได้ anytime
- ค้นหา order ที่มีปัญหา
- วิเคราะห์สถิติความสำเร็จ
- Debug ปัญหาจากข้อมูลที่ส่งมา

---

## 🚨 Common Issues & Solutions

### Issue 1: Order not found
```
"reason": "Order not found"
```
**Solution:**
- ตรวจสอบ MainOrder ID ว่าถูกต้อง
- สอบ formatted_id ในตาราง order
- ตรวจสอบว่า order มี order_detail หรือไม่

### Issue 2: Validation failed (422)
```json
{
  "errors": {
    "Pickuptime": ["The Pickuptime field is required."]
  }
}
```
**Solution:**
- ตรวจสอบ JSON format
- Pickuptime format: YYYY-MM-DD HH:mm
- TruckPlan ต้องเป็น array

### Issue 3: Database column already exists
**Solution:**
- Column wms_location มีอยู่แล้วในระบบ
- ไม่ต้องรัน migration อีก

---

## 📞 Next Steps

1. **Test API** ด้วยข้อมูล จริง
2. **Integrate** กับ WMS System
3. **Monitor** logs ผ่าน Web UI
4. **Verify** ว่า order location อัพเดทถูกต้อง

---

## 📚 Reference Files

| File | Purpose |
|------|---------|
| [WMS_PICKUP_API.md](WMS_PICKUP_API.md) | API Documentation ฉบับเต็ม |
| [IMPLEMENTATION_SUMMARY.md](IMPLEMENTATION_SUMMARY.md) | Implementation Guide |
| [examples/wms_api_examples.php](examples/wms_api_examples.php) | PHP Code Examples |
| [public/js/wms-api.js](public/js/wms-api.js) | JavaScript Library |

---

**Last Updated:** January 18, 2026  
**Status:** ✅ Ready for Production  
**Version:** 1.0.0

---

## 🎯 Key Points to Remember

1. **MainOrder** สามารถใช้ได้ทั้ง formatted_id (SMM260108225455) หรือ numeric id
2. **Location** จะถูกบันทึกในฟิลด์ `wms_location` ของ order_detail
3. **Log** บันทึกทุกครั้งที่มีการ request มา
4. **Partial Success** เมื่อบาง order สำเร็จ บาง order ล้มเหลว
5. **Filter** logs ได้ด้วย status, date range

---

✨ **Happy Coding!** ✨
