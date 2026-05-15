@extends("code-muakey.layouts.app")
@section('title', 'Sửa đơn hàng MidasBuy Japan')
@section('content')
<div class="container mt-5">
    <h3>
        Sửa đơn hàng WWM
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

    @if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    <form action="{{ route('wwm-order.update', ['wwm_order' => $order['id']]) }}" method="post" id="wwmOrderForm" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($order['id'] ?? '') ?>">
        <div class="form-group mt-3">
            <label for="order_id">Mã đơn hàng <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="order_id" name="order_id" placeholder="Nhập mã đơn hàng" value="<?php echo htmlspecialchars($order['order_id'] ?? '') ?>" required>
        </div>
        <div class="form-group mt-3">
            <label for="uid">UID <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="uid" name="uid" placeholder="Nhập UID" value="<?php echo htmlspecialchars($order['uid'] ?? '') ?>" required>
        </div>
        <div class="form-group mt-3">
            <label for="product_id">Product ID <span class="text-danger">*</span></label>
            <select class="form-control" id="product_id" name="product_id" required>
                <option value="">-- Chọn sản phẩm --</option>
                <?php foreach ($iosProducts as $product): ?>
                    <option value="<?php echo htmlspecialchars($product['goodsid']) ?>"
                        <?php echo (isset($order['product_id']) && $order['product_id'] == $product['goodsid']) ? 'selected' : '' ?>>
                        <?php echo htmlspecialchars($product['goodsinfo']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group mt-3">
            <label for="sales_agent_id">Sales Agent ID <span class="text-muted">(Tùy chọn)</span></label>
            <input type="number" class="form-control" id="sales_agent_id" name="sales_agent_id" placeholder="Để trống nếu không có" value="<?php echo isset($order['sales_agent_id']) && $order['sales_agent_id'] !== '' && $order['sales_agent_id'] !== null ? (int)$order['sales_agent_id'] : '' ?>" min="1" step="1" style="max-width: 200px;">
        </div>
        <div class="form-group mt-3">
            <label for="status">Trạng thái <span class="text-danger">*</span></label>
            <select class="form-control" id="status" name="status">
                <option value="">Trạng thái khác</option>
                <option value="pending" <?php echo (isset($order['status']) && $order['status'] == 'pending') ? 'selected' : '' ?>>Đang chờ</option>
                <option value="processing" <?php echo (isset($order['status']) && $order['status'] == 'processing') ? 'selected' : '' ?>>Đang xử lý</option>
                <option value="completed" <?php echo (isset($order['status']) && $order['status'] == 'completed') ? 'selected' : '' ?>>Hoàn thành</option>
                <option value="cancelled" <?php echo (isset($order['status']) && $order['status'] == 'cancelled') ? 'selected' : '' ?>>Đã hủy</option>
            </select>
        </div>
        <div class="form-group mt-3">
            <label for="image">Image <span class="text-muted">(Tùy chọn)</span></label>
            <?php if (!empty($order['image'])): ?>
                <div class="mb-2">
                    <img src="<?php echo htmlspecialchars($order['image']) ?>" alt="Image" style="max-width: 300px; max-height: 200px; border: 1px solid #ddd; border-radius: 4px;">
                    <div class="mt-1">
                        <small class="text-muted">Ảnh hiện tại: <?php echo htmlspecialchars(basename($order['image'])) ?></small>
                    </div>
                </div>
            <?php endif; ?>
            <input type="file"
                class="form-control"
                id="image"
                name="image"
                accept="image/*">
            <small class="form-text text-muted">Chọn file ảnh (có thể để trống, chỉ upload khi muốn thay đổi)</small>
        </div>

        <div class="d-flex" style="gap: 8px">
            <button type="submit" class="btn btn-primary mt-3" id="updateWwmOrderButton">Cập nhật đơn hàng</button>
            <a href="{{ route('wwm-order.index') }}" class="btn btn-secondary mt-3">Quay lại</a>
        </div>
    </form>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
@endsection