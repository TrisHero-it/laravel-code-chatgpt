{{--
    Nút thao tác NetEase cho 1 dòng đơn hàng.
    Cần: $order (mảng/model có id, uid, category)
--}}
<button type="button"
        class="btn btn-outline-dark btn-sm js-verify-uid"
        data-uid="{{ $order['uid'] ?? '' }}"
        data-category="{{ $order['category'] ?? '' }}"
        data-server="{{ $order['server'] ?? '' }}"
        title="Kiểm tra UID trên API NetEase">
    <i class="fas fa-id-badge"></i>
</button>
<a href="{{ route('netease.topup', ['order' => $order['id']]) }}"
   target="_blank" rel="noopener"
   class="btn btn-outline-success btn-sm"
   title="Mở trang nạp chính chủ NetEase">
    <i class="fas fa-bolt"></i>
</a>
