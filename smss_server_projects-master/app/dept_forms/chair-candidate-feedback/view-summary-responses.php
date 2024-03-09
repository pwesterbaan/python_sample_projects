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

$candidates = array("0",
				array("Beatrice Riviere","Rice","3 October 2017 6:00pm","20 November 2017 11:59pm"),
				array("Mark Gockenbach","Michigan Tech","7 November 2017 6:00pm","9 November 2017 11:59pm"),
				array("Tim Hodges","Cincinnati, NSF","9 November 2017 6:00pm","13 November 2017 11:59pm"),
				array("Joshua Tebbs","USC","14 November 2017 6:00pm","16 November 2017 11:59pm"),
				array("Kevin James","Clemson","16 November 2017 6:00pm","20 November 2017 11:59pm"),
				array("Tamàs Terlaky","Lehigh","5 December 2017 6:00pm","11 December 2017 9:00am")
				);
//if ($_GET['cand'] > 0 && $_GET['cand'] < 7)
//{$candidate = $candidates[$_GET['cand']];}


function get_rankings()
{
	global $mthsc_db;
	$stmt = $mthsc_db->prepare("SELECT ranking FROM `chair_candidate_feedback_overall`");
	$stmt->execute();
	return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function get_comments()
{
	global $mthsc_db;
	$stmt = $mthsc_db->prepare("SELECT comments FROM `chair_candidate_feedback_overall`");
	$stmt->execute();
	return $stmt->fetchAll(PDO::FETCH_COLUMN);
}



?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Chair Candidate Feedback</title>
	<meta name="author" content="hedetni">
	<!-- Date: 2017-10-26 -->

<link rel="stylesheet" href="/style/math.css" type="text/css" media="screen" title="no title" charset="utf-8">

<style type="text/css">
div.indent {margin-left:2em;}
table.results td {text-align:center;}
table.results td.option {text-align:left}
span.percent{color:#777;}
div.response{border:1px solid gray;
	background-color:rgba(255, 255, 255, 0.7);
	padding:0.25em;
	margin-bottom:0.5em;}
div.response {page-break-before:avoid;margin-left:2em;}
@media print {
	div#header {display:none;}
	.noprint {display:none;}
	table {border:1px solid lightgray;border-collapse:collapse;page-break-inside: avoid;page-break-before:avoid;}
	td,th {border:1px solid lightgray;}
	div.comment_block,div.response {page-break-before:avoid;page-break-inside:avoid;}
	.nobreakafter {page-break-after:avoid;}
}
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
			<p class="noprint"><a href="submissions.php">Back to list of Candidates</a></p>
			<h1>Summary Survey Responses</h1>
			
			<p style="font-weight:bold;">1. Please rank the candidates you feel are acceptable, with 1 being the candidate you would most support hiring, 6 being the least</p>
			
				
					<?php foreach (get_rankings() as $ranking): ?>
						<?php if ($ranking != ""): ?>
							<div class="response">
								<?php echo nl2br($ranking); ?>
							</div>
						<?php endif; ?>
					<?php endforeach; ?>
				
			
			<br><br>
		
			<p style="font-weight:bold;">2. Add any comments you would like to share about the candidates</p>
			
				
					<?php foreach (get_comments() as $comment): ?>
						<?php if ($comment != ""): ?>
							<div class="response">
								<?php echo nl2br($comment); ?>
							</div>
						<?php endif; ?>
					<?php endforeach; ?>
			
			
			<br>

			
		</div>	
		<div id="footer" class="noprint">Click <a href="/dept_forms/website_error.py" target="_blank">here</a> to report a problem with this website.</div>
	</div>
</body>
</html>