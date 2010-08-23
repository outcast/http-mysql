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
		$enc_query = $_POST['q'];
		$enc_iv = $_POST['iv'];
	} else {
		$enc_query = $_GET['q'];
		$enc_iv = $_GET['iv'];
	}
	if(DEBUG) error_log("Trying to decrypt");
	$query = decrypt(base64_decode($enc_query),base64_decode($enc_iv));
	if(DEBUG) error_log("Using query: ".$query);
	if(!$query) throw new Exception("No Query passed");
	if(DEBUG) error_log("SQL Query: ".$query);
	$result = $link->query($query);
	if($link->error) throw new Exception("MYSQL Error: ".$link->error);
	$x=0;
	while($row = $result->fetch_array(MYSQLI_ASSOC)){ 
		$rows[$x] = $row;
		$x++;
	}
	if(!DEBUG) error_log("Encrypting result");
	$enc = encrypt(json_encode($rows));
	if(!DEBUG) error_log("Sending Result back");
	echo $enc;
	
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


function encrypt($str=NULL) {
	if(!$str){ error_log("No string sent to encrypt"); return 0; }
	$size = mcrypt_get_iv_size(MCRYPT_CIPHER, MCRYPT_MODE);
	error_log("IV Size: ".$size);
	$iv = mcrypt_create_iv($size, MCRYPT_DEV_RANDOM);
	error_log("IV: ".$iv);
	$enc_str =  mcrypt_encrypt(MCRYPT_CIPHER,MCRYPT_KEY,$str,MCRYPT_MODE,$iv);
	$result['string'] = base64_encode($enc_str);
	$result['iv'] = base64_encode($iv);
	return json_encode($result);
}

function decrypt($str,$iv){
	if(!$str ) { error_log("No string sent to decrypt"); return 0; }
	if(!$iv) { error_log("Can not decrypt without IV"); return 0; }
	return str_replace("\x0", '', mcrypt_decrypt(MCRYPT_CIPHER,MCRYPT_KEY,$str,MCRYPT_MODE,$iv));
}

?>
