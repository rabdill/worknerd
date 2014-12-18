<?php
    include 'data.php'; 
    include 'head.php';
?>
    <link href='http://fonts.googleapis.com/css?family=Special+Elite' rel='stylesheet' type='text/css'>
    <title>Tech job listings, IT careers | Work Nerd</title>
<style>
.spread-out li {
    padding-top: 10px;
}

#searchbox {
    padding: 0;
}
</style>
<script>
$('.btn').button();
</script>


</head>
<body>
<?php 
    include 'google-analytics.php';
    include 'navbar.php'; ?>
<div class="container">
    <div class="row" style="padding-top: 20px;">
        <div class="col-sm-8">
            <div class="col-sm-12">
                <h2>What is this?</h2>
                <p>Work Nerd is a new kind of job-search tool: It's a search engine for <strong>the IT industry's biggest and best job boards</strong>, without the hassle and limitations of ineffective keyword searches.
                <p>Work Nerd finds the best jobs for you <strong>based on what you're good at</strong> &ndash; just tell it what technologies you want to work with and let the app do the rest. The results you'll receive will be <strong>sorted based on how well they fit <em>your</em> requirements</strong>, and how many of your technologies are listed in the ad.
                <p>It's time a website gave you a tool to search for jobs the way that you want to. It's time for Work Nerd.
            </div>

            <div class="col-md-60000000">
            <?php
                $query = "SELECT COUNT(jobid) AS inventory FROM listings";
                $info = mysql_fetch_array(mysql_query($query));
                $inventory = $info['inventory'];
                $query = "SELECT COUNT(techid) AS techs FROM techs";
                $info = mysql_fetch_array(mysql_query($query));
                $techs = $info['techs'];
                $query = "SELECT COUNT(tagid) AS tags FROM tags";
                $info = mysql_fetch_array(mysql_query($query));
                $averageTags = $info['tags'] / $inventory;
                $query = "SELECT COUNT(jobid) AS salaried FROM listings WHERE salary IS NOT null";
                $info = mysql_fetch_array(mysql_query($query));
                $salaried = ($info['salaried'] * 100) / $inventory ;
            ?>
                <h2>What are the numbers?</h2>
                <ul class="spread-out">
                    <li>We currently have <strong><?php echo $inventory . " jobs,";?></strong>
                    <li> using <strong><?php echo$techs; ?> different technologies</strong>,
                    <li>from <strong>4 IT job boards</strong>, including <a href="http://www.dice.com">Dice</a>, <a href="http://jobs.github.com">GitHub</a>, and <a href="https://weworkremotely.com">WeWorkRemotely</a> (from <a href="https://signalvnoise.com/posts/3671-our-new-job-board-weworkremotelycom">the Basecamp people</a>).
                    <li>The average listing is tagged with <strong><?php echo round($averageTags, 2); ?> technologies</strong>.
                    <li><strong><?php echo round($salaried, 1); ?> percent</strong> of listings have salary data.
                </ul>
            </div>
            <div class="col-md-6">
                <h2>What do I do?</h2>
                <p>Just scroll through the list and indicate which technologies you work with. The left checkbox will include it in the search, and the right checkbox will <em>require</em> that technology be included in any results.
            </div>
        </div> 
        <div class="col-sm-4 panel panel-default" id="searchbox" style="margin-top: 20px;">
        <!-- top margin here is to match the headline on the left side of the page. -->
                <div class="panel-heading">
                    <h2 class="panel-title">Search</h2>
                </div>
                <div class="panel-body nopadding">
                    <form action="results.php" method="GET" class="panel-body nopadding" style="margin-bottom: 0px;"> 
                        <?php
                        $query = "SELECT * FROM techs;";
                        $data=mysql_query($query);?>
                        <div style="height: 600px; overflow-y: scroll; overflow-x: hidden;">
                 <?php
                     while($info = mysql_fetch_array($data))
                     {
                        echo "<div class=\"row panel panel-default\">
                            <div class=\"col-sm-4 col-xs-6\">" . $info['tech'] . "</div>";
                        echo "<div class=\"col-sm-8 col-xs-6\">
                                <div class=\"btn-group\" data-toggle=\"buttons\">
                                <label class=\"btn btn-include\">";
                        echo "<input type='checkbox' name='tech[]' value='" . $info['techid'] . "'>Include
                                </label>
                             <label class=\"btn btn-require\">
                                <input type='checkbox' name='required[]' value='" . $info['techid'] . "'>Require
                                </label></div>
                            </div></div>";
                    }
                           ?>
                        </div><!-- the scrolling div        -->
                        <div class="panel-footer" style="text-align: center;">                                          
                            <input type="submit" value="Search" class="btn btn-primary btn-lg">
                        </div>
                    </form>
                </div><!-- panel-body -->
            </div>
        </div>
</div>
</body>
</html>
