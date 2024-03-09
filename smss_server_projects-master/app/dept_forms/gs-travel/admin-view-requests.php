<?php

include('gs-travel-functions.php');

if (isset($_POST['submit_request_approval']))
{
	//save submission
	$update = $_POST;
	$request_id = $_POST['request_id'];
	unset($update['submit_request_approval']);
	
	//update request
	$update_query = $mthsc_db->prepare('UPDATE gs_travel_funding SET request_approval = :approval,request_approval_timestamp = now(),approver_comment = :approver_comment, amount_approved = :amount_approved, request_approved_by = :approved_by WHERE request_id = :request_id;');
	$result = $update_query->execute($update);
	if ($result)
	{
		$message = "Request Updated";
	}

	//get request info
	$request_query = $mthsc_db->prepare('SELECT request_id,IF(NOT display_name IS NULL AND display_name != "", display_name, first_name) AS first_name, IF(NOT suffix IS NULL AND suffix != "", CONCAT_WS(" ",last_name,suffix), last_name) AS last_name,username as student_username,student_person_id,advisor_person_id,conference_name,participation_type,location_city,location_state_country,departure_date,return_date,advisor_approval_timestamp FROM `gs_travel_funding` JOIN dept_info.person ON student_person_id = person_id WHERE request_id = ?');
	$request_query->execute(array($request_id));
	$request_info = $request_query->fetch();
	
	if ($update['approval'])
	{
		//email chair and janice
		$headers = "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
		$headers .= "From: 'Math Science Grad Studies' <mthgrad@clemson.edu>\r\n";
		$subject = "Update to Grad Student Travel Funding Request";
		$email = "<html><body>";
		$email .= "<p>There has been an update to a travel funding request of the school.</p>";
		$email .= "<p>Student Name: ".$request_info['first_name'].' '.$request_info['last_name']."<br>
						Conference Name: ".$request_info['conference_name']."<br>
						Participation Type: ".$request_info['participation_type']."<br>
						Conference Location: ".$request_info['location_city'].', '.$request_info['location_state_country']."<br>
						Travel Dates: ".date("F j, Y", strtotime($request_info['departure_date'])).' - '.date("F j, Y", strtotime($request_info['return_date']))."</p>";
		$email .= '<p><strong>This request was approved for $'.$update['amount_approved'].'</strong></p>';
		$email .= "<p>Approver Comment: ".$update['approver_comment']."</p>";
		$email .= "<p>Follow this link to view the request:</p>";
		$email .= "<p>https://mthsc.clemson.edu/dept_forms/gs-travel/admin-view-request.php?id=".$request_id."</p>";
		$email .= "</body></html>";
	
		//change to janice and chair
		mail ('keshias@clemson.edu, mthgrad@clemson.edu, kevja@clemson.edu, lcalla@clemson.edu', $subject, $email, $headers); //send email
		
		//email student
		$headers = "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
		$headers .= "From: 'Math Science Grad Studies' <mthgrad@clemson.edu>\r\n";
		$subject = "Update to Grad Student Travel Funding Request";
		$email = "<html><body>";
		$email .= "<p>This email is to inform you that your recent request for school travel funds has been approved for $".$update['amount_approved'].". You may see the status of your requests here: https://mthsc.clemson.edu/dept_forms/gs-travel/index.php. Please contact the Graduate Coordinator for more information.</p>";
		$email .= "<p>Approver Comment: ".$update['approver_comment']."</p>";
		$email .= "</body></html>";
		
		$get_student_query = $mthsc_db->prepare('SELECT username FROM gs_travel_funding JOIN dept_info.person ON student_person_id = person.person_id WHERE request_id = ?');
		$get_student_query->execute(array($request_id));
		$student_email = $get_student_query->fetchColumn();
	
		mail ($student_email.'@clemson.edu', $subject, $email, $headers); //send email
	}
	else
	{
		//email student
		$headers = "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
		$headers .= "From: 'Math Science Grad Studies' <mthgrad@clemson.edu>\r\n";
		$subject = "Update to Grad Student Travel Funding Request";
		$email = "<html><body>";
		$email .= "<p>This email is to inform you that your recent request for school travel funds has been denied. You may revise your entry and submit the request again, and see the status of your requests here: https://mthsc.clemson.edu/dept_forms/gs-travel/index.php. You may contact the Graduate Coordinator for more information.</p>";
		$email .= "</body></html>";
		
		$get_student_query = $mthsc_db->prepare('SELECT username FROM gs_travel_funding JOIN dept_info.person ON student_person_id = person.person_id WHERE request_id = ?');
		$get_student_query->execute(array($request_id));
		$student_email = $get_student_query->fetchColumn();
	
		mail ($student_email.'@clemson.edu', $subject, $email, $headers); //send email
	}
}


