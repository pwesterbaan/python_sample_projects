<?php

if (isset($_SERVER['REMOTE_USER']))
{
	$user_id = strtoupper($_SERVER['REMOTE_USER']);
	$fullName = $_SERVER['fullName'];
}

if (isset($_POST['submit_compensation_plans']))
{
	//echo '<pre>';print_r($_POST);echo '</pre>';
	
	$headers = "MIME-Version: 1.0\r\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
	$headers .= "From: ".$_POST['user_id']."@clemson.edu>\r\n";
	$subject = "Summer Compensation Plans from ".$_POST['fullName'];
	$email_body = '<html><body><p>Summer Compensation Plans from <strong>'.$_POST['fullName'].'</strong></p>';
	
	if ($_POST['receiving_comp'])
	{
		$email_body .= '<p>I am expecting summer compensation.</p>';
		$email_body .= '<p>Funding Sources: ('.count($_POST['funding_source']).')</p>';
		
		foreach ($_POST['funding_source'] as $source)
		{
			$email_body .= '<hr><p>Funding Type: <strong>'.$source['funding_type'].'</strong></p>';
			if ($source['funding_type'] == 'Grant or another external funding source')
			{
				$email_body .= '<p>Grant Account Number: <strong>'.$source['account'].'</strong></p>';
				$email_body .= '<p>Grant PI/Dept Contact (if non-Math &Stat): <strong>'.$source['pi'].'</strong></p>';
				$email_body .= '<p>Total Payout: <strong>'.$source['total_payout'].'</strong></p>';
			}
			if ($source['funding_type'] == 'Instruction')
			{
				$email_body .= '<p>Courses: <strong>'.nl2br($source['courses']).'</strong></p>';
			}
			if ($source['funding_type'] == 'School or College Commitment')
			{
				$email_body .= '<p>Commitment Type: <strong>'.$source['commitment_type'].'</strong></p>';
			}
			$email_body .= '<p>Sessions Expected: <strong>'.$source['sessions_expected'].'</strong></p>';
			$email_body .= '<p>Additional Details: <strong>'.nl2br($source['additional_details']).'</strong></p>';
		}
	}
	else
	{
		$email_body .= '<p>I will not receive summer compensation.</p>';
	}

	$email_body .= '</body></html>';
	
	//echo $email_body;
	$result = mail ('ahayne@clemson.edu', $subject, $email_body, $headers);
	
	if($result){$submission_received = true;}
	else {$submission_received = false;}
}


?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
	"http://www.w3.org/TR/html4/loose.dtd">


<html lang="en-US">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="author" content="hedetni">
	<!-- Date: 2019-5-2 -->
	
	<title>School Forms | Summer Compensation</title>

<link rel="stylesheet" href="/style/math-internal.css" type="text/css" media="screen" charset="utf-8">

<style type="text/css">
.indent {margin-left:1.5em;}
.funding_source {
	border:1px solid lightgray;
	padding:0.5em;
	margin-bottom:1em;
	overflow:auto;
	background-color:#efefef;
}
.remove_link {
	float:right;
}
</style>

<script src="/style/jquery-3.2.1.min.js" type="text/javascript" charset="utf-8"></script>


<script type="text/javascript">
var next_funding_source_num = 2;

