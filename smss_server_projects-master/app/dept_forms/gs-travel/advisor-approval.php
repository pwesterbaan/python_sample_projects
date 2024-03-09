<?php

include('gs-travel-functions.php');

if (isset($_POST['submit_advisor_approval']))
{
	//save inputs
	$details = $_POST;
	$request_id = $_POST['request_id'];
	unset($details['submit_advisor_approval']);

	//update request
	$update_query = $mthsc_db->prepare('UPDATE gs_travel_funding SET advisor_approval = :approval, advisor_approval_timestamp = now(), advisor_comment = :advisor_comment WHERE request_id = :request_id');
	$success = $update_query->execute($details);
	
	if ($success)
	{
		//get request info
		$request_query = $mthsc_db->prepare('SELECT request_id,IF(NOT display_name IS NULL AND display_name != "", display_name, first_name) AS first_name, IF(NOT suffix IS NULL AND suffix != "", CONCAT_WS(" ",last_name,suffix), last_name) AS last_name,username as student_username,student_person_id,advisor_person_id,conference_name,participation_type,location_city,location_state_country,departure_date,return_date,advisor_approval_timestamp FROM `gs_travel_funding` JOIN dept_info.person ON student_person_id = person_id WHERE request_id = ?');
		$request_query->execute(array($request_id));
		$request_info = $request_query->fetch();
		
		if ($details['approval'])
		{
			//email grad coordinator
			$headers = "MIME-Version: 1.0\r\n";
			$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
			$headers .= "From: 'Math Science Grad Studies' <mthgrad@clemson.edu>\r\n";
			$subject = "New Grad Student Travel Funding Request";
			$email = "<html><body>";
			$email .= "<p>A travel funding request has been submitted to the school.</p>";
			$email .= "<p>Student Name: ".$request_info['first_name'].' '.$request_info['last_name']."<br>
							Conference Name: ".$request_info['conference_name']."<br>
							Participation Type: ".$request_info['participation_type']."<br>
							Conference Location: ".$request_info['location_city'].', '.$request_info['location_state_country']."<br>
							Travel Dates: ".date("F j, Y", strtotime($request_info['departure_date'])).' - '.date("F j, Y", strtotime($request_info['return_date']))."</p>";
			$email .= "<p>Advisor Comment: ".$details['advisor_comment']."</p>";
			$email .= "<p>Follow this link to view the request:</p>";
			$email .= "<p>https://mthsc.clemson.edu/dept_forms/gs-travel/admin-view-request.php?id=".$request_id."</p>";
			$email .= "</body></html>";
		
			mail ('mthgrad@clemson.edu', $subject, $email, $headers); //send email
		}
		else
		{
			//email student
			$headers = "MIME-Version: 1.0\r\n";
			$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
			$headers .= "From: 'Math Science Grad Studies' <mthgrad@clemson.edu>\r\n";
			$subject = "Update to Grad Student Travel Funding Request";
			$email = "<html><body>";
			$email .= "<p>This email is to inform you that your recent request for school travel funds has been denied by your advisor. You may revise your entry and submit the request again, as well as see the status of your requests here: https://mthsc.clemson.edu/dept_forms/gs-travel/index.php. Contact your advisor for more information.</p>";
			$email .= "</body></html>";
			
			$get_student_query = $mthsc_db->prepare('SELECT username FROM gs_travel_funding JOIN dept_info.person ON student_person_id = person.person_id WHERE request_id = ?');
			$get_student_query->execute(array($request_id));
			$student_email = $get_student_query->fetchColumn();
		
			mail ($student_email.'@clemson.edu', $subject, $email, $headers); //send email
		}
		
	}
}

if (isset($_GET['request']) && $_GET['request']!="" && is_numeric($_GET['request']))
{
	$request_id = $_GET['request'];
	
	//get person id of logged in user
	$person_id = get_person_id_from_username($user_id);
	
	//get request info
	$request_query = $mthsc_db->prepare('SELECT request_id,IF(NOT display_name IS NULL AND display_name != "", display_name, first_name) AS first_name, IF(NOT suffix IS NULL AND suffix != "", CONCAT_WS(" ",last_name,suffix), last_name) AS last_name,username as student_username,student_person_id,advisor_person_id,conference_name,participation_type,location_city,location_state_country,departure_date,return_date,registration,transportation_origin,airfare,transportation_destination,personal_vehicle,lodging,parking,per_diem,cgsg_requested,cgsg_secured,conference_sponsors_requested,conference_sponsors_secured,faculty_grant_requested,faculty_grant_secured,other_funding_requested,other_funding_secured,total_request,submitted,advisor_approval_timestamp FROM `gs_travel_funding` JOIN dept_info.person ON student_person_id = person_id WHERE request_id = ?');
	$request_query->execute(array($request_id));
	$request_info = $request_query->fetch();
	
	
	$student_info = get_person_info_from_user_id($request_info['student_person_id']);
}



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

<style type="text/css">
label{font-weight:bold;}

</style>

<script src="/style/jquery-3.2.1.min.js" type="text/javascript" charset="utf-8"></script>
<script src="jquery.validate.js"></script>

