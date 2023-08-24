<?php

namespace Drupal\products_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Returns responses for products_api routes.
 */
class ProductsApiController extends ControllerBase {

  /**
   * Contains the entity Manager object.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityManager;

  /**
   * Conatins the Client Interface object to handle api calling.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $client;

  /**
   * The controller constructor.
   *
   * @param \GuzzleHttp\ClientInterface $client
   *   Contains the HTTPClient object.
   * @param \\Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   Contains the entity manager object.
   */
  public function __construct(
    ClientInterface $client,
    EntityTypeManagerInterface $entity_type_manager,
  ) {
    $this->client = $client;
    $this->entityManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('http_client'),
      $container->get('entity_type.manager'),
    );
  }

  /**
   * Builds the response.
   *
   * @param int $limit
   *   Takes the limit of data to be fetched.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Returns the json response.
   */
  public function invoke(int $limit) {
    $query = $this->entityManager->getStorage('node')->getQuery();
    $query->accessCheck(FALSE)
      ->condition('type', 'product')
      ->range(0, $limit);
    $product_ids = $query->execute();

    $products = $this->entityManager->getStorage('node')->loadMultiple($product_ids);

    $data = $this->constructApiData($products);
    return new JsonResponse($data);
  }

  /**
   * Function to constructs the API Data to return.
   *
   * @param array $products
   *   Takes the array of objects.
   *
   * @return array
   *   Returns the array of api data.
   */
  private function constructApiData(array $products) {
    $data = [];
    if ($products) {
      foreach ($products as $product) {
        $temp['title'] = $product->getTitle();
        $temp['description'] = $product->get('body')->value;
        $temp['price'] = $product->get('field_price')->getString();
        $temp['images'] = $this->getImageUris($product->get('field_images')->referencedEntities());

        array_push($data, $temp);
      }
    }

    return $data;
  }

  /**
   * Function to returrn the image file uris.
   *
   * @param array $images
   *   Takes the image file object array.
   *
   * @return array
   *   Returns the array of File URIs.
   */
  private function getImageUris(array $images) {
    $data = [];
    if ($images) {
      foreach ($images as $image) {
        $data[] = $image->getFileUri();
      }
    }

    return $data;
  }

}
