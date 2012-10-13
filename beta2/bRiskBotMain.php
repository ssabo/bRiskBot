#!/usr/bin/php
<?php
require_once("bRiskGameCtrl.php");
require_once("bRiskBotFunctions.php");

$gameInfo = array();

$options = getopt('g:b:n:');
if (isset($options['g'])){
    $gameInfo['game'] = $options['g'];
    print "Using game ID ".$gameInfo['game']."\n";
}

if (isset($options['b'])){
    $gameInfo['no_bot'] = true;
    print "Not joining a bot game\n";
}

if(isset($options['n'])){
    $teamName = $options['n'];
}
else{
    $teamName = "SSabo";
}

$game = new briskGameCtrl($teamName,$gameInfo, 0);

$gameOver = false;
do{
    
    $gameState = $game->checkReady();
    switch ($gameState){
        case "gameOver":
            $gameOver = true;
            break;
        case "notYourTurn":
            continue;
        case "yourTurn":
            #make sure the game state is current
            $game->updateGame();
            
            #print the turn number
            $turn = $game->getGameState()->num_turns_taken + 1;
            print "Starting turn $turn\n";
            
            #get reserve information
            $numReserves = $game->getPlayerState()->num_reserves;
            print "You have $numReserves armies to deploy\n";
            
            #get your territories and sort them by strongest first
            $tmpTerritories = $game->getPlayerState()->territories;
            $myTerritories = sortTerritories($tmpTerritories, "strongestFirst");
            
            foreach(array_reverse($myTerritories) as $territory) {
                #get this territory's id
                $territoryId = $territory->territory;
                
                #get the enemy adjacent territories sorted by weakest first
                $enemies = getAdjacentEnemies($territoryId);
                $numEnemies = count($enemies);
                if ($numEnemies == 0){
                    continue;
                }
                
                $move = $game->deployArmies($territoryId, $numReserves);
                break;
            }
            
            print "Starting attacks\n";
            
            $attackMade = false;
            do{
                $tmpTerritories = $game->getPlayerState()->territories;
                $myTerritories = sortTerritories($tmpTerritories, "strongestFirst");
                
                $attackMade = false;
                foreach($myTerritories as $territory){
                    
                    $territoryId = $territory->territory;
                    $numArmies = $territory->num_armies;
                    
                    if($numArmies < 3){
                        continue;
                    }
                    
                    $enemies = sortTerritories(getAdjacentEnemies($territoryId), "weakestFirst" );
                    
                    if(count($enemies) == 0){
                        continue;
                    }
                    
                    $weakestEnemy = $enemies[0];
                    $numEnemyArmies = getTerritoryInfo($weakestEnemy)->num_armies;
                    
                    if ( $numArmies - $numEnemyArmies < 3 ) {
                        continue;
                    }
                    
                    $attack = $game->attackTerritory($territoryId, $weakestEnemy, 2);
                    $attackMade = true;
                    
                    if($attack->defender_territory_captured){
                        
                        $numEnemies = count(getAdjacentEnemies($weakestEnemy));
                        if ($numEnemies == 0 ){
                            break;
                        }
                        
                        $survivors = $attack->attacker_territory_armies_left;
                        if($survivors < 3){
                            break;
                        }
                        
                        $move = $game->moveArmies($territoryId, $weakestEnemy, $survivors-2);
                    }
                    
                    
                    break;
                }
            } while ($attackMade) ;
            
            $game->endTurn();
    }
    
}while(!$gameOver);

$winner = $game->getWinner();
print "The winner is player $winner\n";
if($winner == $game->getGameObj()->player){
    $reward = $game->getReward();
    var_dump($reward);
}

?>