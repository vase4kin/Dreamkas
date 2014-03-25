<?php

namespace Lighthouse\CoreBundle\Document\Returne\Product;

use Doctrine\MongoDB\Cursor;
use Lighthouse\CoreBundle\Document\DocumentRepository;

class ReturnProductRepository extends DocumentRepository
{
    /**
     * @param string $storeId
     * @param string $productId
     * @return Cursor
     */
    public function findByStoreAndProduct($storeId, $productId)
    {
        $criteria = array(
            'store' => $storeId,
            'originalProduct' => $productId,
        );
        $sort = array(
            'createdDate' => self::SORT_DESC,
        );
        return $this->findBy($criteria, $sort);
    }
}