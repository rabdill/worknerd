<a href="/"><h1>worknerd.com</h1></a>

<?php	
//Connect to DB:
date_default_timezone_set ('America/New_York');
$data=mysql_connect("job.czxcq0gunx4h.us-east-1.rds.amazonaws.com","aws","8!dkasLDJA7a&Aj");
$data=mysql_select_db("results");

$query="SELECT L.url AS url, L.company AS company, L.title AS title, L.salary as salary, x.tech as tech
FROM listings L
LEFT JOIN tags t ON L.jobid=t.jobid
LEFT JOIN techs x ON t.techid=x.techid";

if($_GET['tech'] != '') $query .= " WHERE x.techid IN (" . implode(",",$_GET['tech']) . ")";

echo $query;

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
