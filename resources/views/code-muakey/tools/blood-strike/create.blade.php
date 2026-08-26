@extends("code-muakey.layouts.app")
@section('title', 'Thêm đơn hàng Blood Strike')
@section('content')
<div class="container mt-5">
    @if ($errors->any())

    <div class="alert alert-danger">

        <ul class="mb-0">

            @foreach ($errors->all() as $error)

            <li>{{ $error }}</li>

            @endforeach

        </ul>

    </div>

    @endif

    @if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    <div class="container mt-5">
        <h3>Thêm đơn hàng Blood Strike</h3>

        <form action="{{ route('blood-strike-order.store') }}" method="post" id="bloodStrikeOrderForm">
            @csrf
            <div class="form-group mt-3">
                <label for="order_data">Dán thông tin đơn hàng <span class="text-danger">*</span></label>
                <textarea class="form-control" id="order_data" name="order_data" rows="6"
                    placeholder="Dán thông tin đơn hàng vào đây (ví dụ:&#10;Mã ĐH: 1296509&#10;Sản phẩm: 500 Tặng 40 Gold Nạp Blood Strike Chỉ Cần ID x 1&#10;UID Blood Strike: 73432255&#10;Ngày mua: 00:53:20 25/07/2026)" required></textarea>
                <small class="form-text text-muted">Hệ thống sẽ tự động nhận diện Order ID, UID và Product ID từ thông tin bạn dán.</small>
            </div>

            <!-- Hidden fields để lưu giá trị đã parse -->
            <input type="text" id="order_id" name="order_id" value="">
            <input type="text" id="uid" name="uid" value="">
            <input type="text" id="product_id" name="product_id" value="">
            <input type="hidden" id="purchase_date" name="purchase_date" value="">
            <input type="hidden" id="continue_add" name="continue_add" value="0">

            <div class="form-group mt-3">
                <label for="sales_agent_id">Sales Agent ID <span class="text-muted">(Tùy chọn)</span></label>
                <input type="number" class="form-control" id="sales_agent_id" name="sales_agent_id" placeholder="Nhập ID đại lý (để trống nếu không có)" value="" min="1" step="1" style="max-width: 200px;">
            </div>

            <!-- Preview thông tin đã parse -->
            <div id="parsedInfo" class="alert alert-info mt-3" style="display: none;">
                <h6>Thông tin đã nhận diện:</h6>
                <ul class="mb-0">
                    <li><strong>Order ID:</strong> <span id="preview_order_id">-</span></li>
                    <li><strong>UID:</strong> <span id="preview_uid">-</span></li>
                    <li><strong>Product ID:</strong> <span id="preview_product_id">-</span></li>
                </ul>
            </div>

            <div class="d-flex" style="gap: 8px">
                <button type="submit" class="btn btn-primary mt-3" id="submitBtn">Thêm đơn hàng</button>
                <button type="button" class="btn btn-success mt-3" id="continueAddBtn">
                    <i class="fas fa-plus-circle"></i> Thêm tiếp
                </button>
                <a href="{{ route('blood-strike-order.index') }}" class="btn btn-secondary mt-3">Quay lại</a>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const orderDataTextarea = document.getElementById('order_data');
            const orderIdInput = document.getElementById('order_id');
            const uidInput = document.getElementById('uid');
            const productIdInput = document.getElementById('product_id');
            const purchaseDateInput = document.getElementById('purchase_date');
            const continueAddInput = document.getElementById('continue_add');
            const parsedInfoDiv = document.getElementById('parsedInfo');
            const submitBtn = document.getElementById('submitBtn');
            const continueAddBtn = document.getElementById('continueAddBtn');

            // Danh sách products từ PHP
            const iosProducts = <?php echo json_encode($iosProducts); ?>;

            function normalizeProductName(name) {
                // Loại bỏ " x 1", " x 2" ở cuối
                return name.replace(/\s+x\s+\d+$/i, '').trim();
            }

            function parseOrderData(text) {
                const result = {
                    order_id: '',
                    uid: '',
                    product_id: '',
                    purchase_date: ''
                };

                // Parse Order ID: "Mã ĐH: 1296509"
                const orderIdMatch = text.match(/Mã\s+ĐH:\s*(\d+)/i) ||
                    text.match(/Mã\s+đơn\s+hàng:\s*(\d+)/i);
                if (orderIdMatch) {
                    result.order_id = orderIdMatch[1].trim();
                }

                // Parse UID: "UID Blood Strike: 73432255"
                const uidMatch = text.match(/UID Blood\s*Strike:\s*(\d+)/i) ||
                    text.match(/UID:\s*(\d+)/i);
                if (uidMatch) {
                    result.uid = uidMatch[1].trim();
                }

                // Parse Purchase Date: "Ngày mua: 00:53:20 25/07/2026"
                const purchaseDateMatch = text.match(/Ngày mua:\s*(.+?)(?:\n|$)/i);
                if (purchaseDateMatch) {
                    result.purchase_date = purchaseDateMatch[1].trim();
                }

                // Parse Product Name: "Sản phẩm: 500 Tặng 40 Gold Nạp Blood Strike Chỉ Cần ID x 1"
                const productNameMatch = text.match(/Sản\s+phẩm:\s*(.+?)(?:\n|$)/i);
                if (productNameMatch) {
                    const cleanProductName = normalizeProductName(productNameMatch[1].trim());

                    for (let product of iosProducts) {
                        if (!product.goodsinfo) continue;

                        const normalizedProductName = normalizeProductName(product.goodsinfo);

                        const normalizedLower = normalizedProductName.toLowerCase();
                        const cleanLower = cleanProductName.toLowerCase();

                        if (normalizedLower === cleanLower ||
                            normalizedLower.includes(cleanLower) ||
                            cleanLower.includes(normalizedLower)) {
                            result.product_id = product.goodsid;
                            break;
                        }
                    }
                }

                return result;
            }

            function updatePreview(parsed) {
                document.getElementById('preview_order_id').textContent = parsed.order_id || '-';
                document.getElementById('preview_uid').textContent = parsed.uid || '-';
                document.getElementById('preview_product_id').textContent = parsed.product_id || '-';

                parsedInfoDiv.style.display = '';
                submitBtn.disabled = !(parsed.order_id && parsed.uid && parsed.product_id);
                continueAddBtn.disabled = !(parsed.order_id && parsed.uid && parsed.product_id);
            }

            function handleInput() {
                const text = orderDataTextarea.value;
                if (text.trim()) {
                    const parsed = parseOrderData(text);
                    orderIdInput.value = parsed.order_id;
                    uidInput.value = parsed.uid;
                    productIdInput.value = parsed.product_id;
                    purchaseDateInput.value = parsed.purchase_date || '';

                    updatePreview(parsed);
                } else {
                    parsedInfoDiv.style.display = 'none';
                    submitBtn.disabled = true;
                    orderIdInput.value = '';
                    uidInput.value = '';
                    productIdInput.value = '';
                    purchaseDateInput.value = '';
                }
            }

            // Xử lý khi paste hoặc nhập
            orderDataTextarea.addEventListener('paste', function(e) {
                setTimeout(handleInput, 10);
            });

            orderDataTextarea.addEventListener('input', handleInput);

            // Xử lý nút "Thêm tiếp"
            continueAddBtn.addEventListener('click', function() {
                const orderId = orderIdInput.value.trim();
                const uid = uidInput.value.trim();
                const productId = productIdInput.value.trim();

                if (!orderId || !uid || !productId) {
                    alert('Vui lòng dán đầy đủ thông tin đơn hàng để hệ thống có thể nhận diện Order ID, UID và Product ID!');
                    return false;
                }

                continueAddInput.value = '1';
                document.getElementById('bloodStrikeOrderForm').submit();
            });

            // Validate trước khi submit
            document.getElementById('bloodStrikeOrderForm').addEventListener('submit', function(e) {
                const orderId = orderIdInput.value.trim();
                const uid = uidInput.value.trim();
                const productId = productIdInput.value.trim();

                if (!orderId || !uid || !productId) {
                    e.preventDefault();
                    alert('Vui lòng dán đầy đủ thông tin đơn hàng để hệ thống có thể nhận diện Order ID, UID và Product ID!');
                    return false;
                }

                if (continueAddInput.value !== '1') {
                    continueAddInput.value = '0';
                }
            });

            // Clear form khi có success message (để sẵn sàng thêm tiếp)
            @if (session('success'))
                setTimeout(function() {
                    orderDataTextarea.value = '';
                    orderIdInput.value = '';
                    uidInput.value = '';
                    productIdInput.value = '';
                    purchaseDateInput.value = '';
                    continueAddInput.value = '0';
                    parsedInfoDiv.style.display = 'none';
                    submitBtn.disabled = true;
                    continueAddBtn.disabled = true;
                    orderDataTextarea.focus();
                }, 100);
            @endif
        });
    </script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</div>
@endsection
