<?php
/**
 * Theme related functions. 
 *
 */

/**
 * Get title for the webpage by concatenating page specific title with site-wide title.
 *
 * @param string $title for this page.
 * @return string/null wether the favicon is defined or not.
 */
function get_title($title) {
  global $crystal;
  return $title . (isset($crystal['title_append']) ? $crystal['title_append'] : null);
}




/**
 * Create a navigation bar / menu, with submenu.
 *
 * @param string $menu for the navigation bar.
 * @return string as the html for the menu.
 */
function get_navbar($menu)
{
    // Keep default options in an array and merge with incoming options that can override the defaults.
    $default = array(
      'id'          => null,
      'class'       => null,
      'wrapper'     => 'nav',
      'create_url'  => function ($url) {
        return $url;
      },
    );
    $menu = array_replace_recursive($default, $menu);
 
    // Function to create urls
    $createUrl = $menu['create_url'];
 
    // Create the ul li menu from the array, use an anonymous recursive function that returns an array of values.
    $createMenu = function ($items, $callback) use (&$createMenu, $createUrl) {
 
        $html = null;
        $hasItemIsSelected = false;
 
        foreach ($items as $item) {
 
            // has submenu, call recursively and keep track on if the submenu has a selected item in it.
            $submenu        = null;
            $selectedParent = null;
 
            if (isset($item['submenu'])) {
                list($submenu, $selectedParent) = $createMenu($item['submenu']['items'], $callback);
                $selectedParent = $selectedParent
                    ? "selected-parent "
                    : null;
            }
 
            // Check if the current menuitem is selected
            $selected = $callback($item['url'])
                ? "selected "
                : null;
 
            // Is there a class set for this item, then use it
            $class = isset($item['class']) && ! is_null($item['class'])
                ? $item['class']
                : null;
 
            // Prepare the class-attribute, if used
            $class = ($selected || $selectedParent || $class)
                ? " class='{$selected}{$selectedParent}{$class}' "
                : null;
 
            // Add the menu item
            $url = $createUrl($item['url']);
            //$html .= "\n<li{$class}><a href='{$url}' title='{$item['title']}'>{$item['text']}</a>{$submenu}</li>\n";
            // \n is removed to omit 4 px inline-block whitespace:
            // http://stackoverflow.com/questions/4969082/unwanted-margin-in-inline-block-list-items
            $html .= "<li{$class}><a href='{$url}' title='{$item['title']}'>{$item['text']}</a>{$submenu}</li>";
 
            // To remember there is selected children when going up the menu hierarchy
            if ($selected) {
                $hasItemIsSelected = true;
            }
        }
 
        // Return the menu
        return array("\n<ul>$html</ul>\n", $hasItemIsSelected);
    };
 
    // Call the anonymous function to create the menu, and submenues if any.
    list($html, $ignore) = $createMenu($menu['items'], $menu['callback']);
 
 
    // Set the id & class element, only if it exists in the menu-array
    $id      = isset($menu['id'])    ? " id='{$menu['id']}'"       : null;
    $class   = isset($menu['class']) ? " class='{$menu['class']}'" : null;
    $wrapper = $menu['wrapper'];
 
    return "\n<{$wrapper}{$id}{$class}>{$html}</{$wrapper}>\n";
}