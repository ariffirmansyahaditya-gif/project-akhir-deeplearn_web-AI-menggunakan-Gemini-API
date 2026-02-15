<?php
// Mencegah timeout jika AI menjawab panjang
set_time_limit(60); 

session_start();
header('Content-Type: application/json');
include 'config/database.php';

// 1. Cek Login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'success', 'reply' => '⚠️ Sesi habis. Silakan login ulang.']);
    exit();
}

$user_message = isset($_POST['message']) ? $_POST['message'] : '';
if (empty($user_message)) {
    echo json_encode(['status' => 'success', 'reply' => '⚠️ Pesan tidak boleh kosong.']);
    exit();
}

// ---------------------------------------------------------
// FITUR BARU: MANAGEMENT SESSION CHAT (V3)
// ---------------------------------------------------------
if (!isset($_SESSION['current_chat_session'])) {
    $_SESSION['current_chat_session'] = $_SESSION['user_id'] . '_' . time() . '_' . rand(1000,9999);
}
$session_id = $_SESSION['current_chat_session'];

// ---------------------------------------------------------
// FITUR BARU: AMBIL KONFIGURASI PROMPT DARI DATABASE (V3)
// ---------------------------------------------------------
$querySettings = "SELECT setting_value FROM settings WHERE setting_key = 'system_instruction' LIMIT 1";
$resultSettings = mysqli_query($conn, $querySettings);
$rowSettings = mysqli_fetch_assoc($resultSettings);

// Default prompt jika database kosong
$system_instruction = $rowSettings['setting_value'] ?? "Peran: Kamu adalah Dr. Nexus, asisten medis AI.";

// ---------------------------------------------------------
// API KEY & AUTO-DETECT MODEL (V3.1 FIX)
// ---------------------------------------------------------
$apiKey = trim("AIzaSyDBGVqc8NjDOXSXaqOw72mPtJr97EPc9LI"); 

// Langkah 1: TANYA GOOGLE DULU, MODEL APA YANG TERSEDIA?
$listUrl = "https://generativelanguage.googleapis.com/v1beta/models?key=" . $apiKey;

$ch = curl_init($listUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
$listResponse = curl_exec($ch);
$listHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$selectedModel = ""; 

if ($listHttpCode == 200) {
    $modelsData = json_decode($listResponse, true);
    if (isset($modelsData['models'])) {
        foreach ($modelsData['models'] as $m) {
            if (isset($m['supportedGenerationMethods']) && in_array("generateContent", $m['supportedGenerationMethods'])) {
                $modelName = str_replace("models/", "", $m['name']);
                
                // Prioritas 1: Flash (Cepat & Token Banyak)
                if (strpos($modelName, 'flash') !== false) {
                    $selectedModel = $modelName;
                    break; 
                }
                // Prioritas 2: Pro (Stabil)
                if (empty($selectedModel) && strpos($modelName, 'pro') !== false) {
                    $selectedModel = $modelName;
                }
            }
        }
    }
}

// Fallback jika deteksi gagal
if (empty($selectedModel)) { 
    $selectedModel = "gemini-1.5-flash"; 
}

// ---------------------------------------------------------
// KIRIM PESAN KE MODEL YANG SUDAH DIPILIH
// ---------------------------------------------------------
$apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$selectedModel}:generateContent?key=" . $apiKey;

$final_prompt = $system_instruction . "\n\nPertanyaan User:\n" . $user_message . "\n\nJawab:";

$data = [
    "contents" => [ [ "parts" => [ ["text" => $final_prompt] ] ] ],
    "generationConfig" => [
        "temperature" => 0.7,        
        
        "maxOutputTokens" => 1000,   
    ]
];

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
// Bypass SSL XAMPP
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// ---------------------------------------------------------
// SIMPAN KE DB DENGAN SESSION ID
// ---------------------------------------------------------
if ($http_code == 200) {
    $decoded = json_decode($response, true);
    $ai_reply = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? "Maaf, respon kosong.";
    
    $user_id = $_SESSION['user_id'];
    $safe_msg = mysqli_real_escape_string($conn, $user_message);
    $safe_reply = mysqli_real_escape_string($conn, $ai_reply);
    
    $sql = "INSERT INTO chat_history (user_id, session_id, user_message, ai_response) 
            VALUES ('$user_id', '$session_id', '$safe_msg', '$safe_reply')";
    mysqli_query($conn, $sql);

    echo json_encode(['status' => 'success', 'reply' => $ai_reply]);

} else {
    // DIAGNOSA ERROR
    $errorDetails = json_decode($response, true);
    $googleMsg = $errorDetails['error']['message'] ?? "Unknown Error";
    
    $debugMsg = "⚠️ **Error ($http_code)**\n";
    $debugMsg .= "Model: $selectedModel\n";
    $debugMsg .= "Pesan: $googleMsg";
    
    echo json_encode(['status' => 'success', 'reply' => $debugMsg]);
}
?>