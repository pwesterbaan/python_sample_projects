<?php

include('college-survey-functions.php');
	
if (isset($_POST['add_candidate']))
{
	$open_date = date("Y-m-d H:i:s",strtotime(mysql_real_escape_string($_POST['open_date'])));
	$close_date = date("Y-m-d H:i:s",strtotime(mysql_real_escape_string($_POST['close_date'])));
	
	$insertCandidate = mysql_query('INSERT INTO surveys (last_name,first_name,affiliation,visit_dates,open_date,close_date,form_id)
	VALUES (
		"'.mysql_real_escape_string($_POST['last_name']).'",
		"'.mysql_real_escape_string($_POST['first_name']).'",
		"'.mysql_real_escape_string($_POST['affiliation']).'",
		"'.mysql_real_escape_string($_POST['visit_dates']).'",
		"'.$open_date.'",
		"'.$close_date.'",
		'.mysql_real_escape_string($_POST['form']).'
		);');
	if(!$insertCandidate){$message = "Could not add candidate";}
	else {$message = "Candidate Added";}
}

//get candidate list
$getCandidates = mysql_query('SELECT * FROM surveys');
if(!$getCandidates){$message = "Could not retrieve candidates";}
else
{
	$candidates = array();
	while ($row = mysql_fetch_array($getCandidates))
	{
		$candidates[] = $row;
	}
}

//get forms
$getForms = mysql_query('SELECT * FROM forms');
if(!$getForms){$message = "Could not retrieve forms";}
else
{
	$forms = array();
	while ($row = mysql_fetch_array($getForms))
	{
		$forms[] = $row;
	}
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
$(document).ready(function() 
{
	$('div#new_survey_div').hide();
	
	$('a#add_survey_link').click(function(){
		$('div#new_survey_div').slideToggle();
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
			<h1>Candidate List</h1>
			
			<?php echo isset($message) ? '<p id="error">'.$message.'</p>' : ""; ?>
			
			<h2><a href="#" id="add_survey_link">Create New Survey</a></h2>
			<div id="new_survey_div">
				<form id="new_candidate_form" action="" method="post">
					First Name: <input type="text" name="first_name" size="20" value="" placeholder="First Name"> 
					Last Name: <input type="text" name="last_name" size="20" value="" placeholder="Last Name"> 
					Affiliation: <input type="text" name="affiliation" size="30" value="" placeholder="School, Company, etc."> <br>
					Visit Dates(s): <input type="text" name="visit_dates" size="20" value="" placeholder="Visit Dates(s)">
					Survey Open Date: <input type="text" name="open_date" size="20" value="" placeholder="Month Day, Year">
					Survey Close Date: <input type="text" name="close_date" size="20" value="" placeholder="Month Day, Year">
					<br>
					Form: <select name="form">
						<?php foreach ($forms as $form): ?>
							<option value="<?php echo $form['form_id'];?>"><?php echo $form['form_name'];?></option>
						<?php endforeach; ?>
					</select>
					<br><br>
					<input type="submit" name="add_candidate" value="Add Candidate">
				</form>
			</div>
			<br><br>
			<div>
				<table>
					<tr>
						<th>Candidate</th>
						<th>Affiliation</th>
						<th>Visit Date(s)</th>
						<th>Survey Open Date</th>
						<th>Survey Close Date</th>
						<th>View Responses</th>
					</tr>
				<?php foreach ($candidates as $candidate): ?>
					<tr>
						<td><?php echo $candidate['first_name'].' '.$candidate['last_name']; ?></td>
						<td><?php echo $candidate['affiliation']; ?></td>
						<td><?php echo $candidate['visit_dates']; ?></td>
						<td><?php echo date("F j, Y", strtotime($candidate['open_date'])); ?></td>
						<td><?php echo date("F j, Y", strtotime($candidate['close_date'])); ?></td>
						<td><a href="view-responses.php?cand=<?php echo $candidate['candidate_id'];?>">View Responses</a></td>
					</tr>
				<?php endforeach; ?>
				</table>
			</div>
			
		</div>
	</div>


</body>
</html>

