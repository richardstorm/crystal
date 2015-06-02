<?php
/**
 * Gets the result from database query and output it into an html table, including links, pagination and sorting.
 *
 */
class CHTMLTable {

  /**
   * Use the current querystring as base, modify it according to $options and return the modified query string.
   *
   * @param array $options to set/change.
   * @param string $prepend this to the resulting query string
   * @return string with an updated query string.
   */
  private function getQueryString($options=array(), $prepend='?') {
    // parse query string into array
    $query = array();
    parse_str($_SERVER['QUERY_STRING'], $query);

    // Modify the existing query string with new options
    $query = array_merge($query, $options);

    // Return the modified querystring
    return $prepend . htmlentities(http_build_query($query));
  }
  
  
  /**
   * Create links for hits per page.
   *
   * @param array $hits a list of hits-options to display.
   * @param array $current value.
   * @return string as a link to this page.
   */
  public function getHitsPerPage($hits, $current=null) {
    $nav = "Träffar per sida: ";
    foreach($hits as $val) {
      if($current == $val) {
        $nav .= "$val ";
      }
      else {
        $nav .= "<a href='" . $this->getQueryString(array('hits' => $val)) . "'>$val</a> ";
      }
    }  
    return $nav;
  }


  /**
   * Create navigation among pages.
   *
   * @param int $page the current page number.
   * @param int $max the maximum number of pages. 
   * @param int $min the the first page number, usually 0 or 1. 
   * @return string the link to this page.
   */
  public function getPageNavigation($page, $max, $min=1) {
    $nav  = ($page != $min) ? "<a href='" . $this->getQueryString(array('page' => $min)) . "'>&lt;&lt;</a> " : '&lt;&lt; ';
    $nav .= ($page > $min) ? "<a href='" . $this->getQueryString(array('page' => ($page > $min ? $page - 1 : $min) )) . "'>&lt;</a> " : '&lt; ';

    for($i=$min; $i<=$max; $i++) {
      if($page == $i) {
        $nav .= "$i ";
      }
      else {
        $nav .= "<a href='" . $this->getQueryString(array('page' => $i)) . "'>$i</a> ";
      }
    }

    $nav .= ($page < $max) ? "<a href='" . $this->getQueryString(array('page' => ($page < $max ? $page + 1 : $max) )) . "'>&gt;</a> " : '&gt; ';
    $nav .= ($page != $max) ? "<a href='" . $this->getQueryString(array('page' => $max)) . "'>&gt;&gt;</a> " : '&gt;&gt; ';
    return $nav;
  }
  
  
  /**
   * Function to create links for sorting.
   *
   * @param string $column the name of the database column to sort by
   * @return string with links to order by column.
   */
  public function orderby($column) {
    $nav  = "<a href='" . $this->getQueryString(array('orderby'=>$column, 'order'=>'asc')) . "'>&darr;</a>";
    $nav .= "<a href='" . $this->getQueryString(array('orderby'=>$column, 'order'=>'desc')) . "'>&uarr;</a>";
    return "<span class='orderby'>" . $nav . "</span>";
  }
  
  
  /**
   * Create select menu for sorting.
   *
   * @param array $columns the names of the database column to sort by
   * @param array $orders the orders of the select menu items
   * @param array $names the names of the select menu items
   * @return string select menu to order by column.
   */
  public function selectOrderby($columns, $orders, $names) {
    $select = null;
    foreach ($columns as $key => $column) {
      $name     = $names[$key];
      $order    = $orders[$key];
      $url      = $this->getQueryString(array('orderby'=>$column, 'order'=>$order));
      $selected = isset($_GET['orderby']) && $_GET['orderby'] == $column && $_GET['order'] == $order  ? " selected" : null;
      $select  .= "<option value='{$url}' {$selected}>{$name}</option>";
    }
    return <<<EOD
      <select onchange='javascript:window.location.href=this.value'>
        {$select}
      </select>
EOD;
  }
  
  /**
   * Create select menu for hits per page.
   *
   * @param array $hits a list of hits-options to display.
   * @param array $current value.
   * @return string as a link to this page.
   */
  public function getSelectHitsPerPage($hits, $current=null) {
    $select = null;
    foreach($hits as $val) {
      $url      = $this->getQueryString(array('hits' => $val));
      $selected = ($current == $val) ? " selected" : null;
      $select  .= "<option value='{$url}' {$selected}>{$val}</option>";
    }  
    return <<<EOD
      <select onchange='javascript:window.location.href=this.value'>
        {$select}
      </select>
EOD;
  }
}
