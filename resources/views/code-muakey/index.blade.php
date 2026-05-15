@extends('code-muakey.layouts.app')
@section('content')
<div class="main-content">
    <div class="container mt-5">
        <!-- Table -->
        <div class="d-flex justify-content-between">

            <h2 class="mb-5">Nhận code : <span style="color: red;">{{ $email ?? 'Vui lòng nhập email' }}</span></h2>


            <!-- Button trigger modal -->
            <button type="button" id="click" style="height: 22px;" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
                Tìm kiếm theo email
            </button>

            <!-- Modal -->
            <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <form action="">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="exampleModalLabel">Vui lòng điền email để lấy code</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <input type="email" class="form-control" style="width: 95%;" name="email" id="email" placeholder="Nhập email">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                <button type="submit" class="btn btn-primary" id="searchSubmitBtn">
                                    <span class="search-btn-text">Tìm kiếm</span>
                                    <span class="spinner-border spinner-border-sm ms-2 d-none" role="status" aria-hidden="true" id="searchSpinner"></span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    (function() {
        var form = document.querySelector('#exampleModal form');
        var btn = document.getElementById('searchSubmitBtn');
        var spinner = document.getElementById('searchSpinner');
        var btnText = document.querySelector('#searchSubmitBtn .search-btn-text');
        var emailInput = document.getElementById('email');

        if (!form || !btn || !spinner || !btnText || !emailInput) return;

        form.addEventListener('submit', function() {
            var email = (emailInput.value || '').trim();
            if (!email) return;

            btn.disabled = true;
            spinner.classList.remove('d-none');
            btnText.textContent = 'Đang tìm...';
        });
    })();
</script>

@php
if (!isset($email) || $email == '') {
echo "<script>
    setTimeout(function() {
        document.getElementById('click').click();
    }, 300);
</script>";
}
@endphp


<div class="main-content">
    <div class="container mt-5">
        <!-- Table -->
        <div class="row">
            <div class="col">
                <div class="card shadow">
                    <div class="card-header border-0">
                        <h3 class="mb-0">Netflix</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-items-center table-flush">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col">Link</th>
                                    <th scope="col">Phân loại</th>
                                    <th scope="col">Tiêu đề</th>
                                    <th scope="col">Thời gian</th>
                                    <th scope="col">Website</th>
                                </tr>
                            </thead>
                            <?php if (isset($_GET['email'])) { ?>
                                <tbody id="resultsBody">
                                    <tr id="loadingRow">
                                        <td colspan="5" class="text-center py-4">
                                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                            Đang tải kết quả...
                                        </td>
                                    </tr>
                                    <tr id="noResultsRow" class="d-none">
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            <div>Không có kết quả</div>
                                        </td>
                                    </tr>
                                    <script>
                                        (function() {
                                            var email = <?php echo json_encode($email, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
                                            var body = document.getElementById('resultsBody');
                                            var loadingRow = document.getElementById('loadingRow');
                                            var noRow = document.getElementById('noResultsRow');
                                            if (!body || !loadingRow || !noRow || !email) return;

                                            function setLoading(v) {
                                                if (v) loadingRow.classList.remove('d-none');
                                                else loadingRow.classList.add('d-none');
                                            }

                                            function setNo(v) {
                                                if (v) noRow.classList.remove('d-none');
                                                else noRow.classList.add('d-none');
                                            }

                                            function clearData() {
                                                var rows = body.querySelectorAll('tr.data-row');
                                                for (var i = 0; i < rows.length; i++) rows[i].remove();
                                            }

                                            function esc(s) {
                                                return String(s || '').replace(/[&<>"']/g, function(c) {
                                                    return ({
                                                        '&': '&amp;',
                                                        '<': '&lt;',
                                                        '>': '&gt;',
                                                        '"': '&quot;',
                                                        "'": '&#39;'
                                                    })[c];
                                                });
                                            }

                                            function append(item) {
                                                var tr = document.createElement('tr');
                                                tr.className = 'data-row';
                                                var createdAt = item.createdAt || '';
                                                var timeText = '';
                                                try {
                                                    var d = new Date(createdAt);
                                                    if (!isNaN(d.getTime())) timeText = d.toLocaleTimeString('vi-VN', {
                                                        hour: '2-digit',
                                                        minute: '2-digit',
                                                        second: '2-digit'
                                                    });
                                                } catch (e) {}

                                                tr.innerHTML =
                                                    '<td><a class="btn btn-primary" target="_blank" rel="noopener noreferrer" href="' + esc(item.verifyLink || '#') + '">Click here</a></td>' +
                                                    '<th scope="row"><div class="media align-items-center"><a href="#" class="avatar rounded-circle mr-3"><img alt="Image placeholder" src="css/images/netflix.jpg"></a><div class="media-body"><span class="mb-0 text-sm">Netflix</span></div></div></th>' +
                                                    '<td><div class="d-flex align-items-center"><span class="badge badge-dot mr-4" style="color: red;">' + esc(item.subject || '') + '</span></div></td>' +
                                                    '<td><div class="d-flex align-items-center"><span class="badge badge-dot mr-4" style="color: black">' + esc(timeText) + '</span></div></td>' +
                                                    '<td><a href="https://muakey.com/">muakey.com</a></td>';
                                                body.appendChild(tr);
                                            }

                                            setNo(false);
                                            clearData();
                                            setLoading(true);

                                            $.ajax({
                                                url: 'api.php',
                                                method: 'GET',
                                                dataType: 'json',
                                                data: {
                                                    act: 'code-search',
                                                    email: email
                                                },
                                                success: function(res) {
                                                    clearData();
                                                    var data = res && res.data;
                                                    if (!Array.isArray(data) || data.length === 0) {
                                                        setNo(true);
                                                        return;
                                                    }
                                                    for (var i = 0; i < data.length; i++) append(data[i]);
                                                    setNo(false);
                                                },
                                                error: function() {
                                                    clearData();
                                                    setNo(true);
                                                },
                                                complete: function() {
                                                    setLoading(false);
                                                }
                                            });
                                        })();
                                    </script>
                                </tbody>
                            <?php } ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection