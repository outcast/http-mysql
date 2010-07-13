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

function httpMySQL($url=NULL,$query=NULL) {

	if(!$url or !$query){
		throw new Exception("Either the url or query was not provided");
	}
	$data="q=".urlencode($query);
	// create a new cURL resource
        $ch = curl_init($url);
        // set URL and other appropriate options
        curl_setopt($ch, CURLOPT_POST      ,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS    , $data);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION  ,1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	// execute cURL resource
	$response = curl_exec($ch);
	return json_decode($response);
}



?>
