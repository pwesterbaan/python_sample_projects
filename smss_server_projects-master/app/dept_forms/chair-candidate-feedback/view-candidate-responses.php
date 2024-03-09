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
if ($_GET['cand'] > 0 && $_GET['cand'] < 7)
{$candidate = $candidates[$_GET['cand']];}


$roles = array('Total','Staff','Lecturer','Senior Lecturer','Associate Professor','Assistant Professor','Professor','Visiting','Prefer Not to Answer');

$overall_response_counts = array(
'Acceptable' => array('Total'=>get_overall_count('Acceptable','Total'),
						'Staff'=>get_overall_count('Acceptable','Staff'),
						'Lecturer'=>get_overall_count('Acceptable','Lecturer'),
						'Senior Lecturer'=>get_overall_count('Acceptable','Senior Lecturer'),
						'Associate Professor'=>get_overall_count('Acceptable','Associate Professor'),
						'Assistant Professor'=>get_overall_count('Acceptable','Assistant Professor'),
						'Professor'=>get_overall_count('Acceptable','Professor'),
						'Visiting'=>get_overall_count('Acceptable','Visiting'),
						'Prefer Not to Answer'=>get_overall_count('Acceptable','Prefer Not to Answer')),
'Unacceptable' => array('Total'=>get_overall_count('Unacceptable','Total'),
						'Staff'=>get_overall_count('Unacceptable','Staff'),
						'Lecturer'=>get_overall_count('Unacceptable','Lecturer'),
						'Senior Lecturer'=>get_overall_count('Unacceptable','Senior Lecturer'),
						'Associate Professor'=>get_overall_count('Unacceptable','Associate Professor'),
						'Assistant Professor'=>get_overall_count('Unacceptable','Assistant Professor'),
						'Professor'=>get_overall_count('Unacceptable','Professor'),
						'Visiting'=>get_overall_count('Unacceptable','Visiting'),
						'Prefer Not to Answer'=>get_overall_count('Unacceptable','Prefer Not to Answer')));
						
$qualified_response_counts = array(
'Acceptable' => array('Total'=>get_qualified_count('Acceptable','Total'),
						'Staff'=>get_qualified_count('Acceptable','Staff'),
						'Lecturer'=>get_qualified_count('Acceptable','Lecturer'),
						'Senior Lecturer'=>get_qualified_count('Acceptable','Senior Lecturer'),
						'Associate Professor'=>get_qualified_count('Acceptable','Associate Professor'),
						'Assistant Professor'=>get_qualified_count('Acceptable','Assistant Professor'),
						'Professor'=>get_qualified_count('Acceptable','Professor'),
						'Visiting'=>get_qualified_count('Acceptable','Visiting'),
						'Prefer Not to Answer'=>get_qualified_count('Acceptable','Prefer Not to Answer')),
'Unacceptable' => array('Total'=>get_qualified_count('Unacceptable','Total'),
						'Staff'=>get_qualified_count('Unacceptable','Staff'),
						'Lecturer'=>get_qualified_count('Unacceptable','Lecturer'),
						'Senior Lecturer'=>get_qualified_count('Unacceptable','Senior Lecturer'),
						'Associate Professor'=>get_qualified_count('Unacceptable','Associate Professor'),
						'Assistant Professor'=>get_qualified_count('Unacceptable','Assistant Professor'),
						'Professor'=>get_qualified_count('Unacceptable','Professor'),
						'Visiting'=>get_qualified_count('Unacceptable','Visiting'),
						'Prefer Not to Answer'=>get_qualified_count('Unacceptable','Prefer Not to Answer')));
						
$leader_response_counts = array(
'Poor' => array('Total'=>get_leader_count('Poor','Total'),
						'Staff'=>get_leader_count('Poor','Staff'),
						'Lecturer'=>get_leader_count('Poor','Lecturer'),
						'Senior Lecturer'=>get_leader_count('Poor','Senior Lecturer'),
						'Associate Professor'=>get_leader_count('Poor','Associate Professor'),
						'Assistant Professor'=>get_leader_count('Poor','Assistant Professor'),
						'Professor'=>get_leader_count('Poor','Professor'),
						'Visiting'=>get_leader_count('Poor','Visiting'),
						'Prefer Not to Answer'=>get_leader_count('Poor','Prefer Not to Answer')),
'Fair' => array('Total'=>get_leader_count('Fair','Total'),
						'Staff'=>get_leader_count('Fair','Staff'),
						'Lecturer'=>get_leader_count('Fair','Lecturer'),
						'Senior Lecturer'=>get_leader_count('Fair','Senior Lecturer'),
						'Associate Professor'=>get_leader_count('Fair','Associate Professor'),
						'Assistant Professor'=>get_leader_count('Fair','Assistant Professor'),
						'Professor'=>get_leader_count('Fair','Professor'),
						'Visiting'=>get_leader_count('Fair','Visiting'),
						'Prefer Not to Answer'=>get_leader_count('Fair','Prefer Not to Answer')),
'Good' => array('Total'=>get_leader_count('Good','Total'),
						'Staff'=>get_leader_count('Good','Staff'),
						'Lecturer'=>get_leader_count('Good','Lecturer'),
						'Senior Lecturer'=>get_leader_count('Good','Senior Lecturer'),
						'Associate Professor'=>get_leader_count('Good','Associate Professor'),
						'Assistant Professor'=>get_leader_count('Good','Assistant Professor'),
						'Professor'=>get_leader_count('Good','Professor'),
						'Visiting'=>get_leader_count('Good','Visiting'),
						'Prefer Not to Answer'=>get_leader_count('Good','Prefer Not to Answer')),
'Very Good' => array('Total'=>get_leader_count('Very Good','Total'),
						'Staff'=>get_leader_count('Very Good','Staff'),
						'Lecturer'=>get_leader_count('Very Good','Lecturer'),
						'Senior Lecturer'=>get_leader_count('Very Good','Senior Lecturer'),
						'Associate Professor'=>get_leader_count('Very Good','Associate Professor'),
						'Assistant Professor'=>get_leader_count('Very Good','Assistant Professor'),
						'Professor'=>get_leader_count('Very Good','Professor'),
						'Visiting'=>get_leader_count('Very Good','Visiting'),
						'Prefer Not to Answer'=>get_leader_count('Very Good','Prefer Not to Answer')));
						
