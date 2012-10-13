<?php

function compareTerritories($t1, $t2){
    $a = getTerritoryInfo($t1)->num_armies;
    $b = getTerritoryInfo($t2)->num_armies;
    return ($a < $b) ? -1 : 1;
}

function getTerritoryInfo($territoryId)
{
    global $gameState;
    return $gameState->territories[$territoryId-1];
}


function getMyTerritoryIds(){
    global $myId, $playerState;
    
    $territories = $playerState->territories;
    
    $territoryIds = array();
    
    foreach($territories as $territory){
        array_push($territoryIds,$territory->territory);
    }
    return $territoryIds;
}

function sortTerritories($territoryIds, $order = "strongestFirst"){
    global $gameState;
    $sortedTerritories = $territoryIds;
    usort($sortedTerritories, "compareTerritories");
    switch ( $order ) {
        case "strongestFirst":
            return array_reverse($sortedTerritories);
            break;
        
        case "weakestFirst":
            return $sortedTerritories;
            break;
    }
}

function getAdjacentEnemies($territoryId){
    global $mapLayout, $myId;
    $enemyTerritories = array();
    $adjacentTerritories = $mapLayout->territories[$territoryId-1]->adjacent_territories;
    foreach($adjacentTerritories as $territory){
        $territoryInfo = getTerritoryInfo($territory);
        if($territoryInfo->player != $myId){
            array_push($enemyTerritories, $territory);
        }
    }
    return $enemyTerritories;
}

function updateGame(){
    global $game, $gameState, $playerState, $myTerritories, $numTerritories, $mapLayout;
    $gameState = $game->getGameState();
    $playerState = $game->getPlayerState();
    $myTerritories = sortTerritories(getMyTerritoryIds());
    $numTerritories = count($myTerritories);
    $mapLayout = $game->getMapLayout();
}

?>