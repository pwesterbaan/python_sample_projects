<?php

include('dept-info-functions.php');
include('gs-info-functions.php');

if (isset($_POST['submit_update']))
{
	if ($_POST['first_name'] == $_POST['new_first_name'] &&
		$_POST['middle_name'] == $_POST['new_middle_name'] &&
		$_POST['last_name'] == $_POST['new_last_name'] &&
		$_POST['maiden_name'] == $_POST['new_maiden_name'] &&
		$_POST['suffix'] == $_POST['new_suffix'] &&
		$_POST['pref_name'] == $_POST['new_pref_name'] &&
		$_POST['display_name'] == $_POST['new_display_name'] &&
		count($_POST) == 17)
	{
		//do nothing
	}
	else
	{
		//echo '<pre>';print_r($_POST);echo '</pre>';
	
		$headers = "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
		$headers .= "From: '".$_POST['first_name'].' '.$_POST['last_name']."' <".$_POST['username']."@clemson.edu>\r\n";
		$subject = "Dept Info Update Request";
	
		$email_body = '<html><body><p>Dept Info Update Request from </p><h2>'.$_POST['first_name'].' '.$_POST['last_name'].'</h2>';
		$email_body .= '<p><a href="http://mthsc.clemson.edu/dept-info/view-person.php?id='.$_POST['person_id'].'">Click here to update their information in Dept Info</a></p><hr>';
	
		//names
		if ($_POST['first_name'] != $_POST['new_first_name'])
		{
			$email_body .= '<p><strong>Updated First Name</strong>: '.$_POST['new_first_name'].'</p>';
		}
		if ($_POST['middle_name'] != $_POST['new_middle_name'])
		{
			$email_body .= '<p><strong>Updated Middle Name</strong>: '.$_POST['new_middle_name'].'</p>';
		}
		if ($_POST['last_name'] != $_POST['new_last_name'])
		{
			$email_body .= '<p><strong>Updated Last Name</strong>: '.$_POST['new_last_name'].'</p>';
		}
		if ($_POST['maiden_name'] != $_POST['new_maiden_name'])
		{
			$email_body .= '<p><strong>Updated Maiden Name</strong>: '.$_POST['new_maiden_name'].'</p>';
		}
		if ($_POST['suffix'] != $_POST['new_suffix'])
		{
			$email_body .= '<p><strong>Updated Suffix</strong>: '.$_POST['new_suffix'].'</p>';
		}
		if ($_POST['pref_name'] != $_POST['new_pref_name'])
		{
			$email_body .= '<p><strong>Updated Preferred Name</strong>: '.$_POST['new_pref_name'].'</p>';
		}
		if ($_POST['display_name'] != $_POST['new_display_name'])
		{
			$email_body .= '<p><strong>Updated Display Name</strong>: '.$_POST['new_display_name'].'</p>';
		}
	
		//Position
		if (isset($_POST['position-remove']) || isset($_POST['position-add']))
		{
			$email_body .= '<p><strong>POSITION</strong></p>';
		}
		if (isset($_POST['position-remove']))
		{
			foreach ($_POST['position-remove'] as $position_remove)
			{
				$email_body .= '<p style="padding-left:1em;">Deletion: '.$position_remove.'</p>';
			}
		}
		if (isset($_POST['position-add']))
		{
			foreach ($_POST['position-add'] as $position_add)
			{
				$email_body .= '<p style="padding-left:1em;">Addition: '.$position_add.'</p>';
			}
		}
	
		//Education
		if (isset($_POST['degree-remove']) || isset($_POST['degree-add']))
		{
			$email_body .= '<p><strong>EDUCATION</strong></p>';
		}
		if (isset($_POST['degree-remove']))
		{
			foreach ($_POST['degree-remove'] as $degree_remove)
			{
				$email_body .= '<p style="padding-left:1em;">Deletion: '.$degree_remove.'</p>';
			}
		}
		if (isset($_POST['degree-add']))
		{
			foreach ($_POST['degree-add'] as $degree_add)
			{
				$email_body .= '<p style="padding-left:1em;">Addition: '.$degree_add.'</p>';
			}
		}
	
		//Email
		if (isset($_POST['email-remove']) || isset($_POST['email-add']))
		{
			$email_body .= '<p><strong>EMAIL</strong></p>';
		}
		if (isset($_POST['email-remove']))
		{
			foreach ($_POST['email-remove'] as $email_remove)
			{
				$email_body .= '<p style="padding-left:1em;">Deletion: '.$email_remove.'</p>';
			}
		}
		if (isset($_POST['email-add']))
		{
			foreach ($_POST['email-add'] as $email_add)
			{
				$email_body .= '<p style="padding-left:1em;">Addition: '.$email_add.'</p>';
			}
		}
	
		//Phone Numbers
		if (isset($_POST['number-remove']) || isset($_POST['number-add']))
		{
			$email_body .= '<p><strong>Phone Numbers</strong></p>';
		}
		if (isset($_POST['number-remove']))
		{
			foreach ($_POST['number-remove'] as $number_remove)
			{
				$email_body .= '<p style="padding-left:1em;">Deletion: '.$number_remove.'</p>';
			}
		}
		if (isset($_POST['number-add']))
		{
			foreach ($_POST['number-add'] as $number_add)
			{
				$email_body .= '<p style="padding-left:1em;">Addition: '.$number_add.'</p>';
			}
		}
	
		//Offices
		if (isset($_POST['office-remove']) || isset($_POST['office-add']))
		{
			$email_body .= '<p><strong>OFFICES</strong></p>';
		}
		if (isset($_POST['office-remove']))
		{
			foreach ($_POST['office-remove'] as $office_remove)
			{
				$email_body .= '<p style="padding-left:1em;">Deletion: '.$office_remove.'</p>';
			}
		}
		if (isset($_POST['office-add']))
		{
			foreach ($_POST['office-add'] as $office_add)
			{
				$email_body .= '<p style="padding-left:1em;">Addition: '.$office_add.'</p>';
			}
		}
	
		//Addresses
		if (isset($_POST['address-remove']) || isset($_POST['address-add']))
		{
			$email_body .= '<p><strong>ADDRESSES</strong></p>';
		}
		if (isset($_POST['address-remove']))
		{
			foreach ($_POST['address-remove'] as $address_remove)
			{
				$email_body .= '<p style="padding-left:1em;">Deletion: '.$address_remove.'</p>';
			}
		}
		if (isset($_POST['address-add']))
		{
			foreach ($_POST['address-add'] as $address_add)
			{
				$email_body .= '<p style="padding-left:1em;">Addition: '.$address_add.'</p>';
			}
		}
	
	
		$email_body .= '</body></html>';
	
	
		mail ('mathstatadmin@clemson.edu,vmcclai@clemson.edu', $subject, $email_body, $headers);
	
		$message = 'Thank you, your updates have been submitted. Please allow time for them to be entered into the system.';
	}
	
}


