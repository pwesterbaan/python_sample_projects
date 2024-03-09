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
label{font-weight:bold;}

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
				<p><a href="index.php">My Requests</a></p>
				<h2>Request Details</h2>
				
				<p><label>Status</label>: <?php if ($request_info['advisor_approval_timestamp'] == '0000-00-00 00:00:00'){echo 'Awaiting Advisor';} else if ($request_info['advisor_approval']==0){echo 'Advisor Denied';} else if ($request_info['request_approval_timestamp'] !== '0000-00-00 00:00:00'){echo $request_info['request_approval'] ? 'Approved':'Denied';}else{echo 'Awaiting School Approval';} ?></p>
				
				<p><label>Student Name</label>: <?php echo $request_info['first_name'].' '.$request_info['last_name']; ?><br>
				<label>Conference Name</label>: <?php echo $request_info['conference_name']; ?><br>
				<label>Participation Type</label>: <?php echo $request_info['participation_type']; ?><br>
				<label>Conference Location</label>: <?php echo $request_info['location_city'].', '.$request_info['location_state_country']; ?><br>
				<label>Travel Dates</label>: <?php echo date("F j, Y", strtotime($request_info['departure_date'])).' - '.date("F j, Y", strtotime($request_info['return_date'])); ?></p>
				
				<p><label>Advisor Approves?</label> <?php echo $request_info['advisor_approval_timestamp'] != '0000-00-00 00:00:00' ? $request_info['advisor_approval'] ? 'Yes' : 'No' : 'Awaiting Advisor'; ?><br>
					<label>Advisor's Comments</label>: <?php echo $request_info['advisor_comment']; ?></p>
				
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
				<br>
				
				<p style="border:1px solid gray;padding:0.5em;background-color:rgba(255, 255, 255, 0.5)">
					<label>Total Amount Requested</label>: $<?php echo $request_info['total_request']; ?><br>
					<label>Total Amount Approved</label>: <?php echo $request_info['request_approval_timestamp'] != '0000-00-00 00:00:00' && $request_info['request_approval'] ? '$'.$request_info['amount_approved'] : '';?></p>

					
				<p><?php echo $request_info['request_approval_timestamp'] != '0000-00-00 00:00:00' ? 'Approver Comment:<br>'.$request_info['approver_comment'] : '';?>
				</p>
				
				
			<?php endif; ?>
			
		</div>	
		<div id="footer"><a href="/dept_forms/website_error.py" target="_blank">Report a problem with this website</a></div>
	</div>
</body>
</html>