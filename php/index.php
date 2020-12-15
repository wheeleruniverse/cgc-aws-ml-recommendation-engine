<?php 
    
    $self = "https://example.com";
    
    class Title {
        
        public $id;
        public $name;
        public $cluster;
        public $distance;
        public $distanceDiff;
        
        
        function get_id(){
            return $this->id;
        }
        function set_id($id){
            $this->id = $id;
        }
        function get_name(){
            return $this->name;
        }
        function set_name($name){
            $this->name = $name;
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
        if(($file = fopen("imdb.csv", "r")) !== FALSE){
            while(($row = fgetcsv($file)) !== FALSE){
                $idx++;
                if ($idx == 1){
                    continue;
                }
                $cnt = count($row);
                $inst = new Title();
                $inst->set_id($row[0]);
                $inst->set_name($row[1]);
                $inst->set_cluster($row[2]);
                $inst->set_distance($row[3]);
                $titleInstances[] = $inst;
            }
            fclose($handle);
        }
        return $titleInstances;
    }
    
    
    function link_to_title($name){
        
        global $self;
        return "<a href='$self?title=$name'>$name</a>";
    }
    
    
    function write_titles($titleInstances){
        
        foreach($titleInstances as $i){
            echo link_to_title($i->get_name()) . "<br/>";
        }
    }
    
    
    function find_by_name($titleInstances, $targetName){
        
        $target = null;
        foreach($titleInstances as $i){
            $titleName = $i->get_name();
            if ($titleName == $targetName){
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
        
        
        # Limit 3
        return array_slice($titleInstances, 0, 3);
    }
?>
<html>
    <head>
        <style>
        </style>
    </head>
    <body>
        <div>
            <form id="search-form" action="" method="GET">
                <?php 
                    $query = "";
                    if(isset($_GET['query'])){
                        $query = $_GET['query'];
                    }
                    else if(isset($_GET['title'])){
                        $query = $_GET['title'];
                    }
                    echo "<input type='text' name='query' value='$query' />"
                ?>
                <input type="submit" value="Search" />
            </form>
            <hr/>
            <?php 
                $titleInstances = read_titles();
                
                if(isset($_GET['query'])){
                    
                    $percent = null;
                    $query = $_GET['query'];
                    $titleNames = array_column($titleInstances, 'name');
                    $found = closest_word($query, $titleNames, $percent);
                    
                    if ($percent == 1){
                        global $self;
                        header("Location: $self?title=$found");
                    }
                    else if($percent >= 0.5){
                        echo "Did you mean " . link_to_title($found) . "? (" . round($percent * 100, 2) . "%) <br/>";
                    }
                    else {
                        echo "No Results";
                    }
                }
                else if(isset($_GET['title'])){
                    
                    $title = $_GET['title'];
                    $target = find_by_name($titleInstances, $title);
                    
                    if ($target == null){
                        echo "Could Not Find Title: '$title'";
                        return;
                    }
                    
                    $targetName = $target->get_name();
                    $targetCluster = $target->get_cluster();
                    $targetDistance = $target->get_distance();
                    
                    $clusterInstances = find_by_cluster($titleInstances, $targetCluster);
                    $distanceInstances = find_by_distance($clusterInstances, $targetName);
                    
                    foreach($distanceInstances as $i){
                        $distanceName = $i->get_name();
                        $distanceDist = $i->get_distance();
                        echo "I recommend $distanceName (Target: $targetDistance | Recommendation: $distanceDist) <br/>";
                    }
                }
            ?>
        </div>
    </body>
</html>



































