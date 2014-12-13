<?php

//Connect to DB:
date_default_timezone_set ('America/New_York');
$data=mysql_connect("job.czxcq0gunx4h.us-east-1.rds.amazonaws.com","aws","8!dkasLDJA7a&Aj");
$data=mysql_select_db("results");



$query = "SELECT * FROM unprocessed;";
$data=mysql_query($query);
?>
<div>
<?php
while($info = mysql_fetch_array($data))
{
    echo "<p>Title: |<a href='" . $info['url'] . "'>" . $info['title'] . "|</a>";
    echo "<p>At |" . $info['company'] . "|";
    echo "<p>" . $info['description'] . "</p><hr>";


}
?>

