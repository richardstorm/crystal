<?php
/**
 * Class for user management.
 *
 */
class CUser {

  /**
   * Members
   */
  private $id;
  private $acronym;
  private $name;
  private $db = null;               // The PDO object
  private $htmlTable = null;          // The CHTMLTable
  
  
  
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
    
    if ($this->IsAuthenticated()) {
      $this->id = $_SESSION['user']->id;
      $this->acronym = $_SESSION['user']->acronym;
      $this->name = $_SESSION['user']->name;
    }
  }
  
  
  /**
   * Login the user if user name and password is correct.
   *
   * @param string $user the user name.
   * @param string $password the user password.
   * @return bool true if success or false if not.
   */
  public function Login($user, $password) {
    $sql = "SELECT id, acronym, name FROM User WHERE acronym = ? AND password = md5(concat(?, salt))";
    $params = array($user, $password);
    $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, $params);

    if(isset($res[0])) {
      $_SESSION['user'] = $res[0];
      return true;
    }
    
    return false;
  }
  
  
  /**
   * Prepare the query based on incoming arguments and execute it.
   *
   * @param array $arg containing details for making the query.
   * @return array with the original resultset and number of result rows.
   */
  public function prepareAndExecuteQuery($arg = array()) {
    $default = array(
      'hits' => 8,
      'page' => 1,
      'orderby' => 'created',
      'order' => 'desc',
    );
    $arg = array_merge($default, $arg);
    
    extract($arg);

    $sqlOrig = "SELECT * FROM User";
    $where    = null;
    $limit    = null;
    $sort     = " ORDER BY $orderby $order";
    $params   = array();

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
      ) AS User
    ";
    $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, $params);
    $rows = $res[0]->rows;
    
    return array($resOrig, $rows);
  }
  
  
  /**
   * Get user from database.
   *
   * @param integer $id the user id.
   * @return string html output.
   */
  public function getUser($id) {
    $sql = 'SELECT * FROM User WHERE id = ?';
    $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, array($id));

    if (!isset($res[0])) {
      return "Det finns ingen användare med detta id ({$id}).";
    }
    else {
      $r = $res[0];
      
      $back = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "movies.php";
      $action = "<a href='{$back}'>[ Tillbaka ]</a>";
      if (($this->IsAuthenticated() && $this->GetId() == $id) || ($this->getAcronym() == "admin")) {
        $action .= "&nbsp;&nbsp;&nbsp;<a href='?edit&id={$id}'>[ Redigera ]</a>";
        if ($this->getAcronym() == "admin") {
          $action .= "&nbsp;&nbsp;&nbsp;<a href='?delete&id={$id}'>[ Radera ]</a>";
        }
      }
      $img = "<img src='img.php?src=Actions-im-user-icon.png&height=208' title='Profile pic'/>";
      return <<<EOD
        <div class='userDetails'>
          <div class='image'>{$img}</div>
          <div class='metadata'>
            <span class='acronym'><strong>Acronym:</strong>{$r->acronym}</span>
            <span class='name'><strong>Name:</strong>{$r->name}</span>
          </div>
          <div class='action'>
            {$action}
          </div>
        </div>
EOD;
    }
  }
  
  
  /**
   * Create a user list with pagination and sorting links.
   *
   * @param array $res the resultset.
   * @param int $rows the number of result rows.
   * @param int $hits the number of hits per page.
   * @param int $page the current page number.
   * @return string the table.
   */
  public function getUsers($res, $rows, $hits = 5, $page = 1) {
    
    // Get max pages for current query, for navigation
    $max = ceil($rows / $hits);

    // page navigation
    $hitsPerPage = $this->htmlTable->getSelectHitsPerPage(array(5, 10, 20, 50), $hits);
    $navigatePage = $this->htmlTable->getPageNavigation($page, $max);

    $columns = array('id',     'id',     'acronym',     'acronym',     'name',     'name');
    $orders  = array('asc',    'desc',   'asc',         'desc',        'asc',      'desc');
    $names   = array('id 0-9', 'id 9-0', 'Acronym A-Ö', 'Acronym Ö-A', 'Namn A-Ö', 'Namn A-Ö');
    $orderBy = $this->htmlTable->selectOrderby($columns, $orders, $names);

    
    $users = "<table><tr><th>id</th><th>Acronym</th><th>Name</th></tr>";
    foreach ($res as $r) {
      $users .= "
        <tr>
          <td>{$r->id}</td>
          <td><a href='?id={$r->id}' title='Visa profil'>{$r->acronym}</a></td>
          <td>{$r->name}</td>
        </tr>
      ";
    }
    $users .= "</table>";
    
    return <<<EOD
      <div class='userlist'>
        <div class='userheader'>
          <span class='hits'>
             Visar {$rows} träffar
          </span>
          <span class='orderBy'>
            <strong>sortera på:</strong> {$orderBy}
          </span>
          <span class='hitsPerPage'>
            <strong>titlar per sida:</strong> {$hitsPerPage}
          </span>
        </div>
        {$users}
        <div class='pagination'>{$navigatePage}</div>
      </div>
EOD;
  }
  
  
  /**
   * Output the formular to create new user.
   *
   * @return string the form as html.
   */
  public function getNewForm() {
    return $this->getForm("Ny användare");
  }
  
  
  /**
   * Output the formular to edit user.
   *
   * @param array $params containing details for editing the user.
   * @return string the form as html.
   */
  public function getEditForm($id) {
    // Select from database
    $sql = 'SELECT * FROM User WHERE id = ?';
    $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, array($id));

    if(isset($res[0])) {
      $r = $res[0];
    }
    else {
      die('Misslyckades: det finns ingen användare med sådant id.');
    }

    (($this->getId() == $id) || ($this->getAcronym() == "admin")) or die('Misslyckades: du kan bara redigera dig själv.');
    
    // Sanitize content before using it.
    $arg = isset($_POST["title"]) ? array() : array(
      'id'      => $id,
      'acronym' => htmlentities($r->acronym, null, 'UTF-8'),
      'name'    => htmlentities($r->name, null, 'UTF-8'),
    );
    
    return $this->getForm("Uppdatera användare", $arg);
  }
  
  
  /**
   * Output the formular to edit movie.
   *
   * @return array the form params.
   */
  protected function getFormParams() {
    return array(
      'id'        => isset($_POST['id'])        ? strip_tags($_POST['id'])        : (isset($_GET['id']) ? strip_tags($_GET['id']) : null),
      'acronym'   => isset($_POST['acronym'])   ? strip_tags($_POST['acronym'])   : null,
      'name'      => isset($_POST['name'])      ? strip_tags($_POST['name'])      : null,
      'password'  => isset($_POST['password'])  ? strip_tags($_POST['password'])  : null
    );
  }
  
  
  /**
   * Output the formular to create or edit user.
   * 
   * @param array $params containing details for saving the user.
   * @param string $legend the legend title.
   *
   * @return string $form the form as html.
   */
  public function getForm($legend, $arg = array()) {
    $arg = array_merge($this->getFormParams(), $arg);
    
    extract($arg);
    
    $idRow = $id ? "<input type='hidden' name='id' value='{$id}'/>" : null;
    
    return <<<EOD
      <form method='post'>
        <fieldset>
        <legend>{$legend}</legend>
        {$idRow}
        <label>Användarnamn:<br/><input type='text' name='acronym' value='{$acronym}' maxlength='16' required/></label>
        <label>Namn:<br/><input type='text' name='name' value='{$name}' maxlength='48' required/></label>
        <label>Lösenord:<br/><input type='password' name='password' value='' maxlength='16' required/></label>
        <input type='submit' name='save' value='Spara'/>
        </fieldset>
      </form>
EOD;
  }
  
  
  /**
   * Check for errors in form input.
   *
   * @return string the error message if error or false if no error found.
   */
  protected function formCheckForErrors() {
    extract($this->getFormParams());
    
    // Check and validate input
    if (strlen($acronym) < 3) {
      return "<p>Acronym must be at least 3 characters long.</p>";
    }
    else if (strlen($name) < 5) {
      return "<p>Name must be at least 5 characters long.</p>";
    }
    else if (strlen($password) < 5) {
      return "<p>Password must be at least 5 characters long.</p>";
    }
    
    // Check if user already exists
    $id = $id ? $id : 0;
    $sql = "SELECT * FROM User WHERE acronym = ? AND id != ?";
    $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, array($acronym, $id));
    if ($res) {
      return "<p>Acronym already exists.</p>";
    }

    return false;
  }
  
  
  /**
   * Insert new or update movie in database.
   *
   * @return string output message (if saved or not).
   */
  public function saveUser() {
    if ($err = $this->formCheckForErrors()) {
      return $err;
    }
    
    extract($this->getFormParams());
    
    // If new user
    if ($id == null) {
      // Get next id
      $sql = "SELECT id FROM User ORDER BY id DESC LIMIT 1";
      $res = $this->db->ExecuteSelectQueryAndFetchAll($sql);
      $id = $res ? $res[0]->id + 1 : 1;
    
      // Insert user into database
      $sql = '
        INSERT INTO User 
        (
          id, 
          acronym, 
          name, 
          salt
        )
        VALUES
        (
          ?, ?, ?, unix_timestamp()
        )
      ';
      $params = array(
        $id, 
        $acronym, 
        $name
      );
      $res = $this->db->ExecuteQuery($sql, $params);
      if (!$res) {
        return '<p>Informationen sparades EJ.<br><pre>' . print_r($this->db->ErrorInfo(), 1) . '</pre></p>';
      }
      
      // Set password
      $sql = '
        UPDATE User 
        SET password = md5(concat(?, salt)) 
        WHERE acronym = ?
      ';
      $params = array(
        $password, 
        $acronym
      );
      $res = $this->db->ExecuteQuery($sql, $params);
      if (!$res) {
        return '<p>Informationen sparades EJ.<br><pre>' . print_r($this->db->ErrorInfo(), 1) . '</pre></p>';
      }
    }
    // If updating existing user
    else {
      $sql = '
        UPDATE User SET
          acronym    = ?, 
          name       = ?, 
          password   = md5(concat(?, salt)) 
        WHERE 
          id = ?
      ';
      $params = array(
        $acronym, 
        $name,
        $password,
        $id
      );
      $res = $this->db->ExecuteQuery($sql, $params);
      if (!$res) {
        return '<p>Informationen sparades EJ.<br><pre>' . print_r($this->db->ErrorInfo(), 1) . '</pre></p>';
      }
    }
    header("Location: ?id={$id}");
    exit;
  }
  
  
  /**
   * Delete user from database.
   *
   * @param integer $id the id of the user to be deleted.
   * @return string the output message (if saved or not).
   */
  public function deleteUser($id) {
    $sql = 'DELETE FROM User WHERE id = ? LIMIT 1';
    $res = $this->db->ExecuteQuery($sql, array($id));
    
    if ($res) {
      return '<p>Användaren raderades.</p>';
    }
    else {
      return '<p>Användaren raderades EJ.<br><pre>' . print_r($this->db->ErrorInfo(), 1) . '</pre></p>';
    }
  }
  
  
  /**
   * Output the formular to delete user.
   * 
   * @param integer $id the user id to be deleted.
   *
   * @return string $form the form as html.
   */
  public function getDeleteForm($id) {
    $form = "
    <form method=post>
      <fieldset>
      <legend>Radera användare</legend>
      <input type='hidden' name='id' value='{$id}'/>
      <p>Är du säker på att du vill radera användaren?</p>
      <p class=buttons><input type='submit' name='confirm' value='Ja, radera'/></p>
      </fieldset>
    </form>";
    
    return $form;
  }
  
  
  /**
   * Logout the user.
   *
   */
  public function Logout() {
    unset($_SESSION['user']);
  }
  
  
  /**
   * Check if user is authenticated.
   *
   * @return bool true if user is authenticated or false if not.
   */
  public function IsAuthenticated() {
    return isset($_SESSION['user']);
  }
  
  
  /**
   * Get the user id.
   *
   * @return integer the user id.
   */
  public function GetId() {
    return $this->id;
  }

  
  /**
   * Get the user acronym.
   *.
   * @return string the user acronym.
   */
  public function GetAcronym() {
    return $this->acronym;
  }
  
  
  /**
   * Get the user name.
   *
   * @return string the user name.
   */
  public function GetName() {
    return $this->name;
  }
}
