<?php

$host = 'mthsc.clemson.edu';
$db   = 'gs_info';
$user = 'math_gs_info';
$pass = 'gr@d00';
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

$year = date('Y',strtotime('now'));
if (date('n') >= 4 || date('n') <= 6)
{
	$season = 'spring';
}
if (date('n') <= 2 || date('n') == 12)
{
	$season = 'fall';
}
$current_term = '202201';
$advisor_review_term = '202201';

function get_nav()
{
	return '<li><a href="index.php">Students to Review</a></li>';
}


//accepts person_id, gets names and user id
function get_person_info($student_person_id)
{
	global $mthsc_db;
	$info_query = $mthsc_db->prepare('SELECT IF(NOT display_name IS NULL AND display_name != "", display_name, first_name) AS first_name, IF(NOT suffix IS NULL AND suffix != "", CONCAT_WS(" ",last_name,suffix), last_name) AS last_name,person_id,username FROM dept_info.person WHERE person_id = ? LIMIT 1');
	$info_query->execute(array($student_person_id));
	$person_info = $info_query->fetch(PDO::FETCH_ASSOC);
	if (count($person_info) > 0)
	{
		return $person_info;
	}
	else {return false;}
}

function get_person_id_from_assignment_id($assignment_id)
{
	global $mthsc_db;
	$person_query = $mthsc_db->prepare('SELECT person_id FROM assignments WHERE assignment_id = ?;');
	$person_query->execute(array($assignment_id));
	$person_id = $person_query->fetchColumn();
	if ($person_id != 0)
	{return $person_id;}
	else
	{return 0;}
}

