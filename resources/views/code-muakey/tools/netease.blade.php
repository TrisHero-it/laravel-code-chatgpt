@extends('code-muakey.layouts.app')
@section('title', 'NetEase - Danh sách game')
@section('content')

<div class="container mt-5">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">NetEase</h2>
            <p class="text-muted mb-4">Chọn game bạn muốn quản lý:</p>
        </div>
    </div>

    <div class="row" style="display: flex;">
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
                    <p class="card-text">Quản lý các đơn hàng game Where Winds Meet.</p>
                    <div class="d-grid gap-2">
                        <a href="{{ route('wwm-order.index') }}" class="btn btn-success">
                            <i class="fas fa-list"></i> Xem danh sách WWM Orders
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Identity V Card -->
        <div class="col-md-5 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <i class="fas fa-gamepad fa-3x text-secondary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="card-title mb-0">Identity V</h4>
                            <p class="text-muted mb-0">Quản lý đơn hàng Identity V</p>
                        </div>
                    </div>
                    <p class="card-text">Quản lý các đơn hàng game Identity V.</p>
                    <div class="d-grid gap-2">
                        <a href="{{ route('identity-order.index') }}" class="btn btn-secondary">
                            <i class="fas fa-list"></i> Xem danh sách Identity V Orders
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Blood Strike Card -->
        <div class="col-md-5 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <i class="fas fa-gamepad fa-3x text-warning"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="card-title mb-0">Blood Strike</h4>
                            <p class="text-muted mb-0">Quản lý đơn hàng Blood Strike</p>
                        </div>
                    </div>
                    <p class="card-text">Quản lý các đơn hàng game Blood Strike.</p>
                    <div class="d-grid gap-2">
                        <a href="{{ route('blood-strike-order.index') }}" class="btn btn-warning">
                            <i class="fas fa-list"></i> Xem danh sách Blood Strike Orders
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Marvel Rivals Card -->
        <div class="col-md-5 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <i class="fas fa-gamepad fa-3x text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="card-title mb-0">Marvel Rivals</h4>
                            <p class="text-muted mb-0">Quản lý đơn hàng Marvel Rivals</p>
                        </div>
                    </div>
                    <p class="card-text">Quản lý các đơn hàng game Marvel Rivals.</p>
                    <div class="d-grid gap-2">
                        <a href="{{ route('marvel-rivals-order.index') }}" class="btn btn-primary">
                            <i class="fas fa-list"></i> Xem danh sách Marvel Rivals Orders
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <a href="{{ route('manager-tools') }}" class="btn btn-outline-danger">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách tool
            </a>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

@endsection
