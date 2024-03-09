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
	$user_hash = md5($user_id.'school_survey42');
}

$query = $mthsc_db->prepare('SELECT submitted,user_hash FROM school_survey');
$query->execute();
$responders = $query->fetchAll();

$q4_options = array('School of Mathematical and Statistical Sciences',
					'School of Mathematics and Decision Science',
					'School of Mathematics, Statistics, and Operations Research',
					'School of Mathematical, Data, and Decision Sciences',
					'Other Name');

$q2_options = array('Recruitment of students',
					'Recruitment of faculty',
					'Development and retention of faculty',
					'Academic programs',
					'External funding for discovery',
					'Strategic partnerships (CU and external)',
					'Alignment with SciForward and CU Forward',
					'Recruitment and development of leadership of unit',
					'Strategic advancement of unit',
					'Clearer reflection of strengths to those external to unit',
					'Other Benefit');

$q3_options = array('Administrative layer between faculty and director',
					'Additional costs associated with new structure',
					'Division of duties and workload of staff',
					'Division of duties and workload of faculty leaders',
					'Loss of sub-faculty identity',
					'Loss of signature breadth',
					'Proposed pillars/programs are reflective of today not future growth',
					'Imbalance of pillars/programs in terms of number of faculty',
					'Creates more division and opposed to less',
					'Creation of new policies, procedures, bylaws (including TPR)',
					'Other Concern');

$q1_options = array('Structure A',
					'Structure B',
					'Structure C',
					'Other Structure');

$roles = array('Staff',
				'Lecturer/Senior Lecturer',
				'Associate Professor',
				'Assistant Professor',
				'Professor',
				'Prefer Not to Answer');

//get response data
$data_query = $mthsc_db->prepare('SELECT * FROM school_survey');
$data_query->execute();
$data = $data_query->fetchAll();

function cmp($a, $b)
{
    if ($a[1] == $b[1]) {
        return 0;
    }
    return ($a[1] > $b[1]) ? -1 : 1;
}

function cmp_q1_overall($a, $b)
{
    global $q1;
	if ($q1['overall'][$a]['rank'] == $q1['overall'][$b]['rank']) {
        return 0;
    }
    return ($q1['overall'][$a]['rank'] < $q1['overall'][$b]['rank']) ? -1 : 1;
}

function cmp_q2_overall($a, $b)
{
    global $q2;
	if ($q2['overall'][$a]['rank'] == $q2['overall'][$b]['rank']) {
        return 0;
    }
    return ($q2['overall'][$a]['rank'] < $q2['overall'][$b]['rank']) ? -1 : 1;
}

function cmp_q3_overall($a, $b)
{
    global $q3;
	if ($q3['overall'][$a]['rank'] == $q3['overall'][$b]['rank']) {
        return 0;
    }
    return ($q3['overall'][$a]['rank'] < $q3['overall'][$b]['rank']) ? -1 : 1;
}

function cmp_q4_overall($a, $b)
{
    global $q4;
	if ($q4['overall'][$a]['rank'] == $q4['overall'][$b]['rank']) {
        return 0;
    }
    return ($q4['overall'][$a]['rank'] < $q4['overall'][$b]['rank']) ? -1 : 1;
}

//==============
// Question One
//==============

$q1 = array();
//frequencies
foreach ($roles as $role)
{
	foreach ($q1_options as $q1_option)
	{
		for ($r=1;$r<=count($q1_options);$r++)
		{
			$column = 'question1_ranked_'.$r;
			$statement = 'SELECT '.$column.',count('.$column.') FROM `school_survey` WHERE role = ? GROUP BY '.$column;
			//echo $statement.'<br>';
			$query = $mthsc_db->prepare($statement);
			$query->execute(array($role));
			$frequencies = $query->fetchAll(PDO::FETCH_KEY_PAIR);
			foreach ($frequencies as $opt => $freq)
			{
				$q1[$role][$opt]['frequencies'][$r] = $freq;
			}
			if (!isset($q1[$role][$q1_option]['frequencies'][$r])){$q1[$role][$q1_option]['frequencies'][$r] = 0;}
		}
	}
}
//overall frequencies
foreach ($q1_options as $q1_option)
{
	for ($r=1;$r<=count($q1_options);$r++)
	{
		$column = 'question1_ranked_'.$r;
		$statement = 'SELECT '.$column.',count('.$column.') FROM `school_survey` GROUP BY '.$column;
		//echo $statement.'<br>';
		$query = $mthsc_db->prepare($statement);
		$query->execute();
		$frequencies = $query->fetchAll(PDO::FETCH_KEY_PAIR);
		foreach ($frequencies as $opt => $freq)
		{
			$q1['overall'][$opt]['frequencies'][$r] = $freq;
		}
		if (!isset($q1['overall'][$q1_option]['frequencies'][$r])){$q1['overall'][$q1_option]['frequencies'][$r] = 0;}
	}
}

