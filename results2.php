<a href="/"><h1>worknerd.com</h1></a>
<table border=1>
<thead><th>Job<th>Company<th>Salary<th>Tags<th>Points<tbody>


<?php	
//Connect to DB:
date_default_timezone_set ('America/New_York');
$data=mysql_connect("job.czxcq0gunx4h.us-east-1.rds.amazonaws.com","aws","8!dkasLDJA7a&Aj");
$data=mysql_select_db("results");

$query="SELECT L.jobid AS jobid, L.url AS url, L.company AS company, L.title AS title, L.salary as salary, x.tech as tech
FROM listings L
LEFT JOIN tags t ON L.jobid=t.jobid
LEFT JOIN techs x ON t.techid=x.techid";


echo $query;
$data=mysql_query($query);

while ($info=mysql_fetch_array($data)) {    
    $query1="SELECT x.tech AS tech, x.techid AS techid FROM techs x
	LEFT JOIN tags t ON x.techid=t.techid
	WHERE t.jobid='" . $info['jobid'] . "';";
    #echo $query1;
    $data1 = mysql_query($query1);
    $points = 0;
    while ($info1=mysql_fetch_array($data1)) {
    	if(in_array($info1['techid'], $_GET['tech'])) {
    	    $points += 1;
    	}
    }
    $score[] = $points;
}

foreach ($data as $key => $row) {
    $url[$key]  = $row['url'];
    $title[$key] = $row['title'];
    $company[$key] = $row['company'];
    $salary[$key] = $row['salary'];
}

array_multisort($score, SORT_DESC, $title, SORT_ASC, $company, $url, $salary)

for ($i = 0; $i < sizeof($score); $i++) {
    echo "<tr><td><a href='" . $url[$i] . "'>";
    if (strpos($url[$i], 'www.dice') >= 0 && strpos($url[$i], 'www.dice') !== false) echo "<img src='img/dice.jpg' height=10>";
    elseif (strpos($url[$i], 'jobs.github') >= 0) echo "<img src='img/github.png' height=10>";
    else echo "<img src='http://imgc.allpostersimages.com/images/P-473-488-90/74/7476/IB2Q100Z/posters/danger-fart-zone-humor-sign-poster.jpg'>";

    echo " " . $title[$i] . "</a><td>" . $company[$i] . "<td>" . $salary[$i] . "<td>" . $score[$i] . "\n";
}


?>
</table>
