<?php

$host = 'mthsc.clemson.edu';
$db   = 'forms';
$user = 'forms';
$pass = 'd8ta_c0l';
$charset = 'utf8';

$dsn = 'mysql:host='.$host.';dbname='.$db.';charset='.$charset;
$opt = array(
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false
);
$mthsc_db = new PDO($dsn, $user, $pass, $opt);

date_default_timezone_set('America/New_York');



if (isset($_POST['open_requests']))
{
	$allow_query = $mthsc_db->query('UPDATE settings SET value = 1 WHERE name = "grader_requests_open";');
}
if (isset($_POST['close_requests']))
{
	$allow_query = $mthsc_db->query('UPDATE settings SET value = 0 WHERE name = "grader_requests_open";');
}



//get terms
$terms_query = $mthsc_db->query('SELECT term FROM `grader_req` WHERE term != "Archived" GROUP BY term ORDER BY term DESC');
$terms = $terms_query->fetchAll(PDO::FETCH_COLUMN);
$latest_term = $terms[0];

if (isset($_POST['term']))
{
	$term_to_get = $_POST['term'];
}
else
{
	$term_to_get = $latest_term;
}

//get open/closed status
$requests_open_query = $mthsc_db->query('SELECT value FROM settings WHERE name = "grader_requests_open"');
$requests_open = $requests_open_query->fetchColumn();


$submissions_query = $mthsc_db->prepare('SELECT * FROM `grader_req` WHERE term = ? ORDER BY submitted DESC');
$submissions_result = $submissions_query->execute(array($term_to_get));
if ($submissions_result)
{
	$submissions = $submissions_query->fetchAll();
	
	$download = "User ID,Submitted,Term,Duties,Hrs/Week,Student,Comments\r\n";
	foreach ($submissions as $sub)
	{
		$download .= '"'.$sub['user_id'].'",';
		$download .= '"'.date('F j, Y, g:i a', strtotime($sub['submitted'])).'",';
		$download .= '"'.$sub['term'].'",';
		$download .= '"'.str_replace('"','\'',$sub['duties']).'",';
		$download .= '"'.str_replace('"','\'',$sub['hours']).'",';
		$download .= '"'.str_replace('"','\'',$sub['student']).'",';
		$download .= '"'.str_replace('"','\'',$sub['comments']).'"';
		$download .= "\r\n"; //close row
	}
}
if (!isset($submissions) || count($submissions) < 1)
{
	$message = "Either there was an error retrieving the requests, or no requests have been made.";
}


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Grader Request Submissions</title>
	<meta name="author" content="hedetni">
	<!-- Date: 2016-8-15 -->

<link rel="stylesheet" href="/style/math.css" type="text/css" media="screen" title="no title" charset="utf-8">

<style type="text/css">


</style>

<script src="/style/jquery-1.3.min.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">

function generate_csv()
{
	var csv = <?php echo json_encode($download); ?>;
	var link = document.createElement('a');
	link.download = 'Grader-Requests-<?php echo $term_to_get; ?>.csv';
	link.href = 'data:text/csv;charset=utf-8,'+escape(csv);
	document.body.appendChild(link);
	link.click();
	link.remove();
}

$(document).ready(function() 
{
	$('tr.details').hide();
	
	$('a.detailsLink').click(function() {
		$(this).closest("tr").next().toggle();
		return false;
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
			<h1>Grader Requests</h1>
		</div>
	
		<div id="content">
			<h1>Status</h1>
			<p>Requests are <?php echo $requests_open ? 'Open' : 'Closed'; ?></p>
			<form name="open-close-grader-requests-form" method="POST" action="">
				<p><input type="submit" name="<?php echo $requests_open ? 'close_requests' : 'open_requests'; ?>" value="<?php echo $requests_open ? 'Close Request Form' : 'Open Request Form'; ?>"></input></p>
			</form>
			
			<h1>Grader Request Submissions:</h1><br>
			<?php echo isset($message) ? '<p id="error">'.$message.'</p>' : ""; ?>
			
			<form name="term_selector" method="POST" action="">
				<p><label for="term">Select a term</label>: 
					<select name="term" id="term">
						<option value="">Select a term...</option>
						<?php foreach ($terms as $term): ?>
							<option value="<?php echo $term;?>" <?php echo $term == $term_to_get ? 'selected' : ''; ?>><?php echo $term;?></option>
						<?php endforeach; ?>
							<option value="Archived" <?php echo $term_to_get == 'Archived' ? 'selected' : ''; ?>>Archived</option>
					</select> 
				<input type="submit" name="select_a_term" value="View Term"></input></p>
			</form>
			
			<p><a href="javascript:generate_csv();">Download Requests</a></p>
			
				<?php foreach ($submissions as $sub): ?>
				
				<table style="margin-right:auto; margin-left:auto;" width="80%">
					<col width="25%">
					<col width="75%">
					<tr><th colspan="2">Requested by <b><?php echo $sub['user_id']?></b> on <?php echo date('M j, Y, g:i A', strtotime($sub['submitted']))?> for <b><?php echo $sub['term'];?></b></th></tr>
					<tr>
						<td>Duties</td><td><?php echo $sub['duties'] ?></td>
					</tr>
					<tr>
						<td>Estimated Hours Per Week</td><td><?php echo $sub['hours'] ?></td>
					</tr>
					<tr>
						<td>Requested Student</td><td><?php echo $sub['student'] ?></td>
					</tr>
					<tr>
						<td>Comments</td><td><?php echo $sub['comments'] ?></td>
					</tr>
					</tr>
				</table>
				<br><br>
			<?php endforeach; ?>

		</div>	
	</div>
</body>
</html>