<?php

namespace Drupal\gcsfs\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Settings form for Google Cloud Storage File System.
 */
class Settings extends ConfigFormBase {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Constructs a \Drupal\system\ConfigFormBase object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager) {
    $this->setConfigFactory($config_factory);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['gcsfs.config'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'gcsfs_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, int $console = 0) {
    $form = parent::buildForm($form, $form_state);
    /** @var \Drupal\Core\Config\ImmutableConfig $config */
    $config = $this->config('gcsfs.config');

    $form['bucket_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Bucket name'),
      '#description' => $this->t('The name of the Google cloud storage bucket.'),
      '#default_value' => $config->get('bucket_name'),
      '#required' => TRUE,
    ];

    $form['credentials'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Credentials'),
      '#description' => $this->t('The credentials for the service account that has access to read/write to the Google cloud storage bucket above.'),
      '#default_value' => $config->get('credentials'),
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $this->config('gcsfs.config')
      ->set('bucket_name', $values['bucket_name'])
      ->set('credentials', $values['credentials'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
