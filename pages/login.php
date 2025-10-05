<?php
require_once '../config/db.php';
$error_message = '';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error_message = 'Please enter both username and password.';
    } else {
        try {
            $stmt = $pdo->prepare('SELECT id, username, password_hash, role FROM users WHERE username = ?');
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                // Password is correct, start session
                session_regenerate_id();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                header('Location: dashboard.php');
                exit;
            } else {
                $error_message = 'Invalid username or password.';
            }
        } catch (PDOException $e) {
            $error_message = 'An error occurred during login.';
        }
    }
}

require_once '../includes/header.php';
?>

<div class="max-w-md mx-auto bg-white p-8 rounded-xl shadow-md">
    <h1 class="text-3xl font-bold text-gray-800 mb-6 text-center">Portal Login</h1>

    <?php if ($error_message): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md mb-6" role="alert">
            <p><?php echo $error_message; ?></p>
        </div>
    <?php endif; ?>

    <form action="login.php" method="POST" class="space-y-6">
        <div>
            <label for="username" class="block text-sm font-medium text-gray-600">Username</label>
            <input type="text" name="username" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div>
            <label for="password" class="block text-sm font-medium text-gray-600">Password</label>
            <input type="password" name="password" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div>
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200">Log In</button>
        </div>
        <div class="text-center text-sm">
            <p class="text-gray-600">Don't have an account? <a href="signup.php" class="font-medium text-indigo-600 hover:underline">Sign up now</a>.</p>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>

