<?php
// need to do here
// offload and partial download to keep realtime
// hard core realtime data parsing, how cool is that....???

// ugh, this is hard..
$real = false;

function req($params)
{
	if(isset($real) && $real == true) return json_decode(file_get_contents("http://127.0.0.1:7876/nxt?".$params));
	else return json_decode(file_get_contents("http://jnxt.org:7876/nxt?".$params));
}

function getNxtTime()
{
	return floor(time()) - 1385294400;
}

if(!isset($_GET['asset']))
{
	if($real) echo file_get_contents("./data/assets.json");
	else echo file_get_contents("http://jnxt.org/jayex/data/assets.json");
	die;
}


if($real) $asset = json_decode(file_get_contents("./data/".$_GET['asset'].".json"));
else $asset = json_decode(file_get_contents("http://jnxt.org/jayex/data/".$_GET['asset'].".json"));

$indexing = 0;
$cont = true;
$subtrades = array();
while($cont)
{
	$newtrades = req("requestType=getTrades&asset=".$_GET['asset']."&firstIndex=".$indexing."&lastIndex=".($indexing+9))->trades;
	foreach($newtrades as $newtrade)
	{
		if(intval($newtrade->timestamp) != intval($asset->trades[0]->timestamp))
		{
			array_push($subtrades, $newtrade);
		}
		else
		{
			$cont = false;
			break;
		}
	}
	$indexing += 10;
	if($indexing == 100) 
	{
		break;
	}
}
$asset->trades = array_merge($subtrades, $asset->trades);

$bids = req("requestType=getBidOrders&asset=".$_GET['asset']."&lastIndex=49")->bidOrders;
$asset->bidOrders = $bids;
$asks = req("requestType=getAskOrders&asset=".$_GET['asset']."&lastIndex=49")->askOrders;
$asset->askOrders = $asks;

echo json_encode($asset);

?>