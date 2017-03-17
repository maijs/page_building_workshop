<?php

namespace Drupal\panely\Plugin\DisplayVariant;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;
use Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant;

/**
 * Provides a display variant that simply contains blocks and CSS.
 *
 * For attributes to apply on a layout template, the layout template needs to be
 * modified to output attributes on the root element of the template. For
 * example, see layout templates in custom theme "panely".
 *
 * @DisplayVariant(
 *   id = "panels_variant_with_attributes",
 *   admin_label = @Translation("Panels with attributes"),
 *   weight = 10
 * )
 */
class PanelsDisplayVariantWithAttributes extends PanelsDisplayVariant {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'attributes' => [
        'class' => '',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['attributes']['class'] = [
      '#title' => $this->t('Class'),
      '#type' => 'textfield',
      '#default_value' => !empty($this->configuration['attributes']['class']) ? $this->configuration['attributes']['class'] : '',
      '#description' => $this->t('Provide a list of classes separated by a space.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    foreach (['class'] as $attribute) {
      if ($form_state->hasValue(['attributes', $attribute])) {
        $this->configuration['attributes'][$attribute] = $form_state->getValue(['attributes', $attribute]);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = parent::build();

    if (!empty($this->configuration['attributes']['class'])) {
      $build['#attributes']['class'] = explode(' ', $this->configuration['attributes']['class']);
      $build['#attributes']['class'] = array_map(function($item) {
        return Html::getClass($item);
      }, $build['#attributes']['class']);
    }

    return $build;
  }

}
