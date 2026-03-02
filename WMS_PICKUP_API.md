# WMS Pickup API Documentation

## Overview
API นี้ใช้สำหรับรับข้อมูลแผนการเลือกของสินค้า (Truck Plan) จาก WMS (Warehouse Management System) และทำการอัพเดท location ของสินค้าในระบบ พร้อมบันทึก log ของการดำเนินการ

## Endpoints

### 1. อัพเดท Truck Plan
**POST** `/api/wms/truck-plan`

#### Request Body
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

#### Request Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| Pickuptime | string | Yes | เวลาเลือกของ (Format: YYYY-MM-DD HH:mm) |
| TruckPlan | array | Yes | Array ของการวางแผนการเลือก |
| TruckPlan[].MainOrder | string | Yes | ID หรือ Formatted ID ของ Order |
| TruckPlan[].Location | string | Yes | Location Code ในคลังสินค้า (เช่น A0-1, A1-1) |

#### Response (Success - 200)
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

#### Response (Partial Success - 200)
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

#### Response (Validation Error - 422)
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

#### Response (Error - 500)
```json
{
  "status": "error",
  "message": "Error processing truck plan",
  "error": "Exception message"
}
```

---

### 2. ดึงรายการ Log
**GET** `/api/wms/logs`

#### Query Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| from_date | string | No | วันเริ่มต้น (Format: YYYY-MM-DD HH:mm:ss) |
| to_date | string | No | วันสิ้นสุด (Format: YYYY-MM-DD HH:mm:ss) |
| status | string | No | สถานะ (success, partial, failed) |
| per_page | integer | No | จำนวน record ต่อหน้า (Default: 20) |

#### Example Request
```
GET /api/wms/logs?status=success&per_page=10
```

#### Response (200)
```json
{
  "status": "success",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "pickup_time": "2025-10-20 04:00:00",
        "truck_plan": [...],
        "total_orders": 4,
        "updated_count": 4,
        "failed_count": 0,
        "failed_orders": null,
        "request_data": {...},
        "status": "success",
        "created_at": "2026-01-18T14:32:34.000000Z",
        "updated_at": "2026-01-18T14:32:34.000000Z"
      }
    ],
    "last_page": 1,
    "total": 1
  }
}
```

---

### 3. ดึง Log Detail
**GET** `/api/wms/logs/{id}`

#### URL Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | Log ID |

#### Example Request
```
GET /api/wms/logs/1
```

#### Response (Success - 200)
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "pickup_time": "2025-10-20 04:00:00",
    "truck_plan": [
      { "MainOrder": "SMM260108225455", "Location": "A0-1" },
      { "MainOrder": "SMM260108225456", "Location": "A1-1" }
    ],
    "total_orders": 2,
    "updated_count": 2,
    "failed_count": 0,
    "failed_orders": null,
    "request_data": {
      "Pickuptime": "2025-10-20 04:00",
      "TruckPlan": [...]
    },
    "status": "success",
    "created_at": "2026-01-18T14:32:34.000000Z",
    "updated_at": "2026-01-18T14:32:34.000000Z"
  }
}
```

#### Response (Not Found - 404)
```json
{
  "status": "error",
  "message": "Log not found"
}
```

---

## Database Schema

### Table: wms_pickup_logs
```sql
CREATE TABLE wms_pickup_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    pickup_time DATETIME COMMENT 'Pickup time from request',
    truck_plan JSON COMMENT 'Full truck plan JSON data',
    total_orders INT COMMENT 'Total number of orders processed',
    updated_count INT DEFAULT 0 COMMENT 'Number of orders successfully updated',
    failed_count INT DEFAULT 0 COMMENT 'Number of orders failed',
    failed_orders TEXT COMMENT 'JSON list of failed order details',
    request_data TEXT COMMENT 'Full request data',
    status VARCHAR(255) DEFAULT 'success' COMMENT 'success or failed',
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Update: order_detail table
```sql
-- เพิ่มฟิลด์ wms_location
ALTER TABLE order_detail ADD COLUMN wms_location VARCHAR(255) NULL COMMENT 'WMS Location Code';
```

---

## Flow Diagram

