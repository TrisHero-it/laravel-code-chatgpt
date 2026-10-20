@extends("code-muakey.layouts.app")
@section('title', $email['subject'] ?? 'Đọc mail')
@section('content')

@php
    $receivedAt = $email['received_at'] ?? $email['sent_at'] ?? null;
    $html = $email['html'] ?? null;
    if (is_array($html)) $html = $html[0] ?? null;
    $text = $email['text'] ?? '';
    $toList = collect($email['to'] ?? [])->pluck('address')->filter()->implode(', ');
@endphp

<div class="container mt-5 mb-5">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <h4 class="mb-0">{{ ($email['subject'] ?? '') !== '' ? $email['subject'] : '(Không có tiêu đề)' }}</h4>
        <a href="{{ route('inbound-mail.index') }}" class="btn btn-secondary">&laquo; Hộp thư</a>
    </div>

    <div class="card mb-3">
        <div class="card-body small">
            <div><strong>Từ:</strong> {{ $email['from']['name'] ?? '' }} &lt;{{ $email['from']['address'] ?? '' }}&gt;</div>
            <div><strong>Đến:</strong> {{ $toList !== '' ? $toList : $mailbox }}</div>
            <div><strong>Nhận lúc:</strong> {{ $receivedAt ? \Carbon\Carbon::parse($receivedAt)->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i:s') : '-' }}</div>
        </div>
    </div>

    <ul class="nav nav-tabs" role="tablist">
        @if ($html)
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-html" type="button">HTML</button>
            </li>
        @endif
        <li class="nav-item">
            <button class="nav-link {{ $html ? '' : 'active' }}" data-bs-toggle="tab" data-bs-target="#tab-text" type="button">Văn bản</button>
        </li>
    </ul>

    <div class="tab-content border border-top-0">
        @if ($html)
            <div class="tab-pane fade show active" id="tab-html">
                {{-- sandbox: không chạy script của mail; base target để link mở tab mới --}}
                <iframe
                    sandbox="allow-popups allow-popups-to-escape-sandbox"
                    srcdoc="{{ '<base target="_blank">' . $html }}"
                    style="width: 100%; height: 75vh; border: 0;"></iframe>
            </div>
        @endif
        <div class="tab-pane fade {{ $html ? '' : 'show active' }}" id="tab-text">
            <pre class="p-3 mb-0" style="white-space: pre-wrap; word-break: break-word;">{{ $text !== '' ? $text : '(Không có nội dung văn bản)' }}</pre>
        </div>
    </div>
</div>

@endsection
