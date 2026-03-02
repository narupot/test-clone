@extends('layouts/admin/default')

@section('title')
@lang('admin_shipping.shipping_profile')
@stop

@section('header_styles')

<style>
    :root {
        --primary-color: #0d47a1;
        --primary-light: #e3f2fd;
        --secondary-color: #6c757d;
        --bg-light: #f8f9fa;
        --border-color: #dee2e6;
        --text-dark: #212529;
        --header-bg: #f1f3f5;
        --success-color: #28a745;
        --warning-bg: #fff3cd;
        --warning-border: #ffecb5;
        --warning-text: #856404;
        --box-shadow-sm: 0 .125rem .25rem rgba(0, 0, 0, .075);
        --box-shadow-md: 0 .5rem 1rem rgba(0, 0, 0, .15);
    }



    .radio-group label {
        margin-right: 25px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        font-weight: 500;
    }

    .radio-group input[type="radio"] {
        margin-right: 8px;
        transform: scale(1.1);
        accent-color: var(--primary-color);
    }

    .top-controls {
        display: flex;
        gap: 30px;
        margin-bottom: 30px;
        padding: 20px;
        background-color: var(--bg-light);
        border-radius: 8px;
        border: 1px solid var(--border-color);
        margin-top: 8px;
    }

    .control-group {
        display: flex;
        flex-direction: column;
    }

    .control-group label {
        font-weight: 800;
        margin-bottom: 8px;
        color: var(--secondary-color);
    }

    .form-input-box,
    .form-select-box {
        width: 100%;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        line-height: 1.5;
        border-radius: 8px;

        border: 1px solid var(--border-color);
        background-color: #fff;


        font-family: inherit;
        transition: border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;

        box-sizing: border-box;
    }

    .form-input-box:focus,
    .form-select-box:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 4px rgba(13, 71, 161, 0.15);
    }

    .form-select-box {
        width: 100%;
        padding: 0.45rem 0.6rem;
        font-size: 0.9rem;
        line-height: 1.3;
        border-radius: 4px;
        border: 1px solid var(--border-color);
        background-color: #fff;
        box-sizing: border-box;
        appearance: none;
        background-position: right 0.5rem center;
        background-repeat: no-repeat;
        background-size: 10px;
        padding-right: 1.75rem;
    }

    .main-layout {
        display: grid;
        grid-template-columns: 400px 1fr;
        gap: 25px;
        align-items: stretch;
        /* คำนวณความสูงหน้าจอ ลบส่วน Header เว็บออก (ปรับเลข 180 ตามความเหมาะสมของ Header คุณ) */
        height: calc(100vh - 180px); 
        min-height: 500px;
        margin-bottom: 20px;
    }

    /* --- Left Pane: Tree View --- */
    .geo-pane {
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 20px;
        background-color: #fff;
        overflow-y: auto;
        height: 100%;
        box-shadow: var(--box-shadow-sm);
    }

    /* --- Right Pane: Delivery Rounds --- */
    .delivery-pane {
        display: flex;
        flex-direction: column;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        background-color: #fff;
        height: 100%;
        overflow: hidden;
        box-shadow: var(--box-shadow-sm);
    }

    ul.tree-view,
    ul.tree-view ul {
        list-style-type: none;
        margin: 0;
        padding: 0;
    }

    ul.tree-view ul {
        margin-left: 22px;
        border-left: 2px solid var(--border-color);
        padding-left: 12px;
    }

    ul.tree-view li {
        margin: 6px 0;
        display: flex;
        align-items: center;
        padding: 4px 0;
    }

    .toggle-icon {
        cursor: pointer;
        margin-right: 8px;
        font-weight: 700;
        width: 22px;
        height: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        background-color: var(--bg-light);
        color: var(--primary-color);
        font-size: 0.85em;
        user-select: none;
        transition: background-color 0.2s;
    }

    .toggle-icon:hover {
        background-color: var(--border-color);
    }

    input[type="checkbox"] {
        margin-right: 10px;
        transform: scale(1.1);
        accent-color: var(--primary-color);
        cursor: pointer;
    }

    .tree-label {
        cursor: pointer;
        flex-grow: 1;
    }

    .postal-code {
        margin-left: auto;
        color: var(--secondary-color);
        font-size: 0.85em;
        background-color: var(--bg-light);
        padding: 3px 8px;
        border-radius: 4px;
        font-weight: 500;
        white-space: nowrap;
    }

    .delivery-header-row {
        flex-shrink: 0; /* ไม่ให้ส่วนหัวหดตัว */
        padding: 20px;
        border-bottom: 2px solid var(--bg-light);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .section-title i {
        color: var(--primary-color, #0d47a1);
        margin-right: 10px;
    }

    .btn-add-round {
        background-color: var(--primary-color, #0d47a1);
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 50px;
        cursor: pointer;
        font-weight: 600;
        font-size: 0.9rem;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .btn-add-round:hover {
        background-color: #0a3d8a;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    /* Grid Container ที่ปรับปรุงแล้ว */
    .round-grid-container.improved-grid {
        flex-grow: 1;
        overflow-y: auto; /* เปิดให้ Scroll เฉพาะส่วนของตาราง */
        padding: 20px;
        display: grid;
        grid-template-columns: repeat(4, 1fr) 40px repeat(3, 1fr) 90px;
        gap: 10px;
        align-content: start;
        background-color: var(--bg-light);
    }

    /* --- Sticky Headers (ล็อคหัวตารางไว้ด้านบนเวลาเลื่อน) --- */
    .grid-header.main {
        position: sticky;
        top: 0;
        background-color: var(--bg-light);
        z-index: 10;
        padding: 10px 5px;
        font-weight: bold;
        color: var(--primary-color);
        border-bottom: 2px solid var(--primary-light);
        text-align: center;
    }

    .grid-header.sub {
        position: sticky;
        top: 45px;
        background-color: var(--bg-light);
        z-index: 9;
        padding: 5px;
        font-size: 0.85rem;
        color: var(--secondary-color);
        text-align: center;
        border-bottom: 1px solid var(--border-color);
    }

    /* --- Form Elements Inside Grid --- */
    .input-box-style {
        width: 100%;
        padding: 8px;
        border: 1px solid var(--primary-color);
        background-color: #fff;
        border-radius: 6px;
        text-align: center;
        font-size: 0.95rem;
        transition: all 0.2s;
    }

    .input-box-style:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(13, 71, 161, 0.2);
    }

    .input-box-style.small {
        background-color: var(--primary-light);
        font-weight: 700;
    }

    .plus-separator {
        text-align: center;
        color: #adb5bd;
    }

    .position-relative {
        position: relative;
    }

    .btn-remove-round {
        position: absolute;
        right: -25px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #dc3545;
        cursor: pointer;
        font-size: 1.1rem;
        padding: 5px;
        transition: color 0.2s;
        opacity: 0.7;
    }

    .btn-remove-round:hover {
        color: #a71d2a;
        opacity: 1;
    }

    .round-item-group>div {
        animation: fadeIn 0.3s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-5px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .round-grid-container {
        display: grid;
        grid-template-columns: 1.2fr 1.2fr 1.2fr auto 0.6fr 1.2fr 1.2fr;
        gap: 12px;
        align-items: center;
        background-color: var(--bg-light);
        padding: 25px;
        border-radius: 8px;
        border: 1px solid var(--border-color);
    }

    .grid-header {
        font-weight: 600;
        font-size: 0.9em;
        color: var(--secondary-color);
        text-align: center;
        white-space: nowrap;
        margin-bottom: 5px;
    }

    .header-group-start {
        grid-column: span 3;
    }

    .header-group-end {
        grid-column: span 3;
    }

    .input-box-style {
        width: 100%;
        padding: 10px;
        border: 1px solid var(--primary-color);
        background-color: var(--primary-light);
        border-radius: 4px;
        box-sizing: border-box;
        transition: border-color 0.2s, box-shadow 0.2s;
        text-align: center;
        font-family: inherit;
        font-size: 1rem;
    }

    /* Container ของสวิตช์ */
    .custom-toggle {
        position: relative;
        display: inline-block;
        width: 48px;
        height: 24px;
        margin-bottom: 0;
        vertical-align: middle;
    }

    /* ซ่อน Checkbox จริง */
    .custom-toggle input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    /* ส่วนพื้นหลังของสวิตช์ */
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #e0e0e0;
        transition: .3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    /* ปุ่มวงกลมด้านใน */
    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    /* เมื่อ Checkbox ถูกเลือก (Active) */
    input:checked+.slider {
        background-color: #34c759;
        /* สีเขียว iOS */
    }

    /* ขยับปุ่มวงกลมเมื่อ Active */
    input:checked+.slider:before {
        transform: translateX(24px);
    }

    /* ทำให้ขอบมนสวยงาม */
    .slider.round {
        border-radius: 24px;
    }

    .slider.round:before {
        border-radius: 50%;
    }

    /* เพิ่ม Effect เมื่อเอาเมาส์ไปวาง */
    .custom-toggle:hover .slider {
        filter: brightness(0.95);
    }

    /* ปุ่มลบ */
    .btn-icon-remove {
        background: none;
        border: none;
        color: #dc3545;
        cursor: pointer;
        font-size: 1rem;
    }

    .btn-icon-remove:hover {
        color: #bd2130;
    }

    .input-box-style:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(13, 71, 161, 0.25);
    }

    .input-box-style.small {
        font-weight: 600;
    }

    .plus-separator {
        text-align: center;
        font-weight: 700;
        color: var(--secondary-color);
        font-size: 1.5em;
        line-height: 1;
    }

    /* --- Footer Note --- */
    .footer-note {
        margin-top: 30px;
        font-size: 0.95em;
        color: var(--warning-text);
        background-color: var(--warning-bg);
        border: 1px solid var(--warning-border);
        padding: 15px 20px;
        border-radius: 8px;
        border-left: 5px solid #ffc107;
    }

    .highlight-note {
        font-weight: 700;
        color: var(--success-color);
        background-color: #e6f4ea;
        padding: 2px 6px;
        border-radius: 4px;
        border: 1px solid var(--success-color);
    }

    /* ส่วน header ของ delivery pane (ไม่ให้ scroll) */
    .delivery-header-row {
        flex-shrink: 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .round-grid-container.improved-grid {
        display: grid;
        /* สูตรคำนวณช่อง: 4ช่องแรก | ลูกศร | 3ช่องหลัง | สถานะ */
        grid-template-columns: repeat(4, 1fr) 40px repeat(3, 1fr) 80px;
        gap: 10px;
        align-items: center;
        background-color: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        border: 1px solid #dee2e6;
    }

    .geo-pane::-webkit-scrollbar,
    .round-grid-container::-webkit-scrollbar {
        width: 8px;
    }

    .geo-pane::-webkit-scrollbar-track,
    .round-grid-container::-webkit-scrollbar-track {
        background: #f1f3f5;
        border-radius: 10px;
    }

    .geo-pane::-webkit-scrollbar-thumb,
    .round-grid-container::-webkit-scrollbar-thumb {
        background-color: #c1c9d2;
        border-radius: 10px;
        border: 2px solid #f1f3f5;
    }

    .round-grid-container::-webkit-scrollbar-thumb {
        background-color: var(--primary-color);
    }

    .tree-label small {
        margin-left: 5px;
        font-size: 0.85em;
    }

    .text-danger {
        color: #dc3545 !important;
    }

    .fw-bold {
        font-weight: bold !important;
    }

    .used-info {
        color: #dc3545;
        font-size: 0.85em;
        font-weight: normal;
        margin-left: 10px;
        display: inline-block;
    }

    #btnConfirmRemove i {
        vertical-align: middle;
        margin-top: -2px;
    }

    .form-switch .form-check-input {
        appearance: none;
        -webkit-appearance: none;
        width: 50px;
        height: 26px;
        background-color: #e9ecef;
        border-radius: 50px;
        position: relative;
        cursor: pointer;
        outline: none;
        border: 1px solid #dee2e6;
        transition: background-color 0.3s ease, border-color 0.3s ease;
    }

    .form-switch .form-check-input::after {
        content: '';
        position: absolute;
        top: 2px;
        left: 2px;
        width: 20px;
        height: 20px;
        background-color: #fff;
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        transition: transform 0.3s cubic-bezier(0.68, -0.55, 0.27, 1.55);
    }

    /* สถานะเมื่อถูกติ๊ก (Checked / Active) */
    .form-switch .form-check-input:checked {
        background-color: #28a745;
        border-color: #28a745;
    }

    /* เลื่อนวงกลมไปทางขวาเมื่อติ๊ก */
    .form-switch .form-check-input:checked::after {
        transform: translateX(24px);
    }

    .form-switch .form-check-input:hover {
        background-color: #dee2e6;
    }

    .form-switch .form-check-input:checked:hover {
        background-color: #218838;
    }

    .section-disabled {
        opacity: 0.4;
        pointer-events: none;
        background-color: #f9f9f9;
        user-select: none;
    }

    #delivery-form-view {
        display: none;
    }

    /* Container */
    .switch-container {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    /* ตัวสวิตช์หลัก */
    .modern-switch {
        position: relative;
        display: inline-block;
        width: 52px;
        height: 28px;
        margin-bottom: 0;
        overflow: hidden;
        vertical-align: middle;
    }

    .modern-switch input {
        position: absolute;
        opacity: 0;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        margin: 0;
        padding: 0;
        cursor: pointer;
        z-index: 5;
        /* ให้อยู่บนสุดเพื่อรับ Click */
    }

    /* พื้นหลังสวิตช์ */
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        z-index: 1;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 20px;
        width: 20px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .4s;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    /* ปรับ Selector ให้ใช้เครื่องหมาย ~ เพื่อข้าม hidden input */
    .modern-switch input:checked~.slider {
        background-color: #28a745;
    }

    .modern-switch input:checked~.slider:before {
        transform: translateX(24px);
    }

    /* เมื่อติ๊ก (Active) */
    input:checked+.slider {
        background-color: #28a745;
    }

    input:checked+.slider:before {
        transform: translateX(24px);
    }

    .slider.round {
        border-radius: 34px;
    }

    .slider.round:before {
        border-radius: 50%;
    }

    /* ข้อความข้างสวิตช์ */
    .status-label {
        font-weight: bold;
        font-size: 14px;
        transition: 0.3s;
    }

    /* ซ่อนปุ่มที่มีคลาสเหล่านี้โดยเฉพาะ */
    .ui-button.ui-corner-all.ui-widget {
        display: none !important;
    }

    /* แก้ไขปัญหาหน้าจอพัง: เปลี่ยนจาก flex เป็น block ในระดับรายการหลัก */
    ul.tree-view li {
        display: block !important;
        position: relative;
        padding: 2px 0;
    }

    /* จัดแถวหัวข้อ (จังหวัด/อำเภอ) */
    .tree-row {
        display: flex;
        align-items: center;
        padding: 4px 0;
    }

    /* ส่วนของระดับตำบล (Subdistrict) */
    .subdistrict-row-item {
        padding-left: 35px !important;
        /* เพิ่มจากเดิมเพื่อให้ตำบลขยับไปขวามากขึ้น */
        position: relative;
    }

    ul.tree-view ul.subdistrict-list {
        margin-left: 15px !important;
        /* ขยับเส้นเชื่อมของตำบลให้ตรงกับ Checkbox ของอำเภอ */
        border-left: 1px dashed #ced4da !important;
        /* ใช้เส้นประเพื่อให้ดูแยกสัดส่วนง่ายขึ้น */
    }

    .subdistrict-flex-container {
        display: flex;
        align-items: center;
        position: relative;
        width: 100%;
        min-height: 32px;
        padding: 2px 0;
    }

    /* รหัสไปรษณีย์: ใช้ Absolute เพื่อให้อยู่ขวาสุดเสมอและไม่ดันชื่อตำบล */
    .postal-code {
        position: absolute;
        right: 10px;
        background-color: #f1f3f5;
        color: #495057;
        font-weight: 500;
        font-size: 11px;
        padding: 2px 8px;
        border-radius: 4px;
        border: 1px solid #dee2e6;
    }

    /* เพิ่มเส้นแนวนอนเล็กๆ ชี้ไปที่ชื่อตำบล */
    .subdistrict-row-item::before {
        content: "";
        position: absolute;
        left: 0;
        top: 16px;
        width: 15px;
        border-top: 1px dashed #ced4da;
    }

    /* เส้น Tree Line: ปรับให้ต่อเนื่องสวยงาม */
    ul.tree-view ul {
        margin-left: 10px !important;
        border-left: 1px solid #dee2e6 !important;
        padding-left: 12px !important;
    }

    /* ตกแต่ง Toggle Icon (+/-) */
    .toggle-icon {
        width: 18px;
        height: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background-color: #f1f3f5;
        border: 1px solid #ccc;
        border-radius: 3px;
        margin-right: 8px;
        cursor: pointer;
        font-size: 12px;
    }

    /* Highlight แถวที่เลือก */
    .bg-light-yellow {
        background-color: #fffde7 !important;
        border-radius: 4px;
    }

    .partial-label {
        color: #dc3545 !important;
        font-size: 0.85em;
        font-weight: normal;
        margin-left: 5px;
    }

    /* ปรับแต่ง Label ให้ตัวหนาและมีระยะห่าง */
    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #333;
        font-size: 0.95rem;
    }

    /* ปรับแต่ง Textarea ให้ดู Modern */
    .modern-textarea {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #e2e8f0;
        /* สีขอบเทาอ่อน */
        border-radius: 8px;
        /* ความโค้งมน */
        background-color: #f8fafc;
        /* พื้นหลังเทาจางๆ */
        font-size: 0.95rem;
        color: #475569;
        transition: all 0.3s ease;
        /* เอฟเฟกต์นุ่มนวลเวลาคลิก */
        resize: vertical;
        /* ให้ยืดขยายแนวตั้งได้อย่างเดียว */
        min-height: 80px;
    }

    /* เมื่อเอาเมาส์ไปชี้ หรือ คลิกพิมพ์ */
    .modern-textarea:hover {
        border-color: #cbd5e1;
    }

    .modern-textarea:focus {
        outline: none;
        border-color: #3b82f6;
        /* สีฟ้าเมื่อคลิกพิมพ์ (เปลี่ยนตามธีมเว็บได้) */
        background-color: #ffffff;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        /* เงาฟุ้งๆ สีฟ้า */
    }

    /* ข้อความคำแนะนำตัวเล็กด้านล่าง */
    .helper-text {
        display: block;
        margin-top: 5px;
        font-size: 0.85rem;
        color: #94a3b8;
    }

    /* บีบขนาดวงกลมของไอคอน */
/* แก้ไขไอคอน SweetAlert2 ที่ขยายใหญ่เกินไป */
.swal2-container .swal2-icon {
    width: 80px !important;  /* ปรับความกว้างตามชอบ */
    height: 80px !important; /* ปรับความสูงตามชอบ */
    margin: 20px auto !important;
}

/* ปรับขนาดเครื่องหมายด้านในไอคอน (เครื่องหมาย !) */
.swal2-icon .swal2-icon-content {
    font-size: 60px !important;
    line-height: 80px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}
</style>
{!!CustomHelpers::dataTableCss()!!}
<link rel="stylesheet" type="text/css" href="{{Config('constants.css_url') }}ui-grid-unstable.css"> 

    <!--page level css -->
    <script>
      
      var fieldSetJson  = {!! $fielddata !!};
      var fieldset = fieldSetJson.fieldSets;
      var pagelimit = "{{action('JsonController@pageLimit')}}";
      var showSearchSection = true;
      var showHeadrePagination = true;
      var getAllDataFromServerOnce = true;
      var dataJsonUrl = "{{ action('Admin\ShippingProfile\ShippingRateTableController@listShippingRatesData',['shipping_profile_id'=>$shippingRateData->id]) }}";
      var lang = ["@lang('admin_shipping.country')","@lang('admin_shipping.state')","@lang('admin_shipping.district')","@lang('admin_shipping.sub_district')","@lang('admin_shipping.zip_from')","@lang('admin_shipping.zip_to')","@lang('admin_shipping.weight_from')","@lang('admin_shipping.weight_to')","@lang('admin_shipping.qty_from')","@lang('admin_shipping.qty_to')","@lang('admin_shipping.product_type')","@lang('admin_shipping.price_from')","@lang('admin_shipping.price_to')","@lang('admin_shipping.base_rate_for_order')","@lang('admin_shipping.ppp')","@lang('admin_shipping.frpp')","@lang('admin_shipping.frpuw')","@lang('admin_shipping.action')"];
      var tableLoaderImgUrl = "{{ Config::get('constants.loader_url')}}ajax-loader.gif";
      //pagination config 
      var pagination = {!! getPagination() !!};
      var per_page_limt = {{ getPagination('limit') }};
      //find index cahnge using method
      function findIndexMethod(list, matchEle){
        var index = -1;
        for (var i = 0; i < list.length; ++i) {
          if (list[i].fieldName!== undefined && list[i].fieldName===matchEle) {
              index = i;
              break;
          }
        }

        return index;  
      };
      //Listen on table columns setting
     _getInfo=(fName,fType)=>{
       var ind = findIndexMethod(fieldset, fName);
       if(ind>=0){
            var r =false;
            if(fType==='sortable'){
              r= (typeof fieldset[ind].sortable!=='undefined')? fieldset[ind].sortable:false;
            }else if(fType==='width'){
              r= (typeof fieldset[ind].width!=='undefined')? fieldset[ind].width:100;
            }else if(fType==='align'){
               r= (typeof fieldset[ind].align!=='undefined')? 'text-'+fieldset[ind].align:'text-left';
            }
            return r;
       }else{
          if(fType==='width'){
            return 100;
          }else if(fType==='align'){
            return 'text-left';
          }else if(fType==='sortable'){
            return false;
          } 
       }
       return false;
      };
      /**** This code used for columns setting of table where field is field name of database filed.*****/
      var columsSetting = [
        {  
          field : 'Action',
          displayName : 'Action',
          cellTemplate: '<a href="<%row.entity.edit_url%>" class="primary-color">@lang('admin_shipping.edit')</a> | <a href="<%row.entity.delete_url%>" class="primary-color">@lang('admin_shipping.delete')</a> ',
          minWidth: _getInfo('Action','width'),
          cellClass:_getInfo('action','align'),
          enableSorting : false,
        },
        {
          field : 'id',
          displayName : '@lang('admin_shipping.sno')',
          cellTemplate : '<span><%grid.appScope.seqNumber(row)+1%></span>',
          enableSorting : _getInfo('sno','sortable'),
          width : _getInfo('sno','width'),
          cellClass : _getInfo('sno','align'),
        },        
        {
          field : 'priority',
          displayName : '@lang('admin_shipping.priority')',
          enableSorting : false,
          width : _getInfo('priority','width'),
          cellClass : _getInfo('priority','align'),
        },

        
        { 
          field : 'country_id',
          displayName : '@lang('admin_shipping.country')',
          enableSorting : _getInfo('country_id','sortable'),
          width : _getInfo('country_id','width'),
          cellClass : _getInfo('country_id','align'),
        },
        
        { 
          field : 'province_state_id',
          displayName : '@lang('admin_shipping.state')',
          enableSorting : _getInfo('province_state_id','sortable'),
          width : _getInfo('province_state_id','width'),
          cellClass : _getInfo('province_state_id','align'),
        },
        
        {  
          field : 'district_city_id',
          displayName : '@lang('admin_shipping.district')',
          enableSorting : _getInfo('district_city_id','sortable'),
          width : _getInfo('district_city_id','width'),
          cellClass:_getInfo('district_city_id','align'),
        },
        {  
          field : 'sub_district_id',
          displayName : '@lang('admin_shipping.sub_district')',
          enableSorting : _getInfo('sub_district_id','sortable'),
          width : _getInfo('sub_district_id','width'),
          cellClass:_getInfo('sub_district_id','align'),
        },
        {  
          field : 'zip_from',
          displayName : '@lang('admin_shipping.zip_from')',
          enableSorting : _getInfo('zip_from','sortable'),
          width : _getInfo('zip_from','width'),
          cellClass:_getInfo('zip_from','align'),
        },
        {  
          field : 'zip_to',
          displayName : '@lang('admin_shipping.zip_to')',
          enableSorting : _getInfo('zip_to','sortable'),
          width : _getInfo('zip_to','width'),
          cellClass:_getInfo('zip_to','align'),
        },
        {  
          field : 'weight_from',
          displayName : '@lang('admin_shipping.weight_from')',
          enableSorting : _getInfo('weight_from','sortable'),
          width : _getInfo('weight_from','width'),
          cellClass:_getInfo('weight_from','align'),
        },
        {  
          field : 'weight_to',
          displayName : '@lang('admin_shipping.weight_to')',
          enableSorting : _getInfo('weight_to','sortable'),
          width : _getInfo('weight_to','width'),
          cellClass:_getInfo('weight_to','align'),
        },
        {  
          field : 'qty_from',
          displayName : '@lang('admin_shipping.qty_from')',
          enableSorting : _getInfo('qty_from','sortable'),
          width : _getInfo('qty_from','width'),
          cellClass:_getInfo('qty_from','align'),
        },
        {  
          field : 'qty_to',
          displayName : '@lang('admin_shipping.qty_to')',
          enableSorting : _getInfo('qty_to','sortable'),
          width : _getInfo('qty_to','width'),
          cellClass:_getInfo('qty_to','align'),
        },
        {  
          field : 'price_from',
          displayName : '@lang('admin_shipping.price_from')',
          enableSorting : _getInfo('price_from','sortable'),
          width : _getInfo('price_from','width'),
          cellClass:_getInfo('price_from','align'),
        },
        {  
          field : 'price_to',
          displayName : '@lang('admin_shipping.price_to')',
          enableSorting : _getInfo('price_to','sortable'),
          width : _getInfo('price_to','width'),
          cellClass:_getInfo('price_to','align'),
        },
        {  
          field : 'product_type_id',
          displayName : '@lang('admin_shipping.product_type_id')',
          enableSorting : _getInfo('product_type_id','sortable'),
          width : _getInfo('product_type_id','width'),
          cellClass:_getInfo('product_type_id','align'),
        },
        {  
          field : 'base_rate_for_order',
          displayName : '@lang('admin_shipping.base_rate_for_order')',
          enableSorting : _getInfo('base_rate_for_order','sortable'),
          width : _getInfo('base_rate_for_order','width'),
          cellClass:_getInfo('base_rate_for_order','align'),
        },
        {  
          field : 'percentage_rate_per_product',
          displayName : '@lang('admin_shipping.ppp')',
          enableSorting : _getInfo('percentage_rate_per_product','sortable'),
          width : _getInfo('percentage_rate_per_product','width'),
          cellClass:_getInfo('percentage_rate_per_product','align'),
        },
        {  
          field : 'fixed_rate_per_product',
          displayName : '@lang('admin_shipping.frpp')',
          enableSorting : _getInfo('fixed_rate_per_product','sortable'),
          width : _getInfo('fixed_rate_per_product','width'),
          cellClass:_getInfo('fixed_rate_per_product','align'),
        },
        {  
          field : 'fixed_rate_per_unit_weight',
          displayName : '@lang('admin_shipping.frpuw')',
          enableSorting : _getInfo('fixed_rate_per_unit_weight','sortable'),
          width : _getInfo('fixed_rate_per_unit_weight','width'),
          cellClass:_getInfo('fixed_rate_per_unit_weight','align'),
        },
        {  
          field : 'logistic_base_rate_for_order',
          displayName : '@lang('admin_shipping.logistic_base_rate_for_order')',
          enableSorting : _getInfo('logistic_base_rate_for_order','sortable'),
          width : _getInfo('logistic_base_rate_for_order','width'),
          cellClass:_getInfo('logistic_base_rate_for_order','align'),
        },
        {  
          field : 'logistic_percentage_rate_per_product',
          displayName : '@lang('admin_shipping.logistic_ppp')',
          enableSorting : _getInfo('logistic_percentage_rate_per_product','sortable'),
          width : _getInfo('logistic_percentage_rate_per_product','width'),
          cellClass:_getInfo('logistic_percentage_rate_per_product','align'),
        },
        {  
          field : 'logistic_fixed_rate_per_product',
          displayName : '@lang('admin_shipping.logistic_frpp')',
          enableSorting : _getInfo('logistic_fixed_rate_per_product','sortable'),
          width : _getInfo('logistic_fixed_rate_per_product','width'),
          cellClass:_getInfo('logistic_fixed_rate_per_product','align'),
        },
        {  
          field : 'logistic_fixed_rate_per_unit_weight',
          displayName : '@lang('admin_shipping.logistic_frpuw')',
          enableSorting : _getInfo('logistic_fixed_rate_per_unit_weight','sortable'),
          width : _getInfo('logistic_fixed_rate_per_unit_weight','width'),
          cellClass:_getInfo('logistic_fixed_rate_per_unit_weight','align'),
        }


        
      ];
  </script>

    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}dataTables.bootstrap.css"/>

<!-- end of page level css -->

@stop

@section('content')
<div class="content">
    <form action="{{action('Admin\ShippingProfile\ShippingRateTableController@saveShippingRateProfile')}}" method="post" name="update" enctype="multipart/form-data">
        <div class="header-title">
            <h1 class="title">{{$shippingRateData->getShippingProfileDesc->name}}</h1>
            <div class="float-right">
                <button name="submit_type" id="btn_save" value="save" class="btn btn-primary save_buttons btn-loading" type="button">
                    @lang('admin_common.save')
                </button>
            </div>
        </div>
        <div class="content-wrap clearfix">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('config')!!}
                </ul>
            </div>
            <div class="content-left">
                <div class="tablist">
                    <ul class="nav nav-tabs">
                        <li class="nav-item"><a class="nav-link show active" @if(!empty($session_data) || !empty($session_rates)) class="" @else class="active" @endif id="general_tab" data-toggle="tab" data-target="#general">@lang('admin_shipping.general')</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" id="import_tab" data-target="#import" @if(!empty($session_data)) class="active" @endif>@lang('admin_shipping.import')</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" id="methods_and_rates_tab" data-target="#methods_and_rates" @if(!empty($session_rates)) class="active" @endif>@lang('admin_shipping.methods_and_rates')</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" id="delivery_time_tab" data-target="#delivery_time" @if(!empty($session_rates)) class="active" @endif>@lang('admin_shipping.delivery_time')</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" id="log_tab" data-target="#shiplog" @if(!empty($session_rates)) class="active" @endif>@lang('admin_shipping.log')</a></li>
                    </ul>
                </div>
            </div>
            <div class="content-right">
                {{ csrf_field() }}
                <div class="tab-content">
                    <input type="hidden" name="shipping_profile_id" value="{{$shippingRateData->id}}">
                    <div id="general" class="tab-pane fade @if(!empty($session_data) || !empty($session_rates)) @else show active @endif ">
                        <div>
                            <h2 class="title-prod">@lang('admin_shipping.general')</h2>
                            <!-- //////// Start ///// -->
                            <div class="row">
                                <div></div>
                                <div class="form-group col-sm-12" id="shipping-rate-table">
                                    <div class="condition-rulebox">
                                        <div class="form-group row shipping-rate-table-field">
                                            <div class="col-sm-4">
                                                <label>@lang('admin_shipping.name')</label>
                                                <input type="text" name="name" value="{{$shippingRateData->getShippingProfileDesc->name}}">
                                            </div>
                                        </div>

                                        <div class="form-group row shipping-rate-table-field">
                                            <div class="col-sm-4">
                                                <label>@lang('admin_shipping.status')</label>
                                                {!! Form::select('status', ['1'=>Lang::get('admin_shipping.active'),'0'=>Lang::get('admin_shipping.deactive')], $shippingRateData->status,[ 'class'=>'custom-select']) !!}
                                            </div>
                                        </div>
                                        <div class="form-group row shipping-rate-table-field">
                                            <div class="col-sm-4">
                                                <label>@lang('admin_shipping.comment')</label>
                                                <textarea name="comment">{{$shippingRateData->comment}}</textarea>
                                            </div>
                                        </div>
                                        <div class="row form-group shipping-rate-table-field">
                                            <div class="col-sm-4">
                                                <label>@lang('admin_shipping.minimal_rate')</label>
                                                <input type="text" value="{{$shippingRateData->minimal_rate}}" name="minimal_rate">
                                                @if ($errors->has('minimal_rate'))
                                                <p id="minimal-rate-error" class="error error-msg">{{ $errors->first('minimal_rate') }}</p>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="row form-group shipping-rate-table-field">
                                            <div class="col-sm-4">
                                                <label>@lang('admin_shipping.maximal_rate')</label>
                                                <input type="text" value="{{$shippingRateData->maximal_rate}}" name="maximal_rate">
                                                @if ($errors->has('maximal_rate'))
                                                <p id="maximal-rate-error" class="error error-msg">{{ $errors->first('maximal_rate') }}</p>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="row form-group shipping-rate-table-field">
                                            <div class="col-sm-4">
                                                <label>@lang('admin_shipping.shipping_calculation_type')</label>
                                                {!! Form::select('shipping_calculation_type', ['0'=>Lang::get('admin_shipping.sum_up_rate'),'1'=>Lang::get('admin_shipping.select_minimal_rate'),'2'=>Lang::get('admin_shipping.select_maximal_rate')], $shippingRateData->shipping_calculation_type,[ 'class'=>'custom-select']) !!}
                                            </div>
                                        </div>
                                        <div class="row shipping-rate-table-field">
                                            <div class="col-sm-9">
                                                <img src="{{$shippingRateData->logo}}" />
                                            </div>
                                            <div class="col-sm-9">
                                                <label>@lang('admin_shipping.profile_logo')<i class="strick">*</i></label>
                                                <div class="mb-2">
                                                    <div class="form-group">
                                                        <input type="file" name="shipping_logo" accept=".png, .jpg, .jpeg">
                                                        @if($errors->has('shipping_logo'))
                                                        <p class="error error-msg">{{ $errors->first('shipping_logo') }}</p>
                                                        @endif
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="attr-variant-view">
                                        <!-- ////// Start ///// -->
                                        <div class="row">
                                            <div class="form-group col-sm-12" id="shipping-rate-table">
                                                <div class="condition-rulebox">

                                                    <div class="row form-group shipping-rate-table-field">
                                                        <div class="col-sm-4">
                                                            <label>@lang('admin_shipping.customer_group')</label>
                                                            <select class="multiple-selectw" name="customer_group[]" multiple="multiple" class="multiple-selectw">
                                                                <option value="">--- Select---</option>
                                                                @foreach($custGroup as $cus_key => $cust)

                                                                <option value="{{$cust['id']}}"

                                                                    <?php
                                                                    $custGArray = explode(',', $shippingRateData->customer_group);

                                                                    if (in_array($cust['id'], $custGArray)) {
                                                                        echo "selected";
                                                                    }
                                                                    ?>>{{$cust['group_name']}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="row form-group shipping-rate-table-field">
                                                        <label class="col-sm-12 check-wrap mb-2">
                                                            <input type="checkbox" name="use_dimension_weight" @if($shippingRateData->use_dimension_weight=='1') checked="checked" @endif id="chkb_dimension_weight" val="1">
                                                            <span class="chk-label">@lang('admin_shipping.use_dimension_weight')</span>
                                                        </label>
                                                        <div id="dimension_weight_container" class="">
                                                            <div class="col-sm-12" id="dimension_weight_content">
                                                                <label>@lang('admin_shipping.factor')</label>
                                                                <input type="number" name="dimension_factor" value="{{$shippingRateData->dimension_factor}}">
                                                            </div>
                                                        </div>
                                                    </div>


                                                </div>

                                            </div>
                                        </div>
                                        <!-- ////// End  ////// -->

                                    </div>
                                </div>
                            </div>
                            <!-- ////// End ///// -->
                        </div>
                    </div>


                    <div id="import" class="tab-pane fade @if(!empty($session_data)) active show @endif ">
                        <div class="">
                            <h2 class="title-prod">@lang('admin_shipping.import_csv_rate')</h2>
                            <!-- ///// Start  -->
                            <div class="row">
                                <div class="form-group col-sm-12" id="shipping-rate-table">
                                    <div class="condition-rulebox">
                                        <div class="row form-group shipping-rate-table-field">
                                            <div class="col-sm-4">
                                                <label>@lang('admin_shipping.delete_existing')</label>
                                                {!! Form::select('delete_existing', ['no'=>Lang::get('admin_common.no'),'yes'=>Lang::get('admin_common.yes')], null,[ 'class'=>'custom-select']) !!}
                                            </div>
                                        </div>
                                        <div id="import_local" class="form-group row">
                                            <div class="col-sm-4">
                                                <label>@lang('admin_shipping.select_file')</label>
                                                <input type="file" name="csv_rates" id="csv_rates">
                                            </div>
                                        </div>
                                        <div class="form-group row shipping-rate-table-field">
                                            <div class="col-sm-4">
                                                <input type="submit" class="btn btn-primary import_csv" name="submit_type" value="Import">
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <div class="table table-content col-sm-12">

                                    @if(!empty($session_data))

                                    <div class="custom-paddleft">
                                        <h3 class="title">@lang('admin_shipping.import_csv_response')</h3>

                                        @foreach($session_data as $res_key => $row)

                                        <h4>{{$res_key}}</h4>
                                        <div>
                                            @if(!empty($row))
                                            <div class="onlytableScroll">
                                                <table style="overflow-x: auto !important;">
                                                    <thead>
                                                        <tr>
                                                            <th>@lang('admin_shipping.s_no')</th>
                                                            <th>@lang('admin_shipping.priority')</th>
                                                            <th>@lang('admin_shipping.country')</th>
                                                            <th>@lang('admin_shipping.state')</th>
                                                            <th>@lang('admin_shipping.district')</th>
                                                            <th>@lang('admin_shipping.sub_district')</th>
                                                            <th>@lang('admin_shipping.zip_from')</th>
                                                            <th>@lang('admin_shipping.zip_to')</th>
                                                            <th>@lang('admin_shipping.weight_from')</th>
                                                            <th>@lang('admin_shipping.weight_to')</th>
                                                            <th>@lang('admin_shipping.qty_from')</th>
                                                            <th>@lang('admin_shipping.qty_to')</th>
                                                            <th>@lang('admin_shipping.price_from')</th>
                                                            <th>@lang('admin_shipping.price_to')</th>
                                                            <th>@lang('admin_shipping.product_type')</th>
                                                            <th>@lang('admin_shipping.base_rate')</th>
                                                            <th>@lang('admin_shipping.ppp')</th>
                                                            <th>@lang('admin_shipping.frpp')</th>
                                                            <th>@lang('admin_shipping.frpuw')</th>
                                                            <th>@lang('admin_shipping.estimate_shipping')</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($row as $k => $data)
                                                        <tr>
                                                            <td>{{$k+1}}</td>
                                                            <td>{{$data['priority']}}</td>
                                                            <td>{{$data['country_id']}}</td>
                                                            <td>{{$data['province_state']}}</td>
                                                            <td>{{$data['district_city']}}</td>
                                                            <td>{{$data['sub_district']}}</td>
                                                            <td>{{$data['zip_from']}}</td>
                                                            <td>{{$data['zip_to']}}</td>
                                                            <td>{{$data['weight_from']}}</td>
                                                            <td>{{$data['weight_to']}}</td>
                                                            <td>{{$data['qty_from']}}</td>
                                                            <td>{{$data['qty_to']}}</td>
                                                            <td>{{$data['price_from']}}</td>
                                                            <td>{{$data['price_to']}}</td>
                                                            <td>{{$data['product_type_id']}}</td>
                                                            <td>{{$data['base_rate_for_order']}}</td>
                                                            <td>{{$data['percentage_rate_per_product']}}</td>
                                                            <td>{{$data['fixed_rate_per_product']}}</td>
                                                            <td>{{$data['fixed_rate_per_unit_weight']}}</td>
                                                            <td>{{$data['estimate_shipping']}}</td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            @else
                                            <div>@lang('admin_shipping.no_data_found')</div>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <!-- ///// End //// -->
                        </div>
                    </div>

                    <div id="methods_and_rates" class="tab-pane fade @if(!empty($session_rates)) show active @endif">
                        <h2 class="title-prod">@lang('admin_shipping.methods_and_rate')</h2>
                        <div class="form-group">
                            <a class="btn-outline-primary ecport_rates mr-1" href="{{action('Admin\ShippingProfile\ShippingRateTableController@export_rates')}}"> @lang('admin_shipping.export_csv')</a>
                            <!-- <a class="btn-primary ecport_rates" href="{{action('Admin\ShippingProfile\ShippingRateTableController@addNewTableRate')}}"> @lang('admin_shipping.add_new_rate')</a>
                              <a class="btn-primary ecport_rates" href="{{action('Admin\ShippingProfile\ShippingRateTableController@addWizardRate')}}"> @lang('admin_shipping.add_wizard_rate')</a> -->
                        </div>
                        <div class="table-wrapper">
                            <div id="jq_grid_table" class="table table-bordered"></div>

                        </div>
                    </div>

                    <div id="delivery_time" class="tab-pane fade @if(!empty($session_rates)) show active @endif">
                        <h2 class="title-prod">@lang('admin_shipping.delivery_time')</h2>

                        <div id="delivery-list-view">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="m-0"> รายการเขตการขาย</h4>
                                <button type="button" class="btn btn-primary" id="btn-show-add-form">
                                    เพิ่มเขตการขายใหม่
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="bg-light">
                                        <tr>
                                            <th width="15%">ชื่อเขตการขาย</th>
                                            <th width="10%" class="text-center">ประเภทเขต</th>
                                            <th width="40%">พื้นที่ให้บริการ</th>
                                            <th width="5%" class="text-center">จำนวนรอบ</th>
                                            <th width="10%" class="text-center">สถานะ</th>
                                            <th width="5%" class="text-center">ผู้ทำรายการ</th>
                                            <th width="15%" class="text-center">จัดการ</th>
                                        </tr>
                                    </thead>
                                    <tbody id="region-table-body">
                                        @forelse($allRegions ?? [] as $reg)
                                        <tr>
                                            <td>{{ $reg->reg_name }}</td>
                                            <td class="text-center">
                                                @if($reg->reg_type == 2)
                                                <span class="badge badge-info">
                                                    <i class="fas fa-store"></i> มารับที่ศูนย์
                                                </span>
                                                @elseif($reg->reg_type == 3)
                                                <span class="badge badge-primary">
                                                    <i class="fas fa-truck"></i> จัดส่งตามที่อยู่
                                                </span>
                                                @else
                                                <span class="badge badge-secondary">ไม่ระบุ</span>
                                                @endif
                                            </td>
                                            <td><small>{{ $reg->area_summary ?? 'ไม่ได้ระบุพื้นที่' }}</small></td>
                                            <td class="text-center">{{ $reg->slots_count }} รอบ</td>
                                            <td class="text-center">
                                                {!! $reg->status == 1 ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>' !!}
                                            </td>
                                             <td class="text-center">{{ $reg->creator_nickname }}</td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-warning btn-edit" data-id="{{ $reg->reg_id }}">แก้ไข</button>
                                                <button type="button" class="btn btn-sm btn-danger btn-delete" data-id="{{ $reg->reg_id }}">ลบ</button>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center">ไม่พบข้อมูลเขตการขาย</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div id="delivery-form-view">
                            
                            <div class="top-header mb-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="radio-group-container">
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="typeAddress" name="deliveryType" value="address" class="custom-control-input" checked>
                                            <label class="custom-control-label font-weight-bold" for="typeAddress">ส่งที่อยู่ลูกค้า</label>
                                        </div>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="typePickup" name="deliveryType" value="pickup" class="custom-control-input">
                                            <label class="custom-control-label font-weight-bold" for="typePickup">รับที่จุดรับสินค้า</label>
                                        </div>
                                    </div>

                                    <button type="button" id="btn-back-to-list" class="btn btn-back-custom">
                                        <i class="fas fa-arrow-left mr-2" style="padding-top: 5px;"></i> กลับไปหน้ารายการ
                                    </button>
                                </div>
                            </div>

                            <div class="top-controls mb-4">
                                <div class="row">

                                    <div class="col-md-7 mb-3">
                                        <div class="form-group">
                                            <label for="region_name" class="font-weight-bold">ชื่อเขตการขาย <span class="required text-danger">*</span></label>
                                            <input type="text" name="region_name" id="region_name" class="form-control form-input-box" placeholder="เช่น เขตกรุงเทพและปริมณฑล">
                                            <input type="hidden" name="reg_id" id="reg_id">
                                        </div>
                                    </div>

                                    <div class="col-md-5 mb-3">
                                        <label class="d-block mb-2 font-weight-bold">Status</label>
                                        <div class="d-flex align-items-center">
                                            <label class="modern-switch mr-2 mb-0">
                                                <input type="hidden" name="Status" value="0">
                                                <input type="checkbox" id="StatusToggle" name="Status" value="1">
                                                <span class="slider round"></span>
                                            </label>
                                            <span id="StatusText" class="status-label text-success font-weight-bold">Active</span>
                                        </div>
                                    </div>

                                </div>

                                <div class="row">

                                    <div class="col-md-8 mb-3" id="pickup-location-group" style="display: none;">
                                        <div class="form-group">
                                            <label for="pickup_address" class="font-weight-bold">ที่อยู่ <span class="required text-danger">*</span></label>
                                            <textarea name="pickup_address" id="pickup_address" class="form-control form-input-box" rows="3" placeholder="ระบุชื่ออาคาร, ชั้น, หรือจุดสังเกตสำหรับรับสินค้า..."></textarea>
                                        </div>
                                    </div>

                                    <div class="col-md-4 mb-3" id="pickup-phone-group" style="display: none;">
                                        <div class="form-group">
                                            <label for="pickup_phone" class="font-weight-bold">เบอร์โทร <span class="required text-danger">*</span></label>
                                            <input type="text" name="pickup_phone" id="pickup_phone" class="form-control form-input-box" placeholder="09-999-999-99">
                                            <input type="hidden" name="selected_subdistricts" id="selected_subdistricts">
                                        </div>
                                    </div>

                                </div>

                                <div class="row">
                                    <div class="col-12 text-right mt-2" id="selected-area-summary">
                                    </div>
                                </div>
                            </div>

                            <div class="main-layout">
                                <div class="geo-pane">
                                    <div class="geo-header">เลือกพื้นที่ให้บริการ (จังหวัด/อำเภอ/ตำบล)</div>
                                    <ul class="tree-view" id="geography-list-container">
                                    </ul>
                                    <div id="area-data-container"></div>
                                </div>

                                <div class="delivery-pane">
                                    <div class="delivery-header-row">
                                        <h3 class="section-title"><i class="fas fa-clock"></i> รอบการจัดส่ง</h3>
                                        <button type="button" class="btn-add-round" id="btn-add-row-slot"> เพิ่มรอบ</button>
                                    </div>

                                    <div class="round-grid-container improved-grid" id="slot-container">
                                        <div class="grid-header main" style="grid-column: 1 / 3;">ตัดรอบ (Cut-off)</div>
                                        <div class="grid-header main" style="grid-column: 3 / 5;">ผู้ขายต้องส่งของมาคลัง</div>
                                        <div></div>
                                        <div class="grid-header main" style="grid-column: 6 / 9;">ขนส่งถึงลูกค้า</div>
                                        <div></div>

                                        <div class="grid-header sub">เวลา</div>
                                        <div class="grid-header sub">เริ่มพิมพ์ (+ชม. Cut-off)</div>
                                        <div class="grid-header sub">เริ่ม</div>
                                        <div class="grid-header sub">จบ</div>
                                        <div></div>
                                        <div class="grid-header sub">+วัน</div>
                                        <div class="grid-header sub">เวลาเริ่ม</div>
                                        <div class="grid-header sub">เวลาจบ</div>
                                        <div class="grid-header sub">สถานะ</div>

                                        <div class="round-item-group slot-row" style="display: contents;">
                                            <input type="hidden" name="slot_id[]" value="">
                                            <div><input type="time" name="cutoff_time[]" class="input-box-style"></div>
                                            <div><input type="number" name="print_active_hour[]" class="input-box-style small text-center" value="1" min="0" max="120" oninput="if(value > 120) value = 120;" required></div>
                                            <div><input type="time" name="seller_start[]" class="input-box-style"></div>
                                            <div><input type="time" name="seller_end[]" class="input-box-style"></div>
                                            <div class="plus-separator"><i class="fas fa-arrow-right"></i></div>
                                            <div><input type="number" name="delivery_day[]" class="input-box-style small text-center" value="0"></div>
                                            <div><input type="time" name="delivery_start[]" class="input-box-style"></div>
                                            <div><input type="time" name="delivery_end[]" class="input-box-style"></div>
                                            <div class="d-flex align-items-center justify-content-center" style="gap: 8px;">
                                                <label class="modern-switch" style="flex-shrink: 0;">
                                                    <input type="checkbox" class="toggle-control" checked>
                                                    <input type="hidden" name="is_active[]" class="status-value" value="1">
                                                    <span class="slider round"></span>
                                                </label>
                                                <button type="button" class="btn-icon-remove remove-row-btn" style="flex-shrink: 0; padding: 0; border: none; background: none;">
                                                    <i class="fas fa-trash-alt text-danger"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="shiplog" class="tab-pane fade @if(!empty($session_rates)) show active @endif">

                        <h2 class="title-prod">@lang('admin_shipping.log')</h2>

                        <table class="table table-bordered" id="table">
                            <thead>
                                <tr class="filters">
                                    <th>@lang('admin_common.slno')</th>
                                    <th>@lang('admin_common.activity')</th>
                                    <th>@lang('admin_shipping.change_from')</th>
                                    <th>@lang('admin_shipping.change_to')</th>
                                    <th>@lang('admin_common.updated_by')</th>
                                    <th>@lang('admin_common.updated_at')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                $i = 0;
                                foreach($log_list as $log_key=>$log_detail) {

                                $update_detail = json_decode($log_detail->update_detail);
                                if($update_detail){
                                foreach($update_detail as $key=>$value) {

                                $value_arr = explode('=>', $value);

                                @endphp
                                <tr>
                                    <td>{{ ++$i }}</td>
                                    <td>{{ ucwords(str_replace('_', ' ', $key)) }}</td>
                                    <td>{{ $value_arr['0'] }}</td>
                                    <td>{{ $value_arr['1'] }}</td>
                                    <td>{{ $log_detail->updated_by }}</td>
                                    <td>{{ getDateFormat($log_detail->updated_at,9) }}</td>
                                </tr>
                                @php
                                }
                                }

                                }
                                @endphp
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
</div>
</form>
<div id="delete_record" class="modal fade" role="dialog">
    <form id="delete_record_frm" method="get" action="">
        <div class="modal-dialog">
            {{ csrf_field() }}
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">@lang('admin_common.confirm')</h4>
                </div>
                <div class="modal-body">
                    <p>@lang('admin_common.do_you_realy_want_to_delete_this_record')</p>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn-danger">@lang('admin_common.yes')</button>
                    <button type="button" class="btn-default" data-dismiss="modal">@lang('admin_common.no')</button>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="modal fade" id="removeSubdistrictModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header class-danger">
                <h5 class="modal-title text-danger">
                    <i class="fas fa-exclamation-triangle"></i> ยืนยันการลบการใช้งาน
                </h5>
                <button type="button" class="btn-close" data-dismiss="modal">&times;</button>

            </div>
            <div class="modal-body">
                <p>คุณต้องการลบตำบล <strong id="modalSubdistrictName" class="text-primary"></strong> ออกจากเขตการขายเหล่านี้ใช่หรือไม่?</p>
                <div class="alert alert-warning">
                    <strong id="modalRegionNames"></strong>
                </div>
                <p class="small text-muted mb-0">เมื่อลบแล้ว ตำบลนี้จะสามารถนำมาผูกกับเขตการขายปัจจุบันได้</p>
                <input type="hidden" id="modalSubdistrictIdToDelete">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-danger d-inline-flex align-items-center" id="btnConfirmRemove">
                    <!-- <i class="fas fa-trash-alt me-2"> </i> -->
                    ยืนยันการลบ
                </button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="delete_record" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="fas fa-exclamation-triangle"></i> ยืนยันการลบ
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                คุณแน่ใจหรือไม่ที่จะลบรายการนี้? <br>
                <small class="text-muted">การกระทำนี้ไม่สามารถย้อนกลับได้</small>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>

                <form id="delete_record_frm" method="POST" action="">
                    <button type="submit" class="btn btn-danger">ยืนยันลบ</button>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="duplicateZoneModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">

            <div class="modal-header border-0 flex-column align-items-center justify-content-center pt-4" style="background: #fff8e1;">
                <div class="text-warning mb-2">
                    <i class="fas fa-exclamation-triangle fa-3x"></i>
                </div>
                <h4 class="modal-title font-weight-bold text-dark text-center">พบข้อมูลซ้ำซ้อน</h4>
                <p class="text-muted text-center mb-0" style="font-size: 0.9rem;">
                    รายการตำบลด้านล่างถูกใช้งานในเขตการขายอื่นแล้ว
                </p>
            </div>

            <div class="modal-body px-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-secondary font-weight-bold" style="font-size: 0.85rem;">รายการที่พบซ้ำ</span>
                    <span class="badge badge-warning text-white" id="duplicate-count-badge">โปรดตรวจสอบ</span>
                </div>

                <div class="card border-0 bg-light">
                    <div class="card-body p-2" style="max-height: 250px; overflow-y: auto;">
                        <table class="table table-hover table-borderless mb-0">
                            <thead class="text-muted" style="font-size: 0.8rem; border-bottom: 1px solid #dee2e6;">
                                <tr>
                                    <th width="50%">ตำบลที่เลือก</th>
                                    <th width="50%" class="text-right">ใช้งานอยู่ที่</th>
                                </tr>
                            </thead>
                            <tbody id="duplicate-list-body" style="font-size: 0.9rem;">
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="alert alert-warning border-0 mt-3 mb-0" style="background-color: #fff3cd; color: #856404; font-size: 0.9rem;">
                    <i class="fas fa-info-circle mr-1"></i> <strong>ทางเลือก:</strong> คุณต้องการย้ายตำบลเหล่านี้มาที่นี่ หรือยกเลิกการเลือก?
                </div>
            </div>

            <div class="modal-footer border-0 justify-content-center pb-4">
                <button type="button" class="btn btn-primary px-4 py-2 shadow-sm rounded-pill" id="btn-force-take-zone">
                    <i class="fas fa-exchange-alt mr-2"></i> ย้ายมาเขตนี้
                </button>

                <button type="button" class="btn btn-outline-secondary px-4 py-2 rounded-pill" id="btn-remove-local-duplicate">
                    <i class="fas fa-trash-alt mr-2"></i> ไม่เลือกตำบลที่ซ้ำ
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('footer_scripts')
@include('includes.gridtablejsdeps')
 {!! CustomHelpers::dataTableJs()!!}
 <script stype="text/javascript" src="{{Config('constants.js_url')}}angular/smmApp/controller/gridTableCtrl.js"></script>
 <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- begining of page level js -->

    <script src="{{ Config('constants.admin_js_url') }}jquery.dataTables.js"></script>
    <script src="{{ Config('constants.admin_js_url') }}dataTables.bootstrap.js"></script>
    
    <!-- end of page level js -->

    <script>
      var JQ_GRID_DATA_URL = "{{ action('Admin\ShippingProfile\ShippingRateTableController@getDeliveryAtAddress') }}";
      const JQ_GRID_TITLE = "@lang('admin_flashsale.flashsale_product_list')";
      const METHOD_TYPE = 'POST';
      const CUSTOM_ROW_HEIGHT = {
          'row_height' : 30,
      }; 
      let columnModel = [
        {   
            title: "@lang('admin_shipping.priority')",
            dataIndx:'priority',
            align:'left',
            minWidth: 80,               
        },  
        {   
            title: "@lang('admin_shipping.country')",
            dataIndx:'country_name',
            align:'left',
            minWidth: 80,  
            filter : {
                attr : "@lang('admin_shipping.country_name')",                        
                crules: [
                    {
                        condition: getFilter('country_name', 'condition') ||  'contain',
                        value : '{{ $search_type == "country_name"?$search:''}}' || getFilter('country_name', 'value')  || "",
                    }
                ],
                type: 'textbox', 
                listeners: ['change'],
            },             
        },
        {   
            title: "@lang('admin_shipping.state')",
            dataIndx:'state',
            align:'left',
            minWidth: 120,  
            filter : {
                attr : "@lang('admin_shipping.state')",                        
                crules: [
                    {
                        condition: getFilter('state', 'condition') ||  'contain',
                        value : '{{ $search_type == "state"?$search:''}}' || getFilter('state', 'value')  || "",
                    }
                ],
                type: 'textbox', 
                listeners: ['change'],
            },              
        },
        {   
            title: "@lang('admin_shipping.district')",
            dataIndx:'district',
            align:'left',
            minWidth: 120, 
            filter : {
                attr : "@lang('admin_shipping.district')",                        
                crules: [
                    {
                        condition: getFilter('district', 'condition') ||  'contain',
                        value : '{{ $search_type == "district"?$search:''}}' || getFilter('district', 'value')  || "",
                    }
                ],
                type: 'textbox', 
                listeners: ['change'],
            },              
        },
        {   
            title: "@lang('admin_shipping.sub_district')",
            dataIndx:'sub_district',
            align:'left',
            minWidth: 110,
            filter : {
                attr : "@lang('admin_shipping.sub_district')",                        
                crules: [
                    {
                        condition: getFilter('sub_district', 'condition') ||  'contain',
                        value : '{{ $search_type == "sub_district"?$search:''}}' || getFilter('sub_district', 'value')  || "",
                    }
                ],
                type: 'textbox', 
                listeners: ['change'],
            },             
        },
        {   
            title: "@lang('admin_shipping.zip_from')",
            dataIndx:'zip_from',
            align:'left',
            minWidth: 90, 
            filter : {
                attr : "@lang('admin_shipping.zip_from')",                        
                crules: [
                    {
                        condition: getFilter('zip_from', 'condition') ||  'contain',
                        value : '{{ $search_type == "zip_from"?$search:''}}' || getFilter('zip_from', 'value')  || "",
                    }
                ],
                type: 'textbox', 
                listeners: ['change'],
            },              
        },
        {   
            title: "@lang('admin_shipping.zip_to')",
            dataIndx:'zip_to',
            align:'left',
            minWidth: 90, 
            filter : {
                attr : "@lang('admin_shipping.zip_to')",                        
                crules: [
                    {
                        condition: getFilter('zip_to', 'condition') ||  'contain',
                        value : '{{ $search_type == "zip_to"?$search:''}}' || getFilter('zip_to', 'value')  || "",
                    }
                ],
                type: 'textbox', 
                listeners: ['change'],
            },              
        },
        {   
            title: "@lang('admin_shipping.weight_from')",
            dataIndx:'weight_from',
            align:'left',
            minWidth: 90,
            filter : {
                attr : "@lang('admin_shipping.weight_from')",                        
                crules: [
                    {
                        condition: getFilter('weight_from', 'condition') ||  'contain',
                        value : '{{ $search_type == "weight_from"?$search:''}}' || getFilter('weight_from', 'value')  || "",
                    }
                ],
                type: 'textbox', 
                listeners: ['change'],
            },               
        },
        {   
            title: "@lang('admin_shipping.weight_to')",
            dataIndx:'weight_to',
            align:'left',
            minWidth: 90, 
            filter : {
                attr : "@lang('admin_shipping.weight_to')",                        
                crules: [
                    {
                        condition: getFilter('weight_to', 'condition') ||  'contain',
                        value : '{{ $search_type == "weight_to"?$search:''}}' || getFilter('weight_to', 'value')  || "",
                    }
                ],
                type: 'textbox', 
                listeners: ['change'],
            },              
        },
        {   
            title: "@lang('admin_shipping.qty_from')",
            dataIndx:'qty_from',
            align:'left',
            minWidth: 90,
            filter : {
                attr : "@lang('admin_shipping.qty_from')",                        
                crules: [
                    {
                        condition: getFilter('qty_from', 'condition') ||  'contain',
                        value : '{{ $search_type == "qty_from"?$search:''}}' || getFilter('qty_from', 'value')  || "",
                    }
                ],
                type: 'textbox', 
                listeners: ['change'],
            },               
        },
        {   
            title: "@lang('admin_shipping.qty_to')",
            dataIndx:'qty_to',
            align:'left',
            minWidth: 90, 
            filter : {
                attr : "@lang('admin_shipping.qty_to')",                        
                crules: [
                    {
                        condition: getFilter('qty_to', 'condition') ||  'contain',
                        value : '{{ $search_type == "qty_to"?$search:''}}' || getFilter('qty_to', 'value')  || "",
                    }
                ],
                type: 'textbox', 
                listeners: ['change'],
            },              
        },
        {   
            title: "@lang('admin_shipping.price_from')",
            dataIndx:'price_from',
            align:'left',
            minWidth: 90,   
            filter : {
                attr : "@lang('admin_shipping.price_from')",                        
                crules: [
                    {
                        condition: getFilter('price_from', 'condition') ||  'contain',
                        value : '{{ $search_type == "price_from"?$search:''}}' || getFilter('price_from', 'value')  || "",
                    }
                ],
                type: 'textbox', 
                listeners: ['change'],
            },            
        },
        {   
            title: "@lang('admin_shipping.price_to')",
            dataIndx:'price_to',
            align:'left',
            minWidth: 90, 
            filter : {
                attr : "@lang('admin_shipping.price_to')",                        
                crules: [
                    {
                        condition: getFilter('price_to', 'condition') ||  'contain',
                        value : '{{ $search_type == "price_to"?$search:''}}' || getFilter('price_to', 'value')  || "",
                    }
                ],
                type: 'textbox', 
                listeners: ['change'],
            },              
        },
        {   
            title: "@lang('admin_shipping.product_type_id')",
            dataIndx:'product_type_id',
            align:'left',
            minWidth: 90,               
        },
        {   
            title: "@lang('admin_shipping.base_rate_for_order')",
            dataIndx:'base_rate_for_order',
            align:'left',
            minWidth: 90, 
            filter : {
                attr : "@lang('admin_shipping.base_rate_for_order')",                        
                crules: [
                    {
                        condition: getFilter('base_rate_for_order', 'condition') ||  'contain',
                        value : '{{ $search_type == "base_rate_for_order"?$search:''}}' || getFilter('base_rate_for_order', 'value')  || "",
                    }
                ],
                type: 'textbox', 
                listeners: ['change'],
            },              
        },
        {   
            title: "@lang('admin_shipping.ppp')",
            dataIndx:'percentage_rate_per_product',
            align:'left',
            minWidth: 90,
            filter : {
                attr : "@lang('admin_shipping.ppp')",                        
                crules: [
                    {
                        condition: getFilter('percentage_rate_per_product', 'condition') ||  'contain',
                        value : '{{ $search_type == "percentage_rate_per_product"?$search:''}}' || getFilter('percentage_rate_per_product', 'value')  || "",
                    }
                ],
                type: 'textbox', 
                listeners: ['change'],
            },               
        },
        {   
            title: "@lang('admin_shipping.frpp')",
            dataIndx:'fixed_rate_per_product',
            align:'left',
            minWidth: 90,
            filter : {
                attr : "@lang('admin_shipping.frpp')",                        
                crules: [
                    {
                        condition: getFilter('fixed_rate_per_product', 'condition') ||  'contain',
                        value : '{{ $search_type == "fixed_rate_per_product"?$search:''}}' || getFilter('fixed_rate_per_product', 'value')  || "",
                    }
                ],
                type: 'textbox', 
                listeners: ['change'],
            },

        },
        {   
            title: "@lang('admin_shipping.frpuw')",
            dataIndx:'fixed_rate_per_unit_weight',
            align:'left',
            minWidth: 90, 
            filter : {
                attr : "@lang('admin_shipping.frpuw')",                        
                crules: [
                    {
                        condition: getFilter('fixed_rate_per_product', 'condition') ||  'contain',
                        value : '{{ $search_type == "fixed_rate_per_product"?$search:''}}' || getFilter('fixed_rate_per_product', 'value')  || "",
                    }
                ],
                type: 'textbox', 
                listeners: ['change'],
            },
              
        },
        {   
            title: "@lang('admin_shipping.logistic_base_rate_for_order')",
            dataIndx:'logistic_base_rate_for_order',
            align:'left',
            minWidth: 90,
            filter : {
                attr : "@lang('admin_shipping.logistic_base_rate_for_order')",                        
                crules: [
                    {
                        condition: getFilter('logistic_base_rate_for_order', 'condition') ||  'contain',
                        value : '{{ $search_type == "logistic_base_rate_for_order"?$search:''}}' || getFilter('logistic_base_rate_for_order', 'value')  || "",
                    }
                ],
                type: 'textbox', 
                listeners: ['change'],
            },               
        },
        {   
            title: "@lang('admin_shipping.logistic_ppp')",
            dataIndx:'logistic_percentage_rate_per_product',
            align:'left',
            minWidth: 90, 
            filter : {
                attr : "@lang('admin_shipping.logistic_ppp')",                        
                crules: [
                    {
                        condition: getFilter('logistic_percentage_rate_per_product', 'condition') ||  'contain',
                        value : '{{ $search_type == "logistic_percentage_rate_per_product"?$search:''}}' || getFilter('logistic_percentage_rate_per_product', 'value')  || "",
                    }
                ],
                type: 'textbox', 
                listeners: ['change'],
            },              
        },
        {   
            title: "@lang('admin_shipping.logistic_frpp')",
            dataIndx:'logistic_fixed_rate_per_product',
            align:'left',
            minWidth: 90,
            filter : {
                attr : "@lang('admin_shipping.logistic_frpp')",                        
                crules: [
                    {
                        condition: getFilter('logistic_fixed_rate_per_product', 'condition') ||  'contain',
                        value : '{{ $search_type == "logistic_fixed_rate_per_product"?$search:''}}' || getFilter('logistic_fixed_rate_per_product', 'value')  || "",
                    }
                ],
                type: 'textbox', 
                listeners: ['change'],
            },               
        },
        {   
            title: "@lang('admin_shipping.logistic_frpuw')",
            dataIndx:'logistic_fixed_rate_per_unit_weight',
            align:'left',
            minWidth: 90,
            filter : {
                attr : "@lang('admin_shipping.logistic_frpuw')",                        
                crules: [
                    {
                        condition: getFilter('logistic_fixed_rate_per_unit_weight', 'condition') ||  'contain',
                        value : '{{ $search_type == "logistic_fixed_rate_per_unit_weight"?$search:''}}' || getFilter('logistic_fixed_rate_per_unit_weight', 'value')  || "",
                    }
                ],
                type: 'textbox', 
                listeners: ['change'],
            },               
        },
        {   
            title: "@lang('admin_common.actions')",
            minWidth: 150,
            render : function(ui) {
                return {
                    text:'<a href="javascript:void(0);" class="link-primary">&nbsp<a href="'+ui.rowData.edit_url+'" class="link-primary">@lang("admin_common.edit")</a>&nbsp|&nbsp<a href="javascript:void(0);" class="link-primary" onclick="deleteRecord(\''+ui.rowData.delete_url+'\')">@lang("admin_common.delete")</a>&nbsp|&nbsp<a href="javascript:void(0);" class="link-primary">&nbsp<a href="'+ui.rowData.log_url+'" class="link-primary">@lang("admin_common.log")</a>',    
                };
            },
        }, 
      ];
      $(document).ready(function(){

        flatpickr("#time", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true
        });

        $("#import_tab").on('click',function (){
          $(".save_buttons").attr('disabled',true);
        });
        
       $("#general_tab").on('click',function (){
          $(".save_buttons").attr('disabled',false);
        });

       $("#methods_and_rates_tab").on('click',function (){
        $("#jq_grid_table").pqGrid('refreshDataAndView');
          $(".save_buttons").attr('disabled',false);
        });

        // display badge condition        
        $('.badge-condition .form-group input[name="badge_condition"]').click(function(){
            $('.badge-condition .form-group').find('.box-detail').hide();
            if ($(this).is(':checked')) {
                $(this).parents('.radio-wrap').next('.box-detail').show();                
            }
        });

        // manage dimension weight | Start
        $("#chkb_dimension_weight").on('click', function(){
            if($(this).prop("checked") == true){
                $('#dimension_weight_container').show();
            }else{
                $('#dimension_weight_container').hide();
            }
        });
        // End

        if($("#chkb_dimension_weight").prop("checked") == true){
            $('#dimension_weight_container').show();
        }else{
            $('#dimension_weight_container').hide();
        }

        $(document).on('click','.select_import_location', function(){
            if($(this).val()==='local'){
                $('#import_server').addClass('d-none');
                $('#import_local').removeClass('d-none');
            }else{
                $('#import_local').addClass('d-none');
                $('#import_server').removeClass('d-none');
            }
        });
        
      });
      function deleteRecord(delete_url) {
        $('#delete_record_frm').attr('action', delete_url);
        $('#delete_record').modal('show');
    }    
    </script>



<script>
    $(document).ready(function() {

        let currentRegId = null;

        $('form').on('keyup keypress', function(e) {
            var keyCode = e.keyCode || e.which;
            // 13 คือรหัสของปุ่ม Enter
            if (keyCode === 13) {
                e.preventDefault();
                return false;
            }
        });

        function initializeTreeStatus() { //ฟังก์ชันสำหรับ Initial Check
            console.log('Starting Initial Tree Status Check...');
            $('.district-checkbox').each(function() {
                updateParentCheckbox($(this));
            });
            setTimeout(function() {
                $('.province-checkbox').each(function() {
                    updateParentCheckbox($(this));
                });
            }, 300);
        }


        function showForm() {
            $('#delivery-list-view').stop(true, true).slideUp(300, function() {
                $('#delivery-form-view').stop(true, true).slideDown(400);
            });
        }

        function showList() {
            $('#delivery-form-view').stop(true, true).slideUp(300, function() {
                $('#delivery-list-view').stop(true, true).slideDown(400);
            });
        }

        $('#btn-show-add-form').on('click', function() {
            resetForm();
            showForm();
        });

        $('#btn-back-to-list').on('click', function() {
            resetForm();
            showList();
        });

        function resetForm() {
            // 1. เคลียร์ข้อมูลพื้นฐาน
            $('#reg_id').val('');
            $('#region_name').val('');
            $('#Status').val('1');
            $('#tree-loading').remove();
            if ($('#numstock').length) $('#numstock').val(0);

            $('.geography-checkbox, .province-checkbox, .district-checkbox, .subdistrict-checkbox').prop('checked', false);

            $('.province-list, .district-list, .subdistrict-list').empty().hide();

            $('.geography-toggle, .province-toggle, .district-toggle').text('+').data('loaded', false).attr('data-loaded', 'false');

            $('.subdistrict-list-container').empty();
            $('#area-data-container').empty();

            // 4. เคลียร์ Time Slots
            const $slotContainer = $('#slot-container');
            $('.slot-row').not(':first').remove();
            $('.slot-row:first input').val('');
            $('.slot-row:first input[name="delivery_day[]"]').val('0');
            $('.slot-row:first input[name="print_active_hour[]"]').val('1');

            if (typeof updateSelectedCount === "function") {
                updateSelectedCount();
            }

            $('li').removeClass('bg-light-yellow');

            $('#pickup_address').val('');
            $('#pickup_phone').val('');
        }

        $('.btn-add-round').click(function(e) {
            e.preventDefault();

            var newRow = `
                        <div class="round-item-group slot-row" style="display: contents;">
                            <input type="hidden" name="slot_id[]" value="">

                            <div>
                                <input type="time" name="cutoff_time[]" class="input-box-style" required>
                            </div>
                            
                            <div>
                                <input type="number" name="print_active_hour[]" class="input-box-style text-center" value="1" min="0" max="120" oninput="if(value > 120) value = 120;" required>
                            </div>
                            
                            <div>
                                <input type="time" name="seller_start[]" class="input-box-style" required>
                            </div>
                            
                            <div>
                                <input type="time" name="seller_end[]" class="input-box-style" required>
                            </div>

                            <div class="plus-separator"><i class="fas fa-arrow-right"></i></div>
                            
                            <div>
                                <input type="number" name="delivery_day[]" class="input-box-style small text-center" value="0" min="0" required>
                            </div>

                            <div>
                                <input type="time" name="delivery_start[]" class="input-box-style" required>
                            </div>
                            
                            <div>
                                <input type="time" name="delivery_end[]" class="input-box-style" style="min-width: 0;" required>
                            </div>
                            
                            <div class="d-flex align-items-center justify-content-center" style="gap: 8px;">
                                <label class="modern-switch" style="flex-shrink: 0;">
                                    <input type="checkbox" class="toggle-control" checked>
                                    <input type="hidden" name="is_active[]" class="status-value" value="1">
                                    <span class="slider round"></span>
                                </label>
                                <button type="button" class="btn-icon-remove remove-row-btn" style="flex-shrink: 0; padding: 0; border: none; background: none;">
                                    <i class="fas fa-trash-alt text-danger"></i>
                                </button>
                            </div>
                        </div>
                    `;

            $('#slot-container').append(newRow);
        });

        $('.round-grid-container').on('click', '.btn-remove-round', function() {
            $(this).closest('.round-item-group').remove();
        });

        $(document).on('click', '.remove-row-btn', function() {
            var row = $(this).closest('.round-item-group');

            Swal.fire({
                title: 'ยืนยันการลบ?',
                text: "คุณต้องการลบรอบเวลาการจัดส่งนี้ใช่หรือไม่?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'ตกลง',
                cancelButtonText: 'ยกเลิก',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    row.remove();

                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 1500,
                        timerProgressBar: true
                    });

                    Toast.fire({
                        icon: 'success',
                        title: 'ลบข้อมูลเรียบร้อยแล้ว'
                    });
                }
            });
        });


        const geographiesUrl = "{{ route('admin.ajax.get-geographies') }}";
        const $mainContainer = $('#geography-list-container');

        $(document).on('change', '.tree-view-item input[type="checkbox"]', function(e) {
            var $me = $(this);
            var isChecked = $me.prop('checked');

            // ตรวจสอบว่าเป็นการคลิกโดย User (ป้องกัน Infinite Loop จากการสั่งด้วย Code)
            if (e.originalEvent !== undefined) {
                // หา Item ที่เรากำลังติ๊กอยู่
                var $currentItem = $me.closest('.tree-view-item');

                if ($me.hasClass('geography-checkbox')) {
                    const geoId = $me.data('id');
                    const isChecked = $me.prop('checked');

                    loadProvinces(geoId, function() {
                        $('#province-list-' + geoId)
                            .find('.province-checkbox')
                            .prop('checked', isChecked)
                            .trigger('change');
                    });



                    return;
                }

                if ($me.hasClass('subdistrict-checkbox')) {
                    return;
                }
                var $allChildren = $currentItem.find('ul input[type="checkbox"]');

                if ($allChildren.length > 0) {
                    $allChildren.prop('checked', isChecked)
                        .prop('indeterminate', false);

                    $currentItem.find('ul .partial-label').remove();
                }
            }

            updateParentCheckbox($me);
        });



        function loadProvinces(geoId, callback) {
            const $list = $('#province-list-' + geoId);

            if ($list.children().length > 0) {
                if (callback) callback();
                return;
            }

            $.ajax({
                url: '/admin/ajax/get-provinces/' + geoId,
                type: 'GET',
                success: function(response) {
                    let html = '';
                    $.each(response, function(_, province) {
                        html += `
                    <li class="tree-view-item">
                        <div class="tree-row">
                            <span class="province-toggle" data-id="${province.id}" data-loaded="false">+</span>
                            <input type="checkbox"
                                   class="province-checkbox"
                                   data-id="${province.id}"
                                   value="${province.id}">
                            <label class="tree-label">${province.name_th}</label>
                        </div>
                        <ul class="district-list tree-view" id="district-list-${province.id}" style="display:none;"></ul>
                    </li>
                `;
                    });
                    $list.html(html).slideDown();

                    if (callback) callback();
                }
            });
        }


       function updateParentCheckbox($child) {
            if (!$child || $child.length === 0) return;

            var $list = $child.closest('ul');
            var $parentItem = $list.closest('.tree-view-item, .region-item, .province-item');
            if ($parentItem.length === 0) return;

            var $parentCheckbox = $parentItem.find('> .tree-row > input[type="checkbox"]');
            var $parentLabel = $parentItem.find('> .tree-row > .tree-label');
            var $siblings = $list.find('> li').find('> .tree-row > input[type="checkbox"], > .subdistrict-flex-container > input[type="checkbox"]');
            
            var total = $siblings.length;
            var checked = $siblings.filter(':checked').length;
            var hasIndeterminate = $siblings.filter(function() {
                return $(this).prop('indeterminate');
            }).length > 0;

            var isAllChecked = (total > 0 && total === checked);
            var isPartial = !isAllChecked && (checked > 0 || hasIndeterminate);

            if (isAllChecked) {
                $parentCheckbox.prop('checked', true).prop('indeterminate', false);
            } else if (isPartial) {
                $parentCheckbox.prop('checked', false).prop('indeterminate', true);
            } else {
                $parentCheckbox.prop('checked', false).prop('indeterminate', false);
            }

            $parentLabel.find('.partial-label').remove();
            if (isPartial) {
                $parentLabel.append('<span class="partial-label text-danger" style="font-size:0.85em;"> (บางส่วน)</span>');
            }

            updateParentCheckbox($parentCheckbox);
        }

        loadGeographies();

        function loadGeographies() {
            $.ajax({
                url: geographiesUrl,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    $mainContainer.empty();
                    if (response.length === 0) {
                        $mainContainer.append('<li>ไม่พบข้อมูลภาค</li>');
                        return;
                    }
                    $.each(response, function(index, geo) {
                        let geoName = geo.name;
                        let geoId = geo.id;

                        let html = `
                        <li class="tree-view-item geography-item fw-bold">
                            <div class="tree-row">
                                <span class="toggle-icon geography-toggle" data-id="${geoId}" data-loaded="false">+</span>
                                <input type="checkbox" value="${geoId}" class="geography-checkbox" data-id="${geoId}">
                                <label class="tree-label">${geoName}</label>
                            </div>
                            <ul class="province-list tree-view" id="province-list-${geoId}" style="display:none; padding-left: 20px;"></ul>
                        </li>
                        `;
                        $mainContainer.append(html);
                    });
                    let checkExist = setInterval(function() {
                    if ($('.province-checkbox').length > 0) {
                        initializeTreeStatus();
                        clearInterval(checkExist);
                    }
                    }, 500);
                },
                error: function(xhr, status, error) {
                    console.error(error);
                    $mainContainer.html('<li class="text-danger">Error loading geographies</li>');
                }
            });
        }

        // 1. ส่วนของการโหลด "จังหวัด" (Provinces)
        $(document).on('click', '.geography-toggle', function() {
            let $icon = $(this);
            let geoId = $icon.data('id');
            let isLoaded = $icon.data('loaded');
            let $provinceList = $('#province-list-' + geoId);

            if ($icon.text() === '+') {
                $icon.text('-');
                $provinceList.slideDown();

                if (!isLoaded) {
                    $provinceList.html('<li><i class="fas fa-spinner fa-spin"></i> &nbsp; กำลังโหลดจังหวัด...</li>');
                    // เช็คว่าภาคถูกเลือกอยู่หรือไม่
                    const isParentChecked = $(`.geography-checkbox[data-id="${geoId}"]`).prop('checked');

                    $.ajax({
                        url: '/admin/ajax/get-provinces/' + geoId,
                        type: 'GET',
                        dataType: 'json',
                        success: function(response) {
                            $provinceList.empty();
                            $icon.data('loaded', true);

                            if (response.length === 0) {
                                $provinceList.append('<li>ไม่พบข้อมูลจังหวัดในภาคนี้</li>');
                            } else {
                                $.each(response, function(index, province) {
                                    let provinceName = province.name_th ? province.name_th : 'N/A';
                                    let provinceId = province.id;
                                    const checkedAttr = isParentChecked ? 'checked' : '';

                                    let html = `
                                    <li class="tree-view-item">
                                        <div class="tree-row">
                                            <span class="toggle-icon province-toggle" data-id="${provinceId}" data-loaded="false">+</span>
                                            <input type="checkbox" name="selected_provinces[]" value="${provinceId}" class="province-checkbox" data-id="${provinceId}" ${checkedAttr}>
                                            <label class="tree-label">${provinceName}</label>
                                        </div>
                                        <ul class="district-list tree-view" id="district-list-${provinceId}" style="display:none; padding-left: 20px;"></ul>
                                    </li>
                                `;
                                    $provinceList.append(html);
                                });
                            }
                        }
                    });
                }
            } else {
                $icon.text('+');
                $provinceList.slideUp();
            }
        });

        // 2. ส่วนของการโหลด "อำเภอ" (Districts)
        $(document).on('click', '.province-toggle', function() {
            let $icon = $(this);
            let provinceId = $icon.data('id');
            let isLoaded = $icon.data('loaded');
            let $districtList = $('#district-list-' + provinceId);

            if ($icon.text() === '+') {
                $icon.text('-');
                $districtList.slideDown();

                if (!isLoaded) {
                    $districtList.html('<li><i class="fas fa-spinner fa-spin"> </i>&nbsp; กำลังโหลดอำเภอ...</li>');
                    const isParentChecked = $(`.province-checkbox[data-id="${provinceId}"]`).prop('checked');

                    $.ajax({
                        url: '/admin/ajax/get-districts/' + provinceId,
                        type: 'GET',
                        dataType: 'json',
                        success: function(response) {
                            $districtList.empty();
                            $icon.data('loaded', true);

                            if (response.length === 0) {
                                $districtList.append('<li>ไม่มีข้อมูลอำเภอ</li>');
                            } else {
                                $.each(response, function(index, district) {
                                    let districtName = district.name_th ? district.name_th : 'N/A';
                                    let districtId = district.id;
                                    const checkedAttr = isParentChecked ? 'checked' : '';

                                    let html = `
                                    <li class="tree-view-item">
                                        <div class="tree-row">
                                            <span class="toggle-icon district-toggle" data-id="${districtId}" data-loaded="false">+</span>
                                            <input type="checkbox" name="selected_districts[]" value="${districtId}" class="district-checkbox" data-id="${districtId}" ${checkedAttr}>
                                            <label class="tree-label">${districtName}</label>
                                        </div>
                                        <ul class="subdistrict-list tree-view" id="subdistrict-list-${districtId}" style="display:none; padding-left: 20px;"></ul>
                                    </li>
                                `;
                                    $districtList.append(html);
                                });
                            }
                        }
                    });
                }
            } else {
                $icon.text('+');
                $districtList.slideUp();
            }
        });

        // 3. ส่วนของการโหลด "ตำบล" (Subdistricts) - จุดสำคัญสุด
        $(document).on('click', '.district-toggle', function() {
            let $icon = $(this);
            let districtId = $icon.data('id');
            let isLoaded = $icon.data('loaded');
            let $subdistrictList = $('#subdistrict-list-' + districtId);

            if ($icon.text() === '+') {
                $icon.text('-');
                $subdistrictList.slideDown();

                if (!isLoaded) {
                    $subdistrictList.html('<li><i class="fas fa-spinner fa-spin"></i> &nbsp; กำลังโหลดตำบล...</li>');
                    const isParentChecked = $(`.district-checkbox[data-id="${districtId}"]`).prop('checked');

                    $.ajax({
                        url: '/admin/ajax/get-subdistricts/' + districtId,
                        type: 'GET',
                        dataType: 'json',
                        success: function(response) {
                            $subdistrictList.empty();
                            $icon.data('loaded', true);

                            if (response.length === 0) {
                                $subdistrictList.append('<li>ไม่มีข้อมูลตำบล</li>');
                            } else {
                                $.each(response, function(index, subdistrict) {
                                    let subdistrictName = subdistrict.name_th ? subdistrict.name_th : 'N/A';
                                    let subdistrictId = subdistrict.id;
                                    let zipCode = subdistrict.zip_code || '-';
                                    let isUsed = subdistrict.is_used;
                                    let regionName = subdistrict.used_by_region_name;
                                    let labelClass = 'tree-label';
                                    let usedInfoHtml = '';

                                    if (isUsed) {
                                        labelClass += ' text-danger fw-bold';
                                        usedInfoHtml = `
                                        <span class="used-info-remove text-danger"
                                            style="cursor: pointer; text-decoration: underline;"
                                            data-subdistrict-id="${subdistrictId}"
                                            data-subdistrict-name="${subdistrictName}"
                                            data-region-names="${regionName}"
                                            title="คลิกเพื่อลบออกจากเขตเหล่านี้">
                                            <br><small>(ใช้งานโดย: ${regionName} - คลิกเพื่อลบ)</small>
                                        </span>`;
                                    }

                                    const checkedAttr = isParentChecked ? 'checked' : '';

                                    // [แก้ไข 3] ปรับ HTML ให้ตรงกับหน้า Edit (ใส่ subdistrict-row-item และ flex-container)
                                    let html = `
                                    <li class="subdistrict-row-item">
                                        <div class="subdistrict-flex-container">
                                            <input type="checkbox"  class="subdistrict-checkbox" value="${subdistrictId}" ${checkedAttr}>
                                            <label class="${labelClass}">
                                                ${subdistrictName} ${usedInfoHtml}
                                            </label>
                                            <span class="postal-code">${zipCode}</span>
                                        </div>
                                    </li>
                                `;
                                    $subdistrictList.append(html);
                                });

                                // กรณีที่โหลดมาแล้วลูกติ๊กครบ หรือติ๊กไม่ครบ แม่ต้องแสดงผลให้ถูก
                                var $anyChild = $subdistrictList.find('input[type="checkbox"]').first();
                                if ($anyChild.length > 0) {
                                    updateParentCheckbox($anyChild);
                                }
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error(error);
                            $subdistrictList.html('<li class="text-danger">Error loading data</li>');
                        }
                    });
                }
            } else {
                $icon.text('+');
                $subdistrictList.slideUp();
            }
        });

        // จัดการการคลิกเพื่อลบตำบลจากเขตอื่น

        $(document).on('click', '.used-info-remove', function(e) {
            e.preventDefault();
            e.stopPropagation();

            let subId = $(this).data('subdistrict-id');
            let subName = $(this).data('subdistrict-name');
            let regionNames = $(this).data('region-names');

            $('#modalSubdistrictIdToDelete').val(subId);
            $('#modalSubdistrictName').text(subName);
            $('#modalRegionNames').text(regionNames);

           $('#removeSubdistrictModal').modal('show');
        });

        $('#btnConfirmRemove').click(function() {
            let $btn = $(this);
            let subIdToDelete = $('#modalSubdistrictIdToDelete').val();

            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> กำลังลบ...');

            $.ajax({
                url: "{{ route('admin.ajax.remove-subdistrict-usage') }}",
                type: 'POST',
                data: {
                    subdistrict_id: subIdToDelete,
                    _token: '{{ csrf_token() }}'
                },
                dataType: 'json',
                success: function(response) {

                    if (response.success) {
                        $('#removeSubdistrictModal').modal('hide');
                        // $('body').removeClass('modal-open').css('padding-right', '');
                        let $activeDistrictToggle = $(`span.toggle-icon.district-toggle[data-loaded="true"]`).filter(function() {
                            return $(this).text().trim() === '-';
                        });

                        Swal.fire({
                                icon: 'success',
                                title: 'ลบสำเร็จ',
                                text: 'ข้อมูลถูกลบเรียบร้อยแล้ว',
                                confirmButtonText: 'ตกลง'
                            }).then(() => {
                                if (currentRegId) {
                                    loadRegionDetail(currentRegId);
                                }
                            });

                        if ($activeDistrictToggle.length > 0) {
                            $activeDistrictToggle.trigger('click');
                            setTimeout(() => {
                                $activeDistrictToggle.trigger('click');
                            }, 500);
                        }
                    } else {
                        alert('Error: ' + response.message);
                        $btn.prop('disabled', false).html('ยืนยันลบ');
                    }
                },
                error: function(xhr, status, error) {
                    console.error(error);
                    alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
                },
                complete: function() {
                    $btn.prop('disabled', false).html('<i class="fas fa-trash-alt"> </i> ยืนยันการลบ');
                }
            });
        });

                // เปลี่ยนตัว Selector ให้ครอบคลุมทุกระดับ
        $(document).on('change', '.region-checkbox, .province-checkbox, .district-checkbox, .subdistrict-checkbox', function(e) {
            // 1. ตรวจสอบสถานะของตัวที่ถูกคลิก
            var isChecked = $(this).is(':checked');
            var $parentItem = $(this).closest('.tree-view-item, .subdistrict-row-item');

            // --- ส่วนที่ 1: ติ๊ก "ลงล่าง" (Cascade Down) ---
            // ค้นหาเฉพาะลูกที่อยู่ "ภายใต้" ตัวที่คลิกเท่านั้น (หาใน ul ถัดไป)
            var $childrenContainer = $parentItem.find('> ul, > .subdistrict-list');
            if ($childrenContainer.length > 0) {
                $childrenContainer.find('input[type="checkbox"]').each(function() {
                    $(this).prop('checked', isChecked).prop('indeterminate', false);
                    
                    // เปลี่ยนสีพื้นหลังลูกๆ
                    if (isChecked) {
                        $(this).closest('.tree-view-item, .subdistrict-row-item').addClass('bg-light-yellow');
                    } else {
                        $(this).closest('.tree-view-item, .subdistrict-row-item').removeClass('bg-light-yellow');
                    }
                });
            }

        // --- ส่วนที่ 2: ติ๊ก "ขึ้นบน" (Cascade Up) ---
        // เรียกฟังก์ชันคำนวณสถานะแม่-ปู่-ย่า-ตา-ยาย
        updateParentCheckbox($(this));

        // จัดการสีพื้นหลังตัวเอง
            if (isChecked) {
                $parentItem.addClass('bg-light-yellow');
            } else {
                $parentItem.removeClass('bg-light-yellow');
            }
    });


        // ฟังก์ชันสำหรับรวบรวมข้อมูลพื้นที่
        function syncAreaSelection() {
            let provinceIds = [];
            $('.province-checkbox:checked').each(function() {
                provinceIds.push($(this).data('id'));
            });

            let districtIds = [];
            $('.district-checkbox:checked').each(function() {
                let pId = $(this).data('province-id');
                // เก็บอำเภอ เฉพาะที่ไม่ได้ติ๊กจังหวัดแม่ไว้
                if (!$(`.province-checkbox[data-id="${pId}"]`).prop('checked')) {
                    districtIds.push($(this).data('id'));
                }
            });

            // ค้นหาฟอร์มที่ใช้ในการเซฟ (ระบุ ID หรือ Name ให้ชัดเจน)
            let $form = $('form[name="update"]'); // หรือ $('#your-form-id')

            if ($('#area-data-container').length === 0) {
                $form.append('<div id="area-data-container"></div>');
            }

            $('#area-data-container').html(`
            <input type="hidden" name="selected_provinces[]" value="${provinceIds.join(',')}">
            <input type="hidden" name="selected_districts[]" value="${districtIds.join(',')}">
        `);
        }

        // ผูกเหตุการณ์ตอนกด Submit ฟอร์ม
        $(document).on('submit', 'form[name="update"]', function() {
            syncAreaSelection();
            return true;
        });


        function resetGeographySelection() {
            // 1. ค้นหา Checkbox ทุกระดับชั้น แล้วเอาติ๊กถูกออก
            $('.geography-checkbox, .province-checkbox, .district-checkbox, .subdistrict-checkbox').prop('checked', false);

            // 2. สั่งพับเก็บ List รายชื่อจังหวัด/อำเภอ/ตำบล
            $('.province-list, .district-list, .subdistrict-list').slideUp();

            // 3. เปลี่ยนไอคอนหน้าหัวข้อให้กลับเป็น (+)
            $('.province-toggle, .district-toggle').text('+');
            $('.geography-toggle').text('+');
        }

        // 1. ดักจับการคลิกปุ่มแก้ไข
        $(document).on('click', '.btn-edit', function(e) {
            e.preventDefault();
            currentRegId = $(this).data('id');
            loadRegionDetail(currentRegId);

            // var $btn = $(this);
            // var regId = $btn.data('id');


            
        });

        function loadRegionDetail(regId) {
            var $btn = $('.btn-edit[data-id="' + regId + '"]');

            $('body').css('cursor', 'wait');
            $btn.prop('disabled', true);


            $.ajax({
                url: '/admin/shipping/get-region-detail/' + regId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {

                    console.log("Response Data:", response);

                    if (!response || response.status !== 'success') {
                        alert(response && response.message ? response.message : 'ไม่พบข้อมูล');
                        return;
                    }

                    var data = response.data;

                    /* =========================
                     * STEP 1 : สลับหน้าจอ
                     * ========================= */
                    if (typeof showForm === "function") {
                        showForm();
                    } else {
                        $('#delivery-list-view').hide();
                        $('#delivery-form-view').show();
                    }

                    /* =========================
                     * STEP 2 : ใส่ข้อมูล Header
                     * ========================= */
                    resetGeographySelection();

                    $('#reg_id').val(data.reg_id || '');
                    $('#region_name').val(data.reg_name || '');
                    $('#pickup_address').val(data.dc_address || '');
                    $('#pickup_phone').val(data.dc_tel || '');
                    $('#Status').val(data.status || '');

                    // ส่วนของ Status
                    if (data.hasOwnProperty('status')) {
                        $('#Status').val(data.status).trigger('change');
                    }

                    let isActive = (data.status == 1);
                    $('#StatusToggle').prop('checked', isActive);
                    updateToggleUI(isActive);

                    if ($('#numstock').length) {
                        $('#numstock').val(
                            typeof data.numstock !== 'undefined' && data.numstock !== null ?
                            data.numstock :
                            0
                        );
                    }

                    var typeVal = (data.reg_type == 2) ? 'pickup' : 'address';
                    $('input[name="deliveryType"][value="' + typeVal + '"]')
                        .prop('checked', true)
                        .trigger('change');

                    $('.title-prod').text('แก้ไขเขตการขาย: ' + data.reg_name);

                    /* =========================
                     * STEP 3 : Time Slot
                     * ========================= */
                    if (typeof renderTimeSlots === "function") {
                        renderTimeSlots(data.time_slots || []);
                    }

                    /* =========================
                     * STEP 4 : Tree View
                     * ========================= */
                    if (data.selected_ids && response.full_structure) {
                        const selected = data.selected_ids;
                        const fullData = response.full_structure;

                        const totalTasks =
                            selected.geographies && selected.geographies.length ?
                            selected.geographies.length :
                            0;
                        var geoPane = $('.geo-pane');
                        let $container = $('#selected-area-summary');

                        window.isLoadCancelled = false;
                        $container.find('#tree-loading').remove();

                        $container.prepend(`
                        <div id="tree-loading" class="alert alert-info d-flex justify-content-between align-items-center p-2 mb-2 shadow-sm" style="font-size: 13px; min-width: 250px;">
                            <div>
                                <i class="fas fa-spinner fa-spin text-primary mr-2"></i> 
                                <span>กำลังโหลดพื้นที่... </span>
                                <strong id="load-progress-count">(0 / ${totalTasks})</strong>
                            </div>
                            <button type="button" id="btn-cancel-load" class="btn btn-xs btn-danger py-0 px-2 ml-2" style="font-size: 11px;">ยกเลิก</button>
                        </div>
                    `);

                        geoPane.addClass('section-disabled');

                        const selectedSet = {
                            geographies: new Set(selected.geographies || []),
                            provinces: new Set(selected.provinces || []),
                            districts: new Set(selected.districts || []),
                            subdistricts: new Set(selected.subdistricts || [])
                        };
                        // ส่ง selectedIds และ fullStructure ให้ถูกลำดับ
                        autoExpandAndSelectTree(selectedSet, fullData)
                            .then(function() {
                                if (window.isLoadCancelled) {
                                    $('#tree-loading')
                                        .removeClass('alert-info')
                                        .addClass('alert-warning')
                                        .html('หยุดการโหลดแล้ว');
                                    setTimeout(function() {
                                        $('#tree-loading').fadeOut();
                                    }, 2000);
                                } else {
                                    $('#tree-loading').remove();
                                }
                                geoPane.removeClass('section-disabled');
                                if (typeof updateSelectedCount === "function") updateSelectedCount();
                            })
                            .catch(function(error) {
                                console.error("Tree Error:", error);
                                $('#tree-loading')
                                    .removeClass('alert-info')
                                    .addClass('alert-danger')
                                    .html('โหลดไม่สำเร็จ');
                            });
                    }

                    // 3. ดักจับปุ่มยกเลิก
                    $(document).on('click', '#btn-cancel-load', function() {
                        window.isLoadCancelled = true;
                        $(this).prop('disabled', true).text('กำลังหยุด...');
                    });
                },

                error: function(xhr) {
                    console.error('AJAX ERROR:', xhr.status);
                    console.error(xhr.responseText);
                    alert('เกิดข้อผิดพลาดในการดึงข้อมูล');
                },

                complete: function() {
                    $('body').css('cursor', 'default');
                    $btn.prop('disabled', false);
                }
            });
        }

        // 1. ฟังก์ชันช่วยจัดกลุ่มข้อมูล (คงเดิม)
        function groupBy(array, key) {
            if (!Array.isArray(array)) return {};
            return array.reduce((acc, obj) => {
                const property = obj[key];
                acc[property] = acc[property] || [];
                acc[property].push(obj);
                return acc;
            }, {});
        }

        // 2. ฟังก์ชันหลักสำหรับหน้า Edit (ปรับให้แสดงครบแบบต้นฉบับ)
        // ปรับฟังก์ชันหลักเพื่อให้วาดโครงสร้างได้ครบถ้วน
        async function autoExpandAndSelectTree(selectedSet, fullStructure) {
            if (!fullStructure) return;

            const selProvs = new Set(Array.from(selectedSet.provinces || []).map(String));
            const selDists = new Set(Array.from(selectedSet.districts || []).map(String));
            const selSubs = new Set(Array.from(selectedSet.subdistricts || []).map(String));

            const provinceMap = groupBy(fullStructure.provinces, 'geography_id');
            const districtMap = groupBy(fullStructure.districts, 'province_id');
            const subdistrictMap = groupBy(fullStructure.subdistricts, 'district_id');

            $('.geography-toggle').each(function() {
                const geoId = String($(this).data('id'));
                const $provinceList = $('#province-list-' + geoId);
                const provinces = provinceMap[geoId] || [];

                if (provinces.length > 0) {
                    const html = provinces
                        .map(p => renderProvince(p, districtMap, subdistrictMap, selProvs, selDists, selSubs))
                        .join('');

                    $provinceList.html(html);

                    // ตรวจสอบเพื่อกาง Tree ออกอัตโนมัติหากมีการเลือกข้อมูลข้างใน
                    const hasSelection = provinces.some(p => {
                        const pId = String(p.id);
                        const districts = districtMap[p.id] || [];
                        return selProvs.has(pId) || districts.some(d => {
                            const dId = String(d.id);
                            return selDists.has(dId) || (subdistrictMap[d.id] || []).some(s => selSubs.has(String(s.id)));
                        });
                    });

                    if (hasSelection) {
                        $provinceList.show();
                        $(this).text('-').data('loaded', true);
                    }
                }
            });
        }

        // ฟังก์ชัน Render ระดับจังหวัด (ปรับโครงสร้างเป็น Column)
        // --- 1. ฟังก์ชัน Render ระดับอำเภอ (District) ---
        function renderDistrict(d, subdistrictMap, selDists, selSubs) {
            const subs = subdistrictMap[d.id] || [];

            // 1. นับจำนวนตำบลที่ถูกเลือกจริง
            const selectedInDistrict = subs.filter(s => selSubs.has(String(s.id))).length;

            // --- แก้ไขจุดนี้ ---
            // อำเภอจะถูกติ๊ก (Checked) ก็ต่อเมื่อ "เลือกตำบลครบทุกอัน" เท่านั้น
            // (เลิกเช็คจาก selDists โดยตรง เพราะค่าอาจจะไม่สัมพันธ์กับลูก)
            const isAllSelected = subs.length > 0 && selectedInDistrict === subs.length;
            const isChecked = isAllSelected ? 'checked' : '';

            // ส่วน Partial (บางส่วน) เหมือนเดิม
            const isPartial = selectedInDistrict > 0 && selectedInDistrict < subs.length;
            const partialText = isPartial ? '<span class="partial-label text-danger" style="font-size:0.85em;">(บางส่วน)</span>' : '';

            // กางออกถ้ามีการเลือกข้างใน (ไม่ว่าจะครบหรือไม่)
            const isExpanded = selectedInDistrict > 0; // หรือใช้ || isAllSelected ก็ได้

            return `
                    <li class="tree-view-item">
                        <div class="tree-row">
                            <span class="toggle-icon district-toggle" data-id="${d.id}" data-loaded="true">${isExpanded ? '-' : '+'}</span>
                            <input type="checkbox" class="district-checkbox" value="${d.id}" data-id="${d.id}" ${isChecked}>
                            <label class="tree-label">${d.name_th} ${partialText}</label>
                        </div>
                        <ul class="subdistrict-list tree-view" id="subdistrict-list-${d.id}" style="display:${isExpanded ? 'block' : 'none'};">
                            ${subs.map(s => {
                                const isSubChecked = selSubs.has(String(s.id)) ? 'checked' : '';
                                return `
                                <li class="subdistrict-row-item ${isSubChecked ? 'bg-light-yellow' : ''}">
                                    <div class="subdistrict-flex-container">
                                        <input type="checkbox"  class="subdistrict-checkbox" value="${s.id}" ${isSubChecked}>
                                        <label class="tree-label">${s.name_th}</label>
                                        <span class="postal-code">${s.zip_code || ''}</span>
                                    </div>
                                </li>`;
                            }).join('')}
                        </ul>
                    </li>`;
                        }

        // --- 2. ฟังก์ชัน Render ระดับจังหวัด (Province) ---
        function renderProvince(p, districtMap, subdistrictMap, selProvs, selDists, selSubs) {
            const districts = districtMap[p.id] || [];

            // Logic: จังหวัดจะถูกติ๊ก ก็ต่อเมื่อ "ทุกอำเภอข้างใน ถูกเลือกตำบลครบทุกอัน"
            // ต้องเช็คลึกลงไปถึงตำบล
            const isAllSelected = districts.length > 0 && districts.every(d => {
                const subs = subdistrictMap[d.id] || [];
                if (subs.length === 0) return false; // ถ้าอำเภอไม่มีตำบล ถือว่าไม่สมบูรณ์

                // นับจำนวนตำบลที่เลือกในอำเภอนั้น
                const subSelectedCount = subs.filter(s => selSubs.has(String(s.id))).length;

                // อำเภอนี้ต้องถูกเลือกครบ
                return subSelectedCount === subs.length;
            });

            const isChecked = isAllSelected ? 'checked' : '';

            const hasAnySelection = districts.some(d => {
                const subs = subdistrictMap[d.id] || [];
                return subs.some(s => selSubs.has(String(s.id)));
            });

            const isPartial = hasAnySelection && !isAllSelected;
            const partialText = isPartial ? '<span class="partial-label text-danger" style="font-size:0.85em;">(บางส่วน)</span>' : '';

            const isExpanded = hasAnySelection;

            return `
                <li class="tree-view-item">
                    <div class="tree-row">
                        <span class="toggle-icon province-toggle" data-id="${p.id}" data-loaded="true">${isExpanded ? '-' : '+'}</span>
                        <input type="checkbox" name="selected_provinces[]" class="province-checkbox" value="${p.id}" data-id="${p.id}" ${isChecked}>
                        <label class="tree-label">${p.name_th} ${partialText}</label>
                    </div>
                    <ul class="district-list tree-view" id="district-list-${p.id}" style="display:${isExpanded ? 'block' : 'none'};">
                        ${districts.map(d => renderDistrict(d, subdistrictMap, selDists, selSubs)).join('')}
                    </ul>
                </li>`;
                    }


        // 1. ฟังก์ชันโหลดจังหวัด (Return Promise)
        function loadProvincesAsync(geoId) {
            return new Promise((resolve, reject) => {
                let $list = $('#province-list-' + geoId);
                let $icon = $(`.geography-toggle[data-id="${geoId}"]`);

                // ถ้าโหลดเสร็จแล้ว หรือกำลังเปิดอยู่ ให้ resolve เลย
                if ($icon.data('loaded') === true) {
                    if ($icon.text() === '+') $list.slideDown(); // เปิดถ้าปิดอยู่
                    $icon.text('-');
                    resolve();
                    return;
                }

                $list.html('<li><i class="fas fa-spinner fa-spin"></i> กำลังโหลด...</li>').show();
                $icon.text('-');

                $.ajax({
                    url: '/admin/ajax/get-provinces/' + geoId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        $list.empty();
                        $icon.data('loaded', true);
                        // ... (ใส่ Logic Loop สร้าง HTML <li> เดิมของคุณตรงนี้) ...
                        // Copy โค้ด HTML Loop เดิมของคุณมาใส่ตรงนี้
                        if (response.length > 0) {
                            $.each(response, function(index, province) {
                                // ... HTML เดิม ...
                                let html = `<li class="tree-view-item">
                                        <span class="toggle-icon province-toggle" data-id="${province.id}" data-loaded="false">+</span>
                                        <input type="checkbox" name="selected_provinces[]" class="province-checkbox" data-id="${province.id}" value="${province.id}">
                                        <label>${province.name_th}</label>
                                    </li>
                                    <ul class="district-list tree-view" id="district-list-${province.id}" style="display:none; padding-left: 20px;"></ul>`;
                                $list.append(html);
                            });
                        }
                        resolve();
                    },
                    error: function(err) {
                        reject(err);
                    }
                });
            });
        }

        // 2. ฟังก์ชันโหลดอำเภอ 
        function loadDistrictsAsync(provId) {
            return new Promise((resolve, reject) => {
                let $list = $('#district-list-' + provId);
                let $icon = $(`.province-toggle[data-id="${provId}"]`);

                if ($icon.data('loaded') === true) {
                    if ($icon.text() === '+') $list.slideDown();
                    $icon.text('-');
                    resolve();
                    return;
                }

                $list.html('<li>กำลังโหลด...</li>').show();
                $icon.text('-');

                $.ajax({
                    url: '/admin/ajax/get-districts/' + provId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        $list.empty();
                        $icon.data('loaded', true);
                        // ... Copy Loop HTML อำเภอ มาใส่ ...
                        if (response.length > 0) {
                            $.each(response, function(index, dist) {
                                let html = `<li class="tree-view-item">
                                        <span class="toggle-icon district-toggle" data-id="${dist.id}" data-loaded="false">+</span>
                                        <input type="checkbox" name="selected_districts[]" class="district-checkbox" data-id="${dist.id}" value="${dist.id}">
                                        <label>${dist.name_th}</label>
                                    </li>
                                    <ul class="subdistrict-list tree-view" id="subdistrict-list-${dist.id}" style="display:none; padding-left: 20px;"></ul>`;
                                $list.append(html);
                            });

                            let $firstChild = $list.find('.district-checkbox').first();
                            if ($firstChild.length > 0) {
                                updateParentCheckbox($firstChild);
                            }
                        }
                        resolve();
                    },
                    error: function(err) {
                        reject(err);
                    }
                });
            });
        }

        // 3. ฟังก์ชันโหลดตำบล Async
        function loadSubdistrictsAsync(distId) {
            return new Promise((resolve, reject) => {
                let $list = $('#subdistrict-list-' + distId);
                let $icon = $(`.district-toggle[data-id="${distId}"]`);

                // ... (Code เช็ค loaded=true เหมือนเดิม) ...
                if ($icon.data('loaded') === true) {
                    if ($icon.text() === '+') $list.slideDown();
                    $icon.text('-');
                    resolve();
                    return;
                }

                $list.html('<li>กำลังโหลด...</li>').show();
                $icon.text('-');

                // เช็คว่าแม่ (อำเภอ) ติ๊กอยู่ไหม
                const isParentChecked = $(`.district-checkbox[data-id="${distId}"]`).prop('checked');

                $.ajax({
                    url: '/admin/ajax/get-subdistricts/' + distId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        $list.empty();
                        $icon.data('loaded', true);

                        if (response.length > 0) {
                            $.each(response, function(index, sub) {
                                let isUsedHtml = sub.is_used ? '<span class="text-danger fw-bold">(ถูกใช้งาน)</span>' : '';
                                // ถ้าอำเภอติ๊กอยู่ -> ตำบลต้องติ๊กด้วย
                                let checkedAttr = isParentChecked ? 'checked' : '';

                                // สร้าง HTML
                                let html = `
                            <li class="subdistrict-row-item">
                                <div class="subdistrict-flex-container">
                                    <input type="checkbox"  value="${sub.id}" class="subdistrict-checkbox" ${checkedAttr}>
                                    <label class="tree-label">${sub.name_th} ${isUsedHtml}</label>
                                    <span class="postal-code">${sub.zip_code || ''}</span>
                                </div>
                            </li>`;
                                $list.append(html);
                            });

                            // ========================================================
                            // [จุดแก้ไข] สั่งคำนวณสถานะแม่ใหม่ทันที เพื่อให้ขึ้น (บางส่วน)
                            // ========================================================
                            let $firstChild = $list.find('.subdistrict-checkbox').first();
                            if ($firstChild.length > 0) {
                                updateParentCheckbox($firstChild);
                            }
                        }
                        resolve();
                    },
                    error: function(err) {
                        reject(err);
                    }
                });
            });
        }

        // $(document).on('change', '#StatusToggle', function() {
        //     updateToggleUI($(this).is(':checked'));
        // });

        // $(document).on('change', '.toggle-control', function() {
        //     // หาค่าว่าติ๊กอยู่หรือไม่ (true/false)
        //     let isChecked = $(this).is(':checked');
            
        //     // หา Hidden Input ที่อยู่ถัดจาก Checkbox นี้ (class status-value)
        //     // และปรับค่าเป็น 1 หรือ 0
        //     $(this).next('.status-value').val(isChecked ? 1 : 0);

        //     // ตรวจสอบค่าใน Console
        //     console.log("Checkbox Status:", isChecked);
        //     console.log("Hidden Input Value:", $(this).next('.status-value').val());
        // });

        $(document).on('change', '#StatusToggle', function() {
            let isChecked = $(this).is(':checked');
            $(this).next('.status-value').val(isChecked ? 1 : 0);
            
            // ถ้ามีฟังก์ชันเปลี่ยนสี UI ให้ใส่ตรงนี้
            if (typeof updateToggleUI === 'function') {
                updateToggleUI(isChecked); 
            }
        });

        // ฟังก์ชันอัปเดตข้อความและสี
        function updateToggleUI(checked) {
            const $text = $('#StatusText');
            if (checked) {
                $text.text('Active').addClass('text-success').removeClass('text-muted');

            } else {
                $text.text('Inactive').addClass('text-muted').removeClass('text-success');
            }
        }

        $(document).on('change', '#StatusToggle', function() {
            // 1. ตรวจสอบสถานะของปุ่มหลัก (Checkbox ตัวบนสุด)
            let isChecked = $(this).is(':checked');
            let statusValue = isChecked ? 1 : 0;
            $('.toggle-control').prop('checked', isChecked);
            $('.status-value').val(statusValue);

            if (typeof updateToggleUI === 'function') {
                updateToggleUI(isChecked); 
            }

        });

            // 2. ปุ่มรองสั่งการปุ่มหลัก (Slaves -> Master)
        $(document).on('change', '.toggle-control', function() {

                let isThisChecked = $(this).is(':checked');
                $(this).closest('.modern-switch').find('.status-value').val(isThisChecked ? 1 : 0);
                
                let total = $('.toggle-control').length;
                let checked = $('.toggle-control:checked').length;
                
                if (checked === total) {
                    $('#StatusToggle').prop('checked', true);
                    updateToggleUI(true);
                } 
                else if (checked === 0) {
                    $('#StatusToggle').prop('checked', false);
                    updateToggleUI(false);
                }
                else {
                    console.log("Mixed state: Master remains the same.");
                }
            });

        // ฟังก์ชันสร้าง HTML รอบเวลา (Time Slots) มาใส่ใหม่
        function renderTimeSlots(slots) {
            $('.slot-row').remove();

            if (slots.length > 0) {
                slots.forEach(function(slot, index) {
                    addSlotRow(slot); // เรียกใช้ฟังก์ชันเดียวกับปุ่ม "เพิ่มรอบ" แต่ส่งค่าเข้าไปด้วย
                });
            } else {
                // กรณีไม่มีรอบเลย ให้เพิ่มแถวเปล่า 1 แถว
                $('#btn-add-row-slot').trigger('click');
            }
        }

        // ฟังก์ชันเพิ่มแถว (ปรับปรุงจากของเดิมของคุณให้รับค่า value ได้)
        function addSlotRow(data = null) {
            var cutoff = data ? data.order_cutoff_time : '';
            var print = data ? data.seller_print_active : '1';

            var html = `
                        <div class="round-item-group slot-row" style="display: contents;">
                            <input type="hidden" name="slot_id[]" value="${data ? data.del_t_s_id : ''}">
                            
                            <div><input type="time" name="cutoff_time[]" class="input-box-style" value="${cutoff}"></div>
                            <div><input type="number" name="print_active_hour[]" class="input-box-style small text-center" value="${print}"></div>
                            <div><input type="time" name="seller_start[]" class="input-box-style" value="${data ? data.seller_start_time_str : ''}"></div>
                            <div><input type="time" name="seller_end[]" class="input-box-style" value="${data ? data.seller_end_time_str : ''}"></div>
                            <div class="plus-separator text-center"><i class="fas fa-arrow-right"></i></div>
                            <div><input type="number" name="delivery_day[]" class="input-box-style small text-center" value="${data ? data.deli_plus_days : 0}"></div>
                            <div><input type="time" name="delivery_start[]" class="input-box-style" value="${data ? data.start_deli_time_str : ''}"></div>
                            <div><input type="time" name="delivery_end[]" class="input-box-style" value="${data ? data.end_deli_time_str : ''}"></div>
                            
                            <div class="d-flex align-items-center justify-content-center" style="gap: 8px;">
                                <label class="modern-switch" style="flex-shrink: 0;">
                                    <input type="checkbox" class="toggle-control" ${data && data.status == 1 ? 'checked' : ''}>
                                    <input type="hidden" name="is_active[]" class="status-value" value="${data && data.status == 1 ? 1 : 0}">
                                    <span class="slider round"></span>
                                </label>
                                <button type="button" class="btn-icon-remove remove-row-btn" style="flex-shrink: 0; padding: 0; border: none; background: none;">
                                    <i class="fas fa-trash-alt text-danger"></i>
                                </button>
                            </div>
                        </div>`;

            $('#slot-container').append(html);
        }

        // ปุ่มย้อนกลับ (แถมให้)
        $('#btn-back-to-list').click(function() {
            $('#delivery-form-view').hide();
            $('#delivery-list-view').fadeIn();
            // เคลียร์ค่าในฟอร์มด้วยก็ดี
        });

        $(document).on('click', '.btn-delete', function(e) {
            e.preventDefault(); // ป้องกันการทำงานซ้ำซ้อน

            var reg_id = $(this).data('id');
            var row = $(this).closest('tr');
            var token = $('meta[name="csrf-token"]').attr('content');

            if (!reg_id) {
                console.error("ID not found!");
                return;
            }

            Swal.fire({
                title: 'ยืนยันการลบ?',
                text: "ข้อมูลจะถูกย้ายไปที่ถังขยะ",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'ลบ',
                cancelButtonText: 'ยกเลิก',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/admin/shipping/region/delete/' + reg_id, // เช็ค URL นี้ให้ตรงกับ web.php
                        type: 'POST', // ใช้ POST แล้วส่ง _method: 'DELETE' จะชัวร์กว่าในบาง Server
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE'
                        },
                        success: function(response) {
                            if (response.status === 'success') {
                                Swal.fire('สำเร็จ', response.message, 'success');
                                row.fadeOut(500);
                            } else {
                                Swal.fire('ผิดพลาด', response.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            console.log(xhr.responseText);
                            Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาดที่ระบบ (Error: ' + xhr.status + ')', 'error');
                        }
                    });
                }
            });
        });


    });
