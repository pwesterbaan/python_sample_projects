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

if (isset($_GET['class']) && $_GET['class'] != "")
{
	$get_noms = mysql_query('SELECT *, count(award_noms.user_id) FROM `award_noms` JOIN student_info ON award_noms.user_id = student_info.user_id WHERE substring(graduation_date,-4) = "'.$_GET['class'].'" GROUP BY award_noms.user_id');
	if ($get_noms)
	{
		$nominations = array();
	
		while ($row = mysql_fetch_array($get_noms))
		{
			$nominations[$row['user_id']] = array('id' => $row['id'],
									'name' => $row['name'],
									'user_id' => $row['user_id'],
									'profile' => true,
									'count' => $row['count(award_noms.user_id)']);
		}
	
		foreach ($nominations as $student)
		{
			$get_nominators = mysql_query('SELECT nominator,letter FROM `award_noms` WHERE user_id = "'.$student['user_id'].'";');
		
			$nominations[$student['user_id']]['nominators'] = array();
			$nominations[$student['user_id']]['letters'] = array();
		
			while ($row = mysql_fetch_array($get_nominators))
			{
				$nominations[$student['user_id']]['nominators'][] = $row['nominator'];
				$nominations[$student['user_id']]['letters'][] = $row['letter'];
			}
		}
	}
}
else
{
	$get_noms = mysql_query('SELECT *, count(user_id) FROM `award_noms` group by user_id');
	if ($get_noms)
	{
		$nominations = array();
	
		while ($row = mysql_fetch_array($get_noms))
		{
			$nominations[$row['user_id']] = array('id' => $row['id'],
									'name' => $row['name'],
									'user_id' => $row['user_id'],
									'profile' => false,
									'count' => $row['count(user_id)']);
		}
	
		foreach ($nominations as $student)
		{
			$get_nominators = mysql_query('SELECT nominator,letter FROM `award_noms` WHERE user_id = "'.$student['user_id'].'";');
		
			$nominations[$student['user_id']]['nominators'] = array();
			//$nominations[$student['user_id']]['letters'] = array();
		
			while ($row = mysql_fetch_array($get_nominators))
			{
				$nominations[$student['user_id']]['nominators'][] = array($row['nominator'],$row['letter']);
				//$nominations[$student['user_id']]['letters'][] = $row['letter'];
			}
		
			$peek_for_info = mysql_query('SELECT * FROM student_info WHERE user_id = "'.$student['user_id'].'";');
		
			if (mysql_num_rows($peek_for_info) > 0)
			{
				$nominations[$student['user_id']]['profile'] = true;
			}
		
		}
		//print_r($nominations);
	}
}


if (!isset($nominations) || count($nominations) < 1)
{
	//print_r($nominations);
	$confirmation = "Either there was an error retrieving the nominations, or no students have been nominated.";
}


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>MthSc Student Award Nominations</title>
	<meta name="author" content="hedetni">
	<!-- Date: 2016-3-9 -->

<link rel="stylesheet" href="/style/math.css" type="text/css" media="screen" title="no title" charset="utf-8">

<style type="text/css">

p#confirmation {
	color: #C47002;
	font-size: 1.25em;
	padding:0.75em;
	text-align:center;
}

div#filter {
	text-align:center;
}

</style>

<script src="jquery-1.3.min.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">
$(document).ready(function() 
{
	$('tr.nominators').hide();
	
	$('a.nominatorLink').click(function() {
		$(this).closest("tr").next().toggle();
		return false;
	});
	
	
});
</script>

</head>
<body>
	<div id="main">
		<div id="header">
			<a href="http://www.clemson.edu/ces/departments/math/index.html" title="Department Home">
				<img src="/style/math_logo.png" alt="math department logo">
			</a>
		</div>
	
		<div id="content">
			<h1 style="text-align:center;">MthSc Student Awards Nominations</h1>
			
			<?php echo isset($confirmation) ? '<p id="confirmation">'.$confirmation.'</p>' : "</br>"; ?>
			
			<p style="text-align:center;">Profile links will only be displayed for students who have filled out the Student Information Form. <br>Letters of recommendation are linked to from the nominator's name (click on the number of nominations to see the list of nominators for a student).</p>
			
			<p style="text-align:center;">Selecting a year displays only Students who have already filled out the Student Information Form AND have indicated their expected graduation date.</p>
			
			
			<div id="filter">
				<p>
					Filter by year:<br>
					<span style="font-size:1em;"><a href="view_nominations.php">All</a> | <a href="view_nominations.php?class=2016">Senior Award</a> | <a href="view_nominations.php?class=2017">Harden Award</a> | <a href="view_nominations.php?class=2018">Sophomore Award</a> | <a href="view_nominations.php?class=2019">Freshman Award</a></span>
				</p>
				<br>
			</div>
			
			<table style="margin-right:auto; margin-left:auto;">
				<tr>
					<th>Name</th>
					<th>Student ID</th>
					<th class="sorttable_numeric">Information Form</th>
					<th>Number of<br>Nominations</th>
				</tr>
				<?php foreach ($nominations as $nom): ?>
					<tr>
						<td><?php echo $nom['name'] ?></td>
						<td><?php echo $nom['user_id'] ?></td>
						<td style="text-align:center;"><?php if ($nom['profile']): ?><a href="view_student_info.php?id=<?php echo $nom['user_id'] ?>">View Profile</a><?php endif; ?></td>
						<td style="text-align:center;"><a href="#" class="nominatorLink"><?php echo $nom['count'] ?></a></td>
					</tr>
					<tr class="nominators"><td colspan="4" style="text-align:center;"><b>Nominators</b><br>
					<?php foreach ($nominations[$nom['user_id']]['nominators'] as $nominator): ?>
						<?php echo $nominator[1]!="" ? '<a href="'.$nominator[1].'">'.$nominator[0].'</a> (PDF)' : $nominator[0]; ?><br>
					<?php endforeach; ?>
						</td></tr>
				<?php endforeach; ?>
			</table>
			<br>
		</div>	
	</div>
</body>
</html>