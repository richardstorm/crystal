<?php
/**
 * A month calendar with photo and navigation.
 *
 */
class CCalendar {
 
  /**
   * Properties
   *
   */
  private $dt;
  private $calYear;
  private $calMonth;
  private $small;


  
  /**
   * Constructor
   *
   */
  public function __construct() {
    $this->dt = new CDateTime(null);
    $this->InitCal();
  }
  
  
  /**
   * Get the html output.
   *
   * @param bool $small if small size of calendar.
   * @return string as html.
   */
  public function HtmlOutput($small = false) {
    $this->small = $small;
    $html = $this->small ? "<div class='smallCalendar'>" : "<div class='calendar'>";
    $html .= $this->GetPhoto();
    $html .= $this->GetMonthTitle();
    $html .= $this->GetMonthView();
    $html .= "</div>";
    return <<<EOD
{$html}
<br>

EOD;
  }
  
  /**
   * Get the photo of the month.
   *
   * @return string as html.
   */
  public function GetPhoto() {
    return "<figure class='monthPhoto'><img src='img.php?src=calendar/{$this->dt->GetMonth()}.jpg&amp;width=656&amp;height=410&amp;crop-to-fit&amp;quality=90' alt='{$this->dt->GetMonthName()}'></figure>";
  }
  
  /**
   * Initiate the calendar.
   *
   */
  public function InitCal() {
    if (isset($_GET['year']) && isset($_GET['month'])) {
      $year = (int)$_GET['year'];
      $month = (int)$_GET['month'];
      $this->dt->setDate($year, $month, 1);
    }
    else {
      $this->dt->setDate($this->dt->GetYear(), $this->dt->GetMonth(), 1);
    }
    $this->calYear = $this->dt->GetYear();
    $this->calMonth = $this->dt->GetMonth();
    
    $this->small = false;
  }
  
  /**
   * Get the month title.
   *
   * $param bool $withYear if year should be included in title
   * @return string the month title including the navigation links.
   */
  public function GetMonthTitle($withYear = true) {
    $title = $withYear && !$this->small ? $this->dt->GetMonthName() . " " . $this->dt->GetYear() : $this->dt->GetMonthName();
    return "<div class='monthTitle'>{$this->GetPrevLink()}{$title}{$this->GetNextLink()}</div>";
  }
  
  /**
   * Get the link to previous month.
   *
   * @return string the link.
   */
  public function GetPrevLink() {
    $time = strtotime($this->calYear . "-" . $this->calMonth . "-01");
    list($year, $month) = explode('-', date("Y-m", strtotime("-1 month", strtotime($this->calYear . "-" . $this->calMonth . "-01"))));
    $title = $this->small ? "&lt;&lt;" : "&lt;&lt; " . $this->dt->IntToMonthName($month);
    //$bookmark = $this->small ? "smallCal" : "cal";
    //return "<span class='navPrev'><a href='?year={$year}&month={$month}#{$bookmark}' id='{$bookmark}'>{$title}</a></span>";
    return "<span class='navPrev'><a href='?year={$year}&amp;month={$month}'>{$title}</a></span>";
  }
  
  /**
   * Get the link to next month.
   *
   * @return string the link.
   */
  public function GetNextLink() {
    $time = strtotime($this->calYear . "-" . $this->calMonth . "-01");
    list($year, $month) = explode('-', date("Y-m", strtotime("+1 month", strtotime($this->calYear . "-" . $this->calMonth . "-01"))));
    $title = $this->small ? "&gt;&gt;" : $this->dt->IntToMonthName($month) . " &gt;&gt;";
    //$bookmark = $this->small ? "smallCal" : "cal";
    //return "<span class='navNext'><a href='?year={$year}&month={$month}#{$bookmark}' id='{$bookmark}'>{$title}</a></span>";
    return "<span class='navNext'><a href='?year={$year}&amp;month={$month}'>{$title}</a></span>";
  }
  
  
  /**
   * Get the month view.
   *
   * @return string as html.
   */
  public function GetMonthView() {
    $html = "<div class='monthView'>";
    $html .= $this->GetMonthHeader();
    $lastWeek = $this->dt->GetLastWeekOfMonth();
    do {
      $week = $this->dt->GetWeek(false);
      $this->dt->SetDateFromYearMonthAndWeek($this->calYear, $this->calMonth, $week);
      $html .= "<div class='week'><div class='weekNr'>{$week}</div></div>";
      for ($i = 1; $i <= 7; $i++) {
        $year = $this->dt->GetYear();
        $month = $this->dt->GetMonth();
        $day = $this->dt->GetDay();
        //$html .= "<div class='weekday'";
        $html .= "<div class='";
        $html .= ($month != $this->calMonth) ? "otherWeekday" : "weekday";
        $html .= "' title='{$year}-{$month}-{$day}'><span class='nameDay'>";
        if ($holiday = $this->dt->IsHoliday()) {
          $html .= $holiday . "<br>";
        }
        $html .= "{$this->dt->GetNameDay($month, $day)}</span>";
        $html .= "<span class='day'>";
        $html .= ($this->dt->IsSunday()) ? "<span class='sunday'>{$this->dt->format('j')}</span>" : $this->dt->format('j');
        $html .= "</span>";
        if ($flagTitle = $this->dt->IsFlagDay()) {
          $html .= "<span class='flagDay' title='{$flagTitle}'></span>";
        }
        list($moonPhase, $phaseTitle) = $this->dt->IsMoonPhase();
        if ($moonPhase) {
          $html .= "<span class='moon-{$moonPhase}' title='{$phaseTitle}'></span>";
        }
        $html .= "</div>";
        $this->dt->modify("+1 day");
      }
    } while ($week != $lastWeek);
    $html .= "<div style='clear: both'></div>";
    $html .= "</div>";
    $html .= "<div style='clear: both'></div>";
    // reset the dt object (must do if another calendar is to be outputted)
    $this->dt->setDate($this->calYear, $this->calMonth, 1);
    return $html;
  }
  
   /**
   * Get the month header.
   *
   * @return string as html.
   */
  public function GetMonthHeader() {
    $html = "<div class='header'>";
    $html .= "<div class='week'>V</div>";
    for ($i = 1; $i <= 7; $i++) {
      $html .= "<div class='weekday'>";
      $html .= $i == 7 ? "<span class='sunday'>{$this->dt->IntToDayName($i, $this->small)}</span>" : $this->dt->IntToDayName($i, $this->small);
      $html .= "</div>";
    }
    $html .= "</div>";
    return $html;
  }
}