//scores
foreach ($roles as $role)
{
	foreach ($q1_options as $q1_option)
	{
		$q1[$role][$q1_option]['score'] = 0;
		for ($r=1;$r<=count($q1_options);$r++)
		{
			$value = count($q1_options)-intval($r)+1;
			$fr = intval($q1[$role][$q1_option]['frequencies'][$r]);
			$temp_score = $value * $fr;
			$q1[$role][$q1_option]['score'] = $q1[$role][$q1_option]['score']+$temp_score;
		}
	}
}
//overall scores
foreach ($q1_options as $q1_option)
{
	$q1['overall'][$q1_option]['score'] = 0;
	for ($r=1;$r<=count($q1_options);$r++)
	{
		$value = count($q1_options)-intval($r)+1;
		$fr = intval($q1['overall'][$q1_option]['frequencies'][$r]);
		$temp_score = $value * $fr;
		$q1['overall'][$q1_option]['score'] = $q1['overall'][$q1_option]['score']+$temp_score;
	}
}

//ranks
foreach ($roles as $role)
{
	$scores = array();
	foreach ($q1_options as $q1_option)
	{
		$scores[] = array($q1_option, $q1[$role][$q1_option]['score']);
	}
	//sort
	usort($scores,"cmp");
	$last_score = -1;
	$last_rank = 0;
	foreach ($scores as $rank => $score)
	{
		if ($score[1] !== $last_score)
		{
			$q1[$role][$score[0]]['rank'] = $rank+1;
			$last_rank = $rank+1;
			$last_score = $score[1];
		}
		else
		{
			$q1[$role][$score[0]]['rank'] = $last_rank;
		}
		$last_score = $score[1];
	}
}
//overall ranks
$scores = array();
foreach ($q1_options as $q1_option)
{
	$scores[] = array($q1_option, $q1['overall'][$q1_option]['score']);
}
//sort
usort($scores,"cmp");
$last_score = -1;
$last_rank = 0;
foreach ($scores as $rank => $score)
{
	if ($score[1] !== $last_score)
	{
		$q1['overall'][$score[0]]['rank'] = $rank+1;
		$last_rank = $rank+1;
		$last_score = $score[1];
	}
	else
	{
		$q1['overall'][$score[0]]['rank'] = $last_rank;
	}
	$last_score = $score[1];
}
usort($q1_options,"cmp_q1_overall");

//==============
// Question Two
//==============

