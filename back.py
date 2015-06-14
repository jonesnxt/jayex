import json
import urllib2
import uuid
import random
import string

# send api call, must have NXT server running 
def nxtapi(typ):
	return json.load(urllib2.urlopen('http://jnxt.org:7876/nxt', typ));

