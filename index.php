<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>mediGemini - AI Kesehatan</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* CSS Khusus untuk Landing Page */
        .landing-container {
            text-align: center;
            padding: 3rem;
            max-width: 800px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .hero-title {
            font-size: 2.5rem;
            color: #007bff;
            margin-bottom: 1rem;
        }
        .hero-subtitle {
            font-size: 1.2rem;
            color: #555;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        .features {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 3rem;
            flex-wrap: wrap;
        }
        .feature-card {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            width: 200px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .feature-icon {
            font-size: 2rem;
            margin-bottom: 10px;
            display: block;
        }
        .btn-cta {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 1rem 2.5rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.1rem;
            transition: transform 0.2s;
        }
        .btn-cta:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <div class="landing-container">
        <h1 class="hero-title">Selamat Datang di mediGemini 🩺</h1>
        <p class="hero-subtitle">
            Konsultasikan keluhan kesehatan Anda secara instan dengan <b>Dr. Nexus</b>.<br>
            Cepat, Mudah, dan Siap Membantu 24/7.
        </p>

        <div class="features">
            <div class="feature-card">
                <span class="feature-icon">⚡</span>
                <h3>Respon Cepat</h3>
                <p>Diagnosa awal dalam hitungan detik.</p>
            </div>
            <div class="feature-card">
                <span class="feature-icon">🛡️</span>
                <h3>Privasi Aman</h3>
                <p>Riwayat chat Anda tersimpan dengan aman.</p>
            </div>
            <div class="feature-card">
                <span class="feature-icon">💊</span>
                <h3>Saran P3K</h3>
                <p>Solusi pertolongan pertama yang tepat.</p>
            </div>
        </div>

        <a href="login.php" class="btn-cta">Mulai Konsultasi Sekarang</a>
        
        <p style="margin-top: 1.5rem; font-size: 0.9rem; color: #888;">
            Belum punya akun? <a href="register.php" style="color:#007bff;">Daftar disini</a>
        </p>
    </div>
</body>
</html>