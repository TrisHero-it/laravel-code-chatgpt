@extends("code-muakey.layouts.app")
@section('title', 'Danh sách Netflix')
@section('content')

<div class="container mt-5">
    <div class="d-flex justify-content-between">
        <h3>
            Danh sách tài khoản
        </h3>
        <div class="d-flex" style="gap: 8px;">
            <a href="{{ route('manager-tools') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay về Dashboard
            </a>
            <a href="{{ route('netflix.create') }}" class="btn btn-primary">Thêm tài khoản</a>
        </div>
    </div>
    <table class="table mt-3">
        <thead>
            <tr>
                <th scope="col">Email</th>
                <th scope="col">Password</th>
                <th scope="col">Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($netflixes as $netflix) {
            ?>
                <tr>
                    <td><?php echo $netflix->email ?></td>
                    <td><?php echo htmlspecialchars($netflix->password, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <form action="{{ route('netflix.destroy', $netflix->id) }}" method="post">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa tài khoản này không?')">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php
            }
            ?>
        </tbody>
    </table>
</div>

@endsection