//============================
//  GET INFORMATION TO DISPLAY
//============================
if (isset($user_id))
{
	$id = get_person_id_from_user_id($user_id);
	//$id = 304;
	if ($id != "")
	{
		$person = get_person_details($id);
	
		if ($person)
		{
			//personal details
			$emails = get_emails($person['person_id']);
			$offices = get_offices($person['person_id']);
			$education_list = get_education_list($person['person_id']);
			$phone_numbers = get_phone_numbers($person['person_id']);
			$positions = get_positions($person['person_id']);
			$addresses = get_addresses($person['person_id']);
			$roles = get_roles($person['person_id']);
			$lists = get_lists($person['person_id']);
		
			//database info
			$mthsc_offices = get_office_list();
			$mthsc_positions = get_position_list();
			$mthsc_schools = get_school_list();
			$mthsc_lists = get_all_lists();
		}
		else
		{
			$error = "Person ID '".$id."' not in database.";
		}
	}
}
else
{$error = "No person selected";}

?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
	"http://www.w3.org/TR/html4/loose.dtd">


<html lang="en-US">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="author" content="hedetni">
	<!-- Date: 2017-6-13 -->
	
	<title>MaSS Info Update</title>

<link rel="stylesheet" href="/style/math-internal.css" type="text/css" media="screen" charset="utf-8">
<link rel="stylesheet" href="dept-info-style.css" type="text/css" media="screen" charset="utf-8">

