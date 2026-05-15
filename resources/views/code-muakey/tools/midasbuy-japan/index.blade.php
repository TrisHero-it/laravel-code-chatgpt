@extends("code-muakey.layouts.app")
@section('title', 'Midasbuy Japan')
@section('content')
<div class="container mt-5">
    <div class="d-flex justify-content-between">
        <div>
            <h3>
                Danh sách đơn hàng MidasBuy Japan
            </h3>
        </div>
        <div class="d-flex" style="gap: 8px; height: 42px;">
            <a href="{{ route('manager-tools') }}" class="btn btn-secondary">Dashboard</a>
            <a href="{{ route('midasbuy-japan.create') }}" class="btn btn-primary">Thêm đơn hàng</a>
        </div>
    </div>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_GET['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Form tìm kiếm -->
    <div class="card mt-3 mb-3">
        <div class="card-body">
            <form method="GET" action="" class="d-flex gap-2 align-items-end">
                <div class="flex-grow-1">
                    <label for="search" class="form-label">Tìm kiếm đơn hàng</label>
                    <input type="text"
                        class="form-control"
                        id="search"
                        name="search"
                        placeholder="Nhập Order ID, UID"
                        value="">
                </div>
                <div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </button>
                </div>
            </form>
        </div>
    </div>

    <table class="table mt-3">
        <thead>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Order ID</th>
                <th scope="col">UID</th>
                <th scope="col">Card</th>
                <th scope="col">Trạng thái</th>
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
                    <td colspan="8" class="text-center">Không có đơn hàng nào</td>
                </tr>
                <?php
            } else {
                foreach ($orders as $order) {
                    $s = $order['status'] ?? 'pending';
                    if ($s === 'success') {
                        $statusClass = 'success';
                        $statusText = 'Thành công';
                    } elseif ($s === 'cancelled') {
                        $statusClass = 'secondary';
                        $statusText = 'Đã huỷ';
                    } elseif ($s === 'refunded') {
                        $statusClass = 'info';
                        $statusText = 'Đã hoàn tiền';
                    } else {
                        $statusClass = 'warning';
                        $statusText = 'Đang chờ';
                    }
                ?>
                    <tr>
                        <td><?php echo $order['id'] ?></td>
                        <td><?php echo !empty($order['order_id']) ? htmlspecialchars($order['order_id']) : '<span class="text-muted">-</span>' ?></td>
                        <td><?php echo htmlspecialchars($order['uid'] ?? 'N/A') ?></td>
                        <td><strong><?php echo htmlspecialchars($order['card'] ?? 'N/A') ?></strong></td>
                        <td><span class="badge bg-<?php echo $statusClass ?>"><?php echo $statusText ?></span></td>
                        <td><?php echo isset($order['sales_agent_id']) && $order['sales_agent_id'] !== null && $order['sales_agent_id'] !== '' ? (int)$order['sales_agent_id'] : '—' ?></td>
                        <td><?php echo !empty($order['created_at']) ? date('d/m/Y H:i', strtotime($order['created_at'])) : '-' ?></td>
                        <td>
                            <div class="d-flex" style="gap: 5px;">
                                <?php if (!empty($order['image'])): ?>
                                    <button type="button"
                                        class="btn btn-sm btn-outline-primary"
                                        title="Xem ảnh"
                                        data-bs-toggle="modal"
                                        data-bs-target="#imageModal<?php echo $order['id'] ?>">
                                        <i class="fas fa-image"></i>
                                    </button>

                                    <!-- Modal hiển thị ảnh -->
                                    <div class="modal fade" id="imageModal<?php echo $order['id'] ?>" tabindex="-1" aria-labelledby="imageModalLabel<?php echo $order['id'] ?>" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="imageModalLabel<?php echo $order['id'] ?>">Ảnh đơn hàng #<?php echo $order['id'] ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body text-center">
                                                    <img src="{{ asset($order['image']) }}"
                                                        class="img-fluid"
                                                        alt="Ảnh đơn hàng"
                                                        style="max-height: 70vh; border-radius: 8px;">
                                                </div>
                                                <div class="modal-footer">
                                                    <a href="{{ asset($order['image']) }}" target="_blank" class="btn btn-primary">
                                                        <i class="fas fa-external-link-alt"></i> Mở trong tab mới
                                                    </a>
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <a href="{{ route('midasbuy-japan.edit', ['midasbuy_japan' => $order['id']]) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form action="{{ route('midasbuy-japan.destroy', ['midasbuy_japan' => $order['id']]) }}" method="post">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa đơn hàng này không?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
@endsection