<?php
include 'db.php';

$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: index.php');
    exit;
}

// fetch current
$res = mysqli_query($conn, "SELECT * FROM products WHERE id = $id LIMIT 1");
if (!$res || mysqli_num_rows($res) == 0) {
    header('Location: index.php');
    exit;
}
$row = mysqli_fetch_assoc($res);

$errors = [];

if (isset($_POST['submit'])) {
    $name  = trim($_POST['name']);
    $price = trim($_POST['price']);
    $currentImage = $row['image'];

    if ($name === '') $errors[] = 'Nama wajib diisi.';
    if ($price === '' || !is_numeric($price)) $errors[] = 'Harga harus angka.';

    $newName = $currentImage; // default keep

    // handle new upload (optional)
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
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $newName = time() . '_' . bin2hex(random_bytes(5)) . '.' . $ext;
            $target = __DIR__ . '/uploads/' . $newName;
            if (!move_uploaded_file($file_tmp, $target)) {
                $errors[] = 'Gagal memindahkan file ke folder uploads.';
            } else {
                // delete old file if exists and different
                if (!empty($currentImage) && file_exists(__DIR__ . '/uploads/' . $currentImage)) {
                    @unlink(__DIR__ . '/uploads/' . $currentImage);
                }
            }
        }
    }

    if (empty($errors)) {
        $name_sql  = mysqli_real_escape_string($conn, $name);
        $price_sql = (float)$price;
        $image_sql = mysqli_real_escape_string($conn, $newName);

        $sql = "UPDATE products SET name = '$name_sql', price = '$price_sql', image = '$image_sql' WHERE id = $id";
        if (mysqli_query($conn, $sql)) {
            header('Location: index.php');
            exit;
        } else {
            $errors[] = 'Error DB: ' . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Edit Product</title>
</head>
<body>
  <h2>Edit Product #<?php echo $row['id']; ?></h2>
  <?php if (!empty($errors)): ?>
    <div style="color:red;">
      <?php foreach ($errors as $e) echo "<div>- " . htmlspecialchars($e) . "</div>"; ?>
    </div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data">
    Name: <br><input type="text" name="name" value="<?php echo isset($name) ? htmlspecialchars($name) : htmlspecialchars($row['name']); ?>" required><br><br>
    Price: <br><input type="number" step="0.01" name="price" value="<?php echo isset($price) ? htmlspecialchars($price) : htmlspecialchars($row['price']); ?>" required><br><br>

    Current Image: <br>
    <?php if (!empty($row['image']) && file_exists(__DIR__ . '/uploads/' . $row['image'])): ?>
      <img src="uploads/<?php echo rawurlencode($row['image']); ?>" style="max-width:120px;"><br>
      <small><?php echo htmlspecialchars($row['image']); ?></small><br><br>
    <?php else: ?>
      (no image)<br><br>
    <?php endif; ?>

    Replace Image: <br><input type="file" name="image" accept="image/*"><br><small>(Leave empty to keep current)</small><br><br>

    <button type="submit" name="submit">Update</button>
    <a href="index.php" style="margin-left:10px;">Cancel</a>
  </form>
</body>
</html>
