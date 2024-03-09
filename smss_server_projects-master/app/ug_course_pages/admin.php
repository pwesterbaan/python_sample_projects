<?php
include($_SERVER['CONTEXT_DOCUMENT_ROOT'].'/global/lib/mysql_patch.php');
//connects to the database, returns a semi-useful error if not accessible.
//$link = mysql_connect("mthsc.clemson.edu", "ug_pages", "crs_info_7");
$link = mysql_connect("localhost", "ug_pages", "crs_info_7");
if(!$link){
    echo "Could not connect to database.  Please try again later.";
    exit;
}

//selects the database...this is independent of the year etc.
else{
    mysql_select_db("ug_pages", $link);
}

if (isset($_POST['removeEditor_x']) && isset($_POST['removeEditor_y']))
{
    $removeEditor = mysql_query('DELETE FROM editors
										WHERE course_id = '.$_POST['CourseToRemoveFrom'].' 
										AND employee_username = "'.$_POST['EditorToRemove'].'"');

    if (!$removeEditor)
    {
	$error = 'Editor not removed: ' . mysql_error($link);
    }
    else
    {
	$error = 'User successfully removed.';
    }
}

if (isset($_POST['add_editor']))
{
    $insertEditor = mysql_query('INSERT INTO editors (course_id,employee_username) VALUES ("'.$_POST['course_id_for_adding'].'","'.strtoupper($_POST['user_id_to_add']).'"); ');
    
    if (!$insertEditor)
    {
	$error = 'User not added: ' . mysql_error($link);
    }
    else
    {
	$error = 'User successfully added to list.';
    }
}

$courses = array();
$get_course_ids = mysql_query('SELECT * FROM page_list AS pl LEFT JOIN course.course_list AS cl ON cl.course_id = pl.course_id ORDER BY prefix, course_num');
while ($row = mysql_fetch_array($get_course_ids))
{
    $courses[] = array('course_id' => $row['course_id'],
		       'prefix' => $row['prefix'],
		       'course_num' => $row['course_num'],
		       'description' => $row['description']);
}
//print_r($courses);

$course_editors = array();
foreach ($courses as $course)
{
    $id = $course['course_id'];
    $course_editors[$id] = array();
    $get_editors = mysql_query('SELECT employee_username FROM  `editors` WHERE course_id = '.$id.' ORDER BY employee_username');
    while ($row = mysql_fetch_array($get_editors))
    {
	$course_editors[$id][] = $row['employee_username'];
    }
}
//print_r($course_editors);

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Course Pages Editor Admin</title>
	<link rel="stylesheet" href="/style/math.css" type="text/css" media="screen" title="no title" charset="utf-8">
	
	<style type="text/css">

	</style>
</head>

<body>
<div id="main">

	<div id="header">
			<a href="http://www.clemson.edu/math" title="Department Home">
				<img src="/style/math_logo.png" alt="math department logo">
			</a>
			<h1><a href="course_pages">Coordinated Course Pages</a></h1>
		</div>
	
		<div id="content">
			<h1>Course Page Editor Admin</h1>
			<br>
			<?php echo isset($error) ? '<p id="error">'.$error.'</p>' : "" ; ?>

			<div style="text-align:center;">
				<center><h2>Add New Editor</h2></center>
				<form id="add_to_editing_list" action="" method="post">
					<p><label for="course_id">Course</label>: <select name="course_id_for_adding" id="course_id">
						<?php foreach ($courses as $course): ?>
							<option value="<?php echo $course['course_id']; ?>"><?php echo $course['prefix']." ".$course['course_num']; ?></option>
						<?php endforeach; ?>
					</select>
					<label for="user_id">User ID: </label><input name="user_id_to_add" id="user_id" size="15" type="text"></input></p>
					<center><input type="submit" name="add_editor" value="Allow User to Edit Selected Course Page"></center>
				</form>
			</div>
			<br><hr><br>
			<?php foreach ($courses as $course): ?>
				<table style="text-align:center;margin-left:auto;margin-right:auto;" width="30%" border=1>
					<tr>
						<th colspan="2" style="padding:0.5em;"><?php echo $course['prefix']." ".$course['course_num']; ?></th>
					</tr>
					<?php foreach ($course_editors[$course['course_id']] as $editor): ?>
						<tr><td><center><?php echo $editor; ?></center></td>
							<td style="padding:0.5em;">
								<form name="remove_<?php echo $course['course_id'].'_'.$editor ?>" action="" method="post">
									<input type="hidden" name="EditorToRemove" value="<?php echo $editor ?>">
									<input type="hidden" name="CourseToRemoveFrom" value="<?php echo $course['course_id'] ?>">
									<input type="image" name="removeEditor" src="static/images/del.png" alt="Remove Editor" />
								</form>
							</td>
						</tr>
			
					<?php endforeach; ?>
				</table>
				<br>
	
					<?php endforeach; ?>

		</div>
	</div>
</body>
</html>
