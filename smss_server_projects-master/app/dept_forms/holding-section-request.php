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

$banner_user = 'math_sciences';
$banner_pass = 'Abel1aN!sleiN';

$dsn = 'oci:dbname=//unidb02.clemson.edu:1521/pedwsods;charset='.$charset;
$banner_db = new PDO($dsn, $banner_user, $banner_pass, $opt);

date_default_timezone_set('America/New_York');

//returns term code for current term based on month
function get_current_term()
{
	$year = date('Y',strtotime('now'));
	$month = date('m',strtotime('now'));
	switch($month)
	{
		case '01':
			$term = $year.'01';
			break;
		case '02':
			$term = $year.'01';
			break;
		case '03':
			$term = $year.'01';
			break;
		case '04':
			$term = $year.'01';
			break;
		case '05':
			$term = $year.'05';
			break;
		case '06':
			$term = $year.'05';
			break;
		case '07':
			$term = $year.'05';
			break;
		default: 
			$term = $year.'08';
			break;
	}
	return $term; 
}

function get_previous_term($current_term)
{
	$current_semester = substr($current_term,-2);
	$current_year = substr($current_term,0,4);
	switch($current_semester)
	{
		case '01':
			$previous_semester = '08';
			$previous_year = $current_year - 1;
			break;
		case '05':
			$previous_semester = '01';
			$previous_year = $current_year;
			break;
		case '08':
			$previous_semester = '05';
			$previous_year = $current_year;
			break;
	}
	return $previous_year.$previous_semester;
}

function get_next_term($current_term)
{
	$current_semester = substr($current_term,-2);
	$current_year = substr($current_term,0,4);
	switch($current_semester)
	{
		case '01':
			$next_semester = '05';
			$next_year = $current_year;
			break;
		case '05':
			$next_semester = '08';
			$next_year = $current_year;
			break;
		case '08':
			$next_semester = '01';
			$next_year = $current_year+1;
			break;
	}
	return $next_year.$next_semester;
}

function term_ending_to_semester($term_ending)
{
	switch(substr($term_ending,-2))
	{
		case '01':
			$semester = "Spring";
			break;
		case '05':
			$semester = "Summer";
			break;
		case '08':
			$semester = "Fall";
			break;
	}
	return $semester;
}

if (isset($_SERVER['REMOTE_USER']))
{
	$user_id = strtoupper($_SERVER['REMOTE_USER']);
	$xid = $_SERVER['clemsonXID'];
	$first_name = $_SERVER['givenName'];
	$last_name = $_SERVER['sn'];
	
	$xid = 'C10319634';
	
	$current_term = get_current_term();
	$next_term = get_next_term($current_term);
	
	//get math class section
	$math_class_query = $banner_db->prepare("SELECT * FROM SIS_MTHSC_STUDENT_REGISTRATION WHERE xid = ? AND term_code = ? AND section_number in (299,888,999) AND subject_code in ('MATH','STAT')");
	$math_class_query->execute(array($xid,$next_term));
	$math_class = $math_class_query->fetch();
}


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Math/Stat Holding Section Placement Request</title>
	<meta name="author" content="hedetni">
	<!-- Date: 2018-12-14 -->

<link rel="stylesheet" href="/style/math.css" type="text/css" media="screen" title="no title" charset="utf-8">

