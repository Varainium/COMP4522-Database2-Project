<?php
class DatabaseHelper
{
    /* Returns a connection object to a database */
    public static function createConnection($connString)
    {
        $pdo = new PDO($connString);
        $pdo->setAttribute(
            PDO::ATTR_ERRMODE,
            PDO::ERRMODE_EXCEPTION
        );
        $pdo->setAttribute(
            PDO::ATTR_DEFAULT_FETCH_MODE,
            PDO::FETCH_ASSOC
        );
        return $pdo;
    }
    /*
Runs the specified SQL query using the passed connection and
the passed array of parameters (null if none)
*/
    public static function runQuery($connection, $sql, $parameters)
    {
        $statement = null;
        // if there are parameters then do a prepared statement
        if (isset($parameters)) {
            // Ensure parameters are in an array
            if (!is_array($parameters)) {
                $parameters = array($parameters);
            }
            // Use a prepared statement if parameters
            $statement = $connection->prepare($sql);
            $executedOk = $statement->execute($parameters);
            if (! $executedOk) throw new PDOException;
        } else {
            // Execute a normal query
            $statement = $connection->query($sql);
            if (!$statement) throw new PDOException;
        }
        return $statement;
    }
}
class StaffDB
{
    private $pdo;
    private static $baseSQL =
    "SELECT *
                FROM staff";
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
    public function getStaff($id)
    {
        $sql = self::$baseSQL . " WHERE staff_id=?";
        $statement = DatabaseHelper::runQuery($this->pdo, $sql, array($id));
        return $statement->fetch();
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
        $statement = DatabaseHelper::runQuery($this->pdo, $sql, array($id));
        return $statement->fetch();
    }
    public function addPatient($first_name, $last_name, $insurance_provider)
    {
        $sql = "INSERT INTO patient (first_name, last_name, insurance_provider)
                VALUES (?, ?, ?)";
        $statement = DatabaseHelper::runQuery($this->pdo, $sql, array($first_name, $last_name, $insurance_provider));
        return $statement;
    }
    public function updatePatient($id, $insurance_provider)
    {
        $sql = "UPDATE patient
                SET insurance_provider=?
                WHERE patient_id=?";
        $statement = DatabaseHelper::runQuery($this->pdo, $sql, array($insurance_provider, $id));
        return $statement;
    }
    public function findPatient($first_name, $last_name, $insurance_provider) 
    {
        $sql = "SELECT *
                FROM patient
                WHERE first_name=? AND last_name=? AND insurance_provider=?";
        $statement = DatabaseHelper::runQuery($this->pdo, $sql, array($first_name, $last_name, $insurance_provider));
        return $statement->fetch();
    }
    public function deletePatient($id)
    {
        $sql = "DELETE FROM patient
                WHERE patient_id=?";
        $statement = DatabaseHelper::runQuery($this->pdo, $sql, array($id));
        return $statement;
    }
}
class DailyMasterScheduleDB
{
    private $pdo;
    private static $baseSQL =
    "SELECT dms.date, dms.staff_id, s.first_name, s.last_name, dms.shift_start_time, dms.shift_end_time, dms.appointment_slots, dms.walk_in_availability
        FROM daily_master_schedule AS dms
        JOIN staff AS s ON dms.staff_id = s.staff_id";
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
    public function getDailyMasterSchedule($id)
    {
        $sql = self::$baseSQL . " WHERE date=?";
        $statement = DatabaseHelper::runQuery($this->pdo, $sql, array($id));
        return $statement->fetchAll();
    }
}
