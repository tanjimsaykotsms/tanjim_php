<?php
error_reporting(0);
ini_set('max_execution_time', 0);
date_default_timezone_set('Asia/Dhaka');

// ==========================================
// ⚙️ BOT CONFIGURATION
// ==========================================
define('API_KEY', '8950363077:AAEj5xWcxu7OdEnGKmC7gRHkkkcHx12g1ik'); 
define('ADMIN_ID', '5587354616'); 

// ==========================================
// 💸 BINANCE AUTO PAY API CONFIGURATION
// ==========================================
define('BINANCE_API_KEY', 'bBM6re4lpCCD1E6krhAoIEt71b1Ou9lMOA5Rz3Q9H7nfsIwur2nMzwm9NUmG7nTz');
define('BINANCE_API_SECRET', 'm3F6J0uhGX00YV0hyEjn4blRtmZXuDdNFF2cTU0irF6Upi3gKXk9TzYSRfB7tOOJ');

$bot_username = "@fast_tanjim_bot";

// ==========================================
// 🎨 CUSTOM EMOJI IDS (From Outline & Application Emoji Files)
// ==========================================
$CUSTOM_EMOJI = [
    'smile' => '5451709985765468632',   // File 6: 😀
    'laugh' => '5458587027270280607',   // File 6: 😂
    'love'  => '5456149049214249060',   // File 7: 🥰
    'star'  => '5395597754166682968',   // File 7: 🤩
    'money' => '5280818098960611598',   // File 7: 🤑
    'done'  => '5458604417592863845',   // File 7: 👍
    'error' => '5458772252029887718',   // File 7: 👎
    'fire'  => '5289722755871162900',   // File 7: 🔥
    'warning'=> '5379725114113805128',  // File 7: 😤
    'rocket'=> '5372917041193828849',   // File 7: 🚀
    'bot'   => '5355051922862653659',   // File 7: 🤖
    'gift'  => '5359664288241829619',   // File 7: 🎁
    'party' => '5458806031947671561',   // File 7: 🥳
    'box'   => '5330345557284109044'    // File 7: 📱
];

// Custom Emoji Wrapper Function
function emo($id, $fallback) {
    return "<tg-emoji emoji-id=\"{$id}\">{$fallback}</tg-emoji>";
}

@mkdir('data');
$db_files = [
    'users.json' => '{}', 
    'settings.json' => '{}', 
    'proofs.json' => '[]', 
    'withdraws.json' => '[]'
];

foreach ($db_files as $file => $default) { 
    if (!file_exists("data/$file")) {
        file_put_contents("data/$file", $default); 
    }
}

function readDB($filename) { 
    $path = "data/$filename";
    $backup_path = "data/$filename.bak";
    
    if (!file_exists($path)) {
        if (file_exists($backup_path)) {
            @copy($backup_path, $path);
        } else {
            return [];
        }
    }
    
    $fp = @fopen($path, "r");
    if (!$fp) return [];
    
    @flock($fp, LOCK_SH);
    $size = @filesize($path);
    $content = $size > 0 ? @fread($fp, $size) : "";
    @flock($fp, LOCK_UN);
    @fclose($fp);
    
    if (empty($content)) {
        if (file_exists($backup_path) && filesize($backup_path) > 0) {
            $content = @file_get_contents($backup_path);
            @file_put_contents($path, $content, LOCK_EX);
        } else {
            return [];
        }
    }
    
    $data = json_decode($content, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        if (file_exists($backup_path)) {
            $backup_content = @file_get_contents($backup_path);
            $backup_data = json_decode($backup_content, true);
            if (is_array($backup_data)) {
                @file_put_contents($path, $backup_content, LOCK_EX);
                return $backup_data;
            }
        }
        return []; 
    }
    return is_array($data) ? $data : [];
}

function writeDB($filename, $data) { 
    $path = "data/$filename";
    $backup_path = "data/$filename.bak";
    
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if ($json === false) return false;
    
    $fp = @fopen($path, "c+");
    if (!$fp) return false;
    
    if (@flock($fp, LOCK_EX)) {
        @ftruncate($fp, 0);
        @rewind($fp);
        @fwrite($fp, $json);
        @fflush($fp);
        @flock($fp, LOCK_UN);
    }
    @fclose($fp);
    
    @file_put_contents($backup_path, $json, LOCK_EX);
    return true;
}

function setState($uid, $newState, $newTemp = '') {
    $users = readDB('users.json');
    if (isset($users[$uid])) {
        $users[$uid]['state'] = $newState;
        $users[$uid]['temp'] = $newTemp;
        writeDB('users.json', $users);
    }
}

$s = readDB('settings.json');
$defaults = [
    'force_channel' => '@rjonlinejobbd', 
    'force_channel_url' => 'https://t.me/rjonlinejobbd',
    'support_id' => 'https://t.me/tannimsaykot',
    'official_channel' => 'https://t.me/rjonlinejobbd',
    'ref_commission' => 5, 
    'ref_bonus_amount' => 2.00, 
    'min_wd_bkash' => 100,
    'bkash_charge' => 5,
    'min_wd_nagad' => 100,
    'nagad_charge' => 5,
    'min_wd_rocket' => 100, 
    'rocket_charge' => 5,   
    'min_wd_binance' => 200,
    'binance_charge' => 0,
    'status_wd_bkash' => 'open',  
    'status_wd_nagad' => 'open',  
    'status_wd_rocket' => 'open', 
    'status_wd_binance' => 'open',
    'reward_instagram' => 4.00,
    'reward_gmail' => 5.00,
    'reward_facebook' => 6.00,
    'reward_facebook_cookies' => 8.00, 
    'reward_instagram_cookies' => 8.00, 
    'video_instagram' => '',
    'admin_insta_password' => 'Pass@insta',   
    'admin_gmail_password' => 'Pass@gmail',   
    'admin_fb_password' => 'Pass@facebook',   
    'admin_fb_cookies_password' => 'Pass@cookies', 
    'admin_insta_cookies_password' => 'Pass@instacookies',   
    'bot_username' => '', 
    'work_videos' => ['https://youtube.com'],
    'status_instagram' => 'open',
    'status_gmail' => 'open',
    'status_facebook' => 'open',
    'status_facebook_cookies' => 'open',
    'status_instagram_cookies' => 'open' 
];

$updated = false; 
foreach ($defaults as $k => $v) { 
    if (!isset($s[$k])) { $s[$k] = $v; $updated = true; } 
}
if ($updated) writeDB('settings.json', $s);

function bot($method, $datas = []) {
    $url = "https://api.telegram.org/bot" . API_KEY . "/" . $method;
    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL, $url); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    curl_setopt($ch, CURLOPT_POSTFIELDS, $datas);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    $res = curl_exec($ch); 
    curl_close($ch); 
    return json_decode($res, true);
}

// ==========================================
// 💸 BINANCE AUTO PAYOUT FUNCTION
// ==========================================
function binanceAutoPayout($payId, $amount) {
    $apiKey = BINANCE_API_KEY;
    $apiSecret = BINANCE_API_SECRET;
    
    if (empty($apiKey) || empty($apiSecret)) {
        return ['status' => 'FAIL', 'errorMessage' => 'API keys are missing.'];
    }

    $timestamp = round(microtime(true) * 1000);
    $nonce = bin2hex(random_bytes(16));
    
    $body = json_encode([
        "requestId" => uniqid(),
        "batchName" => "Bot_Auto_Withdrawal",
        "currency" => "USDT",
        "totalAmount" => $amount,
        "totalNumber" => 1,
        "transferDetailList" => [
            [
                "merchantSendId" => uniqid(),
                "receiveType" => "PAY_ID",
                "receiver" => $payId,
                "transferAmount" => $amount,
                "transferReason" => "User Withdrawal from Bot"
            ]
        ]
    ]);

    $payload = $timestamp . "\n" . $nonce . "\n" . $body . "\n";
    $signature = strtoupper(hash_hmac('sha512', $payload, $apiSecret));

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://bpay.binanceapi.com/binancepay/openapi/payout/transfer");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "BinancePay-Timestamp: $timestamp",
        "BinancePay-Nonce: $nonce",
        "BinancePay-Certificate-SN: $apiKey",
        "BinancePay-Signature: $signature"
    ]);

    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result, true);
}

