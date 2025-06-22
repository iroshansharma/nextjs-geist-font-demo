<?php
require_once '../backend/auth.php';

// Check if user is logged in and has appropriate role
if (!is_logged_in() || !in_array($_SESSION['role_id'], [1, 2])) {
    header('Location: index.php');
    exit;
}

// Read projects data
$projects_json = file_get_contents(__DIR__ . '/../backend/projects.json');
$projects_data = json_decode($projects_json, true);

// Read users data for employee names
$users_json = file_get_contents(__DIR__ . '/../backend/users.json');
$users_data = json_decode($users_json, true);

// Filter parameters
$status = $_GET['status'] ?? '';
$employee_id = (int)($_GET['employee_id'] ?? 0);
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

// Filter projects
$filtered_projects = array_filter($projects_data['projects'] ?? [], function($project) use ($status, $employee_id, $start_date, $end_date) {
    $matches = true;
    
    if ($status && $project['status'] !== $status) {
        $matches = false;
    }
    
    if ($employee_id && $project['manager_id'] !== $employee_id) {
        $matches = false;
    }
    
    if ($start_date && strtotime($project['start_date']) < strtotime($start_date)) {
        $matches = false;
    }
    
    if ($end_date && strtotime($project['end_date']) > strtotime($end_date)) {
        $matches = false;
    }
    
    return $matches;
});

// Calculate statistics
$total_projects = count($filtered_projects);
$status_counts = [
    'pending' => 0,
    'ongoing' => 0,
    'completed' => 0
];

foreach ($filtered_projects as $project) {
    $status_counts[$project['status']]++;
}

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="project_report_' . date('Y-m-d') . '.xls"');
header('Cache-Control: max-age=0');
?>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        table { border-collapse: collapse; }
        th, td { border: 1px solid black; padding: 5px; }
        .header { background-color: #f0f0f0; font-weight: bold; }
    </style>
</head>
<body>
    <h2>Project Report</h2>
    <p>Generated on: <?php echo date('Y-m-d H:i:s'); ?></p>
    <?php if ($start_date || $end_date): ?>
        <p>Period: <?php echo $start_date ?: 'All'; ?> to <?php echo $end_date ?: 'All'; ?></p>
    <?php endif; ?>

    <!-- Summary -->
    <table>
        <tr class="header">
            <th colspan="2">Summary</th>
        </tr>
        <tr>
            <td>Total Projects</td>
            <td><?php echo $total_projects; ?></td>
        </tr>
        <tr>
            <td>Pending Projects</td>
            <td><?php echo $status_counts['pending']; ?></td>
        </tr>
        <tr>
            <td>Ongoing Projects</td>
            <td><?php echo $status_counts['ongoing']; ?></td>
        </tr>
        <tr>
            <td>Completed Projects</td>
            <td><?php echo $status_counts['completed']; ?></td>
        </tr>
    </table>

    <br><br>

    <!-- Detailed Report -->
    <table>
        <tr class="header">
            <th>Project Name</th>
            <th>Location</th>
            <th>Client Name</th>
            <th>Project Manager</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Status</th>
            <th>Created By</th>
            <th>Created At</th>
            <th>Last Updated</th>
            <th>Remarks</th>
        </tr>
        <?php foreach ($filtered_projects as $project): 
            $manager = null;
            foreach ($users_data['users'] as $user) {
                if ($user['id'] === $project['manager_id']) {
                    $manager = $user;
                    break;
                }
            }
        ?>
            <tr>
                <td><?php echo htmlspecialchars($project['name']); ?></td>
                <td><?php echo htmlspecialchars($project['location']); ?></td>
                <td><?php echo htmlspecialchars($project['client_name']); ?></td>
                <td><?php echo $manager ? htmlspecialchars($manager['username']) : 'N/A'; ?></td>
                <td><?php echo date('Y-m-d', strtotime($project['start_date'])); ?></td>
                <td><?php echo date('Y-m-d', strtotime($project['end_date'])); ?></td>
                <td><?php echo ucfirst($project['status']); ?></td>
                <td><?php echo htmlspecialchars($project['created_by']); ?></td>
                <td><?php echo date('Y-m-d H:i', strtotime($project['created_at'])); ?></td>
                <td><?php echo date('Y-m-d H:i', strtotime($project['updated_at'])); ?></td>
                <td><?php echo htmlspecialchars($project['remarks'] ?? ''); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <?php if ($status || $employee_id): ?>
        <p>
            <?php if ($status) echo "Status Filter: " . ucfirst($status); ?>
            <?php 
            if ($employee_id) {
                foreach ($users_data['users'] as $user) {
                    if ($user['id'] === $employee_id) {
                        echo "<br>Manager Filter: " . htmlspecialchars($user['username']);
                        break;
                    }
                }
            }
            ?>
        </p>
    <?php endif; ?>
</body>
</html>
