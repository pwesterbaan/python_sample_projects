<?php

include('college-survey-functions.php');

if (isInCoS())
{
	if (isset($_POST['submit_feedback']))
	{
		//check again to see if within survey time window
		$surveyToEnter = $_POST['survey'];
		//get candidate info
		$getSurvey = mysql_query('SELECT * FROM surveys WHERE open_date < "'.$currentDateTime.'" AND close_date > "'.$currentDateTime.'" AND survey_id = '.$surveyToEnter.' LIMIT 1;');
		
		if (mysql_num_rows($getSurvey) > 0) 
		{
			$user_hash = $_POST['user_hash'];
		
			//enter responses
			foreach ($_POST as $key => $response)
			{
				if (substr($key,0,9) == 'question_')
				{
					$insertResponse = mysql_query('INSERT INTO responses (user_hash,survey_id,question_id,question_response,time_submitted) VALUES(
													"'.mysql_real_escape_string($user_hash).'",
													"'.mysql_real_escape_string($surveyToEnter).'",
													"'.mysql_real_escape_string(substr($key,9)).'",
													"'.mysql_real_escape_string($response).'",
													"'.date("Y-m-d H:i:s",$currentTime).'") ON DUPLICATE KEY UPDATE question_response = "'.mysql_real_escape_string($response).'"');
					if (!$insertResponse)
					{
						$message = "There was an error processing your responses.";
						$responsesToRemove = $user_hash;
					}
				}
			}
			if (isset($removeResponses))
			{
				$removeResponses = mysql_query('DELETE FROM responses WHERE user_hash = "'.$responsesToRemove.'"');
			}
			else
			{
				$message = "Thank you. Your responses have been submitted.";
			}
		}
	}


	//get candidate list
	$getSurveys = mysql_query('SELECT * FROM surveys WHERE open_date < "'.$currentDateTime.'" AND close_date > "'.$currentDateTime.'";');
	if(!$getSurveys){$message = "Could not retrieve surveys";}
	else
	{
		$surveys = array();
		while ($row = mysql_fetch_array($getSurveys))
		{
			//check to see if user has already submitted feedback for this candidate
			$feedbackCheck = mysql_query('SELECT * FROM responses WHERE user_hash="'.md5($user_id.'+'.$row['survey_name'].'+'.$row['survey_id']).'";');
			if (mysql_num_rows($feedbackCheck) > 0)
			{$row['alreadySubmitted'] = true;}
			else
			{$row['alreadySubmitted'] = false;}
			$surveys[] = $row;
		}
	}

}
else
{
	$message = "This system is for members of the College of Science only. If you think you should have access, please contact Kevin Hedetniemi (hedetni@clemson.edu).";
}


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>College of Science Feedback</title>
	<meta name="author" content="hedetni">
	<!-- Date: 2017-04-10 -->
	
	<link rel="shortcut icon" href="/favicon.ico">
	
	<link rel="stylesheet" href="/style/math.css" type="text/css" charset="utf-8">
	<link rel="stylesheet" href="college-survey-style.css" type="text/css" charset="utf-8">
	
	<script src="jquery-1.7.2.js" type="text/javascript" charset="utf-8"></script>
	<script src="jquery.validate.js" type="text/javascript" charset="utf-8"></script>
	
	
	
<style type="text/css">


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
			<?php echo isset($message) ? '<p id="error">'.$message.'</p>' : ""; ?>
			
			<h1>College of Science Feedback</h1>
			<p>Listed below are the surveys for which you are eligible to participate in.</p>
			
			<?php if (count($surveys) > 0): ?>
				<table>
					<tr>
						<th>Survey Name</th>
						<th>Description</th>
						<th>Survey Open Until</th>
						<th>Feedback Link</th>
					</tr>
				<?php foreach ($surveys as $survey): ?>
					<tr>
						<td><?php echo $survey['survey_name']; ?></td>
						<td><?php echo $survey['description']; ?></td>
						<td><?php echo date("F j, Y", strtotime($survey['close_date'])); ?></td>
						<td><?php echo $survey['alreadySubmitted'] ? 'Already Submitted' : '<a href="submit-feedback.php?survey='.$survey['survey_id'].'">Submit Feedback</a>' ?></td>
					</tr>
				<?php endforeach; ?>
				</table>
			<?php else: ?>
				<p>You have no surveys to fill out at this time.</p>
			<?php endif; ?>
		</div>
	</div>


</body>
</html>

