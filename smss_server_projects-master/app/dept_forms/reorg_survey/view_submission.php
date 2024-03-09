<?php

$accepting_submissions = true;

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

//print_r($_POST);

if (isset($_SERVER['REMOTE_USER']))
{
	$user_id = strtoupper($_SERVER['REMOTE_USER']);
	$user_hash = md5($user_id.'survey');
}
	

//first check for requested id
if (isset($_GET['id']) && $_GET['id']!="")
{
	$getSubmission =  mysql_query('SELECT * FROM reorg_survey WHERE id = "'.$_GET['id'].'" LIMIT 1;');
	if (!$getSubmission)
	{
		$message .= 'Error accessing database: ' . mysql_error($link).'<br>';
	}
	else
	{
		$row = mysql_fetch_array($getSubmission);
	
		//store the responses to display
		$q1 = $row['q1'];
		$q2 = $row['q2'];
		$q3 = $row['q3'];
		$q3_comments = $row['q3_comments'];
		$q4 = $row['q4'];
		$q4_comments = $row['q4_comments'];
		$q5a = $row['q5a'];
		$q5b = $row['q5b'];
		$q5c = $row['q5c'];
		$q5d = $row['q5d'];
		$q5e = $row['q5e'];
		$q5f = $row['q5f'];
		$q5g = $row['q5g'];
		$q5_other = $row['q5_other'];
		$q6a = $row['q6a'];
		$q6b = $row['q6b'];
		$q6c = $row['q6c'];
		$q6d = $row['q6d'];
		$q6e = $row['q6e'];
		$q6f = $row['q6f'];
		$q6g = $row['q6g'];
		$q6_other = $row['q6_other'];
		$q7a = $row['q7a'];
		$q7b = $row['q7b'];
		$q7c = $row['q7c'];
		$q7d = $row['q7d'];
		$q7_other = $row['q7_other'];
		$q8a = $row['q8a'];
		$q8b = $row['q8b'];
		$q8c = $row['q8c'];
		$q8d = $row['q8d'];
		$q8_comments = $row['q8_comments'];
		$q9 = $row['q9'];
		$q9_comments = $row['q9_comments'];
		$q10 = $row['q10'];
		$q10_comments = $row['q10_comments'];
		$q11 = $row['q11'];
		$q11_other = $row['q11_other'];
		$q12 = $row['q12'];
		$q12_other = $row['q12_other'];
		$q13 = $row['q13'];
		$q13_comments = $row['q13_comments'];
		$q14 = $row['q14'];
		$q14_other = $row['q14_other'];
		$q15 = $row['q15'];
		$q15_comments = $row['q15_comments'];
		$q16 = $row['q16'];
		$q16_comments = $row['q16_comments'];
		$q17a = $row['q17a'];
		$q17b = $row['q17b'];
		$q17c = $row['q17c'];
		$q17d = $row['q17d'];
		$q17e = $row['q17e'];
		$q17_other = $row['q17_other'];
		$q18 = $row['q18'];
		$q18_comments = $row['q18_comments'];
		$q19 = $row['q19'];
		$q20 = $row['q20'];
		$q20_comments = $row['q20_comments'];
		$q21 = $row['q21'];
		$q22 = $row['q22'];
		$q23 = $row['q23'];
	}
}
else
{
	$message = "No submission selected.";
	$eligible = false;
}




?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Departmental Restructuring Survey Submission</title>
	<meta name="author" content="hedetni">
	<!-- Date: 2016-07-11 -->
	
	<link rel="shortcut icon" href="/favicon.ico">
	
	<link rel="stylesheet" href="/style/math.css" type="text/css" media="screen" title="no title" charset="utf-8">
	
	<script src="jquery-1.7.2.js" type="text/javascript" charset="utf-8"></script>
	<script src="jquery.validate.js" type="text/javascript" charset="utf-8"></script>
	
	
	
<style type="text/css">

td.center {
	text-align:center;
}
table.h_question {
	width:75%;
	table-layout:fixed;
}
</style>

<script type="text/javascript">
$(document).ready(function() 
{
	$("textarea").attr('readonly', true);
	$("input:text").attr('readonly', true);
	$(':radio:not(:checked)').attr('disabled', true);
	$(':checkbox:not(:checked)').attr('disabled', true);
	$(':checkbox:checked').attr('onclick', 'return false');
});
</script>

