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
	$user_hash = md5($user_id.'interim-chair_survey99');
}

$q4_options = array('School of Mathematical and Statistical Sciences',
					'School of Mathematics and Decision Science',
					'School of Mathematics, Statistics, and Operations Research',
					'School of Mathematical, Data, and Decision Sciences',
					'Other Name');



//$privileged = array('SNHENNI','CYYOUNG','LGEHRIN','MEL');

function isInMath($user_id)
{
	global $mthsc_db;
	global $privileged;
	$stmt = $mthsc_db->prepare("SELECT 1 FROM `dept_info`.`people_to_lists_link` as pll JOIN `dept_info`.`person` as p on pll.person_id = p.person_id WHERE list_id=10 AND username = ?");
	$stmt->execute(array($user_id));
	$userExists = $stmt->fetchColumn();
	if ($userExists){return true;}
	else
	{
		if (in_array(strtoupper($user_id),$privileged))
		{
			return true;
		}
		else
		{
			return false;
		}
	}	
}

function has_submitted($user_hash)
{
	global $mthsc_db;
	$stmt = $mthsc_db->prepare("SELECT submitted FROM `interim_chair_survey` WHERE user_hash = ? ");
	$stmt->execute(array($user_hash));
	$has_submitted = $stmt->fetchColumn();
	if ($has_submitted){return $has_submitted;}
	else {return false;}
}

$accepting_submissions = true;
$currentTime = strtotime('now');
if ($currentTime > mktime(12, 00, 00, 5, 1, 2018) || $currentTime < mktime(10, 00, 00, 4, 27, 2018))
{
	$accepting_submissions = false;
}

$candidates = array(
	'a' => 'Dr. Kevin James',
	'b' => 'Dr. Pete Kiessler',
	'c' => 'Dr. Leo Rebholz');
	
$candidates_for_ranking = array('Dr. Kevin James','Dr. Pete Kiessler','Dr. Leo Rebholz');

$ranking_questions = array(
	'2' => 'Ability to lead the mathematical sciences department effectively',
	'3' => 'Ability to lead the mathematical sciences department transparently',
	'4' => 'Vision',
	'5' => 'Ability to foster a collegial departmental environment',
	'6' => 'Ability to evaluate faculty and staff honestly and fairly, including TPR',
	'7' => 'Ability to manage faculty and staff effectively and fairly, including workload',
	'8' => 'Ability to work with faculty, staff, and students toward departmental goals',
	'9' => 'Ability to coordinate potential departmental restructuring',
	'10' => 'Ability to manage departmental growth',
	'11' => 'Ability to manage departmental budget',
	'12' => 'Ability to organize meetings',
	'13' => 'Ability to represent department to college and university administration and to other units');
	
$free_response_questions = array(
	'14' => 'What additional strengths do you see in the candidates with regard to serving in the interim chair position?',
	'15' => 'What concerns do you have about this candidate with regard to serving in the interim chair position?',
	'16' => 'Add any additional comments you would like to share about the candidates');

	
$options = array(
	'p' => 'Poor',
	'f' => 'Fair', 
	'g' => 'Good',
	'v' => 'Very Good',
	'd' => 'Don\'t Know');



?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Interim Chair Survey</title>
	<meta name="author" content="hedetni">
	<!-- Date: 2018-4-25 -->

<link rel="stylesheet" href="/style/math.css" type="text/css" media="screen" title="no title" charset="utf-8">
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

<style type="text/css">
table.ranking_options {
	margin-left:1.5em;
	margin-top:0.5em;
}
table.ranking_options {
	cursor:pointer;
}
textarea {
	margin-left:1.5em;
}
label {
	margin-left:0.25em;
}
.indent {
	margin-left:1.5em;
}
td.ranking {
	text-align:center;
}
td.handle {border:none;background-color:transparent;}
.ui-state-highlight { height: 1.5em; line-height: 1.2em; }
h3 {margin-top:2em;}

</style>

<script src="/style/jquery-3.2.1.min.js" type="text/javascript" charset="utf-8"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="jquery.ui.touch-punch.min.js"></script>
<script type="text/javascript">
$(document).ready(function() 
{
	$( ".ranking_options tbody" ).sortable({
		placeholder: "ui-state-highlight",
		stop: function( event, ui ){
			$(this).find('tr').each(function(i){
				var q = $(this).attr('id');
				var question = q.substring(1,2);
				var ranking = i+1;
				var option = $(this).find('td:last').html()
				$('input#question'+question+'_ranked_'+ranking).val(option);
				$(this).find('td:first').text(i+1);
			});
			//if (ui.item.find('td:last').html() == "Other")
		}
	});
	$( ".ranking_options" ).disableSelection();

	$("input[type='radio']").prop("checked",false);
	$('')
	
});
</script>

