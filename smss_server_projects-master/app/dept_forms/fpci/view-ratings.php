<?php
include('fpci-functions.php');

$person_id = get_person_id($user_id);
//$person_id = 8;
//$person_id = 10;
if ($person_id != 0)
{
	//get percentage entry ids for user
	$evaluations = get_evaluations_for_person_id($person_id);
	//echo '<pre>';print_r($evaluations);echo '</pre>';
}
else
{
	$error = "This form is for School of Mathematical and Statistical Sciences faculty only.";
}

?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
	"http://www.w3.org/TR/html4/loose.dtd">


<html lang="en-US">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="author" content="hedetni">
	<!-- Date: 2019-7-30 -->
	
	<title>FPCI | View Ratings</title>

<link rel="stylesheet" href="/style/math-internal.css" type="text/css" media="screen" charset="utf-8">

<style type="text/css">
label.hide_label {display:none;}

</style>

<script src="/style/jquery-3.2.1.min.js" type="text/javascript" charset="utf-8"></script>

<script type="text/javascript">

function toggle_stats(year)
{
	$('#stats_'+year).slideToggle();
}

$(document).ready(function(){
	
	
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
			<?php if ($person_id != 0): ?>
				<h1>FPCI Ratings for <?php echo get_name_from_person_id($person_id); ?></h1>
				<p><?php echo get_subfaculty_from_person_id($person_id); ?></p>
				
				<?php if (count($evaluations) > 0): ?>				
					<?php foreach ($evaluations as $evaluation): ?>
						<?php if ($evaluation['display_to_instructor']): ?>
							<h2>Ratings for <?php echo $evaluation['year']-1; echo ' - '; echo $evaluation['year']; ?></h2>
							<table class="styled">
								<tr>
									<th scope="col">Activity</th>
									<th scope="col">Percentages</th>
									<th scope="col">FPCI Rating</th>
									<th scope="col">FPCI Score</th>
								</tr>
								<tr>
									<td>Teaching</td>
									<td class="text-center"><?php echo $evaluation['overall_teaching_percentage']?>%</td>
									<td class="text-center"><?php echo $evaluation['teaching_rating'];?></td>
									<td class="text-center"><?php echo $evaluation['teaching_score'];?></td>
								</tr>
								<tr>
									<td>Research</td>
									<td class="text-center"><?php echo $evaluation['overall_research_percentage']?>%</td>
									<td class="text-center"><?php echo $evaluation['research_rating'];?></td>
									<td class="text-center"><?php echo $evaluation['research_score'];?></td>
								</tr>
								<tr>
									<td>Service</td>
									<td class="text-center"><?php echo $evaluation['overall_service_percentage']?>%</td>
									<td class="text-center"><?php echo $evaluation['service_rating'];?></td>
									<td class="text-center"><?php echo $evaluation['service_score'];?></td>
								</tr>
								<tr>
									<td colspan="3" style="text-align:right;">Overall Score</td>
									<td class="text-center"><?php echo $evaluation['total_score'];?></td>
								</tr>
							</table>
							
							<p><a href="javascript:toggle_stats(<?php echo $evaluation['year'];?>)">View Stats</a></p>
							<?php $averages = get_averages_for_division($evaluation['division'],$evaluation['year']); ?>
							<?php $medians = get_medians_for_division($evaluation['division'],$evaluation['year']); ?>
							<?php $counts = get_score_counts_for_division($evaluation['division'],$evaluation['year']); ?>
							<div id="stats_<?php echo $evaluation['year'];?>" style="display:none;">
								<h3>Stats for <?php echo $evaluation['division']; ?>, <?php echo $evaluation['year']-1; echo ' - '; echo $evaluation['year']; ?></h3>
								<table class="styled">
									<tr>
										<th scope="col">Activity</th>
										<th scope="col">Median Rating</th>
										<th scope="col">Average Rating</th>
										<th scope="col"># Rating (0,1]</th>
										<th scope="col"># Rating (1,2]</th>
										<th scope="col"># Rating (2,3]</th>
										<th scope="col"># Rating (3,4]</th>
										<th scope="col"># Rating (4,5]</th>
										<th scope="col"># Rating (5,6]</th>
										<th scope="col"># Rating (6,7]</th>
									</tr>
									<?php if ($evaluation['division'] != "Mathematics and Statistics Education Division"):?>
									<tr>
										<td>Teaching</td>
										<td class="text-center"><?php echo $medians['median_teaching_rating'];?></td>
										<td class="text-center"><?php echo $averages['average_teaching_rating'];?></td>
										<td class="text-center"><?php echo $counts['teaching_rating']['0-1'];?></td>
										<td class="text-center"><?php echo $counts['teaching_rating']['1-2'];?></td>
										<td class="text-center"><?php echo $counts['teaching_rating']['2-3'];?></td>
										<td class="text-center"><?php echo $counts['teaching_rating']['3-4'];?></td>
										<td class="text-center"><?php echo $counts['teaching_rating']['4-5'];?></td>
										<td class="text-center"><?php echo $counts['teaching_rating']['5-6'];?></td>
										<td class="text-center"><?php echo $counts['teaching_rating']['6-7'];?></td>
									</tr>
									<tr>
										<td>Research</td>
										<td class="text-center"><?php echo $medians['median_research_rating'];?></td>
										<td class="text-center"><?php echo $averages['average_research_rating'];?></td>
										<td class="text-center"><?php echo $counts['research_rating']['0-1'];?></td>
										<td class="text-center"><?php echo $counts['research_rating']['1-2'];?></td>
										<td class="text-center"><?php echo $counts['research_rating']['2-3'];?></td>
										<td class="text-center"><?php echo $counts['research_rating']['3-4'];?></td>
										<td class="text-center"><?php echo $counts['research_rating']['4-5'];?></td>
										<td class="text-center"><?php echo $counts['research_rating']['5-6'];?></td>
										<td class="text-center"><?php echo $counts['research_rating']['6-7'];?></td>
									</tr>
									<tr>
										<td>Service</td>
										<td class="text-center"><?php echo $medians['median_service_rating'];?></td>
										<td class="text-center"><?php echo $averages['average_service_rating'];?></td>
										<td class="text-center"><?php echo $counts['service_rating']['0-1'];?></td>
										<td class="text-center"><?php echo $counts['service_rating']['1-2'];?></td>
										<td class="text-center"><?php echo $counts['service_rating']['2-3'];?></td>
										<td class="text-center"><?php echo $counts['service_rating']['3-4'];?></td>
										<td class="text-center"><?php echo $counts['service_rating']['4-5'];?></td>
										<td class="text-center"><?php echo $counts['service_rating']['5-6'];?></td>
										<td class="text-center"><?php echo $counts['service_rating']['6-7'];?></td>
									</tr>
									<?php endif; ?>
									<tr>
										<td>Overall Score</td>
										<td class="text-center"><?php echo $medians['median_overall_rating'];?></td>
										<td class="text-center"><?php echo $averages['average_overall_score'];?></td>
										<td class="text-center"><?php echo $counts['total_score']['0-1'];?></td>
										<td class="text-center"><?php echo $counts['total_score']['1-2'];?></td>
										<td class="text-center"><?php echo $counts['total_score']['2-3'];?></td>
										<td class="text-center"><?php echo $counts['total_score']['3-4'];?></td>
										<td class="text-center"><?php echo $counts['total_score']['4-5'];?></td>
										<td class="text-center"><?php echo $counts['total_score']['5-6'];?></td>
										<td class="text-center"><?php echo $counts['total_score']['6-7'];?></td>
									</tr>
								</table>
							</div>
							<br>
						<?php endif; ?>
						<hr>
					<?php endforeach; ?>
				<?php else: ?>
					<p>No ratings entered yet.</p>
				<?php endif; ?>
				
			<?php endif; ?>
			
		</div>	
		<div id="footer"><a href="/dept_forms/website_error.py" target="_blank">Report a problem with this website</a></div>
	</div>
	
</body>
</html>