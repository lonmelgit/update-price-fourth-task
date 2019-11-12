<?php

namespace Update\Price\Cron;


class TaskUpdate extends \Magento\Framework\View\Element\Template


{
    protected $_orderCollectionFactory;

    protected $_helper;

    public function __construct(
        //\Magento\Framework\App\Action\Context $context,
        \Update\Price\Helper\Data $_helper,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory

    ) {

        $this->_helper=$_helper;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        //parent::__construct($context);

    }


    public function execute() {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $items = array();

        $collection = $this->_orderCollectionFactory->create()
            ->addAttributeToSelect('*');

        $from = date("Y-m-d H:i:s", time() - 12 * 60 * 60); // start date
        $to = date("Y-m-d H:i:s", time()); // end date


        //echo $to.'--'.$from; exit;
        $collection->addFieldToFilter('created_at',
                ['gteq' => $from]
            )
            ->addFieldToFilter('created_at',
                ['lteq' => $to]
            );

            foreach ($collection as $order) {

                $order->getId().'  ';

                $order = $objectManager->create('Magento\Sales\Model\Order')->load($order->getId());
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

                $scopeConfig = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');

                $coreQtyValue = $scopeConfig->getValue(
                    'updateprice/general/threshold_value',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );

                foreach($items as $productId => $qty) {

                    $product = $objectManager->create('Magento\Catalog\Model\Product')->load($productId);
                    $price = $product->getPrice();

                    echo $price;

                    if($qty > $coreQtyValue) {

                        $price = round(($price + ($price / 100) * 2),2);
                        //echo "More";
                    } else {
                        $price = round(($price - ($price / 100) * 3),2);

                       // echo "less";
                    }

                    $this->_helper->updatePrices($productId, $price);
                    echo "--".$price;

                }

            }

        return $collection;

    }


}



