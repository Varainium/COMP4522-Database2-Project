<?php
// Required to connect to the wellness clinic database
require_once "../includes/config.inc.php";
require_once "../includes/db-classes.inc.php";

$conn = DatabaseHelper::createConnection(DBCONNSTRING);
$appointmentDB = new AppointmentDB($conn);
$patientDB = new PatientDB($conn);
$staffDB = new StaffDB($conn);

$message = "";
$appointments = $appointmentDB->getAll();

// Fetch patients and practitioners for dropdowns
$patients = $patientDB->getAll();
$practitioners = $staffDB->getPractitioners();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // Create a New Appointment
        if (isset($_POST['add_appointment'])) {
            $patientId = $_POST['patient_id'];
            $practitionerId = $_POST['practitioner_id'];
            $appointmentDate = $_POST['appointment_date'];
            $appointmentTime = $_POST['appointment_time'];
            $appointmentType = $_POST['appointment_type'];
            $reason = $_POST['reason'];

            $appointmentDB->addAppointment($patientId, $practitionerId, $appointmentDate, $appointmentTime, $appointmentType, $reason);
            $message = "<p style='color:green;'>Appointment created successfully!</p>";
            header("Location: appointments.php");
            exit;
        }

        // Update an Appointment
        if (isset($_POST['update_appointment'])) {
            $appointmentId = $_POST['appointment_id'];
            $patientId = $_POST['patient_id'];
            $practitionerId = $_POST['practitioner_id'];
            $appointmentDate = $_POST['appointment_date'];
            $appointmentTime = $_POST['appointment_time'];
            $appointmentType = $_POST['appointment_type'];
            $reason = $_POST['reason'];
            $status = $_POST['status'];

            $appointmentDB->updateAppointment($appointmentId, $patientId, $practitionerId, $appointmentDate, $appointmentTime, $appointmentType, $reason, $status);
            $message = "<p style='color:green;'>Appointment updated successfully!</p>";
            header("Location: appointments.php");
            exit;
        }

        // Delete an Appointment
        if (isset($_POST['delete_appointment'])) {
            $appointmentId = $_POST['appointment_id'];
            $appointmentDB->deleteAppointment($appointmentId);
            $message = "<p style='color:red;'>Appointment deleted successfully!</p>";
            header("Location: appointments.php");
            exit;
        }

        // Generate Billing Statement
        else if (isset($_POST['generate_bill'])) {
            $appointmentId = $_POST['appointment_id'];
            $practitionerId = $_POST['practitioner_id'];
            $totalFee = $_POST['total_fee'];
            $insurancePaid = $_POST['insurance_paid'] ?? 0.00;
            $patientDue = $_POST['patient_due'];
            $paymentMethod = $_POST['payment_method'];

            try {
                $appointmentDB->generateBillingStatement($appointmentId, $practitionerId, $totalFee, $insurancePaid, $patientDue, $paymentMethod);

                // Display an alert box on successful bill generation
                echo "<script>alert('Billing Statement Generated Successfully!');</script>";

                // Redirect to avoid form resubmission on refresh
                echo "<script>window.location.href = 'appointments.php';</script>";
                exit;
            } catch (Exception $e) {
                echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
            }
        }
    }
} catch (Exception $e) {
    $message = "<p style='color:red;'>{$e->getMessage()}</p>";
}

function generateAppointmentForm($patients, $practitioners)
{
    $output = "<section><h2>Add Appointment</h2>
    <form method='POST' class='add-appointment'>
        <label>Patient:</label>
        <select name='patient_id' required>";

    foreach ($patients as $patient) {
        $output .= "<option value='{$patient['patient_id']}'>{$patient['first_name']} {$patient['last_name']}</option>";
    }

    $output .= "</select>
        <label>Practitioner:</label>
        <select name='practitioner_id' required>";

    foreach ($practitioners as $practitioner) {
        $output .= "<option value='{$practitioner['staff_id']}'>{$practitioner['first_name']} {$practitioner['last_name']}</option>";
    }

    $output .= "</select>
        <label>Date:</label>
        <input type='date' name='appointment_date' required>
        <label>Time:</label>
        <input type='time' name='appointment_time' required>
        <label>Type:</label>
            <select name='appointment_type' required>
                <option value='Scheduled'>Scheduled</option>
                <option value='Walk-In'>Walk-In</option>
            </select>
        <label>Reason:</label>
            <textarea name='reason'></textarea>
        <button type='submit' name='add_appointment'>Add Appointment</button>
    </form></section>";

    return $output;
}

