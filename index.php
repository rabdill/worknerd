<h1>techjob.io</h1>

<?php	
//Connect to DB:
date_default_timezone_set ('America/New_York');
$data=mysql_connect("job.czxcq0gunx4h.us-east-1.rds.amazonaws.com","aws","8!dkasLDJA7a&Aj");
$data=mysql_select_db("results");




$query="SELECT url, company, title, salary FROM listings;";
$data=mysql_query($query);
?>
<table border=1>
<thead><th>Job<th>Company<th>Salary<tbody>
<?php
while ($info=mysql_fetch_array($data)) {
echo "<tr><td><a href='" . $info['url'];
echo "'>" . $info['title'] . "</a><td>" . $info['company'] . "<td>" . $info['salary'];
}

?>
</table>
