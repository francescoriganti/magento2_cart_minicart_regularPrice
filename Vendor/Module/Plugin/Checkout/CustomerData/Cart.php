<?php

namespace Vendor\Module\Plugin\Checkout\CustomerData;


class Cart
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    protected $checkoutHelper;

    /**
     * @var \Magento\Tax\Block\Item\Price\Renderer
     */
    protected $itemPriceRenderer;

    /**
     * @var \Magento\Quote\Model\Quote|null
     */
    protected $quote = null;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @var array|null
     */
    protected $totals = null;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param \Magento\Tax\Block\Item\Price\Renderer $itemPriceRenderer
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Tax\Block\Item\Price\Renderer $itemPriceRenderer,
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->checkoutHelper = $checkoutHelper;
        $this->itemPriceRenderer = $itemPriceRenderer;
        $this->productFactory = $productFactory;
    }

    /**
     * Add tax and regular price data to result
     *
     * @param \Magento\Checkout\CustomerData\Cart $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetSectionData(\Magento\Checkout\CustomerData\Cart $subject, $result)
    {
        $items =$this->getQuote()->getAllVisibleItems();
        if (is_array($result['items'])) {
            foreach ($result['items'] as $key => $itemAsArray) {
                if ($item = $this->findItemById($itemAsArray['item_id'], $items)) {
                    $this->itemPriceRenderer->setItem($item);
                    $this->itemPriceRenderer->setTemplate('checkout/cart/item/price/sidebar.phtml');
                    $result['items'][$key]['product_price']=$this->itemPriceRenderer->toHtml();
                    $result['items'][$key]['product_original_price_value'] = $item->getPrice();
                    $result['items'][$key]['product_data'] = $item->getData();
                    if( $this->getIsSpecialPrice($item->getSku())){
                        $result['items'][$key]['product_price_by_id'] = $this->getPriceBySku($item->getSku());
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @param $sku
     * @return mixed
     */
    public function getPriceBySku($sku)
    {
        $product = $this->productFactory->create();
        $productPriceBySku = $product->loadByAttribute('sku', $sku)->getPrice();
       $formattedproductPriceBySku = $this->checkoutHelper->formatPrice($productPriceBySku);
        return $formattedproductPriceBySku;
    }

    /**
     * @param $sku
     * @return boolean
     */
    public function getIsSpecialPrice($sku){
        $_product = $this->productFactory->create()->loadByAttribute('sku', $sku);
        $orgprice = $_product->getPrice();
        $specialprice = $_product->getSpecialPrice();
        $specialfromdate = $_product->getSpecialFromDate();
        $specialtodate = $_product->getSpecialToDate();
        $today = time();
        if (!$specialprice)
            $specialprice = $orgprice;
        if ($specialprice< $orgprice) {
            if ((is_null($specialfromdate) &&is_null($specialtodate)) || ($today >= strtotime($specialfromdate) &&is_null($specialtodate)) || ($today <= strtotime($specialtodate) &&is_null($specialfromdate)) || ($today >= strtotime($specialfromdate) && $today <= strtotime($specialtodate))) {
                return 1;
            }
        }
        return 0;
    }

    /**
     * Get active quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    protected function getQuote()
    {
        if (null === $this->quote) {
            $this->quote = $this->checkoutSession->getQuote();
        }
        return $this->quote;
    }

    /**
     * Find item by id in items haystack
     *
     * @param int $id
     * @param array $itemsHaystack
     * @return \Magento\Quote\Model\Quote\Item | bool
     */
    protected function findItemById($id, $itemsHaystack)
    {
        if (is_array($itemsHaystack)) {
            foreach ($itemsHaystack as $item) {
                /** @var $item \Magento\Quote\Model\Quote\Item */
                if ((int)$item->getItemId() == $id) {
                    return $item;
                }
            }
        }
        return false;
    }
}
