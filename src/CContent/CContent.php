<?php
/**
 * Class for encapsulation of database content.
 *
 */
class CContent {
  
  /**
   * Members
   */
  private $debug;
  protected $db   = null;               // The PDO object
  protected $filter;
  protected $user;
  protected $urlPage;
  protected $urlPost;
  
  
  
  /**
   * Constructor creating a PDO object connecting to a chosen database.
   *
   * @param array $options containing details for connecting to the database.
   * @param array $urls containing urls for page and post.
   */
  public function __construct($options, $urls = array()) {
    $default = array(
      'dsn' => null,
      'username' => null,
      'password' => null,
      'driver_options' => null,
      'fetch_style' => PDO::FETCH_OBJ,
    );
    $this->db = new CDatabase(array_merge($default, $options));
    
    $default = array(
      'page' => "page.php",
      'post' => "blog.php",
    );
    $urls = array_merge($default, $urls);
    $this->urlPage = $urls['page'];
    $this->urlPost = $urls['post'];
    
    $this->filter = new CTextFilter();
    
    $this->user = new CUser($options);
  }
  
  
  /**
   * Check for errors in form input.
   *
   * @param array $params containing details for checking the content.
   * @return string the error message if error or false if no error found.
   */
  protected function formCheckForErrors($params) {
    // 0 title
    if (($params[0] == "") || ($params[0] == null)) {
      return "<p>Title must be at least one character long.</p>";
    }
    // 1 url
    else if (($params[1] == "") && ($params[4] == "page")) {
      return "<p>url cannot be empty when type is \"page\".";
    }
    // 2 category
    // 3 data
    else if (($params[3] == "") || ($params[3] == null)) {
      return "<p>Text must be at least one character long.</p>";
    }
    // 4 type
    else if (($params[4] != "post") && ($params[4] != "page")) {
      return "<p>Type must be \"post\" or \"page\".</p>";
    }
    // 5 filter
    // 6 published
    // 7 userId
    else {
        return false;
    }
  }
  
  
  /**
   * Restore the database to its defaults.
   *
   * @return bool true on success or false if not.
   */
  public function restoreDatabase() {
    // Load sql from file
    $sql = file_get_contents(__DIR__ . '/reset.sql');;
    $res = $this->db->ExecuteQuery($sql);
    if ($res) {
      return "<p>Databasen är återställd</p>";
    }
    else {
      return "<p>Informationen sparades EJ.<br><pre>" . print_r($this->db->ErrorInfo(), 1) . "</pre>";
    }
  }
  
  
  /**
   * Output the formular to edit content.
   *
   * @param array $params containing details for editing the content.
   * @return string the form as html.
   */
  public function getAllContent() {
    $sql = '
      SELECT *, (published <= NOW()) AS available
      FROM Content
      WHERE deleted IS NULL;
    ';
    $res = $this->db->ExecuteSelectQueryAndFetchAll($sql);
    
    // Put results into a list
    $items = "<ul>";
    foreach($res as $key => $val) {
      $items .= "<li>{$val->type} (" . (!$val->available ? 'inte ' : null) . "publicerad): " . htmlentities($val->title, null, 'UTF-8') . " (<a href='edit.php?id={$val->id}'>editera</a> <a href='" . $this->getUrlToContent($val) . "'>visa</a>)</li>\n";
    }
    return $items ? $items . "</ul>" : "<p>Inget innehåll ännu.</p>";
  }
  
  
  /**
   * Insert new content to database.
   *
   * @param array $params containing details for saving the content.
   * @return string output message (if saved or not).
   */
  public function newContent($params) {
    if ($err = $this->formCheckForErrors($params)) {
      return $err;
    }
    
    // url
    $params[1] = $params[4] == "post" ? null : $this->slugify($params[1]);
    
    // filter
    $params[5] = $params[5] == "" ? null : $params[5];
    
    // published date
    $params[6] = ($params[6] == "") || ($params[6] == null) ? date("Y-m-d H:i:s", time()) : $params[6];
    
    // created date
    $params[8] = $params[6];
    
    // slug
    $params[9] = $params[4] == "post" ? $this->slugify($params[0]) : null;
    
    $sql = '
      INSERT INTO Content 
        (title, url, category, data, type, filter, published, idUser, created, slug) 
        values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ';
    $res = $this->db->ExecuteQuery($sql, $params);
    
    if($res) {
      $page = $params[4] == "page" ? "page" : "blog";
      $url = $page == "page" ? "url={$params[1]}" : "slug={$params[9]}";
      return "<p>Informationen sparades.</p><p>Gå till innehåll: <a href={$page}.php?{$url}>[ Go ]</a></p>";
    }
    else {
      return '<p>Informationen sparades EJ.<br><pre>' . print_r($this->db->ErrorInfo(), 1) . '</pre></p>';
    }
  }
  
