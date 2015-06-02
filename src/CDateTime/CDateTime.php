<?php
/**
 * A class to extend the php DateTime class.
 *
 */
class CDateTime extends DateTime {
 
  /**
   * Constructor
   *
   */
  public function __construct($time = "now", $timezone = NULL) {
    parent::__construct($time, $timezone);
  }
  
  
  
  /**
   * Get the name of the month.
   *
   * @return string the month name.
   */
  public function GetMonthName() {
    return $this->IntToMonthName($this->GetMonth());
  }
  
  /**
   * Get the name of the month.
   *
   * @param int $month the month number.
   * @return string the month name.
   */
  public function IntToMonthName($month) {
    switch ((int)$month) {
      case 1:
        return "januari";
      case 2:
        return "februari";
      case 3:
        return "mars";
      case 4:
        return "april";
      case 5:
        return "maj";
      case 6:
        return "juni";
      case 7:
        return "juli";
      case 8:
        return "augusti";
      case 9:
        return "september";
      case 10:
        return "oktober";
      case 11:
        return "november";
      case 12:
        return "december";
      default:
        return "not valid month";
    }
  }
  
  /**
   * Get the name of the weekday.
   *
   * $param int $day the weekday number.
   * $param bool $abbr if abbrevated name.
   * @return string as day name.
   */
  public function IntToDayName($day, $abbr = false) {
    switch ((int)$day) {
      case 1:
        $d = "måndag";
        break;
      case 2:
        $d = "tisdag";
        break;
      case 3:
        $d = "onsdag";
        break;
      case 4:
        $d = "torsdag";
        break;
      case 5:
        $d = "fredag";
        break;
      case 6:
        $d = "lördag";
        break;
      case 7:
        $d = "söndag";
        break;
      default:
        $d = "not valid day";
        break;
    }
    return ($abbr && $d != "not valid day") ? mb_substr($d, 0, 2) : $d;
  }
  
  
  /**
   * Get the month nr.
   *
   * $param bool $leadingZero if leading zero.
   * @return string the month number.
   */
  public function GetMonth($leadingZero = true) {
    return $leadingZero ? $this->format('m') : $this->format('n');
  }
  
  /**
   * Get the day nr.
   *
   * $param bool $leadingZero if leading zero.
   * @return string the day number.
   */
  public function GetDay($leadingZero = true) {
    return $leadingZero ? $this->format('d') : $this->format('j');
  }
  
  /**
   * Get the week nr.
   *
   * $param bool $leadingZero if leading zeros
   * @return string the week number.
   */
  public function GetWeek($leadingZero = true) {
    return $leadingZero ? $this->format('W') : (int)$this->format('W');
  }
  
  /**
   * Get the first week of month.
   *
   * @return int the week number.
   */
  public function GetFirstWeekOfMonth() {
    return (int)date('W', strtotime($this->format('Y-m-01')));
  }
  
  /**
   * Get the last week of month.
   *
   * @return int the week number.
   */
  public function GetLastWeekOfMonth() {
    return (int)date('W', strtotime($this->format('Y-m-t')));
  }
  
  /**
   * Get the last day of month.
   *
   * @return int the last day of month.
   */
  public function GetLastDayOfMonth() {
    return (int)date('t', strtotime($this->format('Y-m-01')));
  }
  
  /**
   * Set the monday date given the year, month and week.
   *
   * @param int the year.
   * @param int the week number.
   */
  public function SetDateFromYearMonthAndWeek($year, $month, $week) {
    // if decemeber and the week nr belongs to the next year
    if (($month == 12) && ($week <= 2)) {
      $year++;
    }
    // if january and the week nr belongs to the previous year
    else if (($month == 01) && ($week >= 52)) {
      $year--;
    }
    $this->setTimeStamp(strtotime($year . "W" . str_pad($week, 2, '0', STR_PAD_LEFT)));
  }
  
