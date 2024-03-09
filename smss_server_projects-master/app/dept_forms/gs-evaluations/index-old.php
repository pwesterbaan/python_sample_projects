<?php

include 'gs-eval-functions.php';

if (isset($_POST['submit_review']))
{
	//save submission
	$review = $_POST;
	unset($review['submit_review']);
	
	//insert submission
	$insert_query = $mthsc_db->prepare('INSERT INTO reviews (reviewer_person_id,student_person_id,assignment_id,sufficient_progress,rating,going_well,needs_improvement) VALUES (:reviewer_person_id,:student_person_id,:review_year,:sufficient_progress,:rating,:going_well,:needs_improvement);');
	$result = $insert_query->execute($review);
	if ($result){$message = "Review Received";}
}

//$evaluator_person_id = get_person_id($user_id);
$evaluator_person_id = get_person_id("cjb2");
if ($evaluator_person_id)
{
	$students_to_review = get_students_to_review($evaluator_person_id);
	$reviewed_students = get_reviewed_students($evaluator_person_id);
	echo '<pre>';print_r($students_to_review);echo '</pre>';
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
			<?php if ($evaluator_person_id): ?>
				<h1>Students to Review</h1>
			
				<p>To assist the graduate coordinator in evaluating the graduate students' performance, please review the following students:</p>
			
				<table>
					<tr>
						<th>Student</th>
						<th>Assignment</th>
						<th>Review Status</th>
					</tr>
						<?php if (count($students_to_review) - count($reviewed_students) > 0): ?>
							<?php foreach ($students_to_review as $student): ?>
								<?php if (!array_key_exists($student['person_id'],$reviewed_students)): ?>
									<tr>
										<td><?php echo $student['first_name'].' '.$student['last_name'];?></td>
										<td><?php if ($student['review_type']=='assignment'){echo $student['assignment_category'].' '.$student['course'].' ('.term_ending_to_semester(substr($student['term'],-2)).' '.substr($student['term'],0,4).')';}else{echo $student['advisor_type'].' Advisee';} ?></td>
										<td><a href="review-student.php?id=<?php echo $student['assignment_id'];?>">Needs Review</a></td>
									</tr>
								<?php else: ?>
									<tr>
										<td><?php echo $student['first_name'].' '.$student['last_name'];?></td>
										<td><?php if ($student['review_type']=='assignment'){echo $student['assignment_category'].' '.$student['course'].' ('.term_ending_to_semester(substr($student['term'],-2)).' '.substr($student['term'],0,4).')';}else{echo $student['advisor_type'].' Advisee';} ?></td>
										<td>Reviewed</td>
									</tr>
								<?php endif; ?>
						
							<?php endforeach; ?>
						<?php else: ?>
							<tr><td colspan="2" class="text-center">No students to review</td></tr>
						<?php endif; ?>
				</ul>
			<?php endif; ?>
			
		</div>	
		<div id="footer"><a href="/dept_forms/website_error.py" target="_blank">Report a problem with this website</a></div>
	</div>
	
</body>
</html>