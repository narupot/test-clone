# WMS Pickup API - Complete Implementation Guide

## 📋 Overview

ระบบ WMS Pickup API ได้รับข้อมูล Truck Plan จากระบบ WMS และทำการอัพเดท location ของสินค้าในฐานข้อมูล พร้อมบันทึก log สำหรับการตรวจสอบ

## 🎯 Features

✅ รับข้อมูล Truck Plan (JSON)  
✅ อัพเดท `wms_location` ในตาราง `smm_order_detail`  
✅ บันทึก log ของทุกการดำเนินการ  
✅ ตรวจสอบข้อมูล (Validation)  
✅ บันทึกรายการ Order ที่ล้มเหลว  
✅ API Filter logs ด้วยวันที่ และสถานะ  
✅ Web UI สำหรับจัดการ (Blade Template)  
✅ JavaScript library สำหรับ frontend  

## 📁 Files Created

### Database Migrations
```
database/migrations/
├── 2026_01_18_143234_add_wms_location_to_order_detail.php
│   └── เพิ่ม wms_location field ในตาราง order_detail
└── 2026_01_18_143253_create_wms_pickup_logs_table.php
    └── สร้างตาราง wms_pickup_logs สำหรับเก็บ log
```

### Models
```
app/
└── WmsPickupLog.php
    └── Model สำหรับตาราง wms_pickup_logs
```

### Controllers
```
app/Http/Controllers/Api/
└── WmsController.php
    └── API Controller ที่มี 3 methods:
        - updateTruckPlan()  - รับข้อมูลและทำการอัพเดท
        - getPickupLogs()    - ดึงรายการ log
        - getLogDetail()     - ดึง log detail
```

### Routes
```
routes/
└── api.php (updated)
    ├── POST   /api/wms/truck-plan  → WmsController@updateTruckPlan
    ├── GET    /api/wms/logs        → WmsController@getPickupLogs
    └── GET    /api/wms/logs/{id}   → WmsController@getLogDetail
```

### Views & Frontend
```
resources/views/wms/
└── pickup-management.blade.php
    └── UI สำหรับจัดการ Pickup

public/js/
└── wms-api.js
    └── JavaScript library สำหรับเรียก API

examples/
└── wms_api_examples.php
    └── ตัวอย่างการใช้ PHP cURL
```

### Documentation
```
└── WMS_PICKUP_API.md
    └── API Documentation ฉบับสมบูรณ์
```

## 🚀 Quick Start

### 1. Database Setup
```bash
cd c:\Users\pichaet.t\Documents\GitHub\smm-webapp

# Migration ทำไปแล้ว แต่ถ้าต้องรัน:
php artisan migrate --path=database/migrations/2026_01_18_143253_create_wms_pickup_logs_table.php
```

### 2. Test API with cURL
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

### 3. Test API with PHP
```php
// ใช้ wms_api_examples.php
// เปิด examples/wms_api_examples.php และรัน
```

### 4. Test API with JavaScript
```javascript
// ใช้ public/js/wms-api.js
// ในหน้า HTML:
<script src="/js/wms-api.js"></script>
<script>
  updateTruckPlan({
    "Pickuptime": "2025-10-20 04:00",
    "TruckPlan": [
      { "MainOrder": "SMM260108225455", "Location": "A0-1" }
    ]
  });
</script>
```

### 5. View Web UI
```
http://localhost/wms/pickup-management
```

## 📊 Database Schema

### Table: wms_pickup_logs
```sql
CREATE TABLE wms_pickup_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    pickup_time DATETIME,
    truck_plan JSON,
    total_orders INT,
    updated_count INT DEFAULT 0,
    failed_count INT DEFAULT 0,
    failed_orders TEXT,
    request_data TEXT,
    status VARCHAR(255) DEFAULT 'success',
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Table: order_detail (Updated)
```sql
ALTER TABLE order_detail ADD COLUMN wms_location VARCHAR(255) NULL;
```

## 🔄 Flow Diagram

```
┌─────────────────────────────────────┐
│    WMS External System              │
│  (Send Truck Plan Data)             │
└────────────┬────────────────────────┘
             │
             ▼
┌─────────────────────────────────────┐
│  POST /api/wms/truck-plan           │
│  (JSON Request)                      │
└────────────┬────────────────────────┘
             │
             ▼
┌─────────────────────────────────────┐
│  WmsController@updateTruckPlan      │
│  1. Validate Input                  │
│  2. For each Order:                 │
│     - Find Order by ID/formatted_id │
│     - Update order_detail location  │
│     - Track success/failure         │
└────────────┬────────────────────────┘
             │
             ▼
