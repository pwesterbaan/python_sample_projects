<?php

include('speaker-request-functions.php');
if (!isset($_SESSION)){ session_start();}


if (isset($_POST['update_speaker_request']) && in_array($user_id,$admin_list))
{
	//save data
	$submission = $_POST;
	unset($submission['update_speaker_request']);
	$submission['first_pref_date'] = date("Y-m-d",strtotime($submission['first_pref_date']));
	$submission['second_pref_date'] = date("Y-m-d",strtotime($submission['second_pref_date']));
	if ($submission['modality'] == 'Virtual'){$submission['room_preference'] = '--';}
	
	$insert_query = $mthsc_db->prepare('UPDATE speaker_request SET speaker_name = :speaker_name, speaker_affiliation = :speaker_affiliation, speaker_email = :speaker_email, external = :external, first_pref_date = :first_pref_date, second_pref_date = :second_pref_date, modality = :modality, room_preference = :room_preference, talk_category = :talk_category, research_group = :research_group, funding_source = :funding_source, funding_limit = :funding_limit WHERE request_id = :request_id;');
	$result = $insert_query->execute($submission);
	
	if ($result)
	{
		//feedback for user
		$_SESSION['message'] = "Request Updated";
		http_response_code( 303 );
		header("Location: ".$_SERVER['REQUEST_URI']);
		exit;
	}
	else
	{
		$_SESSION['error'] = "Something went wrong, please try again";
		http_response_code( 303 );
		header("Location: ".$_SERVER['REQUEST_URI']);
		exit;
	}
}


if (isset($_POST['approve_speaker']))
{
	if (in_array($user_id,$admin_list))
	{
		$approve_query = $mthsc_db->prepare('UPDATE speaker_request SET approved = 1, date_scheduled = ?, room_scheduled = ? WHERE request_id = ?;');
		$result = $approve_query->execute(array(date("Y-m-d",strtotime($_POST['date_scheduled'])),$_POST['room_scheduled'],$_POST['request_id']));
		
		if ($result)
		{
			$request = get_request_details($_GET['id']);
			
			//send email
			$headers = "MIME-Version: 1.0\r\n";
			$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
			$headers .= "From: '".$fullName."' <".$user_id."@clemson.edu>\r\n";
			$subject = "Speaker Request Approved";
			$email_body = '<html><body><p>Your speaker request has been approved, and a spot has been reserved on the calendar.</p>';
			$email_body .= '<p><strong>Speaker Info</strong></p>';
			$email_body .= '<p>'.$request['speaker_name'].'<br>';
			$email_body .= $request['speaker_affiliation'].'<br>';
			$email_body .= $request['speaker_email'].'</p>';
			$email_body .= '<p><strong>Talk Type</strong>: ';
			$email_body .= $request['talk_category'];
			if ($request['talk_category'] == 'Research Group Seminar')
			{$email_body .= ' - '.$request['research_group'];}
			$email_body .= '</p>';
			$email_body .= '<p><strong>Scheduled Date of Talk</strong>: ';
			$email_body .= date("M j, Y",strtotime($request['date_scheduled'])).'</p>';
			$email_body .= '<p><strong>Scheduled Room</strong>: ';
			$email_body .= $request['room_scheduled'].'</p>';
			
			if ($request['external'] == "External")
			{
				$email_body .= '<p>Because the speaker is external to Clemson, you will now need to fill out the <a href="https://mthsc.clemson.edu/dept_forms/visitor-approval/policy.php?speaker_id='.$request['request_id'].'">Visitor Approval Form</a> for this visitor. If you use the above link, some information about the speaker will be pre-filled in the visitor approval form.</p>';
			}
			$email_body .= '</body></html>';
			
			$to = $request['username'];
		
			mail($to.'@clemson.edu', $subject, $email_body, $headers);
			
			//send email to admins
			$headers = "MIME-Version: 1.0\r\n";
			$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
			$headers .= "From: '".$fullName."' <".$user_id."@clemson.edu>\r\n";
			$subject = "Speaker Request Approved";
			$email_body = '<html><body><p>A Speaker Request has been approved.</p>';
			$email_body .= '<p>Requestor: '.$fullName.'</p>';
			$email_body .= '<p><strong>Speaker Info</strong></p>';
			$email_body .= '<p>'.$request['speaker_name'].'<br>';
			$email_body .= $request['speaker_affiliation'].'<br>';
			$email_body .= $request['speaker_email'].'</p>';
			$email_body .= '<p><strong>Talk Type</strong>: ';
			$email_body .= $request['talk_category'];
			if ($request['talk_category'] == 'Research Group Seminar')
			{$email_body .= ' - '.$request['research_group'];}
			$email_body .= '</p>';
			$email_body .= '<p><strong>Scheduled Date of Talk</strong>: ';
			$email_body .= date("M j, Y",strtotime($request['date_scheduled'])).'</p>';
			$email_body .= '<p><strong>Scheduled Room</strong>: ';
			$email_body .= $request['room_scheduled'].'</p>';
		
			$email_body .= '<p><a href="https://mthsc.clemson.edu/dept_forms/speaker-request/view-request.php?id='.$request['request_id'].'">View the Full Request</a></p>';
			$email_body .= '</body></html>';
		
			mail('lcalla@clemson.edu', $subject, $email_body, $headers);
			
			//feedback for user
			$_SESSION['message'] = "Request Approved. Requestor has been emailed.";
			http_response_code( 303 );
			header("Location: ".$_SERVER['REQUEST_URI']);
			exit;
		}
		else
		{
			$_SESSION['error'] = "Something went wrong, please try again";
			http_response_code( 303 );
			header("Location: ".$_SERVER['REQUEST_URI']);
			exit;
		}
	}
	else
	{
		$error = "Not authorized to edit";
	}
}

