<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Netflix;
use DateInterval;
use DateTime;
use DateTimeZone;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Exception;
use Illuminate\Http\Request;

class CodeController extends Controller
{
    const API_URL = "https://api.mail.tm/";

    public function search(Request $request)
    {
        header('Content-Type: application/json; charset=utf-8');

        $emailParam = $request->email ?? '';
        $email = $request->email ?? '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid email']);
            exit;
        }
        if (!filter_var($emailParam, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid email']);
            exit;
        }
        $email = mb_strtolower(trim($emailParam), 'UTF-8');
        if ($email === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'email required']);
            return;
        }

        $account = Netflix::query()->where('email', $email)->first();

        if ($account != null) {
            $password = str_replace('\\', '\\\\', $account['password']);
            $password = str_replace('"', '\\"', $password);
            $data = [
                'address' => $account['email'],
                'password' => $password
            ];
            $tokenResp = $this->getToken($data);
            $tokenValue = isset($tokenResp) ? (json_decode($tokenResp)->token ?? null) : null;
            if (!$tokenValue) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'token invalid']);
                return;
            }
            $tokens = [$tokenValue];
        } else {
            // fallback: dùng pool token mặc định
            $tokens = [
                "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE3NzY3Mzc0MDksInJvbGVzIjpbIlJPTEVfVVNFUiJdLCJhZGRyZXNzIjoibWluaHRyaTIwNEBkZWx0YWpvaG5zb25zLmNvbSIsImlkIjoiNjlkZTE1OWQ3OTgyNjA0ZGRmMGIxMjAzIiwibWVyY3VyZSI6eyJzdWJzY3JpYmUiOlsiL2FjY291bnRzLzY5ZGUxNTlkNzk4MjYwNGRkZjBiMTIwMyJdfX0.9iJMgTADZfCItCCmtwhyRXs5ouCVGXT8XLXvTYa8I0y6ND6AUO5GXKPTnjl-RYfuC-B7tbUPzSWwMZUaFdjnsQ",
                "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE3NzY4MzEyNTEsInJvbGVzIjpbIlJPTEVfVVNFUiJdLCJhZGRyZXNzIjoibmh1bmd0YW5iYTIzMTVAZGVsdGFqb2huc29ucy5jb20iLCJpZCI6IjY5ZTZmYWUyMDNlNmFkOTQ0ZjBkNDg1OSIsIm1lcmN1cmUiOnsic3Vic2NyaWJlIjpbIi9hY2NvdW50cy82OWU2ZmFlMjAzZTZhZDk0NGYwZDQ4NTkiXX19.2jveP38qRzz2jXSYs29qy8_-WIMu66h3AVP3llTLuYCqU4U1-Ne5LbtULfVljHnUn7ISHSs_bVrYCUzOEN0jkw",
                "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE3NzY4NDI1OTEsInJvbGVzIjpbIlJPTEVfVVNFUiJdLCJhZGRyZXNzIjoidnVzaXh1a2ExMjMzQGRlbHRham9obnNvbnMuY29tIiwiaWQiOiI2OWU3MDQ3MDkwZWQzZTFiNmMwY2UzZGEiLCJtZXJjdXJlIjp7InN1YnNjcmliZSI6WyIvYWNjb3VudHMvNjllNzA0NzA5MGVkM2UxYjZjMGNlM2RhIl19fQ.P-ylAqgQ6SzUmN3eM1IphDBe12uIQUx7NrFpGBPLopUhV1MMmQBts5WJrQKidvbEeKHb2ukOwVWeLFQefhaoGw",
                "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE3NzY4NDI2MjIsInJvbGVzIjpbIlJPTEVfVVNFUiJdLCJhZGRyZXNzIjoic3VhYm5jaDEzNDExM0BkZWx0YWpvaG5zb25zLmNvbSIsImlkIjoiNjllNzIwNTg2MWVmMzc3ODA2MGRjZTljIiwibWVyY3VyZSI6eyJzdWJzY3JpYmUiOlsiL2FjY291bnRzLzY5ZTcyMDU4NjFlZjM3NzgwNjBkY2U5YyJdfX0.n3E9aibB2pif-FySoCrKZFTPiSP2fVCgxn9d6ycYH9AON9aWW0FPRmqbmO4eEBc27u6Qe2dwSZdD_J9xotvjQA",
                "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE3NzY4NDU5OTUsInJvbGVzIjpbIlJPTEVfVVNFUiJdLCJhZGRyZXNzIjoibWluaHhpbmhkZXAxMjMxQGRlbHRham9obnNvbnMuY29tIiwiaWQiOiI2OWU3MjhlNmM1MjNlOWNhZDIwZDhhYmUiLCJtZXJjdXJlIjp7InN1YnNjcmliZSI6WyIvYWNjb3VudHMvNjllNzI4ZTZjNTIzZTljYWQyMGQ4YWJlIl19fQ.z5dvLykmNKn7kCuLo9W7IFHY-Yz0A3UvPFu2sT6bkz8izaTppoCxmrijJQvl5GjcfBfpNqoYTNjvLiz0XC4nbQ",
                "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE3NzY4NDYwMzAsInJvbGVzIjpbIlJPTEVfVVNFUiJdLCJhZGRyZXNzIjoidHJpZ2F5bG8xMjNAZGVsdGFqb2huc29ucy5jb20iLCJpZCI6IjY5ZTcyYmQyMjJmYzM1NGEyMzA5M2Y3YyIsIm1lcmN1cmUiOnsic3Vic2NyaWJlIjpbIi9hY2NvdW50cy82OWU3MmJkMjIyZmMzNTRhMjMwOTNmN2MiXX19.YcTGrFIAHE_zUmMiuqROYg_vjJK4Nwj-2UH5QYnQpVKOaaLcaMsnZ-Fqqg2OXOBM7jBfF58fdapI6ULK-NT5Mw",
                "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE3NzY4NDYwNjIsInJvbGVzIjpbIlJPTEVfVVNFUiJdLCJhZGRyZXNzIjoiZW10cmlyYXRnYXkzMTkyMUBkZWx0YWpvaG5zb25zLmNvbSIsImlkIjoiNjllNzMyYjNiNWIxNTg5YWQ2MDIzMWY5IiwibWVyY3VyZSI6eyJzdWJzY3JpYmUiOlsiL2FjY291bnRzLzY5ZTczMmIzYjViMTU4OWFkNjAyMzFmOSJdfX0.HeIVGoENE_u9y_Vd6F4joJZWyFXpk7m2zJgrF81IuJrCWVXohKLnQjs_7IsxTaCAdfMgtvt6n8E2VnkOlA9z7w",
                "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE3NzY4NDYwOTEsInJvbGVzIjpbIlJPTEVfVVNFUiJdLCJhZGRyZXNzIjoiZW10cmlyYXRnYXkyMjEwQGRlbHRham9obnNvbnMuY29tIiwiaWQiOiI2OWU3M2E0ODExOGRmMTZhZjkwZDg0YTAiLCJtZXJjdXJlIjp7InN1YnNjcmliZSI6WyIvYWNjb3VudHMvNjllNzNhNDgxMThkZjE2YWY5MGQ4NGEwIl19fQ.EXi3Bw6JRmVNJsvKiK5-e2TTyxAFHLOhOKzZMIpidt_0KXA0wfTZGfOT224zgPIjwFodhdYZEzoKQVzZOiEZig",
                "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE3NzY4NDYxMjAsInJvbGVzIjpbIlJPTEVfVVNFUiJdLCJhZGRyZXNzIjoiZ2F5dG9xdWExMjMxQGRlbHRham9obnNvbnMuY29tIiwiaWQiOiI2OWU3NWQ3YzhlYmM0MWU4MmIwNGU0MzIiLCJtZXJjdXJlIjp7InN1YnNjcmliZSI6WyIvYWNjb3VudHMvNjllNzVkN2M4ZWJjNDFlODJiMDRlNDMyIl19fQ.nWxDWU6iqocXOqiYhkw6ri2L12xAqU06bB1xedafgFoGrE5R2UBEI_EdHYPdNlKaa_8ePgHluSDNlyKvd2brQQ",
                "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE3NzY4NDYxNDYsInJvbGVzIjpbIlJPTEVfVVNFUiJdLCJhZGRyZXNzIjoidGhhdHZ1aXN1b25nMTI3MkBkZWx0YWpvaG5zb25zLmNvbSIsImlkIjoiNjllNzVkYTMxZjM1NWI4MTdjMGQ1MmI2IiwibWVyY3VyZSI6eyJzdWJzY3JpYmUiOlsiL2FjY291bnRzLzY5ZTc1ZGEzMWYzNTViODE3YzBkNTJiNiJdfX0.I26gIDUR15Z64pMrECJEVI7S6zeoHs7MrEQPhuisFf-0bTPaNGKY7O0jYc0ZBH5NO-tvY4nMtPTTlMn3_FEdQQ",
                "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE3NzY4NDYxNzQsInJvbGVzIjpbIlJPTEVfVVNFUiJdLCJhZGRyZXNzIjoiY2hpbWRhaWRlbW5hY2sxMjM1QGRlbHRham9obnNvbnMuY29tIiwiaWQiOiI2OWU3NWRjZDI4OGE0YjdkZWUwYzgwNTAiLCJtZXJjdXJlIjp7InN1YnNjcmliZSI6WyIvYWNjb3VudHMvNjllNzVkY2QyODhhNGI3ZGVlMGM4MDUwIl19fQ.zo9TTqxzkV6cWhbGcxBw1Q74g1Gc4pzN17zWIc4DvMvOuM_d5-j7OXkxzUxXZGYDfrriLroJ0Cgl3HnSR2I_Ow",
                "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE3NzY4NDYxOTUsInJvbGVzIjpbIlJPTEVfVVNFUiJdLCJhZGRyZXNzIjoibmd1b2l0aG8xMjNAZGVsdGFqb2huc29ucy5jb20iLCJpZCI6IjY5ZTc1ZGZiNGY4M2E3MjgzMzAxMDk2MiIsIm1lcmN1cmUiOnsic3Vic2NyaWJlIjpbIi9hY2NvdW50cy82OWU3NWRmYjRmODNhNzI4MzMwMTA5NjIiXX19.DR4oLMW7tIekgzsi6WOt9yEuU59dzN51d69sGyAw4X-ioAb4aR51xoCbzjpom9A6VJ-KhRoPlPhPRMiTGtsslQ",
                "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE3NzY4NDYyMTUsInJvbGVzIjpbIlJPTEVfVVNFUiJdLCJhZGRyZXNzIjoibWFkaWJ1MjM2MUBkZWx0YWpvaG5zb25zLmNvbSIsImlkIjoiNjllNzVlMmYxMThkZjE2YWY5MGQ4OGE5IiwibWVyY3VyZSI6eyJzdWJzY3JpYmUiOlsiL2FjY291bnRzLzY5ZTc1ZTJmMTE4ZGYxNmFmOTBkODhhOSJdfX0.r4WK4ym63IgiHfutzIsfzfAyUnr0XtbCe4AMp7MwLRikUkLaib2RoVGdn8eeAQ4aYNb8iEISyLGAnlx-UAWDMg",
                "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE3NzY4NDYyNDcsInJvbGVzIjpbIlJPTEVfVVNFUiJdLCJhZGRyZXNzIjoiZ29rdWRhMTI1QGRlbHRham9obnNvbnMuY29tIiwiaWQiOiI2OWU3NWU3ODhhMTg0NTZiYmYwMTE0ZjMiLCJtZXJjdXJlIjp7InN1YnNjcmliZSI6WyIvYWNjb3VudHMvNjllNzVlNzg4YTE4NDU2YmJmMDExNGYzIl19fQ.Cv4e2CxdM-ww8RLmLgKGYlQ0EK4blSPdasW8ypTKaLoUi6IH_reRddLZvTxZkxRpT99pnpXchSFr8SMqArtQiQ",
                "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE3NzY4NDYyODEsInJvbGVzIjpbIlJPTEVfVVNFUiJdLCJhZGRyZXNzIjoidG9pbGFtYTEyNUBkZWx0YWpvaG5zb25zLmNvbSIsImlkIjoiNjllNzVlOTY4NTZmYjAyNmJkMGNjYTkyIiwibWVyY3VyZSI6eyJzdWJzY3JpYmUiOlsiL2FjY291bnRzLzY5ZTc1ZTk2ODU2ZmIwMjZiZDBjY2E5MiJdfX0.hlvTpNafz0nFG8XWBJL0qlqdQ4SzBY-HSd3eMmvLhnKXJi4f026xlR9G2m_tW-vIgivS2NTj85M6mgyZfJ1l3g",
                "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE3NzY4NDYzMDEsInJvbGVzIjpbIlJPTEVfVVNFUiJdLCJhZGRyZXNzIjoibmd1b2luZ29haWN1MTJAZGVsdGFqb2huc29ucy5jb20iLCJpZCI6IjY5ZTc1ZjI0NGZhYWZkYjBmNjA0ODc2YiIsIm1lcmN1cmUiOnsic3Vic2NyaWJlIjpbIi9hY2NvdW50cy82OWU3NWYyNDRmYWFmZGIwZjYwNDg3NmIiXX19.DybZJcxR0F96kEew3X7tUPKWZ6VMUj0cSq9IQbj2DG0sgcfm05fwfWpNQeQmRfj7JGAXogPSR8s-omshRcxPLQ",
                "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE3NzY4NDYzMzAsInJvbGVzIjpbIlJPTEVfVVNFUiJdLCJhZGRyZXNzIjoiY3V0YXkxMjNAZGVsdGFqb2huc29ucy5jb20iLCJpZCI6IjY5ZTc1ZjNmMzE2ZWM0YTkyODA2ZTJkMiIsIm1lcmN1cmUiOnsic3Vic2NyaWJlIjpbIi9hY2NvdW50cy82OWU3NWYzZjMxNmVjNGE5MjgwNmUyZDIiXX19.ALz4flmpbduj50DL2li4NR9r_Am8HoPM4QMh2YYdN06s6E4JojWJMqZuq5-TWxIkkO8Wib-7T0I1w2l6ZwRAug",
                "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE3NzY4NDYzNDksInJvbGVzIjpbIlJPTEVfVVNFUiJdLCJhZGRyZXNzIjoibmd1b2ljb3Z1MTJAZGVsdGFqb2huc29ucy5jb20iLCJpZCI6IjY5ZTc1ZjVkMmExN2Q1M2Q2ZTA5YThkNyIsIm1lcmN1cmUiOnsic3Vic2NyaWJlIjpbIi9hY2NvdW50cy82OWU3NWY1ZDJhMTdkNTNkNmUwOWE4ZDciXX19.Zsgajg5utJoWI7kOY-BxmiGplX-zlwB43FoXY706CEGFc7PW4ED--JmMbbSjfo6agn4PWhpJMaYnl_mOybbHqg",
                "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE3NzY4NDYzNjQsInJvbGVzIjpbIlJPTEVfVVNFUiJdLCJhZGRyZXNzIjoibmd1b2l0YXljaGltMUBkZWx0YWpvaG5zb25zLmNvbSIsImlkIjoiNjllNzVmN2NmNGMxOGIxOGQ3MDhiOWQ3IiwibWVyY3VyZSI6eyJzdWJzY3JpYmUiOlsiL2FjY291bnRzLzY5ZTc1ZjdjZjRjMThiMThkNzA4YjlkNyJdfX0.uN68gX7sShxi--J6AMAMvMlVByf08hPKBdgjVwZPjcJMs_XX_8jK0TFLc-Nw4zqvTn0JwxROQCluJGT2u86pDw",
                "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE3NzY4NDY0NDAsInJvbGVzIjpbIlJPTEVfVVNFUiJdLCJhZGRyZXNzIjoibHVhY2F5MTI1M0BkZWx0YWpvaG5zb25zLmNvbSIsImlkIjoiNjllN2FjNWViMzI3NDUzN2VhMGU5YjM2IiwibWVyY3VyZSI6eyJzdWJzY3JpYmUiOlsiL2FjY291bnRzLzY5ZTdhYzVlYjMyNzQ1MzdlYTBlOWIzNiJdfX0.u1jAxV3g53LKVbE1No9yTj5Nz4O5U3nczCIz_tn20KHvHjADVPD6A8HqoiBJHZ2kaKMpG6bUMc2gw6k3RvzEiw",
                "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE3NzY4NDY0NTcsInJvbGVzIjpbIlJPTEVfVVNFUiJdLCJhZGRyZXNzIjoibmd1b2lwaG8xMjM0QGRlbHRham9obnNvbnMuY29tIiwiaWQiOiI2OWU4MzlkMzhiMDkzNjBiYWMwMGRmNWIiLCJtZXJjdXJlIjp7InN1YnNjcmliZSI6WyIvYWNjb3VudHMvNjllODM5ZDM4YjA5MzYwYmFjMDBkZjViIl19fQ.dAd_esPqgxadGw0z-MJCmOEi_iI2hkf_lvILZ0C3y8_KbLLrgo2FgzzT5mUohlq_OVCqm8vF3RQAxHyGZGdONA",
                "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE3NzY4NDY0NzEsInJvbGVzIjpbIlJPTEVfVVNFUiJdLCJhZGRyZXNzIjoibmd1b2luaGVuMTIzQGRlbHRham9obnNvbnMuY29tIiwiaWQiOiI2OWU4MzlmNzMwZWVjMzQwZGUwZjczODQiLCJtZXJjdXJlIjp7InN1YnNjcmliZSI6WyIvYWNjb3VudHMvNjllODM5ZjczMGVlYzM0MGRlMGY3Mzg0Il19fQ.YxJSfbiwAef9dXf7joJsQ9xx37G0Ge8Mv0LLDNU9MwOnwQjZd2lmz80wMiOcZNoYUoNu1wQRcfywk8Nk4i6ftg",
            ];
        }

        $multiHandle = curl_multi_init();
        $curlHandles = [];
        $curlHandleTokens = [];

        $url = self::API_URL . "messages";
        foreach ($tokens as $t) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer {$t}"]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_multi_add_handle($multiHandle, $ch);
            $curlHandles[] = $ch;
            $curlHandleTokens[(int)$ch] = $t;
        }

        do {
            $status = curl_multi_exec($multiHandle, $active);
        } while ($status === CURLM_CALL_MULTI_PERFORM || $active);

        $merged = [];
        foreach ($curlHandles as $ch) {
            $body = curl_multi_getcontent($ch);
            $t = $curlHandleTokens[(int)$ch] ?? null;
            curl_multi_remove_handle($multiHandle, $ch);
            curl_close($ch);

            $decoded = json_decode($body ?: '', true);
            if (!is_array($decoded)) continue;
            $members = $decoded['hydra:member'] ?? null;
            if (!is_array($members)) continue;
            foreach ($members as $member) {
                if (!is_array($member)) continue;
                $member['_token'] = $t;
                $merged[] = $member;
            }
        }
        curl_multi_close($multiHandle);

        usort($merged, function ($a, $b) {
            return strtotime($b['createdAt'] ?? '') - strtotime($a['createdAt'] ?? '');
        });

        // chỉ lấy mail trong vòng 15 phút (giờ VN)
        $now = new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
        $cutoff = clone $now;
        $cutoff->sub(new DateInterval('PT15M'));

        $items = [];
        foreach ($merged as $item) {
            $fromName = $item['from']['name'] ?? null;
            $toAddr = $item['to'][0]['address'] ?? null;
            $tokenForItem = $item['_token'] ?? null;
            $messageId = $item['@id'] ?? null;
            $createdAtRaw = $item['createdAt'] ?? null;

            if (!($fromName === 'Netflix' && $toAddr && mb_strtolower($toAddr, 'UTF-8') === $email)) continue;
            if (!$tokenForItem || !$messageId) continue;
            if (!$createdAtRaw) continue;

            try {
                $createdAt = new DateTime($createdAtRaw, new DateTimeZone('UTC'));
                $createdAt->setTimezone(new DateTimeZone('Asia/Ho_Chi_Minh'));
            } catch (Exception $e) {
                continue;
            }

            if ($createdAt < $cutoff) continue;

            $verifyLink = $this->extractNetflixVerifyLink($tokenForItem, $messageId);
            if (!$verifyLink) continue;

            $items[] = [
                'id' => $messageId,
                'subject' => $item['subject'] ?? '',
                'createdAt' => $createdAtRaw,
                'verifyLink' => $verifyLink,
            ];

            // lấy 10 mail mới nhất có link để tránh quá tải
            if (count($items) >= 10) break;
        }

        echo json_encode([
            'success' => true,
            'message' => 'ok',
            'data' => $items,
        ]);
    }

    private function extractNetflixVerifyLink(string $token, string $messageId): ?string
    {
        $url = "https://api.mail.tm" . $messageId;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$token}"
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $body = curl_exec($ch);
        curl_close($ch);

        if (!$body) return null;
        $decoded = json_decode($body, true);
        if (!is_array($decoded)) return null;

        $html = $decoded['html'] ?? null;
        if (is_array($html)) $html = $html[0] ?? null;
        if (!is_string($html) || $html === '') return null;

        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $nodes = $xpath->query('//a[contains(@href,"netflix.com/account/travel/verify")]');
        if (!$nodes || $nodes->length === 0) return null;

        $node = $nodes->item(0);
        if (!$node instanceof DOMElement) return null;
        $href = $node->getAttribute('href');
        return $href !== '' ? $href : null;
    }

    public function getToken(array $accounts)
    {
        $url = 'https://api.mail.tm/token';

        // Dữ liệu cần gửi
        $data = [
            'address' => $accounts['address'],
            'password' => $accounts['password']
        ];

        // Khởi tạo CURL
        $ch = curl_init($url);

        // Thiết lập các tùy chọn
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);

        // Gửi dữ liệu JSON
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        // Thực thi CURL và lấy kết quả
        $response = curl_exec($ch);

        // Đóng CURL
        curl_close($ch);
        return $response;
    }
}
