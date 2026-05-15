@extends("code-muakey.layouts.app")
@section('title', 'Sửa đơn hàng MidasBuy Japan')
@section('content')



<div class="container mt-5">
    <h3>
        Sửa đơn hàng MidasBuy Japan
    </h3>

    @if ($errors->any())

    <div class="alert alert-danger">

        <ul class="mb-0">

            @foreach ($errors->all() as $error)

            <li>{{ $error }}</li>

            @endforeach

        </ul>

    </div>

    @endif

    <form action="{{ route('midasbuy-japan.update', ['midasbuy_japan' => $order['id']]) }}" method="post" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="id" value="{{ $order['id'] ?? '' }}">
        <div class="form-group mt-3">
            <label for="order_id">Mã đơn hàng (Order ID)</label>
            <input type="text" class="form-control" id="order_id" name="order_id" placeholder="Mã đơn hàng từ MidasBuy (số, tùy chọn)" value="{{ $order['order_id'] ?? '' }}">
        </div>
        <div class="form-group mt-3">
            <label for="uid">UID <span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="uid" name="uid" placeholder="Nhập UID (số)" value="{{ $order['uid'] ?? '' }}" required>
        </div>
        <div class="form-group mt-3">
            <label for="card">Card <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="card" name="card" placeholder="Nhập card (tối đa 30 ký tự)" maxlength="30" value="{{ $order['card'] ?? '' }}" required>
        </div>
        <div class="form-group mt-3">
            <label for="sales_agent_id">Sales Agent ID <span class="text-muted">(Tùy chọn)</span></label>
            <input type="number" class="form-control" id="sales_agent_id" name="sales_agent_id" placeholder="Để trống nếu không có" value="{{ $order['sales_agent_id'] ?? '' }}" min="1" step="1" style="max-width: 200px;">
        </div>
        <div class="form-group mt-3">
            <label for="image">Image</label>
            <input type="file" class="form-control" id="image" name="image" accept="image/*">
            <small class="form-text text-muted">Chọn file ảnh mới để thay thế (để trống nếu giữ nguyên ảnh hiện tại)</small>
        </div>
        <div class="form-group mt-3">
            <label for="status">Trạng thái</label>
            <select class="form-control" id="status" name="status">
                <option value="pending" <?php echo (isset($order['status']) && $order['status'] == 'pending') ? 'selected' : '' ?>>Đang chờ</option>
                <option value="success" <?php echo (isset($order['status']) && $order['status'] == 'success') ? 'selected' : '' ?>>Thành công</option>
                <option value="cancelled" <?php echo (isset($order['status']) && $order['status'] == 'cancelled') ? 'selected' : '' ?>>Đã huỷ</option>
            </select>
        </div>

        <div class="d-flex" style="gap: 8px">
            <button type="submit" class="btn btn-primary mt-3">Cập nhật đơn hàng</button>
            <a href="{{ route('midasbuy-japan.index') }}" class="btn btn-secondary mt-3">Quay lại</a>
        </div>
    </form>
</div>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

@endsection