</head>
<body>
	<div id="main">
		<div id="header">
			<a href="http://www.clemson.edu/math" title="Department Home">
				<img src="/style/math_logo.png" alt="math department logo">
			</a>
		</div>

		<div id="content">
			<h1>Departmental Restructuring Survey</h1>
			

			<?php echo isset($message) ? '<p id="error">'.$message.'</p>' : ""; ?>
	
			</center>
			
			<?php echo isset($_GET['id']) ? '<p><b>Submission #'.$_GET['id'].'</b></p>' : ""; ?>
			
			<form id="survey_form" name="survey_form" action="" method="post">

				<p>1) Your Rank:</p>
				<table class="h_question">
					<tr>
						<td class="center">Lecturer<br><input type="radio" name="q1" value="Lecturer" id="Lecturer" <?php echo $q1 == "Lecturer" ? 'checked' : ""; ?> ></td>
						<td class="center">Senior Lecturer<br><input type="radio" name="q1" value="Senior Lecturer" <?php echo $q1 == "Senior Lecturer" ? 'checked' : ""; ?> ></td>
						<td class="center">Assistant Professor<br><input type="radio" name="q1" value="Assistant Professor" <?php echo $q1 == "Assistant Professor" ? 'checked' : ""; ?> ></td>
						<td class="center">Associate Professor<br><input type="radio" name="q1" value="Associate Professor" <?php echo $q1 == "Associate Professor" ? 'checked' : ""; ?> ></td>
						<td class="center">Full Professor<br><input type="radio" name="q1" value="Full Professor" <?php echo $q1 == "Full Professor" ? 'checked' : ""; ?> ></td>
						<td class="center">Wish not to specify<br><input type="radio" name="q1" value="Wish not to specify" <?php echo $q1 == "Wish not to specify" ? 'checked' : ""; ?> ></td>
					</tr>
				</table><br><br>
				
				
				<p>2) Subfaculty with which you most closely identify:</p>
				<table class="h_question">
					<tr>
						<td class="center">ADM<br><input type="radio" name="q2" value="ADM" <?php echo $q2 == "ADM" ? 'checked' : ""; ?> ></td>
						<td class="center">Analysis<br><input type="radio" name="q2" value="Analysis" <?php echo $q2 == "Analysis" ? 'checked' : ""; ?> ></td>
						<td class="center">Applied Stat<br><input type="radio" name="q2" value="Applied Stat" <?php echo $q2 == "Applied Stat" ? 'checked' : ""; ?> ></td>
						<td class="center">Comp. Math<br><input type="radio" name="q2" value="Comp Math" <?php echo $q2 == "Comp Math" ? 'checked' : ""; ?> ></td>
						<td class="center">Educ.<br><input type="radio" name="q2" value="Educ" <?php echo $q2 == "Educ" ? 'checked' : ""; ?> ></td>
						<td class="center">Math Stat<br><input type="radio" name="q2" value="Math Stat" <?php echo $q2 == "Math Stat" ? 'checked' : ""; ?> ></td>
						<td class="center">OR<br><input type="radio" name="q2" value="OR" <?php echo $q2 == "OR" ? 'checked' : ""; ?> ></td>
						<td class="center">Wish not to specify<br><input type="radio" name="q2" value="Wish not to specify" <?php echo $q2 == "Wish not to specify" ? 'checked' : ""; ?> ></td>
					</tr>
				</table><br><br>
				
				
				<p>This mission statement is taken from the Department website: The mission of the Department of Mathematical Sciences is to create and discover new knowledge in the mathematical sciences, to disseminate new and existing knowledge in the mathematical sciences, and to apply new and existing knowledge in the mathematical sciences to benefit the economic future of the state and nation.</p>

				<p>3) Do you agree with the department mission statement?</p>
				<table class="h_question">
					<tr>
						<td class="center">Strongly Agree<br><input type="radio" name="q3" value="Strongly Agree" <?php echo $q3 == "Strongly Agree" ? 'checked' : ""; ?> ></td>
						<td class="center">Agree<br><input type="radio" name="q3" value="Agree" <?php echo $q3 == "Agree" ? 'checked' : ""; ?> ></td>
						<td class="center">Undecided<br><input type="radio" name="q3" value="Undecided" <?php echo $q3 == "Undecided" ? 'checked' : ""; ?> ></td>
						<td class="center">Disagree<br><input type="radio" name="q3" value="Disagree" <?php echo $q3 == "Disagree" ? 'checked' : ""; ?> ></td>
						<td class="center">Strongly Disagree<br><input type="radio" name="q3" value="Strongly Disagree" <?php echo $q3 == "Strongly Disagree" ? 'checked' : ""; ?> ></td>
						<td class="center">Prefer Not to Respond<br><input type="radio" name="q3" value="Prefer Not to Respond" <?php echo $q3 == "Prefer Not to Respond" ? 'checked' : ""; ?> ></td>
					</tr>
				</table>
		
				<p>Comments:<br>
					<textarea name="q3_comments" id="q3_comments" cols="80" rows="5"><?php echo $q3_comments; ?></textarea></p><br>
				
				
				<p>This is a vision statement from a 2002 CHE self-study: The Department of Mathematical Sciences will progress in step with Clemson University toward the goal of being ranked among the Top 20 programs at public research universities. The Department will be recognized by Clemson University as a multi-disciplinary department offering degree programs of the highest quality, and for its collaborative research with individuals, departments and centers across the campus. The graduate program will be recognized nationally for its efforts to prepare mathematical scientists for academic and nonacademic employment, and for the high quality of the disciplinary and interdisciplinary research by its faculty and students. The undergraduate program will provide a solid foundation for careers requiring intensive logical and quantitative skills and will attract students with superior mathematics background. The general education service courses will prepare undergraduate students with the basic quantitative tools and critical thinking skills for success in their respective degree programs.</p>
				
				<p>4) Do you agree with this vision statement?</p>
				<table class="h_question">
					<tr>
						<td class="center">Strongly Agree<br><input type="radio" name="q4" value="Strongly Agree" <?php echo $q4 == "Strongly Agree" ? 'checked' : ""; ?> ></td>
						<td class="center">Agree<br><input type="radio" name="q4" value="Agree" <?php echo $q4 == "Agree" ? 'checked' : ""; ?> ></td>
						<td class="center">Undecided<br><input type="radio" name="q4" value="Undecided" <?php echo $q4 == "Undecided" ? 'checked' : ""; ?> ></td>
						<td class="center">Disagree<br><input type="radio" name="q4" value="Disagree" <?php echo $q4 == "Disagree" ? 'checked' : ""; ?> ></td>
						<td class="center">Strongly Disagree<br><input type="radio" name="q4" value="Strongly Disagree" <?php echo $q4 == "Strongly Disagree" ? 'checked' : ""; ?> ></td>
						<td class="center">Prefer Not to Respond<br><input type="radio" name="q4" value="Prefer Not to Respond" <?php echo $q4 == "Prefer Not to Respond" ? 'checked' : ""; ?> ></td>
					</tr>
				</table>
		
				<p>Comments:<br>
					<textarea name="q4_comments" id="q4_comments" cols="80" rows="5"><?php echo $q4_comments; ?></textarea></p><br>
				
				
				<p>5) What do you see as the biggest challenges for our department? (Choose all that apply)</p>
				<table class="v_question">
					<tr><td class="center"><input type="checkbox" name="q5a" <?php echo $q5a == 1 ? "checked": ""; ?>></td><td>a) Workload distribution</td></tr>
					<tr><td class="center"><input type="checkbox" name="q5b" <?php echo $q5b == 1 ? "checked": ""; ?>></td><td>b) Evaluation</td></tr>
					<tr><td class="center"><input type="checkbox" name="q5c" <?php echo $q5c == 1 ? "checked": ""; ?>></td><td>c) Hiring</td></tr>
					<tr><td class="center"><input type="checkbox" name="q5d" <?php echo $q5d == 1 ? "checked": ""; ?>></td><td>d) TPR</td></tr>
					<tr><td class="center"><input type="checkbox" name="q5e" <?php echo $q5e == 1 ? "checked": ""; ?>></td><td>e) Difficulty in establishing peer groups at other institutions</td></tr>
					<tr><td class="center"><input type="checkbox" name="q5f" <?php echo $q5f == 1 ? "checked": ""; ?>></td><td>f) Perception of the department by those outside the university</td></tr>
					<tr><td class="center"><input type="checkbox" name="q5g" <?php echo $q5g == 1 ? "checked": ""; ?>></td><td>g) Perception by those outside the department, within the university</td></tr>
					<tr><td class="center"></td><td>Other: <input name="q5_other" id="q5_other" size="40" type="text" value="<?php echo $q5_other; ?>"></input></td></tr>
				</table>
				<br><br>
				
				
				<p>6) What do you see as the potential benefits of department restructuring? (Choose all that apply)</p>
				<table class="v_question">
					<tr><td class="center"><input type="checkbox" name="q6a" <?php echo $q6a == 1 ? "checked": ""; ?>></td><td>a) More equitable workload distribution</td></tr>
					<tr><td class="center"><input type="checkbox" name="q6b" <?php echo $q6b == 1 ? "checked": ""; ?>></td><td>b) Evaluation by someone more familiar with your field of expertise</td></tr>
					<tr><td class="center"><input type="checkbox" name="q6c" <?php echo $q6c == 1 ? "checked": ""; ?>></td><td>c) Better hiring in areas of critical need</td></tr>
					<tr><td class="center"><input type="checkbox" name="q6d" <?php echo $q6d == 1 ? "checked": ""; ?>></td><td>d) Improved TPR process</td></tr>
					<tr><td class="center"><input type="checkbox" name="q6e" <?php echo $q6e == 1 ? "checked": ""; ?>></td><td>e) Identification with more similar peer groups at other institutions</td></tr>
					<tr><td class="center"><input type="checkbox" name="q6f" <?php echo $q6f == 1 ? "checked": ""; ?>></td><td>f) Improved perception of the department by those outside the university</td></tr>
					<tr><td class="center"><input type="checkbox" name="q6g" <?php echo $q6g == 1 ? "checked": ""; ?>></td><td>g) Improved perception by those outside the department, within the university</td></tr>
					<tr><td class="center"></td><td>Other: <input name="q6_other" id="q6_other" size="40" type="text" value="<?php echo $q6_other; ?>"></input></td></tr>
				</table>
				<br><br>
				
				
				<p>7) What do you see as the dangers of restructuring? (Choose all that apply)</p>
				<table class="v_question">
					<tr><td class="center"><input type="checkbox" name="q7a" <?php echo $q7a == 1 ? "checked": ""; ?>></td><td>a) Fracturing of the department</td></tr>
					<tr><td class="center"><input type="checkbox" name="q7b" <?php echo $q7b == 1 ? "checked": ""; ?>></td><td>b) Loss of math sciences breadth emphasis</td></tr>
					<tr><td class="center"><input type="checkbox" name="q7c" <?php echo $q7c == 1 ? "checked": ""; ?>></td><td>c) Loss of unique identity</td></tr>
					<tr><td class="center"><input type="checkbox" name="q7d" <?php echo $q7d == 1 ? "checked": ""; ?>></td><td>d) Fixing something that is not broken</td></tr>
					<tr><td class="center"></td><td>Other: <input name="q7_other" id="q7_other" size="40" type="text" value="<?php echo $q7_other; ?>"></input></td></tr>
				</table>
				<br><br>
				
				
				<p>Questions 8 through 15 pertain to the possibility of restructuring the department as a School of Mathematical Sciences,
