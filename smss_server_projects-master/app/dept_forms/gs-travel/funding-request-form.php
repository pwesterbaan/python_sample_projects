<?php

include('gs-travel-functions.php');

$person = get_person_info_from_user_id($user_id);
$advisor = get_advisor_from_person_id($person['person_id']);
//$advisor['person_id']=254;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Grad Student Travel Funding Request</title>
	<meta name="author" content="hedetni">
	<!-- Date: 2018-6-21 -->

<link rel="stylesheet" href="/style/math.css" type="text/css" media="screen" title="no title" charset="utf-8">
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<style type="text/css">
label.error {color:red;font-size:smaller;}

</style>

<script src="/style/jquery-3.2.1.min.js" type="text/javascript" charset="utf-8"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="jquery.validate.js"></script>
<script src="validate-additional-methods.js"></script>
<script type="text/javascript">
$(document).ready(function() 
{
	$('.datepicker').datepicker({
		dateFormat: "MM d, yy",
		onClose: function(){$(this).valid();}
	});	
	
	$("#funding_request_form").validate({
			//debug: true,
			rules: {
				conference_name: "required",
				participation_type: "required",
				location_city: "required",
				location_state_country: "required",
				departure_date: {
					required: true,
					date: true
				},
				return_date: {
					required: true,
					date: true
				},
				registration: {
					number: true
				},
				transportation_origin: {
					number: true
				},
				airfare: {
					number: true
				},
				transportation_destination: {
					number: true
				},
				personal_vehicle: {
					number: true
				},
				lodging: {
					number: true
				},
				parking: {
					number: true
				},
				per_diem: {
					number: true
				},
				cgsg_requested: {
					number: true
				},
				cgsg_secured: {
					number: true
				},
				conference_sponsors_requested: {
					number: true
				},
				conference_sponsors_secured: {
					number: true
				},
				faculty_grant_requested: {
					number: true
				},
				faculty_grant_secured: {
					number: true
				},
				other_funding_requested: {
					number: true
				},
				other_funding_secured: {
					number: true
				},
				total_request: {
					required: true,
					number: true
				}
			}
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
			<h1><a href="index.php">GS Travel Funding Request</a></h1>
		</div>
	
		<div id="content">
			<h1>Grad Student Travel Funding Request Form</h1>
			
			<?php if($person['person_id']): ?>
				<form name="funding_request_form" id="funding_request_form" method="POST" action="index.php">
					<p>The following fields are read only and cannot be changed. Contact the graduate coordinator to correct any errors before submitting, as the request will be sent to your advisor for pre-approval.</p>
					<p><label for="name">Name</label>: <input type="text" name="name" id="name" size="40" value="<?php echo $person['full_name'];?>" readonly><br>
					<label for="user_id">User ID</label>: <input type="text" name="user_id" id="user_id" size="10" value="<?php echo $user_id;?>" readonly><br>
					<label for="advisor">Advisor</label>: <input type="text" name="advisor" id="advisor" size="40" value="<?php echo $advisor['name']; ?>" readonly></p>
					<input type="hidden" name="student_person_id" value="<?php echo $person['person_id'];?>"></input>
					<input type="hidden" name="advisor_person_id" value="<?php echo $advisor['person_id']; ?>"></input>
					<hr>
					<?php if ($advisor['person_id']!='0'): ?>
						<p>Please enter the following information related to your travel funds request:</p>
						<p><label for="conference_name">Conference/Meeting Name</label>: <input type="text" name="conference_name" id="conference_name" size="40" value=""></p>
						<p><label for="participation_type">Participation Type</label>: <input type="text" name="participation_type" id="participation_type" size="40" value="" placeholder="Presenter, Attendee, etc..."></p>
						<p>Location: 
							<label for="location_city">City</label><input type="text" name="location_city" id="location_city" size="20" value="" placeholder="City">
							<label for="location_state_country">State/Country</label><input type="text" name="location_state_country" id="location_state_country" size="20" value="" placeholder="State/Country">
						</p>
						<p>Dates: 
							<label for="departure_date">Departure Date</label><input class="datepicker" type="text" placeholder="Departure Date" name="departure_date" id="departure_date" value=""></input>
							<label for="return_date">Return Date</label><input class="datepicker" type="text" placeholder="Return Date" name="return_date" id="return_date" value=""></input>
					
						<fieldset>
						<legend>Please list the expenses you expect to incur as a result of this travel.</legend>
						<table id="expense_table">
							<tr>
								<th scope="col">Expenses</th>
								<th scope="col">Estimated<br>Amount</th>
							</tr>
							<tr>
								<td><label for="registration">Registration</label></td>
								<td>$<input type="text" name="registration" id="registration" size="10" value=""></input></td>
							</tr>
							<tr>
								<td><label for="transportation_origin">Ground Transportation (origin)</label><br>
									<small>(If appropriate, use GSP to Clemson: 45 miles, ATL to Clemson: 125 miles)</small></td>
								<td>$<input type="text" name="transportation_origin" id="transportation_origin" size="10" value=""></input></td>
							</tr>
							<tr>
								<td><label for="airfare">Airfare</label></td>
								<td>$<input type="text" name="airfare" id="airfare" size="10" value=""></input></td>
							</tr>
							<tr>
								<td><label for="transportation_destination">Ground Transportation (destination)</label></td>
								<td>$<input type="text" name="transportation_destination" id="transportation_destination" size="10" value=""></input></td>
							</tr>
							<tr>
								<td><label for="personal_vehicle">Personal Vehicle (<?php echo $mileage_rate; ?> per mile)</label></td>
								<td>$<input type="text" name="personal_vehicle" id="personal_vehicle" size="10" value=""></input></td>
							</tr>
							<tr>
								<td><label for="lodging">Lodging</label></td>
								<td>$<input type="text" name="lodging" id="lodging" size="10" value=""></input></td>
							</tr>
							<tr>
								<td><label for="parking">Parking</label></td>
								<td>$<input type="text" name="parking" id="parking" size="10" value=""></input></td>
							</tr>
							<tr>
								<td><label for="per_diem">Per Diem</label><br>
									<small>Estimated at $25/day in-state travel & $32/day out-of-state travel</small></td>
								<td>$<input type="text" name="per_diem" id="per_diem" size="10" value=""></input></td>
							</tr>
						</table>
						</fieldset>
						<br>
						<fieldset>
						<legend>In the next section, explain what funding you have already sought and secured. These sources of funding should be explored before requesting funds from the school. If you have not requested funds from a source listed, enter "0" for 'Amount Requested'. In the 'Amount Secured' field, enter the funding amount the source has approved, or "0" if that request is pending or has been denied.</legend>
						<table>
							<tr>
								<th scope="col">Funding Source</th>
								<th scope="col" id="requested">Amount Requested</th>
								<th scope="col" id="secured">Amount Secured</th>
							</tr>
							<tr>
								<td id="cgsg">CGSG</td>
								<td>$<input type="text" name="cgsg_requested" aria-labelledby="cgsg requested" size="10" value=""></input></td>
								<td>$<input type="text" name="cgsg_secured" aria-labelledby="cgsg secured" size="10" value=""></input></td>
							</tr>
							<tr>
								<td id="conference_sponsors">Conference Sponsors</td>
								<td>$<input type="text" name="conference_sponsors_requested" aria-labelledby="conference_sponsors requested" size="10" value=""></input></td>
								<td>$<input type="text" name="conference_sponsors_secured" aria-labelledby="conference_sponsors secured" size="10" value=""></input></td>
							</tr>
							<tr>
								<td id="faculty_grant">Faculty Grant</td>
								<td>$<input type="text" name="faculty_grant_requested" aria-labelledby="faculty_grant requested" size="10" value=""></input></td>
								<td>$<input type="text" name="faculty_grant_secured" aria-labelledby="faculty_grant secured" size="10" value=""></input></td>
							</tr>
							<tr>
								<td id="other_funding">Other</td>
								<td>$<input type="text" name="other_funding_requested" aria-labelledby="other_funding requested" size="10" value=""></input></td>
								<td>$<input type="text" name="other_funding_secured" aria-labelledby="other_funding secured" size="10" value=""></input></td>
							</tr>
						</table>
						</fieldset>
						<br>
						<p><strong><label for="total_request">Total Request from School</label></strong>:<br>$<input type="text" name="total_request" id="total_request" size="10" value=""></input>
				
				
				
						<p><input type="submit" name="submit_funding_request" value="Submit Request"></input></p>
					<?php else: ?>
						<p>You must have an advisor on file to submit the form</p>
					<?php endif; ?>
				</form>
			<?php else: ?>
				<p>Only members of the School of Mathematical and Statistical Sciences may use this form.</p>
			<?php endif; ?>
		</div>	
		<div id="footer"><a href="/dept_forms/website_error.py" target="_blank">Report a problem with this website</a></div>
	</div>
</body>
</html>