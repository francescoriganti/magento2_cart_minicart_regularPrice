<?php

namespace Vendor\Module\Block\Item\Price;

use Magento\Quote\Model\Quote\Item\AbstractItem;


class Renderer extends \Magento\Framework\View\Element\Template
{
    protected $customCustomerData;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Vendor\Module\Plugin\Checkout\CustomerData\Cart $customCustomerData,
        array $data = []
    ) {

        $this->customCustomerData = $customCustomerData;
        parent::__construct($context, $data);
    }


    /**
     * @param $sku
     * @return \Vendor\Module\Plugin\Checkout\CustomerData\Cart
     */
    public function getPriceBySku($sku){
        return $this->customCustomerData->getPriceBySku($sku);
    }

    /**
     * @param $sku
     * @return \Vendor\Module\Plugin\Checkout\CustomerData\Cart
     */
    public function getIsSpecialPrice($sku){
        return $this->customCustomerData->getIsSpecialPrice($sku);
    }


}
