<?php

$user_id = strtoupper($_SERVER['REMOTE_USER']);

//connects to the database, returns a semi-useful error if not accessible.
$link = mysql_connect("mthsc.clemson.edu", "math_dept_info", "cu_tigers!");
if(!$link){
	echo "Could not connect to database.  Please try again later.";
	exit;
}

//selects the database...this is independent of the year etc.
else{
	mysql_select_db("dept_info", $link);
}


$current_faculty = array();
$current_faculty[] = 'HEDETNI';

$faculty_request = mysql_query('SELECT username FROM  `people_to_lists_link`  JOIN  `person` ON person.person_id = people_to_lists_link.person_id WHERE list_id =2');

if (!$faculty_request)
{
	$error = 'Error accessing database: ' . mysql_error($link);
}

while ($row = mysql_fetch_array($faculty_request))
{
	$current_faculty[] = $row['username'];
}




?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Summer Teaching Preferences</title>
	<meta name="author" content="hedetni">
	<!-- Date: 2016-2-23 -->
</head>
<body style="background-color:#555;">
	
	<?php echo isset($error) ? '<p id="error">'.$error.'</p>' : "" ; ?>

	<?php if (in_array($user_id,$current_faculty)): ?>
	<center><iframe src="https://docs.google.com/forms/d/e/1FAIpQLSdqRmtJY9AEpFXwuYtFT_9J76x5c74brkf_aqddM75ZO7XuEg/viewform?embedded=true" width="800" height="1000" frameborder="0" marginheight="0" marginwidth="0">Loading...</iframe></center>
	<?php endif; ?>

</body>
</html>

