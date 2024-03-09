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
    PDO::ATTR_EMULATE_PREPARES   => true
);
$mthsc_db = new PDO($dsn, $user, $pass, $opt);

if (isset($_SERVER['REMOTE_USER']))
{
	$user_id = strtoupper($_SERVER['REMOTE_USER']);
	$user_name = $_SERVER['fullName'];
	$xid = $_SERVER['clemsonXID'];
}
date_default_timezone_set('America/New_York');

//-----------------
// get faculty list
//-----------------
$faculty_query = $mthsc_db->query('SELECT roles.person_id,first_name,last_name FROM dept_info.roles INNER JOIN dept_info.person on roles.person_id = person.person_id WHERE username !="\-\-" AND role = "Faculty" ORDER BY last_name ASC');
//$faculty_query = $mthsc_db->query('SELECT IF(NOT display_name IS NULL AND display_name != "", display_name, first_name) AS first_name,IF(NOT suffix IS NULL AND suffix != "", CONCAT_WS(" ",last_name,suffix), last_name) AS last_name FROM dept_info.people_to_lists_link AS pll LEFT JOIN dept_info.lists AS l ON l.list_id = pll.list_id LEFT JOIN dept_info.person AS p ON p.person_id = pll.person_id LEFT JOIN dept_info.employees AS e ON e.person_id = p.person_id WHERE l.list_name = "current faculty" ORDER BY last_name, first_name');
$faculty = $faculty_query->fetchAll();
$faculty_select = '<option value="">Select your instructor...</option>';
foreach($faculty as $member)
{
	$faculty_select .= "<option value='".$member['first_name']." ".$member['last_name']."'>".$member['last_name'].", ".$member['first_name']."</option>";
}

//---------------
// get settings
//---------------
$settings_query = $mthsc_db->query('SELECT setting,value FROM gs_prelim_signup_settings');
$settings = $settings_query->fetchAll(PDO::FETCH_KEY_PAIR);
//print_r($settings);


$prelim_policy = '<p><strong>We are planning to hold the synchronous portions of the '.$settings['prelim_month'].' '.$settings['prelim_year'].' prelims in person from '.$settings['prelim_date_start'].' to '.$settings['prelim_date_end'].'. The asynchronous portion of the Computation prelim will be held over the weekend of '.$settings['weekend_date'].'</strong>.</p>
			
			<p>The modality for the synchronous exams will be determined by the university’s course modality policy. If most courses will be held in person, then the exams will be in person in a location like Martin Hall M-1 suitable for physical distancing. If most courses will be virtual, then the exams will be virtual as well through Zoom; in this event, students will be allowed to reserve a space on campus to take the exams if their office or home are unsuitable. More details will be sent out as the exam dates approach.</p>
			
			<p>The prelim signup form must be submitted online by '.date('l, F j, Y',strtotime($settings['signup_deadline'])).'. The deadline to withdraw from the prelim attempt is '.date('l, F j, Y',strtotime($settings['withdrawal_deadline'])).'.</p>
			
			<p>Please let us know if you have any issues filling out the webform and/or have any questions or concerns.</p>
			
			<p>Please note the former and current Prelim Policy set by the Mathematical and Statistical Sciences Graduate Affairs Committee below:</p>
			
			<h2>Former SMSS Prelim Policy (Before May 10, 2020)</h2>
			<p>Graduate students are required to receive at least 2 Strong Passes and an additional Pass or Strong Pass without accumulating 4 Fails within 2 years of entering the PhD program. MS students are allowed to take prelims and all passes and fails will count toward their progress. Any prelims taken by a graduate student become part of their permanent prelim record.</p>
			
			<h2>Current SMSS Prelim Policy (Effective May 10, 2020)</h2>
			<p>Graduate students are required to receive 2 Passes without accumulating 3 Fails within 3 years of entering the graduate program. MS students are allowed to take prelims and all passes and fails will count towards their progress. Any prelims taken by a graduate student become part of their permanent prelim record.</p>
			
			<h3>Guidelines for Transitioning to the New System</h3>
			<p>The policy is effective May 10, 2020.
				<br>
				Any student entering the program after May 10, 2020 must follow the new policy.
				<br>
				Any student who left the program before May 10, 2020 is subject to the prelim policy at the time of the student entering the program.
				<br>
				Any current student who entered the program before May 10, 2020 will have two options:
			</p>
			<p><strong>Option 1</strong>: the student can follow the previous policy and any prelim taken after May 10, 2020 will count as follows:
				<ul>
					<li>A Pass in the new system may count as a Strong Pass (SP).</li>
					<li>A Fail in the new system will be the same as a Fail in the old system.</li>
				</ul>
			</p>
				
			<p><strong>Option 2</strong>: the student can follow the new policy and any prelim taken before May 10, 2020 will count as follows:
				<ul>
					<li>A Strong Pass in the previous system will be carried over as a Pass in the new system.</li>
					<li>A Pass in the previous system will be null and void in the new system.</li>
					<li>A Fail in the previous system will be carried over as a Fail in the new system.</li>
				</ul>
			</p>
			<br>
			<h3>No-Show Policy</h3>
			<p>For both policies, a no-show will count as a Fail if a student signed up and did not withdraw by the specific withdrawal deadline (<b>'.$settings['withdrawal_deadline'].'</b> for the <b>'.$settings['prelim_month'].' '.$settings['prelim_year'].'</b> Prelims) unless there are unusual circumstances such as a medical excuse, a family emergency etc. Any exception to the no-show fail policy can only be made if a written request is submitted by the student to the Associate Director for Graduate Studies and Grad Student Services Coordinator and approved by the Associate Director for Graduate Studies.</p>
			
			<h3>List of Prelim Topics</h3>
			<p>Before signing up for a prelim, you should read and understand the topics covered on that prelim. You can view the lists of prelim topics as well as examples of previous prelims on the <a href="https://www.clemson.edu/science/departments/math-stat/academics/graduate/phd-program/past-prelims.html">past prelim page</a>.
			
			<h3>MATH 9910 (Research Hours) Policy</h3>
			<p>Ph.D. students begin the program with an allowance of zero research hours per semester. As students demonstrate maturity by passing certain milestones in the Ph.D. program this allowance is increased as follows.</p>
			<p><ol>
				<li>When a student completes the three required prelims under the current policy or two required prelims under the new policy his or her research hours allowance is increased by three hours per semester.</li>
				<li>When a student completes the Ph.D. breadth requirement his or her research hours allowance is increased by three hours per semester.</li>
				<li>When a student completes his or her comprehensive oral exam his or her research hours allowance is increased by three hours per semester.</li>
			</ol>
			Note: To register for MATH 9910, you need to have approval of your PhD adviser, who is directing the research and is listed as the instructor for the course.</p>';