if (empty($s['bot_username'])) {
    $me = bot('getMe');
    if ($me['ok']) {
        $s['bot_username'] = $me['result']['username'];
        writeDB('settings.json', $s);
    }
}
$bot_username = $s['bot_username'] ?? "fb_ig_buyer_v_bot";

function rowContainsValue($row, $target) {
    $target = strtolower(trim($target));
    if (empty($target)) return false;
    foreach ($row as $cell) {
        $cell = strtolower(trim($cell));
        if ($cell === $target) return true;
        if (strpos($cell, $target) !== false) {
            return true;
        }
    }
    return false;
}

function generateUsername() {
    $first = ['rahim', 'karim', 'sajal', 'arif', 'tanvir', 'shakil', 'rony', 'mim', 'sultana', 'rifat', 'tamim', 'hasan', 'anik', 'joy', 'fahim', 'rubel', 'sumon', 'emran', 'nabil', 'sakib'];
    $last = ['khan', 'ahmed', 'hossain', 'ali', 'islam', 'rahman', 'chowdhury', 'bhuiyan', 'shaikh', 'miah', 'talukder'];
    return $first[array_rand($first)] . "_" . $last[array_rand($last)] . rand(100, 9999);
}

function generateFbNameArray() {
    $first = ['Rahim', 'Karim', 'Sajal', 'Arif', 'Tanvir', 'Shakil', 'Rony', 'Mim', 'Sultana', 'Rifat', 'Tamim', 'Hasan', 'Anik', 'Joy', 'Fahim'];
    $last = ['Khan', 'Ahmed', 'Hossain', 'Ali', 'Islam', 'Rahman', 'Chowdhury', 'Bhuiyan', 'Shaikh', 'Miah'];
    return ['first' => $first[array_rand($first)], 'last' => $last[array_rand($last)]];
}

function generateGmailData() {
    $first = ['rahim', 'karim', 'sajal', 'arif', 'tanvir', 'shakil', 'rony', 'mim', 'sultana', 'rifat', 'tamim', 'hasan', 'anik', 'joy', 'fahim'];
    $last = ['khan', 'ahmed', 'hossain', 'ali', 'islam', 'rahman', 'chowdhury', 'bhuiyan', 'shaikh'];
    return ['email' => $first[array_rand($first)] . $last[array_rand($last)] . rand(100, 9999) . "@gmail.com"];
}

function isValidBase32Key($secret) {
    $secret = str_replace(' ', '', $secret);
    $len = strlen($secret);
    if ($len != 16 && $len != 24 && $len != 32) return false;
    return !preg_match('/[^A-Z2-7]/i', $secret);
}

function base32_decode_custom($secret) {
    $secret = strtoupper(preg_replace('/[^A-Z2-7]/', '', $secret));
    if (empty($secret)) return '';
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $binary = '';
    foreach (str_split($secret) as $char) {
        $pos = strpos($alphabet, $char);
        if ($pos === false) continue;
        $binary .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
    }
    $bytes = str_split($binary, 8);
    $out = '';
    foreach ($bytes as $byte) {
        if (strlen($byte) === 8) $out .= chr(bindec($byte));
    }
    return $out;
}

function getTOTP($secret) {
    $time_slice = floor(time() / 30);
    $secret_key = base32_decode_custom($secret);
    if (empty($secret_key)) return false;
    $time = pack('N*', 0) . pack('N*', $time_slice);
    $hmac = hash_hmac('sha1', $time, $secret_key, true);
    $offset = ord(substr($hmac, -1)) & 0x0F;
    $hash_part = substr($hmac, $offset, 4);
    $value = unpack('N', $hash_part);
    return str_pad(($value[1] & 0x7FFFFFFF) % 1000000, 6, '0', STR_PAD_LEFT);
}

function getTOTPWithOffset($secret, $offset_seconds = 0) {
    $time_slice = floor((time() + $offset_seconds) / 30);
    $secret_key = base32_decode_custom($secret);
    if (empty($secret_key)) return false;
    $time = pack('N*', 0) . pack('N*', $time_slice);
    $hmac = hash_hmac('sha1', $time, $secret_key, true);
    $offset = ord(substr($hmac, -1)) & 0x0F;
    $hash_part = substr($hmac, $offset, 4);
    $value = unpack('N', $hash_part);
    return str_pad(($value[1] & 0x7FFFFFFF) % 1000000, 6, '0', STR_PAD_LEFT);
}

function makeBtn($text) {
    return ['text' => $text];
}

function getMainMenu($uid, $lang = 'bn') {
    $menu = [
        [makeBtn('📝 কাজ ▾'), makeBtn('💸 ব্যালেন্স')],
        [makeBtn('💰 টাকা উত্তোলন'), makeBtn('🎁 My Referrals')],
        [makeBtn('🎬 কাজের ভিডিও'), makeBtn('💬 সাপোর্ট')],
        [makeBtn('⚙️ Settings')]
    ];
    if (strval($uid) === strval(ADMIN_ID)) {
        $menu[] = [makeBtn('⚙️ এডমিন প্যানেল')];
    }
    return json_encode(['keyboard' => $menu, 'resize_keyboard' => true]);
}

function getAdminMenu() {
    $menu = [
        [makeBtn('📊 সেন্ট্রাল শীট'), makeBtn('📥 পেন্ডিং উইথড্র')],
        [makeBtn('📊 পরিসংখ্যান'), makeBtn('🔍 ইউজার সার্চ')],
        [makeBtn('📢 ব্রডকাস্ট'), makeBtn('⚙️ বটের সেটিংস')],
        [makeBtn('🔙 ফিরে যান')]
    ];
    return json_encode(['keyboard' => $menu, 'resize_keyboard' => true]);
}

function getAdminCentralSheetMenu() {
    $menu = [
        [makeBtn('📸 ইনস্টাগ্রাম কন্ট্রোল'), makeBtn('📧 জিমেইল কন্ট্রোল')],
        [makeBtn('📘 ফেসবুক কন্ট্রোল'), makeBtn('🍪 ফেসবুক কুকিজ কন্ট্রোল')], 
        [makeBtn('🍪 ইন্সটা কুকিজ কন্ট্রোল'), makeBtn('🗑 ডাটাবেজ ক্লিয়ার')],
        [makeBtn('🔙 এডমিন মেনু')]
    ];
    return json_encode(['keyboard' => $menu, 'resize_keyboard' => true]);
}

function getInstaControlMenu() {
    return json_encode(['keyboard' => [
        [makeBtn('📥 ইনস্টাগ্রাম শীট ডাউনলোড')],
        [makeBtn('✅ ইন্সটা চালু আছে'), makeBtn('❌ ইন্সটা বন্ধ আছে')],
        [makeBtn('🔙 সেন্ট্রাল শীট')]
    ], 'resize_keyboard' => true]);
}

function getCookiesControlMenu() {
    return json_encode(['keyboard' => [
        [makeBtn('📥 ফেসবুক কুকিজ শীট ডাউনলোড')],
        [makeBtn('✅ কুকিজ চালু আছে'), makeBtn('❌ কুকিজ বন্ধ আছে')],
        [makeBtn('🔙 সেন্ট্রাল শীট')]
    ], 'resize_keyboard' => true]);
}

