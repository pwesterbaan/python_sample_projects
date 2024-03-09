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

if (isset($_SERVER['REMOTE_USER']))
{
	$user_id = strtoupper($_SERVER['REMOTE_USER']);
}
date_default_timezone_set('America/New_York');


function get_nav()
{
	$nav = '<li><a href="/gs-info/index.php">Students</a></li>';
	$nav .= '<li><a href="/gs-info/assistantships.php">Assistantships</a></li>';
	$nav .= '<li><a href="/gs-info/assignments.php">Assignments</a></li>';
	$nav .= '<li><a href="/gs-info/term_stats.php">Term Stats</a></li>';
	$nav .= '<li><a href="/gs-info/reports/index.php">Reports</a></li>';
	$nav .= '<li><a href="../dept-info/index.php">Department Info</a></li>';
	return $nav;
}

//returns associative array of all entries in person table
function get_all_g_students()
{
	global $mthsc_db;
	$query = $mthsc_db->query('SELECT * FROM dept_info.dept_info.roles INNER JOIN dept_info.person on roles.person_id = person.person_id WHERE username !="\-\-" AND role = "Student" ORDER BY last_name ASC');
	return $query->fetchAll();
}

//returns associative array of simple details from all entries in person table
function get_all_g_students_list()
{
	global $mthsc_db;
	$query = $mthsc_db->query('SELECT roles.person_id,IF(NOT pref_name IS NULL AND pref_name != "", pref_name, first_name) AS first_name, IF(NOT suffix IS NULL AND suffix != "", CONCAT_WS(" ",last_name,suffix), last_name) AS last_name,username,IF(status IS NULL, "",status) as status FROM dept_info.roles INNER JOIN dept_info.person on roles.person_id = person.person_id LEFT JOIN student_profile on person.person_id = student_profile.person_id WHERE username !="\-\-" AND role = "Student" ORDER BY last_name ASC');
	return $query->fetchAll();
}

//returns associative array of all students with enrolled, temp leave or incoming status
function get_all_active_students()
{
	global $mthsc_db;
	$query = $mthsc_db->query('SELECT * FROM student_profile INNER JOIN dept_info.person on student_profile.person_id = person.person_id WHERE status="Enrolled" OR status="Temporary Leave" OR status="Incoming" ORDER BY last_name ASC');
	return $query->fetchAll();
}

//returns associative array of all students with enrolled, temp leave or incoming status
function get_filtered_students($status_id)
{
	global $mthsc_db;
	$status_to_find = get_status_from_status_id($status_id);
	$query = $mthsc_db->prepare('SELECT * FROM student_profile INNER JOIN dept_info.person on student_profile.person_id = person.person_id WHERE status = ? ORDER BY last_name ASC');
	$query->execute(array($status_to_find));
	return $query->fetchAll();
}

function get_gtr_eligible_students()
{
	global $mthsc_db;
	$query = $mthsc_db->prepare('SELECT sp.person_id,username,IF(NOT display_name IS NULL AND display_name != "", display_name, first_name) AS first_name, IF(NOT suffix IS NULL AND suffix != "", CONCAT_WS(" ",last_name,suffix), last_name) AS last_name,GTR_eligible,eligible_to_work FROM student_profile sp INNER JOIN dept_info.person on sp.person_id = person.person_id WHERE status = "Enrolled" AND GTR_eligible = 1 ORDER BY last_name ASC');
	$query->execute();
	return $query->fetchAll();
}

function get_degree_programs($person_id)
{
	global $mthsc_db;
	$query = $mthsc_db->prepare('SELECT * FROM degree_programs WHERE person_id = ? ORDER BY start_year DESC');
	$query->execute(array($person_id));
	return $query->fetchAll();
}

function get_latest_degree_program($person_id)
{
	global $mthsc_db;
	$query = $mthsc_db->prepare('SELECT IF(program="PhD*",CONCAT(program," (",cur_degree,")"),program) FROM degree_programs WHERE person_id = ? ORDER BY start_year DESC LIMIT 1');
	$query->execute(array($person_id));
	return $query->fetch(PDO::FETCH_COLUMN);
}

function get_advisors($person_id)
{
	global $mthsc_db;
	$query = $mthsc_db->prepare('SELECT advisor_id,advisor_type,advisee_person_id,advisor_person_id,first_name,last_name FROM advisors JOIN dept_info.person ON advisor_person_id = dept_info.person.person_id WHERE advisee_person_id = ? ORDER BY advisor_type DESC');
	$query->execute(array($person_id));
	return $query->fetchAll();
}

function get_benchmarks($person_id)
{
	global $mthsc_db;
	$query = $mthsc_db->prepare('SELECT * FROM benchmarks JOIN benchmark_types ON benchmarks.benchmark_type_id = benchmark_types.benchmark_type_id WHERE person_id = ? ORDER BY attempt_date DESC');
	$query->execute(array($person_id));
	return $query->fetchAll();
}

