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

//group that gets notified
$admins = array(
'kevja@clemson.edu',
'mthgrad@clemson.edu',
'jdmcken@clemson.edu',
'ahayne@clemson.edu',
'natnat@clemson.edu',
'jdyken@clemson.edu',
'rebholz@clemson.edu',
'adimath@clemson.edu');

//$admins = array('hedetni@clemson.edu');

if (isset($_POST['submit_change_request']))
{
	$entry = $_POST;
	unset($entry['submit_change_request']);
	
	$entry['coming_off_date'] = date("Y-m-d",strtotime($_POST['coming_off_date']));
	
	$insert_query = $mthsc_db->prepare('INSERT INTO grant_funding_change_requests (submitted_by,student_name,grant_name,account_string,grant_end_date,action,going_on_semester,going_on_year,going_on_semester_end,going_on_year_end,support_type,support_level,total_amount,coming_off_date,continue_support,comments) VALUES (:submitted_by,:student_name,:grant_name,:account_string,:grant_end_date,:action,:going_on_semester,:going_on_year,:going_on_semester_end,:going_on_year_end,:support_type,:support_level,:total_amount,:coming_off_date,:continue_support,:comments);');
	
	$insert_query->bindValue(':submitted_by', $entry['submitted_by'], PDO::PARAM_STR);
	$insert_query->bindValue(':student_name', $entry['student_name'], PDO::PARAM_STR);
	$insert_query->bindValue(':grant_name', $entry['grant_name'], PDO::PARAM_STR);
	$insert_query->bindValue(':account_string', $entry['account_string'], PDO::PARAM_STR);
	$insert_query->bindValue(':grant_end_date', $entry['grant_end_date'], PDO::PARAM_STR);
	$insert_query->bindValue(':action', $entry['action'], PDO::PARAM_STR);
	$insert_query->bindValue(':comments', $entry['comments'], PDO::PARAM_STR);
	
	if ($_POST['action']=='on')
	{
		$insert_query->bindValue(':going_on_semester', $entry['going_on_semester'], PDO::PARAM_STR);
		$insert_query->bindValue(':going_on_year', $entry['going_on_year'], PDO::PARAM_STR);
		$insert_query->bindValue(':going_on_semester_end', $entry['going_on_semester_end'], PDO::PARAM_STR);
		$insert_query->bindValue(':going_on_year_end', $entry['going_on_year_end'], PDO::PARAM_STR);
		$insert_query->bindValue(':support_type', $entry['support_type'], PDO::PARAM_STR);
		$insert_query->bindValue(':support_level', $entry['support_level'], PDO::PARAM_STR);
		$insert_query->bindValue(':total_amount', $entry['total_amount'], PDO::PARAM_STR);
		
		$insert_query->bindValue(':coming_off_date', null, PDO::PARAM_INT);
		$insert_query->bindValue(':continue_support', null, PDO::PARAM_INT);
	}
	else if ($_POST['action']=='off')
	{
		$insert_query->bindValue(':going_on_semester', null, PDO::PARAM_INT);
		$insert_query->bindValue(':going_on_year', null, PDO::PARAM_INT);
		$insert_query->bindValue(':going_on_semester_end', null, PDO::PARAM_INT);
		$insert_query->bindValue(':going_on_year_end', null, PDO::PARAM_INT);
		$insert_query->bindValue(':support_type', null, PDO::PARAM_INT);
		$insert_query->bindValue(':support_level', null, PDO::PARAM_INT);
		$insert_query->bindValue(':total_amount', null, PDO::PARAM_INT);
		
		$insert_query->bindValue(':coming_off_date', $entry['coming_off_date'], PDO::PARAM_STR);
		$insert_query->bindValue(':continue_support', $entry['continue_support'], PDO::PARAM_STR);
	}
	
	$result = $insert_query->execute();
	
	if ($result)
	{
		$message = "Your request has been received";
		
		$get_submitter_query = $mthsc_db->prepare('SELECT IF(NOT display_name IS NULL AND display_name != "", display_name, first_name) AS first_name, IF(NOT suffix IS NULL AND suffix != "", CONCAT_WS(" ",last_name,suffix), last_name) AS last_name FROM dept_info.person WHERE username=? LIMIT 1');
		$get_submitter_query->execute(array($_POST['submitted_by']));
		$submitter = $get_submitter_query->fetch();
		
		$headers = "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
		$headers .= "From: ".$_POST['submitted_by']."@clemson.edu>\r\n";
		$subject = "Grant Student Funding Change Request";
		$email_body = '<html><body><p>Grant Student Funding Change Request from <strong>'.$submitter['first_name'].' '.$submitter['last_name'].'</strong></p>';
		$email_body .= '<p>Student Name: <strong>'.$entry['student_name'].'</strong></p>';
		
		if ($entry['action']=="on")
		{
			$email_body .= '<p>Going <strong>ON</strong> grant</p>';
			$email_body .= '<p>Grant Name(s): <br><strong>'.nl2br($entry['grant_name']).'</strong></p>';
			$email_body .= '<p>Grant Account String(s): <br><strong>'.nl2br($entry['account_string']).'</strong></p>';
			$email_body .= '<p>Ending Date of Grant(s): <br><strong>'.nl2br($entry['grant_end_date']).'</strong></p>';
			
			$email_body .= '<p>Starting: <strong>'.$entry['going_on_semester'].' '.$entry['going_on_year'].'</strong></p>';
			$email_body .= '<p>Until: <strong>'.$entry['going_on_semester_end'].' '.$entry['going_on_year_end'].'</strong></p>';
			$email_body .= '<p>Support Type: <strong>'.$entry['support_type'].'</strong></p>';
			$email_body .= '<p>Support Level: <strong>'.$entry['support_level'].'</strong></p>';
			$email_body .= '<p>Total Amount: <strong>'.$entry['total_amount'].'</strong></p>';
		}
		else
		{
			$email_body .= '<p>Coming <strong>OFF</strong> grant</p>';
			$email_body .= '<p>Grant Name(s): <br><strong>'.nl2br($entry['grant_name']).'</strong></p>';
			$email_body .= '<p>Grant Account String(s): <br><strong>'.nl2br($entry['account_string']).'</strong></p>';
			$email_body .= '<p>Ending Date of Grant(s): <br><strong>'.nl2br($entry['grant_end_date']).'</strong></p>';
			
			$email_body .= '<p>Support Ending: <strong>'.$entry['coming_off_date'].'</strong></p>';
			$email_body .= '<p>Should the Department Continue Support? <strong>'.$entry['continue_support'].'</strong></p>';
		}
		
		$email_body .= '<p>Comments: <strong>'.$entry['comments'].'</p>';
		
		//$email_body .= '<p><a href="">View Change Request History</a></p>';
		$email_body .= '</body></html>';
		
		//get admins
		$toSend = $admins;
		//add user
		$toSend[] = $user_id.'@clemson.edu';
		
		$to = implode(',',$toSend); //string of admin array
		
		mail ($to, $subject, $email_body, $headers);
	}
	
}


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Grad Student Funding Change Request Form</title>
	<meta name="author" content="hedetni">
	<!-- Date: 2018-8-13 -->

