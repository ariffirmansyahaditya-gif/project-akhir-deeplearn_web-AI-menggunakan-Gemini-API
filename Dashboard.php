<?php
session_start();
include '../config/database.php';

// Cek Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// --- LOGIKA SIMPAN PENGATURAN AI ---
if (isset($_POST['save_instruction'])) {
    $new_instruction = mysqli_real_escape_string($conn, $_POST['instruction']);
    
    // Cek dulu apakah setting sudah ada di database
    $check = mysqli_query($conn, "SELECT * FROM settings WHERE setting_key = 'system_instruction'");
    
    if(mysqli_num_rows($check) > 0) {
        // Jika sudah ada, lakukan UPDATE
        $queryUpdate = "UPDATE settings SET setting_value = '$new_instruction' WHERE setting_key = 'system_instruction'";
        mysqli_query($conn, $queryUpdate);
    } else {
        // Jika belum ada, lakukan INSERT
        $queryInsert = "INSERT INTO settings (setting_key, setting_value) VALUES ('system_instruction', '$new_instruction')";
        mysqli_query($conn, $queryInsert);
    }
    $msg = "Instruksi AI berhasil diperbarui!";
}

// Ambil Instruksi Saat Ini untuk ditampilkan di form
$queryGet = "SELECT setting_value FROM settings WHERE setting_key = 'system_instruction'";
$resGet = mysqli_query($conn, $queryGet);
$rowGet = mysqli_fetch_assoc($resGet);
$current_instruction = $rowGet['setting_value'] ?? "";

// Ambil Data User (untuk tabel manajemen user)
$my_id = $_SESSION['user_id'];
$queryUsers = "SELECT * FROM users WHERE id != '$my_id' ORDER BY created_at DESC";
$resultUsers = mysqli_query($conn, $queryUsers);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - mediGemini</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* Style Khusus Halaman Admin V3 */
        body { background-color: #f4f6f9; }
        
        .admin-layout { 
            display: flex; 
            flex-wrap: wrap; /* Supaya responsif di layar kecil */
            gap: 2rem; 
            max-width: 1200px; 
            margin: 2rem auto; 
            align-items: flex-start; 
            padding: 0 1rem;
        }
        .admin-card { 
            background: white; 
            padding: 1.5rem; 
            border-radius: 12px; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.05); 
            flex: 1;
            min-width: 300px; /* Lebar minimum kartu */
            border: 1px solid #e1e4e8;
        }
        
        h3 { border-bottom: 2px solid #f0f2f5; padding-bottom: 10px; margin-bottom: 15px; color: #333; }

        /* Style Editor Prompt */
        .prompt-editor textarea { 
            width: 100%; 
            height: 300px; 
            padding: 15px; 
            border: 1px solid #ced4da; 
            border-radius: 8px; 
            font-family: 'Consolas', 'Monaco', monospace; 
            font-size: 0.95rem; 
            line-height: 1.6;
            margin-top: 10px; 
            resize: vertical;
            background-color: #fafbfc;
            color: #24292e;
        }
        .prompt-editor textarea:focus { border-color: #007bff; outline: none; background-color: #fff; }

        /* Table User */
        table { width: 100%; border-collapse: separate; border-spacing: 0; margin-top: 1rem; font-size: 0.9rem; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; font-weight: 600; color: #555; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px; }
        tr:last-child td { border-bottom: none; }
        
        /* Tombol */
        .btn-save { 
            background: #28a745; 
            color: white; 
            border: none; 
            padding: 12px 25px; 
            border-radius: 6px; 
            cursor: pointer; 
            margin-top: 15px; 
            font-weight: 600;
            width: 100%;
            transition: background 0.2s;
        }
        .btn-save:hover { background: #218838; }
        
        .btn-logout {
            background: #dc3545; 
            color: white; 
            padding: 6px 15px; 
            border-radius: 20px; 
            text-decoration: none; 
            font-size: 0.8rem;
            transition: background 0.2s;
        }
        .btn-logout:hover { background: #c82333; }

        .btn-chat {
            background: #007bff; 
            color: white; 
            padding: 6px 15px; 
            border-radius: 20px; 
            text-decoration: none; 
            font-size: 0.8rem;
            margin-right: 5px;
            transition: background 0.2s;
        }
        .btn-chat:hover { background: #0056b3; }

        .role-badge {
            padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: bold;
        }
        .role-admin { background: #cce5ff; color: #004085; }
        .role-user { background: #e2e6ea; color: #383d41; }
    </style>
</head>
<body>

    <div class="admin-layout">
        
        <!-- KOLOM KIRI: Settings AI -->
        <div class="admin-card">
            <h3>🧠 Pengaturan Otak Dr. Nexus</h3>
            <p style="font-size: 0.9rem; color: #666; margin-bottom: 1rem;">
                Ubah cara AI menjawab di sini. Gunakan bahasa natural. Perubahan akan langsung berlaku untuk semua chat user.
            </p>
            
            <?php if(isset($msg)) echo "<div class='alert alert-success'>$msg</div>"; ?>

            <form method="POST">
                <div class="prompt-editor">
                    <label><strong>System Instruction (Instruksi Sistem):</strong></label>
                    <textarea name="instruction" required placeholder="Contoh: Kamu adalah dokter spesialis anak..."><?php echo htmlspecialchars($current_instruction); ?></textarea>
                </div>
                <button type="submit" name="save_instruction" class="btn-save">💾 Simpan Perubahan</button>
            </form>
        </div>

        <!-- KOLOM KANAN: User Management -->
        <div class="admin-card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 1rem;">
                <h3>👥 Manajemen User</h3>
                <div>
                    <!-- TOMBOL BARU: Link ke Chat -->
                    <a href="../user/chat.php" class="btn-chat">💬 Tes Chat AI</a>
                    <a href="../logout.php" class="btn-logout">Logout</a>
                </div>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($resultUsers)): ?>
                    <tr>
                        <td>
                            <b><?php echo htmlspecialchars($row['username']); ?></b><br>
                            <small style="color:#888;">Daftar: <?php echo date('d/m/Y', strtotime($row['created_at'])); ?></small>
                        </td>
                        <td>
                            <span class="role-badge <?php echo ($row['role']=='admin') ? 'role-admin' : 'role-user'; ?>">
                                <?php echo strtoupper($row['role']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="hapus_user.php?id=<?php echo $row['id']; ?>" 
                               onclick="return confirm('Yakin ingin menghapus user ini? Semua history chatnya juga akan hilang.')" 
                               style="color: #dc3545; font-weight: bold; font-size: 0.85rem; text-decoration: none;">
                               Hapus
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    
                    <?php if(mysqli_num_rows($resultUsers) == 0): ?>
                        <tr><td colspan="3" style="text-align:center; color:#999; padding: 20px;">Belum ada user lain yang mendaftar.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>