function getInstaCookiesControlMenu() {
    return json_encode(['keyboard' => [
        [makeBtn('📥 ইন্সটা কুকিজ শীট ডাউনলোড')],
        [makeBtn('✅ ইন্সটা কুকিজ চালু আছে'), makeBtn('❌ ইন্সটা কুকিজ বন্ধ আছে')],
        [makeBtn('🔙 সেন্ট্রাল শীট')]
    ], 'resize_keyboard' => true]);
}

function getGmailControlMenu() {
    return json_encode(['keyboard' => [
        [makeBtn('📥 জিমেইল শীট ডাউনলোড')],
        [makeBtn('✅ জিমেইল চালু আছে'), makeBtn('❌ জিমেইল বন্ধ আছে')],
        [makeBtn('🔙 সেন্ট্রাল শীট')]
    ], 'resize_keyboard' => true]);
}

function getFacebookControlMenu() {
    return json_encode(['keyboard' => [
        [makeBtn('📥 ফেসবুক শীট ডাউনলোড')],
        [makeBtn('✅ ফেসবুক চালু আছে'), makeBtn('❌ ফেসবুক বন্ধ আছে')],
        [makeBtn('🔙 সেন্ট্রাল শীট')]
    ], 'resize_keyboard' => true]);
}

function getAdminSettingsMenu() {
    $menu = [
        [makeBtn('💰 কাজের মূল্য সেট'), makeBtn('🔑 পাসওয়ার্ড সেটিংস')],
        [makeBtn('🎬 কাজের ভিডিও সেট'), makeBtn('🎁 ফিক্সড রেফার বোনাস')],
        [makeBtn('📘 কাজের পারসেন্টেজ কমিশন'), makeBtn('📱 লিমিট ও চার্জ সেট')],
        [makeBtn('📢 চ্যানেল সেটিংস'), makeBtn('⚙️ কাজ চালু/বন্ধ')],
        [makeBtn('⚙️ পেমেন্ট অন/অফ'), makeBtn('🔙 এডমিন মেনু')]
    ];
    return json_encode(['keyboard' => $menu, 'resize_keyboard' => true]);
}

function getAdminPaymentToggleMenu($s) {
    $bkash = ($s['status_wd_bkash'] ?? 'open') == 'open' ? '🔴 বিকাশ বন্ধ' : '🟢 বিকাশ চালু';
    $nagad = ($s['status_wd_nagad'] ?? 'open') == 'open' ? '🔴 নগদ বন্ধ' : '🟢 নগদ চালু';
    $rocket = ($s['status_wd_rocket'] ?? 'open') == 'open' ? '🔴 রকেট বন্ধ' : '🟢 রকেট চালু';
    $binance = ($s['status_wd_binance'] ?? 'open') == 'open' ? '🔴 বাইনান্স বন্ধ' : '🟢 বাইনান্স চালু';
    
    return json_encode(['keyboard' => [
        [makeBtn($bkash), makeBtn($nagad)],
        [makeBtn($rocket), makeBtn($binance)],
        [makeBtn('🔙 বটের সেটিংস')]
    ], 'resize_keyboard' => true]);
}

function getAdminLimitSettingsMenu() {
    return json_encode(['keyboard' => [
        [makeBtn('📱 বিকাশ লিমিট'), makeBtn('📱 বিকাশ চার্জ')],
        [makeBtn('📱 নগদ লিমিট'), makeBtn('📱 নগদ চার্জ')],
        [makeBtn('📱 রকেট লিমিট'), makeBtn('📱 রকেট চার্জ')],
        [makeBtn('📱 বাইনান্স লিমিট'), makeBtn('📱 বাইনান্স চার্জ')],
        [makeBtn('🔙 বটের সেটিংস')]
    ], 'resize_keyboard' => true]);
}

// ----------------- CSV EXPORT -----------------
function exportDynamicCSV($chat_id, $task_type, $headers, $title) {
    global $CUSTOM_EMOJI, $E;
    $proofs = readDB('proofs.json');
    $export_rows = [];
    
    foreach ($proofs as $p) {
        if (($p['status'] ?? '') === 'pending' && ($p['task_type'] ?? '') === $task_type) {
            if ($task_type == 'instagram') {
                $export_rows[] = [$p['username'], $p['password'], $p['data']];
            } elseif ($task_type == 'gmail') {
                $export_rows[] = [$p['email'], $p['password']];
            } elseif ($task_type == 'facebook') {
                $export_rows[] = [$p['uid'], $p['password'], $p['data']];
            } elseif ($task_type == 'facebook_cookies') {
                $export_rows[] = [$p['uid'], $p['password'], $p['data']];
            } elseif ($task_type == 'instagram_cookies') {
                $export_rows[] = [$p['username'], $p['password'], $p['data']];
            }
        }
    }
    
    if (empty($export_rows)) {
        bot('sendMessage', [
            'chat_id' => $chat_id, 
            'text' => emo($CUSTOM_EMOJI['error'], '❌') . " <b>বর্তমান কোনো পেন্ডিং {$title} ডাটা নেই।</b>",
            'parse_mode' => 'HTML'
        ]);
        return;
    }
    
    $temp_filename = "data/" . $task_type . "_" . date("Ymd_His") . ".csv";
    $fp = fopen($temp_filename, "w");
    if ($fp) {
        fputcsv($fp, $headers);
        foreach ($export_rows as $row) {
            fputcsv($fp, $row);
        }
        fclose($fp);
        
        $cfile = new CURLFile(realpath($temp_filename), 'text/csv', $task_type . "_master.csv");
        bot('sendDocument', [
            'chat_id' => $chat_id,
            'document' => $cfile,
            'caption' => emo($CUSTOM_EMOJI['done'], '✅') . " <b>{$title} পেন্ডিং ডাটা ডাউনলোড সম্পন্ন!</b>\nমোট অপেক্ষাউটিং: <b>" . count($export_rows) . "</b> টি",
            'parse_mode' => 'HTML'
        ]);
        
        @unlink($temp_filename);
    }
}

function showAdminWithdrawList($chat_id) {
    global $CUSTOM_EMOJI, $E;
    $withdraws = readDB('withdraws.json');
    $inline_kb = [];
    foreach ($withdraws as $w) {
        if (($w['status'] ?? '') == 'pending') {
            $method_upper = strtoupper($w['method']);
            $inline_kb[] = [
                ['text' => "📱 [{$method_upper}] ৳{$w['amount']} - User: {$w['user_id']}", 'callback_data' => "view_wd_{$w['id']}"]
            ];
        }
    }
    
    if (empty($inline_kb)) {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['error'], '❌') . " <b>বর্তমানে কোনো পেন্ডিং উইথড্র রিকোয়েস্ট নেই।</b>", 'parse_mode' => 'HTML']);
    } else {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => emo($CUSTOM_EMOJI['money'], '🤑') . " <b>পেন্ডিং উইথড্র তালিকা:</b>\n\nযেকোনো উইথড্রর বিস্তারিত দেখতে ক্লিক করুন:",
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode(['inline_keyboard' => $inline_kb])
        ]);
    }
}

// ---------------- SCRIPT START ----------------
$update = json_decode(file_get_contents('php://input'), true);
if (!$update) exit;

$message = $update['message'] ?? null; 
$callback = $update['callback_query'] ?? null;
$chat_id = $message['chat']['id'] ?? $callback['message']['chat']['id'] ?? null;
$from_id = $message['from']['id'] ?? $callback['from']['id'] ?? null;
$text = trim($message['text'] ?? ""); 
$name = $message['from']['first_name'] ?? $callback['from']['first_name'] ?? "User";
$safe_name = htmlspecialchars($name);

if (!$chat_id) exit;

$users = readDB('users.json');
$ref_by = 0; 
if (strpos($text, '/start ') === 0) { 
    $ref_by = trim(str_replace('/start ', '', $text)); 
}

