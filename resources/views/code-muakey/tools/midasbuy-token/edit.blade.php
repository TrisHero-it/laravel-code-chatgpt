@extends("code-muakey.layouts.app")
@section('title', 'Sửa đơn hàng MidasBuy Token')
@section('content')

<div class="container mt-5">
    <h3>
        Sửa đơn hàng MidasBuy Token
    </h3>
    @if (session('success'))
    <div class="alert alert-success" id="successNotification">
        {{ session('success') }}
    </div>

    <script>
        setTimeout(function() {
            document.getElementById('successNotification').style.display = 'none';
        }, 2000);
    </script>
    @endif

    @if ($errors->any())

    <div class="alert alert-danger">

        <ul class="mb-0">

            @foreach ($errors->all() as $error)

            <li>{{ $error }}</li>

            @endforeach

        </ul>

    </div>

    @endif
    <div class="container mt-5">
        <hr class="my-4">
        <form action="{{ route('midasbuy-token.update', $midasbuyToken['id']) }}" method="POST" id="form_order" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group mt-3">
                <label for="order_id">Mã đơn hàng (Order ID)</label>
                <input type="text" class="form-control" id="order_id" name="order_id" placeholder="Mã đơn hàng từ MidasBuy (số, tùy chọn)" value="{{ old('order_id', $midasbuyToken['order_id']) }}">
            </div>
            <div class="form-group mt-3">
                <label for="uid">UID <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="uid" name="uid" placeholder="Nhập UID hoặc dán để phân tích" value="{{ old('uid', $midasbuyToken['uid']) }}" required>
            </div>
            <div class="form-group mt-3">
                <label for="token">Token <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="token" name="token" placeholder="Nhập Token hoặc dán để phân tích" value="{{ old('token', $midasbuyToken['token']) }}" required>
            </div>
            <div class="form-group mt-3">
                <label for="card">Code</label>
                <input type="text" class="form-control" id="code" name="code" placeholder="Nhập code" maxlength="30" value="{{ old('code', $midasbuyToken['code']) }}">
            </div>
            <div class="form-group mt-3">
                <label for="image">Image</label>
                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                <small class="form-text text-muted">Chọn file ảnh mới để thay thế (để trống nếu giữ nguyên ảnh hiện tại)</small>
            </div>
            <div class="form-group mt-3">
                <label for="sales_agent_id">Sales Agent ID <span class="text-muted">(Tùy chọn)</span></label>
                <input type="number" class="form-control" id="sales_agent_id" name="sales_agent_id" placeholder="Nhập ID đại lý (để trống nếu không có)" value="{{ old('sales_agent_id', $midasbuyToken['sales_agent_id']) }}" min="1" step="1" style="max-width: 200px;">
            </div>
            <div class="form-group mt-3">
                <label for="status">Trạng thái</label>
                <select class="form-control" id="status" name="status">
                    <option value="pending">Đang chờ</option>
                    <option value="success">Thành công</option>
                    <option value="cancelled">Đã huỷ</option>
                </select>
            </div>

            <div class="d-flex" style="gap: 8px">
                <button type="submit" class="btn btn-primary mt-3">Cập nhập đơn hàng</button>
                <a href="{{ route('midasbuy-token.index') }}" class="btn btn-secondary mt-3">Quay lại</a>
            </div>
        </form>
    </div>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    @endsection