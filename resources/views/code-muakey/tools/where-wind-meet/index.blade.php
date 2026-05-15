@extends("code-muakey.layouts.app")
@section('title', 'Where Wind Meet')
@section('content')
<div class="container mt-5">
    <div class="d-flex justify-content-between">
        <div>
            <h3>Danh sách wwm_orders</h3>
        </div>
        <div class="d-flex" style="gap: 8px; height: 42px;">
            <a href="{{ route('manager-tools') }}" class="btn btn-secondary">
                <i class="fas fa-home"></i> Dashboard
            </a>
        </div>
    </div>

    <!-- Form tìm kiếm -->
    <div class="card mt-3 mb-3">
        <div class="card-body">
            <form method="GET" action="?" class="d-flex gap-2 align-items-end">
                <input type="hidden" name="act" value="wwm-orders">
                <?php if (isset($_GET['category']) && $_GET['category'] !== ''): ?>
                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($_GET['category']) ?>">
                <?php endif; ?>
                <div class="flex-grow-1">
                    <label for="search_order_id" class="form-label">Tìm kiếm theo Order ID</label>
                    <input type="text"
                        class="form-control"
                        id="search_order_id"
                        name="search_order_id"
                        placeholder="Nhập Order ID để tìm kiếm..."
                        value="<?php echo isset($_GET['search_order_id']) ? htmlspecialchars($_GET['search_order_id']) : '' ?>">
                </div>
                <div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </button>
                    <?php if (isset($_GET['search_order_id']) && $_GET['search_order_id'] !== ''): ?>
                        <a href="?act=wwm-orders<?php echo isset($_GET['category']) && $_GET['category'] !== '' ? '&category=' . urlencode($_GET['category']) : '' ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Xóa
                        </a>
                    <?php endif; ?>
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
                        <td>
                            <?php
                            $productFound = false;
                            // Tìm trong iosProducts (Where Winds Meet)
                            foreach ($iosProducts as $product) {
                                if ($product['goodsid'] == $order['product_id']) {
                                    echo htmlspecialchars($product['goodsinfo']);
                                    $productFound = true;
                                    break;
                                }
                            }
                            // Nếu không tìm thấy, tìm trong productsOneHuman
                            if (!$productFound && isset($productsOneHuman)) {
                                foreach ($productsOneHuman as $product) {
                                    if ($product['goodsid'] == $order['product_id']) {
                                        echo htmlspecialchars($product['goodsinfo']);
                                        $productFound = true;
                                        break;
                                    }
                                }
                            }
                            // Nếu vẫn không tìm thấy, hiển thị product_id
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
                                <a href="{{ route('wwm-order.edit', ['wwm_order' => $order['id']]) }}"
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
if (!empty($wwmOrders)) {
    foreach ($wwmOrders as $order) {
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
                            <img src="<?php echo htmlspecialchars($order['image']) ?>"
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