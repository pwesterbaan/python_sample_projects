<?php

include('summer-plans-functions.php');


if (in_array($user_id,$admin_list))
{
	//============================
	//  GET INFORMATION TO DISPLAY
	//============================
	if (isset($_GET['year']) && is_numeric($_GET['year']) && $_GET['year'] != "")
	{
		//just get that year's entries
		$year = $_GET['year'];
	}
	else
	{
		//get current year's entries
		$year = date("Y",strtotime("now"));
	}
	
	//GET YEARS IN DATABASE TO DISPLAY AS OPTIONS
	$get_years_query = $mthsc_db->query('SELECT year from gs_summer_plans GROUP BY year ORDER BY year DESC;');
	$years_in_database = $get_years_query->fetchAll(PDO::FETCH_COLUMN);
	
	//GET ENTRIES FOR SELECTED YEAR
	$get_entries_query = $mthsc_db->prepare('SELECT * FROM gs_summer_plans INNER JOIN dept_info.person USING (person_id) WHERE year = ? ORDER BY last_name ASC;');
	$get_entries_query->execute(array($year));
	$entries = $get_entries_query->fetchAll();
	
	//GET LIST OF PERSON IDs
	$get_personids_query = $mthsc_db->prepare('SELECT person_id FROM gs_summer_plans INNER JOIN dept_info.person USING (person_id) WHERE year = ? ORDER BY last_name ASC;');
	$get_personids_query->execute(array($year));
	$person_ids = $get_personids_query->fetchAll(PDO::FETCH_COLUMN);
	
	//GENERATE DOWNLOAD
	$rows = "Year,Student,User ID,Sessions Attending,\"Will you be on a fellowship, grant, or internship this summer?\",Last Updated\n";
	foreach ($entries as $entry)
	{
		$rows .= $year.',';
		$rows .= '"'.$entry['last_name'].", ".$entry['first_name'].'",';
		$rows .= $entry['username'].',';
		$rows .= $entry['sessions'].',';
		$rows .= '"'.$entry['external_funding'];
		if ($entry['external_funding'] == 'Yes')
		{
			$rows .= ": ".$entry['external_funding_description'];
		}
		$rows .= '",';
		$rows .= $entry['last_updated'].','."\r\n";
	}
	
	if ($year == date("Y",strtotime("now"))) //if we're looking at this year, find students who haven't submitted yet
	{
		//GET LIST OF ACTIVE STUDENTS (to find those who have yet to enter plans)
		$active_students = get_all_active_students();
	
		$missing_students = array();
		foreach ($active_students as $active)
		{
			if (!in_array($active['person_id'],$person_ids))
			{
				$missing_students[] = $active;
			}
		}
		
		//GENERATE EMAIL LINK
		$missing_email_link = 'mailto:';
		foreach ($missing_students as $missing)
		{
			$missing_email_link .= $missing['username'].'@clemson.edu,';
		}
		$missing_email_link = substr($missing_email_link,0,-1);
	}
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
	
	<title>School Forms | Summer Plans</title>

<link rel="stylesheet" href="/style/math-internal.css" type="text/css" media="screen" charset="utf-8">

<style type="text/css">


</style>

<script src="/style/jquery-3.2.1.min.js" type="text/javascript" charset="utf-8"></script>


<script type="text/javascript">
function generate_csv()
{
	var csv = <?php echo json_encode($rows); ?>;
	var link = document.createElement('a');
	var d = new Date();
	link.download = 'Summer Plans <?php echo $year; ?>.csv';
	link.href = 'data:text/csv;charset=utf-8,'+escape(csv);
	document.body.appendChild(link);
	link.click();
	link.remove();
}

function toggle_missing_students()
{
	$('#missing_students').slideToggle();
}

$(document).ready(function(){
	
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
				<!-- YEAR SELECTOR -->
				<p>Select a year: 
					<?php foreach ($years_in_database as $selectable_year): ?>
						<a href="?year=<?php echo $selectable_year; ?>"><?php echo $selectable_year; ?></a> 
					<?php endforeach;?>
				</p>
				
				<?php if (isset($year)): ?>
					<hr>
				
					<h1>Summer Plan Entries for <?php echo $year; ?></h1>
				
					<p><a href="javascript:generate_csv();" >Download This Table</a></p>
					
					<table class="styled">
						<tr>
							<th>Student</th>
							<th>User ID</th>
							<th>Sessions Attending</th>
							<th>Will you be on a fellowship, grant, or internship this summer?</th>
							<th>Last Updated</th>
						</tr>
					
						<?php if (count($entries)>0): ?>
							<?php foreach ($entries as $entry): ?>
								<tr>
									<td><?php echo $entry['last_name'].', '.$entry['first_name']; ?></td>
									<td><?php echo $entry['username']; ?></td>
									<td><?php echo $entry['sessions']; ?></td>
									<td><?php echo "<strong>".$entry['external_funding']."</strong>"; if ($entry['external_funding'] == 'Yes'){echo ": ".$entry['external_funding_description']; }?></td>
									<td><?php echo $entry['last_updated']; ?></td>
								</tr>
							<?php endforeach; ?>
						<?php else: ?>
							<tr><td colspan="5" class="text-center">No entries for selected year</td></tr>
						<?php endif; ?>
					</table>
						
					<?php if ($year == date("Y",strtotime("now"))): ?>
						<p><a href="javascript:toggle_missing_students()">View active students who have not entered summer plans for this year</a></p>
						<div id="missing_students" style="display:none;">
							<p><a href="<?php echo $missing_email_link; ?>">Email missing students</a></p>
							<table class="styled">
								<tr>
									<th colspan="2">Missing Students</th>
								<tr>
								<tr>
									<th>Name</th>
									<th>User ID</th>
								</tr>
								<?php foreach ($missing_students as $student): ?>
									<tr>
										<td><?php echo $student['last_name'].', '.$student['first_name']; ?></td>
										<td><?php echo $student['username']; ?></td>
									</tr>
								<?php endforeach; ?>
							</table>
						</div>
					<?php endif; ?>
					
				<?php endif; ?>
				
			<?php endif; ?>

		</div>	
		<div id="footer"><a href="/dept_forms/website_error.py" target="_blank">Report a problem with this website</a></div>
	</div>
	
</body>
</html>