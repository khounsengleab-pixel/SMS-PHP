<?php
// Start the session if it's not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMS DUC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <!-- New Print-Specific Styles -->
    <style>
        @media print {
            /* Hide all elements that should not be printed */
            .sidebar, .print-hide {
                display: none !important;
            }

            /* Ensure the main content area has no padding when printing */
            .main-content-area {
                padding: 0 !important;
            }

            /* Make the report card the only visible element and reset its styles for paper */
            #report-card {
                visibility: visible !important;
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                margin: 0;
                padding: 1rem;
                border: none !important;
                box-shadow: none !important;
            }

            /* Ensure everything inside the report card is also visible */
            #report-card * {
                 visibility: visible !important;
            }
        }
    </style>
</head>
<body class="bg-gray-100">

    <div class="flex h-screen">
        <?php if (isset($_SESSION['user_id'])): ?>
        <!-- Sidebar -->
        <aside class="sidebar w-64 bg-gray-800 text-white flex-shrink-0">
            <div class="p-6">
                <h2 class="text-2xl font-bold">SMS DUC</h2>
                <img src="" alt="">
            </div>
            <nav>
                <a href="dashboard.php" class="block py-3 px-6 hover:bg-gray-700 <?php echo $current_page == 'dashboard.php' ? 'bg-gray-900' : ''; ?>">Dashboard</a>
                <a href="students.php" class="block py-3 px-6 hover:bg-gray-700 <?php echo $current_page == 'students.php' ? 'bg-gray-900' : ''; ?>">Students</a>
                <a href="teachers.php" class="block py-3 px-6 hover:bg-gray-700 <?php echo $current_page == 'teachers.php' ? 'bg-gray-900' : ''; ?>">Teachers</a>
                <a href="classes.php" class="block py-3 px-6 hover:bg-gray-700 <?php echo $current_page == 'classes.php' ? 'bg-gray-900' : ''; ?>">Classes</a>
                <a href="subjects.php" class="block py-3 px-6 hover:bg-gray-700 <?php echo $current_page == 'subjects.php' ? 'bg-gray-900' : ''; ?>">Subjects</a>
                <a href="exams.php" class="block py-3 px-6 hover:bg-gray-700 <?php echo $current_page == 'exams.php' ? 'bg-gray-900' : ''; ?>">Exams</a>
                <a href="marks.php" class="block py-3 px-6 hover:bg-gray-700 <?php echo $current_page == 'marks.php' ? 'bg-gray-900' : ''; ?>">Marks</a>
                <a href="reports.php" class="block py-3 px-6 hover:bg-gray-700 <?php echo $current_page == 'reports.php' ? 'bg-gray-900' : ''; ?>">Reports</a>
                <a href="settings.php" class="block py-3 px-6 hover:bg-gray-700 <?php echo $current_page == 'settings.php' ? 'bg-gray-900' : ''; ?>">Settings</a>
                
                <a href="logout.php" class="block py-3 px-6 hover:bg-gray-700">Logout</a>
            </nav>
        </aside>
        <?php endif; ?>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <main class="flex-1 overflow-x-hidden overflow-y-auto main-content-area p-8">

