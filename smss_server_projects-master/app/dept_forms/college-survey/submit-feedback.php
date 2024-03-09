<?php

include('college-survey-functions.php');


if (isset($_GET['survey']))
{
	//get candidate
	$getSurveyInfo = mysql_query('SELECT * FROM surveys WHERE survey_id = '.$_GET['survey'].' ;');
	if(!$getSurveyInfo){$message = "Could not retrieve survey";}
	else
	{
		$surveyData = mysql_fetch_array($getSurveyInfo);
		
		//if within feedback time range
		if (strtotime($surveyData['open_date']) < strtotime($currentDateTime) && strtotime($surveyData['close_date']) > strtotime($currentDateTime))
		{
			$survey = $surveyData;
			
			//get instructions
			$getInstructions = mysql_query('SELECT * FROM forms WHERE form_id = "'.$survey['form_id'].'";');
			if(!$getInstructions){$message = "Could not retrieve form instructions";}
			else 
			{
				$form_data = mysql_fetch_array($getInstructions);
				$instructions = $form_data['instructions'];
			}
			
			//get questions
			$getQuestions = mysql_query('SELECT * FROM questions WHERE form_id = "'.$survey['form_id'].'" ORDER BY question_id;');
			if(!$getQuestions){$message = "Error: Could not retrieve survey questions";}
			else
			{
				$questionData = array();
				while($row = mysql_fetch_array($getQuestions))
				{
					$questionData[] = $row;
				}
				$questions = array();
				foreach ($questionData as $question)
				{
					$questions[] = assemble_question($question['question_type'],$question['question_id'],$question['question_statement']);
				}
			}
			
		}
		else
		{
			$message = "Selected survey can not be completed at this time.";
		}
	}
}
else
{
	$message = "No survey selected";
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
			
			<?php if (isset($survey)): ?>
				<h1><?php echo $survey['survey_name']; ?></h1>
				
				<div id="survey_form">
					<p><?php echo $instructions; ?></p>
					<form name="feedback_form" method="post" action="index.php">
						<?php foreach ($questions as $question): ?>
							<div class="question">
								<?php echo $question; ?>
							</div>
						<?php endforeach; ?>
						
						<input type="hidden" name="user_hash" value="<?php echo md5($user_id.'+'.$survey['survey_name'].'+'.$survey['survey_id']); ?>">
						<input type="hidden" name="survey" value="<?php echo $survey['survey_id']; ?>">
						<input type="submit" name="submit_feedback" value="Submit Responses">
					</form>
				</div>
			<?php endif; ?>
		</div>
	</div>


</body>
</html>

