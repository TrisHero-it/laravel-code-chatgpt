<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MidasbuyToken\StoreMidasbuyTokenRequest;
use App\Models\MidasbuyToken;
use Illuminate\Http\Request;

class MidasbuyTokenController extends Controller
{
    /** Số phút hoãn mặc định khi đơn thiếu code token */
    private const DEFAULT_DELAY_MINUTES = 30;

    public function index()
    {
        // Đơn nào hết thời gian hoãn thì cho quay lại hàng chờ trước khi lấy đơn
        MidasbuyToken::releaseExpiredDelays();

        $midasbuyToken = MidasbuyToken::where('status', MidasbuyToken::STATUS_PENDING)
            ->orderBy('id')
            ->first();

        return response()->json($midasbuyToken);
    }

    public function store(StoreMidasbuyTokenRequest $request)
    {
        $midasbuyToken = MidasbuyToken::create($request->validated());
        return response()->json($midasbuyToken, 201);
    }

    /**
     * Hoãn đơn lại (mặc định 30 phút) để tool bỏ qua và làm các đơn khác trước.
     * Dùng khi kho code token không còn code cho mức token của đơn này.
     */
    public function delay(Request $request, $id)
    {
        $midasbuyToken = MidasbuyToken::find($id);

        if (!$midasbuyToken) {
            return response()->json(['message' => 'Không tìm thấy đơn.'], 404);
        }

        $minutes = (int) $request->input('minutes', self::DEFAULT_DELAY_MINUTES);
        if ($minutes < 1) {
            $minutes = self::DEFAULT_DELAY_MINUTES;
        }

        $reason = $request->input('reason', 'Thiếu code token');
        $midasbuyToken->delay($minutes, $reason);

        return response()->json([
            'message'       => "Đã hoãn đơn {$minutes} phút.",
            'id'            => $midasbuyToken->id,
            'status'        => $midasbuyToken->status,
            'delayed_until' => $midasbuyToken->delayed_until,
            'delay_reason'  => $midasbuyToken->delay_reason,
        ]);
    }

    /**
     * Chốt đơn: cập nhật trạng thái + code + ảnh chứng minh.
     *
     * Thay cho việc tool điền form admin bằng puppeteer. Form puppeteer phải
     * mở thêm tab, chờ selector, upload file rồi submit - mỗi bước đều có thể
     * treo. Khi nó treo thì đơn vẫn "pending" nên vòng sau tool xin code MỚI
     * và nạp lại, làm mất code thật (đơn 9318 đã bị nạp 4 lần như vậy).
     *
     * Gọi bằng multipart POST để kèm được file ảnh.
     */
    public function complete(Request $request, $id)
    {
        $midasbuyToken = MidasbuyToken::find($id);

        if (!$midasbuyToken) {
            return response()->json(['message' => 'Không tìm thấy đơn.'], 404);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,delayed,success,cancelled',
            'code'   => 'nullable|string|max:255',
            'image'  => 'nullable|image|max:10240',
        ]);

        $data = ['status' => $validated['status']];

        if (!empty($validated['code'])) {
            $data['code'] = $validated['code'];
        }

        if ($request->hasFile('image')) {
            // Xoá ảnh cũ nếu có
            if ($midasbuyToken->image && file_exists(public_path($midasbuyToken->image))) {
                unlink(public_path($midasbuyToken->image));
            }

            $image = $request->file('image');
            $fileName = time() . '_' . $image->getClientOriginalName();
            $destinationPath = public_path('uploads/midasbuy-token');

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            $image->move($destinationPath, $fileName);
            $data['image'] = 'uploads/midasbuy-token/' . $fileName;
        }

        // Chốt đơn thì hết hoãn
        $data['delayed_until'] = null;
        $data['delay_reason'] = null;

        $midasbuyToken->update($data);

        return response()->json([
            'message' => "Đã cập nhật đơn {$id} -> \"{$validated['status']}\".",
            'id'      => $midasbuyToken->id,
            'status'  => $midasbuyToken->status,
            'code'    => $midasbuyToken->code,
            'image'   => $midasbuyToken->image,
        ]);
    }

    /**
     * Hoãn TẤT CẢ đơn đang chờ của một mức token (mặc định 30 phút).
     * Dùng khi kho hết code mức đó: mọi đơn cùng mức đều không nạp được nên
     * hoãn cả cụm, tool chuyển sang mức token khác. Cả cụm dùng chung một mốc
     * nên khi hết hạn đơn cũ nhất của mức đó vẫn được chạy trước.
     */
    public function delayToken(Request $request)
    {
        $token = $request->input('token');

        if ($token === null || $token === '') {
            return response()->json(['message' => 'Thiếu tham số token.'], 422);
        }

        $minutes = (int) $request->input('minutes', self::DEFAULT_DELAY_MINUTES);
        if ($minutes < 1) {
            $minutes = self::DEFAULT_DELAY_MINUTES;
        }

        $reason = $request->input('reason', "Thiếu code {$token} tokens");
        $count = MidasbuyToken::delayAllByToken((string) $token, $minutes, $reason);

        return response()->json([
            'message'       => "Đã hoãn {$count} đơn mức {$token} tokens trong {$minutes} phút.",
            'token'         => (string) $token,
            'delayed_count' => $count,
            'minutes'       => $minutes,
            'delay_reason'  => $reason,
        ]);
    }
}
