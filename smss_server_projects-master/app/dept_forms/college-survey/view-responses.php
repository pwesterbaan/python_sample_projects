<?php

include('college-candidate-functions.php');


if (isset($_GET['cand']))
{
	//get candidate
	$getCandidateInfo = mysql_query('SELECT * FROM surveys WHERE candidate_id = '.$_GET['cand'].' ;');
	if(!$getCandidateInfo){$message = "Could not retrieve candidates";}
	else
	{
		$candidateData = mysql_fetch_array($getCandidateInfo);
		$candidate = $candidateData;
		
		//get instructions
		$getInstructions = mysql_query('SELECT * FROM forms WHERE form_id = "'.$candidate['form_id'].'";');
		if(!$getInstructions){$message = "Could not retrieve form instructions";}
		else 
		{
			$form_data = mysql_fetch_array($getInstructions);
			$instructions = $form_data['instructions'];
		}
		
		//get questions
		$getQuestions = mysql_query('SELECT * FROM questions WHERE form_id = "'.$candidate['form_id'].'" ORDER BY question_id;');
		if(!$getQuestions){$message = "Could not retrieve feedback form";}
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
				$questions[] = get_question_responses($question['question_type'],$question['question_id'],$question['question_statement'],$candidate['candidate_id']);
			}
		}
	}
}
else
{
	$message = "No candidate selected";
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
	<link rel="stylesheet" href="college-candidate-style.css" type="text/css" charset="utf-8">
	
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
			
			<?php if (isset($candidate)): ?>
				<h1>Responses for <?php echo $candidate['first_name'].' '.$candidate['last_name'].' from '.$candidate['affiliation']; ?></h1>
				
				<div id="candidate_form">
					<p><?php echo $instructions; ?></p>
						<?php foreach ($questions as $question): ?>
							<div class="question">
								<?php echo $question; ?>
							</div>
						<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>


</body>
</html>

