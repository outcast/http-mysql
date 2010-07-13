# http-mysql - mysql queries over http
# Copyright (C) 2010 James Jones
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License along
# with this program; if not, write to the Free Software Foundation, Inc.,
# 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
#
# If you have question please contact: james@qslnetworks.com or james@freedomnet.co.nz
#########################################################################################
#
#	Note:
#		This an example module for python that has been designed to work with Google App Engine
#		

from django.utils import simplejson as json
from google.appengine.api import urlfetch
import logging
import urllib


LEVELS = {'debug': logging.DEBUG,
          'info': logging.INFO,
          'warning': logging.WARNING,
          'error': logging.ERROR,
          'critical': logging.CRITICAL}

debug =1
query_url = "http://localhost/http-mysql/http-mysql.php"

def query(query):
	statement = {
		'q' : query
	}
	if debug: logging.info('Recieved Query: '+query)
	query_encoded = urllib.urlencode(statement)
	if debug: logging.info('Enconded Query: '+query_encoded)
	result = urlfetch.fetch(query_url+"?"+query_encoded)
	if debug: logging.info('Recieved Content: '+result.content)
	results = json.loads(result.content)
	return results
