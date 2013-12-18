<?php

namespace Lighthouse\CoreBundle\Document\Report\GrossSales;

use Doctrine\ODM\MongoDB\Cursor;
use Lighthouse\CoreBundle\Document\AbstractCollection;
use Lighthouse\CoreBundle\Document\Classifier\AbstractNode;
use Lighthouse\CoreBundle\Document\Classifier\Category\Category;
use Lighthouse\CoreBundle\Document\Classifier\Category\CategoryCollection;
use Lighthouse\CoreBundle\Document\Classifier\Category\CategoryRepository;
use Lighthouse\CoreBundle\Document\Classifier\Group\Group;
use Lighthouse\CoreBundle\Document\Classifier\Group\GroupCollection;
use Lighthouse\CoreBundle\Document\Classifier\Group\GroupRepository;
use Lighthouse\CoreBundle\Document\Classifier\SubCategory\SubCategory;
use Lighthouse\CoreBundle\Document\Classifier\SubCategory\SubCategoryCollection;
use Lighthouse\CoreBundle\Document\Classifier\SubCategory\SubCategoryRepository;
use Lighthouse\CoreBundle\Document\Product\ProductRepository;
use Lighthouse\CoreBundle\Document\Product\Store\StoreProductCollection;
use Lighthouse\CoreBundle\Document\Product\Store\StoreProductRepository;
use Lighthouse\CoreBundle\Document\Report\GrossSales\Category\GrossSalesCategoryRepository;
use Lighthouse\CoreBundle\Document\Report\GrossSales\GrossSales\DayGrossSales;
use Lighthouse\CoreBundle\Document\Report\GrossSales\GrossSales\GrossSales;
use Lighthouse\CoreBundle\Document\Report\GrossSales\GrossSalesByCategories\GrossSalesByCategoriesCollection;
use Lighthouse\CoreBundle\Document\Report\GrossSales\GrossSalesByGroups\GrossSalesByGroupsCollection;
use Lighthouse\CoreBundle\Document\Report\GrossSales\GrossSalesByProducts\GrossSalesByProductsCollection;
use Lighthouse\CoreBundle\Document\Report\GrossSales\GrossSalesBySubCategories\GrossSalesBySubCategoriesCollection;
use Lighthouse\CoreBundle\Document\Report\GrossSales\GrossSalesByStores\GrossSalesByStoresCollection;
use Lighthouse\CoreBundle\Document\Report\GrossSales\GrossSalesByStores\StoreGrossSalesByStores;
use Lighthouse\CoreBundle\Document\Report\GrossSales\Product\GrossSalesProductReport;
use Lighthouse\CoreBundle\Document\Report\GrossSales\Product\GrossSalesProductRepository;
use Lighthouse\CoreBundle\Document\Report\GrossSales\SubCategory\GrossSalesSubCategoryRepository;
use Lighthouse\CoreBundle\Document\Report\Store\StoreGrossSalesReport;
use Lighthouse\CoreBundle\Document\Report\Store\StoreGrossSalesRepository;
use Lighthouse\CoreBundle\Document\Store\Store;
use Lighthouse\CoreBundle\Document\Store\StoreRepository;
use Lighthouse\CoreBundle\Document\TrialBalance\TrialBalanceRepository;
use Lighthouse\CoreBundle\Types\Numeric\Money;
use Lighthouse\CoreBundle\Types\Date\DateTimestamp;
use JMS\DiExtraBundle\Annotation as DI;
use DateTime;

/**
 * @DI\Service("lighthouse.core.document.report.gross_sales.manager")
 */
class GrossSalesReportManager
{
    /**
     * @var StoreGrossSalesRepository
     */
    protected $grossSalesRepository;

    /**
     * @var GrossSalesProductRepository
     */
    protected $grossSalesProductRepository;

    /**
     * @var GrossSalesSubCategoryRepository
     */
    protected $grossSalesSubCategoryRepository;

