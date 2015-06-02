<?php
/**
 * Hides the code to deal with movies.
 *
 */
class CMovie {

  /**
   * Members
   */
  private $db = null;               // The PDO object
  private $htmlTable = null;        // The CHTMLTable



  /**
   * Constructor creating a PDO object connecting to a chosen database.
   *
   * @param array $options containing details for connecting to the database.
   *
   */
  public function __construct($options) {
    $default = array(
      'dsn' => null,
      'username' => null,
      'password' => null,
      'driver_options' => null,
      'fetch_style' => PDO::FETCH_OBJ,
    );
    $this->db = new CDatabase(array_merge($default, $options));
    
    $this->htmlTable = new CHTMLTable();
  }
  
  
  
  /**
   * Convert minutes to hours and minutes.
   *
   * @param integer $min the minutes.
   * @return string hours and minutes.
   */
  public function minutes2HoursAndMinutes($min) {
    $hours = floor($min/60);
    $minutes = ($min % 60);
    return $hours > 0 ? "{$hours}tim {$minutes}min" : "{$minutes}min";
  }
  
  
  /**
   * Create an IMDb rating banner.
   *
   * @param string $idIMDb the IMDb id.
   * @return string the banner as html.
   */
  private function urlIMDb($id, $title, $year) {
    return <<<EOD
      <span class="imdbRatingPlugin" data-user="ur19631957" data-title="{$id}" data-style="p3">
        <a href="http://www.imdb.com/title/{$id}/?ref_=plg_rt_1" title="{$title} ({$year}) på IMDb" target="_blank">
          <img src="http://g-ecx.images-amazon.com/images/G/01/imdb/plugins/rating/images/imdb_37x18.png" alt="{$title} ({$year}) på IMDb" />
        </a>
      </span>
      <script>
        (function(d,s,id){
          var js,stags=d.getElementsByTagName(s)[0];
          if(d.getElementById(id)){return;}
          js=d.createElement(s);
          js.id=id;
          js.src="js/imdb.rating.min.js";
          stags.parentNode.insertBefore(js,stags);
        })
        (document,'script','imdb-rating-api');
      </script>
EOD;
  }
  
  
  
  /**
   * Loop over database result to create movie output.
   *
   * @param object $res the database result.
   * @return string the output as html.
   */
  private function movieOutput($res, $imgW = 180, $imgH = 270) {
    $movies = null;
    foreach ($res as $r) {
      $r->price = 29;
      $title = strlen($r->title) > 24 ? substr($r->title, 0, 24) . "..." : $r->title;
      $movies .= "
        <div class='movie'>
          <span class='title'><a href='movies.php?id={$r->id}' title='{$r->title} ({$r->year})'>{$title}</a></span>
          <span class='image'><a href='movies.php?id={$r->id}' title='{$r->title} ({$r->year})'><img src='img.php?src=movie/{$r->image}&amp;width={$imgW}&amp;height={$imgH}&amp;quality=90&amp;crop-to-fit' alt='{$r->title}'/></a></span>
          <a href='javascript:void(0);' title='Hyr filmen'><span class='rent'>{$r->price}:-</span></a>
        </div>
      ";
    }
    return $movies;
  }
  
  
  
