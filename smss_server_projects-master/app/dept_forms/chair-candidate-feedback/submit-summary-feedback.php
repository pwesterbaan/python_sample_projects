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
	$user_hash = md5($user_id."_chair_feedback_2017");
}

$candidates = array(
				array("Beatrice Riviere","Rice","3 November 2017 6:00pm","7 December 2017 11:59pm",'November 2-3'),
				array("Mark Gockenbach","Michigan Tech","7 November 2017 6:00pm","7 December 2017 11:59pm",'November 6-7'),
				array("Tim Hodges","Cincinnati, NSF","9 November 2017 6:00pm","7 December 2017 11:59pm",'November 8-9'),
				array("Joshua Tebbs","USC","14 November 2017 6:00pm","7 December 2017 11:59pm",'November 13-14'),
				array("Kevin James","Clemson","16 November 2017 6:00pm","7 December 2017 11:59pm",'November 15-16'),
				array("Tamàs Terlaky","Lehigh","5 December 2017 6:00pm","7 December 2017 11:59pm",'December 4-5')
				);
				
//if ($_GET['cand'] > 0 && $_GET['cand'] < 6)
//{$candidate = $candidates[$_GET['cand']];}

function isInMath($user_id)
{
	global $mthsc_db;
	$stmt = $mthsc_db->prepare("SELECT 1 FROM `dept_info`.`people_to_lists_link` as pll JOIN `dept_info`.`person` as p on pll.person_id = p.person_id WHERE list_id=10 AND username = ?");
	$stmt->execute(array($user_id));
	$userExists = $stmt->fetchColumn();
	if ($userExists){return true;}
	else {return false;}
	
}
function survey_is_open()
{
	if (strtotime("now") < strtotime('11 December 2017 9:00am'))
	{
		return true;
	}
	else
	{
		return false; //true for testing, false for production
	}
}
function has_submitted($user_hash)
{
	global $mthsc_db;
	$stmt = $mthsc_db->prepare("SELECT 1 FROM `chair_candidate_feedback_submitters` WHERE candidate = ? AND user_hash = ?");
	$stmt->execute(array('summary',$user_hash));
	$has_submitted = $stmt->fetchColumn();
	if ($has_submitted){return true;}
	else {return false;}
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Chair Candidate Feedback</title>
	<meta name="author" content="hedetni">
	<!-- Date: 2017-10-27 -->

<link rel="stylesheet" href="/style/math.css" type="text/css" media="screen" title="no title" charset="utf-8">

<style type="text/css">
div.indent {margin-left:2em;}
table.option_table td {min-width:10em;text-align:center}
table#example td {text-align:center;}
div#role_more {
	border:1px solid black;
	background-color:rgba(255, 255, 255, 0.5);
	padding:0.5em 1em;
	margin-bottom:1em;
}
input[type="submit"] {
	font-size:2em;
}
</style>

<script src="/style/jquery-3.2.1.min.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">
$(document).ready(function() 
{
	$('a#role_more_link').click(function(){
		$('div#role_more').slideToggle();
	});
	$('a#close_role_more').click(function(){
		$('div#role_more').slideUp();
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
			<h1><a href="index.php">Chair Candidate Feedback</a></h1>
		</div>
	
		<div id="content">
			
				<?php if (isInMath($user_id)): ?>
					<h1>Submit Summary Feedback on Chair Candidates</h1>
			
					<?php if (survey_is_open()): ?>
						<?php if (!has_submitted($user_hash)): ?>
						
							<form name="summary_feedback_form" action="index.php" method="POST">
								<p>This survey is anonymous. We record a hashed version of your user id in a separate table to document when you submit (to prevent multiple submissions). Your identity is not associated with your responses in any way. Your responses are not stored until you press the submit button at the bottom of this page. You may only submit this form once.</p>
								
								<p style="font-weight:bold;">For reference, these are the chair candidates: </p>
								<table>
									<tr>
										<th>Candidate</th>
										<th>Affiliation</th>
										<th>Visiting Dates</th>
									</tr>
								<?php foreach ($candidates as $candidate): ?>
									<tr>
										<td><?php echo $candidate[0]; ?></td>
										<td><?php echo $candidate[1]; ?></td>
										<td><?php echo $candidate[4]; ?></td>
									</tr>
								<?php endforeach; ?>
								</table>
								<br>
						
								<p>1. Please rank the candidates you feel are acceptable, with 1 being the candidate you would most support hiring, 6 being the least</p>
								<p><textarea name="ranking" rows="7" cols="80"></textarea></p>
								<br>
							
								<p>2. Add any comments you would like to share about the candidates</p>
								<p><textarea name="comments" rows="7" cols="80"></textarea></p>
								<br>
							

								
								<p>You must click 'Submit Responses' below for your opinions to be recorded. You may only submit once.</p>
								<input type="hidden" name="candidate" value="summary">
								<input type="hidden" name="user_hash" value="<?php echo $user_hash; ?>">
								<input type="submit" name="submit_summary_responses" value="Submit Responses">
							</form>
						
						
						
						<?php else: ?>
							You have already submitted summary feedback.
						<?php endif; ?>
			
					<?php else: ?>
						Summary Feedback is not being accepted at this time.
					<?php endif; ?>
			
				<?php else: ?>
					Only Faculty and Staff of the Mathematical Sciences Department are eligible to fill out this survey.
				<?php endif; ?>
				
			
			
		</div>	
		<div id="footer">Click <a href="/dept_forms/website_error.py" target="_blank">here</a> to report a problem with this website.</div>
	</div>
</body>
</html>