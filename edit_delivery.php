<?php
session_start();

if(!isset($_SESSION["role"]) || $_SESSION["role"] != "admin"){
    header("Location: ../Login.html");
    exit();
}

include("../config.php");

$id = $_GET['id'];
$res = mysqli_query($conn,"SELECT * FROM delivery WHERE id='$id'");
$row = mysqli_fetch_assoc($res);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Delivery</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#000;
    color:white;
}

.card{
    background:#111;
    padding:30px;
    border-radius:20px;
}

input, textarea, select{
    background:#000 !important;
    color:white !important;
    border:1px solid #333 !important;
}

</style>
</head>

<body>

<div class="container mt-5">
<div class="card">

<h3 class="text-warning mb-4">✏️ Edit Delivery</h3>

<form action="update_delivery.php" method="POST">

<input type="hidden" name="id" value="<?= $row['id'] ?>">

<!-- ❌ LOCKED -->
<div class="mb-3">
<label>Address</label>
<textarea class="form-control" readonly><?= $row['address'] ?></textarea>
</div>

<div class="mb-3">
<label>Phone</label>
<input type="text" class="form-control" value="<?= $row['phone'] ?>" readonly>
</div>

<!-- ✅ EDITABLE -->
<div class="mb-3">
<label>Courier</label>
<input type="text" name="courier_service" class="form-control" value="<?= $row['courier_service'] ?>">
</div>

<div class="mb-3">
<label>Estimated Time</label>
<input type="text" name="estimated_time" class="form-control" value="<?= $row['estimated_time'] ?>">
</div>

<div class="mb-3">
<label>Progress (%)</label>
<input type="number" name="progress" class="form-control" value="<?= $row['progress'] ?>">
</div>

<div class="mb-3">
<label>Status</label>
<select name="delivery_status" class="form-select">
<option <?= $row['delivery_status']=="Processing"?'selected':'' ?>>Processing</option>
<option <?= $row['delivery_status']=="Dispatched"?'selected':'' ?>>Dispatched</option>
<option <?= $row['delivery_status']=="Out for Delivery"?'selected':'' ?>>Out for Delivery</option>
<option <?= $row['delivery_status']=="Delivered"?'selected':'' ?>>Delivered</option>
</select>
</div>

<button class="btn btn-warning">Update</button>
<a href="dilivery.php" class="btn btn-secondary">Back</a>

</form>

</div>
</div>

</body>
</html>