<?php
require_once '../config/db.php';
// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';
$students = [];
$selected_class_id = $_GET['class_id'] ?? null;
$selected_exam_id = $_GET['exam_id'] ?? null;

// Handle saving marks
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_marks'])) {
    $marks_data = $_POST['marks'];
    $exam_id = $_POST['exam_id'];

    try {
        $pdo->beginTransaction();
        // Use INSERT ... ON DUPLICATE KEY UPDATE to either insert new marks or update existing ones
        // This is very efficient for this task.
        $stmt = $pdo->prepare('
            INSERT INTO marks (student_id, exam_id, marks_obtained) 
            VALUES (:student_id, :exam_id, :marks)
            ON DUPLICATE KEY UPDATE marks_obtained = VALUES(marks_obtained)
        ');

        foreach ($marks_data as $student_id => $marks) {
            // Only save if marks are entered and are numeric
            if ($marks !== '' && is_numeric($marks)) { 
                $stmt->execute(['student_id' => $student_id, 'exam_id' => $exam_id, 'marks' => $marks]);
            }
        }
        $pdo->commit();
        $success = 'Marks have been saved successfully!';
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = 'Database error: Could not save marks. ' . $e->getMessage();
    }
}

// Fetch data for dropdowns
$classes = $pdo->query('SELECT id, class_name FROM classes ORDER BY class_name')->fetchAll();
$exams = $pdo->query('
    SELECT exams.id, exams.exam_name, classes.class_name 
    FROM exams 
    JOIN classes ON exams.class_id = classes.id 
    ORDER BY classes.class_name, exams.exam_name
')->fetchAll();

// If a class and exam are selected, fetch the students and their marks for display
if ($selected_class_id && $selected_exam_id) {
    $stmt = $pdo->prepare('
        SELECT s.id, s.first_name, s.last_name, m.marks_obtained
        FROM students s
        LEFT JOIN marks m ON s.id = m.student_id AND m.exam_id = ?
        WHERE s.class_id = ?
        ORDER BY s.last_name, s.first_name
    ');
    $stmt->execute([$selected_exam_id, $selected_class_id]);
    $students = $stmt->fetchAll();
}

require_once '../includes/header.php';
?>

<div class="space-y-8">
    <h1 class="text-3xl font-bold text-gray-800">Enter & Manage Marks</h1>

    <!-- Display Feedback Messages -->
    <?php if ($success): ?><div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md" role="alert"><p><?php echo $success; ?></p></div><?php endif; ?>
    <?php if ($error): ?><div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md" role="alert"><p><?php echo $error; ?></p></div><?php endif; ?>

    <!-- Selection Form to choose class and exam -->
    <div class="bg-white p-6 rounded-xl shadow-md">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Select Class and Exam</h2>
        <form action="marks.php" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div>
                <label for="class_id" class="block text-sm font-medium text-gray-600">Class</label>
                <select name="class_id" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"><option value="">-- Select Class --</option><?php foreach($classes as $c): ?><option value="<?php echo $c['id']; ?>" <?php if($c['id']==$selected_class_id) echo 'selected'; ?>><?php echo htmlspecialchars($c['class_name']); ?></option><?php endforeach; ?></select>
            </div>
            <div>
                <label for="exam_id" class="block text-sm font-medium text-gray-600">Exam</label>
                <select name="exam_id" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"><option value="">-- Select Exam --</option><?php foreach($exams as $e): ?><option value="<?php echo $e['id']; ?>" <?php if($e['id']==$selected_exam_id) echo 'selected'; ?>><?php echo htmlspecialchars($e['class_name'] . ' - ' . $e['exam_name']); ?></option><?php endforeach; ?></select>
            </div>
            <div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-lg transition duration-200">Load Students</button>
            </div>
        </form>
    </div>

    <!-- Marks Entry Form, only shows after selection -->
    <?php if ($selected_class_id && $selected_exam_id && !empty($students)): ?>
    <div class="bg-white p-6 rounded-xl shadow-md">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Enter Marks for Students</h2>
        <form action="marks.php?class_id=<?php echo $selected_class_id; ?>&exam_id=<?php echo $selected_exam_id; ?>" method="POST">
            <input type="hidden" name="exam_id" value="<?php echo $selected_exam_id; ?>">
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase">Student Name</th>
                            <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase">Marks Obtained</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        <?php foreach ($students as $student): ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="py-3 px-4 font-medium"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                            <td class="py-3 px-4">
                                <input type="number" name="marks[<?php echo $student['id']; ?>]" value="<?php echo htmlspecialchars($student['marks_obtained']); ?>" class="w-full max-w-xs px-2 py-1 border border-gray-300 rounded-md shadow-sm" placeholder="Enter marks">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="text-right mt-6">
                <button type="submit" name="save_marks" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-lg transition duration-200">Save All Marks</button>
            </div>
        </form>
    </div>
    <?php elseif ($selected_class_id && $selected_exam_id): ?>
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded-md" role="alert">
            <p>No students found for the selected class. Please go to the <a href="students.php" class="font-bold underline">Students page</a> to add students to this class first.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>