```
WMS System
    ↓
POST /api/wms/truck-plan
    ↓
Validation Check
    ↓
For each order in TruckPlan:
    ├─→ Find Order (by formatted_id or id)
    ├─→ Update order_detail.wms_location
    ├─→ Track success/failure
    └─→ Collect failed orders
    ↓
Create WmsPickupLog Record
    ├─→ pickup_time
    ├─→ truck_plan (JSON)
    ├─→ updated_count
    ├─→ failed_count
    ├─→ failed_orders
    ├─→ request_data
    └─→ status
    ↓
Return Response with Summary
```

---

## Example Usage with cURL

### Update Truck Plan
```bash
curl -X POST "http://localhost/api/wms/truck-plan" \
  -H "Content-Type: application/json" \
  -d '{
    "Pickuptime": "2025-10-20 04:00",
    "TruckPlan": [
      { "MainOrder": "SMM260108225455", "Location": "A0-1" },
      { "MainOrder": "SMM260108225456", "Location": "A1-1" }
    ]
  }'
```

### Get Logs
```bash
curl "http://localhost/api/wms/logs?status=success&per_page=10"
```

### Get Log Detail
```bash
curl "http://localhost/api/wms/logs/1"
```

---

## Example Usage with JavaScript/Fetch

```javascript
// Update Truck Plan
const updateTruckPlan = async () => {
  const response = await fetch('/api/wms/truck-plan', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
      Pickuptime: '2025-10-20 04:00',
      TruckPlan: [
        { MainOrder: 'SMM260108225455', Location: 'A0-1' },
        { MainOrder: 'SMM260108225456', Location: 'A1-1' }
      ]
    })
  });
  
  const data = await response.json();
  console.log(data);
};

// Get Logs
const getLogs = async () => {
  const response = await fetch('/api/wms/logs?status=success&per_page=20');
  const data = await response.json();
  console.log(data);
};

// Get Log Detail
const getLogDetail = async (logId) => {
  const response = await fetch(`/api/wms/logs/${logId}`);
  const data = await response.json();
  console.log(data);
};
```

---

## Files Created/Modified

### Created Files:
1. **[database/migrations/2026_01_18_143234_add_wms_location_to_order_detail.php](database/migrations/2026_01_18_143234_add_wms_location_to_order_detail.php)**
   - Migration เพื่อเพิ่ม `wms_location` field ในตาราง `order_detail`

2. **[database/migrations/2026_01_18_143253_create_wms_pickup_logs_table.php](database/migrations/2026_01_18_143253_create_wms_pickup_logs_table.php)**
   - Migration เพื่อสร้างตาราง `wms_pickup_logs`

3. **[app/WmsPickupLog.php](app/WmsPickupLog.php)**
   - Model สำหรับตาราง `wms_pickup_logs`

4. **[app/Http/Controllers/Api/WmsController.php](app/Http/Controllers/Api/WmsController.php)**
   - API Controller ที่รองรับ 3 methods:
     - `updateTruckPlan()` - รับข้อมูล truck plan และทำการอัพเดท
     - `getPickupLogs()` - ดึงรายการ log
     - `getLogDetail()` - ดึง log detail

### Modified Files:
1. **[routes/api.php](routes/api.php)**
   - เพิ่ม 3 routes สำหรับ WMS API

---

## Status Codes

| Code | Status | Description |
|------|--------|-------------|
| 200 | OK | Request successful |
| 422 | Unprocessable Entity | Validation failed |
| 404 | Not Found | Resource not found |
| 500 | Internal Server Error | Server error |

---

## Notes

1. **MainOrder** สามารถใช้ได้ทั้ง `formatted_id` (เช่น SMM260108225455) หรือ `id` (numeric ID)
2. **Location** จะถูกเก็บในฟิลด์ `wms_location` ของตาราง `order_detail`
3. **Log** บันทึกข้อมูลทั้งหมดที่ได้รับ เพื่อใช้สำหรับการตรวจสอบและ debug
4. หากมี order ล้มเหลว ระบบจะจดบันทึกเหตุผลการล้มเหลวในฟิลด์ `failed_orders`
5. Status สามารถมีค่าดังนี้:
   - `success` - ทั้งหมดสำเร็จ
   - `partial` - บางส่วนสำเร็จ
   - `failed` - ทั้งหมดล้มเหลว

---

## Testing

### ทดสอบด้วย Postman

1. **URL:** `POST http://localhost/api/wms/truck-plan`
2. **Headers:** 
   - Content-Type: application/json
3. **Body (raw JSON):**
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

สำหรับข้อมูลเพิ่มเติมหรือปัญหา กรุณาติดต่อ Development Team
