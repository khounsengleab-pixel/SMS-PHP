<?php
require_once '../config/db.php';
// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// --- DATA FETCHING ---

// 1. Basic Stats
$student_count = $pdo->query('SELECT COUNT(*) FROM students')->fetchColumn();
$teacher_count = $pdo->query('SELECT COUNT(*) FROM teachers')->fetchColumn();
$class_count = $pdo->query('SELECT COUNT(*) FROM classes')->fetchColumn();

// 2. Recently Enrolled Students (Last 5)
$recent_students_stmt = $pdo->query('
    SELECT first_name, last_name, admission_date 
    FROM students 
    ORDER BY admission_date DESC 
    LIMIT 5
');
$recent_students = $recent_students_stmt->fetchAll();

// 3. Class Occupancy and Teacher Assignments
$class_overview_stmt = $pdo->query('
    SELECT 
        c.class_name,
        IFNULL(CONCAT(t.first_name, " ", t.last_name), "<span class=\"text-red-500\">Unassigned</span>") as teacher_name,
        COUNT(s.id) as student_count
    FROM classes c
    LEFT JOIN teachers t ON c.teacher_id = t.id
    LEFT JOIN students s ON s.class_id = c.id
    GROUP BY c.id, c.class_name, teacher_name
    ORDER BY c.class_name
');
$class_overview = $class_overview_stmt->fetchAll();

// 4. Upcoming Exams (Next 5)
$upcoming_exams_stmt = $pdo->query('
    SELECT 
        e.exam_name,
        s.subject_name,
        c.class_name,
        e.exam_date
    FROM exams e
    JOIN subjects s ON e.subject_id = s.id
    JOIN classes c ON e.class_id = c.id
    WHERE e.exam_date >= CURDATE()
    ORDER BY e.exam_date ASC
    LIMIT 5
');
$upcoming_exams = $upcoming_exams_stmt->fetchAll();


require_once '../includes/header.php';
?>

<div class="space-y-8">
    <!-- Header -->
    <div class="main-content-header pb-4">
        <h1 class="text-3xl font-bold text-gray-800">Administrator Dashboard</h1>
        <p class="text-gray-600 mt-1">A live overview of your school's data.</p>
    </div>

    <!-- Stats Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="card p-6 flex items-center space-x-4">
            <div class="bg-blue-100 p-3 rounded-full"><svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M15 21v-1a6 6 0 00-5.197-5.93M9 21a6 6 0 00-6-6v-1a6 6 0 006 6z"></path></svg></div>
            <div><p class="text-gray-500 font-medium">Total Students</p><p class="text-3xl font-bold text-gray-800"><?php echo $student_count; ?></p></div>
        </div>
        <div class="card p-6 flex items-center space-x-4">
            <div class="bg-green-100 p-3 rounded-full"><svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg></div>
            <div><p class="text-gray-500 font-medium">Total Teachers</p><p class="text-3xl font-bold text-gray-800"><?php echo $teacher_count; ?></p></div>
        </div>
        <div class="card p-6 flex items-center space-x-4">
            <div class="bg-indigo-100 p-3 rounded-full"><svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg></div>
            <div><p class="text-gray-500 font-medium">Total Classes</p><p class="text-3xl font-bold text-gray-800"><?php echo $class_count; ?></p></div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Class Overview Card -->
        <div class="card p-8">
            <h2 class="text-xl font-semibold text-gray-700 mb-6">Class Overview</h2>
            <div class="overflow-x-auto">
                <table class="custom-table">
                    <thead><tr><th>Class Name</th><th>Assigned Teacher</th><th>Students</th></tr></thead>
                    <tbody>
                        <?php if (empty($class_overview)): ?>
                            <tr><td colspan="3" class="text-center text-gray-500 py-4">No classes found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($class_overview as $class): ?>
                                <tr>
                                    <td class="font-medium"><?php echo htmlspecialchars($class['class_name']); ?></td>
                                    <td><?php echo $class['teacher_name']; // HTML is trusted from DB logic ?></td>
                                    <td class="text-center"><?php echo $class['student_count']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Upcoming Exams Card -->
        <div class="card p-8">
            <h2 class="text-xl font-semibold text-gray-700 mb-6">Upcoming Exams</h2>
            <div class="overflow-x-auto">
                 <table class="custom-table">
                    <thead><tr><th>Date</th><th>Exam</th><th>Class</th><th>Subject</th></tr></thead>
                    <tbody>
                         <?php if (empty($upcoming_exams)): ?>
                            <tr><td colspan="4" class="text-center text-gray-500 py-4">No upcoming exams scheduled.</td></tr>
                        <?php else: ?>
                            <?php foreach ($upcoming_exams as $exam): ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($exam['exam_date'])); ?></td>
                                    <td class="font-medium"><?php echo htmlspecialchars($exam['exam_name']); ?></td>
                                    <td><?php echo htmlspecialchars($exam['class_name']); ?></td>
                                    <td><?php echo htmlspecialchars($exam['subject_name']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Activity Card -->
    <div class="card p-8">
        <h2 class="text-xl font-semibold text-gray-700 mb-6">Recent Activity</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="font-semibold text-gray-600 mb-3">Newly Enrolled Students</h3>
                <ul class="space-y-3">
                    <?php if (empty($recent_students)): ?>
                        <li class="text-gray-500">No new students found.</li>
                    <?php else: ?>
                        <?php foreach ($recent_students as $student): ?>
                            <li class="flex justify-between items-center text-sm">
                                <span><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></span>
                                <span class="text-gray-500"><?php echo date('M d, Y', strtotime($student['admission_date'])); ?></span>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

