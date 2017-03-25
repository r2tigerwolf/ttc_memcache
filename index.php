<?php
    /*******************************************************************
    This is another version of the skeleton TTC APP with the use of
    Views and Memcache to speed up the results.  Limit had to be set 
    in SQL to prevent arrays from exceeding the Memcache limit.
    There is no styling, Javascript, or any security feature in 
    this APP; customization should be easy.
    You can download the TTC csv files from this URL:
    http://opendata.toronto.ca/TTC/routes/OpenData_TTC_Schedules.zip
    
    This App will create the database and tables, and import the csv
    files to their respective tables.
    https://github.com/r2tigerwolf/ttc_dump
    ********************************************************************/
?>
<?php 
    include("db.class.php");
    $memcache = new Memcache();
    $memcache->connect('localhost', 11211) or die ("Could not connect");
?>
    
    <form action="<?php echo $_SERVER['PHP_SELF'];?>" method="POST" >
        <input type="text" name="route_name" />
        <input type="submit" value="Find Bus" />
    </form>

<?php 
    $conn = new Connect();
    
    $busConn = $conn->dbconnect();
    
    $bus = new Bus; 
       
    $route_cache_result = array();
    $trips_cache_result = array();
    
    $memcache->set('key', '', MEMCACHE_COMPRESSED, 100);
    
    if(isset($_POST['route_name'])) {
    
        $route_cache_result = $memcache->get('route_'.$_POST['route_name']);
    
        if($route_cache_result) {
            $route_result = $route_cache_result;
            echo "<br/>this is cached<br/>";
        } else {
            
            $sqlArray = array('conn' => $busConn, 'rows' => '*', 'table' => 'bus_view', 'join' => '', 'where' => 'route_long_name like "%'.$_POST["route_name"].'%"', 'order' => 'ORDER BY route_short_name DESC', 'limit' => 'LIMIT 500');            
            $routeResult = $bus->select($busConn, $rows, $table, $join, $where, $order, $limit); 
            $memcache->set('route_'.$_POST['route_name'], $routeResult, MEMCACHE_COMPRESSED, 100);            
            echo "<br/>this is NOT cached<br/>";
        }
    
        //$memcache->flush(0);
    
        echo '<ul id="route">';
        
        foreach($routeResult  as $key => $val) {
            echo '<li><a href = "' .$_SERVER['PHP_SELF'] . '?route=' . $val['route_id'] . '&routename=' . $val['route_long_name'] . '">' . $val['route_long_name'] . ' ' . $val['route_short_name'] . '</a></li>';  
        }
    
        echo '</ul>';
        $memcache->close(); 
    }
    
    if(isset($_GET['route'])) {
    
        $trips_cache_result = $memcache->get('trips_'.$_GET['route']); // Memcached object 
    
        if($trips_cache_result) {
            $trips_result = $trips_cache_result;
            echo "<br/>this is cached<br/>";
        } else {
            $sqlArray = array('conn' => $busConn, 'rows' => '*', 'table' => 'route_view', 'join' => '', 'where' => 'route_id = "'.$_GET['route'].'"', 'order' => '', 'limit' => 'LIMIT 500');
            $tripsResult = $bus->select($sqlArray);
            $memcache->set('trips_'.$_GET['route'], $tripsResult, MEMCACHE_COMPRESSED, 100);
            
            echo "<br/>this is NOT cached<br/>";
        }
        
        //$memcache->flush(0);
        
        echo '<ul id="trips">';
        foreach($tripsResult as $key => $val) {
            echo '<li>';
            echo $val['route_long_name']. ', Bus Name: ' . $val['trip_headsign'] . ', Arrive at: ' . 
            date("g:i A", strtotime($val['arrival_time'])) . ', Depart at: ' . 
            date("g:i A", strtotime($val['departure_time'])) . ', Coordinates: ' . 
            $val['stop_lat'] . ' ' . $val['stop_lon'] . '<br />';
            echo $val['stop_name'];
            echo '<br /><br />';
            echo '</li>';  
        }
        echo '</ul>';
        $memcache->close(); 
    }
?>