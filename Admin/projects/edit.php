<?php
// XAMPP/htdocs/portfolio2/Admin/certificates/edit.php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../../public/login.php");
    exit;
}

require_once dirname(__DIR__, 2) . '/app/config.php';
require_admin();

// Generate CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM certificates WHERE id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    die("Certificate not found!");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token");
    }

    $title = trim($_POST['title']);
    $issuer = trim($_POST['issuer']);
    $date = $_POST['date'];
    $image = $row['image']; // keep old image by default

    // Validate date
    if (!DateTime::createFromFormat('Y-m-d', $date)) {
        die("Invalid date format, must be YYYY-MM-DD");
    }

    // Handle file upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg','jpeg','png','gif'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $image = uniqid() . "." . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], "../../uploads/$image");
        }
    }

    $stmt = $pdo->prepare("UPDATE certificates SET title=?, issuer=?, date=?, image=? WHERE id=?");
    $stmt->execute([$title, $issuer, $date, $image, $id]);

    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit Certificate</title>
</head>
<body>
    <h1>Edit Certificate</h1>
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <p>
            <label>Title:<br>
                <input type="text" name="title" value="<?php echo htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </label>
        </p>
        <p>
            <label>Issuer:<br>
                <input type="text" name="issuer" value="<?php echo htmlspecialchars($row['issuer'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </label>
        </p>
        <p>
            <label>Date (YYYY-MM-DD):<br>
                <input type="date" name="date" value="<?php echo htmlspecialchars($row['date'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </label>
        </p>
        <p>
            <label>Image:<br>
                <input type="file" name="image" accept="image/*">
            </label><br>
            <?php if (!empty($row['image'])): ?>
                <img src="../../uploads/<?php echo htmlspecialchars($row['image'], ENT_QUOTES, 'UTF-8'); ?>" width="120" alt="Current certificate image">
            <?php endif; ?>
        </p>
        <button type="submit">Update</button>
    </form>
    <p><a href="index.php">Back</a></p>
</body>
</html>
