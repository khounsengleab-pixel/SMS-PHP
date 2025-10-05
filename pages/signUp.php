<?php
require_once '../config/db.php';
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    $role = $_POST['role'];

    // --- Validation ---
    if (empty($username) || empty($email) || empty($password) || empty($role)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif ($password !== $password_confirm) {
        $error = 'Passwords do not match.';
    } elseif (!in_array($role, ['student', 'teacher'])) {
        $error = 'Invalid role selected.';
    } else {
        try {
            // Check if username or email already exists
            $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                $error = 'Username or email already exists.';
            } else {
                // Hash the password for security
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                // Insert the new user into the database
                $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)');
                $stmt->execute([$username, $email, $password_hash, $role]);
                
                $success = 'Registration successful! You can now <a href="login.php" class="font-bold text-indigo-600 hover:underline">log in</a>.';
            }
        } catch (PDOException $e) {
            // $error = 'Database error: Could not register user.';
            $error = 'Database error: ' . $e->getMessage(); // More detailed error for debugging
        }
    }
}

require_once '../includes/header.php';
?>

<div class="max-w-md mx-auto bg-white p-8 rounded-xl shadow-md">
    <h1 class="text-3xl font-bold text-gray-800 mb-6 text-center">Create an Account</h1>

    <!-- Display Feedback Messages -->
    <?php if ($success): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md mb-6" role="alert">
            <p><?php echo $success; ?></p>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md mb-6" role="alert">
            <p><?php echo $error; ?></p>
        </div>
    <?php endif; ?>

    <?php if (!$success): // Hide form after successful registration ?>
    <form action="signup.php" method="POST" class="space-y-6">
        <div>
            <label for="username" class="block text-sm font-medium text-gray-600">Username</label>
            <input type="text" name="username" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div>
            <label for="email" class="block text-sm font-medium text-gray-600">Email Address</label>
            <input type="email" name="email" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div>
            <label for="password" class="block text-sm font-medium text-gray-600">Password</label>
            <input type="password" name="password" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div>
            <label for="password_confirm" class="block text-sm font-medium text-gray-600">Confirm Password</label>
            <input type="password" name="password_confirm" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div>
             <label for="role" class="block text-sm font-medium text-gray-600">I am a...</label>
             <select name="role" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                <option value="" disabled selected>-- Select a Role --</option>
                <option value="student">Student</option>
                <option value="teacher">Teacher</option>
             </select>
        </div>
        <div>
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200">Sign Up</button>
        </div>
        <div class="text-center text-sm">
            <p class="text-gray-600">Already have an account? <a href="login.php" class="font-medium text-indigo-600 hover:underline">Log in here</a>.</p>
        </div>
    </form>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
