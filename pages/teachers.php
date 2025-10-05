<?php
require_once '../config/db.php';
// Ensure user is logged in, otherwise redirect to the login page
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// --- Initialize variables for user feedback ---
$error = '';
$success = '';

// --- Handle POST requests (for adding or deleting a teacher) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ACTION: Add a new teacher
    if (isset($_POST['add_teacher'])) {
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $specialization = trim($_POST['specialization']);
        $hire_date = $_POST['hire_date'];

        // Basic validation
        if (empty($first_name) || empty($last_name) || empty($hire_date)) {
            $error = 'First name, last name, and hire date are required fields.';
        } else {
            try {
                // Prepare and execute the INSERT statement to prevent SQL injection
                $stmt = $pdo->prepare('INSERT INTO teachers (first_name, last_name, subject_specialization, hire_date) VALUES (?, ?, ?, ?)');
                $stmt->execute([$first_name, $last_name, $specialization, $hire_date]);
                $success = 'New teacher has been added successfully!';
            } catch (PDOException $e) {
                $error = 'Database error: Could not add the teacher.';
            }
        }
    }

    // ACTION: Delete a teacher
    if (isset($_POST['delete_teacher'])) {
        $teacher_id = $_POST['teacher_id'];
        try {
            // Prepare and execute the DELETE statement
            $stmt = $pdo->prepare('DELETE FROM teachers WHERE id = ?');
            $stmt->execute([$teacher_id]);
            $success = 'Teacher has been deleted successfully!';
        } catch (PDOException $e) {
            $error = 'Database error: Could not delete the teacher.';
        }
    }
}

// --- Fetch all teachers from the database to display in the table ---
$teachers = $pdo->query('SELECT * FROM teachers ORDER BY last_name, first_name')->fetchAll();

// --- Include the website header ---
require_once '../includes/header.php';
?>

<div class="space-y-8">
    <h1 class="text-3xl font-bold text-gray-800">Manage Teachers</h1>

    <!-- Display Success/Error Messages -->
    <?php if ($success): ?><div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md" role="alert"><p><?php echo $success; ?></p></div><?php endif; ?>
    <?php if ($error): ?><div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md" role="alert"><p><?php echo $error; ?></p></div><?php endif; ?>

    <!-- "Add New Teacher" Form -->
    <div class="bg-white p-6 rounded-xl shadow-md">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Add New Teacher</h2>
        <form action="teachers.php" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <label for="first_name" class="block text-sm font-medium text-gray-600">First Name</label>
                <input type="text" name="first_name" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label for="last_name" class="block text-sm font-medium text-gray-600">Last Name</label>
                <input type="text" name="last_name" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label for="specialization" class="block text-sm font-medium text-gray-600">Specialization</label>
                <input type="text" name="specialization" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
             <div>
                <label for="hire_date" class="block text-sm font-medium text-gray-600">Hire Date</label>
                <input type="date" name="hire_date" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="md:col-span-4 flex justify-end">
                <button type="submit" name="add_teacher" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-lg transition duration-200">Add Teacher</button>
            </div>
        </form>
    </div>

    <!-- Table Listing All Teachers -->
    <div class="bg-white p-6 rounded-xl shadow-md">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Teacher List</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase">Name</th>
                        <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase">Specialization</th>
                        <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase">Hire Date</th>
                        <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <?php if (empty($teachers)): ?>
                        <tr><td colspan="4" class="py-4 px-4 text-center">No teachers found. Use the form above to add one.</td></tr>
                    <?php else: ?>
                        <?php foreach ($teachers as $teacher): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="py-3 px-4"><?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?></td>
                                <td class="py-3 px-4"><?php echo htmlspecialchars($teacher['subject_specialization']); ?></td>
                                <td class="py-3 px-4"><?php echo date('F j, Y', strtotime($teacher['hire_date'])); ?></td>
                                <td class="py-3 px-4">
                                    <form action="teachers.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this teacher? This action cannot be undone.');">
                                        <input type="hidden" name="teacher_id" value="<?php echo $teacher['id']; ?>">
                                        <button type="submit" name="delete_teacher" class="text-red-500 hover:text-red-700 font-semibold">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