┌─────────────────────────────────────┐
│  Create WmsPickupLog Record         │
│  - pickup_time                      │
│  - truck_plan (JSON)                │
│  - updated_count                    │
│  - failed_count                     │
│  - failed_orders                    │
│  - request_data                     │
│  - status                           │
└────────────┬────────────────────────┘
             │
             ▼
┌─────────────────────────────────────┐
│  Return JSON Response               │
│  - success/error status             │
│  - summary (updated, failed)        │
│  - log_id                           │
│  - failed_orders detail             │
└─────────────────────────────────────┘
```

## 📝 API Endpoints Summary

### 1. Update Truck Plan
```
POST /api/wms/truck-plan

Request:
{
  "Pickuptime": "2025-10-20 04:00",
  "TruckPlan": [
    { "MainOrder": "SMM260108225455", "Location": "A0-1" }
  ]
}

Response:
{
  "status": "success",
  "message": "Truck plan processed",
  "log_id": 1,
  "summary": {
    "total_orders": 1,
    "updated": 1,
    "failed": 0,
    "process_status": "success"
  }
}
```

### 2. Get Logs
```
GET /api/wms/logs?status=success&per_page=20

Response:
{
  "status": "success",
  "data": {
    "current_page": 1,
    "data": [...],
    "last_page": 1,
    "total": 1
  }
}
```

### 3. Get Log Detail
```
GET /api/wms/logs/1

Response:
{
  "status": "success",
  "data": {
    "id": 1,
    "pickup_time": "2025-10-20 04:00:00",
    "truck_plan": [...],
    "status": "success",
    ...
  }
}
```

## 🧪 Testing Examples

### Using Postman
1. Create new POST request
2. URL: `http://localhost/api/wms/truck-plan`
3. Body (raw JSON):
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

### Using JavaScript/Browser Console
```javascript
// ใช้ wms-api.js ที่เรียบร้อยแล้ว
updateTruckPlan().then(data => console.log(data));
getPickupLogs({status: 'success'}).then(data => console.log(data));
getLogDetail(1).then(data => console.log(data));
```

## 🔍 Log Status Values

| Status | Meaning |
|--------|---------|
| `success` | ทั้งหมดสำเร็จ |
| `partial` | บางส่วนสำเร็จ บางส่วนล้มเหลว |
| `failed` | ทั้งหมดล้มเหลว |

## ⚙️ Configuration

### Laravel Routes (api.php)
```php
Route::post('wms/truck-plan', 'Api\WmsController@updateTruckPlan');
Route::get('wms/logs', 'Api\WmsController@getPickupLogs');
Route::get('wms/logs/{id}', 'Api\WmsController@getLogDetail');
```

### Model Relationships
- OrderDetail ← belongsTo → Order (by order_id)
- WmsPickupLog (standalone)

## 🛡️ Validation Rules

| Field | Rule |
|-------|------|
| Pickuptime | required, date_format:Y-m-d H:i |
| TruckPlan | required, array |
| MainOrder | required, string |
| Location | required, string |

## 📚 Additional Files for Reference

- [WMS_PICKUP_API.md](WMS_PICKUP_API.md) - API Documentation
- [examples/wms_api_examples.php](examples/wms_api_examples.php) - PHP Examples
- [public/js/wms-api.js](public/js/wms-api.js) - JavaScript Library
- [resources/views/wms/pickup-management.blade.php](resources/views/wms/pickup-management.blade.php) - Web UI

## 🐛 Troubleshooting

### 1. Orders not found
- ตรวจสอบ MainOrder ID ว่าตรงกับ formatted_id หรือ id ในตาราง order
- ตรวจสอบว่า order นั้นมี order_detail ที่สัมพันธ์กัน

### 2. Migration failed
- ตรวจสอบว่า wms_location field มีอยู่แล้วหรือไม่
- ตรวจสอบ database permissions

### 3. API returns 422 error
- ตรวจสอบ JSON format
- ตรวจสอบ Pickuptime format: YYYY-MM-DD HH:mm
- ตรวจสอบว่า TruckPlan เป็น array

## 📞 Support

สำหรับข้อมูลเพิ่มเติม โปรดติดต่อ Development Team

## ✅ Implementation Checklist

- [x] สร้าง Migration สำหรับ wms_location field
- [x] สร้าง Migration สำหรับ wms_pickup_logs table
- [x] สร้าง WmsPickupLog Model
- [x] สร้าง WmsController API
- [x] เพิ่ม Routes ใน api.php
- [x] สร้าง API Documentation
- [x] สร้าง PHP Examples
- [x] สร้าง JavaScript Library
- [x] สร้าง Blade Template UI
- [x] ทดสอบ API

---

**Last Updated:** January 18, 2026  
**Version:** 1.0.0
