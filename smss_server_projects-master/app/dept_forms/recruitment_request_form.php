<?php

if (isset($_POST['submit']))
{
	$date = date("F j, Y, g:i a");
	
	
	$headers = "MIME-Version: 1.0\r\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
	$headers .= "From: 'Recruitment Request Form' <mthsc@clemson.edu>\r\n";
	
	$subject = "Recruitment Request Submitted";
	
	$message = "<html><body><p>Submitted ".$date."</p>\r\n";
	$message .= "<p><b>Contact Info</b></p>\r\n";
	$message .= "<p><u>Recruiter Name</u>: ".stripslashes($_POST['recruiter_name'])."</p>\r\n";
	$message .= "<p><u>Organization or Business</u>: ".stripslashes($_POST['organization_name'])."</p>\r\n";
	$message .= "<p><u>Email</u>: ".stripslashes($_POST['email'])."</p>\r\n";
	$message .= "<p><u>Phone</u>: ".stripslashes($_POST['phone'])."</p>\r\n";
	$message .= "<p><b>Requested Date/Time</b></p>\r\n";
	$message .= "<p><u>Month</u>: ".$_POST['month']."</p>\r\n";
	$message .= "<p><u>Day</u>: ".$_POST['day']."</p>\r\n";
	$message .= "<p><u>Time of Day</u>: ".$_POST['time']."</p>\r\n";
	$message .= "<p><u>Length</u>: ".$_POST['length']."</p>\r\n";
	$message .= "<p><b>Description</b></p>\r\n";
	$message .= "<p><u>Student Level</u>: ".$_POST['student_level']."</p>\r\n";
	$message .= "<p><u>Target audience</u>: ".stripslashes($_POST['target_demo'])."</p>\r\n";
	$message .= "<p><u>Blurb</u>: ".stripslashes($_POST['blurb'])."</p>\r\n";
	$message .= "<p><u>Notes</u>: ".stripslashes($_POST['notes'])."</p>\r\n";
	
	
	
	$message .= "</body></html>";	
	
	
	/*
		This following sets who receives the form submission data
	*/
	$success = mail ("ugcmath@clemson.edu", $subject, $message, $headers);
	
	
	
	if ($success)
	{
		$confirmation = "Thank you. Your request has been submitted. We will contact you for more information.";
		unset($_POST['submit']);
	}
	else
	{
		$confirmation = "Sorry, something went wrong. Please try again.";
	}
	
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Clemson Math Sciences Recruitment Request Form</title>
	<meta name="author" content="hedetni">
	<!-- Date: 2015-09-23 -->
	
	<link rel="shortcut icon" href="../favicon.ico">
	
	<link rel="stylesheet" href="/style/math.css" type="text/css" media="screen" title="no title" charset="utf-8">
	
<style type="text/css">

label {
	margin-left:2%;
}
p#confirmation {
	color: #C47002;
	font-size: 1.25em;
	padding:0.75em;
	text-align:center;
}
</style>

</head>
<body>
	<div id="main">
		<div id="header">
			<a href="http://www.clemson.edu/ces/departments/math/index.html" title="Department Home">
				<img src="/style/math_logo.png" alt="math department logo">
			</a>
		</div>

		<div id="content">
			<center><h1>Clemson Math Sciences<br>Recruitment Request Form</h1>
			<p style="width:80%;">If you are interested in coming to Clemson for recruiting undergraduate or graduate math students, please fill out the following information. We will contact you regarding your request to set up an opportunity.</p></center>
			

			<?php echo isset($confirmation) ? '<p id="confirmation">'.$confirmation.'</p>' : "</br>"; ?>
	
			</center>

			<form id="request_form" action="" method="post">
	
				<p><b>Contact Info</b></p>
				<p><label for="recruiter_name">Recruiter Name(s): </label><input name="recruiter_name" id="recruiter_name" size="40" type="text"></input></p>

				<p><label for="organization_name">Organization or Business: </label><input name="organization_name" id="organization_name" size="40" type="text"></input></p>
		
				<p><label for="email">Email: </label><input name="email" id="email" size="40" type="text"></input></p>
		
				<p><label for="phone">Phone: </label><input name="phone" id="phone" size="40" type="text"></input></p>
	
				<p><b>Requested Date/Time:</b></p>
				<p><label for="month">Month: </label>
					<select name="month" id="month">
						<option value="">Select...</option>
						<option value="January">January</option>
						<option value="February">February</option>
						<option value="March">March</option>
						<option value="April">April</option>
						<option value="May">May</option>
						<option value="June">June</option>
						<option value="July">July</option>
						<option value="August">August</option>
						<option value="September">September</option>
						<option value="October">October</option>
						<option value="November">November</option>
						<option value="December">December</option>
					</select>

				<label for="day">Day: </label>
					<select name="day" id="day">
						<option value="">Select...</option>
						<?php for ($idx = 1; $idx < 32; $idx++)
							echo '<option value="'.$idx.'">'.$idx.'</option>';
						?>
					</select>
				</p>
	
				<p><label for="time">Time of Day: </label>
					<select name="time" id="time">
						<option value="morning">Morning</option>
						<option value="afternoon">Afternoon</option>
						<option value="evening">Evening</option>
					</select></p>
		
				<p><label for="length">Length of Recruiting Session: </label> 
					<select name="length" id="length">
						<option value="30 minutes">30 minutes</option>
						<option value="60 minutes">60 minutes</option>
						<option value="90 minutes">90 minutes</option>
						<option value="Other">Other</option>
					</select></p>
	
				<p><b>Description</b></p>
				<p><label for="student_level">Student Level: </label> 
					<select name="student_level" id="student_level">
						<option value="Undergraduate">Undergraduate</option>
						<option value="Graduate">Graduate</option>
						<option value="Both Undergrad and Grad">Undergraduate and Graduate</option>
					</select></p>
	
				<p><label for="target_demo">Target Audience (e.g. Seniors, all math majors, Actuarial Science students, etc...): </label><br><input style="margin-left:2%;" name="target_demo" id="target_demo" size="80" type="text"></input></p>
	
				<p style="margin-left:2%;"><label for="blurb">Provide a one paragraph description that can be distributed to students. Include any relevant links students may use for more information prior to the session.</label><br>
				<textarea name="blurb" id="blurb" cols="80" rows="8"></textarea></p>
	
				<p style="margin-left:2%;"><label for="notes">Notes/Special Requests</label>:<br>
				<textarea name="notes" id="notes" cols="80" rows="5"></textarea></p>
	
				</br></br>
				<center>
				<input type="submit" name="submit" value="Submit Request">
				<input type="reset" name="reset" value="Reset Form"></center>	
			</form>
		</div>
	</div>


</body>
</html>

