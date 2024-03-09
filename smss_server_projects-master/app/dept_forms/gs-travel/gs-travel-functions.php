<?php

$host = 'mthsc.clemson.edu';
$db   = 'forms';
$user = 'forms';
$pass = 'd8ta_c0l';
$charset = 'utf8';

$dsn = 'mysql:host='.$host.';dbname='.$db.';charset='.$charset;
$opt = array(
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false
);
$mthsc_db = new PDO($dsn, $user, $pass, $opt);

date_default_timezone_set('America/New_York');

if (isset($_SERVER['REMOTE_USER']))
{
	$user_id = strtoupper($_SERVER['REMOTE_USER']);
	//$user_id = 'talvare';
}

$mileage_rate = "$0.575";

function get_person_info_from_user_id($username)
{
	global $mthsc_db;
	$query = $mthsc_db->prepare('SELECT person_id, CONCAT(IF(NOT display_name IS NULL AND display_name != "", display_name, first_name)," ",IF(NOT suffix IS NULL AND suffix != "", CONCAT_WS(" ",last_name,suffix), last_name)) AS full_name FROM dept_info.person WHERE username = ? LIMIT 1');
	$query->execute(array($username));
	$person_info = $query->fetch();
	return $person_info ? $person_info : array('person_id' => 0, 'full_name' => "");
}

function get_advisor_from_person_id($person_id)
{
	global $mthsc_db;
	$query = $mthsc_db->prepare('SELECT person.person_id, CONCAT(IF(NOT display_name IS NULL AND display_name != "", display_name, first_name)," ",IF(NOT suffix IS NULL AND suffix != "", CONCAT_WS(" ",last_name,suffix), last_name)) AS name FROM gs_info.advisors JOIN gs_info.degree_programs on advisee_person_id = degree_programs.person_id JOIN dept_info.person ON advisor_person_id = person.person_id WHERE advisee_person_id = ? ORDER BY advisor_type DESC LIMIT 1');
	$query->execute(array($person_id));
	$advisor = $query->fetch();
	return $advisor ? $advisor : array('person_id' => 0, 'name' => "No advisor on file");
}

function get_username_from_person_id($person_id)
{
	global $mthsc_db;
	$query = $mthsc_db->prepare('SELECT username FROM dept_info.person WHERE person_id = ? LIMIT 1');
	$query->execute(array($person_id));
	$username = $query->fetchColumn();
	return $username ? $username : "";
}

function get_person_id_from_username($username)
{
	global $mthsc_db;
	$query = $mthsc_db->prepare('SELECT person_id FROM dept_info.person WHERE username = ? LIMIT 1');
	$query->execute(array($username));
	$person_id = $query->fetchColumn();
	return $person_id ? $person_id : 0;
}


?>