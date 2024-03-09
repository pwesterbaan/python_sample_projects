<?php

$accepting_submissions = true;
date_default_timezone_set('America/New_York');
$currentTime = strtotime('now');
//echo $currentTime;

if ($currentTime > mktime(23, 59, 59, 10, 3, 2016))
{
	$accepting_submissions = false;
}
$admins = array('HEDETNI');

//connects to the database, returns a semi-useful error if not accessible.
$link = mysql_connect("mthsc.clemson.edu", "forms", "d8ta_c0l");
if(!$link){
	echo "Could not connect to database.  Please try again later.";
	exit;
}
//selects the database
else{
	mysql_select_db("forms", $link);
}


mysql_set_charset("utf8-bin",$link);

//print_r($_POST);

if (isset($_SERVER['REMOTE_USER']))
{
	$user_id = strtoupper($_SERVER['REMOTE_USER']);
	$user_hash = md5($user_id.'survey-2');
}
	
	
if (isset($_POST['save']))
{
	//temporarily store the POST variables
	$evaluation = mysql_real_escape_string($_POST['evaluation']);
	$workload = mysql_real_escape_string($_POST['workload']);
	$tpr = mysql_real_escape_string($_POST['tpr']);
	$hiring = mysql_real_escape_string($_POST['hiring']);
	$budgeting = mysql_real_escape_string($_POST['budgeting']);
	$other = mysql_real_escape_string($_POST['other']);
	$associates = mysql_real_escape_string($_POST['associates']);
	$associates_number = mysql_real_escape_string($_POST['associates_number']);
	$duties = mysql_real_escape_string($_POST['duties']);
	$comments = mysql_real_escape_string($_POST['comments']);

	
	
	
	$saveResponses = mysql_query('INSERT INTO reorg_survey_2 (
		user_hash,
		evaluation,
		workload,
		tpr,
		hiring,
		budgeting,
		other,
		associates,
		associates_number,
		duties,
		comments
		) VALUES (
		"'.$user_hash.'",
		"'.$evaluation.'",
		"'.$workload.'",
		"'.$tpr.'",
		"'.$hiring.'",
		"'.$budgeting.'",
		"'.$other.'",
		"'.$associates.'",
		"'.$associates_number.'",
		"'.$duties.'",
		"'.$comments.'")
		ON DUPLICATE KEY UPDATE 
		evaluation = "'.$evaluation.'",
		workload = "'.$workload.'",
		tpr = "'.$tpr.'",
		hiring = "'.$hiring.'",
		budgeting = "'.$budgeting.'",
		other = "'.$other.'",
		associates = "'.$associates.'",
		associates_number = "'.$associates_number.'",
		duties = "'.$duties.'",
		comments = "'.$comments.'"');
	
	$success = $saveResponses;	
	
	if ($success)
	{
		$message = "Thank you. Your responses has been saved.";
		unset($_POST);
	}
	else
	{
		$message = "Sorry, something went wrong.<br>Your responses have not been saved.<br>Please try again.";
		$message .= "<br>".mysql_error($link).'<br>';
	}
	
}

//first check for requested id
if (isset($_GET['id']) && $_GET['id']!="")
{
	$getSubmission =  mysql_query('SELECT * FROM reorg_survey_2 WHERE id = "'.$_GET['id'].'" LIMIT 1;');
	if (!$getSubmission)
	{
		$message .= 'Error accessing database: ' . mysql_error($link).'<br>';
	}
}
else
{
	//no request, offer user's previous entries
	$getSubmission =  mysql_query('SELECT * FROM reorg_survey_2 WHERE user_hash = "'.$user_hash.'" LIMIT 1;');
	if (!$getSubmission)
	{
		$message .= 'Error accessing database: ' . mysql_error($link).'<br>';
	}
}

