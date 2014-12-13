<?php
    include 'data.php'; 
    include 'head.php';
?>
    <title>Tech job listings, IT careers | Work Nerd</title>
</head>
<body>
<?php include 'navbar.php'; ?>

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
