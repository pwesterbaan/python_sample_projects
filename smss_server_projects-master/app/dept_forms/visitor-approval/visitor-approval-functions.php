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

//$host = '192.168.202.108';
$science_host = 'mysql.ces.clemson.edu';
$science_db   = 'webscience_people';
$science_user = 'webscience_people';
$science_pass = 'mns@*zj9ea2^';

$science_dsn = 'mysql:host='.$science_host.';dbname='.$science_db.';charset='.$charset;
$science_db = new PDO($science_dsn, $science_user, $science_pass, $opt);



if (isset($_SERVER['REMOTE_USER']))
{
	$user_id = strtolower($_SERVER['REMOTE_USER']);
	$fullName = $_SERVER['fullName'];
	$in_math = is_user_in_math($user_id);
}
date_default_timezone_set('America/New_York');

//this list determines what links people see in the nav, and gives them access on admin pages
$admin_list = array('hedetni','kevja','lcalla','ahayne','keshias','vmcclai');
$notification_list = array('kevja@clemson.edu','lcalla@clemson.edu','keshias@clemson.edu','ahayne@clemson.edu');
//$notification_list = array('hedetni@clemson.edu');

function get_nav()
{
	global $admin_list;
	global $user_id;
	$nav = '<li><a href="index.php">My Visitor Requests</a></li>';
	$nav .= '<li><a href="policy.php">Submit New Visitor Request</a></li>';
	if (in_array($user_id,$admin_list))
	{
		$nav .= '<li><a href="admin-view-requests.php">Admin View Visitor Requests</a></li>';
	}
	return $nav;
}

function get_name_from_username_hub($username)
{
	global $science_db;
	
	$clean_username = clean_username($username);
	
	$query = $science_db->prepare("SELECT CONCAT(IF(preferred_name = '',first_name,preferred_name),' ',last_name) FROM people WHERE username = ?");
	$query->execute(array($clean_username));
	return $query->fetch(PDO::FETCH_COLUMN);
}

function clean_username($username_string)
{
	$clean_string = preg_replace('/[^A-Za-z0-9]/', '', $username_string);
	return strtolower(substr($clean_string,0,9));
}

function is_user_in_math($username)
{
	global $science_db;
	
	$query = $science_db->prepare('SELECT 1 FROM person_to_dept_link WHERE username = ? AND department_id = 5 LIMIT 1');
	$query->execute(array($username));
	return $query->fetchColumn();
}

function get_request_details($request_id)
{
	global $mthsc_db;
	$query = $mthsc_db->prepare('SELECT * FROM visitor_approval WHERE request_id = ?');
	$query->execute(array($request_id));
	return $query->fetch();
}

function get_speaker_request_details($request_id)
{
	global $mthsc_db;
	$query = $mthsc_db->prepare('SELECT * FROM speaker_request WHERE request_id = ?');
	$query->execute(array($request_id));
	return $query->fetch();
}

function get_comments_for_request($request_id)
{
	global $mthsc_db;
	$get_comments_query = $mthsc_db->prepare('SELECT * FROM visitor_approval_comments WHERE request_id = ?;');
	$get_comments_query->execute(array($request_id));
	$comments = $get_comments_query->fetchAll();
	
	return $comments;
}


?>