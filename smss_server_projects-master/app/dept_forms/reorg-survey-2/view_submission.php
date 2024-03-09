<?php

$accepting_submissions = true;

//connects to the database, returns a semi-useful error if not accessible.
$link = mysql_connect("mthsc.clemson.edu", "forms", "d8ta_c0l");
if(!$link){
	echo "Could not connect to database.  Please try again later.";
	exit;
}
//selects the database
else{
	mysql_select_db("forms", $link);
}


mysql_set_charset("utf8-bin",$link);

//print_r($_POST);

if (isset($_SERVER['REMOTE_USER']))
{
	$user_id = strtoupper($_SERVER['REMOTE_USER']);
	$user_hash = md5($user_id.'survey');
}
	

//first check for requested id
if (isset($_GET['id']) && $_GET['id']!="")
{
	$getSubmission =  mysql_query('SELECT * FROM reorg_survey_2 WHERE id = "'.$_GET['id'].'" LIMIT 1;');
	if (!$getSubmission)
	{
		$message .= 'Error accessing database: ' . mysql_error($link).'<br>';
	}
	else
	{
		$row = mysql_fetch_array($getSubmission);
	
		//store the responses to display
		$evaluation = $row['evaluation'];
		$workload = $row['workload'];
		$tpr = $row['tpr'];
		$hiring = $row['hiring'];
		$budgeting = $row['budgeting'];
		$other = $row['other'];
		$associates = $row['associates'];
		$associates_number = $row['associates_number'];
		$duties = $row['duties'];
		$comments = $row['comments'];
	}
}
else
{
	$message = "No submission selected.";
	$eligible = false;
}




?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Departmental Challenges Survey Submission</title>
	<meta name="author" content="hedetni">
	<!-- Date: 2016-07-11 -->
	
	<link rel="shortcut icon" href="/favicon.ico">
	
	<link rel="stylesheet" href="/style/math.css" type="text/css" media="screen" title="no title" charset="utf-8">
	
	<script src="jquery-1.7.2.js" type="text/javascript" charset="utf-8"></script>
	<script src="jquery.validate.js" type="text/javascript" charset="utf-8"></script>
	
	
	
<style type="text/css">

td.center {
	text-align:center;
}
table.h_question {
	width:75%;
	table-layout:fixed;
}
</style>

<script type="text/javascript">
$(document).ready(function() 
{
	$("textarea").attr('readonly', true);
	$("input:text").attr('readonly', true);
	$(':radio:not(:checked)').attr('disabled', true);
	$(':checkbox:not(:checked)').attr('disabled', true);
	$(':checkbox:checked').attr('onclick', 'return false');
});
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
			<h1>Departmental Challenges Survey</h1>
			

			<?php echo isset($message) ? '<p id="error">'.$message.'</p>' : ""; ?>
	
			</center>
			
			<?php echo isset($_GET['id']) ? '<p><b>Submission #'.$_GET['id'].'</b></p>' : ""; ?>
			
			<form id="survey_form" name="survey_form" action="" method="post">

				<p>Challenges:</p>
				
				<p>A. Evaluation</p>
				<textarea name="evaluation" id="evaluation" cols="80" rows="5"><?php echo $evaluation; ?></textarea></p>
				
				<p>B. Workload</p>
				<textarea name="workload" id="workload" cols="80" rows="5"><?php echo $workload; ?></textarea></p>
				
				<p>C. TPR</p>
				<textarea name="tpr" id="tpr" cols="80" rows="5"><?php echo $tpr; ?></textarea></p>
				
				<p>D. Hiring</p>
				<textarea name="hiring" id="hiring" cols="80" rows="5"><?php echo $hiring; ?></textarea></p>
				
				<p>E. Budgeting</p>
				<textarea name="budgeting" id="budgeting" cols="80" rows="5"><?php echo $budgeting; ?></textarea></p>
				
				<p>F. Other (please specify)</p>
				<textarea name="other" id="other" cols="80" rows="5"><?php echo $other; ?></textarea></p>
				
				<br>
				
				<p>Are you in favor of creating one or more Associate Chair positions in the department? (choose one answer only)</p>
				<table class="v_question">
					<tr><td class="center"><input type="radio" name="associates" <?php echo $associates == "yes" ? "checked": ""; ?> value="yes"></td><td>Yes</td></tr>
					<tr><td class="center"><input type="radio" name="associates" <?php echo $associates == "no" ? "checked": ""; ?> value="no"></td><td>No</td></tr>
					<tr><td class="center"><input type="radio" name="associates" <?php echo $associates == "undecided" ? "checked": ""; ?> value="undecided"></td><td>Undecided</td></tr>
				</table>
				<br>
				
				<p>If you answered yes to the previous question:</p>
				<p>1. How many Associate Chair positions should be created?  (choose one answer only) </p>
				<table class="v_question">
					<tr><td class="center"><input type="radio" name="associates_number" <?php echo $associates_number == "1" ? "checked": ""; ?> value="1"></td><td>One</td></tr>
					<tr><td class="center"><input type="radio" name="associates_number" <?php echo $associates_number == "2" ? "checked": ""; ?> value="2"></td><td>Two</td></tr>
					<tr><td class="center"><input type="radio" name="associates_number" <?php echo $associates_number == "3" ? "checked": ""; ?> value="3"></td><td>Three</td></tr>
					<tr><td class="center"><input type="radio" name="associates_number" <?php echo $associates_number == "4" ? "checked": ""; ?> value="4"></td><td>Four</td></tr>
					<tr><td class="center"><input type="radio" name="associates_number" <?php echo $associates_number == "undecided" ? "checked": ""; ?> value="undecided"></td><td>Undecided</td></tr>
				</table>
				<br>
				
				<p>2. What should the duties of the Associate Chair(s) include? <br>
					<textarea name="duties" id="duties" cols="80" rows="5"><?php echo $duties; ?></textarea></p>
					
				<p>Other comments: <br>
					<textarea name="comments" id="comments" cols="80" rows="5"><?php echo $comments; ?></textarea></p>


				</br>
				<center>
				<?php if (!isset($_GET['id'])): ?>
					<input type="submit" name="save" value="Submit/Save Responses">
					<input type="reset" name="reset" value="Reset Form"></center>
				<?php endif; ?>	
			</form>
			

		</div>
	</div>


</body>
</html>

