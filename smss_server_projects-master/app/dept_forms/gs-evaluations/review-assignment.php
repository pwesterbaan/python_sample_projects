<?php

include 'gs-eval-functions.php';
$term = date('Y',strtotime('now')).'';

//THIS ID IS THE ASSIGNMENT ID
if (isset($_GET['id']) && $_GET['id']!=0 && is_numeric($_GET['id']))
{
	//get person id for advisee
	$assignment_id = $_GET['id'];
	$assignee_person_id = get_person_id_from_assignment_id($assignment_id);
	
	if ($assignee_person_id)
	{
		//get person info for student
		$student_info = get_person_info($assignee_person_id);
		
		if($student_info)
		{
			//get person info for evaluator
			$evaluator_person_id = get_person_id($user_id);
			//$evaluator_person_id = get_person_id("pgerard"); //for testing
	
			//get review info
			$assignment_info = get_assignment_info($assignment_id);
			//echo '<pre>';print_r($advisees_to_review);echo '</pre>';
		
			if ($evaluator_person_id && $assignment_info['faculty_supervisor_id'] == $evaluator_person_id)
			{
	
				//check for already reviewed
				$reviewed_assignments = get_reviewed_assignments($evaluator_person_id);
		
				if (array_key_exists($assignment_id,$reviewed_assignments))
				{
					$message = "You have already reviewed this student for this assignment";
					$show_form = false;
				}
				else
				{
					$show_form = true;
				}
			}
			else
			{
				$message = "We are not requesting a review of this assignment from you";
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
					<input type="hidden" name="assignment_id" value="<?php echo $assignment_id; ?>"></input>
				
					<fieldset>
						<legend>1. Is <?php echo $student_info['first_name']; ?> doing an adequate job in performing the assigned duties?</legend>
						<ul class="likert_horiz">
							<li><input type="radio" name="sufficient_progress" id="progress_yes" value="1"></input> <label for="progress_yes">Yes</label></li>
							<li><input type="radio" name="sufficient_progress" id="progress_no" value="0"></input> <label for="progress_no">No</label></li>
						</ul>
					</fieldset>
					
					<p>2. <label for="recommendation">If appropriate please list any recommendation for teaching assignments in future semesters.</label><br>
						
						<textarea name="recommendation" id="recommendation" rows="4" cols="60"></textarea></p>
					
					<fieldset>
						<legend>3. Rate <?php echo $student_info['first_name']; ?>'s overall performance for this assignment: <?php echo $assignment_info['assignment_category'].' - '.$assignment_info['course'].' ('.term_ending_to_semester($assignment_info['term']).' '.substr($assignment_info['term'],0,4).')'; ?>.</legend>
						<ul class="likert_horiz">
							<li><input type="radio" name="rating" id="rating_excel" value="Excellent"></input> <label for="rating_excel">Excellent</label></td>
							<li><input type="radio" name="rating" id="rating_vgood" value="Very Good"></input> <label for="rating_vgood">Very Good</label></li>
							<li><input type="radio" name="rating" id="rating_good" value="Good"></input> <label for="rating_good">Good</label></li>
							<li><input type="radio" name="rating" id="rating_fair" value="Fair"></input> <label for="rating_fair">Fair</label></li>
							<li><input type="radio" name="rating" id="rating_poor" value="Poor"></input> <label for="rating_poor">Poor</label></li>
						</ul>
					</fieldset>
			
					<p>4. <label for="going_well">Comment on what things are going well.</label><br>
			
					<textarea name="going_well" id="going_well" rows="4" cols="60"></textarea></p>
				
					<p>5. <label for="needs_improvement">Comment on what things need improvement.</label><br>
			
					<textarea name="needs_improvement" id="needs_improvement" rows="4" cols="60"></textarea></p>
				
					<input type="submit" name="submit_assignment_review" value="Submit Review">
				</form>
			<?php endif; ?>
			
		</div>	
		<div id="footer"><a href="/dept_forms/website_error.py" target="_blank">Report a problem with this website</a></div>
	</div>
	
</body>
</html>