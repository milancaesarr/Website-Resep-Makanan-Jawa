<?php
session_start();

// Proteksi akses: hanya untuk admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.html");
    exit();
}

require_once "db.php";

// Handle AJAX request for adding recipe (simplified version)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_recipe_action'])) {
    error_log("AJAX request received: " . print_r($_POST, true));

    $action = $_POST['action'] ?? '';
    $response = ['success' => false, 'message' => ''];

    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $region = trim($_POST['region'] ?? '');

        error_log("Processing add with: name=$name, description=$description, region=$region");

        if (empty($name) || empty($description) || empty($region)) {
            $response['message'] = "Nama, deskripsi, dan daerah harus diisi!";
        } else {
            // Simple insert without image first
            $sql = "INSERT INTO recipes (name, description, region) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);

            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "sss", $name, $description, $region);

                if (mysqli_stmt_execute($stmt)) {
                    $response['success'] = true;
                    $response['message'] = "Resep berhasil ditambahkan!";
                    error_log("Recipe added successfully");
                } else {
                    $response['message'] = "Gagal menambahkan resep: " . mysqli_stmt_error($stmt);
                    error_log("SQL execution failed: " . mysqli_stmt_error($stmt));
                }
                mysqli_stmt_close($stmt);
            } else {
                $response['message'] = "Gagal menyiapkan query: " . mysqli_error($conn);
                error_log("SQL prepare failed: " . mysqli_error($conn));
            }
        }
    }

    error_log("Response: " . json_encode($response));
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Resep - Simple</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal.show { display: flex; align-items: center; justify-content: center; }
        .modal-content { background: white; padding: 20px; border-radius: 10px; width: 500px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .btn { padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
        .btn-primary { background: #007bff; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .alert { padding: 10px; margin: 10px 0; border-radius: 5px; }
        .alert.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <h1>Test Simple Recipe Form</h1>
    <button onclick="openModal()" class="btn btn-primary">Tambah Resep</button>

    <!-- Simple Modal -->
    <div id="recipeModal" class="modal">
        <div class="modal-content">
            <h3>Tambah Resep</h3>
            <div id="modalAlert"></div>
            <form id="recipeForm">
                <input type="hidden" name="action" value="add">

                <div class="form-group">
                    <label for="name">Nama Resep:</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="region">Daerah Asal:</label>
                    <select id="region" name="region" required>
                        <option value="">Pilih Daerah</option>
                        <option value="Jawa Barat">Jawa Barat</option>
                        <option value="Jawa Tengah">Jawa Tengah</option>
                        <option value="Jawa Timur">Jawa Timur</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="description">Deskripsi:</label>
                    <textarea id="description" name="description" required></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Tambah Resep</button>
                <button type="button" onclick="closeModal()" class="btn btn-secondary">Batal</button>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('recipeModal').classList.add('show');
        }

        function closeModal() {
            document.getElementById('recipeModal').classList.remove('show');
            document.getElementById('modalAlert').innerHTML = '';
        }

        document.getElementById('recipeForm').addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Form submitted');

            const formData = new FormData(this);
            formData.append('ajax_recipe_action', '1');

            console.log('Form data:');
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }

            fetch('admin-recipes-simple.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.text();
            })
            .then(text => {
                console.log('Raw response:', text);
                try {
                    const data = JSON.parse(text);
                    console.log('Parsed response:', data);
                    const alertDiv = document.getElementById('modalAlert');

                    if (data.success) {
                        alertDiv.innerHTML = `
                            <div class="alert success">
                                ${data.message}
                            </div>
                        `;
                        setTimeout(() => {
                            closeModal();
                            location.reload();
                        }, 1500);
                    } else {
                        alertDiv.innerHTML = `
                            <div class="alert error">
                                ${data.message}
                            </div>
                        `;
                    }
                } catch (parseError) {
                    console.error('JSON Parse Error:', parseError);
                    console.error('Raw text:', text);
                    document.getElementById('modalAlert').innerHTML = `
                        <div class="alert error">
                            Server error: ${text.substring(0, 200)}
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                document.getElementById('modalAlert').innerHTML = `
                    <div class="alert error">
                        Network error: ${error.message}
                    </div>
                `;
            });
        });
    </script>
</body>
</html>
