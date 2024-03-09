<?php

/*
//connects to the database, returns a semi-useful error if not accessible.
$link = mysql_connect("mthsc.clemson.edu", "math_cmpt", "cmpt_pa$$");
if(!$link){
	echo "Could not connect to database.  Please try again later.";
	exit;
}

//selects the database
else{
	mysql_select_db("cmpt", $link);
}

mysql_set_charset("utf8-bin",$link);
*/

$host = 'mthsc.clemson.edu';
$host = 'localhost';
$db   = 'cmpt';
$user = 'math_cmpt';
$pass = 'cmpt_pa$$';
$charset = 'utf8';

$dsn = 'mysql:host='.$host.';dbname='.$db.';charset='.$charset;
$opt = array(
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false
);
$mthsc_db = new PDO($dsn, $user, $pass, $opt);

if (isset($_SERVER['REMOTE_USER']))
{
	$user_id = strtoupper($_SERVER['REMOTE_USER']);
}

if (isset($_POST['removeEntry_x']) && isset($_POST['removeEntry_y']))
{
	$remove_query = $mthsc_db->prepare('DELETE FROM substitute_scores WHERE xid = ?;');
	$removeEntry = $remove_query->execute(array($_POST['EntryToRemove']));
	
	//$removeEntry = mysql_query('DELETE FROM substitute_scores WHERE xid = "'.$_POST['EntryToRemove'].'";');

	if (!$removeEntry)
	{
		//$error = 'Entry not removed: ' . mysql_error($link);
		$error = 'Something went wrong, entry not removed.';
	}
	else
	{
		$error = 'Entry removed.';
	}
}

if (isset($_POST['submit_scores']))
{
	$scores = trim($_POST['score_entry']);
	$entries = explode("\n", $scores);
	$count = 0;
	$score_data = array();
	
	foreach ($entries as $line)
	{
		$newLine = str_replace("\t",",",$line);
		$entry = explode(",",trim($newLine));
		//echo "ID: ".$entry[0]." Score: ".$entry[1]."<br>";
		$add_student_query = $mthsc_db->prepare('INSERT INTO substitute_scores (xid,score) VALUES (?,?) ON DUPLICATE KEY UPDATE score = ?;');
		$add_student = $add_student_query->execute(array(trim($entry[0]),trim($entry[1]),trim($entry[1])));
		
		//$add_student = mysql_query('INSERT INTO substitute_scores (xid,score) VALUES ("'.mysql_real_escape_string(trim($entry[0])).'",'.mysql_real_escape_string(trim($entry[1])).') ON DUPLICATE KEY UPDATE score = '.$entry[1].';');
		
		if ($add_student)
		{
			$count++;
			$get_name_query = $mthsc_db->prepare('SELECT substitute_scores.xid,score,name FROM cmpt.substitute_scores LEFT JOIN Banner_info.student_info ON substitute_scores.xid = student_info.xid where substitute_scores.xid = ? LIMIT 1;');
			
			$getName = $get_name_query->execute(array($entry[0]));
			
			//$getName = mysql_query('SELECT substitute_scores.xid,score,name FROM cmpt.substitute_scores LEFT JOIN Banner_info.student_info ON substitute_scores.xid = student_info.xid where substitute_scores.xid = "'.$entry[0].'" LIMIT 1;');
			if (!$getName)
			{
				//$error = 'Error fetching event info: ' . mysql_error($link);
				$error = 'Error fetching student info';
			}
			
			//$row = mysql_fetch_array($getName);
			$row = $get_name_query->fetch();
			
			$score_data[] = array('xid' => $row['xid'],
						'score' => $row['score'],
						'name' => $row['name']);
		}
		else
		{
			$error = 'Record not updated: ' . mysql_error($link);
		}
	}
	$error = $count.' records updated. Showing updated records. <a href="view_substitute_scores.php">Click here</a> to see all entered scores.';
	unset($_POST['submit_scores']);
}
else
{
	/*$get_scores = mysql_query('SELECT substitute_scores.xid,score,name FROM cmpt.substitute_scores LEFT JOIN Banner_info.student_info ON substitute_scores.xid = student_info.xid ORDER BY substitute_scores.xid;');
	if (!$get_scores)
	{
		$error = 'Error fetching event info: ' . mysql_error($link);
	}
	
	$score_data = array();

	while ($row = mysql_fetch_array($get_scores))
	{
		$score_data[] = array('xid' => $row['xid'],
						'score' => $row['score'],
						'name' => $row['name']);
	}
	if (empty($score_data)) {
		$score_data[] = array('xid' => "- -", 
						'score' => "- -",
						'name' => "- -");
	}*/
	
	$get_scores_query = $mthsc_db->query('SELECT substitute_scores.xid,score,name FROM cmpt.substitute_scores LEFT JOIN Banner_info.student_info ON substitute_scores.xid = student_info.xid ORDER BY substitute_scores.xid;');
	$score_data = $get_scores_query->fetchAll();
}


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Substitute CMPT Scores</title>
	<meta name="author" content="hedetni">
	<!-- Date: 2016-6-7 -->

<link rel="stylesheet" href="/style/math.css" type="text/css" media="screen" charset="utf-8">
<script src="sorttable.js" type="text/javascript" charset="utf-8"></script>

<style type="text/css">

table {
	margin-left:auto;
	margin-right:auto;
}

</style>



</head>
<body>
	<div id="main">
		<div id="header">
			<a href="http://www.clemson.edu/math" title="Department Home">
				<img src="/style/math_logo.png" alt="math department logo">
			</a>
			<h1><a href="view_substitute_scores.php">Substitute CMPT Scores</a></h1>
		</div>
	
		<div id="content">
			<?php echo isset($error) ? '<p id="error" class="orangeTxt">'.$error.'</p>' : "" ; ?>
			
			<p>This listing shows manually entered substitute scores for the CMPT. Entries without a name contain an XID that is not in our database (and likely incorrect).</p>
			
			<p><a href="enter_substitute_scores.php">Enter more substitute scores</a></p>
			
			<table class="sortable">
				<tr>
					<th>Name</th>
					<th>XID</th>
					<th>Score</th>
				</tr>
				<?php foreach ($score_data as $entry):?>
					<tr>
						<td><?php echo $entry['name']; ?></td>
						<td><?php echo $entry['xid']; ?></td>
						<td><?php echo $entry['score']; ?></td>
						<?php if ($entry['xid'] != "- -"): ?>
							<td  style="border:0px;background-color:transparent;">
								<form name="remove_<?php echo $entry['xid'] ?>" action="" method="post">
									<input type="hidden" name="EntryToRemove" value="<?php echo $entry['xid']; ?>">
									<input type="image" name="removeEntry" src="del.png" alt="Remove Entry" />
								</form>
							</td>
						<?php endif; ?>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>	
	</div>
</body>
</html>
