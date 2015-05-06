<?php namespace App;

use App\Team;
use App\Player;
use App\Game;
use Illuminate\Database\Eloquent\Model;

class MatchEngine {

	//

	
	private $round = 0;
	private $matchmaking = false;

	public function __construct()
 	{
 	 $this->result = new \stdClass();
 	 $this->teamHome = new \stdClass();
 	 $this->teamAway = new \stdClass();

  	}	

  	private function setTeamStats(){
  		$this->setTeamEntry();
  		$this->teamHome->score = 0;
  		$this->teamAway->score = 0;
  	}

  	private function setTeamLeaderStat(){

 				 			
 	}

 	private function generateEnemyTeamAgainst($team){
 		$teamrank = Team::getTeamRank($team);
 		$this->teamAway->players = new \stdClass();
 		$this->teamAway->name = "matchmaking";
 		$i = 1;
 		while($i < 6){
 			$name = "player{$i}";
 			$this->teamAway->players->$name = new \stdClass();
 			$stats = [
 				'aim',
 				'spray',
 				'reactions',
 				'entry',
 				'position',
 				'gamesense',
 				'teamplay',
 				'pistol',
 				'rifle',
 				'sniper',
 			];
 			foreach($stats as $stat){
 				$this->teamAway->players->$name->name = $name;
 				$this->teamAway->players->$name->team_id = 0;
 				$this->teamAway->players->$name->id = 0;
 				$this->teamAway->players->$name->$stat = $this->generateRandomStat($teamrank);
 			}
 			
 			$i++;
 		}
 		return $this->teamAway;
 	}

 	private function generateRandomStat($rank){
 		switch ($rank) {
 			case 1:
 				return rand(5,15);
 				break;
  			case 2:
 				return rand(10,20);
 				break;
  			case 3:
 				return rand(15,25);
 				break;
  			case 4:
 				return rand(20,27);
 				break;
  			case 5:
 				return rand(23,30);
 				break;
  			case 6:
 				return rand(26,32);
 				break;
  			case 7:
 				return rand(30,35);
 				break;
  			case 8:
 				return rand(33,37);
 				break;
  			case 9:
 				return rand(36,42);
 				break;
  			case 10:
 				return rand(39,45);
 				break;
  			case 11:
 				return rand(42,47);
 				break;
  			case 12:
 				return rand(45,52);
 				break;
 			case 13:
 				return rand(47,55);
 				break;
  			case 14:
 				return rand(50,60);
 				break;
  			case 15:
 				return rand(60,70);
 				break;
  			case 16:
 				return rand(65,75);
 				break;
  			case 17:
 				return rand(70,80);
 				break;
  			case 18:
 				return rand(75,85);
 				break;
			case 19:
 				return rand(75,85);
 				break;
 		}

 		return $stat;
 	}
  	private function setTeamEntry(){
  		// ----------- Pistol

 		foreach($this->teamHome->players as $player){
 			
 			if($player->id == $this->teamHome->entry){
 				$player->entryfragger = true;
 			}			 			
 		}
	
		if(!$this->matchmaking){
	 		foreach($this->teamAway->players as $player){
	 			
	 			if($player->id == $this->teamAway->entry){
	 				$player->entryfragger = true;
	 			}			 			
	 		}
 		}

  	}


 	private function setTeamPistol(){

 		// ----------- Pistol
 		$this->teamHome->pistol = 0;
  		$this->teamAway->pistol = 0;
  	
 		foreach($this->teamHome->players as $player){
 			$this->teamHome->pistol = $this->teamHome->pistol + $player->pistol;
 		}
		
	}


	private function getItem($item){
		
		$items = array(
			'deagle' => [
				'name' => 'Desert Eagle',
				'price' => '700',
				'dmg' => '2'
			],
			'glock' => [
				'name' => 'Glock 18',
				'price' => '200',
				'dmg' => '3'
			],
			'usp' => [
				'name' => 'USP-S',
				'price' => '200',
				'dmg' => '3'
			],
			'armor' => [
				'name' => 'body armor',
				'price' => '650',
			],
		);
		$items = json_decode(json_encode($items), FALSE);
		return $items->$item;
	}

	public function getPlayers($team){
		$players = Player::where('team_id', '=', $team->id)->get();
		return $players;
	}

	private function getOffenseStat($player){
		$stat = 0;
		//offensive
		$stat += $player->aim;
		$stat += $player->spray;
		$stat += $player->reactions;
		$stat += $player->entry;

		//defensive
		$stat += $player->gamesense;

		$stat += $player->dmg;
		
		return $stat;
	}

