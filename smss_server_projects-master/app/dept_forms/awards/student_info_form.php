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
date_default_timezone_set('America/New_York');

if (isset($_SERVER['REMOTE_USER']))
{
	$user = strtoupper($_SERVER['REMOTE_USER']);
}

if (isset($_POST['nominate']))
{
	//user nominated themselves
	//store nomination in database
	$nominee = strtoupper($_POST['user_id']);
	
	//see if they have nominated themselves already
	$check_nom = mysql_query('SELECT * FROM award_noms WHERE user_id = "'.$nominee.'" AND nominator = "'.$user.'"');
	if ($check_nom)
	{
		if (mysql_num_rows($check_nom) > 0) //submitter already nominated themselves, don't store again
		{
			$confirmation = "Thank you. Your nomination has already been recorded. If you have not already done so, please complete the Student Information Form below.";
			unset($_POST['submit']);
		}
		else //user nominating themselves for first time
		{
			//store nomination in database
			$store_nom = mysql_query('INSERT INTO award_noms (name,user_id,self_nom,nominator,year) VALUES ("'.mysql_real_escape_string($_POST['name']).'","'.mysql_real_escape_string($nominee).'","'.mysql_real_escape_string($_POST['self_nom']).'","'.mysql_real_escape_string($user).'","'.date("Y").'")');
	
			if ($store_nom)
			{
				//remember last insert id for removal if necessary
				$store = mysql_insert_id($link);
		
				//check for previous nominations for this student
				$get_previous_noms = mysql_query('SELECT * FROM award_noms WHERE user_id = "'.$nominee.'"');
				if ($get_previous_noms)
				{
					$num_noms = mysql_num_rows($get_previous_noms);
					if ($num_noms > 1)	//was nominated before, no email necessary
					{
						$confirmation = "Thank you. Your nomination has been recorded. If you have not already done so, please complete the Student Information Form below.";
						unset($_POST['submit']);
					}
					else if ($num_noms <= 1)	//first time nominated, send email
					{
						$to = $nominee.'@clemson.edu';
						$subject = "Math Sciences Award Nomination";
						$message = "Hello,\r\n\r\n";
						$message .= "This is to confirm your nomination for a student award in the Clemson Mathematical Sciences Department. ";
						$message .= "If you have not already done so, please go to the following address,\r\n\r\nhttps://mthsc.clemson.edu/dept_forms/awards/student_info_form.php\r\n\r\nlog in, and fill out the Student Information Form. ";
						$message .= "This information will be used by the awards committee as they evaluate the candidates.\r\n\r\n";
						$message .= "Thank you,\r\nMath Sciences Department";
						$headers = 'From: ugcmath@clemson.edu' . "\r\n" .
									'Reply-To: ugcmath@clemson.edu' . "\r\n" .
									'X-Mailer: PHP/' . phpversion();
				
						mail($to, $subject, $message, $headers);
				
						$confirmation = "Thank you. Your nomination has been recorded. If you have not already done so, please complete the Student Information Form below. A link to this form has been emailed to you so you may edit the form later. Your progress will be saved when you submit.";
						unset($_POST['submit']);
					}
				}
				else //couldn't determine if they have already been nominated, so delete entry so they can try again
				{
					$remove_last = mysql_query('DELETE FROM award_noms WHERE id = "'.$store.'"');
					$confirmation = 'Sorry, something went wrong.<br>Your nomination has not been recorded.<br>Please <a href="nomination_form.php">try again</a>.';
					$confirmation .= "<br>".mysql_error($link);
				}
			}
			else //couldn't store nomination
			{
				$confirmation = "Sorry, something went wrong.<br>Your nomination has not been recorded.<br>Please try again.";
				$confirmation .= "<br>".mysql_error($link);
			}
		}
	}
}

