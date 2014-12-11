<?php
//Connect to DB:
date_default_timezone_set ('America/New_York');
$data=mysql_connect("job.czxcq0gunx4h.us-east-1.rds.amazonaws.com","aws","8!dkasLDJA7a&Aj");
$data=mysql_select_db("results"); ?>

<html>
<head>
	<link rel="author" href="humans.txt" />
    <link rel="stylesheet" href="css/foundation.css">
    <script src="js/vendor/modernizr.js"></script>
    <link rel="stylesheet" href="css/worknerd.css">
</head>
<body>
<div style="position: sticky;">
    <h1>Work Nerd <small>Tech job listing aggregation</small></h1>
    <h3>Select the techs you are qualified to work with:</h3>
    <form action="results.php" method="GET">
        <div class="row" style="margin-left: 1%;">
	        <input type="submit" value="submit" style="position: fixed;">
        </div>
</div>
<?php

$query = "SELECT * FROM techs;";
$data=mysql_query($query);
?>
<div>
<?php
while($info = mysql_fetch_array($data))
{
	echo "<div class=\"row\">
		<div class=\"small-6 columns\"><label class=\"techs\"><input type='checkbox' name='tech[]' value='" . $info['techid'];
	echo "'>" . $info['tech'] . "</label>
		<input type='checkbox' name='required[]' value='" . $info['techid'] . "'></div></div>";
}
?>
</div>                                           
</form>





<script src="/js/vendor/jquery.js"></script>
<script src="/js/foundation.min.js"></script>
<script>
    $(document).foundation();
</script>

</body>
</html>
