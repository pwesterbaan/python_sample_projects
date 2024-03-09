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

$admins = array('HEDETNI', 'CLCOX', 'JDYKEN','REBHOLZ', 'PGERARD');

$user_id = "";
$user_fullname = "";
if (isset($_SERVER['REMOTE_USER']))
{
	$user_id = strtoupper($_SERVER['REMOTE_USER']);
	$person_id = get_person_id_from_user_id($user_id);
}
if (isset($_SERVER['fullName']))
{
	$user_fullname = $_SERVER['fullName'];
}
if (!isset($_SESSION)){ session_start();}

$admins = array('HEDETNI','JDYKEN','VMCCLAI','PGERARD');

function get_nav()
{
	global $admins;
	global $user_id;
	$nav = '<li><a href="index.php">Teaching Preferences Home</a></li>';
	if (in_array($user_id,$admins))
	{
		$nav .= '<li><a href="admin.php">Admin</a></li>';
	}
	return $nav;
}

$currently_requested_term = get_currently_requested_term();
$are_submissions_open = get_submission_status();

$current_term = get_current_term();
$next_term = get_next_term($current_term);

//returns term code for current term based on month
function get_current_term()
{
	$year = date('Y',strtotime('now'));
	$month = date('m',strtotime('now'));
	switch($month)
	{
		case '01':
			$term = $year.'01';
			break;
		case '02':
			$term = $year.'01';
			break;
		case '03':
			$term = $year.'01';
			break;
		case '04':
			$term = $year.'01';
			break;
		case '05':
			$term = $year.'05';
			break;
		case '06':
			$term = $year.'05';
			break;
		case '07':
			$term = $year.'05';
			break;
		default: 
			$term = $year.'08';
			break;
	}
	return $term; 
}

function get_currently_requested_term()
{
	global $mthsc_db;
	$requested_term_query = $mthsc_db->query('SELECT value FROM settings WHERE name = "teaching_pref_current_term"');
	$requested_term = $requested_term_query->fetchColumn();
	return $requested_term;
}

function get_submission_status()
{
	global $mthsc_db;
	$requested_term_query = $mthsc_db->query('SELECT value FROM settings WHERE name = "teaching_pref_open"');
	$requested_term = $requested_term_query->fetchColumn();
	return $requested_term;
}


function get_previous_term($current_term)
{
	$current_semester = substr($current_term,-2);
	$current_year = substr($current_term,0,4);
	switch($current_semester)
	{
		case '01':
			$previous_semester = '08';
			$previous_year = $current_year - 1;
			break;
		case '05':
			$previous_semester = '01';
			$previous_year = $current_year;
			break;
		case '08':
			$previous_semester = '05';
			$previous_year = $current_year;
			break;
	}
	return $previous_year.$previous_semester;
}

function get_next_term($current_term)
{
	$current_semester = substr($current_term,-2);
	$current_year = substr($current_term,0,4);
	switch($current_semester)
	{
		case '01':
			$next_semester = '05';
			$next_year = $current_year;
			break;
		case '05':
			$next_semester = '08';
			$next_year = $current_year;
			break;
		case '08':
			$next_semester = '01';
			$next_year = $current_year+1;
			break;
	}
	return $next_year.$next_semester;
}

function term_ending_to_semester($term)
{
	switch(substr($term,-2))
	{
		case '01':
			$semester = "Spring";
			break;
		case '05':
			$semester = "Summer";
			break;
		case '08':
			$semester = "Fall";
			break;
	}
	return $semester;
}

function get_person_id_from_user_id($username)
{
	global $mthsc_db;
	$query = $mthsc_db->prepare('SELECT person_id FROM dept_info.person WHERE username = ? LIMIT 1');
	$query->execute(array($username));
	return $query->fetchColumn();
}

function check_messages()
{
	global $message;
	global $error;
	if (isset($_SESSION['message']) )
	{
		$message = $_SESSION['message'];
		unset($_SESSION['message']);
	}
	if (isset($_SESSION['error']) )
	{
		$error = $_SESSION['error'];
		unset($_SESSION['error']);
	}
}

function get_all_preferences_for_person($person_id)
{
	global $mthsc_db;
	$query = $mthsc_db->prepare('SELECT * FROM teaching_preferences WHERE person_id = ? ORDER BY term DESC');
	$query->execute(array($person_id));
	return $query->fetchAll();
}

?>


