<?php
/**
 * Copyright (c) On Tap Networks Limited.
 */
namespace OnTap\CategoryBlockLeftNav\Block\Widget;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Widget\Block\BlockInterface;
use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Api\CategoryManagementInterface;
use Magento\Catalog\Api\Data\CategoryTreeInterface;

class SubCategories extends Template implements BlockInterface
{
    /**
     * @var Resolver
     */
    protected $resolver;

    /**
     * @var CategoryManagementInterface
     */
    protected $categoryManagement;

    public function __construct(
        Template\Context $context,
        CollectionFactory $categoryCollectionFactory,
        Resolver $resolver,
        CategoryManagementInterface $categoryManagement,
        LoggerInterface $logger,
        array $data = []
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->layerResolver = $resolver;
        $this->categoryManagement = $categoryManagement;
        $this->logger = $logger;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getCategoryName()
    {
        return $this->layerResolver->get()->getCurrentCategory()->getName();
    }

    /**
     * @return string
     */
    public function getCategoryUrl()
    {
        return $this->layerResolver->get()->getCurrentCategory()->getUrl();
    }

    /**
     * @return int
     */
    public function getCategoryId()
    {
        return $this->layerResolver->get()->getCurrentCategory()->getId();
    }

    /**
     * @return string
     */
    public function getSubCategoryByCategory()
    {
        return $this->getHtml($this->layerResolver->get()->getCurrentCategory()->getId());
    }

    /**
     * @param int $categoryId
     * @return string
     */
    protected function getHtml($categoryId)
    {
        $html = '';
        $currentCategory = $this->getCategoryData($categoryId);
        $subCategories = $currentCategory->getChildrenData();

        foreach ($subCategories as $subCategory) {
            $html .= '<li><a class="level' . $subCategory->getLevel() . '" href="' . $subCategory->getUrl() . '"><span>' . $this->escapeHtml(
                    $subCategory->getName()
                ) . '</span></a>' . $this->addSubMenu($subCategory) . '</li>';
        }

        return $html;
    }

    /**
     * @param $category
     * @return string
     */
    protected function addSubMenu($category)
    {
        $html = '';
        if (count($category->getChildrenData()) < 1) {
            return '';
        }
        $html = '<ul class="level' . $category->getLevel() . '">';
        $html .= $this->getHtml($category->getId());
        $html .= '</ul>';

        return $html;
    }

    /**
     * @param int $categoryId
     * @return CategoryTreeInterface|null
     */
    protected function getCategoryData(int $categoryId): ?CategoryTreeInterface
    {
        try {
            $getSubCategory = $this->categoryManagement->getTree($categoryId, 4);
        } catch (NoSuchEntityException $e) {
            $this->logger->error("Category not found", [$e]);
            $getSubCategory = null;
        }

        return $getSubCategory;
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function getSubCategories()
    {
        $getSubCategory = $this->categoryManagement->getTree($this->layerResolver->get()->getCurrentCategory()->getId());

        if (count($getSubCategory->getChildrenData()) < 1) {
            return false;
        }

        return true;
    }
}