if (isset($_POST['delete_request']))
{
	$request_to_delete = $_POST['request_to_delete'];
	$delete_request_query = $mthsc_db->prepare('DELETE FROM gs_travel_funding WHERE request_id = ?;');
	$delete_request_query->execute(array($request_to_delete));
	$message = "Request Deleted";
}


$requests_query = $mthsc_db->query('SELECT * FROM gs_travel_funding JOIN dept_info.person ON student_person_id = person.person_id ORDER BY submitted DESC');
$requests = $requests_query->fetchAll();

//get fy's
$fy_query = $mthsc_db->query('SELECT fy FROM gs_travel_funding GROUP BY fy ORDER BY fy desc');
$fiscal_years = $fy_query->fetchAll(PDO::FETCH_COLUMN);

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
.text-center {text-align:center;}
table {margin-bottom:2.5em;}
.hidden_cell {border:none;background-color:transparent;}
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
			<h1><a href="index.php">GS Travel Funding Request</a></h1>
		</div>
	
		<div id="content">
			<h1>GS Travel Funding Admin</h1>
			
			<?php echo isset($message) ? '<p id="error">'.$message.'</p>' : ''; ?>
			
			<?php foreach ($fiscal_years as $fy): ?>
				<?php $fy_total_requested=0; $fy_total_approved=0; ?>
				<h2 class="text-center">FY <?php echo $fy; ?></h2>
				<table>
					<tr>
						<th scope="col">View Request</th>
						<th scope="col">Status</th>
						<th scope="col">Submitted</th>
						<th scope="col">Student</th>
						<th scope="col">Advisor</th>
						<th scope="col">Conference</th>
						<th scope="col">Location</th>
						<th scope="col">Dates</th>
						<th scope="col">Requested Amount</th>
						<th scope="col">Approved Amount</th>
						<th scope="col">Delete Request</th>
					</tr>
				<?php if(count($requests)==0): ?>
					<tr><td>No requests to show</td></tr>
				<?php endif; ?>
				<?php foreach ($requests as $request): ?>
					<?php if ($request['fy']==$fy): ?>
					<tr>
						<td class="text-center"><a href="admin-view-request.php?id=<?php echo $request['request_id'];?>">View</a></td>
						<td class="text-center"><?php if ($request['advisor_approval_timestamp'] == '0000-00-00 00:00:00'){echo 'Awaiting Advisor';} else if ($request['advisor_approval']==0){echo 'Advisor Denied';} else if ($request['request_approval_timestamp'] !== '0000-00-00 00:00:00'){echo $request['request_approval'] ? 'Approved':'Denied';}else{echo 'Needs Approval';} ?></td>
						<td><?php echo date("F j, Y", strtotime($request['submitted'])); ?></td>
						<td><?php echo $request['first_name'].' '.$request['last_name']; ?></td>
						<td><?php $advisor = get_advisor_from_person_id($request['student_person_id']); echo $advisor['name']; ?>
						<td><?php echo $request['conference_name']; ?></td>
						<td><?php echo $request['location_city'].', '.$request['location_state_country']; ?></td>
						<td class="text-center"><?php echo date("F j, Y", strtotime($request['departure_date'])).' - '.date("F j, Y", strtotime($request['return_date'])); ?></td>
						<td class="text-center"><?php echo $request['total_request']; ?></td>
					
						<td class="text-center"><?php echo $request['request_approval'] && $request['request_approval_timestamp'] !== '0000-00-00 00:00:00' ? $request['amount_approved'] : ''; ?></td>
						<td><form name="delete_<?php echo $request['request_id'];?>_form" method="POST" action="">
								<input type="hidden" name="request_to_delete" value="<?php echo $request['request_id'];?>"></input>
								<input type="submit" name="delete_request" value="Delete"></input>
							</form></td>
					</tr>
					<?php $fy_total_requested = $fy_total_requested + $request['total_request']; ?>
					<?php $fy_total_approved = $fy_total_approved + $request['amount_approved']; ?>
					<?php endif; ?>
				<?php endforeach; ?>
					<tr>
						<td colspan="6" class="hidden_cell"></td>
						<th>Totals</th>
						<th class="text-center">$<?php echo $fy_total_requested; ?></th>
						<th class="text-center">$<?php echo $fy_total_approved; ?></th>
					</tr>
				</table>
			<?php endforeach; ?>
			
		</div>	
		<div id="footer"><a href="/dept_forms/website_error.py" target="_blank">Report a problem with this website</a></div>
	</div>
</body>
</html>