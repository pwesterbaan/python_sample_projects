<?php

//include('dept-info-functions.php');
include($_SERVER['DOCUMENT_ROOT'].'/gs-info/gs-info-functions.php');

if (isset($_POST['submit_update']))
{
	$headers = "MIME-Version: 1.0\r\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
	$headers .= "From: '".$_POST['student_name']."' <".$_POST['username']."@clemson.edu>\r\n";
	$subject = "GS Info Update Request";
	
	$email_body = '<html><body><p>GS Info Update Request from </p><h2>'.$_POST['student_name'].'</h2>';
	$email_body .= '<h3>Degree Program</h3>';
	if ($_POST['degree_progress'])
	{
		$email_body .= '<p style="padding-left:1em;">- No changes -</p>';
	}
	else
	{
		$email_body .= '<p style="padding-left:1em;">'.$_POST['degree_progress_comment'].'</p>';
	}
	
	$email_body .= '<h3>Advisors</h3>';
	if ($_POST['advisors'])
	{
		$email_body .= '<p style="padding-left:1em;">- No changes -</p>';
	}
	else
	{
		$email_body .= '<p style="padding-left:1em;">'.$_POST['advisors_comment'].'</p>';
	}
	
	$email_body .= '<h3>Prelims</h3>';
	if ($_POST['prelims'])
	{
		$email_body .= '<p style="padding-left:1em;">- No changes -</p>';
	}
	else
	{
		$email_body .= '<p style="padding-left:1em;">'.$_POST['prelims_comment'].'</p>';
	}
	
	$email_body .= '<h3>Oral Presentations</h3>';
	if ($_POST['oral'])
	{
		$email_body .= '<p style="padding-left:1em;">- No changes -</p>';
	}
	else
	{
		$email_body .= '<p style="padding-left:1em;">'.$_POST['oral_comment'].'</p>';
	}
	
	$email_body .= '<h3>Personal Details</h3>';
	if ($_POST['profile'])
	{
		$email_body .= '<p style="padding-left:1em;">- No changes -</p>';
	}
	else
	{
		$email_body .= '<p style="padding-left:1em;">'.$_POST['profile_comment'].'</p>';
	}
	
	$email_body .= '<h3>Student Details</h3>';
	if ($_POST['student_details'])
	{
		$email_body .= '<p style="padding-left:1em;">- No changes -</p>';
	}
	else
	{
		$email_body .= '<p style="padding-left:1em;">'.$_POST['student_details_comment'].'</p>';
	}
	
	$email_body .= '<h3>Education</h3>';
	if ($_POST['education'])
	{
		$email_body .= '<p style="padding-left:1em;">- No changes -</p>';
	}
	else
	{
		$email_body .= '<p style="padding-left:1em;">'.$_POST['education_comment'].'</p>';
	}
	
	$email_body .= '<h3>Assistantships</h3>';
	if ($_POST['assistantships'])
	{
		$email_body .= '<p style="padding-left:1em;">- No changes -</p>';
	}
	else
	{
		$email_body .= '<p style="padding-left:1em;">'.$_POST['assistantships_comment'].'</p>';
	}
	
	$email_body .= '<h3>Assignments</h3>';
	if ($_POST['assignments'])
	{
		$email_body .= '<p style="padding-left:1em;">- No changes -</p>';
	}
	else
	{
		$email_body .= '<p style="padding-left:1em;">'.$_POST['assignments_comment'].'</p>';
	}
	
	$email_body .= '</body></html>';
	
	
	mail ('mthgrad@clemson.edu,vmcclai@clemson.edu,mathstatadmin@clemson.edu', $subject, $email_body, $headers);
	
	$message = 'Thank you, your corrections have been submitted. Please allow time for them to be entered into the system.';
}


