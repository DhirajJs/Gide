<?php
require_once 'abstract.php';

class Gide_Shell_Task extends Mage_Shell_Abstract
{

    public function run()
    {
        if ($this->getArg('file')) {
            $file = $this->getArg('file');
            if (file_exists($file)) {

                $content = file_get_contents($file);
                if ($content) {
                   // $then = microtime();

                    $productIds = explode(',', $content);
                    $collection = Mage::getModel('catalog/product')->getCollection();
                    $collection->addIdFilter($productIds)->addAttributeToSelect('status');
                    $statusArray = ['status' => ['enabled' => '', 'disabled' => ''] ];
                    $imagesArray = ['images' => []];
                    $categoriesArray = ['categories' =>[]];
                    $enabled = [];
                    $disabled = [];

                    foreach ($collection as $product) {

                        $attributes = $product->getTypeInstance(true)->getSetAttributes($product);
                        $media_gallery = $attributes['media_gallery'];
                        $backend = $media_gallery->getBackend();
                        $backend->afterLoad($product);

                        $productId = $product->getId();

                        if ($product->getStatus() == '1') {
                            $enabled[] = $productId;

                        } elseif ($product->getStatus() == '2') {
                            $disabled[] = $productId;
                        }

                        if($mediaGallery = $product->getMediaGalleryImages()) {
                            $imagesArray['images'][$productId] = count($mediaGallery);
                        }

                        $categories = $product->getCategoryIds();

                        if(isset($categories[0])) {
                            $categoriesArray['categories'][$productId] = $categories[0];
                        }



                    }
                    $statusArray['status']['enabled'] = implode(',',$enabled);
                    $statusArray['status']['disabled'] = implode(',',$disabled);
                    //$now = microtime();

                   // echo sprintf("Elapsed:  %f", $now - $then);

                    var_dump($statusArray);
                    var_dump($imagesArray);
                    var_dump($categoriesArray);

                    exit(1);
                } else {
                    echo "File $file is empty\n";
                    exit(1);
                }

            } else {
                echo "404 file not found. Please check the file path\n";
                exit(1);
            }

        } else {
            echo "Looks like you did not specify the file to be loaded\n";
            exit(1);
        }
    }

    // Usage instructions
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f task.php -- [options]
 
  --file <argvalue>       the file path 
  --name <argvalue>       the name of new file 
 
  help                   This help
 
USAGE;
    }
}

// Instantiate
$shell = new Gide_Shell_Task();

// Initiate script
$shell->run();