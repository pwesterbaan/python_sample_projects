<?php

include 'gs-eval-functions.php';
//advisor review term is set in functions file

//THIS ID IS THE ADVISOR ID (not the person id of the advisor or the advisee)
if (isset($_GET['id']) && $_GET['id']!=0 && is_numeric($_GET['id']))
{
	//get person id for advisee
	$advisor_id = $_GET['id'];
	$advisee_person_id = get_person_id_from_advisor_id($advisor_id);
	
	if ($advisee_person_id)
	{
		//get person info for student
		$student_info = get_person_info($advisee_person_id);
		
		if($student_info)
		{
			//get person info for evaluator
			$evaluator_person_id = get_person_id($user_id);
			//$evaluator_person_id = get_person_id("pgerard");
	
			//get review info
			$advisees_to_review = get_advisees_to_review($evaluator_person_id);
			//echo '<pre>';print_r($advisees_to_review);echo '</pre>';
		
			if ($evaluator_person_id && array_key_exists($advisor_id,$advisees_to_review))
			{
				//check for already reviewed
				$reviewed_advisees = get_reviewed_advisees($evaluator_person_id,$advisor_review_term);
		
				if (array_key_exists($advisor_id,$reviewed_advisees))
				{
					$message = "You have already reviewed this student this year";
					$show_form = false;
				}
				else
				{
					$show_form = true;
				}
			}
			else
			{
				$message = "We are not requesting a review of this student from you this year";
				$show_form = false;
			}
		}
		else
		{
			$error = "No such student";$show_form = false;
		}
	}
	else
	{
		$error = "No such assignment";$show_form = false;
	}
	
	
}
else
{
	$error = "Invalid Student Identifier";
	$show_form = false;
}


?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
	"http://www.w3.org/TR/html4/loose.dtd">


<html lang="en-US">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="author" content="hedetni">
	<!-- Date: 2018-5-8 -->
	
	<title>GS Evaluations | Review Student</title>

<link rel="stylesheet" href="/style/math-internal.css" type="text/css" media="screen" charset="utf-8">

<style type="text/css">
table {border-collapse:collapse;margin-bottom:1em;}
table td {border:1px solid lightgray;padding:0.25em 0.5em;}
textarea {margin-bottom:1em;}
ul {margin-top:0em;padding-top:0em;}
ul.likert {list-style-type:none;}
ul.likert li {display:inline; margin-right:1em;}
</style>

<script src="/style/jquery-3.2.1.min.js" type="text/javascript" charset="utf-8"></script>


<script type="text/javascript">
$(document).ready(function(){
	
	
}); 
</script>

</head>

<body>
	<div id="main">
		<div id="header">
			<div id="app_title">GS Evaluations</div>
			<a href="http://www.clemson.edu/math" title="Department Home"><img src="/style/math_logo.png" alt="math department logo"></a>
		</div>
		<ul id="nav">
			<?php echo get_nav(); ?>
		</ul>
		<?php echo isset($message) ? '<div id="message">'.$message.'</div>' : '' ?>
		<?php echo isset($error) ? '<div id="error">'.$error.'</div>' : '' ?>
		
		<div id="content">
			<?php if ($show_form): ?>
				<h1>Review of <?php echo $student_info['first_name'].' '.$student_info['last_name']; ?></h1>
			
				<form name="review_student_form" method="POST" action="index.php">
			
					<input type="hidden" name="reviewer_person_id" value="<?php echo $evaluator_person_id; ?>"></input>
					<input type="hidden" name="student_person_id" value="<?php echo $student_info['person_id']; ?>"></input>
					<input type="hidden" name="term" value="<?php echo $advisor_review_term; ?>"></input>
					<input type="hidden" name="advisor_id" value="<?php echo $advisor_id; ?>"></input>
					
					<fieldset>
					<legend>1. Is <?php echo $student_info['first_name']; ?> making sufficient progress?</legend>
					<ul class="likert">
					<li><input type="radio" name="sufficient_progress" id="progress_yes" value="1"></input> <label for="progress_yes">Yes</label></li>
					<li><input type="radio" name="sufficient_progress" id="progress_no" value="0"></input> <label for="progress_no">No</label></li>
					</ul>
					</fieldset>
					
					<p>2. <label for="expected_graduation">Estimated expected graduation</label>: <input type="text" name="expected_graduation" id="expected_graduation"></input></p>
					
					<fieldset>
					<legend>3. Rate <?php echo $student_info['first_name']; ?>'s overall performance as <?php echo $advisees_to_review[$advisor_id]['advisor_type']=="MS" ? 'an MS' : 'a PhD'; ?> student.</legend>
					<ul class="likert">
					<li><input type="radio" name="rating" id="rating_excel" value="Excellent"></input> <label for="rating_excel">Excellent</label></li>
					<li><input type="radio" name="rating" id="rating_vgood" value="Very Good"></input> <label for="rating_vgood">Very Good</label></li>
					<li><input type="radio" name="rating" id="rating_good" value="Good"></input> <label for="rating_good">Good</label></li>
					<li><input type="radio" name="rating" id="rating_fair" value="Fair"></input> <label for="rating_fair">Fair</label></li>
					<li><input type="radio" name="rating" id="rating_poor" value="Poor"></input> <label for="rating_poor">Poor</label></li>
					</ul>
					</fieldset>
			
					<p>4. <label for="going_well">Comment on what things are going well.</label><br>
			
					<textarea name="going_well" id="going_well" rows="4" cols="60"></textarea></p>
				
					<p>5. <label for="needs_improvement">Comment on what things need improvement</label>.<br>
			
					<textarea name="needs_improvement" id="needs_improvement" rows="4" cols="60"></textarea></p>
				
					<input type="submit" name="submit_advisee_review" value="Submit Review">
				</form>
			<?php endif; ?>
			
		</div>	
		<div id="footer"><a href="/dept_forms/website_error.py" target="_blank">Report a problem with this website</a></div>
	</div>
	
</body>
</html>