function get_prelim_attempts($person_id)
{
	global $mthsc_db;
	$query = $mthsc_db->prepare('SELECT * FROM prelim_attempts WHERE person_id = ? ORDER BY year DESC,term ASC');
	$query->execute(array($person_id));
	return $query->fetchAll();
}

function get_comp_attempts($person_id)
{
	global $mthsc_db;
	$query = $mthsc_db->prepare('SELECT * FROM comp_attempts WHERE person_id = ? ORDER BY date_attempted DESC');
	$query->execute(array($person_id));
	return $query->fetchAll();
}

function get_assistantships($person_id)
{
	global $mthsc_db;
	$query = $mthsc_db->prepare('SELECT * FROM assistantships JOIN support_types ON support_type = support_type_id WHERE person_id = ? ORDER BY term DESC');
	$query->execute(array($person_id));
	return $query->fetchAll();
}

function get_assignments($person_id)
{
	global $mthsc_db;
	$query = $mthsc_db->prepare('SELECT assignment_id,g.assistantship_id,assignment_type_id,assignment_category,types.description,g.course_id,IF(NOT course_num IS NULL,CONCAT(prefix," ",course_num),"N/A")  as course,g.hours,g.faculty_supervisor_id,CONCAT(IF(NOT suffix IS NULL AND suffix != "", CONCAT_WS(" ",last_name,suffix), last_name),", ",IF(NOT pref_name IS NULL AND pref_name != "", pref_name, first_name)) as faculty_supervisor, g.notes,g.last_updated,term FROM `assignments` g JOIN assistantships p ON g.assistantship_id = p.assistantship_id LEFT JOIN course.course_list ON g.course_id = course_list.course_id JOIN assignment_types types ON g.assignment_type_id = types.assignment_category_id JOIN dept_info.person person ON person.person_id = g.faculty_supervisor_id WHERE g.person_id = ? ORDER BY term DESC');
	$query->execute(array($person_id));
	return $query->fetchAll();
}

function get_mthsc_faculty()
{
	global $mthsc_db;
	$query = $mthsc_db->query('SELECT roles.person_id, IF(NOT pref_name IS NULL AND pref_name != "", pref_name, first_name) AS first_name, IF(NOT suffix IS NULL AND suffix != "", CONCAT_WS(" ",last_name,suffix), last_name) AS last_name,username FROM dept_info.roles INNER JOIN dept_info.person on roles.person_id = person.person_id WHERE username !="\-\-" AND role = "Faculty" ORDER BY last_name ASC');
	return $query->fetchAll();
}

function get_school_list()
{
	global $mthsc_db;
	$query = $mthsc_db->query('SELECT * FROM dept_info.schools ORDER BY name');
	return $query->fetchAll();
}

function get_gre_score($person_id)
{
	global $mthsc_db;
	$query = $mthsc_db->prepare('SELECT * FROM GRE_scores WHERE person_id = ?');
	$query->execute(array($person_id));
	return $query->fetch();
}

function get_student_profile($person_id)
{
	global $mthsc_db;
	$query = $mthsc_db->prepare('SELECT * FROM student_profile WHERE person_id = ?');
	$query->execute(array($person_id));
	return $query->fetch();
}

function get_notes_for_student($person_id)
{
	global $mthsc_db;
	$query = $mthsc_db->prepare('SELECT note_id,note,CONCAT(IF(NOT pref_name IS NULL AND pref_name != "", pref_name, first_name)," ", IF(NOT suffix IS NULL AND suffix != "", CONCAT_WS(" ",last_name,suffix), last_name)) AS left_by,left_by_person_id,submitted FROM notes JOIN dept_info.person ON person_id = left_by_person_id WHERE student_person_id = ?');
	$query->execute(array($person_id));
	return $query->fetchAll();
}

function get_gtr_eligibility($CESP_status,$GTR_hours_met)
{
	if ($CESP_status == "N/A" || $CESP_status == "P+" || $CESP_status == "P" || $CESP_status == "CP")
	{
		if ($GTR_hours_met)
		{
			return true;
		}
		else {return false;}
	}
	else {return false;}
}

function get_mthsc_courses()
{
	global $mthsc_db;
	$query = $mthsc_db->query('SELECT course_id,CONCAT(prefix," ",course_num) as course FROM course.course_list ORDER BY course');
	return $query->fetchAll();
}

function get_support_types()
{
	global $mthsc_db;
	$query = $mthsc_db->query('SELECT * FROM support_types');
	return $query->fetchAll();
}

function get_assignment_types()
{
	global $mthsc_db;
	$query = $mthsc_db->query('SELECT * FROM assignment_types');
	return $query->fetchAll();
}

function get_assistantship_terms()
{
	//get terms which have assistantships
	global $mthsc_db;
	$available_terms_query = $mthsc_db->query('SELECT term FROM assistantships GROUP BY term ORDER BY term DESC');
	$available_terms = $available_terms_query->fetchAll(PDO::FETCH_COLUMN);
	return $available_terms;
}

