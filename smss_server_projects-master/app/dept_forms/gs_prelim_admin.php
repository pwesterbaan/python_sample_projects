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

//------------------
// update settings
//------------------

if (isset($_POST['update_settings']))
{
	$settings_to_update = array();
	$settings_to_update[] = array($_POST['allow_signups'],'allow_signups');
	$settings_to_update[] = array($_POST['prelim_month'], 'prelim_month');
	$settings_to_update[] = array($_POST['prelim_year'], 'prelim_year');
	$settings_to_update[] = array($_POST['signup_deadline'], 'signup_deadline');
	$settings_to_update[] = array($_POST['withdrawal_deadline'],'withdrawal_deadline');
	$settings_to_update[] = array($_POST['prelim_date_start'], 'prelim_date_start');
	$settings_to_update[] = array($_POST['prelim_date_end'], 'prelim_date_end');
	$settings_to_update[] = array($_POST['weekend_date'], 'weekend_date');
	
	$update_query = $mthsc_db->prepare('UPDATE gs_prelim_signup_settings SET value = ? WHERE setting = ?;');
	$success = false;
	foreach ($settings_to_update as $setting)
	{
		$success = $update_query->execute($setting);
	}
	if ($success)
	{
		$message = "Settings Updated";
	}
}



//---------------
// get settings
//---------------
$settings_query = $mthsc_db->query('SELECT setting,value FROM gs_prelim_signup_settings');
$settings = $settings_query->fetchAll(PDO::FETCH_KEY_PAIR);

//---------------
// get prelim times
//---------------
$months_query = $mthsc_db->query('SELECT prelim_month FROM gs_prelim_signup GROUP BY prelim_month ASC');
$months = $months_query->fetchAll(PDO::FETCH_COLUMN);

$years_query = $mthsc_db->query('SELECT prelim_year FROM gs_prelim_signup GROUP BY prelim_year DESC');
$years = $years_query->fetchAll(PDO::FETCH_COLUMN);


//--------------
// get signups
//--------------

