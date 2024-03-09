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

$accepting_submissions = true;
date_default_timezone_set('America/New_York');
$currentTime = strtotime('now');

/////////////////
//CUT OFF DATE FOR SUBMISSIONS
////////////////

$cutoff = mktime(23, 59, 00, 1, 20, 2023);

//------------
// ARCHIVING OLD NOMS:
// INSERT INTO award_noms_archive (name, user_id, self_nom, nominator, letter, year) SELECT name, user_id, self_nom, nominator, letter, year FROM award_noms
//------------

if ($currentTime > $cutoff) 
{
	$accepting_submissions = false;
}

if (isset($_SERVER['REMOTE_USER']))
{
	$nominator = strtoupper($_SERVER['REMOTE_USER']);
}
else
{
	$nominator = strtoupper($_SERVER['cn']);
}

if (isset($_POST['nominate']))
{
	//print_r($_FILES);
	
	$nominee = strtoupper($_POST['user_id']);
	
	//see if user has nominated this person already
	$check_nom = mysql_query('SELECT * FROM award_noms WHERE user_id = "'.$nominee.'" AND nominator = "'.$nominator.'"');
	if ($check_nom)
	{
		if (mysql_num_rows($check_nom) > 0) //submitter already nominated this student, don't store again
		{
			$confirmation = "Thank you. Your nomination has been recorded.";
			unset($_POST['submit']);
		}
		else //user nominating student for first time
		{
			//try to upload rec letter
			//was a letter file selected?
			if ($_FILES['letter']['size']!=0 && !empty($_FILES['letter']['tmp_name']) && !empty($_FILES['letter']['name']))
			{
				//is the selected file of an approved type?
				if ($_FILES['letter']['type']=="application/pdf")
				{
					//proceed
					$ext = strrchr( $_FILES['letter']['name'], '.' );
					$letterFileNameBase = md5(mysql_real_escape_string($nominee).mysql_real_escape_string($nominator)."letter");
					$letterFileName = $letterFileNameBase.$ext;
			
					if (is_uploaded_file($_FILES['letter']['tmp_name'])) 
					{
						$letterFileName = "RecommendationLetters/".$letterFileName;
						$letterMoved = move_uploaded_file($_FILES['letter']['tmp_name'], $letterFileName);
				
						if (!$letterMoved)
						{
							$confirmation = "Error uploading file.";
							$letterSuccess = false;
						}
						else
						{
							$letterSuccess = true;
						}
					}
				}
				else
				{
					$confirmation = "File type not supported for Recommendation Letter. Please upload only .pdf files.";
					$letterSuccess = false;
				}
			}
			else
			{
				//no letter selected, continue anyway
				$letterSuccess = true;
				$letterFileName = "";
			}
			
			if ($letterSuccess)
			{
				//store nomination in database
				$store_nom = mysql_query('INSERT INTO award_noms (name,user_id,self_nom,nominator,letter,year) VALUES ("'.mysql_real_escape_string($_POST['name']).'","'.mysql_real_escape_string($nominee).'","'.mysql_real_escape_string($_POST['self_nom']).'","'.mysql_real_escape_string($nominator).'","'.$letterFileName.'","'.date("Y").'")');
	
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
							$confirmation = "Thank you. Your nomination has been recorded.";
							unset($_POST['submit']);
						}
						else if ($num_noms <= 1)	//first time nominated, send email
						{
							$to = $nominee.'@clemson.edu';
							$subject = "Math & Stat Sciences Award Nomination";
							$message = "Hello,\r\n\r\n";
							$message .= "You have been nominated for a student award in the Clemson Mathematical Sciences Department. ";
							$message .= "For full consideration, please go to the following address,\r\n\r\nhttps://mthsc.clemson.edu/dept_forms/awards/student_info_form.php\r\n\r\nlog in, and fill out the Student Information Form. ";
							$message .= "This information will be used by the awards committee as they evaluate the candidates.\r\n\r\n";
							$message .= "Thank you,\r\nMath Sciences Department";
							$headers = 'From: ugcmath@clemson.edu' . "\r\n" .
										'Reply-To: ugcmath@clemson.edu' . "\r\n" .
										'X-Mailer: PHP/' . phpversion();
				
							mail($to, $subject, $message, $headers);
				
							$confirmation = "Thank you. Your nomination has been recorded and an email has been sent to the nominee.";
							unset($_POST['submit']);
						}
					}
					else //couldn't determine if they have already been nominated, so delete entry so they can try again
					{
						$remove_last = mysql_query('DELETE FROM award_noms WHERE id = "'.$store.'"');
						$confirmation = "Sorry, something went wrong.<br>Your nomination has not been recorded.<br>Please try again.";
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
	
	
	
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>MthSc Student Award Nomination Form</title>
	<meta name="author" content="hedetni">
	<!-- Date: 2016-M-D -->

<link rel="stylesheet" href="/style/math.css" type="text/css" media="screen" title="no title" charset="utf-8">

<style type="text/css">

input {
	font-size:0.9em;
}
input#certify {
	font-size:1.5em;
}

p#confirmation {
	color: #C47002;
	font-size: 1.25em;
	padding:0.75em;
	text-align:center;
}

div.entry {
	background-color:rgba(255, 255, 255, 0.3);
	padding:0.2em 0.6em 0.2em 0.6em;
	margin-bottom:0.5em;
}
fieldset {
	border:none;
	margin:none;
	padding:none;
	padding-inline-start:0;
	padding-block-start: 0;
	padding-inline-end: 0;
	padding-block-end: 0;
}

</style>

<script src="jquery-1.3.min.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">
$(document).ready(function() 
{
	$("input:radio[name=self_nom]").click(function() {
		var value = $(this).val();
		if (value == "1")
		{
			$("#info_form").attr("action","student_info_form.php");
			$("#nominate").attr("disabled", false)
		}
		else if (value == "0")
		{
			$("#info_form").attr("action","");
			$("#nominate").attr("disabled", false)
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
			<h1>Math Sciences Student Award Nomination Form</h1>
			
			<?php echo isset($confirmation) ? '<p id="confirmation">'.$confirmation.'</p>' : "</br>"; ?>
			
			<?php if ($accepting_submissions): ?>
				<form name="info_form" id="info_form" action="" method="POST" enctype="multipart/form-data">
					<p>To nominate a student for a Math Sciences award, please enter their name and user id. <br>
						Note: a faculty nomination with recommendation letter will present a stronger case, so students seeking an award should consider asking a faculty member to write a letter in support of their nomination.</p>
						
					<p>Nominations will be accepted until <?php echo date("F j, Y \a\\t g:i a", $cutoff)?>.</p>
					
					<p>If you have questions regarding the nomination process or Math Sciences awards, contact the <a href="mailto:ugcmath@clemson.edu">Undergraduate Coordinator</a>.</p>
				
					<p><label for="name">Nominee Name: </label><input name="name" id="name" size="40" type="text"></input></p>
				
					<p><label for="user_id">Nominee Clemson Username: </label><input name="user_id" id="user_id" size="10" type="text"></input> <a href="https://my.clemson.edu/#/directory" target="_blank">Look up Username here</a>. (Not XID, no @g.clemson.edu)</p>
				
					<fieldset>
					<p><legend>Are you nominating yourself?</legend><br>
						<input type="radio" name="self_nom" id="self_nom_yes" value="1"> <label for="self_nom_yes">Yes</label> (You will be asked to fill out the student information form in the next step)<br>
						<input type="radio" name="self_nom" id="self_nom_no" value="0"> <label for="self_nom_no">No</label> (An automated email will be sent to the nominated student asking them to fill out the student information form. Supporting letters of recommendation should be uploaded below)<br></p></fieldset>
					
					<p><label for="letter">Letter of recommendation</label>: (faculty members only, must be .pdf) <br>
					<input type="file" name="letter" id="letter"></p>	
				
					<center> <input name="nominate" id="nominate" type="submit" value="Submit Nomination" disabled="yes"/> <input id="reset" name="reset" type="reset" value="Clear"/> </center>
				</form> 
					<br><br>
			<?php elseif (!$accepting_submissions): ?>
				<p id="error">We are no longer accepting nominations.</p>
			<?php endif; ?>

			<p><small>Issues with this form should be sent to hedetni@clemson.edu</small></p>
		</div>	
	</div>
</body>
</html>