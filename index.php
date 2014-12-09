<?php
//Connect to DB:
date_default_timezone_set ('America/New_York');
$data=mysql_connect("job.czxcq0gunx4h.us-east-1.rds.amazonaws.com","aws","8!dkasLDJA7a&Aj");
$data=mysql_select_db("results"); ?>

<html>
<body>
<h1>Work Nerd</h1>
<h2>Tech job listing aggregation</h2>
<h3>Select the techs you are qualified to work with:</h3>
<form action="results.php" method="GET">
<?php

$query = "SELECT * FROM techs;";
$data=mysql_query($query);

while($info = mysql_fetch_array($data))
{
	echo "<label><input type='checkbox' name='tech' value='" . $info['techid'];
	echo "'>" . $info['tech'] . "</label><br>";
}
?>                                                                      

<input type="submit" value="submit">
</form>
</body>
</html>
