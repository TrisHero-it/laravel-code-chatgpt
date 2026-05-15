@extends('code-muakey.layouts.app')
@section('title', 'Quản lý công cụ')
@section('content')

<div class="container mt-5">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">Quản lý đơn hàng</h2>
            <p class="text-muted mb-4">Chọn loại đơn hàng bạn muốn quản lý:</p>
        </div>
    </div>

    <div class="row" style="display: flex;">
        <!-- Orders Card -->
        <div class="col-md-5 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <i class="fas fa-shopping-cart fa-3x text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="card-title mb-0">Midasbuy-Japan</h4>
                            <p class="text-muted mb-0">Quản lý đơn hàng thông thường</p>
                        </div>
                    </div>
                    <p class="card-text">Quản lý các đơn hàng với thông tin username, password và backup code.</p>
                    <div class="d-grid gap-2">
                        <a href="{{ route('midasbuy-japan.index') }}" class="btn btn-primary">
                            <i class="fas fa-list"></i> Xem danh sách Orders
                        </a>
                        <a href="{{ route('midasbuy-japan.create') }}" class="btn btn-outline-primary">
                            <i class="fas fa-plus"></i> Thêm đơn hàng mới
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- WWM Orders Card -->
        <div class="col-md-5 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <i class="fas fa-gamepad fa-3x text-success"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="card-title mb-0">WWM Orders</h4>
                            <p class="text-muted mb-0">Quản lý đơn hàng Where Winds Meet</p>
                        </div>
                    </div>
                    <p class="card-text">Quản lý các đơn hàng game Where Winds Meet với thông tin UID và Product ID.</p>
                    <div class="d-grid gap-2">
                        <a href="{{ route('wwm-order.index') }}" class="btn btn-success">
                            <i class="fas fa-list"></i> Xem danh sách WWM Orders
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
   
    <div class="row mt-4" style="display: flex;">
        <!-- Back to List Card -->
        <div class="col-md-5 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <i class="fas fa-arrow-left fa-3x text-danger"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="card-title mb-0">Quay về</h4>
                            <p class="text-muted mb-0">Danh sách tài khoản Netflix</p>
                        </div>
                    </div>
                    <p class="card-text">Quay về trang danh sách tài khoản Netflix để quản lý các tài khoản.</p>
                    <div class="d-grid gap-2">
                        <a href="{{ route('netflix.index') }}" class="btn btn-danger">
                            <i class="fas fa-list"></i> Danh sách tài khoản Netflix
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Steam Region Change Card -->
        <div class="col-md-5 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <i class="fas fa-store fa-3x text-secondary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="card-title mb-0">Midasbuy token</h4>
                            <p class="text-muted mb-0">Quản lý token Midasbuy</p>
                        </div>
                    </div>
                    <p class="card-text">Nạp token cho tài khoản Midasbuy -Nạp token cho tài khoản Midasbuy..</p>
                    <div class="d-grid gap-2">
                        <a href="{{ route('midasbuy-token.index') }}" class="btn btn-secondary">
                            <i class="fas fa-globe"></i> Nạp token cho tài khoản Midasbuy.
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="row mt-4" style="display: flex;">

        <!-- <div class="col-md-5 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <i class="fas fa-store fa-3x text-warning"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="card-title mb-0">MidasBuy</h4>
                            <p class="text-muted mb-0">Quản lý đơn hàng và tài khoản MidasBuy</p>
                        </div>
                    </div>
                    <p class="card-text">Quản lý các đơn hàng và tài khoản MidasBuy với thông tin UID và Token.</p>
                    <div class="d-grid gap-2">
                        <a href="?act=midas-orders" class="btn btn-warning">
                            <i class="fas fa-list"></i> Xem danh sách Orders
                        </a>
                        <a href="?act=midas-order-add" class="btn btn-outline-warning">
                            <i class="fas fa-plus"></i> Thêm đơn hàng mới
                        </a>
                        <a href="?act=midas-accounts" class="btn btn-outline-warning">
                            <i class="fas fa-users"></i> Quản lý tài khoản
                        </a>
                    </div>
                </div>
            </div>
        </div> -->
    </div>

</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

@endsection