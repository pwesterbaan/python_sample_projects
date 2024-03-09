<?php

if (isset($_POST['save']))
{
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

	$date = date("F j, Y, g:i a");
	
	$concentration = "";
	if (isset($_POST['minor']) && $_POST['minor'] != "")
	{
		$concentration = $_POST['minor'];
	}
	else if (isset($_POST['emphasis']) && $_POST['emphasis'] != "")
	{
		$concentration = $_POST['emphasis'];
	}
	
	$submitApp = mysql_query('INSERT INTO peer_mentoring (name,email,graduation_date,gpa,program,concentration,2060_grade,2080_grade,3110_grade,3190_grade,3600_grade,3020_grade,4000_grade,4400_grade,4120_grade,4530_grade,4540_grade,rec_1,rec_2,rec_3,goals,contributions,activity) VALUES ("'.mysql_real_escape_string($_POST['name']).'",
	"'.mysql_real_escape_string($_POST['email']).'",
	"'.mysql_real_escape_string($_POST['graduation_date']).'",
	"'.mysql_real_escape_string($_POST['gpa']).'",
	"'.mysql_real_escape_string($_POST['program']).'",
	"'.mysql_real_escape_string($concentration).'",
	"'.mysql_real_escape_string($_POST['2060_grade']).'",
	"'.mysql_real_escape_string($_POST['2080_grade']).'",
	"'.mysql_real_escape_string($_POST['3110_grade']).'",
	"'.mysql_real_escape_string($_POST['3190_grade']).'",
	"'.mysql_real_escape_string($_POST['3600_grade']).'",
	"'.mysql_real_escape_string($_POST['3020_grade']).'",
	"'.mysql_real_escape_string($_POST['4000_grade']).'",
	"'.mysql_real_escape_string($_POST['4400_grade']).'",
	"'.mysql_real_escape_string($_POST['4120_grade']).'",
	"'.mysql_real_escape_string($_POST['4530_grade']).'",
	"'.mysql_real_escape_string($_POST['4540_grade']).'",
	"'.mysql_real_escape_string($_POST['rec_1']).'",
	"'.mysql_real_escape_string($_POST['rec_2']).'",
	"'.mysql_real_escape_string($_POST['rec_3']).'",
	"'.mysql_real_escape_string($_POST['goals']).'",
	"'.mysql_real_escape_string($_POST['contributions']).'",
	"'.mysql_real_escape_string($_POST['activity']).'" ); ');
	
	$success = $submitApp;	
	
	if ($success)
	{
		$confirmation = "Thank you. Your application has been submitted.";
		unset($_POST['submit']);
	}
	else
	{
		$confirmation = "Sorry, something went wrong.<br>Your application has not been submitted.<br>Please try again.";
		$confirmation .= "<br>".mysql_error($link);
	}
	
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Clemson Math Sciences Peer Mentoring Application</title>
	<meta name="author" content="hedetni">
	<!-- Date: 2016-03-4 -->
	
	<link rel="shortcut icon" href="../favicon.ico">
	
	<link rel="stylesheet" href="/style/math.css" type="text/css" media="screen" title="no title" charset="utf-8">
	
	<script src="jquery-1.7.2.js" type="text/javascript" charset="utf-8"></script>
	<script src="jquery.validate.js" type="text/javascript" charset="utf-8"></script>
	
	
	
<style type="text/css">

label {
	//margin-left:2%;
}
p#confirmation {
	color: #C47002;
	font-size: 1.25em;
	padding:0.75em;
	text-align:center;
}
td {
	padding-right:1em;
}
label.error {
	color: #C47002;
	font-size:0.9em;
}
input {
	font-size:0.9em;
}
table {
	background-color:transparent;
	border:0px;
}
table td {
	border:0px;
}
</style>