    /**
     * @var GrossSalesCategoryRepository
     */
    protected $grossSalesCategoryRepository;

    /**
     * @var StoreRepository
     */
    protected $storeRepository;

    /**
     * @var TrialBalanceRepository
     */
    protected $trialBalanceRepository;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var StoreProductRepository
     */
    protected $storeProductRepository;

    /**
     * @var SubCategoryRepository
     */
    protected $subCategoryRepository;

    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var GroupRepository
     */
    protected $groupRepository;

    /**
     * @var GrossSalesProductReport[]
     */
    protected $scheduledGrossSalesProductReportsToSave = array();

    /**
     * @DI\InjectParams({
     *      "grossSalesRepository" = @DI\Inject("lighthouse.core.document.repository.store_gross_sales"),
     *      "grossSalesProductRepository" = @DI\Inject("lighthouse.core.document.repository.product_gross_sales"),
     *      "grossSalesSubCategoryRepository" =
     *      @DI\Inject("lighthouse.core.document.repository.subcategory_gross_sales"),
     *      "grossSalesCategoryRepository" = @DI\Inject("lighthouse.core.document.repository.category_gross_sales"),
     *      "storeRepository" = @DI\Inject("lighthouse.core.document.repository.store"),
     *      "trialBalanceRepository" = @DI\Inject("lighthouse.core.document.repository.trial_balance"),
     *      "productRepository" = @DI\Inject("lighthouse.core.document.repository.product"),
     *      "storeProductRepository" = @DI\Inject("lighthouse.core.document.repository.store_product"),
     *      "subCategoryRepository" = @DI\Inject("lighthouse.core.document.repository.classifier.subcategory"),
     *      "categoryRepository" = @DI\Inject("lighthouse.core.document.repository.classifier.category"),
     *      "groupRepository" = @DI\Inject("lighthouse.core.document.repository.classifier.group"),
     * })
     * @param StoreGrossSalesRepository $grossSalesRepository
     * @param GrossSalesProductRepository $grossSalesProductRepository
     * @param GrossSalesSubCategoryRepository $grossSalesSubCategoryRepository
     * @param GrossSalesCategoryRepository $grossSalesCategoryRepository
     * @param StoreRepository $storeRepository
     * @param TrialBalanceRepository $trialBalanceRepository
     * @param ProductRepository $productRepository
     * @param StoreProductRepository $storeProductRepository
     * @param SubCategoryRepository $subCategoryRepository
     * @param CategoryRepository $categoryRepository
     * @param GroupRepository $groupRepository
     */
    public function __construct(
        StoreGrossSalesRepository $grossSalesRepository,
        GrossSalesProductRepository $grossSalesProductRepository,
        GrossSalesSubCategoryRepository $grossSalesSubCategoryRepository,
        GrossSalesCategoryRepository $grossSalesCategoryRepository,
        StoreRepository $storeRepository,
        TrialBalanceRepository $trialBalanceRepository,
        ProductRepository $productRepository,
        StoreProductRepository $storeProductRepository,
        SubCategoryRepository $subCategoryRepository,
        CategoryRepository $categoryRepository,
        GroupRepository $groupRepository
    ) {
        $this->grossSalesRepository = $grossSalesRepository;
        $this->grossSalesProductRepository = $grossSalesProductRepository;
        $this->grossSalesSubCategoryRepository = $grossSalesSubCategoryRepository;
        $this->grossSalesCategoryRepository = $grossSalesCategoryRepository;
        $this->storeRepository = $storeRepository;
        $this->trialBalanceRepository = $trialBalanceRepository;
        $this->productRepository = $productRepository;
        $this->storeProductRepository = $storeProductRepository;
        $this->subCategoryRepository = $subCategoryRepository;
        $this->categoryRepository = $categoryRepository;
        $this->groupRepository = $groupRepository;
    }

