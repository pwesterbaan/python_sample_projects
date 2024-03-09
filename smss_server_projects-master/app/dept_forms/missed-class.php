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
date_default_timezone_set('America/New_York');

$admins = array('HEDETNI', 'KEVJA', 'JDYKEN', 'MANGANM', 'CGALLAG', 'REBHOLZ');

$user_id = "";
if (isset($_SERVER['REMOTE_USER']))
{
	$user_id = strtoupper($_SERVER['REMOTE_USER']);
}


if (isset($_POST['submit']))
{
	//temporarily store the POST variables
	$userid = mysql_real_escape_string($_POST['userid']);
	$course = mysql_real_escape_string($_POST['course']);
	if (isset($_POST['honors']))
	{
		$course .= " Honors";
	}
	$section = mysql_real_escape_string($_POST['section']);
	$class_date = date("Y-m-d", strtotime($_POST['class_date']));
	$class_date_end = date("Y-m-d", strtotime($_POST['class_date_end']));
	$reason = mysql_real_escape_string($_POST['reason']);
	$cover = mysql_real_escape_string($_POST['cover']);
	
	$saveRequest = mysql_query('INSERT INTO missed_class (
		user_id,
		course,
		section,
		class_date,
		class_date_end,
		reason,
		cover
		) VALUES (
		"'.$userid.'",
		"'.$course.'",
		"'.$section.'",
		"'.$class_date.'",
		"'.$class_date_end.'",
		"'.$reason.'",
		"'.$cover.'" );');
	
	$success = $saveRequest;	
	
	if ($success)
	{
		$message = "Thank you. We have recorded your missed class.";
		
		//send email
		$headers = "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
		$headers .= "From: ".$userid."@clemson.edu\r\n";
		$subject = "Missed Class Notification Submitted";
		$email_body = '<html><body><h2>Missed Class Notification</h2>';
		$email_body .= '<p><strong>Instructor</strong>: '.$userid.'</p>';
		$email_body .= '<p><strong>Course</strong>: '.$course.'</p>';
		$email_body .= '<p><strong>Section</strong>: '.$section.'</p>';
		$email_body .= '<p><strong>Start Date</strong>: '.$class_date.'</p>';
		$email_body .= '<p><strong>End Date</strong>: '.$class_date_end.'</p>';
		$email_body .= '<p><strong>Reason</strong>: '.$reason.'</p>';
		$email_body .= '<p><strong>Result</strong>: '.$cover.'</p>';
		$email_body .= '<p><a href="https://mthsc.clemson.edu/dept_forms/missed-class-submissions.php">View All Submissions</a></p>';
		$email_body .= '</body></html>';
		
		//EMAIL SENT TO ADIMATH account
		mail ('adimath@clemson.edu', $subject, $email_body, $headers);
		//mail ('hedetni@clemson.edu', $subject, $email_body, $headers);
		
		unset($_POST);
	}
	else
	{
		$message = "Sorry, something went wrong.<br>Your missed class has not been recorded. Please try again.";
		$message .= "<br>".mysql_error($link).'<br>';
	}
	
}

//get eligibility

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
	
		//check current student and faculty lists
		$listRequest = mysql_query('SELECT list_id FROM dept_info.people_to_lists_link where person_id = '.$person_id.' AND list_id IN (1,2);');
		if (!$listRequest)
		{
			$message .= 'Error fetching list info: '.mysql_error($link).'<br>';
			$eligible = false;
		}
		else
		{
			if (mysql_num_rows($listRequest) > 0) //they are in one of selected lists
			{
				$eligible = true;
			}
			else if (in_array($user_id, $admins))
			{
				$eligible = true;
			}
			else
			{
				$eligible = false;
			}
		}
	}
	else
	{
		//not in our database
		$eligible = false;
	}
}




?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Missed Class Notification</title>
	<meta name="author" content="hedetni">
	<!-- Date: 2016-8-17 -->

<link rel="stylesheet" href="/style/math.css" type="text/css" media="screen" title="no title" charset="utf-8">
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

<style type="text/css">


</style>

<script src="/style/jquery-3.2.1.min.js" type="text/javascript" charset="utf-8"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<script type="text/javascript">
$(document).ready(function(){
	
	$('.datepicker').datepicker({
		dateFormat: "yy-mm-dd"}
	);
	
});

</script>

</head>
<body>
	<div id="main">
		<div id="header">
			<a href="http://www.clemson.edu/math" title="Department Home">
				<img src="/style/math_logo.png" alt="math department logo">
			</a>
			<h1>Missed Class Notification</h1>
		</div>
	
		<div id="content">
			<?php echo isset($message) ? '<p id="error">'.$message.'</p>' : ""; ?>
			
			<?php if (!isset($success)): ?>
				<?php if (in_array(strtoupper($user_id),$admins)): ?>
					<p style="text-align:right;margin-top:0px;"><a href="missed-class-submissions.php">Admin View Missed Class Submissions</a></p>
				<?php endif; ?>
				
				<?php if ($eligible): ?>
					<p>Please supply the following information for <b>every</b> class that you do not attend.</p>
					
					<form action="" method="POST">
					<input type="hidden" name="userid" value="<?php echo $user_id; ?>">

					<p>
					<label for="course">Course</label>: <input type="text" name="course" id="course" size="12" value="">
					<label for="honors">Honors</label>: <input type="checkbox" name="honors" id="honors" value="H"><br>
					<label for="section">Section(s)</label>: <input type="text" name="section" id="section" size="12" value="" maxlength="3"><br>
					*Please submit separately for each course.
					</p>
					
					<p>Dates missed:<br>
						<label for="class_date">Starting Date</label>: <input type="text" class="datepicker" name="class_date" id="class_date"></input><br>
						<label for="class_date">Ending Date</label>: <input type="text" class="datepicker" name="class_date_end" id="class_date_end"></input>
					</p>

					<p><label for="reason">Reason for absence</label>:<br>
					<textarea name="reason" id="reason" rows="5" cols="80"></textarea></p>

					<p><label for="cover">How the classes were covered (or why they were not)</label>:<br>
					<textarea name="cover" id="cover" rows="5" cols="80"></textarea></p>

					<br>
					<input name="submit" type="submit" value="Submit">
					<input name="reset" type="reset" value="Reset">
					</form>
					
				<?php else: ?>
					<p>Sorry, only current faculty members and grad students may submit the missed class notification.</p>
				<?php endif; ?>
			<?php else: ?>
				<p>Click <a href="missed-class.php">here</a> to enter another missed class.</p>
			<?php endif; ?>
		</div>	
	</div>
</body>
</html>