$tg_username = $message['from']['username'] ?? $callback['from']['username'] ?? "";
if (!isset($users[$from_id])) {
    $users[$from_id] = [
        'name' => $name, 
        'username' => $tg_username,
        'lang' => 'bn',
        'balance' => 0.00, 
        'pending_withdraw' => 0.00,
        'total_income' => 0.00,
        'completed_tasks' => 0,
        'review_tasks' => 0,
        'rejected_tasks' => 0, 
        'ref_by' => $ref_by, 
        'ref_rewarded' => false,
        'total_refs' => 0,
        'ref_income' => 0.00,
        'state' => '', 
        'temp' => ''
    ];
    writeDB('users.json', $users);
}

$u = $users[$from_id]; 
$state = $u['state'] ?? ''; 
$temp = $u['temp'] ?? '';

$is_reset = ($text === '/start' || strpos($text, '/start ') === 0 || $text === '🔙 ফিরে যান' || $text === '🔙 Back' || $text === '❌ বাতিল' || $text === '🔙 সেন্ট্রাল শীট');

if ($is_reset || in_array($text, ['⚙️ এডমিন প্যানেল', '🔙 এডমিন মেনু', '⚙️ বটের সেটিংস', '🔙 বটের সেটিংস'])) {
    if ($is_reset) {
        setState($from_id, "");
        $state = "";
        $temp = "";
    }
}

if ($is_reset) {
    setState($from_id, "");
    bot('sendMessage', [
        'chat_id' => $chat_id, 
        'text' => emo($CUSTOM_EMOJI['smile'], '😀') . " <b>মূল মেনু:</b>", 
        'reply_markup' => getMainMenu($from_id),
        'parse_mode' => 'HTML'
    ]);
    exit;
}

if ($text == '📝 কাজ ▾' || $text == '📝 Work ▾') {
    $reward_insta = number_format($s['reward_instagram'], 2);
    $reward_gmail = number_format($s['reward_gmail'], 2);
    $reward_fb = number_format($s['reward_facebook'], 2);
    $reward_cookies = number_format($s['reward_facebook_cookies'] ?? 8.00, 2);
    $reward_insta_cookies = number_format($s['reward_instagram_cookies'] ?? 8.00, 2);
    
    $menu = [
        [makeBtn("📸 ইন্সটা 2FA (৳{$reward_insta})"), makeBtn("📧 জিমেইল কাজ (৳{$reward_gmail})")],
        [makeBtn("📘 ফেসবুক 2FA (৳{$reward_fb})"), makeBtn("🍪 ফেসবুক কুকিজ (৳{$reward_cookies})")],
        [makeBtn("🍪 ইন্সটা কুকিজ (৳{$reward_insta_cookies})")], 
        [makeBtn('❌ বাতিল')]
    ];
    bot('sendMessage', [
        'chat_id' => $chat_id, 
        'text' => emo($CUSTOM_EMOJI['box'], '📱') . " <b>নিচের তালিকা থেকে একটি কাজ সিলেক্ট করুন:</b>", 
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode(['keyboard' => $menu, 'resize_keyboard' => true])
    ]);
    exit;
}

// ---------------- USER TASKS WORKFLOW ----------------

// 1. Instagram 2FA
if (strpos($text, '📸 ইন্সটা 2FA') !== false) {
    $gen_username = generateUsername();
    $admin_password = $s['admin_insta_password'] ?? 'Pass@insta';
    $menu = [[makeBtn('🔑 2FA Set')], [makeBtn('🔙 ফিরে যান')]];
    
    $msg = emo($CUSTOM_EMOJI['star'], '🤩') . " <b>ইনস্টাগ্রাম অ্যাকাউন্ট কাজ:</b> \n👤 Username: <code>{$gen_username}</code>\n🔑 Password: <code>{$admin_password}</code>\n\nঅ্যাকাউন্ট তৈরি করে 2FA সেট করুন।";
    
    bot('sendMessage', [
        'chat_id' => $chat_id, 
        'text' => $msg,
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode(['keyboard' => $menu, 'resize_keyboard' => true])
    ]);
    setState($from_id, 'view_task', $gen_username . "|" . $admin_password);
    exit;
}

if ($text == '🔑 2FA Set' && $state == 'view_task') {
    bot('sendMessage', [
        'chat_id' => $chat_id, 
        'text' => emo($CUSTOM_EMOJI['rocket'], '🚀') . " <b>আপনার তৈরি করা অ্যাকাউন্টের 2FA Key টি পাঠান:</b>", 
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode(['keyboard' => [[makeBtn('❌ বাতিল')]], 'resize_keyboard' => true])
    ]);
    setState($from_id, 'wait_2fa_key', $temp); 
    exit;
}

if ($state == 'wait_2fa_key' && $text != '❌ বাতিল') {
    if (!isValidBase32Key($text)) {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['warning'], '😤') . " <b>সঠিক 2FA Key দিন!</b>", 'parse_mode' => 'HTML']);
        exit;
    }
    $live_code = getTOTPWithOffset($text, 0);
    setState($from_id, 'wait_work_otp_confirm', $temp . "|" . $text);
    bot('sendMessage', [
        'chat_id' => $chat_id, 
        'text' => emo($CUSTOM_EMOJI['done'], '👍') . " <b>লাইভ কোড:</b> <code>{$live_code}</code>\n\nকাজ শেষ হলে নিচে বাটনে চাপুন:",
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode(['keyboard' => [[makeBtn('✅ কাজ শেষ')]], 'resize_keyboard' => true])
    ]);
    exit;
}

if ($state == 'wait_work_otp_confirm' && $text == '✅ কাজ শেষ') {
    $parts = explode('|', $temp);
    $proofs = readDB('proofs.json');
    $proofs[] = [
        'id' => uniqid(),
        'user_id' => $from_id,
        'task_type' => 'instagram',
        'username' => $parts[0],
        'password' => $parts[1],
        'data' => str_replace(' ', '', $parts[2]),
        'status' => 'pending'
    ];
    writeDB('proofs.json', $proofs);
    
    $users[$from_id]['review_tasks'] = ($users[$from_id]['review_tasks'] ?? 0) + 1;
    writeDB('users.json', $users);
    
    bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['party'], '🥳') . " <b>কাজটি পর্যালোচনার জন্য জমা হয়েছে।</b>", 'parse_mode' => 'HTML', 'reply_markup' => getMainMenu($from_id)]);
    setState($from_id, "");
    exit;
}

// 2. Gmail Task
if (strpos($text, '📧 জিমেইল কাজ') !== false) {
    $gmail_data = generateGmailData();
    $admin_password = $s['admin_gmail_password'] ?? 'Pass@gmail';
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => emo($CUSTOM_EMOJI['star'], '🤩') . " <b>জিমেইল অ্যাকাউন্ট কাজ:</b> \n📧 Email: <code>{$gmail_data['email']}</code>\n🔑 Password: <code>{$admin_password}</code>",
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode(['keyboard' => [[makeBtn('📤 কাজ সাবমিট করুন')], [makeBtn('🔙 ফিরে যান')]], 'resize_keyboard' => true])
    ]);
    setState($from_id, 'gmail_task_active', $gmail_data['email'] . "|" . $admin_password);
    exit;
}

if ($state == 'gmail_task_active' && $text == '📤 কাজ সাবমিট করুন') {
    $parts = explode('|', $temp);
    $proofs = readDB('proofs.json');
    $proofs[] = [
        'id' => uniqid(),
        'user_id' => $from_id,
        'task_type' => 'gmail',
        'email' => $parts[0],
        'password' => $parts[1],
        'status' => 'pending'
    ];
    writeDB('proofs.json', $proofs);
    
    $users[$from_id]['review_tasks'] = ($users[$from_id]['review_tasks'] ?? 0) + 1;
    writeDB('users.json', $users);
    
    bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['party'], '🥳') . " <b>জিমেইল টাস্ক জমা হয়েছে।</b>", 'parse_mode' => 'HTML', 'reply_markup' => getMainMenu($from_id)]);
    setState($from_id, "");
    exit;
}