    /**
     * @param DateTime $time
     * @return GrossSales
     */
    public function getGrossSales(DateTime $time = null)
    {
        $intervals = array(
            'yesterday' => '-1 day 23:00',
            'twoDaysAgo' => '-2 days 23:00',
            'eightDaysAgo' => '-8 days 23:00',
        );
        $dates = $this->getDates($time, $intervals);
        $storeDayReports = $this->grossSalesRepository->findByDates($dates);
        $grossSales = $this->createGrossSales($storeDayReports, $dates);
        $this->fillGrossSales($grossSales, $dates);
        return $grossSales;
    }

    /**
     * @param Cursor|StoreGrossSalesReport[] $storeDayReports
     * @param DateTimestamp[] $dates
     * @return GrossSales
     */
    protected function createGrossSales(Cursor $storeDayReports, array $dates)
    {
        $grossSales = new GrossSales();
        foreach ($storeDayReports as $storeDayReport) {
            foreach ($dates as $key => $date) {
                if ($date->equals($storeDayReport->dayHour)) {
                    if (!isset($grossSales->$key)) {
                        $grossSales->$key = new DayGrossSales($date);
                    }
                    /* @var DayGrossSales $dayGrossSales */
                    $dayGrossSales = $grossSales->$key;
                    $dayGrossSales->addRunningSum($storeDayReport->runningSum);
                    $dayGrossSales->addHourSum($storeDayReport->hourSum);
                }
            }
        }
        return $grossSales;
    }

    /**
     * @param GrossSales $grossSales
     * @param DateTimestamp[] $dates
     */
    protected function fillGrossSales(GrossSales $grossSales, array $dates)
    {
        foreach ($dates as $key => $date) {
            if (!isset($grossSales->$key)) {
                $grossSales->$key = new DayGrossSales($date);
            }
        }
    }

    /**
     * @param DateTime|string $time
     * @return GrossSalesByStoresCollection
     */
    public function getGrossSalesByStores(DateTime $time = null)
    {
        $intervals = array(
            'yesterday' => '-1 day 23:00',
            'twoDaysAgo' => '-2 days 23:00',
            'eightDaysAgo' => '-8 days 23:00',
        );
        $dates = $this->getDates($time, $intervals);
        $storeDayReports = $this->grossSalesRepository->findByDates($dates);
        $storeDayReports->sort(array('store' => 1));
        /* @var Store[]|Cursor $stores */
        $stores = $this->storeRepository->findAll();
        $stores->sort(array('id' => 1));
        $storeReports = $this->createGrossSalesByStoresCollection($storeDayReports, $dates);
        $this->fillGrossSalesByStoresCollection($storeReports, $stores, $dates);
        return $storeReports;
    }

    /**
     * @param DateTime|string $time
     * @param array $intervals
     * @return DateTimestamp[]
     */
    protected function getDates($time, array $intervals)
    {
        $dateTime = new DateTimestamp($time);
        $dates = array();
        foreach ($intervals as $key => $interval) {
            $nextDateTime = clone $dateTime;
            $dates[$key] = $nextDateTime->modify($interval);
        }
        return $dates;
    }

    /**
     * @param DateTime|string|null $time
     * @param array $intervals
     * @return DateTimestamp[]
     */
    protected function getDayHours($time, array $intervals)
    {
        $dateTime = new DateTimestamp($time);
        $dateTime->setMinutes(0)->setSeconds(0);
        $dayHours = array();
        foreach ($intervals as $key => $interval) {
            $nextDateTime = clone $dateTime;
            if (null !== $interval) {
                $nextDateTime->modify($interval);
            }
            for ($hour = 0; $hour <= $nextDateTime->getHours(); $hour++) {
                $nextDayHour = clone $nextDateTime;
                $dayHours[$key][$hour] = $nextDayHour->setHours($hour);
            }
        }
        return $dayHours;
    }

