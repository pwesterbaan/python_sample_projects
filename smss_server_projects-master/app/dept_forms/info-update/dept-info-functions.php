<?php

$host = 'mthsc.clemson.edu';
$db   = 'dept_info';
$user = 'math_dept_info';
$pass = 'cu_tigers!';
$charset = 'utf8';

$dsn = 'mysql:host='.$host.';dbname='.$db.';charset='.$charset;
$opt = array(
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false
);
$mthsc_db = new PDO($dsn, $user, $pass, $opt);

if (isset($_SERVER['REMOTE_USER']))
{
	$user_id = strtoupper($_SERVER['REMOTE_USER']);
}
date_default_timezone_set('America/New_York');


function get_nav()
{
	$nav = '<li><a href="index.php">Info Update</a></li>';
	return $nav;
}

//returns associative array of all entries in person table
function get_all_people()
{
	global $mthsc_db;
	$query = $mthsc_db->query('SELECT * FROM person WHERE username !="\-\-" ORDER BY last_name ASC');
	return $query->fetchAll();
}

//returns associative array of simple details from all entries in person table
function get_all_people_list()
{
	global $mthsc_db;
	$query = $mthsc_db->query('SELECT person_id,IF(NOT pref_name IS NULL AND pref_name != "", pref_name, first_name) AS first_name, IF(NOT suffix IS NULL AND suffix != "", CONCAT_WS(" ",last_name,suffix), last_name) AS last_name,username FROM person WHERE username !="\-\-" ORDER BY last_name ASC');
	return $query->fetchAll();
}

//returns associative array of all entries in roles table
function get_all_roles()
{
	global $mthsc_db;
	$query = $mthsc_db->query('SELECT person_id,role FROM roles ORDER BY role DESC');
	$roles_data = $query->fetchAll();
	$roles = array();
	foreach ($roles_data as $role)
	{
		$roles[$role['person_id']][] = $role['role'];
	}
	return $roles;
}

function get_people_in_role($role)
{
	global $mthsc_db;
	switch($role)
	{
		case 'students':
			$query = $mthsc_db->query('SELECT * FROM person WHERE person_id IN (SELECT person_id FROM roles where role="Student")');
			break;
		case 'alumni':
			$query = $mthsc_db->query('SELECT * FROM person WHERE person_id IN (SELECT person_id FROM roles where role="Alumni")');
			break;
		case 'faculty':
			$query = $mthsc_db->query('SELECT * FROM person WHERE person_id IN (SELECT person_id FROM roles where role="Faculty")');
			break;
		case 'emeritus':
			$query = $mthsc_db->query('SELECT * FROM person WHERE person_id IN (SELECT person_id FROM roles where role="Emeritus")');
			break;
		case 'staff':
			$query = $mthsc_db->query('SELECT * FROM person WHERE person_id IN (SELECT person_id FROM roles where role="Staff")');
			break;
	}
	return $query->fetchAll();
}

