<?php

include('summer-plans-functions.php');


if (in_array($user_id,$admin_list))
{
	//============================
	//  SET NEW DEADLINEs
	//============================
	if (isset($_POST['set_new_deadlines']))
	{
		$set_initial_deadline_query = $mthsc_db->prepare('UPDATE gs_summer_plans_settings SET value = ? WHERE setting = "initial_deadline";');
		$result = $set_initial_deadline_query->execute(array(date("F j, Y",strtotime($_POST['new_initial_deadline']))));
		
		$set_final_deadline_query = $mthsc_db->prepare('UPDATE gs_summer_plans_settings SET value = ? WHERE setting = "final_deadline";');
		$result = $set_final_deadline_query->execute(array(date("F j, Y",strtotime($_POST['new_final_deadline']))));
		
		if ($result){$message = "Deadlines Set";}
		else {$error = "Error setting deadlines";}
	}
	
	
	//============================
	//  GET CURRENT DEADLINES
	//============================
	$get_final_deadline_query = $mthsc_db->query('SELECT value from gs_summer_plans_settings WHERE setting = "final_deadline";');
	$current_final_deadline = $get_final_deadline_query->fetchColumn();
	
	$get_initial_deadline_query = $mthsc_db->query('SELECT value from gs_summer_plans_settings WHERE setting = "initial_deadline";');
	$current_initial_deadline = $get_initial_deadline_query->fetchColumn();
	
}
else
{
	$error = "Access Denied";
}

?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
	"http://www.w3.org/TR/html4/loose.dtd">


<html lang="en-US">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="author" content="hedetni">
	<!-- Date: 2018-M-D -->
	
	<title>School Forms | Summer Plans Settings</title>

<link rel="stylesheet" href="/style/math-internal.css" type="text/css" media="screen" charset="utf-8">

<style type="text/css">


</style>

<script src="/style/jquery-3.2.1.min.js" type="text/javascript" charset="utf-8"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

<script type="text/javascript">
$(document).ready(function(){
	
	$('.datepicker').datepicker({
			dateFormat: "MM d, yy",
			onClose: function(){$(this).valid();}
		});
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
			<?php if (in_array($user_id,$admin_list)): ?>
				<h1>Summer Plans Settings</h1>
				
				<p>The date shown below in 'Initial deadline to enter plans' is displayed on the form and will not affect the form. The summer plans form will deactivate at the end of the day shown below in 'Final deadline to make changes'. To open up the form for entries, set the final deadline date to some date in the future. To close the form at any time, set the final deadline date to a day that has already passed.</p>
				<form name="summer_plans_deadline_form" action="" method="POST">
					<label for="new_initial_deadline">Initial deadline to enter plans</label>: <input type="text" name="new_initial_deadline" id="new_initial_deadline" value="<?php echo $current_initial_deadline; ?>" class="datepicker"></input> <br>
					<label for="new_final_deadline">Final deadline to make changes</label>: <input type="text" name="new_final_deadline" id="new_final_deadline" value="<?php echo $current_final_deadline; ?>" class="datepicker"></input> 
					<input type="submit" name="set_new_deadlines" value="Set Deadlines"></input>
				</form>
			<?php endif; ?>

		</div>	
		<div id="footer"><a href="/dept_forms/website_error.py" target="_blank">Report a problem with this website</a></div>
	</div>
	
</body>
</html>