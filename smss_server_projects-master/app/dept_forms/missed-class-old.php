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

$admins = array('HEDETNI', 'KEVJA', 'JDYKEN', 'PGERARD');

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
	$class_date = date("Y-m-d", mktime(0, 0, 0, $_POST['month'], $_POST['day'], $_POST['year']));
	$reason = mysql_real_escape_string($_POST['reason']);
	$cover = mysql_real_escape_string($_POST['cover']);
	
	$saveRequest = mysql_query('INSERT INTO missed_class (
		user_id,
		course,
		section,
		class_date,
		reason,
		cover
		) VALUES (
		"'.$userid.'",
		"'.$course.'",
		"'.$section.'",
		"'.$class_date.'",
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
		$email_body .= '<p><strong>Class Date</strong>: '.$class_date.'</p>';
		$email_body .= '<p><strong>Reason</strong>: '.$reason.'</p>';
		$email_body .= '<p><strong>Result</strong>: '.$cover.'</p>';
		$email_body .= '<p><a href="https://mthsc.clemson.edu/dept_forms/missed-class-submissions.php">View All Submissions</a></p>';
		$email_body .= '</body></html>';
		
		//EMAIL SENT TO ADIMATH account
		mail ('adimath@clemson.edu', $subject, $email_body, $headers);
		
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

<style type="text/css">


</style>

<script src="/style/jquery-1.3.min.js" type="text/javascript" charset="utf-8"></script>


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
					<p>Please supply the following information for <b>every</b> class session that you do not attend.</p>
					
					<form action="" method="POST">
					<input type="hidden" name="userid" value="<?php echo $user_id; ?>">

					<p>
					<label for="course">Course</label>:<input type="text" name="course" id="course" size="12" value="">
					<label for="honors">Honors</label>:<input type="checkbox" name="honors" id="honors" value="H"><br>
					<label for="section">Section</label>:<input type="text" name="section" id="section" size="6" value="" maxlength="3">
					</p>
					
					<fieldset>
					<legend>Date missed (use multiple submissions for multiple dates):</legend>
					<label for="month">Month</label>
					<select name="month" id="month">
						<option value="">Month...</option>
						<option value="01">January</option>
						<option value="02">February</option>
						<option value="03">March</option>
						<option value="04">April</option>
						<option value="05">May</option>
						<option value="06">June</option>
						<option value="07">July</option>
						<option value="08">August</option>
						<option value="09">September</option>
						<option value="10">October</option>
						<option value="11">November</option>
						<option value="12">December</option>
					</select>
					<label for="day">Day</label>
					<select name="day" id="day">
						<option value="">Day...</option>
						<option value="01">01</option>
						<option value="02">02</option>
						<option value="03">03</option>
						<option value="04">04</option>
						<option value="05">05</option>
						<option value="06">06</option>
						<option value="07">07</option>
						<option value="08">08</option>
						<option value="09">09</option>
						<option value="10">10</option>
						<option value="11">11</option>
						<option value="12">12</option>
						<option value="13">13</option>
						<option value="14">14</option>
						<option value="15">15</option>
						<option value="16">16</option>
						<option value="17">17</option>
						<option value="18">18</option>
						<option value="19">19</option>
						<option value="20">20</option>
						<option value="21">21</option>
						<option value="22">22</option>
						<option value="23">23</option>
						<option value="24">24</option>
						<option value="25">25</option>
						<option value="26">26</option>
						<option value="27">27</option>
						<option value="28">28</option>
						<option value="29">29</option>
						<option value="30">30</option>
						<option value="31">31</option>
					</select>
					<label for="year">Year</label>
					<select name="year" id="year">
						<option value="">Year...</option>
						<option value="<?php echo date("o", strtotime('now'))-1; ?>"><?php echo date("o", strtotime('now'))-1; ?></option>
						<option value="<?php echo date("o", strtotime('now')); ?>"><?php echo date("o", strtotime('now')); ?></option>
						<option value="<?php echo date("o", strtotime('now'))+1; ?>"><?php echo date("o", strtotime('now'))+1; ?></option>
					</select>
					</fieldset>

					<p><label for="reason">Reason for absence</label>:<br>
					<textarea name="reason" id="reason" rows="5" cols="80"></textarea></p>

					<p><label for="cover">How the class was covered (or why it was not)</label>:<br>
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