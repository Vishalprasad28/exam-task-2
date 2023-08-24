<?php

namespace Drupal\customer_service\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\customer_service\Services\DatabaseHandler;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a customer service form for the customers to add their address..
 */
class AddressForm extends FormBase {

  /**
   * Contains the Connection object.
   *
   * @var \Drupal\customer_service\Services\DatabaseHandler
   */
  protected $dbHandler;

  /**
   * Contains the current User account object.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $user;

  /**
   * Constructs the required dependencies for the form.
   *
   * @param \Drupal\customer_service\Services\DatabaseHandler $db_handler
   *   Contains the databae connection object.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   Contains the Current user account object.
   */
  public function __construct(DatabaseHandler $db_handler, AccountInterface $current_user) {
    $this->dbHandler = $db_handler;
    $this->user = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('customer_service.db_operations'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'customer_service_address';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    // dump($this->dbHandler->checkIfUserExists($this->user->id()));
    $result = $this->dbHandler->checkIfUserExists($this->user->id());
    $form['address'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Enter your address'),
      '#required' => TRUE,
      '#default_value' => $result ? $result[0]['address'] : '',
    ];

    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Save'),
      ],
      'update' => [
        '#type' => 'submit',
        '#value' => $this->t('Update'),
        '#disabled' => $result ? FALSE : TRUE,
        '#submit' => [
          '::updateAddress',
        ],
      ],
    ];

    return $form;
  }

  /**
   * Function to update the user address value.
   *
   * @param array $form
   *   Takes the form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Takes the Formstate object.
   */
  public function updateAddress(array $form, FormStateInterface $form_state) {
    if ($this->dbHandler->updateUserAddress($this->user->id(), $form_state->getValue('address'))) {
      $this->messenger()->addStatus($this->t('Address updated'));
      $form_state->setRedirect('<front>');
    }
    else {
      $this->messenger()->addWarning($this->t('Something wrong happened'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    if ($this->dbHandler->insertUserAddress($this->user->id(), $form_state->getValue('address'))) {
      $this->messenger()->addStatus($this->t('Address saved'));
      $form_state->setRedirect('<front>');
    }
    else {
      $this->messenger()->addWarning($this->t('Something wrong happened'));
    }
  }

}
