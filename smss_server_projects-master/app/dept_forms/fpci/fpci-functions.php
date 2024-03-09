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

//this list determines what links people see in the nav, and gives them access on admin pages
$admin_list = array('HEDETNI','KEVJA','LCALLA','CGALLAG','REBHOLZ','MANGANM','JDYKEN');

//these lists determine what faculty members admins see on the review faculty page (and thus which ones they can evaluate)
$evaluators = array('Mathematics Division' => array('REBHOLZ','KEVJA','LCALLA','HEDETNI'),
					'Statistics and Operations Research Division' => array('CGALLAG','KEVJA','LCALLA','HEDETNI'),
					'Mathematics and Statistics Education Division' => array('MANGANM','KEVJA','REBHOLZ','JDYKEN','LCALLA','HEDETNI'),
					'Not Set' => array('KEVJA','LCALLA','HEDETNI'),
					'Director' => array('KEVJA','LCALLA','HEDETNI'));

$allow_edits_to_previous_years_evaluations = true;

function get_nav()
{
	global $admin_list;
	global $evaluators;
	global $user_id;
	$nav = '<li><a href="index.php">Home</a></li>';
	$nav .= '<li><a href="activity-percentages.php">Activity Percentage Entry</a></li>';
	$nav .= '<li><a href="view-ratings.php">View Ratings</a></li>';
	if (in_array($user_id,$admin_list))
	{
		$nav .= '<li><a href="admin-view-faculty.php">Admin View Faculty</a></li>';
		$nav .= '<li><a href="admin-enter-teaching-loads.php">Admin Teaching Loads</a></li>';
		$nav .= '<li><a href="admin-view-stats.php">Admin View Stats</a></li>';
	}
	if (in_array($user_id,$evaluators['Director']))
	{
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
	$person_query = $mthsc_db->prepare('SELECT CONCAT(IF(NOT suffix IS NULL AND suffix != "", CONCAT_WS(" ",last_name,suffix), last_name),", ", IF(NOT display_name IS NULL AND display_name != "", display_name, first_name)) FROM dept_info.person WHERE person_id = ? LIMIT 1');
	$person_query->execute(array($person_id));
	return $person_query->fetchColumn();
}

function get_username_from_person_id($person_id)
{
	global $mthsc_db;
	$person_query = $mthsc_db->prepare('SELECT username FROM dept_info.person WHERE person_id = ? LIMIT 1');
	$person_query->execute(array($person_id));
	return $person_query->fetchColumn();
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
	global $mthsc_db;
	$year_query = $mthsc_db->query("SELECT value FROM fpci_settings WHERE setting = 'percentage_entry_cutoff';");
	$cutoff = $year_query->fetchColumn();
	if ($cutoff > strtotime('now'))
	return $cutoff;
	else
	{return 0;}
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

function get_evaluations_for_person_id($person_id)
{
	global $mthsc_db;
	$evaluations_query = $mthsc_db->prepare('SELECT entry_id,p.year,p.person_id,p.user_id,division,overall_teaching_percentage,overall_research_percentage,overall_service_percentage,teaching_rating,teaching_score,service_rating,service_score,research_rating,research_score,total_score,display_to_instructor FROM `fpci_percentages` p JOIN `fpci_evaluations` e on entry_id = e.percentage_entry_id WHERE p.person_id = ? ORDER BY year DESC');
	$evaluations_query->execute(array($person_id));
	return $evaluations_query->fetchAll();
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

function get_averages_for_division($division,$year)
{
	global $mthsc_db;
	$averages_query = $mthsc_db->prepare('SELECT round(AVG(teaching_rating),2) as average_teaching_rating,round(AVG(research_rating),2) as average_research_rating,round(AVG(service_rating),2) as average_service_rating,round(AVG(total_score),2) as average_overall_score FROM `fpci_evaluations` as eval INNER JOIN fpci_percentages on percentage_entry_id = entry_id WHERE division = ? AND eval.year = ?;');
	$averages_query->execute(array($division,$year));
	return $averages_query->fetch();
}

// accepts 1D array of numbers
// returns median
function calculate_median($a)
{
	// sort the array		  
	sort($a);
	
	// determine if odd or even
	$count = count($a);
	$odd = $count % 2;
	$index = floor($count / 2); // get midpoint
	if ($odd == 1)
	{
		$median = $a[$index];
	}
	else
	{
		$next_index = $index - 1;
		$median = ($a[$index] + $a[$next_index]) / 2;
	}

	return round($median,2);
}

function get_medians_for_division($division,$year)
{
	global $mthsc_db;
	//---------------
	// get scores
	//---------------
	//teaching
	$teaching_query = $mthsc_db->prepare('SELECT teaching_rating FROM `fpci_evaluations` as eval INNER JOIN fpci_percentages on percentage_entry_id = entry_id WHERE division = ? AND eval.year = ? AND teaching_rating != 0.00 ORDER BY teaching_rating ASC;');
	$teaching_query->execute(array($division,$year));
	$teaching_ratings = $teaching_query->fetchAll(PDO::FETCH_COLUMN);
	
	//research
	$research_query = $mthsc_db->prepare('SELECT research_rating FROM `fpci_evaluations` as eval INNER JOIN fpci_percentages on percentage_entry_id = entry_id WHERE division = ? AND eval.year = ? AND research_rating != 0.00 ORDER BY research_rating ASC;');
	$research_query->execute(array($division,$year));
	$research_ratings = $research_query->fetchAll(PDO::FETCH_COLUMN);
	
	//service
	$service_query = $mthsc_db->prepare('SELECT service_rating FROM `fpci_evaluations` as eval INNER JOIN fpci_percentages on percentage_entry_id = entry_id WHERE division = ? AND eval.year = ? AND service_rating != 0.00 ORDER BY service_rating ASC;');
	$service_query->execute(array($division,$year));
	$service_ratings = $service_query->fetchAll(PDO::FETCH_COLUMN);
	
	//overall
	$overall_query = $mthsc_db->prepare('SELECT total_score FROM `fpci_evaluations` as eval INNER JOIN fpci_percentages on percentage_entry_id = entry_id WHERE division = ? AND eval.year = ? AND total_score != 0.00 ORDER BY total_score ASC;');
	$overall_query->execute(array($division,$year));
	$overall_ratings = $overall_query->fetchAll(PDO::FETCH_COLUMN);

	//---------------
	// calculate medians
	//---------------
	$medians = array();
	$medians['median_teaching_rating'] = calculate_median($teaching_ratings);
	$medians['median_research_rating'] = calculate_median($research_ratings);
	$medians['median_service_rating'] = calculate_median($service_ratings);
	$medians['median_overall_rating'] = calculate_median($overall_ratings);
	
	return $medians;
}

function get_score_counts_for_division($division,$year)
{
	global $mthsc_db;
	$categories = array("teaching_rating", "research_rating", "service_rating", "total_score");
	$counts = array();
	for ($c = 0; $c < count($categories); $c++)
	{
		for ($i = 0; $i < 7; $i++)
		{
			$iplus = $i+1;
			$rating_column = $categories[$c];
			$counts_query = $mthsc_db->prepare('SELECT count(*) FROM `fpci_evaluations` as eval INNER JOIN fpci_percentages on percentage_entry_id = entry_id WHERE division = ? AND eval.year = ? AND '.$rating_column.' > ? AND '.$rating_column.' <= ?');
			$counts_query->execute(array($division,$year, $i, $iplus));
			$counts[$categories[$c]][$i.'-'.$iplus] = $counts_query->fetch(PDO::FETCH_COLUMN);
		}
	}
	return $counts;
}

function get_all_evaluation_years()
{
	global $mthsc_db;
	$years_query = $mthsc_db->query('SELECT year from fpci_evaluations GROUP BY year ORDER BY year DESC;');
	return $years_query->fetchAll(PDO::FETCH_COLUMN);
}

// returns person id
function get_all_people_with_percentages()
{
	global $mthsc_db;
	$list_query = $mthsc_db->query('SELECT DISTINCT(person_id) FROM fpci_percentages JOIN dept_info.person USING (person_id) ORDER BY last_name,first_name');
	return $list_query->fetchAll(PDO::FETCH_COLUMN);
}

function get_all_teaching_loads()
{
	global $mthsc_db;
	
	//$teaching_loads_people = $mthsc_db->prepare('SELECT DISTINCT(person_id) FROM fpci_teaching_loads');
	//$people = $teaching_loads_people->fetchAll(PDO::FETCH_COLUMN);
	
	$teaching_loads_query = $mthsc_db->query('SELECT person_id,year,teaching_load,teaching_load_id FROM fpci_teaching_loads ORDER BY person_id, year DESC');
	$teaching_loads_by_person = $teaching_loads_query->fetchAll(PDO::FETCH_GROUP);
	
	$teaching_loads = array();
	foreach ($teaching_loads_by_person as $person_id => $loads)
	{
		$teaching_loads[$person_id] = array();
		foreach ($loads as $load)
		{
			$teaching_loads[$person_id][$load['year']] = $load['teaching_load'];
		}
	}
	
	return $teaching_loads;
}

?>