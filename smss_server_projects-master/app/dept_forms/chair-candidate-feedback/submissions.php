<?php

$host = 'mthsc.clemson.edu';
$db   = 'forms';
$user = 'forms';
$pass = 'd8ta_c0l';
$charset = 'utf8';

$dsn = 'mysql:host='.$host.';dbname='.$db.';charset='.$charset;
$opt = array(
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false
);
$mthsc_db = new PDO($dsn, $user, $pass, $opt);

date_default_timezone_set('America/New_York');

if (isset($_SERVER['REMOTE_USER']))
{
	$user_id = strtoupper($_SERVER['REMOTE_USER']);
}

function submission_count($candidate)
{
	if ($candidate === 'summary')
	{
		global $mthsc_db;
		$stmt = $mthsc_db->prepare("SELECT count(*) FROM `chair_candidate_feedback_overall`");
		$stmt->execute();
		return $stmt->fetchColumn();
	}
	else
	{
		global $mthsc_db;
		$stmt = $mthsc_db->prepare("SELECT count(candidate) FROM `chair_candidate_feedback` WHERE candidate = ?");
		$stmt->execute(array($candidate));
		return $stmt->fetchColumn();
	}
}


$submissions = array(null,
					submission_count('Beatrice Riviere'),
					submission_count('Mark Gockenbach'),
					submission_count('Tim Hodges'),
					submission_count('Joshua Tebbs'),
					submission_count('Kevin James'),
					submission_count('Tamàs Terlaky'),
					submission_count('summary'));



?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Chair Candidate Feedback</title>
	<meta name="author" content="hedetni">
	<!-- Date: 2017-10-30 -->

<link rel="stylesheet" href="/style/math.css" type="text/css" media="screen" title="no title" charset="utf-8">

<style type="text/css">


</style>

<script src="/style/jquery-3.2.1.min.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">
$(document).ready(function() 
{

	
});
</script>

</head>
<body>
	<div id="main">
		<div id="header">
			<a href="http://www.clemson.edu/math" title="Department Home">
				<img src="/style/math_logo.png" alt="math department logo">
			</a>
			<h1><a href="index.php">Chair Candidate Feedback</a></h1>
		</div>
	
		<div id="content">
			<h1>2017 Chair Search Feedback</h1>
			<table>
				<tr>
					<th>Candidate</th>
					<th>Affiliation</th>
					<th>Visiting Dates</th>
					<th>Submission Count</th>
					<th>View Responses</th>
				</tr>
				<tr>
					<td>Beatrice Riviere</td>
					<td>Rice</td>
					<td>11/2-3</td>
					<td style="text-align:center;"><?php echo $submissions[1]; ?></td>
					<td><a href="view-candidate-responses.php?cand=1">View Responses</a></td>
				</tr>
				<tr>
					<td>Mark Gockenbach</td>
					<td>Michigan Tech</td>
					<td>11/6-7</td>
					<td style="text-align:center;"><?php echo $submissions[2]; ?></td>
					<td><a href="view-candidate-responses.php?cand=2">View Responses</a></td>
				</tr>
				<tr>
					<td>Tim Hodges</td>
					<td>Cincinnati, NSF</td>
					<td>11/8-9</td>
					<td style="text-align:center;"><?php echo $submissions[3]; ?></td>
					<td><a href="view-candidate-responses.php?cand=3">View Responses</a></td>
				</tr>
				<tr>
					<td>Joshua Tebbs</td>
					<td>USC</td>
					<td>11/13-14</td>
					<td style="text-align:center;"><?php echo $submissions[4]; ?></td>
					<td><a href="view-candidate-responses.php?cand=4">View Responses</a></td>
				</tr>
				<tr>
					<td>Kevin James</td>
					<td>Clemson</td>
					<td>11/15-16</td>
					<td style="text-align:center;"><?php echo $submissions[5]; ?></td>
					<td><a href="view-candidate-responses.php?cand=5">View Responses</a></td>
				</tr>
				<tr>
					<td>Tamàs Terlaky</td>
					<td>Lehigh</td>
					<td>12/4-5</td>
					<td style="text-align:center;"><?php echo $submissions[6]; ?></td>
					<td><a href="view-candidate-responses.php?cand=6">View Responses</a></td>
				</tr>
				<tr>
					<td colspan="3" style="text-align:center;">Summary Survey</td>
					<td style="text-align:center;"><?php echo $submissions[7]; ?></td>
					<td><a href="view-summary-responses.php">View Responses</a></td>
			</table>
			
		</div>	
		<div id="footer">Click <a href="/dept_forms/website_error.py" target="_blank">here</a> to report a problem with this website.</div>
	</div>
</body>
</html>