<?php

include('speaker-request-functions.php');
if (!isset($_SESSION)){ session_start();}

if (isset($_POST['submit_request_form']))
{
	//save data
	$submission = $_POST;
	unset($submission['submit_request_form']);
	$submission['first_pref_date'] = date("Y-m-d",strtotime($submission['first_pref_date']));
	$submission['second_pref_date'] = date("Y-m-d",strtotime($submission['second_pref_date']));
	if ($submission['modality'] == 'Virtual'){$submission['room_preference'] = '--';}
	
	$keys = array();
	foreach ($submission as $key => $value)
	{
		$keys[] = $key;
	}
	
	$insert_query = $mthsc_db->prepare('INSERT INTO speaker_request ('.implode($keys,',').') VALUES (:'.implode($keys,',:').')');
	$result = $insert_query->execute($submission);
	
	if ($result)
	{
		//get insert id
		$new_request_id = $mthsc_db->lastInsertId();
		
		//send email to admins
		$headers = "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
		$headers .= "From: '".$fullName."' <".$user_id."@clemson.edu>\r\n";
		$subject = "Speaker Request Submitted";
		$email_body = '<html><body><p>Speaker Request from</p> <h2>'.$fullName.'</h2>';
		$email_body .= '<p><strong>Speaker Info</strong></p>';
		$email_body .= '<p>'.$submission['speaker_name'].'<br>';
		$email_body .= $submission['speaker_affiliation'].'<br>';
		$email_body .= $submission['speaker_email'].'</p>';
		$email_body .= '<p><strong>Talk Type</strong></p>';
		$email_body .= '<p>'.$submission['talk_category'];
		if ($submission['talk_category'] == 'Research Group Seminar')
		{$email_body .= ' - '.$submission['research_group'];}
		$email_body .= '</p>';
		$email_body .= '<p><strong>Preferred Dates of Talk</strong></p>';
		$email_body .= '<p>'.$_POST['first_pref_date'].'<br>'.$_POST['second_pref_date'].'</p>';
		
		$email_body .= '<p><a href="https://mthsc.clemson.edu/dept_forms/speaker-request/view-request.php?id='.$new_request_id.'">View the Full Request</a></p>';
		$email_body .= '</body></html>';
		
		mail(implode($notification_list,','), $subject, $email_body, $headers);
		
		//send confirmation email to requestor
		$requestor_headers = "MIME-Version: 1.0\r\n";
		$requestor_headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
		$requestor_headers .= "From: ".$research_chair."\r\n";
		$requestor_subject = "Speaker Request Confirmation";
		$requestor_email_body = '<html><body><p>This email is to confirm your speaker request:</p>';
		$requestor_email_body .= '<p><strong>Speaker Info</strong></p>';
		$requestor_email_body .= '<p>'.$submission['speaker_name'].'<br>';
		$requestor_email_body .= $submission['speaker_affiliation'].'<br>';
		$requestor_email_body .= $submission['speaker_email'].'</p>';
		$requestor_email_body .= '<p><strong>Talk Type</strong></p>';
		$requestor_email_body .= '<p>'.$submission['talk_category'];
		if ($submission['talk_category'] == 'Research Group Seminar')
		{$requestor_email_body .= ' - '.$submission['research_group'];}
		$requestor_email_body .= '</p>';
		$requestor_email_body .= '<p><strong>Preferences:</strong></p>';
		$requestor_email_body .= '<p>First Date Preference: '.$_POST['first_pref_date'].'<br>';
		$requestor_email_body .= 'Alternate Date Preference: '.$_POST['second_pref_date'].'<br>';
		$requestor_email_body .= 'Modality: '.$_POST['modality'].'<br>';
		$requestor_email_body .= 'Preferred Room: '.$_POST['room_preference'].'</p>';
		$requestor_email_body .= '<p><strong>Funding:</strong></p>';
		$requestor_email_body .= '<p>Funding Source: '.$_POST['funding_source'].'<br>';
		$requestor_email_body .= 'Funding Limit: $'.$_POST['funding_limit'].'</p>';
		
		$requestor_email_body .= '<p>You will be contacted when your request has been approved and the talk has been added to the schedule. You may then also need to fill out the visitor approval form.</p>';
		$requestor_email_body .= '</body></html>';
		
		mail($user_id."@clemson.edu", $requestor_subject, $requestor_email_body, $requestor_headers);
		
		//feedback for user
		$_SESSION['message'] = "Request received. You will be contacted when your speaker is scheduled. You may then need to fill out the visitor approval form.";
		http_response_code( 303 );
		header("Location: ".$_SERVER['REQUEST_URI']);
		exit;
	}
	else
	{
		$_SESSION['error'] = "Something went wrong, try submitting again.";
		http_response_code( 303 );
		header("Location: ".$_SERVER['REQUEST_URI']);
		exit;
	}
}


