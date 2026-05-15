@extends("code-muakey.layouts.app")
@section('title', 'Thêm đơn MidasBuy Token')
@section('content')

<div class="container mt-5">
    <h3>
        Thêm đơn hàng MidasBuy Token
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
    <div class="form-group mt-3">
        <label for="order_paste">Dán thông tin đơn hàng</label>
        <textarea class="form-control" id="order_paste" name="order_paste" rows="5" placeholder="Dán nội dung đơn hàng (Mã đơn hàng/Mã ĐH, Sản phẩm, UID, Ngày mua)..."></textarea>
        <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="btn_parse">
            <i class="fas fa-magic"></i> Phân tích tự động
        </button>
        <small class="text-muted d-block mt-1">Sẽ tự nhận UID và Token (theo tên sản phẩm)</small>
    </div>



    <hr class="my-4">
    <form action="{{ route('midasbuy-token.store') }}" method="POST" id="form_order">
        <div class="form-group mt-3">
            <label for="order_id">Mã đơn hàng (Order ID)</label>
            <input type="text" class="form-control" id="order_id" name="order_id" placeholder="Mã đơn hàng từ MidasBuy (số, tùy chọn)">
        </div>
        <div class="form-group mt-3">
            <label for="uid">UID <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="uid" name="uid" placeholder="Nhập UID hoặc dán để phân tích" required>
        </div>
        <div class="form-group mt-3">
            <label for="token">Token <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="token" name="token" placeholder="Nhập Token hoặc dán để phân tích" required>
        </div>
        <div class="form-group mt-3">
            <label for="card">Code</label>
            <input type="text" class="form-control" id="code" name="code" placeholder="Nhập code" maxlength="30">
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
            <a href="{{ route('midasbuy-token.index') }}" class="btn btn-secondary mt-3">Quay lại</a>
        </div>
    </form>
</div>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<script>
    (function() {
        function parseOrderText(text) {
            var result = {
                uid: '',
                order_id: '',
                product: '',
                token: '',
                date: ''
            };

            if (!text || !text.trim()) return result;

            var lines = text.split('\n');

            for (var i = 0; i < lines.length; i++) {
                var line = lines[i].trim();

                if (/^Mã\s*(đơn hàng|ĐH):\s*/i.test(line)) {
                    result.order_id = line.replace(/^Mã\s*(đơn hàng|ĐH):\s*/i, '').trim();

                } else if (/^(Tên sản phẩm|Sản phẩm):\s*/i.test(line)) {
                    result.product = line.replace(/^(Tên sản phẩm|Sản phẩm):\s*/i, '').trim();

                    // lấy số token đầu dòng sản phẩm
                    var tokenMatch = result.product.match(/^(\d+)\s*Tokens?/i);
                    if (tokenMatch) {
                        result.token = tokenMatch[1];
                    }

                } else if (/^UID/i.test(line)) {
                    var uidMatch = line.match(/:\s*(\d+)/);
                    if (uidMatch) {
                        result.uid = uidMatch[1];
                    }

                } else if (/^Ngày mua:\s*/i.test(line)) {
                    result.date = line.replace(/^Ngày mua:\s*/i, '').trim();
                }
            }

            return result;
        }

        function applyParse() {
            var text = document.getElementById('order_paste').value;
            var parsed = parseOrderText(text);
            if (parsed.order_id) document.getElementById('order_id').value = parsed.order_id;
            if (parsed.uid) document.getElementById('uid').value = parsed.uid;
            if (parsed.token) document.getElementById('token').value = parsed.token;
            if (parsed.uid || parsed.token) {
                document.getElementById('token').focus();
            }
        }

        document.getElementById('btn_parse').addEventListener('click', applyParse);
        document.getElementById('order_paste').addEventListener('paste', function() {
            setTimeout(applyParse, 50);
        });
    })();
</script>
@endsection