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
}


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Departmental Challenges Survey Responses</title>
	<meta name="author" content="hedetni">
	<!-- Date: 2016-07-06 -->
	
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
table.comment_table {
	margin-top:0.75em;
	width:75%;
}
button {
	font-size:1em;
}
table.comment_table th {
	text-align:left;
}
table.comment_table td {
	padding:0.5em;
}

@media print {
	button {
		display:none;
	}
}
</style>

<script type="text/javascript">
$(document).ready(function() 
{
	$('table.comment_table').find('tr:gt(0)').hide();
	
	$('button.expander').click(function(){
		if ($(this).html() == "Collapse")
		{
			$(this).parent().parent().parent().find('tr:gt(0)').hide();
			$(this).html("Expand");
		}
		else if ($(this).html() == "Expand")
		{
			$(this).parent().parent().parent().find('tr').show();
			$(this).html("Collapse");
		}
	});
	
	$('button#expand-all').click(function(){
		$('tr').show();
		$('button').filter(function(index) { return $(this).html() === "Expand"; })
		{
			$('button').html("Collapse");
			$(this).html("Expand All");
		}
	});
	
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
			<h1>Departmental Challenges Survey Responses</h1>
			

			<?php echo isset($message) ? '<p id="error">'.$message.'</p>' : ""; ?>

			<button type="button" id="expand-all">Expand All</button>


			<p>A. Evaluation</p>
			<?php 
				$getEvaluation = mysql_query('SELECT evaluation FROM `reorg_survey_2` WHERE evaluation != "";');
				$evaluation = array();
				while ($row = mysql_fetch_array($getEvaluation))
				{
					$evaluation[] = $row['evaluation'];
				}
			?>
			<table class="comment_table">
				<tr><th>Responses <button type="button" class="expander">Expand</button></th></tr>
				<?php foreach ($evaluation as $evaluation_response): ?>
					<tr><td><?php echo $evaluation_response; ?></td></tr>
				<?php endforeach; ?>
			</table><br><br>
			
			
			<p>B. Workload</p>
			<?php 
				$getWorkload = mysql_query('SELECT workload FROM `reorg_survey_2` WHERE workload != "";');
				$workload = array();
				while ($row = mysql_fetch_array($getWorkload))
				{
					$workload[] = $row['workload'];
				}
			?>
			<table class="comment_table">
				<tr><th>Responses <button type="button" class="expander">Expand</button></th></tr>
				<?php foreach ($workload as $workload_response): ?>
					<tr><td><?php echo $workload_response; ?></td></tr>
				<?php endforeach; ?>
			</table><br><br>
			
			
			<p>C. TPR</p>
			<?php 
				$getTPR = mysql_query('SELECT tpr FROM `reorg_survey_2` WHERE tpr != "";');
				$tpr = array();
				while ($row = mysql_fetch_array($getTPR))
				{
					$tpr[] = $row['tpr'];
				}
			?>
			<table class="comment_table">
				<tr><th>Responses <button type="button" class="expander">Expand</button></th></tr>
				<?php foreach ($tpr as $tpr_response): ?>
					<tr><td><?php echo $tpr_response; ?></td></tr>
				<?php endforeach; ?>
			</table><br><br>
			
			
			<p>D. Hiring</p>
			<?php 
				$getHiring = mysql_query('SELECT hiring FROM `reorg_survey_2` WHERE hiring != "";');
				$hiring = array();
				while ($row = mysql_fetch_array($getHiring))
				{
					$hiring[] = $row['hiring'];
				}
			?>
			<table class="comment_table">
				<tr><th>Responses <button type="button" class="expander">Expand</button></th></tr>
				<?php foreach ($hiring as $hiring_response): ?>
					<tr><td><?php echo $hiring_response; ?></td></tr>
				<?php endforeach; ?>
			</table><br><br>
			
			
			<p>E. Budgeting</p>
			<?php 
				$getBudgeting = mysql_query('SELECT budgeting FROM `reorg_survey_2` WHERE budgeting != "";');
				$budgeting = array();
				while ($row = mysql_fetch_array($getBudgeting))
				{
					$budgeting[] = $row['budgeting'];
				}
			?>
			<table class="comment_table">
				<tr><th>Responses <button type="button" class="expander">Expand</button></th></tr>
				<?php foreach ($budgeting as $budgeting_response): ?>
					<tr><td><?php echo $budgeting_response; ?></td></tr>
				<?php endforeach; ?>
			</table><br><br>
			
			
			<p>F. Other</p>
			<?php 
				$getOther = mysql_query('SELECT other FROM `reorg_survey_2` WHERE other != "";');
				$other = array();
				while ($row = mysql_fetch_array($getOther))
				{
					$other[] = $row['other'];
				}
			?>
			<table class="comment_table">
				<tr><th>Responses <button type="button" class="expander">Expand</button></th></tr>
				<?php foreach ($other as $other_response): ?>
					<tr><td><?php echo $other_response; ?></td></tr>
				<?php endforeach; ?>
			</table><br><br>


			<?php 
				$getAssociates = mysql_query('SELECT associates,count(associates) as count FROM `reorg_survey_2` group by associates');
				$associates = array();
				while ($row = mysql_fetch_array($getAssociates))
				{
					$option = $row['associates'];
					$associates[$option] = $row['count'];
				}
			?>
			<p>Are you in favor of creating one or more Associate Chair positions in the department? (choose one answer only)</p>
			<table class="v_question">
				<tr><th>Count</th><th>Option</th></tr>
				<tr><td class="center"><?php echo isset($associates['yes']) ? $associates['yes'] : "0"; ?></td><td>Yes</td></tr>
				<tr><td class="center"><?php echo isset($associates['no']) ? $associates['no'] : "0"; ?></td><td>No</td></tr>
				<tr><td class="center"><?php echo isset($associates['undecided']) ? $associates['undecided'] : "0"; ?></td><td>Undecided</td></tr>
			</table>
			

			<?php 
				$getAssociatesNumber = mysql_query('SELECT associates_number,count(associates_number) as count FROM `reorg_survey_2` group by associates_number');
				$associates_number = array();
				while ($row = mysql_fetch_array($getAssociatesNumber))
				{
					$option = $row['associates_number'];
					$associates_number[$option] = $row['count'];
				}
			?>
			<p>If you answered yes to the previous question:</p>
			<p>1. How many Associate Chair positions should be created?  (choose one answer only) </p>
			<table class="v_question">
				<tr><th>Count</th><th>Option</th></tr>
				<tr><td class="center"><?php echo isset($associates_number['1']) ? $associates_number["1"] : "0"; ?></td><td>One</td></tr>
				<tr><td class="center"><?php echo isset($associates_number['2']) ? $associates_number["2"] : "0"; ?></td><td>Two</td></tr>
				<tr><td class="center"><?php echo isset($associates_number['3']) ? $associates_number["3"] : "0"; ?></td><td>Three</td></tr>
				<tr><td class="center"><?php echo isset($associates_number['4']) ? $associates_number["4"] : "0"; ?></td><td>Four</td></tr>
				<tr><td class="center"><?php echo isset($associates_number['undecided']) ? $associates_number["undecided"] : "0"; ?></td><td>Undecided</td></tr>
			</table>
			<br>
			
			<p>2. What should the duties of the Associate Chair(s) include? </p>
			<?php 
				$getDuties = mysql_query('SELECT duties FROM `reorg_survey_2` WHERE duties != "";');
				$duties = array();
				while ($row = mysql_fetch_array($getDuties))
				{
					$duties[] = $row['duties'];
				}
			?>
			<table class="comment_table">
				<tr><th>Responses <button type="button" class="expander">Expand</button></th></tr>
				<?php foreach ($duties as $duties_response): ?>
					<tr><td><?php echo $duties_response; ?></td></tr>
				<?php endforeach; ?>
			</table><br><br>
			
			
			<p>Other Comments: </p>
			<?php 
				$getComments = mysql_query('SELECT comments FROM `reorg_survey_2` WHERE comments != "";');
				$comments = array();
				while ($row = mysql_fetch_array($getComments))
				{
					$comments[] = $row['comments'];
				}
			?>
			<table class="comment_table">
				<tr><th>Responses <button type="button" class="expander">Expand</button></th></tr>
				<?php foreach ($comments as $comments_response): ?>
					<tr><td><?php echo $comments_response; ?></td></tr>
				<?php endforeach; ?>
			</table><br><br>

		</div>
	</div>


</body>
</html>