  /**
   * Get the number of weeks in month.
   *
   * @return int the number of weeks in month.
   */
  public function GetNrOffWeeksInMonth() {
    $firstWeek = $this->GetFirstWeekOfMonth();
    $lastWeek = $this->GetLastWeekOfMonth();
    if ($firstWeek > $lastWeek) {
      // if january
			if ($this->GetMonth(true) == "01") {
				return $lastWeek + 1;
			}
      // if december
			else {
				return (((int)date("W", strtotime($this->GetYear() . "-" . $this->GetMonth() . ($this->GetLastDayOfMonth() - 7))) + 1) - $firstWeek + 1);
			}
		}
		return ($lastWeek - $firstWeek + 1);
  }
  
  /**
   * Get the full year.
   *
   * @return string the year.
   */
  public function GetYear() {
    return $this->format('Y');
  }
  
  /**
   * Tell if date is a sunday.
   *
   * @param bool $includeHolidays include holidays.
   * @return bool true or false.
   */
  public function IsSunday($includeHolidays = true) {
    return $includeHolidays ? (((int)$this->format('N')) == 7) || $this->IsHoliday() : (((int)$this->format('N')) == 7);
  }
  
  /**
   * Get the name day.
   *
   * @return string the name day.
   */
  public function GetNameDay() {
    switch($this->GetMonth() . "-" . $this->GetDay()) {
      case "01-02":
        return "Svea";
        break;
      case "01-03":
        return "Alfred<br>Alfrida";
        break;
      case "01-04":
        return "Rut";
        break;
      case "01-05":
        return "Hanna<br>Hannele";
        break;
      case "01-06":
        return "Kasper<br>Melker<br>Baltsar";
        break;
      case "01-07":
        return "August<br>Augusta";
        break;
      case "01-08":
        return "Erland";
        break;
      case "01-09":
        return "Gunnar<br>Gunder";
        break;
      case "01-10":
        return "Sigurd<br>Sigbritt";
        break;
      case "01-11":
        return "Jan<br>Jannike";
        break;
      case "01-12":
        return "Frideborg<br>Fridolf";
        break;
      case "01-13":
        return "Knut";
        break;
      case "01-14":
        return "Felix<br>Felicia";
        break;
      case "01-15":
        return "Laura<br>Lorentz";
        break;
      case "01-16":
        return "Hjalmar<br>Helmer";
        break;
      case "01-17":
        return "Anton<br>Tony";
        break;
      case "01-18":
        return "Hilda<br>Hildur";
        break;
      case "01-19":
        return "Henrik";
        break;
      case "01-20":
        return "Fabian<br>Sebastian";
        break;
      case "01-21":
        return "Agnes<br>Agneta";
        break;
      case "01-22":
        return "Vincent<br>Viktor";
        break;
      case "01-23":
        return "Frej<br>Freja";
        break;
      case "01-24":
        return "Erika";
        break;
      case "01-25":
        return "Paul<br>Pål";
        break;
      case "01-26":
        return "Bodil<br>Boel";
        break;
      case "01-27":
        return "Göte<br>Göta";
        break;
      case "01-28":
        return "Karl<br>Karla";
        break;
      case "01-29":
        return "Diana";
        break;
      case "01-30":
        return "Gunilla<br>Gunhild";
        break;
      case "01-31":
        return "Ivar<br>Joar";
        break;
      case "02-01":
        return "Max<br>Maximilian";
        break;
      case "02-03":
        return "Disa<br>Hjördis";
        break;
      case "02-04":
        return "Ansgar<br>Anselm";
        break;
      case "02-05":
        return "Agata<br>Agda";
        break;
      case "02-06":
        return "Dorotea<br>Doris";
        break;
      case "02-07":
        return "Rikard<br>Dick";
        break;
      case "02-08":
        return "Berta<br>Bert";
        break;
      case "02-09":
        return "Fanny<br>Franciska";
        break;
      case "02-10":
        return "Iris";
        break;
      case "02-11":
        return "Yngve<br>Inge";
        break;
      case "02-12":
        return "Evelina<br>Evy";
        break;
      case "02-13":
        return "Agne<br>Ove";
        break;
      case "02-14":
        return "Valentin";
        break;
      case "02-15":
        return "Sigfrid";
        break;
      case "02-16":
        return "Julia<br>Julius";
        break;
      case "02-17":
        return "Alexandra<br>Sandra";
        break;
      case "02-18":
        return "Frida<br>Fritiof";
        break;
      case "02-19":
        return "Gabriella<br>Ella";
        break;
      case "02-20":
        return "Vivianne";
        break;
      case "02-21":
        return "Hilding";
        break;
      case "02-22":
        return "Pia";
        break;
      case "02-23":
        return "Torsten<br>Torun";
        break;
      case "02-24":
        return "Mattias<br>Mats";
        break;
      case "02-25":
        return "Sigvard<br>Sivert";
        break;
      case "02-26":
        return "Torgny<br>Torkel";
        break;
      case "02-27":
        return "Lage";
        break;
      case "02-28":
        return "Maria";
        break;
      case "03-01":
        return "Albin<br>Elvira";
        break;
      case "03-02":
        return "Ernst<br>Erna";
        break;
      case "03-03":
        return "Gunborg<br>Gunvor";
        break;
      case "03-04":
        return "Adrian<br>Adriana";
        break;
      case "03-05":
        return "Tora<br>Tove";
        break;
      case "03-06":
        return "Ebba<br>Ebbe";
        break;
      case "03-07":
        return "Camilla";
        break;
      case "03-08":
        return "Siv";
        break;
      case "03-09":
        return "Torbjörn<br>Torleif";
        break;
      case "03-10":
        return "Edla<br>Ada";
        break;
      case "03-11":
        return "Edvin<br>Egon";
        break;
      case "03-12":
        return "Viktoria";
        break;
      case "03-13":
        return "Greger";
        break;
      case "03-14":
        return "Matilda<br>Maud";
        break;
      case "03-15":
        return "Kristoffer<br>Christel";
        break;
      case "03-16":
        return "Herbert<br>Gilbert";
        break;
      case "03-17":
        return "Gertrud";
        break;
      case "03-18":
        return "Edvard<br>Edmund";
        break;
      case "03-19":
        return "Josef<br>Josefina";
        break;
      case "03-20":
        return "Joakim<br>Kim";
        break;
      case "03-21":
        return "Bengt";
        break;
      case "03-22":
        return "Kennet<br>Kent";
        break;
      case "03-23":
        return "Gerda<br>Gerd";
        break;
      case "03-24":
        return "Gabriel<br>Rafael";
        break;
      case "03-26":
        return "Emanuel";
        break;
      case "03-27":
        return "Rudolf<br>Ralf";
        break;
      case "03-28":
        return "Malkolm<br>Morgan";
        break;
      case "03-29":
        return "Jonas<br>Jens";
        break;
      case "03-30":
        return "Holger<br>Holmfrid";
        break;
      case "03-31":
        return "Ester";
        break;
      case "04-01":
        return "Harald<br>Hervor";
        break;
      case "04-02":
        return "Gudmund<br>Ingemund";
        break;
      case "04-03":
        return "Ferdinand<br>Nanna";
        break;
      case "04-04":
        return "Marianne<br>Marlene";
        break;
      case "04-05":
        return "Irene<br>Irja";
        break;
      case "04-06":
        return "Vilhelm<br>William";
        break;
      case "04-07":
        return "Irma<br>Irmelin";
        break;
      case "04-08":
        return "Nadja<br>Tanja";
        break;
      case "04-09":
        return "Otto<br>Ottilia";
        break;
      case "04-10":
        return "Ingvar<br>Ingvor";
        break;
      case "04-11":
        return "Ulf<br>Ylva";
        break;
      case "04-12":
        return "Liv";
        break;
      case "04-13":
        return "Artur<br>Douglas";
        break;
      case "04-14":
        return "Tiburtius";
        break;
      case "04-15":
        return "Olivia<br>Oliver";
        break;
      case "04-16":
        return "Patrik<br>Patricia";
        break;
      case "04-17":
        return "Elias<br>Elis";
        break;
      case "04-18":
        return "Valdemar<br>Volmar";
        break;
      case "04-19":
        return "Olaus<br>Ola";
        break;
      case "04-20":
        return "Amalia<br>Amelie";
        break;
      case "04-21":
        return "Anneli<br>Annika";
        break;
      case "04-22":
        return "Allan<br>Glenn";
        break;
      case "04-23":
        return "Georg<br>Göran";
        break;
      case "04-24":
        return "Vega";
        break;
      case "04-25":
        return "Markus";
        break;
      case "04-26":
        return "Teresia<br>Terese";
        break;
      case "04-27":
        return "Engelbrekt";
        break;
      case "04-28":
        return "Ture<br>Tyra";
        break;
      case "04-29":
        return "Tyko";
        break;
      case "04-30":
        return "Mariana";
        break;
      case "05-01":
        return "Valborg";
        break;
      case "05-02":
        return "Filip<br>Filippa";
        break;
      case "05-03":
        return "John<br>Jane";
        break;
      case "05-04":
        return "Monika<br>Mona";
        break;
      case "05-05":
        return "Gotthard<br>Erhard";
        break;
      case "05-06":
        return "Marit<br>Rita";
        break;
      case "05-07":
        return "Carina<br>Carita";
        break;
      case "05-08":
        return "Åke";
        break;
      case "05-09":
        return "Reidar<br>Reidun";
        break;
      case "05-10":
        return "Esbjörn<br>Styrbjörn";
        break;
      case "05-11":
        return "Märta<br>Märit";
        break;
      case "05-12":
        return "Charlotta<br>Lotta";
        break;
      case "05-13":
        return "Linnea<br>Linn";
        break;
      case "05-14":
        return "Halvard<br>Halvar";
        break;
      case "05-15":
        return "Sofia<br>Sonja";
        break;
      case "05-16":
        return "Ronald<br>Ronny";
        break;
      case "05-17":
        return "Rebecka<br>Ruben";
        break;
      case "05-18":
        return "Erik";
        break;
      case "05-19":
        return "Maj<br>Majken";
        break;
      case "05-20":
        return "Karolina<br>Carola";
        break;
      case "05-21":
        return "Konstantin<br>Conny";
        break;
      case "05-22":
        return "Hemming<br>Henning";
        break;
      case "05-23":
        return "Desideria<br>Desirée";
        break;
      case "05-24":
        return "Ivan<br>Vanja";
        break;
      case "05-25":
        return "Urban";
        break;
      case "05-26":
        return "Vilhelmina<br>Vilma";
        break;
      case "05-27":
        return "Beda<br>Blenda";
        break;
      case "05-28":
        return "Ingeborg<br>Borghild";
        break;
      case "05-29":
        return "Yvonne<br>Jeanette";
        break;
      case "05-30":
        return "Vera<br>Veronika";
        break;
      case "05-31":
        return "Petronella<br>Pernilla";
        break;
      case "06-01":
        return "Gun<br>Gunnel";
        break;
      case "06-02":
        return "Rutger<br>Roger";
        break;
      case "06-03":
        return "Ingemar<br>Gudmar";
        break;
      case "06-04":
        return "Solbritt<br>Solveig";
        break;
      case "06-05":
        return "Bo";
        break;
      case "06-06":
        return "Gustav<br>Gösta";
        break;
      case "06-07":
        return "Robert<br>Robin";
        break;
      case "06-08":
        return "Eivor<br>Majvor";
        break;
      case "06-09":
        return "Börje<br>Birger";
        break;
      case "06-10":
        return "Svante<br>Boris";
        break;
      case "06-11":
        return "Bertil<br>Berthold";
        break;
      case "06-12":
        return "Eskil";
        break;
      case "06-13":
        return "Aina<br>Aino";
        break;
      case "06-14":
        return "Håkan<br>Hakon";
        break;
      case "06-15":
        return "Margit<br>Margot";
        break;
      case "06-16":
        return "Axel<br>Axelina";
        break;
      case "06-17":
        return "Torborg<br>Torvald";
        break;
      case "06-18":
        return "Björn<br>Bjarne";
        break;
      case "06-19":
        return "Germund<br>Görel";
        break;
      case "06-20":
        return "Linda";
        break;
      case "06-21":
        return "Alf<br>Alvar";
        break;
      case "06-22":
        return "Paulina<br>Paula";
        break;
      case "06-23":
        return "Adolf<br>Alice";
        break;
      case "06-25":
        return "David<br>Salomon";
        break;
      case "06-26":
        return "Rakel<br>Lea";
        break;
      case "06-27":
        return "Selma<br>Fingal";
        break;
      case "06-28":
        return "Leo";
        break;
      case "06-29":
        return "Peter<br>Petra";
        break;
      case "06-30":
        return "Elof<br>Leif";
        break;
      case "07-01":
        return "Aron<br>Mirjam";
        break;
      case "07-02":
        return "Rosa<br>Rosita";
        break;
      case "07-03":
        return "Aurora";
        break;
      case "07-04":
        return "Ulrika<br>Ulla";
        break;
      case "07-05":
        return "Laila<br>Ritva";
        break;
      case "07-06":
        return "Esaias<br>Jessika";
        break;
      case "07-07":
        return "Klas";
        break;
      case "07-08":
        return "Kjell";
        break;
      case "07-09":
        return "Jörgen<br>Örjan";
        break;
      case "07-10":
        return "André<br>Andrea";
        break;
      case "07-11":
        return "Eleonora<br>Ellinor";
        break;
      case "07-12":
        return "Herman<br>Hermine";
        break;
      case "07-13":
        return "Joel<br>Judit";
        break;
      case "07-14":
        return "Folke";
        break;
      case "07-15":
        return "Ragnhild<br>Ragnvald";
        break;
      case "07-16":
        return "Reinhold<br>Reine";
        break;
      case "07-17":
        return "Bruno";
        break;
      case "07-18":
        return "Fredrik<br>Fritz";
        break;
      case "07-19":
        return "Sara";
        break;
      case "07-20":
        return "Margareta<br>Greta";
        break;
      case "07-21":
        return "Johanna";
        break;
      case "07-22":
        return "Magdalena<br>Madeleine";
        break;
      case "07-23":
        return "Emma";
        break;
      case "07-24":
        return "Kristina<br>Kerstin";
        break;
      case "07-25":
        return "Jakob";
        break;
      case "07-26":
        return "Jesper";
        break;
      case "07-27":
        return "Marta";
        break;
      case "07-28":
        return "Botvid<br>Seved";
        break;
      case "07-29":
        return "Olof";
        break;
      case "07-30":
        return "Algot";
        break;
      case "07-31":
        return "Helena<br>Elin";
        break;
      case "08-01":
        return "Per";
        break;
      case "08-02":
        return "Karin<br>Kajsa";
        break;
      case "08-03":
        return "Tage";
        break;
      case "08-04":
        return "Arne<br>Arnold";
        break;
      case "08-05":
        return "Ulrik<br>Alrik";
        break;
      case "08-06":
        return "Alfons<br>Inez";
        break;
      case "08-07":
        return "Dennis<br>Denise";
        break;
      case "08-08":
        return "Silvia<br>Sylvia";
        break;
      case "08-09":
        return "Roland";
        break;
      case "08-10":
        return "Lars";
        break;
      case "08-11":
        return "Susanna";
        break;
      case "08-12":
        return "Klara";
        break;
      case "08-13":
        return "Kaj";
        break;
      case "08-14":
        return "Uno";
        break;
      case "08-15":
        return "Stella<br>Estelle";
        break;
      case "08-16":
        return "Brynolf";
        break;
      case "08-17":
        return "Verner<br>Valter";
        break;
      case "08-18":
        return "Ellen<br>Lena";
        break;
      case "08-19":
        return "Magnus<br>Måns";
        break;
      case "08-20":
        return "Bernhard<br>Bernt";
        break;
      case "08-21":
        return "Jon<br>Jonna";
        break;
      case "08-22":
        return "Henrietta<br>Henrika";
        break;
      case "08-23":
        return "Signe<br>Signhild";
        break;
      case "08-24":
        return "Bartolomeus";
        break;
      case "08-25":
        return "Lovisa<br>Louise";
        break;
      case "08-26":
        return "Östen";
        break;
      case "08-27":
        return "Rolf<br>Raoul";
        break;
      case "08-28":
        return "Fatima<br>Leila";
        break;
      case "08-29":
        return "Hans<br>Hampus";
        break;
      case "08-30":
        return "Albert<br>Albertina";
        break;
      case "08-31":
        return "Arvid<br>Vidar";
        break;
      case "09-01":
        return "Sam<br>Samuel";
        break;
      case "09-02":
        return "Justus<br>Justina";
        break;
      case "09-03":
        return "Alfhild<br>Alva";
        break;
      case "09-04":
        return "Gisela";
        break;
      case "09-05":
        return "Adela<br>Heidi";
        break;
      case "09-06":
        return "Lilian<br>Lilly";
        break;
      case "09-07":
        return "Kevin<br>Roy";
        break;
      case "09-08":
        return "Alma<br>Hulda";
        break;
      case "09-09":
        return "Anita<br>Annette";
        break;
      case "09-10":
        return "Tord<br>Turid";
        break;
      case "09-11":
        return "Dagny<br>Helny";
        break;
      case "09-12":
        return "Åsa<br>Åslög";
        break;
      case "09-13":
        return "Sture";
        break;
      case "09-14":
        return "Ida";
        break;
      case "09-15":
        return "Sigrid<br>Siri";
        break;
      case "09-16":
        return "Dag<br>Daga";
        break;
      case "09-17":
        return "Hildegard<br>Magnhild";
        break;
      case "09-18":
        return "Orvar";
        break;
      case "09-19":
        return "Fredrika";
        break;
      case "09-20":
        return "Elise<br>Lisa";
        break;
      case "09-21":
        return "Matteus";
        break;
      case "09-22":
        return "Maurits<br>Moritz";
        break;
      case "09-23":
        return "Tekla<br>Tea";
        break;
      case "09-24":
        return "Gerhard<br>Gert";
        break;
      case "09-25":
        return "Tryggve";
        break;
      case "09-26":
        return "Enar<br>Einar";
        break;
      case "09-27":
        return "Dagmar<br>Rigmor";
        break;
      case "09-28":
        return "Lennart<br>Leonard";
        break;
      case "09-29":
        return "Mikael<br>Mikaela";
        break;
      case "09-30":
        return "Helge";
        break;
      case "10-01":
        return "Ragnar<br>Ragna";
        break;
      case "10-02":
        return "Ludvig<br>Love";
        break;
      case "10-03":
        return "Evald<br>Osvald";
        break;
      case "10-04":
        return "Frans<br>Frank";
        break;
      case "10-05":
        return "Bror";
        break;
      case "10-06":
        return "Jenny<br>Jennifer";
        break;
      case "10-07":
        return "Birgitta<br>Britta";
        break;
      case "10-08":
        return "Nils";
        break;
      case "10-09":
        return "Ingrid<br>Inger";
        break;
      case "10-10":
        return "Harry<br>Harriet";
        break;
      case "10-11":
        return "Erling<br>Jarl";
        break;
      case "10-12":
        return "Valfrid<br>Manfred";
        break;
      case "10-13":
        return "Berit<br>Birgit";
        break;
      case "10-14":
        return "Stellan";
        break;
      case "10-15":
        return "Hedvig<br>Hillevi";
        break;
      case "10-16":
        return "Finn";
        break;
      case "10-17":
        return "Antonia<br>Toini";
        break;
      case "10-18":
        return "Lukas";
        break;
      case "10-19":
        return "Tore<br>Tor";
        break;
      case "10-20":
        return "Joakim<br>Kim";
        break;
      case "10-21":
        return "Ursula<br>Yrsa";
        break;
      case "10-22":
        return "Marika<br>Marita";
        break;
      case "10-23":
        return "Severin<br>Sören";
        break;
      case "10-24":
        return "Evert<br>Eilert";
        break;
      case "10-25":
        return "Inga<br>Ingalill";
        break;
      case "10-26":
        return "Amanda<br>Rasmus";
        break;
      case "10-27":
        return "Sabina";
        break;
      case "10-28":
        return "Simon<br>Simone";
        break;
      case "10-29":
        return "Viola";
        break;
      case "10-30":
        return "Elsa<br>Isabella";
        break;
      case "10-31":
        return "Edit<br>Edgar";
        break;
      case "11-02":
        return "Tobias";
        break;
      case "11-03":
        return "Hubert<br>Hugo";
        break;
      case "11-04":
        return "Sverker";
        break;
      case "11-05":
        return "Eugen<br>Eugenia";
        break;
      case "11-06":
        return "Gust";
        break;
      case "11-07":
        return "Ingegerd<br>Ingela";
        break;
      case "11-08":
        return "Vendela";
        break;
      case "11-09":
        return "Teodor<br>Teodora";
        break;
      case "11-10":
        return "Martin<br>Martina";
        break;
      case "11-11":
        return "Mårten";
        break;
      case "11-12":
        return "Konrad<br>Kurt";
        break;
      case "11-13":
        return "Kristian<br>Krister";
        break;
      case "11-14":
        return "Emil<br>Emilia";
        break;
      case "11-15":
        return "Leopold";
        break;
      case "11-16":
        return "Vibeke<br>Viveka";
        break;
      case "11-17":
        return "Naemi<br>Naima";
        break;
      case "11-18":
        return "Lillemor<br>Moa";
        break;
      case "11-19":
        return "Elisabet<br>Lisbet";
        break;
      case "11-20":
        return "Pontus<br>Marina";
        break;
      case "11-21":
        return "Helga<br>Olga";
        break;
      case "11-22":
        return "Cecilia<br>Sissela";
        break;
      case "11-23":
        return "Klemens";
        break;
      case "11-24":
        return "Gudrun<br>Rune";
        break;
      case "11-25":
        return "Katarina<br>Katja";
        break;
      case "11-26":
        return "Linus";
        break;
      case "11-27":
        return "Astrid<br>Asta";
        break;
      case "11-28":
        return "Malte";
        break;
      case "11-29":
        return "Sune";
        break;
      case "11-30":
        return "Andreas<br>Anders";
        break;
      case "12-01":
        return "Oskar<br>Ossian";
        break;
      case "12-02":
        return "Beata<br>Beatrice";
        break;
      case "12-03":
        return "Lydia";
        break;
      case "12-04":
        return "Barbara<br>Barbro";
        break;
      case "12-05":
        return "Sven";
        break;
      case "12-06":
        return "Nikolaus<br>Niklas";
        break;
      case "12-07":
        return "Angela<br>Angelika";
        break;
      case "12-08":
        return "Virginia";
        break;
      case "12-09":
        return "Anna";
        break;
      case "12-10":
        return "Malin<br>Malena";
        break;
      case "12-11":
        return "Daniel<br>Daniela";
        break;
      case "12-12":
        return "Alexander<br>Alexis";
        break;
      case "12-13":
        return "Lucia";
        break;
      case "12-14":
        return "Sten<br>Sixten";
        break;
      case "12-15":
        return "Gottfrid";
        break;
      case "12-16":
        return "Assar";
        break;
      case "12-17":
        return "Stig";
        break;
      case "12-18":
        return "Abraham";
        break;
      case "12-19":
        return "Isak";
        break;
      case "12-20":
        return "Israel<br>Moses";
        break;
      case "12-21":
        return "Konstantin<br>Conny";
        break;
      case "12-22":
        return "Natanael<br>Jonatan";
        break;
      case "12-23":
        return "Adam";
        break;
      case "12-24":
        return "Eva";
        break;
      case "12-26":
        return "Stefan<br>Staffan";
        break;
      case "12-27":
        return "Johannes<br>Johan";
        break;
      case "12-28":
        return "Benjamin";
        break;
      case "12-29":
        return "Natalia<br>Natalie";
        break;
      case "12-30":
        return "Abel<br>Set";
        break;
      case "12-31":
        return "Sylvester";
        break;
      default:
        return "";
    }
  }

