<?php

namespace Drupal\member_login\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Session\AccountProxy;

class Member extends ControllerBase {
  
  /**
   * DependencyInjection @current_user @database service here.
   */
  protected $currentUser;
  protected $connection;

  public function __construct( AccountProxy $currentUser, Connection $connection ) {
    $this->currentUser = $currentUser;
    $this->connection  = $connection;
  }

  public static function create( ContainerInterface $container ) {
    return new static(
      $container->get('current_user'),
      $container->get('database')
    );
  }

  public function list() {
    $header = array(
      array('data' => 'Account Name', 'field' => 'username'),
      array('data' => 'Uid', 'field' => 'uid'),
      array('data' => 'Time', 'field' => 'date', 'sort' => 'desc'),
    );
    $data = $this->member_get_result($header);
    $rows = [];
    foreach($data AS $value) {
      $class = $this->currentUser->id() == $value->uid ? 'current-member-login' : ''; 
      $rows[] = array(
        'data' => array(
          $value->username, $value->uid, date("F j, Y, g:i a",$value->date)
        ), 
        'class' => array($class),
      );
    }

    $output = [];
    $output[] = array(
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $rows,
    );
    $output[] = array('#type' => 'pager');
    $output[] = array(
      '#attached' => array('library' => array('member_login/member_login')),
    );
    return $output;
  }
  
  public function member_get_result($header = array()) {

    $query = $this->connection->select('member_login', 'm')
      ->extend('\Drupal\Core\Database\Query\PagerSelectExtender')
      ->extend('\Drupal\Core\Database\Query\TableSortExtender');
    $query = $query
      ->fields('m', array())
      ->limit(30)
      ->orderByHeader($header);

    $resource = $query->execute();
    $result = $resource->fetchAll();
    return $result;
  }
}
