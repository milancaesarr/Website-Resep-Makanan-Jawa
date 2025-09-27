<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

require_once "db.php";

$message = '';
$message_type = '';

// Handle post submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create_post') {
        $title = mysqli_real_escape_string($conn, $_POST['title']);
        $content = mysqli_real_escape_string($conn, $_POST['content']);
        $category = mysqli_real_escape_string($conn, $_POST['category']);
        $author_id = $_SESSION['user_id'];

        $sql = "INSERT INTO community_posts (title, content, category, author_id, created_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sssi", $title, $content, $category, $author_id);

            if (mysqli_stmt_execute($stmt)) {
                $message = "Post berhasil dibuat!";
                $message_type = 'success';
            } else {
                $message = "Gagal membuat post. Silakan coba lagi.";
                $message_type = 'error';
            }

            mysqli_stmt_close($stmt);
        }
    }

    if ($_POST['action'] === 'add_comment') {
        $post_id = (int)$_POST['post_id'];
        $comment = mysqli_real_escape_string($conn, $_POST['comment']);
        $author_id = $_SESSION['user_id'];

        $sql = "INSERT INTO community_comments (post_id, comment, author_id, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "isi", $post_id, $comment, $author_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
}

// Get filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Get community posts
$sql = "SELECT cp.*, u.full_name as author_name,
        (SELECT COUNT(*) FROM community_comments cc WHERE cc.post_id = cp.id) as comment_count
        FROM community_posts cp
        JOIN users u ON cp.author_id = u.id";

if ($filter !== 'all') {
    $sql .= " WHERE cp.category = '" . mysqli_real_escape_string($conn, $filter) . "'";
}

$sql .= " ORDER BY cp.created_at DESC";
$posts_result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Komunitas - NusaJava Eats</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />
    <script src="https://unpkg.com/feather-icons"></script>

    <style>
        :root {
            --mainColor: #feba71;
            --secondaryColor: #5b2620;
            --accentColor: #af2d2d;
            --doubleColor: #816040;
            --tripleColor: #a7774e;
            --tambahColor: #222;
            --white: #ffffff;
            --light-brown: #f4e4d0;
            --success: #27ae60;
            --error: #e74c3c;
            --info: #3498db;
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

        .community-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .community-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .community-header h1 {
            font-size: 3rem;
            color: #222;
            margin-bottom: 20px;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .community-header p {
            max-width: 700px;
            margin: 0 auto 40px auto;
            font-size: 16px;
            color: #555;
            line-height: 1.6;
        }

        .community-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--white);
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            font-size: 2.5rem;
            color: var(--accentColor);
            margin-bottom: 15px;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--secondaryColor);
            margin-bottom: 5px;
        }

        .stat-label {
            color: var(--doubleColor);
            font-size: 1rem;
        }

        .community-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            gap: 20px;
            flex-wrap: wrap;
        }

        .filter-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 10px 20px;
            background: var(--white);
            color: var(--secondaryColor);
            border: 2px solid var(--tripleColor);
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .filter-btn:hover,
        .filter-btn.active {
            background: var(--accentColor);
            color: var(--white);
            border-color: var(--accentColor);
        }

        .create-post-btn {
            background: linear-gradient(45deg, var(--accentColor), var(--secondaryColor));
            color: var(--white);
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .create-post-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(175, 45, 45, 0.3);
        }

        .message {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 600;
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

        .post-form {
            background: var(--white);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            display: none;
        }

        .post-form.show {
            display: block;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: var(--secondaryColor);
            font-weight: 600;
            margin-bottom: 8px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--light-brown);
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--accentColor);
            box-shadow: 0 0 10px rgba(175, 45, 45, 0.2);
        }

        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }

        .form-actions {
            display: flex;
            gap: 15px;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--accentColor);
            color: var(--white);
        }

        .btn-secondary {
            background: var(--light-brown);
            color: var(--secondaryColor);
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .posts-container {
            display: grid;
            gap: 25px;
        }

        .post-card {
            background: var(--white);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .post-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .post-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            gap: 20px;
        }

        .post-info h3 {
            color: var(--secondaryColor);
            font-size: 1.5rem;
            margin-bottom: 10px;
            line-height: 1.3;
        }

        .post-meta {
            display: flex;
            gap: 20px;
            color: var(--doubleColor);
            font-size: 0.9rem;
            flex-wrap: wrap;
        }

        .post-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .post-category {
            background: var(--tripleColor);
            color: var(--white);
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .post-content {
            color: var(--tambahColor);
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .post-actions {
            display: flex;
            gap: 15px;
            padding-top: 20px;
            border-top: 1px solid var(--light-brown);
            align-items: center;
        }

        .action-btn {
            background: none;
            border: none;
            color: var(--doubleColor);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 8px 12px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .action-btn:hover {
            background: var(--light-brown);
            color: var(--secondaryColor);
        }

        .comments-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--light-brown);
            display: none;
        }

        .comments-section.show {
            display: block;
        }

        .comment-form {
            margin-bottom: 20px;
        }

        .comment-form textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid var(--light-brown);
            border-radius: 10px;
            min-height: 80px;
            margin-bottom: 10px;
        }

        .comment {
            background: var(--light-brown);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 10px;
        }

        .comment-author {
            font-weight: 600;
            color: var(--secondaryColor);
            margin-bottom: 5px;
        }

        .comment-content {
            color: var(--tambahColor);
            line-height: 1.5;
        }

        .comment-date {
            color: var(--doubleColor);
            font-size: 0.8rem;
            margin-top: 5px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: var(--white);
            border-radius: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--tripleColor);
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: var(--secondaryColor);
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: var(--doubleColor);
        }

        @media (max-width: 768px) {
            .community-header h1 {
                font-size: 2rem;
            }

            .community-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-buttons {
                justify-content: center;
            }

            .post-header {
                flex-direction: column;
            }

            .post-meta {
                gap: 10px;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                justify-content: center;
            }
        }
    </style>
