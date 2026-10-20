@extends("code-muakey.layouts.app")
@section('title', 'Hộp thư ' . $mailbox)
@section('content')

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h3>Hộp thư {{ $mailbox }}</h3>
            <p class="text-muted mb-0">Mail mới nhất ở trên cùng. Giờ hiển thị theo giờ Việt Nam.</p>
        </div>
        <div class="d-flex" style="gap: 8px;">
            <a href="{{ route('inbound-mail.index') }}" class="btn btn-primary">
                <i class="fas fa-sync-alt"></i> Tải lại
            </a>
            <a href="{{ route('manager-tools') }}" class="btn btn-secondary">Dashboard</a>
        </div>
    </div>

    <table class="table table-hover mt-3">
        <thead>
            <tr>
                <th scope="col" style="width: 170px;">Nhận lúc</th>
                <th scope="col" style="width: 260px;">Người gửi</th>
                <th scope="col" style="width: 240px;">Người nhận</th>
                <th scope="col">Tiêu đề</th>
                <th scope="col" style="width: 90px;"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($emails as $email)
                @php
                    $receivedAt = $email['received_at'] ?? $email['sent_at'] ?? null;
                @endphp
                <tr>
                    <td class="text-nowrap">
                        {{ $receivedAt ? \Carbon\Carbon::parse($receivedAt)->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i:s') : '-' }}
                    </td>
                    <td>
                        @if (!empty($email['from_name']))
                            <div>{{ $email['from_name'] }}</div>
                        @endif
                        <small class="text-muted">{{ $email['from_address'] ?? '' }}</small>
                    </td>
                    <td><small>{{ $email['recipient'] ?? '-' }}</small></td>
                    <td>
                        <a href="{{ route('inbound-mail.show', $email['id']) }}">
                            {{ ($email['subject'] ?? '') !== '' ? $email['subject'] : '(Không có tiêu đề)' }}
                        </a>
                    </td>
                    <td>
                        <a href="{{ route('inbound-mail.show', $email['id']) }}" class="btn btn-sm btn-outline-primary">Xem</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5"class="text-center text-muted">Không có mail nào (hoặc không gọi được API inbound-mail)</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="d-flex justify-content-between mb-5">
        <div>
            @unless ($isFirstPage)
                <a href="{{ route('inbound-mail.index') }}" class="btn btn-outline-secondary">&laquo; Mới nhất</a>
            @endunless
        </div>
        <div>
            @if ($nextBefore)
                <a href="{{ route('inbound-mail.index', ['before' => $nextBefore]) }}" class="btn btn-outline-secondary">Cũ hơn &raquo;</a>
            @endif
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

@endsection