<script type="text/javascript">
$(document).ready(function() 
{
	$("#program").change(function() {
	    if ($(this).val() == "BA")
		{
			$("#ba_more").show();
			$("#bs_more").hide();
			$("#bs_more input").val("");
		}
		else if ($(this).val() == "BS")
		{
			$("#bs_more").show();
			$("#ba_more").hide();
			$("#ba_more input").val("");
		}
		else if ($(this).val() == "NONE")
		{
			$("#bs_more").hide();
			$("#ba_more").hide();
			$("#ba_more input").val("");
			$("#bs_more input").val("");
		}
	});
	
	$.validator.setDefaults({
		submitHandler: function(form) {
    		form.submit();
  		}
	});
	
	// validate signup form on keyup and submit
	$("#application_form").validate({
		rules: {
			name: "required",
			email: {
				required: true,
				email: true
			},
			graduation_date: "required",
			gpa: "required",
			program: "required",
			minor: {
				required: function(element) {
					return $("#program").val() == "BA";
				}
			},
			emphasis: {
				required: function(element) {
					return $("#program").val() == "BS";
				}
			},
			rec_1: "required",
			rec_2: "required",
			rec_3: "required",
			goals: "required",
			contributions: "required",
			activity: "required"	
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
			<center><h2>Peer Mentoring in Mathematical Sciences<br>
				Application to be a Mentor for 2016-2017 Year</h2>
			

			<?php echo isset($confirmation) ? '<p id="confirmation">'.$confirmation.'</p>' : ""; ?>
	
			</center>

			<?php if (!$success): ?>
			
			<p>*All fields required</p>
			
			<form id="application_form" name="application_form" action="" method="post">

				<p><label>Name: </label><input name="name" id="name" size="40" type="text"></input></p>
		
				<p><label>Email: </label><input name="email" id="email" size="40" type="text"></input></p>
				
				<p><label>Anticipated Graduation: </label>
					<select id="graduation_date" name="graduation_date">
						<option value="">Select Date...</option>
						<option value="May 2017">May 2017</option>
						<option value="August 2017">August 2017</option>
						<option value="December 2017">December 2017</option>
						<option value="May 2018">May 2018</option>
						<option value="August 2018">August 2018</option>
						<option value="December 2018">December 2018</option>
						<option value="May 2019">May 2019</option>
						<option value="August 2019">August 2019</option>
						<option value="December 2019">December 2019</option>
						<option value="May 2020">May 2020</option>
						<option value="August 2020">August 2020</option>
						<option value="December 2020">December 2020</option>
					</select></p>
		
				<p><label>Current GPA: </label><input name="gpa" id="gpa" size="6" type="text"></input></p>
	
				<p><label>Program: </label>
					<select id="program" name="program">
						<option value="">Select Program...</option>
						<option value="BA">BA</option>
						<option value="BS">BS</option>
					</select>
					<span id="ba_more" style="display:none;">with a minor in <input name="minor" id="minor" size="40" type="text"></input></span>
					<span id="bs_more" style="display:none;">with an emphasis in <input name="emphasis" id="emphasis" size="40" type="text"></input></span>
				</p>
	
				<p>Indicate the grade received in each of the courses you have completed. Write "IP" if you are currently taking the course: <br>
					<table id="course_grades">
						<tr>
							<td><input name="2060_grade" id="2060_grade" size="3" type="text" maxlength="2"></input> MATH 2060</td>
							<td><input name="2080_grade" id="2080_grade" size="3" type="text" maxlength="2"></input> MATH 2080</td>
							<td><input name="3110_grade" id="3110_grade" size="3" type="text" maxlength="2"></input> MATH 3110</td>
						</tr>
						<tr>
							<td><input name="3190_grade" id="3190_grade" size="3" type="text" maxlength="2"></input> MATH 3190</td>
							<td><input name="3600_grade" id="3600_grade" size="3" type="text" maxlength="2"></input> MATH 3600</td>
							<td><input name="3020_grade" id="3020_grade" size="3" type="text" maxlength="2"></input> MATH 3020</td>
						</tr>
						<tr>
							<td><input name="4000_grade" id="4000_grade" size="3" type="text" maxlength="2"></input> MATH 4000</td>
							<td><input name="4400_grade" id="4400_grade" size="3" type="text" maxlength="2"></input> MATH 4400</td>
							<td><input name="4120_grade" id="4120_grade" size="3" type="text" maxlength="2"></input> MATH 4120</td>
						</tr>
						<tr>
							<td><input name="4530_grade" id="4530_grade" size="3" type="text" maxlength="2"></input> MATH 4530</td>
							<td><input name="4540_grade" id="4540_grade" size="3" type="text" maxlength="2"></input> MATH 4540</td>
						</tr>
					</table>
				</p>
	
				<p>List three Clemson University faculty members who are willing to serve as references for you. At least one must be a faculty member in Mathematical Sciences. We do not need letters, but may contact the faculty members for a verbal assessment of your interpersonal and academic skills, and the level of responsibility and leadership you could be expected to display in a mentoring capacity.</p>
				<p>
				1. <input name="rec_1" id="rec_1" size="60" type="text"></input><br>
				2. <input name="rec_2" id="rec_2" size="60" type="text"></input><br>
				3. <input name="rec_3" id="rec_3" size="60" type="text"></input><br></p>
				
				<br>
				
				<p>What would you get out of being a peer mentor in Mathematical Sciences?<br>
				<textarea name="goals" id="goals" cols="80" rows="5"></textarea></p>
				
				<p>What would you contribute to the peer mentoring program as a mentor?<br>
				<textarea name="contributions" id="contributions" cols="80" rows="5"></textarea></p>
				
				<p>Describe one activity you might suggest as part of a series of "friendly competition" events between mentoring teams. The activity should be doable in a 1-2 hour time frame, relatively inexpensive, and team-based.<br>
				<textarea name="activity" id="activity" cols="80" rows="5"></textarea></p>
	
				</br></br>
				<center>
				<input type="submit" name="save" value="Submit Application">
				<input type="reset" name="reset" value="Reset"></center>	
			</form>
			<?php endif; ?>
		</div>
	</div>


</body>
</html>

