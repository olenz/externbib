<?php
    // alternative sqlite functions for the dba_* functions
    function bibdb_open($path, $mode, $table="default_table")
    {
        try {
            $sqliteobject = new SQLite3($path);
        } catch (Exception $e) {
            error_log("Error in bibdb_open: $e->getMessage()\n", 3, "/var/log/mediawiki/error.log");
            return false; 
        }
        try {
            $sqliteobject->exec("CREATE TABLE IF NOT EXISTS $table (KEY TEXT, VALUE TEXT)");
        } catch (Exception $e) {
            error_log("Error in bibdb_open: $e->getMessage()\n", 3, "/var/log/mediawiki/error.log");
	    return false;
        }
        return $sqliteobject;
    }
    
    
    
    function bibdb_insert($keydata, $valuedata, $sqliteobject, $table="default_table")
    {
        try {
            $savevalue = SQLite3::escapeString($valuedata);
            $sqlstring = "INSERT INTO $table (KEY, VALUE) VALUES (" . "'$keydata', " . "'$savevalue');";
            $sqliteobject->exec($sqlstring);
        } catch (Exception $e) {
            return false;
        }
    }
    
    
    function bibdb_query($sqliteobject, $table="default_table")
    {
        $query = "SELECT * FROM $table;";
        $res = $sqliteobject->query($query);
        return $res;
    }
    

    function bibdb_fetch($key, $sqliteobject, $table="default_table")
    {
        $sqlstring = "SELECT VALUE FROM $table WHERE KEY='$key';";
        $fetch = $sqliteobject->querySingle($sqlstring);
        $fetch_readable = print_r($fetch, true);
        $sqliteobject_readable = print_r($sqliteobject, true);
        return $fetch; 
    }


    function bibdb_close($sqliteobject)
    {
        $status = $sqliteobject->close();
        return $status;
    }
?>
