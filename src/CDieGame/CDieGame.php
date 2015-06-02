<?php
/**
 * A CDie game.
 *
 */
class CDieGame {
 
  /**
   * Properties
   *
   */
  private $die;
  private $player;
  private $currentPlayer;
 
 
  /**
   * Constructor
   *
   */
  public function __construct($players = 1, $com = false) {
    $this->die = new CDie();
    $this->com = $com ? true : false;

    for ($i = 1; $i <= $players; $i++) {
      $this->player[$i] = new CDiePlayer($i == $players && $com);
    }
    $this->currentPlayer = 1;
  }
  
  
  /**
   * Check if game is won.
   *
   * @return int player nr if won or 0 if not.
   */
  public function GameWin() {
    for ($i = 1; $i <= $this->GetNrOfPlayers(); $i++) {
      if ($this->player[$i]->GetRoundScore() >= 100) {
        return $i;
      }
    }
    return 0;
  }
  
  /**
   * Roll the die
   *
   */
  public function Roll() {
    $roll = $this->die->Roll();
    $this->CurrentPlayer()->IncrementNumRolls();
    
    if ($roll == 1) {
      $this->CurrentPlayer()->NewRound();
      if ($this->GetNrOfPlayers() > 1) {
        $this->NextPlayer();
      }
    }
    else {
      $this->CurrentPlayer()->AddToUnsavedScore($roll);
    }
  }
    
  /**
   * Get the roll.
   *
   * @return int the roll.
   */
  public function GetRoll() {
    return $this->die->GetRoll();
  }
  
  /**
   * Get the roll as an image.
   *
   * @return string the image as html.
   */
  public function GetRollAsImage() {
    return $this->die->GetRollAsImage();
  }
  
  /**
   * Get the current player.
   *
   * @return object current player.
   */
  public function CurrentPlayer() {
    return $this->player[$this->currentPlayer];
  }
  
  /**
   * Get the number of players.
   *
   * @return int the number of players.
   */
  public function GetNrOfPlayers() {
    return count($this->player);
  }
  
  /**
   * switch to next player if there is one.
   *
   */
  public function NextPlayer() {
    if (($nrOfPlayers = count($this->player)) > 1) {
      if (++$this->currentPlayer > $nrOfPlayers) $this->currentPlayer = 1;
    }
  }

  
  /**
   * Get the scores
   *
   * @return string as html.
   */
  public function GetScores() {
    $html = "";
    for ($i = 1; $i <= $this->GetNrOfPlayers(); $i++) {
      $scores = array(
        "Aktuell poäng" => $this->player[$i]->GetRoundScore() . " p",
        "Denna rond" => $this->player[$i]->GetUnsavedScore() . " p",
        "Sparade poäng" => $this->player[$i]->GetSavedScore() . " p",
        "Antal kast" => $this->player[$i]->GetRoundNumRolls() . " <span class=\"total\">(" . $this->player[$i]->GetTotalNumRolls() . ")</span>"
        );
      $html .= "<div id=\"scoreArea{$i}\"";
      if ($i != $this->currentPlayer) $html .= " class=\"otherPlayer\"";
      $html .= ">";
      $html .= "<div class=\"player\">Spelare {$i}";
      if ($this->player[$i]->isCom()) $html .= " COM";
      $html .= "</div>";
      foreach ($scores as $text => $score) {
        $html .= "<div class=\"score\"><span class=\"scoreText\">{$text}:</span>{$score}</div>";
      }
      $html .= "</div>";
    }
    return $html;
  }
}