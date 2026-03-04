<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายการสินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h1 class="mb-3">รายการสินค้า</h1>

    @if($products->isEmpty())
        <div class="alert alert-info">ยังไม่มีสินค้าจากฐานข้อมูล</div>
    @else
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ชื่อ</th>
                    <th>คำอธิบาย</th>
                    <th>ราคา</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                    <tr>
                        <td>{{ $product->id }}</td>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->description }}</td>
                        <td>{{ number_format($product->price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
</body>
</html>