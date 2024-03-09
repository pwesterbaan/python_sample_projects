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

if (isset($_POST['submit_responses']))
{
	if (!has_submitted($_POST['user_hash'],$_POST['candidate']) && isInMath($user_id))
	{
		//save responses
		$responses = array('candidate' => $_POST['candidate'],
						'overall' => $_POST['overall'],
						'overall_comments' => $_POST['overall_comments'],
						'qualified' => $_POST['qualified'],
						'qualified_comments' => $_POST['qualified_comments'],
						'leader' => $_POST['leader'],
						'leader_comments' => $_POST['leader_comments'],
						'vision' => $_POST['vision'],
						'vision_comments' => $_POST['vision_comments'],
						'chair_by_default' => $_POST['chair_by_default'],
						'role' => $_POST['role']);
	
		foreach($responses as $index => $response)
		{
			if ($response == null)
			{
				$responses[$index] = "";
			}
		}
					
		$submission = array('candidate' => $_POST['candidate'],
						'user_hash' => $_POST['user_hash']);
	
		try {
			//insert responses
			$mthsc_db->beginTransaction();
			$query = $mthsc_db->prepare("INSERT INTO chair_candidate_feedback (candidate,overall,overall_comments,qualified,qualified_comments,leader,leader_comments,vision,vision_comments,chair_by_default,role) VALUES (:candidate,:overall,:overall_comments,:qualified,:qualified_comments,:leader,:leader_comments,:vision,:vision_comments,:chair_by_default,:role);");
			$query->execute($responses);
		
			$query2 = $mthsc_db->prepare("INSERT INTO chair_candidate_feedback_submitters (user_hash,candidate) VALUES (:user_hash,:candidate);");
			$query2->execute($submission);
			//insert submission
			
			$message = 'Thank you, your opinions on '.$_POST['candidate'].' have been recorded.';
		
		}
		catch (Exception $e){
	    	$mthsc_db->rollback();
			$message = 'Sorry, something went wrong. Your opinions on '.$_POST['candidate'].' were NOT recorded.';
	    throw $e;
		}
	}
}

if (isset($_POST['submit_summary_responses']))
{
	if (!has_submitted($_POST['user_hash'],$_POST['candidate']) && isInMath($user_id))
	{
		//save responses
		$responses = array(
						'ranking' => $_POST['ranking'],
						'comments' => $_POST['comments']);
	
		foreach($responses as $index => $response)
		{
			if ($response == null)
			{
				$responses[$index] = "";
			}
		}
					
		$submission = array('candidate' => $_POST['candidate'],
						'user_hash' => $_POST['user_hash']);
	
		try {
			//insert responses
			$mthsc_db->beginTransaction();
			$query = $mthsc_db->prepare("INSERT INTO chair_candidate_feedback_overall (ranking,comments) VALUES (:ranking,:comments);");
			$query->execute($responses);
		
			$query2 = $mthsc_db->prepare("INSERT INTO chair_candidate_feedback_submitters (user_hash,candidate) VALUES (:user_hash,:candidate);");
			$query2->execute($submission);
			//insert submission
			
			$message = 'Thank you, your opinions on '.$_POST['candidate'].' have been recorded.';
		
		}
		catch (Exception $e){
	    	$mthsc_db->rollback();
			$message = 'Sorry, something went wrong. Your opinions on '.$_POST['candidate'].' were NOT recorded.';
	    throw $e;
		}
	}
}

function has_submitted($user_hash,$candidate)
{
	global $mthsc_db;
	$stmt = $mthsc_db->prepare("SELECT submitted FROM `chair_candidate_feedback_submitters` WHERE candidate = ? AND user_hash = ?");
	$stmt->execute(array($candidate,$user_hash));
	$has_submitted = $stmt->fetchColumn();
	if ($has_submitted){return $has_submitted;}
	else {return false;}
}

function isInMath($user_id)
{
	global $mthsc_db;
	$stmt = $mthsc_db->prepare("SELECT 1 FROM `dept_info`.`people_to_lists_link` as pll JOIN `dept_info`.`person` as p on pll.person_id = p.person_id WHERE list_id=10 AND username = ?");
	$stmt->execute(array($user_id));
	$userExists = $stmt->fetchColumn();
	if ($userExists){return true;}
	else {return false;}
	
}

