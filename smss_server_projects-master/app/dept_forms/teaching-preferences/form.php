<?php
require('teaching-pref-functions.php');


// has the user filled out their preferences for the current term?
$fetch_prefs_query = $mthsc_db->prepare('SELECT * FROM teaching_preferences WHERE term = ? AND person_id = ?;');
$fetch_prefs_query->execute(array($currently_requested_term, $person_id));
$previous_submission = $fetch_prefs_query->fetch();

if ($previous_submission != NULL)
{
	$has_already_submitted_for_term = true;
	$previous_submission['willing_to_teach'] = explode("; ",$previous_submission['willing_to_teach']);
	$previous_submission['time_of_day'] = explode("; ",$previous_submission['time_of_day']);
}
else {$has_already_submitted_for_term = false;}

check_messages();

?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
	"http://www.w3.org/TR/html4/loose.dtd">


<html lang="en-US">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="author" content="hedetni">
	<!-- Date: 2020-1-15 -->
	
	<title>School Forms | Teaching Preferences</title>

<link rel="stylesheet" href="/style/math-internal.css" type="text/css" media="screen" charset="utf-8">

<style type="text/css">
#required_notice {color:red;}

</style>

<script src="/style/jquery-3.2.1.min.js" type="text/javascript" charset="utf-8"></script>


<script type="text/javascript">

function validate_form()
{
	var credit_hours = $("#credit_hours").val();
	if ($("input:radio[name='planning_to_teach']:checked").length > 0 && $.trim(credit_hours) !== "")
	{
		$("#submit_button").attr('disabled',false);
		$("#required_notice").hide();
	}
	else
	{
		$("#submit_button").attr('disabled',true);
		$("#required_notice").show();
	}
}

$(document).ready(function(){
	validate_form();
	$("input[name='planning_to_teach']").change(function(){
		validate_form();
	});
	$("#credit_hours").change(function(){
		validate_form();
	});
}); 
</script>

</head>

