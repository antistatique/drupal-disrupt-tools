<?php

namespace Drupal\disrupt_tools\Service;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Database\Connection;

/**
 * TaxonomyHelpers.
 *
 * Service to make it easy to work with Taxonomy Term.
 */
class TaxonomyHelpers {
  /**
   * EntityTypeManagerInterface to load Term(s).
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTaxonomy;

  /**
   * Provides a Drupal-specific extension of the PDO database.
   *
   * @var Drupal\Core\Database\Connection
   */
  private $database;

  /**
   * Class constructor.
   */
  public function __construct(EntityTypeManagerInterface $entity, Connection $database) {
    $this->entityTaxonomy = $entity->getStorage('taxonomy_term');
    $this->database       = $database;
  }

  /**
   * Get all the siblings terms of a given taxonomy tid.
   *
   * @param int $tid
   *   Taxonomy tid.
   * @param int $max_depth
   *   The number of levels of the siblings tree to return.
   *   Leave NULL to return all levels.
   *
   * @return array|null
   *   An array of Drupal\taxonomy\Entity\Term that are the siblings
   *   of the taxonomy term $tid.
   */
  public function getSiblings($tid, $max_depth = 1) {
    $term = $this->entityTaxonomy->load($tid);

    if (!$term) {
      return NULL;
    }

    // Retrieve this term's direct parent to load all children.
    $parent = $this->getTopParent($tid);
    if (!empty($parent)) {

      // Check if we load from a top parent or
      // from nothing (so the given $tid is a top parent).
      $load_from = $parent->id();
      if ($term->id() == $parent->id()) {
        $load_from = 0;
      }

      // Load the flat tree.
      $flat_tree = $this->entityTaxonomy->loadTree($term->getVocabularyId(), $load_from, $max_depth, TRUE);

      return $flat_tree;
    }

    return NULL;
  }

  /**
   * Get the top parent term of given taxonomy term.
   *
   * @param int $tid
   *   Given tid to retrieve top parent.
   * @param Drupal\Core\Entity\EntityInterface $parent
   *   Current parent.
   *
   * @return Drupal\taxonomy\Entity\Term
   *   The parent Taxonomy term.
   */
  public function getTopParent($tid, EntityInterface $parent = NULL) {
    // Check it has parent.
    if ($parent = $this->entityTaxonomy->loadParents($tid)) {
      $parents_tid = array_keys($parent);
      $parent_tid = reset($parents_tid);
      $parent = reset($parent);

      // Check if it's a top parent, otherwise load until reach top.
      if ($parent_tid != 0) {
        $parent = $this->getTopParent($parent_tid, $parent);
      }
    }
    else {
      $parent = $this->entityTaxonomy->load($tid);
    }

    return $parent;
  }

  /**
   * Retrieve the depth of a given term id into his vocabulary.
   *
   * @param int $tid
   *   Taxonomy tid to get hierarchy level.
   *
   * @return int|null
   *   Depth of the given term id.
   */
  public function getDepth($tid) {
    // Retrieve the terms.
    $query = $this->database->select('taxonomy_term_hierarchy', 't');
    $results = $query->fields('t')
      ->condition('t.tid', $tid)
      ->range(0, 1)
      ->execute();

    // Retrieve the depth.
    if (!empty($results)) {
      foreach ($results as $entry) {
        return $entry->parent;
      }
    }

    return NULL;
  }

  /**
   * Converting a flat array of Drupal\taxonomy\Entity\Term into a nested tree.
   *
   * The $elements must be generated from Drupal\taxonomy\TermStorage::loadTree.
   *
   * @param array $elements
   *   Flat array of Drupal\taxonomy\Entity\Term.
   * @param int $parent
   *   Previous $parent Drupal\taxonomy\Entity\Term.
   *
   * @return array
   *   Nested array of Drupal\taxonomy\Entity\Term.
   */
  public function buildTree(array $elements, $parent = 0) {
    $branch = [];

    foreach ($elements as $element) {
      if ($element->parents[0] == $parent) {
        $children = $this->buildTree($elements, $element->id());
        if ($children) {
          $element->children = $children;
        }
        $branch[] = $element;
      }
    }

    return $branch;
  }

}
