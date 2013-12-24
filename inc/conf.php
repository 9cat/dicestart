<?php
//db connection parameters
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DATABASE', 'dice');

//wallet / daemon rpc server parameters
define('RPC_HOST','127.0.0.1');
define('RPC_PORT','9331');
define('RPC_USER','slavco');
define('RPC_PASS','');


//path to php binary
define('PATH_TO_PHP', 'php');

//server secret to prove the fairness
define('SERVER_PLAY_KEY', 'some long key with spec^al chars, numb3rs, lOWER & UpperCase!');

//include path
define('INCLUDE_PATH', '/var/www/swork/dice/pr/inc/');

//game directory - defines directory where the game logic + play address are set up come in place
define('PLAY_DIRECTORY', INCLUDE_PATH.'play/');

//json_rpc client location
define('JSON_RPC_CLIENT',INCLUDE_PATH.'jsonRPCClient.php');

//address where to hold the rest of the play outcome - never hold everything on the play addresses. 
//Updating play address with funds need to be performed over finance managament scripts or better create it under the same wallet
define('DELIVER_FUNDS','6mkdiZDD1aisfmxnkuHKWuih1bNjwC5LQM');

//this should be calculated but best practice is to check if sometimes doesn't has some extreme value - it's based on the transaction size!
define('TRANSACTION_FEE', 0.005);
define('LOOSER_BACK', 0.001);

//log location
define('PLAY_LOG_LOCATION', '/var/www/swork/dice/pr/playlogs/');

$game_addresses = array(
	//address => game script path ( under PLAY_DIRECTORY )
	"6headp1fo8AmDoFvuJndGT3b4VTEiwVU7E"=>"coinflip/head.php",
	"6taiLoiPTGPuQXFRLPyreYTaQyyTBQbxbM"=>"coinflip/tail.php"
);

$game_odds = array(
	//address => odds for the address
	"6headp1fo8AmDoFvuJndGT3b4VTEiwVU7E"=>1.95,
	"6taiLoiPTGPuQXFRLPyreYTaQyyTBQbxbM"=>1.95
);