$vision_response_counts = array(
'Poor' => array('Total'=>get_vision_count('Poor','Total'),
						'Staff'=>get_vision_count('Poor','Staff'),
						'Lecturer'=>get_vision_count('Poor','Lecturer'),
						'Senior Lecturer'=>get_vision_count('Poor','Senior Lecturer'),
						'Associate Professor'=>get_vision_count('Poor','Associate Professor'),
						'Assistant Professor'=>get_vision_count('Poor','Assistant Professor'),
						'Professor'=>get_vision_count('Poor','Professor'),
						'Visiting'=>get_vision_count('Poor','Visiting'),
						'Prefer Not to Answer'=>get_vision_count('Poor','Prefer Not to Answer')),
'Fair' => array('Total'=>get_vision_count('Fair','Total'),
						'Staff'=>get_vision_count('Fair','Staff'),
						'Lecturer'=>get_vision_count('Fair','Lecturer'),
						'Senior Lecturer'=>get_vision_count('Fair','Senior Lecturer'),
						'Associate Professor'=>get_vision_count('Fair','Associate Professor'),
						'Assistant Professor'=>get_vision_count('Fair','Assistant Professor'),
						'Professor'=>get_vision_count('Fair','Professor'),
						'Visiting'=>get_vision_count('Fair','Visiting'),
						'Prefer Not to Answer'=>get_vision_count('Fair','Prefer Not to Answer')),
'Good' => array('Total'=>get_vision_count('Good','Total'),
						'Staff'=>get_vision_count('Good','Staff'),
						'Lecturer'=>get_vision_count('Good','Lecturer'),
						'Senior Lecturer'=>get_vision_count('Good','Senior Lecturer'),
						'Associate Professor'=>get_vision_count('Good','Associate Professor'),
						'Assistant Professor'=>get_vision_count('Good','Assistant Professor'),
						'Professor'=>get_vision_count('Good','Professor'),
						'Visiting'=>get_vision_count('Good','Visiting'),
						'Prefer Not to Answer'=>get_vision_count('Good','Prefer Not to Answer')),
'Very Good' => array('Total'=>get_vision_count('Very Good','Total'),
						'Staff'=>get_vision_count('Very Good','Staff'),
						'Lecturer'=>get_vision_count('Very Good','Lecturer'),
						'Senior Lecturer'=>get_vision_count('Very Good','Senior Lecturer'),
						'Associate Professor'=>get_vision_count('Very Good','Associate Professor'),
						'Assistant Professor'=>get_vision_count('Very Good','Assistant Professor'),
						'Professor'=>get_vision_count('Very Good','Professor'),
						'Visiting'=>get_vision_count('Very Good','Visiting'),
						'Prefer Not to Answer'=>get_vision_count('Very Good','Prefer Not to Answer')));
						
$default_response_counts = array(
'Enthusiastic' => array('Total'=>get_default_count('Enthusiastic','Total'),
						'Staff'=>get_default_count('Enthusiastic','Staff'),
						'Lecturer'=>get_default_count('Enthusiastic','Lecturer'),
						'Senior Lecturer'=>get_default_count('Enthusiastic','Senior Lecturer'),
						'Associate Professor'=>get_default_count('Enthusiastic','Associate Professor'),
						'Assistant Professor'=>get_default_count('Enthusiastic','Assistant Professor'),
						'Professor'=>get_default_count('Enthusiastic','Professor'),
						'Visiting'=>get_default_count('Enthusiastic','Visiting'),
						'Prefer Not to Answer'=>get_default_count('Enthusiastic','Prefer Not to Answer')),
'Satisfied or content' => array('Total'=>get_default_count('Satisfied or content','Total'),
						'Staff'=>get_default_count('Satisfied or content','Staff'),
						'Lecturer'=>get_default_count('Satisfied or content','Lecturer'),
						'Senior Lecturer'=>get_default_count('Satisfied or content','Senior Lecturer'),
						'Associate Professor'=>get_default_count('Satisfied or content','Associate Professor'),
						'Assistant Professor'=>get_default_count('Satisfied or content','Assistant Professor'),
						'Professor'=>get_default_count('Satisfied or content','Professor'),
						'Visiting'=>get_default_count('Satisfied or content','Visiting'),
						'Prefer Not to Answer'=>get_default_count('Satisfied or content','Prefer Not to Answer')),
'Neutral' => array('Total'=>get_default_count('Neutral','Total'),
						'Staff'=>get_default_count('Neutral','Staff'),
						'Lecturer'=>get_default_count('Neutral','Lecturer'),
						'Senior Lecturer'=>get_default_count('Neutral','Senior Lecturer'),
						'Associate Professor'=>get_default_count('Neutral','Associate Professor'),
						'Assistant Professor'=>get_default_count('Neutral','Assistant Professor'),
						'Professor'=>get_default_count('Neutral','Professor'),
						'Visiting'=>get_default_count('Neutral','Visiting'),
						'Prefer Not to Answer'=>get_default_count('Neutral','Prefer Not to Answer')),
'Disappointed or discontent' => array('Total'=>get_default_count('Disappointed or discontent','Total'),
						'Staff'=>get_default_count('Disappointed or discontent','Staff'),
						'Lecturer'=>get_default_count('Disappointed or discontent','Lecturer'),
						'Senior Lecturer'=>get_default_count('Disappointed or discontent','Senior Lecturer'),
						'Associate Professor'=>get_default_count('Disappointed or discontent','Associate Professor'),
						'Assistant Professor'=>get_default_count('Disappointed or discontent','Assistant Professor'),
						'Professor'=>get_default_count('Disappointed or discontent','Professor'),
						'Visiting'=>get_default_count('Disappointed or discontent','Visiting'),
						'Prefer Not to Answer'=>get_default_count('Disappointed or discontent','Prefer Not to Answer')),
'Sick or worried' => array('Total'=>get_default_count('Sick or worried','Total'),
						'Staff'=>get_default_count('Sick or worried','Staff'),
						'Lecturer'=>get_default_count('Sick or worried','Lecturer'),
						'Senior Lecturer'=>get_default_count('Sick or worried','Senior Lecturer'),
						'Associate Professor'=>get_default_count('Sick or worried','Associate Professor'),
						'Assistant Professor'=>get_default_count('Sick or worried','Assistant Professor'),
						'Professor'=>get_default_count('Sick or worried','Professor'),
						'Visiting'=>get_default_count('Sick or worried','Visiting'),
						'Prefer Not to Answer'=>get_default_count('Sick or worried','Prefer Not to Answer')));


