<?php
require_once '../config/db.php';
// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';
$user_id = $_SESSION['user_id'];

// Handle form submission for password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // 1. Fetch current password hash from DB
    $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    // 2. Verify current password
    if (!$user || !password_verify($current_password, $user['password_hash'])) {
        $error = 'Your current password is not correct.';
    }
    // 3. Validate new password
    elseif (empty($new_password) || strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters long.';
    }
    // 4. Confirm new password
    elseif ($new_password !== $confirm_password) {
        $error = 'New password and confirmation do not match.';
    }
    // 5. All checks passed, update the password
    else {
        try {
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
            $update_stmt->execute([$new_password_hash, $user_id]);
            $success = 'Your password has been changed successfully!';
        } catch (PDOException $e) {
            $error = 'Database error: Could not update your password.';
        }
    }
}

require_once '../includes/header.php';
?>

<div class="space-y-8 max-w-2xl mx-auto">
    <h1 class="text-3xl font-bold text-gray-800">Settings</h1>

    <!-- Display Feedback Messages -->
    <?php if ($success): ?><div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md" role="alert"><p><?php echo $success; ?></p></div><?php endif; ?>
    <?php if ($error): ?><div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md" role="alert"><p><?php echo $error; ?></p></div><?php endif; ?>

    <!-- Change Password Form -->
    <div class="bg-white p-8 rounded-xl shadow-md">
         <h2 class="text-xl font-semibold text-gray-700 mb-6">Change Your Password</h2>
         <form action="settings.php" method="POST" class="space-y-6">
            <div>
                <label for="current_password" class="block text-sm font-medium text-gray-600">Current Password</label>
                <input type="password" name="current_password" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
             <div>
                <label for="new_password" class="block text-sm font-medium text-gray-600">New Password</label>
                <input type="password" name="new_password" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
             <div>
                <label for="confirm_password" class="block text-sm font-medium text-gray-600">Confirm New Password</label>
                <input type="password" name="confirm_password" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="text-right">
                 <button type="submit" name="change_password" class="inline-flex justify-center py-3 px-8 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Update Password
                 </button>
            </div>
         </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

