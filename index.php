<html>
<body>
<h1>Work Nerd</h1>
<h2>Tech job listing aggregation</h2>
<h3>Select the techs you are qualified to work with:</h3>
<form action="results.php" method="GET">
<?php

$query = "SELECT * FROM techs;"
$data=mysql_query($query);

while($info = mysql_fetch_array($data))
{
	echo "<label class='btn btn-check'><input type='checkbox' name='place[]' value='" . $info['techid'];
	echo "'>" . $info['tech'] . "</label>";
}
?>                                                                      

<input type="submit" value="submit">
</form>
</body>
</html>