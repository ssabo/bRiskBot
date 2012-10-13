<?php
class BriskGame {
    
    public $debug = 0;


    private $url = "www.boxcodingchallenge.com/v1/brisk/game";
    private $teamName = "Sabo";
    
    private $gameID;
    private $player;
    private $token;
    
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
    
    public function getPlayerId() {
        return $this->player;
    }
    
    public function __construct() {
        $data = json_encode(array( "join" => true,"team_name" => $this->teamName ));
        $game = $this->makeAction($data);
        $this->gameID = $game->game;
        $this->player = $game->player;
        $this->token = $game->token;
        print "Starting game ".$this->gameID." as player ".$this->player."\n";
    }
    
    public function getGameState() {
        $suffix = "/".$this->gameID;
        $gameState = $this->makeAction('',$suffix);
        switch($this->debug){
            case 3:
                print "Get state:\n";
                var_dump($gameState);
        }
        return $gameState;
    }
    
    public function getPlayerState(){
        $suffix = "/" . $this->gameID . "/player/" . $this->player;
        $response = $this->makeAction('',$suffix);
        return $response;
    }

    public function checkReady(){
        $data ='';
        $suffix = "/" . $this->gameID . "/player/" . $this->player."?check_turn=true";
        $response = $this->makeAction($data,$suffix);
        if ($response->winner != NULL){
            return "gameOver";
        }
        if ($response->current_turn == true ){
            return "yourTurn";
        }
        return "notYourTurn";
    }
    
    public function deployArmies($id, $numArmies) {
        $data = json_encode(array("token"=>$this->token, "num_armies"=>$numArmies));
        $suffix = "/" . $this->gameID . "/player/" . $this->player . "/territory/" . $id;
        $response = $this->makeAction($data,$suffix);
        print "Deployed $numArmies armies to loc $id\n";
        return $response;
    }
    
    public function attackTerritory($fromId, $toId, $numArmies){
        $data = json_encode(array( "token"=>$this->token, "num_armies"=>$numArmies, "attacker"=>$fromId ));
        $suffix = '/' . $this->gameID . '/player/' . $this->player . '/territory/' . $toId;
        $response = $this->makeAction($data, $suffix);
        print "Attacking territory $toId from $fromId with $numArmies armies\n";
        return $response;
    }
    
    public function moveArmies($fromId, $toId, $numArmies){
	$data = json_encode(array( "token"=>$this->token, "num_armies"=>$numArmies, "destination"=>$toId ));
	$suffix = '/' . $this->gameID . '/player/' . $this->player . '/territory/' . $fromId;
	$response = $this->makeAction($data, $suffix);
	print "Moving $numArmies armies from $fromId to $toId\n";
	return $response;
    }
    
    public function endTurn(){
        $data = json_encode(array("token"=>$this->token, "end_turn"=>true));
        $suffix = '/' . $this->gameID . '/player/' . $this->player;
        $response = $this->makeAction($data, $suffix);
        print "Ending turn\n";
        return $response;
    }
    
    public function getMapLayout(){
        $data = '';
        $suffix = '/' . $this->gameID . "?map=true";
        $response = $this->makeAction($data, $suffix);
        return $response;
    }
    
    public function getWinner(){
	$data ='';
        $suffix = "/" . $this->gameID . "/player/" . $this->player."?check_turn=true";
        $response = $this->makeAction($data,$suffix);
	return $response->winner;
    }
    
}
?>