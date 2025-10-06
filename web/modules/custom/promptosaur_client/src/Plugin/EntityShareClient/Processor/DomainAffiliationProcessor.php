<?php

declare(strict_types = 1);

namespace Drupal\promptosaur_client\Plugin\EntityShareClient\Processor;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\domain_access\DomainAccessManagerInterface;
use Drupal\entity_share_client\Attribute\ImportProcessor;
use Drupal\entity_share_client\ImportProcessor\ImportProcessorPluginBase;
use Drupal\entity_share_client\RuntimeImportContext;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Assign domain affiliation to imported nodes based on inline tag mapping.
 */
#[ImportProcessor(
  id: 'promptosaur_domain_affiliation',
  label: new TranslatableMarkup('Promptosaur: Domain affiliation by inline tags'),
  description: new TranslatableMarkup('Sets domain affiliation on imported nodes mapped from field_inline_tags.'),
  stages: [
    'post_entity_save' => 0,
  ],
  locked: TRUE,
)]
class DomainAffiliationProcessor extends ImportProcessorPluginBase {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Mapping of inline tag term IDs to domain IDs.
   *
   * @var array<string,string>
   */
  protected $tagToDomainMapping;

  /**
   * Constructor.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, LoggerChannelInterface $logger, array $tag_to_domain_mapping) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger;
    $this->tagToDomainMapping = $tag_to_domain_mapping;
  }

  /**
   * {@inheritdoc}
   */
  public static function create($container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('logger.channel.promptosaur_client'),
      $container->getParameter('promptosaur_client.tag_to_domain_mapping')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function postEntitySave(RuntimeImportContext $runtime_import_context, ContentEntityInterface $processed_entity) {
    if ($processed_entity->getEntityTypeId() !== 'node') {
      return;
    }
    if (!$processed_entity->hasField('field_inline_tags')) {
      return;
    }
    if (!$processed_entity->hasField(DomainAccessManagerInterface::DOMAIN_ACCESS_FIELD)) {
      return;
    }

    $tag_items = $processed_entity->get('field_inline_tags');
    if ($tag_items->isEmpty()) {
      return;
    }

    $domain_storage = $this->entityTypeManager->getStorage('domain');
    $target_domain_ids = [];
    foreach ($tag_items as $item) {
    //  $term_id = (string) ($item->target_id ?? '');
      $term_id = (string) ($item->value ?? '');
      if ($term_id === '') {
        continue;
      }
      if (isset($this->tagToDomainMapping[$term_id])) {
        $target_domain_ids[$this->tagToDomainMapping[$term_id]] = TRUE;
      }
    }

    if ($target_domain_ids === []) {
      return;
    }

    // Load domains by machine id and set references by target_id.
    $domains = $domain_storage->loadMultiple(array_keys($target_domain_ids));
    if ($domains === []) {
      return;
    }

    $values = [];
    foreach ($domains as $domain) {
      $values[] = ['target_id' => $domain->id()];
    }

    $processed_entity->set(DomainAccessManagerInterface::DOMAIN_ACCESS_FIELD, $values);
    // Save again to persist domain affiliations.
    $processed_entity->save();
  }

}


