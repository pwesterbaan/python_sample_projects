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
}

$current_year = date('Y',strtotime('now'));
if (date('n') >= 4 || date('n') <= 6)
{
	$season = 'spring';
}
if (date('n') <= 2 || date('n') == 12)
{
	$season = 'fall';
}
$alphabet = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');

//this list determines what links people see in the nav, and gives them access on admin pages
$admin_list = array($user_id);

//these lists determine what faculty members admins see on the review faculty page (and thus which ones they can evaluate)
$evaluators = array('Mathematics Division' => array($user_id),
					'Statistics and Operations Research Division' => array($user_id),
					'Mathematics and Statistics Education Division' => array($user_id),
					'Not Set' => array($user_id),
					'Director' => array($user_id));

function get_nav()
{
	global $admin_list;
	global $user_id;
	$nav = '<li><a href="index.php">Home</a></li>';
	$nav .= '<li><a href="activity-percentages.php">Activity Percentage Entry</a></li>';
	if (in_array($user_id,$admin_list))
	{
		$nav .= '<li><a href="admin-view-faculty.php">Admin View Faculty</a></li>';
		$nav .= '<li><a href="settings.php">Admin Settings</a></li>';
	}
	return $nav;
}

function get_person_id($username)
{
	global $mthsc_db;
	$username = strtoupper($username);
	$person_query = $mthsc_db->prepare("SELECT person_id FROM dept_info.person WHERE username = ? LIMIT 1");
	$person_query->execute(array($username));
	$person_id = $person_query->fetchColumn();
	if ($person_id != 0)
	{return $person_id;}
	else
	{return 0;}
}

function get_name_from_person_id($person_id)
{
	global $mthsc_db;
	global $alphabet;
	$person_query = $mthsc_db->prepare('SELECT username FROM dept_info.person WHERE person_id = ? LIMIT 1');
	$person_query->execute(array($person_id));
	$uname = $person_query->fetchColumn();
	$fake_uname = preg_replace('/[a-zA-Z]+/', '', md5($uname));
	$first = strtoupper($alphabet[substr($fake_uname,0,1)]);
	$last = strtoupper($alphabet[substr($fake_uname,6,1)]);
	return $first.'----, '.$last.'----------';
}

function get_username_from_person_id($person_id)
{
	global $mthsc_db;
	$person_query = $mthsc_db->prepare('SELECT username FROM dept_info.person WHERE person_id = ? LIMIT 1');
	$person_query->execute(array($person_id));
	$uname = $person_query->fetchColumn();
	return strtoupper(substr(preg_replace('/[0-9]+/', '', md5($uname)),0,6));
}

function get_division_from_person_id($person_id)
{
	global $mthsc_db;
	$subfaculty_query = $mthsc_db->prepare('SELECT division FROM dept_info.primary_subfaculty INNER JOIN dept_info.subfaculties USING (subfaculty_id) WHERE person_id = ?');
	$subfaculty_query->execute(array($person_id));
	return $subfaculty_query->fetchColumn();
}

function get_subfaculty_from_person_id($person_id)
{
	global $mthsc_db;
	$subfaculty_query = $mthsc_db->prepare('SELECT subfaculty_name FROM dept_info.primary_subfaculty INNER JOIN dept_info.subfaculties USING (subfaculty_id) WHERE person_id = ?');
	$subfaculty_query->execute(array($person_id));
	return $subfaculty_query->fetchColumn();
}



function get_current_evaluation_year()
{
	global $mthsc_db;
	$year_query = $mthsc_db->query("SELECT value FROM fpci_settings WHERE setting = 'current_evaluation_year';");
	return $year_query->fetchColumn();
}

function get_percentages($person_id,$year)
{
	global $mthsc_db;
	$percentages_query = $mthsc_db->prepare("SELECT * FROM fpci_percentages WHERE person_id = ? AND year = ?;");
	$percentages_query->execute(array($person_id,$year));
	return $percentages_query->fetch();
}

function get_percentages_from_entry_id($entry_id)
{
	global $mthsc_db;
	$percentages_query = $mthsc_db->prepare("SELECT * FROM fpci_percentages WHERE entry_id = ?;");
	$percentages_query->execute(array($entry_id));
	return $percentages_query->fetch();
}

function get_percentage_entry_cutoff()
{
	return strtotime('next year');
}

function term_ending_to_semester($term_ending)
{
	switch(substr($term_ending,-2))
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

function is_person_on_evaluation_list($person_id)
{
	global $mthsc_db;
	$check_query = $mthsc_db->prepare('SELECT 1 FROM dept_info.people_to_lists_link JOIN dept_info.lists USING (list_id) WHERE list_name = "Faculty Evaluation List" AND person_id = ?');
	$check_query->execute(array($person_id));
	return $check_query->fetchColumn(); 
}

function get_current_evaluation_list()
{
	global $mthsc_db;
	$list_query = $mthsc_db->query('SELECT person_id FROM dept_info.people_to_lists_link JOIN dept_info.lists USING (list_id) JOIN dept_info.person USING (person_id) WHERE list_name = "Faculty Evaluation List" ORDER BY last_name,first_name');
	return $list_query->fetchAll(PDO::FETCH_COLUMN);
}

function get_evaluation_from_percentage_entry_id($entry_id)
{
	global $mthsc_db;
	$evaluation_query = $mthsc_db->prepare('SELECT * FROM fpci_evaluations WHERE percentage_entry_id = ? LIMIT 1;');
	$evaluation_query->execute(array($entry_id));
	return $evaluation_query->fetch();
}

function is_evaluation_complete($entry_id)
{
	global $mthsc_db;
	$complete_query = $mthsc_db->prepare('SELECT 1 FROM `fpci_evaluations` WHERE percentage_entry_id = ? AND teaching_rating != 0 AND research_rating != 0 AND service_rating != 0 AND FAS_rating != "" AND FAS_evaluation != "";');
	$complete_query->execute(array($entry_id));
	$result = $complete_query->fetch(PDO::FETCH_COLUMN);
	if ($result != NULL){return true;}
	else {return false;}
}

?>