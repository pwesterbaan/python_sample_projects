<?php
include('fpci-functions.php');

if (in_array($user_id,$admin_list))
{
	
	
	
	//get previous years in database
	$year_query = $mthsc_db->query("SELECT year FROM `fpci_percentages` GROUP BY year");
	$previous_years = $year_query->fetchAll(PDO::FETCH_COLUMN);

	if (isset($_POST['view_year'])) //show only that year
	{
		$displayed_year = $_POST['year_to_display'];
		
		//get entries
		$entries_query = $mthsc_db->prepare("SELECT * FROM `fpci_percentages` LEFT JOIN dept_info.primary_subfaculty USING (person_id) LEFT JOIN dept_info.subfaculties using (subfaculty_id) WHERE year = ?");
		$entries_query->execute(array($_POST['year_to_display']));
		$people = $entries_query->fetchAll();
		
		foreach ($people as &$divperson)
		{
			if ($divperson['division']==NULL)
			{
				$divperson['division'] = "Not Set";
			}
		}
	}
	else //show current year
	{
		$displayed_year = get_current_evaluation_year();
		//get list of people who need to be evaluated
		$evaluation_list = get_current_evaluation_list();
		
		$people = array();
		foreach ($evaluation_list as $person_id)
		{
			$person_percentages = get_percentages($person_id,$displayed_year);
			$division = get_division_from_person_id($person_id);
			if ($division == NULL)
			{
				$division = "Not Set";
			}
			
			if ($person_percentages == NULL)
			{
				$people[] = array("user_id" => get_username_from_person_id($person_id),
								"person_id"=> $person_id,
								"division" => $division);
			}
			else
			{
				$person_percentages['division'] = $division;
				$people[] = $person_percentages;
			}
		}
	}
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
	<!-- Date: 2019-1-14 -->
	
	<title>FPCI DEMO | Admin View Activity Entries</title>

<link rel="stylesheet" href="/style/math-internal.css" type="text/css" media="screen" charset="utf-8">

<style type="text/css">


</style>

<script src="/style/jquery-3.2.1.min.js" type="text/javascript" charset="utf-8"></script>
<script src="/style/sorttable.js" type="text/javascript" charset="utf-8"></script>


<script type="text/javascript">
$(document).ready(function(){
	var myTH = document.getElementsByTagName("th")[0];
	sorttable.innerSortFunction.apply(myTH, []);
	
}); 
</script>

</head>

<body>
	<div id="main">
		<div id="header">
			<div id="app_title">FPCI DEMO</div>
			<a href="http://www.clemson.edu/math" title="School Home"><img src="/style/math_logo.png" alt="school logo"></a>
		</div>
		<ul id="nav">
			<?php echo get_nav(); ?>
		</ul>
		<?php echo isset($message) ? '<div id="message">'.$message.'</div>' : '' ?>
		<?php echo isset($error) ? '<div id="error">'.$error.'</div>' : '' ?>
		
		<div id="content">
			<?php if (in_array($user_id,$admin_list)): ?>
				<h1>Activity Percentage Entries for <?php echo $displayed_year-1;echo '-';echo$displayed_year; ?></h1>
			
				<form name="year_selection_form" method="POST" action="">
					<label for="year_to_display">View Previous Year</label>: 
					<select name="year_to_display" id="year_to_display">
						<?php foreach ($previous_years as $year):?>
							<option value="<?php echo $year;?>"><?php echo $year-1; echo '-'; echo $year;?></option>
						<?php endforeach; ?>
					</select>
					<input type="submit" name="view_year" value="View Previous Year"></input>
				</form>
				
				<?php if (isset($_POST['view_year'])): ?>
					<p><a href="">View Current Year</a></p>
				<?php endif; ?>
				
				<hr>
				
				<?php if (!isset($_POST['view_year'])): ?>
					<p>The following people are on the evaluation list for the current year. A checkmark by a name means all fields have been filled in the evaluation of that faculty member. Click the links to view/edit their percentage activity entry and view/edit their evaluation. <a href="admin-bulk-evaluate.php">Bulk edit FPCI ratings</a>.</p>
				<?php else: ?>
					<p>The following people submitted activity percentages for the selected year:</p>
				<?php endif; ?>
				
				<table id="faculty_table" class="styled sortable">
					<tr>
						<th scope="col">Person</th>
						<th scope="col">User ID</th>
						<th scope="col">Division</th>
						<th scope="col">Overall<br>Teaching</th>
						<th scope="col">Overall<br>Research</th>
						<th scope="col">Overall<br>Service</th>
						<th scope="col">View Full Entry</th>
						<th scope="col"><?php echo isset($_POST['view_year']) ? 'View Evaluation' : 'Set Ratings'; ?></th>
					</tr>
					<?php foreach ($people as $person):?>
						<?php if (in_array($user_id,$evaluators[$person['division']])): ?>
						<tr>
							<td><?php echo get_name_from_person_id($person['person_id']); ?> <?php echo isset($person['entry_id']) && is_evaluation_complete($person['entry_id']) ? "&#10003;" : ""; ?></td>
							<td><?php echo strtoupper(substr(preg_replace('/[0-9]+/', '', md5($person['user_id'])),0,6)); ?></td>
							<td><small><?php echo get_division_from_person_id($person['person_id']); ?></small></td>
							<td class="text-center"><?php echo isset($person['overall_teaching_percentage']) ? $person['overall_teaching_percentage'].'%' : "--"; ?></td>
							<td class="text-center"><?php echo isset($person['overall_research_percentage']) ? $person['overall_research_percentage'].'%' : "--"; ?></td>
							<td class="text-center"><?php echo isset($person['overall_service_percentage']) ? $person['overall_service_percentage'].'%' : "--"; ?></td>
							<td class="text-center">
								<?php if (isset($person['entry_id'])): ?>
									<a href="admin-view-percentage-entry.php?entry=<?php echo $person['entry_id']; ?>">View/Edit Percentages</a>
								<?php else: ?>
									<?php if (in_array($user_id,$evaluators['Director'])): ?>
										<a href="admin-enter-percentages.php?person=<?php echo $person['person_id']; ?>">Enter Percentages</a>
									<?php endif; ?>
								<?php endif; ?>
							</td>
							<td class="text-center">
								<?php echo isset($person['entry_id']) ? '<a href="admin-evaluate.php?entry='.$person['entry_id'].'">View/Edit Evaluation</a>' : "";?>
							</td>
						</tr>
						<?php endif; ?>
					<?php endforeach; ?>
				</table>
			<?php endif; ?>
			
		</div>	
		<div id="footer"><a href="/dept_forms/website_error.py" target="_blank">Report a problem with this website</a></div>
	</div>
	
</body>
</html>