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

if (isset($_SERVER['REMOTE_USER']))
{
	$user_id = strtoupper($_SERVER['REMOTE_USER']);
}

date_default_timezone_set('America/New_York');

$get_submissions = mysql_query('SELECT id,last_updated FROM `reorg_survey_2`');
if ($get_submissions)
{
	$submissions = array();
	
	while ($row = mysql_fetch_array($get_submissions))
	{
		$submissions[] = array('id' => $row['id'],
							'last_updated' => date('M j, Y', strtotime($row['last_updated'])));
	}
}
if (!isset($submissions) || count($submissions) < 1)
{
	$confirmation = "No submissions.";
}


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Challenges Survey Submissions</title>
	<meta name="author" content="hedetni">
	<!-- Date: 2016-3-9 -->

<link rel="stylesheet" href="/style/math.css" type="text/css" media="screen" title="no title" charset="utf-8">

<style type="text/css">


</style>

<script src="jquery-1.7.2.js" type="text/javascript" charset="utf-8"></script>


</head>
<body>
	<div id="main">
		<div id="header">
			<a href="http://www.clemson.edu/math" title="Department Home">
				<img src="/style/math_logo.png" alt="math department logo">
			</a>
		</div>
	
		<div id="content">
			<h1 style="text-align:center;">Challenges Survey: Sept 2016 Results</h1>
			
			<?php echo isset($confirmation) ? '<p id="confirmation">'.$confirmation.'</p>' : "</br>"; ?>
			
			<center>
				<p><h3><a href="view_responses.php">View All Survey Response Data</a></h3></p><br>
				<p>Survey Submissions:
			
			<table style="margin-right:auto; margin-left:auto;">
				<tr>
					<th>ID</th>
					<th>Last Updated</th>
					<th>Link</th>
				</tr>
				<?php foreach ($submissions as $sub): ?>
					<tr>
						<td><?php echo 'Submission #'.$sub['id'] ?></td>
						<td><?php echo $sub['last_updated'] ?></td>
						<td style="text-align:center;"><a href="view_submission.php?id=<?php echo $sub['id'] ?>">View Submission</a></td>
					</tr>
				<?php endforeach; ?>
			</table></p>
			</center>
		</div>	
	</div>
</body>
</html>