function get_overall_count($option,$role)
{
	global $mthsc_db;
	global $candidate;
	if ($role != "Total")
	{
		$stmt = $mthsc_db->prepare("SELECT COUNT( overall ) FROM chair_candidate_feedback WHERE candidate =  ? AND role =  ? AND overall = ? GROUP BY overall");
		$stmt->execute(array($candidate[0],$role,$option));
		$count = $stmt->fetchColumn();
		if ($count){return $count;}
		else {return 0;}
	}
	else
	{
		$stmt = $mthsc_db->prepare("SELECT COUNT( overall ) FROM chair_candidate_feedback WHERE candidate =  ? AND overall = ? GROUP BY overall");
		$stmt->execute(array($candidate[0],$option));
		$count = $stmt->fetchColumn();
		if ($count){return $count;}
		else {return 0;}
	}
}

function get_qualified_count($option,$role)
{
	global $mthsc_db;
	global $candidate;
	if ($role != "Total")
	{
		$stmt = $mthsc_db->prepare("SELECT COUNT( qualified ) FROM chair_candidate_feedback WHERE candidate =  ? AND role =  ? AND qualified = ? GROUP BY qualified");
		$stmt->execute(array($candidate[0],$role,$option));
		$count = $stmt->fetchColumn();
		if ($count){return $count;}
		else {return 0;}
	}
	else
	{
		$stmt = $mthsc_db->prepare("SELECT COUNT( qualified ) FROM chair_candidate_feedback WHERE candidate =  ? AND qualified = ? GROUP BY qualified");
		$stmt->execute(array($candidate[0],$option));
		$count = $stmt->fetchColumn();
		if ($count){return $count;}
		else {return 0;}
	}
}

function get_leader_count($option,$role)
{
	global $mthsc_db;
	global $candidate;
	if ($role != "Total")
	{
		$stmt = $mthsc_db->prepare("SELECT COUNT( leader ) FROM chair_candidate_feedback WHERE candidate =  ? AND role =  ? AND leader = ? GROUP BY leader");
		$stmt->execute(array($candidate[0],$role,$option));
		$count = $stmt->fetchColumn();
		if ($count){return $count;}
		else {return 0;}
	}
	else
	{
		$stmt = $mthsc_db->prepare("SELECT COUNT( leader ) FROM chair_candidate_feedback WHERE candidate =  ? AND leader = ? GROUP BY leader");
		$stmt->execute(array($candidate[0],$option));
		$count = $stmt->fetchColumn();
		if ($count){return $count;}
		else {return 0;}
	}
}

function get_vision_count($option,$role)
{
	global $mthsc_db;
	global $candidate;
	if ($role != "Total")
	{
		$stmt = $mthsc_db->prepare("SELECT COUNT( vision ) FROM chair_candidate_feedback WHERE candidate =  ? AND role =  ? AND vision = ? GROUP BY vision");
		$stmt->execute(array($candidate[0],$role,$option));
		$count = $stmt->fetchColumn();
		if ($count){return $count;}
		else {return 0;}
	}
	else
	{
		$stmt = $mthsc_db->prepare("SELECT COUNT( vision ) FROM chair_candidate_feedback WHERE candidate =  ? AND vision = ? GROUP BY vision");
		$stmt->execute(array($candidate[0],$option));
		$count = $stmt->fetchColumn();
		if ($count){return $count;}
		else {return 0;}
	}
}

function get_default_count($option,$role)
{
	global $mthsc_db;
	global $candidate;
	if ($role != "Total")
	{
		$stmt = $mthsc_db->prepare("SELECT COUNT( chair_by_default ) FROM chair_candidate_feedback WHERE candidate =  ? AND role =  ? AND chair_by_default = ? GROUP BY chair_by_default");
		$stmt->execute(array($candidate[0],$role,$option));
		$count = $stmt->fetchColumn();
		if ($count){return $count;}
		else {return 0;}
	}
	else
	{
		$stmt = $mthsc_db->prepare("SELECT COUNT( chair_by_default ) FROM chair_candidate_feedback WHERE candidate =  ? AND chair_by_default = ? GROUP BY chair_by_default");
		$stmt->execute(array($candidate[0],$option));
		$count = $stmt->fetchColumn();
		if ($count){return $count;}
		else {return 0;}
	}
}


function submission_count($candidate)
{
	global $mthsc_db;
	$stmt = $mthsc_db->prepare("SELECT count(candidate) FROM `chair_candidate_feedback` WHERE candidate = ?");
	$stmt->execute(array($candidate));
	return $stmt->fetchColumn();
}