	private function getDefenseStat($player){
		$stat = 0;
		//offensive
		$stat += $player->aim;
		$stat += $player->spray;
		$stat += $player->reactions;
		$stat += $player->entry;

		//defensive
		$stat += $player->gamesense;
		$stat += $player->position;
		$stat += $player->teamplay;

		$stat += $player->dmg;
		$stat = $stat / 3;
		return $stat;
	}

	public function startMatch(){

		// Set Player Properties
		foreach($this->teamHome->players as $player){
				$player->side = "Counter-Terrorist";
				$player->money = 800;
				$player->kills = 0;
				$player->deaths = 0;
				$player->hp = 100;
				$player->dmg = 50;
				$player->armor = false;			
				$player->headarmor = false;
		}
		foreach($this->teamAway->players as $player){
				$player->side = "Terrorist";
				$player->money = 800;
				$player->kills = 0;
				$player->deaths = 0;
				$player->hp = 100;
				$player->dmg = 50;
				$player->armor = false;			
				$player->headarmor = false;
		}

		//Set Team Properties
		$this->setTeamPistol();

	}

	public function buy($player, $item){
		$item = $this->getItem($item);
		$player->money = $player->money - $item->price;
		echo "<p small>{$player->name} bought {$item->name}</p>";
	}

	public function freezetime(){
		// Set Player Properties
		foreach($this->teamHome->players as $player){

				$player->money = 800;
				$player->hp = 100;

		}
		foreach($this->teamAway->players as $player){

				$player->money = 800;

				$player->hp = 100;
		}
		/*
		//teamHome buy
		if($this->round == 1 || $this->round == 16){
			foreach($this->teamHome->players as $player){
				$this->buy($player, 'armor');
			}
			foreach($this->teamAway->players as $player){
				$this->buy($player, 'armor');
			}
		}
		*/

	}

	private function randomAlivePlayer($team = null){
		if(!$team){
			if(rand(1,2) == 1){
				$team = $this->teamHome;
			} else {
				$team = $this->teamAway;
			}
		}
		$alive = 0;
		foreach($team->players as $player){
				if($player->hp > 0){
					$alive++;
				}
			}

/*   	--- IF ALL PLAYERS ARE DEAD ----

		if($alive == 0){
			if($team == $this->teamHome){
				$this->teamAway->score = $this->teamAway->score + 1;
			}
			if($team == $this->teamAway){
				$this->teamHome->score = $this->teamHome->score + 1;
			}	

			return false;	
		}
*/		
		while(!isset($randomPlayer)){
			$rand = rand(1,5);
			$i = 1;
			foreach($team->players as $player){
				if($rand == $i && $player->hp > 0){
					$randomPlayer = $player;
				}
			$i++;
			}
		}
		return $randomPlayer;
	}

	private function kill($p1, $p2, $dmg = 0){
		$p1->kills += 1;
		$p2->deaths += 1;
		$p2->hp = 0;
	}

	public function encounter($p1, $p2){


		$p1->offstat = $this->getOffenseStat($p1);
		$p2->defstat = $this->getDefenseStat($p2);

		//add some luck
		$p1->offstat += rand(1,300);
		$p2->defstat += rand(1,400);

		// - INSTAKILL PERCENT -
		$p1->hs = $p1->aim/5;
		$p2->hs = $p2->aim/5;
		$first = $p1->entry - $p2->position;
	
		if($p1->entry > $p2->position){

			if(rand(1,100) < ($p1->hs) ){
				$this->kill($p1, $p2);
				return true;
			}
			
		} else {

			if(rand(1,100) < ($p1->hs) ){
				$this->kill($p2, $p1);
				return true;
			}
			

		}

		// ---- END INSTAKILL

		$diff =	$p1->offstat / $p2->defstat;
		$diff = round(($diff), 2);
		$diff = $diff*100;

		if($diff < 101){
			//If diff is more than HP remaining
			if($diff > $p2->hp){
	
			 $this->kill($p1, $p2);
			}
			$p2->hp = $p2->hp - $diff;
			$this->kill($p2, $p1);

		}
		if($diff > 100){
			$diff = $diff - 100;
			if($diff > $p1->hp){
		
			 $this->kill($p2, $p1);
			}
			$p1->hp = $p1->hp - $diff;
			$this->kill($p1, $p2);
		}

	}

	private function checkAlive(){
		
		$alive = 0;
		foreach($this->teamAway->players as $player){
				if($player->hp > 0){
					$alive++;
				}
			}
		if($alive == 0){
			$this->teamHome->score += 1;
			return false;
		}

		$alive = 0;
		foreach($this->teamHome->players as $player){
				if($player->hp > 0){
					$alive++;
				}
			}
		if($alive == 0){
			$this->teamAway->score += 1;
			return false;
		}

		return "bror";
	}

