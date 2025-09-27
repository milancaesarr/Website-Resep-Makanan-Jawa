<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

require_once "db.php";


$user_id = $_SESSION['user_id'];


try {
    $check_columns_sql = "SHOW COLUMNS FROM users LIKE 'last_login'";
    $check_result = mysqli_query($conn, $check_columns_sql);

    if (mysqli_num_rows($check_result) > 0) {
        // Columns exist, update login tracking
        $update_login_sql = "UPDATE users SET last_login = NOW(), login_count = COALESCE(login_count, 0) + 1 WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_login_sql);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
    }
} catch (Exception $e) {
   
    error_log("Login tracking failed: " . $e->getMessage());
}

// Ambil beberapa resep untuk ditampilkan di homepage (limit 6) - ORDER BY id ASC untuk konsistensi dengan resep.php
$recipe_sql = "SELECT * FROM recipes ORDER BY id ASC LIMIT 6";
$recipe_result = mysqli_query($conn, $recipe_sql);

// Ambil beberapa produk untuk ditampilkan di homepage (limit 6)
$product_sql = "SELECT * FROM products WHERE stock > 0 ORDER BY id DESC LIMIT 6";
$product_result = mysqli_query($conn, $product_sql);


$user_count_sql = "SELECT COUNT(*) as count FROM users WHERE role = 'user'";
$user_count_result = mysqli_query($conn, $user_count_sql);
$user_count = mysqli_fetch_assoc($user_count_result)['count'];

$post_count_sql = "SELECT COUNT(*) as count FROM community_posts";
$post_count_result = mysqli_query($conn, $post_count_sql);
$post_count = mysqli_fetch_assoc($post_count_result)['count'];