if (isset($_POST['view_signups']))
{
	$month = $_POST['month'];
	$year = $_POST['year'];
	$signups_query = $mthsc_db->prepare('SELECT * FROM gs_prelim_signup WHERE prelim_month = ? AND prelim_year = ? ORDER BY user_id ASC');
	$signups_query->execute(array($month,$year));
	$signups = $signups_query->fetchAll();
	
	$counts = array('algebra' => 0,
					'analysis' => 0,
					'comp_math' => 0,
					'operations' => 0,
					'statistics' => 0,
					'stochastics' => 0);
					
	$courses = array('8510','8530','8210','8220','8600','8610','8100','8130','8010','8040','8030','8170');
	
	$profs = array('8510' => array(),
					'8530' => array(),
					'8210' => array(),
					'8220' => array(),
					'8600' => array(),
					'8610' => array(),
					'8100' => array(),
					'8130' => array(),
					'8010' => array(),
					'8040' => array(),
					'8030' => array(),
					'8170' => array());
					
	foreach ($courses as $course)
	{
		$query = 'SELECT `'.$course.'` as prof,count(`'.$course.'`) as number FROM `gs_prelim_signup` WHERE `'.$course.'` != "" AND prelim_month = ? AND prelim_year = ? GROUP BY `'.$course.'` ASC;';
		$prof_query = $mthsc_db->prepare($query);
		$prof_query->execute(array($month,$year));
		$profs[$course] = $prof_query->fetchAll();
	}
	/*
	$c8510_query = $mthsc_db->query('SELECT `8510` as prof,count(`8510`) as number FROM `gs_prelim_signup` WHERE `8510` != "" GROUP BY `8510` ASC;');
	$profs['8510'] = $c8510_query->fetchAll();
	
	$c8530_query = $mthsc_db->query('SELECT `8530` as prof,count(`8530`) as number FROM `gs_prelim_signup` WHERE `8530` != "" GROUP BY `8530` ASC;');
	$profs['8530'] = $c8530_query->fetchAll();
	
	$c8210_query = $mthsc_db->query('SELECT `8210` as prof,count(`8210`) as number FROM `gs_prelim_signup` WHERE `8210` != "" GROUP BY `8210` ASC;');
	$profs['8210'] = $c8210_query->fetchAll();
	
	$c8220_query = $mthsc_db->query('SELECT `8220` as prof,count(`8220`) as number FROM `gs_prelim_signup` WHERE `8220` != "" GROUP BY `8220` ASC;');
	$profs['8220'] = $c8220_query->fetchAll();
	
	$c8600_query = $mthsc_db->query('SELECT `8600` as prof,count(`8600`) as number FROM `gs_prelim_signup` WHERE `8600` != "" GROUP BY `8600` ASC;');
	$profs['8600'] = $c8600_query->fetchAll();
	
	$c8610_query = $mthsc_db->query('SELECT `8610` as prof,count(`8610`) as number FROM `gs_prelim_signup` WHERE `8610` != "" GROUP BY `8610` ASC;');
	$profs['8610'] = $c8610_query->fetchAll();
	
	$c8100_query = $mthsc_db->query('SELECT `8100` as prof,count(`8100`) as number FROM `gs_prelim_signup` WHERE `8100` != "" GROUP BY `8100` ASC;');
	$profs['8100'] = $c8100_query->fetchAll();
	
	$c8130_query = $mthsc_db->query('SELECT `8130` as prof,count(`8130`) as number FROM `gs_prelim_signup` WHERE `8130` != "" GROUP BY `8130` ASC;');
	$profs['8130'] = $c8130_query->fetchAll();
	
	$c8140_query = $mthsc_db->query('SELECT `8140` as prof,count(`8140`) as number FROM `gs_prelim_signup` WHERE `8140` != "" GROUP BY `8140` ASC;');
	$profs['8140'] = $c8140_query->fetchAll();
	
	$c8010_query = $mthsc_db->query('SELECT `8010` as prof,count(`8010`) as number FROM `gs_prelim_signup` WHERE `8010` != "" GROUP BY `8010` ASC;');
	$profs['8010'] = $c8010_query->fetchAll();
	
	$c8040_query = $mthsc_db->query('SELECT `8040` as prof,count(`8040`) as number FROM `gs_prelim_signup` WHERE `8040` != "" GROUP BY `8040` ASC;');
	$profs['8040'] = $c8040_query->fetchAll();
	
	$c8030_query = $mthsc_db->query('SELECT `8030` as prof,count(`8030`) as number FROM `gs_prelim_signup` WHERE `8030` != "" GROUP BY `8030` ASC;');
	$profs['8030'] = $c8030_query->fetchAll();
	
	$c8170_query = $mthsc_db->query('SELECT `8170` as prof,count(`8170`) as number FROM `gs_prelim_signup` WHERE `8170` != "" GROUP BY `8170` ASC;');
	$profs['8170'] = $c8170_query->fetchAll();
	*/
	
	$emails_query = $mthsc_db->prepare('SELECT CONCAT(user_id,"@mail.clemson.edu") as email FROM `gs_prelim_signup` where prelim_month = ? and prelim_year = ? AND (algebra != 0 || analysis != 0 || comp_math != 0 || operations != 0 || statistics != 0 || stochastics != 0) ORDER BY email ASC');
	$emails_query->execute(array($month,$year));
	$emails = $emails_query->fetchAll(PDO::FETCH_COLUMN);
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Prelim Signup Admin</title>
	<meta name="author" content="hedetni">
	<!-- Date: 2017-9-25 -->

<link rel="stylesheet" href="/style/math.css" type="text/css" media="screen" title="no title" charset="utf-8">
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<style type="text/css">
input {font-size:1em;}
div.area,div#instructor_section {display:none;}
.center_text{text-align:center;}
span.more{color:#666;cursor:pointer;font-size:0.9em;}
table.exam_table{
	display:inline-block;
	margin-right:2em;
	vertical-align:top;
	margin-bottom:1.5em;
}
span.inline_count{
	color:#666;
}
div.student_exam_courses {padding-left:1em;display:none;}

@media print {
	body{padding-top:0em;}
	.noprint {display:none;}
	table.exam_table{width:45%}
	h1 {margin-top:1em;}
}
</style>

<script src="/style/jquery-3.2.1.min.js" type="text/javascript" charset="utf-8"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script type="text/javascript">

$(document).ready(function() 
{
	$('.datepicker').datepicker({
			dateFormat: "MM d, yy",
			onClose: function(){$(this).valid();}
	});
	
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
	
	$('span.more').click(
		function() {
			$(this).parent().children('div.student_exam_courses').slideToggle();
			if ($(this).html() == '\u25b2')
			{
				$(this).html('\u25bc');
			}
			else
			{
				$(this).html('\u25b2');
			}
				
				
		}
	);
	
	$('a#toggle_emails').click(function(){
		$('p#emails').slideToggle();
	});
	
	$('a.toggle_test_emails').click(function(){
		$(this).next('div.emails').slideToggle();
	});

});

</script>

</head>
<body>
	<div id="main">
		<div id="header" class="noprint">
			<a href="http://www.clemson.edu/math" title="School Home">
				<img src="/style/math_logo.png" alt="school logo">
			</a>
			<h1><a href="gs_prelim_signup.php">Prelim Signup</a></h1>
		</div>
	
	<div id="content">
		<div class="noprint">
		<h1>Prelim Signup Admin</h1>
		
		<?php echo isset($message)? '<p id="error">'.$message.'</p>' : ''; ?>
		
		<h2>Settings</h2>
		<form name="update_settings_form" method="post" action="">
			<p><label for="allow_signups">Allow Signups?</label>
				<select name="allow_signups" id="allow_signups">
					<option value="1" <?php echo $settings['allow_signups'] ? "selected" : ""; ?> >Yes</option>
					<option value="0" <?php echo $settings['allow_signups'] ? "" : "selected"; ?> >No</option>
				</select>
			 <label for="prelim_month" style="margin-left:2em;">Next Upcoming Prelim Session: </label>
				<select name="prelim_month" id="prelim_month">
					<option value="Winter" <?php echo $settings['prelim_month']=='Winter' ? "selected" : ""; ?> >Winter</option>
					<option value="Summer" <?php echo $settings['prelim_month']=='Summer' ? "selected" : ""; ?> >Summer</option>
				</select> 
			<label for="prelim_year">Year</label>
				<select name="prelim_year" id="prelim_year">
					<option value="<?php echo date("Y");?>" <?php echo $settings['prelim_year']==date("Y") ? "selected" : ""; ?>><?php echo date("Y");?></option>
					<option value="<?php echo date("Y")+1;?>" <?php echo $settings['prelim_year']==date("Y")+1 ? "selected" : ""; ?>><?php echo date("Y")+1;?></option>
					<option value="<?php echo date("Y")+2;?>" <?php echo $settings['prelim_year']==date("Y")+2 ? "selected" : ""; ?>><?php echo date("Y")+2;?></option>
				</select></p>
			<p><label for="signup_deadline">Signup Deadline to Display on Form: </label><input type="text" class="datepicker" name="signup_deadline" id="signup_deadline" value="<?php echo $settings['signup_deadline'];?>"></input> 
				<label for="withdrawal_deadline" style="margin-left:2em;">Withdrawal Deadline to Display on Form: </label><input type="text" class="datepicker" name="withdrawal_deadline" id="withdrawal_deadline" value="<?php echo $settings['withdrawal_deadline'];?>"></input></p>
			<p>*Note, these deadlines are not automatically enforced. To prevent signups, you must select 'No' above.</p>
			<p>Prelim Testing Dates:<br>
				<label for="prelim_date_start">Prelim Period Start Date: </label><input type="text" class="datepicker" name="prelim_date_start" id="prelim_date_start" value="<?php echo $settings['prelim_date_start'];?>"></input><br>
				<label for="prelim_date_end">Prelim Period End Date: </label><input type="text" class="datepicker" name="prelim_date_end" id="prelim_date_end" value="<?php echo $settings['prelim_date_end'];?>"></input><br>
				<label for="weekend_date">Asynchronous Weekend Date: </label><input type="text"  name="weekend_date" id="weekend_date" value="<?php echo $settings['weekend_date'];?>"></input>
			</p>
			<p><input type="submit" name="update_settings" value="Update Settings"></input>
		</form>
		<br>
		<h2>View Signups</h2>
		<p>Select a month and year to view signups:<p>
		<form name="prelim_select" method="post" action="">
			<p><label for="month">Semester</label>
				<select name="month" id="month">
				<?php foreach ($months as $av_month): ?>
					<option value="<?php echo $av_month; ?>"><?php echo $av_month; ?></option>
				<?php endforeach; ?>
			</select>
			<label for="year">Year</label>
			<select name="year" id="year">
				<?php foreach ($years as $av_year): ?>
					<option value="<?php echo $av_year; ?>"><?php echo $av_year; ?></option>
				<?php endforeach; ?>
			</select>
			<input type="submit" name="view_signups" value="View"></input></p>
		</form>
		<br>
		</div>
		
		<?php if (isset($_POST['view_signups'])): ?>
			<h2><?php echo $month.' '.$year; ?> Prelim Signups</h2>
			
			<h3>Students by Exam</h3>
			<p class="noprint">Click the arrow next to a student's name to see their professors</p>
			<table class="exam_table">
				<tr><th>Algebra</th></tr>
				<?php foreach ($signups as $student): ?>
					<?php if ($student['algebra']): ?>
						<tr><td><?php echo $student['name'].' ('.strtolower($student['user_id']).')'; ?> <span class="more noprint">&#x25bc;</span><br>
						<div class="student_exam_courses noprint">
							8510: <?php echo $student['8510']; ?><br>
							8530: <?php echo $student['8530']; ?>
						</div></td></tr>
						<?php $counts['algebra']++; ?>
					<?php endif; ?>
				<?php endforeach; ?>
				<tr><td class="center_text"><?php echo $counts['algebra']; ?></td></tr>
				<tr><td><a class="toggle_test_emails">Show/Hide g.clemson Emails</a>
					<div class="emails" style="display:none;"><br>
						<?php foreach ($signups as $student): ?>
							<?php if ($student['algebra']): ?>
								<?php echo $student['user_id'].'@g.clemson.edu<br>'; ?>
							<?php endif; ?>
						<?php endforeach; ?>
					</div></td></tr>
			</table>
			
			<table class="exam_table">
				<tr><th>Analysis</th></tr>
				<?php foreach ($signups as $student): ?>
					<?php if ($student['analysis']): ?>
						<tr><td><?php echo $student['name'].' ('.strtolower($student['user_id']).')'; ?> <span class="more noprint">&#x25bc;</span><br>
						<div class="student_exam_courses noprint">
							8210: <?php echo $student['8210']; ?><br>
							8220: <?php echo $student['8220']; ?>
						</div></td></tr>
						<?php $counts['analysis']++; ?>
					<?php endif; ?>
				<?php endforeach; ?>
				<tr><td class="center_text"><?php echo $counts['analysis']; ?></td></tr>
				<tr><td><a class="toggle_test_emails">Show/Hide g.clemson Emails</a>
					<div class="emails" style="display:none;"><br>
						<?php foreach ($signups as $student): ?>
							<?php if ($student['analysis']): ?>
								<?php echo $student['user_id'].'@g.clemson.edu<br>'; ?>
							<?php endif; ?>
						<?php endforeach; ?>
					</div></td></tr>
			</table>
			
			<table class="exam_table">
				<tr><th>Comp Math</th></tr>
				<?php foreach ($signups as $student): ?>
					<?php if ($student['comp_math']): ?>
						<tr><td><?php echo $student['name'].' ('.strtolower($student['user_id']).')'; ?> <span class="more noprint">&#x25bc;</span><br>
						<div class="student_exam_courses noprint">
							8600: <?php echo $student['8600']; ?><br>
							8610: <?php echo $student['8610']; ?>
						</div></td></tr>
						<?php $counts['comp_math']++; ?>
					<?php endif; ?>
				<?php endforeach; ?>
				<tr><td class="center_text"><?php echo $counts['comp_math']; ?></td></tr>
				<tr><td><a class="toggle_test_emails">Show/Hide g.clemson Emails</a>
					<div class="emails" style="display:none;"><br>
						<?php foreach ($signups as $student): ?>
							<?php if ($student['comp_math']): ?>
								<?php echo $student['user_id'].'@g.clemson.edu<br>'; ?>
							<?php endif; ?>
						<?php endforeach; ?>
					</div></td></tr>
			</table>
			
			<table class="exam_table">
				<tr><th>OR</th></tr>
				<?php foreach ($signups as $student): ?>
					<?php if ($student['operations']): ?>
						<tr><td><?php echo $student['name'].' ('.strtolower($student['user_id']).')'; ?> <span class="more noprint">&#x25bc;</span><br>
						<div class="student_exam_courses noprint">
							8100: <?php echo $student['8100']; ?><br>
							8130: <?php echo $student['8130']; ?><br>
						</div></td></tr>
						<?php $counts['operations']++; ?>
					<?php endif; ?>
				<?php endforeach; ?>
				<tr><td class="center_text"><?php echo $counts['operations']; ?></td></tr>
				<tr><td><a class="toggle_test_emails">Show/Hide g.clemson Emails</a>
					<div class="emails" style="display:none;"><br>
						<?php foreach ($signups as $student): ?>
							<?php if ($student['operations']): ?>
								<?php echo $student['user_id'].'@g.clemson.edu<br>'; ?>
							<?php endif; ?>
						<?php endforeach; ?>
					</div></td></tr>
			</table>
			
			<table class="exam_table">
				<tr><th>Statistics</th></tr>
				<?php foreach ($signups as $student): ?>
					<?php if ($student['statistics']): ?>
						<tr><td><?php echo $student['name'].' ('.strtolower($student['user_id']).')'; ?> <span class="more noprint">&#x25bc;</span><br>
						<div class="student_exam_courses noprint">
							8010: <?php echo $student['8010']; ?><br>
							8040: <?php echo $student['8040']; ?>
						</div></td></tr>
						<?php $counts['statistics']++; ?>
					<?php endif; ?>
				<?php endforeach; ?>
				<tr><td class="center_text"><?php echo $counts['statistics']; ?></td></tr>
				<tr><td><a class="toggle_test_emails">Show/Hide g.clemson Emails</a>
					<div class="emails" style="display:none;"><br>
						<?php foreach ($signups as $student): ?>
							<?php if ($student['statistics']): ?>
								<?php echo $student['user_id'].'@g.clemson.edu<br>'; ?>
							<?php endif; ?>
						<?php endforeach; ?>
					</div></td></tr>
			</table>
			
			<table class="exam_table" style="page-break-after:always;">
				<tr><th>Stochastics</th></tr>
				<?php foreach ($signups as $student): ?>
					<?php if ($student['stochastics']): ?>
						<tr><td><?php echo $student['name'].' ('.strtolower($student['user_id']).')'; ?> <span class="more noprint">&#x25bc;</span><br>
						<div class="student_exam_courses noprint">
							8030: <?php echo $student['8030']; ?><br>
							8170: <?php echo $student['8170']; ?>
						</div></td></tr>
						<?php $counts['stochastics']++; ?>
					<?php endif; ?>
				<?php endforeach; ?>
				<tr><td class="center_text"><?php echo $counts['stochastics']; ?></td></tr>
				<tr><td><a class="toggle_test_emails">Show/Hide g.clemson Emails</a>
					<div class="emails" style="display:none;"><br>
						<?php foreach ($signups as $student): ?>
							<?php if ($student['stochastics']): ?>
								<?php echo $student['user_id'].'@g.clemson.edu<br>'; ?>
							<?php endif; ?>
						<?php endforeach; ?>
					</div></td></tr>
			</table>
			
			<h4 class="noprint"><a id="toggle_emails">Show/Hide Emails</a></h4>
			
			<p id="emails" style="display:none;">
				<?php foreach ($emails as $email): ?>
					<?php echo strtolower($email); ?><br>
				<?php endforeach; ?>
			</p>
			
			<!-- PROFESSORS -->
			
			<h3 style="page-break-before:always;">Professors</h3>
			
			<table class="exam_table">
				<tr>
					<th colspan="2">Algebra</th>
				</tr>
				<tr>
					<th style="font-weight:normal;">8510</th>
					<th style="font-weight:normal;">8530</th>
				</tr>
				<tr>
					<td><?php foreach ($profs['8510'] as $prof): ?>
							<?php echo $prof['prof'].' <span class="inline_count">'.$prof['number'].'</span><br>'; ?>
						<?php endforeach; ?></td>
					<td><?php foreach ($profs['8530'] as $prof): ?>
							<?php echo $prof['prof'].' <span class="inline_count">'.$prof['number'].'</span><br>'; ?>
						<?php endforeach; ?></td>
				</tr>
			</table>
			
			<table class="exam_table">
				<tr>
					<th colspan="2">Analysis</th>
				</tr>
				<tr>
					<th style="font-weight:normal;">8210</th>
					<th style="font-weight:normal;">8220</th>
				</tr>
				<tr>
					<td><?php foreach ($profs['8210'] as $prof): ?>
							<?php echo $prof['prof'].' <span class="inline_count">'.$prof['number'].'</span><br>'; ?>
						<?php endforeach; ?></td>
					<td><?php foreach ($profs['8220'] as $prof): ?>
							<?php echo $prof['prof'].' <span class="inline_count">'.$prof['number'].'</span><br>'; ?>
						<?php endforeach; ?></td>
				</tr>
			</table>
			
			<table class="exam_table">
				<tr>
					<th colspan="2">Comp Math</th>
				</tr>
				<tr>
					<th style="font-weight:normal;">8600</th>
					<th style="font-weight:normal;">8610</th>
				</tr>
				<tr>
					<td><?php foreach ($profs['8600'] as $prof): ?>
							<?php echo $prof['prof'].' <span class="inline_count">'.$prof['number'].'</span><br>'; ?>
						<?php endforeach; ?></td>
					<td><?php foreach ($profs['8610'] as $prof): ?>
							<?php echo $prof['prof'].' <span class="inline_count">'.$prof['number'].'</span><br>'; ?>
						<?php endforeach; ?></td>
				</tr>
			</table>
			
			<table class="exam_table">
				<tr>
					<th colspan="3">OR</th>
				</tr>
				<tr>
					<th style="font-weight:normal;">8100</th>
					<th style="font-weight:normal;">8130</th>
				</tr>
				<tr>
					<td><?php foreach ($profs['8100'] as $prof): ?>
							<?php echo $prof['prof'].' <span class="inline_count">'.$prof['number'].'</span><br>'; ?>
						<?php endforeach; ?></td>
					<td><?php foreach ($profs['8130'] as $prof): ?>
							<?php echo $prof['prof'].' <span class="inline_count">'.$prof['number'].'</span><br>'; ?>
						<?php endforeach; ?></td>
				</tr>
			</table>
			
			<table class="exam_table">
				<tr>
					<th colspan="2">Statistics</th>
				</tr>
				<tr>
					<th style="font-weight:normal;">8010</th>
					<th style="font-weight:normal;">8040</th>
				</tr>
				<tr>
					<td><?php foreach ($profs['8010'] as $prof): ?>
							<?php echo $prof['prof'].' <span class="inline_count">'.$prof['number'].'</span><br>'; ?>
						<?php endforeach; ?></td>
					<td><?php foreach ($profs['8040'] as $prof): ?>
							<?php echo $prof['prof'].' <span class="inline_count">'.$prof['number'].'</span><br>'; ?>
						<?php endforeach; ?></td>
				</tr>
			</table>
			
			<table class="exam_table">
				<tr>
					<th colspan="2">Stochastics</th>
				</tr>
				<tr>
					<th style="font-weight:normal;">8030</th>
					<th style="font-weight:normal;">8170</th>
				</tr>
				<tr>
					<td><?php foreach ($profs['8030'] as $prof): ?>
							<?php echo $prof['prof'].' <span class="inline_count">'.$prof['number'].'</span><br>'; ?>
						<?php endforeach; ?></td>
					<td><?php foreach ($profs['8170'] as $prof): ?>
							<?php echo $prof['prof'].' <span class="inline_count">'.$prof['number'].'</span><br>'; ?>
						<?php endforeach; ?></td>
				</tr>
			</table>
			<br>
			
			
		<?php endif; ?>
	</div>
	
	
		<div id="footer"><a href="/dept_forms/website_error.py" target="_blank">Report a problem with this website</a></div>
	</div>
</body>
</html>