<script type="text/javascript">
$(document).ready(function() 
{
	$("#advisor_approval_form").validate({
			//debug: true,
			rules: {
				approval: {
					required: true
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
			<?php if ($request_info['advisor_person_id'] != $person_id):?>
				<p id="error">Either the request is invalid or you are not authorized to approve it.</p>
			<?php else: ?>
				<?php if (isset($success) && $success): ?>
					<p>Thank you. Your response has been received and will be routed appropriately.</p>
				<?php elseif (isset($success) && !$success): ?>
					<p id="error">Something went wrong. Please close this page and try again.</p>
				<?php elseif (!isset($success)): ?>
					<?php if ($request_info['advisor_approval_timestamp'] != "0000-00-00 00:00:00"): ?>
						<p id="error">This request has already been processed.</p>
					<?php else: ?>
						<form name="advisor_approval_form" id="advisor_approval_form" method="POST" action="">
							<p>Your pre-approval is needed for the following grad student travel funding request:</p>
						
							<input type="hidden" name="request_id" value="<?php echo $request_info['request_id']; ?>">
						
							<p><label>Student Name</label>: <?php echo $request_info['first_name'].' '.$request_info['last_name']; ?><br>
							<label>Conference Name</label>: <?php echo $request_info['conference_name']; ?><br>
							<label>Participation Type</label>: <?php echo $request_info['participation_type']; ?><br>
							<label>Conference Location</label>: <?php echo $request_info['location_city'].', '.$request_info['location_state_country']; ?><br>
							<label>Travel Dates</label>: <?php echo date("F j, Y", strtotime($request_info['departure_date'])).' - '.date("F j, Y", strtotime($request_info['return_date'])); ?></p>
							
							<table id="expense_table">
								<tr>
									<th>Expenses</th>
									<th>Estimated<br>Amount</th>
								</tr>
								<tr>
									<td>Registration</td>
									<td>$<?php echo $request_info['registration']; ?></td>
								</tr>
								<tr>
									<td>Ground Transportation (origin)<br>
										<small>(If appropriate, use GSP to Clemson: 45 miles, ATL to Clemson: 125 miles)</small></td>
									<td>$<?php echo $request_info['transportation_origin']; ?></td>
								</tr>
								<tr>
									<td>Airfare</td>
									<td>$<?php echo $request_info['airfare']; ?></td>
								</tr>
								<tr>
									<td>Ground Transportation (destination)</td>
									<td>$<?php echo $request_info['transportation_destination']; ?></td>
								</tr>
								<tr>
									<td>Personal Vehicle ($0.535 per mile)</td>
									<td>$<?php echo $request_info['personal_vehicle']; ?></td>
								</tr>
								<tr>
									<td>Lodging</td>
									<td>$<?php echo $request_info['lodging']; ?></td>
								</tr>
								<tr>
									<td>Parking</td>
									<td>$<?php echo $request_info['parking']; ?></td>
								</tr>
								<tr>
									<td>Per Diem<br>
										<small>Estimated at $25/day in-state travel & $32/day out-of-state travel</small></td>
									<td>$<?php echo $request_info['per_diem']; ?></td>
								</tr>
							</table>
							<br>
				
							<table>
								<tr>
									<th>Funding Source</th>
									<th>Amount Requested</th>
									<th>Amount Secured</th>
								</tr>
								<tr>
									<td>CGSG</td>
									<td>$<?php echo $request_info['cgsg_requested']; ?></td>
									<td>$<?php echo $request_info['cgsg_secured']; ?></td>
								</tr>
								<tr>
									<td>Conference Sponsors</td>
									<td>$<?php echo $request_info['conference_sponsors_requested']; ?></td>
									<td>$<?php echo $request_info['conference_sponsors_secured']; ?></td>
								</tr>
								<tr>
									<td>Faculty Grant</td>
									<td>$<?php echo $request_info['faculty_grant_requested']; ?></td>
									<td>$<?php echo $request_info['faculty_grant_secured']; ?></td>
								</tr>
								<tr>
									<td>Other</td>
									<td>$<?php echo $request_info['other_funding_requested']; ?></td>
									<td>$<?php echo $request_info['other_funding_secured']; ?></td>
								</tr>
							</table>
							
							<p style="border:1px solid gray;padding:0.5em;background-color:rgba(255, 255, 255, 0.5)"><label>Total Amount Requested</label>: $<?php echo $request_info['total_request']; ?></p>
							
							<p>Are you aware of the expected travel and do you approve of this request being sent to the school for review?
								<label for="approval" class="error" style="display:none;"></label><br>
								<input type="radio" name="approval" id="approve" value="1"></input> <label for="approve">Yes</label> <br>
								<input type="radio" name="approval" id="deny" value="0"></input> <label for="deny">No</label>
							</p>
							<p><label for="advisor_comment">If you approve, use this space for any comments you would like to be sent to the chair</label>:<br>
								<textarea name="advisor_comment" id="advisor_comment" rows="4" cols="60"></textarea></p>
							<p><input type="submit" name="submit_advisor_approval" value="Submit"></input>
						</form>
					<?php endif; ?>
				<?php endif; ?>
			<?php endif; ?>
			
		</div>	
		<div id="footer"><a href="/dept_forms/website_error.py" target="_blank">Report a problem with this website</a></div>
	</div>
</body>
</html>