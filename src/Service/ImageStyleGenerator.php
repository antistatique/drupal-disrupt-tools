<?php

namespace Drupal\disrupt_tools\Service;

use Drupal\file\Plugin\Field\FieldType\FileFieldItemList;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;

/**
 * ImageStyleGenerator.
 *
 * Service to make it easy to generate Image Styles.
 */
class ImageStyleGenerator {
  /**
   * EntityTypeManagerInterface to load Image Styles.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityImageStyle;

  /**
   * EntityTypeManagerInterface to load files.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityFile;

  /**
   * Provides helpers to operate on files and stream wrappers.
   *
   * @var Drupal\Core\File\FileSystemInterface
   */
  private $fso;

  /**
   * Class constructor.
   */
  public function __construct(EntityTypeManagerInterface $entity, FileSystemInterface $fso) {
    $this->entityImageStyle = $entity->getStorage('image_style');
    $this->entityFile       = $entity->getStorage('file');
    $this->fso              = $fso;
  }

  /**
   * Generate Image Style, with responsive format.
   *
   * @param Drupal\file\Plugin\Field\FieldType\FileFieldItemList $field
   *   Field File Entity to Retrieve cover and generate it.
   * @param array $styles
   *   Styles to be generated.
   *
   * @return array
   *   Generated link of styles
   */
  public function fromField(FileFieldItemList $field, array $styles) {
    $build = array();

    // Retrieve node.
    $cover_fid = '';

    if (isset($field) && $field->entity) {
      $cover_fid = $field->entity->id();
    }

    if ($cover_fid) {
      $build = $this->styles($cover_fid, $styles);
    }

    return $build;
  }

  /**
   * Generate Image Style, with responsive format.
   *
   * @param int $fid
   *   File id to generate.
   * @param array $styles
   *   Styles to be generated.
   *
   * @return array
   *   Generated link of styles
   */
  public function fromFile($fid, array $styles) {
    $build = array();

    $image = $this->entityFile->load($fid);

    if ($image) {
      $build = $this->styles($fid, $styles);
    }

    return $build;
  }

  /**
   * Generate Image Style URL, with responsive format.
   *
   * The Image Style URL given will be processed (derivated)
   * by Drupal.
   *
   * @param int $fid
   *   File id to generated.
   * @param array $styles
   *   Styles to be generated.
   *
   * @return array
   *   Generated url of styles
   */
  private function styles($fid, array $styles) {
    $build = array();

    $image = $this->entityFile->load($fid);

    // Check the image exist on the file system.
    $image_path = $this->fso->realpath($image->getFileUri());
    if (!file_exists($image_path)) {
      return $build;
    }

    foreach ($styles as $media => $style) {
      $img_style = $this->entityImageStyle->load($style);

      if ($img_style) {
        $build[$media] = $img_style->buildUrl($image->getFileUri());
      }
    }

    return $build;
  }

}
