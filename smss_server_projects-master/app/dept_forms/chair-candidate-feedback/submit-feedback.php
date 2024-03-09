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

$candidates = array("0",
				array("Beatrice Riviere","Rice","3 November 2017 6:00pm","11 December 2017 9:00am"),
				array("Mark Gockenbach","Michigan Tech","7 November 2017 6:00pm","11 December 2017 9:00am"),
				array("Tim Hodges","Cincinnati, NSF","9 November 2017 6:00pm","11 December 2017 9:00am"),
				array("Joshua Tebbs","USC","14 November 2017 6:00pm","11 December 2017 9:00am"),
				array("Kevin James","Clemson","16 November 2017 6:00pm","11 December 2017 9:00am"),
				array("Tamàs Terlaky","Lehigh","5 December 2017 6:00pm","11 December 2017 9:00am")
				);
				
if ($_GET['cand'] > 0 && $_GET['cand'] < 7)
{$candidate = $candidates[$_GET['cand']];}

function isInMath($user_id)
{
	global $mthsc_db;
	$stmt = $mthsc_db->prepare("SELECT 1 FROM `dept_info`.`people_to_lists_link` as pll JOIN `dept_info`.`person` as p on pll.person_id = p.person_id WHERE list_id=10 AND username = ?");
	$stmt->execute(array($user_id));
	$userExists = $stmt->fetchColumn();
	if ($userExists){return true;}
	else {return false;}
	
}
function survey_is_open($candidate)
{
	if (strtotime("now") >= strtotime($candidate[2]) && strtotime("now") < strtotime($candidate[3]))
	{
		return true;
	}
	else
	{
		return false; //true for testing, false for production
	}
}
function has_submitted($user_hash,$candidate)
{
	global $mthsc_db;
	$stmt = $mthsc_db->prepare("SELECT 1 FROM `chair_candidate_feedback_submitters` WHERE candidate = ? AND user_hash = ?");
	$stmt->execute(array($candidate[0],$user_hash));
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
			<?php if (isset($candidate) && $candidate !== 0): ?>
				<?php if (isInMath($user_id)): ?>
					<h1>Submit Feedback on <?php echo $candidate[0]; ?> (<?php echo $candidate[1]; ?>)</h1>
			
					<?php if (survey_is_open($candidate)): ?>
						<?php if (!has_submitted($user_hash, $candidate)): ?>
						
							<form name="feedback_form" action="index.php" method="POST">
								<p>This survey is anonymous. We record a hashed version of your user id in a separate table to document when you submit (to prevent multiple submissions). Your identity is not associated with your responses in any way. Your responses are not stored until you press the submit button at the bottom of this page. You may only submit this form once.</p>
								
								<p style="font-weight:bold;">Please rate <?php echo $candidate[0]; ?> on the following aspects:</p>
						
								<p>1. Overall</p>
								<div class="indent">
									<table class="option_table">
										<tr>
											<td><input type="radio" name="overall" value="Acceptable" id="1-a"></input> <label for="1-a">Acceptable</label></td>
											<td><input type="radio" name="overall" value="Unacceptable" id="1-u"></input> <label for="1-u">Unacceptable</label></td>
										</tr>
									</table>
									<p>Comments:<br><textarea name="overall_comments" rows="3" cols="60"></textarea></p>
								</div>
								<br>
							
								<p>2. Sufficiently qualified to be our chair</p>
								<div class="indent">
									<table class="option_table">
										<tr>
											<td><input type="radio" name="qualified" value="Acceptable" id="2-a"></input> <label for="2-a">Acceptable</label></td>
											<td><input type="radio" name="qualified" value="Unacceptable" id="2-u"></input> <label for="2-u">Unacceptable</label></td>
										</tr>
									</table>
									<p>Comments:<br><textarea name="qualified_comments" rows="3" cols="60"></textarea></p>
								</div>
								<br>
							
								<p>3. Ability to lead our mathematical sciences department</p>
								<div class="indent">
									<table class="option_table">
										<tr>
											<td><input type="radio" name="leader" value="Poor" id="3-p"></input> <label for="3-p">Poor</label></td>
											<td><input type="radio" name="leader" value="Fair" id="3-f"></input> <label for="3-f">Fair</label></td>
											<td><input type="radio" name="leader" value="Good" id="3-g"></input> <label for="3-g">Good</label></td>
											<td><input type="radio" name="leader" value="Very Good" id="3-vg"></input> <label for="3-vg">Very Good</label></td>
										</tr>
									</table>
									<p>Comments:<br><textarea name="leader_comments" rows="3" cols="60"></textarea></p>
								</div>
								<br>
							
								<p>4. Vision</p>
								<div class="indent">
									<table class="option_table">
										<tr>
											<td><input type="radio" name="vision" value="Poor" id="4-p"></input> <label for="4-p">Poor</label></td>
											<td><input type="radio" name="vision" value="Fair" id="4-f"></input> <label for="4-f">Fair</label></td>
											<td><input type="radio" name="vision" value="Good" id="4-g"></input> <label for="4-g">Good</label></td>
											<td><input type="radio" name="vision" value="Very Good" id="4-vg"></input> <label for="4-vg">Very Good</label></td>
										</tr>
									</table>
									<p>Comments:<br><textarea name="vision_comments" rows="3" cols="60"></textarea></p>
								</div>
								<br>
							
								<p>5. If all other candidates decline and <?php echo $candidate[0]; ?> becomes chair by default, then I will feel...</p>
								<div class="indent">
									<table class="option_table">
										<tr>
											<td><input type="radio" name="chair_by_default" value="Enthusiastic" id="5-e"></input> <label for="5-e">Enthusiastic</label></td>
											<td><input type="radio" name="chair_by_default" value="Satisfied or content" id="5-c"></input> <label for="5-c">Satisfied or content</label></td>
											<td><input type="radio" name="chair_by_default" value="Neutral" id="5-n"></input> <label for="5-n">Neutral</label></td>
											<td><input type="radio" name="chair_by_default" value="Disappointed or discontent" id="5-d"></input> <label for="5-d">Disappointed or discontent</label></td>
											<td><input type="radio" name="chair_by_default" value="Sick or worried" id="5-w"></input> <label for="5-w">Sick or worried</label></td>
										</tr>
									</table>
								</div>
								<br>
							
								<p>6. If you wish, select your role in the department. <a id="role_more_link">Click here to see how this information will be used</a></p>
							
								<div id="role_more" style="display:none;">
								<p>We are asking for your role to make it easier to see trends within groups and anticipate any broad issues with a candidate. Those who prefer not to answer, will have their responses grouped together. Comments will not be grouped and all responses remain anonymous. The data from this survey will be presented in the following way:</p>
								<table id="example">
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
									<tr><td>Heads</td>
										<td style="border-left:2px solid black;border-right:2px solid black;">50 <span style="color:#777;">(48%)</span></td>
										<td>4</td>
										<td>6</td>
										<td>11</td>
										<td>9</td>
										<td>3</td>
										<td>10</td>
										<td>3</td>
										<td>4</td>
									</tr>
									<tr><td>Tails</td>
										<td style="border-left:2px solid black;border-right:2px solid black;border-bottom:2px solid black;">54 <span style="color:#777;">(52%)</span></td>
										<td>4</td>
										<td>2</td>
										<td>10</td>
										<td>3</td>
										<td>14</td>
										<td>9</td>
										<td>4</td>
										<td>8</td>
									</tr>
								</table>
								<p><a id="close_role_more">Hide this explanation</a>
								</div>
							
								<div class="indent">
									<table class="option_table_role">
										<tr><th>Role</th></tr>
										<tr><td><input type="radio" name="role" value="Staff" id="Staff"></input> <label for="Staff">Staff</label></td></tr>
										<tr><td><input type="radio" name="role" value="Lecturer" id="Lecturer"></input> <label for="Lecturer">Lecturer</label></td></tr>
										<tr><td><input type="radio" name="role" value="Senior Lecturer" id="Senior"></input> <label for="Senior">Senior Lecturer</label></td></tr>
										<tr><td><input type="radio" name="role" value="Associate Professor" id="Associate"></input> <label for="Associate">Associate Professor</label></td></tr>
										<tr><td><input type="radio" name="role" value="Assistant Professor" id="Assistant"></input> <label for="Assistant">Assistant Professor</label></td></tr>
										<tr><td><input type="radio" name="role" value="Professor" id="Professor"></input> <label for="Professor">Professor</label></td></tr>
										<tr><td><input type="radio" name="role" value="Visiting" id="Visiting"></input> <label for="Visiting">Visiting (Postdoc, Lecturer, Assist. Prof.)</label></td></tr>
										<tr><td><input type="radio" name="role" value="Prefer Not to Answer" checked id="no_answer"></input> <label for="no_answer">Prefer Not to Answer</label></td></tr>
									</table>
								</div>
								<br><br>
								
								<p>You must click 'Submit Responses' below for your opinions to be recorded. You may only submit once.</p>
								<input type="hidden" name="candidate" value="<?php echo $candidate[0]; ?>">
								<input type="hidden" name="user_hash" value="<?php echo $user_hash; ?>">
								<input type="submit" name="submit_responses" value="Submit Responses">
							</form>
						
						
						
						<?php else: ?>
							You have already submitted feedback for this candidate.
						<?php endif; ?>
			
					<?php else: ?>
						Feedback is not being accepted for <?php echo $candidate[0]; ?> at this time.
					<?php endif; ?>
			
				<?php else: ?>
					Only Faculty and Staff of the Mathematical Sciences Department are eligible to fill out this survey.
				<?php endif; ?>
				
			<?php else: ?>
				No candidate specified
			<?php endif; ?>
			
		</div>	
		<div id="footer">Click <a href="/dept_forms/website_error.py" target="_blank">here</a> to report a problem with this website.</div>
	</div>
</body>
</html>