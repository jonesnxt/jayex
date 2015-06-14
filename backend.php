<?php
// what needs to be done here...
// load off all assets with only the necesarry fields
// store asset names and balances in json files to be served by a different file.
function req($params)
{
	return json_decode(file_get_contents("http://127.0.0.1:7876/nxt?".$params));
}

function getNxtTime()
{
	return floor(time()) - 1385294400;
}
$starttime = time();
$assetdata = req("requestType=getAllAssets&lastIndex=600");
var_dump($assetdata->assets);
echo count($assetdata->assets);
$assets = $assetdata;
$unordered = array();
$volumes = array();
$counter = 0;
foreach($assets->assets as $asset)
{

	$id = $asset->asset;
	$storeasset = new STDClass();
	$storeasset->assetData = $asset;
	$newer = new STDClass();
	$newer->name = $asset->name;
	$newer->trades = $asset->numberOfTrades;
	$newer->asset = $asset->asset;

	$now = getNxtTime();
	$storeasset->timestamp = $now;
	$seven = $now - 86400*7;

	$untrades = req("requestType=getTrades&includeAssetInfo=false&asset=".$id);
	$volume = 0;
	foreach($untrades->trades as $trade)
	{
		// lets get rid of a few things
		if($id == "12071612744977229797" && $trade->priceNQT == "272000001")
		{
			$trade->priceNQT = "2720000";
		}
		unset($trade->seller);
		unset($trade->bidOrder);
		unset($trade->buyer);
		unset($trade->askOrder);
		unset($trade->block);
		unset($trade->asset);
		unset($trade->askOrderHeight);
		unset($trade->bidOrderHeight);
		unset($trade->height);

		if($trade->timestamp > $seven)
		{
			$volume += ($trade->quantityQNT*$trade->priceNQT/pow(10,8));
		}
	}
	$volume = intval($volume);
	echo $asset->name . " " . $volume . "\n";
	$newer->volume = $volume;
	array_push($volumes, $volume);
	$storeasset->trades = $untrades->trades;

	/*$sells = req("requestType=getAskOrders&asset=".$id);
	foreach($sells->askOrders as $order)
	{
		unset($order->account);
		unset($order->asset);
		unset($order->type);
		unset($order->order);
		unset($order->height);
	}
	$storeasset->askOrders = $sells->askOrders;

	$buys = req("requestType=getBidOrders&asset=".$id);
	foreach($buys->bidOrders as $order)
	{
		unset($order->account);
		unset($order->asset);
		unset($order->type);
		unset($order->order);
		unset($order->height);
	}
	$storeasset->bidOrders = $buys->bidOrders;*/
	array_push($unordered, $newer);

	file_put_contents("/var/www/html/jayex/data/".$asset->asset.".json", json_encode($storeasset));
	unset($trade);
	unset($storeasset);
}

sort($volumes);
$volumes = array_reverse($volumes);
//var_dump($volumes);
$ordered = array();
for($i=0;$i<count($unordered);$i++)
{
	for($j=0;$j<count($unordered);$j++)
	{
		if($volumes[$i] == $unordered[$j]->volume)
		{
			array_push($ordered, $unordered[$j]);
			array_splice($unordered, $j, 1);

			break;
		}
	}
}

file_put_contents("/var/www/html/jayex/data/assets.json", json_encode($ordered));
echo "completed in " . (time() - $starttime) . " seconds\n\n";
?>