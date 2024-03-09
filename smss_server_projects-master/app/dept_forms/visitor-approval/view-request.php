<?php

include('visitor-approval-functions.php');

if (isset($_POST['update_approval_form']))
{
	if (in_array($user_id,$admin_list))
	{
		//capture data
		$edit_request = $_POST;
		unset($edit_request['update_approval_form']);
		
		$edit_request['visit_arrival_date'] = date("Y-m-d",strtotime($edit_request['visit_arrival_date']));
		$edit_request['visit_departure_date'] = date("Y-m-d",strtotime($edit_request['visit_departure_date']));
		$edit_request['travel_arrival_date'] = date("Y-m-d",strtotime($edit_request['travel_arrival_date']));
		$edit_request['travel_departure_date'] = date("Y-m-d",strtotime($edit_request['travel_departure_date']));
		$edit_request['parking_arrival_date'] = date("Y-m-d",strtotime($edit_request['parking_arrival_date']));
		$edit_request['parking_departure_date'] = date("Y-m-d",strtotime($edit_request['parking_departure_date']));
	
		$fields = array("visitor_name","visitor_affiliation","visitor_email","visitor_phone","last_four","residency","visa_type","visit_arrival_date","visit_departure_date","purpose_seminar_speaker","purpose_colloquium_speaker","purpose_collaborative_research","purpose_prospective_student","purpose_recruiting","purpose_potential_donor","purpose_other_purpose","seminar","other_visit_type","expenses_travel_lodging","expenses_parking","mileage","rental_car","shuttle","meals","airfare_directbill","airfare_reimbursement","lodging_directbill","lodging_reimbursement","travel_arrival_date","travel_departure_date","travel_amount","travel_account","parking_arrival_date","parking_departure_date","parking_account","visitor_office","visitor_office_dates","refreshments","refreshments_datetime","other_request_comments");
	
		foreach ($fields as $field)
		{
			if (!isset($edit_request[$field]))
			{
				$edit_request[$field] = 0;
			}
		}
	
		//echo '<pre>';print_r($new_request);echo '</pre>';
		$query = 'UPDATE visitor_approval SET ';
		foreach ($fields as $field)
		{
			$query .= $field.'=:'.$field.',';
		}
		$query = substr($query,0,-1);
		$query .= ' WHERE request_id = :request_id;';
		
		$update_query = $mthsc_db->prepare($query);
		$result = $update_query->execute($edit_request);
		
		if ($result)
		{
			$message = "Request Updated";
		}
		else
		{
			$error = "Something went wrong, your request was not saved";
		}
	}
	else
	{
		$error = "Not authorized to edit";
	}
}

if (isset($_POST['mark_as_completed']))
{
	$query = $mthsc_db->prepare('UPDATE visitor_approval SET completed = 1 WHERE request_id = ?');
	$result = $query->execute(array($_POST['request_to_mark']));
	
	if ($result)
	{
		$message = "Request marked as complete";
	}
	else
	{
		$error = "Something went wrong, the request was not marked as complete";
	}
}

if (isset($_POST['mark_as_not_completed']))
{
	$query = $mthsc_db->prepare('UPDATE visitor_approval SET completed = 0 WHERE request_id = ?');
	$result = $query->execute(array($_POST['request_to_mark']));
	
	if ($result)
	{
		$message = "Request marked as not complete";
	}
	else
	{
		$error = "Something went wrong, the request was not marked as not complete";
	}
}


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
		if ($request['username'] == $user_id || in_array($user_id,$admin_list))
		{
			$display_request = true;
		}
		else
		{
			$display_request = false;
			$error = "Access Denied";
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
p.leader {font-weight:bold;margin-top:1.5em;}
.indent {margin-left:1em;}
#more_parking,#more_travel,#more_honorarium,#account_section {margin-left:2em;background-color:#efefef;border:1px solid #ddd;margin-bottom:1em;page-break-inside: avoid;}
span.input {font-weight:bold;}
div#other_requests_comments {font-style:italic;margin-left:1em;}
div.note_info {font-size:0.9em;font-style:italic;color:#666;}
div.note_text {margin-bottom:1em;margin-left:0.5em;}
hr#single_col_sep {margin-top:1.5em;margin-bottom:1.5em;}
@media only screen and (min-width: 1024px) {
	div#content {
		overflow:auto;
	}
	div#request_col {
		float:left;
		width:49%;
	}
	div#comment_col {
		width:49%;
		margin-left:50%;
	}
	hr#single_col_sep {display:none;}
}
@media only print {
	html {margin:0;padding:0;}
	#header,#nav,#subnav,#footer {
		display:none;
	}
	body {font-size:12px;font-family:Helvetica,sans-serif;}
	h1 {text-align:center;}
	p.leader {margin-top:0.75em;}
	#comment_col {
		page-break-before:always;
	}
	#edit_link,#add_comment_section,#single_col_sep,.image_link {display:none;}
	#visitor_section {
		width:49%;
		display:inline-block;
	}
	#dates_section {
		width:49%;
		display:inline-block;
		vertical-align:top;
	}
}
</style>