if ($getSubmission && mysql_num_rows($getSubmission) > 0) //user already submitted
{
	$eligible = true;
	$row = mysql_fetch_array($getSubmission);
	
	//store the responses to display
	$evaluation = $row['evaluation'];
	$workload = $row['workload'];
	$tpr = $row['tpr'];
	$hiring = $row['hiring'];
	$budgeting = $row['budgeting'];
	$other = $row['other'];
	$associates = $row['associates'];
	$associates_number = $row['associates_number'];
	$duties = $row['duties'];
	$comments = $row['comments'];

}
else
{
	
	//check elligibility and display blank form
	$person_id = 0;

	$personIDRequest = mysql_query('SELECT person_id FROM dept_info.person WHERE username="'.$user_id.'" LIMIT 1');
	if (!$personIDRequest)
	{
		$message .= 'Error fetching person id: '.mysql_error($link).'<br>';
		$eligible = false;
	}
	else
	{
		if (mysql_num_rows($personIDRequest) > 0)
		{
			//get person id
			$row = mysql_fetch_array($personIDRequest);
			$person_id = $row['person_id'];
			
			//check voting members list
			$listRequest = mysql_query('SELECT list_id FROM dept_info.people_to_lists_link where person_id = '.$person_id.' AND list_id=8');
			if (!$listRequest)
			{
				$message .= 'Error fetching list info: '.mysql_error($link).'<br>';
				$eligible_to_vote = false;
			}
			else
			{
				if (mysql_num_rows($listRequest) > 0) //they are listed as current faculty
				{
					$eligible = true;

					$evaluation = null;
					$workload = null;
					$tpr = null;
					$hiring = null;
					$budgeting = null;
					$other = null;
					$associates = null;
					$associates_number = null;
					$duties = null;
					$comments = null;
				}
				else if (in_array($user_id, $admins))
				{
					$eligible = true;

					$evaluation = null;
					$workload = null;
					$tpr = null;
					$hiring = null;
					$budgeting = null;
					$other = null;
					$associates = null;
					$associates_number = null;
					$duties = null;
					$comments = null;
				}
				else
				{
					$message = "This survey is intended only for voting members of the Mathematical Sciences department. Contact Kevin Hedetniemi if you think you should be eligible.";
					$eligible = false;
				}
			}
		}
		else
		{
			$message = "You are ineligible for this survey. Contact Kevin Hedetniemi if you think this is incorrect.";
			$eligible = false;
		}
	}
}


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Departmental Challenges Survey: Sept 2016</title>
	<meta name="author" content="hedetni">
	<!-- Date: 2016-07-06 -->
	
	<link rel="shortcut icon" href="/favicon.ico">
	
	<link rel="stylesheet" href="/style/math.css" type="text/css" media="screen" title="no title" charset="utf-8">
	
	<script src="jquery-1.7.2.js" type="text/javascript" charset="utf-8"></script>
	<script src="jquery.validate.js" type="text/javascript" charset="utf-8"></script>
	
	
	
<style type="text/css">

td.center {
	text-align:center;
}
table.h_question {
	width:75%;
	table-layout:fixed;
}
input[type=submit], input[type=reset] {
	font-size:2.5em;
}
</style>

<script type="text/javascript">

</script>