  /**
   * Save edited content to database.
   *
   * @param array $params containing details for saving the content.
   * @return string the output message (if saved or not).
   */
  public function updateContent($params) {
    if ($err = $this->formCheckForErrors($params)) {
      return $err;
    }
    
    // url
    $params[1] = $params[4] == "post" ? null : $this->slugify($params[1]);
    
    // published date
    $params[6] = ($params[6] == "") || ($params[6] == null) ? date("Y-m-d H:i:s", time()) : $params[6];
    
    
    $params[8] = $params[7];
    
    // slug
    $params[7] = $params[4] == "post" ? $this->slugify($params[0]) : null;
    
    $sql = '
      UPDATE Content SET
        title     = ?,
        url       = ?,
        category  = ?,
        data      = ?,
        type      = ?,
        filter    = ?,
        published = ?,
        updated   = NOW(),
        slug      = ?
      WHERE 
        id = ?
    ';
    $res = $this->db->ExecuteQuery($sql, $params);
    
    if ($res) {
      $page = $params[4] == "page" ? $this->urlPage : $this->urlPost;
      $url = $page == $this->urlPage ? "url={$params[1]}" : "slug={$params[7]}";
      return "<p>Informationen sparades.</p><p>Gå till innehåll: <a href={$page}?{$url}>[ Go ]</a></p>";
    }
    else {
      return '<p>Informationen sparades EJ.<br><pre>' . print_r($this->db->ErrorInfo(), 1) . '</pre></p>';
    }
  }
  
  
  /**
   * Delete content from database.
   *
   * @param integer $id the id of the content to be deleted.
   * @return string the output message (if saved or not).
   */
  public function deleteContent($id) {
    $sql = '
      UPDATE Content SET
        deleted = NOW()
      WHERE 
        id = ?
    ';
    $res = $this->db->ExecuteQuery($sql, array($id));
    
    if ($res) {
      return '<p>Innehållet raderades.</p>';
    }
    else {
      return '<p>Innehållet raderades EJ.<br><pre>' . print_r($this->db->ErrorInfo(), 1) . '</pre></p>';
    }
  }
  
  
  /**
   * Output the formular to create new content.
   *
   * @param array $params containing details for creating the content.
   * @return string the form as html.
   */
  public function getNewForm($params) {
    return $this->getForm($params, "Nytt innehåll");
  }
  
  
  /**
   * Output the formular to edit content.
   *
   * @param array $params containing details for editing the content.
   * @return string the form as html.
   */
  public function getUpdateForm($id) {
    // Select from database
    $sql = 'SELECT * FROM Content WHERE id = ?';
    $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, array($id));

    if(isset($res[0])) {
      $c = $res[0];
    }
    else {
      die('Misslyckades: det finns inget innehåll med sådant id.');
    }

    // Sanitize content before using it.
    $title     = htmlentities($c->title, null, 'UTF-8');
    $url       = htmlentities($c->url, null, 'UTF-8');
    $category  = htmlentities($c->category, null, 'UTF-8');
    $data      = htmlentities($c->data, null, 'UTF-8');
    $type      = htmlentities($c->type, null, 'UTF-8');
    $filter    = htmlentities($c->filter, null, 'UTF-8');
    $published = htmlentities($c->published, null, 'UTF-8');

    $params = array($title, $url, $category, $data, $type, $filter, $published, $id);
    
    $this->debug = $this->db->Dump();
    