//---------------
// PROCEDURES
//---------------

if (isset($_POST['policy_understand']))
{
	$progress = "agree";
}
if (isset($_POST['form_submit']))
{
	//set up submission
	$submission = array('xid' => $xid,
						'prelim_month' => $settings['prelim_month'],
						'prelim_year' => $settings['prelim_year'],
						'name' => $user_name,
						'user_id' => $user_id,
						'algebra' => 0,
						'analysis' => 0,
						'comp_math' => 0,
						'operations' => 0,
						'statistics' => 0,
						'stochastics' => 0,
						'c8510' => "",
						'c8530' => "",
						'c8210' => "",
						'c8220' => "",
						'c8600' => "",
						'c8610' => "",
						'c8100' => "",
						'c8130' => "",
						'c8010' => "",
						'c8040' => "",
						'c8030' => "",
						'c8170' => "");
	
	if (!isset($_POST['exams']))
	{
		$_POST['exams'] = array();
	}
						
	//capture data
	if (in_array('algebra',$_POST['exams']))
	{
		$submission['algebra'] = 1;
		$submission['c8510'] = $_POST['8510'];
		$submission['c8530'] = $_POST['8530'];
	}
	if (in_array('analysis',$_POST['exams']))
	{
		$submission['analysis'] = 1;
		$submission['c8210'] = $_POST['8210'];
		$submission['c8220'] = $_POST['8220'];
	}
	if (in_array('comp_math',$_POST['exams']))
	{
		$submission['comp_math'] = 1;
		$submission['c8600'] = $_POST['8600'];
		$submission['c8610'] = $_POST['8610'];
	}
	if (in_array('operations',$_POST['exams']))
	{
		$submission['operations'] = 1;
		$submission['c8100'] = $_POST['8100'];
		$submission['c8130'] = $_POST['8130'];
	}
	if (in_array('statistics',$_POST['exams']))
	{
		$submission['statistics'] = 1;
		$submission['c8010'] = $_POST['8010'];
		$submission['c8040'] = $_POST['8040'];
	}
	if (in_array('stochastics',$_POST['exams']))
	{
		$submission['stochastics'] = 1;
		$submission['c8030'] = $_POST['8030'];
		$submission['c8170'] = $_POST['8170'];
	}
	$submit_query = $mthsc_db->prepare('INSERT INTO gs_prelim_signup (`xid`,`prelim_month`,`prelim_year`,`name`,`user_id`,`algebra`,`analysis`,`comp_math`,`operations`,`statistics`,`stochastics`,`8510`,`8530`,`8210`,`8220`,`8600`,`8610`,`8100`,`8130`,`8010`,`8040`,`8030`,`8170`) VALUES (:xid,:prelim_month,:prelim_year,:name,:user_id,:algebra,:analysis,:comp_math,:operations,:statistics,:stochastics,:c8510,:c8530,:c8210,:c8220,:c8600,:c8610,:c8100,:c8130,:c8010,:c8040,:c8030,:c8170) ON DUPLICATE KEY UPDATE algebra = :algebra,analysis= :analysis,comp_math = :comp_math,operations = :operations,statistics = :statistics,stochastics = :stochastics,`8510` = :c8510,`8530` = :c8530,`8210` = :c8210,`8220` = :c8220,`8600` = :c8600,`8610` = :c8610,`8100` = :c8100,`8130` = :c8130,`8010` = :c8010,`8040` = :c8040,`8030` = :c8030,`8170` = :c8170;');
	//var_dump($submit_query);
	//echo('<br>');
	//print_r($submission);
	$result = $submit_query->execute($submission);
	
	//craft email
	$submission_text = '<p>';
	
	if ($submission['algebra']){$submission_text .= '<b>Algebra</b><br>';}
	if ($submission['analysis']){$submission_text .= '<b>Analysis</b><br>';}
	if ($submission['comp_math']){$submission_text .= '<b>Computational Math</b><br>';}
	if ($submission['operations']){$submission_text .= '<b>Operations Research</b><br>';}
	if ($submission['statistics']){$submission_text .= '<b>Statistics</b><br>';}
	if ($submission['stochastics']){$submission_text .= '<b>Stochastics</b><br>';}
	
	$submission_text .= '</p><p>and provided this course history:</p><p>';
	if ($submission['algebra'])
	{
		$submission_text .= '8510: '.$submission['c8510'].'<br>';
		$submission_text .= '8530: '.$submission['c8530'].'<br>';
	}
	if ($submission['analysis'])
	{
		$submission_text .= '8210: '.$submission['c8210'].'<br>';
		$submission_text .= '8220: '.$submission['c8220'].'<br>';
	}
	if ($submission['comp_math'])
	{
		$submission_text .= '8600: '.$submission['c8600'].'<br>';
		$submission_text .= '8610: '.$submission['c8610'].'<br>';
	}
	if ($submission['operations'])
	{
		$submission_text .= '8100: '.$submission['c8100'].'<br>';
		$submission_text .= '8130: '.$submission['c8130'].'<br>';
	}
	if ($submission['statistics'])
	{
		$submission_text .= '8010: '.$submission['c8010'].'<br>';
		$submission_text .= '8040: '.$submission['c8040'].'<br>';
	}
	if ($submission['stochastics'])
	{
		$submission_text .= '8030: '.$submission['c8030'].'<br>';
		$submission_text .= '8170: '.$submission['c8170'].'<br>';
	}
	$submission_text .= '</p>';
	
	$student_opener = '<html><body><p>Thank you, '.$_SERVER['givenName'].', you have signed up for the following '.$settings['prelim_month'].' '.$settings['prelim_year'].' prelims:</p>';
	$coord_opener = '<html><body><p>'.$_SERVER['fullName'].' has signed up for the following '.$settings['prelim_month'].' '.$settings['prelim_year'].' prelims:</p>';
	
	$student_closer = '<p>If you have any questions, please contact The Associate Director for Graduate Studies.</p></body></html>';
	
	$coord_closer = '<p><a href="https://mthsc.clemson.edu/dept_forms/gs_prelim_admin.php">Click here</a> to view the Prelim Admin Page.</p></body></html>';
	
	$headers = "MIME-Version: 1.0\r\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
	$headers .= "From: 'SMSS Assoc. Dir. for Grad Studies' <mthgrad@clemson.edu>\r\n";
	$subject = "Prelim Signup Submission";
	
	//send student email
	mail ($submission['user_id'].'@clemson.edu', $subject, $student_opener.' '.$submission_text.' '.$prelim_policy.' '.$student_closer, $headers);
	//send coord email
	mail ('mthgrad@clemson.edu', $subject, $coord_opener.' '.$submission_text.' '.$coord_closer, $headers);
	
	$progress = "success";
}


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Prelim Signup</title>
	<meta name="author" content="hedetni">
	<!-- Date: 2017-9-25 -->

