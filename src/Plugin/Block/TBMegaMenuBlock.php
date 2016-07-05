<?php

/**
 * @file
 * Contains \Drupal\tb_megamenu\Plugin\Block\TBMegaMenuBlock.
 */

namespace Drupal\tb_megamenu\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides blocks which belong to TB Mega Menu.
 *
 *
 * @Block(
 *   id = "tb_megamenu_menu_block",
 *   admin_label = @Translation("TB Mega Menu"),
 *   category = @Translation("TB Mega Menu"),
 *   deriver = "Drupal\tb_megamenu\Plugin\Derivative\TBMegaMenuBlock",
 * )
 *
 */
class TBMegaMenuBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return array(
      '#theme' => 'tb_megamenu',
      '#menu_name' => $this->getDerivativeId(),
      '#attached' => array('library' => array('tb_megamenu/theme.tb_megamenu')),
      '#post_render' => array('tb_megamenu_attach_number_columns')
    );
  }

  /**
   *  Default cache is disabled. 
   * 
   * @param array $form
   * @param \Drupal\tb_megamenu\Plugin\Block\FormStateInterface $form_state
   * @return 
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $rebuild_form = parent::buildConfigurationForm($form, $form_state);
    $rebuild_form['cache']['max_age']['#default_value'] = 0;
    return $rebuild_form;
  }

}
