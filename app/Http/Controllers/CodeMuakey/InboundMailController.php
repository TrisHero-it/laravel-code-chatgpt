<?php

namespace App\Http\Controllers\CodeMuakey;

use App\Http\Controllers\Controller;
use App\Services\InboundMailService;
use Illuminate\Http\Request;

/**
 * Trang đọc tạm hộp thư netflix@lumekza.io.vn (lấy qua API inbound-mail).
 */
class InboundMailController extends Controller
{
    private const MAILBOX = 'netflix@lumekza.io.vn';
    private const PER_PAGE = 20;

    public function index(Request $request, InboundMailService $inbound)
    {
        $before = $request->query('before');
        $result = $inbound->list(self::MAILBOX, is_string($before) ? $before : null, self::PER_PAGE);

        return view('code-muakey.tools.inbound-mail.index', [
            'mailbox' => self::MAILBOX,
            'emails' => array_map(
                fn ($mail) => $mail + ['recipient' => $inbound->recipientOf($mail)],
                $result['items']
            ),
            'nextBefore' => $result['next_before'],
            'isFirstPage' => empty($before),
        ]);
    }

    public function show(string $id, InboundMailService $inbound)
    {
        $email = $inbound->find($id);

        // Chỉ cho xem mail thuộc hộp này
        $to = mb_strtolower((string) ($email['envelope']['to'] ?? ''), 'UTF-8');
        abort_if(!$email || $to !== self::MAILBOX, 404);

        return view('code-muakey.tools.inbound-mail.show', [
            'mailbox' => self::MAILBOX,
            'email' => $email,
        ]);
    }
}
