<?php
// XAMPP/htdocs/portfolio2/Admin/certificates/create.php
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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token");
    }

    $title = trim($_POST['title']);
    $issuer = trim($_POST['issuer']);
    $date = $_POST['date'];
    $image = null;

    // Validate date format
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

    $stmt = $pdo->prepare("INSERT INTO certificates (title, issuer, date, image) VALUES (?, ?, ?, ?)");
    $stmt->execute([$title, $issuer, $date, $image]);

    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Create Certificate</title>
</head>
<body>
    <h1>Create New Certificate</h1>
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <p>
            <label>Title:<br>
                <input type="text" name="title" required>
            </label>
        </p>
        <p>
            <label>Issuer:<br>
                <input type="text" name="issuer" required>
            </label>
        </p>
        <p>
            <label>Date (YYYY-MM-DD):<br>
                <input type="date" name="date" required>
            </label>
        </p>
        <p>
            <label>Image:<br>
                <input type="file" name="image" accept="image/*">
            </label>
        </p>
        <button type="submit">Save</button>
    </form>
    <p><a href="index.php">Back</a></p>
</body>
</html>
