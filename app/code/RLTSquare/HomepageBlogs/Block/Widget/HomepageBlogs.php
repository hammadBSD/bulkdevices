<?php
declare(strict_types=1);

namespace RLTSquare\HomepageBlogs\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Widget\Block\BlockInterface;
use Mageplaza\Blog\Model\ResourceModel\Post\CollectionFactory as PostCollectionFactory;

class HomepageBlogs extends Template implements BlockInterface
{
    /**
     * Default value for Blogs count that will be shown
     */
    const DEFAULT_BLOG_COUNT = 5;
    /**
     * @var string
     */
    protected string $template = 'widget/homepageblogs.phtml';

    /**
     * @var PostCollectionFactory
     */
    private PostCollectionFactory $postCollectionFactory;

    /**
     * @param Context $context
     * @param PostCollectionFactory $postCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context           $context,
        PostCollectionFactory $postCollectionFactory,
        array             $data = []
    ) {
        parent::__construct($context, $data);
        $this->postCollectionFactory = $postCollectionFactory;
    }

    /**
     * get featured product collection
     */
    public function getBlogsData()
    {
        $limit = $this->getBlogsLimit();

        $postsCollection = $this->postCollectionFactory->create()
            ->addFieldToFilter('enabled', '1')
            ->setOrder('created_at', 'DESC')
            ->setPageSize($limit);
        return $postsCollection->getData();
    }
    /**
     * Get the configured limit of products
     * @return int
     */
    public function getBlogsLimit()
    {
        if ($this->getData('blogsCount') === '') {
            return self::DEFAULT_BLOG_COUNT;
        }
        return $this->getData('blogsCount');
    }
}
