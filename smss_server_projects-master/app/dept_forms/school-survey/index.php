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
	$user_hash = md5($user_id.'school_survey42');
}

$q4_options = array('School of Mathematical and Statistical Sciences',
					'School of Mathematics and Decision Science',
					'School of Mathematics, Statistics, and Operations Research',
					'School of Mathematical, Data, and Decision Sciences',
					'Other Name');

$q2_options = array('Recruitment of students',
					'Recruitment of faculty',
					'Development and retention of faculty',
					'Academic programs',
					'External funding for discovery',
					'Strategic partnerships (CU and external)',
					'Alignment with SciForward and CU Forward',
					'Recruitment and development of leadership of unit',
					'Strategic advancement of unit',
					'Clearer reflection of strengths to those external to unit',
					'Other Benefit');

$q3_options = array('Administrative layer between faculty and director',
					'Additional costs associated with new structure',
					'Division of duties and workload of staff',
					'Division of duties and workload of faculty leaders',
					'Loss of sub-faculty identity',
					'Loss of signature breadth',
					'Proposed pillars/programs are reflective of today not future growth',
					'Imbalance of pillars/programs in terms of number of faculty',
					'Creates more division and opposed to less',
					'Creation of new policies, procedures, bylaws (including TPR)',
					'Other Concern');

$q1_options = array('Structure A',
					'Structure B',
					'Structure C',
					'Other Structure');



$privileged = array('SNHENNI','CYYOUNG','LGEHRIN','MEL');

function isInMath($user_id)
{
	global $mthsc_db;
	global $privileged;
	$stmt = $mthsc_db->prepare("SELECT 1 FROM `dept_info`.`people_to_lists_link` as pll JOIN `dept_info`.`person` as p on pll.person_id = p.person_id WHERE list_id=12 AND username = ?");
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
	$stmt = $mthsc_db->prepare("SELECT submitted FROM `school_survey` WHERE user_hash = ? ");
	$stmt->execute(array($user_hash));
	$has_submitted = $stmt->fetchColumn();
	if ($has_submitted){return $has_submitted;}
	else {return false;}
}

$accepting_submissions = true;
$currentTime = strtotime('now');
if ($currentTime > mktime(23, 59, 59, 4, 17, 2018))
{
	$accepting_submissions = false;
}


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Math School Survey</title>
	<meta name="author" content="hedetni">
	<!-- Date: 2018-M-D -->

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
	margin-left:1.5em;
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
	/*$("#question2_other").keyup(function(){
		var new_other = $(this).val();
		var td = $("table#question2").find('td').filter(function(){
			return $(this).html().substring(0,13) === 'Other Benefit';
		});
		td.html('Other Benefit: '+new_other);
		
	});*/
	
});
</script>