function generateAppointmentsTable($appointments, $patients, $practitioners)
{
    $output = "<section><h2>Appointments</h2>
    <table border='1'>
        <tr>
            <th>ID</th>
            <th>Patient</th>
            <th>Practitioner</th>
            <th>Date</th>
            <th>Time</th>
            <th>Actions</th>
        </tr>";

    foreach ($appointments as $appointment) {
        $appointmentId = $appointment['appointment_id'];
        $practitionerId = $appointment['practitioner_id'];
        $output .= "<tr>
            <td>{$appointmentId}</td>
            <td>{$appointment['patient_name']}</td>
            <td>{$appointment['practitioner_name']}</td>
            <td>{$appointment['appointment_date']}</td>
            <td>{$appointment['appointment_time']}</td>
            <td>
                <button onclick=\"openModal('billModal{$appointmentId}')\">Generate Bill</button>
                <button onclick=\"openModal('editModal{$appointmentId}')\">Edit</button>
                <form method='POST' style='display:inline;'>
                    <input type='hidden' name='appointment_id' value='{$appointmentId}'>
                    <button type='submit' name='delete_appointment' class='delete'>Delete</button>
                </form>
            </td>
        </tr>";

        $output .= generateBillingModal($appointmentId, $practitionerId);
        $output .= generateEditModal($appointment, $patients, $practitioners);
    }

    $output .= "</table></section>";
    return $output;
}

function generateEditModal($appointment, $patients, $practitioners)
{
    $appointmentId = $appointment['appointment_id'];
    $patientId = $appointment['patient_id'];
    $practitionerId = $appointment['practitioner_id'];
    $date = $appointment['appointment_date'];
    $time = $appointment['appointment_time'];
    $appointmentType = $appointment['appointment_type'];
    $reason = htmlspecialchars($appointment['reason'] ?? '');
    $status = $appointment['status'] ?? 'Pending';

    // Generate patient dropdown options
    $patientOptions = "";
    foreach ($patients as $patient) {
        $selected = ($patient['patient_id'] == $patientId) ? "selected" : "";
        $patientOptions .= "<option value='{$patient['patient_id']}' {$selected}>{$patient['first_name']} {$patient['last_name']}</option>";
    }

    // Generate practitioner dropdown options
    $practitionerOptions = "";
    foreach ($practitioners as $practitioner) {
        $selected = ($practitioner['staff_id'] == $practitionerId) ? "selected" : "";
        $practitionerOptions .= "<option value='{$practitioner['staff_id']}' {$selected}>{$practitioner['first_name']} {$practitioner['last_name']}</option>";
    }

    // Generate appointment type options
    $typeOptions = "";
    $types = ['Scheduled', 'Walk-In'];
    foreach ($types as $type) {
        $selected = ($type === $appointmentType) ? "selected" : "";
        $typeOptions .= "<option value='{$type}' {$selected}>{$type}</option>";
    }

    // Generate status options
    $statusOptions = "";
    $statuses = ['Pending', 'Completed', 'Cancelled'];
    foreach ($statuses as $statusOption) {
        $selected = ($statusOption === $status) ? "selected" : "";
        $statusOptions .= "<option value='{$statusOption}' {$selected}>{$statusOption}</option>";
    }

    return <<<HTML
    <div id="editModal{$appointmentId}" class="modal">
        <div class="modal-content">
            <span onclick="closeModal('editModal{$appointmentId}')" class="close">&times;</span>
            <h2>Edit Appointment</h2>
            <form method="POST">
                <input type="hidden" name="appointment_id" value="{$appointmentId}">

                <div class="form-group">
                    <label>Patient:</label>
                    <select name="patient_id" required>{$patientOptions}</select>
                </div>

                <div class="form-group">
                    <label>Practitioner:</label>
                    <select name="practitioner_id" required>{$practitionerOptions}</select>
                </div>

                <div class="form-group">
                    <label>Date:</label>
                    <input type="date" name="appointment_date" value="{$date}" required>
                </div>

                <div class="form-group">
                    <label>Time:</label>
                    <input type="time" name="appointment_time" value="{$time}" required>
                </div>

                <div class="form-group">
                    <label>Appointment Type:</label>
                    <select name="appointment_type" required>{$typeOptions}</select>
                </div>

                <div class="form-group">
                    <label>Reason:</label>
                    <textarea name="reason">{$reason}</textarea>
                </div>

                <div class="form-group">
                    <label>Status:</label>
                    <select name="status" required>{$statusOptions}</select>
                </div>

                <div class="button-group">
                    <button type="submit" name="update_appointment">Update Appointment</button>
                </div>
            </form>
        </div>
    </div>
HTML;
}

