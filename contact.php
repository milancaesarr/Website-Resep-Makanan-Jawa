<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

require_once "db.php";

$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $message_content = mysqli_real_escape_string($conn, $_POST['message']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);

    // Insert into contacts table
    $sql = "INSERT INTO contacts (name, email, subject, message, category, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sssss", $name, $email, $subject, $message_content, $category);

        if (mysqli_stmt_execute($stmt)) {
            $message = "Pesan Anda berhasil dikirim! Kami akan merespons dalam 1-2 hari kerja.";
            $message_type = 'success';
        } else {
            $message = "Terjadi kesalahan. Silakan coba lagi.";
            $message_type = 'error';
        }

        mysqli_stmt_close($stmt);
    } else {
        $message = "Terjadi kesalahan sistem. Silakan coba lagi.";
        $message_type = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontak Kami - NusaJava Eats</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />
    <script src="https://unpkg.com/feather-icons"></script>

    <style>
        :root {
            --mainColor: #feba71;
            --secondrayColor: #5b2620;
            --acsentColor: #af2d2d;
            --doubleColor: #816040;
            --trippleColor: #a7774e;
            --tambahColor: #222;
            --white: #ffffff;
            --light-brown: #f4e4d0;
            --success: #27ae60;
            --error: #e74c3c;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--mainColor);
            padding-top: 80px;
            min-height: 100vh;
        }

        .contact-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .contact-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .contact-header h1 {
            font-size: 3rem;
            color: #222;
            margin-bottom: 20px;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .contact-header p {
            max-width: 700px;
            margin: 0 auto 40px auto;
            font-size: 16px;
            color: #555;
            line-height: 1.6;
        }

        .contact-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            margin-bottom: 60px;
        }

        .contact-info {
            background: var(--white);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .contact-info h2 {
            color: var(--secondrayColor);
            font-size: 2rem;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 25px;
            padding: 20px;
            background: var(--light-brown);
            border-radius: 15px;
            transition: all 0.3s ease;
        }

        .info-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .info-icon {
            background: var(--acsentColor);
            color: var(--white);
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .info-content h3 {
            color: var(--secondrayColor);
            font-size: 1.2rem;
            margin-bottom: 5px;
        }

        .info-content p {
            color: var(--doubleColor);
            font-size: 1rem;
            line-height: 1.5;
        }

        .contact-form {
            background: var(--white);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .contact-form h2 {
            color: var(--secondrayColor);
            font-size: 2rem;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            color: var(--secondrayColor);
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 1.1rem;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid var(--light-brown);
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--acsentColor);
            box-shadow: 0 0 10px rgba(175, 45, 45, 0.2);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .submit-btn {
            background: linear-gradient(45deg, var(--acsentColor), var(--secondrayColor));
            color: var(--white);
            border: none;
            padding: 18px 40px;
            border-radius: 15px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(175, 45, 45, 0.3);
        }

        .message {
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .message.success {
            background: rgba(39, 174, 96, 0.1);
            color: var(--success);
            border: 2px solid var(--success);
        }

        .message.error {
            background: rgba(231, 76, 60, 0.1);
            color: var(--error);
            border: 2px solid var(--error);
        }

        .social-section {
            background: var(--white);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
        }

        .social-section h2 {
            color: var(--secondrayColor);
            font-size: 2rem;
            margin-bottom: 20px;
        }

        .social-section p {
            color: var(--doubleColor);
            font-size: 1.1rem;
            margin-bottom: 30px;
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .social-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px 25px;
            background: var(--light-brown);
            color: var(--secondrayColor);
            text-decoration: none;
            border-radius: 15px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .social-link:hover {
            background: var(--acsentColor);
            color: var(--white);
            transform: translateY(-3px);
        }

        .map-section {
            margin-top: 50px;
            background: var(--white);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .map-section h2 {
            color: var(--secondrayColor);
            font-size: 2rem;
            margin-bottom: 20px;
            text-align: center;
        }

        .map-container {
            border-radius: 15px;
            overflow: hidden;
            height: 400px;
            background: var(--light-brown);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--doubleColor);
            font-size: 1.2rem;
        }

        @media (max-width: 768px) {
            .contact-content {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .contact-header h1 {
                font-size: 2rem;
            }

            .contact-info,
            .contact-form {
                padding: 25px;
            }

            .info-item {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }

            .social-links {
                flex-direction: column;
                align-items: center;
            }

            .social-link {
                width: 100%;
                max-width: 300px;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="contact-container">
        <div class="contact-header">
            <h1>
                <i class="fas fa-envelope"></i>
                Hubungi Kami
            </h1>
            <p>Kami senang mendengar dari Anda! Hubungi tim NusaJava Eats untuk pertanyaan, saran, atau kerja sama. Tim kami siap membantu Anda kapan saja.</p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="contact-content">
            <div class="contact-info">
                <h2>
                    <i class="fas fa-info-circle"></i>
                    Informasi Kontak
                </h2>

                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="info-content">
                        <h3>Alamat Kantor</h3>
                        <p>Jl.Telkom <br>Milan Arden Keisya Neta<br>Indonesia</p>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="info-content">
                        <h3>Telepon</h3>
                        <p>+62 857 2719 6825<br>+62 812 3456 7890</p>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="info-content">
                        <h3>Email</h3>
                        <p>info@nusajavaeats.com<br>@nusajavaeats</p>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="info-content">
                        <h3>Jam Operasional</h3>
                        <p>Senin - Jumat: 07:15 - 15:55<br>Sabtu: libur<br>Minggu: Libur</p>
                    </div>
                </div>
            </div>

            <div class="contact-form">
                <h2>
                    <i class="fas fa-paper-plane"></i>
                    Kirim Pesan
                </h2>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="name">Nama Lengkap *</label>
                        <input type="text" id="name" name="name" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="category">Kategori Pesan *</label>
                        <select id="category" name="category" required>
                            <option value="">Pilih Kategori</option>
                            <option value="general">Pertanyaan Umum</option>
                            <option value="recipe">Pertanyaan Resep</option>
                            <option value="product">Pertanyaan Produk</option>
                            <option value="order">Bantuan Pesanan</option>
                            <option value="partnership">Kerjasama</option>
                            <option value="complaint">Keluhan</option>
                            <option value="suggestion">Saran</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="subject">Subjek *</label>
                        <input type="text" id="subject" name="subject" required>
                    </div>

                    <div class="form-group">
                        <label for="message">Pesan *</label>
                        <textarea id="message" name="message" placeholder="Tuliskan pesan Anda di sini..." required></textarea>
                    </div>

                    <button type="submit" class="submit-btn">
                        <i class="fas fa-paper-plane"></i>
                        Kirim Pesan
                    </button>
                </form>
            </div>
        </div>

        <div class="social-section">
            <h2>
                <i class="fas fa-share-alt"></i>
                Ikuti Kami
            </h2>
            <p>Dapatkan update terbaru resep dan produk kami melalui media sosial</p>

            <div class="social-links">
                <a href="#" class="social-link">
                    <i class="fab fa-instagram"></i>
                    Instagram
                </a>
                <a href="#" class="social-link">
                    <i class="fab fa-facebook"></i>
                    Facebook
                </a>
                <a href="#" class="social-link">
                    <i class="fab fa-youtube"></i>
                    YouTube
                </a>
                <a href="#" class="social-link">
                    <i class="fab fa-tiktok"></i>
                    TikTok
                </a>
                <a href="#" class="social-link">
                    <i class="fab fa-whatsapp"></i>
                    WhatsApp
                </a>
            </div>
        </div>

        <div class="map-section">
            <h2>
                <i class="fas fa-map"></i>
                Lokasi Kami
            </h2>
            <div class="map-container">
                <i class="fas fa-map-marker-alt" style="font-size: 3rem; margin-right: 20px;"></i>
                <div>
                    <h3>Kantor Pusat NusaJava Eats</h3>
                    <p>Jl. Milan Arden Keisya Neta</p>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        feather.replace();

        // Auto hide success message
        const message = document.querySelector('.message.success');
        if (message) {
            setTimeout(() => {
                message.style.opacity = '0';
                message.style.transform = 'translateY(-20px)';
                setTimeout(() => {
                    message.remove();
                }, 300);
            }, 5000);
        }

        // Form validation
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const category = document.getElementById('category').value;
            const subject = document.getElementById('subject').value.trim();
            const message = document.getElementById('message').value.trim();

            if (!name || !email || !category || !subject || !message) {
                e.preventDefault();
                alert('Mohon lengkapi semua field yang wajib diisi!');
                return;
            }

            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Format email tidak valid!');
                return;
            }
        });
    </script>
</body>
</html>
