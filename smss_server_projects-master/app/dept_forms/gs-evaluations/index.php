<?php

include 'gs-eval-functions.php';

if (isset($_POST['submit_advisee_review']))
{
	//save submission
	$review = $_POST;
	if (!isset($review['sufficient_progress'])){$review['sufficient_progress']="Not answered";}
	if (!isset($review['rating'])){$review['rating']="Not answered";}
	unset($review['submit_advisee_review']);
	
	//insert submission
	$insert_query = $mthsc_db->prepare('INSERT INTO advisor_reviews (reviewer_person_id,student_person_id,term,advisor_id,sufficient_progress,expected_graduation,rating,going_well,needs_improvement) VALUES (:reviewer_person_id,:student_person_id,:term,:advisor_id,:sufficient_progress,:expected_graduation,:rating,:going_well,:needs_improvement);');
	$result = $insert_query->execute($review);
	if ($result){$message = "Review Received";}
}

if (isset($_POST['submit_assignment_review']))
{
	//save submission
	$review = $_POST;
	if (!isset($review['sufficient_progress'])){$review['sufficient_progress']="Not answered";}
	if (!isset($review['rating'])){$review['rating']="Not answered";}
	unset($review['submit_assignment_review']);
	
	//insert submission
	$insert_query = $mthsc_db->prepare('INSERT INTO assignment_reviews (reviewer_person_id,student_person_id,assignment_id,sufficient_progress,recommendation, rating,going_well,needs_improvement) VALUES (:reviewer_person_id,:student_person_id,:assignment_id,:sufficient_progress,:recommendation,:rating,:going_well,:needs_improvement);');
	$result = $insert_query->execute($review);
	if ($result){$message = "Review Received";}
}

$evaluator_person_id = get_person_id($user_id);
//$evaluator_person_id = get_person_id("jdyken");


if ($evaluator_person_id)
{
	// current term is set in the functions file
	$semester = substr($current_term, -2);
	//advisor review term is set in functions file
	
	if ($semester == '01')
	{
		$advisees_to_review = get_advisees_to_review($evaluator_person_id);
		$reviewed_advisees = get_reviewed_advisees($evaluator_person_id,$advisor_review_term);
	
		$spring_assignments_to_review = get_assignments_to_review($evaluator_person_id,'202201');
		$reviewed_spring_assignments = get_reviewed_assignments($evaluator_person_id,'202201');
		
		$fall_assignments_to_review = get_assignments_to_review($evaluator_person_id,'202108');
		$reviewed_fall_assignments = get_reviewed_assignments($evaluator_person_id,'202108');
	}
	
	if ($semester == '08')
	{
		$advisees_to_review = get_advisees_to_review($evaluator_person_id);
		$reviewed_advisees = get_reviewed_advisees($evaluator_person_id,$current_term);
		
		$fall_assignments_to_review = get_assignments_to_review($evaluator_person_id,'202108');
		$reviewed_fall_assignments = get_reviewed_assignments($evaluator_person_id,'202108');
	}
	
	//echo '<pre>';print_r($advisees_to_review);echo '</pre>';
}
else
{
	$message = "Only Mathematical Sciences members may review students";
}


?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
	"http://www.w3.org/TR/html4/loose.dtd">


<html lang="en-US">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="author" content="hedetni">
	<!-- Date: 2018-5-7 -->
	
	<title>GS Evaluations</title>

<link rel="stylesheet" href="/style/math-internal.css" type="text/css" media="screen" charset="utf-8">

<style type="text/css">
table {border-collapse:collapse;margin-bottom:1em;}
table td,th {border:1px solid lightgray;padding:0.25em 0.5em;}
table th {background-color:#eee;}

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
			<h1>Grad Student Evaluations</h1>
			<?php if ($evaluator_person_id): ?>
			
				<p>To assist the graduate coordinator in evaluating the graduate students' performance, please review the following students. If there are students listed below you think you should not be reviewing, or if there are missing students, contact the <a href="mailto:jdyken@clemson.edu">Assistant Director of Instruction and General Education</a> for verification.</p>
			
				<table>
					<tr><th scope="colgroup" colspan="3">Advisees</th></tr>
					<tr>
						<th scope="col">Student</th>
						<th scope="col">Advisement</th>
						<th scope="col">Review Status</th>
					</tr>
						<?php if (count($advisees_to_review) > 0): ?>
							<?php foreach ($advisees_to_review as $advisor_id => $advisee): ?>
								
							<tr>
								<td><?php echo $advisee['first_name'].' '.$advisee['last_name'];?></td>
								<td><?php echo $advisee['advisor_type'].' Advisee'; ?></td>
								<?php if (!array_key_exists($advisor_id,$reviewed_advisees)): ?>
									<td><a href="review-advisee.php?id=<?php echo $advisor_id;?>">Click to Review</a></td>
								<?php else: ?>
									<td>Reviewed</td>
								<?php endif; ?>
							</tr>
								
							<?php endforeach; ?>
						<?php else: ?>
							<tr><td colspan="3" class="text-center">No advisees to review</td></tr>
						<?php endif; ?>
				</table>
				
				<table>
					<tr><th scope="colgroup" colspan="3">Supervisees</th></tr>
					<tr>
						<th scope="col">Student</th>
						<th scope="col">Assignment</th>
						<th scope="col">Review Status</th>
					</tr>
						<?php if ($semester == '01'): ?>
							<?php if (count($spring_assignments_to_review) > 0): ?>
								<?php foreach ($spring_assignments_to_review as $assignment_id => $assignment): ?>
								
								<tr>
									<td><?php echo $assignment['first_name'].' '.$assignment['last_name'];?></td>
									<td><?php echo $assignment['assignment_category'].' - '.$assignment['course'].' ('.term_ending_to_semester($assignment['term']).' '.substr($assignment['term'],0,4).')'; ?></td>
									<?php if (!array_key_exists($assignment_id,$reviewed_spring_assignments)): ?>
										<td><a href="review-assignment.php?id=<?php echo $assignment_id;?>">Click to Review</a></td>
									<?php else: ?>
										<td>Reviewed</td>
									<?php endif; ?>
								</tr>
								
								<?php endforeach; ?>
							<?php else: ?>
								<tr><td colspan="3" class="text-center">No spring assignments to review</td></tr>
							<?php endif; ?>
						<?php endif; ?>
						
						<?php if ($semester): ?>
							<?php if (count($fall_assignments_to_review) > 0): ?>
								<?php foreach ($fall_assignments_to_review as $assignment_id => $assignment): ?>
								
								<tr>
									<td><?php echo $assignment['first_name'].' '.$assignment['last_name'];?></td>
									<td><?php echo $assignment['assignment_category'].' - '.$assignment['course'].' ('.term_ending_to_semester($assignment['term']).' '.substr($assignment['term'],0,4).')'; ?></td>
									<?php if (!array_key_exists($assignment_id,$reviewed_fall_assignments)): ?>
										<td><a href="review-assignment.php?id=<?php echo $assignment_id;?>">Click to Review</a></td>
									<?php else: ?>
										<td>Reviewed</td>
									<?php endif; ?>
								</tr>
								
								<?php endforeach; ?>
							<?php else: ?>
								<tr><td colspan="3" class="text-center">No fall assignments to review</td></tr>
							<?php endif; ?>
						<?php endif; ?>
				</table>
				
			<?php endif; ?>
			
		</div>	
		<div id="footer"><a href="/dept_forms/website_error.py" target="_blank">Report a problem with this website</a></div>
	</div>
	
</body>
</html>