<?php

namespace Drupal\panely\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a condition based on modulo operation on node iD.
 *
 * Returns TRUE if remainder after division of node ID by 2 is 0.
 *
 * @Condition(
 *   id = "mod",
 *   label = @Translation("Modulo on Node ID"),
 *   context = {
 *     "node" = @ContextDefinition("entity:node", label = @Translation("Node"))
 *   }
 * )
 *
 */
class Mod extends ConditionPluginBase implements ContainerFactoryPluginInterface {

  /**
   * Mod2 constructor.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array('mod' => 2) + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $levels = [2, 3, 4, 5];

    $form['mod'] = array(
      '#title' => $this->t('Modulus'),
      '#type' => 'select',
      '#options' => array_combine($levels, $levels),
      '#default_value' => $this->configuration['mod'],
    );
    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['mod'] = $form_state->getValue('mod');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $node = $this->getContextValue('node');
    return $node->id() % $this->configuration['mod'] == 0;
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    if ($this->isNegated()) {
      return $this->t('The remainder of node ID division by @mod is not 0.', array('@mod' => $this->configuration['mod']));
    }

    return $this->t('The remainder of node ID division by @mod is 0.', array('@mod' => $this->configuration['mod']));
  }

}
