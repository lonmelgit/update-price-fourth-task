<?php
namespace Update\Price\Model\Updater;
class MassUpdater
{
    protected $scopeConfig;
    protected $categoryFactory;
    protected $productCollectionFactory;
    protected $action;
    protected $storeManager;
    protected $appState;
    protected $indexerFactory;
    protected $indexerCollectionFactory;
    protected $cacheTypeList;
    protected $cacheFrontendPool;


    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Action $action,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\State $appState,
        \Magento\Indexer\Model\IndexerFactory $indexerFactory,
        \Magento\Indexer\Model\Indexer\CollectionFactory $indexerCollectionFactory,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->categoryFactory = $categoryFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->action = $action;
        $this->storeManager = $storeManager;
        $this->appState = $appState;
        $this->indexerFactory = $indexerFactory;
        $this->indexerCollectionFactory = $indexerCollectionFactory;
        $this->cacheFrontendPool = $cacheFrontendPool;
        $this->cacheTypeList = $cacheTypeList;

    }

    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }


    public function reindexAll()
    {
        $indexer = $this->indexerFactory->create();
        $indexerCollection = $this->indexerCollectionFactory->create();
        $ids = $indexerCollection->getAllIds();
        foreach ($ids as $id) {
            $idx = $indexer->load($id);
            if ($idx->getStatus() != 'valid') {
                $idx->reindexRow($id);
            }
        }
    }

    public function flushCache()
    {
        $types = array('config', 'layout', 'block_html', 'collections', 'reflection', 'db_ddl', 'eav', 'config_integration', 'config_integration_api', 'full_page', 'translate', 'config_webservice');
        foreach ($types as $type) {
            $this->cacheTypeList->cleanType($type);
        }
        foreach ($this->cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
    }


}