<?php

namespace Drupal\panely\Plugin\Block;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block to view a specific entity.
 *
 * @Block(
 *   id = "entity_field_view",
 *   deriver = "Drupal\panely\Plugin\Deriver\EntityFieldViewDeriver",
 * )
 */
class EntityFieldView extends BlockBase implements ContextAwarePluginInterface, ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * EntityFieldView constructor.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityManagerInterface $entity_manager, EntityFieldManagerInterface $entity_field_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityManager = $entity_manager;
    $this->entityFieldManager = $entity_field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.manager'),
      $container->get('entity_field.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'view_mode' => 'default',
      'field' => NULL,
    ];
  }

  /**
   * Returns fields by entity type and view mode.
   *
   * @param $entity_type
   * @param $view_mode
   *
   * @return array
   */
  protected function getFieldsByViewMode($entity_type, $view_mode) {
    $result = [];

    /** @var \Drupal\Core\Entity\Display\EntityViewDisplayInterface[] $entity_view_displays */
    $entity_view_displays = $this->entityManager->getStorage('entity_view_display')->loadMultiple();

    // Filter out view displays that are related to this entity type and view mode.
    if ($eligible_displays = preg_grep(sprintf('/%s\..*\.%s/i', $entity_type, $view_mode), array_keys($entity_view_displays))) {
      foreach ($eligible_displays as $eligible_display) {
        $eligible_display = $entity_view_displays[$eligible_display];

        foreach ($eligible_display->getComponents() as $component_name => $component) {
          // Allow only real fields.
          if (!empty($component['type'])) {
            $result[$component_name] = $component_name;
          }
        }
      }
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['#prefix'] = '<div id="form-wrapper">';
    $form['#suffix'] = '</div>';

    $form['view_mode'] = [
      '#type' => 'select',
      '#options' => $this->entityManager->getViewModeOptions($this->getDerivativeId()),
      '#title' => $this->t('View mode'),
      '#default_value' => $this->configuration['view_mode'],
      '#ajax' => [
        'callback' => [$this, 'reloadForm'],
        'wrapper' => 'form-wrapper',
      ],
    ];

    // Get user input.
    $input = $form_state->getUserInput();

    // Set default field.
    $field = NULL;

    // Get view mode from submitted input.
    if ($view_mode = NestedArray::getValue($input, ['settings', 'view_mode'])) {
    }
    // Get view mode from saved settings.
    elseif (!empty($this->configuration['view_mode'])) {
      $view_mode = $this->configuration['view_mode'];

      if (!empty($this->configuration['field'])) {
        $field = $this->configuration['field'];
      }
    }
    // Get view mode from scratch.
    else {
      $view_mode = 'default';
    }

    // Full content is the same as default.
    $view_mode = ($view_mode == 'full') ? 'default' : $view_mode;

    // Get all fields for this view mode.
    $fields = $this->getFieldsByViewMode($this->getDerivativeId(), $view_mode);

    $form['field'] = [
      '#type' => 'select',
      '#options' => $fields,
      '#empty_value' => '',
      '#title' => $this->t('Field'),
      '#default_value' => $field,
      '#validated' => TRUE,
    ];

    return $form;
  }

  public function reloadForm(&$form, FormStateInterface $form_state) {
    $form_state->setRebuild(TRUE);
    return $form['settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['view_mode'] = $form_state->getValue('view_mode');
    $this->configuration['field'] = $form_state->getValue('field');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    /** @var $entity \Drupal\Core\Entity\ContentEntityInterface */
    $entity = $this->getContextValue('entity');

    try {
      $build = $entity->get($this->configuration['field'])->view($this->configuration['view_mode']);
      CacheableMetadata::createFromObject($this->getContext('entity'))->applyTo($build);
    } catch (\Exception $e) {
      watchdog_exception('panely', $e);
    }

    return $build;
  }

}