if (isset($_POST['cancel_speaker']))
{
	if (in_array($user_id,$admin_list))
	{
		$cancel_query = $mthsc_db->prepare('UPDATE speaker_request SET approved = 0, date_scheduled = "0000-00-00", room_scheduled = "" WHERE request_id = ?;');
		$result = $cancel_query->execute(array($_POST['request_id']));
		
		if ($result)
		{
			//feedback for user
			$_SESSION['message'] = "Request canceled";
			http_response_code( 303 );
			header("Location: ".$_SERVER['REQUEST_URI']);
			exit;
		}
		else
		{
			$_SESSION['error'] = "Something went wrong, please try again";
			http_response_code( 303 );
			header("Location: ".$_SERVER['REQUEST_URI']);
			exit;
		}
	}
	else
	{
		$error = "Not authorized to edit";
	}
}

if (isset($_POST['update_talk_details']))
{
	if (in_array($user_id,$admin_list))
	{
		$update_query = $mthsc_db->prepare('UPDATE speaker_request SET talk_title = ? WHERE request_id = ?;');
		$result = $update_query->execute(array($_POST['talk_title'],$_POST['request_id']));
		
		if ($result)
		{
			//feedback for user
			$_SESSION['message'] = "Talk details updated";
			http_response_code( 303 );
			header("Location: ".$_SERVER['REQUEST_URI']);
			exit;
		}
		else
		{
			$_SESSION['error'] = "Something went wrong, please try again";
			http_response_code( 303 );
			header("Location: ".$_SERVER['REQUEST_URI']);
			exit;
		}
	}
	else
	{
		$error = "Not authorized to edit";
	}
}