</head>
<body>
	<div id="main">
		<div id="header">
			<a href="http://www.clemson.edu/math" title="Department Home">
				<img src="/style/math_logo.png" alt="math department logo">
			</a>
		</div>

		<div id="content">
			<h1>Departmental Challenges Survey: Sept. 2016</h1>
			

			<?php echo isset($message) ? '<p id="error">'.$message.'</p>' : ""; ?>
	
			</center>

			<?php if ($accepting_submissions && $eligible): ?>
			
			<?php if (!isset($_GET['id'])): ?>
			<p>Dear colleagues,</p>
			<p>In the recent Mathematical Sciences reorganization survey, some items were identified as “challenges” within the department. For each challenge, please elaborate on the following:</p>
			
			<ol>
				<li>What particular concerns do you have (or have you had) about the way this is handled in the department?</li>
				<li>How would you recommend the department address these concerns? For instance, do you think that associate chair(s) and/or reorganization would help with this?</li>
			</ol>
			
			<p>Feel free to leave any items blank. After submitting your responses, any time before the submission deadline of Monday October 3, 2016, you may return to the survey to edit your responses.</p>
			
			<p>Your submission is stored along with a hashed identifier, so that you may return to this form if necessary and pick up where you left off. You must press the submit button at the bottom of the page for your responses to be saved. Your user id is not stored with your responses and therefore they will be presented anonymously.</p>
			<br>
			<?php endif; ?>
			
			<?php echo isset($_GET['id']) ? '<p><b>Submission #'.$_GET['id'].'</b></p>' : ""; ?>
			
			<form id="survey_form" name="survey_form" action="" method="post">

				<p>Challenges:</p>
				
				<p>A. Evaluation</p>
				<textarea name="evaluation" id="evaluation" cols="80" rows="5"><?php echo $evaluation; ?></textarea></p>
				
				<p>B. Workload</p>
				<textarea name="workload" id="workload" cols="80" rows="5"><?php echo $workload; ?></textarea></p>
				
				<p>C. TPR</p>
				<textarea name="tpr" id="tpr" cols="80" rows="5"><?php echo $tpr; ?></textarea></p>
				
				<p>D. Hiring</p>
				<textarea name="hiring" id="hiring" cols="80" rows="5"><?php echo $hiring; ?></textarea></p>
				
				<p>E. Budgeting</p>
				<textarea name="budgeting" id="budgeting" cols="80" rows="5"><?php echo $budgeting; ?></textarea></p>
				
				<p>F. Other (please specify)</p>
				<textarea name="other" id="other" cols="80" rows="5"><?php echo $other; ?></textarea></p>
				
				<br>
				
				<p>Are you in favor of creating one or more Associate Chair positions in the department? (choose one answer only)</p>
				<table class="v_question">
					<tr><td class="center"><input type="radio" name="associates" <?php echo $associates == "yes" ? "checked": ""; ?> value="yes"></td><td>Yes</td></tr>
					<tr><td class="center"><input type="radio" name="associates" <?php echo $associates == "no" ? "checked": ""; ?> value="no"></td><td>No</td></tr>
					<tr><td class="center"><input type="radio" name="associates" <?php echo $associates == "undecided" ? "checked": ""; ?> value="undecided"></td><td>Undecided</td></tr>
				</table>
				<br>
				
				<p>If you answered yes to the previous question:</p>
				<p>1. How many Associate Chair positions should be created?  (choose one answer only) </p>
				<table class="v_question">
					<tr><td class="center"><input type="radio" name="associates_number" <?php echo $associates_number == "1" ? "checked": ""; ?> value="1"></td><td>One</td></tr>
					<tr><td class="center"><input type="radio" name="associates_number" <?php echo $associates_number == "2" ? "checked": ""; ?> value="2"></td><td>Two</td></tr>
					<tr><td class="center"><input type="radio" name="associates_number" <?php echo $associates_number == "3" ? "checked": ""; ?> value="3"></td><td>Three</td></tr>
					<tr><td class="center"><input type="radio" name="associates_number" <?php echo $associates_number == "4" ? "checked": ""; ?> value="4"></td><td>Four</td></tr>
					<tr><td class="center"><input type="radio" name="associates_number" <?php echo $associates_number == "undecided" ? "checked": ""; ?> value="undecided"></td><td>Undecided</td></tr>
				</table>
				<br>
				
				<p>2. What should the duties of the Associate Chair(s) include? <br>
					<textarea name="duties" id="duties" cols="80" rows="5"><?php echo $duties; ?></textarea></p>
					
				<p>Other Comments: <br>
					<textarea name="comments" id="comments" cols="80" rows="5"><?php echo $comments; ?></textarea></p>


				</br>
				<center>
				<?php if (!isset($_GET['id'])): ?>
					<input type="submit" name="save" value="Submit/Save Responses">
					<input type="reset" name="reset" value="Reset Form"></center>
				<?php endif; ?>	
			</form>

			
			<?php elseif (!$accepting_submissions): ?>
				<p id="error">We are no longer accepting submissions for this survey.</p>
			<?php endif; ?>
		</div>
	</div>


</body>
</html>

