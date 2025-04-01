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
    <aside>
        <h2>Add New Staff</h2>
        <form method="POST">
            <label for="first_name">First Name:</label>
            <input type="text" id="first_name" name="first_name" required>

            <label for="last_name">Last Name:</label>
            <input type="text" id="last_name" name="last_name" required>

            <label for="phone">Phone:</label>
            <input type="text" id="phone" name="phone" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="department">Department:</label>
            <select id="department" name="department" required>
                <option value="Practitioner">Practitioner</option>
                <option value="Admin">Admin</option>
            </select>

            <button type="submit" name="add_staff">Add Staff</button>
        </form>
    </aside>
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

                <div class="form-group">
                    <label for="first_name_{$staff['staff_id']}">First Name:</label>
                    <input type="text" id="first_name_{$staff['staff_id']}" name="first_name" value="{$staff['first_name']}" required>
                </div>

                <div class="form-group">
                    <label for="last_name_{$staff['staff_id']}">Last Name:</label>
                    <input type="text" id="last_name_{$staff['staff_id']}" name="last_name" value="{$staff['last_name']}" required>
                </div>

                <div class="form-group">
                    <label for="phone_{$staff['staff_id']}">Phone:</label>
                    <input type="text" id="phone_{$staff['staff_id']}" name="phone" value="{$staff['phone']}" required>
                </div>

                <div class="form-group">
                    <label for="email_{$staff['staff_id']}">Email:</label>
                    <input type="email" id="email_{$staff['staff_id']}" name="email" value="{$staff['email']}" required>
                </div>

                <div class="form-group">
                    <label for="department_{$staff['staff_id']}">Department:</label>
                    <select id="department_{$staff['staff_id']}" name="department">
                        <option value="Practitioner" {$selectedPractitioner}>Practitioner</option>
                        <option value="Admin" {$selectedAdmin}>Admin</option>
                    </select>
                </div>

                <div class="button-group">
                    <button type="submit" name="update_staff">Update Staff</button>
                    <button type="submit" name="delete_staff" style="background-color: red;">Delete Staff</button>
                </div>
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
    <link rel="stylesheet" href="../css/styles.css">
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
        /* Styling for the Aside Section */
        aside {
            background-color: #e3f2fd;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        aside h2 {
            color: #0277bd;
            margin-bottom: 15px;
        }

        aside form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        aside form label {
            font-weight: bold;
            margin-top: 10px;
            color: #333;
        }

        aside form input,
        aside form select {
            width: 80%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
        }

        aside form button {
            background-color: #0277bd;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            margin-top: 15px;
            transition: background-color 0.3s ease;
        }

        aside form button:hover {
            background-color: #01579b;
        }
        /* Modal Styling */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
            overflow: auto;
            z-index: 1000; /* Ensure the modal is above other elements */
        }

        .modal-content {
            background-color: #ffffff; /* White background for the modal */
            margin: 5% auto;
            padding: 20px;
            width: 50%;
            border-radius: 10px; /* Rounded corners */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Add a subtle shadow */
            text-align: left;
            font-family: Arial, sans-serif;
        }

        .modal-content h2 {
            color: #0277bd; /* Light blue heading */
            margin-bottom: 20px;
            text-align: center;
        }

        .modal-content form {
            display: flex;
            flex-direction: column;
            gap: 15px; /* Add spacing between form elements */
        }

        .modal-content .form-group {
            display: flex;
            flex-direction: column;
        }

        .modal-content .form-group label {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .modal-content .form-group input,
        .modal-content .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
            box-sizing: border-box;
        }

        .modal-content .button-group {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        .modal-content .button-group button {
            background-color: #0277bd;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s ease;
        }

        .modal-content .button-group button:hover {
            background-color: #01579b;
        }

        .modal-content .button-group button[style*="background-color: red"] {
            background-color: red;
            color: white;
        }

        .modal-content .button-group button[style*="background-color: red"]:hover {
            background-color: darkred;
        }

        .close {
            float: right;
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .close:hover {
            color: #ff0000; /* Red color on hover */
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
                <li><a href="<?php echo BASE_URL; ?>/views/appointments.php">Appointments</a></li>
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