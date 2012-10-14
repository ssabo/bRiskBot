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
            
            #search for your weakest territory with enemies
            do {
                
                $tmpTerritories = $game->getPlayerState()->territories;
                $myTerritories = sortTerritories($tmpTerritories, "weakestFirst");
                //var_dump($myTerritories);
                
                foreach($myTerritories as $territory){
                    $territoryId = $territory->territory;
                    
                    $enemies = getAdjacentEnemies($territoryId);
                    
                    $numEnemies = count($enemies);
                    if($numEnemies == 0){
                        continue;
                    }
                    
                    //$numDeploy = ceil(sqrt($game->getPlayerState()->num_reserves));
                    $numDeploy = ceil($game->getPlayerState()->num_reserves * .9);
                    
                    $move = $game->deployArmies($territoryId,$numDeploy);
                    sleep(1);
                    break;
                }
            }while ($game->getPlayerState()->num_reserves > 0);
            
            
            print "Starting attacks\n";
            
            #initialize attack flag
            $attackMade = false;
            
            #search for attacks and execute them until none are left
            do{
                #update your list of territories
                $tmpTerritories = $game->getPlayerState()->territories;
                $myTerritories = sortTerritories($tmpTerritories, "strongestFirst");
                
                #reset the attack flag
                $attackMade = false;
                
                #iterate through territories to see if they can attack
                foreach($myTerritories as $territory){
                    
                    #get this territory's info
                    $territoryId = $territory->territory;
                    $numArmies = $territory->num_armies;
                    
                    #skip this territory if it is too week to attack
                    if($numArmies <= 2){
                        continue;
                    }
                    
                    $numAttackers = ($numArmies > 3) ? 3 : 2;
                    
                    #get the list of enemys adjacent
                    $enemies = sortTerritories(getAdjacentEnemies($territoryId), "weakestFirst" );
                    
                    #skip this territory if it has no enemies
                    if(count($enemies) == 0){
                        continue;
                    }
                    
                    
                    #get the weakest adjacent enemy
                    
                    
                    
                    $weakestEnemy = $enemies[0];
                    
                    $localEnemies = getTerroriesInSameContinent($enemies,$territoryId);
                    if(count($localEnemies) != 0){
                        $weakestEnemy = $localEnemies[0];
                    }
                    $numEnemyArmies = getTerritoryInfo($weakestEnemy)->num_armies;
                    
                    #if the enemy is too much stronger than this territory dont attack
                    if ( $numArmies - $numEnemyArmies < 0) {
                        continue;
                    }
                    
                    #make an attack against the weakest enemy territory
                    $attack = $game->attackTerritory($territoryId, $weakestEnemy, $numAttackers);
                    $attackMade = true;
                    
                    #check if you won the attack
                    if($attack->defender_territory_captured){
                        
                        #if the attacking territory still has enemies dont move your amries
                        $numEnemies = count(getAdjacentEnemies($weakestEnemy));
                        if ($numEnemies == 0 ){
                            break;
                        }
                        
                        #if the attacking territory has less than 3 armies dont move your armies
                        $survivors = $attack->attacker_territory_armies_left;
                        if($survivors < 2){
                            break;
                        }
                        
                        #move all but 2 armies to the captured territory
                        $move = $game->moveArmies($territoryId, $weakestEnemy, $survivors-1);
                    }
                    #break out to re-evaluate map situation
                    break;
                }
            } while ($attackMade) ;
            
            #end the turn after all attacks and deployments are made
            $game->endTurn();
    }
    
}while(!$gameOver);

#get the game winner
$winner = $game->getWinner();
print "The winner is player $winner\n";

#if you are the winner print out the code submission email
if($winner == $game->getGameObj()->player){
    $reward = $game->getReward();
    var_dump($reward);
}

?>