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
            <form method="GET" action="{{ route('token-codes.index') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="search" class="form-label">Tìm kiếm code</label>
                    <input type="text"
                        class="form-control"
                        id="search"
                        name="search"
                        placeholder="Nhập code..."
                        value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label for="token" class="form-label">Token</label>
                    <select name="token" id="token" class="form-control">
                        <option value="">Tất cả token</option>
                        @foreach ($tokenOptions as $tokenValue)
                            <option value="{{ $tokenValue }}" @selected(request('token') == $tokenValue)>
                                {{ number_format($tokenValue, 0, '.', '') }} Tokens
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Trạng thái</label>
                    <select name="status" id="status" class="form-control">
                        <option value="">Tất cả</option>
                        <option value="unused" @selected(request('status') === 'unused')>Chưa sử dụng</option>
                        <option value="used" @selected(request('status') === 'used')>Đã sử dụng</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="fas fa-filter"></i> Lọc
                    </button>
                    @if (request()->hasAny(['search', 'token', 'status']))
                        <a href="{{ route('token-codes.index') }}" class="btn btn-outline-secondary" title="Xóa bộ lọc">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>
            </form>
            @if ($selectedToken !== null)
                <div class="alert alert-info mb-0 mt-3 py-2">
                    <i class="fas fa-info-circle"></i>
                    <strong>{{ number_format($selectedToken, 0, '.', '') }} Tokens:</strong>
                    còn <strong>{{ number_format($unusedCountForToken, 0, '.', '') }}</strong> code chưa sử dụng
                    @if (request()->hasAny(['search', 'status']))
                        <span class="text-muted">(tổng trong DB, không phụ thuộc bộ lọc code/trạng thái khác)</span>
                    @endif
                </div>
            @endif
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
            @forelse ($tokenCodes as $codeItem)
                @php
                    $isUnused = $codeItem->status === 'unused';
                    $statusClass = $isUnused ? 'success' : 'danger';
                    $statusText = $isUnused ? 'Chưa sử dụng' : 'Đã sử dụng';
                @endphp
                <tr>
                    <td>{{ $codeItem->id }}</td>
                    <td><strong>{{ $codeItem->code ?? 'N/A' }}</strong></td>
                    <td>
                        <span class="badge bg-primary">
                            {{ number_format($codeItem->token, 0, '.', '') }}
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-{{ $statusClass }}">{{ $statusText }}</span>
                    </td>
                    <td>
                        <a onclick="return confirm('Bạn có chắc chắn muốn xóa code này không?')"
                            href="?act=payment-code-delete&id={{ $codeItem->id }}"
                            class="btn btn-danger btn-sm">
                            <i class="fas fa-trash"></i> Xóa
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">
                        <div class="alert alert-warning mb-0">
                            @if (request()->hasAny(['search', 'token', 'status']))
                                <strong><i class="fas fa-search"></i> Không tìm thấy code</strong>
                                <p class="mb-0 mt-2">Thử đổi bộ lọc hoặc <a href="{{ route('token-codes.index') }}">xóa bộ lọc</a>.</p>
                            @else
                                <strong><i class="fas fa-exclamation-triangle"></i> Cần phải thêm code!</strong>
                                <p class="mb-0 mt-2">Hiện tại chưa có code nào trong hệ thống.</p>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    {{ $tokenCodes->links() }}
</div>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

@endsection