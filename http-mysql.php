<?php
/*

    http-mysql - mysql queries over http
    Copyright (C) 2010 James Jones

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along
    with this program; if not, write to the Free Software Foundation, Inc.,
    51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.

    If you have question please contact: james@qslnetworks.com or james@freedomnet.co.nz


*/

define('DEBUG',1);
require_once './config/config.php';

try{
	$link = getDBH(1);
	if(!$link){
		error_log("MYSQL Error: ".$link->error);
		throw new Exception("MYSQL Error: ".$link->error);
	}
	if(!$_GET['q']) {
		$query = $_POST['q'];
	} else {
		$query = $_GET['q'];
	}
	if(!$query) throw new Exception("No Query passed");
	if(DEBUG) error_log("SQL Query: ".$query);
	$result = $link->query($query);
	if($link->error) throw new Exception("MYSQL Error: ".$link->error);
	$x=0;
	while($row = $result->fetch_array(MYSQLI_ASSOC)){ 
		$rows[$x] = $row;
		$x++;
	}
	echo json_encode($rows);
	
} catch(Exception $e) {
	error_log($e->getMessage());
	echo "Error: ".$e->getMessage();
}
				
	

function getDBH($rw=0,$db=NULL)
{
        if(!$db) $db = MYSQL_DB;
        if($rw){
                $user = MYSQL_RW_USER;
                $pass = MYSQL_RW_PASS;
        } else {
                $user = MYSQL_RO_USER;
                $pass = MYSQL_RO_PASS;
        }
        $link = new Mysqli;
        $link->init();
        $link->options(MYSQLI_OPT_CONNECT_TIMEOUT,5);

        if(MYSQL_HOST_PRIMARY)  { $host[0] = MYSQL_HOST_PRIMARY;}
        else {
                error_log("Primary MYSQL server not configured");
                throw new Exception("Primary MYSQL server not configured");
        }

        if(MYSQL_HOST_SECONDARY) $host[1] = MYSQL_HOST_SECONDARY;

        shuffle($host);
        foreach($host as $srv)
          {
            if(DEBUG) error_log("Using DB Server: ".$srv);
            $link->real_connect($srv, $user, $pass, $db);
            if($link->errno)  { error_log("Mysql Error: ".$link->errno."->".$link->error.": trying other server"); }
            else  { break; }
        }
        if($link->errno) {
                error_log("Mysql Error: ".$link->errno."->".$link->error.": both servers failed");
                throw new Exception("Mysql Error: ".$link->errno."->".$link->error.": both servers failed");
        }
        return $link;
}
?>
