<?php    
    class Connect {
        private $db_host = 'localhost';
        private $db_user = 'root';
        private $db_password = '';
        private $db_db = 'ttc';
    
        function dbconnect(){
            $conn = mysql_connect($this->db_host, $this->db_user, $this->db_password)
                or die ("<br/>Could not connect to MySQL server");
             
            mysql_select_db($this->db_db, $conn)
                or die ("<br/>Could not select the indicated database");
            
            return $conn;
        }
    } 
   
    class Bus {
        public function select($row, $table, $join, $where, $sort, $limit) {   
            $sql = 'SELECT ' . $row . ' FROM `' . $table . '` ' . $join . ' WHERE ' . $where . ' ' . $sort . ' ' . $limit;
            
            echo $sql;  // This is to display the join. For debuging
            
            $query = mysql_query($sql);

            if($query)
            {
                $this->numResults = mysql_num_rows($query);
                
                for($i = 0; $i < $this->numResults; $i++)
                {
                    $result = mysql_fetch_array($query);
                    $key = array_keys($result); 
                    for($x = 0; $x < count($key); $x++)
                    {
                        // Sanitizes keys so only alphavalues are allowed
                        if(!is_int($key[$x]))
                        {
                            if(mysql_num_rows($query) > 1)
                                $this->result[$i][$key[$x]] = $result[$key[$x]];
                            else if(mysql_num_rows($query) < 1)
                                $this->result = null; 
                            else
                                $this->result[$key[$x]] = $r[$key[$x]]; 
                        }
                    }
                }            
                return $this->result; 
            }
            else
            {
                return false; 
            }
        }
    }
?>