<style type="text/css">
.indent {margin-left:1em;}

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
			<a href="http://www.clemson.edu/math" title="School Home">
				<img src="/style/math_logo.png" alt="school logo">
			</a>
			<h1><a href="index.php">Holding Section to Real Section</a></h1>
		</div>
	
		<div id="content">
			<?php if (!isset($_POST['submit_request'])): ?>
			<p>This form will be used to place students that are currently in a holding section (888,999,299) of MATH or STAT into a real, timed section. Only students that meet the prerequisites will be moved. Our only goal is to register students for a section that fits in their school schedule. We cannot accommodate time/professor requests.</p>
			
			<form name="holding_section_request_form" method="POST" action="">
				<p>Name: <input type="text" name="name" readonly value="<?php echo $first_name.' '.$last_name; ?>"></input></p>
				<p>CU XID: <input type="text" name="xid" readonly value="<?php echo $xid; ?>"></input></p>
				<p>Email Address (including @clemson.edu or @g.clemson.edu): <input type="text" name="email" value=""></input></p>
				
				<p>You are registered for the following class for <?php echo term_ending_to_semester(substr($next_term,-2)).' '.substr($next_term,0,4); ?>: <strong><?php echo $math_class['SUBJECT_CODE'].' '.$math_class['COURSE_NUMBER'].' Section '.$math_class['SECTION_NUMBER']; ?></strong></p>
				
				<p>Is this the the same course you need to be added to now (placed in a real section)?</p>
				
				<p class="indent">
					<input type="radio" name="placement_for" value="<?php echo $math_class['SUBJECT_CODE'].' '.$math_class['COURSE_NUMBER']; ?>"></input> Yes <br>
					<input type="radio" name="placement_for" value="MATH 1020"></input> No, I now need MATH 1020 (Business Calc 1)<br>
					<input type="radio" name="placement_for" value="MATH 1040"></input> No, I now need MATH 1040 (First Semester of Long Calculus) - requires a 65 on the CMPT<br>
					<input type="radio" name="placement_for" value="MATH 1060"></input> No, I now need MATH 1060 (Calc 1) - Must have a CMPT score of at least  80 to be moved to a real section<br>
					<input type="radio" name="placement_for" value="MATH 1080"></input> No, I now need MATH 1080 (Calc 2)<br>
					<input type="radio" name="placement_for" value="MATH 2060"></input> No, I now need MATH 2060 (Calc 3)<br>
					<input type="radio" name="placement_for" value="MATH 2070"></input> No, I now need MATH 2070 (Business Calc 2)<br>
					<input type="radio" name="placement_for" value="Other"></input> No, I now need another MATH or STAT course than listed here (specify below)<br>
					<input type="radio" name="placement_for" value="None"></input> No, I will no longer be taking a MATH/STAT class and will drop myself<br>
				</p>
				
				<p>Did you register for the holding section because you were/are waiting for AP,IB,transfer credit or an updated CMPT score?</p>
				
				<p class="indent">
					<input type="radio" name="reason" value="AP"></input> AP Credit<br>
					<input type="radio" name="reason" value="IB"></input> IB higher level credit (Clemson doesn't accept standard level)<br>
					<input type="radio" name="reason" value="Transfer Ready"></input> Transfer credit that should be here by now<br>
					<input type="radio" name="reason" value="Transfer Credit Soon"></input> Transfer credit that will be here within the next few weeks<br>
					<input type="radio" name="reason" value="Transfer Credit Later"></input> Transfer credit for a course I haven't completed yet<br>
					<input type="radio" name="reason" value="Updated CMPT"></input> An updated CMPT Scores of 80 or above for Math 1060<br>
				</p>
				
				<p>If you had a choice, when would you prefer to take this course? (We will try to accommodate where possible, but this is not a guarantee)</p>
				
				<p class="indent">
					<input type="radio" name="time_preference" value="Morning"></input> Morning<br>
					<input type="radio" name="time_preference" value="Midday"></input> Midday<br>
					<input type="radio" name="time_preference" value="Afternoon"></input> Afternoon<br>
					<input type="radio" name="time_preference" value="No preference"></input> No preference; just thankful for a seat<br>
				</p>
				
				<p>Do you have any special school related schedule requirements? For example do you need to be in the honors section, are you part of RiSE, are you an athlete who practice schedule we need to work around, etc? This does NOT include students that have a time/professor request. *Students that need another course other than those listed above, please specify here.</p>
				
				<textarea name="preference" rows="3" cols="50" class="indent"></textarea>
				
				<p><input type="hidden" name="term" value="<?php echo $next_term; ?>"></input>
				<input type="submit" name="submit_request" value="Submit Request"></input></p>
				
			</form>
			<?php else: ?>
				
				
			<?php endif; ?>
		</div>	
		<div id="footer">Click <a href="/dept_forms/website_error.py" target="_blank">here</a> to report a problem with this website.</div>
	</div>
</body>
</html>