$q2 = array();
//frequencies
foreach ($roles as $role)
{
	foreach ($q2_options as $q2_option)
	{
		for ($r=1;$r<=count($q2_options);$r++)
		{
			$column = 'question2_ranked_'.$r;
			$statement = 'SELECT '.$column.',count('.$column.') FROM `school_survey` WHERE role = ? GROUP BY '.$column;
			//echo $statement.'<br>';
			$query = $mthsc_db->prepare($statement);
			$query->execute(array($role));
			$frequencies = $query->fetchAll(PDO::FETCH_KEY_PAIR);
			foreach ($frequencies as $opt => $freq)
			{
				$q2[$role][$opt]['frequencies'][$r] = $freq;
			}
			if (!isset($q2[$role][$q2_option]['frequencies'][$r])){$q2[$role][$q2_option]['frequencies'][$r] = 0;}
		}
	}
}
//overall frequencies
foreach ($q2_options as $q2_option)
{
	for ($r=1;$r<=count($q2_options);$r++)
	{
		$column = 'question2_ranked_'.$r;
		$statement = 'SELECT '.$column.',count('.$column.') FROM `school_survey` GROUP BY '.$column;
		//echo $statement.'<br>';
		$query = $mthsc_db->prepare($statement);
		$query->execute();
		$frequencies = $query->fetchAll(PDO::FETCH_KEY_PAIR);
		foreach ($frequencies as $opt => $freq)
		{
			$q2['overall'][$opt]['frequencies'][$r] = $freq;
		}
		if (!isset($q2['overall'][$q2_option]['frequencies'][$r])){$q2['overall'][$q2_option]['frequencies'][$r] = 0;}
	}
}
//scores
foreach ($roles as $role)
{
	foreach ($q2_options as $q2_option)
	{
		$q2[$role][$q2_option]['score'] = 0;
		for ($r=1;$r<=count($q2_options);$r++)
		{
			$value = count($q2_options)-intval($r)+1;
			$fr = intval($q2[$role][$q2_option]['frequencies'][$r]);
			$temp_score = $value * $fr;
			$q2[$role][$q2_option]['score'] = $q2[$role][$q2_option]['score']+$temp_score;
		}
	}
}
//overall scores
foreach ($q2_options as $q2_option)
{
	$q2['overall'][$q2_option]['score'] = 0;
	for ($r=1;$r<=count($q2_options);$r++)
	{
		$value = count($q2_options)-intval($r)+1;
		$fr = intval($q2['overall'][$q2_option]['frequencies'][$r]);
		$temp_score = $value * $fr;
		$q2['overall'][$q2_option]['score'] = $q2['overall'][$q2_option]['score']+$temp_score;
	}
}
//ranks
foreach ($roles as $role)
{
	$scores = array();
	foreach ($q2_options as $q2_option)
	{
		$scores[] = array($q2_option, $q2[$role][$q2_option]['score']);
	}
	//sort
	usort($scores,"cmp");
	$last_score = -1;
	$last_rank = 0;
	foreach ($scores as $rank => $score)
	{
		if ($score[1] !== $last_score)
		{
			$q2[$role][$score[0]]['rank'] = $rank+1;
			$last_rank = $rank+1;
			$last_score = $score[1];
		}
		else
		{
			$q2[$role][$score[0]]['rank'] = $last_rank;
		}
		$last_score = $score[1];
	}
}
//overall ranks
$scores = array();
foreach ($q2_options as $q2_option)
{
	$scores[] = array($q2_option, $q2['overall'][$q2_option]['score']);
}
//sort
usort($scores,"cmp");
$last_score = -1;
$last_rank = 0;
foreach ($scores as $rank => $score)
{
	if ($score[1] !== $last_score)
	{
		$q2['overall'][$score[0]]['rank'] = $rank+1;
		$last_rank = $rank+1;
		$last_score = $score[1];
	}
	else
	{
		$q2['overall'][$score[0]]['rank'] = $last_rank;
	}
	$last_score = $score[1];
}
usort($q2_options,"cmp_q2_overall");
//echo '<pre>';
//print_r($q2['overall']);
//print_r($scores);
//echo '</pre>';

//===============
// Question Three
//===============

