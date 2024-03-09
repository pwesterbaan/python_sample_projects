<?php

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


if (isset($_GET['id']) && $_GET['id'] != "")
{
	$id = $_GET['id'];
	
	//get information to fill in fields
	$get_info = mysql_query('SELECT * FROM peer_mentoring WHERE id = "'.$id.'" LIMIT 1');

	if ($get_info)
	{
		if (mysql_num_rows($get_info) > 0)
		{
			$app = mysql_fetch_array($get_info);
		}
		else
		{
			$app = array();
		}
	}
}
else
{
	$confirmation = "No profile exists for that user id.";
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
</style>

<script type="text/javascript">
$(document).ready(function() 
{
	
	
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
			<a href="https://mthsc.clemson.edu/dept_forms/peer_mentoring/view_applicants.php"><- Back to List of Applicants</a>
			<?php echo isset($confirmation) ? '<p id="confirmation">'.$confirmation.'</p>' : ""; ?>
			
			<center>
				<h2>Application to be a Peer Mentor</h2>
				
				<h2><?php echo $app['name']; ?><br><?php echo $app['email']; ?></h2>
			</center>
				
			<p><b>Anticipated Graduation: </b><?php echo $app['graduation_date']; ?>
	
			<p><b>Current GPA: </b><?php echo $app['gpa']; ?></p>

			<p><b>Program: </b><?php echo $app['program']; ?> with a minor/emphasis in <?php echo $app['concentration']; ?>
			</p>

			<p><b>Course Grades:</b>
				<table id="course_grades">
					<tr><td>MATH 2060</td><td><?php echo $app['2060_grade']; ?></td></tr>
					<tr><td>MATH 2080</td><td><?php echo $app['2080_grade']; ?></td></tr>
					<tr><td>MATH 3110</td><td><?php echo $app['3110_grade']; ?></td></tr>
					<tr><td>MATH 3190</td><td><?php echo $app['3190_grade']; ?></td></tr>
					<tr><td>MATH 3600</td><td><?php echo $app['3600_grade']; ?></td></tr>
					<tr><td>MATH 3020</td><td><?php echo $app['3020_grade']; ?></td></tr>
					<tr><td>MATH 4000</td><td><?php echo $app['4000_grade']; ?></td></tr>
					<tr><td>MATH 4400</td><td><?php echo $app['4400_grade']; ?></td></tr>
					<tr><td>MATH 4120</td><td><?php echo $app['4120_grade']; ?></td></tr>
					<tr><td>MATH 4530</td><td><?php echo $app['4530_grade']; ?></td></tr>
					<tr><td>MATH 4540</td><td><?php echo $app['4540_grade']; ?></td></tr>
				</table>
			</p>

			<p><b>Faculty Member References:</b><br>
			1. <?php echo $app['rec_1']; ?><br>
			2. <?php echo $app['rec_2']; ?><br>
			3. <?php echo $app['rec_3']; ?></p>
			
			<p><b>What would you get out of being a peer mentor in Mathematical Sciences?</b><br>
			<?php echo $app['goals']; ?></p>
			
			<p><b>What would you contribute to the peer mentoring program as a mentor?</b><br>
			<?php echo $app['contributions']; ?></p>
			
			<p><b>Describe one activity you might suggest as part of a series of "friendly competition" events between mentoring teams. The activity should be doable in a 1-2 hour time frame, relatively inexpensive, and team-based.</b><br>
			<?php echo $app['activity']; ?></p>

	
			

		</div>
	</div>


</body>
</html>