    /**
     * @param Cursor|StoreGrossSalesReport[] $storeDayReports
     * @param DateTimestamp[] $dates
     * @return StoreGrossSalesByStores[]|GrossSalesByStoresCollection
     */
    protected function createGrossSalesByStoresCollection(Cursor $storeDayReports, array $dates)
    {
        $storeReports = new GrossSalesByStoresCollection();
        /* @var StoreGrossSalesReport $storeDayReport */
        foreach ($storeDayReports as $storeDayReport) {
            $storeReport = $storeReports->getByStore($storeDayReport->store);
            foreach ($dates as $key => $date) {
                if ($date->equals($storeDayReport->dayHour)) {
                    $storeReport->$key = $storeDayReport;
                }
            }
        }
        return $storeReports;
    }

    /**
     * @param GrossSalesByStoresCollection $grossSalesByStores
     * @param Cursor|Store[] $stores
     * @param DateTime[] $dates
     */
    protected function fillGrossSalesByStoresCollection(
        GrossSalesByStoresCollection $grossSalesByStores,
        Cursor $stores,
        array $dates
    ) {
        foreach ($stores as $store) {
            $storeReport = $grossSalesByStores->getByStore($store);
            foreach ($dates as $key => $date) {
                if (!isset($storeReport->$key)) {
                    $storeReport->$key = $this->grossSalesRepository->createByDayHourAndStore($date, $store);
                }
            }
        }
    }

    /**
     * @return int
     */
    public function recalculateStoreGrossSalesReport()
    {
        $results = $this->trialBalanceRepository->calculateGrossSales();
        $dm = $this->grossSalesRepository->getDocumentManager();
        foreach ($results as $result) {
            $storeId = $result['_id']['store'];
            $day = DateTimestamp::createFromMongoDate($result['_id']['day']);
            foreach ($result['value'] as $hour => $grossSales) {
                $dayHour = clone $day;
                $dayHour->setTime($hour, 0);
                $report = $this->grossSalesRepository->createByDayHourAndStoreId(
                    $dayHour,
                    (string) $storeId,
                    new Money($grossSales['runningSum']),
                    new Money($grossSales['hourSum'])
                );
                $dm->persist($report);
            }
        }
        $dm->flush();

        return count($results);
    }

    /**
     * @param int $batch
     * @return int
     */
    public function recalculateGrossSalesProductReport($batch = 1000)
    {
        $stores = $this->storeRepository->findAll()->toArray();
        $countProducts = $this->productRepository->findAll()->count();

        $results = $this->trialBalanceRepository->calculateGrossSalesProduct($stores, $countProducts);

        $countResultsSaved = 0;
        $batchCount = 0;
        $batchStartTime = microtime(true);
        $totalSaveTime = 0;
        foreach ($results['reports'] as $reports) {
            foreach ($reports as $reportRAW) {

                $storeProductId = $reportRAW['_id']['storeProduct'];
                $year = $reportRAW['_id']['year'];
                $month = $reportRAW['_id']['month'];
                $day = $reportRAW['_id']['day'];
                $hour = $reportRAW['_id']['hour'];
                $dayHour = $this->createUTCDateByYMDH($year, $month, $day, $hour);

                $report = $this->grossSalesProductRepository->createByDayHourAndStoreProductId(
                    $dayHour,
                    $storeProductId,
                    null,
                    new Money($reportRAW['hourSum'])
                );

                $this->scheduleGrossSalesProductReportToSave($report);

                $countResultsSaved++;
                $batchCount++;
                if (0 == $countResultsSaved % $batch) {
                    $this->saveScheduledReports();
                    $batchDurationTime = microtime(true) - $batchStartTime;
                    $totalSaveTime += $batchDurationTime;
//                    printf(
//                        "Скорость: %6.2f отчётов в секунду, средняя: %6.2f, всего сохранено: %6d, осталось: %6d \n",
//                        $batchCount / $batchDurationTime,
//                        $countResultsSaved / $totalSaveTime,
//                        $countResultsSaved,
//                        $countResults - $countResultsSaved
//                    );
                    $batchCount = 0;
                    $batchStartTime = microtime(true);
                }
            }
        }

        $this->saveScheduledReports();

        return $results['totalCount'];
    }

