<?php

include('fpci-functions.php');

if (in_array($user_id,$admin_list))
{
	$current_evaluation_year = get_current_evaluation_year();

	if (isset($_GET['person']) && $_GET['person'] != 0 && is_numeric($_GET['person']))
	{
		$person_id = $_GET['person'];
		$person_user_id = get_username_from_person_id($person_id);
		
		//check if user is in need of percentages
		$on_evaluation_list = is_person_on_evaluation_list($person_id);
		
		if ($on_evaluation_list)
		{
			//see if they already have percentages
			$percentages = get_percentages($person_id,$current_evaluation_year);
			$division = get_division_from_person_id($person_id);
			if ($division == NULL || $division == "")
			{
				$division = "Not Set";
			}
			if ($percentages != NULL)
			{
				$error = "Percentages Already Entered";
			}
			
			//get teaching load
			$get_teaching_load_query = $mthsc_db->prepare('SELECT teaching_load FROM fpci_teaching_loads WHERE person_id = ? AND year = ?');
			$get_teaching_load_query->execute(array($person_id, $current_evaluation_year));
			$teaching_load = $get_teaching_load_query->fetchColumn();
		}
		else
		{
			$error = "User not on evaluation list";
		}
	}
	else
	{
		$error = "Invalid ID.";
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
	<!-- Date: 2018-11-28 -->
	
	<title>FPCI | Admin View Percentage Entry</title>

<link rel="stylesheet" href="/style/math-internal.css" type="text/css" media="screen" charset="utf-8">

<style type="text/css">
.text-right {text-align:right;}
input.error {border-color:red !important; border-style:solid;}
input {text-align:right;}
label.hide_label {display:none;}
</style>

<script src="/style/jquery-3.2.1.min.js" type="text/javascript" charset="utf-8"></script>
<script src="/style/jquery.validate.js" type="text/javascript" charset="utf-8"></script>


<script type="text/javascript">

function update_totals()
{
	var summer_total = parseFloat($("#summer_teaching_percentage").val())+parseFloat($("#summer_research_percentage").val())+parseFloat($("#summer_service_percentage").val());
	$("#summer_percentage_total").val(summer_total);
	
	var fall_total = parseFloat($("#fall_teaching_percentage").val())+parseFloat($("#fall_research_percentage").val())+parseFloat($("#fall_service_percentage").val());
	$("#fall_percentage_total").val(fall_total);
	
	var spring_total = parseFloat($("#spring_teaching_percentage").val())+parseFloat($("#spring_research_percentage").val())+parseFloat($("#spring_service_percentage").val());
	$("#spring_percentage_total").val(spring_total);
}

function update_overall_percentages()
{
	//teaching
	var teaching_top = $("#summer_teaching_percentage").val()*0.66;
	teaching_top = teaching_top+parseFloat($("#fall_teaching_percentage").val())+parseFloat($("#spring_teaching_percentage").val());
	var teaching_bottom = $("#summer_percentage_total").val()*0.66;
	teaching_bottom = teaching_bottom+200;
	overall_teaching = 100*(teaching_top/teaching_bottom);
	$("#overall_teaching_percentage").val(overall_teaching.toFixed(0));
	
	//research
	var research_top = $("#summer_research_percentage").val()*0.66;
	research_top = research_top+parseFloat($("#fall_research_percentage").val())+parseFloat($("#spring_research_percentage").val());
	var research_bottom = $("#summer_percentage_total").val()*0.66;
	research_bottom = research_bottom+200;
	overall_research = 100*(research_top/research_bottom);
	$("#overall_research_percentage").val(overall_research.toFixed(0));
	
	//service
	var service_top = $("#summer_service_percentage").val()*0.66;
	service_top = service_top+parseFloat($("#fall_service_percentage").val())+parseFloat($("#spring_service_percentage").val());
	var service_bottom = $("#summer_percentage_total").val()*0.66;
	service_bottom = service_bottom+200;
	overall_service = 100*(service_top/service_bottom);
	$("#overall_service_percentage").val(overall_service.toFixed(0));
	
	overall_total = overall_teaching+overall_research+overall_service;
	$("#overall_percentage_total").val(overall_total);
}

function check_percentage_totals()
{
	if (parseInt($("#overall_teaching_percentage").val()) > 100 || $("#overall_teaching_percentage").val() == "NaN")
	{$("#overall_teaching_percentage").addClass('error');$("#submit_percentages").attr("disabled",true);}else{$("#overall_teaching_percentage").removeClass('error');$("#submit_percentages").attr("disabled",false);}
	
	if (parseInt($("#overall_research_percentage").val()) > 100 || $("#overall_research_percentage").val() == "NaN")
	{$("#overall_research_percentage").addClass('error');$("#submit_percentages").attr("disabled",true);}else{$("#overall_research_percentage").removeClass('error');$("#submit_percentages").attr("disabled",false);}
	
	if (parseInt($("#overall_service_percentage").val()) > 100 || $("#overall_service_percentage").val() == "NaN")
	{$("#overall_service_percentage").addClass('error');$("#submit_percentages").attr("disabled",true);}else{$("#overall_service_percentage").removeClass('error');$("#submit_percentages").attr("disabled",false);}
	
	if (parseInt($("#summer_percentage_total").val()) > 100 || $("#summer_percentage_total").val() == "NaN")
	{$("#summer_percentage_total").addClass('error');$("#submit_percentages").attr("disabled",true);}else{$("#summer_percentage_total").removeClass('error');$("#submit_percentages").attr("disabled",false);}
	
	if (parseInt($("#fall_percentage_total").val()) > 100 || $("#fall_percentage_total").val() == "NaN")
	{$("#fall_percentage_total").addClass('error');$("#submit_percentages").attr("disabled",true);}else{$("#fall_percentage_total").removeClass('error');$("#submit_percentages").attr("disabled",false);}
	
	if (parseInt($("#spring_percentage_total").val()) > 100 || $("#spring_percentage_total").val() == "NaN")
	{$("#spring_percentage_total").addClass('error');$("#submit_percentages").attr("disabled",true);}else{$("#spring_percentage_total").removeClass('error');$("#submit_percentages").attr("disabled",false);}
	
	if (parseInt($("#overall_percentage_total").val()) > 100 || $("#overall_percentage_total").val() == "NaN")
	{$("#overall_percentage_total").addClass('error');$("#submit_percentages").attr("disabled",true);}else{$("#overall_percentage_total").removeClass('error');$("#submit_percentages").attr("disabled",false);}
}

$(document).ready(function(){
	
	$(":input[type=text]:not([readonly])").css({"border-color":"#109DC0","border-style":"solid","border-width":"2px"});
	$(":input[readonly]").css("background-color","#eee");
	
	update_totals();
	update_overall_percentages();
	check_percentage_totals();
	
	$('form').validate({
		debug:false,
		errorContainer: "#messageBox",
		errorLabelContainer: "#messageBox ul",
		wrapper: "li",
		rules: {
			summer_course_credits: {
				required: true,
				number: true,
				max: 10,
			},
			summer_research_days: {
				required: true,
				number: true,
				max: 64
			},
			summer_service_days: {
				required: true,
				number: true,
				max: 64
			},
			fall_teaching_percentage: {
				required: true,
				number: true,
				range: [0,100]
			},
			fall_research_percentage: {
				required: true,
				number: true,
				range: [0,100]
			},
			fall_service_percentage: {
				required: true,
				number: true,
				range: [0,100]
			},
			spring_teaching_percentage: {
				required: true,
				number: true,
				range: [0,100]
			},
			spring_research_percentage: {
				required: true,
				number: true,
				range: [0,100]
			},
			spring_service_percentage: {
				required: true,
				number: true,
				range: [0,100]
			},
			fall_percentage_total: {
				required: true,
				number: true,
				min: 100,
				max: 100
			},
		},
		messages: {
			summer_course_credits: "Max of 10 summer course credits",
			summer_research_days: "Max of 64 days of paid research",
			summer_service_days: "Max of 64 days of paid research",
			fall_teaching_percentage: "Number between 0 and 100 required",
			fall_research_percentage: "Number between 0 and 100 required",
			fall_service_percentage: "Number between 0 and 100 required",
			spring_teaching_percentage: "Number between 0 and 100 required",
			spring_research_percentage: "Number between 0 and 100 required",
			spring_service_percentage: "Number between 0 and 100 required",
		}
	});
	
	$('#summer_course_credits').keyup(function() {
		var credits = parseInt($('#summer_course_credits').val());
		if ($.isNumeric(credits))
		{
			percentage = credits*10;
			$("#summer_teaching_percentage").val(percentage);
		}
		update_totals();
		update_overall_percentages();
		check_percentage_totals();
	});
	
	$('#summer_research_days').keyup(function() {
		var credits = parseInt($('#summer_research_days').val());
		if ($.isNumeric(credits))
		{
			percentage = credits*1.57;
			$("#summer_research_percentage").val(percentage);
		}
		update_totals();
		update_overall_percentages();
		check_percentage_totals();
	});
	
	$('#summer_service_days').keyup(function() {
		var credits = parseInt($('#summer_service_days').val());
		if ($.isNumeric(credits))
		{
			percentage = credits*1.57;
			$("#summer_service_percentage").val(percentage);
		}
		update_totals();
		update_overall_percentages();
		check_percentage_totals();
	});
	
	$('input.fall').keyup(function(){
		update_totals();
		update_overall_percentages();
		check_percentage_totals();
	});
	
	$('input.spring').keyup(function(){
		update_totals();
		update_overall_percentages();
		check_percentage_totals();
	});
	
	$('input').click(function(){
		$(this).select();
	});
	
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
				<?php if (isset($person_id) && $on_evaluation_list && $percentages == NULL): ?>
					<h1>FPCI Activity Percentage Entry for <?php echo $person_user_id; ?> for <?php echo $current_evaluation_year-1;echo '-'; echo $current_evaluation_year; ?></h1>
				
					<p><a href="admin-view-faculty.php">Back to list of faculty</a></p>
				
					<form name="fpci_percentage_form" method="POST" action="admin-view-faculty.php">
						
						<p><label for="teaching_load">Teaching Load</label>: <input type="number" name="teaching_load" id="teaching_load" value="<?php echo $teaching_load; ?>" style="width:4em;"></p>
						
						<table class="styled">
							<tr>
								<th>Activity</th>
								<th>Summer</th>
								<th>Fall</th>
								<th>Spring</th>
								<th>Overall</th>
							</tr>
							<!-- Teaching -->
							<tr>
								<td>Teaching</td>
								<td class="text-right"><label for="summer_course_credits">Summer course credits</label> <input type="text" size="2" name="summer_course_credits" id="summer_course_credits" value="0"> <label for="summer_teaching_percentage" class="hide_label">Summer Teaching Percentage</label><input type="text" size="2" name="summer_teaching_percentage" id="summer_teaching_percentage" value="0" tabindex="-1" readonly></input>%</td>
								<td><label for="fall_teaching_percentage" class="hide_label">Fall Teaching Percentage</label><input type="text" size="2" name="fall_teaching_percentage" id="fall_teaching_percentage" class="fall teaching" value="0"></input>%</td>
								<td><label for="spring_teaching_percentage" class="hide_label">Spring Teaching Percentage</label><input type="text" size="2" name="spring_teaching_percentage" id="spring_teaching_percentage" class="spring teaching" value="0"></input>%</td>
								<td><label for="overall_teaching_percentage" class="hide_label">Overall Teaching Percentage</label><input type="text" size="2" name="overall_teaching_percentage" id="overall_teaching_percentage" value="0"tabindex="-1" readonly></input>%</td>
							</tr>
			
							<tr>
								<td>Research</td>
								<td class="text-right"><label for="summer_research_days">Summer research days</label> <input type="text" size="2" name="summer_research_days" id="summer_research_days" value="0"> <label for="summer_research_percentage" class="hide_label">Summer Research Percentage</label><input type="text" size="2" name="summer_research_percentage" id="summer_research_percentage" value="0" tabindex="-1" readonly></input>%</td>
								<td><label for="fall_research_percentage" class="hide_label">Fall Research Percentage</label><input type="text" size="2" name="fall_research_percentage" id="fall_research_percentage" class="fall research" value="0"></input>%</td>
								<td><label for="spring_research_percentage" class="hide_label">Spring Research Percentage</label><input type="text" size="2" name="spring_research_percentage" id="spring_research_percentage" class="spring research" value="0"></input>%</td>
								<td><label for="overall_research_percentage" class="hide_label">Overall Research Percentage</label><input type="text" size="2" name="overall_research_percentage" id="overall_research_percentage" value="0" tabindex="-1" readonly></input>%</td>
							</tr>
			
							<tr>
								<td>Service</td>
								<td class="text-right"><label for="summer_service_days">Summer service days</label> <input type="text" size="2" name="summer_service_days" id="summer_service_days" value="0"> <label for="summer_service_percentage" class="hide_label">Summer Service Percentage</label><input type="text" size="2" name="summer_service_percentage" id="summer_service_percentage" value="0" tabindex="-1" readonly></input>%</td>
								<td><label for="fall_service_percentage" class="hide_label">Fall Service Percentage</label><input type="text" size="2" name="fall_service_percentage" id="fall_service_percentage" class="fall service" value="0"></input>%</td>
								<td><label for="spring_service_percentage" class="hide_label">Spring Service Percentage</label><input type="text" size="2" name="spring_service_percentage" id="spring_service_percentage" class="fall service" value="0"></input>%</td>
								<td><label for="overall_service_percentage" class="hide_label">Overall Service Percentage</label><input type="text" size="2" name="overall_service_percentage" id="overall_service_percentage" value="0" tabindex="-1" readonly></input>%</td>
							</tr>
			
							<tr>
								<td>Semester Totals</td>
								<td class="text-right"><label for="summer_percentage_total" class="hide_label">Summer Percentage Total</label><input type="text" size="2" id="summer_percentage_total" value="0" tabindex="-1" readonly></input>%</td>
								<td><label for="fall_percentage_total" class="hide_label">Fall Percentage Total</label><input type="text" size="2" id="fall_percentage_total" value="0" tabindex="-1" readonly></input>%</td>
								<td><label for="spring_percentage_total" class="hide_label">Spring Percentage Total</label><input type="text" size="2" id="spring_percentage_total" value="0" tabindex="-1" readonly></input>%</td>
								<td><label for="overall_percentage_total" class="hide_label">Overall Percentage Total</label><input type="text" size="2" id="overall_percentage_total" value="0" tabindex="-1" readonly></input>%</td>
							</tr>
						</table>
				
						<div id="messageBox">
								<ul>
								</ul>
							</div>
			
							<p>
								<input type="hidden" name="user_id" value="<?php echo $person_user_id;?>"></input>
								<input type="hidden" name="person_id" value="<?php echo $person_id;?>"></input>
								<input type="hidden" name="year" value="<?php echo $current_evaluation_year;?>"></input>
								<input type="hidden" name="division" value="<?php echo $division;?>"></input>
								<input type="submit" name="add_percentages" id="add_percentages" value="Submit Percentages"></input>
							</p>
		
					</form>
				<?php else:?>	

				<?php endif;?>
			<?php endif; ?>
			
		</div>	
		<div id="footer"><a href="/dept_forms/website_error.py" target="_blank">Report a problem with this website</a></div>
	</div>
	
</body>
</html>