$submissions = array(null,
					has_submitted($user_hash,'Beatrice Riviere'),
					has_submitted($user_hash,'Mark Gockenbach'),
					has_submitted($user_hash,'Tim Hodges'),
					has_submitted($user_hash,'Joshua Tebbs'),
					has_submitted($user_hash,'Kevin James'),
					has_submitted($user_hash,'Tamàs Terlaky'),
					has_submitted($user_hash,'summary'));


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
div#message {color: #F66733;font-weight:bold; }

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
			<h1>2017 Chair Search</h1>
			
			<?php echo isset($message) ? '<div id="message">'.$message.'</div>' : '' ?>
			
			<?php if (isInMath($user_id)): ?>
				<p><strong><?php echo $_SERVER['givenName']; ?></strong>, the Chair Search Committee invites you to share your opinions of each candidate using the following links.</p>
			
				<table>
					<tr>
						<th>Candidate</th>
						<th>Affiliation</th>
						<th>Visiting Dates</th>
						<th>Feedback Link</th>
						<th>Colloquium Talk</th>
						<th>Vision/Goals Talk</th>
					</tr>
					<tr>
						<td>Beatrice Riviere</td>
						<td>Rice</td>
						<td>11/2-3</td>
						<td><?php if (!$submissions[1]): ?>
								<?php if (strtotime("now") < strtotime("3 November 2017 6:00pm")): ?>
									Survey Opens November 3 at 6pm
								<?php elseif (strtotime("now") >= strtotime("3 November 2017 6:00pm") && strtotime("now") < strtotime("11 December 2017 9:00am")): ?>
									<a href="submit-feedback.php?cand=1">Click to Submit Feedback</a><br>
									Closes December 11 at 9:00 am
								<?php else: ?>
									No longer available
								<?php endif; ?>
							<?php else: ?>
								<i>Submitted <?php echo date("M j, g:i a",strtotime($submissions[1])); ?></i>
							<?php endif; ?>
						</td>
						<td><a href="https://www.youtube.com/watch?v=OM3vkPwHZP4">Colloquium Video</a></td>
						<td><a href="https://www.youtube.com/watch?v=tT1XrJM0Y6I">Vision/Goals Video</a></td>
					</tr>
					<tr>
						<td>Mark Gockenbach</td>
						<td>Michigan Tech</td>
						<td>11/6-7</td>
						<td><?php if (!$submissions[2]): ?>
								<?php if (strtotime("now") < strtotime("7 November 2017 6:00pm")): ?>
									Survey Opens November 7 at 6pm
								<?php elseif (strtotime("now") >= strtotime("7 November 2017 6:00pm") && strtotime("now") < strtotime("11 December 2017 9:00am")): ?>
									<a href="submit-feedback.php?cand=2">Click to Submit Feedback</a><br>
									Closes December 11 at 9:00 am
								<?php else: ?>
									No longer available
								<?php endif; ?>
							<?php else: ?>
								<i>Submitted <?php echo date("M j, g:i a",strtotime($submissions[2])); ?></i>
							<?php endif; ?>
						</td>
						<td><a href="https://www.youtube.com/watch?v=FNnK9qQTsaE">Colloquium Video</a></td>
						<td><a href="https://www.youtube.com/watch?v=mSfi4YzUsvA">Vision/Goals Video</a></td>
					</tr>
					<tr>
						<td>Tim Hodges</td>
						<td>Cincinnati, NSF</td>
						<td>11/8-9</td>
						<td><?php if (!$submissions[3]): ?>
								<?php if (strtotime("now") < strtotime("9 November 2017 6:00pm")): ?>
									Survey Opens November 9 at 6pm
								<?php elseif (strtotime("now") >= strtotime("9 November 2017 6:00pm") && strtotime("now") < strtotime("11 December 2017 9:00am")): ?>
									<a href="submit-feedback.php?cand=3">Click to Submit Feedback</a><br>
									Closes December 11 at 9:00 am
								<?php else: ?>
									No longer available
								<?php endif; ?>
							<?php else: ?>
								<i>Submitted <?php echo date("M j, g:i a",strtotime($submissions[3])); ?></i>
							<?php endif; ?>
						</td>
						<td><a href="https://www.youtube.com/watch?v=Lk5jvBEMROE">Colloquium Video</a></td>
						<td><a href="https://www.youtube.com/watch?v=c1OQMMQOdSI">Vision/Goals Video</a></td>
					</tr>
					<tr>
						<td>Joshua Tebbs</td>
						<td>USC</td>
						<td>11/13-14</td>
						<td><?php if (!$submissions[4]): ?>
								<?php if (strtotime("now") < strtotime("14 November 2017 6:00pm")): ?>
									Survey Opens November 14 at 6pm
								<?php elseif (strtotime("now") >= strtotime("14 November 2017 6:00pm") && strtotime("now") < strtotime("11 December 2017 9:00am")): ?>
									<a href="submit-feedback.php?cand=4">Click to Submit Feedback</a><br>
									Closes December 11 at 9:00 am
								<?php else: ?>
									No longer available
								<?php endif; ?>
							<?php else: ?>
								<i>Submitted <?php echo date("M j, g:i a",strtotime($submissions[4])); ?></i>
							<?php endif; ?>
						</td>
						<td><a href="https://www.youtube.com/watch?v=97NQ2feM50E">Colloquium Video</a></td>
						<td><a href="https://www.youtube.com/watch?v=6QZFu37hYJM">Vision/Goals Video</a></td>
					</tr>
					<tr>
						<td>Kevin James</td>
						<td>Clemson</td>
						<td>11/15-16</td>
						<td><?php if (!$submissions[5]): ?>
								<?php if (strtotime("now") < strtotime("16 November 2017 6:00pm")): ?>
									Survey Opens November 16 at 6pm
								<?php elseif (strtotime("now") >= strtotime("16 November 2017 6:00pm") && strtotime("now") < strtotime("11 December 2017 9:00am")): ?>
									<a href="submit-feedback.php?cand=5">Click to Submit Feedback</a><br>
									Closes December 11 at 9:00 am
								<?php else: ?>
									No longer available
								<?php endif; ?>
							<?php else: ?>
								<i>Submitted <?php echo date("M j, g:i a",strtotime($submissions[5])); ?></i>
							<?php endif; ?>
						</td>
						<td><a href="https://www.youtube.com/watch?v=QWXjUn41XC8">Colloquium Video</a></td>
						<td><a href="https://www.youtube.com/watch?v=fhtpBJL2PEU">Vision/Goals Video</a></td>
					</tr>
					<tr>
						<td>Tamàs Terlaky</td>
						<td>Lehigh</td>
						<td>12/4-5</td>
						<td><?php if (!$submissions[6]): ?>
								<?php if (strtotime("now") < strtotime("5 December 2017 6:00pm")): ?>
									Survey Opens December 5 at 6pm
								<?php elseif (strtotime("now") >= strtotime("5 December 2017 6:00pm") && strtotime("now") < strtotime("11 December 2017 9:00am")): ?>
									<a href="submit-feedback.php?cand=6">Click to Submit Feedback</a><br>
									Closes December 11 at 9:00 am
								<?php else: ?>
									No longer available
								<?php endif; ?>
							<?php else: ?>
								<i>Submitted <?php echo date("M j, g:i a",strtotime($submissions[6])); ?></i>
							<?php endif; ?>
						</td>
						<td><a href="https://www.youtube.com/watch?v=-R9PeZM0nzc">Colloquium Video</a></td>
						<td><a href="https://www.youtube.com/watch?v=8yCWl2SbUMc">Vision/Goals Video</a></td>
					</tr>
				</table>
				<br>
				
				<h2>Summary Feedback Survey</h2>
				<p><?php if (!$submissions[7]): ?>
								<?php if (strtotime("now") < strtotime("5 December 2017 6:00pm")): ?>
									Survey Opens December 5 at 6pm
								<?php elseif (strtotime("now") >= strtotime("5 December 2017 6:00pm") && strtotime("now") < strtotime("11 December 2017 9:00am")): ?>
									<a href="submit-summary-feedback.php">Click here to submit the summary feedback survey</a><br>
									Closes December 11 at 9:00 am
								<?php else: ?>
									No longer available
								<?php endif; ?>
							<?php else: ?>
								<i>Submitted <?php echo date("M j, g:i a",strtotime($submissions[7])); ?></i>
							<?php endif; ?>
				</p>
			<?php else: ?>
				<p>We are only accepting feedback from employees of the Math Department.</p>
			<?php endif; ?>
			
		</div>	
		<div id="footer">Click <a href="/dept_forms/website_error.py" target="_blank">here</a> to report a problem with this website.</div>
	</div>
</body>
</html>