    /**
     * @param GrossSalesProductReport $report
     */
    protected function scheduleGrossSalesProductReportToSave(GrossSalesProductReport $report)
    {
        $this->scheduledGrossSalesProductReportsToSave[] = $report;
    }

    /**
     *
     */
    protected function saveScheduledReports()
    {
        if (count($this->scheduledGrossSalesProductReportsToSave)) {
            $this->grossSalesProductRepository->rawUpsertReports($this->scheduledGrossSalesProductReportsToSave);
            $this->scheduledGrossSalesProductReportsToSave = array();
        }
    }

    /**
     * @param int $year
     * @param int $month
     * @param int $day
     * @param int $hour
     * @return DateTimestamp
     */
    protected function createUTCDateByYMDH($year, $month, $day, $hour)
    {
        $dateString = sprintf(
            "%d-%d-%dT%d:00:00Z",
            $year,
            $month,
            $day,
            $hour
        );

        return new DateTimestamp($dateString);
    }

    /**
     * @param Store $store
     * @param SubCategory $subCategory
     * @param DateTime|null $time
     * @return GrossSalesByProductsCollection
     */
    public function getGrossSalesByProducts(Store $store, SubCategory $subCategory, DateTime $time = null)
    {
        $intervals = array(
            'today' => null,
            'yesterday' => '-1 days',
            'weekAgo' => '-7 days',
        );

        $dayHours = $this->getDayHours($time, $intervals);
        $endDayHours = $this->extractEndDayHours($dayHours);
        $queryDates = $this->getQueryDates($dayHours);

        $storeProducts = $this->storeProductRepository->findByStoreSubCategory($store, $subCategory);

        $reports = $this->grossSalesProductRepository->findByDayHoursStoreProducts(
            $queryDates,
            $storeProducts->getIds()
        );

        $grossSalesByProductCollection = $this->createGrossSalesByProductsCollection($reports, $endDayHours);

        $this->fillGrossSalesByProductsCollection(
            $grossSalesByProductCollection,
            $storeProducts,
            $endDayHours
        );

        return $grossSalesByProductCollection->normalizeKeys();
    }

    /**
     * @param Cursor $reports
     * @param DateTimestamp[] $endDayHours
     * @return GrossSalesByProductsCollection
     */
    protected function createGrossSalesByProductsCollection(Cursor $reports, array $endDayHours)
    {
        $collection = new GrossSalesByProductsCollection();
        /** @var GrossSalesProductReport $report */
        foreach ($reports as $report) {
            $storeProductReport = $collection->getByStoreProduct($report->product, $endDayHours);
            foreach ($endDayHours as $dayName => $dayHour) {
                if ($dayHour->equalsDate($report->dayHour)) {
                    $storeProductReport->$dayName->addRunningSum($report->hourSum);
                    break;
                }
            }
        }

        return $collection;
    }

    /**
     * @param GrossSalesByProductsCollection $collection
     * @param StoreProductCollection $storeProducts
     * @param DateTime[] $endDayHours
     * @return GrossSalesByProductsCollection
     */
    public function fillGrossSalesByProductsCollection(
        GrossSalesByProductsCollection $collection,
        StoreProductCollection $storeProducts,
        array $endDayHours
    ) {
        foreach ($storeProducts as $storeProduct) {
            if (!$collection->containsStoreProduct($storeProduct)) {
                $collection->createByStoreProduct($storeProduct, $endDayHours);
            }
        }

        return $collection;
    }