function get_person_id_from_advisor_id($assignment_id)
{
	global $mthsc_db;
	$person_query = $mthsc_db->prepare('SELECT advisee_person_id FROM advisors WHERE advisor_id = ?;');
	$person_query->execute(array($assignment_id));
	$person_id = $person_query->fetchColumn();
	if ($person_id != 0)
	{return $person_id;}
	else
	{return 0;}
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

//accepts a reviewer's person id, returns an array of all students who they should review
function get_students_to_review($evaluator_person_id)
{
	$year = date('Y',strtotime('now'));
	$last_year = $year-1;
	$summer_term = $last_year.'05';
	$fall_term = $last_year.'08';
	$spring_term = $year.'01';
	
	//advisees
	global $mthsc_db;
	$advisee_query = $mthsc_db->prepare('SELECT advisee_person_id as person_id,advisor_type,IF(NOT display_name IS NULL AND display_name != "", display_name, first_name) AS first_name, IF(NOT suffix IS NULL AND suffix != "", CONCAT_WS(" ",last_name,suffix), last_name) AS last_name,"advisee" as review_type FROM `advisors` JOIN dept_info.person on advisee_person_id = dept_info.person.person_id JOIN student_profile sp ON advisee_person_id = sp.person_id JOIN degree_programs dp ON advisee_person_id = dp.person_id WHERE sp.status = "Enrolled" AND advisor_person_id = ? AND advisor_type = dp.cur_degree');
	$advisee_query->execute(array($evaluator_person_id));
	$advisees = $advisee_query->fetchAll();
	
	//assignments
	$assignee_query = $mthsc_db->prepare('SELECT sign.person_id,IF(NOT display_name IS NULL AND display_name != "", display_name, first_name) AS first_name, IF(NOT suffix IS NULL AND suffix != "", CONCAT_WS(" ",last_name,suffix), last_name) AS last_name,ship.term,sign.assignment_id,ast.assignment_category,CONCAT(prefix," ",course_num) as course,"assignment" as review_type FROM `assignments` sign JOIN dept_info.person p ON sign.person_id = p.person_id JOIN assistantships ship ON ship.person_id = sign.person_id JOIN assignment_types ast ON sign.assignment_type_id = ast.assignment_category_id LEFT JOIN course.course_list cl ON sign.course_id = cl.course_id WHERE faculty_supervisor_id = ? AND ship.term IN (?,?,?) AND sign.assistantship_id = ship.assistantship_id');
	$assignee_query->execute(array($evaluator_person_id,$summer_term,$fall_term,$spring_term));
	$assignees = $assignee_query->fetchAll();
	
	$students_to_review = array();
	foreach ($advisees as $advisee)
	{
		$students_to_review[] = $advisee;
	}
	foreach ($assignees as $assignee)
	{
		$students_to_review[] = $assignee;
	}
	//echo '<pre>';print_r($students_to_review);echo '</pre>';
	return $students_to_review;
}

function get_advisees_to_review($evaluator_person_id)
{
	//advisees
	global $mthsc_db;
	global $advisor_review_term;
	$advisee_query = $mthsc_db->prepare('SELECT advisor_id,advisee_person_id as person_id,advisor_type,IF(NOT display_name IS NULL AND display_name != "", display_name, first_name) AS first_name, IF(NOT suffix IS NULL AND suffix != "", CONCAT_WS(" ",last_name,suffix), last_name) AS last_name,"advisee" as review_type FROM `advisors` JOIN dept_info.person on advisee_person_id = dept_info.person.person_id JOIN student_profile sp ON advisee_person_id = sp.person_id JOIN degree_programs dp ON advisee_person_id = dp.person_id WHERE sp.status = "Enrolled" AND advisor_person_id = ? AND advisor_type = dp.cur_degree  AND dp.start_term <= ? AND dp.end_term >= ?');
	$advisee_query->execute(array($evaluator_person_id, $advisor_review_term, $advisor_review_term));
	$advisees = $advisee_query->fetchAll(PDO::FETCH_UNIQUE);
	
	return $advisees;
}

//accepts a reviewer's person id, returns an array of all students who have been reviewed by that evaluator
function get_reviewed_advisees($evaluator_person_id,$term)
{
	global $mthsc_db;
	$reviewed_query = $mthsc_db->prepare('SELECT advisor_id,student_person_id,review_id,date_submitted FROM advisor_reviews WHERE reviewer_person_id = ? AND term = ?;');
	$reviewed_query->execute(array($evaluator_person_id,$term));
	$reviewed_students = $reviewed_query->fetchAll(PDO::FETCH_UNIQUE);
	return $reviewed_students;
}

function get_assignments_to_review($evaluator_person_id,$term)
{
	//assignments
	global $mthsc_db;
	$assignment_query = $mthsc_db->prepare('SELECT sign.assignment_id,sign.person_id,IF(NOT display_name IS NULL AND display_name != "", display_name, first_name) AS first_name, IF(NOT suffix IS NULL AND suffix != "", CONCAT_WS(" ",last_name,suffix), last_name) AS last_name,ship.term,ast.assignment_category,CONCAT(prefix," ",course_num,"-",section) as course,"assignment" as review_type FROM `assignments` sign JOIN dept_info.person p ON sign.person_id = p.person_id JOIN assistantships ship ON ship.person_id = sign.person_id JOIN assignment_types ast ON sign.assignment_type_id = ast.assignment_category_id LEFT JOIN course.course_list cl ON sign.course_id = cl.course_id WHERE faculty_supervisor_id = ? AND ship.term = ? AND sign.assistantship_id = ship.assistantship_id');
	$assignment_query->execute(array($evaluator_person_id,$term));
	$assignments = $assignment_query->fetchAll(PDO::FETCH_UNIQUE);
	return $assignments;
}

//accepts a reviewer's person id, returns an array of all students who have been reviewed by that evaluator
function get_reviewed_assignments($evaluator_person_id)
{
	global $mthsc_db;
	$reviewed_query = $mthsc_db->prepare('SELECT assignment_id,student_person_id,review_id,date_submitted FROM assignment_reviews WHERE reviewer_person_id = ?;');
	$reviewed_query->execute(array($evaluator_person_id));
	$reviewed_assignments = $reviewed_query->fetchAll(PDO::FETCH_UNIQUE);
	return $reviewed_assignments;
}

function get_assignment_info($assignment_id)
{
	global $mthsc_db;
	$assignment_query = $mthsc_db->prepare('SELECT sign.assignment_id,sign.person_id,IF(NOT display_name IS NULL AND display_name != "", display_name, first_name) AS first_name, IF(NOT suffix IS NULL AND suffix != "", CONCAT_WS(" ",last_name,suffix), last_name) AS last_name,ship.term,ast.assignment_category,CONCAT(prefix," ",course_num) as course,"assignment" as review_type,sign.faculty_supervisor_id FROM `assignments` sign JOIN dept_info.person p ON sign.faculty_supervisor_id = p.person_id JOIN assistantships ship ON ship.person_id = sign.person_id JOIN assignment_types ast ON sign.assignment_type_id = ast.assignment_category_id LEFT JOIN course.course_list cl ON sign.course_id = cl.course_id WHERE sign.assistantship_id = ship.assistantship_id AND assignment_id = ?');
	$assignment_query->execute(array($assignment_id));
	$assignment_info = $assignment_query->fetch(PDO::FETCH_ASSOC);
	return $assignment_info;
}


//accepts a reviewer's person id, returns an array of all students who have been reviewed by that evaluator
function get_reviewed_students($evaluator_person_id)
{
	global $mthsc_db;
	$reviewed_query = $mthsc_db->prepare('SELECT assignment_id,student_person_id,review_id,date_submitted FROM reviews WHERE reviewer_person_id = ?;');
	$reviewed_query->execute(array(date("Y"),$evaluator_person_id));
	$reviewed_students = $reviewed_query->fetchAll(PDO::FETCH_GROUP);
	return $reviewed_students;
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

?>