//============================
//  GET INFORMATION TO DISPLAY
//============================
if (isset($user_id))
{
	$id = get_person_id_from_user_id($user_id);
	//$id = 440;
	if ($id != "")
	{
		$person = get_person_details($id);
		$person_id = $person['person_id'];
		
		if ($person)
		{
			//personal details
			$emails = get_emails($person['person_id']);
			$offices = get_offices($person['person_id']);
			$education_list = get_education_list($person['person_id']);
			$phone_numbers = get_phone_numbers($person['person_id']);
			$addresses = get_addresses($person['person_id']);
			$roles = get_roles($person['person_id']);
			$profile = get_student_profile($person['person_id']);
			$programs = get_degree_programs($person['person_id']);
			$advisors = get_advisors($person['person_id']);
			$benchmarks = get_benchmarks($person['person_id']);
			$prelim_attempts = get_prelim_attempts($person['person_id']);
			$comp_attempts = get_comp_attempts($person['person_id']);
			$oral_presentations = get_oral_presentations($person_id);
			$presentation_committee_members = get_presentation_committee_members($person_id);
			$gre = get_gre_score($person['person_id']);
			$jobs = get_employment_records($person['person_id']);
			$assistantships = get_assistantships($person['person_id']);
			$assignments = get_assignments($person['person_id']);
		
			//database info
			$mthsc_faculty = get_mthsc_faculty();
			$mthsc_schools = get_school_list();
			$mthsc_courses = get_mthsc_courses();
			$mthsc_section_options = get_mthsc_section_options();
			$support_types = get_support_types();
			$assignment_types = get_assignment_types();
			$employment_categories = get_employment_categories();
			$oral_presentation_types = get_oral_presentation_types();
			
			$current_term = get_current_term();
		}
		else
		{
			$error = "Person ID '".$id."' not in database.";
		}
	}
}
else
{$error = "User not in database";}

?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
	"http://www.w3.org/TR/html4/loose.dtd">


<html lang="en-US">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="author" content="hedetni">
	<!-- Date: 2017-6-13 -->
	
	<title>GS Info Update</title>

<link rel="stylesheet" href="/style/math-internal.css" type="text/css" media="screen" charset="utf-8">
<link rel="stylesheet" href="dept-info-style.css" type="text/css" media="screen" charset="utf-8">

