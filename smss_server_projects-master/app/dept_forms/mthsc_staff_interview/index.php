<?php

if (isset($_POST['submit']))
{
	
	$userID = $_POST['userID'];
	$program = $_POST['program'];
	
	$date = date("F j, Y, g:i a");
	
	$headers = "MIME-Version: 1.0\r\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
	$headers .= "From: 'Department Workgroup' <COES0975_DEPARTMENT_WORKGROUP-l@clemson.edu>\r\n";
	
	$subject = "MthSc Staff Interview Submission";
	
	$message = "<html><head><title>MthSc Staff Interview Submission</title></head>\r\n";
	$message .= "<body>Submitted ".$date."<br />\r\n";
	$message .= "Name: ".$_POST['name']."</p>\r\n";
	
	
	$message .= "<p>1. At times we have large projects with absolute deadlines. Staff members pitch in and work as a team to achieve the deadline goal. Describe your level of comfort in working as a team member and provide an example of a team project you have participated in.</p>\r\n";
	$message .= "<p style='margin-left:50px;'>".stripslashes($_POST['q1'])."</p>";
	
	$message .= "<p>2. After the beginning of the semester rush has subsided how would you manage your time when things are less hectic in the office.</p>\r\n";
	$message .= "<p style='margin-left:50px;'>".stripslashes($_POST['q2'])."</p>";
	
	$message .= "<p>3. Give an example of a project you worked on in a previous job that demonstrates your willingness to devote the time necessary to complete the project on time.</p>\r\n";
	$message .= "<p style='margin-left:50px;'>".stripslashes($_POST['q3'])."</p>";
	
	$message .= "<p>4. Our department is comprised of a culturally diverse group of faculty and graduate students. How would you interact with an individual that has a heavy accent and you have difficulty understanding them?</p>\r\n";
	$message .= "<p style='margin-left:50px;'>".stripslashes($_POST['q4'])."</p>";
	

	
	$message .= "</body></html>";
		
	mail ("ahayne@clemson.edu", $subject, $message, $headers);
	
	
	unset($_POST['submit']);
	
	include 'thanks.html.php';
	exit();
	
}

include 'mthsc_staff_interview.html.php';

?>

