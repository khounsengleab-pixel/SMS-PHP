<?php
require_once '../config/db.php';
// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$report_data = [];
$student_details = null;
$selected_class_id = $_GET['class_id'] ?? null;
$selected_student_id = $_GET['student_id'] ?? null;

// Fetch data for dropdowns
$classes = $pdo->query('SELECT id, class_name FROM classes ORDER BY class_name')->fetchAll();
$students = [];
if ($selected_class_id) {
    $stmt = $pdo->prepare('SELECT id, first_name, last_name FROM students WHERE class_id = ? ORDER BY last_name');
    $stmt->execute([$selected_class_id]);
    $students = $stmt->fetchAll();
}

// If a student is selected, generate the report
if ($selected_student_id) {
    // Get student details
    $stmt_student = $pdo->prepare('
        SELECT s.first_name, s.last_name, c.class_name 
        FROM students s 
        JOIN classes c ON s.class_id = c.id 
        WHERE s.id = ?
    ');
    $stmt_student->execute([$selected_student_id]);
    $student_details = $stmt_student->fetch();

    // Get marks details
    $stmt_marks = $pdo->prepare('
        SELECT 
            m.marks_obtained, 
            e.exam_name, 
            e.max_marks, 
            sub.subject_name
        FROM marks m
        JOIN exams e ON m.exam_id = e.id
        JOIN subjects sub ON e.subject_id = sub.id
        WHERE m.student_id = ?
        ORDER BY e.exam_name, sub.subject_name
    ');
    $stmt_marks->execute([$selected_student_id]);
    $report_data = $stmt_marks->fetchAll();
}

require_once '../includes/header.php';
?>

<div class="space-y-8">
    <h1 class="text-3xl font-bold text-gray-800">Generate Student Reports</h1>

    <!-- Selection Form -->
    <div class="bg-white p-6 rounded-xl shadow-md">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Select Student</h2>
        <form action="reports.php" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div>
                <label for="class_id" class="block text-sm font-medium text-gray-600">First, Select Class</label>
                <select name="class_id" onchange="this.form.submit()" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                    <option value="">-- Select Class --</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?php echo $c['id']; ?>" <?php if ($c['id'] == $selected_class_id) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($c['class_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($selected_class_id): ?>
            <div>
                <label for="student_id" class="block text-sm font-medium text-gray-600">Now, Select Student</label>
                <select name="student_id" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                    <option value="">-- Select Student --</option>
                    <?php foreach ($students as $s): ?>
                        <option value="<?php echo $s['id']; ?>" <?php if ($s['id'] == $selected_student_id) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($s['first_name'] . ' ' . $s['last_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-lg">Generate Report</button>
            </div>
            <?php endif; ?>
        </form>
    </div>

    <!-- Report Card Display -->
    <?php if ($student_details && !empty($report_data)): ?>
    <div class="bg-white p-8 rounded-xl shadow-lg" id="report-card">
        <div class="border-b-2 border-gray-200 pb-4 mb-6 text-center">
            <h2 class="text-3xl font-bold text-gray-800">Student Report Card</h2>
            <p class="text-gray-600">Sunshine Academy</p>
        </div>
        <div class="grid grid-cols-2 gap-4 mb-6">
            <p><strong>Student Name:</strong> <?php echo htmlspecialchars($student_details['first_name'] . ' ' . $student_details['last_name']); ?></p>
            <p><strong>Class:</strong> <?php echo htmlspecialchars($student_details['class_name']); ?></p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-300">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-3 px-4 text-left font-semibold text-gray-700">Exam</th>
                        <th class="py-3 px-4 text-left font-semibold text-gray-700">Subject</th>
                        <th class="py-3 px-4 text-center font-semibold text-gray-700">Marks Obtained</th>
                        <th class="py-3 px-4 text-center font-semibold text-gray-700">Max Marks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        $total_obtained = 0;
                        $total_max = 0;
                        foreach ($report_data as $row): 
                        $total_obtained += $row['marks_obtained'];
                        $total_max += $row['max_marks'];
                    ?>
                    <tr class="border-t border-gray-200">
                        <td class="py-3 px-4"><?php echo htmlspecialchars($row['exam_name']); ?></td>
                        <td class="py-3 px-4"><?php echo htmlspecialchars($row['subject_name']); ?></td>
                        <td class="py-3 px-4 text-center"><?php echo htmlspecialchars($row['marks_obtained']); ?></td>
                        <td class="py-3 px-4 text-center"><?php echo htmlspecialchars($row['max_marks']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="bg-gray-100 font-bold">
                     <tr class="border-t-2 border-gray-300">
                        <td class="py-3 px-4 text-right" colspan="2">Total</td>
                        <td class="py-3 px-4 text-center"><?php echo $total_obtained; ?></td>
                        <td class="py-3 px-4 text-center"><?php echo $total_max; ?></td>
                    </tr>
                     <tr>
                        <td class="py-3 px-4 text-right" colspan="3">Overall Percentage</td>
                        <td class="py-3 px-4 text-center text-lg text-green-600"><?php echo $total_max > 0 ? round(($total_obtained / $total_max) * 100, 2) . '%' : 'N/A'; ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="text-center mt-4">
        <button onclick="window.print()" class="bg-gray-700 hover:bg-gray-800 text-white font-bold py-2 px-6 rounded-lg">Print Report</button>
    </div>
    <?php elseif($selected_student_id): ?>
         <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded-md" role="alert">
            <p>No marks have been entered for this student yet. Please go to the <a href="marks.php" class="font-bold underline">Marks page</a> to enter them.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>