if (isset($_SESSION['message']) )
{
	$message = $_SESSION['message'];
	unset($_SESSION['message']);
	$show_form = false;
}
else if (isset($_SESSION['error']) )
{
	$error = $_SESSION['error'];
	unset($_SESSION['error']);
	$show_form = true;
}
else
{
	$show_form = true;
}


?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
	"http://www.w3.org/TR/html4/loose.dtd">


<html lang="en-US">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="author" content="hedetni">
	<!-- Date: 2019-9-16 -->
	
	<title>School Forms | Speaker Request Form</title>

<link rel="stylesheet" href="/style/math-internal.css" type="text/css" media="screen" charset="utf-8">
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<style type="text/css">
p.leader, legend p.leader {font-weight:bold;}
.indent {margin-left:1em;}
.form_section {margin-bottom:1.5em;}
#more_seminar {margin-left:2em;margin-bottom:1em;background-color:#efefef;border:1px solid #ddd;padding:0.5em 1em;}
span#warning {color:#c60f13;}
label.error {color:#c60f13;}
input.error {border:solid 1px #c60f13;background-color:rgba(198, 15, 19, 0.1);}
</style>

<script src="/style/jquery-3.2.1.min.js" type="text/javascript" charset="utf-8"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="/style/jquery.validate.js"></script>
<script src="/style/validate-additional-methods.js"></script>

<script type="text/javascript">
$(document).ready(function(){
	
	$('.datepicker').datepicker({
			dateFormat: "MM d, yy",
			onClose: function(){$(this).valid();}
	});
		
	$("form#speaker_request_form").validate({
		//debug: true,
		errorContainer: "#warning",
		rules: {
			speaker_name: "required",
			speaker_affiliation: "required",
			speaker_email: {required: true,email: true},
			external: "required",
			first_pref_date: "required",
			second_pref_date: "required",
			modality: "required",
			room_preference: {
				required: function(element){
					return $('input[name="modality"]:checked').val() == "In Person";
				}
			},
			talk_category: "required",
			research_group: {required: "#seminar:checked"},
			funding_source: "required",
			funding_limit: {required: true, number: true}
		}
	});
	
	
	$('input:radio[name="talk_category"]').change(function(){
		if ($('input:radio[name="talk_category"]:checked').val() == "Research Group Seminar")
		{
			$('#more_seminar').show();
		}
		else
		{
			$('#more_seminar').hide();
			$('#research_group option:first').prop('selected',true);
		}
	});
	
}); 
</script>

</head>

<body>
	<div id="main">
		<div id="header">
			<div id="app_title">School Forms</div>
			<a href="http://www.clemson.edu/math" title="School Home"><img src="/style/math_logo.png" alt="school logo"></a>
		</div>
		<ul id="nav">
			<?php echo get_nav(); ?>
		</ul>
		
		
		<div id="content">
			<h1>Speaker Request Form</h1>
			
			<?php echo isset($message) ? '<div id="message">'.$message.'</div>' : '' ?>
			<?php echo isset($error) ? '<div id="error">'.$error.'</div>' : '' ?>
			
			<?php if ($in_math || in_array($user_id,$admin_list)): ?>
				<?php if ($show_form): ?>
					<p>Please use this form to request scheduling a seminar or colloquium speaker for the current academic year. Note that this form is to be submitted <strong>before</strong> the Visitor Approval form, and the latter will be approved <strong>only if</strong> this request is approved and you get a confirmation that the talk is scheduled. You may view <a href="view-talks.php">a list of scheduled talks</a> or the <a href="https://calendar.google.com/calendar/embed?src=g.clemson.edu_ad5mhadpbdsue0f91i495nq5u0@group.calendar.google.com&ctz=America/New_York">Departmental Calendar</a> to check for conflicts.</p>
					<br>
				
				
					<form name="speaker_request_form" id="speaker_request_form" method="POST" action="">
						<div class="form_section">
							<p class="leader">I would like to invite...</p>
		
							<p class="indent"><label for="speaker_name">Speaker Name</label>: <input type="text" size="40" name="speaker_name" id="speaker_name" value=""></input><br>
								<label for="speaker_affiliation">Speaker Affiliation (College, Univ, Business, etc)</label>: <input type="text" size="40" name="speaker_affiliation" id="speaker_affiliation" value=""></input><br>
								<label for="speaker_email">Speaker Email Address</label>: <input type="text" size="40" name="speaker_email" id="speaker_email" value=""></input></p>
						</div>
			
						<div class="form_section">
							<fieldset>
							<legend><p class="leader">Is this speaker external to Clemson (i.e. not a Clemson employee)? <label for="external" class="error" style="display:none;"></label></p></legend>
		
							<p class="indent">
								<input type="radio" name="external" id="external_yes" value="External"></input> <label for="external_yes">Yes, they are <em>external</em> to Clemson</label><br>
								<input type="radio" name="external" id="external_no" value="Internal"></input> <label for="external_no">No, they are <em>internal</em> to Clemson</label>
							</p>
							</fieldset>
						</div>
			
						<div class="form_section">
							<p class="leader">Preferred Dates of talk:</p>
		
							<p class="indent"><label for="first_pref_date">First Date Preference</label>: <input type="text" name="first_pref_date" id="first_pref_date" class="datepicker" value=""></input><br>
								<label for="second_pref_date">Alternate Date Preference</label>: <input type="text" name="second_pref_date" id="second_pref_date" class="datepicker" value=""></input></p>
						</div>
						
						<div class="form_section">
							<fieldset>
							<legend><p class="leader">Will the seminar be held in person or virtually (through Zoom or equivalent remote meeting software)? If both, select In Person. <label for="modality" class="error" style="display:none;"></label></p></legend>
		
							<p class="indent">
								<input type="radio" name="modality" id="in_person" value="In Person"></input> <label for="in_person">In person</label><br>
								<input type="radio" name="modality" id="virtual" value="Virtual"></input> <label for="virtual">Virtual</label>
							</p>
							</fieldset>
						</div>
						
						
						<div class="form_section">
							<fieldset>
							<legend><p class="leader">Preferred Room: <label for="room_preference" class="error" style="display:none;"></label></p></legend>
		
							<p class="indent">
								<input type="radio" name="room_preference" id="m_102" value="M-102"></input> <label for="m_102">M-102</label><br>
								<input type="radio" name="room_preference" id="m_103" value="M-103"></input> <label for="m_103">M-103</label><br>
								<input type="radio" name="room_preference" id="no_room_preference" value="No room preference"></input> <label for="no_room_preference">No room preference</label>
							</p>
							</fieldset>
						</div>
					
						<div class="form_section">
							<fieldset>
							<legend><p class="leader">Category of Talk: <label for="talk_category" class="error" style="display:none;"></label></p></legend>
		
							<p class="indent">
								<input type="radio" name="talk_category" id="colloquium" value="School Colloquium"></input> <label for="colloquium">School Colloquium</label><br>
								<input type="radio" name="talk_category" id="seminar" value="Research Group Seminar"></input> <label for="seminar">Research Group Seminar</label>
							</p>
							</fieldset>
						
							<div class="indent" id="more_seminar" style="display:none;">
								<p><label for="research_group">Choose the primary research group. If this will be a joint talk between multiple groups, then select the "lead" group.</label></p>
							
								<select name="research_group" id="research_group">
									<option value="">Select the primary research group...</option>
									<option value="Algebra and Discrete Math">Algebra and Discrete Math</option>
									<option value="Algebraic Geometry & Number Theory">Algebraic Geometry & Number Theory</option>
									<option value="Analysis">Analysis</option>
									<option value="Computational Math">Computational Math</option>
									<option value="Operations Research">Operations Research</option>
									<option value="RTG CCNT">RTG CCNT</option>
									<option value="Statistics (Pure and Applied)">Statistics (Pure and Applied)</option>
								</select>

							</div>
						
						</div>
		
						<div class="form_section">
							<fieldset>
							<legend><p class="leader">Funding Source: <label for="funding_source" class="error" style="display:none;"></label></p></legend>
		
							<p class="indent">
								<input type="radio" name="funding_source" id="personal_funding" value="Personal"></input> <label for="personal_funding">Personal Funds (incentive, grant, etc)</label><br>
								<input type="radio" name="funding_source" id="school_funding" value="School Funding"></input> <label for="school_funding">Colloquium or Seminar Budget</label>
							</p>
							</fieldset>
						</div>
					
						<div class="form_section">
							<p class="leader">Funding Upper Limit:</p>
							<p class="indent" id="funding_limit_explanation">For the funding upper limit, total ALL anticipated expenses, including those that might be prepaid/direct billed before visitor's arrival. By CU procurement policy, we <strong>cannot</strong> spend more than this amount, so estimate correctly.<br>Standard rates: (Hotel) $100 per night, (Per diem) $35 per day, (Mileage) $0.58 per mile, (Parking) $7 per day, (Shuttle) $70 one-way from GSP</p>
							<p  class="indent"><label for="funding_limit">Upper limit on School Funding:</label> $<input type="text" name="funding_limit" id="funding_limit" size="10"></input></p>
						</div>
		
					
						<?php if (!isset($request)): ?>
							<p class="leader">Submitted by...</p>
							<p class="indent"><em><?php echo $fullName.' ('.$user_id.')';?></em></p>
						<?php endif; ?>
						<br>
						<p>
							<input type="hidden" name="username" value="<?php echo $user_id;?>"></input>
							<input type="submit" name="submit_request_form" value="Submit"></input>
							<input type="reset" value="Reset"></input>
							<span class="container" id="warning" style="display:none;">Errors marked in red must be corrected before continuing</span>
						</p>
		
					</form>
				<?php endif; //end if show form ?>
			<?php else: ?>
				<p>This form is for School of Mathematical and Statistical Sciences use only.</p>
			<?php endif; //end if in math ?>
		</div>	
		<div id="footer"><a href="/dept_forms/website_error.py" target="_blank">Report a problem with this website</a></div>
	</div>
	
</body>
</html>