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

</style>

</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container">
    <div class="row">
        <div class="col-md-6">
            <h2>What is this?</h2>
            <p>Work Nerd is a new kind of job-search tool: It's a search engine for <strong>the IT industry's biggest and best job boards</strong>, without the hassle and limitations of ineffective keyword searches.
            <p>Work Nerd finds the best jobs for you <strong>based on what you're good at</strong> &ndash; just tell it what technologies you want to work with and let the app do the rest. The results you'll receive will be <strong>sorted based on how well they fit <em>your</em> requirements</strong>, and how many of your technologies are listed in the ad.
            <p>It's time a website gave you a tool to search for jobs the way that you want to. It's time for Work Nerd.
        </div>

        <div class="col-md-6">
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
        ?>
            <h2>What are the numbers?</h2>
            <ul class="spread-out">
                <li>We currently have <strong><?php echo $inventory . " jobs,";?></strong>
                <li> using <strong><?php echo$techs; ?> different technologies</strong>,
                <li>from <strong>4 IT job boards</strong>, including <a href="http://www.dice.com">Dice</a>, <a href="http://jobs.github.com">GitHub</a>, and <a href="https://weworkremotely.com">WeWorkRemotely</a> (from <a href="https://signalvnoise.com/posts/3671-our-new-job-board-weworkremotelycom">the Basecamp people</a>).
                <li>The average listing is tagged with <strong><?php echo round($averageTags, 2); ?> technologies</strong>.
            </ul>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <h2>What do I do?</h2>
            <p>Just scroll through the list and indicate which technologies you work with. The left checkbox will include it in the search, and the right checkbox will <em>require</em> that technology be included in any results.
        </div>
        
        <div class="col-md-6">
            <form action="results.php" method="GET"> 
                <?php
                $query = "SELECT * FROM techs;";
                $data=mysql_query($query);?>
                <div style="height: 300px; overflow-y: scroll; overflow-x: hidden;">
                    <?php
                    while($info = mysql_fetch_array($data))
                    {
                            echo "<div class=\"row\" style=\"margin-left: 70px;\">
                                    <div class=\"small-6 columns\"><label class=\"techs\"><input type='checkbox' name='tech[]' value='" . $info['techid'];
                            echo "'>" . $info['tech'] . "</label>
                                    <input type='checkbox' name='required[]' value='" . $info['techid'] . "'></div></div>";
                    }
                    ?>
                </div>
                <div style="text-align: center; padding-top: 10px;">                                          
                    <input type="submit" value="Search" class="btn btn-primary btn-lg">
                </div>
            </form>
        </div>
    </div>
</div>


    <script src="/js/vendor/jquery.js"></script>
    <script src="/js/foundation.min.js"></script>
    <script>
    $(document).foundation();
</script>

</body>
</html>