<link rel="stylesheet" href="/style/math.css" type="text/css" media="screen" title="no title" charset="utf-8">
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<style type="text/css">
div#message {color:#cc0000;font-weight:bold;font-size:large;}

</style>

<script src="/style/jquery-3.2.1.min.js" type="text/javascript" charset="utf-8"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script type="text/javascript">

function check_for_completion()
{
	if (
		$('select#action_select').val() == 'off' && 
		$.trim($("input[name='coming_off_date']").val()) !== "" &&
		$.trim($("input[name='student_name']").val()) !=="" &&
		$.trim($("textarea[name='account_string']").val()) !==""
		)
	{
		$('input#submit_button').prop('disabled', false);
	}
	else if (
			$('select#action_select').val() == 'on' && 
			$.trim($("input[name='student_name']").val()) !=="" &&
			$.trim($("textarea[name='account_string']").val()) !==""
			)
	{
		$('input#submit_button').prop('disabled', false);
	}
	else
	{
		$('input#submit_button').prop('disabled', true);
	}
}

$(document).ready(function() 
{
	$('.datepicker').datepicker({
			dateFormat: "MM d, yy",
			onClose: function(){check_for_completion();}
	});
	
	check_for_completion();
	
	$('select#action_select').change(function(){
		if ($('select#action_select').val()=='on')
		{
			$('div#coming_off').slideUp();
			$('div#going_on').slideDown();
		}
		else if ($('select#action_select').val()=='off')
		{
			$('div#going_on').slideUp();
			$('div#coming_off').slideDown();
		}
		else
		{
			$('div#going_on').slideDown();
			$('div#coming_off').slideDown();
		}
		check_for_completion();
	});
	
	$('select#support_type').change(function(){
		if ($('select#support_type').val()=='Hourly')
		{
			$('select#support_level').val("Hourly");
		}
	});
	
	$("input[name='student_name']").keyup(function(){
		check_for_completion();
	});
	
	$("textarea[name='account_string']").keyup(function(){
		check_for_completion();
	});
	
	$("input[name='coming_off_date']").keyup(function(){
		check_for_completion();
	});
	
});
</script>

