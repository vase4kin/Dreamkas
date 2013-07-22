<?php

namespace Lighthouse\CoreBundle\Document\Group;

use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Lighthouse\CoreBundle\Document\Category\CategoryRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Lighthouse\CoreBundle\Document\Group\Group;
use Lighthouse\CoreBundle\Document\Group\GroupNotEmptyException;

/**
 * @DI\DoctrineMongoDBListener(events={"preRemove"})
 */
class GroupListener
{
    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @DI\InjectParams({
     *      "categoryRepository"=@DI\Inject("lighthouse.core.document.repository.category")
     * })
     * @param CategoryRepository $categoryRepository
     */
    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function preRemove(LifecycleEventArgs $eventArgs)
    {
        $document = $eventArgs->getDocument();
        if ($document instanceof Group) {
            $this->checkGroupHasNoCategories($document);
        }
    }

    /**
     * @param Group $group
     * @throws GroupNotEmptyException
     */
    protected function checkGroupHasNoCategories(Group $group)
    {
        $count = $this->categoryRepository->countByGroup($group->id);
        if ($count > 0) {
            throw new GroupNotEmptyException('Group is not empty');
        }
    }
}