function get_overall_comments($candidate)
{
	global $mthsc_db;
	$stmt = $mthsc_db->prepare("SELECT overall_comments FROM `chair_candidate_feedback` WHERE candidate = ?");
	$stmt->execute(array($candidate));
	return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function get_qualified_comments($candidate)
{
	global $mthsc_db;
	$stmt = $mthsc_db->prepare("SELECT qualified_comments FROM `chair_candidate_feedback` WHERE candidate = ?");
	$stmt->execute(array($candidate));
	return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function get_leader_comments($candidate)
{
	global $mthsc_db;
	$stmt = $mthsc_db->prepare("SELECT leader_comments FROM `chair_candidate_feedback` WHERE candidate = ?");
	$stmt->execute(array($candidate));
	return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function get_vision_comments($candidate)
{
	global $mthsc_db;
	$stmt = $mthsc_db->prepare("SELECT vision_comments FROM `chair_candidate_feedback` WHERE candidate = ?");
	$stmt->execute(array($candidate));
	return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function get_submission_counts($candidate)
{
	global $mthsc_db;
	$stmt = $mthsc_db->prepare("SELECT role, COUNT( role ) AS count FROM  `chair_candidate_feedback` WHERE candidate = ? GROUP BY role ");
	$stmt->execute(array($candidate));
	return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
}
$submission_counts = get_submission_counts($candidate[0]);
$submission_counts['Total'] = submission_count($candidate[0]);



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
@media print {
	div#header {display:none;}
	.noprint {display:none;}
	table {border:1px solid lightgray;border-collapse:collapse;page-break-inside: avoid;page-break-before:avoid;}
	td,th {border:1px solid lightgray;}
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
			<h1>Responses for <?php echo $candidate[0]; ?> (<?php echo $candidate[1]; ?>)</h1>
			
			<p><strong>Number of Submissions</strong></p>
			<div class="indent">
				<table class="results">
					<tr>
						<th style="border-left:2px solid black;border-right:2px solid black; border-top:2px solid black;">Total</th>
						<th>Staff</th>
						<th>Lecturer</th>
						<th>Senior Lecturer</th>
						<th>Associate Professor</th>
						<th>Assistant Professor</th>
						<th>Professor</th>
						<th>Visiting</th>
						<th>Prefer not to answer</th>
					</tr>
					<tr>
						<td style="border-left:2px solid black;border-right:2px solid black;border-bottom:2px solid black;">
							<?php echo $submission_counts['Total']?></td>
						<td><?php echo isset($submission_counts['Staff']) ? $submission_counts['Staff'] : 0; ?></td>
						<td><?php echo isset($submission_counts['Lecturer']) ? $submission_counts['Lecturer'] :0; ?></td>
						<td><?php echo isset($submission_counts['Senior Lecturer']) ? $submission_counts['Senior Lecturer'] : 0; ?></td>
						<td><?php echo isset($submission_counts['Associate Professor']) ? $submission_counts['Associate Professor'] : 0; ?></td>
						<td><?php echo isset($submission_counts['Assistant Professor']) ? $submission_counts['Assistant Professor'] : 0; ?></td>
						<td><?php echo isset($submission_counts['Professor']) ? $submission_counts['Professor'] : 0; ?></td>
						<td><?php echo isset($submission_counts['Visiting']) ? $submission_counts['Visiting'] : 0; ?></td>
						<td><?php echo isset($submission_counts['Prefer Not to Answer']) ? $submission_counts['Prefer Not to Answer'] : 0; ?></td>
					</tr>
				</table>
			</div>
			<br>
			
			<p style="font-weight:bold;">Please rate <?php echo $candidate[0]; ?> on the following aspects:</p>
			
			<p class="nobreakafter">1. Overall</p>
			<div class="indent">
				<table class="results">
					<tr><td></td>
						<th style="border-left:2px solid black;border-right:2px solid black; border-top:2px solid black;">Total</th>
						<th>Staff</th>
						<th>Lecturer</th>
						<th>Senior Lecturer</th>
						<th>Associate Professor</th>
						<th>Assistant Professor</th>
						<th>Professor</th>
						<th>Visiting</th>
						<th>Prefer not to answer</th>
					</tr>
					<tr><td class="option">Acceptable</td>
						<td style="border-left:2px solid black;border-right:2px solid black;">
							<?php echo $overall_response_counts['Acceptable']['Total']; ?>
							<span class="percent"><?php echo '('.$overall_response_counts['Acceptable']['Total']*100 / ($overall_response_counts['Acceptable']['Total']+$overall_response_counts['Unacceptable']['Total']).'%)'; ?></span></td>
						<td><?php echo $overall_response_counts['Acceptable']['Staff']?></td>
						<td><?php echo $overall_response_counts['Acceptable']['Lecturer']?></td>
						<td><?php echo $overall_response_counts['Acceptable']['Senior Lecturer']?></td>
						<td><?php echo $overall_response_counts['Acceptable']['Associate Professor']?></td>
						<td><?php echo $overall_response_counts['Acceptable']['Assistant Professor']?></td>
						<td><?php echo $overall_response_counts['Acceptable']['Professor']?></td>
						<td><?php echo $overall_response_counts['Acceptable']['Visiting']?></td>
						<td><?php echo $overall_response_counts['Acceptable']['Prefer Not to Answer']?></td>
					</tr>
					<tr><td class="option">Unacceptable</td>
						<td style="border-left:2px solid black;border-right:2px solid black;border-bottom:2px solid black;">
							<?php echo $overall_response_counts['Unacceptable']['Total']?>
							<span class="percent"><?php echo '('.$overall_response_counts['Unacceptable']['Total']*100 / ($overall_response_counts['Acceptable']['Total']+$overall_response_counts['Unacceptable']['Total']).'%)'; ?></span></td>
						<td><?php echo $overall_response_counts['Unacceptable']['Staff']?></td>
						<td><?php echo $overall_response_counts['Unacceptable']['Lecturer']?></td>
						<td><?php echo $overall_response_counts['Unacceptable']['Senior Lecturer']?></td>
						<td><?php echo $overall_response_counts['Unacceptable']['Associate Professor']?></td>
						<td><?php echo $overall_response_counts['Unacceptable']['Assistant Professor']?></td>
						<td><?php echo $overall_response_counts['Unacceptable']['Professor']?></td>
						<td><?php echo $overall_response_counts['Unacceptable']['Visiting']?></td>
						<td><?php echo $overall_response_counts['Unacceptable']['Prefer Not to Answer']?></td>
					</tr>
				</table>
				<div class="comment_block">
					<p><strong>Comments:</strong></p>
					<ul><?php foreach (get_overall_comments($candidate[0]) as $comment): ?>
						<?php if ($comment != ""): ?><li><?php echo $comment; ?></li><?php endif; ?>
					<?php endforeach; ?></ul>
				</div>
			</div>
			<br>
		
			<p class="nobreakafter">2. Sufficiently qualified to be our chair</p>
			<div class="indent">
				<table class="results">
					<tr><td></td>
						<th style="border-left:2px solid black;border-right:2px solid black; border-top:2px solid black;">Total</th>
						<th>Staff</th>
						<th>Lecturer</th>
						<th>Senior Lecturer</th>
						<th>Associate Professor</th>
						<th>Assistant Professor</th>
						<th>Professor</th>
						<th>Visiting</th>
						<th>Prefer not to answer</th>
					</tr>
					<tr><td class="option">Acceptable</td>
						<td style="border-left:2px solid black;border-right:2px solid black;">
							<?php echo $qualified_response_counts['Acceptable']['Total']?>
							<span class="percent"><?php echo '('.$qualified_response_counts['Acceptable']['Total']*100 / ($qualified_response_counts['Acceptable']['Total']+$qualified_response_counts['Unacceptable']['Total']).'%)'; ?></span></td>
						<td><?php echo $qualified_response_counts['Acceptable']['Staff']?></td>
						<td><?php echo $qualified_response_counts['Acceptable']['Lecturer']?></td>
						<td><?php echo $qualified_response_counts['Acceptable']['Senior Lecturer']?></td>
						<td><?php echo $qualified_response_counts['Acceptable']['Associate Professor']?></td>
						<td><?php echo $qualified_response_counts['Acceptable']['Assistant Professor']?></td>
						<td><?php echo $qualified_response_counts['Acceptable']['Professor']?></td>
						<td><?php echo $qualified_response_counts['Acceptable']['Visiting']?></td>
						<td><?php echo $qualified_response_counts['Acceptable']['Prefer Not to Answer']?></td>
					</tr>
					<tr><td class="option">Unacceptable</td>
						<td style="border-left:2px solid black;border-right:2px solid black;border-bottom:2px solid black;">
							<?php echo $qualified_response_counts['Unacceptable']['Total']?>
							<span class="percent"><?php echo '('.$qualified_response_counts['Unacceptable']['Total']*100 / ($qualified_response_counts['Acceptable']['Total']+$qualified_response_counts['Unacceptable']['Total']).'%)'; ?></span></td>
						<td><?php echo $qualified_response_counts['Unacceptable']['Staff']?></td>
						<td><?php echo $qualified_response_counts['Unacceptable']['Lecturer']?></td>
						<td><?php echo $qualified_response_counts['Unacceptable']['Senior Lecturer']?></td>
						<td><?php echo $qualified_response_counts['Unacceptable']['Associate Professor']?></td>
						<td><?php echo $qualified_response_counts['Unacceptable']['Assistant Professor']?></td>
						<td><?php echo $qualified_response_counts['Unacceptable']['Professor']?></td>
						<td><?php echo $qualified_response_counts['Unacceptable']['Visiting']?></td>
						<td><?php echo $qualified_response_counts['Unacceptable']['Prefer Not to Answer']?></td>
					</tr>
				</table>
				<div class="comment_block">
					<p><strong>Comments:</strong></p>
					<ul><?php foreach (get_qualified_comments($candidate[0]) as $comment): ?>
						<?php if ($comment != ""): ?><li><?php echo $comment; ?></li><?php endif; ?>
					<?php endforeach; ?></ul>
				</div>
			</div>
			<br>
		
			<p class="nobreakafter">3. Ability to lead our mathematical sciences department</p>
			<div class="indent">
				<table class="results">
					<tr><td></td>
						<th style="border-left:2px solid black;border-right:2px solid black; border-top:2px solid black;">Total</th>
						<th>Staff</th>
						<th>Lecturer</th>
						<th>Senior Lecturer</th>
						<th>Associate Professor</th>
						<th>Assistant Professor</th>
						<th>Professor</th>
						<th>Visiting</th>
						<th>Prefer not to answer</th>
					</tr>
					<tr><td class="option">Poor</td>
						<td style="border-left:2px solid black;border-right:2px solid black;">
							<?php echo $leader_response_counts['Poor']['Total']?>
							<span class="percent"><?php echo '('.$leader_response_counts['Poor']['Total']*100 / ($leader_response_counts['Poor']['Total']+$leader_response_counts['Fair']['Total']+$leader_response_counts['Good']['Total']+$leader_response_counts['Very Good']['Total']).'%)'; ?></span></td>
						<td><?php echo $leader_response_counts['Poor']['Staff']?></td>
						<td><?php echo $leader_response_counts['Poor']['Lecturer']?></td>
						<td><?php echo $leader_response_counts['Poor']['Senior Lecturer']?></td>
						<td><?php echo $leader_response_counts['Poor']['Associate Professor']?></td>
						<td><?php echo $leader_response_counts['Poor']['Assistant Professor']?></td>
						<td><?php echo $leader_response_counts['Poor']['Professor']?></td>
						<td><?php echo $leader_response_counts['Poor']['Visiting']?></td>
						<td><?php echo $leader_response_counts['Poor']['Prefer Not to Answer']?></td>
					</tr>
					<tr><td class="option">Fair</td>
						<td style="border-left:2px solid black;border-right:2px solid black;">
							<?php echo $leader_response_counts['Fair']['Total']?>
							<span class="percent"><?php echo '('.$leader_response_counts['Fair']['Total']*100 / ($leader_response_counts['Poor']['Total']+$leader_response_counts['Fair']['Total']+$leader_response_counts['Good']['Total']+$leader_response_counts['Very Good']['Total']).'%)'; ?></span></td>
						<td><?php echo $leader_response_counts['Fair']['Staff']?></td>
						<td><?php echo $leader_response_counts['Fair']['Lecturer']?></td>
						<td><?php echo $leader_response_counts['Fair']['Senior Lecturer']?></td>
						<td><?php echo $leader_response_counts['Fair']['Associate Professor']?></td>
						<td><?php echo $leader_response_counts['Fair']['Assistant Professor']?></td>
						<td><?php echo $leader_response_counts['Fair']['Professor']?></td>
						<td><?php echo $leader_response_counts['Fair']['Visiting']?></td>
						<td><?php echo $leader_response_counts['Fair']['Prefer Not to Answer']?></td>
					</tr>
					<tr><td class="option">Good</td>
						<td style="border-left:2px solid black;border-right:2px solid black;">
							<?php echo $leader_response_counts['Good']['Total']?>
							<span class="percent"><?php echo '('.$leader_response_counts['Good']['Total']*100 / ($leader_response_counts['Poor']['Total']+$leader_response_counts['Fair']['Total']+$leader_response_counts['Good']['Total']+$leader_response_counts['Very Good']['Total']).'%)'; ?></span></td>
						<td><?php echo $leader_response_counts['Good']['Staff']?></td>
						<td><?php echo $leader_response_counts['Good']['Lecturer']?></td>
						<td><?php echo $leader_response_counts['Good']['Senior Lecturer']?></td>
						<td><?php echo $leader_response_counts['Good']['Associate Professor']?></td>
						<td><?php echo $leader_response_counts['Good']['Assistant Professor']?></td>
						<td><?php echo $leader_response_counts['Good']['Professor']?></td>
						<td><?php echo $leader_response_counts['Good']['Visiting']?></td>
						<td><?php echo $leader_response_counts['Good']['Prefer Not to Answer']?></td>
					</tr>
					<tr><td class="option">Very Good</td>
						<td style="border-left:2px solid black;border-right:2px solid black;border-bottom:2px solid black;">
							<?php echo $leader_response_counts['Very Good']['Total']?>
							<span class="percent"><?php echo '('.$leader_response_counts['Very Good']['Total']*100 / ($leader_response_counts['Poor']['Total']+$leader_response_counts['Fair']['Total']+$leader_response_counts['Good']['Total']+$leader_response_counts['Very Good']['Total']).'%)'; ?></span></td>
						<td><?php echo $leader_response_counts['Very Good']['Staff']?></td>
						<td><?php echo $leader_response_counts['Very Good']['Lecturer']?></td>
						<td><?php echo $leader_response_counts['Very Good']['Senior Lecturer']?></td>
						<td><?php echo $leader_response_counts['Very Good']['Associate Professor']?></td>
						<td><?php echo $leader_response_counts['Very Good']['Assistant Professor']?></td>
						<td><?php echo $leader_response_counts['Very Good']['Professor']?></td>
						<td><?php echo $leader_response_counts['Very Good']['Visiting']?></td>
						<td><?php echo $leader_response_counts['Very Good']['Prefer Not to Answer']?></td>
					</tr>
				</table>
				<div class="comment_block">
					<p><strong>Comments:</strong></p>
					<ul><?php foreach (get_leader_comments($candidate[0]) as $comment): ?>
						<?php if ($comment != ""): ?><li><?php echo $comment; ?></li><?php endif; ?>
					<?php endforeach; ?></ul>
				</div>
			</div>
			<br>
		
			<p class="nobreakafter">4. Vision</p>
			<div class="indent">
				<table class="results">
					<tr><td></td>
						<th style="border-left:2px solid black;border-right:2px solid black; border-top:2px solid black;">Total</th>
						<th>Staff</th>
						<th>Lecturer</th>
						<th>Senior Lecturer</th>
						<th>Associate Professor</th>
						<th>Assistant Professor</th>
						<th>Professor</th>
						<th>Visiting</th>
						<th>Prefer not to answer</th>
					</tr>
					<tr><td class="option">Poor</td>
						<td style="border-left:2px solid black;border-right:2px solid black;">
							<?php echo $vision_response_counts['Poor']['Total']?>
							<span class="percent"><?php echo '('.$vision_response_counts['Poor']['Total']*100 / ($vision_response_counts['Poor']['Total']+$vision_response_counts['Fair']['Total']+$vision_response_counts['Good']['Total']+$vision_response_counts['Very Good']['Total']).'%)'; ?></span></td>
						<td><?php echo $vision_response_counts['Poor']['Staff']?></td>
						<td><?php echo $vision_response_counts['Poor']['Lecturer']?></td>
						<td><?php echo $vision_response_counts['Poor']['Senior Lecturer']?></td>
						<td><?php echo $vision_response_counts['Poor']['Associate Professor']?></td>
						<td><?php echo $vision_response_counts['Poor']['Assistant Professor']?></td>
						<td><?php echo $vision_response_counts['Poor']['Professor']?></td>
						<td><?php echo $vision_response_counts['Poor']['Visiting']?></td>
						<td><?php echo $vision_response_counts['Poor']['Prefer Not to Answer']?></td>
					</tr>
					<tr><td class="option">Fair</td>
						<td style="border-left:2px solid black;border-right:2px solid black;">
							<?php echo $vision_response_counts['Fair']['Total']?>
							<span class="percent"><?php echo '('.$vision_response_counts['Fair']['Total']*100 / ($vision_response_counts['Poor']['Total']+$vision_response_counts['Fair']['Total']+$vision_response_counts['Good']['Total']+$vision_response_counts['Very Good']['Total']).'%)'; ?></span></td>
						<td><?php echo $vision_response_counts['Fair']['Staff']?></td>
						<td><?php echo $vision_response_counts['Fair']['Lecturer']?></td>
						<td><?php echo $vision_response_counts['Fair']['Senior Lecturer']?></td>
						<td><?php echo $vision_response_counts['Fair']['Associate Professor']?></td>
						<td><?php echo $vision_response_counts['Fair']['Assistant Professor']?></td>
						<td><?php echo $vision_response_counts['Fair']['Professor']?></td>
						<td><?php echo $vision_response_counts['Fair']['Visiting']?></td>
						<td><?php echo $vision_response_counts['Fair']['Prefer Not to Answer']?></td>
					</tr>
					<tr><td class="option">Good</td>
						<td style="border-left:2px solid black;border-right:2px solid black;">
							<?php echo $vision_response_counts['Good']['Total']?>
							<span class="percent"><?php echo '('.$vision_response_counts['Good']['Total']*100 / ($vision_response_counts['Poor']['Total']+$vision_response_counts['Fair']['Total']+$vision_response_counts['Good']['Total']+$vision_response_counts['Very Good']['Total']).'%)'; ?></span></td>
						<td><?php echo $vision_response_counts['Good']['Staff']?></td>
						<td><?php echo $vision_response_counts['Good']['Lecturer']?></td>
						<td><?php echo $vision_response_counts['Good']['Senior Lecturer']?></td>
						<td><?php echo $vision_response_counts['Good']['Associate Professor']?></td>
						<td><?php echo $vision_response_counts['Good']['Assistant Professor']?></td>
						<td><?php echo $vision_response_counts['Good']['Professor']?></td>
						<td><?php echo $vision_response_counts['Good']['Visiting']?></td>
						<td><?php echo $vision_response_counts['Good']['Prefer Not to Answer']?></td>
					</tr>
					<tr><td class="option">Very Good</td>
						<td style="border-left:2px solid black;border-right:2px solid black;border-bottom:2px solid black;">
							<?php echo $vision_response_counts['Very Good']['Total']?>
							<span class="percent"><?php echo '('.$vision_response_counts['Very Good']['Total']*100 / ($vision_response_counts['Poor']['Total']+$vision_response_counts['Fair']['Total']+$vision_response_counts['Good']['Total']+$vision_response_counts['Very Good']['Total']).'%)'; ?></span></td>
						<td><?php echo $vision_response_counts['Very Good']['Staff']?></td>
						<td><?php echo $vision_response_counts['Very Good']['Lecturer']?></td>
						<td><?php echo $vision_response_counts['Very Good']['Senior Lecturer']?></td>
						<td><?php echo $vision_response_counts['Very Good']['Associate Professor']?></td>
						<td><?php echo $vision_response_counts['Very Good']['Assistant Professor']?></td>
						<td><?php echo $vision_response_counts['Very Good']['Professor']?></td>
						<td><?php echo $vision_response_counts['Very Good']['Visiting']?></td>
						<td><?php echo $vision_response_counts['Very Good']['Prefer Not to Answer']?></td>
					</tr>
				</table>
				<div class="comment_block">
					<p><strong>Comments:</strong></p>
					<ul><?php foreach (get_vision_comments($candidate[0]) as $comment): ?>
						<?php if ($comment != ""): ?><li><?php echo $comment; ?></li><?php endif; ?>
					<?php endforeach; ?></ul>
				</div>
			</div>
			<br>
		
			<p class="nobreakafter">5. If all other candidates decline and <?php echo $candidate[0]; ?> becomes chair by default, then I will feel...</p>
			<div class="indent">
				<table class="results">
					<tr><td></td>
						<th style="border-left:2px solid black;border-right:2px solid black; border-top:2px solid black;">Total</th>
						<th>Staff</th>
						<th>Lecturer</th>
						<th>Senior Lecturer</th>
						<th>Associate Professor</th>
						<th>Assistant Professor</th>
						<th>Professor</th>
						<th>Visiting</th>
						<th>Prefer not to answer</th>
					</tr>
					<tr><td class="option">Enthusiastic</td>
						<td style="border-left:2px solid black;border-right:2px solid black;">
							<?php echo $default_response_counts['Enthusiastic']['Total']?>
							<span class="percent"><?php echo '('.$default_response_counts['Enthusiastic']['Total']*100 / ($default_response_counts['Enthusiastic']['Total']+$default_response_counts['Satisfied or content']['Total']+$default_response_counts['Neutral']['Total']+$default_response_counts['Disappointed or discontent']['Total']+$default_response_counts['Sick or worried']['Total']).'%)'; ?></span></td>
						<td><?php echo $default_response_counts['Enthusiastic']['Staff']?></td>
						<td><?php echo $default_response_counts['Enthusiastic']['Lecturer']?></td>
						<td><?php echo $default_response_counts['Enthusiastic']['Senior Lecturer']?></td>
						<td><?php echo $default_response_counts['Enthusiastic']['Associate Professor']?></td>
						<td><?php echo $default_response_counts['Enthusiastic']['Assistant Professor']?></td>
						<td><?php echo $default_response_counts['Enthusiastic']['Professor']?></td>
						<td><?php echo $default_response_counts['Enthusiastic']['Visiting']?></td>
						<td><?php echo $default_response_counts['Enthusiastic']['Prefer Not to Answer']?></td>
					</tr>
					<tr><td class="option">Satisfied or content</td>
						<td style="border-left:2px solid black;border-right:2px solid black;">
							<?php echo $default_response_counts['Satisfied or content']['Total']?>
							<span class="percent"><?php echo '('.$default_response_counts['Satisfied or content']['Total']*100 / ($default_response_counts['Enthusiastic']['Total']+$default_response_counts['Satisfied or content']['Total']+$default_response_counts['Neutral']['Total']+$default_response_counts['Disappointed or discontent']['Total']+$default_response_counts['Sick or worried']['Total']).'%)'; ?></span></td>
						<td><?php echo $default_response_counts['Satisfied or content']['Staff']?></td>
						<td><?php echo $default_response_counts['Satisfied or content']['Lecturer']?></td>
						<td><?php echo $default_response_counts['Satisfied or content']['Senior Lecturer']?></td>
						<td><?php echo $default_response_counts['Satisfied or content']['Associate Professor']?></td>
						<td><?php echo $default_response_counts['Satisfied or content']['Assistant Professor']?></td>
						<td><?php echo $default_response_counts['Satisfied or content']['Professor']?></td>
						<td><?php echo $default_response_counts['Satisfied or content']['Visiting']?></td>
						<td><?php echo $default_response_counts['Satisfied or content']['Prefer Not to Answer']?></td>
					</tr>
					<tr><td class="option">Neutral</td>
						<td style="border-left:2px solid black;border-right:2px solid black;">
							<?php echo $default_response_counts['Neutral']['Total']?>
							<span class="percent"><?php echo '('.$default_response_counts['Neutral']['Total']*100 / ($default_response_counts['Enthusiastic']['Total']+$default_response_counts['Satisfied or content']['Total']+$default_response_counts['Neutral']['Total']+$default_response_counts['Disappointed or discontent']['Total']+$default_response_counts['Sick or worried']['Total']).'%)'; ?></span></td>
						<td><?php echo $default_response_counts['Neutral']['Staff']?></td>
						<td><?php echo $default_response_counts['Neutral']['Lecturer']?></td>
						<td><?php echo $default_response_counts['Neutral']['Senior Lecturer']?></td>
						<td><?php echo $default_response_counts['Neutral']['Associate Professor']?></td>
						<td><?php echo $default_response_counts['Neutral']['Assistant Professor']?></td>
						<td><?php echo $default_response_counts['Neutral']['Professor']?></td>
						<td><?php echo $default_response_counts['Neutral']['Visiting']?></td>
						<td><?php echo $default_response_counts['Neutral']['Prefer Not to Answer']?></td>
					</tr>
					<tr><td class="option">Disappointed or discontent</td>
						<td style="border-left:2px solid black;border-right:2px solid black;">
							<?php echo $default_response_counts['Disappointed or discontent']['Total']?>
							<span class="percent"><?php echo '('.$default_response_counts['Disappointed or discontent']['Total']*100 / ($default_response_counts['Enthusiastic']['Total']+$default_response_counts['Satisfied or content']['Total']+$default_response_counts['Neutral']['Total']+$default_response_counts['Disappointed or discontent']['Total']+$default_response_counts['Sick or worried']['Total']).'%)'; ?></span></td>
						<td><?php echo $default_response_counts['Disappointed or discontent']['Staff']?></td>
						<td><?php echo $default_response_counts['Disappointed or discontent']['Lecturer']?></td>
						<td><?php echo $default_response_counts['Disappointed or discontent']['Senior Lecturer']?></td>
						<td><?php echo $default_response_counts['Disappointed or discontent']['Associate Professor']?></td>
						<td><?php echo $default_response_counts['Disappointed or discontent']['Assistant Professor']?></td>
						<td><?php echo $default_response_counts['Disappointed or discontent']['Professor']?></td>
						<td><?php echo $default_response_counts['Disappointed or discontent']['Visiting']?></td>
						<td><?php echo $default_response_counts['Disappointed or discontent']['Prefer Not to Answer']?></td>
					</tr>
					<tr><td class="option">Sick or worried</td>
						<td style="border-left:2px solid black;border-right:2px solid black;border-bottom:2px solid black;">
							<?php echo $default_response_counts['Sick or worried']['Total']?>
							<span class="percent"><?php echo '('.$default_response_counts['Sick or worried']['Total']*100 / ($default_response_counts['Enthusiastic']['Total']+$default_response_counts['Satisfied or content']['Total']+$default_response_counts['Neutral']['Total']+$default_response_counts['Disappointed or discontent']['Total']+$default_response_counts['Sick or worried']['Total']).'%)'; ?></span></td>
						<td><?php echo $default_response_counts['Sick or worried']['Staff']?></td>
						<td><?php echo $default_response_counts['Sick or worried']['Lecturer']?></td>
						<td><?php echo $default_response_counts['Sick or worried']['Senior Lecturer']?></td>
						<td><?php echo $default_response_counts['Sick or worried']['Associate Professor']?></td>
						<td><?php echo $default_response_counts['Sick or worried']['Assistant Professor']?></td>
						<td><?php echo $default_response_counts['Sick or worried']['Professor']?></td>
						<td><?php echo $default_response_counts['Sick or worried']['Visiting']?></td>
						<td><?php echo $default_response_counts['Sick or worried']['Prefer Not to Answer']?></td>
					</tr>
				</table>
			</div>
			<br>
			
		</div>	
		<div id="footer" class="noprint">Click <a href="/dept_forms/website_error.py" target="_blank">here</a> to report a problem with this website.</div>
	</div>
</body>
</html>