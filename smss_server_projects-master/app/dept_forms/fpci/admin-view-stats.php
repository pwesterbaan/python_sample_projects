<?php
include('fpci-functions.php');

if (in_array($user_id,$admin_list))
{
	$evaluation_years = get_all_evaluation_years();
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
			<?php if (in_array($user_id,$admin_list)): ?>
				
				<h1>FPCI Ratings Statistics</h1>
				
				<?php foreach ($evaluation_years as $year): ?>
					<h2><?php echo $year-1; echo ' - '; echo $year; ?></h2>
					<?php foreach ($evaluators as $division => $evaluator_list): ?>
						<?php if (in_array($user_id,$evaluator_list) && !in_array($division,array('Not Set','Director'))): ?>
							<?php $averages = get_averages_for_division($division,$year); ?>
							<?php $medians = get_medians_for_division($division,$year); ?>
							<?php $counts = get_score_counts_for_division($division,$year); ?>
							<div>
								<table id="averages(<?php echo $year;?>)" class="styled">
									<caption>Stats for <?php echo $division; ?>, <?php echo $year-1; echo ' - '; echo $year; ?></caption>
									<tr>
										<th scope="col">Activity</th>
										<th scope="col">Average Rating</th>
										<th scope="col">Median Rating</th>
										<th scope="col"># Rating (0,1]</th>
										<th scope="col"># Rating (1,2]</th>
										<th scope="col"># Rating (2,3]</th>
										<th scope="col"># Rating (3,4]</th>
										<th scope="col"># Rating (4,5]</th>
										<th scope="col"># Rating (5,6]</th>
										<th scope="col"># Rating (6,7]</th>
									</tr>
									<tr>
										<td>Teaching</td>
										<td class="text-center"><?php echo $averages['average_teaching_rating'];?></td>
										<td class="text-center"><?php echo $medians['median_teaching_rating'];?></td>
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
										<td class="text-center"><?php echo $averages['average_research_rating'];?></td>
										<td class="text-center"><?php echo $medians['median_research_rating'];?></td>
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
										<td class="text-center"><?php echo $averages['average_service_rating'];?></td>
										<td class="text-center"><?php echo $medians['median_service_rating'];?></td>
										<td class="text-center"><?php echo $counts['service_rating']['0-1'];?></td>
										<td class="text-center"><?php echo $counts['service_rating']['1-2'];?></td>
										<td class="text-center"><?php echo $counts['service_rating']['2-3'];?></td>
										<td class="text-center"><?php echo $counts['service_rating']['3-4'];?></td>
										<td class="text-center"><?php echo $counts['service_rating']['4-5'];?></td>
										<td class="text-center"><?php echo $counts['service_rating']['5-6'];?></td>
										<td class="text-center"><?php echo $counts['service_rating']['6-7'];?></td>
									</tr>
									<tr>
										<td>Overall Score</td>
										<td class="text-center"><?php echo $averages['average_overall_score'];?></td>
										<td class="text-center"><?php echo $medians['median_overall_rating'];?></td>
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
						<?php endif; ?>
					<?php endforeach; ?>
					<hr><br>
				<?php endforeach; ?>
				
			<?php endif; ?>
			
		</div>	
		<div id="footer"><a href="/dept_forms/website_error.py" target="_blank">Report a problem with this website</a></div>
	</div>
	
</body>
</html>