<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

require_once "db.php";

// Ambil ID resep
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: resep.php");
    exit();
}

// Ambil data resep dari database
$sql = "SELECT * FROM recipes WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    header("Location: resep.php");
    exit();
}

$recipe = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo htmlspecialchars($recipe['name']); ?> - NusaJava Eats</title>
    <script src="https://unpkg.com/feather-icons"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />

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
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--mainColor);
            min-height: 100vh;
            padding-top: 80px;
        }

        .recipe-container {
            max-width: 1200px;
            margin: 20px auto 20px;
            padding: 30px;
            background: var(--light-brown);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .recipe-main-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }

        .recipe-image-section .recipe-image {
            width: 100%;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .recipe-image img {
            width: 100%;
            height: 400px;
            object-fit: cover;
        }

        .recipe-info-section h1 {
            font-size: 2.5em;
            color: var(--secondaryColor);
            margin-bottom: 15px;
            font-weight: bold;
        }

        .recipe-rating {
            margin-bottom: 20px;
        }

        .recipe-rating .stars {
            color: #ffd700;
            font-size: 1.2em;
            margin-right: 10px;
        }

        .recipe-rating .rating-value {
            font-weight: bold;
            color: var(--accentColor);
            font-size: 1.1em;
        }

        .recipe-rating .votes {
            color: var(--doubleColor);
            font-size: 0.9em;
        }

        .author, .meta-row {
            margin-bottom: 12px;
            color: var(--doubleColor);
        }

        .meta-row strong {
            color: var(--secondaryColor);
        }

        .key {
            font-size: 1.2em;
            margin-right: 5px;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin-right: 15px;
            margin-bottom: 20px;
        }

        .btn-green {
            background: var(--tripleColor);
            color: var(--white);
        }

        .btn-green:hover {
            background: var(--doubleColor);
            transform: translateY(-2px);
        }

        .recipe-details {
            background: var(--tripleColor);
            padding: 25px;
            border-radius: 15px;
            margin-top: 20px;
        }

        .detail-box {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .detail {
            text-align: center;
            padding: 15px;
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .detail .icon {
            font-size: 2em;
            margin-bottom: 10px;
            color: var(--accentColor);
        }

        .detail-label {
            font-size: 0.9em;
            color: var(--doubleColor);
            margin-bottom: 5px;
        }

        .detail-value {
            font-weight: bold;
            color: var(--secondaryColor);
        }

        .detail-season {
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--white);
            padding: 15px;
            border-radius: 12px;
        }

        .detail-season .icon {
            font-size: 1.5em;
            color: var(--accentColor);
        }

        .recipes-description {
            margin-bottom: 40px;
        }

        .recipes-description h2 {
            color: var(--secondaryColor);
            margin-bottom: 20px;
            font-size: 2em;
            border-bottom: 3px solid var(--tripleColor);
            padding-bottom: 10px;
        }

        .recipes-description p {
            line-height: 1.8;
            color: var(--doubleColor);
            font-size: 1.1em;
        }

        .recipe-content-2col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }

        .recipe-ingredients h2, .recipe-steps h2 {
            color: var(--secondaryColor);
            margin-bottom: 20px;
            font-size: 1.8em;
            border-bottom: 2px solid var(--tripleColor);
            padding-bottom: 10px;
        }

        .ingredients-list, .steps-list {
            list-style: none;
            counter-reset: item;
        }

        .ingredients-list li, .steps-list li {
            counter-increment: item;
            margin-bottom: 15px;
            padding: 15px;
            background: var(--tripleColor);
            border-radius: 10px;
            position: relative;
            padding-left: 60px;
            line-height: 1.6;
            color: var(--white);
        }

        .ingredients-list li::before, .steps-list li::before {
            content: counter(item);
            position: absolute;
            left: 15px;
            top: 15px;
            background: var(--accentColor);
            color: var(--white);
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }

        .steps-list li strong {
            color: var(--mainColor);
        }

        @media (max-width: 768px) {
            .recipe-main-row {
                grid-template-columns: 1fr;
            }

            .recipe-content-2col {
                grid-template-columns: 1fr;
            }

            .recipe-container {
                margin: 0px 10px 20px;
                padding: 20px;
            }

            .recipe-info-section h1 {
                font-size: 2em;
            }

            .detail-box {
                grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
                gap: 10px;
            }
        }
    </style>
</head>

<body>
    <?php include 'navbar-home.php'; ?>

    <div class="recipe-container">
        <div class="recipe-main-row">
            <div class="recipe-image-section">
                <div class="recipe-image">
                    <?php if (!empty($recipe['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($recipe['image_url']); ?>" alt="<?php echo htmlspecialchars($recipe['name']); ?>" />
                    <?php else: ?>
                        <div style="width:100%;height:400px;background:var(--light-brown);display:flex;align-items:center;justify-content:center;color:var(--doubleColor);font-weight:bold;">No Image Available</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="recipe-info-section">
                <div class="recipe-rating">
                    <span class="stars">★★★★★</span>
                    <span class="rating-value"><?php echo $recipe['rating']; ?></span>
                    <span class="votes">dari <?php echo $recipe['votes']; ?> votes</span>
                </div>

                <h1><?php echo htmlspecialchars($recipe['name']); ?></h1>

                <?php if (!empty($recipe['author'])): ?>
                    <div class="author"><strong>Warung Terkenal:</strong> <?php echo htmlspecialchars($recipe['author']); ?></div>
                <?php endif; ?>

                <?php if (!empty($recipe['cooking_method'])): ?>
                    <div class="meta-row"><strong>Metode Memasak:</strong> <?php echo htmlspecialchars($recipe['cooking_method']); ?></div>
                <?php endif; ?>

                <?php if (!empty($recipe['cuisine'])): ?>
                    <div class="meta-row"><strong>Asal Makanan:</strong> <?php echo htmlspecialchars($recipe['cuisine']); ?></div>
                <?php endif; ?>

                <?php if (!empty($recipe['courses'])): ?>
                    <div class="meta-row"><strong>Katagori Hidangan:</strong> <?php echo htmlspecialchars($recipe['courses']); ?></div>
                <?php endif; ?>

                <div class="meta-row"><strong>Selamat Memasak🔥</strong></div>

                <button onclick="history.back()" class="btn btn-green">Kembali</button>
                <button id="printBtn" class="btn btn-green" onclick="window.print()">Print Recipe</button>

                <div class="recipe-details">
                    <div class="detail-box">
                        <?php if (!empty($recipe['difficulty'])): ?>
                        <div class="detail">
                            <span class="icon">⚡</span>
                            <div class="detail-label">Difficulty:</div>
                            <div class="detail-value"><?php echo htmlspecialchars($recipe['difficulty']); ?></div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($recipe['prep_time'])): ?>
                        <div class="detail">
                            <span class="icon">⏰</span>
                            <div class="detail-label">Prep Time</div>
                            <div class="detail-value"><?php echo htmlspecialchars($recipe['prep_time']); ?></div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($recipe['cook_time'])): ?>
                        <div class="detail">
                            <span class="icon">🔥</span>
                            <div class="detail-label">Cook Time</div>
                            <div class="detail-value"><?php echo htmlspecialchars($recipe['cook_time']); ?></div>
                        </div>
                        <?php endif; ?>

                        <div class="detail">
                            <span class="icon">💤</span>
                            <div class="detail-label">Rest Time</div>
                            <div class="detail-value">15 mins</div>
                        </div>
                    </div>

                    <div class="detail-season">
                        <span class="icon">🎉</span>
                        <div class="detail-label">Best Season:</div>
                        <div class="detail-value">Acara Keluarga, Hari Besar, Arisan</div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($recipe['description'])): ?>
        <div class="recipes-description">
            <h2>Description</h2>
            <p><?php echo nl2br(htmlspecialchars($recipe['description'])); ?></p>
        </div>
        <?php endif; ?>

        <div class="recipe-content-2col">
            <div class="recipe-ingredients">
                <?php if (!empty($recipe['ingredients_main'])): ?>
                <h2>Bahan Utama</h2>
                <ol class="ingredients-list">
                    <?php
                    $main_ingredients = explode("\n", $recipe['ingredients_main']);
                    foreach ($main_ingredients as $ingredient):
                        if (trim($ingredient) != ''):
                    ?>
                        <li><?php echo htmlspecialchars(trim($ingredient)); ?></li>
                    <?php
                        endif;
                    endforeach;
                    ?>
                </ol>
                <?php endif; ?>

                <?php if (!empty($recipe['ingredients_seasoning'])): ?>
                <h2>Bumbu Halus</h2>
                <ol class="ingredients-list">
                    <?php
                    $seasoning_ingredients = explode("\n", $recipe['ingredients_seasoning']);
                    foreach ($seasoning_ingredients as $ingredient):
                        if (trim($ingredient) != ''):
                    ?>
                        <li><?php echo htmlspecialchars(trim($ingredient)); ?></li>
                    <?php
                        endif;
                    endforeach;
                    ?>
                </ol>
                <?php endif; ?>

                <?php if (!empty($recipe['ingredients_complementary'])): ?>
                <h2>Pelengkap</h2>
                <ol class="ingredients-list">
                    <?php
                    $complementary_ingredients = explode("\n", $recipe['ingredients_complementary']);
                    foreach ($complementary_ingredients as $ingredient):
                        if (trim($ingredient) != ''):
                    ?>
                        <li><?php echo htmlspecialchars(trim($ingredient)); ?></li>
                    <?php
                        endif;
                    endforeach;
                    ?>
                </ol>
                <?php endif; ?>
            </div>

            <div class="recipe-steps">
                <?php if (!empty($recipe['instructions'])): ?>
                <h2>Cara Pembuatan</h2>
                <ol class="steps-list">
                    <?php
                    $instructions = explode("\n", $recipe['instructions']);
                    foreach ($instructions as $instruction):
                        if (trim($instruction) != ''):
                    ?>
                        <li><?php echo htmlspecialchars(trim($instruction)); ?></li>
                    <?php
                        endif;
                    endforeach;
                    ?>
                </ol>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        feather.replace();

        const navbar = document.getElementById("mainNavbar");
        window.addEventListener("scroll", () => {
            if (window.scrollY > 20) {
                navbar.classList.add("scrolled");
            } else {
                navbar.classList.remove("scrolled");
            }
        });
    </script>
</body>
</html>
