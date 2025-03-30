<?php
// Required to connect to the wellness clinic database
require_once "../includes/config.inc.php";
require_once "../includes/db-classes.inc.php";

$conn = DatabaseHelper::createConnection(DBCONNSTRING);
$staffGateway = new StaffDB($conn);

$message = "";
$allStaff = $staffGateway->getAll();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $department = trim($_POST['department'] ?? '');

        if (empty($firstName) || empty($lastName) || empty($phone) || empty($email) || empty($department)) {
            throw new Exception("All fields are required.");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format.");
        }

        if (!preg_match("/^[0-9]{10}$/", $phone)) {
            throw new Exception("Phone number must be 10 digits.");
        }

        if (isset($_POST['add_staff'])) {
            $staffGateway->addStaff($firstName, $lastName, $phone, $email, $department);
            $message = "<p style='color:green;'>Staff added successfully.</p>";
        }

        if (isset($_POST['update_staff'])) {
            $staffGateway->updateStaff($_POST['staff_id'], $firstName, $lastName, $phone, $email, $department);
            $message = "<p style='color:green;'>Staff updated successfully.</p>";
        }

        if (isset($_POST['delete_staff'])) {
            $staffGateway->deleteStaff($_POST['staff_id']);
            $message = "<p style='color:red;'>Staff deleted successfully.</p>";
        }

        header("Location: staff.php");
        exit;
    }
} catch (Exception $e) {
    $message = "<p style='color:red;'>{$e->getMessage()}</p>";
}

// Function to generate the Aside Form
function generateAside()
{
    return <<<HTML
    <h2>Add New Staff</h2>
    <form method="POST">
        <label>First Name:</label><input type="text" name="first_name" required><br>
        <label>Last Name:</label><input type="text" name="last_name" required><br>
        <label>Phone:</label><input type="text" name="phone" required><br>
        <label>Email:</label><input type="email" name="email" required><br>
        <label>Department:</label>
        <select name="department" required>
            <option value="Practitioner">Practitioner</option>
            <option value="Admin">Admin</option>
        </select><br>
        <button type="submit" name="add_staff">Add Staff</button>
    </form>
HTML;
}

// Function to generate the Main Content Table
function generateMainContent($allStaff)
{
    $output = "<h2>Staff List</h2><table border='1'><tr><th>ID</th><th>Name</th><th>Phone</th><th>Email</th><th>Department</th><th>Actions</th></tr>";

    foreach ($allStaff as $staff) {
        $output .= "
            <tr>
                <td>{$staff['staff_id']}</td>
                <td>{$staff['first_name']} {$staff['last_name']}</td>
                <td>{$staff['phone']}</td>
                <td>{$staff['email']}</td>
                <td>{$staff['department']}</td>
                <td><button onclick=\"openModal({$staff['staff_id']})\">View</button></td>
            </tr>
            " . generateModal($staff);
    }

    $output .= "</table>";
    return $output;
}

// Function to generate Modals for each staff
function generateModal($staff)
{
    $selectedPractitioner = $staff['department'] === 'Practitioner' ? 'selected' : '';
    $selectedAdmin = $staff['department'] === 'Admin' ? 'selected' : '';

    return <<<HTML
    <div class="modal" id="staffModal{$staff['staff_id']}">
        <div class="modal-content">
            <span onclick="closeModal({$staff['staff_id']})" class="close">&times;</span>
            <h2>Edit Staff: {$staff['first_name']} {$staff['last_name']}</h2>
            <form method="POST">
                <input type="hidden" name="staff_id" value="{$staff['staff_id']}">
                <label>First Name:</label><input type="text" name="first_name" value="{$staff['first_name']}" required><br>
                <label>Last Name:</label><input type="text" name="last_name" value="{$staff['last_name']}" required><br>
                <label>Phone:</label><input type="text" name="phone" value="{$staff['phone']}" required><br>
                <label>Email:</label><input type="email" name="email" value="{$staff['email']}" required><br>
                <label>Department:</label>
                <select name="department">
                    <option value="Practitioner" {$selectedPractitioner}>Practitioner</option>
                    <option value="Admin" {$selectedAdmin}>Admin</option>
                </select><br>
                <button type="submit" name="update_staff">Update Staff</button>
                <button type="submit" name="delete_staff">Delete Staff</button>
            </form>
        </div>
    </div>
HTML;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Management - Wellness Clinic</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 20px;
            width: 50%;
            border-radius: 8px;
        }

        .close {
            float: right;
            cursor: pointer;
        }
    </style>
    <script>
        function openModal(staffId) {
            document.getElementById('staffModal' + staffId).style.display = 'block';
        }

        function closeModal(staffId) {
            document.getElementById('staffModal' + staffId).style.display = 'none';
        }
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>
</head>

<body>
    <?= $message ?>
    <header>
        <h1>Wellness Clinic</h1>
        <nav>
            <ul>
                <li><a href="<?php echo BASE_URL; ?>/index.php">Home</a></li>
                <li><a href="<?php echo BASE_URL; ?>/views/schedule.php">Schedules</a></li>
                <li><a href="<?php echo BASE_URL; ?>/views/reports.php">Reports</a></li>
                <li><a href="<?php echo BASE_URL; ?>/views/staff.php">Staff</a></li>
                <li><a href="<?php echo BASE_URL; ?>/views/patients.php">Patients</a></li>
                <li><a href="<?php echo BASE_URL; ?>/views/prescription.php">Prescriptions</a></li>
            </ul>
        </nav>
    </header>
    <aside><?= generateAside() ?></aside>
    <main><?= generateMainContent($allStaff) ?></main>
    <footer>
        <p>&copy; <?= date("Y") ?> Wellness Clinic Project</p>
    </footer>
</body>

</html>