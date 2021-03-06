<?php

namespace Plugin\BpmLinkPaymentGateway\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * BpmcPaymentMethodRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class BpmcPaymentMethodRepository extends EntityRepository
{

  /**
   * Find or create payment method
   *
   * @param type $id
   * @return type
   */
  public function findOrCreate($id)
  {
      if ($id == 0) {
          $Payment = new \Plugin\BpmLinkPaymentGateway\Entity\BpmcPaymentMethod();
          $Payment
              ->setDelFlg(0)
              ->setUpdateDate('CURRENT_TIMESTAMP')
              ->setCreateDate('CURRENT_TIMESTAMP');
      } else {
          $Payment = $this->find($id);
      }

      return $Payment;
  }

  public function getSelectedTypes(){

    $qb = $this->getEntityManager()->createQueryBuilder();
    $qb->select('g.payment_type')
      ->from('\Eccube\Entity\Payment', 'p')
      ->innerJoin('\Plugin\BpmLinkPaymentGateway\Entity\BpmcPaymentMethod', 'g', 'WITH', 'p.id = g.id')
      ->where($qb->expr()->andx(                
          $qb->expr()->eq('p.del_flg', 0))
      );

    $rows = $qb->getQuery()->getResult();
    $ret = array();

    if( is_null($rows) ){
      return array();
    }

    foreach ($rows as $row) {
      $ret[] = $row['payment_type'];
    }

    return $ret;
  }
}
