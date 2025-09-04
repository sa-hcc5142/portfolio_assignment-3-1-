<?php
// XAMPP/htdocs/portfolio2/Admin/certificates/index.php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../../public/login.php");
    exit;
}

require_once dirname(__DIR__, 2) . '/app/config.php';
require_admin();

// Fetch certificates
$stmt = $pdo->query("SELECT * FROM certificates ORDER BY id DESC");
$certificates = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Generate CSRF token if not already set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Certificates - Admin</title>
</head>
<body>
    <h1>Certificates</h1>
    <a href="create.php">+ Add New Certificate</a>
    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Issuer</th>
                <th>Date</th>
                <th>Image</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($certificates as $row): ?>
                <tr>
                    <td><?php echo (int)$row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['issuer'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['date'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <?php if (!empty($row['image'])): ?>
                            <img src="../../uploads/<?php echo htmlspecialchars($row['image'], ENT_QUOTES, 'UTF-8'); ?>" width="100" alt="certificate image">
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="edit.php?id=<?php echo (int)$row['id']; ?>">Edit</a> |
                        <form method="post" action="delete.php" style="display:inline" onsubmit="return confirm('Delete this certificate?');">
                            <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <button type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