function add_funding_source()
{
	var source = `
	<div class="funding_source" id="source_1">
		<a class="remove_link" href="">Remove this Funding Source</a>
		<p><label for="funding_source[${next_funding_source_num}][funding_type]">Funding Source: </label><select name="funding_source[${next_funding_source_num}][funding_type]" id="funding_source[${next_funding_source_num}][funding_type]" class="funding_type">
			<option value="Grant or another external funding source">Grant or another external funding source</option>
			<option value="Instruction">Instruction</option>
			<option value="School or College Commitment">School or College Commitment</option>
		</select></p>
		<br>
		<div class="grant_details">
			<p>
			<label for="funding_source[${next_funding_source_num}][account]">23-digit Account Number:</label> <input type="text" name="funding_source[${next_funding_source_num}][account]" id="funding_source[${next_funding_source_num}][account]"></input><br>
			<label for="funding_source[${next_funding_source_num}][pi]">Grant PI/Dept Contact (if non-Math &Stat):</label> <input type="text" name="funding_source[${next_funding_source_num}][pi]" id="funding_source[${next_funding_source_num}][pi]"></input><br>
			<label for="funding_source[${next_funding_source_num}][total_payout]">Total payout:</label> <input type="text" name="funding_source[${next_funding_source_num}][total_payout]" id="funding_source[${next_funding_source_num}][total_payout]"></input>
			</p>
		</div>
		<div class="instruction_details" style="display:none;">
			<p><label for="funding_source[${next_funding_source_num}][courses]">Please list courses:</label></p>
			<textarea name="funding_source[${next_funding_source_num}][courses]" id="funding_source[${next_funding_source_num}][courses]" rows="5" cols="40"></textarea>
		</div>
		<div class="commitment_details" style="display:none;">
			<p><label for="funding_source[${next_funding_source_num}][commitment_type]">Which type of support are you expecting?<label>
				<select name="funding_source[${next_funding_source_num}][commitment_type]" id="funding_source[${next_funding_source_num}][commitment_type]">
					<option value="Start-up">Start-up</option>
					<option value="Administrative">Administrative</option>
					<option value="Course Coordinator">Course Coordinator</option>
					<option value="Other School/College Commitment">Other School/College Commitment</option>
				</select>
		</div>
		<br>
		<p><label for="funding_source[${next_funding_source_num}][sessions_expected]">Payment is expected: </label>
			<select name="funding_source[${next_funding_source_num}][sessions_expected]" id="funding_source[${next_funding_source_num}][sessions_expected]">
				<option value="First summer session only">First summer session only</option>
				<option value="Second summer session only">Second summer session only</option>
				<option value="Both summer sessions">Both summer sessions</option>
			</select>
		</p>
		<p><label for="funding_source[${next_funding_source_num}][additional_details]">Additional details regarding this funding source:</label></p>
		<textarea name="funding_source[${next_funding_source_num}][additional_details]" id="funding_source[${next_funding_source_num}][additional_details]" rows="5" cols="40"></textarea>
	</div>
	`;
	$('div#add_source').before(source);
	
	next_funding_source_num++;
}



