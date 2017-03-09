<?php

/**
 * @file
 * Contains \Drupal\tb_megamenu\Plugin\Derivative\TBMegaMenuBlock.
 */

namespace Drupal\tb_megamenu\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;

/**
 * Provides blocks which belong to TB Mega Menu.
 */
class TBMegaMenuBlock extends DeriverBase {
  
  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $menus = menu_ui_get_menus();
    foreach ($menus as $menu => $title) {
      $this->derivatives[$menu] = $base_plugin_definition;
      $this->derivatives[$menu]['admin_label'] = $title;
    }
    return $this->derivatives;
  }
}