// 3. Facebook 2FA
if (strpos($text, '📘 ফেসবুক 2FA') !== false) {
    $fb_name = generateFbNameArray();
    $admin_password = $s['admin_fb_password'] ?? 'Pass@facebook';
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => emo($CUSTOM_EMOJI['star'], '🤩') . " <b>ফেসবুক অ্যাকাউন্ট কাজ:</b> \n👤 Name: <code>{$fb_name['first']} {$fb_name['last']}</code>\n🔑 Password: <code>{$admin_password}</code>",
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode(['keyboard' => [[makeBtn('📤 ফেসবুক আইডি দিন')], [makeBtn('🔙 ফিরে যান')]], 'resize_keyboard' => true])
    ]);
    setState($from_id, 'fb_task_active', $admin_password);
    exit;
}

if ($text == '📤 ফেসবুক আইডি দিন' && $state == 'fb_task_active') {
    bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['rocket'], '🚀') . " <b>ফেসবুক UID লিখে পাঠান:</b>", 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['keyboard' => [[makeBtn('❌ বাতিল')]], 'resize_keyboard' => true])]);
    setState($from_id, 'fb_wait_uid', $temp);
    exit;
}

if ($state == 'fb_wait_uid' && $text != '❌ বাতিল') {
    setState($from_id, 'fb_wait_2fa', $temp . "|" . $text);
    bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['rocket'], '🚀') . " <b>এবার 2FA Key পাঠান:</b>", 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['keyboard' => [[makeBtn('❌ বাতিল')]], 'resize_keyboard' => true])]);
    exit;
}

if ($state == 'fb_wait_2fa' && $text != '❌ বাতিল') {
    if (!isValidBase32Key($text)) {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['warning'], '😤') . " <b>সঠিক 2FA Key পাঠান!</b>", 'parse_mode' => 'HTML']);
        exit;
    }
    $live_code = getTOTP($text);
    setState($from_id, 'fb_wait_confirm', $temp . "|" . $text);
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => emo($CUSTOM_EMOJI['done'], '👍') . " <b>2FA কোড:</b> <code>{$live_code}</code>\n\nসাবমিট করতে চাপ দিন:",
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode(['keyboard' => [[makeBtn('✅ সাবমিট সম্পন্ন করুন')]], 'resize_keyboard' => true])
    ]);
    exit;
}

if ($state == 'fb_wait_confirm' && $text == '✅ সাবমিট সম্পন্ন করুন') {
    $parts = explode('|', $temp);
    $proofs = readDB('proofs.json');
    $proofs[] = [
        'id' => uniqid(),
        'user_id' => $from_id,
        'task_type' => 'facebook',
        'password' => $parts[0],
        'uid' => $parts[1],
        'data' => str_replace(' ', '', $parts[2]),
        'status' => 'pending'
    ];
    writeDB('proofs.json', $proofs);
    
    $users[$from_id]['review_tasks'] = ($users[$from_id]['review_tasks'] ?? 0) + 1;
    writeDB('users.json', $users);
    
    bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['party'], '🥳') . " <b>ফেসবুক কাজটি জমা হয়েছে।</b>", 'parse_mode' => 'HTML', 'reply_markup' => getMainMenu($from_id)]);
    setState($from_id, "");
    exit;
}

// 4. Facebook Cookies
if (strpos($text, '🍪 ফেসবুক কুকিজ') !== false) {
    $admin_password = $s['admin_fb_cookies_password'] ?? 'Pass@cookies';
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => emo($CUSTOM_EMOJI['star'], '🤩') . " <b>ফেসবুক কুকিজ কাজ</b>\n\nআপনার ফেসবুক UID/ইমেইল লিখে পাঠান:",
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode(['keyboard' => [[makeBtn('❌ বাতিল')]], 'resize_keyboard' => true])
    ]);
    setState($from_id, 'wait_fb_cookies_uid', $admin_password);
    exit;
}

if ($state == 'wait_fb_cookies_uid' && $text != '❌ বাতিল') {
    setState($from_id, 'wait_fb_cookies_data', $temp . "|" . $text);
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => emo($CUSTOM_EMOJI['rocket'], '🚀') . " <b>সম্পূর্ণ কুকিজ টেক্সট বা .txt ফাইল আকারে পাঠান:</b>",
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode(['keyboard' => [[makeBtn('❌ বাতিল')]], 'resize_keyboard' => true])
    ]);
    exit;
}

if ($state == 'wait_fb_cookies_data' && $text != '❌ বাতিল') {
    $cookies_content = $text;
    if (isset($message['document'])) {
        $file_info = bot('getFile', ['file_id' => $message['document']['file_id']]);
        if (isset($file_info['result']['file_path'])) {
            $cookies_content = file_get_contents("https://api.telegram.org/file/bot" . API_KEY . "/" . $file_info['result']['file_path']);
        }
    }
    
    $parts = explode('|', $temp);
    $proofs = readDB('proofs.json');
    $proofs[] = [
        'id' => uniqid(),
        'user_id' => $from_id,
        'task_type' => 'facebook_cookies',
        'password' => $parts[0],
        'uid' => $parts[1],
        'data' => trim($cookies_content),
        'status' => 'pending'
    ];
    writeDB('proofs.json', $proofs);
    
    $users[$from_id]['review_tasks'] = ($users[$from_id]['review_tasks'] ?? 0) + 1;
    writeDB('users.json', $users);
    
    bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['party'], '🥳') . " <b>ফেসবুক কুকিজ জমা সম্পন্ন হয়েছে!</b>", 'parse_mode' => 'HTML', 'reply_markup' => getMainMenu($from_id)]);
    setState($from_id, "");
    exit;
}

// 5. Instagram Cookies
if (strpos($text, '🍪 ইন্সটা কুকিজ') !== false) {
    $admin_password = $s['admin_insta_cookies_password'] ?? 'Pass@instacookies';
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => emo($CUSTOM_EMOJI['star'], '🤩') . " <b>ইন্সটাগ্রাম কুকিজ কাজ</b>\n\nআপনার Instagram Username লিখে পাঠান:",
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode(['keyboard' => [[makeBtn('❌ বাতিল')]], 'resize_keyboard' => true])
    ]);
    setState($from_id, 'wait_insta_cookies_user', $admin_password);
    exit;
}

if ($state == 'wait_insta_cookies_user' && $text != '❌ বাতিল') {
    setState($from_id, 'wait_insta_cookies_data', $temp . "|" . trim($text));
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => emo($CUSTOM_EMOJI['rocket'], '🚀') . " <b>সম্পূর্ণ ইন্সটাগ্রাম কুকিজ টেক্সট বা .txt ফাইল আকারে পাঠান:</b>",
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode(['keyboard' => [[makeBtn('❌ বাতিল')]], 'resize_keyboard' => true])
    ]);
    exit;
}

if ($state == 'wait_insta_cookies_data' && $text != '❌ বাতিল') {
    $cookies_content = $text;
    if (isset($message['document'])) {
        $file_info = bot('getFile', ['file_id' => $message['document']['file_id']]);
        if (isset($file_info['result']['file_path'])) {
            $cookies_content = file_get_contents("https://api.telegram.org/file/bot" . API_KEY . "/" . $file_info['result']['file_path']);
        }
    }
    
    $parts = explode('|', $temp);
    $proofs = readDB('proofs.json');
    $proofs[] = [
        'id' => uniqid(),
        'user_id' => $from_id,
        'task_type' => 'instagram_cookies',
        'password' => $parts[0],
        'username' => $parts[1],
        'data' => trim($cookies_content),
        'status' => 'pending'
    ];
    writeDB('proofs.json', $proofs);
    
    $users[$from_id]['review_tasks'] = ($users[$from_id]['review_tasks'] ?? 0) + 1;
    writeDB('users.json', $users);
    
    bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['party'], '🥳') . " <b>ইন্সটাগ্রাম কুকিজ জমা সম্পন্ন হয়েছে!</b>", 'parse_mode' => 'HTML', 'reply_markup' => getMainMenu($from_id)]);
    setState($from_id, "");
    exit;
}