<style type="text/css">
span.info {font-size:small;}
span.to_delete {margin-right:1em;}
label.radio {color:#333;}
div.section_content {margin-bottom:1.5em;}
div.confirmation {background-color:#eee;padding:0.3em 0.6em;margin-top:0.75em;}
p.correction {display:none;}
h3 {font-weight:normal;}
span#conflict_message{font-size:small;color:red;}
.presentation {border:1px solid #ccc;padding:0.2em 0.5em;margin-bottom:1em;background-color:#efefef;}
.presentation_section {display:inline-block;vertical-align:top;margin-right:2em;margin-bottom:1em;border:1px solid #ccc;padding:0.2em 0.5em;background-color:white}
.presentation_details {max-width:35%;}
h4.presentation_type {
	font-family:'Source Sans Pro',sans-serif;
	font-weight:600;
	text-transform:uppercase;
	font-size:1em;}
@media only screen and (max-width: 1200px) {
.presentation_details {max-width:80%;}
}
@media only screen and (max-width: 980px) {
.presentation_details {max-width:100%;}
}
</style>

<script src="/style/jquery-3.2.1.min.js" type="text/javascript" charset="utf8"></script>
<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.15/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="//cdn.datatables.net/1.10.15/js/jquery.dataTables.js"></script>


<script type="text/javascript">

function check_for_completion()
{
	if($("input[name='degree_progress']").is(':checked') && 
		$("input[name='advisors']").is(':checked') &&
		$("input[name='prelims']").is(':checked') &&
		$("input[name='oral']").is(':checked') &&
		$("input[name='profile']").is(':checked') &&
		$("input[name='education']").is(':checked') &&
		$("input[name='assistantships']").is(':checked') &&
		$("input[name='assignments']").is(':checked')
		)
	{
		//check for corrections
		var submittable = true;
		if($("input[name='degree_progress']:checked").val()==0 && $("textarea[name='degree_progress_comment']").val().length == 0){submittable = false;}
		if($("input[name='advisors']:checked").val()==0 && $("textarea[name='advisors_comment']").val().length == 0){submittable = false;}
		if($("input[name='prelims']:checked").val()==0 && $("textarea[name='prelims_comment']").val().length == 0){submittable = false;}
		if($("input[name='oral']:checked").val()==0 && $("textarea[name='oral_comment']").val().length == 0){submittable = false;}
		if($("input[name='profile']:checked").val()==0 && $("textarea[name='profile_comment']").val().length == 0){submittable = false;}
		if($("input[name='education']:checked").val()==0 && $("textarea[name='education_comment']").val().length == 0){submittable = false;}
		if($("input[name='assistantships']:checked").val()==0 && $("textarea[name='assistantships_comment']").val().length == 0){submittable = false;}
		if($("input[name='assignments']:checked").val()==0 && $("textarea[name='assignments_comment']").val().length == 0){submittable = false;}
		
		if (submittable)
		{
			$('input#submit_button').prop('disabled', false);
			$('span#conflict_message').html("");
		}
		else
		{
			$('input#submit_button').prop('disabled', true);
			$('span#conflict_message').html("Correction(s) missing");
		}
	}
	else //reject
	{
		$('input#submit_button').prop('disabled', true);
		$('span#conflict_message').html("Section(s) missing");
	}
}

$(document).ready(function(){ 
	$('input.is_correct').change(function(){
		if ($(this).val()==0)
		{
			$(this).parent().next('p.correction').slideDown();
		}
		else
		{
			$(this).parent().next('p.correction').slideUp();
		}
		check_for_completion();
	});
	
	$('textarea').keyup(function(){
		check_for_completion();
	});
}); 
</script>

</head>

<body>
	<div id="main">
		<div id="header">
			<div id="app_title">Info Update</div>
			<a href="http://www.clemson.edu/math" title="Department Home"><img src="/style/math_logo.png" alt="math department logo"></a>
		</div>
		
		<?php echo isset($message) ? '<div id="message"> '.$message.'</div>' : '' ?>
		<?php echo isset($error) ? '<div id="error"> '.$error.'</div>' : '' ?>
		
		<div id="content">
			<h1>GS Info Update Form</h1>
			<?php if (isset($person) && $person && (in_array("Student",$roles) || $person['username']=='HEDETNI')): ?>
			
			<p>This form shows you information the school currently has on file. Please update or add any information that is incorrect or missing. When you are finished, press 'Submit' at the bottom of the page to send your request to an administrative assistant for review. They will update the information as time allows. Please be patient.</p>
			
			<h2>Information for <?php echo $person['first_name'].' '.$person['last_name']; ?></h2>
			
			<form name="info_update_form" method="POST" action="">
				
			<input type="hidden" name="student_name" value="<?php echo $person['first_name'].' '.$person['last_name']; ?>"></input>
			<input type="hidden" name="username" value="<?php echo $person['username']; ?>"></input>
			
			<!-- DEGREE PROGRESS -->
			
			<div class="section">
				<div class="section_heading">Degree Progress</div>
				
				<!-- degree program -->
				<div class="section_content">
					<h3>Degree Program</h3>
					<p>If you are doing a MS en route to a PhD, your degree program will be PhD*. Current Degree is the degree you are currently working toward, and should only differ from Program if your program is PhD*.</p>
					<table id="programs_table">
						<tr>
							<td class="field">Program</td>
							<td class="field">Current Degree</td>
							<td class="field">Area</td>
							<td class="field">Start</td>
							<td class="field">End</td>
						</tr>
					<?php if (count($programs)>0): ?>
					
					<?php foreach ($programs as $program): ?>
						<tr>
							<td><?php echo $program['program']; ?></td>
							<td><?php echo $program['cur_degree']; ?></td>
							<td><?php echo $program['area']; ?></td>
							<td><?php echo $program['start_semester'].' '.$program['start_year']; ?></td>
							<td><?php echo $program['end_semester'].' '.$program['end_year']; ?></td>
						</tr>
					<?php endforeach; ?>
					<?php else: ?>
						<tr>
							<td colspan="5">No degree program on file</td>
						</tr>
					<?php endif; ?>
					</table>
					
					
					<div class="confirmation">
						<fieldset>
						<legend><strong>Is this degree program information correct and complete? </strong></legend>
						<p><input type="radio" name="degree_progress" id="degree_progress_yes" class="is_correct"value="1"> <label class="radio" for="degree_progress_yes">Yes</label> 
						<input type="radio" name="degree_progress" id="degree_progress_no" class="is_correct" value="0"> <label class="radio" for="degree_progress_no">No</label></p>
						<p class="correction">
							<label for="degree_progress_comment">What needs correcting?</label><br>
							<textarea name="degree_progress_comment" id="degree_progress_comment" placeholder="What needs correcting?" rows="3" cols="60"></textarea>
						</p>
						</fieldset>
					</div>
				</div>
				
				
				<!-- Advisors -->
				<div class="section_content">
					<h3>Advisors</h3>
					<p>All your advisors, past and present, should be shown here (first, MS, and PhD).</p>
					<?php if (count($advisors)>0): ?>
						<?php foreach ($advisors as $advisor): ?>
							<p class="indent"><?php echo $advisor['first_name'].' '.$advisor['last_name'].' ['.$advisor['advisor_type'].']'; ?></p>
						<?php endforeach; ?>
					<?php else: ?>
						<p>No advisors on file</p>
					<?php endif; ?>
					
					
					<div class="confirmation">
						<fieldset>
						<legend><strong>Is this advisor information correct and complete? </strong></legend>
						<p>
							<input type="radio" name="advisors" id="advisors_yes" class="is_correct"value="1"> <label class="radio" for="advisors_yes">Yes</label> 
							<input type="radio" name="advisors" id="advisors_no" class="is_correct" value="0"> <label class="radio" for="advisors_no">No</label>
						</p>
						<p class="correction">
							<label for="advisors_comment">What needs correcting?</label><br>
							<textarea name="advisors_comment" id="advisors_comment" placeholder="What needs correcting?" rows="3" cols="60"></textarea>
						</p>
						</fieldset>
					</div>
				</div>
				
				
				<!-- Prelims -->
				<div class="section_content">
					<h3>Prelims</h3>
					<p>All your prelims should be listed here, both the ones you passed and the ones you didn't pass.</p>
					<table>
						<tr>
							<td class="field">Area</td>
							<td class="field">Attempted</td>
							<td class="field">Result</td>
						</tr>
					<?php if (count($prelim_attempts)>0): ?>
						<?php foreach ($prelim_attempts as $attempt): ?>
							<tr <?php echo $attempt['result']=="F" ? 'class="failed"' : '' ?>>
								<td><?php echo $attempt['area']; ?></td>
								<td><?php echo $attempt['term'].' '.$attempt['year']; ?></td>
								<td class="text-center"><?php echo $attempt['result']; ?></td>
							</tr>
						<?php endforeach; ?>
					<?php else: ?>
						<tr>
							<td colspan="3">No prelim attempts on file</td>
						</tr>
					<?php endif; ?>
					</table>
					
					
					<div class="confirmation">
						<fieldset>
						<legend><strong>Is this prelim information correct and complete? </strong></legend>
						<p>
							<input type="radio" name="prelims" id="prelims_yes" class="is_correct"value="1"> <label class="radio" for="prelims_yes">Yes</label> 
							<input type="radio" name="prelims" id="prelims_no" class="is_correct" value="0"> <label class="radio" for="prelims_no">No</label>
						</p>
						<p class="correction">
							<label for="prelims_comment">What needs correcting?</label><br>
							<textarea name="prelims_comment" id="prelims_comment" placeholder="What needs correcting?" rows="3" cols="60"></textarea>
						</p>
						</fieldset>
					</div>
					
				</div>
				
				
				<!-- Oral Presentations -->
				<div class="section_content">
					<h3>Oral Presentations</h3>
					<?php if (count($oral_presentations)>0): ?>
					<?php foreach ($oral_presentations as $presentation): ?>
						<div class="presentation" id="presentation_<?php echo $presentation['presentation_id']; ?>">
							<h4 class="presentation_type"><?php echo $presentation['program'].' '.$presentation['presentation_type']; ?></h4>
							
							<!-- DETAILS SECTION -->
							<div class="presentation_details presentation_section">
								<h5>Details:</h5>
								<p>
									<em><?php echo $presentation['title']; ?></em><br>
									<?php echo $presentation['date'] != "" ? date("F j, Y",strtotime($presentation['date'])).'<br>' : ""; ?>
									<?php echo $presentation['location'] != "" ? $presentation['location'].'<br>' : ""; ?>
									<?php echo $presentation['time'] != "" ? $presentation['time'].'<br>' : "";  ?>
								</p>
							</div>
							
							<!-- COMMITTEE SECTION -->
							<div class="presentation_committee presentation_section">
								<h5>Committee Members:</h5>
								<div class="committee_list">
									<?php if (isset($presentation_committee_members[$presentation['presentation_id']]) && count($presentation_committee_members[$presentation['presentation_id']]) > 0): ?>
									<?php foreach ($presentation_committee_members[$presentation['presentation_id']] as $comm_member): ?>
										<p class="indent"><?php echo $comm_member['person_type'] == 'Internal' ? get_name_from_person_id($comm_member['person_id']) : $comm_member['name']; ?> (<?php echo $comm_member['role'];?>)</p>
									<?php endforeach;?>
									<?php endif; ?>
								</div>
							</div>
							
							<!-- RESULTS SECTION -->
							<div class="presentation_results presentation_section">
								<h5>Results:</h5>
								<table>
									<tr><th style="text-align:left;"><?php echo $oral_presentation_assesment_questions[$presentation['presentation_type_id']]['knowledge']; ?></th><td class="text-center"><?php echo $presentation['knowledge_score']; ?></td></tr>
									<tr><th style="text-align:left;"><?php echo $oral_presentation_assesment_questions[$presentation['presentation_type_id']]['research']; ?></th><td class="text-center"><?php echo $presentation['research_score']; ?></td></tr>
									<tr><th style="text-align:left;"><?php echo $oral_presentation_assesment_questions[$presentation['presentation_type_id']]['writing']; ?></th><td class="text-center"><?php echo $presentation['writing_score']; ?></td></tr>
									<tr><th style="text-align:left;"><?php echo $oral_presentation_assesment_questions[$presentation['presentation_type_id']]['oral']; ?></th><td class="text-center"><?php echo $presentation['oral_score']; ?></td></tr>
									<tr><th style="text-align:left;">Result</th><td class="text-center"><?php echo $presentation['result']; ?></td></tr>
								</table>
							</div>
						</div>
					<?php endforeach; ?>
					<?php endif; ?>
					
					<div class="confirmation">
						<fieldset>
						<legend><strong>Is this oral presentation information correct and complete? </strong></legend>
						<p>
							<input type="radio" name="oral" id="oral_yes" class="is_correct"value="1"> <label class="radio" for="oral_yes">Yes</label> 
							<input type="radio" name="oral" id="oral_no" class="is_correct" value="0"> <label class="radio" for="oral_no">No</label>
						</p>
						<p class="correction">
							<label for="oral_comment">What needs correcting?</label><br>
							<textarea name="oral_comment" id="oral_comment" placeholder="What needs correcting?" rows="3" cols="60"></textarea>
						</p>
						</fieldset>
					</div>
					
				</div>
				
			</div>
			
			
			
			<!-- STUDENT PROFILE -->
			
			<div class="section">
				<div class="section_heading">Student Profile</div>
				
				<!-- profile details -->
				<div class="section_content">
					<h3>Personal Details</h3>
					<table id="personal_profile_table">
						<tr>
							<td class="field">Residency</td>
							<td class="field">Home Location</td>
							<td class="field">Ethnicity</td>
							<td class="field">Gender</td>
						</tr>
					<?php if ($profile!=null): ?>
							<tr>
								<td><?php echo $profile['residency']; ?></td>
								<td><?php echo $profile['home_location']; ?></td>
								<td><?php echo $profile['ethnicity']; ?></td>
								<td><?php echo $person['sex'];?></td>
							</tr>
						</table>
					<?php else: ?>
							<tr>
								<td colspan="4">No student profile details on file</td>
							</tr>
					<?php endif; ?>
					
					</table>
					
					<div class="confirmation">
						<fieldset>
						<legend><strong>Is this personal information correct and complete? </strong></legend>
						<p>
							<input type="radio" name="profile" id="profile_yes" class="is_correct"value="1"> <label class="radio" for="profile_yes">Yes</label> 
							<input type="radio" name="profile" id="profile_no" class="is_correct" value="0"> <label class="radio" for="profile_no">No</label>
						</p>
						<p class="correction">
							<label for="profile_comment">What needs correcting?</label><br>
							<textarea name="profile_comment" id="profile_comment" placeholder="What needs correcting?" rows="3" cols="60"></textarea>
						</p>
						</fieldset>
					</div>
					
				</div>
				
				
				<!-- Student Details -->
				<div class="section_content">
					<h3>Student Details</h3>
					
					<p class="indent">MS Breadth Requirement Met: <?php echo $profile['ms_breadth_met'] ? 'Yes' : 'No';?></p>
					<p class="indent">PhD Breadth Requirement Met: <?php echo $profile['phd_breadth_met'] ? 'Yes' : 'No';?></p>
					
					<div class="confirmation">
						<fieldset>
						<legend><strong>Are these details correct and complete? </strong></legend>
						<p>
							<input type="radio" name="student_details" id="student_details_yes" class="is_correct"value="1"> <label class="radio" for="student_details_yes">Yes</label> 
							<input type="radio" name="student_details" id="student_details_no" class="is_correct" value="0"> <label class="radio" for="student_details_no">No</label>
						</p>
						<p class="correction">
							<label for="student_details_comment">What needs correcting?</label><br>
							<textarea name="student_details_comment" id="student_details_comment" placeholder="What needs correcting?" rows="3" cols="60"></textarea>
						</p>
						</fieldset>
					</div>
				</div>
				

				<!-- education -->
				<div class="section_content">
					<h3>Education</h3>
					
					<?php if (count($education_list)>0): ?>
						<?php foreach ($education_list as $degree): ?>
							<p class="indent"><?php echo $degree['degree'].' in '.$degree['major'].' from '.$degree['school'];?> [<?php echo $degree['city'];?>, <?php echo $degree['state']!="" ? $degree['state'] : $degree['country']; ?>], <?php echo $degree['year']; if ($degree['final_gpa']!=''){ echo ' ('.$degree['final_gpa'].')';} ?></p>
						<?php endforeach; ?>
					<?php else: ?>
						<p>No previous degrees on file</p>
					<?php endif; ?>
					
					<div class="confirmation">
						<fieldset>
						<legend><strong>Is this previous education information correct and complete? </strong></legend>
						<p>
							<input type="radio" name="education" id="education_yes" class="is_correct"value="1"> <label class="radio" for="education_yes">Yes</label> 
							<input type="radio" name="education" id="education_no" class="is_correct" value="0"> <label class="radio" for="education_no">No</label>
						</p>
						<p class="correction">
							<label for="education_comment">What needs correcting? Please include degree, major, school (including city, state, country),year, and final gpa.</label><br>
							<textarea name="education_comment" id="education_comment" placeholder="What needs correcting?" rows="3" cols="60"></textarea>
						</p>
						</fieldset>
					</div>
					
				</div>

			</div>
			
			
			
			<!-- ASSISTANTSHIP DETAILS -->
			
			<div class="section">
			
				<div class="section_heading">Assistantship Details</div>
				
				<!-- Assistantships -->
				<div class="section_content">
					
					<h3>Assistantships</h3>
					<p>*Does not include current term data.</p>
					<table id="assistantship_table">
						<tr>
							<td class="field">Term</td>
							<td class="field">Support Type</td>
							<td class="field">Funding Source</td>
						</tr>
					<?php if (count($assistantships)>0): ?>
							<?php foreach ($assistantships as $assistantship): ?>
								<?php if($assistantship['term'] != $current_term): ?>
									<tr>
										<td><?php echo term_ending_to_semester($assistantship['term'])." ".substr($assistantship['term'],0,4); ?></td>
										<td><?php echo $assistantship['support_description']; ?></td>
										<td><?php echo $assistantship['other_funding_source']; ?></td>
									</tr>
								<?php endif; ?>
							<?php endforeach; ?>
					<?php else: ?>
						<tr>
							<td colspan="3">No assistantship information on file</td>
						</tr>
					<?php endif; ?>
					</table>
						
					<div class="confirmation">
						<fieldset>
						<legend><strong>Is this assistantship information correct and complete? </strong></legend>
						<p>
							<input type="radio" name="assistantships" id="assistantships_yes" class="is_correct"value="1"> <label class="radio" for="assistantships_yes">Yes</label> 
							<input type="radio" name="assistantships" id="assistantships_no" class="is_correct" value="0"> <label class="radio" for="assistantships_no">No</label>
						</p>
						<p class="correction">
							<label for="assistantships_comment">What needs correcting?</label><br>
							<textarea name="assistantships_comment" id="assistantships_comment" placeholder="What needs correcting?" rows="3" cols="60"></textarea>
						</p>
						</fieldset>
					</div>
					
				</div>
				
				<!-- Assignments -->
				<div class="section_content">
					<h3>Assignments</h3>
					<p>*Does not include current term data.</p>
						<table id="assignments_table">
						<tr>
							<td class="field">Term</td>
							<td class="field">Assignment Type</td>
							<td class="field">Course</td>
							<td class="field">Supervisor</td>
						</tr>
					<?php if (count($assignments)>0): ?>
							<?php foreach ($assignments as $assignment): ?>
								<?php if($assignment['term'] != $current_term): ?>
									<tr>
										<td><?php echo term_ending_to_semester($assignment['term'])." ".substr($assignment['term'],0,4); ?></td>
										<td><?php echo $assignment['assignment_category'].' - '.$assignment['description']; ?></td>
										<td><?php echo $assignment['course']; ?></td>
										<td><?php echo $assignment['faculty_supervisor']; ?></td>
									</tr>
								<?php endif; ?>
							<?php endforeach; ?>
					<?php else: ?>
								<tr>
									<td colspan="4">No assignment information on file</td>
								</tr>
					<?php endif; ?>
					</table>
						
					<div class="confirmation">
						<fieldset>
						<legend><strong>Is this assignment information correct and complete? </strong></legend>
						<p>
							<input type="radio" name="assignments" id="assignments_yes" class="is_correct"value="1"> <label class="radio" for="assignments_yes">Yes</label> 
							<input type="radio" name="assignments" id="assignments_no" class="is_correct" value="0"> <label class="radio" for="assignments_no">No</label>
						</p>
						<p class="correction">
							<label for="assignments_comment">What needs correcting?</label><br>
							<textarea name="assignments_comment" id="assignments_comment" placeholder="What needs correcting?" rows="3" cols="60"></textarea>
						</p>
						</fieldset>
					</div>
					
				</div>
				
			</div>

			
			
			<input type="submit" value="Submit" name="submit_update" id="submit_button" disabled="true"><span id="conflict_message"></span>
			
			</form>
			
			<?php else:?>
				<p>This form is only for Mathematical Sciences graduate students.</p>
			<?php endif; ?>
		</div>	
		<div id="footer"><a href="/dept_forms/website_error.py" target="_blank">Report a problem with this website</a></div>
	</div>
	
</body>
</html>