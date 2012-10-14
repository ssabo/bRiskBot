<?php

function compareTerritories($t1, $t2){
    $t1n = $t1->num_armies;
    $t2n = $t2->num_armies;
    return ($t1n < $t2n) ? -1: 1;
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

function getTerroriesInSameContinent($territories, $ref){
    global $game;
    $map = $game->getMapLayout()->continents;
    
    $continent = '';
    
    foreach($map as $tmpContinent){
        if (in_array($ref, $tmpContinent->territories)){
            $continent = $tmpContinent;
            break;
        }
    }
    
    $territoriesInContinent = array();
    
    foreach($territories as $territoryId){
        if(in_array($territoryId,$continent->territories)){
            array_push($territoriesInContinent, $territoryId);
        }
    }
    
    return $territoriesInContinent;
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