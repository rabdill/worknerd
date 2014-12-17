<?php
    include 'data.php';
    include 'head.php';
?>

    <link href='http://fonts.googleapis.com/css?family=News+Cycle' rel='stylesheet' type='text/css'>
    <title>Tech job listings, IT careers | Work Nerd</title>
    <style>
    .table-hover > tbody > tr:hover {
        background-color: #E8E9FF;
    }
    .label {
        margin: 3px;
        line-height: 2;
    }
    .jobTitle {
        font-size: 20px;
        font-family: 'News Cycle', sans-serif; 
    }
    a {
        color: #0341FF;
    }
    </style>
</head>
<body>

<!-- required to turn on the tooltips:  -->
<script>
$(function () {
  $('[data-toggle="tooltip"]').tooltip()
})
</script>

<?php
    include 'google-analytics.php';
    include 'navbar.php'; ?>

<div class="container" style="padding-top: 20px;">
<table class="table table-striped table-hover">
<thead><th>Job<th>Company<th>Salary<th>Location<th>Tags<th>Points<span class="label label-primary" data-toggle="tooltip" data-placement="top" title="The score is based on how many search terms are matched by the listing's tags." style="font-size: 1.2em; font-weight: bold;">?</span><tbody>




<?php	
if (isset($_GET['tech']) === false) {
    if(isset($_GET['required']) === false) {
        echo "ERROR: You did not submit search terms.";
        $search[] = "";
    }
    else {
        $search = $_GET['required'];
        #We only want to print the "refine" stuff if there's a search to refine: 
       echo "<a href=\"#\" data-toggle=\"modal\" data-target=\"#refine\">Refine your search</a>";
    }
}
else {
    if (isset($_GET['required'])) {
        $search = array_merge(array_flip(array_flip(array_merge($_GET['tech'], $_GET['required']))));
        echo "<a href=\"#\" data-toggle=\"modal\" data-target=\"#refine\">Refine your search</a>";
    }

    else {
        $search = $_GET['tech'];
        echo "<a href=\"#\" data-toggle=\"modal\" data-target=\"#refine\">Refine your search</a>";
    }
}
$query = "SELECT L.jobid AS jobid, L.url AS url, L.company AS company, L.title AS title, L.salary as salary, L.location as location, x.tech as tech
FROM listings L
LEFT JOIN tags t ON L.jobid=t.jobid
LEFT JOIN techs x ON t.techid=x.techid
WHERE t.techid IN (" . implode(",",$search);

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
    $location[$n] = $info['location'];

    $query1="SELECT x.tech AS tech, t.techid AS techid FROM techs x
	LEFT JOIN tags t ON x.techid=t.techid
	WHERE t.jobid='" . $info['jobid'] . "';";
    $data1 = mysql_query($query1);
    $points = 0;
    $tags[$n] = "";
    while ($info1=mysql_fetch_array($data1)) {
    	if(in_array($info1['techid'], $search)) {
    	    $points += 1;
	    $tags[$n] .= "<span class=\"label label-success\">" . $info1['tech'] . "</span> ";
    }
    else {
	    $tags[$n] .= "<span class=\"label label-default\">" . $info1['tech'] . "</span> ";
	    
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

array_multisort($score, SORT_DESC, $title, SORT_ASC, $company, $url, $salary, $location, $tags);

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
        echo "<tr><td>" . "<a href='" . $url[$i] . "' class=\"jobTitle\">" . $title[$i] . "</a><br>";
	echo "<img class='sourceLogo' src='";
        #print logo of source
        if (strpos($url[$i], 'www.dice') !== false) echo "img/dice.jpg";
        elseif (strpos($url[$i], 'jobs.github') !== false) echo "img/github.png";
        elseif (strpos($url[$i], 'weworkremotely.com') !== false) echo "img/weworkremotely.jpg";
	elseif (strpos($url[$i], 'sologig.com') !== false) echo "img/sologig.png";
        else echo "http://imgc.allpostersimages.com/images/P-473-488-90/74/7476/IB2Q100Z/posters/danger-fart-zone-humor-sign-poster.jpg";


    echo "'></a><td>" . $company[$i] . "<td>" . $salary[$i] . "<td>" . $location[$i] . "<td>" . $tags[$i] . "<td>" . $score[$i] . "\n";
    }
}


?>
</table>
</div>




<div class="modal fade" id="refine"  tabindex="-1" role="dialog" aria-labelledby="refineLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title">Improving result score accuracy</h4>
      </div>
      <div class="modal-body">
        <p class="lead">Below are the technologies that appeared alongside your search terms most frequently. Consider adding some to your search:</p>
        <form action="results.php" method="GET">
            <?php
            $i = 0;
            
            echo "<div style=\"height: 250px; overflow-y: scroll; overflow-x: hidden;\">";
            while($i < sizeof($extraTechIds))
            {
                
                echo "<div class=\"row panel panel-default\">
                      <div class=\"col-sm-6\">" . $extraTechNames[$i] . "</div>";
                echo "<div class=\"col-sm-6\">
                          <div class=\"btn-group\" data-toggle=\"buttons\">
                          <label class=\"btn btn-include\">";
                echo "<input type='checkbox' name='tech[]' value='" . $extraTechIds[$i] . "'>Include
                           </label>
                           <label class=\"btn btn-require\">
                           <input type='checkbox' name='required[]' value='" . $extraTechIds[$i] . "'>Require
                            </label></div>
                            </div></div>";
                $i++;
              } 
            #attach the already-included search terms to the new form
            $i = 0;
            while($i < sizeof($search))
            {
                echo "<input type='hidden' name='tech[]' value='" . $search[$i] . "'>\n";
                $i++; 
            } 

            #attach the required terms again
            if (isset($_GET['required'])) {
                $i = 0;
                while($i < sizeof($_GET['required']))
                {
                    echo"<input type='hidden' name='required[]' value='" . $_GET['required'][$i] . "'>\n";
                    $i++;
                }
            }
            ?>
            </div><!-- the scrolling div -->
            <input type="submit" value="Search" class="btn btn-primary btn-lg">
        </form>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
    </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->



</body>
</html>