function get_all_lists()
{
	global $mthsc_db;
	$query = $mthsc_db->query('SELECT lists.list_id,list_name,IF (list_count IS NULL,0,list_count) as list_count
FROM lists
LEFT JOIN (SELECT list_id, COUNT( list_id )  as list_count
FROM  `people_to_lists_link` 
GROUP BY list_id
) AS list_counts ON lists.list_id = list_counts.list_id');
	return $query->fetchAll();
}

//accepts: person id
//returns: associative array of details from person table
function get_person_details($person_id)
{
	global $mthsc_db;
	$query = $mthsc_db->prepare('SELECT * FROM person WHERE person_id = ? LIMIT 1');
	$query->execute(array($person_id));
	return $query->fetch();
}

//accepts: person id
//returns: associative array of details from emails table
function get_emails($person_id)
{
	global $mthsc_db;
	$query = $mthsc_db->prepare('SELECT * FROM email_addresses WHERE person_id = ?');
	$query->execute(array($person_id));
	return $query->fetchAll();
}

//accepts: person id
//returns: associative array of offices for that person
function get_offices($person_id)
{
	global $mthsc_db;
	$query = $mthsc_db->prepare('SELECT pol.id AS link_id, o.office_id, o.description, o.office_type FROM people_to_offices_link AS pol INNER JOIN offices AS o ON o.office_id = pol.office_id WHERE person_id = ? ORDER BY o.description');
	$query->execute(array($person_id));
	return $query->fetchAll();
}

//accepts: person id
//returns: associative array of offices for that person
function get_education_list($person_id)
{
	global $mthsc_db;
	$query = $mthsc_db->prepare('SELECT ed.education_id, ed.school_id, ed.semester, IF(ed.year = 0, "",ed.year) as year, ed.degree, ed.major, IF(ed.final_gpa = -1, "", ed.final_gpa) AS final_gpa, s.name AS school FROM education AS ed LEFT JOIN schools AS s ON ed.school_id = s.school_id WHERE person_id = ? ORDER BY year DESC, FIELD(ed.semester, "fall", "summer II", "summer I", "spring"), FIELD(ed.degree, "PhD", "MA", "ME", "MEd", "MS", "AB", "BA", "BS"), ed.major');
	$query->execute(array($person_id));
	return $query->fetchAll();
}

//accepts: person id
//returns: associative array of phone numbers for that person
function get_phone_numbers($person_id)
{
	global $mthsc_db;
	$query = $mthsc_db->prepare('SELECT 
IF(number = "", "", CONCAT("(", SUBSTRING(number,1,3), ") ", SUBSTRING(number,4,3), "-", SUBSTRING(number, 7,4))) AS number, number_id, number_type FROM dept_info.phone_numbers WHERE person_id = ? ORDER BY number');
	$query->execute(array($person_id));
	return $query->fetchAll();
}

function get_positions($person_id)
{
	global $mthsc_db;
	$query = $mthsc_db->prepare('SELECT id,position FROM people_to_positions_link JOIN positions ON people_to_positions_link.position_id=positions.position_id WHERE person_id=?');
	$query->execute(array($person_id));
	return $query->fetchAll();
}

function get_lists($person_id)
{
	global $mthsc_db;
	$query = $mthsc_db->prepare('SELECT lists.list_id, list_name
FROM  `people_to_lists_link` 
JOIN lists ON people_to_lists_link.list_id = lists.list_id
WHERE person_id = ?');
	$query->execute(array($person_id));
	return $query->fetchAll();
}

//accepts: person id
//returns: associative array of details from roles table
function get_roles($person_id)
{
	global $mthsc_db;
	$query = $mthsc_db->prepare('SELECT role FROM roles WHERE person_id = ?');
	$query->execute(array($person_id));
	return $query->fetchAll(PDO::FETCH_COLUMN);
}

//accepts: person id
//returns: associative array of details from emails table
function get_addresses($person_id)
{
	global $mthsc_db;
	$query = $mthsc_db->prepare('SELECT * FROM mail_addresses WHERE person_id = ?');
	$query->execute(array($person_id));
	return $query->fetchAll();
}

function get_office_list()
{
	global $mthsc_db;
	$query = $mthsc_db->query('SELECT LEFT(description, IF(LOCATE("-", description) > 0, LOCATE("-", description), LENGTH(description))) AS temp1, CONVERT(RIGHT(description, LENGTH(description) - LOCATE("-", description)), SIGNED) AS temp2, office_id AS id, description, IF(number = "", "", CONCAT("(", SUBSTRING(number,1,3), ") ", SUBSTRING(number,4,3), "-", SUBSTRING(number, 7,4))) AS number, office_type FROM offices ORDER BY temp1, temp2, description, number');
	return $query->fetchAll();
}

function get_position_list()
{
	global $mthsc_db;
	$query = $mthsc_db->query('SELECT * FROM positions ORDER BY position');
	return $query->fetchAll();
}

function get_school_list()
{
	global $mthsc_db;
	$query = $mthsc_db->query('SELECT * FROM schools ORDER BY name');
	return $query->fetchAll();
}

function get_role_tag($role)
{
	if ($role=="Faculty" || $role=="Emeritus")
	{return 'faculty_tag';}
	else if ($role=="Student" || $role=="Alumni")
	{return 'student_tag';}
	else if ($role=="Staff")
	{return 'staff_tag';}
	else
	{return 'staff_tag';}
}

function get_list_details($list_id)
{
	global $mthsc_db;
	$query = $mthsc_db->prepare('SELECT lists.list_id,list_name,IF (list_count IS NULL,0,list_count) as list_count
FROM lists
LEFT JOIN (SELECT list_id, COUNT( list_id )  as list_count
FROM  `people_to_lists_link` 
GROUP BY list_id
) AS list_counts ON lists.list_id = list_counts.list_id WHERE lists.list_id=?');
	$query->execute(array($list_id));
	return $query->fetch();
}

function get_members_of_list($list_id)
{
	global $mthsc_db;
	$query = $mthsc_db->prepare('SELECT person.person_id, IF(NOT display_name IS NULL AND pref_name != "", pref_name, first_name) AS first_name, IF(NOT suffix IS NULL AND suffix != "", CONCAT_WS(" ",last_name,suffix), last_name) AS last_name, pref_name, username
FROM  `people_to_lists_link` 
INNER JOIN person ON people_to_lists_link.person_id = person.person_id
WHERE list_id = ? ORDER BY last_name ASC');
	$query->execute(array($list_id));
	return $query->fetchAll();
}

function get_current_people()
{
	global $mthsc_db;
	$query = $mthsc_db->prepare('SELECT person.person_id, IF(NOT display_name IS NULL AND display_name != "", display_name, first_name) AS first_name, IF(NOT suffix IS NULL AND suffix != "", CONCAT_WS(" ",last_name,suffix), last_name) AS last_name, pref_name, username
FROM  `people_to_lists_link` 
INNER JOIN person ON people_to_lists_link.person_id = person.person_id
WHERE list_id IN ("1","2","3","4") ORDER BY last_name ASC');
	$query->execute();
	return $query->fetchAll();
}





?>