$q3 = array();
//frequencies
foreach ($roles as $role)
{
	foreach ($q3_options as $q3_option)
	{
		for ($r=1;$r<=count($q3_options);$r++)
		{
			$column = 'question3_ranked_'.$r;
			$statement = 'SELECT '.$column.',count('.$column.') FROM `school_survey` WHERE role = ? GROUP BY '.$column;
			//echo $statement.'<br>';
			$query = $mthsc_db->prepare($statement);
			$query->execute(array($role));
			$frequencies = $query->fetchAll(PDO::FETCH_KEY_PAIR);
			foreach ($frequencies as $opt => $freq)
			{
				$q3[$role][$opt]['frequencies'][$r] = $freq;
			}
			if (!isset($q3[$role][$q3_option]['frequencies'][$r])){$q3[$role][$q3_option]['frequencies'][$r] = 0;}
		}
	}
}
//overall frequencies
foreach ($q3_options as $q3_option)
{
	for ($r=1;$r<=count($q3_options);$r++)
	{
		$column = 'question3_ranked_'.$r;
		$statement = 'SELECT '.$column.',count('.$column.') FROM `school_survey` GROUP BY '.$column;
		//echo $statement.'<br>';
		$query = $mthsc_db->prepare($statement);
		$query->execute();
		$frequencies = $query->fetchAll(PDO::FETCH_KEY_PAIR);
		foreach ($frequencies as $opt => $freq)
		{
			$q3['overall'][$opt]['frequencies'][$r] = $freq;
		}
		if (!isset($q3['overall'][$q3_option]['frequencies'][$r])){$q3['overall'][$q3_option]['frequencies'][$r] = 0;}
	}
}
//scores
foreach ($roles as $role)
{
	foreach ($q3_options as $q3_option)
	{
		$q3[$role][$q3_option]['score'] = 0;
		for ($r=1;$r<=count($q3_options);$r++)
		{
			$value = count($q3_options)-intval($r)+1;
			$fr = intval($q3[$role][$q3_option]['frequencies'][$r]);
			$temp_score = $value * $fr;
			$q3[$role][$q3_option]['score'] = $q3[$role][$q3_option]['score']+$temp_score;
		}
	}
}
//overall scores
foreach ($q3_options as $q3_option)
{
	$q3['overall'][$q3_option]['score'] = 0;
	for ($r=1;$r<=count($q3_options);$r++)
	{
		$value = count($q3_options)-intval($r)+1;
		$fr = intval($q3['overall'][$q3_option]['frequencies'][$r]);
		$temp_score = $value * $fr;
		$q3['overall'][$q3_option]['score'] = $q3['overall'][$q3_option]['score']+$temp_score;
	}
}
//ranks
foreach ($roles as $role)
{
	$scores = array();
	foreach ($q3_options as $q3_option)
	{
		$scores[] = array($q3_option, $q3[$role][$q3_option]['score']);
	}
	//sort
	usort($scores,"cmp");
	$last_score = -1;
	$last_rank = 0;
	foreach ($scores as $rank => $score)
	{
		if ($score[1] !== $last_score)
		{
			$q3[$role][$score[0]]['rank'] = $rank+1;
			$last_rank = $rank+1;
			$last_score = $score[1];
		}
		else
		{
			$q3[$role][$score[0]]['rank'] = $last_rank;
		}
		$last_score = $score[1];
	}
}
//overall ranks
$scores = array();
foreach ($q3_options as $q3_option)
{
	$scores[] = array($q3_option, $q3['overall'][$q3_option]['score']);
}
//sort
usort($scores,"cmp");
$last_score = -1;
$last_rank = 0;
foreach ($scores as $rank => $score)
{
	if ($score[1] !== $last_score)
	{
		$q3['overall'][$score[0]]['rank'] = $rank+1;
		$last_rank = $rank+1;
		$last_score = $score[1];
	}
	else
	{
		$q3['overall'][$score[0]]['rank'] = $last_rank;
	}
	$last_score = $score[1];
}
usort($q3_options,"cmp_q3_overall");


//===============
// Question Four
//===============