</head>
<body>
	<div id="main">
		<div id="header">
			<a href="http://www.clemson.edu/math" title="Department Home">
				<img src="/style/science-math-logo-white.png" height="73px" alt="math department logo">
			</a>
			<h1><a href="index.php">Interim Chair Survey</a></h1>
		</div>
	
		<div id="content">
			<?php if (isInMath($user_id) && has_submitted($user_hash) == false): ?>
				<noscript>
					<style type="text/css">
						form {display:none;}
					</style>
					<div class="noscriptmsg">
						<p>You must have javascript enabled to submit this survey.</p>
					</div>
				</noscript>
				
				<?php if ($accepting_submissions): ?>
				<form name="school_survey_form" id="school_survey_form" method="POST" action="review.php">
					
					<p>1. Please rank the candidates, with 1 being the candidate you would most support hiring, and 3 being the least:</p>
					<label>(Drag and drop to arrange)</label>
					<table class="ranking_options" id="question4"><tbody>
						<?php foreach ($candidates_for_ranking as $rank_cand => $rank_candidate): ?>
						<tr id="q1_<?php echo $rank_cand+1; ?>"><td class="ranking"><?php echo $rank_cand+1; ?></td><td class="handle"></td><td><?php echo $rank_candidate; ?></td></tr>
						<?php endforeach; ?>
					</tbody></table>
					<?php foreach ($candidates_for_ranking as $rank_cand => $rank_candidate): ?>
					<input type="hidden" name="question1_ranked_<?php echo $rank_cand+1; ?>" value="<?php echo $candidates_for_ranking[$rank_cand];?>" id="question1_ranked_<?php echo $rank_cand+1; ?>">
					<?php endforeach; ?>
					<br>
					
			<!-- 2-13 -->
					<?php foreach ($ranking_questions as $number => $question): ?>
						<p><?php echo $number; ?>. <?php echo $question; ?></p>
						<div class="indent">
							<?php foreach ($candidates as $cand => $candidate): ?>
								<?php echo $candidate; ?>
								<table class="option_table">
									<tr>
										<?php foreach ($options as $abbr => $option): ?>
										<td>
											<input type="radio" name="q<?php echo $number.'_'.$cand;?>" value="<?php echo $option;?>" id="o<?php echo $number.'-'.$cand.'-'.$abbr;?>"></input>
											<label for="o<?php echo $number.'-'.$cand.'-'.$abbr;?>"><?php echo $option;?></label>
										</td>
										<?php endforeach; ?>
									</tr>
								</table>
								<br>
							<?php endforeach; ?>
						</div>
						<br>
					<?php endforeach; ?>
			
			<!-- 14-16 -->
					<?php foreach ($free_response_questions as $free_number => $free_question): ?>
						<p><?php echo $free_number;?>. <?php echo $free_question;?></p>
						<div class="indent">
							<?php foreach ($candidates as $cand => $candidate):?>
								<p><?php echo $candidate; ?><br>
								<textarea name="q<?php echo $free_number.'_'.$cand;?>" rows="6" cols="80"></textarea></p>
							<?php endforeach; ?>
						</div><br>
					<?php endforeach; ?>
					

					<p>17. Please select your role in the department. These options reflect the listening session groups. Those who prefer not to answer here, will have their responses grouped together.
					<table class="option_table_role">
						<tr><th>Role</th></tr>
						<tr><td><input type="radio" name="role" value="Graduate Student" id="Graduate Student"></input> <label for="Graduate Student">Graduate Student</label></td></tr>
						<tr><td><input type="radio" name="role" value="Staff" id="Staff"></input> <label for="Staff">Staff</label></td></tr>
						<tr><td><input type="radio" name="role" value="Lecturer/Senior Lecturer" id="Lecturer/Senior Lecturer"></input> <label for="Lecturer/Senior Lecturer">Lecturer/Senior Lecturer</label></td></tr>
						<tr><td><input type="radio" name="role" value="Associate Professor" id="Associate"></input> <label for="Associate">Associate Professor</label></td></tr>
						<tr><td><input type="radio" name="role" value="Assistant Professor" id="Assistant"></input> <label for="Assistant">Assistant Professor</label></td></tr>
						<tr><td><input type="radio" name="role" value="Professor" id="Professor"></input> <label for="Professor">Professor</label></td></tr>
						<tr><td><input type="radio" name="role" value="Prefer Not to Answer" id="no_answer"></input> <label for="no_answer">Prefer Not to Answer</label></td></tr>
					</table>
					<br>
					
					<p><input type="submit" name="submit_survey" value="Submit Responses"></input></p>
				</form>
				<?php else: ?>
					<p>We are not accepting submissions at this time.</p>
				<?php endif; ?>
			<?php else: ?>
				<p>You have either already filled out this survey, or are otherwise not eligible. Contact Kevin Hedetniemi for more information.</p>
			<?php endif; ?>
		</div>	
		<div id="footer">Click <a href="/dept_forms/website_error.py" target="_blank">here</a> to report a problem with this website.</div>
	</div>
</body>
</html>