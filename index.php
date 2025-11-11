<?php
include 'db.php';

// --- Search ---
$search = '';
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

// --- Pagination setup ---
$limit = 5;
$page  = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1) $page = 1;
$start = ($page - 1) * $limit;

// --- Count total records (with search) ---
$search_sql = mysqli_real_escape_string($conn, $search);
$countSql = "SELECT COUNT(*) AS total FROM products WHERE name LIKE '%$search_sql%'";
$countResult = mysqli_query($conn, $countSql);
$countRow = mysqli_fetch_assoc($countResult);
$total = (int)$countRow['total'];
$pages = ceil($total / $limit);

// --- Fetch records (with search + limit) ---
$sql = "SELECT * FROM products WHERE name LIKE '%$search_sql%' ORDER BY created_at DESC LIMIT $start, $limit";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>CRUD - Daftar Produk</title>
    <style>
      body{font-family: Arial; margin:20px;}
      table{border-collapse:collapse; width:100%;}
      th, td{border:1px solid #ccc; padding:8px; text-align:left;}
      img{max-width:80px; max-height:80px;}
      .pagination a{margin:0 4px; text-decoration:none;}
      .topbar{display:flex; gap:12px; align-items:center; margin-bottom:12px;}
    </style>
</head>
<body>
<h2>Daftar Produk</h2>

<div class="topbar">
  <form method="GET" style="margin:0;">
    <input type="text" name="search" placeholder="Search by name..." value="<?php echo htmlspecialchars($search); ?>">
    <button type="submit">Search</button>
  </form>
  <a href="add.php" style="margin-left:auto;">+ Add New Product</a>
</div>

<table>
  <tr>
    <th>ID</th>
    <th>Nama</th>
    <th>Harga</th>
    <th>Gambar</th>
    <th>Dibuat</th>
    <th>Aksi</th>
  </tr>

  <?php if ($result && mysqli_num_rows($result) > 0): ?>
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
      <tr>
        <td><?php echo $row['id']; ?></td>
        <td><?php echo htmlspecialchars($row['name']); ?></td>
        <td><?php echo number_format($row['price'], 2, ',', '.'); ?></td>
        <td>
          <?php if (!empty($row['image']) && file_exists(__DIR__ . '/uploads/' . $row['image'])): ?>
            <img src="uploads/<?php echo rawurlencode($row['image']); ?>" alt="">
          <?php else: ?>
            (no image)
          <?php endif; ?>
        </td>
        <td><?php echo $row['created_at']; ?></td>
        <td>
          <a href="edit.php?id=<?php echo $row['id']; ?>">Edit</a> |
          <a href="delete.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Yakin hapus?')">Delete</a>
        </td>
      </tr>
    <?php endwhile; ?>
  <?php else: ?>
    <tr><td colspan="6">Tidak ada data.</td></tr>
  <?php endif; ?>
</table>

<div class="pagination" style="margin-top:12px;">
  <?php if ($pages > 1): ?>
    <?php for ($i = 1; $i <= $pages; $i++): ?>
      <?php if ($i == $page): ?>
        <strong><?php echo $i; ?></strong>
      <?php else: ?>
        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
      <?php endif; ?>
    <?php endfor; ?>
  <?php endif; ?>
</div>

</body>
</html>
