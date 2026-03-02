@extends('layouts.app')

@section('header_style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .beam-payment-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .payment-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .order-details {
            background: white;
            padding: 20px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .beam-pay-button {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s ease;
        }
        .beam-pay-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4);
        }
        .beam-pay-button:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
        }
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s ease-in-out infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .price-summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .price-summary .row {
            margin-bottom: 8px;
        }
        .price-summary .text-danger {
            color: #dc3545 !important;
        }
        .price-summary .fw-bold {
            font-weight: bold;
        }
        .price-summary hr {
            border-color: #dee2e6;
            margin: 15px 0;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-success {
            color: #3c763d;
            background-color: #dff0d8;
            border-color: #d6e9c6;
        }
        .alert-danger {
            color: #a94442;
            background-color: #f2dede;
            border-color: #ebccd1;
        }
        .alert-info {
            color: #31708f;
            background-color: #d9edf7;
            border-color: #bce8f1;
        }
    </style>
@endsection

@section('content')
<div class="beam-payment-container">
    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> {{ session('info') }}
                </div>
            @endif

            <div class="text-center">
                <button id="beamPayButton" class="beam-pay-button">
                    <span id="buttonText">ชำระเงินด้วย {{ $payment_method_name }}</span>
                    <span id="loadingSpinner" class="loading-spinner" style="display: none;"></span>
                </button>
            </div>
            
            <div class="payment-info">
                <h3>ข้อมูลการสั่งซื้อ</h3>
                <p><strong>เลขที่คำสั่งซื้อ:</strong> {{ $orderInfo->formatted_id }}</p>
                <p><strong>วันที่สั่งซื้อ:</strong> {{ date('d/m/Y H:i', strtotime($orderInfo->created_at)) }}</p>
                <p><strong>ยอดรวมสุทธิ:</strong> {{ number_format($total_with_transaction_fee, 2) }} บาท</p>
            </div>

            {{-- <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>หมายเหตุ:</strong> เมื่อกดปุ่มชำระเงิน ระบบจะนำคุณไปยังหน้า checkout ของ {{ $payment_method_name }} โดยตรง เพื่อความปลอดภัยตามมาตรฐาน PCI
            </div> --}}
        </div>
    </div>
</div>
@endsection

@section('footer_scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const payButton = document.getElementById('beamPayButton');
    const buttonText = document.getElementById('buttonText');
    const loadingSpinner = document.getElementById('loadingSpinner');

    payButton.addEventListener('click', function() {
        // Call beam API (support test mode)
        const testMode = {{ isset($testMode) && $testMode ? 'true' : 'false' }};
        const testPaymentMethod = '{{ $testPaymentMethod ?? '' }}';
        const fetchOptions = {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        };
        if (testMode) {
            fetchOptions.body = JSON.stringify({ test_mode: true, test_payment_method: testPaymentMethod });
        }
        
        fetch('{{ action('Checkout\CartController@createBeamOrder', $orderInfo->formatted_id) }}', fetchOptions)
        .then(response => {
            return response.json();
        })
        .then(data => {
            console.log('Beam API Response:', data);
            if (data.success && data.paymentLink) {
                // Direct redirect to Beam Payment instead of iframe
                window.location.href = data.paymentLink;
            } else {
                let errorMsg = 'เกิดข้อผิดพลาดในการสร้างคำสั่งชำระเงิน';
                if (data.error) errorMsg += '\n\nรายละเอียด: ' + data.error;
                if (data.debug) console.log('Debug Info:', data.debug);
                alert(errorMsg);
            }
        })
        .catch(error => {
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อ\n\nรายละเอียด: ' + error.message);
        });
    });

    // ตรวจสอบสถานะการชำระเงินทันทีเมื่อโหลดหน้า (กรณีที่ผู้ใช้กลับมาหน้าเดิม)
    function checkInitialPaymentStatus() {
        console.log('Checking initial payment status...');
        
        fetch('{{ url('/checkout/beam/status-api/'.$orderInfo->formatted_id) }}', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.payment_status === 'completed') {
                // ถ้าชำระเงินเสร็จแล้ว ให้ redirect ไปหน้า thanks ทันที
                window.location.href = '{{ url('/checkout/thanks/'.$orderInfo->formatted_id) }}';
            }
        })
        .catch(error => {
            console.log('Initial payment status check error:', error);
        });
    }

    // ตรวจสอบสถานะการชำระเงินเมื่อโหลดหน้า
    checkInitialPaymentStatus();
});
</script>
@endsection