</script>
<script>
    function deleteRecord(delete_url) {
        $('#delete_record_frm').attr('action', delete_url);
        $('#delete_record').modal('show');
    }
</script>

<script>
    $(document).ready(function() {

        // ==========================================
        // 1. ส่วน GENERAL UI (Tabs, Toggles, Display)
        // ==========================================

        // ฟังก์ชันเปิด/ปิดส่วนเลือกจังหวัดตามประเภทการส่ง
        function toggleGeoLocation() {
            var deliveryType = $('input[name="deliveryType"]:checked').val();
            var geoPane = $('.geo-pane');
            var pickupGroup = $('#pickup-location-group');
            var pickupInput = $('#pickup_address');
            var pickupPhoneGroup = $('#pickup-phone-group');


            if (deliveryType === 'pickup') {
                //geoPane.addClass('section-disabled');
                // แสดงแบบสไลด์ลงมา (300ms)
                pickupGroup.stop(true, true).slideDown(300);
                pickupInput.prop('required', true);
                pickupPhoneGroup.stop(true, true).slideDown(300);
            } else {
                //geoPane.removeClass('section-disabled');
                // ซ่อนแบบสไลด์ขึ้นไป
                pickupGroup.stop(true, true).slideUp(300);
                pickupInput.prop('required', false);
                pickupPhoneGroup.stop(true, true).slideUp(300);
            }
        }

        // เรียกใช้งานฟังก์ชันเปิด/ปิด ครั้งแรกและเมื่อมีการเปลี่ยนค่า
        toggleGeoLocation();
        $('input[name="deliveryType"]').on('change', toggleGeoLocation);


        // Badge Condition (แสดงรายละเอียดเมื่อเลือก Radio)
        $('.badge-condition .form-group input[name="badge_condition"]').click(function() {
            $('.badge-condition .form-group').find('.box-detail').hide();
            if ($(this).is(':checked')) {
                $(this).parents('.radio-wrap').next('.box-detail').show();
            }
        });

        // Dimension Weight Toggle
        $("#chkb_dimension_weight").on('click', function() {
            $('#dimension_weight_container').toggle($(this).prop("checked"));
        });
        // Init Dimension Weight state
        $('#dimension_weight_container').toggle($("#chkb_dimension_weight").prop("checked"));

        // Import Location Selector
        $(document).on('click', '.select_import_location', function() {
            if ($(this).val() === 'local') {
                $('#import_server').addClass('d-none');
                $('#import_local').removeClass('d-none');
            } else {
                $('#import_local').addClass('d-none');
                $('#import_server').removeClass('d-none');
            }
        });

        // ลบขอบแดง (Validation Error) เมื่อมีการพิมพ์แก้ไข
        $(document).on('input change', '#region_name, .slot-row input', function() {
            if ($(this).val() !== '') {
                $(this).css('border', '').removeClass('is-invalid');
            }
        });


        // ==========================================
        // 2. ส่วน SAVE LOGIC & DUPLICATE CHECK (ตรวจสอบค่าว่าง + เขตซ้ำ)
        // ==========================================

        var duplicatedSubDistrictIds = [];
        var conflictProvinceIds = [];
        var conflictDistrictIds = [];

        $('#btn_save').on('click', function(e) {
            // 1. หยุดการทำงานปกติของปุ่มทันที
            e.preventDefault();
            console.log("--- 1. เริ่มต้นการกดปุ่ม Save ---");

            var btn = $(this);
            var form = btn.closest('form');


            // ** ลบบรรทัดที่เช็ค delivery-form-view ออกชั่วคราว เพื่อป้องกันการข้าม **
            // if ($('#delivery-form-view').is(':hidden')) { ... }

            let isValid = true;
            let errorMsg = "";

            // --- 2.1 ตรวจสอบค่าว่าง (Validation) ---
            console.log("--- 2. เริ่มตรวจสอบ Validation ---");
            // --- เช็คเฉพาะปุ่ม Tab "delivery_time_tab" เท่านั้น ---
            var isDeliveryTab = $('#delivery_time').hasClass('active');

            console.log("Check Delivery Tab Active: ", isDeliveryTab);

            if (isDeliveryTab) {

                // เช็คชื่อเขต
                let regionName = $('#region_name');
                if ($.trim(regionName.val()) === '') {
                    regionName.addClass('is-invalid').css('border', '1px solid red');
                    isValid = false;
                    errorMsg = "กรุณากรอก 'ชื่อเขตการขาย'";
                }
                if( $('input[name="deliveryType"]:checked').val() === 'pickup' ) {
                    // เช็คที่อยู่จัดส่ง (กรณี Pickup)
                 let pickupAddress = $('#pickup_address');
                    if ($.trim(pickupAddress.val()) === '') {
                        pickupAddress.addClass('is-invalid').css('border', '1px solid red');
                        isValid = false;
                        errorMsg = "กรุณากรอก 'ที่อยู่จัดส่ง'";
                    }

                    let pickup_phone = $('#pickup_phone');
                    if ($.trim(pickup_phone.val()) === '') {
                        pickup_phone.addClass('is-invalid').css('border', '1px solid red');
                        isValid = false;
                        errorMsg = "กรุณากรอก 'เบอร์โทรศัพท์จัดส่ง'";
                    }
                }
               

                // เช็คตารางรอบจัดส่ง
                $('.slot-row').each(function() {
                    let inputs = $(this).find('input[type="time"], input[type="number"]');
                    inputs.each(function() {
                        if (!$(this).val()) {
                            isValid = false;
                            $(this).css('border', '1px solid red');
                            if (errorMsg === "") errorMsg = "กรุณากรอกข้อมูลรอบการจัดส่งให้ครบทุกช่อง";
                        }
                    });
                });

                if (!isValid) {
                    console.log("Validation Failed: " + errorMsg);
                    alert(errorMsg);
                    // เลื่อนหน้าจอไปหา error
                    let errorEl = $(".is-invalid, input[style*='border: 1px solid red']").first();
                    if (errorEl.length) $('html, body').animate({
                        scrollTop: errorEl.offset().top - 100
                    }, 500);
                    return false;
                }

                // --- 2.2 ส่ง AJAX ไปเช็คเขตซ้ำ ---
                console.log("--- 3. Validation ผ่าน -> กำลังส่ง AJAX ไปเช็คซ้ำ ---");

                var originalBtnText = btn.html();
                btn.html('<i class="fa fa-circle-o-notch fa-spin"></i> กำลังตรวจสอบ...');
                btn.addClass('disabled').css('pointer-events', 'none');

                var formData = form.serialize();

                $.ajax({
                    url: '/admin/ajax/shipping/check-duplicate-zone', // *** ตรวจสอบ URL นี้ว่าถูกต้อง 100% ***
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        console.log("--- 4. AJAX Success: ได้รับผลตอบกลับ ---", response);

                        if (response.is_duplicate) {
                            console.log(">> พบข้อมูลซ้ำ! กำลังเปิด Modal");
                            // === กรณี: เจอข้อมูลซ้ำ ===
                            duplicatedSubDistrictIds = response.duplicate_ids || [];
                            conflictProvinceIds = response.conflict_province_ids || [];
                            conflictDistrictIds = response.conflict_district_ids || [];

                            var html = '';
                            // ตรวจสอบว่า response.details มีข้อมูลไหม
                            if (response.details && response.details.length > 0) {
                                $.each(response.details, function(index, item) {
                                    html += `<tr>
                                                <td>${item.location_name}</td>
                                                <td class="text-danger text-right">${item.conflict_profile_name}</td>
                                            </tr>`;
                                });
                            } else {
                                html = '<tr><td colspan="2">พบข้อมูลซ้ำ แต่ไม่ได้รับรายละเอียด</td></tr>';
                            }

                            $('#duplicate-list-body').html(html);
                            $('#duplicateZoneModal').modal('show'); // สั่งเปิด Modal

                            // คืนค่าปุ่ม Save
                            btn.html(originalBtnText).removeClass('disabled').css('pointer-events', 'auto');
                        } else {
                            console.log(">> ไม่พบข้อมูลซ้ำ -> ทำการบันทึก (Submit)");
                            // === กรณี: ไม่ซ้ำ ===
                            submitForm(form, btn);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("--- AJAX Error ---");
                        console.error("Status: " + status);
                        console.error("Error: " + error);
                        console.error(xhr.responseText);

                        alert('เกิดข้อผิดพลาดในการตรวจสอบข้อมูล (ดู Console เพื่อเช็ค Error)');
                        btn.html(originalBtnText).removeClass('disabled').css('pointer-events', 'auto');
                    }
                });
            } else {
                // กรณีอยู่ Tab อื่นที่ไม่ใช่หน้าตั้งค่ารอบส่ง
                console.log("--- อยู่หน้าอื่น: บันทึกทันที ---");
                submitForm(form, btn);
            }
        });



        // ==========================================
        // 3. ส่วน MODAL ACTIONS (จัดการเมื่อกดปุ่มใน Modal)
        // ==========================================

        // Option A: "แย่งมาเป็นของตัวเอง" (Force Overwrite) -> ส่งค่าพิเศษไปบอก Server
        $('#btn-force-take-zone').click(function() {
            var form = $('#btn_save').closest('form');
            var btn = $('#btn_save');

            // เรียกใช้ SweetAlert2 เพื่อยืนยัน
            Swal.fire({
                title: 'ยืนยันการดึงพื้นที่?',
                text: "พื้นที่ที่ซ้ำซ้อนจะถูกย้ายมาอยู่ในโซนนี้ทันที ยืนยันหรือไม่?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745', // สีเขียว
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'ยืนยัน, ดึงพื้นที่เลย!',
                cancelButtonText: 'ยกเลิก',
                reverseButtons: true // ให้ปุ่มยกเลิกอยู่ซ้าย ปุ่มยืนยันอยู่ขวา
            }).then((result) => {
                if (result.isConfirmed) {
                    // 1. เพิ่ม Input พิเศษ force_overwrite_duplicate = 1
                    // เช็คก่อนว่ามี input นี้อยู่หรือยัง ถ้ามีแล้วให้เปลี่ยนค่า ถ้าไม่มีให้สร้างใหม่
                    var forceInput = form.find('input[name="force_overwrite_duplicate"]');
                    if (forceInput.length === 0) {
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'force_overwrite_duplicate',
                            value: '1'
                        }).appendTo(form);
                    } else {
                        forceInput.val('1');
                    }

                    // 2. ปิด Modal เดิม
                    $('#duplicateZoneModal').modal('hide');

                    // 3. ส่งข้อมูล
                    submitForm(form, btn);
                }
            });
        });

        // Option B: "ลบรายการที่ซ้ำออก" (Uncheck Checkbox) และเคลียร์ Parent
        $('#btn-remove-local-duplicate').click(function() {
            console.log('Provinces to remove:', conflictProvinceIds);

            // --- 3. เอาติ๊ก "จังหวัด" ออก ---
            if (conflictProvinceIds && conflictProvinceIds.length > 0) {
                $.each(conflictProvinceIds, function(i, pId) {
                    $('input[value="' + pId + '"]').prop('checked', false);
                });
            }

            // --- 4. เอาติ๊ก "อำเภอ" ออก ---
            if (conflictDistrictIds && conflictDistrictIds.length > 0) {
                $.each(conflictDistrictIds, function(i, dId) {
                    $('input[value="' + dId + '"]').prop('checked', false);
                });
            }

            // --- 5. เอาติ๊ก "ตำบล" ออก ---
            if (duplicatedSubDistrictIds && duplicatedSubDistrictIds.length > 0) {
                $.each(duplicatedSubDistrictIds, function(i, sId) {
                    $('input[type="checkbox"][value="' + sId + '"]').prop('checked', false).trigger('change');

                    // ซ่อนแจ้งเตือน text สีแดง (ถ้ามี)
                    $('.used-info-remove[data-subdistrict-id="' + sId + '"]').hide();
                });
            }

            $('#duplicateZoneModal').modal('hide');

            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true
            });

            Toast.fire({
                icon: 'success',
                title: 'ปลดการเลือกพื้นที่ซ้ำเรียบร้อยแล้ว'
            });
        });



        // คลิกที่ข้อความสีแดง "(ใช้งานโดย...)" เพื่อลบรายการนั้น
        // $(document).on('click', '.used-info-remove', function(e) {
        //     e.preventDefault();
        //     e.stopPropagation();
        //     if (confirm('ต้องการเลิกเลือกตำบลนี้ใช่หรือไม่?')) {
        //         var subId = $(this).data('subdistrict-id');
        //         var $chk = $('input[type="checkbox"][value="' + subId + '"]');
        //         $chk.prop('checked', false).trigger('change');
        //         $(this).hide();
        //     }
        // });


        // ==========================================
        // 4. HELPER FUNCTION: SUBMIT FORM
        // ==========================================
        function submitForm(form, btn) {
            btn.html('<i class="fa fa-circle-o-notch fa-spin"></i> กำลังบันทึก...');
            btn.addClass('disabled').css('pointer-events', 'none');

            $(form)
                .find('.province-checkbox:not(:checked), .district-checkbox:not(:checked), .subdistrict-checkbox:not(:checked)')
                .prop('disabled', true);

            if (btn.attr('name')) {
                $('<input>').attr({
                    type: 'hidden',
                    name: btn.attr('name'),
                    value: btn.val()
                }).appendTo(form);
            }

            setTimeout(function() {
                console.log($(form).serialize());
                let subIds = [];

                $(form).find('.subdistrict-checkbox:checked').each(function() {
                    subIds.push($(this).val());
                });

                // ส่งเป็น CSV ก้อนเดียว
                $('#selected_subdistricts').val(subIds.join(','));
                $(form).find('.subdistrict-checkbox').prop('disabled', true);
                form.trigger('submit');
            }, 100);
        }




    }); // End Document Ready
</script>

@stop