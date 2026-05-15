@extends("code-muakey.layouts.app")
@section('title', 'Danh sách MidasBuy Token')
@section('content')

<div class="container mt-5">
    <div class="d-flex justify-content-between">
        <div>
            <h3>
                Danh sách đơn hàng MidasBuy Japan Token
            </h3>
        </div>
        <div class="d-flex" style="gap: 8px; height: 42px;">
            <a href="{{ route('manager-tools') }}" class="btn btn-secondary">Dashboard</a>
            <a href="{{ route('midasbuy-token.create') }}" class="btn btn-primary">Thêm đơn hàng</a>
        </div>
    </div>
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
                        placeholder="Nhập Order ID, UID, Code..."
                        value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                </div>
                <div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </button>
                </div>
            </form>
        </div>
    </div>
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
    <table class="table mt-3">
        <thead>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Order ID</th>
                <th scope="col">UID</th>
                <th scope="col">Code</th>
                <th scope="col">Tokens</th>
                <th scope="col">Trạng thái</th>
                <th scope="col">Sale Agent ID</th>
                <th scope="col">Ngày tạo</th>
                <th scope="col">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (empty($midasbuyTokens)) {
            ?>
                <tr>
                    <td colspan="9" class="text-center">Không có đơn hàng nào</td>
                </tr>
                <?php
            } else {
                foreach ($midasbuyTokens as $midasbuyToken) {
                    $s = $midasbuyToken['status'] ?? 'pending';
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
                        <td><?php echo $midasbuyToken['id'] ?></td>
                        <td><?php echo !empty($midasbuyToken['order_id']) ? htmlspecialchars($midasbuyToken['order_id']) : '<span class="text-muted">-</span>' ?></td>
                        <td><?php echo htmlspecialchars($midasbuyToken['uid'] ?? 'N/A') ?></td>
                        <td><strong><?php echo htmlspecialchars($midasbuyToken['code'] ?? 'N/A') ?></strong></td>
                        <td><?php echo htmlspecialchars($midasbuyToken['token'] ?? 'N/A') ?></td>
                        <td><span class="badge bg-<?php echo $statusClass ?>"><?php echo $statusText ?></span></td>
                        <td><?php echo isset($midasbuyToken['sale_agent_id']) && $midasbuyToken['sale_agent_id'] !== null && $midasbuyToken['sale_agent_id'] !== '' ? (int)$midasbuyToken['sale_agent_id'] : '—' ?></td>
                        <td><?php echo !empty($midasbuyToken['created_at']) ? date('d/m/Y H:i', strtotime($midasbuyToken['created_at'])) : '-' ?></td>
                        <td>
                            <div class="d-flex" style="gap: 5px;">
                                <?php if (!empty($midasbuyToken['image'])): ?>
                                    <button type="button"
                                        class="btn btn-sm btn-outline-primary"
                                        title="Xem ảnh"
                                        data-bs-toggle="modal"
                                        data-bs-target="#imageModal<?php echo $midasbuyToken['id'] ?>">
                                        <i class="fas fa-image"></i>
                                    </button>

                                    <!-- Modal hiển thị ảnh -->
                                    <div class="modal fade" id="imageModal<?php echo $midasbuyToken['id'] ?>" tabindex="-1" aria-labelledby="imageModalLabel<?php echo $midasbuyToken['id'] ?>" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="imageModalLabel<?php echo $midasbuyToken['id'] ?>">Ảnh đơn hàng #<?php echo $midasbuyToken['id'] ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body text-center">
                                                    <img src="{{ asset($midasbuyToken['image']) }}"
                                                        class="img-fluid"
                                                        alt="Ảnh đơn hàng"
                                                        style="max-height: 70vh; border-radius: 8px;">
                                                </div>
                                                <div class="modal-footer">
                                                    <a href="{{ asset($midasbuyToken['image']) }}" target="_blank" class="btn btn-primary">
                                                        <i class="fas fa-external-link-alt"></i> Mở trong tab mới
                                                    </a>
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <a href="{{ route('midasbuy-token.edit', $midasbuyToken['id']) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <?php if ($s === 'cancelled'): ?>
                                    <a onclick="return confirm('Bạn có chắc chắn muốn hoàn tiền cho đơn hàng này không?')" href="?act=midas-japan-order-refund&id=<?php echo $midasbuyToken['id'] ?>" class="btn btn-info btn-sm">
                                        <i class="fas fa-money-bill-wave"></i> Hoàn tiền
                                    </a>
                                <?php endif; ?>
                                <form action="{{ route('midasbuy-token.destroy', $midasbuyToken['id']   ) }}" onsubmit="return confirm('Bạn có chắc chắn muốn xóa đơn hàng này không?')" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
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
    <?php if (isset($totalPages) && $totalPages > 1): ?>
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center mt-4">
                <?php
                $currentPage = isset($currentPage) ? $currentPage : (isset($_GET['page']) ? (int)$_GET['page'] : 1);
                $statusParam = isset($_GET['status']) && $_GET['status'] != '' ? '&status=' . htmlspecialchars($_GET['status']) : '';
                $searchParam = isset($_GET['search']) && $_GET['search'] != '' ? '&search=' . urlencode($_GET['search']) : '';

                if ($currentPage > 1):
                ?>
                    <li class="page-item">
                        <a class="page-link" href="?act=midas-japan-orders&page=<?php echo $currentPage - 1 ?><?php echo $statusParam ?><?php echo $searchParam ?>">Trước</a>
                    </li>
                <?php else: ?>
                    <li class="page-item disabled">
                        <span class="page-link">Trước</span>
                    </li>
                <?php endif; ?>

                <?php
                $startPage = max(1, $currentPage - 2);
                $endPage = min($totalPages, $currentPage + 2);

                if ($startPage > 1):
                ?>
                    <li class="page-item">
                        <a class="page-link" href="?act=midas-japan-orders&page=1<?php echo $statusParam ?><?php echo $searchParam ?>">1</a>
                    </li>
                    <?php if ($startPage > 2): ?>
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <li class="page-item <?php echo $i == $currentPage ? 'active' : '' ?>">
                        <a class="page-link" href="?act=midas-japan-orders&page=<?php echo $i ?><?php echo $statusParam ?><?php echo $searchParam ?>"><?php echo $i ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($endPage < $totalPages): ?>
                    <?php if ($endPage < $totalPages - 1): ?>
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    <?php endif; ?>
                    <li class="page-item">
                        <a class="page-link" href="?act=midas-japan-orders&page=<?php echo $totalPages ?><?php echo $statusParam ?><?php echo $searchParam ?>"><?php echo $totalPages ?></a>
                    </li>
                <?php endif; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?act=midas-japan-orders&page=<?php echo $currentPage + 1 ?><?php echo $statusParam ?><?php echo $searchParam ?>">Sau</a>
                    </li>
                <?php else: ?>
                    <li class="page-item disabled">
                        <span class="page-link">Sau</span>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        <div class="text-center text-muted mb-3">
            <small>Trang <?php echo $currentPage ?> / <?php echo $totalPages ?></small>
        </div>
    <?php endif; ?>
</div>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

@endsection