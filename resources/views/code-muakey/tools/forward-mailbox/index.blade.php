@extends("code-muakey.layouts.app")
@section('title', 'Hòm chuyển tiếp Netflix')
@section('content')

<div class="container mt-5">
    <div class="d-flex justify-content-between">
        <h3>
            Hòm chuyển tiếp (mail.tm)
        </h3>
        <div class="d-flex" style="gap: 8px;">
            <a href="{{ route('manager-tools') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay về Dashboard
            </a>
            <form action="{{ route('forward-mailbox.check') }}" method="post">
                @csrf
                <button type="submit" class="btn btn-info" onclick="this.disabled=true; this.innerText='Đang kiểm tra...'; this.form.submit();">
                    <i class="fas fa-check-circle"></i> Kiểm tra tất cả
                </button>
            </form>
        </div>
    </div>

    <p class="text-muted mt-2 mb-0">
        Thứ tự tra mã Netflix: (1) tài khoản mail.tm trong danh sách Netflix, (2) hòm thư tổng
        netflix@lumekza.io.vn, (3) các hòm đang bật dưới đây.
    </p>

    @if (session('success'))
    <div class="alert alert-success mt-3">
        {{ session('success') }}
    </div>
    @endif

    @isset($checkSummary)
    <div class="alert {{ str_contains($checkSummary, ' 0 lỗi') ? 'alert-success' : 'alert-warning' }} mt-3">
        {{ $checkSummary }}
    </div>
    @endisset

    @if ($errors->any())
    <div class="alert alert-danger mt-3">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('forward-mailbox.store') }}" method="post" class="mt-3">
        @csrf
        <div class="form-group">
            <label for="lines">Thêm hòm (mỗi dòng: email|mật khẩu)</label>
            <textarea class="form-control" id="lines" name="lines" rows="4" placeholder="abc@deltajohnsons.com|matkhau">{{ old('lines') }}</textarea>
        </div>
        <button type="submit" class="btn btn-primary mt-2">Thêm</button>
    </form>

    <table class="table mt-4">
        <thead>
            <tr>
                <th scope="col">Email</th>
                <th scope="col">Password</th>
                <th scope="col">Trạng thái</th>
                @isset($results)
                <th scope="col">Kết quả kiểm tra</th>
                @endisset
                <th scope="col">Hành động</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($mailboxes as $mailbox)
            <tr>
                <td>{{ $mailbox->address }}</td>
                <td>{{ $mailbox->password }}</td>
                <td>
                    @if ($mailbox->active)
                    <span class="badge bg-success">Đang bật</span>
                    @else
                    <span class="badge bg-secondary">Đã tắt</span>
                    @endif
                </td>
                @isset($results)
                <td>
                    @php $result = $results[$mailbox->id] ?? null; @endphp
                    @if ($result)
                    <span class="{{ $result['ok'] ? 'text-success' : 'text-danger fw-bold' }}">
                        {{ $result['ok'] ? '✔' : '✘' }} {{ $result['message'] }}
                    </span>
                    @endif
                </td>
                @endisset
                <td class="d-flex" style="gap: 8px;">
                    <form action="{{ route('forward-mailbox.update', $mailbox->id) }}" method="post">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn btn-warning">{{ $mailbox->active ? 'Tắt' : 'Bật' }}</button>
                    </form>
                    <form action="{{ route('forward-mailbox.destroy', $mailbox->id) }}" method="post">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Xoá hòm này?')">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-muted">Chưa có hòm nào.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
