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
<div class="row">
<div class="medium-6 columns">
    <h1>Work Nerd <small>Tech job listing aggregation</small></h1>
    <p>Select the techs you are qualified to work with.
    <p>(Left checkbox: Include in search. Right checkbox: <em>Require</em> in search.)
</div>

<div class="medium-6 columns">
   <?php
    $query = "SELECT COUNT(jobid) AS inventory FROM listings";
    $info = mysql_fetch_array(mysql_query($query));
    echo "<h2>With " . $info['inventory'] . " listings covering ";
    $query = "SELECT COUNT(techid) AS tinventory FROM techs";
    $info = mysql_fetch_array(mysql_query($query));
    echo $info['tinventory'] . " technologies!</h2>";
    ?>
</div>
</div>
<form action="results.php" method="GET">
   <div class="row">
      <input type="submit" value="submit" style="position: fixed;">
     
<?php

$query = "SELECT * FROM techs;";
$data=mysql_query($query);
?>
<div>
<?php
while($info = mysql_fetch_array($data))
{
	echo "<div class=\"row\" style=\"margin-left: 70px;\">
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
