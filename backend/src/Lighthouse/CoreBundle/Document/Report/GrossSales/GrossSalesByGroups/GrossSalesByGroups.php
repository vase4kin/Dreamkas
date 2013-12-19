<?php

namespace Lighthouse\CoreBundle\Document\Report\GrossSales\GrossSalesByGroups;

use Lighthouse\CoreBundle\Document\Classifier\AbstractNode;
use Lighthouse\CoreBundle\Document\Classifier\Group\Group;
use Lighthouse\CoreBundle\Document\Report\GrossSales\GrossSalesByClassifierNode;
use DateTime;

class GrossSalesByGroups extends GrossSalesByClassifierNode
{
    /**
     * @var Group
     */
    protected $group;

    /**
     * @param Group $group
     * @param DateTime[] $dates
     */
    public function __construct(Group $group, array $dates)
    {
        $this->group = $group;
        parent::__construct($dates);
    }

    /**
     * @return AbstractNode
     */
    public function getNode()
    {
        return $this->group;
    }
}