<link rel="stylesheet" href="/style/math.css" type="text/css" media="screen" title="no title" charset="utf-8">

<style type="text/css">
input {font-size:1em;}
div.area,div#instructor_section {display:none;}

</style>

<script src="/style/jquery-3.2.1.min.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">

$(document).ready(function() 
{
	$('input[type=checkbox]').change(
		function() {
			var checked_count = $('input[type=checkbox]:checked').length;
			if (checked_count > 0)
			{
				$('div#instructor_section').show();
			}
			else
			{
				$('div#instructor_section').hide();
			}
			var exam = this.value
			if ($('#'+exam+'_exam').is(':checked'))
			{
				$('#'+exam+'_courses').show();
			}
			else
			{
				$('#'+exam+'_courses').hide();
			}
		}
	);
	


});

</script>

</head>
<body>
	<div id="main">
		<div id="header">
			<a href="http://www.clemson.edu/math" title="School Home">
				<img src="/style/math_logo.png" alt="school logo">
			</a>
			<h1>Prelim Signup</h1>
		</div>
	
	<?php if (isset($progress) && $progress=="agree"): ?>
		<div id="content">
			<p>Be sure to click "Submit" after completing the form. You will receive an email confirming your submission.</p>
			<form name="prelim_registration_form" method="POST" action="">
			<h2>Student Information</h2>
			<label>Name:</label> <input type="text" name="name" value="<?php echo $user_name; ?>" readonly></input><br>
			<label>User ID:</label> <input type="text" name="user_id" value="<?php echo $user_id; ?>" readonly></input><br>
			<label>XID:</label> <input type="text" name="xid" value="<?php echo $xid; ?>" readonly></input>
			
			<h2>Exams</h2>
			<p>Select all exams which you intend to take during the <b><?php echo $settings['prelim_month'].' '.$settings['prelim_year']; ?></b> Prelims (if you are withdrawing from a prelim, make sure that prelim is NOT checked):</p>
			<p><input type="checkbox" name="exams[]" value="algebra" id="algebra_exam"></input> <label for="algebra_exam">Algebra</label></p>
			<p><input type="checkbox" name="exams[]" value="analysis" id="analysis_exam"></input> <label for="analysis_exam">Analysis</label></p>
			<p><input type="checkbox" name="exams[]" value="comp_math" id="comp_math_exam"></input> <label for="comp_math_exam">Computational Math</label></p>
			<p><input type="checkbox" name="exams[]" value="operations" id="operations_exam"></input> <label for="operations_exam">Operations Research</label></p>
			<p><input type="checkbox" name="exams[]" value="statistics" id="statistics_exam"></input> <label for="statistics_exam">Statistics</label></p>
			<p><input type="checkbox" name="exams[]" value="stochastics" id="stochastics_exam"></input> <label for="stochastics_exam">Stochastics</label></p>
			
			<div id="instructor_section">
				<h2>Instructors</h2>
				<p>For each exam you checked, select your instructor for the following courses:</p>
			
				<div class="area" id="algebra_courses">
					<p><b>Algebra</b><br>
						<label>8510</label> 
						<select name="8510">
							<?php echo $faculty_select; ?>
						</select><br>
						<label>8530</label>
						<select name="8530">
							<?php echo $faculty_select; ?>
						</select>
					</p>
				</div>
			
				<div class="area" id="analysis_courses">
					<p><b>Analysis</b><br>
						<label>8210</label> 
						<select name="8210">
							<?php echo $faculty_select; ?>
						</select><br>
						<label>8220</label>
						<select name="8220">
							<?php echo $faculty_select; ?>
						</select>
					</p>
				</div>
				
				<div class="area" id="comp_math_courses">
					<p><b>Computational Math</b><br>
						<label>8600</label> 
						<select name="8600">
							<?php echo $faculty_select; ?>
						</select><br>
						<label>8610</label>
						<select name="8610">
							<?php echo $faculty_select; ?>
						</select>
					</p>
				</div>
				
				<div class="area" id="operations_courses">
					<p><b>Operations Research</b><br>
						<label>8100</label> 
						<select name="8100">
							<?php echo $faculty_select; ?>
						</select><br>
						<label>8130</label>
						<select name="8130">
							<?php echo $faculty_select; ?>
						</select><br>
					</p>
				</div>
				
				<div class="area" id="statistics_courses">
					<p><b>Statistics</b><br>
						<label>8010</label> 
						<select name="8010">
							<?php echo $faculty_select; ?>
						</select><br>
						<label>8040</label>
						<select name="8040">
							<?php echo $faculty_select; ?>
						</select>
					</p>
				</div>
				
				<div class="area" id="stochastics_courses">
					<p><b>Stochastics</b><br>
						<label>8030</label> 
						<select name="8030">
							<?php echo $faculty_select; ?>
						</select><br>
						<label>8170</label>
						<select name="8170">
							<?php echo $faculty_select; ?>
						</select>
					</p>
				</div>
				
				
			
			</div>
			<input type="submit" value="Submit" name="form_submit">
		</div>	
		
		
	<?php elseif (isset($progress) && $progress=="success"): ?>
		<div id="content">
			<h2>Submission Received</h2>
			
			<p>Thank you, <?php echo $_SERVER['givenName']; ?>. You have signed up for the following prelims:</p>
			<p>
				<?php echo $submission['algebra'] ? '<b>Algebra</b><br>' : ""; ?>
				<?php echo $submission['analysis'] ? '<b>Analysis</b><br>' : ""; ?>
				<?php echo $submission['comp_math'] ? '<b>Computational Math</b><br>' : ""; ?>
				<?php echo $submission['operations'] ? '<b>Operations Research</b><br>' : ""; ?>
				<?php echo $submission['statistics'] ? '<b>Statistics</b><br>' : ""; ?>
				<?php echo $submission['stochastics'] ? '<b>Stochastics</b><br>' : ""; ?>
			</p>
			
			<p>and provided this course history:</p>
			<p>
				<?php if ($submission['algebra']): ?>
					8510: <?php echo $submission['c8510']; ?><br>
					8530: <?php echo $submission['c8530']; ?><br>
				<?php endif; ?>
				<?php if ($submission['analysis']): ?>
					8210: <?php echo $submission['c8210']; ?><br>
					8220: <?php echo $submission['c8220']; ?><br>
				<?php endif; ?>
				<?php if ($submission['comp_math']): ?>
					8600: <?php echo $submission['c8600']; ?><br>
					8610: <?php echo $submission['c8610']; ?><br>
				<?php endif; ?>
				<?php if ($submission['operations']): ?>
					8100: <?php echo $submission['c8100']; ?><br>
					8130: <?php echo $submission['c8130']; ?><br>
				<?php endif; ?>
				<?php if ($submission['statistics']): ?>
					8010: <?php echo $submission['c8010']; ?><br>
					8040: <?php echo $submission['c8040']; ?><br>
				<?php endif; ?>
				<?php if ($submission['stochastics']): ?>
					8030: <?php echo $submission['c8030']; ?><br>
					8170: <?php echo $submission['c8170']; ?><br>
				<?php endif; ?>
			</p>
			<p>This information will be emailed to you along with a copy of the Prelim Policy for your records. The deadline to withdraw from a prelim attempt is <b><?php echo $settings['withdrawal_deadline']; ?></b>.</p>
			
			
			
		</div>
	
	
	<?php else: ?>
		<div id="content">
			<?php echo $prelim_policy; ?>
			<br>
			<h2>Register for <b><?php echo $settings['prelim_month'].' '.$settings['prelim_year']; ?></b> Prelims</h2>
			<?php if ($settings['allow_signups']): ?>
				<p>We are planning to hold the synchronous portions of the <strong><?php echo $settings['prelim_month'].' '.$settings['prelim_year'];?></strong> prelims in person from <strong><?php echo $settings['prelim_date_start'];?></strong> to <strong><?php echo $settings['prelim_date_end'];?></strong>. The asynchronous portion of the Computation prelim will be held over the weekend of <strong><?php echo $settings['weekend_date'];?></strong>.</p>
				
				<p>By clicking ‘I understand’ below, you are acknowledging that you have read and understand both the current and the new prelim policy and that you have read and acknowledge the prelim topics as linked to above. To register for the <b><?php echo $settings['prelim_month'].' '.$settings['prelim_year']; ?></b> prelims, the signup form must be submitted by  <b><?php echo date('l, F j, Y',strtotime($settings['signup_deadline'])); ?></b>. The deadline to withdraw from a prelim attempt is <b><?php echo date('l, F j, Y',strtotime($settings['withdrawal_deadline'])); ?></b>. To withdraw, submit the form again selecting only those prelims you wish to take. A prelim you wish not to take should not be checked. All previous signups are overwritten each time you submit.<p>
			
				<form action="" method="POST" name="policy_understand_form">
					<input type="submit" name="policy_understand" value="I understand, take me to the form"></input>
				</form>
			<?php else: ?>
				<p>We are not accepting prelim signups at this time.</p>
			<?php endif; ?>
		</div>
	<?php endif; ?>
	
		<div id="footer">Click <a href="/dept_forms/website_error.py" target="_blank">here</a> to report a problem with this website.</div>
	</div>
</body>
</html>