$(document).ready(function(){
	$('input[name="receiving_comp"]').change(function(){
		var receiving_comp = $('input[name="receiving_comp"]:checked').val();
		if(receiving_comp==1)
		{
			$('div#expecting_compensation').slideDown();
		}
		else
		{
			$('div#expecting_compensation').slideUp();
		}
		$('input#submit_compensation_plans').prop("disabled", false);
	});
	
	$(document.body).on('click', '.remove_link' ,function(event){
		$(this).parent('div.funding_source').remove();
		event.preventDefault();
	});
	
	//$('select.funding_type').change(function(){
	$(document.body).on('change', 'select.funding_type' ,function(event){
		var selected = $("option:selected", this).val();
		switch(selected) {
			case "Grant or another external funding source":
				$(this).parent().siblings('div.grant_details').slideDown();
				$(this).parent().siblings('div.instruction_details').slideUp();
				$(this).parent().siblings('div.commitment_details').slideUp();
				break;
			case "Instruction":
				$(this).parent().siblings('div.grant_details').slideUp();
				$(this).parent().siblings('div.instruction_details').slideDown();
				$(this).parent().siblings('div.commitment_details').slideUp();
				break;
			case "School or College Commitment":
				$(this).parent().siblings('div.grant_details').slideUp();
				$(this).parent().siblings('div.instruction_details').slideUp();
				$(this).parent().siblings('div.commitment_details').slideDown();
				break;
			default:
				$(this).parent().siblings('div.grant_details').slideUp();
				$(this).parent().siblings('div.instruction_details').slideUp();
				$(this).parent().siblings('div.commitment_details').slideUp();
				break;
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
			<li><a href="https://www.clemson.edu/science/departments/math-stat/resources/dept-forms.html">Forms</a></li>
		</ul>

		<?php echo isset($message) ? '<div id="message">'.$message.'</div>' : '' ?>
		<?php echo isset($error) ? '<div id="error">'.$error.'</div>' : '' ?>
		
		<div id="content">
			<h1>Summer Compensation Plans</h1>
			
			<?php if(isset($submission_received) && $submission_received): ?>
				<?php if(isset($_POST['receiving_comp']) && $_POST['receiving_comp']): ?>
					<p>Thank you for submitting your summer compensation options. You will be contacted directly by e-mail when your Summer Pay Form is ready for signature.</p>
				<?php else: ?>
					Please download the <a href="https://www.clemson.edu/science/departments/math-stat/documents/protected/no-summer-pay-form.pdf">No Summer Pay Form</a>, enter you name at the top, print your name, sign, and return to April Haynes (or place in her mailbox).
				<?php endif; ?>
				
				<h3>Supporting a student?</h3>
				
				<p>Please complete and submit the following form for each student that you are requesting to be on grant/fellowship/other external funding (continuing or just beginning):<br><a href="https://mthsc.clemson.edu/dept_forms/gs-funding-change-request.php">Grad Student Funding Change Form</a></p>
			<?php elseif (isset($submission_received) && !$submission_received): ?>
			
			<?php elseif (!isset($submission_received)):?>
				<p>Please use this form to notify the school of your summer compensation plans. Your request should be submitted by April 15. To update a previous submission, simply re-submit the form with your updated information.</p>
				<p>Notes:
					<ol>
						<li>Grant funded payments have a longer approval process since the Grants Coordinator, Business Officer, and the Dean have to sign.</li>
						<li>Insurance will be deducted from the May 15th check to cover May, June, & July.</li>
						<li>There is 33.33% cap that applies to each summer session as well as the overall combined summer pay and summer school.</li>
					</ol>
				</p>
				<hr>
				<form name="summer_compensation_form" method="POST" action="">
					<fieldset>
						<legend><p>Are you expecting compensation this summer?</p></legend>
					<p class="indent"><input type="radio" name="receiving_comp" id="receiving_comp_no" value="0"></input> <label for="receiving_comp_no">I will not receive summer compensation</label></p>
					<p class="indent"><input type="radio" name="receiving_comp" id="receiving_comp_yes" value="1"></input> <label for="receiving_comp_yes">I am expecting summer compensation</label></p>
					</fieldset>
				
					<div id="expecting_compensation" style="display:none;">
						<p>Please list all funding sources you expect:</p>
					
						<div class="funding_source" id="source_1">
							<p><label for="funding_source[1][funding_type]">Funding Source: </label><select name="funding_source[1][funding_type]" id="funding_source[1][funding_type]" class="funding_type">
								<option value="Grant or another external funding source">Grant or another external funding source</option>
								<option value="Instruction">Instruction</option>
								<option value="School or College Commitment">School or College Commitment</option>
							</select></p>
							<br>
							<div class="grant_details">
								<p>
								<label for="funding_source[1][account]">23-digit Account Number:</label> <input type="text" name="funding_source[1][account]" id="funding_source[1][account]"></input><br>
								<label for="funding_source[1][pi]">Grant PI/Dept Contact (if non-Math &Stat)</label>: <input type="text" name="funding_source[1][pi]" id="funding_source[1][pi]"></input><br>
								<label for="funding_source[1][total_payout]">Total payout:</label> <input type="text" name="funding_source[1][total_payout]" id="funding_source[1][total_payout]"></input>
								</p>
							</div>
							<div class="instruction_details" style="display:none;">
								<p><label for="funding_source[1][courses]">Please list courses:</label></p>
								<textarea name="funding_source[1][courses]" id="funding_source[1][courses]" rows="5" cols="40"></textarea>
							</div>
							<div class="commitment_details" style="display:none;">
								<p><label for="funding_source[1][commitment_type]">Which type of support are you expecting?</label>
									<select name="funding_source[1][commitment_type]" id="funding_source[1][commitment_type]">
										<option value="Start-up">Start-up</option>
										<option value="Administrative">Administrative</option>
										<option value="Course Coordinator">Course Coordinator</option>
										<option value="Other School/College Commitment">Other School/College Commitment</option>
									</select>
							</div>
							<br>
							<p><label for="funding_source[1][sessions_expected]">Payment is expected: </label>
								<select name="funding_source[1][sessions_expected]" id="funding_source[1][sessions_expected]">
									<option value="First summer session only">First summer session only</option>
									<option value="Second summer session only">Second summer session only</option>
									<option value="Both summer sessions">Both summer sessions</option>
								</select>
							</p>
							<p><label for="funding_source[1][additional_details]">Additional details regarding this funding source:</label></p>
							<textarea name="funding_source[1][additional_details]" id="funding_source[1][additional_details]" rows="5" cols="40"></textarea>
						</div>
					
						<div id="add_source"><a href="javascript:add_funding_source();">+ Add funding source</a></div>
					</div>
					<br>
					<p>
						<input type="hidden" name="user_id" value="<?php echo $user_id;?>"></input>
						<input type="hidden" name="fullName" value="<?php echo $fullName;?>"></input>
						<input type="submit" name="submit_compensation_plans" id="submit_compensation_plans" value="Submit Summer Compensation Plans" disabled></input>
					</p>
				</form>
			<?php endif; ?>
			
			
			
		</div>	
		<div id="footer"><a href="/dept_forms/website_error.py" target="_blank">Report a problem with this website</a></div>
	</div>
	
</body>
</html>