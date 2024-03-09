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
}


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Departmental Restructuring Survey Responses</title>
	<meta name="author" content="hedetni">
	<!-- Date: 2016-07-06 -->
	
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
table.comment_table {
	margin-top:0.75em;
	width:75%;
}
button {
	font-size:1em;
}
table.comment_table th {
	text-align:left;
}
</style>

<script type="text/javascript">
$(document).ready(function() 
{
	$('table.comment_table').find('tr:gt(0)').hide();
	
	$('button.expander').click(function(){
		if ($(this).html() == "Hide")
		{
			$(this).parent().parent().parent().find('tr:gt(0)').hide();
			$(this).html("Expand");
		}
		else if ($(this).html() == "Expand")
		{
			$(this).parent().parent().parent().find('tr').show();
			$(this).html("Hide");
		}
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
		</div>

		<div id="content">
			<h1>Departmental Restructuring Survey Responses</h1>
			

			<?php echo isset($message) ? '<p id="error">'.$message.'</p>' : ""; ?>


			<?php 
				$getQ1 = mysql_query('SELECT q1,count(q1) as count FROM `reorg_survey` group by q1');
				$q1 = array();
				while ($row = mysql_fetch_array($getQ1))
				{
					$option = $row['q1'];
					$q1[$option] = $row['count'];
				}
			?>
			<p>1) Your Rank:</p>
			<table class="h_question">
				<tr>
					<td class="center">Lecturer<br><?php echo isset($q1['Lecturer']) ? $q1['Lecturer'] : "0"; ?></td>
					<td class="center">Senior Lecturer<br><?php echo isset($q1['Senior Lecturer']) ? $q1['Senior Lecturer'] : "0"; ?></td>
					<td class="center">Assistant Professor<br><?php echo isset($q1['Assistant Professor']) ? $q1['Assistant Professor'] : "0"; ?></td>
					<td class="center">Associate Professor<br><?php echo isset($q1['Associate Professor']) ? $q1['Associate Professor'] : "0"; ?></td>
					<td class="center">Full Professor<br><?php echo isset($q1['Full Professor']) ? $q1['Full Professor'] : "0"; ?></td>
					<td class="center">Wish not to specify<br><?php echo isset($q1['Wish not to specify']) ? $q1['Wish not to specify'] : "0"; ?></td>
				</tr>
			</table><br><br>
			
			
			<?php 
				$getQ2 = mysql_query('SELECT q2,count(q2) as count FROM `reorg_survey` group by q2');
				$q2 = array();
				while ($row = mysql_fetch_array($getQ2))
				{
					$option = $row['q2'];
					$q2[$option] = $row['count'];
				}
			?>
			<p>2) Subfaculty with which you most closely identify:</p>
			<table class="h_question">
				<tr>
					<td class="center">ADM<br><?php echo isset($q2['ADM']) ? $q2['ADM'] : "0"; ?></td>
					<td class="center">Analysis<br><?php echo isset($q2['Analysis']) ? $q2['Analysis'] : "0"; ?></td>
					<td class="center">Applied Stat<br><?php echo isset($q2['Applied Stat']) ? $q2['Applied Stat'] : "0"; ?></td>
					<td class="center">Comp. Math<br><?php echo isset($q2['Comp Math']) ? $q2['Comp Math'] : "0"; ?></td>
					<td class="center">Educ.<br><?php echo isset($q2['Educ']) ? $q2['Educ'] : "0"; ?></td>
					<td class="center">Math Stat<br><?php echo isset($q2['Math Stat']) ? $q2['Math Stat'] : "0"; ?></td>
					<td class="center">OR<br><?php echo isset($q2['OR']) ? $q2['OR'] : "0"; ?></td>
					<td class="center">Wish not to specify<br><?php echo isset($q2['Wish not to specify']) ? $q2['Wish not to specify'] : "0"; ?></td>
				</tr>
			</table><br><br>
			
			
			<?php 
				$getQ3 = mysql_query('SELECT q3,count(q3) as count FROM `reorg_survey` group by q3');
				$q3 = array();
				while ($row = mysql_fetch_array($getQ3))
				{
					$option = $row['q3'];
					$q3[$option] = $row['count'];
				}
			?>
			<p>This mission statement is taken from the Department website: The mission of the Department of Mathematical Sciences is to create and discover new knowledge in the mathematical sciences, to disseminate new and existing knowledge in the mathematical sciences, and to apply new and existing knowledge in the mathematical sciences to benefit the economic future of the state and nation.</p>

			<p>3) Do you agree with the department mission statement?</p>
			<table class="h_question">
				<tr>
					<td class="center">Strongly Agree<br><?php echo isset($q3['Strongly Agree']) ? $q3['Strongly Agree'] : "0"; ?></td>
					<td class="center">Agree<br><?php echo isset($q3['Agree']) ? $q3['Agree'] : "0"; ?></td>
					<td class="center">Undecided<br><?php echo isset($q3['Undecided']) ? $q3['Undecided'] : "0"; ?></td>
					<td class="center">Disagree<br><?php echo isset($q3['Disagree']) ? $q3['Disagree'] : "0"; ?></td>
					<td class="center">Strongly Disagree<br><?php echo isset($q3['Strongly Disagree']) ? $q3['Strongly Disagree'] : "0"; ?></td>
					<td class="center">Prefer Not to Respond<br><?php echo isset($q3['Prefer Not to Respond']) ? $q3['Prefer Not to Respond'] : "0"; ?></td>
				</tr>
			</table>
			
			<?php 
				$getQ3_comments = mysql_query('SELECT q3_comments FROM `reorg_survey` WHERE q3_comments != "";');
				$q3_comments = array();
				while ($row = mysql_fetch_array($getQ3_comments))
				{
					$q3_comments[] = $row['q3_comments'];
				}
			?>
			<table class="comment_table">
				<tr><th>Comments <button type="button" class="expander">Expand</button></th></tr>
				<?php foreach ($q3_comments as $comment): ?>
					<tr><td><?php echo $comment; ?></td></tr>
				<?php endforeach; ?>
			</table><br><br>
			
			
			<?php 
				$getQ4 = mysql_query('SELECT q4,count(q4) as count FROM `reorg_survey` group by q4');
				$q4 = array();
				while ($row = mysql_fetch_array($getQ4))
				{
					$option = $row['q4'];
					$q4[$option] = $row['count'];
				}
			?>
			<p>This is a vision statement from a 2002 CHE self-study: The Department of Mathematical Sciences will progress in step with Clemson University toward the goal of being ranked among the Top 20 programs at public research universities. The Department will be recognized by Clemson University as a multi-disciplinary department offering degree programs of the highest quality, and for its collaborative research with individuals, departments and centers across the campus. The graduate program will be recognized nationally for its efforts to prepare mathematical scientists for academic and nonacademic employment, and for the high quality of the disciplinary and interdisciplinary research by its faculty and students. The undergraduate program will provide a solid foundation for careers requiring intensive logical and quantitative skills and will attract students with superior mathematics background. The general education service courses will prepare undergraduate students with the basic quantitative tools and critical thinking skills for success in their respective degree programs.</p>
			
			<p>4) Do you agree with this vision statement?</p>
			<table class="h_question">
				<tr>
					<td class="center">Strongly Agree<br><?php echo isset($q4['Strongly Agree']) ? $q4['Strongly Agree'] : "0"; ?></td>
					<td class="center">Agree<br><?php echo isset($q4['Agree']) ? $q4['Agree'] : "0"; ?></td>
					<td class="center">Undecided<br><?php echo isset($q4['Undecided']) ? $q4['Undecided'] : "0"; ?></td>
					<td class="center">Disagree<br><?php echo isset($q4['Disagree']) ? $q4['Disagree'] : "0"; ?></td>
					<td class="center">Strongly Disagree<br><?php echo isset($q4['Strongly Disagree']) ? $q4['Strongly Disagree'] : "0"; ?></td>
					<td class="center">Prefer Not to Respond<br><?php echo isset($q4['Prefer Not to Respond']) ? $q4['Prefer Not to Respond'] : "0"; ?></td>
				</tr>
			</table>
			
			<?php 
				$getQ4_comments = mysql_query('SELECT q4_comments FROM `reorg_survey` WHERE q4_comments != "";');
				$q4_comments = array();
				while ($row = mysql_fetch_array($getQ4_comments))
				{
					$q4_comments[] = $row['q4_comments'];
				}
			?>
			<table class="comment_table">
				<tr><th>Comments <button type="button" class="expander">Expand</button></th></tr>
				<?php foreach ($q4_comments as $comment): ?>
					<tr><td><?php echo $comment; ?></td></tr>
				<?php endforeach; ?>
			</table><br><br>
			
			
			<?php 
				$getQ5 = mysql_query('SELECT SUM(q5a) as q5aSUM, SUM(q5b) as q5bSUM, SUM(q5c) as q5cSUM, SUM(q5d) as q5dSUM, SUM(q5e) as q5eSUM, SUM(q5f) as q5fSUM, SUM(q5g) as q5gSUM  FROM `reorg_survey`');
				$row = mysql_fetch_array($getQ5);
				$q5a = $row['q5aSUM'];
				$q5b = $row['q5bSUM'];
				$q5c = $row['q5cSUM'];
				$q5d = $row['q5dSUM'];
				$q5e = $row['q5eSUM'];
				$q5f = $row['q5fSUM'];
				$q5g = $row['q5gSUM'];
				
			?>
			<?php 
				$getQ5_other = mysql_query('SELECT q5_other FROM `reorg_survey` WHERE q5_other != "";');
				$q5_other = array();
				while ($row = mysql_fetch_array($getQ5_other))
				{
					$q5_other[] = $row['q5_other'];
				}
			?>
			<p>5) What do you see as the biggest challenges for our department? (Choose all that apply)</p>
			<table class="v_question">
				<tr><td>Count</td><td>Option</td>
				<tr><td class="center"><?php echo $q5a ?></td><td>a) Workload distribution</td></tr>
				<tr><td class="center"><?php echo $q5b ?></td><td>b) Evaluation</td></tr>
				<tr><td class="center"><?php echo $q5c ?></td><td>c) Hiring</td></tr>
				<tr><td class="center"><?php echo $q5d ?></td><td>d) TPR</td></tr>
				<tr><td class="center"><?php echo $q5e ?></td><td>e) Difficulty in establishing peer groups at other institutions</td></tr>
				<tr><td class="center"><?php echo $q5f ?></td><td>f) Perception of the department by those outside the university</td></tr>
				<tr><td class="center"><?php echo $q5g ?></td><td>g) Perception by those outside the department, within the university</td></tr>
				<tr><td class="center">Other:</td><td>
					<table class="other_table">
						<?php foreach ($q5_other as $other): ?>
							<tr><td><?php echo $other; ?></td></tr>
						<?php endforeach; ?>
					</table>
				</td></tr>
			</table>
			<br><br>
			
			
			<?php 
				$getQ6 = mysql_query('SELECT SUM(q6a) as q6aSUM, SUM(q6b) as q6bSUM, SUM(q6c) as q6cSUM, SUM(q6d) as q6dSUM, SUM(q6e) as q6eSUM, SUM(q6f) as q6fSUM, SUM(q6g) as q6gSUM  FROM `reorg_survey`');
				$row = mysql_fetch_array($getQ6);
				$q6a = $row['q6aSUM'];
				$q6b = $row['q6bSUM'];
				$q6c = $row['q6cSUM'];
				$q6d = $row['q6dSUM'];
				$q6e = $row['q6eSUM'];
				$q6f = $row['q6fSUM'];
				$q6g = $row['q6gSUM'];
				
			?>
			<?php 
				$getQ6_other = mysql_query('SELECT q6_other FROM `reorg_survey` WHERE q6_other != "";');
				$q6_other = array();
				while ($row = mysql_fetch_array($getQ6_other))
				{
					$q6_other[] = $row['q6_other'];
				}
			?>
			<p>6) What do you see as the potential benefits of department restructuring? (Choose all that apply)</p>
			<table class="v_question">
				<tr><td>Count</td><td>Option</td>
				<tr><td class="center"><?php echo $q6a ?></td><td>a) More equitable workload distribution</td></tr>
				<tr><td class="center"><?php echo $q6b ?></td><td>b) Evaluation by someone more familiar with your field of expertise</td></tr>
				<tr><td class="center"><?php echo $q6c ?></td><td>c) Better hiring in areas of critical need</td></tr>
				<tr><td class="center"><?php echo $q6d ?></td><td>d) Improved TPR process</td></tr>
				<tr><td class="center"><?php echo $q6e ?></td><td>e) Identification with more similar peer groups at other institutions</td></tr>
				<tr><td class="center"><?php echo $q6f ?></td><td>f) Improved perception of the department by those outside the university</td></tr>
				<tr><td class="center"><?php echo $q6g ?></td><td>g) Improved perception by those outside the department, within the university</td></tr>
				<tr><td class="center">Other:</td><td>
					<table class="other_table">
						<?php foreach ($q6_other as $other): ?>
							<tr><td><?php echo $other; ?></td></tr>
						<?php endforeach; ?>
					</table>
				</td></tr>
			</table>
			<br><br>
			
			
			<?php 
				$getQ7 = mysql_query('SELECT SUM(q7a) as q7aSUM, SUM(q7b) as q7bSUM, SUM(q7c) as q7cSUM, SUM(q7d) as q7dSUM  FROM `reorg_survey`');
				$row = mysql_fetch_array($getQ7);
				$q7a = $row['q7aSUM'];
				$q7b = $row['q7bSUM'];
				$q7c = $row['q7cSUM'];
				$q7d = $row['q7dSUM'];
				
			?>
			<?php 
				$getQ7_other = mysql_query('SELECT q7_other FROM `reorg_survey` WHERE q7_other != "";');
				$q7_other = array();
				while ($row = mysql_fetch_array($getQ7_other))
				{
					$q7_other[] = $row['q7_other'];
				}
			?>
			<p>7) What do you see as the dangers of restructuring? (Choose all that apply)</p>
			<table class="v_question">
				<tr><td>Count</td><td>Option</td>
				<tr><td class="center"><?php echo $q7a ?></td><td>a) Fracturing of the department</td></tr>
				<tr><td class="center"><?php echo $q7b ?></td><td>b) Loss of math sciences breadth emphasis</td></tr>
				<tr><td class="center"><?php echo $q7c ?></td><td>c) Loss of unique identity</td></tr>
				<tr><td class="center"><?php echo $q7d ?></td><td>d) Fixing something that is not broken</td></tr>
				<tr><td class="center">Other:</td><td>
					<table class="other_table">
						<?php foreach ($q7_other as $other): ?>
							<tr><td><?php echo $other; ?></td></tr>
						<?php endforeach; ?>
					</table>
				</td></tr>
			</table>
			<br><br>
			
			
			<p>Questions 8 through 15 pertain to the possibility of restructuring the department as a School of Mathematical Sciences,
