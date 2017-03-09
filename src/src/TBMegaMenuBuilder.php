<?php

/**
 * @file
 * Contains \Drupal\tb_megamenu\TBMegaMenuBuilder.
 *
 * Retrieve menus and Mega Menus.
 */

namespace Drupal\tb_megamenu;

use Drupal\Core\Menu\MenuTreeParameters;

class TBMegaMenuBuilder {

  /**
   * Get the configuration of blocks.
   * @param string $menu_name
   * @param string $theme
   * @return array
   */
  public static function getBlockConfig($menu_name, $theme) {
    $menu = self::getMenus($menu_name, $theme);
    return isset($menu->block_config) ? json_decode($menu->block_config, true) : array();
  }

  /**
   * Get menus that belongs TB mega menu.
   *
   * @global stdClass $language
   * @param string $menu_name
   * @param string $theme
   * @return array
   */
  public static function getMenus($menu_name, $theme) {
    $query = \Drupal::service('database')->select('menu_tree', 'm');
    $query->leftJoin('tb_megamenus', 't', 't.menu_name = m.menu_name');
    return $query->fields('t', array('menu_config', 'block_config'))
            ->condition('t.theme', $theme)
            ->condition('m.menu_name', $menu_name)
            ->execute()
            ->fetchObject();
  }

  public static function getMenuItem($menu_name, $plugin_id) {
    $tree = \Drupal::menuTree()->load($menu_name, (new MenuTreeParameters())->onlyEnabledLinks());
    //  Need to review this.
    //  if (function_exists('i18n_menu_localize_tree')) {
    //    $tree = i18n_menu_localize_tree($tree);
    //  }
    $item = self::findMenuItem($tree, $plugin_id);
    return $item;
  }

  /**
   * search by menu item.
   *
   * @param type $tree
   * @param type $plugin_id
   * @return type
   */
  public static function findMenuItem($tree, $plugin_id) {
    foreach ($tree as $menu_plugin_id => $item) {
      if ($menu_plugin_id == $plugin_id) {
        return $item;
      }
      elseif ($result = self::findMenuItem($item->subtree, $plugin_id)) {
        return $result;
      }
    }
    return NULL;
  }

  /**
   * Load blocks by block_id.
   *
   * @param string $block_id
   * @return array
   */
  public static function loadEntityBlock($block_id) {
    return \Drupal::entityManager()->getStorage('block')->load($block_id);
  }

  /**
   * Get configuration of menu.
   *
   * @param string $menu_name
   * @param string $theme
   * @return stdClass
   */
  public static function getMenuConfig($menu_name, $theme) {
    $menu = self::getMenus($menu_name, $theme);
    return isset($menu->menu_config) ? json_decode($menu->menu_config, TRUE) : array();
  }

  /**
   * Create the default attributes for the configuration of block.
   *
   * @param array $block_config
   */
  public static function editBlockConfig(&$block_config) {
    $block_config += array(
      'animation' => 'none',
      'style' => '',
      'auto-arrow' => TRUE,
      'duration' => 400,
      'delay' => 200,
      'always-show-submenu' => TRUE,
      'off-canvas' => 0,
      'number-columns' => 0
    );
  }

  /**
   * Set the default values to configuration in Sub TB Megamenu if it's empty.
   *
   * @param type $submenu_config
   */
  public static function editSubMenuConfig(&$submenu_config) {
    $submenu_config += array(
      'width' => '',
      'class' => '',
      'group' => '',
    );
  }

  /**
   * Set the default values to configuration in TB Megamenu item if it's empty.
   *
   * @param array $item_config
   */
  public static function editItemConfig(&$item_config) {
    $attributes = array(
      'xicon' => '',
      'class' => '',
      'caption' => '',
      'alignsub' => '',
      'group' => 0,
      'hidewcol' => 0,
      'hidesub' => 0
    );
    foreach ($attributes as $attribute => $value) {
      if (!isset($item_config[$attribute])) {
        $item_config[$attribute] = $value;
      }
    }
  }

  /**
   * Set the default values to configuration in columns if it's empty.
   *
   * @param array $col_config
   */
  public static function editColumnConfig(&$col_config) {
    $attributes = array(
      'width' => 12,
      'class' => '',
      'hidewcol' => 0,
      'showblocktitle' => 0,
    );
    foreach ($attributes as $attribute => $value) {
      if (!isset($col_config[$attribute])) {
        $col_config[$attribute] = $value;
      }
    }
  }

