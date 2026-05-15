@extends("code-muakey.layouts.app")
@section('title', 'Thêm tài khoản Netflix')
@section('content')

<div class="container mt-5">
    <h3>
        Thêm tài khoản
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

    <form action="{{ route('netflix.store') }}" method="post" enctype="multipart/form-data">
        <div class="form-group  mt-3">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="Nhập email">
        </div>
        <div class="form-group  mt-3">
            <label for="password">Password</label>
            <input type="text" class="form-control" id="password" name="password" placeholder="Nhập password">
        </div>
        <div class="form-group mt-3">
            <input class="form-control" type="file" name="excel_file" accept=".xlsx, .xls">
        </div>

        <div class="d-flex" style="gap: 8px">
            <button type="submit" class="btn btn-primary mt-3">Thêm</button>
            <a href="{{ route('netflix.export-form-add') }}" class="btn btn-success mt-3">Xuất Excel mẫu</a>
        </div>
    </form>
</div>

@endsection