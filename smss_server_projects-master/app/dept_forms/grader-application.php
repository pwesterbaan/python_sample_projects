<?php

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

$admins = array('HEDETNI', 'CLCOX', 'JDYKEN', 'REBHOLZ','PGERARD');

$user_id = "";
$user_fullname = "";
$user_xid = "";
if (isset($_SERVER['REMOTE_USER']))
{
	$user_id = strtoupper($_SERVER['REMOTE_USER']);
}
if (isset($_SERVER['fullName']))
{
	$user_fullname = $_SERVER['fullName'];
}
if (isset($_SERVER['clemsonXID']))
{
	$user_xid = $_SERVER['clemsonXID'];
}

date_default_timezone_set('America/New_York');
$current_term = get_current_term();
$next_term = get_next_term($current_term);
$then_term = get_next_term($next_term);

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
	//temporarily store the POST variables
	$name = mysql_real_escape_string($_POST['name']);
	$xid = mysql_real_escape_string($_POST['xid']);
	$userid = mysql_real_escape_string($_POST['userid']);
	$term = mysql_real_escape_string($_POST['term']);
	$job = mysql_real_escape_string($_POST['job']);
	$reference = mysql_real_escape_string($_POST['reference']);
	$phone = mysql_real_escape_string($_POST['phone']);
	$major = mysql_real_escape_string($_POST['major']);
	$semester = mysql_real_escape_string($_POST['semester']);
	$courses = mysql_real_escape_string($_POST['courses']);
	$hours = mysql_real_escape_string($_POST['hours']);
	$comments = mysql_real_escape_string($_POST['comments']);
	
	$saveApplication = mysql_query('INSERT INTO grader_app (
		xid,
		user_id,
		name,
		term,
		job,
		reference,
		phone,
		major,
		semester,
		courses,
		hours,
		comments
		
		) VALUES (
		"'.$xid.'",
		"'.$userid.'",
		"'.$name.'",
		"'.$term.'",
		"'.$job.'",
		"'.$reference.'",
		"'.$phone.'",
		"'.$major.'",
		'.$semester.',
		"'.$courses.'",
		"'.$hours.'",
		"'.$comments.'" )
		ON DUPLICATE KEY UPDATE 
		name = "'.$name.'",
		user_id = "'.$userid.'",
		term = "'.$term.'",
		job = "'.$job.'",
		reference = "'.$reference.'",
		phone = "'.$phone.'",
		major = "'.$major.'",
		semester = '.$semester.',
		courses = "'.$courses.'",
		hours = "'.$hours.'",
		comments = "'.$comments.'" ');
	
	$success = $saveApplication;	
	
	if ($success)
	{
		$message = "Thank you. Your application has been saved.";
		unset($_POST);
		
		/*//send email
		$headers = "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
		$headers .= "From: 'MTHSC Automated Email' <mthsc@clemson.edu>\r\n";
		$subject = "Grader Application Submitted";
		$email = "<html><body>";
		$email .= '<p><a href="https://mthsc.clemson.edu/dept_forms/grader-application-submissions.php">Click to view the application</a></p>';
		$email .= '</body></html>';
		mail ('rebholz@clemson.edu, jdyken@clemson.edu', $subject, $email, $headers); //email user*/
	}
	else
	{
		$message = "Sorry, something went wrong.<br>Your application has not been saved.<br>Please try again.";
		$message .= "<br>".mysql_error($link).'<br>';
	}
	
}


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Grader Application</title>
	<meta name="author" content="hedetni">
	<!-- Date: 2016-8-15 -->

<link rel="stylesheet" href="/style/math.css" type="text/css" media="screen" title="no title" charset="utf-8">

<style type="text/css">


</style>

<script src="/style/jquery-1.3.min.js" type="text/javascript" charset="utf-8"></script>