  /**
   * Create block which using tb_megamenu.
   *
   * @param string $menu_name
   * @param string $theme
   * @return array
   */
  public static function renderBlock($menu_name, $theme) {
    return array(
      '#theme' => 'tb_megamenu',
      '#menu_name' => $menu_name,
      '#block_theme' => $theme,
      '#section' => 'backend',
      '#post_render' => array('tb_megamenu_attach_number_columns')
    );
  }

  /**
   * Get Id of column.
   *
   * @param int $number_columns
   * @return string
   */
  public static function getIdColumn($number_columns) {
    $value = &drupal_static('column');
    if (!isset($value)) {
      $value = 1;
    }
    elseif (!$number_columns || $value < $number_columns) {
      $value++;
    }
    return "tb-megamenu-column-$value";
  }

  /**
   * Get all of blocks in system without blocks which belong to TB Mega Menu.
   * In array, each element includes key which is plugin_id and value which is label of block.
   *
   * @staticvar array $_blocks_array
   * @return array
   */
  public static function getAllBlocks() {
    static $_blocks_array = array();
    if (empty($_blocks_array)) {
      // Get default theme for user.
      $theme_default = \Drupal::config('system.theme')->get('default');
      // Get storage handler of block.
      $block_storage = \Drupal::entityManager()->getStorage('block');
      // Get the enabled block in the default theme.
      $entity_ids = $block_storage->getQuery()->condition('theme', $theme_default)->execute();
      $entities = $block_storage->loadMultiple($entity_ids);
      $_blocks_array = [];
      foreach ($entities as $block_id => $block) {
        if ($block->get('settings')['provider'] != 'tb_megamenu') {
          $_blocks_array[$block_id] = $block->label();
        }
      }
      asort($_blocks_array);
    }
    return $_blocks_array;
  }

  /**
   * Create options for animation.
   *
   * @param array $block_config
   * @return array
   */
  public static function createAnimationOptions($block_config) {
    return array(
      'none' => t('None'),
      'fading' => t('Fading'),
      'slide' => t('Slide'),
      'zoom' => t('Zoom'),
      'elastic' => t('Elastic')
    );
  }

  /**
   * Create options for styles.
   *
   * @param array $block_config
   * @return array
   */
  public static function createStyleOptions($block_config) {
    return array(
      '' => t('Default'),
      'black' => t('Black'),
      'blue' => t('Blue'),
      'green' => t('Green'),
    );
  }

  public static function buildPageTrail($menu_items) {
    $trail = array();
    foreach ($menu_items as $pluginId => $item) {
      $is_front = \Drupal::service('path.matcher')->isFrontPage();
      $route_name = $item->link->getPluginDefinition()['route_name'];
      if ($item->inActiveTrail || ($route_name == '<front>' && $is_front)) {
        $trail[$pluginId] = $item;
      }

      if ($item->subtree) {
        $trail += self::buildPageTrail($item->subtree);
      }
    }
    return $trail;
  }

  /**
   *
   * @param array $menu_items
   * @param array $menu_config
   * @param string $section
   */
  public static function syncConfigAll($menu_items, &$menu_config, $section) {
    foreach ($menu_items as $id => $menu_item) {
      $item_config = isset($menu_config[$id]) ? $menu_config[$id] : array();
      if ($menu_item->hasChildren || $item_config) {
        self::syncConfig($menu_item->subtree, $item_config, $section);
        $menu_config[$id] = $item_config;
        self::syncConfigAll($menu_item->subtree, $menu_config, $section);
      }
    }
  }

