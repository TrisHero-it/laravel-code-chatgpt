<?php

namespace App\Http\Controllers\CodeMuakey;

use App\Http\Controllers\Controller;
use App\Models\ForwardMailbox;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ForwardMailboxController extends Controller
{
    public function index()
    {
        $mailboxes = ForwardMailbox::orderBy('id')->get();
        return view('code-muakey.tools.forward-mailbox.index', compact('mailboxes'));
    }

    /**
     * Thêm hàng loạt: mỗi dòng "email|mật khẩu" (hoặc cách bằng tab/khoảng trắng).
     * Email đã có thì cập nhật mật khẩu và bỏ token cache để đăng nhập lại.
     */
    public function store(Request $request)
    {
        $request->validate(['lines' => 'required|string']);

        $added = 0;
        $updated = 0;
        $invalid = [];
        foreach (preg_split('/\R/u', $request->input('lines')) as $line) {
            $line = trim($line);
            if ($line === '') continue;

            $parts = preg_split('/\s*\|\s*|\t+|\s+/u', $line, 2);
            $address = mb_strtolower(trim($parts[0] ?? ''), 'UTF-8');
            $password = trim($parts[1] ?? '');
            if (!filter_var($address, FILTER_VALIDATE_EMAIL) || $password === '') {
                $invalid[] = $line;
                continue;
            }

            $mailbox = ForwardMailbox::firstOrNew(['address' => $address]);
            $mailbox->exists ? $updated++ : $added++;
            $mailbox->fill(['password' => $password, 'active' => true])->save();
            Cache::forget(ForwardMailbox::tokenCacheKey($address));
        }

        $message = "Đã thêm {$added} hòm, cập nhật {$updated} hòm.";
        if ($invalid) {
            $message .= ' Bỏ qua ' . count($invalid) . ' dòng sai định dạng: ' . implode(', ', $invalid);
        }

        return redirect()->route('forward-mailbox.index')->with('success', $message);
    }

    public function update($id)
    {
        $mailbox = ForwardMailbox::findOrFail($id);
        $mailbox->update(['active' => !$mailbox->active]);

        return redirect()->route('forward-mailbox.index')
            ->with('success', ($mailbox->active ? 'Đã bật ' : 'Đã tắt ') . $mailbox->address);
    }

    public function destroy($id)
    {
        $mailbox = ForwardMailbox::findOrFail($id);
        Cache::forget(ForwardMailbox::tokenCacheKey($mailbox->address));
        $mailbox->delete();

        return redirect()->route('forward-mailbox.index')->with('success', 'Đã xoá ' . $mailbox->address);
    }
}
