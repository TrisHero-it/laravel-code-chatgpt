@extends("code-muakey.layouts.app")
@section('title', 'Danh sách Code')
@section('content')

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center">
        <h3>Danh sách code payment</h3>
        <div class="d-flex" style="gap: 8px;">
            <a href="{{ route('token-codes.create') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> Thêm code
            </a>

            <a href="{{ route('midasbuy-token.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>
    <div class="card mt-3 mb-3">
        <div class="card-body">
            <form method="GET" action="" class="d-flex gap-2 align-items-end">
                <div class="flex-grow-1">
                    <label for="search" class="form-label">Tìm kiếm đơn hàng</label>
                    <input type="text"
                        class="form-control"
                        id="search"
                        name="search"
                        placeholder="Code..."
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
    <table class="table mt-3">
        <thead>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Code</th>
                <th scope="col">Token</th>
                <th scope="col">Trạng thái</th>
                <th scope="col">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (empty($tokenCodes)) {
            ?>
                <tr>
                    <td colspan="5" class="text-center">
                        <div class="alert alert-warning mb-0">
                            <strong><i class="fas fa-exclamation-triangle"></i> Cần phải thêm code!</strong>
                            <p class="mb-0 mt-2">Hiện tại chưa có code nào trong hệ thống.</p>
                        </div>
                    </td>
                </tr>
                <?php
            } else {
                foreach ($tokenCodes as $codeItem) {
                    $status = $codeItem->status; // Giả sử 'status' là trường lưu trữ số token còn lại
                    $statusClass = $status == "unused" ? 'success' : 'danger';
                    $statusText = $status == "unused" ? 'Chưa sử dụng' : 'Đã sử dụng';
                ?>
                    <tr>
                        <td><?php echo $codeItem['id'] ?></td>
                        <td><strong><?php echo htmlspecialchars($codeItem['code'] ?? 'N/A') ?></strong></td>
                        <td>
                            <span class="badge bg-<?php echo $statusClass ?>">
                                <?php echo number_format($codeItem->token, 0, '.', '') ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-<?php echo $statusClass ?>"><?php echo $statusText ?></span>
                        </td>
                        <td>
                            <a onclick="return confirm('Bạn có chắc chắn muốn xóa code này không?')"
                                href="?act=payment-code-delete&id=<?php echo $codeItem['id'] ?>"
                                class="btn btn-danger btn-sm">
                                <i class="fas fa-trash"></i> Xóa
                            </a>
                        </td>
                    </tr>
            <?php
                }
            }
            ?>
        </tbody>
    </table>
    {{ $tokenCodes->links() }}
</div>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

@endsection