</head>

<body>
    <?php include 'navbar.php'; ?>

    <div class="community-container">
        <div class="community-header">
            <h1>
                <i class="fas fa-users"></i>
                Komunitas NusaJava Eats
            </h1>
            <p>Bergabunglah dengan komunitas pecinta kuliner Nusantara! Berbagi resep, tips masak, dan diskusi seputar makanan tradisional Jawa. Mari lestarikan warisan kuliner bersama-sama.</p>
        </div>

        <div class="community-stats">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number">
                    <?php
                    $user_count_sql = "SELECT COUNT(*) as count FROM users WHERE role = 'user'";
                    $user_count_result = mysqli_query($conn, $user_count_sql);
                    echo mysqli_fetch_assoc($user_count_result)['count'];
                    ?>
                </div>
                <div class="stat-label">Total Anggota</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-comments"></i>
                </div>
                <div class="stat-number">
                    <?php
                    $post_count_sql = "SELECT COUNT(*) as count FROM community_posts";
                    $post_count_result = mysqli_query($conn, $post_count_sql);
                    echo mysqli_fetch_assoc($post_count_result)['count'];
                    ?>
                </div>
                <div class="stat-label">Total Diskusi</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-number">
                    <?php
                    $recipe_count_sql = "SELECT COUNT(*) as count FROM recipes";
                    $recipe_count_result = mysqli_query($conn, $recipe_count_sql);
                    echo mysqli_fetch_assoc($recipe_count_result)['count'];
                    ?>
                </div>
                <div class="stat-label">Resep Tersedia</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-number">4.8</div>
                <div class="stat-label">Rating Komunitas</div>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="community-actions">
            <div class="filter-buttons">
                <a href="komunitas.php?filter=all" class="filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>">
                    <i class="fas fa-list"></i> Semua
                </a>
                <a href="komunitas.php?filter=recipe" class="filter-btn <?php echo $filter === 'recipe' ? 'active' : ''; ?>">
                    <i class="fas fa-utensils"></i> Resep
                </a>
                <a href="komunitas.php?filter=tips" class="filter-btn <?php echo $filter === 'tips' ? 'active' : ''; ?>">
                    <i class="fas fa-lightbulb"></i> Tips
                </a>
                <a href="komunitas.php?filter=discussion" class="filter-btn <?php echo $filter === 'discussion' ? 'active' : ''; ?>">
                    <i class="fas fa-comments"></i> Diskusi
                </a>
                <a href="komunitas.php?filter=review" class="filter-btn <?php echo $filter === 'review' ? 'active' : ''; ?>">
                    <i class="fas fa-star"></i> Review
                </a>
            </div>

            <button class="create-post-btn" onclick="togglePostForm()">
                <i class="fas fa-plus"></i>
                Buat Post Baru
            </button>
        </div>

        <!-- Create Post Form -->
        <div class="post-form" id="postForm">
            <h3 style="color: var(--secondaryColor); margin-bottom: 20px;">
                <i class="fas fa-edit"></i>
                Buat Post Baru
            </h3>

            <form method="POST" action="">
                <input type="hidden" name="action" value="create_post">

                <div class="form-group">
                    <label for="category">Kategori</label>
                    <select id="category" name="category" required>
                        <option value="">Pilih Kategori</option>
                        <option value="recipe">Resep</option>
                        <option value="tips">Tips Masak</option>
                        <option value="discussion">Diskusi</option>
                        <option value="review">Review</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="title">Judul Post</label>
                    <input type="text" id="title" name="title" required placeholder="Masukkan judul yang menarik...">
                </div>

                <div class="form-group">
                    <label for="content">Konten</label>
                    <textarea id="content" name="content" required placeholder="Bagikan pengalaman, resep, atau tips Anda..."></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i>
                        Posting
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="togglePostForm()">
                        <i class="fas fa-times"></i>
                        Batal
                    </button>
                </div>
            </form>
        </div>

        <!-- Posts Container -->
        <div class="posts-container">
            <?php if (mysqli_num_rows($posts_result) > 0): ?>
                <?php while ($post = mysqli_fetch_assoc($posts_result)): ?>
                    <div class="post-card">
                        <div class="post-header">
                            <div class="post-info">
                                <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                                <div class="post-meta">
                                    <span>
                                        <i class="fas fa-user"></i>
                                        <?php echo htmlspecialchars($post['author_name']); ?>
                                    </span>
                                    <span>
                                        <i class="fas fa-calendar"></i>
                                        <?php echo date('d M Y', strtotime($post['created_at'])); ?>
                                    </span>
                                    <span>
                                        <i class="fas fa-comments"></i>
                                        <?php echo $post['comment_count']; ?> Komentar
                                    </span>
                                </div>
                            </div>
                            <div class="post-category">
                                <?php
                                $categories = [
                                    'recipe' => 'Resep',
                                    'tips' => 'Tips',
                                    'discussion' => 'Diskusi',
                                    'review' => 'Review'
                                ];
                                echo $categories[$post['category']] ?? $post['category'];
                                ?>
                            </div>
                        </div>

                        <div class="post-content">
                            <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                        </div>

                        <div class="post-actions">
                            <button class="action-btn" onclick="toggleComments(<?php echo $post['id']; ?>)">
                                <i class="fas fa-comment"></i>
                                Komentar (<?php echo $post['comment_count']; ?>)
                            </button>
                            <button class="action-btn">
                                <i class="fas fa-heart"></i>
                                Suka
                            </button>
                            <button class="action-btn">
                                <i class="fas fa-share"></i>
                                Bagikan
                            </button>
                        </div>

                        <!-- Comments Section -->
                        <div class="comments-section" id="comments-<?php echo $post['id']; ?>">
                            <div class="comment-form">
                                <form method="POST" action="">
                                    <input type="hidden" name="action" value="add_comment">
                                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                    <textarea name="comment" placeholder="Tulis komentar Anda..." required></textarea>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane"></i>
                                        Kirim Komentar
                                    </button>
                                </form>
                            </div>

                            <div class="comments-list">
                                <?php
                                $comments_sql = "SELECT cc.*, u.full_name as author_name
                                                FROM community_comments cc
                                                JOIN users u ON cc.author_id = u.id
                                                WHERE cc.post_id = " . $post['id'] . "
                                                ORDER BY cc.created_at ASC";
                                $comments_result = mysqli_query($conn, $comments_sql);

                                while ($comment = mysqli_fetch_assoc($comments_result)):
                                ?>
                                    <div class="comment">
                                        <div class="comment-author"><?php echo htmlspecialchars($comment['author_name']); ?></div>
                                        <div class="comment-content"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></div>
                                        <div class="comment-date"><?php echo date('d M Y H:i', strtotime($comment['created_at'])); ?></div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-comments"></i>
                    <h3>Belum Ada Diskusi</h3>
                    <p>Jadilah yang pertama memulai diskusi di komunitas ini!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        feather.replace();

        function togglePostForm() {
            const form = document.getElementById('postForm');
            form.classList.toggle('show');

            if (form.classList.contains('show')) {
                form.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        }

        function toggleComments(postId) {
            const commentsSection = document.getElementById('comments-' + postId);
            commentsSection.classList.toggle('show');

            if (commentsSection.classList.contains('show')) {
                commentsSection.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest'
                });
            }
        }

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
        const postForm = document.querySelector('#postForm form');
        if (postForm) {
            postForm.addEventListener('submit', function(e) {
                const title = document.getElementById('title').value.trim();
                const content = document.getElementById('content').value.trim();
                const category = document.getElementById('category').value;

                if (!title || !content || !category) {
                    e.preventDefault();
                    alert('Mohon lengkapi semua field!');
                    return;
                }

                if (title.length < 5) {
                    e.preventDefault();
                    alert('Judul minimal 5 karakter!');
                    return;
                }

                if (content.length < 10) {
                    e.preventDefault();
                    alert('Konten minimal 10 karakter!');
                    return;
                }
            });
        }
    </script>
</body>

</html>