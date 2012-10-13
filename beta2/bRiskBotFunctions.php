<?php

function compareTerritories($t1, $t2){
    $a = getTerritoryInfo($t1)->num_armies;
    $b = getTerritoryInfo($t2)->num_armies;
    return ($a < $b) ? -1 : 1;
}

function getTerritoryInfo($territoryId)
{
    global $game;
    return $game->getGameState()->territories[$territoryId-1];
}

function sortTerritories($territories, $order = "strongestFirst"){    
    
    $sortedTerritories = $territories;
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
    global $game;
    $mapLayout = $game->getMapLayout();
    $playerId = $game->getGameObj()->player;
    
    $enemyTerritories = array();
    $adjacentTerritories = $mapLayout->territories[$territoryId-1]->adjacent_territories;
    foreach($adjacentTerritories as $territory){
        $territoryInfo = getTerritoryInfo($territory);
        if($territoryInfo->player != $playerId){
            array_push($enemyTerritories, $territory);
        }
    }
    return $enemyTerritories;
}

?>