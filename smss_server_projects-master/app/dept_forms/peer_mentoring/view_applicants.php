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

if (isset($_SERVER['REMOTE_USER']))
{
	$user_id = strtoupper($_SERVER['REMOTE_USER']);
}

$get_apps = mysql_query('SELECT * FROM `peer_mentoring`');
if ($get_apps)
{
	$applicants = array();
	
	while ($row = mysql_fetch_array($get_apps))
	{
		$applicants[] = array('id' => $row['id'],
							'name' => $row['name'],
							'email' => $row['email']);
	}
}
if (!isset($applicants) || count($applicants) < 1)
{
	//print_r($applicants);
	$confirmation = "Either there was an error retrieving the nominations, or no students have been nominated.";
}


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>MthSc Peer Tutoring Applicants</title>
	<meta name="author" content="hedetni">
	<!-- Date: 2016-3-9 -->

<link rel="stylesheet" href="/style/math.css" type="text/css" media="screen" title="no title" charset="utf-8">

<style type="text/css">


</style>

<script src="jquery-1.7.2.js" type="text/javascript" charset="utf-8"></script>


</head>
<body>
	<div id="main">
		<div id="header">
			<a href="http://www.clemson.edu/math" title="Department Home">
				<img src="/style/math_logo.png" alt="math department logo">
			</a>
		</div>
	
		<div id="content">
			<h1 style="text-align:center;">MthSc Peer Tutoring Applicants</h1>
			
			<?php echo isset($confirmation) ? '<p id="confirmation">'.$confirmation.'</p>' : "</br>"; ?>
			
			<table style="margin-right:auto; margin-left:auto;">
				<tr>
					<th>Name</th>
					<th>Email</th>
					<th>Application</th>
				</tr>
				<?php foreach ($applicants as $app): ?>
					<tr>
						<td><?php echo $app['name'] ?></td>
						<td><?php echo $app['email'] ?></td>
						<td style="text-align:center;"><a href="view_application.php?id=<?php echo $app['id'] ?>">View Application</a></td>
					</tr>
				<?php endforeach; ?>
			</table>
			
		</div>	
	</div>
</body>
</html>