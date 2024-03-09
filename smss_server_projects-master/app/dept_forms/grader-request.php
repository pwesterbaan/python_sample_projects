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

$admins = array('HEDETNI', 'JDYKEN','REBHOLZ', 'PGERARD');

$user_id = "";
$user_fullname = "";
if (isset($_SERVER['REMOTE_USER']))
{
	$user_id = strtoupper($_SERVER['REMOTE_USER']);
}
if (isset($_SERVER['fullName']))
{
	$user_fullname = $_SERVER['fullName'];
}

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
			$term = $year.'08';
			break;
		case '06':
			$term = $year.'08';
			break;
		case '07':
			$term = $year.'08';
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
			$next_semester = '08';
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

if (isset($_POST['submit']))
{
	$entry = $_POST;
	unset($entry['submit']);
	unset($entry['reset']);
	
	$saveRequest = $mthsc_db->prepare('INSERT INTO grader_req (user_id,term,duties,hours,student,comments) VALUES (:user_id,:term,:duties,:hours,:student,:comments);');
	$success = $saveRequest->execute($entry);	
	
	if ($success)
	{
		$message = "Thank you. Your request has been submitted.";
		unset($_POST);
	}
	else
	{
		$message = "Sorry, something went wrong.<br>Your request has not been submitted.<br>Please try again.";
	}
	
}

function get_person_id_from_user_id($username)
{
	global $mthsc_db;
	$query = $mthsc_db->prepare('SELECT person_id FROM dept_info.person WHERE username = ? LIMIT 1');
	$query->execute(array($username));
	return $query->fetchColumn();
}

//get eligibility

//get open/closed status
$requests_open_query = $mthsc_db->query('SELECT value FROM settings WHERE name = "grader_requests_open"');
$requests_open = $requests_open_query->fetchColumn();

$person_id = get_person_id_from_user_id($user_id);

if ($person_id)
{
	//check current faculty list
	$list_query = $mthsc_db->prepare('SELECT 1 FROM dept_info.people_to_lists_link where person_id = ? AND list_id=2');
	$list_result = $list_query->execute(array($person_id));
	$is_math_faculty = $list_query->fetchColumn();
	
	if ($is_math_faculty) //they are listed as current faculty
	{
		$eligible_to_vote = true;
		
		//get previous submission history
		$submissions_query = $mthsc_db->prepare('SELECT * FROM `grader_req` WHERE user_id = ? ORDER BY submitted DESC');
		$submissions_result = $submissions_query->execute(array($user_id));
		$submissions = $submissions_query->fetchAll();
	}
	else if (in_array($user_id, $admins))
	{
		$eligible_to_vote = true;
		
		$submissions = array();
	}
	else
	{
		$eligible_to_vote = false;
		$submissions = array();
	}
}
else
{
	//not in our database
	$eligible_to_vote = false;
}





?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Grader Request</title>
	<meta name="author" content="hedetni">
	<!-- Date: 2016-8-15 -->

<link rel="stylesheet" href="/style/math.css" type="text/css" media="screen" title="no title" charset="utf-8">

<style type="text/css">
table#history{
	font-size:small;
}

</style>

<script src="/style/jquery-1.3.min.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">

function toggleHistory() {
	$('table#history').toggle();
	if ($('table#history').attr('class') == "hidden")
	{
		$('a#toggleButton').text("Click to hide request history");
		$('table#history').removeClass("hidden");
		$('table#history').addClass("visible");
	}
	else if ($('table#history').attr('class') == "visible")
	{
		$('a#toggleButton').text("Click to show request history");
		$('table#history').removeClass("visible");
		$('table#history').addClass("hidden");
	}
}
</script>

</head>
<body>
	<div id="main">
		<div id="header">
			<a href="http://www.clemson.edu/math" title="School Home">
				<img src="/style/math_logo.png" alt="school logo">
			</a>
			<h1>Grader Request</h1>
		</div>
	
		<div id="content">
			<?php echo isset($message) ? '<p id="error">'.$message.'</p>' : ""; ?>
			
			<?php if (!isset($success)): ?>
				<?php if (in_array(strtoupper($user_id),$admins)): ?>
					<p style="text-align:right;margin-top:0px;"><a href="grader-request-submissions.php">Admin View Grader Requests</a></p>
				<?php endif; ?>
				
				<?php if ($eligible_to_vote): ?>
			
					<p>Currently logged in user requesting a grader: <big><?php echo $user_id; ?></big>.</p>
				
					<?php if (count($submissions)>0): ?>
					<p><a href="javascript:toggleHistory()" id="toggleButton">Click to view request history</a></p>
					<table id="history" style="display:none;" class="hidden">
						<tr><th>Submitted</th>
							<th>Term</th>
							<th>Duties</th>
							<th>Estimated Hours</th>
							<th>Requested Student</th>
							<th>Comments</th>
						</tr>
						<?php foreach ($submissions as $sub): ?>
						<tr>
							<td><?php echo date('M j, Y, g:i A', strtotime($sub['submitted'])); ?></td>
							<td><?php echo $sub['term']; ?></td>
							<td><?php echo $sub['duties']; ?></td>
							<td><?php echo $sub['hours']; ?></td>
							<td><?php echo $sub['student']; ?></td>
							<td><?php echo $sub['comments']; ?></td>
						</tr>
						<?php endforeach; ?>
					</table>
					<br>
					<?php endif; ?>
				
					<?php if ($requests_open): ?>
						<p>Please supply the following information to request grading assistance. <strong>Fill out separate requests for each course</strong>. If you are teaching multiple sections of the same course, only one request needs to be made.</p>

						<p>NOTE: In requesting a grader, you agree to submit the grader evaluation form at the end of the semester. Also, if you utilize the grader for significantly fewer hours than requested, please notify the department as early as possible since an additional assignment for the grader might be appropriate.</p>
			
						<hr>
			
						<form action="" method="POST">
							<input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
					
							<p><label for="term">For which term are you looking for a grader?</label> 
								<select name="term" id="term">
							<option value="<?php echo $current_term; ?>"><?php echo term_ending_to_semester($current_term).' '.substr($current_term,0,4); ?></option>
							<option value="<?php echo $next_term; ?>"><?php echo term_ending_to_semester($next_term).' '.substr($next_term,0,4); ?></option>
						</select>
							</p>

							<p><label for="duties">Course and Request Type (grader or TA; TAs can attend class, hold additional office hours, and grade)</label>:<br>
							<TEXTAREA name="duties" id="duties" ROWS="5" COLS="70"></TEXTAREA></p>

							<p><label for="hours">Estimated hours required per week</label>:<br>
							<TEXTAREA name="hours" id="hours" ROWS="5" COLS="70"></TEXTAREA></p>

							<p><label for="student">Request for specific student</label>:<br>
							<TEXTAREA name="student" id="student" ROWS="5" COLS="70"></TEXTAREA></p>

							<p><label for="comments">Comments</label>:<br>
							<TEXTAREA name="comments" id="comments" ROWS="5" COLS="70"></TEXTAREA></p>
	
							<br>
							<INPUT NAME="submit" TYPE="submit" VALUE="Submit Request">
							<INPUT NAME="reset" TYPE="reset" VALUE="Reset">
						</FORM>
					<?php else: ?>
						<p>Requests are currently closed. Contact Jennifer Van Dyken for special requests.</p>
					<?php endif; ?>
				<?php else: ?>
					<p>Sorry, only current faculty members may submit the grader request form.</p>
				<?php endif; ?>
			<?php endif; ?>
		</div>	
	</div>
</body>
</html>