<?php
session_start();
include '../config/database.php';

// 1. Cek Login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'] ?? 'user';

// ------------------------------------------
// LOGIKA SESSION CHAT (HISTORY)
// ------------------------------------------

// A. Jika user klik riwayat lama di sidebar
if (isset($_GET['load_session'])) {
    $_SESSION['current_chat_session'] = $_GET['load_session'];
    header("Location: chat.php");
    exit();
}

// B. Jika user klik tombol "Chat Baru"
if (isset($_GET['new_chat'])) {
    $_SESSION['current_chat_session'] = $user_id . '_' . time() . '_' . rand(1000,9999);
    header("Location: chat.php");
    exit();
}

// C. Pastikan selalu ada session ID aktif
if (!isset($_SESSION['current_chat_session'])) {
    $_SESSION['current_chat_session'] = $user_id . '_' . time() . '_' . rand(1000,9999);
}
$current_session_id = $_SESSION['current_chat_session'];

// ------------------------------------------
// QUERY DATA
// ------------------------------------------

// 2. Ambil Pesan untuk SESI AKTIF saja
$queryChat = "SELECT * FROM chat_history WHERE user_id = '$user_id' AND session_id = '$current_session_id' ORDER BY timestamp ASC";
$resultChat = mysqli_query($conn, $queryChat);

// 3. Ambil Daftar Riwayat (Sidebar)
$queryHistory = "SELECT session_id, MIN(timestamp) as start_time, SUBSTRING(user_message, 1, 25) as preview 
                 FROM chat_history 
                 WHERE user_id = '$user_id' 
                 GROUP BY session_id 
                 ORDER BY start_time DESC";
