<?php

require('teaching-pref-functions.php');

if (isset($_POST['submit_preferences']) || isset($_POST['update_preferences']))
{
	$preferences = $_POST;
	unset($preferences['submit_preferences']);
	unset($preferences['update_preferences']);
	if (!isset($preferences['planning_to_teach'])){$preferences['planning_to_teach'] = "";}
	if (!isset($preferences['round_table_pref'])){$preferences['round_table_pref'] = "";}
	if (!isset($preferences['willing_to_teach'])){$preferences['willing_to_teach'] = array();}
	if (!isset($preferences['time_of_day'])){$preferences['time_of_day'] = array();}
	$preferences['willing_to_teach'] = implode('; ',$preferences['willing_to_teach']);
	$preferences['time_of_day'] = implode('; ',$preferences['time_of_day']);
}

if (isset($_POST['submit_preferences']))
{
	// insert into database
	$insert_query = $mthsc_db->prepare('INSERT INTO teaching_preferences (person_id,term,planning_to_teach,credit_hours,willing_to_teach,first_pref,second_pref,third_pref,earliest_time,latest_time,round_table_pref,time_of_day,comments) VALUES (:person_id,:term,:planning_to_teach,:credit_hours,:willing_to_teach,:first_pref,:second_pref,:third_pref,:earliest_time,:latest_time,:round_table_pref,:time_of_day,:comments)');
	$insert_result = $insert_query->execute($preferences);
	
	if ($insert_result)
	{
		$_SESSION['message'] = "Preferences Submitted";
		http_response_code( 303 );
		header("Location: ".$_SERVER['REQUEST_URI']);
		exit;
	}
	else
	{
		$_SESSION['error'] = "Error: preferences not saved";
		http_response_code( 303 );
		header("Location: ".$_SERVER['REQUEST_URI']);
		exit;
	}
}

if (isset($_POST['update_preferences']))
{
	// update database
	$update_query = $mthsc_db->prepare('UPDATE teaching_preferences SET planning_to_teach = :planning_to_teach, credit_hours = :credit_hours, willing_to_teach = :willing_to_teach, first_pref = :first_pref, second_pref = :second_pref, third_pref = :third_pref, earliest_time = :earliest_time, latest_time = :latest_time, round_table_pref = :round_table_pref, time_of_day = :time_of_day, comments = :comments WHERE person_id = :person_id AND term = :term LIMIT 1;');
	$update_result = $update_query->execute($preferences);
	
	if ($update_result)
	{
		$_SESSION['message'] = "Preferences Updated";
		http_response_code( 303 );
		header("Location: ".$_SERVER['REQUEST_URI']);
		exit;
	}
	else
	{
		$_SESSION['error'] = "Error: preferences not updated";
		http_response_code( 303 );
		header("Location: ".$_SERVER['REQUEST_URI']);
		exit;
	}
}

$users_preferences = get_all_preferences_for_person($person_id);

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


</style>

<script src="/style/jquery-3.2.1.min.js" type="text/javascript" charset="utf-8"></script>


<script type="text/javascript">
$(document).ready(function(){
	
	
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
				<h1>Teaching Preferences</h1>
			
				<?php if ($are_submissions_open): ?>
					<p>Teaching preferences are currently being accepted for <?php echo term_ending_to_semester($currently_requested_term); ?> <?php echo substr($currently_requested_term,0,4); ?>. <a href="form.php">Submit or Update your Teaching Preferences Here</a>.</p>
				<?php else: ?>
					<p>Teaching preferences are not being accepted at this time.</p>
				<?php endif; ?>
			
				<?php if (count($users_preferences) > 0 ): ?>
					<br>
					<h2>My Preferences</h2>
					<ul style="list-style-type:none;">
					<?php foreach ($users_preferences as $term_preference): ?>
						<li><a href="view-preferences.php?id=<?php echo $term_preference['pref_id'];?>"><?php echo term_ending_to_semester($term_preference['term']).' '.substr($term_preference['term'],0,4); ?></a></li>
					<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			<?php endif; ?>
		</div>	
		<div id="footer"><a href="/dept_forms/website_error.py" target="_blank">Report a problem with this website</a></div>
	</div>
	
</body>
</html>