</head>
<body>
	<div id="main">
		<div id="header">
			<a href="http://www.clemson.edu/math" title="School Home">
				<img src="/style/math_logo.png" alt="school logo">
			</a>
			<h1>Grad Student Funding Change Request</h1>
		</div>
	
		<div id="content">
			<?php echo isset($message) ? '<div id="message">'.$message.'</div>' : ''; ?>
			
			<p>This form should be used to request a change in funding support for a graduate student.  It should be completed as early as possible but no later than 1 month before a change in support will occur.</p>
			<form name="grant_funding_change_form" action="" method="POST">
				<input type="hidden" name="submitted_by" value="<?php echo $user_id; ?>"></input>
				
				<p><label for="student_name">Student Name:</label> <input type="text" name="student_name" id="student_name" placeholder="Student Name"></input></p>
				
				<p><label for="action_select">Is the student being put ON support external to the department or taken OFF of support external to the department?</label><br>
					<select name="action" id="action_select">
						<option selected disabled value="Not selected">Select an action</option>
						<option value="on">Put ON external support</option>
						<option value="off">Taken OFF external support</option>
					</select>
				</p>
				
				<p><label for="grant_name">Grant Name(s):</label> <br><textarea name="grant_name" id="grant_name" placeholder="Grant Name(s)" rows="4" cols="50"></textarea></p>
				
				<p><label for="account_string">Grant Account String(s):</label> <br><textarea name="account_string" id="account_string" placeholder="Grant Account String(s)" rows="4" cols="50"></textarea></p>
				
				<p><label for="grant_end_date">Ending Date of Grant(s):</label> <br><textarea name="grant_end_date" id="grant_end_date" placeholder="Ending Date of Grant" rows="4" cols="50"></textarea></p>
				
				<div id="going_on" style="display:none;">
					<hr>
					<p>When will the student go on support? <label for="going_on_semester">Semester: </label>
						<select name="going_on_semester" id="going_on_semester">
							<option value="Spring">Full Academic Year 8/16 - 5/15</option>
							<option value="Spring">Spring 1/1 - 5/15</option>
							<option value="Full Summer">Full Summer 5/16 - 8/15</option>
							<option value="First Summer">First Summer Session 5/16 - 6/30</option>
							<option value="Second Summer">Second Summer Session 7/1-8/15</option>
							<option value="Fall">Fall 8/16 - 12/31</option>
						</select>
						<label for="going_on_year">Year: </label>
						<select name="going_on_year" id="going_on_year">
							<option value="<?php echo date("Y",strtotime("now"));?>"><?php echo date("Y",strtotime("now"));?></option>
							<option value="<?php echo date("Y",strtotime("now"))+1;?>"><?php echo date("Y",strtotime("now"))+1;?></option>
							<option value="<?php echo date("Y",strtotime("now"))+2;?>"><?php echo date("Y",strtotime("now"))+2;?></option>
							<option value="<?php echo date("Y",strtotime("now"))+3;?>"><?php echo date("Y",strtotime("now"))+3;?></option>
							<option value="<?php echo date("Y",strtotime("now"))+4;?>"><?php echo date("Y",strtotime("now"))+4;?></option>
						<select>
					</p>
					
					<p>When will the grant support end?
						<label for="going_on_semester_end">Semester: </label>
						<select name="going_on_semester_end" id="going_on_semester_end">
							<option value="Spring">Full Academic Year 8/16 - 5/15</option>
							<option value="Spring">Spring 1/1 - 5/15</option>
							<option value="Full Summer">Full Summer 5/16 - 8/15</option>
							<option value="First Summer">First Summer Session 5/16 - 6/30</option>
							<option value="Second Summer">Second Summer Session 7/1-8/15</option>
							<option value="Fall">Fall 8/16 - 12/31</option>
						</select>
						<label for="going_on_year_end">Year: </label>
						<select name="going_on_year_end" id="going_on_year_end">
							<option value="<?php echo date("Y",strtotime("now"));?>"><?php echo date("Y",strtotime("now"));?></option>
							<option value="<?php echo date("Y",strtotime("now"))+1;?>"><?php echo date("Y",strtotime("now"))+1;?></option>
							<option value="<?php echo date("Y",strtotime("now"))+2;?>"><?php echo date("Y",strtotime("now"))+2;?></option>
							<option value="<?php echo date("Y",strtotime("now"))+3;?>"><?php echo date("Y",strtotime("now"))+3;?></option>
							<option value="<?php echo date("Y",strtotime("now"))+4;?>"><?php echo date("Y",strtotime("now"))+4;?></option>
						<select>
					</p>
					
					<p><label for="support_type">What type of support will the grant provide?</label>
						<select name="support_type" id="support_type">
							<option value="Assistantship">Assistantship</option>
							<option value="Stipend/Fellowship">Stipend/Fellowship</option>
							<option value="Hourly">Hourly</option>
						</select>
					</p>
					
					<p><label for="support_level">What level of support will the grant fund?</label>
						<select name="support_level" id="support_level">
							<option value="Full">Full Assistantship (20 Hrs/wk)</option>
							<option value="Half">Half Assistantship (10 Hrs/wk)</option>
							<option value="Stipend/Fellowship only">Stipend/Fellowship only</option>
							<option value="Stipend/Fellowship with Tuition/Fees Payment">Stipend/Fellowship with Tuition/Fees Payment</option>
							<option value="Hourly">Hourly</option>
						</select>
					</p>
					
					<p><label for="total_amount">What is the total salary to be paid to the student over the support period? Do not include fringe.</label>
						<input type="text" name="total_amount" id="total_amount" placeholder="Total salary"></input>
					</p>
					
				</div>
				
				<div id="coming_off" style="display:none;">
					<hr>
					<p><label for="coming_off_date">When will the student come off support?</label>
						<input type="text" class="datepicker" name="coming_off_date" id="coming_off_date" placeholder="Date support ends"></input>
					</p>
					<p><label for="continue_support">Would you recommend the department continue support for this student?</label>
						<select name="continue_support" id="continue_support">
							<option value="Yes">Yes</option>
							<option value="No">No</option>
						</select>
					</p>
				</div>
				
				<hr>
				
				<p><label for="comments">Additional Comments: </label><br><textarea name="comments" id="comments" rows="3" cols="50" placeholder="Use this space for any additional comments or information"></textarea></p>
				
				<p><input type="submit" name="submit_change_request" value="Submit Change Request" id="submit_button" disabled="true"></input></p>
				
			</form>
			
			
		</div>	
		<div id="footer"><a href="/dept_forms/website_error.py" target="_blank">Report a problem with this website</a></div>
	</div>
</body>
</html>