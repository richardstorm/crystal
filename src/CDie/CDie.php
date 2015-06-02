<?php
/**
 * A CDie class to play around with a die.
 *
 */
class CDie {
 
  /**
   * Properties
   *
   */
  private $faces;
  protected $roll;
 
 
  /**
   * Constructor
   *
   * @param int $faces the number of faces to use
   */
  public function __construct($faces = 6) {
    $this->faces = $faces;
    $this->roll = 0;
  }
 
 
 
  /**
   * Roll the die
   *
   * @return int the roll.
   */
  public function Roll() {
    $this->roll = rand(1, $this->faces);

    return $this->roll;
  }
  
  /**
   * Get the roll as an image.
   *
   * @return string the image as html.
   */
  public function GetRollAsImage() {
    $html  = "<ul class='die'>";
    $html .= "<li class='die-{$this->GetRoll()}'></li>";
    $html .= "</ul>";
    return $html;
  }
  
  /**
   * Get the roll.
   *
   * @return int the roll.
   */
  public function GetRoll() {
    return $this->roll;
  }
}
