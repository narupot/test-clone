# สรุปการแก้ไข - ระบบที่อยู่และแผนที่

## 1. Backend - AjaxController.php

### 1.1 geocodeToSmm() - เพิ่ม geocode_log สำหรับ Debug

- **geocode_log** ถูกส่งกลับในทุกกรณี (success, partial, failed) ประกอบด้วย:
  - `formatted_address` - ที่อยู่ที่จัดรูปแบบจาก Google
  - `extracted` - provinceName, districtName, subDistrictName, zipCode ที่ดึงจาก address_components
  - `matched` - ค่าที่ match กับ smm_master (province, district, sub_district, zip_code)
  - `first_result_components` - กรณีพิกัดไม่อยู่ในไทย
  - `raw_response` - กรณีไม่พบข้อมูลจาก API

### 1.2 extractAddressFromComponents() - Fallback ดึงเขตจาก formatted_address

- เพิ่มพารามิเตอร์ `$formattedAddress` (optional)
- เมื่อ `districtName` ว่าง (กรุงเทพฯ มักไม่มีเขตใน address_components):
  - ดึงจากรูปแบบ `เขตXXX` (เช่น เขตพระนคร → พระนคร)
  - ดึงจากรูปแบบ `อำเภอXXX` (จังหวัดอื่น)

### 1.3 การส่ง formatted_address

- เก็บ `formatted_address` จาก result ที่มี country=TH
- ส่งไปยัง `extractAddressFromComponents()` เพื่อใช้ fallback

---

## 2. Frontend - หน้าเพิ่มที่อยู่ (addressAdd.blade.php)

### 2.1 Log Display สำหรับ Debug

- เพิ่มบล็อก **"ข้อมูลจาก Google (Debug)"** ใต้ปุ่มเลือกตำแหน่งบนแผนที่
- แสดงเฉพาะเมื่อ `use_smm_address` เปิดใช้งาน
- ใช้ `<details>` ให้กดเปิด/ปิดได้
- แสดง log หลังเรียก geocode API (ทั้ง success, partial, failed)
- ใช้ `.text()` เพื่อป้องกัน XSS

### 2.2 CSS

```css
#geocode-debug-log { ... }
#geocode-debug-log summary { ... }
#geocode-debug-log pre { ... }
```

---

## 3. ภาษา - resources/lang/de/customer.php

### 3.1 แก้ไข label ช่อง road

- **เดิม:** `'road'=>'แขวง/ตำบล'` (ซ้ำกับ label แขวง/ตำบล dropdown)
- **ใหม่:** `'road'=>'ถนน'`

---

## 4. แผนที่ - addressList.blade.php

### 4.1 เปิดการเลื่อนแผนที่ (Pan)

- **gestureHandling:** `"none"` → `"greedy"`
- ทำให้สามารถ:
  - คลิกค้างแล้วลากเพื่อเลื่อนแผนที่
  - ใช้ scroll wheel ซูมได้ (เดสก์ท็อป)
  - ปัดและ pinch ซูมได้ (มือถือ)

### 4.2 ปุ่ม Zoom

- ปุ่ม zoom (+/-) ถูกเพิ่มแล้วลบออกตามคำขอ

---

## 5. แผนที่ - mapPicker.blade.php

### 5.1 เปิดการเลื่อนแผนที่ (Pan)

- **gestureHandling:** `"none"` → `"greedy"`

### 5.2 ปุ่ม Zoom

- ปุ่ม zoom (+/-) ถูกเพิ่มแล้วลบออกตามคำขอ

---

## ไฟล์ที่แก้ไข

| ไฟล์ | การแก้ไข |
|------|----------|
| `app/Http/Controllers/AjaxController.php` | geocode_log, fallback ดึงเขตจาก formatted_address |
| `resources/views/shipBillAddress/addressAdd.blade.php` | Log display สำหรับ debug |
| `resources/lang/de/customer.php` | แก้ road → ถนน |
| `resources/views/shipBillAddress/addressList.blade.php` | gestureHandling: greedy |
| `resources/views/shipBillAddress/mapPicker.blade.php` | gestureHandling: greedy |

---

## หมายเหตุ

- **geocode_log** ใช้สำหรับ debug เท่านั้น สามารถซ่อนหรือลบออกได้เมื่อไม่จำเป็น
- **gestureHandling: "greedy"** ยังคงอยู่ เพื่อให้ผู้ใช้เลื่อนแผนที่ได้ด้วยการคลิกลาก
