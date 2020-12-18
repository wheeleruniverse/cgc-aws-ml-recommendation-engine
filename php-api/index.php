<?php 
    
    $self = "https://wheelerrecommends.com";
    
    class Title {
        
        # Fields
        public $id;
        public $cluster;
        public $distance;
        public $genres;
        public $name;
        public $rating;
        public $votes;
        public $year;
        
        # Generated Fields
        public $distanceDiff;
        
        
        function get_id(){
            return $this->id;
        }
        function set_id($id){
            $this->id = $id;
        }
        function get_cluster(){
            return $this->cluster;
        }
        function set_cluster($cluster){
            $this->cluster = $cluster;
        }
        function get_distance(){
            return $this->distance;
        }
        function set_distance($distance){
            $this->distance = $distance;
        }
        function get_genres(){
            return $this->genres;
        }
        function set_genres($genres){
            $this->genres = $genres;
        }
        function get_name(){
            return $this->name;
        }
        function set_name($name){
            $this->name = $name;
        }
        function get_rating(){
            return $this->rating;
        }
        function set_rating($rating){
            $this->rating = $rating;
        }
        function get_votes(){
            return $this->votes;
        }
        function set_votes($votes){
            $this->votes = $votes;
        }
        function get_year(){
            return $this->year;
        }
        function set_year($year){
            $this->year = $year;
        }
        
        
        function get_distanceDiff(){
            return $this->distanceDiff;
        }
        function set_distanceDiff($distanceDiff){
            $this->distanceDiff = $distanceDiff;
        }
    }

    function closest_word($input, $words, &$percent = null){
        
        $shortest = -1;
        foreach($words as $i){
            $lev = levenshtein($input, $i);
            if ($lev == 0){
                $closest = $i;
                $shortest = 0;
                break;
            }
            if ($lev <= $shortest || $shortest < 0){
                $closest = $i;
                $shortest = $lev;
            }
        }
        $percent = 1 - levenshtein($input, $closest) / max(strlen($input), strlen($closest));
        return $closest;
    }
    
    function read_titles(){
        
        $idx = 0;
        $titleInstances = array();
        if(($file = fopen("/var/task/imdb.csv", "r")) !== FALSE){
            while(($row = fgetcsv($file)) !== FALSE){
                $idx++;
                if ($idx == 1){
                    continue;
                }
                $cnt = count($row);
                
                $inst = new Title();
                $inst->set_id($row[0]);
                $inst->set_genres($row[1]);
                $inst->set_rating($row[2]);
                $inst->set_name($row[3]);
                $inst->set_votes($row[4]);
                $inst->set_year($row[5]);
                $inst->set_cluster($row[6]);
                $inst->set_distance($row[7]);

                $titleInstances[] = $inst;
            }
            fclose($file);
        }
        return $titleInstances;
    }
    
    
    function link_to_title($id, $name){
        
        global $self;
        return "<a href='$self?title=$id'>$name</a>";
    }
    
    
    function write_titles($titleInstances){
        
        global $self;
        
        echo "<div>";
        foreach($titleInstances as $i){
            
            $id = $i->get_id();
            $name = $i->get_name();
            $year = $i->get_year();
            
            echo "<p id='$id' class='poster'>";
            echo "<a href='$self?title=$id'>";
            echo "<img src='/assets/img/$id.jpg' onerror=\"this.onerror=null;this.src='/assets/img/noposter.jpg';\" />";
            
            $name_substring = null;
            if(strlen($name) > 20){
                $name_substring = substr($name, 0, 17) . "...";
            } else {
                $name_substring = substr($name, 0, 20);
            }
            
            echo "$name_substring ($year)";
            echo "</a>";
            echo "</p>";
        }
        echo "<p style='clear: both;'></p>";
        echo "</div>";
    }
    
    
    function write_title($title){
        
        $id = $title->get_id();
        $genres = $title->get_genres();
        $name = $title->get_name();
        $rating = $title->get_rating();
        $votes = $title->get_votes();
        $year = $title->get_year();
        
        include "/var/task/title.php";
    }
    
    
    function find_by_id($titleInstances, $targetId){
        
        $target = null;
        foreach($titleInstances as $i){
            $titleId = $i->get_id();
            if ($titleId == $targetId){
                $target = $i;
                break;
            }
        }
        return $target;
    }
    
    
    function find_by_cluster($titleInstances, $targetCluster){
        
        $clusterInstances = [];
        foreach($titleInstances as $i){
            $titleCluster = $i->get_cluster();
            if ($titleCluster == $targetCluster){
                $clusterInstances[] = $i;
            }
        }
        return $clusterInstances;
    }
    
    
    function find_by_distance($titleInstances, $targetName){
        
        # Find Count 
        $cnt = count($titleInstances);
        if ($cnt < 1){
            throw new Exception("find_by_distance: titleInstances is invalid");
        }
        
        # Find Title Index
        $idx = -1;
        $targetDistance = null;
        foreach($titleInstances as $i => $v){
            
            $titleName = $v->get_name();
            if ($titleName == $targetName){
                $idx = $i;
                $targetDistance = $v->get_distance();
                break;
            }
        }
        if($idx < 0){
            throw new Exception("find_by_distance: could not find $name in titleInstances");
        }
        
        # Populate distanceDiff
        foreach($titleInstances as $i => $v){
            
            $diff = 0;
            if ($i == $idx){
                $diff = 999999;
            } else {
                $diff = abs($targetDistance - $v->get_distance());
            }
            $v->set_distanceDiff($diff);
        }
        
        # Sort By distanceDiff
        usort($titleInstances, fn($a, $b) => strcmp($a->get_distanceDiff(), $b->get_distanceDiff()));
        
        return $titleInstances;
    }
