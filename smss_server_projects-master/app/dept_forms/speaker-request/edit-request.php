<?php

include('speaker-request-functions.php');
if (!isset($_SESSION)){ session_start();}


if (isset($_GET['id']) && is_numeric($_GET['id']) && $_GET['id'] != "" && $_GET['id'] != 0)
{
	//get request
	$request = get_request_details($_GET['id']);
	//echo '<pre>';var_dump($request);echo '</pre>';
	
	if (!$request)
	{
		$error = 'Invalid Request ID';
	}
	else
	{
		if (in_array($user_id,$admin_list))
		{
			$edit_request = true;
		}
		else
		{
			$edit_request = false;
			$error = "Cannot edit this request";
		}
	}
}
else
{
	$error = 'Invalid Request';
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
		
	$("form#edit_speaker_request_form").validate({
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
		<?php echo isset($message) ? '<div id="message">'.$message.'</div>' : '' ?>
		<?php echo isset($error) ? '<div id="error">'.$error.'</div>' : '' ?>
		
		<div id="content">
			
				<?php if ($edit_request): ?>
					
					<h1>Speaker Request Form</h1>
					
					<p class="leader">Editing Request from <?php echo get_name_from_username_hub($request['username']).' ('.$request['username'].')';?></p>
					
					<form name="delete_form" method="POST" action="admin-view-requests.php" onsubmit="return confirm('Are you sure you want to delete this request? This cannot be undone.');">
						<p>
							<input type="hidden" name="request_to_delete" value="<?php echo $request['request_id'];?>">
							<input type="submit" name="delete_request" value="Delete Request" style="color:red;">
						</p>
					</form>
					<br>
				
				
					<form name="edit_speaker_request_form" id="edit_speaker_request_form" method="POST" action="view-request.php?id=<?php echo $request['request_id'];?>">
						<div class="form_section">
							<p class="leader">I would like to invite...</p>
		
							<p class="indent"><label for="speaker_name">Speaker Name</label>: <input type="text" size="40" name="speaker_name" id="speaker_name" value="<?php echo $request['speaker_name'];?>"></input><br>
								<label for="speaker_affiliation">Speaker Affiliation (College, Univ, Business, etc)</label>: <input type="text" size="40" name="speaker_affiliation" id="speaker_affiliation" value="<?php echo $request['speaker_affiliation'];?>"></input><br>
								<label for="speaker_email">Speaker Email Address</label>: <input type="text" size="40" name="speaker_email" id="speaker_email" value="<?php echo $request['speaker_email'];?>"></input></p>
						</div>
			
						<div class="form_section">
							<fieldset>
							<legend><p class="leader">Is this speaker external to Clemson (i.e. not a Clemson employee)? <label for="external" class="error" style="display:none;"></label></p></legend>
		
							<p class="indent">
								<input type="radio" name="external" id="external_yes" value="External" <?php echo $request['external'] == "External" ? 'checked' : ''; ?>></input> <label for="external_yes">Yes, they are <em>external</em> to Clemson</label><br>
								<input type="radio" name="external" id="external_no" value="Internal" <?php echo $request['external'] == "Internal" ? 'checked' : ''; ?>></input> <label for="external_no">No, they are <em>internal</em> to Clemson</label>
							</p>
							</fieldset>
						</div>
			
						<div class="form_section">
							<p class="leader">Preferred Dates of talk:</p>
		
							<p class="indent"><label for="first_pref_date">First Date Preference</label>: <input type="text" name="first_pref_date" id="first_pref_date" class="datepicker" value="<?php echo date("F j, Y",strtotime($request['first_pref_date']));?>"></input><br>
								<label for="second_pref_date">Alternate Date Preference</label>: <input type="text" name="second_pref_date" id="second_pref_date" class="datepicker" value="<?php echo date("F j, Y",strtotime($request['second_pref_date']));?>"></input></p>
						</div>
						
						<div class="form_section">
							<fieldset>
							<legend><p class="leader">Will the seminar be held in person or virtually (through Zoom or equivalent remote meeting software)? If both, select In Person. <label for="modality" class="error" style="display:none;"></label></p></legend>
		
							<p class="indent">
								<input type="radio" name="modality" id="in_person" value="In Person" <?php echo $request['modality'] == "In Person" ? 'checked' : ''; ?> ></input> <label for="in_person">In person</label><br>
								<input type="radio" name="modality" id="virtual" value="Virtual" <?php echo $request['modality'] == "Virtual" ? 'checked' : ''; ?>></input> <label for="virtual">Virtual</label>
							</p>
							</fieldset>
						</div>
						
						
						<div class="form_section">
							<fieldset>
							<legend><p class="leader">Preferred Room: <label for="room_preference" class="error" style="display:none;"></label></p></legend>
		
							<p class="indent">
								<input type="radio" name="room_preference" id="m_103" value="M-103" <?php echo $request['room_preference'] == "M-103" ? 'checked' : ''; ?>></input> <label for="m_103">M-103</label><br>
								<input type="radio" name="room_preference" id="m_301" value="M-301" <?php echo $request['room_preference'] == "M-301" ? 'checked' : ''; ?>></input> <label for="m_301">M-301</label><br>
								<input type="radio" name="room_preference" id="no_room_preference" value="No room preference" <?php echo $request['room_preference'] == "No room preference" ? 'checked' : ''; ?>></input> <label for="no_room_preference">No room preference</label>
							</p>
							</fieldset>
						</div>
					
						<div class="form_section">
							<fieldset>
							<legend><p class="leader">Category of Talk: <label for="talk_category" class="error" style="display:none;"></label></p></legend>
		
							<p class="indent">
								<input type="radio" name="talk_category" id="colloquium" value="School Colloquium" <?php echo $request['talk_category'] == "School Colloquium" ? 'checked' : ''; ?> ></input> <label for="colloquium">School Colloquium</label><br>
								<input type="radio" name="talk_category" id="seminar" value="Research Group Seminar" <?php echo $request['talk_category'] == "Research Group Seminar" ? 'checked' : ''; ?> ></input> <label for="seminar">Research Group Seminar</label>
							</p>
							</fieldset>
						
							<div class="indent" id="more_seminar" <?php echo $request['talk_category'] == "School Colloquium" ? 'style="display:none;"' : ''; ?>>
								<p><label for="research_group">Choose the primary research group. If this will be a joint talk between multiple groups, then select the "lead" group.</label></p>
							
								<select name="research_group" id="research_group">
									<option value="">Select the primary research group...</option>
									<option value="Algebra and Discrete Math" <?php echo $request['research_group'] == "Algebra and Discrete Math" ? 'selected' : ''; ?> >Algebra and Discrete Math</option>
									<option value="Algebraic Geometry & Number Theory" <?php echo $request['research_group'] == "Algebraic Geometry & Number Theory" ? 'selected' : ''; ?> >Algebraic Geometry & Number Theory</option>
									<option value="Analysis" <?php echo $request['research_group'] == "Analysis" ? 'selected' : ''; ?> >Analysis</option>
									<option value="Computational Math" <?php echo $request['research_group'] == "Computational Math" ? 'selected' : ''; ?> >Computational Math</option>
									<option value="Operations Research" <?php echo $request['research_group'] == "Operations Research" ? 'selected' : ''; ?> >Operations Research</option>
									<option value="RTG CCNT" <?php echo $request['research_group'] == "RTG CCNT" ? 'selected' : ''; ?> >RTG CCNT</option>
									<option value="Statistics (Pure and Applied)" <?php echo $request['research_group'] == "Statistics (Pure and Applied)" ? 'selected' : ''; ?> >Statistics (Pure and Applied)</option>
								</select>

							</div>
						
						</div>
		
						<div class="form_section">
							<fieldset>
							<legend><p class="leader">Funding Source: <label for="funding_source" class="error" style="display:none;"></label></p></legend>
		
							<p class="indent">
								<input type="radio" name="funding_source" id="personal_funding" value="Personal" <?php echo $request['funding_source'] == "Personal" ? 'checked' : ''; ?> ></input> <label for="personal_funding">Personal Funds (incentive, grant, etc)</label><br>
								<input type="radio" name="funding_source" id="school_funding" value="School Funding" <?php echo $request['funding_source'] == "School Funding" ? 'checked' : ''; ?> ></input> <label for="school_funding">Colloquium or Seminar Budget</label>
							</p>
							</fieldset>
						</div>
					
						<div class="form_section">
							<p class="leader">Funding Upper Limit:</p>
							<p class="indent" id="funding_limit_explanation">For the funding upper limit, total ALL anticipated expenses, including those that might be prepaid/direct billed before visitor's arrival. By CU procurement policy, we <strong>cannot</strong> spend more than this amount, so estimate correctly.<br>Standard rates: (Hotel) $100 per night, (Per diem) $35 per day, (Mileage) $0.58 per mile, (Parking) $7 per day, (Shuttle) $70 one-way from GSP</p>
							<p  class="indent"><label for="funding_limit">Upper limit on School Funding:</label> $<input type="text" name="funding_limit" id="funding_limit" size="10" value="<?php echo $request['funding_limit'];?>"></input></p>
						</div>
		
					
						<?php if (!isset($request)): ?>
							<p class="leader">Submitted by...</p>
							<p class="indent"><em><?php echo $fullName.' ('.$user_id.')';?></em></p>
						<?php endif; ?>
						<br>
						<p>
							<input type="hidden" name="request_id" value="<?php echo $request['request_id'];?>"></input>
							<input type="submit" name="update_speaker_request" value="Save Changes"></input>
							<input type="reset" value="Reset"></input>
							<span class="container" id="warning" style="display:none;">Errors marked in red must be corrected before continuing</span>
						</p>
		
					</form>
				<?php else: ?>
					<p>Access Denied</p>
				<?php endif; //end if show form ?>

		</div>	
		<div id="footer"><a href="/dept_forms/website_error.py" target="_blank">Report a problem with this website</a></div>
	</div>
	
</body>
</html>