<body>
	<div id="main">
		<div id="header">
			<div id="app_title">School Forms</div>
			<a href="http://www.clemson.edu/math" title="School Home"><img src="/style/math_logo.png" alt="school logo"></a>
		</div>
		<ul id="nav" role="navigation" aria-label="main navigation">
			<?php echo get_nav();?>
		</ul>
		<?php echo isset($message) ? '<div id="message">'.$message.'</div>' : '' ?>
		<?php echo isset($error) ? '<div id="error">'.$error.'</div>' : '' ?>
		
		<div id="content" role="main">
			<?php if ($person_id != NULL && $person_id != 0): ?>
				<?php if ($are_submissions_open): ?>
					<h1>Teaching Preferences for <?php echo term_ending_to_semester($currently_requested_term); ?> <?php echo substr($currently_requested_term,0,4); ?></h1>
			
					<form name="teaching_preferences" action="index.php" method="POST">
						<p>Name: <strong><?php echo $user_fullname; ?></strong></p>
			
						<fieldset>
							<legend>1. Do you plan to teach for our school in <?php echo term_ending_to_semester($currently_requested_term); ?> <?php echo substr($currently_requested_term,0,4); ?>?</legend>
							<input type="radio" name="planning_to_teach" id="planning_to_teach" value="Yes" <?php echo isset($previous_submission['planning_to_teach']) && $previous_submission['planning_to_teach'] == "Yes" ? 'checked' : ''; ?> ></input> <label for="planning_to_teach">Yes</label><br>
							<input type="radio" name="planning_to_teach" id="not_planning_to_teach" value="No" <?php echo isset($previous_submission['planning_to_teach']) && $previous_submission['planning_to_teach'] == "No" ? 'checked' : ''; ?> ></input> <label for="not_planning_to_teach">No, and I will explain why I am putting 0 as the answer to the next question.</label>
						</fieldset>
						<br>
				
						<p><label for="credit_hours">2. How many credit hours do you think you should teach in the fall? If this is not your typical teaching load, please explain. If the answer is zero, you do not need to answer the remaining questions, but do briefly tell us why you won't be teaching for us in the spring.</label></p>
						<p><textarea name="credit_hours" id="credit_hours" rows="4" cols="60"><?php echo isset($previous_submission['credit_hours']) ? $previous_submission['credit_hours'] : ''; ?></textarea></p>
						<br>
				
						<fieldset>
							<legend>3. Which of the following courses are you willing to teach (not necessarily prefer, but willing if we needed you to)? Those with an asterisk (*) are coordinated courses with common exams.</legend>
							<input type="checkbox" name="willing_to_teach[]" id="math_1010" value="MATH 1010" <?php echo isset($previous_submission['willing_to_teach']) && in_array("MATH 1010",$previous_submission['willing_to_teach']) ? 'checked' : ''; ?> ></input> <label for="math_1010">MATH 1010*</label><br>
							<input type="checkbox" name="willing_to_teach[]" id="math_1020" value="MATH 1020" <?php echo isset($previous_submission['willing_to_teach']) && in_array("MATH 1020",$previous_submission['willing_to_teach']) ? 'checked' : ''; ?> ></input> <label for="math_1020">MATH 1020*</label><br>
							<input type="checkbox" name="willing_to_teach[]" id="math_1040" value="MATH 1040" <?php echo isset($previous_submission['willing_to_teach']) && in_array("MATH 1040",$previous_submission['willing_to_teach']) ? 'checked' : ''; ?> ></input> <label for="math_1040">MATH 1040* (4 hrs)</label><br>
							<input type="checkbox" name="willing_to_teach[]" id="math_1060" value="MATH 1060" <?php echo isset($previous_submission['willing_to_teach']) && in_array("MATH 1060",$previous_submission['willing_to_teach']) ? 'checked' : ''; ?> ></input> <label for="math_1060">MATH 1060* (4 hrs)</label><br>
							<input type="checkbox" name="willing_to_teach[]" id="math_1070" value="MATH 1070" <?php echo isset($previous_submission['willing_to_teach']) && in_array("MATH 1070",$previous_submission['willing_to_teach']) ? 'checked' : ''; ?> ></input> <label for="math_1070">MATH 1070* (4 hrs)</label><br>
							<input type="checkbox" name="willing_to_teach[]" id="math_1080" value="MATH 1080" <?php echo isset($previous_submission['willing_to_teach']) && in_array("MATH 1080",$previous_submission['willing_to_teach']) ? 'checked' : ''; ?> ></input> <label for="math_1080">MATH 1080* (4 hrs)</label><br>
							<input type="checkbox" name="willing_to_teach[]" id="math_1150" value="MATH 1150, 1160 or 2160" <?php echo isset($previous_submission['willing_to_teach']) && in_array("MATH 1150, 1160 or 2160",$previous_submission['willing_to_teach']) ? 'checked' : ''; ?> ></input> <label for="math_1150">MATH 1150, 1160 or 2160</label><br>
							<input type="checkbox" name="willing_to_teach[]" id="math_2060" value="MATH 2060" <?php echo isset($previous_submission['willing_to_teach']) && in_array("MATH 2060",$previous_submission['willing_to_teach']) ? 'checked' : ''; ?> ></input> <label for="math_2060">MATH 2060 (4 hrs)</label><br>
							<input type="checkbox" name="willing_to_teach[]" id="math_2070" value="MATH 2070" <?php echo isset($previous_submission['willing_to_teach']) && in_array("MATH 2070",$previous_submission['willing_to_teach']) ? 'checked' : ''; ?> ></input> <label for="math_2070">MATH 2070*</label><br>
							<input type="checkbox" name="willing_to_teach[]" id="math_2080" value="MATH 2080" <?php echo isset($previous_submission['willing_to_teach']) && in_array("MATH 2080",$previous_submission['willing_to_teach']) ? 'checked' : ''; ?>></input> <label for="math_2080">MATH 2080 (4 hrs)</label><br>
							<input type="checkbox" name="willing_to_teach[]" id="math_3020" value="MATH 3020" <?php echo isset($previous_submission['willing_to_teach']) && in_array("MATH 3020",$previous_submission['willing_to_teach']) ? 'checked' : ''; ?> ></input> <label for="math_3020">MATH 3020</label><br>
							<input type="checkbox" name="willing_to_teach[]" id="math_3110" value="MATH 3110" <?php echo isset($previous_submission['willing_to_teach']) && in_array("MATH 3110",$previous_submission['willing_to_teach']) ? 'checked' : ''; ?> ></input> <label for="math_3110">MATH 3110</label><br>
							<input type="checkbox" name="willing_to_teach[]" id="math_3150" value="MATH 3150 or 3160" <?php echo isset($previous_submission['willing_to_teach']) && in_array("MATH 3150 or 3160",$previous_submission['willing_to_teach']) ? 'checked' : ''; ?>  ></input> <label for="math_3150">MATH 3150 or 3160</label><br>
							<input type="checkbox" name="willing_to_teach[]" id="math_3600" value="MATH 3600" <?php echo isset($previous_submission['willing_to_teach']) && in_array("MATH 3600",$previous_submission['willing_to_teach']) ? 'checked' : ''; ?> ></input> <label for="math_3600">MATH 3600</label><br>
							<input type="checkbox" name="willing_to_teach[]" id="math_3650" value="MATH 3650" <?php echo isset($previous_submission['willing_to_teach']) && in_array("MATH 3650",$previous_submission['willing_to_teach']) ? 'checked' : ''; ?> ></input> <label for="math_3650">MATH 3650</label><br>
							<input type="checkbox" name="willing_to_teach[]" id="math_4300" value="MATH 4300, 4310 or 4320" <?php echo isset($previous_submission['willing_to_teach']) && in_array("MATH 4300, 4310 or 4320",$previous_submission['willing_to_teach']) ? 'checked' : ''; ?> ></input> <label for="math_4300">MATH 4300, 4310 or 4320</label><br>
							<input type="checkbox" name="willing_to_teach[]" id="stat_2220" value="STAT 2220" <?php echo isset($previous_submission['willing_to_teach']) && in_array("STAT 2220",$previous_submission['willing_to_teach']) ? 'checked' : ''; ?> ></input> <label for="stat_2220">STAT 2220*</label><br>
							<input type="checkbox" name="willing_to_teach[]" id="stat_2300" value="STAT 2300" <?php echo isset($previous_submission['willing_to_teach']) && in_array("STAT 2300",$previous_submission['willing_to_teach']) ? 'checked' : ''; ?> ></input> <label for="stat_2300">STAT 2300*</label><br>
							<input type="checkbox" name="willing_to_teach[]" id="stat_3090" value="STAT 3090" <?php echo isset($previous_submission['willing_to_teach']) && in_array("STAT 3090",$previous_submission['willing_to_teach']) ? 'checked' : ''; ?> ></input> <label for="stat_3090">STAT 3090*</label><br>
						</fieldset>
						<br>
				
						<p><label for="first_pref">4. From the list above, which course would be your <em>first</em> preference to teach?</label></p>
						<p><input type="text" size="30" name="first_pref" id="first_pref" value="<?php echo isset($previous_submission['first_pref']) ? $previous_submission['first_pref'] : ''; ?>"></input></p>
						<br>
				
						<p><label for="second_pref">5. From the list above, which course would be your <em>second</em> preference to teach?</label></p>
						<p><input type="text" size="30" name="second_pref" id="second_pref" value="<?php echo isset($previous_submission['second_pref']) ? $previous_submission['second_pref'] : ''; ?>"></input></p>
						<br>
				
						<p><label for="third_pref">6. From the list above, which course would be your <em>third</em> preference to teach?</label></p>
						<p><input type="text" size="30" name="third_pref" id="third_pref" value="<?php echo isset($previous_submission['third_pref']) ? $previous_submission['third_pref'] : ''; ?>"></input></p>
						<br>
				
						<p><label for="earliest_time">7. We have courses that begin as early as 8:00 AM. What is the earliest time of day you can teach? (Again, not what you prefer, but how early could you teach if we needed you to that semester?)</label></p>
						<p><input type="text" size="30" name="earliest_time" id="earliest_time" value="<?php echo isset($previous_submission['earliest_time']) ? $previous_submission['earliest_time'] : ''; ?>"></input></p>
						<br>
				
						<p><label for="latest_time">8. We have courses that end as late as 5:15 PM. What is the latest time you can be on campus? (Again, not what you prefer, but how late could you be here if we needed you to teach late that semester?)</label></p>
						<p><input type="text" size="30" name="latest_time" id="latest_time" value="<?php echo isset($previous_submission['latest_time']) ? $previous_submission['latest_time'] : ''; ?>"></input></p>
						<br>
				
						<fieldset>
							<legend>9. How do you feel about teaching in our round table rooms?</legend>
							<input type="radio" name="round_table_pref" id="love_round" value="Love it and want to all of the time" <?php echo isset($previous_submission['round_table_pref']) && $previous_submission['round_table_pref'] == "Love it and want to all of the time" ? 'checked' : ''; ?> ></input> <label for="love_round">Love it and want to all of the time</label><br>
							<input type="radio" name="round_table_pref" id="prefer_round" value="Prefer it, but not the end of the world if not in one" <?php echo isset($previous_submission['round_table_pref']) && $previous_submission['round_table_pref'] == "Prefer it, but not the end of the world if not in one" ? 'checked' : ''; ?> ></input> <label for="prefer_round">Prefer it, but not the end of the world if not in one</label><br>
							<input type="radio" name="round_table_pref" id="no_pref_round" value="Don't care" <?php echo isset($previous_submission['round_table_pref']) && $previous_submission['round_table_pref'] == "Don't care" ? 'checked' : ''; ?> ></input> <label for="no_pref_round">Don't care</label><br>
							<input type="radio" name="round_table_pref" id="not_preferred_round" value="Prefer not to" <?php echo isset($previous_submission['round_table_pref']) && $previous_submission['round_table_pref'] == "Prefer not to" ? 'checked' : ''; ?> ></input> <label for="not_preferred_round">Prefer not to</label><br>
							<input type="radio" name="round_table_pref" id="ruined_round" value="My semester is completely ruined if you make me teach in there" <?php echo isset($previous_submission['round_table_pref']) && $previous_submission['round_table_pref'] == "My semester is completely ruined if you make me teach in there" ? 'checked' : ''; ?> ></input> <label for="ruined_round">My semester is completely ruined if you make me teach in there</label><br>
							<input type="radio" name="round_table_pref" id="depends_round" value="It depends on the course I'm teaching" <?php echo isset($previous_submission['round_table_pref']) && $previous_submission['round_table_pref'] == "It depends on the course I'm teaching" ? 'checked' : ''; ?> ></input> <label for="depends_round">It depends on the course I'm teaching</label><br>
						</fieldset>
						<br>
				
						<fieldset>
							<legend>10. Lastly, when would you prefer to teach? Select all that apply. We try to honor preferences, but please know it is not always possible.</legend>
							<input type="checkbox" name="time_of_day[]" id="morning" value="Morning" <?php echo isset($previous_submission['time_of_day']) && in_array("Morning",$previous_submission['time_of_day']) ? 'checked' : ''; ?> ></input> <label for="morning">Morning</label><br>
							<input type="checkbox" name="time_of_day[]" id="midday" value="Midday" <?php echo isset($previous_submission['time_of_day']) && in_array("Midday",$previous_submission['time_of_day']) ? 'checked' : ''; ?> ></input> <label for="midday">Midday</label><br>
							<input type="checkbox" name="time_of_day[]" id="afternoon" value="Afternoon" <?php echo isset($previous_submission['time_of_day']) && in_array("Afternoon",$previous_submission['time_of_day']) ? 'checked' : ''; ?> ></input> <label for="afternoon">Afternoon</label><br>
						</fieldset>
						<br>
				
						<p><label for="comments">11. Is there anything else you think we should know or consider before completing the spring schedule? If you are wanting to teach in one of our large scale-up rooms, please let me know here.</label></p>
						<p><textarea name="comments" id="comments" rows="4" cols="60"><?php echo isset($previous_submission['comments']) ? $previous_submission['comments'] : ''; ?></textarea></p>
						<br>
				
						<input type="hidden" name="person_id" value="<?php echo $person_id;?>"></input>
						<input type="hidden" name="term" value="<?php echo $currently_requested_term;?>"></input>
						<?php if (!$has_already_submitted_for_term): ?>
							<input type="submit" name="submit_preferences" id="submit_button" value="Submit Preferences" disabled></input>
						<?php else: ?>
							<input type="submit" name="update_preferences" id="submit_button" value="Update Preferences" disabled></input>
						<?php endif; ?>
						<p id="required_notice">Questions 1 and 2 are required.</p>
					</form>
				<?php else: ?>
					<p>Teaching preferences are not currently being accepted.</p>
				<?php endif; ?>
			<?php else: ?>
				<p>Access Denied</p>
			<?php endif; ?>
		</div>	
		<div id="footer"><a href="/dept_forms/website_error.py" target="_blank">Report a problem with this website</a></div>
	</div>
	
</body>
</html>