?>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1">
    
        <!-- Favicon -->
        <link rel="shortcut icon" href="/assets/img/favicon.ico">
        
        <!-- Custom CSS -->
        <link rel="stylesheet" href="/assets/css/main.css"/>
        <link rel="stylesheet" href="/assets/css/media.css"/>
        
        <!-- Font Awesome CSS -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    </head>
    <body>
        <div>
            <div class="flex">
                <div id="brand-logo" >
                    <a href="https://wheelerrecommends.com">
                        <img src="/assets/css/favicon.jpg" />
                    </a>
                </div>
                <div id="brand-name">
                    <h1>WHEELER RECOMMENDS</h1>
                </div>
                <div>
                    <a href="https://github.com/wheelers-websites/CloudGuruChallenge_1020">
                        <i class="fa fa-github github"></i>
                    </a>
                </div>
            </div>
            <hr/>
            <?php 
                $titleInstances = read_titles();
                
                if(isset($_POST['query'])){
                    
                    $percent = null;
                    $query = $_POST['query'];
                    unset($_POST);
                    
                    $titleNames = array_column($titleInstances, 'name');
                    $found = closest_word($query, $titleNames, $percent);
                    
                    $inst = null;
                    foreach($titleInstances as $i){
                        $titleName = $i->get_name();
                        if($titleName == $found){
                            $inst = $i;
                            break;
                        }
                    }
                    
                    $inst_id = $inst->get_id();
                    
                    if ($percent == 1){
                        global $self;
                        header("Location: $self?title=$inst_id");
                    }
                    else if($percent >= 0.5){
                        echo "Did you mean " . link_to_title($inst_id, $found) . "? (" . round($percent * 100, 2) . "%) <br/>";
                    }
                    else {
                        echo "No Results";
                    }
                }
                else if(isset($_GET['title'])){
                    
                    $title = $_GET['title'];
                    $target = find_by_id($titleInstances, $title);
                    
                    if ($target == null){
                        echo "Could Not Find Title: '$title'";
                        return;
                    }
                    
                    $targetName = $target->get_name();
                    $targetCluster = $target->get_cluster();
                    $targetDistance = $target->get_distance();
                    
                    $clusterInstances = find_by_cluster($titleInstances, $targetCluster);
                    $distanceInstances = find_by_distance($clusterInstances, $targetName);
                    
                    write_title($target);
                    
                    echo "<br/>";
                    echo "<hr/>";
                    echo "<h1>Recommendations</h1>";
                    
                    $pageInstances = array_slice($distanceInstances, 0, 9);
                    write_titles($pageInstances);
                }
                else {
                    shuffle($titleInstances);
                    $pageInstances = array_slice($titleInstances, 0, 100);
                    write_titles($pageInstances);
                }
            ?>
        </div>
    </body>
</html>



































