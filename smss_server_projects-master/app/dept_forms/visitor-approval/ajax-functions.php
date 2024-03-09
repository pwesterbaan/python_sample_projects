<?php

require('visitor-approval-functions.php');

if (isset($_POST['leave_comment']))
{
	$request_id = $_POST['request_id'];
	$left_by = $_POST['username'];
	$comment = $_POST['comment'];
	
	$leave_comment_query = $mthsc_db->prepare('INSERT INTO visitor_approval_comments (request_id,username,comment) VALUES (?,?,?);');
	$result = $leave_comment_query->execute(array($request_id,$left_by,$comment));
	
	$request_details = get_request_details($request_id);
	$requestor_username = $request_details['username'];
	$requestor_name = get_name_from_username_hub($request_details['username']);
	
	$commenter_username = $left_by;
	$commenter_name = get_name_from_username_hub($left_by);
	
	//send notification email
	$headers = "MIME-Version: 1.0\r\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
	$headers .= "From: <".$commenter_username."@clemson.edu>\r\n";
	$subject = "Update to Visitor Approval Request";
	$email_body = '<html><body><p><strong>Visitor Approval Request Update</strong></p>';
	$email_body .= '<p>The following request has been updated: </p>';
	$email_body .= '<p>'.$request_details['visitor_name'].'<br>';
	$email_body .= $request_details['visitor_affiliation'].'<br>';
	$email_body .= '<p>Dates of Visit: ';
	$email_body .= date("M j, Y",strtotime($request_details['visit_arrival_date'])).' - '.date("M j, Y",strtotime($request_details['visit_departure_date'])).'</p>';
	$email_body .= '<p><strong>Update from '.$commenter_name.'</strong>: <br>'.$comment.'</p>';
	$email_body .= '<p><a href="https://mthsc.clemson.edu/dept_forms/visitor-approval/view-request.php?id='.$request_id.'">View the Full Request</a></p>';
	$email_body .= '</body></html>';
	
	$send_list = $requestor_username.'@clemson.edu,';
	$send_list .= implode($notification_list,',');
	
	mail ($send_list, $subject, $email_body, $headers);
	//mail ('hedetni@clemson.edu', $subject, $send_list.'<br>'.$email_body, $headers);
	
	if ($result){echo true;}
	else {echo false;}
}

if (isset($_POST['get_comments_for_request']))
{
	$request_id = $_POST['request_id'];
	$refreshed_comments = get_comments_for_request($request_id);
	
	$html = "";
	foreach ($refreshed_comments as $comment)
	{
		$html .= '<div class="note_info">'.get_name_from_username_hub($comment['username']).', '.date("F j, Y, g:i a", strtotime($comment['submitted'])).':</div>
		<div class="note_text">';
		$html .= '<p>'.$comment['comment'];
		if ($comment['username'] == $user_id)
		{
			$html .= ' <a href="javascript:delete_comment('.$comment['comment_id'].','.$comment['request_id'].')" class="image_link">&#10060;</a>';
		}
		$html .= '</p></div>';
	}
	echo $html;
}

if (isset($_POST['delete_comment']))
{
	$comment_to_delete = $_POST['comment_id'];
	$delete_comment_query = $mthsc_db->prepare('DELETE FROM visitor_approval_comments WHERE comment_id = ?;');
	$result = $delete_comment_query->execute(array($comment_to_delete));
	
	if ($result){echo true;}
	else {echo false;}
}

?>