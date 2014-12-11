<html>
<head>
    <link rel="stylesheet" href="css/foundation.css">
    <script src="js/vendor/modernizr.js"></script>

    <style>
	.sourceLogo {
	    height: 20px;
	}
	table tr td {
	    font-size: 1.5em;
	    line-height: 1.5em!important;
	}
	.techs {
            display: inline;
            font-size: 2em;
        }
        input[type="checkbox"] {
            margin: 20px;
        }
        .centered-column {
            text-align: center;
        }

    </style>


</head>
<body>
<h1><a href="/">worknerd.com</a></h1>

<a href="#" data-reveal-id="refineSearch">Refine your search</a>

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

$data=mysql_query($query);

#For each row, search for the tags associated with that job
$n = 0;
$extraNum = 0;
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
	    $tags[$n] .= "<span class=\"round success label\">" . $info1['tech'] . "</span> ";
    	}
	else {
	    $tags[$n] .= "<span class=\"round secondary label\">" . $info1['tech'] . "</span>";
	    
	    #We're keeping a list of techs that show up OUTSIDE of the search:
	    $extraTechIds[$extraNum] = $info1['techid'];
	    $extraTechNames[$extraNum] = $info1['tech'];
	    $extraNum++;
	}
    }
    $score[$n] = $points;
    $n++;
}

#trim the repeats out of the arrays. it uses this bizarre routine
#because using array_unique leaves gaps in the arrays where it removes values.
$extraTechIds = array_merge(array_flip(array_flip($extraTechIds))); 
$extraTechNames = array_merge(array_flip(array_flip($extraTechNames)));
#Put em in alphabetical order:
array_multisort($extraTechNames, SORT_ASC, $extraTechIds);

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
        echo "<tr><td>" . "<a href='" . $url[$i] . "'>" . $title[$i] . "<br>";
	echo "<img class='sourceLogo' src='";
        #print logo of source
        if (strpos($url[$i], 'www.dice') !== false) echo "img/dice.jpg";
        elseif (strpos($url[$i], 'jobs.github') !== false) echo "img/github.png";
        elseif (strpos($url[$i], 'weworkremotely.com') !== false) echo "img/weworkremotely.jpg";
        else echo "http://imgc.allpostersimages.com/images/P-473-488-90/74/7476/IB2Q100Z/posters/danger-fart-zone-humor-sign-poster.jpg";


    echo "'></a><td>" . $company[$i] . "<td>" . $salary[$i] . "<td>" . $tags[$i] . "<td>" . $score[$i] . "\n";
    }
}


?>
</table>





<!-- modal for refining search terms -->
<div id="refineSearch" class="reveal-modal" data-reveal>
  <h2>Get better results</h2>
  <p class="lead">Below are the technologies that appeared alongside your search terms most frequently. Consider adding some to your search:</p>
  
  <?php
    $i = 0;
    
   while($i < sizeof($extraTechIds))
  {
      echo "<p><label class=\"techs\"><input type='checkbox' name='tech[]' value='" . $extraTechIds[$i];
      echo "'>" . $extraTechNames[$i] . "</label>
         <input type='checkbox' name='required[]' value='" . $extraTechIds[$i] . "'></p>";
      $i++;
	
}
?>
 

  <a class="close-reveal-modal">&#215;</a>
</div>

<script src="/js/vendor/jquery.js"></script>
<script src="/js/foundation.min.js"></script>
<script>
    $(document).foundation();
</script>

</body>
</html>
