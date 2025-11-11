<?php
include 'db.php';

$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: index.php');
    exit;
}

// get record to know filename
$res = mysqli_query($conn, "SELECT image FROM products WHERE id = $id LIMIT 1");
if ($res && mysqli_num_rows($res) > 0) {
    $row = mysqli_fetch_assoc($res);
    $img = $row['image'];

    // delete DB record
    $del = mysqli_query($conn, "DELETE FROM products WHERE id = $id");
    if ($del) {
        // delete file if exists
        if (!empty($img) && file_exists(__DIR__ . '/uploads/' . $img)) {
            @unlink(__DIR__ . '/uploads/' . $img);
        }
    } else {
        // optionally handle error
        die('Error deleting: ' . mysqli_error($conn));
    }
}

header('Location: index.php');
exit;
