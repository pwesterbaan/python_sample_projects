<?php

include('visitor-approval-functions.php');


if (in_array($user_id,$admin_list))
{
	if (isset($_POST['delete_request']))
	{
		$request_to_delete = $_POST['request_to_delete'];
		
		//delete request
		$delete_request_query = $mthsc_db->prepare("DELETE FROM visitor_approval WHERE request_id = ?;");
		$result_one = $delete_request_query->execute(array($request_to_delete));
		
		if ($result_one)
		{
			//delete comments
			$delete_comments_query = $mthsc_db->prepare("DELETE FROM visitor_approval_comments WHERE request_id = ?;");
			$result_two = $delete_comments_query->execute(array($request_to_delete));
			
			if ($result_two)
			{
				$message = 'Request deleted';
			}
			else
			{
				$error = "Error: request not fully deleted";
			}
		}
		else
		{
			$error = "Error: request not deleted";
		}
	}
	
	//get requests
	$get_requests_query = $mthsc_db->query("SELECT * FROM visitor_approval ORDER BY submitted DESC;");
	$requests = $get_requests_query->fetchAll();
}
else
{
	$error = "Access Denied";
}

?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
	"http://www.w3.org/TR/html4/loose.dtd">


<html lang="en-US">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="author" content="hedetni">
	<!-- Date: 2019-5-21 -->
	
	<title>School Forms | Visitor Approval Requests</title>

<link rel="stylesheet" href="/style/math-internal.css" type="text/css" media="screen" charset="utf-8">
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<style type="text/css">
.no-break {white-space: nowrap;}
</style>

<script src="/style/jquery-3.2.1.min.js" type="text/javascript" charset="utf-8"></script>
<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.15/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="//cdn.datatables.net/1.10.15/js/jquery.dataTables.js"></script>

<script type="text/javascript">
$(document).ready(function(){
	$('table#request_table').DataTable({
		//"scrollY": 600,
		"scrollCollapse":true,
		"lengthChange": true,
		"dom": 'Biftlp',
		"pageLength": 10,
		"paging":true,
		"columns": [
			{'searchable': false},
			null,
			null,
			null,
			{'type':'date'},
			{'type':'date'},
			{'type':'date'},
			null]
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
			<?php if (in_array($user_id,$admin_list)): ?>
				<h1>Visitor Approval Requests</h1>
			
				<?php if (count($requests) > 0): ?>
					<table class="styled" id="request_table">
						<thead>
							<tr>
								<th scope="col">Full Request</th>
								<th scope="col">Requestor</th>
								<th scope="col">Visitor</th>
								<th scope="col">Affiliation</th>
								<th scope="col">Arriving</th>
								<th scope="col">Departing</th>
								<th scope="col">Submitted</th>
								<th scope="col">Completed</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($requests as $request): ?>
								<tr>
									<td class="text-center"><a href="view-request.php?id=<?php echo $request['request_id'];?>">View Full Request</a></td>
									<td><a href="https://science.clemson.edu/scinet/hub/view-person.php?user=<?php echo $request['username'];?>"><?php echo get_name_from_username_hub($request['username']); ?></a></td>
									<td><?php echo $request['visitor_name']; ?></td>
									<td><?php echo $request['visitor_affiliation']; ?></td>
									<td class="no-break"><?php echo date("M j, Y",strtotime($request['visit_arrival_date'])); ?></td>
									<td class="no-break"><?php echo date("M j, Y",strtotime($request['visit_departure_date'])); ?></td>
									<td class="no-break"><?php echo date("M j, Y",strtotime($request['submitted'])); ?></td>
									<td class="no-break"><?php echo $request['completed'] ? 'Complete' : 'Pending'; ?></td>
								</tr>
							<?php endforeach;?>
						</tbody>
					</table>
				<?php else:?>
					<p>No requests submitted yet</p>
				<?php endif; ?>
			<?php endif; ?>
		</div>	
		<div id="footer"><a href="/dept_forms/website_error.py" target="_blank">Report a problem with this website</a></div>
	</div>
	
</body>
</html>