organized in divisions.</p><br>
			
			
			<p>8) Please indicate whether you find the following models for divisions as acceptable or unacceptable:</p>
			
			
			<?php 
				$getQ8a = mysql_query('SELECT q8a,count(q8a) as count FROM `reorg_survey` group by q8a');
				$q8a = array();
				while ($row = mysql_fetch_array($getQ8a))
				{
					$option = $row['q8a'];
					$q8a[$option] = $row['count'];
				}
			?>
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
					<td class="center">Acceptable<br><?php echo isset($q8a['Acceptable']) ? $q8a['Acceptable'] : "0"; ?></td>
					<td class="center">Unacceptable<br><?php echo isset($q8a['Unacceptable']) ? $q8a['Unacceptable'] : "0"; ?></td>
					<td class="center">Prefer Not to Respond<br><?php echo isset($q8a['Prefer Not to Respond']) ? $q8a['Prefer Not to Respond'] : "0"; ?></td>
				</tr>
			</table><br>
			
			
			<?php 
				$getQ8b = mysql_query('SELECT q8b,count(q8b) as count FROM `reorg_survey` group by q8b');
				$q8b = array();
				while ($row = mysql_fetch_array($getQ8b))
				{
					$option = $row['q8b'];
					$q8b[$option] = $row['count'];
				}
			?>
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
					<td class="center">Acceptable<br><?php echo isset($q8b['Acceptable']) ? $q8b['Acceptable'] : "0"; ?></td>
					<td class="center">Unacceptable<br><?php echo isset($q8b['Unacceptable']) ? $q8b['Unacceptable'] : "0"; ?></td>
					<td class="center">Prefer Not to Respond<br><?php echo isset($q8b['Prefer Not to Respond']) ? $q8b['Prefer Not to Respond'] : "0"; ?></td>
				</tr>
			</table><br>
			
			
			<?php 
				$getQ8c = mysql_query('SELECT q8c,count(q8c) as count FROM `reorg_survey` group by q8c');
				$q8c = array();
				while ($row = mysql_fetch_array($getQ8c))
				{
					$option = $row['q8c'];
					$q8c[$option] = $row['count'];
				}
			?>
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
					<td class="center">Acceptable<br><?php echo isset($q8c['Acceptable']) ? $q8c['Acceptable'] : "0"; ?></td>
					<td class="center">Unacceptable<br><?php echo isset($q8c['Unacceptable']) ? $q8c['Unacceptable'] : "0"; ?></td>
					<td class="center">Prefer Not to Respond<br><?php echo isset($q8c['Prefer Not to Respond']) ? $q8c['Prefer Not to Respond'] : "0"; ?></td>
				</tr>
			</table><br>
			
			
			<?php 
				$getQ8d = mysql_query('SELECT q8d,count(q8d) as count FROM `reorg_survey` group by q8d');
				$q8d = array();
				while ($row = mysql_fetch_array($getQ8d))
				{
					$option = $row['q8d'];
					$q8d[$option] = $row['count'];
				}
			?>
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
					<td class="center">Acceptable<br><?php echo isset($q8d['Acceptable']) ? $q8d['Acceptable'] : "0"; ?></td>
					<td class="center">Unacceptable<br><?php echo isset($q8d['Unacceptable']) ? $q8d['Unacceptable'] : "0"; ?></td>
					<td class="center">Prefer Not to Respond<br><?php echo isset($q8d['Prefer Not to Respond']) ? $q8d['Prefer Not to Respond'] : "0"; ?></td>
				</tr>
			</table>
			

			<?php 
				$getQ8_comments = mysql_query('SELECT q8_comments FROM `reorg_survey` WHERE q8_comments != "";');
				$q8_comments = array();
				while ($row = mysql_fetch_array($getQ8_comments))
				{
					$q8_comments[] = $row['q8_comments'];
				}
			?>
			<table class="comment_table">
				<tr><th>Comments (or suggest another model) <button type="button" class="expander">Expand</button></th></tr>
				<?php foreach ($q8_comments as $comment): ?>
					<tr><td><?php echo $comment; ?></td></tr>
				<?php endforeach; ?>
			</table><br><br>
				
			
			<?php 
				$getQ9 = mysql_query('SELECT q9,count(q9) as count FROM `reorg_survey` group by q9');
				$q9 = array();
				while ($row = mysql_fetch_array($getQ9))
				{
					$option = $row['q9'];
					$q9[$option] = $row['count'];
				}
			?>
			<p>9) It is important that the divisions are similar in size.</p>
			<table class="h_question">
				<tr>
					<td class="center">Strongly Agree<br><?php echo isset($q9['Strongly Agree']) ? $q9['Strongly Agree'] : "0"; ?></td>
					<td class="center">Agree<br><?php echo isset($q9['Agree']) ? $q9['Agree'] : "0"; ?></td>
					<td class="center">Undecided<br><?php echo isset($q9['Undecided']) ? $q9['Undecided'] : "0"; ?></td>
					<td class="center">Disagree<br><?php echo isset($q9['Disagree']) ? $q9['Disagree'] : "0"; ?></td>
					<td class="center">Strongly Disagree<br><?php echo isset($q9['Strongly Disagree']) ? $q9['Strongly Disagree'] : "0"; ?></td>
					<td class="center">Prefer Not to Respond<br><?php echo isset($q9['Prefer Not to Respond']) ? $q9['Prefer Not to Respond'] : "0"; ?></td>
				</tr>
			</table>
			
			<?php 
				$getQ9_comments = mysql_query('SELECT q9_comments FROM `reorg_survey` WHERE q9_comments != "";');
				$q9_comments = array();
				while ($row = mysql_fetch_array($getQ9_comments))
				{
					$q9_comments[] = $row['q9_comments'];
				}
			?>
			<table class="comment_table">
				<tr><th>Comments <button type="button" class="expander">Expand</button></th></tr>
				<?php foreach ($q9_comments as $comment): ?>
					<tr><td><?php echo $comment; ?></td></tr>
				<?php endforeach; ?>
			</table><br><br>
				
			
			<?php 
				$getQ10 = mysql_query('SELECT q10,count(q10) as count FROM `reorg_survey` group by q10');
				$q10 = array();
				while ($row = mysql_fetch_array($getQ10))
				{
					$option = $row['q10'];
					$q10[$option] = $row['count'];
				}
			?>
			<p>10) There should be a division of instruction.</p>
			<table class="h_question">
				<tr>
					<td class="center">Strongly Agree<br><?php echo isset($q10['Strongly Agree']) ? $q10['Strongly Agree'] : "0"; ?></td>
					<td class="center">Agree<br><?php echo isset($q10['Agree']) ? $q10['Agree'] : "0"; ?></td>
					<td class="center">Undecided<br><?php echo isset($q10['Undecided']) ? $q10['Undecided'] : "0"; ?></td>
					<td class="center">Disagree<br><?php echo isset($q10['Disagree']) ? $q10['Disagree'] : "0"; ?></td>
					<td class="center">Strongly Disagree<br><?php echo isset($q10['Strongly Disagree']) ? $q10['Strongly Disagree'] : "0"; ?></td>
					<td class="center">Prefer Not to Respond<br><?php echo isset($q10['Prefer Not to Respond']) ? $q10['Prefer Not to Respond'] : "0"; ?></td>
				</tr>
			</table>
			
			<?php 
				$getQ10_comments = mysql_query('SELECT q10_comments FROM `reorg_survey` WHERE q10_comments != "";');
				$q10_comments = array();
				while ($row = mysql_fetch_array($getQ10_comments))
				{
					$q10_comments[] = $row['q10_comments'];
				}
			?>
			<table class="comment_table">
				<tr><th>Comments <button type="button" class="expander">Expand</button></th></tr>
				<?php foreach ($q10_comments as $comment): ?>
					<tr><td><?php echo $comment; ?></td></tr>
				<?php endforeach; ?>
			</table><br><br>
			
			
			<?php 
				$getQ11 = mysql_query('SELECT q11,count(q11) as count FROM `reorg_survey` group by q11');
				$q11 = array();
				while ($row = mysql_fetch_array($getQ11))
				{
					$option = $row['q11'];
					$q11[$option] = $row['count'];
				}
			?>
			<?php 
				$getQ11_other = mysql_query('SELECT q11_other FROM `reorg_survey` WHERE q11_other != "";');
				$q11_other = array();
				while ($row = mysql_fetch_array($getQ11_other))
				{
					$q11_other[] = $row['q11_other'];
				}
			?>
			<p>11) Should the lecturers be in a separate division, or should each lecturer choose a primary division with which he/she most closely aligns?</p>
			<table class="v_question">
				<tr><td class="center"><?php echo isset($q11['a']) ? $q11['a'] : "0"; ?></td><td>a) Separate division</td></tr>
				<tr><td class="center"><?php echo isset($q11['b']) ? $q11['b'] : "0"; ?></td><td>b) Choose which division (a specific research division or instruction division)</td></tr>
				<tr><td class="center"><?php echo isset($q11['c']) ? $q11['c'] : "0"; ?></td><td>c) Not sure</td></tr>
				<tr><td class="center"><?php echo isset($q11['d']) ? $q11['d'] : "0"; ?></td><td>d) No opinion</td></tr>
				<tr><td class="center"><?php echo isset($q11['other']) ? $q11['other'] : "0"; ?></td>
					<td>
						<table class="other_table">
							<?php foreach ($q11_other as $other): ?>
								<tr><td><?php echo $other; ?></td></tr>
							<?php endforeach; ?>
						</table>
					</td></tr>
			</table>
			<br><br>
			
			
			<?php 
				$getQ12 = mysql_query('SELECT q12,count(q12) as count FROM `reorg_survey` group by q12');
				$q12 = array();
				while ($row = mysql_fetch_array($getQ12))
				{
					$option = $row['q12'];
					$q12[$option] = $row['count'];
				}
			?>
			<?php 
				$getQ12_other = mysql_query('SELECT q12_other FROM `reorg_survey` WHERE q12_other != "";');
				$q12_other = array();
				while ($row = mysql_fetch_array($getQ12_other))
				{
					$q12_other[] = $row['q12_other'];
				}
			?>
			<p>12) Should the school have its own TPR committee, or should each division have its own? (Choose one)</p>
			<table class="v_question">
				<tr><td class="center"><?php echo isset($q12['a']) ? $q12['a'] : "0"; ?></td><td>a) Separate TPR committee in each division</td></tr>
				<tr><td class="center"><?php echo isset($q12['b']) ? $q12['b'] : "0"; ?></td><td>b) Sub-TPR committee per division that reports to main (school) TPR committee</td></tr>
				<tr><td class="center"><?php echo isset($q12['c']) ? $q12['c'] : "0"; ?></td><td>c) One TPR committee for school.</td></tr>
				<tr><td class="center"><?php echo isset($q12['other']) ? $q12['other'] : "0"; ?></td>
					<td>
						<table class="other_table">
							<?php foreach ($q11_other as $other): ?>
								<tr><td><?php echo $other; ?></td></tr>
							<?php endforeach; ?>
						</table>
					</td></tr>
			</table>
			<br><br>
			
			
			<?php 
				$getQ13 = mysql_query('SELECT q13,count(q13) as count FROM `reorg_survey` group by q13');
				$q13 = array();
				while ($row = mysql_fetch_array($getQ13))
				{
					$option = $row['q13'];
					$q13[$option] = $row['count'];
				}
			?>
			<p>13) Divisions should recommend their choice of division head to the school director, who makes the appointment.</p>
			<table class="h_question">
				<tr>
					<td class="center">Strongly Agree<br><?php echo isset($q13['Strongly Agree']) ? $q13['Strongly Agree'] : "0"; ?></td>
					<td class="center">Agree<br><?php echo isset($q13['Agree']) ? $q13['Agree'] : "0"; ?></td>
					<td class="center">Undecided<br><?php echo isset($q13['Undecided']) ? $q13['Undecided'] : "0"; ?></td>
					<td class="center">Disagree<br><?php echo isset($q13['Disagree']) ? $q13['Disagree'] : "0"; ?></td>
					<td class="center">Strongly Disagree<br><?php echo isset($q13['Strongly Disagree']) ? $q13['Strongly Disagree'] : "0"; ?></td>
					<td class="center">Prefer Not to Respond<br><?php echo isset($q13['Prefer Not to Respond']) ? $q13['Prefer Not to Respond'] : "0"; ?></td>
				</tr>
			</table>
			
			<?php 
				$getQ13_comments = mysql_query('SELECT q13_comments FROM `reorg_survey` WHERE q13_comments != "";');
				$q13_comments = array();
				while ($row = mysql_fetch_array($getQ13_comments))
				{
					$q13_comments[] = $row['q13_comments'];
				}
			?>
			<table class="comment_table">
				<tr><th>Comments <button type="button" class="expander">Expand</button></th></tr>
				<?php foreach ($q13_comments as $comment): ?>
					<tr><td><?php echo $comment; ?></td></tr>
				<?php endforeach; ?>
			</table><br><br>
			
			
			<?php 
				$getQ14 = mysql_query('SELECT q14,count(q14) as count FROM `reorg_survey` group by q14');
				$q14 = array();
				while ($row = mysql_fetch_array($getQ14))
				{
					$option = $row['q14'];
					$q14[$option] = $row['count'];
				}
			?>
			<?php 
				$getQ14_other = mysql_query('SELECT q14_other FROM `reorg_survey` WHERE q14_other != "";');
				$q14_other = array();
				while ($row = mysql_fetch_array($getQ14_other))
				{
					$q14_other[] = $row['q14_other'];
				}
			?>
			<p>14) How should membership in a division be determined? </p>
			<table class="v_question">
				<tr><td class="center"><?php echo isset($q14['a']) ? $q14['a'] : "0"; ?></td><td>a) By subfaculty</td></tr>
				<tr><td class="center"><?php echo isset($q14['b']) ? $q14['b'] : "0"; ?></td><td>b) By choice</td></tr>
				<tr><td class="center"><?php echo isset($q14['other']) ? $q14['other'] : "0"; ?></td>
					<td>
						<table class="other_table">
							<?php foreach ($q14_other as $other): ?>
								<tr><td><?php echo $other; ?></td></tr>
							<?php endforeach; ?>
						</table>
					</td></tr>
			</table>
			<br><br>
			
			
			<?php 
				$getQ15 = mysql_query('SELECT q15,count(q15) as count FROM `reorg_survey` group by q15');
				$q15 = array();
				while ($row = mysql_fetch_array($getQ15))
				{
					$option = $row['q15'];
					$q15[$option] = $row['count'];
				}
			?>
			<p>15) Faculty should be free to move between divisions (e.g. by switching subfaculty)</p>
			<table class="h_question">
				<tr>
					<td class="center">Strongly Agree<br><?php echo isset($q15['Strongly Agree']) ? $q15['Strongly Agree'] : "0"; ?></td>
					<td class="center">Agree<br><?php echo isset($q15['Agree']) ? $q15['Agree'] : "0"; ?></td>
					<td class="center">Undecided<br><?php echo isset($q15['Undecided']) ? $q15['Undecided'] : "0"; ?></td>
					<td class="center">Disagree<br><?php echo isset($q15['Disagree']) ? $q15['Disagree'] : "0"; ?></td>
					<td class="center">Strongly Disagree<br><?php echo isset($q15['Strongly Disagree']) ? $q15['Strongly Disagree'] : "0"; ?></td>
					<td class="center">Prefer Not to Respond<br><?php echo isset($q15['Prefer Not to Respond']) ? $q15['Prefer Not to Respond'] : "0"; ?></td>
				</tr>
			</table>
			
			<?php 
				$getQ15_comments = mysql_query('SELECT q15_comments FROM `reorg_survey` WHERE q15_comments != "";');
				$q15_comments = array();
				while ($row = mysql_fetch_array($getQ15_comments))
				{
					$q15_comments[] = $row['q15_comments'];
				}
			?>
			<table class="comment_table">
				<tr><th>Comments <button type="button" class="expander">Expand</button></th></tr>
				<?php foreach ($q15_comments as $comment): ?>
					<tr><td><?php echo $comment; ?></td></tr>
				<?php endforeach; ?>
			</table><br><br>
				
			
			<?php 
				$getQ16 = mysql_query('SELECT q16,count(q16) as count FROM `reorg_survey` group by q16');
				$q16 = array();
				while ($row = mysql_fetch_array($getQ16))
				{
					$option = $row['q16'];
					$q16[$option] = $row['count'];
				}
			?>	
			<p>16) How many associate chairs should there be?</p>
			<table class="h_question">
				<tr>
					<td class="center">One<br><?php echo isset($q16['1']) ? $q16['1'] : "0"; ?></td>
					<td class="center">Two<br><?php echo isset($q16['2']) ? $q16['2'] : "0"; ?></td>
					<td class="center">Three<br><?php echo isset($q16['3']) ? $q16['3'] : "0"; ?></td>
					<td class="center">Four<br><?php echo isset($q16['4']) ? $q16['4'] : "0"; ?></td>
					<td class="center">Undecided<br><?php echo isset($q16['Undecided']) ? $q16['Undecided'] : "0"; ?></td>
				</tr>
			</table>
			
			<?php 
				$getQ16_comments = mysql_query('SELECT q16_comments FROM `reorg_survey` WHERE q16_comments != "";');
				$q16_comments = array();
				while ($row = mysql_fetch_array($getQ16_comments))
				{
					$q16_comments[] = $row['q16_comments'];
				}
			?>
			<table class="comment_table">
				<tr><th>Comments <button type="button" class="expander">Expand</button></th></tr>
				<?php foreach ($q16_comments as $comment): ?>
					<tr><td><?php echo $comment; ?></td></tr>
				<?php endforeach; ?>
			</table><br><br>
			
			
			<?php 
				$getQ17 = mysql_query('SELECT SUM(q17a) as q17aSUM, SUM(q17b) as q17bSUM, SUM(q17c) as q17cSUM, SUM(q17d) as q17dSUM, SUM(q17e) as q17eSUM  FROM `reorg_survey`');
				$row = mysql_fetch_array($getQ17);
				$q17a = $row['q17aSUM'];
				$q17b = $row['q17bSUM'];
				$q17c = $row['q17cSUM'];
				$q17d = $row['q17dSUM'];
				$q17e = $row['q17eSUM'];
			?>
			<?php 
				$getQ17_other = mysql_query('SELECT q17_other FROM `reorg_survey` WHERE q17_other != "";');
				$q17_other = array();
				while ($row = mysql_fetch_array($getQ17_other))
				{
					$q17_other[] = $row['q17_other'];
				}
			?>
			<p>17) What should be the role of an associate chair?  (Choose all that apply)</p>
			<table class="v_question">
				<tr><td class="center"><?php echo $q17a ?></td><td>a) Workload distribution</td></tr>
				<tr><td class="center"><?php echo $q17b ?></td><td>b) Evaluation</td></tr>
				<tr><td class="center"><?php echo $q17c ?></td><td>c) Helping to determine hiring priorities</td></tr>
				<tr><td class="center"><?php echo $q17d ?></td><td>d) Research leadership</td></tr>
				<tr><td class="center"><?php echo $q17e ?></td><td>e) Budget input (e.g. travel, colloquia & seminars)</td></tr>
				<tr><td class="center">Other:</td><td>
					<table class="other_table">
						<?php foreach ($q17_other as $other): ?>
							<tr><td><?php echo $other; ?></td></tr>
						<?php endforeach; ?>
					</table>
				</td></tr>
			</table>
			<br><br>
			
			
			<?php 
				$getQ18 = mysql_query('SELECT q18,count(q18) as count FROM `reorg_survey` group by q18');
				$q18 = array();
				while ($row = mysql_fetch_array($getQ18))
				{
					$option = $row['q18'];
					$q18[$option] = $row['count'];
				}
			?>
			<p>18) The department should have a review conducted by an outside panel</p>
			<table class="h_question">
				<tr>
					<td class="center">Strongly Agree<br><?php echo isset($q18['Strongly Agree']) ? $q18['Strongly Agree'] : "0"; ?></td>
					<td class="center">Agree<br><?php echo isset($q18['Agree']) ? $q18['Agree'] : "0"; ?></td>
					<td class="center">Undecided<br><?php echo isset($q18['Undecided']) ? $q18['Undecided'] : "0"; ?></td>
					<td class="center">Disagree<br><?php echo isset($q18['Disagree']) ? $q18['Disagree'] : "0"; ?></td>
					<td class="center">Strongly Disagree<br><?php echo isset($q18['Strongly Disagree']) ? $q18['Strongly Disagree'] : "0"; ?></td>
					<td class="center">Prefer Not to Respond<br><?php echo isset($q18['Prefer Not to Respond']) ? $q18['Prefer Not to Respond'] : "0"; ?></td>
				</tr>
			</table>
			
			<?php 
				$getQ18_comments = mysql_query('SELECT q18_comments FROM `reorg_survey` WHERE q18_comments != "";');
				$q18_comments = array();
				while ($row = mysql_fetch_array($getQ18_comments))
				{
					$q18_comments[] = $row['q18_comments'];
				}
			?>
			<table class="comment_table">
				<tr><th>Comments <button type="button" class="expander">Expand</button></th></tr>
				<?php foreach ($q18_comments as $comment): ?>
					<tr><td><?php echo $comment; ?></td></tr>
				<?php endforeach; ?>
			</table><br><br>
			
			
				
			<p>19) If you are in favor of an outside review and you would like to suggest one or more people to serve on a review panel, please list names and affiliations here.</p>
			<?php 
				$getQ19 = mysql_query('SELECT q19 FROM `reorg_survey` WHERE q19 != "";');
				$q19 = array();
				while ($row = mysql_fetch_array($getQ19))
				{
					$q19[] = $row['q19'];
				}
			?>
			<table class="comment_table">
				<tr><th>Responses <button type="button" class="expander">Expand</button></th></tr>
				<?php foreach ($q19 as $q19_input): ?>
					<tr><td><?php echo $q19_input; ?></td></tr>
				<?php endforeach; ?>
			</table><br><br>
			
			
			
			<?php 
				$getQ20 = mysql_query('SELECT q20,count(q20) as count FROM `reorg_survey` group by q20');
				$q20 = array();
				while ($row = mysql_fetch_array($getQ20))
				{
					$option = $row['q20'];
					$q20[$option] = $row['count'];
				}
			?>
			<p>20) The department should have an (ongoing) outside advisory panel</p>
			<table class="h_question">
				<tr>
					<td class="center">Strongly Agree<br><?php echo isset($q20['Strongly Agree']) ? $q20['Strongly Agree'] : "0"; ?></td>
					<td class="center">Agree<br><?php echo isset($q20['Agree']) ? $q20['Agree'] : "0"; ?></td>
					<td class="center">Undecided<br><?php echo isset($q20['Undecided']) ? $q20['Undecided'] : "0"; ?></td>
					<td class="center">Disagree<br><?php echo isset($q20['Disagree']) ? $q20['Disagree'] : "0"; ?></td>
					<td class="center">Strongly Disagree<br><?php echo isset($q20['Strongly Disagree']) ? $q20['Strongly Disagree'] : "0"; ?></td>
					<td class="center">Prefer Not to Respond<br><?php echo isset($q20['Prefer Not to Respond']) ? $q20['Prefer Not to Respond'] : "0"; ?></td>
				</tr>
			</table>
			
			<?php 
				$getQ20_comments = mysql_query('SELECT q20_comments FROM `reorg_survey` WHERE q20_comments != "";');
				$q20_comments = array();
				while ($row = mysql_fetch_array($getQ20_comments))
				{
					$q20_comments[] = $row['q20_comments'];
				}
			?>
			<table class="comment_table">
				<tr><th>Comments <button type="button" class="expander">Expand</button></th></tr>
				<?php foreach ($q20_comments as $comment): ?>
					<tr><td><?php echo $comment; ?></td></tr>
				<?php endforeach; ?>
			</table><br><br>
				
				
			
			<p>21) If you are in favor of an outside advisory panel and you would like to suggest one or more people to serve on an advisory panel, please list names and affiliations here.</p>
			<?php 
				$getQ21 = mysql_query('SELECT q21 FROM `reorg_survey` WHERE q21 != "";');
				$q21 = array();
				while ($row = mysql_fetch_array($getQ21))
				{
					$q21[] = $row['q21'];
				}
			?>
			<table class="comment_table">
				<tr><th>Responses <button type="button" class="expander">Expand</button></th></tr>
				<?php foreach ($q21 as $q21_input): ?>
					<tr><td><?php echo $q21_input; ?></td></tr>
				<?php endforeach; ?>
			</table><br><br>
			
			
			
			<p>22) If you do not favor reorganization but see problems that need to be remedied, please suggest approaches that would be helpful.</p>
			<?php 
				$getQ22 = mysql_query('SELECT q22 FROM `reorg_survey` WHERE q22 != "";');
				$q22 = array();
				while ($row = mysql_fetch_array($getQ22))
				{
					$q22[] = $row['q22'];
				}
			?>
			<table class="comment_table">
				<tr><th>Responses <button type="button" class="expander">Expand</button></th></tr>
				<?php foreach ($q22 as $q22_input): ?>
					<tr><td><?php echo $q22_input; ?></td></tr>
				<?php endforeach; ?>
			</table><br><br>
				
				
				
			<p>23) Are there any other comments that you would like to add?</p>
			<?php 
				$getQ23 = mysql_query('SELECT q23 FROM `reorg_survey` WHERE q23 != "";');
				$q23 = array();
				while ($row = mysql_fetch_array($getQ23))
				{
					$q23[] = $row['q23'];
				}
			?>
			<table class="comment_table">
				<tr><th>Responses <button type="button" class="expander">Expand</button></th></tr>
				<?php foreach ($q23 as $q23_input): ?>
					<tr><td><?php echo $q23_input; ?></td></tr>
				<?php endforeach; ?>
			</table><br><br>




		</div>
	</div>


</body>
</html>