$resultHistory = mysqli_query($conn, $queryHistory);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - mediGemini</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* CSS KHUSUS HALAMAN CHAT V3 */
        body { background: #f0f2f5; display: block; height: 100vh; overflow: hidden; }
        
        .app-layout { display: flex; height: 100vh; width: 100%; max-width: 1400px; margin: 0 auto; background: white; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        
        /* SIDEBAR */
        .sidebar { width: 260px; background: #f8f9fa; border-right: 1px solid #ddd; display: flex; flex-direction: column; flex-shrink: 0; }
        .sidebar-header { padding: 1.2rem; border-bottom: 1px solid #ddd; font-weight: bold; color: #333; font-size: 1.1rem; }
        .history-list { flex: 1; overflow-y: auto; padding: 0.5rem; }
        
        .history-item { display: block; padding: 12px; margin-bottom: 5px; background: white; border-radius: 8px; text-decoration: none; color: #444; font-size: 0.9rem; border: 1px solid #eee; transition: 0.2s; }
        .history-item:hover { background: #e9ecef; }
        .history-item.active { border-left: 5px solid #007bff; background: #e3f2fd; color: #007bff; font-weight: 500; }
        .history-time { font-size: 0.75rem; color: #999; display: block; margin-top: 4px; }
        
        .new-chat-btn { display: block; text-align: center; background: #007bff; color: white; padding: 12px; margin: 10px; border-radius: 8px; text-decoration: none; font-weight: bold; transition: 0.2s; }
        .new-chat-btn:hover { background: #0056b3; }
        
        /* MAIN CHAT AREA */
        .main-area { flex: 1; display: flex; flex-direction: column; position: relative; min-width: 0; }
        
        /* HEADER YANG DIPERBARUI */
        .chat-header { 
            padding: 15px 25px; background: white; border-bottom: 1px solid #eee; 
            display: flex; justify-content: space-between; align-items: center; 
        }
        
        .chat-box { flex: 1; padding: 25px; overflow-y: auto; background: #fff; scroll-behavior: smooth; }
        
        /* MODAL */
        .modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); backdrop-filter: blur(2px); }
        .modal-content { background-color: white; margin: 10% auto; padding: 30px; border-radius: 15px; width: 350px; text-align: center; position: relative; box-shadow: 0 10px 25px rgba(0,0,0,0.2); animation: slideDown 0.3s ease-out; }
        @keyframes slideDown { from {transform: translateY(-50px); opacity: 0;} to {transform: translateY(0); opacity: 1;} }
        .close-modal { position: absolute; right: 20px; top: 15px; font-size: 28px; font-weight: bold; cursor: pointer; color: #aaa; }
        
        /* Tombol Header */
        .btn-switch { background: white; border: 1px solid #28a745; color: #28a745; padding: 8px 15px; font-size: 0.9rem; border-radius: 20px; cursor: pointer; margin-right: 10px; transition: 0.2s; }
        .btn-switch:hover { background: #28a745; color: white; }
        .btn-dashboard { background: #6c757d; color: white; padding: 8px 15px; font-size: 0.9rem; border-radius: 20px; text-decoration: none; margin-right: 10px; transition: 0.2s; }
        .btn-dashboard:hover { background: #5a6268; }
    </style>
</head>
<body>

<div class="app-layout">
    
    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="sidebar-header">
            🕑 Riwayat Chat
        </div>
        <a href="chat.php?new_chat=1" class="new-chat-btn">+ Percakapan Baru</a>
        
        <div class="history-list">
            <?php while($hist = mysqli_fetch_assoc($resultHistory)): ?>
                <a href="chat.php?load_session=<?php echo $hist['session_id']; ?>" 
                   class="history-item <?php echo ($hist['session_id'] == $current_session_id) ? 'active' : ''; ?>">
                    <div>
                        <?php 
                            $preview = htmlspecialchars($hist['preview']);
                            echo empty($preview) ? "Percakapan Baru" : $preview . "..."; 
                        ?>
                    </div>
                    <span class="history-time"><?php echo date('d M, H:i', strtotime($hist['start_time'])); ?></span>
                </a>
            <?php endwhile; ?>
            
            <?php if(mysqli_num_rows($resultHistory) == 0): ?>
                <div style="padding:20px; color:#888; font-size:0.85rem; text-align:center;">
                    Belum ada riwayat.<br>Mulai chat sekarang!
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- MAIN CHAT -->
    <div class="main-area">
        <div class="chat-header">
            <!-- BAGIAN INI YANG DIPERBARUI: LOGO & JUDUL -->
            <div style="display: flex; align-items: center; gap: 12px;">
                <!-- Icon Lingkaran -->
                <div style="background: #e3f2fd; padding: 10px; border-radius: 50%; display: flex; align-items: center; justify-content: center; width: 45px; height: 45px;">
                    <span style="font-size: 1.5rem;">🩺</span>
                </div>
                
                <!-- Teks Branding -->
                <div>
                    <h3 style="margin: 0; font-size: 1.2rem; color: #007bff;">mediGemini</h3>
                    <small style="color: #666; font-size: 0.85rem; display: block; margin-top: 2px;">
                        Konsultasi Dr. Nexus &bull; <b><?php echo htmlspecialchars($username); ?></b>
                    </small>
                </div>
            </div>

            <div>
                <?php if($role == 'admin'): ?>
                    <a href="../admin/dashboard.php" class="btn-dashboard">⬅ Dashboard</a>
                <?php endif; ?>
                <button onclick="openModal()" class="btn-switch">🔄 Ganti Akun</button>
                <a href="../logout.php" class="btn-logout" style="background:#dc3545; color:white; padding:8px 15px; border-radius:20px; text-decoration:none; font-size:0.9rem;">Logout</a>
            </div>
        </div>

        <div class="chat-box" id="chatBox">
            <?php if(mysqli_num_rows($resultChat) == 0): ?>
                <div class="message bot">
                    Halo, <b><?php echo htmlspecialchars($username); ?></b>! 👋<br>
                    Saya Dr. Nexus di mediGemini. Silakan ceritakan keluhan Anda.
                    <span class="time">Sekarang</span>
                </div>
            <?php endif; ?>

            <?php 
                mysqli_data_seek($resultChat, 0);
                while ($chat = mysqli_fetch_assoc($resultChat)): 
            ?>
                <div class="message user">
                    <?php echo htmlspecialchars($chat['user_message']); ?>
                    <span class="time"><?php echo date('H:i', strtotime($chat['timestamp'])); ?></span>
                </div>
                <div class="message bot">
                    <?php echo nl2br(htmlspecialchars($chat['ai_response'])); ?>
                    <span class="time"><?php echo date('H:i', strtotime($chat['timestamp'])); ?></span>
                </div>
            <?php endwhile; ?>
        </div>

        <form class="chat-input-area" id="chatForm">
            <input type="text" id="userMessage" name="message" placeholder="Ketik pesan Anda..." required autocomplete="off">
            <button type="submit" id="sendBtn">Kirim</button>
        </form>
    </div>
</div>

<!-- MODAL LOGIN -->
<div id="loginModal" class="modal">
    <div class="modal-content">
        <span class="close-modal" onclick="closeModal()">&times;</span>
        <h3 style="margin-bottom: 5px;">Login Akun Lain</h3>
        <p style="font-size:0.9rem; color:#666; margin-bottom:20px;">Masuk sebagai Admin atau User lain.</p>
        
        <?php if(isset($_GET['login_error'])): ?>
            <p style="color:red; font-size:0.8rem; margin-bottom:10px;">Username/Password Salah!</p>
        <?php endif; ?>

        <form action="../auth_process.php" method="POST">
            <div class="form-group">
                <input type="text" name="username" placeholder="Username" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px; margin-bottom:15px;">
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px; margin-bottom:15px;">
            </div>
            <button type="submit" name="login" class="btn-primary" style="width:100%; padding:10px; border-radius:5px;">Masuk Sekarang</button>
        </form>
    </div>
</div>

<script>
    const chatBox = document.getElementById('chatBox');
    function scrollToBottom() { chatBox.scrollTop = chatBox.scrollHeight; }
    scrollToBottom();

    function openModal() { document.getElementById('loginModal').style.display = 'block'; }
    function closeModal() { document.getElementById('loginModal').style.display = 'none'; }
    window.onclick = function(event) { if (event.target == document.getElementById('loginModal')) closeModal(); }

    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('login_error')) openModal();

    const chatForm = document.getElementById('chatForm');
    const userMessageInput = document.getElementById('userMessage');
    const sendBtn = document.getElementById('sendBtn');

    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const message = userMessageInput.value;
        if (message.trim() === '') return;

        const userBubble = document.createElement('div');
        userBubble.className = 'message user';
        userBubble.innerHTML = message + '<span class="time">Baru saja</span>';
        chatBox.appendChild(userBubble);
        
        userMessageInput.value = '';
        scrollToBottom();
        sendBtn.disabled = true; sendBtn.innerText = '...';

        const formData = new FormData();
        formData.append('message', message);

        fetch('../api_handler.php', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            const botBubble = document.createElement('div');
            botBubble.className = 'message bot';
            if (data.status === 'success') {
                botBubble.innerHTML = data.reply.replace(/\n/g, "<br>") + '<span class="time">Baru saja</span>';
            } else {
                botBubble.innerHTML = 'Error: ' + data.reply + '<span class="time">Error</span>';
            }
            chatBox.appendChild(botBubble);
            scrollToBottom();
            sendBtn.disabled = false; sendBtn.innerText = 'Kirim';
        })
        .catch(error => { console.error(error); sendBtn.disabled = false; sendBtn.innerText = 'Kirim'; });
    });
</script>
</body>
</html>