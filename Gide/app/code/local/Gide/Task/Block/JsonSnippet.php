<?php


/**
 *
 *
 * @author      dhiraj <dhirajmetal@gmail.com>
 */
class Gide_Task_Block_JsonSnippet extends Mage_Core_Block_Template
{

    /**
     * @var Mage_Catalog_Model_Product
     */
    protected $product;

    public function getCurrentProduct()
    {

        if (empty($this->product)) {
            $this->product = Mage::registry('current_product');
        }

        return $this->product;
    }


    public function getJsonSnippet()
    {
        $jsonSnippet = '';

        if ($snippet = $this->generateJson()) {

            $jsonSnippet = json_encode($snippet,JSON_UNESCAPED_SLASHES);
        }

        return $jsonSnippet;
    }


    /**
     * @return array
     */
    protected function generateJson()
    {
        $jsonSnippetArray = [];
        $product = $this->getCurrentProduct();

        if ($product) {

            $jsonSnippetArray = [
                "@context" => 'http://schema.org/',
                "@type" => "Product",
                "name" => $product->getName(),
                'description' => $product->getDescription(),
                'sku' => $product->getSku(),
                'offer' => $this->getOffers($product)
            ];
        }


        if (!empty($images = $this->getProductImages($product))) {
            $jsonSnippetArray['image'] = $images;
        }

        if (!empty($aggregateRating = $this->getRatingSummary($product))) {
            $jsonSnippetArray['aggregateRating'] = $aggregateRating;
        }

        return $jsonSnippetArray;
    }

    /**
     * @param $product
     * @return array
     */
    protected function getProductImages($product)
    {

        $galleryImages = $product->getMediaGalleryImages();
        $images = [];
        if (count($galleryImages->getItems())) {
            $imagesList = $galleryImages->getItems();
            foreach ($imagesList as $image) {
                if ($image['url'] && !$image['disabled']) {
                    $images[] = $image['url'];
                }
            }

        } else {
            $imagesListArray = $product->getMediaGallery();
            $imagesList = $imagesListArray['images'];

            if (count($imagesList)) {
                $mediaUrl = Mage::getBaseUrl('media');
                foreach ($imagesList as $image) {

                    if ($imageUrl = $image['file']) {

                        $imageUrl = 'catalog/product' . $imageUrl;
                        $images[] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . $imageUrl;
                    }
                }
            }
        }

        return $images;
    }

    /**
     * @param $product
     * @return mixed
     */
    protected function getRatingSummary($product)
    {
        $ratingSummary = $product->getRatingSummary();
        $aggregateRating = [];
        if ($ratingSummary->getReviewsCount()) {

            $ratingValue = $ratingSummary->getRatingSummary();
            $aggregateRating = [
                "@type" => "AggregateRating",
                "ratingValue" => ($ratingValue * 5) / 100,
                "reviewCount" => $ratingSummary->getReviewsCount()
            ];
        }

        return $aggregateRating;
    }


    /**
     * @param $product
     * @return array
     */
    protected function getOffers($product)
    {

        $offers = [
            "@type" => "Offer",
            "priceCurrency" => Mage::app()->getStore()->getCurrentCurrencyCode(),
            "price" => $this->getProductPrice($product),
            "itemCondition" => "http://schema.org/UsedCondition",
            "availability" => "http://schema.org/InStock"
        ];

        if ($product->getSpecialToDate()) {
            $offers['priceValidUntil'] = date('Y-m-d', strtotime($product->getSpecialToDate()));
        }

        return $offers;
    }

    protected function getProductPrice($product)
    {
        $price = 0;
        $store = $product->getStore();

        if ($product->getTypeId() == Mage_Catalog_Model_Product_Type_Configurable::TYPE_CODE) {
            //here it's using the configurable price, maybe should loop in the simple product and get the lowest
            // maybe need to ask in the requirement
            $price = $store->convertPrice($product->getPrice());
        } elseif ($product->getTypeId() == 'bundle') {

            $priceModel = $product->getPriceModel();
            list($_minimalPriceTax, $_maximalPriceTax) = $priceModel->getTotalPrices($product, null, null, false);
            $price = $_minimalPriceTax;

        } else {
            $price = $product->getFinalPrice();
        }

        return $store->roundPrice($price);
    }
}