  /**
   * Check if holiday.
   *
   * @return string the holiday name or bool false if not.
   */
  public function IsHoliday() {
    // static days
    switch($this->GetMonth() . "-" . $this->GetDay()) {
      case "01-01":
        return "Nyårsdagen";
      case "01-06":
        return "Trettondedag jul";
      case "05-01":
        return "Första maj";
      case "06-06":
        return "Sveriges<br>nationaldag";
      case "12-25":
        return "Juldagen";
      case "12-25":
        return "Annandag jul";
    }
    
    // dynamic days
    switch($this->GetYear() . "-" . $this->GetMonth() . "-" . $this->GetDay()) { 
      case "2015-04-03":
        return "Långfredagen";
      case "2015-04-05":
        return "Påskdagen";
      case "2015-04-06":
        return "Annandag påsk";
      case "2015-05-14":
        return "Kristi<br>himmelsfärd";
      case "2015-05-24":
        return "Pingstdagen";
      case "2015-06-20":
        return "Midsommar-<br>dagen";
      case "2015-10-31":
        return "Alla helgons<br>dag";
    }
    
    return false;
  }
  
  /**
   * Get the flag day.
   *
   * @return string the flag day name or bool false if not.
   */
  public function IsFlagDay() {
    // static days
    switch($this->GetMonth() . "-" . $this->GetDay()) {
      case "01-01":
        return "Nyårsdagen";
      case "01-28":
        return "Konung Carl XVI Gustafs namnsdag";
      case "02-23":
        return "Kronprinsessan Estelles födelsedag";
      case "03-12":
        return "Kronprinsessan Victorias namnsdag";
      case "04-30":
        return "Konung Carl XVI Gustafs födelsedag";
      case "05-01":
        return "Första maj";
      case "06-06":
        return "Sveriges nationaldag";
      case "07-14":
        return "Kronprinsessan Victorias födelsedag";
      case "08-08":
        return "Drottning Silvias namnsdag";
      case "08-15":
        return "Kronprinsessan Estelles namnsdag";
      case "10-24":
        return "FN-dagen";
      case "11-06":
        return "Gustav Adolfsdagen";
      case "12-10":
        return "Nobeldagen";
      case "12-23":
        return "Silvias födelsedag";
      case "12-25":
        return "Juldagen";
    }
    
    // dynamic days
    switch($this->GetYear() . "-" . $this->GetMonth() . "-" . $this->GetDay()) { 
      case "2014-04-20":
      case "2015-04-05":
      case "2016-03-27":
        return "Påskdagen";
      case "2014-06-08":
      case "2015-05-24":
      case "2016-05-15":
        return "Pingstdagen";
      case "2014-06-08":
      case "2015-06-20":
      case "2016-06-25":
        return "Midsommardagen";
      case "2014-09-14":
        return "Riksdagsvalet";
    }
    
    return false;
  }
  