    /**
     * @param Store $store
     * @param Category $category
     * @param DateTime|null $time
     * @return GrossSalesBySubCategoriesCollection
     */
    public function getGrossSalesBySubCategories(Store $store, Category $category, DateTime $time = null)
    {
        $intervals = array(
            'today' => null,
            'yesterday' => '-1 days',
            'weekAgo' => '-7 days',
        );

        $dayHours = $this->getDayHours($time, $intervals);
        $endDayHours = $this->extractEndDayHours($dayHours);
        $queryDates = $this->getQueryDates($dayHours);

        $cursor = $this->subCategoryRepository->findByCategory($category->id);
        $cursor->sort(array('name' => 1));
        $subCategories = new SubCategoryCollection($cursor);

        $reports = $this->grossSalesSubCategoryRepository->findByDayHoursAndSubCategoryIds(
            $queryDates,
            $subCategories->getIds(),
            $store->id
        );

        $collection = new GrossSalesBySubCategoriesCollection();

        $this->createGrossSalesByClassifierNodeCollection($collection, $reports, $endDayHours);
        $this->fillGrossSalesByClassifierNodeCollection($collection, $subCategories, $endDayHours);

        return $collection->normalizeKeys();
    }

    /**
     * @param Store $store
     * @param Group $group
     * @param DateTime|null $time
     * @return GrossSalesByCategoriesCollection
     */
    public function getGrossSalesByCategories(Store $store, Group $group, DateTime $time = null)
    {
        $intervals = array(
            'today' => null,
            'yesterday' => '-1 days',
            'weekAgo' => '-7 days',
        );

        $dayHours = $this->getDayHours($time, $intervals);
        $endDayHours = $this->extractEndDayHours($dayHours);
        $queryDates = $this->getQueryDates($dayHours);

        $cursor = $this->categoryRepository->findByGroup($group->id);
        $cursor->sort(array('name' => 1));
        $categories = new CategoryCollection($cursor);

        $reports = $this->grossSalesCategoryRepository->findByDayHoursAndCategoryIds(
            $queryDates,
            $categories->getIds(),
            $store->id
        );

        $grossSalesByCategoriesCollection = new GrossSalesByCategoriesCollection();

        $this->createGrossSalesByClassifierNodeCollection(
            $grossSalesByCategoriesCollection,
            $reports,
            $endDayHours
        );

        $this->fillGrossSalesByClassifierNodeCollection(
            $grossSalesByCategoriesCollection,
            $categories,
            $endDayHours
        );

        return $grossSalesByCategoriesCollection->normalizeKeys();
    }


    /**
     * @param GrossSalesByClassifierNodeCollection $collection
     * @param Cursor|GrossSalesClassifierNodeReport[] $reports
     * @param DateTimestamp[] $endDayHours
     * @return GrossSalesBySubCategoriesCollection
     */
    protected function createGrossSalesByClassifierNodeCollection(
        GrossSalesByClassifierNodeCollection $collection,
        Cursor $reports,
        array $endDayHours
    ) {
        foreach ($reports as $report) {
            $categoryReport = $collection->getByNode($report->getNode(), $endDayHours);
            foreach ($endDayHours as $dayName => $dayHour) {
                if ($dayHour->equalsDate($report->dayHour)) {
                    $categoryReport->$dayName->addRunningSum($report->hourSum);
                    break;
                }
            }
        }

        return $collection;
    }

    /**
     * @param Store $store
     * @param DateTime|null $time
     * @return GrossSalesByGroupsCollection
     */
    public function getGrossSalesByGroups(Store $store, DateTime $time = null)
    {
        $intervals = array(
            'today' => null,
            'yesterday' => '-1 days',
            'weekAgo' => '-7 days',
        );

        $dayHours = $this->getDayHours($time, $intervals);
        $endDayHours = $this->extractEndDayHours($dayHours);

        $cursor = $this->groupRepository->findAll();
        $cursor->sort(array('name' => 1));
        $groups = new GroupCollection($cursor);

        $grossSalesByGroupsCollection = new GrossSalesByGroupsCollection();

        $this->fillGrossSalesByClassifierNodeCollection(
            $grossSalesByGroupsCollection,
            $groups,
            $endDayHours
        );

        return $grossSalesByGroupsCollection->normalizeKeys();
    }