$q4 = array();
//frequencies
foreach ($roles as $role)
{
	foreach ($q4_options as $q4_option)
	{
		for ($r=1;$r<=count($q4_options);$r++)
		{
			$column = 'question4_ranked_'.$r;
			$statement = 'SELECT '.$column.',count('.$column.') FROM `school_survey` WHERE role = ? GROUP BY '.$column;
			//echo $statement.'<br>';
			$query = $mthsc_db->prepare($statement);
			$query->execute(array($role));
			$frequencies = $query->fetchAll(PDO::FETCH_KEY_PAIR);
			foreach ($frequencies as $opt => $freq)
			{
				$q4[$role][$opt]['frequencies'][$r] = $freq;
			}
			if (!isset($q4[$role][$q4_option]['frequencies'][$r])){$q4[$role][$q4_option]['frequencies'][$r] = 0;}
		}
	}
}
//overall frequencies
foreach ($q4_options as $q4_option)
{
	for ($r=1;$r<=count($q4_options);$r++)
	{
		$column = 'question4_ranked_'.$r;
		$statement = 'SELECT '.$column.',count('.$column.') FROM `school_survey` GROUP BY '.$column;
		//echo $statement.'<br>';
		$query = $mthsc_db->prepare($statement);
		$query->execute();
		$frequencies = $query->fetchAll(PDO::FETCH_KEY_PAIR);
		foreach ($frequencies as $opt => $freq)
		{
			$q4['overall'][$opt]['frequencies'][$r] = $freq;
		}
		if (!isset($q4['overall'][$q4_option]['frequencies'][$r])){$q4['overall'][$q4_option]['frequencies'][$r] = 0;}
	}
}
//scores
foreach ($roles as $role)
{
	foreach ($q4_options as $q4_option)
	{
		$q4[$role][$q4_option]['score'] = 0;
		for ($r=1;$r<=count($q4_options);$r++)
		{
			$value = count($q4_options)-intval($r)+1;
			$fr = intval($q4[$role][$q4_option]['frequencies'][$r]);
			$temp_score = $value * $fr;
			$q4[$role][$q4_option]['score'] = $q4[$role][$q4_option]['score']+$temp_score;
		}
	}
}
//overall scores
foreach ($q4_options as $q4_option)
{
	$q4['overall'][$q4_option]['score'] = 0;
	for ($r=1;$r<=count($q4_options);$r++)
	{
		$value = count($q4_options)-intval($r)+1;
		$fr = intval($q4['overall'][$q4_option]['frequencies'][$r]);
		$temp_score = $value * $fr;
		$q4['overall'][$q4_option]['score'] = $q4['overall'][$q4_option]['score']+$temp_score;
	}
}
//ranks
foreach ($roles as $role)
{
	$scores = array();
	foreach ($q4_options as $q4_option)
	{
		$scores[] = array($q4_option, $q4[$role][$q4_option]['score']);
	}
	//sort
	usort($scores,"cmp");
	$last_score = -1;
	$last_rank = 0;
	foreach ($scores as $rank => $score)
	{
		if ($score[1] !== $last_score)
		{
			$q4[$role][$score[0]]['rank'] = $rank+1;
			$last_rank = $rank+1;
			$last_score = $score[1];
		}
		else
		{
			$q4[$role][$score[0]]['rank'] = $last_rank;
		}
		$last_score = $score[1];
	}
}
//overall ranks
$scores = array();
foreach ($q4_options as $q4_option)
{
	$scores[] = array($q4_option, $q4['overall'][$q4_option]['score']);
}
//sort
usort($scores,"cmp");
$last_score = -1;
$last_rank = 0;
foreach ($scores as $rank => $score)
{
	if ($score[1] !== $last_score)
	{
		$q4['overall'][$score[0]]['rank'] = $rank+1;
		$last_rank = $rank+1;
		$last_score = $score[1];
	}
	else
	{
		$q4['overall'][$score[0]]['rank'] = $last_rank;
	}
	$last_score = $score[1];
}
usort($q4_options,"cmp_q4_overall");

//other structures
$other_structure_query = $mthsc_db->prepare('SELECT other_structure FROM school_survey WHERE other_structure != "";');
$other_structure_query->execute();
$other_structures = $other_structure_query->fetchAll(PDO::FETCH_COLUMN);

//question 2 others 
$question2_other_query = $mthsc_db->prepare('SELECT question2_other FROM school_survey WHERE question2_other != "";');
$question2_other_query->execute();
$question2_others = $question2_other_query->fetchAll(PDO::FETCH_COLUMN);

//question 3 others 
$question3_other_query = $mthsc_db->prepare('SELECT question3_other FROM school_survey WHERE question3_other != "";');
$question3_other_query->execute();
$question3_others = $question3_other_query->fetchAll(PDO::FETCH_COLUMN);

//question 4 others 
$question4_other_query = $mthsc_db->prepare('SELECT question4_other FROM school_survey WHERE question4_other != "";');
$question4_other_query->execute();
$question4_others = $question4_other_query->fetchAll(PDO::FETCH_COLUMN);


//echo '<pre>';
//print_r($q1_options);
//echo '</pre>'

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Review Submission</title>
	<meta name="author" content="hedetni">
	<!-- Date: 2018-M-D -->

