<?php

if ( isset($argv[1]) && isset($argv[2]) && isset($argv[3]) ){
	$stx = $argv[1];
	$odd = $argv[2];
	$odd_address = $argv[3];
}else{
	die("Not enought input params to perform the game");
}

//need for connections parameters
include_once('/path_to/pr/inc/conf.php');

//connect to the DB 
mysql_connect(DB_HOST,DB_USER,DB_PASS) or die(mysql_error()." ".mysql_errno());
mysql_select_db(DATABASE) or die(mysql_error()." ".mysql_errno());

//include json_rpc client 
include_once(JSON_RPC_CLIENT);

//connect to the wallet 
$ftc = new jsonRPCClient('http://'.RPC_USER.':'.RPC_PASS.'@'.RPC_HOST.':'.RPC_PORT.'/');


//set up min and max bets for this game
$max_bet = 0.5;
$min_bet = 0.1;



//code start
$play_log = array();

$play_log["txin"] = $stx;
$play_log["oddaddress"] = $odd;
$play_log["odd"] = $odd;



$transactiondetails = $ftc->getrawtransaction($stx,1);

$vintxid = $transactiondetails['vin'][0]['txid'];
$vinvout = $transactiondetails['vin'][0]['vout'];

$k = 0;
$kvin = 0;
$bet = '';
foreach( $transactiondetails["vout"] as $td ){
		
	if ( $td["scriptPubKey"]["addresses"][0] == $odd_address ){
		
		$bet = $td["value"];
		$vinscriptpubkey = $td["scriptPubKey"]["hex"];
		$kvin = $k;
		break;
	}	
	$k++;
}

$play_log["bet"] = $bet;


//check bet if in rang $min_bet to $max_bet
if ($bet >= $min_bet && $bet <= $max_bet ){
	
	$check = false;
	while ( !$check ){
		$transactionin = $ftc->getrawtransaction($vintxid,1);
		$fodd = false;
		foreach ($transactionin["vout"] as $vitem){
			if ( $vitem["scriptPubKey"]["addresses"][0] == $odd_address ){
				$fodd = true;
			}
		}
		if ( $fodd ){
			$vintxid = $transactionin['vin'][0]['txid'];
			$vinvout = $transactionin['vin'][0]['vout'];
		}else{
			$check = true;
							
		}
	}
	
	//get the player address
	$playeraddress = $transactionin['vout'][$vinvout]['scriptPubKey']['addresses'][0];
	$play_log["betaddress"] = $playeraddress;
		
	//perform the game - change this calculations on your way
	$salt = SERVER_PLAY_KEY;
	$txidrnd = $stx;
	$hash = md5($txidrnd.$salt);
	$fin = substr($hash, strlen($hash)-4);
	$a = unpack("L",pack("C*",$fin[3],$fin[2],$fin[1],$fin[0]));
	$rnd = 0;
	if ( is_array($a) ){
		foreach($a as $val){
			if ( is_numeric($val) ){	
			$rnd = $val;
			break;
			}
		}
	}
	$rnd = (int) $rnd;	
	
	if ( $rnd % 100 > 49 ){
			//loose
			$intx = array(
				array(
					"txid"=>$stx,
					"vout"=>$kvin
				)
			);
			$ttt = $bet-LOOSER_BACK-TRANSACTION_FEE;
			$inadd = array(
				$playeraddress => LOOSER_BACK,
				DELIVER_FUNDS => $ttt	
			);
			$play_log["outcome"] = 0;
			$play_log["sumout"] = LOOSER_BACK;
					
		}else{
				
			//win
			$betvalue = $bet;
			$oddvalue = $odd;
			$win = $betvalue * $oddvalue;
			$intx = array(
				array(
					"txid"=>$stx,
					"vout"=>$kvin
				)
			);
			//prepare to pay the winner 
			$money = $ftc->listunspent();
			$sumpay = 0;
			foreach ($money as $cash){
					$sumpay += $cash["amount"];
					$intx[] = array(
							"txid"=>$cash["txid"],
							"vout"=>$cash["vout"]
						);
					if ( $sumpay > $win + TRANSACTION_FEE ){
					break;
					}
			}
			$inadd = array(
				$playeraddress => $win,
				DELIVER_FUNDS => ($sumpay - $win)	
			);
			$play_log["outcome"] = 1;
			$play_log["sumout"] = $win;
		}//end else win
		
		//create output tx 
		$tl = $ftc->createrawtransaction($intx, $inadd);
		//sign tx
		$stl = $ftc->signrawtransaction($tl);
		
		//send
		if ( isset($stl["complete"]) && $stl["complete"] == 1 ){
				
			$out = $ftc->sendrawtransaction($stl["hex"]);
			
			$rawout = $ftc->decoderawtransaction($stl["hex"]);
			
			$play_log["txout"] = $rawout['txid'];
			
			//put in the log file for the succesfull plays (wins or looses) 
			$logname = PLAY_LOG_LOCATION."play_log".date("Y-m-d").".txt";
			$strout = @json_encode($play_log)."\n";
			@file_put_contents($logname, $strout, FILE_APPEND | LOCK_EX);
			
		}
}else{
	//the one who use this script should define this	
	//bet is smaller than minimum or bigger than max amount
	//capture the bet or return to the bet address or prform bet with the max amount and return the rest + outcome of the game
	
}
//end if ($bet >= $min_bet && $bet <= $max_bet )
