<?php
require_once '../config/db.php';
$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $specialization = trim($_POST['specialization']);
    $cover_letter = trim($_POST['cover_letter']);

    // Basic validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($specialization)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please provide a valid email address.';
    } else {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO teacher_applications (first_name, last_name, email, specialization, cover_letter) 
                 VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([$first_name, $last_name, $email, $specialization, $cover_letter]);
            $message = 'Thank you for your interest! Your application has been received and will be reviewed by our team.';
        } catch (PDOException $e) {
            $error = 'Database error: Could not submit application. Please try again later.';
        }
    }
}

require_once '../includes/header.php';
?>

<div class="container mx-auto px-6 py-12">
    <div class="max-w-2xl mx-auto">
        <div class="card p-8">
            <h1 class="text-3xl font-bold text-gray-800 text-center mb-2">Teacher Application</h1>
            <p class="text-gray-600 text-center mb-8">Join our team of dedicated educators. Fill out the form below to apply.</p>

            <!-- Feedback Messages -->
            <?php if ($message): ?>
                <div class="alert alert-success mb-6"><?php echo $message; ?></div>
            <?php else: ?>
                <?php if ($error): ?><div class="alert alert-danger mb-6"><?php echo $error; ?></div><?php endif; ?>

                <form action="join-teachers.php" method="POST" class="space-y-6">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-600 mb-1">First Name</label>
                        <input type="text" name="first_name" required class="form-input">
                    </div>
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-600 mb-1">Last Name</label>
                        <input type="text" name="last_name" required class="form-input">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-600 mb-1">Email Address</label>
                        <input type="email" name="email" required class="form-input">
                    </div>
                    <div>
                        <label for="specialization" class="block text-sm font-medium text-gray-600 mb-1">Subject Specialization</label>
                        <input type="text" name="specialization" required class="form-input" placeholder="e.g., Mathematics, Early Childhood Education">
                    </div>
                    <div>
                        <label for="cover_letter" class="block text-sm font-medium text-gray-600 mb-1">Cover Letter (Optional)</label>
                        <textarea name="cover_letter" rows="4" class="form-input"></textarea>
                    </div>
                    <div class="pt-4">
                        <button type="submit" class="btn btn-primary w-full">Submit Application</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