// ---------------- WITHDRAW ----------------
if ($text == '💸 ব্যালেন্স' || $text == '💸 Balance') {
    $bal = number_format($u['balance'], 2);
    $pending = number_format($u['pending_withdraw'], 2);
    $total_inc = number_format($u['total_income'], 2);
    $msg = emo($CUSTOM_EMOJI['money'], '🤑') . " <b>আপনার ব্যালেন্স</b>\n" .
           "➖➖➖➖➖➖➖➖➖➖➖➖➖➖\n" .
           "💰 ব্যালেন্স: <b>{$bal} BDT</b>\n" .
           "🔒 পেন্ডিং উইথড্র: <b>{$pending} BDT</b>\n" .
           "💰 মোট আয়: <b>{$total_inc} BDT</b>\n" .
           "➖➖➖➖➖➖➖➖➖➖➖➖➖➖\n" .
           "✅ সম্পন্ন কাজ: <b>" . ($u['completed_tasks'] ?? 0) . "</b> টি\n" .
           "⏳ রিভিউ কাজ: <b>" . ($u['review_tasks'] ?? 0) . "</b> টি";
           
    bot('sendMessage', ['chat_id' => $chat_id, 'text' => $msg, 'parse_mode' => 'HTML']);
    exit;
}

if ($text == '💰 টাকা উত্তোলন' || $text == '💰 Withdraw') {
    $menu = [];
    if (($s['status_wd_bkash'] ?? 'open') == 'open') $menu[] = [makeBtn("📱 bKash -> Min: {$s['min_wd_bkash']}৳(-{$s['bkash_charge']})")];
    if (($s['status_wd_nagad'] ?? 'open') == 'open') $menu[] = [makeBtn("📱 Nagad -> Min: {$s['min_wd_nagad']}৳(-{$s['nagad_charge']})")];
    if (($s['status_wd_rocket'] ?? 'open') == 'open') $menu[] = [makeBtn("📱 Rocket -> Min: {$s['min_wd_rocket']}৳(-{$s['rocket_charge']})")];
    if (($s['status_wd_binance'] ?? 'open') == 'open') $menu[] = [makeBtn("📱 Binance -> Min: {$s['min_wd_binance']}৳(-{$s['binance_charge']})")];
    $menu[] = [makeBtn('🔙 ফিরে যান')];
    
    bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['box'], '📦') . " <b>পেমেন্ট মেথড সিলেক্ট করুন:</b>", 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['keyboard' => $menu, 'resize_keyboard' => true])]);
    exit;
}

if (strpos($text, 'bKash') !== false) {
    if ($u['balance'] < $s['min_wd_bkash']) {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['error'], '❌') . " <b>সর্বনিম্ন উইথড্র ৳{$s['min_wd_bkash']}</b>", 'parse_mode' => 'HTML']);
        exit;
    }
    bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['box'], '📱') . " <b>বিকাশ পার্সোনাল নাম্বার পাঠান:</b>", 'parse_mode' => 'HTML']);
    setState($from_id, 'wait_wd_number', 'bkash');
    exit;
}

if (strpos($text, 'Nagad') !== false) {
    if ($u['balance'] < $s['min_wd_nagad']) {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['error'], '❌') . " <b>সর্বনিম্ন উইথড্র ৳{$s['min_wd_nagad']}</b>", 'parse_mode' => 'HTML']);
        exit;
    }
    bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['box'], '📱') . " <b>নগদ পার্সোনাল নাম্বার পাঠান:</b>", 'parse_mode' => 'HTML']);
    setState($from_id, 'wait_wd_number', 'nagad');
    exit;
}

if (strpos($text, 'Rocket') !== false) {
    if ($u['balance'] < $s['min_wd_rocket']) {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['error'], '❌') . " <b>সর্বনিম্ন উইথড্র ৳{$s['min_wd_rocket']}</b>", 'parse_mode' => 'HTML']);
        exit;
    }
    bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['box'], '📱') . " <b>রকেট নাম্বার পাঠান:</b>", 'parse_mode' => 'HTML']);
    setState($from_id, 'wait_wd_number', 'rocket');
    exit;
}

if (strpos($text, 'Binance') !== false) {
    if ($u['balance'] < $s['min_wd_binance']) {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['error'], '❌') . " <b>সর্বনিম্ন উইথড্র ৳{$s['min_wd_binance']}</b>", 'parse_mode' => 'HTML']);
        exit;
    }
    bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['box'], '📱') . " <b>Binance Pay ID (USDT) পাঠান:</b>", 'parse_mode' => 'HTML']);
    setState($from_id, 'wait_wd_number', 'binance');
    exit;
}

if ($state == 'wait_wd_number') {
    setState($from_id, 'wait_wd_amount', $temp . "|" . $text);
    bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['money'], '🤑') . " <b>উত্তোলনের পরিমাণ লিখুন (BDT):</b>", 'parse_mode' => 'HTML']);
    exit;
}

if ($state == 'wait_wd_amount' && is_numeric($text)) {
    $parts = explode('|', $temp);
    $method = $parts[0];
    $account = $parts[1];
    $amount = floatval($text);
    $min = floatval($s['min_wd_'.$method] ?? 100);
    
    if ($amount < $min || $amount > $u['balance']) {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['error'], '❌') . " <b>উত্তোলন ব্যর্থ!</b> ব্যালেন্স অপর্যাপ্ত বা ভুল পরিমাণ।", 'reply_markup' => getMainMenu($from_id), 'parse_mode' => 'HTML']);
        setState($from_id, "");
        exit;
    }
    
    $users[$from_id]['balance'] -= $amount;
    
    if ($method == 'binance') {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['rocket'], '🚀') . " <b>আপনার বাইনান্স পেমেন্ট প্রসেস হচ্ছে...</b>", 'parse_mode' => 'HTML']);
        
        $payout = binanceAutoPayout($account, $amount);
        
        if (isset($payout['status']) && $payout['status'] === "SUCCESS") {
            $users[$from_id]['total_income'] += $amount;
            writeDB('users.json', $users);
            
            $withdraws = readDB('withdraws.json');
            $withdraws[] = [
                'id' => uniqid(),
                'user_id' => $from_id,
                'method' => $method,
                'account' => $account,
                'amount' => $amount,
                'status' => 'approved' 
            ];
            writeDB('withdraws.json', $withdraws);
            
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['party'], '🥳') . " <b>আপনার {$amount} USDT বাইনান্সে অটোমেটিক পাঠানো হয়েছে!</b>", 'parse_mode' => 'HTML', 'reply_markup' => getMainMenu($from_id)]);
        } else {
            $users[$from_id]['balance'] += $amount;
            writeDB('users.json', $users);
            $err = $payout['errorMessage'] ?? 'Unknown Error';
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['warning'], '😤') . " <b>বাইনান্স পেমেন্ট ফেইল হয়েছে!</b>\nকারণ: <code>{$err}</code>", 'parse_mode' => 'HTML', 'reply_markup' => getMainMenu($from_id)]);
        }
    } else {
        $users[$from_id]['pending_withdraw'] += $amount;
        writeDB('users.json', $users);
        
        $withdraws = readDB('withdraws.json');
        $withdraws[] = [
            'id' => uniqid(),
            'user_id' => $from_id,
            'method' => $method,
            'account' => $account,
            'amount' => $amount,
            'status' => 'pending'
        ];
        writeDB('withdraws.json', $withdraws);
        
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['done'], '👍') . " <b>আপনার রিকোয়েস্ট অ্যাডমিনের কাছে পাঠানো হয়েছে!</b>", 'parse_mode' => 'HTML', 'reply_markup' => getMainMenu($from_id)]);
    }
    
    setState($from_id, "");
    exit;
}