	public function nextRound(){
		$this->round += 1;
		$this->freezetime();

		if($this->round <= 15){
		if(rand(1,10) > 6 && isset($this->teamAway->entry)){
			foreach ($this->teamAway->players as $player){
				if($player->entryfragger && $player->hp > 0){
					$entry = $player;
				} else {
					$entry = $this->RandomAlivePlayer($this->teamAway);
				}
			}
		}else{
			$entry = $this->RandomAlivePlayer($this->teamAway);
		}

		$this->encounter($entry, $this->RandomAlivePlayer($this->teamHome));
		$roundContinue = 1;
		while($roundContinue == 1){
			$this->encounter($this->RandomAlivePlayer($this->teamAway), $this->RandomAlivePlayer($this->teamHome));
			if($this->checkAlive() == false){
				$roundContinue = 0;
			}
		}
		}

		if($this->round > 15){
			if(rand(1,10) > 6 && isset($this->teamHome->entry)){
			foreach ($this->teamHome->players as $player){
				if($player->entryfragger && $player->hp > 0){
					$entry = $player;
				} else {
					$entry = $this->RandomAlivePlayer($this->teamHome);
				}
			}
		}else{
			$entry = $this->RandomAlivePlayer($this->teamHome);
		}

		$this->encounter($entry, $this->RandomAlivePlayer($this->teamAway));
		$roundContinue = 1;
		while($roundContinue == 1){
			$this->encounter($this->RandomAlivePlayer($this->teamHome), $this->RandomAlivePlayer($this->teamAway));
			if($this->checkAlive() == false){
				$roundContinue = 0;
			}
		}
		}

		return true;

	}

	public function getScoreboard($team){
		echo "<table border=1px width=400>";
		echo "<tr><th colspan='4'>{$team->name} - {$team->score}</th></tr>";
		echo "<tr><td></td><td>K - D</td><td>Health</td><td>$</td>";
		foreach($team->players as $player){
		echo "<tr><td>{$player->name}</td><td>{$player->kills} - {$player->deaths}</td><td>{$player->hp}</td><td>{$player->money}</td>";	
		}
		echo "</table>";
	}


	public function run($teamHome, $teamAway = null){
		if(Team::busy($teamHome)){
			return redirect()->route('games.wait');
		}

		if(!isset($teamAway)){
			$this->matchmaking = true;
			$teamAway = $this->generateEnemyTeamAgainst($teamHome);	
			//dd($this->teamAway);	
		}
		else {
			$this->teamAway = $teamAway;
			$this->teamAway->players = $this->getPlayers($teamAway);			
		}

		$this->teamHome = $teamHome;
		$this->teamHome->players = $this->getPlayers($teamHome);

		$this->setTeamStats();
		$this->startMatch();

		$matchEnd = 0;
		while($matchEnd == 0){
			$this->nextRound();
			if($this->teamHome->score == 16){
				$matchEnd = 1;
			}
			if($this->teamAway->score == 16){
				$matchEnd = 1;
			}
			if($this->round == 30){
				$matchEnd = 1;
			}
		}


		$this->getScoreboard($this->teamHome);
		$this->getScoreboard($this->teamAway);		

		$this->result->teamHome = $this->teamHome;
		$this->result->teamAway = $this->teamAway;
		
		$result = $this->result;

		$game = new Game();
			$game->team_home_id = $this->teamHome->id;
			if($this->matchmaking){
				$game->team_away_id = 0;
				$game->matchmaking = 1;
			} else {
				$game->team_away_id = $this->teamAway->id;
				$game->matchmaking = 0;
			}
			$game->score_home = $this->teamHome->score;
			$game->score_away = $this->teamAway->score;
			$game->completed = date("Y-m-d H:i:s", strtotime("+60 minute"));
		$game->save();

		if($this->matchmaking){
			foreach($this->teamHome->players as $player){
				$gp = new GamePlayer();
					$gp->game_id = $game->id;
					$gp->player_id = $player->id;
					$gp->team_id = $player->team_id;
					$gp->kills = $player->kills;
					$gp->deaths = $player->deaths;
					$gp->win = 0;
					$gp->draw = 0;
					$gp->loss = 0;
				$gp->save();
			}
			foreach($this->teamAway->players as $player){
				$gp = new GamePlayer();
					$gp->game_id = $game->id;
					$gp->player_id = $player->id;
					$gp->team_id = $player->team_id;
					$gp->kills = $player->kills;
					$gp->deaths = $player->deaths;
					$gp->win = 0;
					$gp->draw = 0;
					$gp->loss = 0;
				$gp->save();
			}
		}
		return redirect()->route('games.wait');
	}



}
