@extends("code-muakey.layouts.app")
@section('title', 'Thêm đơn hàng MidasBuy Japan')
@section('content')
<div class="container mt-5">
    <h3>
        Thêm đơn hàng MidasBuy Japan
    </h3>

    @if ($errors->any())

    <div class="alert alert-danger">

        <ul class="mb-0">

            @foreach ($errors->all() as $error)

            <li>{{ $error }}</li>

            @endforeach

        </ul>

    </div>

    @endif

    <div class="form-group mt-3">
        <label for="order_paste">Dán thông tin đơn hàng</label>
        <textarea class="form-control" id="order_paste" name="order_paste" rows="5" placeholder="Dán nội dung đơn hàng (Mã đơn hàng/Mã ĐH, Sản phẩm, UID, Ngày mua)..."></textarea>
        <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="btn_parse">
            <i class="fas fa-magic"></i> Phân tích tự động
        </button>
        <small class="text-muted d-block mt-1">Sẽ tự nhận UID và Card (plus/normal theo tên sản phẩm)</small>
    </div>

    <hr class="my-4">
    <form action="{{ route('midasbuy-japan.store') }}" method="POST" id="form_order">
        @csrf
        <div class="form-group mt-3">
            <label for="order_id">Mã đơn hàng (Order ID)</label>
            <input type="text" class="form-control" id="order_id" name="order_id" placeholder="Mã đơn hàng từ MidasBuy (số, tùy chọn)">
        </div>
        <div class="form-group mt-3">
            <label for="uid">UID <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="uid" name="uid" placeholder="Nhập UID hoặc dán để phân tích" required>
        </div>
        <div class="form-group mt-3">
            <label for="card">Card <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="card" name="card" placeholder="plus hoặc normal (tự nhận từ tên sản phẩm)" maxlength="30" required>
        </div>
        <div class="form-group mt-3">
            <label for="sales_agent_id">Sales Agent ID <span class="text-muted">(Tùy chọn)</span></label>
            <input type="number" class="form-control" id="sales_agent_id" name="sales_agent_id" placeholder="Nhập ID đại lý (để trống nếu không có)" value="" min="1" step="1" style="max-width: 200px;">
        </div>
        <div class="form-group mt-3">
            <label for="status">Trạng thái</label>
            <select class="form-control" id="status" name="status">
                <option value="pending">Đang chờ</option>
                <option value="success">Thành công</option>
                <option value="cancelled">Đã huỷ</option>
            </select>
        </div>

        <div class="d-flex" style="gap: 8px">
            <button type="submit" class="btn btn-primary mt-3">Thêm đơn hàng</button>
            <a href="{{ route('midasbuy-japan.index') }}" class="btn btn-secondary mt-3">Quay lại</a>
        </div>
    </form>
</div>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<script>
    (function() {
        function parseOrderText(text) {
            var result = {
                uid: '',
                card: 'normal',
                order_id: '',
                product: '',
                date: ''
            };
            if (!text || !text.trim()) return result;

            var lines = text.split('\n');
            for (var i = 0; i < lines.length; i++) {
                var line = lines[i].trim();
                if (/^Mã (đơn hàng|ĐH):\s*/i.test(line)) {
                    result.order_id = line.replace(/^Mã (đơn hàng|ĐH):\s*/i, '').trim();
                } else if (/^(Tên sản phẩm|Sản phẩm):\s*/i.test(line)) {
                    result.product = line.replace(/^(Tên sản phẩm|Sản phẩm):\s*/i, '').trim();
                    result.card = /plus/i.test(result.product) ? 'plus' : 'normal';
                } else if (/UID\s+(HONOR OF KINGS GLOBAL)?\s*:/i.test(line)) {
                    var m = line.match(/:\s*(\d+)/);
                    if (m) result.uid = m[1];
                } else if (line.indexOf('Ngày mua:') === 0) {
                    result.date = line.replace(/Ngày mua:\s*/i, '').trim();
                }
            }
            return result;
        }

        function applyParse() {
            var text = document.getElementById('order_paste').value;
            var parsed = parseOrderText(text);
            if (parsed.order_id) document.getElementById('order_id').value = parsed.order_id;
            if (parsed.uid) document.getElementById('uid').value = parsed.uid;
            if (parsed.card) document.getElementById('card').value = parsed.card;
            if (parsed.uid || parsed.card) {
                document.getElementById('card').focus();
            }
        }

        document.getElementById('btn_parse').addEventListener('click', applyParse);
        document.getElementById('order_paste').addEventListener('paste', function() {
            setTimeout(applyParse, 50);
        });
    })();
</script>
@endsection