if (isset($_GET['id']) && is_numeric($_GET['id']) && $_GET['id'] != "" && $_GET['id'] != 0)
{
	//get request
	$request = get_request_details($_GET['id']);
		
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

if (isset($_SESSION['message']) )
{
	$message = $_SESSION['message'];
	unset($_SESSION['message']);
}
if (isset($_SESSION['error']) )
{
	$error = $_SESSION['error'];
	unset($_SESSION['error']);
}

?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
	"http://www.w3.org/TR/html4/loose.dtd">


<html lang="en-US">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="author" content="hedetni">
	<!-- Date: 2019-9-17 -->
	
	<title>School Forms | View Speaker Request</title>

<link rel="stylesheet" href="/style/math-internal.css" type="text/css" media="screen" charset="utf-8">
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<style type="text/css">
p.leader {font-weight:bold;margin-top:1.5em;}
.indent {margin-left:1em;}
span.input {font-weight:bold;}

@media only screen and (min-width: 1024px) {
	div#content {
		overflow:auto;
	}
	div#request_col {
		float:left;
		width:49%;
	}
	div#approval_col {
		width:49%;
		margin-left:50%;
	}
}
@media only print {
	html {margin:0;padding:0;}
	#header,#nav,#subnav,#footer,form {
		display:none;
	}
	body {font-size:12px;font-family:Helvetica,sans-serif;}
	h1 {text-align:center;}
	p.leader {margin-top:0.75em;}
	#edit_link,#add_comment_section,#single_col_sep,.image_link {display:none;}
}
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
				<h1>Speaker Request</h1>
				<div id="request_col">
					
					<h2>Submitted by <?php echo get_name_from_username_hub($request['username']).'<br><small>on '.date("M d, Y", strtotime($request['submitted'])).'</small>';?></h2>
					
					<?php if (in_array($user_id,$admin_list)): ?>
						<h2 id="edit_link"><a href="edit-request.php?id=<?php echo $request['request_id']; ?>">Edit this Request</a></h2>
					<?php endif; ?>
					
					<div class="form_section">
						<p class="leader">Speaker Info</p>
						<p class="indent">
							<?php echo $request['speaker_name'];?><br>
							<?php echo $request['speaker_affiliation'];?><br>
							<?php echo $request['speaker_email'];?><br>
							This speaker is <span class="input"><?php echo $request['external']; ?></span> to Clemson
						</p>
					</div>
	
					<div class="form_section">
						<p class="leader">Talk Info</p>
						<p class="indent">Talk Type: <span class="input"><?php echo $request['talk_category'] ;?></span>
						<?php echo $request['talk_category'] == "Research Group Seminar" ? '<br>Research Group: <span class="input">'.$request['research_group'].'</span>' : ''; ?>
						</p>
					</div>
					
					<div class="form_section">
						<p class="leader">Preferences:</p>
						<p class="indent">First Date Preference: <span class="input"><?php echo date("M j, Y",strtotime($request['first_pref_date'])) ;?></span><br>
							Second Date Preference: <span class="input"><?php echo date("M j, Y",strtotime($request['second_pref_date'])) ;?></span><br>
							Modality: <span class="input"><?php echo $request['modality']; ?></span><br>
							Preferred Room: <span class="input"><?php echo $request['room_preference']; ?></span>
						</p>
					</div>
	
					<div class="form_section">
						<p class="leader">Funding:</p>
						<p class="indent">Funding Source: <span class="input"><?php echo $request['funding_source'];?></span><br>
							Funding Limit: <span class="input">$<?php echo $request['funding_limit']; ?></span>
						</p>
						</fieldset>
					</div>
				</div>
				
				<div id="approval_col">
					<?php if (!$request['approved']): ?>
						<div id="approval">
							<p>This request has not been approved. To approve, select a date and room below.</p>
							
							<?php if ($request['external'] == "External"): ?>
								<p>Upon approval, <?php echo get_name_from_username_hub($request['username']); ?> will receive an email confirmation that also asks them to fill out the visitor approval form for this speaker.</p>
							<?php else: ?>
								<p>Upon approval, <?php echo get_name_from_username_hub($request['username']); ?> will receive an email confirmation.</p>
							<?php endif; ?>
							
							<form name="approve_request_form" method="POST" action="">
								<p><label for="date_scheduled">Scheduled Date</label>: <input type="text" name="date_scheduled" id="date_scheduled" class="datepicker" value=""></input></p>
								<p><label for="room_scheduled">Scheduled Room</label>: <input type="text" name="room_scheduled" id="room_scheduled"></input></p>
								<p>
									<input type="hidden" name="request_id" value="<?php echo $request['request_id'];?>"></input>
									<input type="hidden" name="external" value="<?php echo $request['external'] == "External" ? 1 : 0; ?>"></input>
									<input type="submit" name="approve_speaker" value="Approve Speaker"></input>
								</p>
							</form>
						</div>
					<?php else: ?>
						<div id="approval">
							<p>This request has been approved and scheduled for<br>
							<span class="input"><?php echo date("M j, Y",strtotime($request['date_scheduled'])) ;?></span> in <span class="input"><?php echo $request['room_scheduled']; ?></span></p>
							<p>To cancel/un-approve, click the button below.</p>
							
							<form name="cancel_speaker_form" method="POST" action="">
								<p>
									<input type="hidden" name="request_id" value="<?php echo $request['request_id'];?>"></input>
									<input type="submit" name="cancel_speaker" value="Cancel Speaker"></input>
								</p>
							<form>
						</div>
					<?php endif; ?>
					
					<div class="form_section">
						<p class="leader">Talk Details</p>
						<form name="talk_details_form" method="POST" action="">
							<p><label for="talk_title">Talk Title</label>: <input type="text" name="talk_title" id="talk_title" value="<?php echo $request['talk_title'];?>"></input></p>
							<p>
								<input type="hidden" name="request_id" value="<?php echo $request['request_id'];?>"></input>
								<input type="submit" name="update_talk_details" value="Update Talk Details"></input>
							</p>
						</form>
					</div>
				</div>
			<?php endif; ?>
			
			
		</div>	
		<div id="footer"><a href="/dept_forms/website_error.py" target="_blank">Report a problem with this website</a></div>
	</div>
	
</body>
</html>