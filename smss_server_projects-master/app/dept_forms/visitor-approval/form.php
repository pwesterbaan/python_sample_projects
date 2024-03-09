<?php

include('visitor-approval-functions.php');

if (isset($_GET['id']) && is_numeric($_GET['id']) && $_GET['id'] != "" && $_GET['id'] != 0)
{
	//get request
	$request = get_request_details($_GET['id']);
	//echo '<pre>';var_dump($request);echo '</pre>';
	
	//get comments
	$comments = get_comments_for_request($_GET['id']);
	//echo '<pre>';var_dump($comments);echo '</pre>';
	
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

if (isset($_GET['speaker_id']) && is_numeric($_GET['speaker_id']) && $_GET['speaker_id'] != "" && $_GET['speaker_id'] != 0)
{
	$speaker_request = get_speaker_request_details($_GET['speaker_id']);
	if ($user_id == $speaker_request['username'])
	{
		$request = array();

		$request['visitor_name'] = $speaker_request['speaker_name'];
		$request['visitor_affiliation'] = $speaker_request['speaker_affiliation'];
		$request['visitor_email'] = $speaker_request['speaker_email'];

		if ($speaker_request['talk_category'] == "School Colloquium")
		{$request['purpose_colloquium_speaker'] = 1;}

		if ($speaker_request['talk_category'] == "Research Group Seminar")
		{
			$request['purpose_seminar_speaker'] = 1;
			$request['seminar'] = $speaker_request['research_group'];
		}
	}
}


?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
	"http://www.w3.org/TR/html4/loose.dtd">


<html lang="en-US">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="author" content="hedetni">
	<!-- Date: 2019-5-21 -->
	
	<title>School Forms | Visitor Approval Form</title>

<link rel="stylesheet" href="/style/math-internal.css" type="text/css" media="screen" charset="utf-8">
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<style type="text/css">
p.leader, legend p.leader {font-weight:bold;}
.indent {margin-left:1em;}
.form_section {margin-bottom:1.5em;}
#more_parking,#more_travel,#more_honorarium,#account_section {margin-left:2em;margin-bottom:1em;background-color:#efefef;border:1px solid #ddd;}
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
		
	$("form#visitor_approval_form").validate({
		//debug: true,
		errorContainer: "#warning",
		rules: {
			visitor_name: "required",
			visitor_affiliation: "required",
			visitor_email: {required: true,email: true},
			visitor_phone: "required",
			residency: "required",
			visa_type: {
				required: function(element){
					return $('input:radio[name="residency"]:checked').val() == "Foreign National";
				}
			},
			visit_arrival_date: "required",
			visit_departure_date: "required",
			purpose_seminar_speaker: {
				require_from_group: [1, ".purpose"]
			},
			purpose_colloquium_speaker: {
				require_from_group: [1, ".purpose"]
			},
			purpose_collaborative_research: {
				require_from_group: [1, ".purpose"]
			},
			purpose_prospective_student: {
				require_from_group: [1, ".purpose"]
			},
			purpose_recruiting: {
				require_from_group: [1, ".purpose"]
			},
			purpose_potential_donor: {
				require_from_group: [1, ".purpose"]
			},
			purpose_other_purpose: {
				require_from_group: [1, ".purpose"]
			},
			seminar: {required: "#purpose_seminar_speaker:checked"},
			other_visit_type: {required: "#purpose_other_purpose:checked"},
			travel_arrival_date: {required: "#expenses_travel_lodging:checked"},
			travel_departure_date: {required: "#expenses_travel_lodging:checked"},
			travel_amount: {required: "#expenses_travel_lodging:checked"},
			travel_account: {required: "#expenses_travel_lodging:checked"},
			parking_arrival_date: {required: "#expenses_parking:checked"},
			parking_departure_date: {required: "#expenses_parking:checked"},
			parking_account: {required: "#expenses_parking:checked"},
			//honorarium_amount: {required: "#expenses_honorarium:checked"},
			//honorarium_account: {required: "#expenses_honorarium:checked"},
			visitor_office_dates: {required: "#visitor_office:checked"},
			refreshments_datetime: {required: "#refreshments:checked"}
		},
		groups: {
				purpose: "purpose_seminar_speaker purpose_colloquium_speaker purpose_collaborative_research purpose_prospective_student purpose_recruiting purpose_potential_donor purpose_other_purpose"
			},
	});
	
	$('input:checkbox[name="expenses_travel_lodging"]').change(function(){
		if (this.checked)
		{
			$('div#more_travel').show();
		}
		else
		{
			$('div#more_travel').hide();
			$('div#more_travel input:checkbox').prop('checked', false);
			$('div#more_travel input:text').val('');
		}
	});
	
	$('input:checkbox[name="expenses_parking"]').change(function(){
		if (this.checked)
		{
			$('div#more_parking').show();
		}
		else
		{
			$('div#more_parking').hide();
			$('div#more_parking input:text').val('');
		}
	});
	
	/*
	$('input:checkbox[name="expenses_honorarium"]').change(function(){
		if (this.checked)
		{
			$('div#more_honorarium').show();
		}
		else
		{
			$('div#more_honorarium').hide();
			$('div#more_honorarium input:text').val('');
		}
	});*/
	
	$('input:radio[name="residency"]').change(function(){
		if ($('input:radio[name="residency"]:checked').val() == "Foreign National")
		{
			$('#if_foreign_national').show();
		}
		else
		{
			$('#if_foreign_national').hide();
			$('#visa_type').val('');
		}
	});
	
	$('input#purpose_seminar_speaker').change(function(){
		if (this.checked)
		{
			$('#if_research_seminar').show();
		}
		else
		{
			$('#if_research_seminar').hide();
			$('#seminar').val("");
		}
	});
	
	$('input#purpose_other_purpose').change(function(){
		if (this.checked)
		{
			$('#if_other_purpose').show();
		}
		else
		{
			$('#if_other_purpose').hide();
			$('#other_visit_type').val("");
		}
	});
	
	$('input#visitor_office').change(function(){
		if (this.checked)
		{
			$('#if_visitor_office').show();
		}
		else
		{
			$('#if_visitor_office').hide();
			$('#visitor_office_dates').val("");
		}
	});
	
	$('input#refreshments').change(function(){
		if (this.checked)
		{
			$('#if_refreshments').show();
		}
		else
		{
			$('#if_refreshments').hide();
			$('#refreshments_datetime').val("");
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
			<h1>Visitor Approval Form</h1>
			
			<?php if ($in_math || in_array($user_id,$admin_list)): ?>
				<?php if (isset($_POST['policy_acknowledge']) || (isset($edit_request) && $edit_request)): ?>
					<?php if (isset($request) && !isset($speaker_request)): ?>
						<p class="leader">Editing Request from <?php echo get_name_from_username_hub($request['username']).' ('.$request['username'].')';?></p>
						<p>
							<form name="delete_form" method="POST" action="admin-view-requests.php" onsubmit="return confirm('Are you sure you want to delete this request? This cannot be undone.');">
								<input type="hidden" name="request_to_delete" value="<?php echo $request['request_id'];?>">
								<input type="submit" name="delete_request" value="Delete Request" style="color:red;">
							</form>
						</p>
						<form name="visitor_approval_form" id="visitor_approval_form" method="POST" action="view-request.php?id=<?php echo $request['request_id'];?>">
					<?php else:?>
						<form name="visitor_approval_form" id="visitor_approval_form" method="POST" action="index.php">
					<?php endif;?>
						<div class="form_section">
							<p class="leader">I would like to invite...</p>
			
							<p class="indent"><label for="visitor_name">Visitor Name</label>: <input type="text" size="40" name="visitor_name" id="visitor_name" value="<?php echo isset($request) ? $request['visitor_name'] : "";?>"></input><br>
								<label for="visitor_affiliation">Visitor Affiliation (College, Univ, Business, etc)</label>: <input type="text" size="40" name="visitor_affiliation" id="visitor_affiliation" value="<?php echo isset($request) ? $request['visitor_affiliation'] : "";?>"></input><br>
								<label for="visitor_email">Visitor Email Address</label>: <input type="text" size="40" name="visitor_email" id="visitor_email" value="<?php echo isset($request) ? $request['visitor_email'] : "";?>"></input><br>
								<label for="visitor_phone">Visitor Phone Number</label>: <input type="text" size="40" name="visitor_phone" id="visitor_phone" value="<?php echo isset($request) ? $request['visitor_phone'] : "";?>"></input><br>
								<label for="last_four">Last 4 Digits of Social Security Number</label>: <input type="text" size="10" name="last_four" id="last_four" value="<?php echo (isset($request) && $request['last_four'] != 0) ? $request['last_four'] : "";?>"></input></p>
						</div>
				
						<div class="form_section">
							<fieldset>
							<legend><p class="leader">Visitor's residency status:<label for="residency" class="error" style="display:none;"></label></p></legend>
			
							<p class="indent"><input type="radio" name="residency" id="us_citizen" value="US Citizen or Permanent Resident" <?php echo (isset($request) && $request['residency'] == "US Citizen or Permanent Resident") ? 'checked' : ""; ?> ></input> <label for="us_citizen">US Citizen or Permanent Resident</label><br>
								<input type="radio" name="residency" id="foreign_national" value="Foreign National" <?php echo (isset($request) && $request['residency'] == "Foreign National") ? 'checked' : ""; ?>></input> <label for="foreign_national">Foreign National</label>
							</fieldset>
				
							<p class="indent" id="if_foreign_national" <?php echo (isset($request) && $request['residency'] == "Foreign National") ?  '': 'style="display:none;"';?>><label for="visa_type">Visitor's Visa Type</label>: <input type="text" size="10" name="visa_type" id="visa_type" value="<?php echo isset($request) ? $request['visa_type'] : "";?>"></input></p>
						</div>
				
						<div class="form_section">
							<p class="leader">Dates of visit:</p>
			
							<p class="indent"><label for="visit_arrival_date">Arriving</label>: <input type="text" name="visit_arrival_date" id="visit_arrival_date" class="datepicker" value="<?php echo (isset($request) && $request['visit_arrival_date'] != "1969-12-31") ? $request['visit_arrival_date'] : "";?>"></input><br>
								<label for="visit_departure_date">Departing</label>: <input type="text" name="visit_departure_date" id="visit_departure_date" class="datepicker" value="<?php echo (isset($request) && $request['visit_departure_date'] != "1969-12-31") ? $request['visit_departure_date'] : "";?>"></input></p>
						</div>
				
						<div class="form_section">
							<fieldset>
							<legend><p class="leader">Purpose of visit:<label for="purpose" class="error" style="display:none;"></label></p></legend>
			
							<p class="indent">
								<input type="checkbox" class="purpose" name="purpose_seminar_speaker" id="purpose_seminar_speaker" value="1" <?php echo (isset($request) && $request['purpose_seminar_speaker']) ? 'checked' : ""; ?>></input> <label for="purpose_seminar_speaker">Invited Speaker</label> for Research Seminar<br>
								<span id="if_research_seminar" <?php echo (isset($request) && $request['purpose_seminar_speaker']) ?  '': 'style="display:none;"';?> ><label for="seminar">Which seminar?</label> <input type="text" size="20" name="seminar" id="seminar" value="<?php echo isset($request) ? $request['seminar'] : "";?>"></input><br></span>
								<input type="checkbox" class="purpose" name="purpose_colloquium_speaker" id="purpose_colloquium_speaker" value="1" <?php echo (isset($request) && $request['purpose_colloquium_speaker']) ? 'checked' : ""; ?>></input> <label for="purpose_colloquium_speaker">Invited Speaker for School Colloquium</label><br>
								<input type="checkbox" class="purpose" name="purpose_collaborative_research" id="purpose_collaborative_research" value="1" <?php echo (isset($request) && $request['purpose_collaborative_research']) ? 'checked' : ""; ?>></input> <label for="purpose_collaborative_research">Collaborative Research</label><br>
								<input type="checkbox" class="purpose" name="purpose_prospective_student" id="purpose_prospective_student" value="1" <?php echo (isset($request) && $request['purpose_prospective_student']) ? 'checked' : ""; ?>></input> <label for="purpose_prospective_student">Prospective student</label><br>
								<input type="checkbox" class="purpose" name="purpose_recruiting" id="purpose_recruiting" value="1" <?php echo (isset($request) && $request['purpose_recruiting']) ? 'checked' : ""; ?>></input> <label for="purpose_recruiting">Employment Opportunities (Recruiting)</label><br>
								<input type="checkbox" class="purpose" name="purpose_potential_donor" id="purpose_potential_donor" value="1" <?php echo (isset($request) && $request['purpose_potential_donor']) ? 'checked' : ""; ?>></input> <label for="purpose_potential_donor">Potential Donor</label><br>
								<input type="checkbox" class="purpose" name="purpose_other_purpose" id="purpose_other_purpose" value="1" <?php echo (isset($request) && $request['purpose_other_purpose']) ? 'checked' : ""; ?>></input> <label for="purpose_other_purpose">Other Type of Visitor</label><br>
								<span id="if_other_purpose" <?php echo (isset($request) && $request['purpose_other_purpose']) ?  '': 'style="display:none;"';?> ><label for="other_visit_type">What is the purpose of the visit?</label> <input type="text" size="50" name="other_visit_type" id="other_visit_type" value="<?php echo isset($request) ? $request['other_visit_type'] : "";?>"></input><br></span>
							</p>
							</fieldset>
						</div>
			
						<div class="form_section">
							<p class="leader">Anticipated Expenses:</p>
			
							<p class="indent">
								<input type="checkbox" name="expenses_travel_lodging" id="expenses_travel_lodging" value="1" <?php echo (isset($request) && $request['expenses_travel_lodging']) ? 'checked' : ""; ?>></input> <label for="expenses_travel_lodging">Travel/Lodging</label><br>
								<input type="checkbox" name="expenses_parking" id="expenses_parking" value="1" <?php echo (isset($request) && $request['expenses_parking']) ? 'checked' : ""; ?>></input> <label for="expenses_parking">Parking Pass @ $7/day</label> (<a href="https://www.clemson.edu/campus-life/parking/visitors/" target="_blank">More information about visitor parking</a>)<br>
								<!--<input type="checkbox" name="expenses_honorarium" id="expenses_honorarium" value="1" <?php //echo (isset($request) && $request['expenses_honorarium']) ? 'checked' : ""; ?>></input> <label for="expenses_honorarium">Paid an Honorarium</label>-->
							</p>
			
							<div class="indent" id="more_travel" <?php echo (isset($request) && $request['expenses_travel_lodging']) ?  '': 'style="display:none;"';?> >
								<fieldset>
									<legend class="indent"><strong>Travel Lodging Details</strong></legend>
									<p class="indent">
										<input type="checkbox" name="mileage" id="mileage" value="1" <?php echo (isset($request) && $request['mileage']) ? 'checked' : ""; ?>></input> <label for="mileage">Mileage Reimbursement</label><br>
										<input type="checkbox" name="rental_car" id="rental_car" value="1" <?php echo (isset($request) && $request['rental_car']) ? 'checked' : ""; ?>></input> <label for="rental_car">Rental Car Reimbursement</label><br>
										<input type="checkbox" name="shuttle" id="shuttle" value="1" <?php echo (isset($request) && $request['shuttle']) ? 'checked' : ""; ?>></input> <label for="shuttle">Shuttle Service</label><br>
										<input type="checkbox" name="meals" id="meals" value="1" <?php echo (isset($request) && $request['meals']) ? 'checked' : ""; ?>></input> <label for="meals">Meals Reimbursement</label><br>
										<input type="checkbox" name="airfare_directbill" id="airfare_directbill" value="1" <?php echo (isset($request) && $request['airfare_directbill']) ? 'checked' : ""; ?>></input> <label for="airfare_directbill">Direct Bill Airfare</label><br>
										<input type="checkbox" name="airfare_reimbursement" id="airfare_reimbursement" value="1" <?php echo (isset($request) && $request['airfare_reimbursement']) ? 'checked' : ""; ?>></input> <label for="airfare_reimbursement">Airfare Reimbursement</label><br>
										<input type="checkbox" name="lodging_directbill" id="lodging_directbill" value="1" <?php echo (isset($request) && $request['lodging_directbill']) ? 'checked' : ""; ?>></input> <label for="lodging_directbill">Direct Bill Lodging</label><br>
										<input type="checkbox" name="lodging_reimbursement" id="lodging_reimbursement" value="1" <?php echo (isset($request) && $request['lodging_reimbursement']) ? 'checked' : ""; ?>></input> <label for="lodging_reimbursement">Lodging Reimbursement</label>
									</p>
				
									<p class="indent"><label for="travel_arrival_date">Travel Arrival Date</label>: <input type="text" name="travel_arrival_date" id="travel_arrival_date" class="datepicker" value="<?php echo (isset($request) && $request['travel_arrival_date'] != "1969-12-31") ? $request['travel_arrival_date'] : "";?>"></input><br>
									<label for="travel_departure_date">Travel Departure Date</label>: <input type="text" name="travel_departure_date" id="travel_departure_date" class="datepicker" value="<?php echo (isset($request) && $request['travel_departure_date'] != "1969-12-31") ? $request['travel_departure_date'] : "";?>"></input></p>
									<p class="indent"><label for="travel_amount">Travel/Lodging expense amount not to exceed</label>: <input type="text" name="travel_amount" id="travel_amount" size="20" value="<?php echo isset($request) ? $request['travel_amount'] : "";?>"></input></p>
									<p class="indent"><label for="travel_account">Account Number to be used</label>: <input type="text" name="travel_account" id="travel_account" size="25" value="<?php echo isset($request) ? $request['travel_account'] : "";?>"></input></p>
								</fieldset>
							</div>

							<div class="indent" id="more_parking" <?php echo (isset($request) && $request['expenses_parking']) ?  '': 'style="display:none;"';?> >
								<fieldset>
									<legend class="indent"><strong>Parking Details</strong></legend>
									<p class="indent"><label for="parking_arrival_date">Arrival Date</label>: <input type="text" name="parking_arrival_date" id="parking_arrival_date" class="datepicker" value="<?php echo (isset($request) && $request['parking_arrival_date'] != "1969-12-31") ? $request['parking_arrival_date'] : "";?>"></input><br>
									<label for="parking_departure_date">Departure Date</label>: <input type="text" name="parking_departure_date" id="parking_departure_date" class="datepicker" value="<?php echo (isset($request) && $request['parking_departure_date'] != "1969-12-31") ? $request['parking_departure_date'] : "";?>"></input></p>
									<p class="indent"><label for="parking_account">Account Number to be used</label>: <input type="text" name="parking_account" id="parking_account" size="25" value="<?php echo isset($request) ? $request['parking_account'] : "";?>"></input></p>
								</fieldset>
							</div>
							
							<!--
							<div class="indent" id="more_honorarium" <?php //echo (isset($request) && $request['expenses_honorarium']) ?  '': 'style="display:none;"';?> >
								<fieldset>
									<legend class="indent"><strong>Honorarium Details</strong></legend>
									<p class="indent"><label for="honorarium_amount">Amount</label>: <input type="text" name="honorarium_amount" id="honorarium_amount" value="<?php //echo isset($request) ? $request['honorarium_amount'] : "";?>"></input></p>
									<p class="indent"><label for="honorarium_account">Account Number to be used</label>: <input type="text" name="honorarium_account" id="honorarium_account" size="25" value="<?php //echo isset($request) ? $request['honorarium_account'] : "";?>"></input></p>
								</fieldset>
							</div>-->
						</div>
			
						<div class="form_section">
							<fieldset>
								<legend><p class="leader">Other Requests</p></legend>
			
								<p class="indent"><input type="checkbox" name="visitor_office" id="visitor_office" value="1" <?php echo (isset($request) && $request['visitor_office']) ? 'checked' : ""; ?>></input> <label for="visitor_office">Visitor Office</label></p>
								<p class="indent" id="if_visitor_office" <?php echo (isset($request) && $request['visitor_office']) ?  '': 'style="display:none;"';?> ><label for="visitor_office_dates">Dates Office Needed</label>: <input type="text" size="20" name="visitor_office_dates" id="visitor_office_dates" placeholder="Date(s)" value="<?php echo isset($request) ? $request['visitor_office_dates'] : "";?>"></input></p>
								
								<?php if (isset($request) && $request['refreshments']): // not shown on new requests anymore ?>
								<p class="indent"><input type="checkbox" name="refreshments" id="refreshments" value="1" <?php echo (isset($request) && $request['refreshments']) ? 'checked' : ""; ?>></input> <label for="refreshments">Coffee/Tea</label></p>
								<p class="indent" id="if_refreshments" <?php echo (isset($request) && $request['refreshments']) ?  '': 'style="display:none;"';?>><label for="refreshments_datetime">Date/Time for Coffee/Tea</label>: <input type="text" size="20" name="refreshments_datetime" id="refreshments_datetime" placeholder="Date and Time"  value="<?php echo isset($request) ? $request['refreshments_datetime'] : "";?>" ></input></p>
								<?php endif; ?>
							</fieldset>
						</div>
				
						<div class="form_section">
							<label for="other_request_comments">Other requests/comments:</label><br>
							<textarea name="other_request_comments" id="other_request_comments" rows="3" cols="60"><?php echo isset($request) ? $request['other_request_comments'] : "";?></textarea>
						</div>
			
						
						<?php if (!isset($request)): ?>
							<p class="leader">Submitted by...</p>
							<p class="indent"><em><?php echo $fullName.' ('.$user_id.')';?></em></p>
						<?php endif; ?>
						<br>
						<p>
							<?php if (isset($request) && !isset($speaker_request)): ?>
								<input type="hidden" name="request_id" value="<?php echo $request['request_id'];?>"></input>
								<input type="submit" name="update_approval_form" value="Save Changes"></input>
								<input type="reset" value="Reset"></input>
							<?php else: ?>
								<input type="hidden" name="username" value="<?php echo $user_id;?>"></input>
								<input type="submit" name="submit_approval_form" value="Submit"></input>
								<input type="reset" value="Reset"></input>
							<?php endif; ?>
							<span class="container" id="warning" style="display:none;">Errors marked in red must be corrected before continuing</span>
						</p>
			
					</form>
				<?php else: ?>
					<p><a href="policy.php">Acknowledgement of the visitor policy required</a></p>
				<?php endif; ?> 
			<?php else: ?>
				<p>This form is for School of Mathematical and Statistical Sciences use only.</p>
			<?php endif; ?>
		</div>	
		<div id="footer"><a href="/dept_forms/website_error.py" target="_blank">Report a problem with this website</a></div>
	</div>
	
</body>
</html>