if (isset($_POST['save']))
{
	//get major
	if ($_POST['Math_Major'] == "Other")
	{
		$major = $_POST['Other_Major'];
	}
	else
	{
		$major = $_POST['Math_Major'];
	}
	
	//print_r($_POST);
	$save_responses = mysql_query('INSERT INTO student_info SET
		user_id = "'.mysql_real_escape_string($_POST['User_ID']).'",
		first_name = "'.mysql_real_escape_string($_POST['First_name']).'",
		middle_name = "'.mysql_real_escape_string($_POST['Middle_name']).'",
		last_name = "'.mysql_real_escape_string($_POST['Last_name']).'",
		nickname = "'.mysql_real_escape_string($_POST['Nickname']).'",
		local_phone = "'.mysql_real_escape_string($_POST['Local_Phone']).'",
		address = "'.mysql_real_escape_string($_POST['Address']).'",
		city_state_zip = "'.mysql_real_escape_string($_POST['City_State_Zip']).'",
		parent1_first_name = "'.mysql_real_escape_string($_POST['Parent1_First_name']).'",
		parent1_last_name = "'.mysql_real_escape_string($_POST['Parent1_Last_name']).'",
		parent1_phone = "'.mysql_real_escape_string($_POST['Parent1_Phone']).'",
		parent1_address = "'.mysql_real_escape_string($_POST['Parent1_Address']).'",
		parent1_city_state_zip = "'.mysql_real_escape_string($_POST['Parent1_City_State_Zip']).'",
		parent2_first_name = "'.mysql_real_escape_string($_POST['Parent2_First_name']).'",
		parent2_last_name = "'.mysql_real_escape_string($_POST['Parent2_Last_name']).'",
		parent2_phone = "'.mysql_real_escape_string($_POST['Parent2_Phone']).'",
		parent2_address = "'.mysql_real_escape_string($_POST['Parent2_Address']).'",
		parent2_city_state_zip = "'.mysql_real_escape_string($_POST['Parent2_City_State_Zip']).'",
		major = "'.mysql_real_escape_string($major).'",
		minor = "'.mysql_real_escape_string($_POST['Minor']).'",
		total_credits = "'.mysql_real_escape_string($_POST['Total_Credits']).'",
		cu_credits = "'.mysql_real_escape_string($_POST['CU_Credits']).'",
		gpa = "'.mysql_real_escape_string($_POST['GPA']).'",
		graduation_date = "'.mysql_real_escape_string($_POST['Graduation_Date']).'",
		calhoun = "'.mysql_real_escape_string($_POST['calhoun']).'",
		honors_college_stage = "'.mysql_real_escape_string($_POST['Honors_College_Stage']).'",
		course1 = "'.mysql_real_escape_string($_POST['Course1']).'",
		course2 = "'.mysql_real_escape_string($_POST['Course2']).'",
		course3 = "'.mysql_real_escape_string($_POST['Course3']).'",
		course4 = "'.mysql_real_escape_string($_POST['Course4']).'",
		course5 = "'.mysql_real_escape_string($_POST['Course5']).'",
		course6 = "'.mysql_real_escape_string($_POST['Course6']).'",
		course7 = "'.mysql_real_escape_string($_POST['Course7']).'",
		course8 = "'.mysql_real_escape_string($_POST['Course8']).'",
		leader_org1 = "'.mysql_real_escape_string($_POST['Leader_Organization1']).'",
		leader_pos1 = "'.mysql_real_escape_string($_POST['Leader_Position1']).'",
		leader_start_date1 = "'.mysql_real_escape_string($_POST['Leader_Start_Date1']).'",
		leader_end_date1 = "'.mysql_real_escape_string($_POST['Leader_End_Date1']).'",
		leader_hrs_wk1 = "'.mysql_real_escape_string($_POST['Leader_Hrs_Wk1']).'",
		leader_acc1 = "'.mysql_real_escape_string($_POST['Leader_Accomplishment_Responsibility1']).'",
		leader_org2 = "'.mysql_real_escape_string($_POST['Leader_Organization2']).'",
		leader_pos2 = "'.mysql_real_escape_string($_POST['Leader_Position2']).'",
		leader_start_date2 = "'.mysql_real_escape_string($_POST['Leader_Start_Date2']).'",
		leader_end_date2 = "'.mysql_real_escape_string($_POST['Leader_End_Date2']).'",
		leader_hrs_wk2 = "'.mysql_real_escape_string($_POST['Leader_Hrs_Wk2']).'",
		leader_acc2 = "'.mysql_real_escape_string($_POST['Leader_Accomplishment_Responsibility2']).'",
		leader_org3 = "'.mysql_real_escape_string($_POST['Leader_Organization3']).'",
		leader_pos3 = "'.mysql_real_escape_string($_POST['Leader_Position3']).'",
		leader_start_date3 = "'.mysql_real_escape_string($_POST['Leader_Start_Date3']).'",
		leader_end_date3 = "'.mysql_real_escape_string($_POST['Leader_End_Date3']).'",
		leader_hrs_wk3 = "'.mysql_real_escape_string($_POST['Leader_Hrs_Wk3']).'",
		leader_acc3 = "'.mysql_real_escape_string($_POST['Leader_Accomplishment_Responsibility3']).'",
		leader_org4 = "'.mysql_real_escape_string($_POST['Leader_Organization4']).'",
		leader_pos4 = "'.mysql_real_escape_string($_POST['Leader_Position4']).'",
		leader_start_date4 = "'.mysql_real_escape_string($_POST['Leader_Start_Date4']).'",
		leader_end_date4 = "'.mysql_real_escape_string($_POST['Leader_End_Date4']).'",
		leader_hrs_wk4 = "'.mysql_real_escape_string($_POST['Leader_Hrs_Wk4']).'",
		leader_acc4 = "'.mysql_real_escape_string($_POST['Leader_Accomplishment_Responsibility4']).'",
		participate_org1 = "'.mysql_real_escape_string($_POST['Participate_Organization1']).'",
		participate_act1 = "'.mysql_real_escape_string($_POST['Participate_Activities1']).'",
		participate_start1 = "'.mysql_real_escape_string($_POST['Participate_Start_Date1']).'",
		participate_end1 = "'.mysql_real_escape_string($_POST['Participate_End_Date1']).'",
		participate_hrs_wk1 = "'.mysql_real_escape_string($_POST['Participate_Hrs_Wk1']).'",
		participate_org2 = "'.mysql_real_escape_string($_POST['Participate_Organization2']).'",
		participate_act2 = "'.mysql_real_escape_string($_POST['Participate_Activities2']).'",
		participate_start2 = "'.mysql_real_escape_string($_POST['Participate_Start_Date2']).'",
		participate_end2 = "'.mysql_real_escape_string($_POST['Participate_End_Date2']).'",
		participate_hrs_wk2 = "'.mysql_real_escape_string($_POST['Participate_Hrs_Wk2']).'",
		participate_org3 = "'.mysql_real_escape_string($_POST['Participate_Organization3']).'",
		participate_act3 = "'.mysql_real_escape_string($_POST['Participate_Activities3']).'",
		participate_start3 = "'.mysql_real_escape_string($_POST['Participate_Start_Date3']).'",
		participate_end3 = "'.mysql_real_escape_string($_POST['Participate_End_Date3']).'",
		participate_hrs_wk3 = "'.mysql_real_escape_string($_POST['Participate_Hrs_Wk3']).'",
		participate_org4 = "'.mysql_real_escape_string($_POST['Participate_Organization4']).'",
		participate_act4 = "'.mysql_real_escape_string($_POST['Participate_Activities4']).'",
		participate_start4 = "'.mysql_real_escape_string($_POST['Participate_Start_Date4']).'",
		participate_end4 = "'.mysql_real_escape_string($_POST['Participate_End_Date4']).'",
		participate_hrs_wk4 = "'.mysql_real_escape_string($_POST['Participate_Hrs_Wk4']).'",
		academic_ach1 = "'.mysql_real_escape_string($_POST['Academic_Achievement1']).'",
		academic_title1 = "'.mysql_real_escape_string($_POST['Academic_Title_Description1']).'",
		academic_start1 = "'.mysql_real_escape_string($_POST['Academic_Start_Date1']).'",
		academic_end1 = "'.mysql_real_escape_string($_POST['Academic_End_Date1']).'",
		academic_ach2 = "'.mysql_real_escape_string($_POST['Academic_Achievement2']).'",
		academic_title2 = "'.mysql_real_escape_string($_POST['Academic_Title_Description2']).'",
		academic_start2 = "'.mysql_real_escape_string($_POST['Academic_Start_Date2']).'",
		academic_end2 = "'.mysql_real_escape_string($_POST['Academic_End_Date2']).'",
		academic_ach3 = "'.mysql_real_escape_string($_POST['Academic_Achievement3']).'",
		academic_title3 = "'.mysql_real_escape_string($_POST['Academic_Title_Description3']).'",
		academic_start3 = "'.mysql_real_escape_string($_POST['Academic_Start_Date3']).'",
		academic_end3 = "'.mysql_real_escape_string($_POST['Academic_End_Date3']).'",
		academic_ach4 = "'.mysql_real_escape_string($_POST['Academic_Achievement4']).'",
		academic_title4 = "'.mysql_real_escape_string($_POST['Academic_Title_Description4']).'",
		academic_start4 = "'.mysql_real_escape_string($_POST['Academic_Start_Date4']).'",
		academic_end4 = "'.mysql_real_escape_string($_POST['Academic_End_Date4']).'",
		award_honor1 = "'.mysql_real_escape_string($_POST['Award_Honor1']).'",
		year_received1 = "'.mysql_real_escape_string($_POST['Year_Award_Received1']).'",
		award_honor2 = "'.mysql_real_escape_string($_POST['Award_Honor2']).'",
		year_received2 = "'.mysql_real_escape_string($_POST['Year_Award_Received2']).'",
		award_honor3 = "'.mysql_real_escape_string($_POST['Award_Honor3']).'",
		year_received3 = "'.mysql_real_escape_string($_POST['Year_Award_Received3']).'",
		award_honor4 = "'.mysql_real_escape_string($_POST['Award_Honor4']).'",
		year_received4 = "'.mysql_real_escape_string($_POST['Year_Award_Received4']).'",
		interests = "'.mysql_real_escape_string($_POST['Interests']).'",
		other = "'.mysql_real_escape_string($_POST['Other']).'",
		faculty_member = "'.mysql_real_escape_string($_POST['Faculty_Member']).'" 
		ON DUPLICATE KEY UPDATE 
		first_name = "'.mysql_real_escape_string($_POST['First_name']).'",
		middle_name = "'.mysql_real_escape_string($_POST['Middle_name']).'",
		last_name = "'.mysql_real_escape_string($_POST['Last_name']).'",
		nickname = "'.mysql_real_escape_string($_POST['Nickname']).'",
		local_phone = "'.mysql_real_escape_string($_POST['Local_Phone']).'",
		address = "'.mysql_real_escape_string($_POST['Address']).'",
		city_state_zip = "'.mysql_real_escape_string($_POST['City_State_Zip']).'",
		parent1_first_name = "'.mysql_real_escape_string($_POST['Parent1_First_name']).'",
		parent1_last_name = "'.mysql_real_escape_string($_POST['Parent1_Last_name']).'",
		parent1_phone = "'.mysql_real_escape_string($_POST['Parent1_Phone']).'",
		parent1_address = "'.mysql_real_escape_string($_POST['Parent1_Address']).'",
		parent1_city_state_zip = "'.mysql_real_escape_string($_POST['Parent1_City_State_Zip']).'",
		parent2_first_name = "'.mysql_real_escape_string($_POST['Parent2_First_name']).'",
		parent2_last_name = "'.mysql_real_escape_string($_POST['Parent2_Last_name']).'",
		parent2_phone = "'.mysql_real_escape_string($_POST['Parent2_Phone']).'",
		parent2_address = "'.mysql_real_escape_string($_POST['Parent2_Address']).'",
		parent2_city_state_zip = "'.mysql_real_escape_string($_POST['Parent2_City_State_Zip']).'",
		major = "'.mysql_real_escape_string($major).'",
		minor = "'.mysql_real_escape_string($_POST['Minor']).'",
		total_credits = "'.mysql_real_escape_string($_POST['Total_Credits']).'",
		cu_credits = "'.mysql_real_escape_string($_POST['CU_Credits']).'",
		gpa = "'.mysql_real_escape_string($_POST['GPA']).'",
		graduation_date = "'.mysql_real_escape_string($_POST['Graduation_Date']).'",
		calhoun = "'.mysql_real_escape_string($_POST['calhoun']).'",
		honors_college_stage = "'.mysql_real_escape_string($_POST['Honors_College_Stage']).'",
		course1 = "'.mysql_real_escape_string($_POST['Course1']).'",
		course2 = "'.mysql_real_escape_string($_POST['Course2']).'",
		course3 = "'.mysql_real_escape_string($_POST['Course3']).'",
		course4 = "'.mysql_real_escape_string($_POST['Course4']).'",
		course5 = "'.mysql_real_escape_string($_POST['Course5']).'",
		course6 = "'.mysql_real_escape_string($_POST['Course6']).'",
		course7 = "'.mysql_real_escape_string($_POST['Course7']).'",
		course8 = "'.mysql_real_escape_string($_POST['Course8']).'",
		leader_org1 = "'.mysql_real_escape_string($_POST['Leader_Organization1']).'",
		leader_pos1 = "'.mysql_real_escape_string($_POST['Leader_Position1']).'",
		leader_start_date1 = "'.mysql_real_escape_string($_POST['Leader_Start_Date1']).'",
		leader_end_date1 = "'.mysql_real_escape_string($_POST['Leader_End_Date1']).'",
		leader_hrs_wk1 = "'.mysql_real_escape_string($_POST['Leader_Hrs_Wk1']).'",
		leader_acc1 = "'.mysql_real_escape_string($_POST['Leader_Accomplishment_Responsibility1']).'",
		leader_org2 = "'.mysql_real_escape_string($_POST['Leader_Organization2']).'",
		leader_pos2 = "'.mysql_real_escape_string($_POST['Leader_Position2']).'",
		leader_start_date2 = "'.mysql_real_escape_string($_POST['Leader_Start_Date2']).'",
		leader_end_date2 = "'.mysql_real_escape_string($_POST['Leader_End_Date2']).'",
		leader_hrs_wk2 = "'.mysql_real_escape_string($_POST['Leader_Hrs_Wk2']).'",
		leader_acc2 = "'.mysql_real_escape_string($_POST['Leader_Accomplishment_Responsibility2']).'",
		leader_org3 = "'.mysql_real_escape_string($_POST['Leader_Organization3']).'",
		leader_pos3 = "'.mysql_real_escape_string($_POST['Leader_Position3']).'",
		leader_start_date3 = "'.mysql_real_escape_string($_POST['Leader_Start_Date3']).'",
		leader_end_date3 = "'.mysql_real_escape_string($_POST['Leader_End_Date3']).'",
		leader_hrs_wk3 = "'.mysql_real_escape_string($_POST['Leader_Hrs_Wk3']).'",
		leader_acc3 = "'.mysql_real_escape_string($_POST['Leader_Accomplishment_Responsibility3']).'",
		leader_org4 = "'.mysql_real_escape_string($_POST['Leader_Organization4']).'",
		leader_pos4 = "'.mysql_real_escape_string($_POST['Leader_Position4']).'",
		leader_start_date4 = "'.mysql_real_escape_string($_POST['Leader_Start_Date4']).'",
		leader_end_date4 = "'.mysql_real_escape_string($_POST['Leader_End_Date4']).'",
		leader_hrs_wk4 = "'.mysql_real_escape_string($_POST['Leader_Hrs_Wk4']).'",
		leader_acc4 = "'.mysql_real_escape_string($_POST['Leader_Accomplishment_Responsibility4']).'",
		participate_org1 = "'.mysql_real_escape_string($_POST['Participate_Organization1']).'",
		participate_act1 = "'.mysql_real_escape_string($_POST['Participate_Activities1']).'",
		participate_start1 = "'.mysql_real_escape_string($_POST['Participate_Start_Date1']).'",
		participate_end1 = "'.mysql_real_escape_string($_POST['Participate_End_Date1']).'",
		participate_hrs_wk1 = "'.mysql_real_escape_string($_POST['Participate_Hrs_Wk1']).'",
		participate_org2 = "'.mysql_real_escape_string($_POST['Participate_Organization2']).'",
		participate_act2 = "'.mysql_real_escape_string($_POST['Participate_Activities2']).'",
		participate_start2 = "'.mysql_real_escape_string($_POST['Participate_Start_Date2']).'",
		participate_end2 = "'.mysql_real_escape_string($_POST['Participate_End_Date2']).'",
		participate_hrs_wk2 = "'.mysql_real_escape_string($_POST['Participate_Hrs_Wk2']).'",
		participate_org3 = "'.mysql_real_escape_string($_POST['Participate_Organization3']).'",
		participate_act3 = "'.mysql_real_escape_string($_POST['Participate_Activities3']).'",
		participate_start3 = "'.mysql_real_escape_string($_POST['Participate_Start_Date3']).'",
		participate_end3 = "'.mysql_real_escape_string($_POST['Participate_End_Date3']).'",
		participate_hrs_wk3 = "'.mysql_real_escape_string($_POST['Participate_Hrs_Wk3']).'",
		participate_org4 = "'.mysql_real_escape_string($_POST['Participate_Organization4']).'",
		participate_act4 = "'.mysql_real_escape_string($_POST['Participate_Activities4']).'",
		participate_start4 = "'.mysql_real_escape_string($_POST['Participate_Start_Date4']).'",
		participate_end4 = "'.mysql_real_escape_string($_POST['Participate_End_Date4']).'",
		participate_hrs_wk4 = "'.mysql_real_escape_string($_POST['Participate_Hrs_Wk4']).'",
		academic_ach1 = "'.mysql_real_escape_string($_POST['Academic_Achievement1']).'",
		academic_title1 = "'.mysql_real_escape_string($_POST['Academic_Title_Description1']).'",
		academic_start1 = "'.mysql_real_escape_string($_POST['Academic_Start_Date1']).'",
		academic_end1 = "'.mysql_real_escape_string($_POST['Academic_End_Date1']).'",
		academic_ach2 = "'.mysql_real_escape_string($_POST['Academic_Achievement2']).'",
		academic_title2 = "'.mysql_real_escape_string($_POST['Academic_Title_Description2']).'",
		academic_start2 = "'.mysql_real_escape_string($_POST['Academic_Start_Date2']).'",
		academic_end2 = "'.mysql_real_escape_string($_POST['Academic_End_Date2']).'",
		academic_ach3 = "'.mysql_real_escape_string($_POST['Academic_Achievement3']).'",
		academic_title3 = "'.mysql_real_escape_string($_POST['Academic_Title_Description3']).'",
		academic_start3 = "'.mysql_real_escape_string($_POST['Academic_Start_Date3']).'",
		academic_end3 = "'.mysql_real_escape_string($_POST['Academic_End_Date3']).'",
		academic_ach4 = "'.mysql_real_escape_string($_POST['Academic_Achievement4']).'",
		academic_title4 = "'.mysql_real_escape_string($_POST['Academic_Title_Description4']).'",
		academic_start4 = "'.mysql_real_escape_string($_POST['Academic_Start_Date4']).'",
		academic_end4 = "'.mysql_real_escape_string($_POST['Academic_End_Date4']).'",
		award_honor1 = "'.mysql_real_escape_string($_POST['Award_Honor1']).'",
		year_received1 = "'.mysql_real_escape_string($_POST['Year_Award_Received1']).'",
		award_honor2 = "'.mysql_real_escape_string($_POST['Award_Honor2']).'",
		year_received2 = "'.mysql_real_escape_string($_POST['Year_Award_Received2']).'",
		award_honor3 = "'.mysql_real_escape_string($_POST['Award_Honor3']).'",
		year_received3 = "'.mysql_real_escape_string($_POST['Year_Award_Received3']).'",
		award_honor4 = "'.mysql_real_escape_string($_POST['Award_Honor4']).'",
		year_received4 = "'.mysql_real_escape_string($_POST['Year_Award_Received4']).'",
		interests = "'.mysql_real_escape_string($_POST['Interests']).'",
		other = "'.mysql_real_escape_string($_POST['Other']).'",
		faculty_member = "'.mysql_real_escape_string($_POST['Faculty_Member']).'"');
		
		if ($save_responses)
		{
			$confirmation = "Thank you. Your information has been saved.";
			unset($_POST['submit']);
		}
		else
		{
			$confirmation = "Sorry, there was an error. Your information was not saved.";
			$saved = $_POST;
		}
}


if (isset($saved))
{
	$info = $saved;
}
else {
	//get information to fill in fields
	$get_info = mysql_query('SELECT * FROM student_info WHERE user_id = "'.$user.'" LIMIT 1');

	if ($get_info)
	{
		if (mysql_num_rows($get_info) > 0)
		{
			$info = mysql_fetch_array($get_info);
		}
	}
	else
	{
		$info = array();
	}
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

p#confirmation {
	color: #C47002;
	font-size: 1.25em;
	padding:0.75em;
	text-align:center;
}
div.separator {
	//background-color:rgba(255, 255, 255, 0.3);
	border:1px solid #aaa;
	padding:0.2em 0.6em 0.2em 0.6em;
	margin-bottom:0.5em;
}

</style>

<script src="jquery-1.3.min.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">
$(document).ready(function() 
{
	if ($("#Math_Major").val() == "Other")
	{
		$("#show_other_major").show();
	}
	else
	{
		$("#show_other_major").hide();
	}
	
	$("#certify").change(function() {
	    if ($(this).attr('checked') == true)
		{
			$("#save").attr("disabled", false);
		}
		else if ($(this).attr('checked') == false)
		{
			$("#save").attr("disabled", true);
		}
	});
	
	$("#Math_Major").change(function() {
	    if ($(this).val() == "Other")
		{
			$("#show_other_major").show();
		}
		else
		{
			$("#show_other_major").hide();
		}
	});
	
	
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
			<h1>Math Sciences Student Information Form</h1>
			
			<?php echo isset($confirmation) ? '<p id="confirmation">'.$confirmation.'</p>' : "</br>"; ?>
			
			<form name="info_form" id="info_form" action="" method="POST">

				<h2>Personal Information:</h2>
				
				<div class="separator">
					<p><label for="First_name">First Name:<label> <input name="First_name" id="First_name" size="30" value="<?php echo $info['first_name'] ?>"/> 
						<label for="Middle_name">Middle Name:</label> <input name="Middle_name" id="Middle_name" size="25" value="<?php echo $info['middle_name'] ?>"/>
						<label for="Last_name">Last Name:</label> <input name="Last_name" id="Last_name" size="30" value="<?php echo $info['last_name'] ?>"/> </p>
					
					<p><label for="Nickname">Nickname:</label> <input name="Nickname" id="Nickname" size="31" value="<?php echo $info['nickname'] ?>"/>
					<label for="User_ID">Clemson User ID:</label> <input name="User_ID" id="User_ID"  size="10" value="<?php echo $user; ?>" readonly="true" />
					<label for="Local_Phone">Local Phone:</label> <input name="Local_Phone" id="Local_Phone" size="15" value="<?php echo $info['local_phone'] ?>"/> </p>
				
					<p><label for="Address">Address:</label> <input name="Address" id="Address" size="55" value="<?php echo $info['address'] ?>"/>
					<label for="City_State_Zip">City/State/Zip:</label> <input name="City_State_Zip" id="City_State_Zip" size="55" value="<?php echo $info['city_state_zip'] ?>"/></p>
				</div>

				<br>


				<h2>Parents' Information:</h2>
				<div class="separator">
					<p><label for="Parent1_First_name">First Name:</label> <input name="Parent1_First_name" id="Parent1_First_name" size="30" value="<?php echo $info['parent1_first_name'] ?>"/>
						<label for="Parent1_Last_name">Last Name:</label> <input name="Parent1_Last_name" id="Parent1_Last_name" size="30" value="<?php echo $info['parent1_last_name'] ?>"/> 
						<label for="Parent1_Phone">Phone:</label> <input name="Parent1_Phone" id="Parent1_Phone" size="15" value="<?php echo $info['parent1_phone'] ?>"/></p>
					
					<p><label for="Parent1_Address">Address:</label> <input name="Parent1_Address" id="Parent1_Address"  size="55" value="<?php echo $info['parent1_address'] ?>"/>
					<label for="Parent1_City_State_Zip">City/State/Zip:</label> <input name="Parent1_City_State_Zip" id="Parent1_City_State_Zip" size="55" value="<?php echo $info['parent1_city_state_zip'] ?>"/></p>
				</div>
				
				<div class="separator">
					<p><label for="Parent2_First_name">First Name:</label> <input name="Parent2_First_name" id="Parent2_First_name" size="30" value="<?php echo $info['parent2_first_name'] ?>"/> 
						<label for="Parent2_Last_name">Last Name:</label> <input name="Parent2_Last_name" id="Parent2_Last_name" size="30" value="<?php echo $info['parent2_last_name'] ?>"/>
						<label for="Parent2_Phone">Phone:</label> <input name="Parent2_Phone" id="Parent2_Phone" size="15" value="<?php echo $info['parent2_phone'] ?>"/></p>
					
					<p><label for="Parent2_Address">Address:</label> <input name="Parent2_Address" id="Parent2_Address" size="55" value="<?php echo $info['parent2_address'] ?>"/>
					<label for="Parent2_City_State_Zip">City/State/Zip:</label> <input name="Parent2_City_State_Zip" id="Parent2_City_State_Zip" size="55" value="<?php echo $info['parent2_city_state_zip'] ?>"/></p>
				</div>

				<br>


				<h2>Academic Information:</h2>
				
				<div class="separator">
					<p><label for="Math_Major">Major:</label> 
						<select id="Math_Major" name="Math_Major" id="Math_Major">
							<option value="B.A. Mathematical Sciences" <?php if ($info['major'] == "B.A. Mathematical Sciences"){echo 'selected="selected"';}?> >B.A. Mathematical Sciences</option>
							<option value="B.S. Mathematical Sciences" <?php if ($info['major'] == "B.S. Mathematical Sciences"){echo 'selected="selected"';}?> >B.S. Mathematical Sciences</option>
							<option value="Other" <?php if ($info['major'] != "B.A. Mathematical Sciences" && $info['major'] != "B.S. Mathematical Sciences"){echo 'selected="selected"';}?> >Other</option>
						</select>
						<span id="show_other_major"> <label for="Other_Major">Please Specify:</label> <input name="Other_Major" id="Other_Major" size="55" value="<?php if ($info['major'] != "B.A. Mathematical Sciences" && $info['major'] != "B.S. Mathematical Sciences"){echo $info['major'];} ?>"></input> </span></p>
					<p><label for="Minor">Minor/Emphasis Area:</label> <input name="Minor" id="Minor" size="55" value="<?php echo $info['minor'] ?>"/></p>
				
					<p><label for="Total_Credits">Total Credit Hours Earned (AP, Transfer, etc.):</label> <input name="Total_Credits" id="Total_Credits" size="5" value="<?php echo $info['total_credits'] ?>"/> 
						<label for="CU_Credits">Credit Hours Earned at Clemson:</label> <input name="CU_Credits" id="CU_Credits" size="5" value="<?php echo $info['cu_credits'] ?>"/>
						<label for="GPA">GPA:</label> <input name="GPA" id="GPA" size="4" value="<?php echo $info['gpa'] ?>"/><br>
						*Note: This information is available in <a href="https://iroar.clemson.edu" target="_blank">iRoar</a> </p>
					
					<p><label for="Graduation_Date">Expected Date of Graduation:</label> 
						<select id="Graduation_Date" name="Graduation_Date" id="Graduation_Date" size="1">
							<option value="" <?php if ($info['graduation_date'] == ""){echo 'selected="selected"';}?> >Select Date...</option>
							<option value="May 2019" <?php if ($info['graduation_date'] == "May 2019"){echo 'selected="selected"';}?> >May 2019</option>
							<option value="August 2019" <?php if ($info['graduation_date'] == "August 2019"){echo 'selected="selected"';}?> >August 2019</option>
							<option value="December 2019" <?php if ($info['graduation_date'] == "December 2019"){echo 'selected="selected"';}?> >December 2019</option>
							<option value="May 2020" <?php if ($info['graduation_date'] == "May 2020"){echo 'selected="selected"';}?> >May 2020</option>
							<option value="August 2020" <?php if ($info['graduation_date'] == "August 2020"){echo 'selected="selected"';}?> >August 2020</option>
							<option value="December 2020" <?php if ($info['graduation_date'] == "December 2020"){echo 'selected="selected"';}?> >December 2020</option>
							<option value="May 2021" <?php if ($info['graduation_date'] == "May 2021"){echo 'selected="selected"';}?> >May 2021</option>
							<option value="August 2021" <?php if ($info['graduation_date'] == "August 2021"){echo 'selected="selected"';}?> >August 2021</option>
							<option value="December 2021" <?php if ($info['graduation_date'] == "December 2021"){echo 'selected="selected"';}?> >December 2021</option>
							<option value="May 2022" <?php if ($info['graduation_date'] == "May 2022"){echo 'selected="selected"';}?> >May 2022</option>
							<option value="August 2022" <?php if ($info['graduation_date'] == "August 2022"){echo 'selected="selected"';}?> >August 2022</option>
							<option value="December 2022" <?php if ($info['graduation_date'] == "December 2022"){echo 'selected="selected"';}?> >December 2022</option>
							<option value="May 2023" <?php if ($info['graduation_date'] == "May 2023"){echo 'selected="selected"';}?> >May 2023</option>
							<option value="August 2023" <?php if ($info['graduation_date'] == "August 2023"){echo 'selected="selected"';}?> >August 2023</option>
							<option value="December 2023" <?php if ($info['graduation_date'] == "December 2023"){echo 'selected="selected"';}?> >December 2023</option>
							<option value="May 2024" <?php if ($info['graduation_date'] == "May 2024"){echo 'selected="selected"';}?> >May 2024</option>
							<option value="August 2024" <?php if ($info['graduation_date'] == "August 2024"){echo 'selected="selected"';}?> >August 2024</option>
							<option value="December 2024" <?php if ($info['graduation_date'] == "December 2024"){echo 'selected="selected"';}?> >December 2024</option>
							<option value="May 2025" <?php if ($info['graduation_date'] == "May 2025"){echo 'selected="selected"';}?> >May 2025</option>
							<option value="August 2025" <?php if ($info['graduation_date'] == "August 2025"){echo 'selected="selected"';}?> >August 2025</option>
							<option value="December 2025" <?php if ($info['graduation_date'] == "December 20254"){echo 'selected="selected"';}?> >December 2025</option>
					</select>
					<br /><br />
				
					Calhoun Honors College? <label for="calhoun_yes">Yes</label> <input type="radio" name="calhoun" id="calhoun_yes" value="Yes" <?php if ($info['calhoun'] == "Yes"){echo 'checked';}?> /> <label for="calhoun_no">No</label> <input type="radio" name="calhoun" id="calhoun_no" value="No" <?php if ($info['calhoun'] == "No"){echo 'checked';}?> /> <br />
					<label for="Honors_College_Stage">If yes, what stage?</label> <input name="Honors_College_Stage" id="Honors_College_Stage" size="20" value="<?php echo $info['honors_college_stage'] ?>"/></p>


					<p>List courses you are taking this semester (or last semester if you are on co-op assignment):</p>
					<p>
						<label for="Course1">Course 1: </label><input name="Course1" id="Course1" size="25" value="<?php echo $info['course1'] ?>"/>
						<label for="Course2">Course 2: </label><input name="Course2" id="Course2" size="25" value="<?php echo $info['course2'] ?>"/>
						<label for="Course3">Course 3: </label><input name="Course3" id="Course3" size="25" value="<?php echo $info['course3'] ?>"/>
						<label for="Course4">Course 4: </label><input name="Course4" id="Course4" size="25" value="<?php echo $info['course4'] ?>"/>
					</p>
					<p>
						<label for="Course5">Course 5: </label><input name="Course5" id="Course5" size="25" value="<?php echo $info['course5'] ?>"/>
						<label for="Course6">Course 6: </label><input name="Course6" id="Course6" size="25" value="<?php echo $info['course6'] ?>"/>
						<label for="Course7">Course 7: </label><input name="Course7" id="Course7" size="25" value="<?php echo $info['course7'] ?>"/>
						<label for="Course8">Course 8: </label><input name="Course8" id="Course8" size="25" value="<?php echo $info['course8'] ?>"/>
					</p>
				</div>


				<br>


				<h2>Academic Achievements:</h2>
				
				<p>Research and development projects, internships, publications, presentations at professional meetings, study abroad, etc.</p>
				<p>Please limit to 4 of your most significant contributions.</p>
				
				<div class="separator">
					<p><label for="Academic_Achievement1">Activity:</label> <input name="Academic_Achievement1" id="Academic_Achievement1" size="80" value="<?php echo $info['academic_ach1'] ?>"/></p>
					<p><label for="Academic_Title_Description1">Title or Description:</label> <br><textarea cols="100" name="Academic_Title_Description1" id="Academic_Title_Description1"><?php echo $info['academic_title1'] ?></textarea></p>
					<p><label for="Academic_Start_Date1">Start Date:</label> <input name="Academic_Start_Date1" id="Academic_Start_Date1" size="30" value="<?php echo $info['academic_start1'] ?>"/> 
						<label for="Academic_End_Date1">End Date:</label> <input name="Academic_End_Date1" id="Academic_End_Date1" size="30" value="<?php echo $info['academic_end1'] ?>"/> </p>
				</div>
				
				<div class="separator">
					<p><label for="Academic_Achievement2">Activity:</label> <input name="Academic_Achievement2" id="Academic_Achievement2" size="80" value="<?php echo $info['academic_ach2'] ?>"/></p>
					<p><label for="Academic_Title_Description2">Title or Description:</label> <br><textarea cols="100" name="Academic_Title_Description2" id="Academic_Title_Description2"><?php echo $info['academic_title2'] ?></textarea></p>
					<p><label for="Academic_Start_Date2">Start Date:</label> <input name="Academic_Start_Date2" id="Academic_Start_Date2" size="30" value="<?php echo $info['academic_start2'] ?>"/> 
						<label for="Academic_End_Date2">End Date:</label> <input name="Academic_End_Date2" id="Academic_End_Date2" size="30" value="<?php echo $info['academic_end2'] ?>"/> </p>
				</div>
				
				<div class="separator">
					<p><label for="Academic_Achievement3">Activity:</label> <input name="Academic_Achievement3" id="Academic_Achievement3" size="80" value="<?php echo $info['academic_ach3'] ?>"/></p>
					<p><label for="Academic_Title_Description3">Title or Description:</label> <br><textarea cols="100" name="Academic_Title_Description3" id="Academic_Title_Description3"><?php echo $info['academic_title3'] ?></textarea></p>
					<p><label for="Academic_Start_Date3">Start Date:</label> <input name="Academic_Start_Date3" id="Academic_Start_Date3" size="30" value="<?php echo $info['academic_start3'] ?>"/> 
						<label for="Academic_End_Date3">End Date:</label> <input name="Academic_End_Date3" id="Academic_End_Date3" size="30" value="<?php echo $info['academic_end3'] ?>"/> </p>
				</div>
				
				<div class="separator">
					<p><label for="Academic_Achievement4">Activity:</label> <input name="Academic_Achievement4" id="Academic_Achievement4" size="80" value="<?php echo $info['academic_ach4'] ?>"/></p>
					<p><label for="Academic_Title_Description4">Title or Description:</label> <br><textarea cols="100" name="Academic_Title_Description4" id="Academic_Title_Description4"><?php echo $info['academic_title4'] ?></textarea></p>
					<p><label for="Academic_Start_Date4">Start Date:</label> <input name="Academic_Start_Date4" id="Academic_Start_Date4" size="30" value="<?php echo $info['academic_start4'] ?>"/> 
						<label for="Academic_End_Date4">End Date:</label> <input name="Academic_End_Date4" id="Academic_End_Date4" size="30" value="<?php echo $info['academic_end4'] ?>"/> </p>
				</div>
				
				
				<br>
				
				
				
				<h2>Leadership Roles:</h2>
				<p>Please limit to 4 of your most significant roles. Indicate all University related organizations with a "U".</p>
				
				<div class="separator">
					<p><label for="Leader_Organization1">Name of Organization:</label> <input name="Leader_Organization1" id="Leader_Organization1" size="80" value="<?php echo $info['leader_org1'] ?>"/></p>
					<p><label for="Leader_Position1">Position or Activity:</label> <input name="Leader_Position1" id="Leader_Position1" size="80" value="<?php echo $info['leader_pos1'] ?>"/></p>
					<p><label for="Leader_Start_Date1">Start Date:</label> <input name="Leader_Start_Date1" id="Leader_Start_Date1" size="30" value="<?php echo $info['leader_start_date1'] ?>"/> 
						<label for="Leader_End_Date1">End Date:</label> <input name="Leader_End_Date1" id="Leader_End_Date1" size="30" value="<?php echo $info['leader_end_date1'] ?>"/> 
						<label for="Leader_Hrs_Wk1">Avg. Hrs/Wk:</label> <input name="Leader_Hrs_Wk1" id="Leader_Hrs_Wk1" size="15" value="<?php echo $info['leader_hrs_wk1'] ?>"/></p>
					<p><label for="Leader_Accomplishment_Responsibility1">Most Significant Accomplishment / Responsibility:</label><br><textarea cols="100" name="Leader_Accomplishment_Responsibility1" id="Leader_Accomplishment_Responsibility1"><?php echo $info['leader_acc1'] ?></textarea></p>
				</div>
				
				<div class="separator">
					<p><label for="Leader_Organization2">Name of Organization:</label> <input name="Leader_Organization2" id="Leader_Organization2" size="80" value="<?php echo $info['leader_org2'] ?>"/></p>
					<p><label for="Leader_Position2">Position or Activity:</label> <input name="Leader_Position2" id="Leader_Position2" size="80" value="<?php echo $info['leader_pos2'] ?>"/></p>
					<p><label for="Leader_Start_Date2">Start Date:</label> <input name="Leader_Start_Date2" id="Leader_Start_Date2" size="30" value="<?php echo $info['leader_start_date2'] ?>"/> 
						<label for="Leader_End_Date2">End Date:</label> <input name="Leader_End_Date2" id="Leader_End_Date2" size="30" value="<?php echo $info['leader_end_date2'] ?>"/> 
						<label for="Leader_Hrs_Wk2">Avg. Hrs/Wk:</label> <input name="Leader_Hrs_Wk2" id="Leader_Hrs_Wk2" size="15" value="<?php echo $info['leader_hrs_wk2'] ?>"/></p>
					<p><label for="Leader_Accomplishment_Responsibility2">Most Significant Accomplishment / Responsibility:</label><br><textarea cols="100" name="Leader_Accomplishment_Responsibility2" id="Leader_Accomplishment_Responsibility2"><?php echo $info['leader_acc2'] ?></textarea></p>
				</div>
				
				<div class="separator">
					<p><label for="Leader_Organization3">Name of Organization:</label> <input name="Leader_Organization3" id="Leader_Organization3" size="80" value="<?php echo $info['leader_org3'] ?>"/></p>
					<p><label for="Leader_Position3">Position or Activity:</label> <input name="Leader_Position3" id="Leader_Position3" size="80" value="<?php echo $info['leader_pos3'] ?>"/></p>
					<p><label for="Leader_Start_Date3">Start Date:</label> <input name="Leader_Start_Date3" id="Leader_Start_Date3" size="30" value="<?php echo $info['leader_start_date3'] ?>"/> 
						<label for="Leader_End_Date3">End Date:</label> <input name="Leader_End_Date3" id="Leader_End_Date3" size="30" value="<?php echo $info['leader_end_date3'] ?>"/> 
						<label for="Leader_Hrs_Wk3">Avg. Hrs/Wk:</label> <input name="Leader_Hrs_Wk3" id="Leader_Hrs_Wk3" size="15" value="<?php echo $info['leader_hrs_wk3'] ?>"/></p>
					<p><label for="Leader_Accomplishment_Responsibility3">Most Significant Accomplishment / Responsibility:</label><br><textarea cols="100" name="Leader_Accomplishment_Responsibility3" id="Leader_Accomplishment_Responsibility3"><?php echo $info['leader_acc3'] ?></textarea></p>
				</div>
				
				<div class="separator">
					<p><label for="Leader_Organization4">Name of Organization:</label> <input name="Leader_Organization4" id="Leader_Organization4" size="80" value="<?php echo $info['leader_org4'] ?>"/></p>
					<p><label for="Leader_Position4">Position or Activity:</label> <input name="Leader_Position4" id="Leader_Position4" size="80" value="<?php echo $info['leader_pos4'] ?>"/></p>
					<p><label for="Leader_Start_Date4">Start Date:</label> <input name="Leader_Start_Date4" id="Leader_Start_Date4" size="30" value="<?php echo $info['leader_start_date4'] ?>"/> 
						<label for="Leader_End_Date4">End Date:</label> <input name="Leader_End_Date4" id="Leader_End_Date4" size="30" value="<?php echo $info['leader_end_date4'] ?>"/> 
						<label for="Leader_Hrs_Wk4">Avg. Hrs/Wk:</label> <input name="Leader_Hrs_Wk4" id="Leader_Hrs_Wk4" size="15" value="<?php echo $info['leader_hrs_wk4'] ?>"/></p>
					<p><label for="Leader_Accomplishment_Responsibility4">Most Significant Accomplishment / Responsibility:</label><br><textarea cols="100" name="Leader_Accomplishment_Responsibility4" id="Leader_Accomplishment_Responsibility4"><?php echo $info['leader_acc4'] ?></textarea></p>
				</div>


				<br>


				<h2>Participatory Roles:</h2>
				
				<p>University and Non-University Related Organizations. Please limit to 4 of the most significant organizations.</p>
				
				<div class="separator">
					<p><label for="Participate_Organization1">Name of Organization:</label> <input name="Participate_Organization1" id="Participate_Organization1" size="80" value="<?php echo $info['participate_org1'] ?>"/></p>
					<p><label for="Participate_Activities1">Activities in Which You Participated:</label> <br><textarea cols="100" name="Participate_Activities1" id="Participate_Activities1"><?php echo $info['participate_act1'] ?></textarea></p>
					<p><label for="Participate_Start_Date1">Start Date:</label> <input name="Participate_Start_Date1" id="Participate_Start_Date1" size="30" value="<?php echo $info['participate_start1'] ?>"/> 
						<label for="Participate_End_Date1">End Date:</label> <input name="Participate_End_Date1" id="Participate_End_Date1" size="30" value="<?php echo $info['participate_end1'] ?>"/> 
						<label for="Participate_Hrs_Wk1">Avg. Hrs/Wk:</label> <input name="Participate_Hrs_Wk1" id="Participate_Hrs_Wk1" size="15" value="<?php echo $info['participate_hrs_wk1'] ?>"/></p>
				</div>
				
				<div class="separator">
					<p><label for="Participate_Organization2">Name of Organization:</label> <input name="Participate_Organization2" id="Participate_Organization2" size="80" value="<?php echo $info['participate_org2'] ?>"/></p>
					<p><label for="Participate_Activities2">Activities in Which You Participated:</label> <br><textarea cols="100" name="Participate_Activities2" id="Participate_Activities2"><?php echo $info['participate_act2'] ?></textarea></p>
					<p><label for="Participate_Start_Date2">Start Date:</label> <input name="Participate_Start_Date2" id="Participate_Start_Date2" size="30" value="<?php echo $info['participate_start2'] ?>"/> 
						<label for="Participate_End_Date2">End Date:</label> <input name="Participate_End_Date2" id="Participate_End_Date2" size="30" value="<?php echo $info['participate_end2'] ?>"/> 
						<label for="Participate_Hrs_Wk2">Avg. Hrs/Wk:</label> <input name="Participate_Hrs_Wk2" id="Participate_Hrs_Wk2" size="15" value="<?php echo $info['participate_hrs_wk2'] ?>"/></p>
				</div>
				
				<div class="separator">
					<p><label for="Participate_Organization3">Name of Organization:</label> <input name="Participate_Organization3" id="Participate_Organization3" size="80" value="<?php echo $info['participate_org3'] ?>"/></p>
					<p><label for="Participate_Activities3">Activities in Which You Participated:</label> <br><textarea cols="100" name="Participate_Activities3" id="Participate_Activities3"><?php echo $info['participate_act3'] ?></textarea></p>
					<p><label for="Participate_Start_Date3">Start Date:</label> <input name="Participate_Start_Date3" id="Participate_Start_Date3" size="30" value="<?php echo $info['participate_start3'] ?>"/> 
						<label for="Participate_End_Date3">End Date:</label> <input name="Participate_End_Date3" id="Participate_End_Date3" size="30" value="<?php echo $info['participate_end3'] ?>"/> 
						<label for="Participate_Hrs_Wk3">Avg. Hrs/Wk:</label> <input name="Participate_Hrs_Wk3" id="Participate_Hrs_Wk3" size="15" value="<?php echo $info['participate_hrs_wk3'] ?>"/></p>
				</div>
				
				<div class="separator">
					<p><label for="Participate_Organization4">Name of Organization:</label> <input name="Participate_Organization4" id="Participate_Organization4" size="80" value="<?php echo $info['participate_org4'] ?>"/></p>
					<p><label for="Participate_Activities4">Activities in Which You Participated:</label> <br><textarea cols="100" name="Participate_Activities4" id="Participate_Activities4"><?php echo $info['participate_act4'] ?></textarea></p>
					<p><label for="Participate_Start_Date4">Start Date:</label> <input name="Participate_Start_Date4" id="Participate_Start_Date4" size="30" value="<?php echo $info['participate_start4'] ?>"/> 
						<label for="Participate_End_Date4">End Date:</label> <input name="Participate_End_Date4" id="Participate_End_Date4" size="30" value="<?php echo $info['participate_end4'] ?>"/> 
						<label for="Participate_Hrs_Wk4">Avg. Hrs/Wk:</label> <input name="Participate_Hrs_Wk4" id="Participate_Hrs_Wk4" size="15" value="<?php echo $info['participate_hrs_wk4'] ?>"/></p>
				</div>

				
				<br>


				<h2>Academic Awards &amp; Honors:</h2>
				
				<div class="separator">
					<p><label for="Award_Honor1">Award/Honor:</label> <input name="Award_Honor1" id="Award_Honor1" size="70" value="<?php echo $info['award_honor1'] ?>"/>
					<label for="Year_Award_Received1">Year Received:</label> <input name="Year_Award_Received1" id="Year_Award_Received1" size="10" value="<?php echo $info['year_received1'] ?>"/></p>
					<p><label for="Award_Honor2">Award/Honor:</label> <input name="Award_Honor2" id="Award_Honor2" size="70" value="<?php echo $info['award_honor2'] ?>"/>
					<label for="Year_Award_Received2">Year Received:</label> <input name="Year_Award_Received2" id="Year_Award_Received2" size="10" value="<?php echo $info['year_received2'] ?>"/></p>
					<p><label for="Award_Honor3">Award/Honor:</label> <input name="Award_Honor3" id="Award_Honor3" size="70" value="<?php echo $info['award_honor3'] ?>"/>
					<label for="Year_Award_Received3">Year Received:</label> <input name="Year_Award_Received3" id="Year_Award_Received3" size="10" value="<?php echo $info['year_received3'] ?>"/></p>
					<p><label for="Award_Honor4">Award/Honor:</label> <input name="Award_Honor4" id="Award_Honor4" size="70" value="<?php echo $info['award_honor4'] ?>"/>
					<label for="Year_Award_Received4">Year Received:</label> <input name="Year_Award_Received4" id="Year_Award_Received4" size="10" value="<?php echo $info['year_received4'] ?>"/></p>
				</div>

				
				<br>


				<h2>Interests:</h2>
				
				<div class="separator">
					<p><label for="Interests">List what you like to do in your spare time, including hobbies and cultural interests:</label><br>
						<textarea cols="100" rows="4" name="Interests" id="Interests"><?php echo $info['interests'] ?></textarea></p>
				</div>

				
				<br>


				<h2>Other:</h2>
				
				<div class="separator">
					<p><label for="Other">Other important information about yourself, including your plans after graduation:</label><br>
						<textarea cols="100" rows="4" name="Other" id="Other"><?php echo $info['other'] ?></textarea></p>
					<p><label for="Faculty_Member">Faculty member most familiar with your accomplishments:</label> <input name="Faculty_Member" id="Faculty_Member" size="40" value="<?php echo isset($info['faculty_member']) ? $info['faculty_member'] : "" ?>"/></p>
				</div>
				
				<br>
				
				<center><hr style="height:1px"></center>
				<p style="text-align:center;"><input type="checkbox" name="certify" id="certify"></input> <label for="certify">I certify that all of the information contained in this information form is accurate.</label></p>
				

				<center> <input name="save" id="save" type="submit" value="Save Responses" disabled="true"/> </center>
			</form> 
			<br><br>

			
		</div>	
	</div>
</body>
</html>