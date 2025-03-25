<?php
define('DBHOST', 'localhost');
define('DBNAME', 'wellness_clinic');
define('DBUSER', '');
define('DBPASS', '');
// define('DBCONNSTRING', 'sqlite:./data/wellness_clinic.db');
define('DBCONNSTRING', 'sqlite:'.realpath(__DIR__ . '/../data/wellness_clinic.db'));
define('BASE_URL', '/COMP4522-Database2-Project');