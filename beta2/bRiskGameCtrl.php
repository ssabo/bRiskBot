<?php
class bRiskGameCtrl{
    
    private $debug = 0;
    
    private $url = "www.boxcodingchallenge.com/v1/brisk/game";
    
    private $gameObj;
    private $gameState;
    private $playerState;
    private $mapLayout;
    
    #set the debug level
    public function setDebug($level = 0){
        $this->debug = $level;
    }
    
    #INTERNAL make an action against the API
    private function makeAction( $data = '' , $urlSuffix = '' ) {
        $curl = curl_init();
        $tmpUrl = $this->url.$urlSuffix;
	curl_setopt ($curl, CURLOPT_URL, $tmpUrl);
	curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
        if ($data != '') curl_setopt ($curl, CURLOPT_POSTFIELDS, $data);
        $jsonResponse = curl_exec ($curl);
        $response = json_decode($jsonResponse);
        switch ($this->debug) {
            case 1:
                print "Data: $data\n";
                print "URL: $tmpUrl\n";
                var_dump($response);
                print "\n";
                break;
            case 2:
                print "Data: $data\n";
                print "URL: $tmpUrl\n";
                print "$jsonResponse\n";
                print "\n";
                break;
        }
        return $response;
    }
    
    #INTERNAL get the turn status of game
    private function checkTurn() {
        $data ='';
        $suffix = "/" . $this->gameObj->game . "/player/" . $this->gameObj->player."?check_turn=true";
        $response = $this->makeAction($data,$suffix);
        return $response;
    }
    #INTERNAL update the map layout (does not usually change)
    private function updateMapLayout(){
        $data = '';
        $suffix = '/' . $this->gameObj->game . "?map=true";
        $this->mapLayout = $this->makeAction($data, $suffix);
    }
    #INTERNAL update the gameState (all territories)
    private function updateGameState(){
        $suffix = "/".$this->gameObj->game;
        $this->gameState = $this->makeAction('',$suffix);
    }
    #INTERNAL update the playerState (player's territories)
    private function updatePlayerState(){
        $suffix = "/" . $this->gameObj->game . "/player/" . $this->gameObj->player;
        $this->playerState = $this->makeAction('',$suffix);
    }
    #construct the game object and update game states
    public function __construct($teamName, $debugLevel = 0) {
        $this->setDebug($debugLevel);
        $data = json_encode(array( "join" => true,"team_name" => $teamName ));
        $this->gameObj = $this->makeAction($data);
        sleep(1);
        $this->updateGame();
        $this->updateMapLayout();
        print "Starting game ".$this->gameObj->game." as player ".$this->gameObj->player."\n";
    }
    
    #force the game states to update (game and player)
    public function updateGame(){
        $this->updateGameState();
        $this->updatePlayerState();
    }
    
    #get the game object (player info and token)
    public function getGameObj(){
        return $this->gameObj;
    }
    #get the map layout
    public function getMapLayout() {
        return $this->mapLayout;
    }
    #get the cached gameState (all territories)
    public function getGameState() {
        return $this->gameState;
    }
    
    #get the cached playerState (player's territories)
    public function getPlayerState() {
        return $this->playerState;
    }
    
    #check if it is your turn or if the game is over
    public function checkReady(){
        $turnState = $this->checkTurn();
        if ($turnState->winner != NULL){
            return "gameOver";
        }
        if ($turnState->current_turn == true ){
            return "yourTurn";
        }
        return "notYourTurn";
    }
    
    #get the winner of the game
    public function getWinner(){
        $turnState = $this->checkTurn();
        return $turnState->winner;
    }
    
    #end your turn
    public function endTurn(){
        $data = json_encode(array("token"=>$this->gameObj->token, "end_turn"=>true));
        $suffix = '/' . $this->gameObj->game . '/player/' . $this->gameObj->player;
        $response = $this->makeAction($data, $suffix);
        print "Ending turn\n";
        return $response;
    }
    
    #move a number of armies from one territory to another
    public function moveArmies($source, $destination, $numArmies){
	$data = json_encode(array( "token"=>$this->gameObj->token, "num_armies"=>$numArmies, "destination"=>$destination ));
	$suffix = '/' . $this->gameObj->game . '/player/' . $this->gameObj->player . '/territory/' . $source;
	$response = $this->makeAction($data, $suffix);
	print "Moving $numArmies armies from $source to $destination\n";
        $this->updateGame();
	return $response;
    }
    
    #attack an enemy territory from one of your own with a number of armies
    public function attackTerritory($source, $destination, $numArmies){
        $data = json_encode(array( "token"=>$this->gameObj->token, "num_armies"=>$numArmies, "attacker"=>$source ));
        $suffix = '/' . $this->gameObj->game . '/player/' . $this->gameObj->player . '/territory/' . $destination;
        $response = $this->makeAction($data, $suffix);
        print "Attacking territory $destination from $source with $numArmies armies\n";
        $this->updateGame();
        return $response;
    }
    
    #deploy a number of armoes to one of your territories
    public function deployArmies($id, $numArmies) {
        $data = json_encode(array("token"=>$this->gameObj->token, "num_armies"=>$numArmies));
        $suffix = "/" . $this->gameObj->game . "/player/" . $this->gameObj->player . "/territory/" . $id;
        $response = $this->makeAction($data,$suffix);
        print "Deployed $numArmies armies to loc $id\n";
        $this->updateGame();
        return $response;
    }
    
    #get the winning reward
    public function getReward(){
        $curl = curl_init();
        $tmpUrl = "www.boxcodingchallenge.com/v1/brisk/reward.php";
        $data = json_encode(array( "game"=>$this->gameObj->game, "player"=>$this->gameObj->player, "token"=>$this->gameObj->token ));
	curl_setopt ($curl, CURLOPT_URL, $tmpUrl);
	curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
        if ($data != '') curl_setopt ($curl, CURLOPT_POSTFIELDS, $data);
        $jsonResponse = curl_exec ($curl);
        $response = json_decode($jsonResponse);
        switch ($this->debug) {
            case 1:
                print "Data: $data\n";
                print "URL: $tmpUrl\n";
                var_dump($response);
                print "\n";
                break;
            case 2:
                print "Data: $data\n";
                print "URL: $tmpUrl\n";
                print "$jsonResponse\n";
                print "\n";
                break;
        }
        return $response;
    }
}

?>