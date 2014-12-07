<?php	
//Connect to DB:
date_default_timezone_set ('America/New_York');
$data=mysql_connect("job.czxcq0gunx4h.us-east-1.rds.amazonaws.com","aws","12WQasXZ!");
$data=mysql_select_db("results");




$query="SELECT url, company, title, salary FROM listings;";
$data=mysql_query($query);
$info=mysql_fetch_array($data);

echo "<br><a href='" . $info['url'];
echo "'>" . $info['title'] . "</a> at " . $info['company'] . " (salary: |" . $info['salary') . "|)";