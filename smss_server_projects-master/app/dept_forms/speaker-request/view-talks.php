<?php

include('speaker-request-functions.php');

//get requests
$get_requests_query = $mthsc_db->query("SELECT * FROM speaker_request WHERE approved = 1 ORDER BY date_scheduled DESC;");
$requests = $get_requests_query->fetchAll();

?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
	"http://www.w3.org/TR/html4/loose.dtd">


<html lang="en-US">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="author" content="hedetni">
	<!-- Date: 2019-9-17 -->
	
	<title>School Forms | View Talks</title>

<link rel="stylesheet" href="/style/math-internal.css" type="text/css" media="screen" charset="utf-8">
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<style type="text/css">

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
		"order": [[ 4, "desc" ]]
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
				<h1>Scheduled Talks</h1>
			
				<?php if (count($requests) > 0): ?>
					<table class="styled compact" id="request_table">
						<thead>
							<tr>
								<th scope="col">Speaker</th>
								<th scope="col">Affiliation</th>
								<th scope="col">Talk Type</th>
								<th scope="col">Title</th>
								<th scope="col">Date</td>
								<th scope="col">Location</td>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($requests as $request): ?>
								<tr>
									<td><?php echo $request['speaker_name']; ?></td>
									<td><?php echo $request['speaker_affiliation']; ?></td>
									<td><?php echo $request['talk_category'] == 'School Colloquium' ? 'Colloquium' : $request['research_group'].' Seminar'; ?></td>
									<td><?php echo $request['talk_title'] != "" ? $request['talk_title'] : "TBA"; ?></td>
									<td class="text-center"><?php echo $request['approved'] ? date("M j, Y",strtotime($request['date_scheduled'])) : ''; ?></td>
									<td class="text-center"><?php echo $request['room_scheduled']; ?></td>
								</tr>
							<?php endforeach;?>
						</tbody>
					</table>
				<?php else:?>
					<p>No talks scheduled yet</p>
				<?php endif; ?>
			<?php endif; ?>
		</div>	
		<div id="footer"><a href="/dept_forms/website_error.py" target="_blank">Report a problem with this website</a></div>
	</div>
	
</body>
</html>