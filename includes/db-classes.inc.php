<?php
class DatabaseHelper
{
    /* Returns a connection object to a database */
    public static function createConnection($connString)
    {
        $pdo = new PDO($connString);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    }

    /* Runs the specified SQL query using the passed connection and parameters */
    public static function runQuery($connection, $sql, $parameters = null)
    {
        try {
            $statement = null;
            if ($parameters !== null) {
                if (!is_array($parameters)) $parameters = [$parameters];
                $statement = $connection->prepare($sql);
                $executedOk = $statement->execute($parameters);
                if (!$executedOk) throw new PDOException;
            } else {
                $statement = $connection->query($sql);
                if (!$statement) throw new PDOException;
            }
            return $statement;
        } catch (PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
            return null;
        }
    }
}
class StaffDB
{
    private $pdo;
    private static $baseSQL = "SELECT * FROM staff";

    public function __construct($connection)
    {
        $this->pdo = $connection;
    }

    public function addStaff($firstName, $lastName, $phone, $email, $department)
    {
        if ($this->checkDuplicateStaff($firstName, $lastName, $phone, $email)) {
            throw new Exception("A staff member with the same name, phone, or email already exists.");
        }

        $sql = "INSERT INTO staff (first_name, last_name, phone, email, department)
                VALUES (?, ?, ?, ?, ?)";
        DatabaseHelper::runQuery($this->pdo, $sql, [$firstName, $lastName, $phone, $email, $department]);
        return $this->pdo->lastInsertId();
    }

    public function updateStaff($staffId, $firstName, $lastName, $phone, $email, $department)
    {
        $existingStaff = $this->getStaff($staffId);
        if (!$existingStaff) {
            throw new Exception("Staff member not found.");
        }

        // Avoid checking for duplicates if updating the same record
        if ($existingStaff['first_name'] !== $firstName || $existingStaff['last_name'] !== $lastName || $existingStaff['phone'] !== $phone || $existingStaff['email'] !== $email) {
            if ($this->checkDuplicateStaff($firstName, $lastName, $phone, $email)) {
                throw new Exception("Cannot update to a duplicate staff record.");
            }
        }

        $sql = "UPDATE staff SET first_name = ?, last_name = ?, phone = ?, email = ?, department = ?
                WHERE staff_id = ?";
        DatabaseHelper::runQuery($this->pdo, $sql, [$firstName, $lastName, $phone, $email, $department, $staffId]);
    }

    public function deleteStaff($staffId)
    {
        $sql = "DELETE FROM staff WHERE staff_id = ?";
        DatabaseHelper::runQuery($this->pdo, $sql, [$staffId]);
    }

    public function getAll()
    {
        $statement = DatabaseHelper::runQuery($this->pdo, self::$baseSQL);
        return $statement->fetchAll();
    }

    public function getStaff($id)
    {
        $sql = self::$baseSQL . " WHERE staff_id = ?";
        $statement = DatabaseHelper::runQuery($this->pdo, $sql, [$id]);
        return $statement->fetch();
    }

    public function checkDuplicateStaff($firstName, $lastName, $phone, $email)
    {
        $sql = "SELECT COUNT(*) as count
            FROM staff 
            WHERE (first_name = ? AND last_name = ?) 
            OR phone = ? 
            OR email = ?";

        $statement = DatabaseHelper::runQuery($this->pdo, $sql, [$firstName, $lastName, $phone, $email]);
        $result = $statement->fetch();
        return $result['count'] > 0;
    }
}
class PatientDB
{
    private $pdo;
    private static $baseSQL =
    "SELECT *
                FROM patient";
    public function __construct($connection)
    {
        $this->pdo = $connection;
    }
    public function getAll()
    {
        $sql = self::$baseSQL;
        $statement = DatabaseHelper::runQuery($this->pdo, $sql, null);
        return $statement->fetchAll();
    }
    public function getPatient($id)
    {
        $sql = self::$baseSQL . " WHERE patient_id=?";
        $statement = DatabaseHelper::runQuery($this->pdo, $sql, [$id]);
        return $statement->fetch();
    }
    public function addPatient($first_name, $last_name, $insurance_provider)
    {
        $sql = "INSERT INTO patient (first_name, last_name, insurance_provider)
                VALUES (?, ?, ?)";
        $statement = DatabaseHelper::runQuery($this->pdo, $sql, [$first_name, $last_name, $insurance_provider]);
        return $statement;
    }
    public function updatePatient($id, $insurance_provider)
    {
        $sql = "UPDATE patient
                SET insurance_provider=?
                WHERE patient_id=?";
        $statement = DatabaseHelper::runQuery($this->pdo, $sql, [$insurance_provider, $id]);
        return $statement;
    }
    public function findPatient($first_name, $last_name)
    {
        $sql = "SELECT *
                FROM patient
                WHERE first_name=? AND last_name=?";
        $statement = DatabaseHelper::runQuery($this->pdo, $sql, [$first_name, $last_name]);
        return $statement->fetch();
    }
    public function deletePatient($id)
    {
        $sql = "DELETE FROM patient
                WHERE patient_id=?";
        $statement = DatabaseHelper::runQuery($this->pdo, $sql, [$id]);
        return $statement;
    }
}
class DailyMasterScheduleDB
{
    private $pdo;
    private static $baseSQL = "SELECT * FROM daily_master_schedule_view";

    public function __construct($connection)
    {
        $this->pdo = $connection;
    }

    public function getAll()
    {
        $sql = self::$baseSQL;
        $statement = DatabaseHelper::runQuery($this->pdo, $sql, null);
        return $statement->fetchAll();
    }

    public function getDailyMasterSchedule($date)
    {
        $sql = self::$baseSQL . " WHERE date = ?";
        $statement = DatabaseHelper::runQuery($this->pdo, $sql, [$date]);
        return $statement->fetchAll();
    }
}
class PrescriptionDB
{
    private $pdo;
    private static $baseSQL = "SELECT * FROM prescription_view";

    public function __construct($connection)
    {
        $this->pdo = $connection;
    }

    public function getAll()
    {
        $sql = self::$baseSQL;
        $statement = DatabaseHelper::runQuery($this->pdo, $sql, null);
        return $statement->fetchAll();
    }

    public function getPrescription($id)
    {
        $sql = self::$baseSQL . " WHERE prescription_id = ?";
        $statement = DatabaseHelper::runQuery($this->pdo, $sql, [$id]);
        return $statement->fetch();
    }
}

class ReportsDB
{
    private $pdo;
    public function __construct($connection)
    {
        $this->pdo = $connection;
    }
    public function getPatientMonthlyStatement()
    {
        $sql = "SELECT * FROM patient_monthly_statement";
        $statement = DatabaseHelper::runQuery($this->pdo, $sql, null);
        return $statement->fetchAll();
    }
    public function getMonthlyActivityReport()
    {
        $sql = "SELECT * FROM monthly_activity_report";
        $statement = DatabaseHelper::runQuery($this->pdo, $sql, null);
        return $statement->fetchAll();
    }
    public function getMonthlyPractitionerReport()
    {
        $sql = "SELECT * FROM monthly_practitioner_report";
        $statement = DatabaseHelper::runQuery($this->pdo, $sql, null);
        return $statement->fetchAll();
    }
}