// ---------------- ADMIN PANEL ----------------
if (strval($from_id) === strval(ADMIN_ID)) {

    if ($text == '⚙️ এডমিন প্যানেল' || $text == '🔙 এডমিন মেনু') {
        setState($from_id, "");
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['bot'], '🤖') . " <b>এডমিন প্যানেল</b>", 'parse_mode' => 'HTML', 'reply_markup' => getAdminMenu()]);
        exit;
    }

    if ($text == '📊 সেন্ট্রাল শীট') {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['box'], '📱') . " <b>সেন্ট্রাল শীট প্যানেল:</b>", 'parse_mode' => 'HTML', 'reply_markup' => getAdminCentralSheetMenu()]);
        exit;
    }

    if ($text == '📸 ইনস্টাগ্রাম কন্ট্রোল') {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['box'], '📱') . " <b>ইনস্টাগ্রাম শীট কন্ট্রোল</b>", 'parse_mode' => 'HTML', 'reply_markup' => getInstaControlMenu()]);
        exit;
    }

    if ($text == '📧 জিমেইল কন্ট্রোল') {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['box'], '📱') . " <b>জিমেইল শীট কন্ট্রোল</b>", 'parse_mode' => 'HTML', 'reply_markup' => getGmailControlMenu()]);
        exit;
    }

    if ($text == '📘 ফেসবুক কন্ট্রোল') {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['box'], '📱') . " <b>ফেসবুক শীট কন্ট্রোল</b>", 'parse_mode' => 'HTML', 'reply_markup' => getFacebookControlMenu()]);
        exit;
    }

    if ($text == '🍪 ফেসবুক কুকিজ কন্ট্রোল') {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['box'], '📱') . " <b>ফেসবুক কুকিজ কন্ট্রোল</b>", 'parse_mode' => 'HTML', 'reply_markup' => getCookiesControlMenu()]);
        exit;
    }

    if ($text == '🍪 ইন্সটা কুকিজ কন্ট্রোল') {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['box'], '📱') . " <b>ইন্সটাগ্রাম কুকিজ কন্ট্রোল</b>", 'parse_mode' => 'HTML', 'reply_markup' => getInstaCookiesControlMenu()]);
        exit;
    }

    // --- ডাউনলোড অপশনস ---
    if ($text == '📥 ইনস্টাগ্রাম শীট ডাউনলোড') {
        exportDynamicCSV($chat_id, 'instagram', ["Username", "Password", "2FA Key"], "ইনস্টাগ্রাম");
        exit;
    }

    if ($text == '📥 জিমেইল শীট ডাউনলোড') {
        exportDynamicCSV($chat_id, 'gmail', ["Email", "Password"], "জিমেইল");
        exit;
    }

    if ($text == '📥 ফেসবুক শীট ডাউনলোড') {
        exportDynamicCSV($chat_id, 'facebook', ["UID", "Password", "2FA Key"], "ফেসবুক");
        exit;
    }

    if ($text == '📥 ফেসবুক কুকিজ শীট ডাউনলোড') {
        exportDynamicCSV($chat_id, 'facebook_cookies', ["UID", "Password", "Cookies"], "ফেসবুক কুকিজ");
        exit;
    }

    if ($text == '📥 ইন্সটা কুকিজ শীট ডাউনলোড') {
        exportDynamicCSV($chat_id, 'instagram_cookies', ["Username", "Password", "Cookies"], "ইন্সটাগ্রাম কুকিজ");
        exit;
    }

    if ($text == '🗑 ডাটাবেজ ক্লিয়ার') {
        $proofs = readDB('proofs.json');
        $pending_only = [];
        $cleared = 0;
        foreach ($proofs as $p) {
            if ($p['status'] == 'pending') $pending_only[] = $p;
            else $cleared++;
        }
        writeDB('proofs.json', $pending_only);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['done'], '👍') . " <b>{$cleared} টি পুরনো কাজ ক্লিয়ার করা হয়েছে।</b>", 'parse_mode' => 'HTML', 'reply_markup' => getAdminCentralSheetMenu()]);
        exit;
    }

    if ($text == '⚙️ বটের সেটিংস' || $text == '🔙 বটের সেটিংস') {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['bot'], '🤖') . " <b>সেটিংস মেনু</b>", 'parse_mode' => 'HTML', 'reply_markup' => getAdminSettingsMenu()]);
        exit;
    }

    if ($text == '📱 লিমিট ও চার্জ সেট') {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['box'], '📱') . " <b>লিমিট ও চার্জ সেটিংস</b>", 'parse_mode' => 'HTML', 'reply_markup' => getAdminLimitSettingsMenu()]);
        exit;
    }

    // বিকাশ সেটিংস
    if ($text == '📱 বিকাশ লিমিট') {
        setState($from_id, 'set_limit_bkash');
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['money'], '🤑') . " <b>বিকাশ সর্বনিম্ন উইথড্র লিমিট (BDT) দিন:</b>", 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['keyboard' => [[makeBtn('🔙 বটের সেটিংস')]], 'resize_keyboard' => true])]);
        exit;
    }
    if ($state == 'set_limit_bkash' && is_numeric($text)) {
        $s['min_wd_bkash'] = floatval($text);
        writeDB('settings.json', $s);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['done'], '✅') . " <b>বিকাশ লিমিট আপডেট সম্পন্ন!</b>", 'parse_mode' => 'HTML', 'reply_markup' => getAdminLimitSettingsMenu()]);
        setState($from_id, "");
        exit;
    }

    if ($text == '📱 বিকাশ চার্জ') {
        setState($from_id, 'set_charge_bkash');
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['money'], '🤑') . " <b>বিকাশ চার্জ (BDT) দিন:</b>", 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['keyboard' => [[makeBtn('🔙 বটের সেটিংস')]], 'resize_keyboard' => true])]);
        exit;
    }
    if ($state == 'set_charge_bkash' && is_numeric($text)) {
        $s['bkash_charge'] = floatval($text);
        writeDB('settings.json', $s);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['done'], '✅') . " <b>বিকাশ চার্জ আপডেট সম্পন্ন!</b>", 'parse_mode' => 'HTML', 'reply_markup' => getAdminLimitSettingsMenu()]);
        setState($from_id, "");
        exit;
    }

    // নগদ সেটিংস
    if ($text == '📱 নগদ লিমিট') {
        setState($from_id, 'set_limit_nagad');
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['money'], '🤑') . " <b>নগদ সর্বনিম্ন উইথড্র লিমিট (BDT) দিন:</b>", 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['keyboard' => [[makeBtn('🔙 বটের সেটিংস')]], 'resize_keyboard' => true])]);
        exit;
    }
    if ($state == 'set_limit_nagad' && is_numeric($text)) {
        $s['min_wd_nagad'] = floatval($text);
        writeDB('settings.json', $s);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['done'], '✅') . " <b>নগদ লিমিট আপডেট সম্পন্ন!</b>", 'parse_mode' => 'HTML', 'reply_markup' => getAdminLimitSettingsMenu()]);
        setState($from_id, "");
        exit;
    }

    if ($text == '📱 নগদ চার্জ') {
        setState($from_id, 'set_charge_nagad');
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['money'], '🤑') . " <b>নগদ চার্জ (BDT) দিন:</b>", 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['keyboard' => [[makeBtn('🔙 বটের সেটিংস')]], 'resize_keyboard' => true])]);
        exit;
    }
    if ($state == 'set_charge_nagad' && is_numeric($text)) {
        $s['nagad_charge'] = floatval($text);
        writeDB('settings.json', $s);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['done'], '✅') . " <b>নগদ চার্জ আপডেট সম্পন্ন!</b>", 'parse_mode' => 'HTML', 'reply_markup' => getAdminLimitSettingsMenu()]);
        setState($from_id, "");
        exit;
    }

    // রকেট সেটিংস
    if ($text == '📱 রকেট লিমিট') {
        setState($from_id, 'set_limit_rocket');
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['money'], '🤑') . " <b>রকেট সর্বনিম্ন উইথড্র লিমিট (BDT) দিন:</b>", 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['keyboard' => [[makeBtn('🔙 বটের সেটিংস')]], 'resize_keyboard' => true])]);
        exit;
    }
    if ($state == 'set_limit_rocket' && is_numeric($text)) {
        $s['min_wd_rocket'] = floatval($text);
        writeDB('settings.json', $s);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['done'], '✅') . " <b>রকেট লিমিট আপডেট সম্পন্ন!</b>", 'parse_mode' => 'HTML', 'reply_markup' => getAdminLimitSettingsMenu()]);
        setState($from_id, "");
        exit;
    }

    if ($text == '📱 রকেট চার্জ') {
        setState($from_id, 'set_charge_rocket');
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['money'], '🤑') . " <b>রকেট চার্জ (BDT) দিন:</b>", 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['keyboard' => [[makeBtn('🔙 বটের সেটিংস')]], 'resize_keyboard' => true])]);
        exit;
    }
    if ($state == 'set_charge_rocket' && is_numeric($text)) {
        $s['rocket_charge'] = floatval($text);
        writeDB('settings.json', $s);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['done'], '✅') . " <b>রকেট চার্জ আপডেট সম্পন্ন!</b>", 'parse_mode' => 'HTML', 'reply_markup' => getAdminLimitSettingsMenu()]);
        setState($from_id, "");
        exit;
    }

    // বাইনান্স সেটিংস
    if ($text == '📱 বাইনান্স লিমিট') {
        setState($from_id, 'set_limit_binance');
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['money'], '🤑') . " <b>বাইনান্স সর্বনিম্ন উইথড্র লিমিট (BDT) দিন:</b>", 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['keyboard' => [[makeBtn('🔙 বটের সেটিংস')]], 'resize_keyboard' => true])]);
        exit;
    }
    if ($state == 'set_limit_binance' && is_numeric($text)) {
        $s['min_wd_binance'] = floatval($text);
        writeDB('settings.json', $s);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['done'], '✅') . " <b>বাইনান্স লিমিট আপডেট সম্পন্ন!</b>", 'parse_mode' => 'HTML', 'reply_markup' => getAdminLimitSettingsMenu()]);
        setState($from_id, "");
        exit;
    }

    if ($text == '📱 বাইনান্স চার্জ') {
        setState($from_id, 'set_charge_binance');
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['money'], '🤑') . " <b>বাইনান্স চার্জ (BDT) দিন:</b>", 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['keyboard' => [[makeBtn('🔙 বটের সেটিংস')]], 'resize_keyboard' => true])]);
        exit;
    }
    if ($state == 'set_charge_binance' && is_numeric($text)) {
        $s['binance_charge'] = floatval($text);
        writeDB('settings.json', $s);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => emo($CUSTOM_EMOJI['done'], '✅') . " <b>বাইনান্স চার্জ আপডেট সম্পন্ন!</b>", 'parse_mode' => 'HTML', 'reply_markup' => getAdminLimitSettingsMenu()]);
        setState($from_id, "");
        exit;
    }

    if ($text == '📥 পেন্ডিং উইথড্র') {
        showAdminWithdrawList($chat_id);
        exit;
    }
}

