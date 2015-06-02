<?php
/**
 * A CDie game round.
 *
 */
class CDieRound {
 
  /**
   * Properties
   *
   */
  private $game = null;
  private $gameMode = 0b111;

  
  /**
   * Set the game mode
   *
   * @param int (bit) gamemode (1 player, vs, vscom)
   *
   */ 
  public function setGameMode($gameMode = 0b111) {
    $this->gameMode = $gameMode;
  }
  
  /**
   * Handles the session.
   *
   */
  public function SessionHandling() {
    // destroy session if 'destroy button' is clicked or if new game
    if (isset($_GET['destroy'])) {
      unset($_SESSION['dicegame']);
    }
    else if (isset($_SESSION['dicegame'])) {
      $this->game = $_SESSION['dicegame'];
    }
    else if (isset($_GET['players'])) {
      switch($_GET['players']) {
        case "vs":
          $this->game = new CDieGame(2);
          break;
        case "vscom":
          $this->game = new CDieGame(2, true);
          break;
        default:
          $this->game = new CDieGame(1);
      }
      $_SESSION['dicegame'] = $this->game;
    }
  }
  
  
  
  public function playerWin($player = 1) {
    return $this->game ? ($this->game->GameWin() == $player) : false;
  }
  
  /**
   * Get the html output.
   *
   * @return string as html.
   */
  public function HtmlOutput() {
    
    $this->SessionHandling();

    $roll = isset($_GET['roll']) ? true : false;
    $save = isset($_GET['save']) ? true : false;

    $html = "";

    // roll the die
    if (($roll || $save) && isset($this->game) && !$this->game->GameWin()) {
      if ($save) {
        $this->game->CurrentPlayer()->SaveScore();
        $this->game->NextPlayer();
      }
      else {
        $this->game->Roll();
      }
    }

    if (isset($this->game)) {
      if (($player = $this->game->GameWin()) != 0) {
        $html .= "<div id=\"gameWin\">Spelare {$player} vann!</div>";
      }
      
      // Print out the results
      $html .= "<div class=\"diceRes\">";
      if ($this->game->GetRoll()) {
        $html .= $this->game->GetRollAsImage() . "<span class=\"text\"> = " . $this->game->GetRoll() . "</span>";
      }
      $html .= "</div>";

      if ($this->game->getNrOfPlayers()) {
        $html .= $this->game->GetScores();
      }
    }

    // game menu
    $nav = "<div class=\"gameMenu\">";
    if (!isset($_SESSION['dicegame'])) {
      if ($this->gameMode & 0b100) {
        $nav .= "<button type=\"button\" onClick=\"window.location.href='?players=1';\">1 spelare</button>";
      }
      if ($this->gameMode & 0b010) {
        $nav .= "<button type=\"button\" onClick=\"window.location.href='?players=vs';\">2 spelare</button>";
      }
      if ($this->gameMode & 0b001) {
        $nav .= "<button type=\"button\" onClick=\"window.location.href='?players=vscom';\">vs COM</button>";
      }
    }
    else {
      $nav .= $this->GetRollButton();
      $nav .= $this->GetSaveButton($save);
      $nav .= $this->GetDestroyButton();
    }
    $nav .= "</div>";
    
    if (isset($this->game) && $this->game->CurrentPlayer()->IsCom() && !$this->game->GameWin()) {
      $randomMax = 5;
      $randomVal = rand(1, $randomMax);
      $state = (($randomVal == $randomMax) && ($this->game->CurrentPlayer()->GetRoundNumRolls() > 0)) ? 'save' : 'roll';
      header("refresh:1;url=?{$state}");
    }
    
    return <<<EOD
<h1>Kasta tärning</h1>
<h2>Spela tärningsspelet 100</h2>
<p>Spelet går ut på att samla ihop poäng tills man kommer till 100. Man kastar en tärning tills man väljer att stanna och spara poängen
eller tills det dyker upp en 1:a och man förlorar alla poäng som samlats in i rundan.</p>
<br>
<div id="diceGamePlayArea">
{$nav}{$html}
</div>

EOD;
  }
  
  /**
   * Get the roll button.
   *
   * @return string as html.
   */
  public function GetRollButton() {
    $html = "<button type=\"button\" onClick=\"window.location.href='?roll';\"";
    if ($this->game->GameWin() || $this->game->CurrentPlayer()->IsCom()) $html .= " disabled";
    $html .= ">Kasta tärningen</button> ";
    return $html;
  }
  
  /**
   * Get the save button.
   *
   * @return string as html.
   */
  public function GetSaveButton($save = false) {
    $html = "<button type=\"button\" onClick=\"window.location.href='?save';\"";
    if ($this->game->GameWin() || $save || !$this->game->CurrentPlayer()->GetRoundNumRolls() || $this->game->CurrentPlayer()->IsCom()) $html .= " disabled";
    $html .= ">";
    $html .= $save ? "Poängen sparad" : "Spara poängen";
    $html .= "</button> ";
    return $html;
  }
  
  /**
   * Get the destroy button.
   *
   * @return string as html.
   */
  public function GetDestroyButton() {
    $html = "<button type=\"button\" onClick=\"window.location.href='?destroy';\">Förstör sessionen</button>";
    return $html;
  }
}