$recipe_count_sql = "SELECT COUNT(*) as count FROM recipes";
$recipe_count_result = mysqli_query($conn, $recipe_count_sql);
$recipe_count = mysqli_fetch_assoc($recipe_count_result)['count'];
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title data-i18n="title">NusaJava Eats</title>
  <link rel="stylesheet" href="home.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    crossorigin="anonymous" />
  <script src="https://unpkg.com/feather-icons"></script>
  <style>
    /* Override dengan warna coklat untuk section resep saja */
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

    /* Recipe Cards dengan Brown Theme - hanya untuk section resep */
    .container-resep {
      max-width: 1240px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 32px 32px;
      justify-content: center;
      align-items: flex-start;
    }

    .container-resep .card {
      background-color: #a7774e;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(60, 60, 60, 0.11);
      width: 365px;
      min-width: 280px;
      flex-shrink: 0;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      align-items: center;
      transition: all 0.3s ease;
    }

    .container-resep .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }

    .container-resep .card-img {
      width: 100%;
      height: 185px;
      object-fit: cover;
      background: #eee;
      display: block;
    }

    .container-resep .card-content {
      padding: 17px 18px 22px 18px;
      display: flex;
      flex-direction: column;
      gap: 9px;
      background: #a7774e;
      height: 100%;
      width: 100%;
    }

    .container-resep .location {
      font-size: 1rem;
      font-weight: 600;
      letter-spacing: 0.2px;
      color: #fffaf3;
      opacity: 0.95;
      margin-bottom: 0.5px;
      margin-top: 2px;
      text-align: center;
    }

    .container-resep .food-name {
      font-size: 1.25rem;
      font-weight: bold;
      color: #252525;
      text-align: center;
      margin: 0;
      margin-bottom: 6px;
      line-height: 1.13;
    }

    .container-resep .card p {
      color: #fff;
      font-weight: 500;
      text-align: center;
      line-height: 1.4;
    }

    .container-resep .details-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      margin: 5px 35px 0px;
    }

    .container-resep .detail {
      display: flex;
      align-items: center;
      font-size: 1.04rem;
      color: #ffd5c2;
      gap: 7px;
    }

    .container-resep .detail .fa-clock {
      color: #ff837d;
      font-size: 1.07em;
      margin-right: 3px;
    }

    .container-resep .detail .fa-star {
      color: #ffc246;
      font-size: 1.09em;
      margin-right: 3px;
    }

    .container-resep .lihat-btn {
      margin: 13px auto 0 auto;
      padding: 7px 26px;
      outline: none;
      background: #e9a06e;
      color: #432a0b;
      font-weight: 600;
      font-size: 1rem;
      border: none;
      border-radius: 50px;
      cursor: pointer;
      transition: background 0.16s;
      text-decoration: none;
      box-shadow: 0 2px 8px #0002;
      display: inline-block;
    }

    .container-resep .lihat-btn:hover {
      background: #f9bc93;
    }

    /* Product Grid Layout for Homepage - Modified */
    .produk-grid-home {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 30px;
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
      justify-items: center;
    }

    .card-produk-home {
      min-width: 280px;
      max-width: 320px;
      background: #b07e4d;
      border-radius: 1.6em;
      box-shadow: 0 4px 18px 0 rgb(111 66 0 / 9%);
      padding: 20px 15px 25px 15px;
      display: flex;
      flex-direction: column;
      align-items: center;
      border: none;
      transition: box-shadow 0.15s, transform 0.15s;
      position: relative;
    }

    .card-produk-home:hover {
      box-shadow: 0 8px 38px 0 rgba(111,66,0,0.13);
      transform: translateY(-6px) scale(1.02);
    }

    .card-produk-home img {
      height: 120px;
      width: auto;
      border-radius: 12px;
      margin-bottom: 15px;
      background: #fff0d7;
      padding: 8px;
      box-shadow: 0 2px 8px #c28f6720;
      object-fit: cover;
    }

    .card-produk-home .product-name {
      font-weight: bold;
      font-size: 1.1rem;
      color: #fffbe8;
      text-align: center;
      margin-bottom: 10px;
      min-height: 50px;
      letter-spacing: 0.1px;
      line-height: 1.3;
      display: flex;
      align-items: center;
    }

    .card-produk-home .product-price {
      color: #ffeaa4;
      font-size: 1.2em;
      font-weight: 600;
      margin-bottom: 10px;
      text-align: center;
    }

    .card-produk-home .product-meta {
      margin-bottom: 15px;
      text-align: center;
    }

    .card-produk-home .lihat-produk-btn {
      background: #fff3ea;
      border: 1.5px solid #a7774e;
      color: #853c00;
      font-weight: 600;
      font-size: 1.1em;
      border-radius: 25px;
      padding: 12px 30px;
      cursor: pointer;
      text-decoration: none;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      width: 100%;
      justify-content: center;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .card-produk-home .lihat-produk-btn:hover {
      background: #ffc67b;
      color: #af2d2d;
      border-color: #d8922a;
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }

    /* Filter buttons dengan horizontal layout yang diperbaiki */
    .filter-button {
      display: flex;
      justify-content: center;
      margin-bottom: 20px;
      flex-wrap: wrap;
      gap: 15px;
      padding: 0 20px;
    }

    .filter-button button {
      background: #ff983e;
      color: #432a0b;
      font-size: 0.95rem;
      padding: 12px 24px;
      border-radius: 25px;
      cursor: pointer;
      transition: all 0.3s ease;
      text-align: center;
      white-space: nowrap;
      border: none;
      min-width: 120px;
      font-weight: 600;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .filter-button button:hover {
      background: #ffa95c;
      color: #2e1c06;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .filter-button button.active {
      background-color: #a7774e;
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(167, 119, 78, 0.3);
    }

    /* FAQ Section - Brown Theme Design - COMPLETELY FIXED */
    .pertanyaan {
      background: var(--mainColor);
      padding: 80px 20px;
      margin-top: 50px;
    }

    .faq-container {
      max-width: 1200px;
      margin: 0 auto;
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }

    .faq-title {
      font-size: 2.5rem;
      color: var(--secondaryColor);
      margin-bottom: 20px;
      line-height: 1.3;
      text-align: center;
      font-weight: bold;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
    }

    .faq-subtitle {
      font-size: 1.2rem;
      text-align: center;
      line-height: 1.6;
      max-width: 800px;
      margin: 0 auto 50px auto;
      background: var(--tripleColor);
      color: var(--white);
      padding: 20px;
      border-radius: 15px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .faq-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 30px;
      margin-top: 40px;
    }

    .faq-column {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    .faq-item {
      background: var(--tripleColor);
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      transition: all 0.3s ease;
      border: 3px solid transparent;
    }

    .faq-item:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.15);
      border-color: var(--accentColor);
    }

    .faq-question {
      width: 100%;
      background: var(--tripleColor);
      border: none;
      color: var(--white);
      font-size: 1.1rem;
      font-weight: 600;
      letter-spacing: 0.02em;
      text-align: left;
      padding: 25px 25px 25px 60px;
      position: relative;
      cursor: pointer;
      outline: none;
      transition: all 0.3s ease;
    }

    .faq-question:hover {
      background: var(--doubleColor);
    }

    .faq-question:before {
      content: "\f067";
      font-family: "Font Awesome 6 Free";
      font-weight: 900;
      position: absolute;
      left: 20px;
      top: 50%;
      transform: translateY(-50%);
      font-size: 1.2em;
      color: var(--white);
      transition: transform 0.3s ease;
    }

    .faq-item.open .faq-question {
      background: var(--doubleColor);
    }

    .faq-item.open .faq-question:before {
      content: "\f068";
      transform: translateY(-50%) rotate(180deg);
    }

    .faq-answer {
      background: var(--light-brown);
      color: var(--secondaryColor);
      font-size: 1rem;
      line-height: 1.7;
      max-height: 0;
      overflow: hidden;
      opacity: 0;
      padding: 0 25px;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .faq-item.open .faq-answer {
      max-height: 300px;
      opacity: 1;
      padding: 25px;
    }

    /* Community Preview Section - Brown Theme */
    .community-preview {
      background:rgb(224, 162, 96);
      padding: 80px 20px;
      margin-top: 50px;
      color: var(--white);
    }

    .community-container {
      max-width: 1200px;
      margin: 0 auto;
      text-align: center;
    }

    .community-title {
      font-size: 2.5rem;
      color: var(--white);
      margin-bottom: 20px;
      font-weight: bold;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
    }

    .community-desc {
      font-size: 1.2rem;
      color: var(--light-brown);
      margin-bottom: 50px;
      max-width: 800px;
      margin-left: auto;
      margin-right: auto;
      line-height: 1.6;
    }

    .community-stats {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 30px;
      margin-bottom: 50px;
    }

    .stat-card {
      background: var(--light-brown);
      padding: 30px;
      border-radius: 20px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      transition: all 0.3s ease;
      border: 3px solid transparent;
    }

    .stat-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 15px 30px rgba(0,0,0,0.2);
      border-color: var(--accentColor);
    }

    .stat-icon {
      background: linear-gradient(45deg, var(--accentColor), var(--secondaryColor));
      color: var(--white);
      width: 80px;
      height: 80px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2rem;
      margin: 0 auto 20px auto;
      box-shadow: 0 5px 15px rgba(175, 45, 45, 0.3);
    }

    .stat-number {
      font-size: 2.5rem;
      font-weight: bold;
      color: var(--secondaryColor);
      margin-bottom: 10px;
    }

    .stat-label {
      color: var(--doubleColor);
      font-size: 1.1rem;
      font-weight: 500;
    }

    .community-cta {
      background: linear-gradient(45deg, var(--accentColor), var(--secondaryColor));
      color: var(--white);
      padding: 18px 40px;
      border: none;
      border-radius: 25px;
      font-size: 1.2rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 10px;
      text-decoration: none;
      box-shadow: 0 5px 15px rgba(175, 45, 45, 0.3);
    }

    .community-cta:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(175, 45, 45, 0.4);
      background: linear-gradient(45deg, var(--secondaryColor), var(--accentColor));
    }

    /* Contact Preview Section - Brown Theme */
    .contact-preview {
      background: rgb(224, 162, 96);
      padding: 80px 20px;
      margin-top: 50px;
      color: var(--white);
    }

    .contact-container {
      max-width: 1200px;
      margin: 0 auto;
    }

    .contact-content {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 50px;
      align-items: center;
    }

    .contact-info h2 {
      font-size: 2.5rem;
      margin-bottom: 20px;
      font-weight: bold;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
    }

    .contact-info p {
      font-size: 1.2rem;
      line-height: 1.6;
      margin-bottom: 30px;
      opacity: 0.9;
    }

    .contact-quick-info {
      display: flex;
      flex-direction: column;
      gap: 15px;
      margin-bottom: 30px;
    }

    .contact-item {
      display: flex;
      align-items: center;
      gap: 15px;
      font-size: 1.1rem;
    }

    .contact-item i {
      background: var(--light-brown);
      color: var(--accentColor);
      width: 50px;
      height: 50px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.2rem;
    }

    .contact-form-preview {
      background: var(--light-brown);
      padding: 40px;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }

    .contact-form-preview h3 {
      color: var(--secondaryColor);
      font-size: 1.8rem;
      margin-bottom: 20px;
      text-align: center;
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
    .form-group textarea {
      width: 100%;
      padding: 12px 15px;
      border: 2px solid var(--tripleColor);
      border-radius: 10px;
      font-size: 1rem;
      transition: all 0.3s ease;
      font-family: inherit;
      box-sizing: border-box;
    }

    .form-group input:focus,
    .form-group textarea:focus {
      outline: none;
      border-color: var(--accentColor);
      box-shadow: 0 0 10px rgba(175, 45, 45, 0.2);
    }

    .form-group textarea {
      min-height: 100px;
      resize: vertical;
    }

    .form-actions {
      display: flex;
      gap: 15px;
    }

    .btn-preview {
      flex: 1;
      padding: 12px 25px;
      border: none;
      border-radius: 10px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      text-decoration: none;
      text-align: center;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }

    .btn-primary {
      background: var(--accentColor);
      color: var(--white);
    }

    .btn-secondary {
      background: var(--tripleColor);
      color: var(--white);
    }

    .btn-preview:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }

    /* Additional Services Section - Brown Theme */
    .additional-services {
      background: var(--mainColor);
      padding: 60px 20px;
      margin-top: 50px;
    }

    .services-container {
      max-width: 1200px;
      margin: 0 auto;
      text-align: center;
    }

    .services-title {
      font-size: 2.5rem;
      color: var(--secondaryColor);
      margin-bottom: 20px;
      font-weight: bold;
    }

    .services-desc {
      font-size: 1.2rem;
      color: var(--doubleColor);
      margin-bottom: 50px;
      max-width: 700px;
      margin-left: auto;
      margin-right: auto;
    }

    .services-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
      gap: 40px;
      margin-top: 40px;
    }

    .service-card {
      background: linear-gradient(135deg, var(--light-brown), var(--mainColor));
      padding: 40px 30px;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      transition: all 0.3s ease;
      text-align: center;
      border: 3px solid transparent;
    }

    .service-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 20px 40px rgba(0,0,0,0.2);
      border-color: var(--accentColor);
    }

    .service-icon {
      background: var(--accentColor);
      color: var(--white);
      width: 80px;
      height: 80px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2rem;
      margin: 0 auto 25px auto;
      box-shadow: 0 5px 15px rgba(175, 45, 45, 0.3);
    }

    .service-card h3 {
      font-size: 1.8rem;
      color: var(--secondaryColor);
      margin-bottom: 20px;
      font-weight: bold;
    }

    .service-card p {
      color: var(--doubleColor);
      font-size: 1rem;
      line-height: 1.6;
      margin-bottom: 25px;
    }

    .service-features {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
      margin-bottom: 30px;
    }

    .service-features span {
      display: flex;
      align-items: center;
      gap: 8px;
      color: var(--secondaryColor);
      font-weight: 500;
      font-size: 0.9rem;
    }

    .service-features i {
      color: var(--accentColor);
      font-size: 0.8rem;
    }

    .service-btn {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      background: linear-gradient(45deg, var(--accentColor), var(--secondaryColor));
      color: var(--white);
      padding: 15px 30px;
      border-radius: 25px;
      text-decoration: none;
      font-weight: 600;
      font-size: 1.1rem;
      transition: all 0.3s ease;
      box-shadow: 0 5px 15px rgba(175, 45, 45, 0.3);
    }

    .service-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(175, 45, 45, 0.4);
      background: linear-gradient(45deg, var(--secondaryColor), var(--accentColor));
    }

    /* Responsive adjustments */
    @media (max-width: 1100px) {
      .container-resep {
        grid-template-columns: repeat(2, 1fr);
      }

      .produk-grid-home {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 25px;
      }
    }

    @media (max-width: 780px) {
      .container-resep {
        grid-template-columns: 1fr;
        gap: 20px;
        padding: 0 7px;
      }
.produk-grid-home {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        padding: 15px;
      }

      .filter-button {
        flex-direction: column;
        gap: 8px;
      }

      .filter-button button {
        min-width: 100%;
        padding: 10px 16px;
      }

      .services-grid {
        grid-template-columns: 1fr;
        gap: 30px;
      }

      .services-title {
        font-size: 2rem;
      }

      .service-features {
        grid-template-columns: 1fr;
        gap: 8px;
      }

      .service-card {
        padding: 30px 20px;
      }

      .community-title,
      .faq-title {
        font-size: 2rem;
      }

      .contact-content {
        grid-template-columns: 1fr;
      }

      .contact-info h2 {
        font-size: 2rem;
      }
      .faq-grid {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 480px) {
      .filter-button {
        padding: 0 10px;
      }

      .produk-grid-home {
        grid-template-columns: 1fr;
        padding: 10px;
      }

      .card-produk-home {
        min-width: 100%;
        max-width: 100%;
      }
    }
  </style>
</head>

<body>

  <?php include 'navbar-home.php'; ?>

  <div id="searchResult"></div>

  <section class="slider-1">
    <div class="slides">
      <div class="slide">
        <img src="Assets/Img/banner/Banner 1.png" alt="Banner 1">
        <div class="content">
          <h1 data-i18n="brand_title">NusaJava Eats</h1>
          <p data-i18n="brand_subtitle_1">Dari Dapur Jawa, Untuk Dunia...</p>
          <button class="banner" data-i18n="btn_visit">Lihat</button>
        </div>
      </div>
      <div class="slide">
        <img src="Assets/Img/banner/Banner 2.png" alt="Banner 2">
        <div class="content">
          <h1 data-i18n="brand_title">NusaJava Eats</h1>
          <p data-i18n="brand_subtitle_2">Rasa Tradisi, Sentuhan Modern...</p>
          <button class="banner" data-i18n="btn_visit">Lihat</button>
        </div>
      </div>
      <div class="slide">
        <img src="Assets/Img/banner/Banner 3.png" alt="Banner 3">
        <div class="content">
          <h1 data-i18n="brand_title">NusaJava Eats</h1>
          <p data-i18n="brand_subtitle_3">Lebih dari Sekadar Makan...</p>
          <button class="banner" data-i18n="btn_visit">Lihat</button>
        </div>
      </div>
    </div>
    <div class="navigation">
      <span class="prev">&#10094;</span>
      <span class="next">&#10095;</span>
    </div>
    <div class="dots">
      <span class="dot active"></span>
      <span class="dot"></span>
      <span class="dot"></span>
    </div>
  </section>

<section class="about" id="about">
    <div class="about-content">
      <div class="about-left">
        <h1 class="about-title" data-i18n="about_title">About Us</h1>
        <div class="about-img">
          <img src="Assets/Img/Gudeg.png" alt="Makanan Jawa">
        </div>
        <div class="about-text">
          <h4 class="about-2" data-i18n="about_variety_title">Keanekaragaman Makanan</h4>
          <p class="deskripsi" data-i18n="about_variety_desc">Makanan khas Jawa memiliki cita rasa yang beragam, mulai
            dari manis, gurih, hingga pedas. Contohnya, Gudeg yang manis khas Yogyakarta, Rawon dengan kuah hitam khas
            Jawa Timur, dan Soto Betawi yang gurih dan kaya rempah.</p>
          <center><button data-i18n="btn_learn">Ayo Lihat</button></center>
        </div>
      </div>
      <div class="about-right">
        <div class="about-text-3">
          <p class="about-1" data-i18n="about_intro">Kenapa Sih Kita Mau Memperkenalkan Makanan Tradisional Jawa ke
            Dunia?</p>
          <h2 data-i18n="about_subtitle">Jelajahi Kekayaan Rasa Dari Jawa</h2>
          <div class="about-img">
            <img src="Assets/Img/Soto Betawi Jakarta.png" alt="Makanan Jawa">
          </div>
        </div>
        <div class="about-text-2">
          <h4 class="about-2" data-i18n="about_flavor_title">Keanekaragaman Rasa</h4>
          <p class="deskripsi" data-i18n="about_flavor_desc">Pulau Jawa menyimpan kekayaan kuliner yang tak ternilai.
            Dari hidangan manis, gurih, hingga pedas, semua kami hadirkan dengan rasa yang otentik. Di balik setiap
            menu, ada cerita tentang tanah, petani, dan resep yang diwariskan dari generasi ke generasi.</p>
          <center><button data-i18n="btn_learn">Ayo Lihat</button></center>
        </div>
      </div>
    </div>
  </section>

<section class="resep">
    <h1 data-i18n="resep_title">Menyusuri Rasa di Tanah Jawa<br>Ayo Lihat!!</h1>
    <p data-i18n="resep_desc">Jelajahi kelezatan khas pulau Jawa lewat ragam kuliner autentik yang menggugah selera.
      Dari manisnya Gudeg Yogyakarta, pedas gurihnya Seblak Jawa Barat, hingga segarnya Soto Betawi dari Jakarta—setiap
      sajian menyimpan cerita dan budaya yang kaya. <br class="br"> Ayo lihat dan rasakan sendiri cita rasa Nusantara
      yang tak terlupakan!</p>
    <div class="slider-container">
      <div class="slider-nav">
        <button class="slider-btn" id="btn-prev"><i class="fa-solid fa-arrow-left"></i></button>
        <button class="slider-btn" id="btn-next"><i class="fa-solid fa-arrow-right"></i></button>
      </div>
      <div class="slider" id="slider">
        <!-- Kartu akan dimasukkan oleh JS -->
      </div>
    </div>
  </section>

  <center>
    <section class="hero">
      <h1 data-i18n="hero_title">Warisan Rasa Nusantara: Resep Kuliner Terbaik dari Tanah Jawa</h1>
      <p data-i18n="hero_desc">Website ini menghadirkan berbagai resep autentik dari Jawa Timur, Jawa Tengah, Jawa
        Barat, Yogyakarta, Jakarta, dan Banten. Disusun dengan menjaga cita rasa tradisional, setiap resep siap membawa
        kehangatan budaya Nusantara ke meja makan Anda. Temukan inspirasi, jelajahi rasa, dan nikmati warisan kuliner
        terbaik Indonesia di sini. Temukan inspirasi memasak, rasakan nostalgia, dan hadirkan kelezatan warisan
        Nusantara di meja makan Anda — hanya di sini.</p>
      <div class="hero-content">
        <h4 data-i18n="hero_slogan">Cari Resep Nusantara? Ini Dia Kumpulan Terbaik dari Tanah Jawa!</h4>
        

        <!-- BAGIAN RESEP YANG DIUPDATE DENGAN DATABASE DAN TEMA COKLAT -->
        <div class="container-resep">
          <?php if ($recipe_result && mysqli_num_rows($recipe_result) > 0): ?>
            <?php while ($recipe = mysqli_fetch_assoc($recipe_result)): ?>
              <div class="card mix <?php echo strtolower(str_replace(' ', '-', $recipe['region'] ?? '')); ?>">
                <?php if (!empty($recipe['image_url'])): ?>
                  <img class="card-img" src="<?php echo htmlspecialchars($recipe['image_url']); ?>" alt="<?php echo htmlspecialchars($recipe['name'] ?? ''); ?>" />
                <?php else: ?>
                  <div style="width:100%;height:185px;background:#eee;display:flex;align-items:center;justify-content:center;color:#666;">No Image</div>
                <?php endif; ?>
                <div class="card-content">
                  <div class="location"><?php echo htmlspecialchars($recipe['region'] ?? ''); ?></div>
                  <div class="food-name"><?php echo htmlspecialchars($recipe['name'] ?? ''); ?></div>
                  <p><?php echo htmlspecialchars(substr($recipe['description'] ?? '', 0, 80)) . '...'; ?></p>
                  <div class="details-row">
                    <span class="detail"><i class="fa-solid fa-clock"></i> <?php echo htmlspecialchars($recipe['cook_time'] ?? ''); ?></span>
                    <span class="detail"><i class="fa-solid fa-star"></i> <?php echo $recipe['rating'] ?? '0'; ?></span>
                  </div>
                  <a href="resep-detail.php?id=<?php echo $recipe['id']; ?>" class="lihat-btn">Lihat</a>
                </div>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <!-- Fallback static cards jika database belum ada data -->
            <div class="card mix jawa-tengah">
              <img class="card-img" src="Assets/Img/makanan/Nasi Liwet.png" alt="Nasi Liwet">
              <div class="card-content">
                <div class="location">Jawa Tengah</div>
                <div class="food-name">Nasi Liwet</div>
                <p>Nasi gurih khas dari Solo, Jawa Tengah yang kaya rasa dan aroma santan.</p>
                <div class="details-row">
                  <span class="detail"><i class="fa-solid fa-clock"></i> 1 hr</span>
                  <span class="detail"><i class="fa-solid fa-star"></i> 4.4</span>
                </div>
                <a href="resep.php" class="lihat-btn">Lihat</a>
              </div>
            </div>
            <div class="card mix yogyakarta">
              <img class="card-img" src="Assets/Img/makanan/Gudeg Jogja.png" alt="Gudeg" />
              <div class="card-content">
                <div class="location">Yogyakarta</div>
                <div class="food-name">Gudeg</div>
                <p>Makanan khas Yogyakarta berbahan nangka muda dimasak dengan santan.</p>
                <div class="details-row">
                  <span class="detail"><i class="fa-solid fa-clock"></i> 50 mins</span>
                  <span class="detail"><i class="fa-solid fa-star"></i> 4.8</span>
                </div>
                <a href="resep.php" class="lihat-btn">Lihat</a>
              </div>
            </div>
            <div class="card mix jawa-barat">
              <img class="card-img" src="Assets/Img/makanan/Seblak.png" alt="Seblak" />
              <div class="card-content">
                <div class="location">Jawa Barat</div>
                <div class="food-name">Seblak</div>
                <p>Masakan khas Bandung dengan rasa pedas gurih berbahan kerupuk basah.</p>
                <div class="details-row">
                  <span class="detail"><i class="fa-solid fa-clock"></i> 45 mins</span>
                  <span class="detail"><i class="fa-solid fa-star"></i> 4.7</span>
                </div>
                <a href="resep.php" class="lihat-btn">Lihat</a>
              </div>
            </div>
            <div class="card mix jakarta">
              <img class="card-img" src="Assets/Img/makanan/Soto Betawi.png" alt="Soto Betawi" />
              <div class="card-content">
                <div class="location">DKI Jakarta</div>
                <div class="food-name">Soto Betawi</div>
                <p>Soto khas Betawi dengan kuah santan atau susu yang kaya rasa.</p>
                <div class="details-row">
                  <span class="detail"><i class="fa-solid fa-clock"></i> 55 mins</span>
                  <span class="detail"><i class="fa-solid fa-star"></i> 4.5</span>
                </div>
                <a href="resep.php" class="lihat-btn">Lihat</a>
              </div>
            </div>
            <div class="card mix jawa-timur">
              <img class="card-img" src="Assets/Img/makanan/Rawon.png" alt="Rawon" />
              <div class="card-content">
                <div class="location">Jawa Timur</div>
                <div class="food-name">Rawon</div>
                <p>Sup daging sapi khas Jawa Timur dengan kuah hitam dari kluwek.</p>
                <div class="details-row">
                  <span class="detail"><i class="fa-solid fa-clock"></i> 1 hr</span>
                  <span class="detail"><i class="fa-solid fa-star"></i> 4.6</span>
                </div>
                <a href="resep.php" class="lihat-btn">Lihat</a>
              </div>
            </div>
            <div class="card mix banten">
              <img class="card-img" src="Assets/Img/makanan/Sate Bandeng.png" alt="Sate Bandeng" />
              <div class="card-content">
                <div class="location">Banten</div>
                <div class="food-name">Sate Bandeng</div>
                <p>Ikan bandeng berbumbu khas Banten yang dibakar hingga matang sempurna.</p>
                <div class="details-row">
                  <span class="detail"><i class="fa-solid fa-clock"></i> 40 mins</span>
                  <span class="detail"><i class="fa-solid fa-star"></i> 4.3</span>
                </div>
                <a href="resep.php" class="lihat-btn">Lihat</a>
              </div>
            </div>
          <?php endif; ?>
        </div>

        <div class="lihat-semua-container">
          <a href="resep.php" class="lihat-semua-btn" data-i18n="btn_lihat_semua">Lihat Semua <i
              data-feather="arrow-right"></i></a>
        </div>
      </div>
    </section>
  </center>

<!-- Product Section with Grid Layout - No Cart Functionality -->
<section class="produk">
    <div class="container-produk">
        <h1 data-i18n="produk_title">Bumbu Instant Nusantara: Kepraktisan Rasa Khas Tanah Jawa</h1>
        <p class="desc" data-i18n="produk_desc">Dengan beragam rempah yang ada, setiap hidangan menjadi lebih istimewa,
            memancarkan kelezatan yang tidak terlupakan. Mari jelajahi kekayaan cita rasa dari Tanah Jawa, yang menyatukan
            tradisi dan kelezatan dalam setiap hidangan.</p>

        <h4 data-i18n="produk_slogan" style="text-align: center; margin: 30px 0; color: var(--secondaryColor);">
            Cari Bumbu khas Nusantara selalu berhasil memberikan sentuhan rasa yang kaya dan menggugah selera!
        </h4>

        <div class="produk-grid-home">
            <?php
            // Fixed product display for homepage
            if ($product_result && mysqli_num_rows($product_result) > 0) {
                mysqli_data_seek($product_result, 0);
                $count = 0;
                while (($product = mysqli_fetch_assoc($product_result)) && $count < 6):
            ?>
                    <div class="card-produk-home">
                        <img src="<?php echo htmlspecialchars($product['image_url'] ?? ''); ?>" alt="<?php echo htmlspecialchars($product['name'] ?? ''); ?>" />
                        <div class="product-name"><?php echo htmlspecialchars($product['name'] ?? ''); ?></div>
                        <div class="product-price">Rp<?php echo number_format($product['price'] ?? 0, 0, ',', '.'); ?></div>
                        <div class="product-meta">
                            <?php if (!empty($product['brand'])): ?>
                                <small style="color: #ffeaa4; font-size: 0.85em;">Brand: <?php echo htmlspecialchars($product['brand']); ?></small>
                            <?php endif; ?>
                        </div>
                        <a href="product.php" class="lihat-produk-btn" data-i18n="btn_lihat_produk">
                            <i data-feather="eye"></i> Lihat Detail
                        </a>
                    </div>
            <?php
                    $count++;
                endwhile;
            } else {
            ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 40px; color: var(--doubleColor);">
                    <p>Belum ada produk tersedia.</p>
                    <a href="product.php" class="lihat-semua-btn">Jelajahi Produk</a>
                </div>
            <?php } ?>
        </div>

        <div class="lihat-semua-container">
            <a href="product.php" class="lihat-semua-btn" data-i18n="btn_lihat_semua">Lihat Semua Produk <i
                data-feather="arrow-right"></i></a>
        </div>
    </div>
</section>

  <!-- FAQ Section yang Diperbaiki -->
  <section class="pertanyaan">
    <div class="faq-container">
      <h1 class="faq-title" data-i18n="faq_title">
        <i class="fas fa-question-circle"></i>
        Temukan Jawaban Seputar NusaJavaEats di Sini!
      </h1>
      <div class="faq-subtitle" data-i18n="faq_subtitle">
        Punya pertanyaan tentang NusaJavaEats? Simak FAQ berikut ini.
        Jika pertanyaan Anda belum terjawab, silakan hubungi tim kami!
      </div>
      <div class="faq-grid">
        <div class="faq-column">
          <div class="faq-item">
            <button class="faq-question" data-i18n="faq_q1">Apa itu NusaJavaEats?</button>
            <div class="faq-answer" data-i18n="faq_a1">NusaJavaEats adalah website yang menyajikan resep-resep masakan
              tradisional dari berbagai daerah di Pulau Jawa, seperti Jawa Timur, Jawa Tengah, Jawa Barat, Yogyakarta,
              Jakarta, dan Banten. Kami juga menyediakan produk bumbu instan asli Nusantara untuk memudahkan Anda memasak
              di rumah.</div>
          </div>
          <div class="faq-item">
            <button class="faq-question" data-i18n="faq_q2">Resep apa saja yang tersedia di NusaJavaEats?</button>
            <div class="faq-answer" data-i18n="faq_a2">Kami menyediakan berbagai resep khas Jawa, mulai dari Rawon, Gudeg,
              Soto Betawi, hingga Sate Bandeng Banten, dan masih banyak lagi.</div>
          </div>
          <div class="faq-item">
            <button class="faq-question" data-i18n="faq_q3">Apakah semua resep di NusaJavaEats autentik?</button>
            <div class="faq-answer" data-i18n="faq_a3">Ya, semua resep kami dikurasi dengan teliti untuk menjaga keaslian
              rasa dan tradisi masakan dari setiap daerah di Pulau Jawa.</div>
          </div>
          <div class="faq-item">
            <button class="faq-question" data-i18n="faq_q4">Apakah NusaJavaEats menjual produk bumbu instan?</button>
            <div class="faq-answer" data-i18n="faq_a4">Betul! Kami menawarkan berbagai pilihan bumbu instan berkualitas
              yang memudahkan Anda memasak makanan khas Jawa di rumah dengan cita rasa otentik.</div>
          </div>
        </div>
        <div class="faq-column">
          <div class="faq-item">
            <button class="faq-question" data-i18n="faq_q5">Bagaimana cara membeli bumbu instan dari
              NusaJavaEats?</button>
            <div class="faq-answer" data-i18n="faq_a5">Anda bisa langsung membeli melalui website kami di halaman produk,
              pilih bumbu yang Anda inginkan, dan selesaikan transaksi dengan metode pembayaran yang tersedia.</div>
          </div>
          <div class="faq-item">
            <button class="faq-question" data-i18n="faq_q6">Apakah pengiriman produk tersedia ke seluruh
              Indonesia?</button>
            <div class="faq-answer" data-i18n="faq_a6">Ya, kami melayani pengiriman bumbu instan ke seluruh wilayah
              Indonesia.</div>
          </div>
          <div class="faq-item">
            <button class="faq-question" data-i18n="faq_q7">Apakah ada tips memasak menggunakan bumbu instan dari
              NusaJavaEats?</button>
            <div class="faq-answer" data-i18n="faq_a7">Tentu! Setiap produk bumbu instan kami dilengkapi dengan panduan
              memasak praktis agar Anda dapat menghasilkan masakan lezat dengan mudah.</div>
          </div>
          <div class="faq-item">
            <button class="faq-question" data-i18n="faq_q8">Apakah saya bisa meminta resep baru di NusaJavaEats?</button>
            <div class="faq-answer" data-i18n="faq_a8">Kami sangat terbuka terhadap permintaan resep. Anda dapat
              mengajukan permintaan melalui formulir kontak di website kami.</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Community Preview Section -->
  <section class="community-preview">
    <div class="community-container">
      <h2 class="community-title">
        <i class="fas fa-users"></i>
        Bergabung dengan Komunitas NusaJava Eats
      </h2>
      <p class="community-desc">
        Jelajahi lebih banyak fitur dan bergabunglah dengan komunitas pecinta kuliner Nusantara.
        Berbagi resep, tips masak, dan diskusi seputar makanan tradisional Jawa bersama ribuan anggota lainnya.
      </p>

      <div class="community-stats">
        <div class="stat-card">
          <div class="stat-icon">
            <i class="fas fa-users"></i>
          </div>
          <div class="stat-number"><?php echo $user_count; ?>+</div>
          <div class="stat-label">Total Anggota</div>
        </div>

        <div class="stat-card">
          <div class="stat-icon">
            <i class="fas fa-comments"></i>
          </div>
          <div class="stat-number"><?php echo $post_count; ?>+</div>
          <div class="stat-label">Diskusi Aktif</div>
        </div>

        <div class="stat-card">
          <div class="stat-icon">
            <i class="fas fa-book"></i>
          </div>
          <div class="stat-number"><?php echo $recipe_count; ?>+</div>
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

      <a href="komunitas.php" class="community-cta">
        <i class="fas fa-arrow-right"></i>
        Gabung Komunitas Sekarang
      </a>
    </div>
  </section>

  <!-- Contact Preview Section -->
  <section class="contact-preview">
    <div class="contact-container">
      <div class="contact-content">
        <div class="contact-info">
          <h2>
            <i class="fas fa-envelope"></i>
            Hubungi Kami
          </h2>
          <p>Punya pertanyaan atau saran? Tim customer service kami siap membantu Anda 24/7. Hubungi kami untuk informasi lebih lanjut tentang resep atau produk kami.</p>

          <div class="contact-quick-info">
            <div class="contact-item">
              <i class="fas fa-phone"></i>
              <span>+62 21 1234 5678</span>
            </div>
            <div class="contact-item">
              <i class="fas fa-envelope"></i>
              <span>info@nusajavaeats.com</span>
            </div>
            <div class="contact-item">
              <i class="fas fa-clock"></i>
              <span>24/7 Customer Support</span>
            </div>
          </div>

          <a href="contact.php" class="community-cta" style="background: var(--light-brown); color: var(--accentColor); margin-top: 20px;">
            <i class="fas fa-arrow-right"></i>
            Form Lengkap
          </a>
        </div>

        <div class="contact-form-preview">
          <h3>
            <i class="fas fa-paper-plane"></i>
            Kirim Pesan Cepat
          </h3>

          <form id="quickContactForm">
            <div class="form-group">
              <label for="quick_name">Nama</label>
              <input type="text" id="quick_name" name="name" required placeholder="Masukkan nama Anda">
            </div>

            <div class="form-group">
              <label for="quick_email">Email</label>
              <input type="email" id="quick_email" name="email" required placeholder="Masukkan email Anda">
            </div>

            <div class="form-group">
              <label for="quick_message">Pesan</label>
              <textarea id="quick_message" name="message" required placeholder="Tulis pesan singkat..."></textarea>
            </div>

            <div class="form-actions">
              
              <a href="contact.php" class="btn-preview btn-secondary">
                <i class="fas fa-edit"></i>
                Form Lengkap
              </a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>

  <?php include 'footer.php'; ?>

  <!-- JavaScript Files -->
  <script>
    
    document.addEventListener('DOMContentLoaded', function() {
      updateHomeCartCount();
      feather.replace();
    });

    feather.replace();
  </script>

  <script data-cfasync="false" src="/cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script>
  <script src="home.js"></script>
  <Script src="home2.js"></Script>
  <script src="slider.js"></script>
  <script src="mixitup.min.js"></script>
  <script src="https://unpkg.com/feather-icons"></script>
  <script>
    feather.replace();
  </script>
  <script>
    var mixer = mixitup('.container-resep');
  </script>
  <script>
    feather.replace(); // untuk ikon feather
  </script>

  
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const faqItems = document.querySelectorAll('.faq-item');

      faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');

        question.addEventListener('click', () => {
          const isOpen = item.classList.contains('open');

          
          faqItems.forEach(otherItem => {
            if (otherItem !== item) {
              otherItem.classList.remove('open');
            }
          });

          
          if (isOpen) {
            item.classList.remove('open');
          } else {
            item.classList.add('open');
          }
        });
      });
    });
  </script>
</body>
</html>