// ---------------- WITHDRAW CALLBACK HANDLERS ----------------
if ($callback && strval($from_id) === strval(ADMIN_ID)) {
    $cb_data = $callback['data'];
    $cb_id = $callback['id'];
    $msg_id = $callback['message']['message_id'];
    
    if ($cb_data == 'list_wd') {
        bot('deleteMessage', ['chat_id' => $chat_id, 'message_id' => $msg_id]);
        showAdminWithdrawList($chat_id);
        exit;
    }

    if (strpos($cb_data, 'view_wd_') === 0) {
        $wd_id = str_replace('view_wd_', '', $cb_data);
        $withdraws = readDB('withdraws.json');
        foreach ($withdraws as $w) {
            if ($w['id'] == $wd_id) {
                $inline_kb = [
                    [['text' => '✅ Approve', 'callback_data' => "app_wd_{$w['id']}"], ['text' => '❌ Reject', 'callback_data' => "rej_wd_{$w['id']}"]],
                    [['text' => '🔙 তালিকায় ফিরে যান', 'callback_data' => 'list_wd']]
                ];
                bot('editMessageText', [
                    'chat_id' => $chat_id,
                    'message_id' => $msg_id,
                    'text' => emo($CUSTOM_EMOJI['fire'], '🔥') . " <b>উইথড্র ডিটেইলস:</b>\n\nUser: <code>{$w['user_id']}</code>\nMethod: <b>{$w['method']}</b>\nAccount: <code>{$w['account']}</code>\nAmount: ৳<b>{$w['amount']}</b>",
                    'parse_mode' => 'HTML',
                    'reply_markup' => json_encode(['inline_keyboard' => $inline_kb])
                ]);
                exit;
            }
        }
    }

    if (strpos($cb_data, 'app_wd_') === 0) {
        $wd_id = str_replace('app_wd_', '', $cb_data);
        $withdraws = readDB('withdraws.json');
        foreach ($withdraws as $k => $w) {
            if ($w['id'] == $wd_id && ($w['status'] ?? '') == 'pending') {
                $withdraws[$k]['status'] = 'approved';
                writeDB('withdraws.json', $withdraws);
                
                $users[$w['user_id']]['pending_withdraw'] = max(0, $users[$w['user_id']]['pending_withdraw'] - $w['amount']);
                $users[$w['user_id']]['total_income'] += $w['amount'];
                writeDB('users.json', $users);
                
                bot('answerCallbackQuery', ['callback_query_id' => $cb_id, 'text' => '✅ Approved!']);
                bot('deleteMessage', ['chat_id' => $chat_id, 'message_id' => $msg_id]);
                bot('sendMessage', ['chat_id' => $w['user_id'], 'text' => emo($CUSTOM_EMOJI['party'], '🥳') . " <b>আপনার ৳{$w['amount']} উত্তোলনের রিকোয়েস্ট সফল হয়েছে!</b>", 'parse_mode' => 'HTML']);
                showAdminWithdrawList($chat_id);
                exit;
            }
        }
    }

    if (strpos($cb_data, 'rej_wd_') === 0) {
        $wd_id = str_replace('rej_wd_', '', $cb_data);
        $withdraws = readDB('withdraws.json');
        foreach ($withdraws as $k => $w) {
            if ($w['id'] == $wd_id && ($w['status'] ?? '') == 'pending') {
                $withdraws[$k]['status'] = 'rejected';
                writeDB('withdraws.json', $withdraws);
                
                $users[$w['user_id']]['balance'] += $w['amount'];
                $users[$w['user_id']]['pending_withdraw'] = max(0, $users[$w['user_id']]['pending_withdraw'] - $w['amount']);
                writeDB('users.json', $users);
                
                bot('answerCallbackQuery', ['callback_query_id' => $cb_id, 'text' => '❌ Rejected!']);
                bot('deleteMessage', ['chat_id' => $chat_id, 'message_id' => $msg_id]);
                bot('sendMessage', ['chat_id' => $w['user_id'], 'text' => emo($CUSTOM_EMOJI['error'], '❌') . " <b>আপনার উইথড্র রিকোয়েস্ট বাতিল করা হয়েছে এবং ব্যালেন্স ফেরত দেওয়া হয়েছে।</b>", 'parse_mode' => 'HTML']);
                showAdminWithdrawList($chat_id);
                exit;
            }
        }
    }
}
?>
