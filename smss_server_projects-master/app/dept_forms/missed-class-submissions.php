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

if (isset($_GET['year']) && $_GET['year'] != "" && is_numeric($_GET['year']))
{
	$start = $_GET['year'];
	$end = $_GET['year']+1;
	$get_submissions = mysql_query('SELECT * FROM `missed_class` JOIN dept_info.person ON (user_id = username) WHERE year(date_submitted) >= '.$start.' AND year(date_submitted) < '.$end.' ORDER BY date_submitted DESC');
	if ($get_submissions)
	{
		$submissions = array();
	
		while ($row = mysql_fetch_array($get_submissions))
		{
			if ($row['class_date_end'] === "0000-00-00"){$date_end = date('M j, Y', strtotime($row['class_date']));} else {$date_end = date('M j, Y', strtotime($row['class_date_end']));}
			$submissions[] = array('notification_id' => $row['notification_id'],
								'date_submitted' => date('M j, Y, g:i A', strtotime($row['date_submitted'])),
								'user_id' => $row['user_id'],
								'first_name' => $row['first_name'],
								'last_name' => $row['last_name'],
								'course' => $row['course'],
								'section' => $row['section'],
								'class_date' => date('M j, Y', strtotime($row['class_date'])),
								'class_date_end' => $date_end,
								'reason' => $row['reason'],
								'cover' => $row['cover']);
		}
	}
}
else
{
	$year = date("Y");
	$get_submissions = mysql_query('SELECT * FROM `missed_class` JOIN dept_info.person ON (user_id = username) WHERE date_submitted > "'.$year.'-01-01 00:00:00" ORDER BY date_submitted DESC');
	if ($get_submissions)
	{
		$submissions = array();
	
		while ($row = mysql_fetch_array($get_submissions))
		{
			if ($row['class_date_end'] === "0000-00-00"){$date_end = date('M j, Y', strtotime($row['class_date']));} else {$date_end = date('M j, Y', strtotime($row['class_date_end']));}
			$submissions[] = array('notification_id' => $row['notification_id'],
								'date_submitted' => date('M j, Y, g:i A', strtotime($row['date_submitted'])),
								'user_id' => $row['user_id'],
								'first_name' => $row['first_name'],
								'last_name' => $row['last_name'],
								'course' => $row['course'],
								'section' => $row['section'],
								'class_date' => date('M j, Y', strtotime($row['class_date'])),
								'class_date_end' => $date_end,
								'reason' => $row['reason'],
								'cover' => $row['cover']);
		}
	}
}

if (!isset($submissions) || count($submissions) < 1)
{
	$message = "Either there was an error retrieving the requests, or no missed classes have been submitted.";
}

//get years
$get_years = mysql_query('SELECT YEAR(date_submitted) as subyear FROM `missed_class` GROUP BY subyear ORDER BY subyear');
if ($get_years)
{
	$years = array();
	
	while ($row = mysql_fetch_array($get_years))
	{
		$years[] = $row['subyear'];
	}
}


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Missed Class Notifications</title>
	<meta name="author" content="hedetni">
	<!-- Date: 2016-8-15 -->

<link rel="stylesheet" href="/style/math.css" type="text/css" media="screen" title="no title" charset="utf-8">

<style type="text/css">
.centered {
	text-align:center;
}

</style>

<script src="/style/jquery-1.3.min.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">
$(document).ready(function() 
{
	$('tr.details').hide();
	
	$('a.detailsLink').click(function() {
		$(this).closest("tr").next().toggle();
		return false;
	});
	
	
});
</script>

</head>
<body>
	<div id="main">
		<div id="header">
			<a href="http://www.clemson.edu/math" title="Department Home">
				<img src="/style/math_logo.png" alt="math department logo">
			</a>
			<h1>Missed Class Submissions</h1>
		</div>
	
		<div id="content">
			<h1>Missed Class Submissions:</h1><br>
			<?php echo isset($message) ? '<p id="error">'.$message.'</p>' : ""; ?>
			
				<p>Select Year: 
					<?php foreach ($years as $year): ?>
						<a href="missed-class-submissions.php?year=<?php echo $year;?>"><?php echo $year;?></a>&nbsp;&nbsp;
					<?php endforeach;?>
				</p>
			
				<?php foreach ($submissions as $sub): ?>
				
				<table style="margin-right:auto; margin-left:auto;" width="80%">
					<tr><th colspan="3">Submitted by <b><?php echo $sub['first_name'].' '.$sub['last_name'];?>, <?php echo $sub['user_id']?></b> on <?php echo $sub['date_submitted']?></th></tr>
					<tr>
						<td class="centered"><b>Course:</b> <?php echo $sub['course'] ?></td>
						<td class="centered"><b>Section:</b> <?php echo $sub['section'] ?></td>
						<td class="centered"><b>Dates:</b> <?php echo $sub['class_date'].' - '.$sub['class_date_end'] ?></td>
					</tr>
					<tr>
						<td colspan="3"><b>Reason:</b> <?php echo $sub['reason'] ?></td>
					</tr>
					<tr>
						<td colspan="3"><b>Result:</b> <?php echo $sub['cover'] ?></td>
					</tr>
				</table>
				<br><br>
			<?php endforeach; ?>

		</div>	
	</div>
</body>
</html>