  /**
   * Get latest movies from database.
   *
   * @param integer $count the number of movies to show.
   * @return string the output as html.
   */
  public function getLatestMovies($count) {
    $sql = "SELECT * FROM VMovie WHERE deleted IS NULL ORDER BY created DESC LIMIT {$count}";
    $res = $this->db->ExecuteSelectQueryAndFetchAll($sql);
    return <<<EOD
      <div class='latestMovies'>
        {$this->movieOutput($res, 144, 216)}
      </div>
EOD;
  }
  
  
  /**
   * Get most rented movie from database.
   *
   * @param integer $count the number of movies to show.
   * @return string the output as html.
   */
  public function getMostRented($count) {
    if ($count > 6) $count = 6;
    $ids = implode(',', array_slice(array(1, 5, 17, 19, 23, 26), 0, $count));
    $sql = "SELECT * FROM VMovie WHERE ID IN ({$ids})";
    $res = $this->db->ExecuteSelectQueryAndFetchAll($sql);
    return <<<EOD
      <div class='mostRentedMovies'>
        {$this->movieOutput($res, 144, 216)}
      </div>
EOD;
  }
  
  
  /**
   * Get last rented movie from database.
   *
   * @param integer $count the number of movies to show.
   * @return string the output as html.
   */
  public function getLastRented($count) {
    if ($count > 6) $count = 6;
    $ids = implode(',', array_slice(array(2, 6, 9, 13, 15, 25), 0, $count));
    $sql = "SELECT * FROM VMovie WHERE ID IN ({$ids})";
    $res = $this->db->ExecuteSelectQueryAndFetchAll($sql);
    return <<<EOD
      <div class='lastRentedMovies'>
        {$this->movieOutput($res, 144, 216)}
      </div>
EOD;
  }
  
  
  /**
   * Get movie of the month from database.
   *
   * @return string the output as html.
   */
  public function getMovieOfTheMonth($month) {
    $sql = "SELECT * FROM VMovie WHERE ID = " . ($month + 15) . "";
    $res = $this->db->ExecuteSelectQueryAndFetchAll($sql);
    return <<<EOD
      <div class='movieOfTheMonth'>
        {$this->movieOutput($res)}
      </div>
EOD;
  }
  
  
  /**
   * Get movie title from database.
   *
   * @param integer $id the movie id.
   * @return string html output.
   */
  public function getTitle($id) {
    $sql = 'SELECT * FROM VMovie WHERE id = ?';
    $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, array($id));
  return isset($res[0]) ? "{$res[0]->title} ({$res[0]->year})" : null;
  }
  
  
  /**
   * Get movie from database.
   *
   * @param integer $id the movie id.
   * @return string html output.
   */
  public function getMovie($id) {
    $sql = 'SELECT * FROM VMovie WHERE id = ?';
    $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, array($id));

    if (!isset($res[0])) {
      return "Det finns ingen film med detta id ({$id}).";
    }
    else {
      $r = $res[0];
      
      $img = "<img src='img.php?src=movie/{$r->image}&amp;width=240&amp;height=360&amp;quality=90&amp;crop-to-fit' title='{$r->title}'/>";
      
      $back = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "movies.php";
      $action = "<a href='{$back}'>[ Tillbaka ]</a>";
      $action .= isset($_SESSION['user']) ? "
        &nbsp;&nbsp;&nbsp;
        <a href='?edit&id={$r->id}'>[ Redigera ]</a>
        &nbsp;&nbsp;&nbsp;
        <a href='?delete&id={$r->id}'>[ Radera ]</a>
      " : null;
      $year = "(<a href='?year1={$r->year}&year2={$r->year}'>{$r->year}</a>)";
      $genres = null;
      foreach(explode(', ', $r->genres) as $key=>$genre) {
        if ($key) $genres .= ", ";
        $genres .= "<a href='?genre={$genre}'>{$genre}</a>";
      }
      
      $directors = null;
      foreach(explode(', ', $r->directors) as $key=>$director) {
        if ($key) $directors .= ", ";
        $directors .= "<a href='?search={$director}'>{$director}</a>";
      }
      
      $actors = null;
      foreach(explode(', ', $r->actors) as $key=>$actor) {
        if ($key) $actors .= ", ";
        $actors .= "<a href='?search={$actor}'>{$actor}</a>";
      }
      
      $trailer = $r->urlTrailer ? "<span class='trailer'><a href='{$r->urlTrailer}' title='Se trailer' target='_blank'><img src='img.php?src=youtube.png'></a></span>" : null;
      
      return <<<EOD
        <div class='movieDetails'>
          <div class='image'>{$img}</div>
          <div class='metadata'>
            <span class='title'>{$r->title}</span>
            <span class='year'>{$year}</span>
            <span class='orgTitle'>{$r->orgTitle} <em>(originaltitel)</em></span>
            <span class='length'>{$this->minutes2HoursAndMinutes($r->length)}</span>
            <span class='spacer'>|</span>
            <span class='genre'>{$genres}</span>
            <span class='imdb'>{$this->urlIMDb($r->imdb, $r->title, $r->year)}</span>
            {$trailer}
            <span class='plot'>{$r->plot}</span>
            <span class='directors'><strong>Regissör:</strong> {$directors}</span>
            <span class='actors'><strong>Skådespelare:</strong> {$actors}</span>
          </div>
          <div class='action'>
            {$action}
          </div>
        </div>
EOD;
    }
  }
  
  
  
  /**
   * Get all active genres and put them in a select menu.
   *
   * @param string $genre the chosen genre.
   * @return string the select menu.
   */
  private function getGenreSelect($genre = null) {
    $sql = '
      SELECT DISTINCT G.name
      FROM Genre AS G
        INNER JOIN Movie2Genre AS M2G
          ON G.id = M2G.idGenre
    ';
    $res = $this->db->ExecuteSelectQueryAndFetchAll($sql);

    $genres = "<select name='genre'><option value=''>- välj -</option>";
    foreach($res as $val) {
      $selected = ($val->name == $genre) ? " selected" : null;
      $genres .= "<option value='{$val->name}'{$selected}>{$val->name}</option>";
    }
    $genres .= "</select>";
    return $genres;
  }
  
  
  /**
   * Get all active genres.
   *
   * @return array the genres.
   */
  public function getActiveGenres() {
    $sql = '
      SELECT DISTINCT G.name
      FROM Genre AS G
        INNER JOIN Movie2Genre AS M2G
          ON G.id = M2G.idGenre
    ';
    $res = $this->db->ExecuteSelectQueryAndFetchAll($sql);

    return $res ? $res : array();
  }
  
  
  
  /**
   * Prepare the query based on incoming arguments and execute it.
   *
   * @param array $arg containing details for making the query.
   * @return array with the original resultset and number of result rows.
   */
  public function prepareAndExecuteQuery($arg = array()) {
    $default = array(
      'search' => null,
      'title' => null,
      'actor' => null,
      'genre' => null,
      'hits' => 8,
      'page' => 1,
      'orderby' => 'created',
      'order' => 'desc',
    );
    $arg = array_merge($default, $arg);
    
    extract($arg);

    $sqlOrig = "SELECT * FROM VMovie";
    $where    = " AND deleted IS NULL";
    $limit    = null;
    $sort     = " ORDER BY $orderby $order";
    $params   = array();

    // Select by title or actor
    if($search) {
      $where .= ' AND (title LIKE ? OR orgTitle LIKE ? OR actors LIKE ? OR directors LIKE ?)';
      $params[] = "%{$search}%";
      $params[] = "%{$search}%";
      $params[] = "%{$search}%";
      $params[] = "%{$search}%";
    }
    
    // Select by genre
    if ($genre) {
      $where .= ' AND genres LIKE ?';
      $params[] = "%{$genre}%";
    } 

    // Pagination
    if ($hits && $page) {
      $limit = " LIMIT $hits OFFSET " . (($page - 1) * $hits);
    }

    // Complete the sql statement
    $where = $where ? " WHERE 1 {$where}" : null;
    $sql = $sqlOrig . $where . $sort . $limit;
    $resOrig = $this->db->ExecuteSelectQueryAndFetchAll($sql, $params);
    
    // Get max pages for current query, for navigation
    $sql = "
      SELECT
        COUNT(id) AS rows
      FROM 
      (
        $sqlOrig $where
      ) AS Movie
    ";
    $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, $params);
    $rows = $res[0]->rows;
    
    return array($resOrig, $rows);
  }
  
  
  
  /**
   * Function to create search form.
   *
   * @param int $hits the number of hits per page.
   * @param string $title the movie title.
   * @param string $genre the movie genre.
   * @param string $director the movie director.
   * @param string $actor the movie actor.
   * @return string the form.
   */
  public function createSearchForm($hits = 20, $title = null, $genre = null, $director = null, $actor = null, $year1 = null, $year2 = null) {
    return <<<EOD
    <form>
      <fieldset>
      <legend>Sök</legend>
      <input type=hidden name=hits value='{$hits}'/>
      <input type=hidden name=page value='1'/>
      <label>Titel:<br><input type='search' name='title' value='{$title}'/></label>
      <label>Genre:<br>{$this->getGenreSelect($genre)}</label>
      <label>Regissör:<br><input type='text' name='director' value='{$director}'/></label>
      <label>Skådespelare:<br><input type='text' name='actor' value='{$actor}'/></label>
      <label>
        Skapad mellan åren:<br>
        <input type='number' class='year' name='year1' min='1900' max='2100' value='{$year1}'/>
        - 
        <input type='number' class='year' name='year2' min='1900' max='2100'value='{$year2}'/>
      </label>
      <input type='submit' name='search' value='Sök'/>
      <br><br>
      <a href='?search'>Visa alla</a>
      </fieldset>
    </form>
EOD;
  }
  
  
  
  /**
   * Function to create the movie search table with pagination and sorting links.
   *
   * @param array $res the resultset.
   * @param int $rows the number of result rows.
   * @param int $hits the number of hits per page.
   * @param int $page the current page number.
   * @return string the table.
   */
  public function createSearchTable($res, $rows, $hits = 10, $page = 1) {
    
    if ($rows == 0) return "<div class='dbtable'><div class='rows'>{$rows} träffar.</div></div>";
    
    // Get max pages for current query, for navigation
    $max = ceil($rows / $hits);

    // page navigation
    $hitsPerPage = $this->htmlTable->getHitsPerPage(array(10, 25, 50, 75, 100), $hits);
    $navigatePage = $this->htmlTable->getPageNavigation($page, $max);

    $orderByTitle = $this->htmlTable->orderby('title');
    $orderByYear = $this->htmlTable->orderby('year');
    $orderByDirector = $this->htmlTable->orderby('directors');
    $orderByLength = $this->htmlTable->orderby('length');
    
    $tr = <<<EOD
      <tr>
        <!--<th>Rad</th>-->
        <th>Titel {$orderByTitle}</th>
        <th>År {$orderByYear}</th>
        <th>Regissör {$orderByDirector}</th>
        <th>Skådespelare</th>
        <th>Längd {$orderByLength}</th>
        <th>Genre</th>
      </tr>
EOD;
    foreach ($res as $key => $val) {
      $actorsArr = explode(', ', $val->actors);
      $actors = "";
      foreach ($actorsArr as $key=>$actor) {
        if ($key >= 3) {
          break; 
        }
        $actors .= $key ? ", " : "";
        $actors .= $actor;
      }
      $tr .= <<<EOD
        <tr>
          <!--<td>{$key}</td>-->
          <td><a href="?id={$val->id}">{$val->title}</a></td>
          <td>{$val->year}</td>
          <td>{$val->directors}</td>
          <td>{$actors}</td>
          <td style='white-space: nowrap;'>{$this->minutes2HoursAndMinutes($val->length)}</td>
          <td>{$val->genres}</td>
        </tr>
EOD;
    }
    return <<<EOD
      <div class='dbtable'>
        <div class='rows'>{$rows} träffar. {$hitsPerPage}</div>
        <table>
        {$tr}
        </table>
        <div class='pages'>{$navigatePage}</div>
      </div>
EOD;
  }
  
  
  /**
   * Create a movie list with pagination and sorting links.
   *
   * @param array $res the resultset.
   * @param int $rows the number of result rows.
   * @param int $hits the number of hits per page.
   * @param int $page the current page number.
   * @return string the table.
   */
  public function getMovies($res, $rows, $hits = 8, $page = 1) {
    
    // Get max pages for current query, for navigation
    $max = ceil($rows / $hits);

    // page navigation
    $hitsPerPage = $this->htmlTable->getSelectHitsPerPage(array(8, 16, 32, 64, 96), $hits);
    $navigatePage = $this->htmlTable->getPageNavigation($page, $max);
    
    $search = isset($_GET["search"]) ? "<strong>{$_GET['search']}</strong> - " : null;

    $columns = array('created',         'title',     'title',     'year',           'year');
    $orders  = array('desc',            'asc',       'desc',      'asc',            'desc');
    $names   = array('Senast tillagda', 'Titel A-Ö', 'Titel Ö-A', 'Årtal stigande', 'Årtal fallande');
    $orderBy = $this->htmlTable->selectOrderby($columns, $orders, $names);

    
    $movies = $this->movieOutput($res);
    return <<<EOD
      <div class='searchresult'>
        <div class='searchheader'>
          <span class='hits'>
            {$search}Visar {$rows} träffar
          </span>
          <span class='orderBy'>
            <strong>sortera på:</strong> {$orderBy}
          </span>
          <span class='hitsPerPage'>
            <strong>titlar per sida:</strong> {$hitsPerPage}
          </span>
        </div>
        {$movies}
        <div class='pagination'>{$navigatePage}</div>
      </div>
EOD;
  }
  
  
  /**
   * Get genres.
   *
   * @return the genres.
   */
  public function getGenres($genre = null) {
    $sql = "
      SELECT DISTINCT name
      FROM Genre 
      ORDER BY name ASC
    ";
    $res = $this->db->ExecuteSelectQueryAndFetchAll($sql);

    $selected = !$genre ? " class='selected'" : null;
    $genres = "<div class='genre'><a href='?' title='- Visa alla -'{$selected}>- Visa alla -</a></div>";
    foreach ($res as $r) {
      $selected = $r->name == $genre ? " class='selected'" : null;
      $genres .= "
        <div class='genre'>
          <a href='?genre={$r->name}' title='{$r->name}'{$selected}>{$r->name}</a>
        </div>
      ";
    }
    return <<<EOD
      <div class='genres'>
        <span class='title'>Kategorier</span>
        {$genres}
      </div>
EOD;
  }
  
  
  
  /**
   * Check for errors in form input.
   *
   * @return string the error message if error or false if no error found.
   */
  protected function formCheckForErrors() {
    extract($this->getFormParams());
    
    if ($id && is_nan($id)) {
      return "<p>ID must be a number</p>";
    }
    else if (!$title) {
      return "<p>Title cannot be empty</p>";
    }
    else if (is_nan($year) || $year < 1900 || $year > 2100) {
      return "<p>Year must be between 1900 and 2100</p>";
    }
    else if (is_nan($length) || $length < 1 || $length > 14400) {
      return "<p>Length must be between 1 and 14400</p>";
    }
    else if (count($genres) == 0) {
      return "<p>At least one genre must be selected</p>";
    }
    else if ($_FILES["image"]["name"] && (($imgType = pathinfo(basename($_FILES["image"]["name"]),PATHINFO_EXTENSION)) != "jpg") &&
            ($imgType != "jpeg") && ($imgType != "gif") && ($imgType != "png")){
      return "<p>Image must be .jpg, .jpeg, .gif or .png (it is .{$imgType})</p>";
    }

    return false;
  }
  
  
  /**
   * Insert new or update movie in database.
   *
   * @return string output message (if saved or not).
   */
  public function saveMovie() {
    if ($err = $this->formCheckForErrors()) {
      return $err;
    }
    
    extract($this->getFormParams());
    
    $image = null;
    // Upload file
    if ($_FILES["image"]["name"]) {
      $imgType = pathinfo(basename($_FILES["image"]["name"]), PATHINFO_EXTENSION);
      $image = strtolower(preg_replace ('/[^a-z0-9]/i', '', $orgTitle . $year)) . "." . $imgType;
      $targetFile = "img/movie/" . $image;
      if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
        return "<p>Informationen sparades EJ: Possible file upload attack!</p>";
      }
    }

    // If new movie
    if ($id == null) {
      $sql = '
        SELECT 
          CASE 
            WHEN MAX(id) 
            THEN MAX(id) + 1 
            ELSE 1 
            END 
          AS nextID 
        FROM Movie;
      ';
      $res = $this->db->ExecuteSelectQueryAndFetchAll($sql);
      if (!$res) {
        return '<p>Informationen sparades EJ.<br><pre>' . print_r($this->db->ErrorInfo(), 1) . '</pre></p>';
      }
        
      $id = $res[0]->nextID;
      $sql = '
        INSERT INTO Movie 
        (
          id, 
          title, 
          orgTitle,
          year,
          directors,
          actors,
          length,
          plot,
          image,
          imdb,
          urlTrailer,
          created
        )
        VALUES
        (
          ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
        )
      ';
      $params = array(
        $id, 
        $title, 
        $orgTitle,
        $year,
        $directors,
        $actors,
        $length,
        $plot,
        $image,
        $imdb,
        $urlTrailer
      );
      $res = $this->db->ExecuteQuery($sql, $params);
      if (!$res) {
        return '<p>Informationen sparades EJ.<br><pre>' . print_r($this->db->ErrorInfo(), 1) . '</pre></p>';
      }
    }
    // If updating existing movie
    else {
      $imageRow = $image ? "image = '{$image}', " : null;
      $sql = "
        UPDATE Movie SET
          title      = ?, 
          orgTitle   = ?, 
          year       = ?, 
          directors  = ?, 
          actors     = ?, 
          length     = ?, 
          plot       = ?, 
          {$imageRow}
          imdb       = ?, 
          urlTrailer = ?,
          updated    = NOW()
        WHERE 
          id = ?
      ";
      $params = array(
        $title, 
        $orgTitle,
        $year,
        $directors,
        $actors,
        $length,
        $plot,
        $imdb,
        $urlTrailer,
        $id
      );
      $res = $this->db->ExecuteQuery($sql, $params);
      if (!$res) {
        return '<p>Informationen sparades EJ.<br><pre>' . print_r($this->db->ErrorInfo(), 1) . '</pre></p>';
      }
      
      // Delete genres
      $sql = 'DELETE FROM Movie2Genre WHERE idMovie = ?';
      $res = $this->db->ExecuteQuery($sql, array($id));
    }

    // Insert genres
    $sql = 'INSERT INTO Movie2Genre (idMovie, idGenre) VALUES ';
    foreach($genres as $key=>$idGenre) {
      if ($key) $sql .= ", ";
      $sql .= "({$id}, {$idGenre})";
    }
    $res = $this->db->ExecuteQuery($sql);
    
    if (!$res) {
      return '<p>Informationen sparades EJ.<br><pre>' . print_r($this->db->ErrorInfo(), 1) . '</pre></p>';
    }
    else {
      //return "<p>Informationen sparades.</p><p>Gå till innehåll: <a href='?id={$id}'>[ Go ]</a></p>";
      header("Location: ?id={$id}");
      exit;
    }
  }
  
  
  /**
   * Output the formular to create new movie.
   *
   * @return string the form as html.
   */
  public function getNewForm() {
    return $this->getForm("Ny film");
  }
  
  
  /**
   * Output the formular to edit movie.
   *
   * @param array $params containing details for editing the movie.
   * @return string the form as html.
   */
  public function getEditForm($id) {
    // Select from database
    $sql = 'SELECT * FROM VMovie WHERE id = ?';
    $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, array($id));

    if(isset($res[0])) {
      $r = $res[0];
    }
    else {
      die('Misslyckades: det ingen film med sådant id.');
    }

    // Sanitize content before using it.
    $arg = isset($_POST["title"]) ? array() : array(
      'id'         => $id,
      'title'      => htmlentities($r->title, null, 'UTF-8'),
      'orgTitle'   => htmlentities($r->orgTitle, null, 'UTF-8'),
      'year'       => (int)$r->year,
      'length'     => (int)$r->length,
      'directors'  => htmlentities($r->directors, null, 'UTF-8'),
      'actors'     => htmlentities($r->actors, null, 'UTF-8'),
      'plot'       => htmlentities($r->plot, null, 'UTF-8'),
      'image'      => htmlentities($r->image, null, 'UTF-8'),
      'imdb'       => htmlentities($r->imdb, null, 'UTF-8'),
      'urlTrailer' => htmlentities($r->urlTrailer, null, 'UTF-8'),
      'genres'     => explode(', ', $r->idGenres),
    );
    
    return $this->getForm("Uppdatera film", $arg);
  }
  
  
  /**
   * Output the formular to edit movie.
   *
   * @return array the form params.
   */
  protected function getFormParams() {
    return array(
      'id'         => isset($_POST['id'])         ? strip_tags($_POST['id']) : (isset($_GET['id']) ? strip_tags($_GET['id']) : null),
      'title'      => isset($_POST['title'])      ? strip_tags($_POST['title'])     : null,
      'orgTitle'   => isset($_POST['orgTitle'])   ? strip_tags($_POST['orgTitle'])  : null,
      'year'       => isset($_POST['year'])       ? (int)strip_tags($_POST['year'])      : null,
      'length'     => isset($_POST['length'])     ? (int)strip_tags($_POST['length'])    : null,
      'directors'  => isset($_POST['directors'])  ? strip_tags($_POST['directors']) : null,
      'actors'     => isset($_POST['actors'])     ? strip_tags($_POST['actors'])    : null,
      'plot'       => isset($_POST['plot'])       ? strip_tags($_POST['plot'])      : null,
      'image'      => isset($_POST['image'])      ? strip_tags($_POST['image'])     : null,
      'imdb'       => isset($_POST['imdb'])       ? strip_tags($_POST['imdb'])      : null,
      'urlTrailer' => isset($_POST['urlTrailer']) ? strip_tags($_POST['urlTrailer']): null,
      'genres'     => isset($_POST['genres'])     ? $_POST['genres'] : array(),
    );
  }
  
  
  /**
   * Output the formular to create or edit movie.
   * 
   * @param array $params containing details for saving the movie.
   * @param string $legend the legend title.
   *
   * @return string $form the form as html.
   */
  public function getForm($legend, $arg = array()) {
    $arg = array_merge($this->getFormParams(), $arg);
    
    extract($arg);
    
    $idRow = $id ? "<input type='hidden' name='id' value='{$id}'/>" : null;
    
    $genreRow = "";
    $sql = 'SELECT * FROM genre ORDER BY name ASC';
    $res = $this->db->ExecuteSelectQueryAndFetchAll($sql);
    foreach($res as $val) {
      $genreRow .= "<label><input type='checkbox' name='genres[]' value='{$val->id}'";
      if (in_array($val->id, $genres)) {
        $genreRow .= " checked";
      }
      $genreRow .= ">{$val->name}</label>";
    }
    
    
    return <<<EOD
    <form method='post' class='movieForm' enctype="multipart/form-data">
      <fieldset>
      <legend>{$legend}</legend>
      {$idRow}
      <label class='title'>
        Titel:<br>
        <input type='text' name='title' value='{$title}' placeholder='Titel' autofocus required/>
      </label>
      <label class='orgTitle'>
        Originaltitel:<br>
        <input type='text' name='orgTitle' value='{$orgTitle}' placeholder='Originaltitel'/>
      </label>
      <label class='directors'>
        Regissör:<br>
        <input type='text' name='directors' value='{$directors}' placeholder='Regissör'/>
      </label>
      <label class='year'>
        År:<br>
        <input type='number' class='year' name='year' min='1900' max='2100' value='{$year}' placeholder='År' required/>
      </label>
      <label class='length'>
        Längd:<br>
        <input type='number' class='length' name='length' min='1' max='14400' value='{$length}' placeholder='Längd' required/>
      </label>
      <label class='imdb'>
        IMDb-ID:<br>
        <input type='text' name='imdb' value='{$imdb}' placeholder='IMDb-ID'/>
      </label>
      <label class='urlTrailer'>
        Länk till trailer:<br>
        <input type='text' name='urlTrailer' value='{$urlTrailer}' placeholder='Länk till trailer'/>
      </label>
      <label class='image'>
        Bild:<br>
        <input type="file" name="image"/>
      </label>
      <span class='genre'>
        Genre:<br>
        {$genreRow}
      </span>
      <label class='actors'>
        Skådespelare:<br>
        <textarea name='actors' placeholder='Skådespelare'>{$actors}</textarea>
      </label>
      <label class='plot'>
        Handling:<br>
        <textarea name='plot' placeholder='Handling'>{$plot}</textarea>
      </label>
      <input type='submit' name='save' value='Spara film'/>
      </fieldset>
    </form>
EOD;
  }
  
  
  /**
   * Delete movie from database.
   *
   * @param integer $id the id of the movie to be deleted.
   * @return string the output message (if saved or not).
   */
  public function deleteMovie($id) {
    $sql = '
      UPDATE Movie SET
        deleted = NOW()
      WHERE 
        id = ?
    ';
    $res = $this->db->ExecuteQuery($sql, array($id));
    
    if ($res) {
      return '<p>Filmen raderades.</p>';
    }
    else {
      return '<p>Filmen raderades EJ.<br><pre>' . print_r($this->db->ErrorInfo(), 1) . '</pre></p>';
    }
  }
  
  
  /**
   * Output the formular to delete movie.
   * 
   * @param integer $id the movie id to be deleted.
   *
   * @return string $form the form as html.
   */
  public function getDeleteForm($id) {
    $form = "
    <form method=post>
      <fieldset>
      <legend>Radera film</legend>
      <input type='hidden' name='id' value='{$id}'/>
      <p>Är du säker på att du vill radera filmen?</p>
      <p class=buttons><input type='submit' name='confirm' value='Ja, radera'/></p>
      </fieldset>
    </form>";
    
    return $form;
  }
  
  
  /**
   * Create a breadcrumb of the movie path.
   *
   * @return string html with ul/li to display the thumbnail.
   */
  public function createBreadcrumb() {
    $breadcrumb = "<ul class='breadcrumb'>\n<li><a href='movies.php'>Filmer</a> »</li>\n";
   
    if (isset($_GET["new"])) {
      $breadcrumb .= "<li>Ny</li>\n";
    }
    else if (isset($_GET["edit"])) {
      $breadcrumb .= "<li>Redigera</li>\n";
    }
    else if (isset($_GET["delete"])) {
      $breadcrumb .= "<li>Radera</li>\n";
    }
    else if (isset($_GET["genre"])) {
      $breadcrumb .= "<li>{$_GET["genre"]}</li>\n";
    }
    else if (isset($_GET["id"])) {
      $breadcrumb .= "<li>{$this->getTitle($_GET['id'])}</li>\n";
    }
   
    $breadcrumb .= "</ul>\n";
    return $breadcrumb;
  }
}