  /**
   * Check if date is a moon phase.
   *
   * @return array the moon phase nr and title or null if no moon phase.
   */
  public function IsMoonPhase() {
    switch($this->GetYear() . "-" . $this->GetMOnth() . "-" . $this->GetDay()) {
      case "2015-01-20":
      case "2015-02-19":
      case "2015-03-20":
      case "2015-04-18":
      case "2015-05-18":
      case "2015-06-16":
      case "2015-07-16":
      case "2015-08-14":
      case "2015-09-13":
      case "2015-10-13":
      case "2015-11-11":
      case "2015-12-11":
        return array(1, "Nymåne"); // new moon
      case "2015-01-27":
      case "2015-02-25":
      case "2015-03-27":
      case "2015-04-26":
      case "2015-05-25":
      case "2015-06-24":
      case "2015-07-24":
      case "2015-08-22":
      case "2015-09-21":
      case "2015-10-20":
      case "2015-11-19":
      case "2015-12-18":
        return array(3, "Växande halvmåne"); // first quarter moon
      case "2015-01-05":
      case "2015-02-04":
      case "2015-03-05":
      case "2015-04-04":
      case "2015-05-04":
      case "2015-06-02":
      case "2015-07-02":
      case "2015-07-31":
      case "2015-08-29":
      case "2015-09-28":
      case "2015-10-27":
      case "2015-11-25":
      case "2015-12-25":
        return array(5, "Fullmåne"); // full moon
      case "2015-01-13":
      case "2015-02-12":
      case "2015-03-13":
      case "2015-04-12":
      case "2015-05-11":
      case "2015-06-09":
      case "2015-07-08":
      case "2015-08-07":
      case "2015-09-05":
      case "2015-10-04":
      case "2015-11-03":
      case "2015-12-03":
        return array(7, "Avtagande halvmåne"); // third quarter moon
      default:
        return array(null, null);
    }
  }
}