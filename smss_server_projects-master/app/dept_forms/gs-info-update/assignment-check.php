<?php

//include('dept-info-functions.php');
include('gs-info-functions.php');

if (isset($_POST['submit_update']))
{
	$headers = "MIME-Version: 1.0\r\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
	$headers .= "From: '".$_POST['student_name']."' <".$_POST['username']."@clemson.edu>\r\n";
	$subject = "GS Assignment Check Update";
	
	$email_body = '<html><body><p>GS Assignment Check Update from</p><h2>'.$_POST['student_name'].'</h2>';
	
	$email_body .= '<h3>Assistantships</h3>';
	if ($_POST['assistantships'])
	{
		$email_body .= '<p style="padding-left:1em;">- Confirmed -</p>';
	}
	else
	{
		$email_body .= '<p style="padding-left:1em;">'.$_POST['assistantships_comment'].'</p>';
	}
	
	$email_body .= '<h3>Assignments</h3>';
	if ($_POST['assignments'])
	{
		$email_body .= '<p style="padding-left:1em;">- Confirmed -</p>';
	}
	else
	{
		$email_body .= '<p style="padding-left:1em;">'.$_POST['assignments_comment'].'</p>';
	}
	
	$email_body .= '</body></html>';
	
	
	mail ('jdmcken@clemson.edu,mthgrad@clemson.edu,adimath@clemson.edu,jdyken@clemson.edu', $subject, $email_body, $headers);
	
	$message = 'Thank you, your feedback has been submitted. Please allow time for any changes to be entered into the system.';
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
			$roles = get_roles($person['person_id']);
			$assistantships = get_assistantships($person['person_id']);
			$assignments = get_assignments($person['person_id']);
		
			//database info
			$mthsc_faculty = get_mthsc_faculty();
			$mthsc_courses = get_mthsc_courses();
			$support_types = get_support_types();
			$assignment_types = get_assignment_types();
			
			$current_term = get_current_term();
		}
		else
		{
			$error = "Person ID '".$id."' not in database.";
		}
	}
}
else
{$error = "No person selected";}

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
</style>

<script src="/style/jquery-3.2.1.min.js" type="text/javascript" charset="utf8"></script>
<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.15/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="//cdn.datatables.net/1.10.15/js/jquery.dataTables.js"></script>


<script type="text/javascript">

function check_for_completion()
{
	if($("input[name='assistantships']").is(':checked') &&
		$("input[name='assignments']").is(':checked')
		)
	{
		//check for corrections
		var submittable = true;
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
			<a href="http://www.clemson.edu/math" title="School Home"><img src="/style/math_logo.png" alt="school logo"></a>
		</div>
		

		
		<?php echo isset($message) ? '<div id="message"> '.$message.'</div>' : '' ?>
		<?php echo isset($error) ? '<div id="error"> '.$error.'</div>' : '' ?>
		
		<div id="content">
			<h1>GS Assignment Check</h1>
			
			<?php if ($person && in_array("Student",$roles)): ?>
			
			<p>This form shows you assistantship and assignment information the school currently has on file for the current term. Please confirm or add any information that is incorrect or missing. When you are finished, press 'Submit' at the bottom of the page to send your request to an administrative assistant for review. If necessary, they will update the information as time allows. Please be patient.</p>
			
			<h2>Information for <?php echo $person['first_name'].' '.$person['last_name']; ?></h2>
			
			<form name="info_update_form" method="POST" action="">
				
			<input type="hidden" name="student_name" value="<?php echo $person['first_name'].' '.$person['last_name']; ?>"></input>
			<input type="hidden" name="username" value="<?php echo $person['username']; ?>"></input>
			
			<!-- ASSISTANTSHIP DETAILS -->
			
			<div class="section">
			
				<div class="section_heading">Assistantship Details</div>
				
				<!-- Assistantships -->
				<div class="section_content">
					
					<h3>Assistantships</h3>
					<p>*Current term data</p>
					<table id="assistantship_table">
						<tr>
							<th scope="col">Term</th>
							<th scope="col">Support Type</th>
							<th scope="col">Funding Source</th>
						</tr>
					<?php $asst_count = 0; ?>
					<?php if (count($assistantships)>0): ?>
						<?php foreach ($assistantships as $assistantship): ?>
							<?php if($assistantship['term'] == $current_term): ?>
								<tr>
									<td><?php echo term_ending_to_semester($assistantship['term'])." ".substr($assistantship['term'],0,4); ?></td>
									<td><?php echo $assistantship['support_description']; ?></td>
									<td><?php echo $assistantship['other_funding_source']; ?></td>
								</tr>
								<?php $asst_count++;?>
							<?php endif; ?>
						<?php endforeach; ?>
					<?php endif; ?>
					<?php if($asst_count<1): ?>
						<tr>
							<td colspan="3" class="text-center">No assistantship information on file</td>
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
			</div>
			
			<div class="section">
			
				<div class="section_heading">Assignment Details</div>
				
				<!-- Assignments -->
				<div class="section_content">
					<h3>Assignments</h3>
					<p>*Current term data</p>
						<table id="assignments_table">
						<tr>
							<th scope="col">Term</td>
							<th scope="col">Assignment Type</td>
							<th scope="col">Course</td>
							<th scope="col">Supervisor</td>
						</tr>
					<?php $assign_count = 0; ?>
					<?php if (count($assignments)>0): ?>
						<?php foreach ($assignments as $assignment): ?>
							<?php if($assignment['term'] == $current_term): ?>
								<tr>
									<td><?php echo term_ending_to_semester($assignment['term'])." ".substr($assignment['term'],0,4); ?></td>
									<td><?php echo $assignment['assignment_category'].' - '.$assignment['description']; ?></td>
									<td><?php echo $assignment['course']; ?></td>
									<td><?php echo $assignment['faculty_supervisor']; ?></td>
								</tr>
								<?php $assign_count++;?>
							<?php endif; ?>
						<?php endforeach; ?>
					<?php endif; ?>
					<?php if($assign_count<1): ?>
						<tr>
							<td colspan="4" class="text-center">No assignment information on file</td>
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