</head>
<body>
	<div id="main">
		<div id="header">
			<a href="http://www.clemson.edu/math" title="Department Home">
				<img src="/style/science-math-logo-white.png" height="73px" alt="math department logo">
			</a>
			<h1><a href="index.php">Mathematical Sciences School Survey</a></h1>
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
					<p><b>For all ranking questions, drag and drop the options to reflect your preferred order. This form may not work on all touch-based platforms.</b></p>
					
					<p>Please select your role in the department. These options reflect the listening session groups. Those who prefer not to answer here, will have their responses grouped together.
					<table class="option_table_role">
						<tr><th>Role</th></tr>
						<tr><td><input type="radio" name="role" value="Staff" id="Staff"></input> <label for="Staff">Staff</label></td></tr>
						<tr><td><input type="radio" name="role" value="Lecturer/Senior Lecturer" id="Lecturer/Senior Lecturer"></input> <label for="Lecturer/Senior Lecturer">Lecturer/Senior Lecturer</label></td></tr>
						<tr><td><input type="radio" name="role" value="Associate Professor" id="Associate"></input> <label for="Associate">Associate Professor</label></td></tr>
						<tr><td><input type="radio" name="role" value="Assistant Professor" id="Assistant"></input> <label for="Assistant">Assistant Professor</label></td></tr>
						<tr><td><input type="radio" name="role" value="Professor" id="Professor"></input> <label for="Professor">Professor</label></td></tr>
						<tr><td><input type="radio" name="role" value="Prefer Not to Answer" id="no_answer"></input> <label for="no_answer">Prefer Not to Answer</label></td></tr>
					</table>
					<br>
					
					<p>For the first question, consider the following department/school structures:</p>
					<div style="text-align:center;">
						<h3>Structure A</h3><img src="school-of-math-A1.jpg" width="80%"></p>
						<h3>Structure B</h3><img src="school-of-math-B1.jpg" width="80%"></p>
						<h3>Structure C</h3><img src="school-of-math-C.jpg" width="80%"></p>
					</div>
					<p>1. Please rank the above department/school structures with 1 being the best and 3 being the worst:</p>
					<label>(Drag and drop to arrange)</label>
					<table class="ranking_options" id="question4"><tbody>
						<?php foreach ($q1_options as $q1_option_number => $q1_option): ?>
						<tr id="q1_<?php echo $q1_option_number+1; ?>"><td class="ranking"><?php echo $q1_option_number+1; ?></td><td class="handle"></td><td><?php echo $q1_option; ?></td></tr>
						<?php endforeach; ?>
					</tbody></table>
					<input type="hidden" name="question1_ranked_1" value="<?php echo $q1_options[0];?>" id="question1_ranked_1">
					<input type="hidden" name="question1_ranked_2" value="<?php echo $q1_options[1];?>" id="question1_ranked_2">
					<input type="hidden" name="question1_ranked_3" value="<?php echo $q1_options[2];?>" id="question1_ranked_3">
					<input type="hidden" name="question1_ranked_4" value="<?php echo $q1_options[3];?>" id="question1_ranked_4">
					<p class="indent">Other structure:</p>
					<textarea name="other_structure" rows="4" cols="80" class="indent"></textarea>
					<br><br>
					
					<p>2. Please rank the benefits of a school structure with 1 being the most beneficial and 10 being the least:</p>
					<label>(Drag and drop to arrange)</label>
					<table class="ranking_options" id="question2"><tbody>
						<?php foreach ($q2_options as $q2_option_number => $q2_option): ?>
						<tr id="q2_<?php echo $q2_option_number+1; ?>"><td class="ranking"><?php echo $q2_option_number+1; ?></td><td class="handle"></td><td><?php echo $q2_option; ?></td></tr>
						<?php endforeach; ?>
					</tbody></table>
					<input type="hidden" name="question2_ranked_1" value="<?php echo $q2_options[0];?>" id="question2_ranked_1">
					<input type="hidden" name="question2_ranked_2" value="<?php echo $q2_options[1];?>" id="question2_ranked_2">
					<input type="hidden" name="question2_ranked_3" value="<?php echo $q2_options[2];?>" id="question2_ranked_3">
					<input type="hidden" name="question2_ranked_4" value="<?php echo $q2_options[3];?>" id="question2_ranked_4">
					<input type="hidden" name="question2_ranked_5" value="<?php echo $q2_options[4];?>" id="question2_ranked_5">
					<input type="hidden" name="question2_ranked_6" value="<?php echo $q2_options[5];?>" id="question2_ranked_6">
					<input type="hidden" name="question2_ranked_7" value="<?php echo $q2_options[6];?>" id="question2_ranked_7">
					<input type="hidden" name="question2_ranked_8" value="<?php echo $q2_options[7];?>" id="question2_ranked_8">
					<input type="hidden" name="question2_ranked_9" value="<?php echo $q2_options[8];?>" id="question2_ranked_9">
					<input type="hidden" name="question2_ranked_10" value="<?php echo $q2_options[9];?>" id="question2_ranked_10">
					<input type="hidden" name="question2_ranked_11" value="<?php echo $q2_options[10];?>" id="question2_ranked_11">
					<p class="indent">Other Benefit: <input type="text" size="80" name="question2_other" maxlength="80" id="question2_other"></input></p>
					<br>
			
					<p>3. Please rank the concerns of a school structure with 1 being the most concerning and 10 being the least:</p>
					<label>(Drag and drop to arrange)</label>
					<table class="ranking_options" id="question3"><tbody>
						<?php foreach ($q3_options as $q3_option_number => $q3_option): ?>
						<tr id="q3_<?php echo $q3_option_number+1; ?>"><td class="ranking"><?php echo $q3_option_number+1; ?></td><td class="handle"></td><td><?php echo $q3_option; ?></td></tr>
						<?php endforeach; ?>
					</tbody></table>
					<input type="hidden" name="question3_ranked_1" value="<?php echo $q3_options[0];?>" id="question3_ranked_1">
					<input type="hidden" name="question3_ranked_2" value="<?php echo $q3_options[1];?>" id="question3_ranked_2">
					<input type="hidden" name="question3_ranked_3" value="<?php echo $q3_options[2];?>" id="question3_ranked_3">
					<input type="hidden" name="question3_ranked_4" value="<?php echo $q3_options[3];?>" id="question3_ranked_4">
					<input type="hidden" name="question3_ranked_5" value="<?php echo $q3_options[4];?>" id="question3_ranked_5">
					<input type="hidden" name="question3_ranked_6" value="<?php echo $q3_options[5];?>" id="question3_ranked_6">
					<input type="hidden" name="question3_ranked_7" value="<?php echo $q3_options[6];?>" id="question3_ranked_7">
					<input type="hidden" name="question3_ranked_8" value="<?php echo $q3_options[7];?>" id="question3_ranked_8">
					<input type="hidden" name="question3_ranked_9" value="<?php echo $q3_options[8];?>" id="question3_ranked_9">
					<input type="hidden" name="question3_ranked_10" value="<?php echo $q3_options[9];?>" id="question3_ranked_10">
					<input type="hidden" name="question3_ranked_11" value="<?php echo $q3_options[10];?>" id="question3_ranked_11">
					<p class="indent">Other Concern: <input type="text" size="80" name="question3_other" maxlength="80" id="question3_other"></input></p>
					<br>
			
					<p>4. If a school structure is chosen, please rank the following possible names of the school, with 1 being the best and 4 being the worst:</p>
					<label>(Drag and drop to arrange)</label>
					<table class="ranking_options" id="question4"><tbody>
						<?php foreach ($q4_options as $q4_option_number => $q4_option): ?>
						<tr id="q4_<?php echo $q4_option_number+1; ?>"><td class="ranking"><?php echo $q4_option_number+1; ?></td><td class="handle"></td><td><?php echo $q4_option; ?></td></tr>
						<?php endforeach; ?>
					</tbody></table>
					<input type="hidden" name="question4_ranked_1" value="<?php echo $q4_options[0];?>" id="question4_ranked_1">
					<input type="hidden" name="question4_ranked_2" value="<?php echo $q4_options[1];?>" id="question4_ranked_2">
					<input type="hidden" name="question4_ranked_3" value="<?php echo $q4_options[2];?>" id="question4_ranked_3">
					<input type="hidden" name="question4_ranked_4" value="<?php echo $q4_options[3];?>" id="question4_ranked_4">
					<input type="hidden" name="question4_ranked_5" value="<?php echo $q4_options[4];?>" id="question4_ranked_5">
					<p class="indent">Other Name: <input type="text" size="80" name="question4_other" maxlength="80" id="question4_other"></input></p>
					<br>
			
					<p>5. Please use this space for any additional comments:</p>
					<textarea name="comments" rows="8" cols="100"></textarea>
					<br>
				
					<p><input type="submit" name="submit_survey" value="Submit Responses"></input></p>
				</form>
				<?php endif; ?>
			<?php else: ?>
				<p>You have either already filled out this survey, or are otherwise not eligible. Contact Kevin Hedetniemi for more information.</p>
			<?php endif; ?>
		</div>	
		<div id="footer">Click <a href="/dept_forms/website_error.py" target="_blank">here</a> to report a problem with this website.</div>
	</div>
</body>
</html>