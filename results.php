<h1><a href="/">worknerd.com</a></h1>
<table border=1>
<thead><th>Job<th>Company<th>Salary<th>Tags<th>Points<tbody>


<?php	
//Connect to DB:
date_default_timezone_set ('America/New_York');
$data=mysql_connect("job.czxcq0gunx4h.us-east-1.rds.amazonaws.com","aws","8!dkasLDJA7a&Aj");
$data=mysql_select_db("results");

#make sure nobody screws up by checking the "required" box but not the "search" box:
if (isset($_GET['required'])) $search = array_unique(array_merge($_GET['tech'], $_GET['required']));
else $search = $_GET['tech'];

$query = "SELECT L.jobid AS jobid, L.url AS url, L.company AS company, L.title AS title, L.salary as salary, x.tech as tech
FROM listings L
LEFT JOIN tags t ON L.jobid=t.jobid
LEFT JOIN techs x ON t.techid=x.techid
WHERE t.techid IN (" . implode(",",$search);

#if (isset($_GET['required'])) $query .= ", " . implode(",",$_GET['required']);

$query .=  ") GROUP BY url";


echo $query;
$data=mysql_query($query);

$n = 0;
while ($info=mysql_fetch_array($data)) {
    $url[$n] = $info['url'];
    $title[$n] = $info['title'];
    $company[$n] = $info['company'];
    $salary[$n] = $info['salary'];

    $query1="SELECT x.tech AS tech, t.techid AS techid FROM techs x
	LEFT JOIN tags t ON x.techid=t.techid
	WHERE t.jobid='" . $info['jobid'] . "';";
    $data1 = mysql_query($query1);
    $points = 0;
    $tags[$n] = "";
    while ($info1=mysql_fetch_array($data1)) {
    	if(in_array($info1['techid'], $search)) {
    	    $points += 1;
	    $tags[$n] .= "<strong>" . $info1['tech'] . "</strong> ";
    	}
	else $tags[$n] .= $info1['tech'] . " ";
    }
    $score[$n] = $points;
    $n++;
}



array_multisort($score, SORT_DESC, $title, SORT_ASC, $company, $url, $salary, $tags);

for ($i = 0; $i < sizeof($score); $i++) {
    #Check to make sure it has the required techs
    $print=true;
    
    if (isset($_GET['required'])) {
	foreach ($_GET['required'] as $requirement) {
	    $query2 = "SELECT tech FROM techs WHERE techid='" . $requirement . "'";
	    $info2 = mysql_fetch_array(mysql_query($query2));
	    #this search needs to look for a full chiclet, not just the name.
	    #we'll get stuck on false positives for "C"/"C++" or "C"/"Corsica" etc
	    if (strpos($tags[$i], $info2['tech']) === false) $print=false;
	    #(has to use === because a "0" answer is also acceptable)
        }
    }

    if ($print) {
        echo "<tr><td>" . "<a href='" . $url[$i] . "'>";

        #print logo of source
        if (strpos($url[$i], 'www.dice') !== false) echo "<img src='img/dice.jpg' height=10>";
        elseif (strpos($url[$i], 'jobs.github') >= 0) echo "<img src='img/github.png' height=10>";
        elseif (strpos($url[$i], 'weworkremotely.com') >= 0) echo "<img src='img/weworkremotely.jpg' height=10>";
        else echo "<img src='http://imgc.allpostersimages.com/images/P-473-488-90/74/7476/IB2Q100Z/posters/danger-fart-zone-humor-sign-poster.jpg'>";

        echo " " . $title[$i] . "</a><td>" . $company[$i] . "<td>" . $salary[$i] . "<td>" . $tags[$i] . "<td>" . $score[$i] . "\n";
    }
}


?>
</table>
