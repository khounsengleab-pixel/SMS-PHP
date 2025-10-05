<?php
require_once '../config/db.php';
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

// --- Initialize Variables ---
$error = '';
$success = '';
$editing_student = null; // This will hold student data when in edit mode

// --- Handle POST Requests (Add, Update, Delete) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Action: ADD a new student
    if (isset($_POST['add_student'])) {
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $dob = $_POST['date_of_birth'];
        $admission_date = $_POST['admission_date'];
        $class_id = !empty($_POST['class_id']) ? $_POST['class_id'] : null;

        if (empty($first_name) || empty($last_name) || empty($admission_date)) {
            $error = 'First name, last name, and admission date are mandatory.';
        } else {
            try {
                $stmt = $pdo->prepare('INSERT INTO students (first_name, last_name, date_of_birth, class_id, admission_date) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$first_name, $last_name, $dob, $class_id, $admission_date]);
                $success = 'Student has been added successfully!';
            } catch (PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }

    // Action: UPDATE an existing student
    if (isset($_POST['update_student'])) {
        $student_id = $_POST['student_id'];
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $dob = $_POST['date_of_birth'];
        $admission_date = $_POST['admission_date'];
        $class_id = !empty($_POST['class_id']) ? $_POST['class_id'] : null;
        
        if (empty($first_name) || empty($last_name) || empty($admission_date)) {
            $error = 'First name, last name, and admission date are mandatory.';
        } else {
            try {
                $stmt = $pdo->prepare('UPDATE students SET first_name = ?, last_name = ?, date_of_birth = ?, class_id = ?, admission_date = ? WHERE id = ?');
                $stmt->execute([$first_name, $last_name, $dob, $class_id, $admission_date, $student_id]);
                $success = 'Student details have been updated successfully!';
            } catch (PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }

    // Action: DELETE a student
    if (isset($_POST['delete_student'])) {
        $student_id = $_POST['student_id'];
        try {
            $stmt = $pdo->prepare('DELETE FROM students WHERE id = ?');
            $stmt->execute([$student_id]);
            $success = 'Student has been deleted successfully!';
        } catch (PDOException $e) {
            $error = 'Error deleting student.';
        }
    }
}

// --- Handle GET Request for Editing ---
// If 'edit' is in the URL, fetch that student's data to pre-fill the form
if (isset($_GET['edit'])) {
    $student_id_to_edit = $_GET['edit'];
    $stmt = $pdo->prepare('SELECT * FROM students WHERE id = ?');
    $stmt->execute([$student_id_to_edit]);
    $editing_student = $stmt->fetch();
}

// --- Fetch Data for Display ---
$classes = $pdo->query('SELECT id, class_name FROM classes ORDER BY class_name')->fetchAll();
$students = $pdo->query("
    SELECT s.id, s.first_name, s.last_name, s.admission_date, c.class_name
    FROM students s
    LEFT JOIN classes c ON s.class_id = c.id
    ORDER BY s.last_name, s.first_name
")->fetchAll();

require_once '../includes/header.php';
?>

<div class="space-y-8">
    <h1 class="text-3xl font-bold text-gray-800">Manage Students</h1>

    <?php if ($success): ?><div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md" role="alert"><p><?php echo $success; ?></p></div><?php endif; ?>
    <?php if ($error): ?><div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md" role="alert"><p><?php echo $error; ?></p></div><?php endif; ?>

    <!-- Add/Edit Student Form -->
    <div class="bg-white p-6 rounded-xl shadow-md">
        <h2 class="text-xl font-semibold text-gray-700 mb-4"><?php echo $editing_student ? 'Edit Student Details' : 'Add New Student'; ?></h2>
        
        <form action="students.php" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php if ($editing_student): ?>
                <input type="hidden" name="student_id" value="<?php echo $editing_student['id']; ?>">
            <?php endif; ?>
            
            <div>
                <label for="first_name" class="block text-sm font-medium text-gray-600">First Name</label>
                <input type="text" name="first_name" required class="mt-1 block w-full input-style" value="<?php echo htmlspecialchars($editing_student['first_name'] ?? ''); ?>">
            </div>
            <div>
                <label for="last_name" class="block text-sm font-medium text-gray-600">Last Name</label>
                <input type="text" name="last_name" required class="mt-1 block w-full input-style" value="<?php echo htmlspecialchars($editing_student['last_name'] ?? ''); ?>">
            </div>
             <div>
                <label for="class_id" class="block text-sm font-medium text-gray-600">Assign to Class</label>
                <select name="class_id" class="mt-1 block w-full input-style">
                    <option value="">-- Select a Class --</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?php echo $class['id']; ?>" <?php echo (isset($editing_student['class_id']) && $editing_student['class_id'] == $class['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($class['class_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="date_of_birth" class="block text-sm font-medium text-gray-600">Date of Birth</label>
                <input type="date" name="date_of_birth" class="mt-1 block w-full input-style" value="<?php echo htmlspecialchars($editing_student['date_of_birth'] ?? ''); ?>">
            </div>
             <div>
                <label for="admission_date" class="block text-sm font-medium text-gray-600">Admission Date</label>
                <input type="date" name="admission_date" required class="mt-1 block w-full input-style" value="<?php echo htmlspecialchars($editing_student['admission_date'] ?? ''); ?>">
            </div>
            <div class="md:col-span-3 flex justify-end items-center gap-4">
                 <?php if ($editing_student): ?>
                    <a href="students.php" class="text-gray-600 hover:text-indigo-600 font-medium">Cancel Edit</a>
                    <button type="submit" name="update_student" class="btn-primary">Update Student</button>
                <?php else: ?>
                    <button type="submit" name="add_student" class="btn-primary">Add Student</button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Student List Table -->
    <div class="bg-white p-6 rounded-xl shadow-md">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Student List</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="th-style">Name</th>
                        <th class="th-style">Class</th>
                        <th class="th-style">Admission Date</th>
                        <th class="th-style">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <?php if (empty($students)): ?>
                        <tr><td colspan="4" class="py-4 px-4 text-center">No students found. Add one using the form above.</td></tr>
                    <?php else: ?>
                        <?php foreach ($students as $student): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="td-style"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                <td class="td-style"><?php echo htmlspecialchars($student['class_name'] ?? 'N/A'); ?></td>
                                <td class="td-style"><?php echo date('F j, Y', strtotime($student['admission_date'])); ?></td>
                                <td class="td-style space-x-4">
                                    <a href="students.php?edit=<?php echo $student['id']; ?>" class="text-indigo-600 hover:text-indigo-800 font-semibold">Edit</a>
                                    <form action="students.php" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this student? This cannot be undone.');">
                                        <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
                                        <button type="submit" name="delete_student" class="text-red-500 hover:text-red-700 font-semibold">Delete</button>
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

<style>
    .input-style {
        padding: 0.5rem 0.75rem;
        border: 1px solid #D1D5DB;
        border-radius: 0.375rem;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }
    .input-style:focus {
        outline: none;
        box-shadow: 0 0 0 2px #C7D2FE;
        border-color: #6366F1;
    }
    .btn-primary {
        background-color: #4F46E5;
        color: white;
        font-weight: bold;
        padding: 0.5rem 1.5rem;
        border-radius: 0.375rem;
        transition: background-color 0.2s;
    }
    .btn-primary:hover {
        background-color: #4338CA;
    }
    .th-style {
        padding: 0.75rem 1rem;
        text-align: left;
        font-size: 0.75rem;
        font-weight: 600;
        color: #4B5563;
        text-transform: uppercase;
    }
    .td-style {
        padding: 0.75rem 1rem;
    }
</style>

<?php require_once '../includes/footer.php'; ?>

