@extends("code-muakey.layouts.app")
@section('title', 'Thêm Code')
@section('content')

<div class="container mt-5">
    <h3>
        Thêm code payment
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
    <form action="{{ route('token-codes.store') }}" method="post" id="paymentCodesForm">
        <div class="form-group mt-3">
            <label for="codes">Danh sách code token <span class="text-danger">*</span></label>
            <textarea
                class="form-control"
                id="codes"
                name="codes"
                rows="10"
                placeholder="Paste các code token vào đây, mỗi code một dòng. Ví dụ:&#10;Fycrwy7u2V2a3e70ve&#10;FycrwL7B2Y293b70w2"
                required></textarea>
            <small class="form-text text-muted">
                Code payment là mã dùng để nạp tiền vào tài khoản MidasBuy, thường có dạng dãy số dài. Bạn có thể paste nhiều code cùng lúc, mỗi code trên một dòng.
            </small>
        </div>

        <select name="token" class="form-control mt-3" required>
            <option value="">Chọn Token</option>
            <option value="16">16 Tokens</option>
            <option value="80">80 Tokens</option>
            <option value="240">240 Tokens</option>
            <option value="400">400 Tokens</option>
            <option value="560">560 Tokens</option>
            <option value="830">830 Tokens</option>
            <option value="1245">1245 Tokens</option>
            <option value="2508">2508 Tokens</option>
            <option value="4180">4180 Tokens</option>
            <option value="8360">8360 Tokens</option>
        </select>

        <div class="d-flex" style="gap: 8px">
            <button type="submit" class="btn btn-primary mt-3">Thêm code</button>
            <a href="{{ route('midasbuy-token.index') }}" class="btn btn-secondary mt-3">Quay lại</a>
        </div>
    </form>

</div>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

@endsection