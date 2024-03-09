<?php
include('fpci-functions.php');


?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
	"http://www.w3.org/TR/html4/loose.dtd">


<html lang="en-US">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="author" content="hedetni">
	<!-- Date: 2019-1-10 -->
	
	<title>FPCI | Page Title</title>

<link rel="stylesheet" href="/style/math-internal.css" type="text/css" media="screen" charset="utf-8">

<style type="text/css">


</style>

<script src="/style/jquery-3.2.1.min.js" type="text/javascript" charset="utf-8"></script>


<script type="text/javascript">
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
		<div id="subnav">
			Subnav
		</div>
		<?php echo isset($message) ? '<div id="message">'.$message.'</div>' : '' ?>
		<?php echo isset($error) ? '<div id="error">'.$error.'</div>' : '' ?>
		
		<div id="content">
			<h1>Page Title</h1>
			<p>When in the Course of human events, it becomes necessary for one people to dissolve the political bands which have connected them with another, and to assume among the powers of the earth, the separate and equal station to which the <a href="">Laws of Nature</a> and of Nature's God entitle them, a decent respect to the opinions of mankind requires that they should declare the causes which impel them to the separation.</p>
			<p>We hold these truths to be self-evident, that all men are created equal, that they are endowed by their Creator with certain unalienable Rights, that among these are Life, Liberty and the pursuit of Happiness.</p>
			
			<table>
				<tr>
					<th>Name</th>
					<th>Email</th>
					<th>Office</th>
				</tr>
				<tr>
					<td>Kevin Hedetniemi</td>
					<td>hedetni@clemson.edu</td>
					<td>Martin O-119</td>
				</tr>
				<tr>
					<td>Laura Hedetniemi</td>
					<td>lhedet@clemson.edu</td>
					<td>Martin O-120</td>
				</tr>
				<tr>
					<td>Jason Hedetniemi</td>
					<td>jhedetn@clemson.edu</td>
					<td>Martin O-121</td>
				</tr>
			</table>
			
			<form>
				<input type="checkbox" value="0">
				<input type="radio">
				<input type="text">
				<input type="submit" value="Submit">
			</form>
			
			<p>That to secure these rights, Governments are instituted among Men, deriving their just powers from the consent of the governed, That whenever any Form of Government becomes destructive of these ends, it is the Right of the People to alter or to abolish it, and to institute new Government, laying its foundation on such principles and organizing its powers in such form, as to them shall seem most likely to effect their Safety and Happiness. Prudence, indeed, will dictate that Governments long established should not be changed for light and transient causes; and accordingly all experience hath shewn, that mankind are more disposed to suffer, while evils are sufferable, than to right themselves by abolishing the forms to which they are accustomed. But when a long train of abuses and usurpations, pursuing invariably the same Object evinces a design to reduce them under absolute Despotism, it is their right, it is their duty, to throw off such Government, and to provide new Guards for their future security.</p>
			
			
			
		</div>	
		<div id="footer"><a href="/dept_forms/website_error.py" target="_blank">Report a problem with this website</a></div>
	</div>
	
</body>
</html>