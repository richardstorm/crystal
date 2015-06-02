<?php
/**
 * A CDie player to play the CDieGame.
 *
 */
class CDiePlayer {
 
  /**
   * Properties
   *
   */
  private $numRolls;
  private $totalNumRolls;
  private $unsavedScore;
  private $savedScore;
  private $com;
 
 
  /**
   * Constructor
   *
   */
  public function __construct($com = false) {
    $this->numRolls = 0;
    $this->totalNumRolls = 0;
    $this->unsavedScore = 0;
    $this->savedScore = 0;
    $this->com = $com ? true : false;
  }
 
 
  /**
   * Add the score to the unsaved score.
   *
   */
  public function AddToUnsavedScore($score) {
    return $this->unsavedScore += $score;
  }
  
  /**
   * Increment numRolls.
   *
   */
  public function IncrementNumRolls() {
    $this->numRolls++;
    $this->totalNumRolls++;
  }
  
  /**
   * Get the score of this round.
   *
   * @return int as a sum of the round score.
   */
  public function GetRoundScore() {
    return $this->savedScore + $this->unsavedScore;
  }
  
  /**
   * Get the unsaved score of this round.
   *
   * @return int the unsaved round score.
   */
  public function GetUnsavedScore() {
    return $this->unsavedScore;
  }
  
  /**
   * Get the saved score of this round.
   *
   * @return int the saved round score.
   */
  public function GetSavedScore() {
    return $this->savedScore;
  }
  
  /**
   * Save the score of this round.
   *
   */
  public function SaveScore() {
    $this->savedScore = $this->GetRoundScore();
    $this->NewRound();
  }
  
  /**
   * Check if player is com.
   *
   * @return bool true if com or false if not.
   */
  public function IsCom() {
    return $this->com;
  }
  
  /**
   * Start a new round.
   *
   */
  public function NewRound() {
    $this->numRolls = 0;
    $this->unsavedScore = 0;
  }
  
  /**
   * Get the number of rolls in the round.
   *
   * @return int as a sum of the rounds rolls.
   */
  public function GetRoundNumRolls() {
    return $this->numRolls;
  }
  
  /**
   * Get the total number of rolls.
   *
   * @return int as a sum of the total rolls.
   */
  public function GetTotalNumRolls() {
    return $this->totalNumRolls;
  }
}