<?php
require_once '../config/db.php';
$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $date_of_birth = trim($_POST['date_of_birth']);
    $parent_name = trim($_POST['parent_name']);
    $parent_email = trim($_POST['parent_email']);

    // Basic validation
    if (empty($first_name) || empty($last_name) || empty($date_of_birth) || empty($parent_name) || empty($parent_email)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($parent_email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please provide a valid parent email address.';
    } else {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO student_applications (first_name, last_name, date_of_birth, parent_name, parent_email) 
                 VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([$first_name, $last_name, $date_of_birth, $parent_name, $parent_email]);
            $message = 'Thank you! Your application has been submitted successfully. We will contact you soon.';
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
            <h1 class="text-3xl font-bold text-gray-800 text-center mb-2">Student Enrollment Application</h1>
            <p class="text-gray-600 text-center mb-8">Please fill out the form below to begin the enrollment process.</p>

            <!-- Feedback Messages -->
            <?php if ($message): ?>
                <div class="alert alert-success mb-6"><?php echo $message; ?></div>
            <?php else: ?>
                <?php if ($error): ?><div class="alert alert-danger mb-6"><?php echo $error; ?></div><?php endif; ?>

                <form action="enroll-student.php" method="POST" class="space-y-6">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-600 mb-1">Student's First Name</label>
                        <input type="text" name="first_name" required class="form-input">
                    </div>
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-600 mb-1">Student's Last Name</label>
                        <input type="text" name="last_name" required class="form-input">
                    </div>
                    <div>
                        <label for="date_of_birth" class="block text-sm font-medium text-gray-600 mb-1">Date of Birth</label>
                        <input type="date" name="date_of_birth" required class="form-input">
                    </div>
                    <hr class="my-6">
                    <div>
                        <label for="parent_name" class="block text-sm font-medium text-gray-600 mb-1">Parent/Guardian Full Name</label>
                        <input type="text" name="parent_name" required class="form-input">
                    </div>
                    <div>
                        <label for="parent_email" class="block text-sm font-medium text-gray-600 mb-1">Parent/Guardian Email</label>
                        <input type="email" name="parent_email" required class="form-input">
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

