<?php

if (isset($_POST['submit']))
{
	
	$userID = $_POST['userID'];
	$program = $_POST['program'];
	
	$date = date("F j, Y, g:i a");
	
	$headers = "MIME-Version: 1.0\r\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
	$headers .= "From: 'Department Workgroup' <COES0975_DEPARTMENT_WORKGROUP-l@clemson.edu>\r\n";
	
	$subject = "Exit Interview Submission";
	
	$message = "<html><head><title>Exit Interview Submission</title></head>\r\n";
	$message .= "<body>Submitted ".$date."<br />\r\n";
	$message .= "<p>Student User ID: ".$userID."</br>\r\n";
	$message .= "CUID: ".$_POST['CUID']."</p>\r\n";
	$message .= "<p>Degree Program: ".$_POST['program']."</br>\r\n";
	if ($program == "BA")
	{
		$message .= "Minor: ".stripslashes($_POST['minor'])."</p>\r\n";
	}
	if ($program == "BA-MATH-SECED")
	{
		$message .= "Minor: ".stripslashes($_POST['minor'])."</p>\r\n";
	}
	if ($program == "BS")
	{
		$message .= "Area of Concentration: ".$_POST['undergrad_concentration']."</br>\r\n";
		$message .= "Minor: ".$_POST['minor']."</p>\r\n";
	}
	if ($program == "MS" || $program == "PhD")
	{
		$message .= "Area of Concentration: ".$_POST['grad_concentration']."</p>\r\n";
	}
	
	$message .= "<p>Reason for Leaving: ".$_POST['reason']."</p></br>\r\n";
	
	
	
	$message .= "<p>1. State the names of any instructor in the Department of Mathematical Sciences whom you feel did an exceptional job in contributing to your education at Clemson University. Please explain why you named each individual.</p>\r\n";
	$message .= "<p style='margin-left:50px;'>".stripslashes($_POST['q1'])."</p>";
	
	$message .= "<p>2. State the names of any instructors in the Department of Mathematical Sciences whom you feel did a less than adequate job in contributing to your education. Please explain why and comment on how you feel these individuals can improve upon their performances.</p>\r\n";
	$message .= "<p style='margin-left:50px;'>".stripslashes($_POST['q2'])."</p>";
	
	$message .= "<p>3. What aspects of the academic program did you find especially educational and rewarding, and what aspects did you find lacking? Please explain.</p>\r\n";
	$message .= "<p style='margin-left:50px;'>".stripslashes($_POST['q3'])."</p>";
	
	$message .= "<p>4. Please make any additional comments which you feel could be useful in improving the educational process in the Department of Mathematical Sciences.</p>\r\n";
	$message .= "<p style='margin-left:50px;'>".stripslashes($_POST['q4'])."</p>";
	
	$message .= "<p>5. If you have obtained a position of employment, give the name and location of the company that you are going to work for, a brief description of what you will be doing and your title (if you know). Additionally, and this is optional, we would like to know your starting salary.</p>\r\n";
	$message .= "<p style='margin-left:50px;'>".stripslashes($_POST['q5'])."</p>";
	
	$message .= "<p>6. While you were at Clemson, did you study abroad?</p>\r\n";
	$message .= "<p style='margin-left:50px;'>".$_POST['abroad']."</p>";
	
	if ($_POST['abroad'] == "yes")
	{
		$message .= "<p>6a. If so, where did you study and what courses did you take?</p>\r\n";
		$message .= "<p style='margin-left:50px;'>".stripslashes($_POST['q6a'])."</p>";
	
		$message .= "<p>6b. Were you sponsored by an organization? If so, please specify.</p>\r\n";
		$message .= "<p style='margin-left:50px;'>".stripslashes($_POST['q6b'])."</p>";
	}
	
	$message .= "<p>7. While you were at Clemson, did you participate in any of these experiential education programs?</p>\r\n";
	if (isset($_POST['experience']))
	{
		$message .= "<p>\r\n";
		foreach ($_POST['experience'] as $experience)
		{
			$message .= "<span style='margin-left:50px;'>".$experience."</br>";
		}
		$message .= "</p>\r\n";
	}
	
	$message .= "<p>7b. If so, where and when did you participate?</p>\r\n";
	$message .= "<p style='margin-left:50px;'>".stripslashes($_POST['q7b'])."</p>";
	
	$message .= "<p>8. How likely are you to recommend our program to other students?</p>\r\n";
	$message .= "<p style='margin-left:50px;'>".$_POST['recommend']."</p>";
	
	$message .= "<p>8b. Why did you select this?</p>\r\n";
	$message .= "<p style='margin-left:50px;'>".stripslashes($_POST['q8'])."</p>";
	
	$message .= "<p>9. Please rate the following aspects of our program:</p>\r\n";
	$message .= "<p style='margin-left:50px;'>\r\n";
	$message .= "a. Overall experience: ".$_POST['q9a']."</br>\r\n";
	$message .= "b. Training appropriate to current/future occupation: ".$_POST['q9b']."</br>\r\n";
	$message .= "c. Faculty interaction with students: ".$_POST['q9c']."</br>\r\n";
	$message .= "d. Departmental Technology Resources: ".$_POST['q9d']."</br>\r\n";
	$message .= "</p></br>\r\n";
	
	if ($program == "BA" || $program == "BS" || $program =="BA-MATH-SECED")
	{
		$message .= "<p><b>Undergraduate Student Specific Questions</b></p>\r\n";
		
		$message .= "<p>1. If you are continuing on to graduate school, give the name of the university, the field of study, and the degree that you are going to pursue (MS, PhD).</p>\r\n";
		$message .= "<p style='margin-left:50px;'>".stripslashes($_POST['u1'])."</p>";
		
		$message .= "<p>2. If you are going to pursue a professional degree, for example, law or medicine, please give the type of program and the name of the institution that you will be attending.</p>\r\n";
		$message .= "<p style='margin-left:50px;'>".stripslashes($_POST['u2'])."</p>";
	}
	
	if ($program == "MS" || $program == "PhD")
	{
		$message .= "<p><b>Graduate Student Specific Questions</b></p>\r\n";
		
		$message .= "<p>1. What are the strengths of our graduate program?</p>\r\n";
		$message .= "<p style='margin-left:50px;'>".stripslashes($_POST['g1'])."</p>";
		
		$message .= "<p>2. What are the weaknesses of our graduate program?</p>\r\n";
		$message .= "<p style='margin-left:50px;'>".stripslashes($_POST['g2'])."</p>";
		
		$message .= "<p>3. What did you like best about the coursework you have completed?</p>\r\n";
		$message .= "<p style='margin-left:50px;'>".stripslashes($_POST['g3'])."</p>";
		
		$message .= "<p>4. What did not you like about the coursework you have completed?</p>\r\n";
		$message .= "<p style='margin-left:50px;'>".stripslashes($_POST['g4'])."</p>";
		
		$message .= "<p>5. What benefits do you see from working on the MS project?</p>\r\n";
		$message .= "<p style='margin-left:50px;'>".stripslashes($_POST['g5'])."</p>";
		
		$message .= "<p>6. If you already have a job offer, what in our graduate program made you attractive to this employer?</p>\r\n";
		$message .= "<p style='margin-left:50px;'>".stripslashes($_POST['g6'])."</p>";
		
		$message .= "<p>7. If you do not have a job offer yet, how will you use your Clemson MS/PhD degree in Mathematical Sciences in marketing yourself to a potential employer?</p>\r\n";
		$message .= "<p style='margin-left:50px;'>".stripslashes($_POST['g7'])."</p>";
		
		
	}
	
	$message .= "</body></html>";
		
	if ($program == "BA" || $program == "BS" || $program =="BA-MATH-SECED")
	{
		mail ("ugcmath@clemson.edu", $subject, $message, $headers);
		mail ("jdmcken@clemson.edu", $subject, $message, $headers);
	}
	if ($program == "MS" || $program == "PhD")
	{
		mail ("jdmcken@clemson.edu", $subject, $message, $headers);
		mail ("mthgrad@clemson.edu", $subject, $message, $headers);
	}
	
	unset($_POST['submit']);
	
	include 'thanks.html.php';
	exit();
	
}

include 'exit_interview.html.php';

?>

