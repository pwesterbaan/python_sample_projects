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

mysql_set_charset("utf8-bin",$link);

if (isset($_SERVER['REMOTE_USER']))
{
	$user = strtoupper($_SERVER['REMOTE_USER']);
}

if (isset($_GET['id']) && $_GET['id'] != "")
{
	$student_id = $_GET['id'];
	
	//get information to fill in fields
	$get_info = mysql_query('SELECT * FROM student_info WHERE user_id = "'.$student_id.'" LIMIT 1');

	if ($get_info)
	{
		if (mysql_num_rows($get_info) > 0)
		{
			$info = mysql_fetch_array($get_info);
		}
		else
		{
			$info = array();
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
	<title>MthSc Student Information Form</title>
	<meta name="author" content="hedetni">
	<!-- Date: 2016-3-7 -->

<link rel="stylesheet" href="/style/math.css" type="text/css" media="screen" title="no title" charset="utf-8">

<style type="text/css">

input, textarea {
	font-size:0.9em;
}
input#certify, select {
	font-size:1.5em;
}
p#confirmation {
	color: #C47002;
	font-size: 1.25em;
	padding:0.75em;
	text-align:center;
}
div.separator {
	padding:0.1em 0.6em 0.1em 0.6em;
	margin-bottom:0.5em;
	border:1px solid #aaa;
}
span.field {
	border:1px solid gray;
	background-color:rgba(255, 255, 255, 0.7);
}

</style>

<script src="jquery-1.3.min.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">
$(document).ready(function() 
{
	
	
});
</script>

</head>
<body>
	<div id="main">
		<div id="header">
			<a href="http://www.clemson.edu/ces/departments/math/index.html" title="Department Home">
				<img src="/style/math_logo.png" alt="math department logo">
			</a>
		</div>
	
		<div id="content">
			<a href="https://mthsc.clemson.edu/dept_forms/awards/view_nominations.php"><- Back to list of nominations</a>
			<?php echo isset($confirmation) ? '<p id="confirmation">'.$confirmation.'</p>' : ""; ?>
			

			<h1><?php echo $info['first_name'].' '.$info['middle_name'].' '.$info['last_name']; ?></h1>
			
			<form name="info_form" id="info_form" action="" readonly="true" method="POST">

				<h2>Personal Information:</h2>
				
				<div class="separator">
					<p>Nickname: <?php echo $info['nickname'] ?></p>
					<p>
					Clemson User ID: <?php echo $info['user_id'] ?><br>
					Local Phone: <?php echo $info['local_phone'] ?> </p>
				
					<p>Address:<br>
					<?php echo $info['address'] ?><br>
					<?php echo $info['city_state_zip'] ?></p>
				</div>

				<br>


				<h2>Parents' Information:</h2>
				<div class="separator">
					<p><?php echo $info['parent1_first_name'].' '.$info['parent1_last_name'] ?><br>
						Phone: <?php echo $info['parent1_phone'] ?></p>
					
					<p>
						<?php echo $info['parent1_address'] ?><br>
						<?php echo $info['parent1_city_state_zip'] ?></p>
				</div>
				
				<div class="separator">
					<p><?php echo $info['parent2_first_name'].' '.$info['parent2_last_name'] ?><br>
						Phone: <?php echo $info['parent2_phone'] ?></p>
					
					<p>
						<?php echo $info['parent2_address'] ?><br>
						<?php echo $info['parent2_city_state_zip'] ?></p>
				</div>

				<br>


				<h2>Academic Information:</h2>
				
				<div class="separator">
					<p>Major: <?php echo $info['major']; ?><br>
						Minor/Emphasis Area: <?php echo $info['minor'] ?></p>
				
					<p>Total Credit Hours Earned (AP, Transfer, etc.): <?php echo $info['total_credits'] ?><br> 
						Credit Hours Earned at Clemson: <?php echo $info['cu_credits'] ?><br>
						GPA: <?php echo $info['gpa'] ?></p>
					
					<p>Expected Date of Graduation: <?php echo $info['graduation_date']; ?></p>
				
					<p>Calhoun Honors College? <?php echo $info['calhoun'] ?>
					<?php echo $info['calhoun'] == "Yes" ? '<br>'.$info['honors_college_stage'] : "" ?></p>


					<p>Courses taking this semester:<br>
						<?php echo $info['course1'] ?><br>
						<?php echo $info['course2'] ?><br>
						<?php echo $info['course3'] ?><br>
						<?php echo $info['course4'] ?><br>
						<?php echo $info['course5'] ?><br>
						<?php echo $info['course6'] ?><br>
						<?php echo $info['course7'] ?><br>
						<?php echo $info['course8'] ?><br>
					</p>
				</div>

				<br>


				<h2>Academic Achievements:</h2>
				
				<div class="separator">
					<p>Activity: <?php echo $info['academic_ach1'] ?></p>
					<p>Title or Description: <?php echo $info['academic_title1'] ?></p>
					<p>Start Date: <?php echo $info['academic_start1'] ?><br>
						End Date: <?php echo $info['academic_end1'] ?> </p>
				</div>
				
				<div class="separator">
					<p>Activity: <?php echo $info['academic_ach2'] ?></p>
					<p>Title or Description: <?php echo $info['academic_title2'] ?></p>
					<p>Start Date: <?php echo $info['academic_start2'] ?><br>
						End Date: <?php echo $info['academic_end2'] ?> </p>
				</div>
				
				<div class="separator">
					<p>Activity: <?php echo $info['academic_ach2'] ?></p>
					<p>Title or Description: <?php echo $info['academic_title3'] ?></p>
					<p>Start Date: <?php echo $info['academic_start3'] ?><br>
						End Date: <?php echo $info['academic_end3'] ?> </p>
				</div>
				
				<div class="separator">
					<p>Activity: <?php echo $info['academic_ach4'] ?></p>
					<p>Title or Description: <?php echo $info['academic_title4'] ?></p>
					<p>Start Date: <?php echo $info['academic_start4'] ?><br>
						End Date: <?php echo $info['academic_end4'] ?> </p>
				</div>
				
				
				<br>
				
				
				
				<h2>Leadership Roles:</h2>
				
				<div class="separator">
					<p>Name of Organization: <?php echo $info['leader_org1'] ?></p>
					<p>Position or Activity: <?php echo $info['leader_pos1'] ?></p>
					<p>Start Date: <?php echo $info['leader_start_date1'] ?><br> 
						End Date: <?php echo $info['leader_end_date1'] ?><br> 
						Avg. Hrs/Wk: <?php echo $info['leader_hrs_wk1'] ?></p>
					<p>Most Significant Accomplishment / Responsibility:<br><?php echo $info['leader_acc1'] ?></p>
				</div>
				
				<div class="separator">
					<p>Name of Organization: <?php echo $info['leader_org2'] ?></p>
					<p>Position or Activity: <?php echo $info['leader_pos2'] ?></p>
					<p>Start Date: <?php echo $info['leader_start_date2'] ?><br> 
						End Date: <?php echo $info['leader_end_date2'] ?><br> 
						Avg. Hrs/Wk: <?php echo $info['leader_hrs_wk2'] ?></p>
					<p>Most Significant Accomplishment / Responsibility:<br><?php echo $info['leader_acc2'] ?></p>
				</div>
				
				<div class="separator">
					<p>Name of Organization: <?php echo $info['leader_org3'] ?></p>
					<p>Position or Activity: <?php echo $info['leader_pos3'] ?></p>
					<p>Start Date: <?php echo $info['leader_start_date3'] ?><br> 
						End Date: <?php echo $info['leader_end_date3'] ?><br> 
						Avg. Hrs/Wk: <?php echo $info['leader_hrs_wk3'] ?></p>
					<p>Most Significant Accomplishment / Responsibility:<br><?php echo $info['leader_acc3'] ?></p>
				</div>
				
				<div class="separator">
					<p>Name of Organization: <?php echo $info['leader_org4'] ?></p>
					<p>Position or Activity: <?php echo $info['leader_pos4'] ?></p>
					<p>Start Date: <?php echo $info['leader_start_date4'] ?><br> 
						End Date: <?php echo $info['leader_end_date4'] ?><br> 
						Avg. Hrs/Wk: <?php echo $info['leader_hrs_wk4'] ?></p>
					<p>Most Significant Accomplishment / Responsibility:<br><?php echo $info['leader_acc4'] ?></p>
				</div>
				


				<br>


				<h2>Participatory Roles:</h2>
				
				<div class="separator">
					<p>Name of Organization: <?php echo $info['participate_org1'] ?></p>
					<p>Activities in Which You Participated: <br><?php echo $info['participate_act1'] ?></p>
					<p>Start Date: <?php echo $info['participate_start1'] ?><br> 
						End Date: <?php echo $info['participate_end1'] ?><br>
						Avg. Hrs/Wk: <?php echo $info['participate_hrs_wk1'] ?></p>
				</div>
				
				<div class="separator">
					<p>Name of Organization: <?php echo $info['participate_org2'] ?></p>
					<p>Activities in Which You Participated: <br><?php echo $info['participate_act2'] ?></p>
					<p>Start Date: <?php echo $info['participate_start2'] ?><br> 
						End Date: <?php echo $info['participate_end2'] ?><br>
						Avg. Hrs/Wk: <?php echo $info['participate_hrs_wk2'] ?></p>
				</div>
				
				<div class="separator">
					<p>Name of Organization: <?php echo $info['participate_org3'] ?></p>
					<p>Activities in Which You Participated: <br><?php echo $info['participate_act3'] ?></p>
					<p>Start Date: <?php echo $info['participate_start3'] ?><br> 
						End Date: <?php echo $info['participate_end3'] ?><br>
						Avg. Hrs/Wk: <?php echo $info['participate_hrs_wk3'] ?></p>
				</div>
				
				<div class="separator">
					<p>Name of Organization: <?php echo $info['participate_org4'] ?></p>
					<p>Activities in Which You Participated: <br><?php echo $info['participate_act4'] ?></p>
					<p>Start Date: <?php echo $info['participate_start4'] ?><br> 
						End Date: <?php echo $info['participate_end4'] ?><br>
						Avg. Hrs/Wk: <?php echo $info['participate_hrs_wk4'] ?></p>
				</div>
				
				
				
				<br>


				<h2>Academic Awards &amp; Honors:</h2>
				
				<div class="separator">
					<p>Award: <?php echo $info['award_honor1'] ?><br>
					Year Received: <?php echo $info['year_received1'] ?></p>
					<p>Award: <?php echo $info['award_honor2'] ?><br>
					Year Received: <?php echo $info['year_received2'] ?></p>
					<p>Award: <?php echo $info['award_honor3'] ?><br>
					Year Received: <?php echo $info['year_received3'] ?></p>
					<p>Award: <?php echo $info['award_honor4'] ?><br>
					Year Received: <?php echo $info['year_received4'] ?></p>
				</div>

				
				<br>


				<h2>Interests:</h2>
				
				<div class="separator">
					<p><?php echo $info['interests'] ?></p>
				</div>
				
				<br>


				<h2>Other:</h2>
				
				<div class="separator">
					<p><?php echo $info['other'] ?></p>
				
					<p>Faculty member most familiar with accomplishments: <?php echo $info['faculty_member'] ?></p>
				</div>
				
				<br><br><br>

			
		</div>	
	</div>
</body>
</html>