function generateBillingModal($appointmentId, $practitionerId)
{
    return <<<HTML
    <div id="billModal{$appointmentId}" class="modal">
        <div class="modal-content">
            <span onclick="closeModal('billModal{$appointmentId}')" class="close">&times;</span>
            <h2>Generate Bill</h2>
            <form method="POST">
                <input type="hidden" name="appointment_id" value="{$appointmentId}">
                <input type="hidden" name="practitioner_id" value="{$practitionerId}">

                <div class="form-group">
                    <label>Total Fee:</label>
                    <input type="number" name="total_fee" step="0.01" required>
                </div>

                <div class="form-group">
                    <label>Insurance Paid:</label>
                    <input type="number" name="insurance_paid" step="0.01" value="0.00" required>
                </div>

                <div class="form-group">
                    <label>Patient Due:</label>
                    <input type="number" name="patient_due" step="0.01" required>
                </div>

                <div class="form-group">
                    <label>Payment Method:</label>
                    <select name="payment_method" required>
                        <option value="Out-of-Pocket">Out-of-Pocket</option>
                        <option value="Insurance">Insurance</option>
                        <option value="Government">Government</option>
                    </select>
                </div>

                <div class="button-group">
                    <button type="submit" name="generate_bill">Generate Bill</button>
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
    <title>Appointments</title>
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
            overflow: auto;
            z-index: 1000;
        }

        .modal-content {
            background-color: #ffffff;
            margin: 5% auto;
            padding: 20px;
            width: 50%;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            font-family: Arial, sans-serif;
        }

        .modal-content h2 {
            color: #0277bd;
            text-align: center;
            margin-bottom: 20px;
        }

        .modal-content .form-group {
            margin-bottom: 15px;
        }

        .modal-content .form-group label {
            font-weight: bold;
            color: #333;
            display: block;
            margin-bottom: 5px;
        }

        .modal-content .form-group input,
        .modal-content .form-group select,
        .modal-content .form-group textarea,
        form.add-appointment input,
        form.add-appointment select,
        form.add-appointment textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
            box-sizing: border-box;
            text-align: center; /* Center the text inside the input, select, and textarea */
        }

        .modal-content .form-group textarea {
            resize: vertical;
            height: 100px;
        }

        .modal-content .button-group {
            text-align: center;
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

        .close {
            float: right;
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .close:hover {
            color: #ff0000;
        }

        form h2 {
            color: #0277bd;
            text-align: center;
            margin-bottom: 15px;
        }

        form label {
            font-weight: bold;
            margin-top: 10px;
            display: block;
            color: #333;
        }

        form input,
        form select,
        form textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
            box-sizing: border-box;
        }

        form textarea {
            resize: vertical;
            height: 100px;
        }

        /* Styling for the Add Appointment Form */
        form.add-appointment {
            background-color: #e3f2fd;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 0 auto;
            text-align: left;
        }

        form.add-appointment h2 {
            color: #0277bd;
            text-align: center;
            margin-bottom: 15px;
        }

        form.add-appointment label {
            font-weight: bold;
            margin-top: 10px;
            display: block;
            color: #333;
        }

        form.add-appointment input,
        form.add-appointment select,
        form.add-appointment textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
            box-sizing: border-box;
        }

        form.add-appointment textarea {
            resize: vertical;
            height: 100px;
        }

    </style>
    <script>
        function openModal(id) {
            document.getElementById(id).style.display = 'block';
        }

        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
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
        <h1>Appointments</h1>
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

    <main>
        <?= generateAppointmentForm($patients, $practitioners); ?>
        <?= generateAppointmentsTable($appointments, $patients, $practitioners); ?>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Wellness Clinic Project</p>
    </footer>
</body>

</html>