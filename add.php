<?php
include 'db.php';

$errors = [];
$success = false;

if (isset($_POST['submit'])) {
    $name  = trim($_POST['name']);
    $price = trim($_POST['price']);

    // simple validation
    if ($name === '') $errors[] = 'Nama wajib diisi.';
    if ($price === '' || !is_numeric($price)) $errors[] = 'Harga harus angka.';

    // File upload handling
    if (!empty($_FILES['image']['name'])) {
        $allowed = ['image/jpeg','image/png','image/gif'];
        $file_type = $_FILES['image']['type'];
        $file_tmp  = $_FILES['image']['tmp_name'];
        $file_err  = $_FILES['image']['error'];
        $file_size = $_FILES['image']['size'];

        if ($file_err !== UPLOAD_ERR_OK) {
            $errors[] = 'Upload gagal (error code: ' . $file_err . ').';
        } elseif (!in_array($file_type, $allowed)) {
            $errors[] = 'Tipe file tidak diijinkan. Hanya JPG/PNG/GIF.';
        } elseif ($file_size > 2 * 1024 * 1024) {
            $errors[] = 'Ukuran file terlalu besar (max 2MB).';
        } else {
            // create safe unique filename
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $newName = time() . '_' . bin2hex(random_bytes(5)) . '.' . $ext;
            $target = __DIR__ . '/uploads/' . $newName;
            if (!move_uploaded_file($file_tmp, $target)) {
                $errors[] = 'Gagal memindahkan file ke folder uploads.';
            }
        }
    } else {
        $newName = ''; // no image
    }

    if (empty($errors)) {
        $name_sql  = mysqli_real_escape_string($conn, $name);
        $price_sql = (float)$price;

        $sql = "INSERT INTO products (name, price, image) VALUES ('$name_sql', '$price_sql', '" . mysqli_real_escape_string($conn, $newName) . "')";
        if (mysqli_query($conn, $sql)) {
            header('Location: index.php');
            exit;
        } else {
            $errors[] = 'Error DB: ' . mysqli_error($conn);
            // if DB failed and we uploaded file, remove it
            if (!empty($newName) && file_exists(__DIR__ . '/uploads/' . $newName)) {
                @unlink(__DIR__ . '/uploads/' . $newName);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Add Product</title>
</head>
<body>
  <h2>Add New Product</h2>
  <?php if (!empty($errors)): ?>
    <div style="color:red;">
      <?php foreach ($errors as $e) echo "<div>- " . htmlspecialchars($e) . "</div>"; ?>
    </div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data">
    Name: <br><input type="text" name="name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required><br><br>
    Price: <br><input type="number" step="0.01" name="price" value="<?php echo isset($price) ? htmlspecialchars($price) : ''; ?>" required><br><br>
    Image: <br><input type="file" name="image" accept="image/*"><br><small>(JPG/PNG/GIF, max 2MB)</small><br><br>
    <button type="submit" name="submit">Save</button>
    <a href="index.php" style="margin-left:10px;">Cancel</a>
  </form>
</body>
</html>
