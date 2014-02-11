dicestart
=========

Mini framework with small footprint for easy creating blockchain games Satoshidice like.

PoC game: coinflip with 50% chances to win and 1.95 odds predefined

Requirements:
- feathercoind or any another cryptocurrency wallet that is based on bitcoind with RPC enabled
- mysql
- php

Installation:
- put the files on the machine where php is installed
- create db and dummy table ( check folder sql )

Configuration ( read the comments and set up constants/variables with your values):
inc/conf.php

How to use (console):
php go.php 

How to create your game: If you can't figure out from play/coinflip/head.php or tail.php manual soon

Displaying results from the logs on the betting site.
Example:
- get the last N plays with: tail -N /path_to/playlogs/play_log2013-12-24.txt
- hash the output
- read output line by line and prepare for sending on the clientside
- send to the client hash + data and add hash to the session
- client pools the server with hash
- if hash is the same, no new plays if not send new hash and data
- on client side display only new data

TODO: 
Database support, debug log files,... 

Donations:

BTC: 1LuckAsHuFJ3Y59aztSxzHaBtRihc2vDmX

FTC: 6sLaveXfN6Nhi96LVufa4GjrojGnXsbc4w 

Licence: MIT Licence
