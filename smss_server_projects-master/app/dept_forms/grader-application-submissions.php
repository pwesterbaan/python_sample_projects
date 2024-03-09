<?php

//connects to the database, returns a semi-useful error if not accessible.
$link = mysql_connect("mthsc.clemson.edu", "forms", "d8ta_c0l");
if(!$link){
	echo "Could not connect to database.  Please try again later.";
	exit;
}
//selects the database
else{
	mysql_select_db("forms", $link);
}

mysql_set_charset("utf8-bin",$link);

date_default_timezone_set('America/New_York');

//get terms
$get_terms = mysql_query('SELECT term FROM `grader_app` WHERE term != "Archived" GROUP BY term ORDER BY term DESC');
$terms = array();
while ($row = mysql_fetch_array($get_terms))
{
	$terms[] = $row['term'];
}
$latest_term = $terms[0];

if (isset($_POST['term']))
{
	$term_to_get = $_POST['term'];
}
else
{
	$term_to_get = $latest_term;
}

$get_submissions = mysql_query('SELECT * FROM `grader_app` WHERE term = "'.$term_to_get.'" ORDER BY last_updated DESC');
if ($get_submissions)
{
	$submissions = array();

	while ($row = mysql_fetch_array($get_submissions))
	{
		$submissions[] = array('xid' => $row['xid'],
							'name' => $row['name'],
							'user_id' => $row['user_id'],
							'term' => $row['term'],
							'job' => $row['job'],
							'reference' => $row['reference'],
							'phone' => $row['phone'],
							'major' => $row['major'],
							'semester' => $row['semester'],
							'courses' => $row['courses'],
							'hours' => $row['hours'],
							'comments' => $row['comments'],
							'last_updated' => date('M j, Y, g:i A', strtotime($row['last_updated'])));
	}
	
	// build download
	$rows = "Term,XID,Name,Username,Phone,Considered For,Major,Semester,Reference,Hours,Courses,Comments,Last Updated\r\n";
	foreach ($submissions as $sub)
	{
		$rows .= '"'.$sub['term'].'",';
		$rows .= '"'.$sub['xid'].'",';
		$rows .= '"'.$sub['name'].'",';
		$rows .= '"'.$sub['user_id'].'",';
		$rows .= '"'.$sub['phone'].'",';
		$rows .= '"'.$sub['job'].'",';
		$rows .= '"'.$sub['major'].'",';
		$rows .= '"'.$sub['semester'].'",';
		$rows .= '"'.$sub['reference'].'",';
		$rows .= '"'.$sub['hours'].'",';
		$rows .= '"'.$sub['courses'].'",';
		$rows .= '"'.$sub['comments'].'",';
		$rows .= '"'.$sub['last_updated'].'",';
		$rows .= "\r\n"; //close row
	}
}
if (!isset($submissions) || count($submissions) < 1)
{
	$message = "Either there was an error retrieving the nominations, or no students have been nominated.";
}


function get_semester_from_code($code)
{
	switch ($code)
	{
		case 1:
			return '1st Semester Freshman';
		case 2:
			return '2nd Semester Freshman';
		case 3:
			return '1st Semester Sophomore';
		case 4:
			return '2nd Semester Sophomore';
		case 5:
			return '1st Semester Junior';
		case 6:
			return '2nd Semester Junior';
		case 7:
			return '1st Semester Senior';
		case 8:
			return '2nd Semester Senior';
		case 9:
			return 'Grad Student';
		
	}
}



?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Grader Application Submissions</title>
	<meta name="author" content="hedetni">
	<!-- Date: 2016-8-15 -->

<link rel="stylesheet" href="/style/math.css" type="text/css" media="screen" title="no title" charset="utf-8">

<style type="text/css">


</style>

<script src="/style/jquery-1.3.min.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">

function generate_csv()
{
	var csv = <?php echo json_encode($rows); ?>;
	var link = document.createElement('a');
	link.download = 'Grader-Applications.csv';
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
			<h1>Grader Application Submissions</h1>
		</div>
	
		<div id="content">
			<h1>Application Submissions:</h1>
			
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
			
			<?php echo isset($message) ? '<p id="error">'.$message.'</p>' : ""; ?>
			
			<p><button type="button" name="download" onclick="generate_csv()" >Download as CSV</button></p>
			
			<table style="margin-right:auto; margin-left:auto;" width="100%">
				<tr>
					<th>XID</th>
					<th>Name</th>
					<th>User ID</th>
					<th>Major</th>
					<th>Term</th>
					<th>Last Updated</th>
					<th>View</th>
				</tr>
				<?php foreach ($submissions as $sub): ?>
					<tr>
						<td><?php echo $sub['xid'] ?></td>
						<td><?php echo $sub['name'] ?></td>
						<td><?php echo $sub['user_id'] ?></td>
						<td><?php echo $sub['major'] ?></td>
						<td><?php echo $sub['term'] ?></td>
						<td><?php echo $sub['last_updated'] ?></td>
						<td style="text-align:center;"><a href="#" class="detailsLink">View Details</a></td>
					</tr>
					<tr class="details"><td colspan="9" style="background-color:#ddd;padding:1em;">
						<table width="100%">
							<tr>
								<th style="text-align:center;background-color:#ccc;">Phone</th>
								<th style="text-align:center;background-color:#ccc;">Major</th>
								<th style="text-align:center;background-color:#ccc;">Semester</th>
								<th style="text-align:center;background-color:#ccc;">Hours</th>
								<th style="text-align:center;background-color:#ccc;">Considered For</th>
								<th style="text-align:center;background-color:#ccc;">Reference</th>
							</tr>
							<tr>
								<td style="text-align:center;background-color:transparent;"><?php echo $sub['phone'] ?></td>
								<td style="text-align:center;background-color:transparent;"><?php echo $sub['major'] ?></td>
								<td style="text-align:center;background-color:transparent;"><?php echo get_semester_from_code($sub['semester']); ?></td>
								<td style="text-align:center;background-color:transparent;"><?php echo $sub['hours'] ?></td>
								<td style="text-align:center;background-color:transparent;"><?php echo $sub['job'] ?></td>
								<td style="text-align:center;background-color:transparent;"><?php echo $sub['reference'] ?></td>
							</tr>
							<tr><th colspan="6" style="text-align:center;background-color:#ccc;">Courses</th></tr>
							<tr><td colspan="6" style="text-align:center;background-color:transparent;"><?php echo $sub['courses'] ?></td></tr>
							<tr><th colspan="6" style="text-align:center;background-color:#ccc;">Comments</th></tr>
							<tr><td colspan="6" style="text-align:center;background-color:transparent;"><?php echo $sub['comments'] ?></td></tr>
						</table>
					</td></tr>
				<?php endforeach; ?>
			</table>
			
		</div>	
	</div>
</body>
</html>