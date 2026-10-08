{{-- JS cho nút "Kiểm tra UID". Include 1 lần ở cuối trang danh sách đơn. --}}
<script>
document.addEventListener('click', function (event) {
    var button = event.target.closest('.js-verify-uid');
    if (!button) return;

    var original = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    fetch(@json(route('netease.verify-uid')), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': @json(csrf_token()),
        },
        body: JSON.stringify({
            uid: button.dataset.uid,
            category: button.dataset.category,
            server: button.dataset.server || null,
        }),
    })
        .then(function (response) { return response.json(); })
        .then(function (data) {
            if (data.ok) {
                button.classList.replace('btn-outline-dark', 'btn-success');

                var lines = ['UID hợp lệ trên NetEase'];
                if (data.role_name) lines.push('Nhân vật: ' + data.role_name);
                if (data.hostid) lines.push('Server (hostid): ' + data.hostid);
                if (data.alpha2) lines.push('Khu vực: ' + data.alpha2);
                (data.limits || []).forEach(function (limit) {
                    lines.push('Giới hạn ' + limit.goodsid + ': đã mua ' + limit.buy_num + '/' + limit.limit_num);
                });
                alert(lines.join('\n'));
            } else {
                button.classList.replace('btn-outline-dark', 'btn-danger');
                alert(data.message || 'Không tra cứu được UID.');
            }
        })
        .catch(function () { alert('Lỗi kết nối tới API NetEase.'); })
        .finally(function () {
            button.disabled = false;
            button.innerHTML = original;
        });
});
</script>
