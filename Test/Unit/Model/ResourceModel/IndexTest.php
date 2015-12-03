<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\Model\ResourceModel;

use Magento\Elasticsearch\Model\ResourceModel\Index;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class IndexTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Elasticsearch\Model\ResourceModel\Index
     */
    private $model;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRepository;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $categoryRepository;

    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavConfig;

    /**
     * @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fullText;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManager;

    /**
     * @var \Magento\Catalog\Api\Data\ProductInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $product;

    /**
     * @var \Magento\Catalog\Api\Data\CategoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $category;

    /**
     * @var \Magento\Catalog\Api\Data\ProductAttributeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productAttributeInterface;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connection;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $select;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resources;

    /**
     * @var \Magento\Store\Api\Data\StoreInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeInterface;

    /**
     * Setup
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return void
     */
    public function setUp()
    {
        $this->storeManager = $this->getMockBuilder('\Magento\Store\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->setMethods([
                'getStore',
            ])
            ->getMockForAbstractClass();

        $this->storeInterface = $this->getMockBuilder('\Magento\Store\Api\Data\StoreInterface')
            ->disableOriginalConstructor()
            ->setMethods([
                'getWebsiteId',
            ])
            ->getMockForAbstractClass();

        $this->productRepository = $this->getMockBuilder('\Magento\Catalog\Api\ProductRepositoryInterface')
            ->getMockForAbstractClass();

        $this->categoryRepository = $this->getMockBuilder('\Magento\Catalog\Api\CategoryRepositoryInterface')
            ->getMockForAbstractClass();

        $this->eavConfig = $this->getMockBuilder('\Magento\Eav\Model\Config')
            ->disableOriginalConstructor()
            ->setMethods([
                'getEntityAttributeCodes',
                'getAttribute',
            ])
            ->getMock();

        $this->fullText = $this->getMockBuilder('\Magento\CatalogSearch\Model\ResourceModel\Fulltext')
            ->disableOriginalConstructor()
            ->getMock();

        $this->context = $this->getMockBuilder('\Magento\Framework\Model\ResourceModel\Db\Context')
            ->disableOriginalConstructor()
            ->setMethods([
                'getTransactionManager',
                'getResources',
                'getObjectRelationProcessor',
            ])
            ->getMock();

        $this->eventManager = $this->getMockBuilder('\Magento\Framework\Event\ManagerInterface')
            ->setMethods(['dispatch'])
            ->getMock();

        $this->product = $this->getMockBuilder('\Magento\Catalog\Api\Data\ProductInterface')
            ->disableOriginalConstructor()
            ->setMethods([
                'getData',
            ])
            ->getMockForAbstractClass();

        $this->category = $this->getMockBuilder('\Magento\Catalog\Api\Data\CategoryInterface')
            ->disableOriginalConstructor()
            ->setMethods([
                'getName',
            ])
            ->getMockForAbstractClass();

        $this->connection = $this->getMockBuilder('Magento\Framework\DB\Adapter\AdapterInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->select = $this->getMockBuilder('\Magento\Framework\DB\Select')
            ->disableOriginalConstructor()
            ->setMethods([
                'distinct',
                'from',
                'join',
                'where',
                'orWhere',
            ])
            ->getMock();

        $this->resources = $this->getMockBuilder('\Magento\Framework\App\ResourceConnection')
            ->disableOriginalConstructor()
            ->setMethods([
                'getConnection',
                'getTableName',
                'getTablePrefix',
            ])
            ->getMock();

        $this->context->expects($this->any())
            ->method('getResources')
            ->willReturn($this->resources);

        $this->resources->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connection);

        $this->resources->expects($this->any())
            ->method('getTablePrefix')
            ->willReturn('');

        $objectManager = new ObjectManagerHelper($this);
        $this->model = $objectManager->getObject(
            '\Magento\Elasticsearch\Model\ResourceModel\Index',
            [
                'context' => $this->context,
                'storeManager' => $this->storeManager,
                'productRepository' => $this->productRepository,
                'categoryRepository' => $this->categoryRepository,
                'eavConfig' => $this->eavConfig,
                'connectionName' => 'default'
            ]
        );
    }

    /**
     * Test getPriceIndexDataEmpty method wich return empty array
     */
    public function testGetPriceIndexData()
    {
        $connection = $this->connection;
        $select = $this->select;

        $connection->expects($this->any())
            ->method('select')
            ->willReturn($select);

        $select->expects($this->any())
            ->method('from')
            ->willReturnSelf();

        $connection->expects($this->once())
            ->method('fetchAll')
            ->with($select)
            ->willReturn([[
                'website_id' => 1,
                'entity_id' => 1,
                'customer_group_id' => 1,
                'min_price' => 1,
            ]]);

        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeInterface);

        $this->storeInterface->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn(1);

        $this->assertEquals(
            [
                1 => [
                    1 => 1,
                ],
            ],
            $this->model->getPriceIndexData([1 ], 1)
        );
    }

    /**
     * Test getPriceIndexDataEmpty method wich return empty array
     */
    public function testGetPriceIndexDataEmpty()
    {
        $connection = $this->connection;
        $select = $this->select;

        $connection->expects($this->any())
            ->method('select')
            ->willReturn($select);

        $select->expects($this->any())
            ->method('from')
            ->willReturnSelf();

        $connection->expects($this->once())
            ->method('fetchAll')
            ->with($select)
            ->willReturn([]);

        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeInterface);

        $this->storeInterface->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn(1);

        $this->assertEquals(
            [],
            $this->model->getPriceIndexData([1 ], 1)
        );
    }

    /**
     * Test getCategoryProductIndexData method
     */
    public function testGetCategoryProductIndexData()
    {
        $connection = $this->connection;
        $select = $this->select;

        $connection->expects($this->any())
            ->method('select')
            ->willReturn($select);

        $select->expects($this->any())
            ->method('from')
            ->willReturnSelf();

        $select->expects($this->any())
            ->method('where')
            ->willReturnSelf();

        $connection->expects($this->once())
            ->method('fetchAll')
            ->with($select)
            ->willReturn([[
                'product_id' => 1,
                'category_id' => 1,
                'position' => 1,
            ]]);

        $this->assertEquals(
            [
                1 => [
                    1 => 1,
                ],
            ],
            $this->model->getCategoryProductIndexData(1, [1, ])
        );
    }

    /**
     * Test getMovedCategoryProductIds method
     */
    public function testGetMovedCategoryProductIds()
    {
        $connection = $this->connection;
        $select = $this->select;

        $connection->expects($this->any())
            ->method('select')
            ->willReturn($select);

        $select->expects($this->any())
            ->method('distinct')
            ->willReturnSelf();

        $this->resources->expects($this->exactly(2))
            ->method('getTableName');

        $select->expects($this->any())
            ->method('from')
            ->willReturnSelf();

        $select->expects($this->any())
            ->method('join')
            ->willReturnSelf();

        $select->expects($this->any())
            ->method('where')
            ->willReturnSelf();

        $select->expects($this->any())
            ->method('orWhere')
            ->willReturnSelf();

        $connection->expects($this->once())
            ->method('fetchCol')
            ->with($select)
            ->willReturn([1, ]);

        $this->assertEquals([1, ], $this->model->getMovedCategoryProductIds(1));
    }

    /**
     * Test getFullProductIndexData method
     *
     * @dataProvider attributeCodeProvider
     * @param string $frontendInput
     * @return void
     */
    public function testGetFullProductIndexData($frontendInput)
    {
        $this->productRepository->expects($this->once())
            ->method('getById')
            ->willReturn($this->product);

        $this->product->expects($this->once())
            ->method('getData')
            ->willReturn([
                'name' => 'Product Name'
            ]);

        $this->eavConfig->expects($this->once())
            ->method('getEntityAttributeCodes')
            ->with('catalog_product')
            ->willReturn([
                'name',
            ]);

        $attributeMock = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute\AbstractAttribute')
            ->disableOriginalConstructor()
            ->setMethods([
                'getFrontendInput',
                'getOptions'
            ])
            ->getMock();

        $this->eavConfig->expects($this->once())
            ->method('getAttribute')
            ->with('catalog_product', 'name')
            ->willReturn($attributeMock);

        $attributeMock->expects($this->any())
            ->method('getFrontendInput')
            ->willReturn($frontendInput);

        $attributeOption = $this->getMock('\Magento\Eav\Model\Entity\Attribute\Option', [], [], '', false);
        $attributeOption->expects($this->any())->method('getValue')->willReturn('Product Name');
        $attributeOption->expects($this->any())->method('getLabel')->willReturn('label');

        $attributeMock->expects($this->any())
            ->method('getOptions')
            ->willReturn([$attributeOption]);

        $this->assertInternalType(
            'array',
            $this->model->getFullProductIndexData([1])
        );
    }

    /**
     * Test getFullCategoryProductIndexData method
     */
    public function testGetFullCategoryProductIndexData()
    {
        $this->categoryRepository->expects($this->once())
            ->method('get')
            ->willReturn($this->category);

        $this->category->expects($this->once())
            ->method('getName')
            ->willReturn([
                'name' => 'Category Name',
            ]);

        $connection = $this->connection;
        $select = $this->select;

        $connection->expects($this->any())
            ->method('select')
            ->willReturn($select);

        $select->expects($this->any())
            ->method('from')
            ->willReturnSelf();

        $select->expects($this->any())
            ->method('where')
            ->willReturnSelf();

        $connection->expects($this->once())
            ->method('fetchAll')
            ->with($select)
            ->willReturn([[
                'product_id' => 1,
                'category_id' => 1,
                'position' => 1,
            ]]);

        $this->assertInternalType(
            'array',
            $this->model->getFullCategoryProductIndexData([1, [1, ]])
        );
    }

    /**
     * @return array
     */
    public static function attributeCodeProvider()
    {
        return [
            ['string'],
            ['select'],
        ];
    }
}