</head>
<body>
	<div id="main">
		<div id="header">
			<a href="http://www.clemson.edu/math" title="School Home">
				<img src="/style/math_logo.png" alt="school logo">
			</a>
			<h1>Grader Application</h1>
		</div>
	
		<div id="content">
			<?php echo isset($message) ? '<p id="error">'.$message.'</p>' : ""; ?>
			
			<?php if (in_array(strtoupper($user_id),$admins)): ?>
					<p style="text-align:right;margin-top:0px;"><a href="grader-application-submissions.php">Admin View Application Submissions</a></p>
			<?php endif; ?>
			
			<?php if (!isset($success)): ?>
			<p>The School of Mathematical and Statistical Sciences hires graders, as needed and paid on an hourly basis, for a variety of courses. Graders will be chosen based on course experience, GPA, and faculty recommendations. 
				<b>Note: Employment opportunities are generally limited to students whose major is Mathematical Sciences.</b>
				If you are interested in grading, please submit the information requested below by the first day of classes if possible.</p>

			<hr>
			<p>Some fields may be autofilled based on the logged in user: <big><?php echo $user_id; ?></big>.</p>
			<form name="grader_app_form" action="" method="POST">
			<p><label for="name">Full Name</label>:<br>
			<input type="text" name="name" id="name" size="40" value="<?php echo $user_fullname; ?>"></p>

			<p><label for="xid">XID</label>:<br>
			<input type="text" name="xid" id="xid" size="15" maxlength="11" value="<?php echo $user_xid; ?>"></p>
			
			<p><label for="userid">Clemson User ID</label>:<br>
			<input type="text" name="userid" id="userid" size="15" maxlength="11" value="<?php echo $user_id; ?>"></p>

			<p><label for="phone">Local phone number</label>:<br>
			<input type="text" name="phone" id="phone" size="20"></p>

			<p><label for="major">Major department</label>:<br>
			<input type="text" name="major" id="major" size="40"></p>

			<p><label for="semester">Current semester of study</label>:<br>
			<select name="semester" id="semester">
			<option selected value="0">Select Semester Status...</option>
			<option value="1">1st Semester Freshman</option>
			<option value="2">2nd Semester Freshman</option>
			<option value="3">1st Semester Sophomore</option>
			<option value="4">2nd Semester Sophomore</option>
			<option value="5">1st Semester Junior</option>
			<option value="6">2nd Semester Junior</option>
			<option value="7">1st Semester Senior</option>
			<option value="8">2nd Semester Senior</option>
			<option value="9">Graduate Student</option>
			</select></p>
			
			<p><label for="term">For which term to be wish to be a grader?</label> 
				<select name="term" id="term">
					<option value="<?php echo $current_term; ?>"><?php echo term_ending_to_semester($current_term).' '.substr($current_term,0,4); ?></option>
					<option value="<?php echo $next_term; ?>"><?php echo term_ending_to_semester($next_term).' '.substr($next_term,0,4); ?></option>
					<option value="<?php echo $then_term; ?>"><?php echo term_ending_to_semester($then_term).' '.substr($then_term,0,4); ?></option>
				</select>
			</p>
			
			<p><label for="job">Do you wish to be considered for a grader, TA, or either?</label> 
				<select name="job" id="job">
					<option value="Grader">Grader</option>
					<option value="TA">TA</option>
					<option value="Either Grader or TA">Either Grader or TA</option>
				</select>
			</p>
				
				
			<p><label for="reference">Name of Math and Stat Sciences faculty member who has agreed to provide a reference (required of new applicants;<br>
				please ask the faculty member to send a recommendation via email to the Associate Director of Instruction, adimath@clemson.edu)</label>:<br>
			<input type="text" name="reference" id="reference" size="40"></p>

			<p><label for="courses">Courses for which you feel qualified to grade</label>:<br>
			<textarea name="courses" id="courses" rows=5 cols=60></textarea></p>

			<p><label for="hours">Estimated hours per week you can work</label>:<br>
			<input type="text" name="hours" id="hours" size="20"></p>

			<p><label for="comments">Comments</label>:<br>
			<textarea name="comments" id="comments" rows=5 cols=60></textarea></p>
	
			<br>

			<input name="submit" type="submit" value="Submit Application">
			<input name="reset" type="reset" value="Reset">
			</form>
			<?php endif; ?>
		</div>	
	</div>
</body>
</html>