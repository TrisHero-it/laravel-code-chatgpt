<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Lọc mail Netflix theo tiêu đề
    |--------------------------------------------------------------------------
    |
    | Chỉ nhận mail "Mã xác minh. Hết hạn sau 15 phút." (mail chứa mã 6 số để
    | xác minh hộ gia đình). Netflix gửi tiêu đề theo NGÔN NGỮ của từng tài
    | khoản nên không so khớp được một chuỗi cố định.
    |
    | Thứ tự xét trong CodeController::isVerifySubject():
    |   1. Trùng 'exclude' -> loại ngay (mail "Mã đăng nhập" cũng hết hạn sau
    |      15 phút nên nếu không loại trước sẽ lọt vào luật số 3).
    |   2. Trùng 'exact'   -> nhận.
    |   3. Có số 15 + một từ nghĩa là "phút" -> nhận.
    |
    | Tiêu đề bị loại ở bước 3 được ghi log 'netflix: tieu de bi loai' kèm
    | nguyên văn, xem log để biết Netflix thực tế dùng câu gì ở ngôn ngữ đó rồi
    | thêm vào 'exact' cho chắc.
    |
    */

    'verify_subject' => [

        // Tiêu đề nguyên văn đã gặp thực tế (so khớp kiểu "chứa", không phân
        // biệt hoa thường). Gặp ngôn ngữ mới thì thêm vào đây.
        'exact' => [
            'Mã xác minh. Hết hạn sau 15 phút.',
        ],

        // Từ nghĩa là "phút" để dựng luật không phụ thuộc ngôn ngữ. Đây là từ
        // vựng thông thường, KHÔNG phải tiêu đề chính thức của Netflix - mục
        // đích chỉ để không bỏ sót bản dịch chưa kịp thêm vào 'exact'.
        'minute_words' => [
            'phút',                                 // vi
            'minute', 'minutes',                    // en, fr, de, ro, cs
            'minuto', 'minutos', 'minuti',          // es, pt, it, fil
            'minuut', 'minuten',                    // nl, de
            'minut', 'minuty', 'minutach',          // pl, cs, sv, da
            'minuter', 'minutter', 'minutt',        // sv, da, no
            'minuutin', 'minuuttia',                // fi
            'минут', 'минуты', 'минутах',           // ru
            'хвилин',                               // uk
            'dakika',                               // tr
            'menit', 'minit',                       // id, ms
            'นาที',                                  // th
            '分', '分鐘', '分钟',                      // ja, zh-TW, zh-CN
            '분',                                    // ko
            'دقيقة', 'دقائق',                        // ar
            'मिनट',                                  // hi
            'דקות',                                  // he
            'λεπτά',                                // el
            'perc',                                 // hu
        ],

        // Mail "Mã đăng nhập" (login code) - loại trước mọi luật khác. Chỉ để
        // từ đặc trưng cho đăng nhập, tránh từ chung chung (vd "entrar",
        // "accesso") vì danh sách này chạy đầu tiên, khớp sai là mất mail thật.
        'exclude' => [
            'đăng nhập',                            // vi
            'login', 'log in', 'sign in', 'sign-in', // en
            'inicio de sesión', 'inicio de sesion',  // es
            'início de sessão', 'inicio de sessao',  // pt
            'connexion',                            // fr
            'anmeldung', 'anmelden',                // de
            'inloggen',                             // nl
            'logowanie',                            // pl
            'вход', 'вхід',                          // ru, uk
            'giriş',                                // tr
            'masuk',                                // id, ms
            'เข้าสู่ระบบ',                              // th
            'ログイン',                               // ja
            '로그인',                                 // ko
            '登录', '登入',                           // zh
            'تسجيل الدخول',                          // ar
            'लॉगिन',                                 // hi
            'התחברות',                               // he
            'inloggning', 'innlogging',             // sv, no
            'kirjautumis',                          // fi
            'přihlášení',                           // cs
            'autentificare',                        // ro
            'σύνδεση',                              // el
            'bejelentkezés',                        // hu
        ],

    ],

];