organized in divisions.</p><br>
				
				
				<p>8) Please indicate whether you find the following models for divisions as acceptable or unacceptable:</p>
				
				<table class="h_question">
					<tr><td colspan="3">
						<p style="float:left;">a)</p>
						<ol type="I" style="margin-left:2em;">
							<li>ADM, Analysis, Computational Math</li>
							<li>OR, Math Stat, Applied Stat</li>
							<li>Instruction</li>
						</ol>
					</td></tr>
					<tr>
						<td class="center">Acceptable<br><input type="radio" name="q8a" value="Acceptable" <?php echo $q8a == "Acceptable" ? 'checked' : ""; ?> ></td>
						<td class="center">Unacceptable<br><input type="radio" name="q8a" value="Unacceptable" <?php echo $q8a == "Unacceptable" ? 'checked' : ""; ?> ></td>
						<td class="center">Prefer Not to Respond<br><input type="radio" name="q8a" value="Prefer Not to Respond" <?php echo $q8a == "Prefer Not to Respond" ? 'checked' : ""; ?> ></td>
					</tr>
				</table><br>
				
				
				<table class="h_question">
					<tr><td colspan="3">
						<p style="float:left;">b)</p>
						<ol type="I" style="margin-left:2em;">
							<li>ADM, Analysis</li>
							<li>Computational Math, OR</li>
							<li>Math Stat, Applied Stat</li>
							<li>Instruction</li>
						</ol>
					</td></tr>
					<tr>
						<td class="center">Acceptable<br><input type="radio" name="q8b" value="Acceptable" <?php echo $q8b == "Acceptable" ? 'checked' : ""; ?> ></td>
						<td class="center">Unacceptable<br><input type="radio" name="q8b" value="Unacceptable" <?php echo $q8b == "Unacceptable" ? 'checked' : ""; ?> ></td>
						<td class="center">Prefer Not to Respond<br><input type="radio" name="q8b" value="Prefer Not to Respond" <?php echo $q8b == "Prefer Not to Respond" ? 'checked' : ""; ?> ></td>
					</tr>
				</table><br>
				
				
				<table class="h_question">
					<tr><td colspan="3">
						<p style="float:left;">c)</p>
						<ol type="I" style="margin-left:2em;">
							<li>ADM, Analysis</li>
							<li>Applied Analysis, Computational Math</li>
							<li>OR, Math Stat, Applied Stat</li>
							<li>Instruction</li>
						</ol>
					</td></tr>
					<tr>
						<td class="center">Acceptable<br><input type="radio" name="q8c" value="Acceptable" <?php echo $q8c == "Acceptable" ? 'checked' : ""; ?> ></td>
						<td class="center">Unacceptable<br><input type="radio" name="q8c" value="Unacceptable" <?php echo $q8c == "Unacceptable" ? 'checked' : ""; ?> ></td>
						<td class="center">Prefer Not to Respond<br><input type="radio" name="q8c" value="Prefer Not to Respond" <?php echo $q8c == "Prefer Not to Respond" ? 'checked' : ""; ?> ></td>
					</tr>
				</table><br>
				
				
				<table class="h_question">
					<tr><td colspan="3">
						<p style="float:left;">d)</p>
						<ol type="I" style="margin-left:2em;">
							<li>ADM, Analysis</li>
							<li>Computational Math</li>
							<li>OR, Math Stat, Applied Stat</li>
							<li>Instruction</li>
						</ol>
					</td></tr>
					<tr>
						<td class="center">Acceptable<br><input type="radio" name="q8d" value="Acceptable" <?php echo $q8d == "Acceptable" ? 'checked' : ""; ?> ></td>
						<td class="center">Unacceptable<br><input type="radio" name="q8d" value="Unacceptable" <?php echo $q8d == "Unacceptable" ? 'checked' : ""; ?> ></td>
						<td class="center">Prefer Not to Respond<br><input type="radio" name="q8d" value="Prefer Not to Respond" <?php echo $q8d == "Prefer Not to Respond" ? 'checked' : ""; ?> ></td>
					</tr>
				</table>
				
				<p>Comments (or suggest another model):<br>
					<textarea name="q8_comments" id="q8_comments" cols="80" rows="5"><?php echo $q8_comments; ?></textarea></p><br>
					
				
				<p>9) It is important that the divisions are similar in size.</p>
				<table class="h_question">
					<tr>
						<td class="center">Strongly Agree<br><input type="radio" name="q9" value="Strongly Agree" <?php echo $q9 == "Strongly Agree" ? 'checked' : ""; ?> ></td>
						<td class="center">Agree<br><input type="radio" name="q9" value="Agree" <?php echo $q9 == "Agree" ? 'checked' : ""; ?> ></td>
						<td class="center">Undecided<br><input type="radio" name="q9" value="Undecided" <?php echo $q9 == "Undecided" ? 'checked' : ""; ?> ></td>
						<td class="center">Disagree<br><input type="radio" name="q9" value="Disagree" <?php echo $q9 == "Disagree" ? 'checked' : ""; ?> ></td>
						<td class="center">Strongly Disagree<br><input type="radio" name="q9" value="Strongly Disagree" <?php echo $q9 == "Strongly Disagree" ? 'checked' : ""; ?> ></td>
						<td class="center">Prefer Not to Respond<br><input type="radio" name="q9" value="Prefer Not to Respond" <?php echo $q9 == "Prefer Not to Respond" ? 'checked' : ""; ?> ></td>
					</tr>
				</table>
				
				<p>Comments:<br>
					<textarea name="q9_comments" id="q9_comments" cols="80" rows="5"><?php echo $q9_comments; ?></textarea></p><br>
					
				
				<p>10) There should be a division of instruction.</p>
				<table class="h_question">
					<tr>
						<td class="center">Strongly Agree<br><input type="radio" name="q10" value="Strongly Agree" <?php echo $q10 == "Strongly Agree" ? 'checked' : ""; ?> ></td>
						<td class="center">Agree<br><input type="radio" name="q10" value="Agree" <?php echo $q10 == "Agree" ? 'checked' : ""; ?> ></td>
						<td class="center">Undecided<br><input type="radio" name="q10" value="Undecided" <?php echo $q10 == "Undecided" ? 'checked' : ""; ?> ></td>
						<td class="center">Disagree<br><input type="radio" name="q10" value="Disagree" <?php echo $q10 == "Disagree" ? 'checked' : ""; ?> ></td>
						<td class="center">Strongly Disagree<br><input type="radio" name="q10" value="Strongly Disagree" <?php echo $q10 == "Strongly Disagree" ? 'checked' : ""; ?> ></td>
						<td class="center">Prefer Not to Respond<br><input type="radio" name="q10" value="Prefer Not to Respond" <?php echo $q10 == "Prefer Not to Respond" ? 'checked' : ""; ?> ></td>
					</tr>
				</table>
				
				<p>Comments:<br>
					<textarea name="q10_comments" id="q10_comments" cols="80" rows="5"><?php echo $q10_comments; ?></textarea></p><br>
				
				
				<p>11) Should the lecturers be in a separate division, or should each lecturer choose a primary division with which he/she most closely aligns?</p>
				<table class="v_question">
					<tr><td class="center"><input type="radio" name="q11" value="a" <?php echo $q11 == "a" ? 'checked' : ""; ?> ></td><td>a) Separate division</td></tr>
					<tr><td class="center"><input type="radio" name="q11" value="b" <?php echo $q11 == "b" ? 'checked' : ""; ?> ></td><td>b) Choose which division (a specific research division or instruction division)</td></tr>
					<tr><td class="center"><input type="radio" name="q11" value="c" <?php echo $q11 == "c" ? 'checked' : ""; ?> ></td><td>c) Not sure</td></tr>
					<tr><td class="center"><input type="radio" name="q11" value="d" <?php echo $q11 == "d" ? 'checked' : ""; ?> ></td><td>d) No opinion</td></tr>
					<tr><td class="center"><input type="radio" name="q11" id="11_other" value="other" <?php echo $q11 == "other" ? 'checked' : ""; ?> ></td><td>Other: <input name="q11_other" id="q11_other" size="40" type="text" value="<?php echo $q11_other; ?>"></input></td></tr>
				</table>
				<br><br>
				
				
				<p>12) Should the school have its own TPR committee, or should each division have its own? (Choose one)</p>
				<table class="v_question">
					<tr><td class="center"><input type="radio" name="q12" value="a" <?php echo $q12 == "a" ? 'checked' : ""; ?> ></td><td>a) Separate TPR committee in each division</td></tr>
					<tr><td class="center"><input type="radio" name="q12" value="b" <?php echo $q12 == "b" ? 'checked' : ""; ?> ></td><td>b) Sub-TPR committee per division that reports to main (school) TPR committee</td></tr>
					<tr><td class="center"><input type="radio" name="q12" value="c" <?php echo $q12 == "c" ? 'checked' : ""; ?> ></td><td>c) One TPR committee for school.</td></tr>
					<tr><td class="center"><input type="radio" name="q12" id="12_other" value="other" <?php echo $q12 == "other" ? 'checked' : ""; ?> ></td><td>Other: <input name="q12_other" id="q12_other" size="40" type="text" value="<?php echo $q12_other; ?>"></input></td></tr>
				</table>
				<br><br>
				
				
				<p>13) Divisions should recommend their choice of division head to the school director, who makes the appointment.</p>
				<table class="h_question">
					<tr>
						<td class="center">Strongly Agree<br><input type="radio" name="q13" value="Strongly Agree" <?php echo $q13 == "Strongly Agree" ? 'checked' : ""; ?> ></td>
						<td class="center">Agree<br><input type="radio" name="q13" value="Agree" <?php echo $q13 == "Agree" ? 'checked' : ""; ?> ></td>
						<td class="center">Undecided<br><input type="radio" name="q13" value="Undecided" <?php echo $q13 == "Undecided" ? 'checked' : ""; ?> ></td>
						<td class="center">Disagree<br><input type="radio" name="q13" value="Disagree" <?php echo $q13 == "Disagree" ? 'checked' : ""; ?> ></td>
						<td class="center">Strongly Disagree<br><input type="radio" name="q13" value="Strongly Disagree" <?php echo $q13 == "Strongly Disagree" ? 'checked' : ""; ?> ></td>
						<td class="center">Prefer Not to Respond<br><input type="radio" name="q13" value="Prefer Not to Respond" <?php echo $q13 == "Prefer Not to Respond" ? 'checked' : ""; ?> ></td>
					</tr>
				</table>
				
				<p>Comments:<br>
					<textarea name="q13_comments" id="q13_comments" cols="80" rows="5"><?php echo $q13_comments; ?></textarea></p><br>
				
				
				<p>14) How should membership in a division be determined? </p>
				<table class="v_question">
					<tr><td class="center"><input type="radio" name="q14" value="a" <?php echo $q14 == "a" ? 'checked' : ""; ?> ></td><td>a) By subfaculty</td></tr>
					<tr><td class="center"><input type="radio" name="q14" value="b" <?php echo $q14 == "b" ? 'checked' : ""; ?> ></td><td>b) By choice</td></tr>
					<tr><td class="center"><input type="radio" name="q14" id="14_other" value="other" <?php echo $q14 == "other" ? 'checked' : ""; ?> ></td><td>Other: <input name="q14_other" id="q14_other" size="40" type="text" value="<?php echo $q14_other; ?>"></input></td></tr>
				</table>
				<br><br>
				
				
				<p>15) Faculty should be free to move between divisions (e.g. by switching subfaculty)</p>
				<table class="h_question">
					<tr>
						<td class="center">Strongly Agree<br><input type="radio" name="q15" value="Strongly Agree" <?php echo $q15 == "Strongly Agree" ? 'checked' : ""; ?> ></td>
						<td class="center">Agree<br><input type="radio" name="q15" value="Agree" <?php echo $q15 == "Agree" ? 'checked' : ""; ?> ></td>
						<td class="center">Undecided<br><input type="radio" name="q15" value="Undecided" <?php echo $q15 == "Undecided" ? 'checked' : ""; ?> ></td>
						<td class="center">Disagree<br><input type="radio" name="q15" value="Disagree" <?php echo $q15 == "Disagree" ? 'checked' : ""; ?> ></td>
						<td class="center">Strongly Disagree<br><input type="radio" name="q15" value="Strongly Disagree" <?php echo $q15 == "Strongly Disagree" ? 'checked' : ""; ?> ></td>
						<td class="center">Prefer Not to Respond<br><input type="radio" name="q15" value="Prefer Not to Respond" <?php echo $q15 == "Prefer Not to Respond" ? 'checked' : ""; ?> ></td>
					</tr>
				</table>
				
				<p>Comments:<br>
					<textarea name="q15_comments" id="q15_comments" cols="80" rows="5"><?php echo $q15_comments; ?></textarea></p><br>
					
					
				<p>16) How many associate chairs should there be?</p>
				<table class="h_question">
					<tr>
						<td class="center">One<br><input type="radio" name="q16" value="1" <?php echo $q16 == "1" ? 'checked' : ""; ?> ></td>
						<td class="center">Two<br><input type="radio" name="q16" value="2" <?php echo $q16 == "2" ? 'checked' : ""; ?> ></td>
						<td class="center">Three<br><input type="radio" name="q16" value="3" <?php echo $q16 == "3" ? 'checked' : ""; ?> ></td>
						<td class="center">Four<br><input type="radio" name="q16" value="4" <?php echo $q16 == "4" ? 'checked' : ""; ?> ></td>
						<td class="center">Undecided<br><input type="radio" name="q16" value="Undecided" <?php echo $q16 == "Undecided" ? 'checked' : ""; ?> ></td>
					</tr>
				</table>
				
				<p>Comments:<br>
					<textarea name="q16_comments" id="q16_comments" cols="80" rows="5"><?php echo $q16_comments; ?></textarea></p><br>
				
				
				<p>17) What should be the role of an associate chair?  (Choose all that apply)</p>
				<table class="v_question">
					<tr><td class="center"><input type="checkbox" name="q17a" <?php echo $q17a == 1 ? "checked": ""; ?>></td><td>a) Workload distribution</td></tr>
					<tr><td class="center"><input type="checkbox" name="q17b" <?php echo $q17b == 1 ? "checked": ""; ?>></td><td>b) Evaluation</td></tr>
					<tr><td class="center"><input type="checkbox" name="q17c" <?php echo $q17c == 1 ? "checked": ""; ?>></td><td>c) Helping to determine hiring priorities</td></tr>
					<tr><td class="center"><input type="checkbox" name="q17d" <?php echo $q17d == 1 ? "checked": ""; ?>></td><td>d) Research leadership</td></tr>
					<tr><td class="center"><input type="checkbox" name="q17e" <?php echo $q17e == 1 ? "checked": ""; ?>></td><td>e) Budget input (e.g. travel, colloquia & seminars)</td></tr>
					<tr><td class="center"></td><td>Other: <input name="q17_other" id="q17_other" size="40" type="text" value="<?php echo $q17_other; ?>"></input></td></tr>
				</table>
				<br><br>
				
				
				<p>18) The department should have a review conducted by an outside panel</p>
				<table class="h_question">
					<tr>
						<td class="center">Strongly Agree<br><input type="radio" name="q18" value="Strongly Agree" <?php echo $q18 == "Strongly Agree" ? 'checked' : ""; ?> ></td>
						<td class="center">Agree<br><input type="radio" name="q18" value="Agree" <?php echo $q18 == "Agree" ? 'checked' : ""; ?> ></td>
						<td class="center">Undecided<br><input type="radio" name="q18" value="Undecided" <?php echo $q18 == "Undecided" ? 'checked' : ""; ?> ></td>
						<td class="center">Disagree<br><input type="radio" name="q18" value="Disagree" <?php echo $q18 == "Disagree" ? 'checked' : ""; ?> ></td>
						<td class="center">Strongly Disagree<br><input type="radio" name="q18" value="Strongly Disagree" <?php echo $q18 == "Strongly Disagree" ? 'checked' : ""; ?> ></td>
						<td class="center">Prefer Not to Respond<br><input type="radio" name="q18" value="Prefer Not to Respond" <?php echo $q18 == "Prefer Not to Respond" ? 'checked' : ""; ?> ></td>
					</tr>
				</table>
				
				<p>Comments:<br>
					<textarea name="q18_comments" id="q18_comments" cols="80" rows="5"><?php echo $q18_comments; ?></textarea></p><br>
				
				
					
				<p>19) If you are in favor of an outside review and you would like to suggest one or more people to serve on a review panel, please list names and affiliations here.<br>
					<textarea name="q19" id="q19" cols="80" rows="5"><?php echo $q19; ?></textarea></p><br>
				
				
				
				<p>20) The department should have an (ongoing) outside advisory panel</p>
				<table class="h_question">
					<tr>
						<td class="center">Strongly Agree<br><input type="radio" name="q20" value="Strongly Agree" <?php echo $q20 == "Strongly Agree" ? 'checked' : ""; ?> ></td>
						<td class="center">Agree<br><input type="radio" name="q20" value="Agree" <?php echo $q20 == "Agree" ? 'checked' : ""; ?> ></td>
						<td class="center">Undecided<br><input type="radio" name="q20" value="Undecided" <?php echo $q20 == "Undecided" ? 'checked' : ""; ?> ></td>
						<td class="center">Disagree<br><input type="radio" name="q20" value="Disagree" <?php echo $q20 == "Disagree" ? 'checked' : ""; ?> ></td>
						<td class="center">Strongly Disagree<br><input type="radio" name="q20" value="Strongly Disagree" <?php echo $q20 == "Strongly Disagree" ? 'checked' : ""; ?> ></td>
						<td class="center">Prefer Not to Respond<br><input type="radio" name="q20" value="Prefer Not to Respond" <?php echo $q20 == "Prefer Not to Respond" ? 'checked' : ""; ?> ></td>
					</tr>
				</table>
				
				<p>Comments:<br>
					<textarea name="q20_comments" id="q20_comments" cols="80" rows="5"><?php echo $q20_comments; ?></textarea></p><br>
					
					
				
				<p>21) If you are in favor of an outside advisory panel and you would like to suggest one or more people to serve on an advisory panel, please list names and affiliations here.<br>
					<textarea name="q21" id="q21" cols="80" rows="5"><?php echo $q21; ?></textarea></p><br>
				
				
				
				<p>22) If you do not favor reorganization but see problems that need to be remedied, please suggest approaches that would be helpful.<br>
					<textarea name="q22" id="q22" cols="80" rows="5"><?php echo $q22; ?></textarea></p><br>
					
					
					
				<p>23) Are there any other comments that you would like to add?<br>
					<textarea name="q23" id="q23" cols="80" rows="5"><?php echo $q23; ?></textarea></p><br>

			</form>

		</div>
	</div>


</body>
</html>