<link rel="stylesheet" href="/style/math.css" type="text/css" media="screen" title="no title" charset="utf-8">

<style type="text/css">
table.ranking_options {
	margin-left:1.5em;
	margin-top:0.5em;
}
table.ranking_options {
	cursor:pointer;
}
textarea {
	margin-left:1.5em;
}
label {
	margin-left:1.5em;
}
td.ranking {
	text-align:center;
}
td.handle {border:none;background-color:transparent;}
.ui-state-highlight { height: 1.5em; line-height: 1.2em; }
h3 {margin-top:2em;}
.center {text-align:center;}
.score {color:gray;}
div.other {
	border:1px solid lightgray;
	padding:0.25em;
	margin:0.25em;
}
</style>

<script src="/style/jquery-3.2.1.min.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">
$(document).ready(function() 
{
		
	
});
</script>

</head>
<body>
	<div id="main">
		<div id="header">
			<a href="http://www.clemson.edu/math" title="Department Home">
				<img src="/style/science-math-logo-white.png" height="73px" alt="math department logo">
			</a>
			<h1><a href="index.php">Mathematical Sciences School Survey</a></h1>
		</div>
	
		<div id="content">
			<h1>Mathematical Sciences School Survey</h1>
			<h2>Question Analysis</h2>
			<h3>Question One</h3>
			<table>
				<tr>
					<th>Option</th>
					<th colspan="2">Overall</th>
					<th colspan="2">Staff</th>
					<th colspan="2">Lecturer/Senior Lecturer</th>
					<th colspan="2">Associate Prof</th>
					<th colspan="2">Assistant Prof</th>
					<th colspan="2">Professor</th>
					<th colspan="2">Prefer Not to Answer</th>
				</tr>
				<tr>
					<th></th>
					<th>Score</th><th>Rank</th>
					<th>Score</th><th>Rank</th>
					<th>Score</th><th>Rank</th>
					<th>Score</th><th>Rank</th>
					<th>Score</th><th>Rank</th>
					<th>Score</th><th>Rank</th>
					<th>Score</th><th>Rank</th>
				</tr>
				<?php foreach ($q1_options as $q1_option): ?>
					<tr>
						<td><?php echo $q1_option; ?></td>
						<td class="center score"><?php echo $q1['overall'][$q1_option]['score']; ?></td><td class="center"><?php echo $q1['overall'][$q1_option]['rank']; ?></td>
						<td class="center score"><?php echo $q1['Staff'][$q1_option]['score']; ?></td><td class="center"><?php echo $q1['Staff'][$q1_option]['rank']; ?></td>
						<td class="center score"><?php echo $q1['Lecturer/Senior Lecturer'][$q1_option]['score']; ?></td><td class="center"><?php echo $q1['Lecturer/Senior Lecturer'][$q1_option]['rank']; ?></td>
						<td class="center score"><?php echo $q1['Associate Professor'][$q1_option]['score']; ?></td><td class="center"><?php echo $q1['Associate Professor'][$q1_option]['rank']; ?></td>
						<td class="center score"><?php echo $q1['Assistant Professor'][$q1_option]['score']; ?></td><td class="center"><?php echo $q1['Assistant Professor'][$q1_option]['rank']; ?></td>
						<td class="center score"><?php echo $q1['Professor'][$q1_option]['score']; ?></td><td class="center"><?php echo $q1['Professor'][$q1_option]['rank']; ?></td>
						<td class="center score"><?php echo $q1['Prefer Not to Answer'][$q1_option]['score']; ?></td><td class="center"><?php echo $q1['Prefer Not to Answer'][$q1_option]['rank']; ?></td>
					</tr>
				<?php endforeach; ?>
			</table>
			<p><strong>Other Structures:</strong></p>
			<?php foreach ($other_structures as $other_structure): ?>
				<div class="other"><?php echo $other_structure; ?></div>
			<?php endforeach;?>
			
			<h3>Question Two</h3>
			<table>
				<tr>
					<th>Option</th>
					<th colspan="2">Overall</th>
					<th colspan="2">Staff</th>
					<th colspan="2">Lecturer/Senior Lecturer</th>
					<th colspan="2">Associate Prof</th>
					<th colspan="2">Assistant Prof</th>
					<th colspan="2">Professor</th>
					<th colspan="2">Prefer Not to Answer</th>
				</tr>
				<tr>
					<th></th>
					<th>Score</th><th>Rank</th>
					<th>Score</th><th>Rank</th>
					<th>Score</th><th>Rank</th>
					<th>Score</th><th>Rank</th>
					<th>Score</th><th>Rank</th>
					<th>Score</th><th>Rank</th>
					<th>Score</th><th>Rank</th>
				</tr>
				<?php foreach ($q2_options as $q2_option): ?>
					<tr>
						<td><?php echo $q2_option; ?></td>
						<td class="center score"><?php echo $q2['overall'][$q2_option]['score']; ?></td><td class="center"><?php echo $q2['overall'][$q2_option]['rank']; ?></td>
						<td class="center score"><?php echo $q2['Staff'][$q2_option]['score']; ?></td><td class="center"><?php echo $q2['Staff'][$q2_option]['rank']; ?></td>
						<td class="center score"><?php echo $q2['Lecturer/Senior Lecturer'][$q2_option]['score']; ?></td><td class="center"><?php echo $q2['Lecturer/Senior Lecturer'][$q2_option]['rank']; ?></td>
						<td class="center score"><?php echo $q2['Associate Professor'][$q2_option]['score']; ?></td><td class="center"><?php echo $q2['Associate Professor'][$q2_option]['rank']; ?></td>
						<td class="center score"><?php echo $q2['Assistant Professor'][$q2_option]['score']; ?></td><td class="center"><?php echo $q2['Assistant Professor'][$q2_option]['rank']; ?></td>
						<td class="center score"><?php echo $q2['Professor'][$q2_option]['score']; ?></td><td class="center"><?php echo $q2['Professor'][$q2_option]['rank']; ?></td>
						<td class="center score"><?php echo $q2['Prefer Not to Answer'][$q2_option]['score']; ?></td><td class="center"><?php echo $q2['Prefer Not to Answer'][$q2_option]['rank']; ?></td>
					</tr>
				<?php endforeach; ?>
			</table>
			<p><strong>Other Benefits:</strong></p>
			<?php foreach ($question2_others as $question2_other): ?>
				<div class="other"><?php echo $question2_other; ?></div>
			<?php endforeach;?>
			
			<h3>Question Three</h3>
			<table>
				<tr>
					<th>Option</th>
					<th colspan="2">Overall</th>
					<th colspan="2">Staff</th>
					<th colspan="2">Lecturer/Senior Lecturer</th>
					<th colspan="2">Associate Prof</th>
					<th colspan="2">Assistant Prof</th>
					<th colspan="2">Professor</th>
					<th colspan="2">Prefer Not to Answer</th>
				</tr>
				<tr>
					<th></th>
					<th>Score</th><th>Rank</th>
					<th>Score</th><th>Rank</th>
					<th>Score</th><th>Rank</th>
					<th>Score</th><th>Rank</th>
					<th>Score</th><th>Rank</th>
					<th>Score</th><th>Rank</th>
					<th>Score</th><th>Rank</th>
				</tr>
				<?php foreach ($q3_options as $q3_option): ?>
					<tr>
						<td><?php echo $q3_option; ?></td>
						<td class="center score"><?php echo $q3['overall'][$q3_option]['score']; ?></td><td class="center"><?php echo $q3['overall'][$q3_option]['rank']; ?></td>
						<td class="center score"><?php echo $q3['Staff'][$q3_option]['score']; ?></td><td class="center"><?php echo $q3['Staff'][$q3_option]['rank']; ?></td>
						<td class="center score"><?php echo $q3['Lecturer/Senior Lecturer'][$q3_option]['score']; ?></td><td class="center"><?php echo $q3['Lecturer/Senior Lecturer'][$q3_option]['rank']; ?></td>
						<td class="center score"><?php echo $q3['Associate Professor'][$q3_option]['score']; ?></td><td class="center"><?php echo $q3['Associate Professor'][$q3_option]['rank']; ?></td>
						<td class="center score"><?php echo $q3['Assistant Professor'][$q3_option]['score']; ?></td><td class="center"><?php echo $q3['Assistant Professor'][$q3_option]['rank']; ?></td>
						<td class="center score"><?php echo $q3['Professor'][$q3_option]['score']; ?></td><td class="center"><?php echo $q3['Professor'][$q3_option]['rank']; ?></td>
						<td class="center score"><?php echo $q3['Prefer Not to Answer'][$q3_option]['score']; ?></td><td class="center"><?php echo $q3['Prefer Not to Answer'][$q3_option]['rank']; ?></td>
					</tr>
				<?php endforeach; ?>
			</table>
			<p><strong>Other Concerns:</strong></p>
			<?php foreach ($question3_others as $question3_other): ?>
				<div class="other"><?php echo $question3_other; ?></div>
			<?php endforeach;?>
			
			<h3>Question Four</h3>
			<table>
				<tr>
					<th>Option</th>
					<th colspan="2">Overall</th>
					<th colspan="2">Staff</th>
					<th colspan="2">Lecturer/Senior Lecturer</th>
					<th colspan="2">Associate Prof</th>
					<th colspan="2">Assistant Prof</th>
					<th colspan="2">Professor</th>
					<th colspan="2">Prefer Not to Answer</th>
				</tr>
				<tr>
					<th></th>
					<th>Score</th><th>Rank</th>
					<th>Score</th><th>Rank</th>
					<th>Score</th><th>Rank</th>
					<th>Score</th><th>Rank</th>
					<th>Score</th><th>Rank</th>
					<th>Score</th><th>Rank</th>
					<th>Score</th><th>Rank</th>
				</tr>
				<?php foreach ($q4_options as $q4_option): ?>
					<tr>
						<td><?php echo $q4_option; ?></td>
						<td class="center score"><?php echo $q4['overall'][$q4_option]['score']; ?></td><td class="center"><?php echo $q4['overall'][$q4_option]['rank']; ?></td>
						<td class="center score"><?php echo $q4['Staff'][$q4_option]['score']; ?></td><td class="center"><?php echo $q4['Staff'][$q4_option]['rank']; ?></td>
						<td class="center score"><?php echo $q4['Lecturer/Senior Lecturer'][$q4_option]['score']; ?></td><td class="center"><?php echo $q4['Lecturer/Senior Lecturer'][$q4_option]['rank']; ?></td>
						<td class="center score"><?php echo $q4['Associate Professor'][$q4_option]['score']; ?></td><td class="center"><?php echo $q4['Associate Professor'][$q4_option]['rank']; ?></td>
						<td class="center score"><?php echo $q4['Assistant Professor'][$q4_option]['score']; ?></td><td class="center"><?php echo $q4['Assistant Professor'][$q4_option]['rank']; ?></td>
						<td class="center score"><?php echo $q4['Professor'][$q4_option]['score']; ?></td><td class="center"><?php echo $q4['Professor'][$q4_option]['rank']; ?></td>
						<td class="center score"><?php echo $q4['Prefer Not to Answer'][$q4_option]['score']; ?></td><td class="center"><?php echo $q4['Prefer Not to Answer'][$q4_option]['rank']; ?></td>
					</tr>
				<?php endforeach; ?>
			</table>
			<p><strong>Other Names:</strong></p>
			<?php foreach ($question4_others as $question4_other): ?>
				<div class="other"><?php echo $question4_other; ?></div>
			<?php endforeach;?>
			<br><br>
			
			
			<h2>Review Individual Responses</h2>
			<p>Click on a user hash below to view their responses</p>
			
			<?php if (isset($responders) && count($responders)>0): ?>
			
			<table>
				<tr>
					<th>User</th>
					<th>Submitted</td>
				</tr>
				<?php foreach ($responders as $responder): ?>
					<tr>
						<td><a href="review.php?id=<?php echo $responder['user_hash'];?>"><?php echo $responder['user_hash'];?></a></td>
						<td><?php echo $responder['submitted'];?></td>
					</tr>
				<?php endforeach; ?>
			</table>
				
			<?php endif; ?>
			
		</div>	
		<div id="footer">Click <a href="/dept_forms/website_error.py" target="_blank">here</a> to report a problem with this website.</div>
	</div>
</body>
</html>