<style type="text/css">
span.info {font-size:small;}
span.to_delete {margin-right:1em;}
div.address {padding-left:1em;display:block;padding-bottom:0.5em;}
a.add_link {font-weight:bold;background-color:#109DC0; color:white; padding:0.1em 0.25em !important;margin-right:0em !important;}
a.add_link:hover {text-shadow:none;background-color:#522D80;}
label.hide_label {display:none;}
</style>

<script src="/style/jquery-3.2.1.min.js" type="text/javascript" charset="utf8"></script>
<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.15/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="//cdn.datatables.net/1.10.15/js/jquery.dataTables.js"></script>


<script type="text/javascript">
function close(toclose){
	if (toclose=="message")
	{$('div#message').slideUp();}
	else if (toclose=="error")
	{$('div#error').slideUp();}
}


//POSTION
function toggle_position(){
	$('p#add_position_form').slideToggle();
	if ($('a#add_position_link').html()=="Add Position")
	{$('a#add_position_link').html("Cancel");}
	else
	{
		$('a#add_position_link').html("Add Position");
	}
}
function delete_position(id,description){
	$("p#position_"+id).remove();
	$("div#position_list").append('<input type="hidden" name="position-remove[]" value="'+description+'">');
}
function add_position(){
	var position = $('input#position_to_add').val();
	var stripped_position = position.replace(/\s/g, '').replace(/\</g, '').replace(/\>/g, '').replace(/\;/g, '');
	$("div#position_list").append('<input type="hidden" name="position-add[]" value="'+position+'" id="added_position_'+stripped_position+'">');
	$("div#position_list").append('<p class="indent" id="position_'+stripped_position+'">'+position+' (<a href="javascript:delete_added_position(\'position_'+stripped_position+'\');">Delete</a>)</p>');
	$('input#position_to_add').val("");
	toggle_position();
}
function delete_added_position(position){
	$("p#"+position).remove();
	$("input#added_"+position).remove();
}

//EDUCATION
function toggle_education(){
	$('div#add_education_form').slideToggle();
	if ($('a#add_education_link').html()=="Add Education")
	{$('a#add_education_link').html("Cancel");}
	else
	{
		$('a#add_education_link').html("Add Education");
	}
}
function delete_education(id,description){
	$("p#degree_"+id).remove();
	$("div#education_list").append('<input type="hidden" name="degree-remove[]" value="'+description+'">');
}
function add_education(){
	var degree = $('select#degree_type').val()+" in "+$('input#major').val()+" from "+$('input#school').val();
	if ($('input#year').val() != "")
	{degree += ", "+$('select#degree_semester').val()+" "+$('input#year').val();}
	var stripped_degree = degree.replace(/\s/g, '');
	stripped_degree = stripped_degree.replace(/,/g, '').replace(/\</g, '').replace(/\>/g, '').replace(/\;/g, '');
	$("div#education_list").append('<input type="hidden" name="degree-add[]" value="'+degree+'" id="added_degree_'+stripped_degree+'">');
	$("div#education_list").append('<p class="indent" id="degree_'+stripped_degree+'">'+degree+' (<a href="javascript:delete_added_education(\'degree_'+stripped_degree+'\');">Delete</a>)</p>');
	$('input#major').val("");
	$('input#school').val("");
	$('input#year').val("");
	toggle_education();
}
function delete_added_education(degree){
	$("p#"+degree).remove();
	$("input#added_"+degree).remove();
}

//EMAIL
function toggle_email(){
	$('div#add_email_form').slideToggle();
	if ($('a#add_email_link').html()=="Add Email")
	{$('a#add_email_link').html("Cancel");}
	else
	{$('a#add_email_link').html("Add Email");}
}
function delete_email(id,description){
	$("p#email_"+id).remove();
	$("div#email_list").append('<input type="hidden" name="email-remove[]" value="'+description+'">');
}
function add_email(){
	var email = $('input#email_to_add').val()+" ["+$('select#email_type').val()+"]";
	var stripped_email = email.replace(/\s/g, '').replace(/@/g, '').replace(/\[/g, '').replace(/\]/g, '').replace(/\./g, '').replace(/\</g, '').replace(/\>/g, '').replace(/\;/g, '');
	$("div#email_list").append('<input type="hidden" name="email-add[]" value="'+email+'" id="added_email_'+stripped_email+'">');
	$("div#email_list").append('<p class="indent" id="email_'+stripped_email+'">'+email+' (<a href="javascript:delete_added_email(\'email_'+stripped_email+'\');">Delete</a>)</p>');
	$('input#email_to_add').val("");
	toggle_email();
}
function delete_added_email(email){
	$("p#"+email).remove();
	$("input#added_"+email).remove();
}

//PHONE
function toggle_phone(){
	$('div#add_phone_form').slideToggle();
	if ($('a#add_phone_link').html()=="Add Phone Number")
	{$('a#add_phone_link').html("Cancel");}
	else
	{$('a#add_phone_link').html("Add Phone Number");}
}
function delete_number(id,description){
	$("p#number_"+id).remove();
	$("div#phone_list").append('<input type="hidden" name="number-remove[]" value="'+description+'">');
}
function add_number(){
	var number = "("+$('input#area_code').val()+") "+$('input#first_3').val()+"-"+$('input#last_4').val()+" ["+$('select#number_type').val()+"]";
	var stripped_number = number.replace(/\s/g, '').replace(/@/g, '').replace(/\[/g, '').replace(/\]/g, '').replace(/\-/g, '').replace(/\</g, '').replace(/\>/g, '').replace(/\;/g, '');
	$("div#phone_list").append('<input type="hidden" name="number-add[]" value="'+number+'" id="added_number_'+stripped_number+'">');
	$("div#phone_list").append('<p class="indent" id="number_'+stripped_number+'">'+number+' (<a href="javascript:delete_added_number(\'number_'+stripped_number+'\');">Delete</a>)</p>');
	$('input#area_code').val("");
	$('input#first_3').val("");
	$('input#last_4').val("");
	toggle_phone();
}
function delete_added_number(number){
	$("p#"+number).remove();
	$("input#added_"+number).remove();
}

//OFFICE
function toggle_office(){
	$('div#add_office_form').slideToggle();
	if ($('a#add_office_link').html()=="Add Office")
	{$('a#add_office_link').html("Cancel");}
	else
	{$('a#add_office_link').html("Add Office");}
}
function delete_office(id,office){
	$("p#office_"+id).remove();
	$("div#office_list").append('<input type="hidden" name="office-remove[]" value="'+office+'">');
}
function add_office(){
	var office = $('input#office_to_add').val();
	var stripped_office = office.replace(/\s/g, '').replace(/@/g, '').replace(/\[/g, '').replace(/\]/g, '').replace(/\./g, '').replace(/\</g, '').replace(/\>/g, '').replace(/\;/g, '');
	$("div#office_list").append('<input type="hidden" name="office-add[]" value="'+office+'" id="added_office_'+stripped_office+'">');
	$("div#office_list").append('<p class="indent" id="office_'+stripped_office+'">'+office+' (<a href="javascript:delete_added_office(\'office_'+stripped_office+'\');">Delete</a>)</p>');
	$('input#office_to_add').val("");
	toggle_office();
}
function delete_added_office(office){
	$("p#"+office).remove();
	$("input#added_"+office).remove();
}

//ADDRESS
function toggle_address(){
	$('div#add_address_form').slideToggle();
	if ($('a#add_address_link').html()=="Add Address")
	{$('a#add_address_link').html("Cancel");}
	else
	{$('a#add_address_link').html("Add Address");}
}
function delete_address(id,address){
	$("div#address_"+id).remove();
	$("div#address_list").append('<input type="hidden" name="address-remove[]" value="'+address+'">');
}
function add_address(){
	var address = $('input#street_address_line_1').val()+", ";
	if ($('input#street_address_line_2').val() != "")
	{
		address += $('input#street_address_line_2').val()+", ";
	}
	address += $('input#city').val()+", "+$('input#state').val()+" "+$('input#country').val()+" "+$('input#zip_code').val()+" ["+$('select#address_type').val()+"]";
	
	var stripped_address = address.replace(/\s/g, '').replace(/@/g, '').replace(/\[/g, '').replace(/\]/g, '').replace(/\./g, '').replace(/\,/g, '').replace(/\</g, '').replace(/\>/g, '').replace(/\;/g, '');
	
	$("div#address_list").append('<input type="hidden" name="address-add[]" value="'+address+'" id="added_address_'+stripped_address+'">');
	
	var display_address = '<div class="address" id="address_'+stripped_address+'">';
	display_address += $('input#street_address_line_1').val()+"<br>";
	if ($('input#street_address_line_2').val() != "")
	{
		display_address += $('input#street_address_line_2').val()+"<br>";
	}
	display_address += $('input#city').val()+", "+$('input#state').val()+" "+$('input#zip_code').val()+"<br>"+$('input#country').val()+" ["+$('select#address_type').val()+"]";
	display_address += ' (<a href="javascript:delete_added_address(\'address_'+stripped_address+'\');">Delete</a>)</div>';
	
	$("div#address_list").append(display_address);
	$('input#street_address_line_1').val("");
	$('input#street_address_line_2').val("");
	$('input#city').val("");
	$('input#state').val("");
	$('input#country').val("");
	$('input#zip_code').val("");
	toggle_address();
}
function delete_added_address(address){
	$("div#"+address).remove();
	$("input#added_"+address).remove();
}

$(document).ready(function(){ 
	$('select#position_select').change(function(){
		if ($('select#position_select').val()=="other")
		{
			$('div#more_position').slideUp(function(){$('form#new_position_form').slideDown();});
		}
		else
		{
			$('form#new_position_form').slideUp(function(){$('div#more_position').slideDown();});
		}
	});

}); 
</script>

</head>

<body>
	<div id="main">
		<div id="header">
			<div id="app_title">SCHOOL FORMS</div>
			<a href="http://www.clemson.edu/math" title="School Home"><img src="/style/math_logo.png" alt="school logo"></a>
		</div>
		
		<?php if (in_array("Student",$roles)): ?>

		<?php endif; ?>
		
		<?php echo isset($message) ? '<div id="message"><a href="javascript:close(\'message\')" class="close">&#9447;</a> '.$message.'</div>' : '' ?>
		<?php echo isset($error) ? '<div id="error"><a href="javascript:close(\'error\')" class="close">&#9447;</a> '.$error.'</div>' : '' ?>
		
		<div id="content">
			<?php if ($person): ?>
			<h1>School Information Update Form</h1>
			<p>This form shows you information the school currently has on file. Please update or add any information that is incorrect or missing. <strong>When you are finished making changes, press 'Submit Update Request' at the bottom of the page</strong> to send your request to an administrative assistant for review. They will update the information as appropriate. Please be patient.</p>
			
			<form name="info_update_form" method="POST" action="">
			
			<input type="hidden" name="username" value="<?php echo $user_id; ?>"></input>
			<input type="hidden" name="person_id" value="<?php echo $id; ?>"></input>
			
			<!-- PERSONAL -->
			
			<div class="section">
				<h2>Personal</h2>
				<div class="section_content">
					<table>
						<tr>
							<td class="field"><label for="new_first_name">Legal First Name</label></td><td><input type="text" name="new_first_name" id="new_first_name" size="40" placeholder="First Name" value="<?php echo $person['first_name']; ?>"></input></td>
							<input type="hidden" name="first_name" value="<?php echo $person['first_name']; ?>">
						</tr>
						<tr>
							<td class="field"><label for="new_middle_name">Middle Name</label></td><td><input type="text" name="new_middle_name" id="new_middle_name" size="40" placeholder="Middle Name" value="<?php echo $person['middle_name']; ?>"></input></td>
							<input type="hidden" name="middle_name" value="<?php echo $person['middle_name']; ?>">
						</tr>
						<tr>
							<td class="field"><label for="new_last_name">Last Name</label></td><td><input type="text" name="new_last_name" id="new_last_name" size="40" placeholder="Last Name" value="<?php echo $person['last_name']; ?>"></input></td>
							<input type="hidden" name="last_name" value="<?php echo $person['last_name']; ?>">
						</tr>
						<tr>
							<td class="field"><label for="new_maiden_name">Maiden Name</label></td>
							<td><span class="info">We only need this if any of your records have your maiden name listed on them.</span><br>
								<input type="text" name="new_maiden_name" id="new_maiden_name" size="40" placeholder="Maiden Name" value="<?php echo $person['maiden_name']; ?>"></input></td>
								<input type="hidden" name="maiden_name" value="<?php echo $person['maiden_name']; ?>">
						</tr>
						<tr>
							<td class="field"><label for="new_suffix">Suffix</label></td><td><input type="text" name="new_suffix" id="new_suffix" size="40" placeholder="Suffix" value="<?php echo $person['suffix']; ?>"></input></td>
							<input type="hidden" name="suffix" value="<?php echo $person['suffix']; ?>">
						</tr>
						<tr>
							<td class="field"><label for="new_pref_name">Common First Name</label></td>
							<td><span class="info">If you are known by some name other than your first name please list it here.</span><br>
								<input type="text" name="new_pref_name" id="new_pref_name" size="40" placeholder="Preferred Name" value="<?php echo $person['pref_name']; ?>"></input> </td>
								<input type="hidden" name="pref_name" value="<?php echo $person['pref_name']; ?>">
						</tr>
						<tr>
							<td class="field"><label for="new_display_name">Display Name</label></td>
							<td><span class="info">This name will be displayed in the directory and on your profile page. To use your legal first name leave this blank. If you wish to have your middle initial display, enter your first name and middle initial here.</span><br>
								<input type="text" name="new_display_name" id="new_display_name" size="40" placeholder="Display Name" value="<?php echo $person['display_name']; ?>"></input> </td>
								<input type="hidden" name="display_name" value="<?php echo $person['display_name']; ?>">
						</tr>
					</table>
					
				</div>
			</div>
			
			<p>For the following sections, be sure to click the <a class="add_link">Save</a> button when entering new information. <strong>When all sections reflect correct information, press 'Submit Update Request' at the bottom of the page</strong>.</p>
			
			<!-- GENERAL -->
			
			<div class="section">
				<h2>General</h2>
				
				<!-- positions -->
				<?php if (in_array("Faculty",$roles) || in_array("Staff",$roles) || in_array("Emeritus",$roles)): ?>
				<div class="section_content">	
					<h3>Positions</h3>
					
					<div id="position_list">
						<?php foreach ($positions as $position): ?>
							<p class="indent" id="position_<?php echo $position['id']; ?>">
								
								<?php echo $position['position']; ?> (<a href="javascript:delete_position(<?php echo $position['id'].",'".$position['position']."'"; ?>);">Delete</a>)
							</p>
						<?php endforeach; ?>
					</div>
					
					<p class="indent" id="add_position_form" style="display:none;">
						<label for="position_to_add">New Position</label>
						<input type="text" id="position_to_add"></input>
						<a href="javascript:add_position();" class="add_link">Save</a>
					</p>
					
					<p class="indent">+ <a href="javascript:toggle_position();" id="add_position_link">Add Position</a></p>
				</div>
				<?php endif; ?>
				
				<!-- education -->
				<div class="section_content">
					<h3>Education</h3>
					
					<div id="education_list">
						<?php foreach ($education_list as $degree): ?>
								<p class="indent" id="degree_<?php echo $degree['education_id']; ?>">
								
									<?php echo $degree['degree'].' in '.$degree['major'].' from '.$degree['school'].', '.$degree['year']; if ($degree['final_gpa']!=''){ echo ' ('.$degree['final_gpa'].')';} ?> (<a href="javascript:delete_education(<?php echo $degree['education_id'].",'".$degree['degree']." in ".$degree['major']." from ".$degree['school'].", ".$degree['year']."'"; ?>);">Delete</a>)
								</p>
						<?php endforeach; ?>
					</div>
					
					<div id="add_education_form" style="display:none;" method="post" action="">
						<p class="indent">
							<label for="degree_type">Degree Type: </label>
							<select id="degree_type">
								<option selected disabled>Degree</option>
								<option value="AB">AB</option>
								<option value="BA">BA</option>
								<option value="BBA">BBA</option>
								<option value="BS">BS</option>
								<option value="BSEd">BSEd</option>
								<option value="MA">MA</option>
								<option value="ME">ME</option>
								<option value="MEd">MEd</option>
								<option value="MPhil">MPhil</option>
								<option value="MS">MS</option>
								<option value="MStat">MStat</option>
								<option value="PhD">PhD</option>
								<option value="SM">SM</option>
							</select>
							<label for="school">School: </label>
							<input id="school" placeholder="School" size="40">
							<label for="major">Major: </label>
							<input id="major" placeholder="Major" size="40">
							<label for="degree_semester">Semester: </label>
							<select id="degree_semester">
								<option value="Spring">Spring</option>
								<option value="Fall">Fall</option>
								<option value="Summer">Summer</option>
							</select>
							<label for="year">Year</label>
							<input id="year" placeholder="Year" size="4">
						</p>
						<p class="indent">
							<a href="javascript:add_education();" class="add_link">Save</a>
						</p>
					</div>
					
					<p class="indent">+ <a href="javascript:toggle_education();" id="add_education_link">Add Education</a></p>
				</div>
				
			</div>
				
			<!-- CONTACT -->
			
			<div class="section">
				<h2>Contact</h2>
				
				<!-- emails -->
				<div class="section_content">
					<h3>Email Addresses</h3>
					
					<div id="email_list">
						<p class="indent"><?php echo strtolower($person['username']); ?>@clemson.edu (Clemson email)</p>
						<?php foreach ($emails as $email): ?>
							<p class="indent" id="email_<?php echo $email['email_id'];?>">
								<?php echo $email['email_address'].' ['.$email['email_type'].']'; ?> 
								(<a href="javascript:delete_email(<?php echo $email['email_id'].",'".$email['email_address']."'"; ?>);">Delete</a>)
							</p>
						<?php endforeach; ?>
					</div>
					
					<div id="add_email_form" style="display:none;" method="post" action="">
						<p class="indent"><label for="email_to_add">Email Address: </label><input type="text" id="email_to_add" placeholder="Email Address"></input>
						<label for="email_type">Email Type</label>
						<select id="email_type">
							<option selected disabled>Select Type</option>
    						<option value="personal">personal</option>
    						<option value="previous school">previous school</option>
    						<option value="work">work</option>
    						<option value="other">other</option>
						</select>
						<a href="javascript:add_email();" class="add_link">Save</a>
					</div>
					<p class="indent">+ <a href="javascript:toggle_email();" id="add_email_link">Add Email</a></p>
				</div>
				
				
				<!-- phone numbers -->
				<div class="section_content">
					<h3>Phone Numbers</h3>
					
					<p>Only phone numbers marked 'Office' will be displayed in the directory.</p>
					
					<div id="phone_list">
						<?php foreach ($phone_numbers as $number): ?>
							<p class="indent" id="number_<?php echo $number['number_id'];?>">
								<?php echo $number['number'].' ['.$number['number_type'].']'; ?>
								(<a href="javascript:delete_number(<?php echo $number['number_id']; ?>,'<?php echo $number['number'].' ['.$number['number_type'].']'; ?>');">Delete</a>)
							</p>
						<?php endforeach; ?>
					</div>
					
					<div id="add_phone_form" style="display:none" method="post" action="">
						<p class="indent">
							<label for="area_code" class="hide_label">Area Code: </label><input type="text" id="area_code" value="" size="3" placeholder="(Area)"> 
							<label for="first_3" class="hide_label">First Digits: </label><input type="text" id="first_3" value="" size="3"> - 
							<label for="last_4" class="hide_label">Last Digits: </label><input type="text" id="last_4" value="" size="4">
						<label for="number_type">Phone Number Type: </label>
						<select id="number_type">
							<option selected disabled>Select Type</option>
							<option value="cell">cell</option>
							<option value="office">office</option>
							<option value="personal">personal</option>
							<option value="previous school">previous school</option>
							<option value="work">work</option>
							<option value="other">other</option>
						</select>
						<a href="javascript:add_number();" class="add_link">Save</a>
					</div>
					
					<p class="indent">+ <a href="javascript:toggle_phone();" id="add_phone_link">Add Phone Number</a></p>
				</div>
				
				
				<!-- offices -->
				<div class="section_content">
					<h3>Offices</h3>
					
					<div id="office_list">
						<?php foreach ($offices as $office): ?>
								<p class="indent" id="office_<?php echo $office['link_id']; ?>"><?php echo $office['description']; ?> 
								(<a href="javascript:delete_office(<?php echo $office['link_id']; ?>,'<?php echo $office['description']; ?>')">Delete</a>)
								</p>
						<?php endforeach; ?>
					</div>
					
					<div id="add_office_form" style="display:none" method="post" action="">
						<p class="indent">
							<label for="office_to_add">Building and Office Number: </label><input type="text" id="office_to_add" placeholder="Building and Office Number"></input>
							<a href="javascript:add_office();" class="add_link">Save</a>
						</p>
					</div>
					
					<p class="indent">+ <a href="javascript:toggle_office();" id="add_office_link">Add Office</a></p>
				</div>
				
				
				<!-- addresses -->
				<div class="section_content">
					<h3>Addresses</h3>
					
					<div id="address_list">
						<?php foreach ($addresses as $address): ?>
								<div class="address" id="address_<?php echo $address['address_id']; ?>"><?php echo $address['street_address_line_1']; ?><br>
								<?php if ($address['street_address_line_2']!=""): ?>
									<?php echo $address['street_address_line_2']; ?><br>
								<?php endif; ?>
								<?php echo $address['city'].', '.$address['state'].' '.$address['zip_code']; ?>
								<?php if ($address['street_address_line_1']!=""): ?>
									<br><?php echo $address['country']; ?>
								<?php endif; ?>
								[<?php echo $address['address_type']; ?>]<br>
								(<a href="javascript:delete_address(<?php echo $address['address_id']; ?>,'<?php echo $address['street_address_line_1'].', '; echo $address['street_address_line_2']!= "" ? $address['street_address_line_2'].', ' : ""; echo $address['city'].', '.$address['state'].' '.$address['country'].' '.$address['zip_code']; ?>')">Delete</a>)</div>
						<?php endforeach; ?>
					</div>
					
					<div id="add_address_form" style="display:none" method="post" action="">
						<p class="indent"><label for="street_address_line_1">Address Line 1: </label><input id="street_address_line_1" placeholder="Address Line 1" size="50"></p>
						<p class="indent"><label for="street_address_line_2">Address Line 2: </label><input id="street_address_line_2" placeholder="Address Line 2" size="50"></p>
						<p class="indent"><label for="city">City: </label><input id="city" placeholder="City" size="25"></p>
						<p class="indent"><label for="state">State: </label><input id="state" placeholder="State" size="15"></p> 
						<p class="indent"><label for="zip_code">Zip Code: </label><input id="zip_code" placeholder="Zip Code" size="5"></p>
						<p class="indent"><label for="country">Country: </label><input id="country" placeholder="Country" size="40"></p>
						<p class="indent"><label for="address_type">Address Type: </label><select id="address_type">
								<option selected disabled>Select Type</option>
								<option value="personal">personal</option>
								<option value="work">work</option>
								<option value="previous school">previous school</option>
								<option value="other">other</option>
							</select></p>
						<p class="indent"><a href="javascript:add_address();" class="add_link">Save</a></p>
					</div>
					<p class="indent">+ <a href="javascript:toggle_address();" id="add_address_link">Add Address</a></p>
				</div>
				
			</div>

			
			
			<input type="submit" value="Submit Update Request" name="submit_update"> <a href="">Reset without Submitting</a>
			
			</form>
			
			
			<?php endif; ?>
		</div>	
		<div id="footer"><a href="/dept_forms/website_error.py" target="_blank">Report a problem with this website</a></div>
	</div>
	
</body>
</html>