<script src="/style/jquery-3.2.1.min.js" type="text/javascript" charset="utf-8"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="/style/jquery.validate.js"></script>
<script src="/style/validate-additional-methods.js"></script>

<script type="text/javascript">

function leave_comment(request_id,username)
{
	var comment = $('#comment_field').val();
	if ($.trim(comment) != '')
	{
		$.post('ajax-functions.php', {leave_comment: "true", request_id:request_id, comment:comment, username:username},function(data,status){
			if (status == "success")
			{
				if (data)
				{
					refresh_comments(request_id);
					$('#comment_field').val("")
				}
				else
				{
					alert("Something went wrong");
				}
			}
			else
			{
				alert("Something went wrong");
			}
		});
	}
}

function refresh_comments(request_id)
{
	var comments_div = $('#comments');
	$.post('ajax-functions.php', {get_comments_for_request: "true", request_id: request_id},function(data,status){
		if (status == "success")
		{
			var received_data = data;
			comments_div.html(received_data);
		}
		else
		{
			alert("Could not connect to server");
		}
	});
}

function delete_comment(comment_id,request_id)
{
	$.post('ajax-functions.php', {delete_comment: "true", comment_id: comment_id},function(data,status){
		if (status == "success")
		{
			if (data)
			{
				refresh_comments(request_id);
			}
			else
			{
				alert("Something went wrong");
			}
		}
		else
		{
			alert("Could not connect to server");
		}
	});
}

