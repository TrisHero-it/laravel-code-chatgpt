@extends("code-muakey.layouts.app")
@section('title', 'Identity V')
@section('content')
<div class="container mt-5">
    <div class="d-flex justify-content-between">
        <div>
            <h3>Danh sách đơn hàng Identity V</h3>
        </div>
        <div class="d-flex" style="gap: 8px; height: 42px;">
            <a href="{{ route('identity-order.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Thêm đơn hàng
            </a>
            <a href="{{ route('netease-tools') }}" class="btn btn-secondary">
                <i class="fas fa-home"></i> Dashboard
            </a>
        </div>
    </div>

    <!-- Form tìm kiếm -->
    <div class="card mt-3 mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('identity-order.index') }}" class="d-flex gap-2 align-items-end">
                <div class="flex-grow-1">
                    <label for="search" class="form-label">Tìm kiếm theo Order ID / UID</label>
                    <input type="text"
                        class="form-control"
                        id="search"
                        name="search"
                        placeholder="Nhập Order ID hoặc UID để tìm kiếm..."
                        value="{{ request()->query('search') }}">
                </div>
                <div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </button>
                    @if (request()->query('search'))
                        <a href="{{ route('identity-order.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Xóa
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>
    @if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif
    <table class="table mt-3">
        <thead>
            <tr>
                <th scope="col">Order ID</th>
                <th scope="col">UID</th>
                <th scope="col">Server</th>
                <th scope="col">Product ID</th>
                <th scope="col">Status</th>
                <th scope="col">Sales Agent ID</th>
                <th scope="col">Ngày tạo</th>
                <th scope="col">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (empty($orders)) {
            ?>
                <tr>
                    <td colspan="10" class="text-center">Không có dữ liệu nào</td>
                </tr>
                <?php
            } else {
                foreach ($orders as $order) {
                    $statusClass = '';
                    $statusText = '';
                    switch ($order['status']) {
                        case 'pending':
                            $statusClass = 'warning';
                            $statusText = 'Đang chờ';
                            break;
                        case 'processing':
                            $statusClass = 'info';
                            $statusText = 'Đang xử lý';
                            break;
                        case 'completed':
                            $statusClass = 'success';
                            $statusText = 'Hoàn thành';
                            break;
                        case 'cancelled':
                            $statusClass = 'danger';
                            $statusText = 'Đã hủy';
                            break;
                        default:
                            $statusClass = 'danger';
                            $statusText = $order['status'];
                    }
                ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($order['order_id'] ?? 'N/A') ?></strong></td>
                        <td><strong><?php echo htmlspecialchars($order['uid'] ?? 'N/A') ?></strong></td>
                        <td><?php echo htmlspecialchars($order['server'] ?? '') ?></td>
                        <td>
                            <?php
                            $productFound = false;
                            foreach ($iosProducts as $product) {
                                if ($product['goodsid'] == $order['product_id']) {
                                    echo htmlspecialchars($product['goodsinfo']);
                                    $productFound = true;
                                    break;
                                }
                            }
                            if (!$productFound) {
                                echo htmlspecialchars($order['product_id'] ?? 'N/A');
                            }
                            ?>
                        </td>

                        <td><span class="badge bg-<?php echo $statusClass ?>"><?php echo $statusText ?></span></td>
                        <td><?php echo isset($order['sales_agent_id']) && $order['sales_agent_id'] !== null && $order['sales_agent_id'] !== '' ? (int)$order['sales_agent_id'] : '__' ?></td>
                        <td><?php echo isset($order['created_at']) ? date('d/m/Y H:i', strtotime($order['created_at'])) : 'N/A' ?></td>
                        <td>
                            <div class="d-flex" style="gap: 5px;">
                                <?php if (!empty($order['image'])): ?>
                                    <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#imageModal<?php echo $order['id'] ?>">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                <?php endif; ?>
                                <a href="{{ route('identity-order.edit', ['identity_order' => $order['id']]) }}"
                                    class="btn btn-primary btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </div>
                        </td>
                    </tr>
            <?php
                }
            }
            ?>
        </tbody>
    </table>

    <!-- Phân trang -->
    {{ $orders->links() }}
</div>

<!-- Modals for Image -->
<?php
if (!empty($orders)) {
    foreach ($orders as $order) {
        if (!empty($order['image'])) {
?>
            <!-- Modal for Order ID <?php echo $order['id'] ?> -->
            <div class="modal fade" id="imageModal<?php echo $order['id'] ?>" tabindex="-1" aria-labelledby="imageModalLabel<?php echo $order['id'] ?>" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="imageModalLabel<?php echo $order['id'] ?>">
                                Image - Đơn hàng #<?php echo htmlspecialchars($order['order_id'] ?? $order['id']) ?>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center">
                            <img src="{{ asset($order['image']) }}"
                                alt="Image"
                                class="img-fluid"
                                style="max-height: 70vh; border-radius: 4px;">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        </div>
                    </div>
                </div>
            </div>
<?php
        }
    }
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
@endsection
