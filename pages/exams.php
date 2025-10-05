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
    // Action: Schedule a new exam
    if (isset($_POST['schedule_exam'])) {
        $exam_name = trim($_POST['exam_name']);
        $class_id = $_POST['class_id'];
        $subject_id = $_POST['subject_id'];
        $exam_date = $_POST['exam_date'];
        $max_marks = $_POST['max_marks'];

        if (empty($exam_name) || empty($class_id) || empty($subject_id) || empty($exam_date)) {
            $error = 'Please fill all required fields to schedule the exam.';
        } else {
            try {
                $stmt = $pdo->prepare('INSERT INTO exams (exam_name, class_id, subject_id, exam_date, max_marks) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$exam_name, $class_id, $subject_id, $exam_date, $max_marks]);
                $success = "Exam '{$exam_name}' has been scheduled successfully.";
            } catch (PDOException $e) {
                $error = 'Database error: Could not schedule the exam.';
            }
        }
    }

    // Action: Delete an exam
    if (isset($_POST['delete_exam'])) {
        $exam_id = $_POST['exam_id'];
        try {
            $stmt = $pdo->prepare('DELETE FROM exams WHERE id = ?');
            $stmt->execute([$exam_id]);
            $success = 'Exam has been deleted successfully.';
        } catch (PDOException $e) {
            $error = 'Database error: Could not delete the exam. Marks may be associated with it.';
        }
    }
}

// --- Fetch data for display ---
$classes = $pdo->query('SELECT id, class_name FROM classes ORDER BY class_name')->fetchAll();
$subjects = $pdo->query('SELECT id, subject_name FROM subjects ORDER BY subject_name')->fetchAll();
$exams = $pdo->query('
    SELECT exams.*, classes.class_name, subjects.subject_name 
    FROM exams 
    JOIN classes ON exams.class_id = classes.id 
    JOIN subjects ON exams.subject_id = subjects.id
    ORDER BY exams.exam_date DESC
')->fetchAll();

require_once '../includes/header.php';
?>

<div class="space-y-8">
    <h1 class="text-3xl font-bold text-gray-800">Schedule Exams</h1>

    <!-- Display Feedback Messages -->
    <?php if ($success): ?><div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md" role="alert"><p><?php echo $success; ?></p></div><?php endif; ?>
    <?php if ($error): ?><div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md" role="alert"><p><?php echo $error; ?></p></div><?php endif; ?>

    <!-- "Schedule New Exam" Form -->
    <div class="bg-white p-6 rounded-xl shadow-md">
         <h2 class="text-xl font-semibold text-gray-700 mb-4">Schedule New Exam</h2>
         <form action="exams.php" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div>
                <label for="exam_name" class="block text-sm font-medium text-gray-600">Exam Name (e.g., Mid-Term)</label>
                <input type="text" name="exam_name" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
            </div>
            <div>
                <label for="class_id" class="block text-sm font-medium text-gray-600">Class</label>
                <select name="class_id" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"><option value="">-- Select Class --</option><?php foreach($classes as $c): ?><option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['class_name']); ?></option><?php endforeach; ?></select>
            </div>
            <div>
                <label for="subject_id" class="block text-sm font-medium text-gray-600">Subject</label>
                <select name="subject_id" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"><option value="">-- Select Subject --</option><?php foreach($subjects as $s): ?><option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['subject_name']); ?></option><?php endforeach; ?></select>
            </div>
            <div>
                <label for="exam_date" class="block text-sm font-medium text-gray-600">Date of Exam</label>
                <input type="date" name="exam_date" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
            </div>
             <div>
                <label for="max_marks" class="block text-sm font-medium text-gray-600">Max Marks</label>
                <input type="number" name="max_marks" value="100" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
            </div>
            <div>
                 <button type="submit" name="schedule_exam" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-lg">Schedule Exam</button>
            </div>
         </form>
    </div>

    <!-- Table Listing Scheduled Exams -->
    <div class="bg-white p-6 rounded-xl shadow-md">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Scheduled Exams List</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase">Date</th>
                        <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase">Exam</th>
                        <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase">Class</th>
                        <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase">Subject</th>
                        <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <?php foreach ($exams as $exam): ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="py-3 px-4"><?php echo date('M j, Y', strtotime($exam['exam_date'])); ?></td>
                            <td class="py-3 px-4 font-medium"><?php echo htmlspecialchars($exam['exam_name']); ?></td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($exam['class_name']); ?></td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($exam['subject_name']); ?></td>
                            <td class="py-3 px-4">
                                <form action="exams.php" method="POST" onsubmit="return confirm('Are you sure? This will delete the exam and any associated marks.');">
                                    <input type="hidden" name="exam_id" value="<?php echo $exam['id']; ?>">
                                    <button type="submit" name="delete_exam" class="text-red-500 hover:text-red-700 font-semibold">Delete</button>
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