$(document).ready(function(){

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
			<?php if ($request && $display_request): ?>
				<h1>Visitor Request</h1>
				<div id="request_col">
					
					<h2>Submitted by <?php echo get_name_from_username_hub($request['username']).'<br><small>on '.date("M d, Y", strtotime($request['submitted'])).'</small>';?></h2>
					
					<?php if (in_array($user_id,$admin_list)): ?>
						<h2 id="edit_link"><a href="form.php?id=<?php echo $request['request_id']; ?>">Edit this Request</a></h2>
						
						<form name="mark_completed_form" method="POST" action="">
							<p><input type="hidden" name="request_to_mark" value="<?php echo $request['request_id'];?>"></input>
							<?php if ($request['completed'] == 0): ?>
								<input type="submit" name="mark_as_completed" value="Mark as Completed"></input>
							<?php else: ?>
								<input type="submit" name="mark_as_not_completed" value="Mark as Not Completed"></input>
							<?php endif; ?>
							</p>
						</form>
						
					<?php else: ?>
						<p>To make changes to this request, contact Lynn Callahan.</p>
					<?php endif; ?>
			
					<div class="form_section" id="visitor_section">
						<p class="leader">Visitor Info</p>
						<p class="indent">
							<?php echo $request['visitor_name'];?><br>
							<?php echo $request['visitor_affiliation'];?><br>
							<?php echo $request['visitor_email'];?><br>
							<?php echo $request['visitor_phone'];?><br>
							Last 4 Digits of SS#: <?php echo $request['last_four'];?><br>
							<?php echo $request['residency'] == "Foreign National" ? $request['residency'].", Visa: ".$request['visa_type'] : $request['residency'] ;?>
						</p>
					</div>
	
					<div class="form_section" id="dates_section">
						<p class="leader">Dates of visit:</p>
						<p class="indent">Arriving: <span class="input"><?php echo $request['visit_arrival_date'];?></span><br>
							Departing: <span class="input"><?php echo $request['visit_departure_date'];?></span>
						</p>
					</div>
	
					<div class="form_section" id="purpose_section">
						<p class="leader">Purpose of visit:</p>
						<p class="indent">
							<ul>
								<?php echo $request['purpose_seminar_speaker'] ? '<li>Invited Speaker for Research Seminar ('.$request['seminar'].')</li>' : ''; ?>
								<?php echo $request['purpose_colloquium_speaker'] ? '<li>Invited Speaker for School Colloquium</li>' : ''; ?>
								<?php echo $request['purpose_collaborative_research'] ? '<li>Collaborative Research</li>' : ''; ?>
								<?php echo $request['purpose_prospective_student'] ? '<li>Prospective student</li>' : ''; ?>
								<?php echo $request['purpose_recruiting'] ? '<li>Employment Opportunities (Recruiting)</li>' : ''; ?>
								<?php echo $request['purpose_potential_donor'] ? '<li>Potential Donor</li>' : ''; ?>
								<?php echo $request['purpose_other_purpose'] ? '<li>Other Type of Visitor: '.$request['other_visit_type'].'</li>' : ''; ?>
							</ul>
						</p>
						</fieldset>
					</div>

					<div class="form_section" id="expenses_section">
						<p class="leader">Anticipated Expenses:</p>
						<p class="indent">
							<ul>
								<?php echo $request['expenses_travel_lodging'] ? '<li>Travel/Lodging</li>' : ''; ?>
								<?php echo $request['expenses_parking'] ? '<li>Parking Pass</li>' : ''; ?>
								<?php //echo $request['expenses_honorarium'] ? '<li>Paid an Honorarium</li>' : ''; ?>
							</ul>	
						</p>
					
						<?php if ($request['expenses_travel_lodging']): ?>
						<div class="indent" id="more_travel">
							<p class="indent"><strong>Travel Lodging Details</strong></p>
							<p class="indent">
								Anticipated Expenses:
								<ul style="margin-top:0;">
									<?php echo $request['mileage'] ? '<li>Mileage Reimbursement</li>' : ''; ?>
									<?php echo $request['rental_car'] ? '<li>Rental Car Reimbursement</li>' : ''; ?>
									<?php echo $request['shuttle'] ? '<li>Shuttle Service</li>' : ''; ?>
									<?php echo $request['meals'] ? '<li>Meals Reimbursement</li>' : ''; ?>
									<?php echo $request['airfare_directbill'] ? '<li>Direct Bill Airfare</li>' : ''; ?>
									<?php echo $request['airfare_reimbursement'] ? '<li>Airfare Reimbursement</li>' : ''; ?>
									<?php echo $request['lodging_directbill'] ? '<li>Direct Bill Lodging</li>' : ''; ?>
									<?php echo $request['lodging_reimbursement'] ? '<li>Lodging Reimbursement</li>' : ''; ?>
								</ul>
							</p>

							<p class="indent">Travel Arrival Date: <span class="input"><?php echo $request['travel_arrival_date'];?></span><br>
							Travel Departure Date: <span class="input"><?php echo $request['travel_departure_date'];?></span></p>
							<p class="indent">Travel/Lodging expense amount not to exceed: <span class="input"><?php echo $request['travel_amount'];?></span></p>
							<p class="indent">Account Number to be used: <span class="input"><?php echo $request['travel_account'];?></span></p>
						</div>
						<?php endif; ?>

						<?php if ($request['expenses_parking']): ?>
						<div class="indent" id="more_parking">
							<p class="indent"><strong>Parking Details</strong></p>
							<p class="indent">Arrival Date: <span class="input"><?php echo $request['parking_arrival_date'];?></span><br>
							Departure Date: <span class="input"><?php echo $request['parking_departure_date'];?></span></p>
							<p class="indent">Account Number to be used: <span class="input"><?php echo $request['parking_account'];?></span></p>
						</div>
						<?php endif; ?>
						
						<!--
						<?php //if ($request['expenses_honorarium']): ?>
						<div class="indent" id="more_honorarium">
							<p class="indent"><strong>Honorarium Details</strong></p>
							<p class="indent">Amount: <span class="input"><?php //echo $request['honorarium_amount'];?></span></p>
							<p class="indent">Account Number to be used: <span class="input"><?php //echo $request['honorarium_account'];?></span></p>
						</div>
						<?php //endif; ?>
						-->
					</div>
						

					<?php if ($request['visitor_office'] || $request['refreshments']): ?>
					<div class="form_section">
						<p class="leader">Other Requests</p></legend>
							<p class="indent"><?php echo $request['visitor_office'] ? 'Visitor Office: <span class="input">'.$request['visitor_office_dates'].'</span>' : ''; ?></p>
							<p class="indent"><?php echo $request['refreshments'] ? 'Coffee/Tea: <span class="input">'.$request['refreshments_datetime'].'</span>' : ''; ?></p>
					</div>
					<?php endif; ?>
	
					<div class="form_section">
						<p class="leader">Other requests/comments:</p>
						<div id="other_requests_comments"><?php echo nl2br($request['other_request_comments']);?></div>
					</div>
				</div>
				
					<div id="comment_col">
						
						<hr id="single_col_sep">
						
						<h2>Comments</h2>
						<?php if (in_array($user_id,$admin_list)): ?>
							<div id="add_comment_section">
							<p>
								<label for="comment_field">Leave a comment. Comments will be sent via email to requestor and notification group and will be visible below.</label><br>
									<textarea id="comment_field" style="margin-bottom:0.5em" rows="3" cols="60"></textarea><br>
									<button onclick="javascript:leave_comment(<?php echo $request['request_id'];?>,'<?php echo $user_id; ?>')">Leave Comment</button>
							</p>
							<hr>
							</div>
						<?php endif; ?>
					
						<div id="comments">
							<?php foreach ($comments as $comment): ?>
								<div class="note_info"><?php echo get_name_from_username_hub($comment['username']).', '.date("F j, Y, g:i a", strtotime($comment['submitted']));?>:</div>
								<div class="note_text">
									<p><?php echo $comment['comment'];?>
		<?php echo ($comment['username'] == $user_id && in_array($user_id,$admin_list)) ? ' <a href="javascript:delete_comment('.$comment['comment_id'].','.$comment['request_id'].')" class="image_link">&#10060;</a>' : '';?>
									</p></div>
							<?php endforeach; ?>
						</div>
					</div>
			<?php endif; ?>
			
			
		</div>	
		<div id="footer"><a href="/dept_forms/website_error.py" target="_blank">Report a problem with this website</a></div>
	</div>
	
</body>
</html>