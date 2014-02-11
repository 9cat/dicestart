<?php
error_reporting(E_ALL);

include_once('/var/www/swork/dice/pr/inc/conf.php');

mysql_connect(DB_HOST,DB_USER,DB_PASS) or die(mysql_error()." ".mysql_errno());
mysql_select_db(DATABASE) or die(mysql_error()." ".mysql_errno());

//include json_rpc client 
include_once(JSON_RPC_CLIENT);

//connect to the wallet 
$ftc = new jsonRPCClient('http://'.RPC_USER.':'.RPC_PASS.'@'.RPC_HOST.':'.RPC_PORT.'/');

$blockcount = $ftc->getblockcount(); 

while ( true ){
	sleep(5);
	$txx = array();
	//scan last x=50 blocks for incomming but not processes transaction
	$transactions = $ftc->listsinceblock($ftc->getblockhash($blockcount-50));
	
	if ( is_array($transactions["transactions"]) && sizeof($transactions["transactions"])>0 ){
		foreach ($transactions["transactions"] as $t){
			
			if ( array_key_exists($t["address"], $game_addresses) && $t["category"] == "receive"){
					
				//prevent transaction malleability	
				
				$rawtx = $ftc->getrawtransaction($t["txid"], 1);
				
				$tprocessed = "";
				if ( is_array($rawtx) ){
					foreach ( $rawtx["vin"] as $tinput ){
						$tprocessed .= $tinput["txid"].$tinput["vout"];	
					}
				}else{
					die("grrr");
				}
				$tprocessed = md5($tprocessed);
				
				//end prevent transaction malleability	
				
				//$sql = "select * from processed where txid = '".$t["txid"]."'";
				$sql = "select * from processed where txid = '".$tprocessed."'";
				
				$rez = mysql_query($sql);
				
				if ( mysql_num_rows($rez) == 0){
				
					//async call the game with the tx id
					exec(PATH_TO_PHP." ".PLAY_DIRECTORY.$game_addresses[$t['address']]." '".$t['txid']."' '".$game_odds[$t['address']]."' '".$t['address']."' > /dev/null 2>/dev/null &");
					//update the txid as processed
					/*$sql ="
					INSERT INTO  
						`processed` 
						(
							`txid`
						)
					VALUES 
						(
						'".$t['txid']."'
						);
					";//*/
					$sql ="
					INSERT INTO  
						`processed` 
						(
							`txid`
						)
					VALUES 
						(
						'".$tprocessed."'
						);
					";
					@mysql_query($sql);
				}
			}
		}	
	}//end if ( is_array($transactions["transactions"]) && sizeof($transactions["transactions"])>0 )
}//end while ( true )
