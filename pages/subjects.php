<?php
require_once '../config/db.php';
// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Action: Add a new subject
    if (isset($_POST['add_subject'])) {
        $subject_name = trim($_POST['subject_name']);
        $subject_code = trim($_POST['subject_code']);

        if (empty($subject_name)) {
            $error = 'Subject name is required.';
        } else {
            try {
                $stmt = $pdo->prepare('INSERT INTO subjects (subject_name, subject_code) VALUES (?, ?)');
                $stmt->execute([$subject_name, $subject_code]);
                $success = "Subject '{$subject_name}' has been added.";
            } catch (PDOException $e) {
                if ($e->errorInfo[1] == 1062) { // Duplicate entry error code
                    $error = 'A subject with this name or code already exists.';
                } else {
                    $error = 'Database error: Could not add subject.';
                }
            }
        }
    }

    // Action: Delete a subject
    if (isset($_POST['delete_subject'])) {
        $subject_id = $_POST['subject_id'];
        try {
            $stmt = $pdo->prepare('DELETE FROM subjects WHERE id = ?');
            $stmt->execute([$subject_id]);
            $success = 'Subject has been deleted successfully.';
        } catch (PDOException $e) {
            $error = 'Database error: Could not delete subject. It might be linked to an exam.';
        }
    }
}

// Fetch all subjects for display
$subjects = $pdo->query('SELECT * FROM subjects ORDER BY subject_name')->fetchAll();

require_once '../includes/header.php';
?>

<div class="space-y-8">
    <h1 class="text-3xl font-bold text-gray-800">Manage Subjects</h1>

    <!-- Display Feedback Messages -->
    <?php if ($success): ?><div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md" role="alert"><p><?php echo $success; ?></p></div><?php endif; ?>
    <?php if ($error): ?><div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md" role="alert"><p><?php echo $error; ?></p></div><?php endif; ?>

    <!-- "Add New Subject" Form -->
    <div class="bg-white p-6 rounded-xl shadow-md">
         <h2 class="text-xl font-semibold text-gray-700 mb-4">Add New Subject</h2>
         <form action="subjects.php" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div>
                <label for="subject_name" class="block text-sm font-medium text-gray-600">Subject Name (e.g., Mathematics)</label>
                <input type="text" name="subject_name" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label for="subject_code" class="block text-sm font-medium text-gray-600">Subject Code (e.g., MATH101)</label>
                <input type="text" name="subject_code" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                 <button type="submit" name="add_subject" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-lg transition duration-200">Add Subject</button>
            </div>
         </form>
    </div>

    <!-- Table Listing All Subjects -->
    <div class="bg-white p-6 rounded-xl shadow-md">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Subject List</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase">Subject Name</th>
                        <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase">Subject Code</th>
                        <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <?php foreach ($subjects as $subject): ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="py-3 px-4 font-medium"><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($subject['subject_code']); ?></td>
                            <td class="py-3 px-4">
                                <form action="subjects.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this subject?');">
                                    <input type="hidden" name="subject_id" value="<?php echo $subject['id']; ?>">
                                    <button type="submit" name="delete_subject" class="text-red-500 hover:text-red-700 font-semibold">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

