#!/usr/bin/php
<?php

require_once ("game.php");

function findMyWeakestTerritory(){
    global $game;
    $player = $game->getPlayerId();
    $weakestLocId = '';
    $weakestLocArmies = 200;
    
    $myLocations = $game->getPlayerState()->territories;

    foreach($myLocations as $location) {
        if ($location->num_armies < $weakestLocArmies) {
            $weakestLocArmies = $location->num_armies;
            $weakestLocId = $location->territory;
        }
    }
    return $weakestLocId;
}

function findMyStrongestTerritory() {
   global $game;

    global $game;
    $player = $game->getPlayerId();
    $strongestLocId = '';
    $strongestLocArmies = 0;
    
    $myLocations = $game->getPlayerState()->territories;

    foreach($myLocations as $location) {
        if ($location->num_armies > $strongestLocArmies) {
            $strongestLocArmies = $location->num_armies;
            $strongestLocId = $location->territory;
        }
    }
    return $strongestLocId;
}

function getTarget($territory) {
    global $game;
    $player = $game->getPlayerId();
    $layout = $game->getMapLayout()->territories;
    $state = $game->getGameState()->territories;
    
    $weakestTargetArmies = 200;
    $weakestTargetId = '';
    
    $targets = $layout[$territory-1]->adjacent_territories;
    foreach($targets as $id){
        $numArmies = $state[$id-1]->num_armies;
        $owner = $state[$id-1]->player;
        if($owner != $player && $numArmies < $weakestTargetArmies){
            $weakestTargetId = $id;
            $weakestTargetArmies = $numArmies;
        }
    }
    return $weakestTargetId;
}


//setup game
$game = new Game();
sleep(1);

//play game
while ( true ){
    $status = $game->checkReady();
    switch($status){
        case "yourTurn":
            $myState = $game->getPlayerState();
            
            $numReserves = $myState->num_reserves;
            
            $deployLoc = findMyWeakestTerritory();
            
            $game->deployArmies($deployLoc,$numReserves);
            $attacker = findMyStrongestTerritory();
            
            $target = getTarget($attacker);
            $game->attackTerritory($attacker, $target, 2);
            $game->endTurn();
            break;
        case "gameOver":
            break 2;
        default:
            break;
    }
}
print "\n";
?>