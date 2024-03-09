<?php
include('fpci-functions.php');

if (in_array($user_id,$admin_list))
{
	//if saving evaluation
	if (isset($_POST['submit_evaluation']))
	{
		$evaluation = $_POST;
		unset($evaluation['submit_evaluation']);
		
		$projected_teaching_load = $_POST['projected_teaching_load'];
		unset($evaluation['projected_teaching_load']);
		
		if (!isset($evaluation['display_to_instructor']))
		{$evaluation['display_to_instructor'] = 0;}
		
		//echo '<pre>';print_r($evaluation);echo '</pre>';
		
		$existing_teaching_loads = get_all_teaching_loads();
		if (isset($existing_teaching_loads[$evaluation['person_id']][$evaluation['year']+1]))
		{
			// if the value entered is not blank, update it
			if (isset($projected_teaching_load) && $projected_teaching_load != "")
			{
				$update_load_query = $mthsc_db->prepare('UPDATE fpci_teaching_loads SET teaching_load = ?, last_updated_by = ? WHERE person_id = ? AND year = ?');
				$update_load_result = $update_load_query->execute(array($projected_teaching_load, $user_id, $evaluation['person_id'], $evaluation['year']+1));
			}
			// else the value entered is blank, remove it
			else
			{
				$remove_load_query = $mthsc_db->prepare('DELETE FROM fpci_teaching_loads WHERE person_id = ? AND year = ?');
				$remove_load_result = $remove_load_query->execute(array($evaluation['person_id'], $evaluation['year']+1));
			}
		}
		// else no load has been entered for that year for this person
		else
		{
			// if the value entered is not blank, insert it
			if (isset($projected_teaching_load) && $projected_teaching_load != "")
			{
				$insert_load_query = $mthsc_db->prepare('INSERT INTO fpci_teaching_loads (person_id, year, teaching_load, last_updated_by) VALUES (?,?,?,?)');
				$insert_load_result = $insert_load_query->execute(array($evaluation['person_id'], $evaluation['year']+1, $projected_teaching_load, $user_id));
			}
			// else the value entered is blank, ignore it
		}
		
		$update_query = $mthsc_db->prepare('INSERT INTO fpci_evaluations (percentage_entry_id,year,person_id,user_id,teaching_rating,research_rating,service_rating,teaching_score,research_score,service_score,total_score,display_to_instructor) VALUES (:percentage_entry_id,:year,:person_id,:user_id,:teaching_rating,:research_rating,:service_rating,:teaching_score,:research_score,:service_score,:total_score,:display_to_instructor) ON DUPLICATE KEY UPDATE teaching_rating=VALUES(teaching_rating), research_rating=VALUES(research_rating), service_rating=VALUES(service_rating), teaching_score=VALUES(teaching_score), research_score=VALUES(research_score), service_score=VALUES(service_score), total_score=VALUES(total_score), display_to_instructor=VALUES(display_to_instructor);');
		$result = $update_query->execute($evaluation);
		if ($result) {$message = "Evaluation Saved";}else {$message = "Something went wrong, evaluation not saved";}
	}
	
	//if entering new percentages
	if (isset($_POST['add_percentages']))
	{
		$submission = $_POST;
		
		$new_teaching_load = $submission['teaching_load'];
		
		$existing_teaching_loads = get_all_teaching_loads();
		if (isset($existing_teaching_loads[$submission['person_id']][$submission['year']]))
		{
			// if the value entered is not blank, update it
			if (isset($new_teaching_load) && $new_teaching_load != "")
			{
				$update_load_query = $mthsc_db->prepare('UPDATE fpci_teaching_loads SET teaching_load = ?, last_updated_by = ? WHERE person_id = ? AND year = ?');
				$update_load_result = $update_load_query->execute(array($new_teaching_load, $user_id, $submission['person_id'], $submission['year']));
			}
			// else the value entered is blank, remove it
			else
			{
				$remove_load_query = $mthsc_db->prepare('DELETE FROM fpci_teaching_loads WHERE person_id = ? AND year = ?');
				$remove_load_result = $remove_load_query->execute(array($submission['person_id'], $submission['year']));
			}
		}
		// else no load has been entered for that year for this person
		else
		{
			// if the value entered is not blank, insert it
			if (isset($new_teaching_load) && $new_teaching_load != "")
			{
				$insert_load_query = $mthsc_db->prepare('INSERT INTO fpci_teaching_loads (person_id, year, teaching_load, last_updated_by) VALUES (?,?,?,?)');
				$insert_load_result = $insert_load_query->execute(array($submission['person_id'], $submission['year'], $new_teaching_load, $user_id));
			}
			// else the value entered is blank, ignore it
		}
		
		unset($submission['teaching_load']);
		unset($submission['add_percentages']);
		$insert_query = $mthsc_db->prepare("INSERT INTO fpci_percentages (year, person_id, user_id, division, summer_course_credits, summer_teaching_percentage, summer_research_days, summer_research_percentage, summer_service_days, summer_service_percentage, fall_teaching_percentage, fall_research_percentage, fall_service_percentage, spring_teaching_percentage, spring_research_percentage, spring_service_percentage, overall_teaching_percentage, overall_research_percentage, overall_service_percentage) VALUES (:year, :person_id, :user_id, :division, :summer_course_credits, :summer_teaching_percentage, :summer_research_days, :summer_research_percentage, :summer_service_days, :summer_service_percentage, :fall_teaching_percentage, :fall_research_percentage, :fall_service_percentage, :spring_teaching_percentage, :spring_research_percentage, :spring_service_percentage, :overall_teaching_percentage, :overall_research_percentage, :overall_service_percentage)");
		$result = $insert_query->execute($submission);
		if ($result) {$message = "Percentages Saved";}else {$message = "Something went wrong, percentages not saved";}
	}
	
	
	//get previous years in database
	$year_query = $mthsc_db->query("SELECT year FROM `fpci_percentages` GROUP BY year ORDER BY year DESC");
	$previous_years = $year_query->fetchAll(PDO::FETCH_COLUMN);

	if (isset($_POST['view_year'])) //show only that year
	{
		$displayed_year = $_POST['year_to_display'];
		$displaying_current_year = false;
		
		//get entries
		$entries_query = $mthsc_db->prepare("SELECT * FROM `fpci_percentages` WHERE year = ?");
		$entries_query->execute(array($_POST['year_to_display']));
		$people = $entries_query->fetchAll();
		
		foreach ($people as &$divperson)
		{
			if ($divperson['division']==NULL || $divperson['division']== "")
			{
				$divperson['division'] = "Not Set";
			}
		}
		unset($divperson);
	}
	else //show current year
	{
		$displayed_year = get_current_evaluation_year();
		//get list of people who need to be evaluated
		$evaluation_list = get_current_evaluation_list();
		
		
		//echo '<pre>';print_r($teaching_loads);echo '</pre>';
		
		$displaying_current_year = true;
		
		$people = array();
		foreach ($evaluation_list as $person_id)
		{
			$person_percentages = get_percentages($person_id,$displayed_year);
			
			if ($person_percentages == NULL)
			{
				$division = get_division_from_person_id($person_id);
				if ($division == NULL || $division == "")
				{
					$division = "Not Set";
				}
				$people[] = array("user_id" => get_username_from_person_id($person_id),
								"person_id"=> $person_id,
								"division" => $division);
			}
			else
			{
				//$person_percentages['division'] = $division;
				$people[] = $person_percentages;
			}
		}
	}
	
	$teaching_loads = get_all_teaching_loads();
	
	//echo '<pre>';print_r($people);echo '</pre>';
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
	
	<title>FPCI | Admin View Activity Entries</title>

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
			<div id="app_title">FPCI</div>
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
							<?php if ($year != get_current_evaluation_year()): ?>
								<option value="<?php echo $year;?>" <?php echo $year == $displayed_year ? 'selected' : ''; ?> ><?php echo $year-1; echo '-'; echo $year;?></option>
							<?php endif; ?>
						<?php endforeach; ?>
					</select>
					<input type="submit" name="view_year" value="View Previous Year"></input>
				</form>
				
				<?php if (isset($_POST['view_year'])): ?>
					<p><a href="">View Current Year</a></p>
				<?php endif; ?>
				
				<hr>
				
				<?php if (!isset($_POST['view_year'])): ?>
					<p>The following people are on the evaluation list for the current year. A checkmark by a name means all fields have been filled in the evaluation of that faculty member. Click the links to view/edit their percentage activity entry and view/edit their evaluation.</p>
					<h2><a href="admin-bulk-evaluate.php">Bulk edit FPCI ratings</a></h2>
				<?php else: ?>
					<p>The following people submitted activity percentages for the selected year:</p>
				<?php endif; ?>
				
				<table id="faculty_table" class="styled sortable">
					<tr>
						<th scope="col">Person</th>
						<th scope="col">User ID</th>
						<th scope="col">Division</th>
						<th scope="col">Teaching<br>Load</th>
						<th scope="col">Overall<br>Teaching</th>
						<th scope="col">Overall<br>Research</th>
						<th scope="col">Overall<br>Service</th>
						<th scope="col">View Full Entry</th>
						<th scope="col">Teaching<br>Rating</th>
						<th scope="col">Research<br>Rating</th>
						<th scope="col">Service<br>Rating</th>
						<th scope="col">Overall<br>Score</th>
						<th scope="col"><?php echo isset($_POST['view_year']) ? 'View Evaluation' : 'Set Ratings'; ?></th>
						<th scope="col">Displayed<br>to Instructor</th>
					</tr>
					<?php foreach ($people as $person):?>
						<?php if (in_array($user_id,$evaluators[$person['division']])): ?>
						<tr>
							<td><?php echo get_name_from_person_id($person['person_id']); ?> <?php echo isset($person['entry_id']) && is_evaluation_complete($person['entry_id']) ? "&#10003;" : ""; ?></td>
							<td><?php echo $person['user_id']; ?></td>
							<td><small><?php echo $person['division']; ?></small></td>
							<td class="text-center"><?php echo isset($teaching_loads[$person['person_id']][$displayed_year]) ? $teaching_loads[$person['person_id']][$displayed_year] : ""; ?></td>
							<td class="text-center"><?php echo isset($person['overall_teaching_percentage']) ? $person['overall_teaching_percentage'].'%' : "--"; ?></td>
							<td class="text-center"><?php echo isset($person['overall_research_percentage']) ? $person['overall_research_percentage'].'%' : "--"; ?></td>
							<td class="text-center"><?php echo isset($person['overall_service_percentage']) ? $person['overall_service_percentage'].'%' : "--"; ?></td>
							<td class="text-center">
								<?php if (isset($person['entry_id'])): ?>
									<a href="admin-view-percentage-entry.php?entry=<?php echo $person['entry_id']; ?>">
										<?php echo $displaying_current_year ? 'View/Edit Percentages' : 'View Percentages';?>
									</a>
								<?php else: ?>
									<a href="admin-enter-percentages.php?person=<?php echo $person['person_id']; ?>">Enter Percentages</a>
								<?php endif; ?>
							</td>
							<?php if (isset($person['entry_id'])): ?>
								<?php $evaluation = get_evaluation_from_percentage_entry_id($person['entry_id']); ?>
								<?php if ($evaluation != NULL): ?>										
									<td class="text-center"><?php echo $evaluation['teaching_rating'];?></td>
									<td class="text-center"><?php echo $evaluation['research_rating'];?></td>
									<td class="text-center"><?php echo $evaluation['service_rating'];?></td>
									<td class="text-center"><strong><?php echo $evaluation['total_score'];?></strong></td>
									<td class="text-center">
										<a href="admin-evaluate.php?entry=<?php echo $person['entry_id'];?>">
											<?php echo $displaying_current_year ? 'View/Edit Evaluation' : 'View Evaluation' ;?>
										</a>
									</td>
									<td class="text-center"><?php echo $evaluation['display_to_instructor'] ? "Yes" : "No";?></td>
								<?php else: ?>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
									<td class="text-center">
										<a href="admin-evaluate.php?entry=<?php echo $person['entry_id'];?>">Enter Evaluation</a>
									</td>
									<td></td>
								<?php endif; ?>
							<?php else: ?>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
							<?php endif; ?>
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