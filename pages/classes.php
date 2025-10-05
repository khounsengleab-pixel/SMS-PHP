<?php
require_once '../config/db.php';
// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';

// Handle Add Class
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_class'])) {
    $class_name = trim($_POST['class_name']);
    $room_number = trim($_POST['room_number']);

    if (!empty($class_name)) {
        try {
            $stmt = $pdo->prepare('INSERT INTO classes (class_name, room_number) VALUES (?, ?)');
            $stmt->execute([$class_name, $room_number]);
            $message = 'New class added successfully!';
        } catch (PDOException $e) {
            // Generic error for any database issue
            $error = 'Database Error: Could not add class. ' . $e->getMessage();
        }
    } else {
        $error = "Class name cannot be empty.";
    }
}

// Handle Assign Teacher
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_teacher'])) {
    $class_id = $_POST['class_id'];
    $teacher_id = $_POST['teacher_id'];
    $teacher_id = empty($teacher_id) ? null : $teacher_id; // Allow un-assigning

    try {
        $stmt = $pdo->prepare('UPDATE classes SET teacher_id = ? WHERE id = ?');
        $stmt->execute([$teacher_id, $class_id]);
        $message = 'Teacher successfully assigned!';
    } catch (PDOException $e) {
        $error = 'Error assigning teacher: ' . $e->getMessage();
    }
}


// Fetch all classes and teachers for display
$classes = $pdo->query('
    SELECT c.*, CONCAT(t.first_name, " ", t.last_name) as teacher_name
    FROM classes c
    LEFT JOIN teachers t ON c.teacher_id = t.id
    ORDER BY c.class_name
')->fetchAll();
$teachers = $pdo->query('SELECT * FROM teachers ORDER BY first_name, last_name')->fetchAll();

require_once '../includes/header.php';
?>

<div class="space-y-8">
    <div class="main-content-header pb-4">
        <h1 class="text-3xl font-bold text-gray-800">Manage Classes</h1>
        <p class="text-gray-600 mt-1">Add new classes and assign teachers.</p>
    </div>

    <!-- Feedback Messages -->
    <?php if ($message): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>

    <!-- Add Class Form -->
    <div class="card p-8">
        <h2 class="text-xl font-semibold text-gray-700 mb-6">Add New Class</h2>
        <form action="classes.php" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
            <input type="hidden" name="add_class" value="1">
            <div>
                <label for="class_name" class="block text-sm font-medium text-gray-600 mb-1">Class Name (e.g., PG-A)</label>
                <input type="text" name="class_name" required class="form-input">
            </div>
            <div>
                <label for="room_number" class="block text-sm font-medium text-gray-600 mb-1">Room Number</label>
                <input type="text" name="room_number" class="form-input">
            </div>
            <div>
                <button type="submit" class="btn btn-primary w-full">Add Class</button>
            </div>
        </form>
    </div>

    <!-- Class List Table -->
    <div class="card p-8">
        <h2 class="text-xl font-semibold text-gray-700 mb-6">Class List</h2>
        <div class="overflow-x-auto">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Class Name</th>
                        <th>Room</th>
                        <th>Assigned Teacher</th>
                        <th class="w-1/3">Assign Teacher</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($classes as $class): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($class['class_name']); ?></td>
                        <td><?php echo htmlspecialchars($class['room_number'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($class['teacher_name'] ?? 'Unassigned'); ?></td>
                        <td>
                            <form action="classes.php" method="POST" class="flex items-center space-x-2">
                                <input type="hidden" name="assign_teacher" value="1">
                                <input type="hidden" name="class_id" value="<?php echo $class['id']; ?>">
                                <select name="teacher_id" class="form-input w-full">
                                    <option value="">-- Unassigned --</option>
                                    <?php foreach ($teachers as $teacher): ?>
                                    <option value="<?php echo $teacher['id']; ?>" <?php if($class['teacher_id'] == $teacher['id']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-primary text-sm !py-2">Assign</button>
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

