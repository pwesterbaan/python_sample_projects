<?php

include('speaker-request-functions.php');


?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
	"http://www.w3.org/TR/html4/loose.dtd">


<html lang="en-US">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="author" content="hedetni">
	<!-- Date: 2019-9-16 -->
	
	<title>School Forms | Speaker Request Form</title>

<link rel="stylesheet" href="/style/math-internal.css" type="text/css" media="screen" charset="utf-8">
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<style type="text/css">
p.leader {font-weight:bold;margin-top:1.5em;}
.indent {margin-left:1em;}
</style>

<script src="/style/jquery-3.2.1.min.js" type="text/javascript" charset="utf-8"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="/style/jquery.validate.js"></script>
<script src="/style/validate-additional-methods.js"></script>

<script type="text/javascript">
$(document).ready(function(){

}); 
</script>

</head>

<body>
	<div id="main">
		<div id="header">
			<div id="app_title">School Forms</div>
			<a href="http://www.clemson.edu/math" title="School Home"><img src="/style/math_logo.png" alt="school logo"></a>
		</div>
		<ul id="nav">
			<?php echo get_nav(); ?>
		</ul>
		<?php echo isset($message) ? '<div id="message">'.$message.'</div>' : '' ?>
		<?php echo isset($error) ? '<div id="error">'.$error.'</div>' : '' ?>
		
		<div id="content">
			<h1>Speaker Request Form (DRAFT)</h1>
			
			
		</div>	
		<div id="footer"><a href="/dept_forms/website_error.py" target="_blank">Report a problem with this website</a></div>
	</div>
	
</body>
</html>