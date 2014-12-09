<h1>worknerd.com</h1>

<?php	
//Connect to DB:
date_default_timezone_set ('America/New_York');
$data=mysql_connect("job.czxcq0gunx4h.us-east-1.rds.amazonaws.com","aws","8!dkasLDJA7a&Aj");
$data=mysql_select_db("results");




$query="SELECT url, company, title, salary FROM listings ORDER BY company DESC;";
$data=mysql_query($query);
?>
<table border=1>
<thead><th>Job<th>Company<th>Salary<tbody>
<?php
while ($info=mysql_fetch_array($data)) {
echo "<tr><td><a href='" . $info['url'] . "'>";
if (strpos($info['url'], 'www.dice') >= 0 && strpos($info['url'], 'www.dice') !== false) echo "<img src='img/dice.jpg' height=10>";
elseif (strpos($info['url'], 'jobs.github') >= 0) echo "<img src='img/github.png' height=10>";
else echo "<img src='http://imgc.allpostersimages.com/images/P-473-488-90/74/7476/IB2Q100Z/posters/danger-fart-zone-humor-sign-poster.jpg'>";

echo " " . $info['title'] . "</a><td>" . $info['company'] . "<td>" . $info['salary'] . "\n";
}

?>
</table>
