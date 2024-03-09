<?php

include('gs-travel-functions.php');

if (isset($_GET['id']) && $_GET['id']!="" && is_numeric($_GET['id']))
{
	$request_id = $_GET['id'];
	
	//get request info
	$request_query = $mthsc_db->prepare('SELECT request_id,IF(NOT display_name IS NULL AND display_name != "", display_name, first_name) AS first_name, IF(NOT suffix IS NULL AND suffix != "", CONCAT_WS(" ",last_name,suffix), last_name) AS last_name,username as student_username,student_person_id,advisor_person_id,conference_name,participation_type,location_city,location_state_country,departure_date,return_date,registration,transportation_origin,airfare,transportation_destination,personal_vehicle,lodging,parking,per_diem,cgsg_requested,cgsg_secured,conference_sponsors_requested,conference_sponsors_secured,faculty_grant_requested,faculty_grant_secured,other_funding_requested,other_funding_secured,total_request,submitted,advisor_approval,advisor_approval_timestamp,advisor_comment,request_approval,request_approval_timestamp,approver_comment,amount_approved FROM `gs_travel_funding` JOIN dept_info.person ON student_person_id = person_id WHERE request_id = ?');
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
span.label{font-weight:bold;}

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
			<h1><a href="admin-view-requests.php">GS Travel Funding Request</a></h1>
		</div>
	
		<div id="content">
			<?php if (isset($request_id)): ?>
				<p><a href="admin-view-requests.php">View All Requests</a></p>
				
				<h2>Status: <?php if ($request_info['advisor_approval_timestamp'] == '0000-00-00 00:00:00'){echo 'Awaiting Advisor';} else if ($request_info['advisor_approval']==0){echo 'Advisor Denied';} else if ($request_info['request_approval_timestamp'] !== '0000-00-00 00:00:00'){echo $request_info['request_approval'] ? 'Approved':'Denied';}else{echo 'Needs Approval';} ?></h2>
				
				<p><span class="label">Student Name</span>: <?php echo $request_info['first_name'].' '.$request_info['last_name']; ?><br>
				<span class="label">Conference Name</span>: <?php echo $request_info['conference_name']; ?><br>
				<span class="label">Participation Type</span>: <?php echo $request_info['participation_type']; ?><br>
				<span class="label">Conference Location</span>: <?php echo $request_info['location_city'].', '.$request_info['location_state_country']; ?><br>
				<span class="label">Travel Dates</span>: <?php echo date("F j, Y", strtotime($request_info['departure_date'])).' - '.date("F j, Y", strtotime($request_info['return_date'])); ?><br>
				<span class="label">Submitted</span>: <?php echo $request_info['submitted']; ?></p>
				
				<p><span class="label">Advisor</span>: <?php echo get_advisor_from_person_id($request_info['student_person_id'])['name']; ?><br>
					<span class="label">Advisor Approves?</span> <?php echo $request_info['advisor_approval_timestamp'] != '0000-00-00 00:00:00' ? $request_info['advisor_approval'] ? 'Yes' : 'No' : 'Awaiting Advisor'; ?><br>
					<span class="label">Advisor's Comments</span>: <?php echo $request_info['advisor_comment']; ?></p>
				
				<table id="expense_table">
					<tr>
						<th scope="col">Expenses</th>
						<th scope="col">Estimated<br>Amount</th>
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
						<td>Personal Vehicle (<?php echo $mileage_rate; ?> per mile)</td>
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
						<th scope="col">Funding Source</th>
						<th scope="col">Amount Requested</th>
						<th scope="col">Amount Secured</th>
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
				<br>
				
				<p style="border:1px solid gray;padding:0.5em;background-color:#f4f3f2;"><span class="label">Total Amount Requested</span>: $<?php echo $request_info['total_request']; ?></p>
				
				<hr>
				<h2>Respond to Request</h2>
				
				<?php if ($request_info['advisor_approval_timestamp'] != '0000-00-00 00:00:00' && $request_info['advisor_approval']): ?>
					
					<form name="request_approval_form" id="request_approval_form" method="POST" action="admin-view-requests.php">
						<input type="hidden" name="request_id" value="<?php echo $request_info['request_id']; ?>"></input>
						<input type="hidden" name="approved_by" value="<?php echo $user_id; ?>"></input>
					
						<p>Do you approve funding for this request?<br>
							<input type="radio" name="approval" id="approve" value="1" <?php echo $request_info['request_approval'] && $request_info['request_approval_timestamp'] != '0000-00-00 00:00:00' ? 'checked' : ""; ?> ></input> <label for="approve">Yes</label> <br>
							<input type="radio" name="approval" id="deny" value="0" <?php echo $request_info['request_approval']==0 && $request_info['request_approval_timestamp'] != '0000-00-00 00:00:00' ? 'checked' : ''; ?> ></input> <label for="deny">No</label>
						</p>
					
						<p><label for="amount_approved">Total Amount Approved</label>: $<input type="text" name="amount_approved" id="amount_approved" size="10" value="<?php echo $request_info['request_approval_timestamp'] != '0000-00-00 00:00:00' ? $request_info['amount_approved'] : '';?>"></input>
					
						<p><label for="approver_comment">Use this space for any comments you would like to be sent to the student</label>:<br>
							<textarea name="approver_comment" id="approver_comment" rows="4" cols="60"><?php echo $request_info['request_approval_timestamp'] != '0000-00-00 00:00:00' ? $request_info['approver_comment'] : '';?></textarea></p>
						
						<p><input type="submit" name="submit_request_approval" value="Submit"></input><br>
							<small>*Submitting will notify the student of the decision as well as the chair and business manager</small></p>
					</form>
				<?php elseif ($request_info['advisor_approval_timestamp'] != '0000-00-00 00:00:00' && !$request_info['advisor_approval']): ?>	
					<p>Advisor Denied Request</p>
				<?php else: ?>
					<p>Awaiting Advisor Response</p>
				<?php endif; ?>
				
				
			<?php endif; ?>
			
		</div>	
		<div id="footer"><a href="/dept_forms/website_error.py" target="_blank">Report a problem with this website</a></div>
	</div>
</body>
</html>