  /**
   *
   * @param array $items
   * @param array $item_config
   * @param string $section
   */
  public static function syncConfig($items, &$item_config, $section) {
    if (empty($item_config['rows_content'])) {
      $item_config['rows_content'][0][0] = array(
        'col_content' => array(),
        'col_config' => array()
      );

      foreach ($items as $plugin_id => $item) {
        if ($item->link->isEnabled()) {
          $item_config['rows_content'][0][0]['col_content'][] = array(
            'type' => 'menu_item',
            'plugin_id' => $plugin_id,
            'tb_item_config' => array(),
            'weight' => $item->link->getWeight(),
          );
        }
      }
      if (empty($item_config['rows_content'][0][0]['col_content'])) {
        unset($item_config['rows_content'][0]);
      }
    }
    else {
      $hash = array();
      foreach ($item_config['rows_content'] as $i => $row) {
        foreach ($row as $j => $col) {
          foreach ($col['col_content'] as $k => $tb_item) {
            if ($tb_item['type'] == 'menu_item') {
              $hash[$tb_item['plugin_id']] = array(
                'row' => $i,
                'col' => $j
              );
              $existed = false;
              foreach ($items as $plugin_id => $item) {
                if ($item->link->isEnabled() && $tb_item['plugin_id'] == $plugin_id) {
                  $item_config['rows_content'][$i][$j]['col_content'][$k]['weight'] = $item->link->getWeight();
                  $existed = true;
                  break;
                }
              }
              if (!$existed) {
                unset($item_config['rows_content'][$i][$j]['col_content'][$k]);
                if (empty($item_config['rows_content'][$i][$j]['col_content'])) {
                  unset($item_config['rows_content'][$i][$j]);
                }
                if (empty($item_config['rows_content'][$i])) {
                  unset($item_config['rows_content'][$i]);
                }
              }
            }
            else if (!self::IsBlockContentEmpty($tb_item['block_id'], $section)) {
              unset($item_config['rows_content'][$i][$j]['col_content'][$k]);
              if (empty($item_config['rows_content'][$i][$j]['col_content'])) {
                unset($item_config['rows_content'][$i][$j]);
              }
              if (empty($item_config['rows_content'][$i])) {
                unset($item_config['rows_content'][$i]);
              }
            }
          }
        }
      }
      $row = -1;
      $col = -1;
      foreach ($items as $plugin_id => $item) {
        if ($item->link->isEnabled()) {
          if (isset($hash[$plugin_id])) {
            $row = $hash[$plugin_id]['row'];
            $col = $hash[$plugin_id]['col'];
            continue;
          }
          if ($row > -1) {
            self::InsertTBMenuItem($item_config, $row, $col, $item);
          }
          else {
            $row = $col = 0;
            while (isset($item_config['rows_content'][$row][$col]['col_content'][0]['type']) &&
            $item_config['rows_content'][$row][$col]['col_content'][0]['type'] == 'block') {

              $row++;
            }
            self::InsertTBMenuItem($item_config, $row, $col, $item);
            $item_config['rows_content'][$row][$col]['col_config'] = array();
          }
        }
      }
    }
  }

  /**
   * Sync order of menu items between menu and tb_megamenus.
   *
   * @param array $menu_config
   */
  public static function syncOrderMenus(&$menu_config) {
    foreach ($menu_config as $mlid => $config) {
      foreach ($config['rows_content'] as $rows_id => $row) {
        $item_sorted = array();
        // Get weight from items.
        foreach ($row as $col) {
          foreach ($col['col_content'] as $menu_item) {
            if ($menu_item['type'] == 'menu_item') {
              $item_sorted[$menu_item['weight']] = $menu_item;
            }
          }
        }
        ksort($item_sorted); // Sort menu by weight.
        foreach ($row as $rid => $col) {
          foreach ($col['col_content'] as $menu_item_id => $menu_item) {
            if ($menu_item['type'] == 'menu_item') {
              $menu_config[$mlid]['rows_content'][$rows_id][$rid]['col_content'][$menu_item_id] = array_shift($item_sorted);
            }
          }
        }
      }
    }
  }

  /**
   *
   * @param type $block_id
   * @param type $section
   * @return boolean
   */
  public static function IsBlockContentEmpty($block_id, $section) {
    $entity_block = self::loadEntityBlock($block_id);
    if ($entity_block && ($entity_block->getPlugin()->build() || $section == 'backend')) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   *
   * @param array $item_config
   * @param string $row
   * @param string $col
   * @param type $item
   */
  public static function InsertTBMenuItem(&$item_config, $row, $col, $item) {
    $i = 0;
    $col_content = isset($item_config['rows_content'][$row][$col]['col_content']) ? $item_config['rows_content'][$row][$col]['col_content'] : array();
    while ($i < count($col_content) && $col_content[$i]['weight'] < $item->link->getWeight()) {
      $i++;
    }
    for ($j = count($col_content); $j > $i; $j--) {
      $item_config['rows_content'][$row][$col]['col_content'][$j] = $item_config['rows_content'][$row][$col]['col_content'][$j - 1];
    }
    $item_config['rows_content'][$row][$col]['col_content'][$i] = array(
      'plugin_id' => $item->link->getPluginId(),
      'type' => 'menu_item',
      'weight' => $item->link->getWeight(),
      'tb_item_config' => array(),
    );
  }

}