    /**
     * @param GrossSalesByClassifierNodeCollection $collection
     * @param AbstractNode[]|AbstractCollection $nodes
     * @param DateTime[] $dates
     */
    public function fillGrossSalesByClassifierNodeCollection(
        GrossSalesByClassifierNodeCollection $collection,
        AbstractCollection $nodes,
        array $dates
    ) {
        foreach ($nodes as $node) {
            if (!$collection->containsKey($node->id)) {
                $collection->createByNode($node, $dates);
            }
        }
    }

    /**
     * @param array $dayHours
     * @return DateTimestamp[]
     */
    protected function extractEndDayHours(array $dayHours)
    {
        $endDayHours = array();
        foreach ($dayHours as $key => $dayHoursArray) {
            $endDayHours[$key] = max($dayHoursArray);
        }
        return $endDayHours;
    }

    /**
     * @param array $dayHours
     * @return DateTimestamp[]
     */
    public function getQueryDates(array $dayHours)
    {
        return call_user_func_array('array_merge', $dayHours);
    }

    /**
     * @param int $batch
     * @return int
     */
    public function recalculateGrossSalesBySubCategories($batch = 1000)
    {
        $subCategoryIds = $this->subCategoryRepository->findAllIds();
        $storeIds = $this->storeRepository->findAllIds();

        $dm = $this->grossSalesProductRepository->getDocumentManager();
        $i = 0;

        foreach ($subCategoryIds as $subCategoryId) {
            $productIds = $this->productRepository->findIdsBySubCategoryId($subCategoryId);
            foreach ($storeIds as $storeId) {
                $storeProductIds = array();
                foreach ($productIds as $productId) {
                    $storeProductIds[] = $this->storeProductRepository->getIdByStoreIdAndProductId(
                        $storeId,
                        $productId
                    );
                }
                $hourSums = $this->grossSalesProductRepository->calculateGrossSalesByIds($storeProductIds);
                foreach ($hourSums as $hourSum) {
                    $report = $this->grossSalesSubCategoryRepository->createByDayHourAndSubCategoryId(
                        DateTimestamp::createFromMongoDate($hourSum['_id']),
                        (string) $subCategoryId,
                        (string) $storeId,
                        new Money($hourSum['hourSum'])
                    );
                    $dm->persist($report);
                    if (0 == ++$i % $batch) {
                        $dm->flush();
                        $dm->clear();
                    }
                }
            }
        }

        $dm->flush();
        $dm->clear();

        return $i;
    }

    /**
     * @param int $batch
     * @return int
     */
    public function recalculateGrossSalesByCategories($batch = 1000)
    {
        $categoryIds = $this->categoryRepository->findAllIds();
        $storeIds = $this->storeRepository->findAllIds();

        $dm = $this->grossSalesSubCategoryRepository->getDocumentManager();
        $i = 0;

        foreach ($categoryIds as $categoryId) {
            $subCategoryIds = $this->subCategoryRepository->findIdsByCategoryId($categoryId);
            foreach ($storeIds as $storeId) {
                $hourSums = $this->grossSalesSubCategoryRepository->calculateGrossSalesByIds($subCategoryIds);
                foreach ($hourSums as $hourSum) {
                    $report = $this->grossSalesCategoryRepository->createByDayHourAndCategoryId(
                        DateTimestamp::createFromMongoDate($hourSum['_id']),
                        (string) $categoryId,
                        (string) $storeId,
                        new Money($hourSum['hourSum'])
                    );
                    $dm->persist($report);
                    if (0 == ++$i % $batch) {
                        $dm->flush();
                        $dm->clear();
                    }
                }
            }
        }

        $dm->flush();
        $dm->clear();

        return $i;
    }
}
