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

$candidates = array(
	'a' => 'Dr. Kevin James',
	'b' => 'Dr. Pete Kiessler',
	'c' => 'Dr. Leo Rebholz');
	
$ranking_questions = array(
	'2' => 'Ability to lead the mathematical sciences department effectively',
	'3' => 'Ability to lead the mathematical sciences department transparently',
	'4' => 'Vision',
	'5' => 'Ability to foster a collegial departmental environment',
	'6' => 'Ability to evaluate faculty and staff honestly and fairly, including TPR',
	'7' => 'Ability to manage faculty and staff effectively and fairly, including workload',
	'8' => 'Ability to work with faculty, staff, and students toward departmental goals',
	'9' => 'Ability to coordinate potential departmental restructuring',
	'10' => 'Ability to manage departmental growth',
	'11' => 'Ability to manage departmental budget',
	'12' => 'Ability to organize meetings',
	'13' => 'Ability to represent department to college and university administration and to other units');

function isInMath($user_id)
{
	global $mthsc_db;
	global $privileged;
	$stmt = $mthsc_db->prepare("SELECT 1 FROM `dept_info`.`people_to_lists_link` as pll JOIN `dept_info`.`person` as p on pll.person_id = p.person_id WHERE list_id=10 AND username = ?");
	$stmt->execute(array($user_id));
	$userExists = $stmt->fetchColumn();
	if ($userExists){return true;}
	else
	{
		if (in_array(strtoupper($user_id),$privileged))
		{
			return true;
		}
		else
		{
			return false;
		}
	}	
}

function has_submitted($user_hash)
{
	global $mthsc_db;
	$stmt = $mthsc_db->prepare("SELECT submitted FROM `interim_chair_survey` WHERE user_hash = ? ");
	$stmt->execute(array($user_hash));
	$has_submitted = $stmt->fetchColumn();
	if ($has_submitted){return $has_submitted;}
	else {return false;}
}

$user_id = "";
if (isset($_SERVER['REMOTE_USER']))
{
	$user_id = strtoupper($_SERVER['REMOTE_USER']);
	$user_hash = md5($user_id.'interim-chair_survey99');
}

if (isset($_POST['submit_survey']) && isInMath($user_id))
{
	$responses = $_POST;
	unset($responses['submit_survey']);
	$responses['user_hash'] = $user_hash;
	
	if (!isset($responses['role']))
	{
		$responses['role'] = "Prefer Not to Answer";
	}

	foreach ($ranking_questions as $number => $question)
	{
		foreach ($candidates as $cand => $candidate)
		{
			if (!isset($responses['q'.$number.'_'.$cand]))
			{
				$responses['q'.$number.'_'.$cand] = "";
			}
		}
	}
	
	//echo '<pre>';
	//print_r($responses);
	//echo '</pre>';
	
	//insert data
	
	$insert = $mthsc_db->prepare('INSERT IGNORE INTO interim_chair_survey (user_hash,role,question1_ranked_1,question1_ranked_2,question1_ranked_3,q2_a,q2_b,q3_a,q3_b,q4_a,q4_b,q5_a,q5_b,q6_a,q6_b,q7_a,q7_b,q8_a,q8_b,q9_a,q9_b,q10_a,q10_b,q11_a,q11_b,q12_a,q12_b,q13_a,q13_b,q14_a,q14_b,q15_a,q15_b,q16_a,q16_b,q2_c,q3_c,q4_c,q5_c,q6_c,q7_c,q8_c,q9_c,q10_c,q11_c,q12_c,q13_c,q14_c,q15_c,q16_c) VALUES (:user_hash,:role,:question1_ranked_1,:question1_ranked_2,:question1_ranked_3,:q2_a,:q2_b,:q3_a,:q3_b,:q4_a,:q4_b,:q5_a,:q5_b,:q6_a,:q6_b,:q7_a,:q7_b,:q8_a,:q8_b,:q9_a,:q9_b,:q10_a,:q10_b,:q11_a,:q11_b,:q12_a,:q12_b,:q13_a,:q13_b,:q14_a,:q14_b,:q15_a,:q15_b,:q16_a,:q16_b,:q2_c,:q3_c,:q4_c,:q5_c,:q6_c,:q7_c,:q8_c,:q9_c,:q10_c,:q11_c,:q12_c,:q13_c,:q14_c,:q15_c,:q16_c)');
	$insert->execute($responses);
	$result = $insert->rowCount();
	
}
else if (isset($_GET['id']))
{
	$survey_id = $_GET['id'];
	$query = $mthsc_db->prepare('SELECT * FROM interim_chair_survey WHERE user_hash = ? LIMIT 1');
	$query->execute(array($survey_id));
	$responses = $query->fetch();
	$responses['lookup'] = true;
	$result = 1;
}


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
div.comment {
	margin-left:1.5em;border:1px solid gray;padding:0.5em;
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
			<h1><a href="index.php">Interim Chair Survey</a></h1>
		</div>
	
		<div id="content">
			<?php if (isset($result) && $result > 0): ?>
			
				<?php if (isset($responses) && count($responses)>1): ?>
				
				<?php echo isset($responses['lookup']) ? '<p>Responses from <strong>'.$responses['user_hash'].'</strong></p>' : '<h1>Thank you</h1><h2>Your responses have been saved.</h2>' ?>
				
				
				<?php endif; ?>
			<?php else: ?>
				<p>You may only submit this form once. Your initial responses have not been changed.</p>
				
			<?php endif; ?>
		</div>	
		<div id="footer">Click <a href="/dept_forms/website_error.py" target="_blank">here</a> to report a problem with this website.</div>
	</div>
</body>
</html>