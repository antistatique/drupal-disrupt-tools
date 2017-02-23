<?php

namespace Drupal\disrupt_tools\Service;

use Drupal\image\Entity\ImageStyle;
use Drupal\file\Plugin\Field\FieldType\FileFieldItemList;

// Injections.
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;

/**
 * ImageStyleGenerator.
 */
class ImageStyleGenerator {
  /**
   * EntityTypeManagerInterface to load Files.
   *
   * @var EntityTypeManagerInterface
   */
  private $entityFile;

  /**
   * Provides helpers to operate on files and stream wrappers.
   *
   * @var FileSystemInterface
   */
  private $fso;

  /**
   * Class constructor.
   */
  public function __construct(EntityTypeManagerInterface $entity, FileSystemInterface $fso) {
    $this->entityFile = $entity->getStorage('file');
    $this->fso        = $fso;
  }

  /**
   * Generate Image Style, with responsive format.
   *
   * @param FileFieldItemList $field
   *   Field File Entity to retreive cover and generate it.
   * @param array $styles
   *   Styles to be generated.
   *
   * @return array
   *   Generated link of styles
   */
  public function fromField(FileFieldItemList $field, array $styles) {
    $build = array();

    // Retreive node.
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
      $img_style = ImageStyle::load($style);

      if ($img_style) {
        $build[$media] = $img_style->buildUrl($image->getFileUri());
      }
    }

    return $build;
  }

}