    return $this->getForm($params, "Uppdatera innehåll");
  }
  
  
  /**
   * Output the formular to create or edit content.
   * 
   * @param array $params containing details for saving the content.
   * @param string $legend the legend title.
   *
   * @return string $form the form as html.
   */
  public function getForm($params, $legend) {
    list($title, $url, $category, $data, $type, $filter, $published) = $params;
    
    $selectType = "<select name='type'>";
    $selectedPost = $type == 'post' ? " selected" : null;
    $selectedPage = $type == 'page' ? " selected" : null;
    $selectType .= "<option value='post'{$selectedPost}>Blogg</option>";
    $selectType .= "<option value='page'{$selectedPage}>Sida</option>";
    $selectType .= "</select>";

    $form = "
    <form method='post' class='contentForm'>
      <fieldset>
      <legend>{$legend}</legend>";
    if (isset($id)) {
      $form .= "<input type='hidden' name='id' value='{$id}'/>";
    }
    $form .= "
        <label class='title'>
          Titel:<br/>
          <input type='text' name='title' value='{$title}'/>
        </label>
        <label class='url'>
          Url:<br/>
          <input type='text' name='url' value='{$url}'/>
        </label>
        <label class='category'>
          Kategori:<br/>
          <input type='text' name='category' value='{$category}'/>
        </label>
        <label class='filter'>
          Filter:<br/>
          <input type='text' name='filter' value='{$filter}'/>
        </label>
        <label class='type'>
          Typ:<br/>
          {$selectType}
        </label>
        <label class='date'>
          Publiceringsdatum:<br/>
          <input type='text' name='published' value='{$published}'/>
        </label>
        <label class='data'>
          Text:<br/>
          <textarea name='data'>{$data}</textarea>
        </label>
        <input type='submit' name='save' value='Spara'/>
        <input type='reset' value='Återställ'/>
      </fieldset>
    </form>";
    
    return $form;
  }
  
  
  /**
   * Output the formular to delete content.
   * 
   * @param integer $id the content id to be deleted.
   *
   * @return string $form the form as html.
   */
  public function getDeleteForm($id) {
    $form = "
    <form method=post>
      <fieldset>
      <legend>Radera innehåll</legend>
      <input type='hidden' name='id' value='{$id}'/>
      <p>Är du säker på att du vill radera innehållet?</p>
      <p class=buttons><input type='submit' name='delete' value='Ja, radera'/></p>
      </fieldset>
    </form>";
    
    return $form;
  }
  
  
  /**
   * Create a link to the content, based on its type.
   *
   * @param object $content to link to.
   * @return string with url to display content.
   */
  public function getUrlToContent($content) {
    switch($content->type) {
      case 'page': return "{$this->urlPage}?url={$content->url}"; break;
      case 'post': return "{$this->urlPost}?slug={$content->slug}"; break;
      default: return null; break;
    }
  }

  
  /**
   * Create navigation menu items from blog and page posts in database.
   *
   * @return array $arr the array to be merged with the menus original menu items.
   */
  public function getNavItems() {
    $arr = array();
    
    // get pages
    $sql = '
      SELECT * FROM Content WHERE 
        type = "page" AND 
        published <= NOW() AND 
        deleted IS NULL
        ORDER BY published DESC;';
    $res = $this->db->ExecuteSelectQueryAndFetchAll($sql);
    if (isset($res[0])) {
      $arr["page"] = array('text'    => 'Sida',
                           'url'     => $this->urlPage,
                           'title'   => 'Sida',
                           'submenu' => array("items" => array()),
                           );
      foreach ($res as $r) {
        $arr["page"]["submenu"]["items"][$r->url] = array('text'  => $r->title,
                                                          'url'   => "{$this->urlPage}?url={$r->url}",
                                                          'title' => $r->title
                                                          );
      }
    }
    
    // get posts
    $sql = '
      SELECT * FROM Content WHERE 
        type = "post" AND 
        published <= NOW() AND 
        deleted IS NULL
        ORDER BY published DESC;';
    $res = $this->db->ExecuteSelectQueryAndFetchAll($sql);
    if (isset($res[0])) {
      $arr["blog"] = array('text'  => 'Blogg',
                            'url'   => $this->urlPost,
                            'title' => 'Bloggen',
                            'submenu' => array("items" => array()),
                            );
      foreach ($res as $r) {
        $arr["blog"]["submenu"]["items"][$r->slug] = array('text'  => $r->title,
                                                           'url'   => "{$this->urlPost}?slug={$r->slug}",
                                                           'title' => $r->title
                                                          );
      }
    }
    
    return $arr;
  }
  
  
  
  /**
   * Create a slug of a string, to be used as url.
   *
   * @param string $str the string to format as slug.
   * @returns str the formatted slug. 
   */
  public function slugify($str) {
    $str = mb_strtolower(trim($str));
    $str = str_replace(array('å','ä','ö'), array('a','a','o'), $str);
    $str = preg_replace('/[^a-z0-9-]/', '-', $str);
    $str = trim(preg_replace('/-+/', '-', $str), '-');
    return $str;
  }
}
