<?php

namespace Update\Price\Cron;


class TaskUpdate extends \Magento\Framework\View\Element\Template


{
    protected $scopeConfig;
    protected $orderCollectionFactory;
    protected $action;
    protected $massUpdater;
    protected $orderFactory;
    protected $productRepository;


    public function __construct(
        //\Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\ResourceModel\Product\Action $action,
        \Update\Price\Model\Updater\MassUpdater $massUpdater,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory


    ) {

        $this->scopeConfig =$scopeConfig;
        $this->action = $action;
        $this->massUpdater = $massUpdater;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderFactory = $orderFactory;
        $this->productFactory = $productFactory;

        //parent::__construct($context);

    }

    public function execute() {

        $items = array();

        $collection = $this->orderCollectionFactory->create()
            ->addAttributeToSelect('*');

        $from = date("Y-m-d H:i:s", time() - 12 * 60 * 60); // start date
        $to = date("Y-m-d H:i:s", time()); // end date

        $collection->addFieldToFilter('created_at',
                ['gteq' => $from]
            )
            ->addFieldToFilter('created_at',
                ['lteq' => $to]
            );

            foreach ($collection as $order) {

                $order = $this->orderFactory->create()->load($order->getId());


                $orderItems = $order->getAllItems();
                foreach ($orderItems as $item)
                {

                    if ( $item->getProductType() == 'configurable' ) {
                        continue;
                    }
                    $productId = $item->getProductId();

                    if(isset($items[$productId])) {
                        $items[$productId] += (int)$item->getQtyOrdered();

                    } else {
                        $items[$productId] = (int)$item->getQtyOrdered();
                    }
                }


            }

            print_r($items);
            if(!empty($items)) {

                $coreQtyValue = $this->scopeConfig->getValue(
                    'updateprice/general/threshold_value',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );

                foreach($items as $productId => $qty) {

                    $product = $this->productFactory->create()->load($productId);

                    $price = $product->getPrice();

                    echo $price;

                    if($qty > $coreQtyValue) {

                        $newPrice = round(($price + ($price / 100) * 2),2);

                        $this->action->updateAttributes([$product->getId()], ['price' => $newPrice], $this->massUpdater->getStoreId());

                    } else {
                        $newPrice = round(($price - ($price / 100) * 3),2);

                        $this->action->updateAttributes([$product->getId()], ['price' => $newPrice], $this->massUpdater->getStoreId());
                    }

                    $this->massUpdater->flushCache();
                    $this->massUpdater->reindexAll();

                    echo "--".$newPrice;

                }

            }

        return $collection;

    }