function get_employment_categories()
{
	global $mthsc_db;
	$query = $mthsc_db->query('SELECT * FROM employment_categories');
	return $query->fetchAll();
}

function get_employment_category_from_id($category_id)
{
	global $mthsc_db;
	$query = $mthsc_db->prepare('SELECT category FROM employment_categories WHERE emp_cat_id = ?');
	$query->execute(array($category_id));
	return $query->fetchColumn();
}

function get_employment_records($person_id)
{
	global $mthsc_db;
	$query = $mthsc_db->prepare('SELECT * FROM employment WHERE person_id = ?');
	$query->execute(array($person_id));
	return $query->fetchAll();
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

function get_status_options()
{
	global $mthsc_db;
	$query = $mthsc_db->query('SELECT * FROM gs_info.status_options;');
	$statuses = $query->fetchAll();
	return $statuses;
}

function get_status_from_status_id($id)
{
	global $mthsc_db;
	$query = $mthsc_db->prepare('SELECT status FROM gs_info.status_options WHERE status_option_id = ?;');
	$query->execute(array($id));
	$status = $query->fetchColumn();
	return $status;
}

///////////////////////
//
// DEPT INFO FUNCTIONS
//
///////////////////////

//returns associative array of all entries in roles table
function get_all_roles()
{
	global $mthsc_db;
	$query = $mthsc_db->query('SELECT person_id,role FROM dept_info.roles ORDER BY role DESC');
	$roles_data = $query->fetchAll();
	$roles = array();
	foreach ($roles_data as $role)
	{
		$roles[$role['person_id']][] = $role['role'];
	}
	return $roles;
}

//accepts: person id
//returns: associative array of details from person table
function get_person_details($person_id)
{
	global $mthsc_db;
	$query = $mthsc_db->prepare('SELECT * FROM dept_info.person WHERE person_id = ? LIMIT 1');
	$query->execute(array($person_id));
	return $query->fetch();
}

//accepts: person id
//returns: associative array of details from emails table
function get_emails($person_id)
{
	global $mthsc_db;
	$query = $mthsc_db->prepare('SELECT * FROM dept_info.email_addresses WHERE person_id = ?');
	$query->execute(array($person_id));
	return $query->fetchAll();
}

//accepts: person id
//returns: associative array of offices for that person
function get_offices($person_id)
{
	global $mthsc_db;
	$query = $mthsc_db->prepare('SELECT pol.id AS link_id, o.office_id, o.description, o.office_type FROM dept_info.people_to_offices_link AS pol INNER JOIN dept_info.offices AS o ON o.office_id = pol.office_id WHERE person_id = ? ORDER BY o.description');
	$query->execute(array($person_id));
	return $query->fetchAll();
}

//accepts: person id
//returns: associative array of offices for that person
function get_education_list($person_id)
{
	global $mthsc_db;
	$query = $mthsc_db->prepare('SELECT ed.education_id, ed.school_id, ed.semester, IF(ed.year = 0, "",ed.year) as year, ed.degree, ed.major, IF(ed.final_gpa = -1, "", ed.final_gpa) AS final_gpa, s.name AS school,s.city,s.state,s.country FROM dept_info.education AS ed LEFT JOIN dept_info.schools AS s ON ed.school_id = s.school_id WHERE person_id = ? ORDER BY year DESC, FIELD(ed.semester, "fall", "summer II", "summer I", "spring"), FIELD(ed.degree, "PhD", "MA", "ME", "MEd", "MS", "AB", "BA", "BS"), ed.major');
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

//returns: array of lists the person is a member of
function get_lists($person_id)
{
	global $mthsc_db;
	$query = $mthsc_db->prepare('SELECT lists.list_id, list_name
FROM  dept_info.people_to_lists_link 
JOIN dept_info.lists ON people_to_lists_link.list_id = lists.list_id
WHERE person_id = ?');
	$query->execute(array($person_id));
	return $query->fetchAll();
}

//accepts: person id
//returns: associative array of details from roles table
function get_roles($person_id)
{
	global $mthsc_db;
	$query = $mthsc_db->prepare('SELECT role FROM dept_info.roles WHERE person_id = ?');
	$query->execute(array($person_id));
	return $query->fetchAll(PDO::FETCH_COLUMN);
}

//accepts: person id
//returns: associative array of details from emails table
function get_addresses($person_id)
{
	global $mthsc_db;
	$query = $mthsc_db->prepare('SELECT * FROM dept_info.mail_addresses WHERE person_id = ?');
	$query->execute(array($person_id));
	return $query->fetchAll();
}

function get_position_list()
{
	global $mthsc_db;
	$query = $mthsc_db->query('SELECT * FROM dept_info.positions ORDER BY position');
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

function get_person_id_from_user_id($username)
{
	global $mthsc_db;
	$query = $mthsc_db->prepare('SELECT person_id FROM dept_info.person WHERE username = ? LIMIT 1');
	$query->execute(array($username));
	return $query->fetchColumn();
}

?>