<?php

require('teaching-pref-functions.php');

if (in_array($user_id, $admins))
{
	if (isset($_POST['open_form']))
	{
		$allow_query = $mthsc_db->query('UPDATE settings SET value = 1 WHERE name = "teaching_pref_open";');
		$_SESSION['message'] = "Form Opened";
		http_response_code( 303 );
		header("Location: ".$_SERVER['REQUEST_URI']);
		exit;
	}
	if (isset($_POST['close_form']))
	{
		$allow_query = $mthsc_db->query('UPDATE settings SET value = 0 WHERE name = "teaching_pref_open";');
		$_SESSION['message'] = "Form Closed";
		http_response_code( 303 );
		header("Location: ".$_SERVER['REQUEST_URI']);
		exit;
	}
	if (isset($_POST['update_term']))
	{
		$update_query = $mthsc_db->prepare('UPDATE settings SET value = ? WHERE name = "teaching_pref_current_term";');
		$update_query->execute(array($_POST['requested_term']));
		$_SESSION['message'] = "Term Updated";
		http_response_code( 303 );
		header("Location: ".$_SERVER['REQUEST_URI']);
		exit;
	}

	$currently_requested_term = get_currently_requested_term();
	$are_submissions_open = get_submission_status();

	//get terms
	$terms_query = $mthsc_db->query('SELECT term FROM `teaching_preferences` GROUP BY term ORDER BY term DESC');
	$terms = $terms_query->fetchAll(PDO::FETCH_COLUMN);
	$latest_term = $terms[0];

	if (isset($_POST['term']))
	{
		$term_to_get = $_POST['term'];
	}
	else
	{
		$term_to_get = $latest_term;
	}

	$submissions_query = $mthsc_db->prepare('SELECT * FROM `teaching_preferences` JOIN dept_info.person USING(person_id) WHERE term = ? ORDER BY last_updated DESC');
	$submissions_result = $submissions_query->execute(array($term_to_get));
	$submissions = $submissions_query->fetchAll();

	// create csv
	$rows = "Term, Last Name, First Name, User ID, Planning to Teach, Credit Hours, Willing to Teach, First Pref, Second Pref, Third Pref, Earliest Time, Latest Time, Round Table Pref, Time of Day, Comments, Last Updated\r\n";
	foreach ($submissions as $sub_row)
	{
		$rows .= $sub_row['term'].",";
		$rows .= $sub_row['last_name'].",";
		$rows .= $sub_row['first_name'].",";
		$rows .= $sub_row['username'].",";
		$rows .= '"'.$sub_row['planning_to_teach'].'",';
		$rows .= '"'.$sub_row['credit_hours'].'",';
		$rows .= '"'.$sub_row['willing_to_teach'].'",';
		$rows .= '"'.$sub_row['first_pref'].'",';
		$rows .= '"'.$sub_row['second_pref'].'",';
		$rows .= '"'.$sub_row['third_pref'].'",';
		$rows .= '"'.$sub_row['earliest_time'].'",';
		$rows .= '"'.$sub_row['latest_time'].'",';
		$rows .= '"'.$sub_row['round_table_pref'].'",';
		$rows .= '"'.$sub_row['time_of_day'].'",';
		$rows .= '"'.$sub_row['comments'].'",';
		$rows .= $sub_row['last_updated'].",";
		$rows .= "\r\n";
	}

	check_messages();
}
else
{
	$term_to_get = "";
	$rows = "";
	$error = "Access Denied";
}
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
	"http://www.w3.org/TR/html4/loose.dtd">


<html lang="en-US">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="author" content="hedetni">
	<!-- Date: 2020-1-16 -->
	
	<title>School Forms | Teaching Preferences</title>

<link rel="stylesheet" href="/style/math-internal.css" type="text/css" media="screen" charset="utf-8">

<style type="text/css">


</style>

<script src="/style/jquery-3.2.1.min.js" type="text/javascript" charset="utf-8"></script>


<script type="text/javascript">

function generate_csv()
{
	var csv = <?php echo json_encode($rows); ?>;
	var link = document.createElement('a');
	link.download = 'Teaching Preferences <?php echo $term_to_get;?>.csv';
	link.href = 'data:text/csv;charset=utf-8,'+escape(csv);
	document.body.appendChild(link);
	link.click();
	link.remove();
}

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
			<?php if (in_array($user_id,$admins)): ?>
				<h1>Teaching Preferences Admin</h1>
			
				<form name="update_term_form" method="POST" action="">
					<p><label for="requested_term">Requested Term</label>: <input type="text" name="requested_term" id="requested_term" size="6" value="<?php echo $currently_requested_term;?>"></input> <input type="submit" name="update_term" value="Update Term"></input></p>
				</form>
			
				<form name="open-close-teaching-preferences-form" method="POST" action="">
					<p>Teaching Preferences form is <?php echo $are_submissions_open ? 'open' : 'closed'; ?> <input type="submit" name="<?php echo $are_submissions_open ? 'close_form' : 'open_form'; ?>" value="<?php echo $are_submissions_open ? 'Close Teaching Preferences Form' : 'Open Teaching Preferences Form'; ?>"></input></p>
				</form>
			
				<br>
				<h2>View Teaching Preferences</h2>
				<form name="term_selector" method="POST" action="">
					<p><label for="term">Select a term</label>: 
						<select name="term" id="term">
							<option value="">Select a term...</option>
							<?php foreach ($terms as $term): ?>
								<option value="<?php echo $term;?>" <?php echo $term == $term_to_get ? 'selected' : ''; ?>><?php echo $term;?></option>
							<?php endforeach; ?>
						</select> 
					<input type="submit" name="select_a_term" value="View Term"></input></p>
				</form>
				<br>
				<table class="styled">
					<caption>Submissions for <?php echo $term_to_get; ?>  <a href="javascript:generate_csv();">Download Full CSV File</a></caption>
					<tr>
						<th>View Full</th>
						<th>Person</th>
						<th>First Preference</th>
						<th>Second Preference</th>
						<th>Third Preference</th>
						<th>Preferred Time</th>
						<th>Last Updated</th>
					</tr>
				<?php foreach ($submissions as $sub): ?>
					<tr>
						<td class="text-center"><a href="view-preferences.php?id=<?php echo $sub['pref_id'];?>">View</a></td>
						<td><?php echo $sub['last_name'].', '.$sub['first_name']; ?></td>
						<td><?php echo $sub['first_pref']; ?></td>
						<td><?php echo $sub['second_pref']; ?></td>
						<td><?php echo $sub['third_pref']; ?></td>
						<td><?php echo $sub['time_of_day']; ?></td>
						<td><?php echo $sub['last_updated']; ?></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>	
		<div id="footer"><a href="/dept_forms/website_error.py" target="_blank">Report a problem with this website</a></div>
	</div>
	
</body>
</html>