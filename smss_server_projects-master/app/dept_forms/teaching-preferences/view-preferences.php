<?php
require('teaching-pref-functions.php');

if (isset($_GET['id']) && is_numeric($_GET['id']) && $_GET['id'] != 0 && $_GET['id'] != "")
{
	$fetch_prefs_query = $mthsc_db->prepare('SELECT * FROM teaching_preferences JOIN dept_info.person using (person_id) WHERE pref_id= ?;');
	$fetch_prefs_query->execute(array($_GET['id']));
	$pref = $fetch_prefs_query->fetch();
	
	if ($pref)
	{	$pref['willing_to_teach'] = explode("; ",$pref['willing_to_teach']);
		$pref['time_of_day'] = explode("; ",$pref['time_of_day']);
	}
}


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
p.response {margin-left:1em;}
p.response, ul.response {margin-bottom:1.5em;}
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
			<?php if ($pref != NULL && ($pref['person_id'] == $person_id || in_array($user_id,$admins))): ?>
				<h1>Teaching Preferences from <?php echo $pref['first_name'].' '.$pref['last_name']; ?> for <?php echo term_ending_to_semester($pref['term']); ?> <?php echo substr($pref['term'],0,4); ?></h1>
				<p style="font-size:small;">Last Updated: <?php echo $pref['last_updated']; ?></p>
			
				<?php if ($pref['term'] == $currently_requested_term && $are_submissions_open && $pref['person_id'] == $person_id): ?>
					<p><a href="form.php">Edit these preferences</a></p>
				<?php endif; ?>
				<br>
				
				<p>Name: <strong><?php echo $pref['first_name'].' '.$pref['last_name']; ?></strong><br>
					User ID: <strong><?php echo $pref['username']; ?></strong></p>
			
				<p>1. Do you plan to teach for our school in <?php echo term_ending_to_semester($pref['term']); ?> <?php echo substr($pref['term'],0,4); ?>?</p>
				<p class="response"><?php echo $pref['planning_to_teach']; ?></p>
				
				<p>2. How many credit hours do you think you should teach in the fall? If this is not your typical teaching load, please explain. If the answer is zero, you do not need to answer the remaining questions, but do briefly tell us why you won't be teaching for us in the spring.</p>
				<p class="response"><?php echo $pref['credit_hours']?></p>
				
				<p>3. Which courses are you willing to teach (not necessarily prefer, but willing if we needed you to)? Those with an asterisk (*) are coordinated courses with common exams.</p>
				<ul class="response">
					<?php foreach ($pref['willing_to_teach'] as $course): ?>
						<li><?php echo $course; ?></li>
					<?php endforeach; ?>
				</ul>
			
				<p>4. From the list above, which course would be your <em>first</em> preference to teach?</p>
				<p class="response"><?php echo $pref['first_pref']; ?></p>
				
				<p>5. From the list above, which course would be your <em>second</em> preference to teach?</p>
				<p class="response"><?php echo $pref['second_pref']; ?></p>
				
				<p>6. From the list above, which course would be your <em>third</em> preference to teach?</p>
				<p class="response"><?php echo $pref['third_pref']; ?></p>
			
				<p>7. We have courses that begin as early as 8:00 AM. What is the earliest time of day you can teach? (Again, not what you prefer, but how early could you teach if we needed you to that semester?)</p>
				<p class="response"><?php echo $pref['earliest_time']; ?></p>
			
				<p>8. We have courses that end as late as 5:15 PM. What is the latest time you can be on campus? (Again, not what you prefer, but how late could you be here if we needed you to teach late that semester?)</p>
				<p class="response"><?php echo $pref['latest_time']; ?></p>
			
				<p>9. How do you feel about teaching in our round table rooms?</p>
				<p class="response"><?php echo $pref['round_table_pref']; ?></p>
			
				<p>10. Lastly, when would you prefer to teach? We try to honor preferences, but please know it is not always possible.</p>
				<ul class="response">
					<?php foreach ($pref['time_of_day'] as $time): ?>
						<li><?php echo $time; ?></li>
					<?php endforeach; ?>
				</ul>
			
				<p>11. Is there anything else you think we should know or consider before completing the spring schedule? If you are wanting to teach in one of our large scale-up rooms, please let me know here.</p>
				<p class="response"><?php echo $pref['comments']; ?></p>

			<?php else: ?>
				<p>Access Denied</p>
			<?php endif; ?>
		</div>	
		<div id="footer"><a href="/dept_forms/website_error.py" target="_blank">Report a problem with this website</a></div>
	</div>
	
</body>
</html>