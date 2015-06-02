<?php
/**
 * Class for encapsulation of page content.
 *
 */
class CBlog extends CContent {
  
  /**
   * Members
   */
  private $title = "Bloggen";
  
  
  
  /**
   * Constructor creating a PDO object connecting to a chosen database.
   *
   * @param array $options containing details for connecting to the database.
   */
  public function __construct($options, $url = "blog.php") {
    parent::__construct($options, array('post' => $url));
  }

  
  /**
   * Get given post from database.
   *
   * @param integer $id the id of the post to get.
   * @return string the post(s) as html or info message if no post found.
   */
  public function getPost($slug, $category = null) {
    // Get content
    $slugSql = $slug ? 'slug = ?' : '1';
    $categorySql = $category ? 'category = ?' : '1';
    $sql = "
      SELECT Content.*,
      User.name AS userName 
      FROM Content 
      INNER JOIN User ON 
      User.id = Content.idUser 
      WHERE 
        $slugSql AND 
        $categorySql AND 
        type = 'post' AND 
        published <= NOW() AND 
        deleted IS NULL 
      ORDER BY published DESC
      ;";
    $params = $slug ? array($slug) : array($category);
    $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, $params);
    
    if (isset($res[0])) {
      $categories = !$slug ? $this->getCategories($category) : null;
      $class = $slug ? "blogpost" : "blogposts";
      $h1 = !$slug ? "<h1>Nyheter</h1>" : null;
      $output = null;
      foreach ($res as $r) {
        $title = htmlentities($r->title, null, 'UTF-8');
        $header = $slug ? "<h1>{$title}</h1>" : "<a href='{$this->urlPost}?slug={$this->slugify($r->title)}'><h2>{$title}</h2></a>";
        $publisher = "<div class='publisher'>Av {$r->userName} - {$r->published}</div>";
        $data = $r->filter ? $this->filter->doFilter(htmlentities($r->data, null, 'UTF-8'), $r->filter) : $r->data;
        $data = !$slug && strlen($data) > 96 ? substr($data, 0, 96) . "... <a href='{$this->urlPost}?slug={$this->slugify($r->title)}'>Läs mer »</a>": $data;
        $editLink = $this->user->IsAuthenticated() ? "<a href='edit.php?id={$r->id}'>[ Redigera ]</a>&nbsp;&nbsp;&nbsp;<a href='delete.php?id={$r->id}'>[ Radera ]</a>" : null;
        if ($slug) {
          $this->title = $title;
        }
        $categoryRow = $slug && $r->category ? "<span class='categoryfooter'><strong>Kategori:</strong> <a href='?category={$r->category}'>{$r->category}</a></span>" : null;

        $output .= <<<EOD
            <article>
            <header>
              {$header}
            </header>
            {$publisher}
            {$data}
            {$categoryRow}
            <footer>
            {$editLink}
            </footer>
            </article>
EOD;
      }
      return <<<EOD
        {$categories}
        <section class='{$class}'>
          {$h1}
          {$output}
        </section>
EOD;
    }
    else if ($slug) {
      return "Det fanns inte en sådan bloggpost.";
    }
    else {
      return "Det fanns inga bloggposter.";
    }
  }
  
  
  /**
   * Get categories.
   *
   * @return the genres.
   */
  public function getCategories($category = null) {
    $sql = "
      SELECT DISTINCT category AS name
      FROM content
      WHERE type = 'post' AND category != '' AND deleted IS NULL
      ORDER BY name ASC
    ";
    $res = $this->db->ExecuteSelectQueryAndFetchAll($sql);

    $selected = !$category ? " class='selected'" : null;
    $categories = "<div class='category'><a href='?' title='- Visa alla -'{$selected}>- Visa alla -</a></div>";
    foreach ($res as $r) {
      $selected = $r->name == $category ? " class='selected'" : null;
      $categories .= "
        <div class='category'>
          <a href='?category={$r->name}' title='{$r->name}'{$selected}>{$r->name}</a>
        </div>
      ";
    }
    return <<<EOD
      <div class='blogcategories'>
        <span class='title'>Kategorier</span>
        {$categories}
      </div>
EOD;
  }
  
  
  /**
   * Get the lastest posts from database.
   *
   * @param integer $count the number of posts to get.
   * @return string the posts as html or info message if no post found.
   */
  public function getLatestPosts($count) {
    // Get content
    $sql = "
      SELECT Content.*,
      User.name AS userName 
      FROM Content 
      INNER JOIN User ON 
      User.id = Content.idUser 
      WHERE 
        type = 'post' AND 
        published <= NOW() AND 
        deleted IS NULL 
      ORDER BY published DESC
      LIMIT $count
      ;";
    $res = $this->db->ExecuteSelectQueryAndFetchAll($sql);
    
    if (isset($res[0])) {
      $output = "";
      foreach ($res as $r) {
        $title = htmlentities($r->title, null, 'UTF-8');
        $publisher = "<div class='publisher'>{$r->published}</div>";
        $data = $r->filter ? $this->filter->doFilter(htmlentities($r->data, null, 'UTF-8'), $r->filter) : $r->data;

        $output .= <<<EOD
            <article>
              <header>
                <a href='{$this->urlPost}?slug={$this->slugify($r->title)}'><h2>{$title}</h2></a>
              </header>
              {$publisher}
              {$data}
            </article>
EOD;
      }
      return "<div class='latestPosts'>{$output}</div>";
    }
    else {
      return "Det finns inga bloggposter ännu.";
    }
  }
  
  
  /**
   * Get post title.
   *
   * @return string the post title.
   */
  public function getTitle() {
    return $this->title;
  }
  
  
  /**
   * Create a breadcrumb of the movie path.
   *
   * @return string html with ul/li to display the thumbnail.
   */
  public function createBreadcrumb() {
    $breadcrumb = "<ul class='breadcrumb'>\n<li><a href='news.php'>Nyheter</a> »</li>\n";
   
    $page = basename($_SERVER['PHP_SELF']);
    if ($page == "new.php") {
      $breadcrumb .= "<li>Ny</li>\n";
    }
    else if ($page == "edit.php") {
      $breadcrumb .= "<li>Redigera</li>\n";
    }
    else if ($page == "delete.php") {
      $breadcrumb .= "<li>Radera</li>\n";
    }
    else if (isset($_GET["category"])) {
      $breadcrumb .= "<li>{$_GET["category"]}</li>\n";
    }
    else if (isset($_GET["slug"])) {
      $breadcrumb .= "<li>{$this->getTitle()}</li>\n";
    }
   
    $breadcrumb .= "</ul>\n";
    return $breadcrumb;
  }
}
