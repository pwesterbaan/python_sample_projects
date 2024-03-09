<?php

include('summer-plans-functions.php');

//============================
//  PROCESS ENTRY
//============================
if (isset($_POST['submit_summer_plans']))
{
	unset($_POST['submit_summer_plans']);
	$entry = $_POST;
	$entry['year'] = date("Y",strtotime("now"));
	
	$insert_query = $mthsc_db->prepare('INSERT INTO gs_summer_plans (person_id,year,sessions,external_funding,external_funding_description) VALUES (:person_id,:year,:sessions,:external_funding,:external_funding_description) ON DUPLICATE KEY UPDATE sessions = VALUES(sessions), external_funding = VALUES(external_funding), external_funding_description = VALUES(external_funding_description);');
	$result = $insert_query->execute($entry);
	
	if ($result){$message = "Thank you, your plans have been recorded.";}
	else {$message = "Sorry, something went wrong.";}
}



//============================
//  GET INFORMATION TO DISPLAY
//============================
if (isset($user_id))
{
	$person_id = get_person_id_from_user_id($user_id);
	$person_lists = get_lists($person_id);

	if ($person_id != "")
	{
		$roles = get_roles($person_id);
		if (in_array($user_id,$admin_list))
		{
			$roles[] = 'Student';
		}
		
		//get deadlines
		$initial_deadline_query = $mthsc_db->query('SELECT value FROM gs_summer_plans_settings WHERE setting = "initial_deadline" LIMIT 1');
		$initial_deadline = $initial_deadline_query->fetchColumn();
		
		$final_deadline_query = $mthsc_db->query('SELECT value FROM gs_summer_plans_settings WHERE setting = "final_deadline" LIMIT 1');
		$final_deadline = $final_deadline_query->fetchColumn();
	}
	else
	{
		$error = "User not found in Math Student database";
	}
}
else
{$error = "User not in database";}

?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
	"http://www.w3.org/TR/html4/loose.dtd">


<html lang="en-US">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="author" content="hedetni">
	<!-- Date: 2018-M-D -->
	
	<title>School Forms | Summer Plans</title>

<link rel="stylesheet" href="/style/math-internal.css" type="text/css" media="screen" charset="utf-8">

<style type="text/css">


</style>

<script src="/style/jquery-3.2.1.min.js" type="text/javascript" charset="utf-8"></script>


<script type="text/javascript">
function validate()
{
	//REQUIRE SELECTIONS FOR FIRST TWO QUESTIONS AND THIRD IF YES IS SELECTED FOR SECOND
	
	var sessions = $("input[name='sessions']:checked").val();
	var ext_funding = $("input[name='external_funding']:checked").val();
	var ext_funding_desc = $("textarea[name='external_funding_description']").val();
	
	if (sessions && ext_funding=="No")
	{
		$('input#submit_button').prop('disabled',false);
	}
	else if (sessions && ext_funding=="Yes" && $.trim(ext_funding_desc) !== "")
	{
		$('input#submit_button').prop('disabled',false);
	}
	else
	{
		$('input#submit_button').prop('disabled',true);
	}
}

$(document).ready(function(){
	$('input[type=radio]').change(function()
	{
		validate();
	})
	
	$('textarea').keyup(function()
	{
		validate();
	})
	
}); 
</script>

</head>

<body>
	<div id="main">
		<div id="header">
			<div id="app_title">School Forms</div>
			<a href="http://www.clemson.edu/math" title="School Home"><img src="/style/math_logo.png" alt="math and stat logo"></a>
		</div>
		<ul id="nav">
			<?php echo get_nav(); ?>
		</ul>

		<?php echo isset($message) ? '<div id="message">'.$message.'</div>' : '' ?>
		<?php echo isset($error) ? '<div id="error">'.$error.'</div>' : '' ?>
		
		<div id="content">
			<?php if ($person_id != "" && in_array("Student",$roles)): ?>
				<?php if (strtotime("now") < strtotime($final_deadline."+1 day")): ?>
					<h1>Summer Plans</h1>
					
					<p><strong>Please use this form to inform the School about your summer plans. Your plans should be submitted by <u><?php echo $initial_deadline; ?></u>. To update a previous submission for the current year, simply re-submit the form with your updated information. Your old entry will be overwritten.</strong></p>
			
					<br>
			
					<form name="summer_plans_form" method="POST" action="">
						<fieldset>
						<legend>During which sessions do you plan to be here and be on a School Assistantship for this summer? (select one)</legend>
				
						<p><input type="radio" name="sessions" id="first_only" value="First Summer Session Only"></input> <label for="first_only">First Summer Session Only</label><br>
							<input type="radio" name="sessions" id="second_only" value="Second Summer Session Only"></input> <label for="second_only">Second Summer Session Only</label><br>
							<input type="radio" name="sessions" id="both_sessions" value="Both Summer Sessions"></input> <label for="both_sessions">Both Summer Sessions</label><br>
							<input type="radio" name="sessions" id="neither_session" value="Neither Summer Session"></input> <label for="neither_session">I will not be here either summer session</label>
						</p>
						</fieldset>
				
						<br>
						<fieldset>
						<legend>Will you be on a fellowship, grant, or internship this summer? (select one)</legend>
				
						<p><input type="radio" name="external_funding" id="funding_yes" value="Yes"></input> <label for="funding_yes">Yes</label><br>
							<input type="radio" name="external_funding" id="funding_no" value="No"></input> <label for="funding_no">No</label>
						</p>
						</fieldset>
				
						<p><label for="external_funding_description">If so, please describe the funding source, and during which sessions you will be supported.</label></p>
				
						<p><textarea name="external_funding_description" id="external_funding_description" rows="4" cols="60"></textarea></p>
				
						<br>
				
						<p>
							<input type="hidden" name="person_id" value="<?php echo $person_id; ?>"></input>
							<input type="submit" name="submit_summer_plans" value="Submit" id="submit_button" disabled></input>
						</p>
					</form>
				<?php else: ?>
					<p>The deadline has passed to submit summer plans for this year.</p>
				<?php endif; ?>
			<?php endif; ?>

		</div>	
		<div id="footer"><a href="/dept_forms/website_error.py" target="_blank">Report a problem with this website</a></div>
	</div>
	
</body>
</html>