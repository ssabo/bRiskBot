#!/usr/bin/php
<?php
#import game library
require_once("briskGame.php");
#import AI functions
require_once("briskActions.php");

#create connection to game
$game = new BriskGame();
#pause for 1 second while game initializes
sleep(1);

#store my ID
$myId = $game->getPlayerId();

#store the map layout
$mapLayout = $game->getMapLayout();

#wait for turn
while (true){
    switch($game->checkReady()){
        case "notYourTurn":
            continue;
        case "gameOver":
            break 2;
        case "yourTurn":
            
            
            $gameState = $game->getGameState();
            $playerState = $game->getPlayerState();
            $myTerritories = sortTerritories(getMyTerritoryIds());
            $numTerritories = count($myTerritories);
            updateGame();
            
            
            
            $turn = $gameState->num_turns_taken;
            print "Starting your turn ($turn)\n";
            
            
            $numReserves = $playerState->num_reserves;
            print "You have $numReserves reserves\n";
            
            foreach(array_reverse($myTerritories) as $territory){
                $numEnemies = count(getAdjacentEnemies($territory));
                if ( $numEnemies == 0 ){
                    continue;
                }
                $game->deployArmies($territory, $numReserves);
                print "here\n";
                //updateGame();
                break;
            }
            
            
            //$weakestTerritory = $myTerritories[$numTerritories-1];
            
            //$game->deployArmies($weakestTerritory, $numReserves);
            //updateGame();
            
            
            $attackMade = false;
            do{
                //print "Checking for (more) attacks\n";
                $attackMade = false;
                $myTerritories = sortTerritories(getMyTerritoryIds());
                print "here1\n";
                foreach($myTerritories as $territory){
                    
                    if( $game->checkReady() == "gameOver"){
                        break 3;
                    }
                    
                    $territoryInfo = getTerritoryInfo($territory);
                    $numArmies = $territoryInfo->num_armies;
                    
                    if($numArmies < 4){
                        continue;
                    }
                    
                    $enemies = sortTerritories(getAdjacentEnemies($territory), "weakestFirst");
                    if (count($enemies) == 0){
                        continue;
                    }
                    
                    $weakestEnemy = getTerritoryInfo($enemies[0]);
                    if($numArmies - $weakestEnemy->num_armies < 3){
                        continue;
                    }
                    
                    $attack = $game->attackTerritory($territory, $enemies[0],3);
                    updateGame();
                    $attackMade = true;
                    
                    if ($attack->defender_territory_captured){
                        $numRemainingArmies = $attack->attacker_territory_armies_left;
                        if ($numRemainingArmies > 2){
                            $game->moveArmies($territory, $enemies[0],$numRemainingArmies-2);
                            updateGame();
                        }
                    }
                }
                
            }while($attackMade);
            
            
            $game->endTurn();
            break;
        default:
            continue;
    }
}
$winner = $game->getWinner();
print "Game over, winner: player $winner\n";


?>