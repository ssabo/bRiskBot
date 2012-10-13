#!/usr/bin/php
<?php

$gameID = '';
$player = '';
$token = '';
$url = "www.boxcodingchallenge.com/v1/brisk/game";

function MakeCall ($method)
{
	global $url, $gameID, $player;
	$curl = curl_init();
	curl_setopt ($curl, CURLOPT_URL, $url);
	curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
	switch ($method) {
		case "startGame":
			print "Start Game!\n";
			$data = json_encode(array( "join" => true,"team_name" => "BestInTheWorld" ));
			curl_setopt ($curl, CURLOPT_POSTFIELDS, $data);
			break;
		case "getState":
			print "Get game state\n";
			$tmpURL = "$url/$gameID";
			curl_setopt($curl, CURLOPT_URL, $tmpURL);
			break;
		case "getTurnState":
			$tmpURL = "$url/$gameID/player/$player?check_turn=true";
			curl_setopt($curl, CURLOPT_URL,$tmpURL);
			break;
	}
	$response = curl_exec ($curl);
#	print $response;
	return json_decode($response);
}
$game = MakeCall("startGame");
#var_dump ($game);
$gameID = $game->game;
$player = $game->player;
$token = $game->token;


print "ID: $gameID
Player: $player
\n";

#$state = MakeCall("getState");
#var_dump($state);

$turn = MakeCall("getTurnState");
var_dump($turn);



?>
