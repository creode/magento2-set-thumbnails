<?php

namespace Creode\ThumbnailSelector\Console\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;


class ThumbnailCommand extends Command
{
 	
 	public function __construct(
 		\Magento\Catalog\Model\ProductRepository $productRepository,
	    \Magento\Framework\Api\SearchCriteriaInterface $criteria,
	    \Magento\Framework\Api\Search\FilterGroup $filterGroup,
	    \Magento\Framework\Api\FilterBuilder $filterBuilder,
	    \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
	    \Magento\Catalog\Model\Product\Visibility $productVisibility,
	    \Magento\Framework\App\State $state
 	){
 		$this->productRepository = $productRepository;
	    $this->searchCriteria = $criteria;
	    $this->filterGroup = $filterGroup;
	    $this->filterBuilder = $filterBuilder;
	    $this->productStatus = $productStatus;
	    $this->productVisibility = $productVisibility;
	    $state->setAreaCode('admin');
	    parent::__construct();

 	}
    protected function configure()
    {
        $this->setName('creode:set-thumbnails')->setDescription('Selects the first image for every product and sets it as the thumbnail and small image');
    }
 
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        
        //put space at the top
        $output->writeln('');
        $output->writeln('');
        //load object manager
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    	
    	$question = $objectManager->create('\Symfony\Component\Console\Question\ConfirmationQuestion', ['question'=>'<fg=cyan;>Would you like to set thumbnails on products that already have thumbnails set? (y/n): </>', 'default' => FALSE]);
    	$helper = $this->getHelper('question');

         $ans = $helper->ask($input, $output, $question);
       	 $output->writeln('Getting Product Data...');

       	 //bit of info about what is going on.
         $output->writeln('');
         $output->writeln('');
         $output->writeln('Key:');
         $output->writeln('<fg=red;>. = Thumbnail exists</>');
         $output->writeln('<fg=yellow;>. = Skipped</>');
         $output->writeln('<fg=green;>. = Thumbnail set by script</>');
         $output->writeln('');
         $output->writeln('');
    	
        $products = $this->getProductData();
        //count products
    	$productCount = count($products);

    	//tell user stuffs
    	$output->writeln('<fg=green;>Processing Thumbnails</>');
    	$output->writeln('Found <fg=green;>'.$productCount.'</> Products');
    	$counts = array(
    		"fails" => 0,
    		"success" => 0,
    		"skips" => 0
    	);


    	foreach ($products as $key => $product) {
    			
    			//$output->writeln($product->getId());
    			$objectManager->get("Magento\Catalog\Model\Product\Gallery\ReadHandler")->execute($product);
    			$images = $product->getMediaGalleryImages();
    			//count images and make sure theres data to work with


    			//get current thumbnail and check if empty. saves on processing power.
				$thumb = $product->getData('thumbnail');
				
				if(strtolower($ans) == 'y'){
					//if yes and the thumbnail is
					if(count($images) > 0){
	    				$image = $images->getFirstItem();
	    				$imageUrl = $image->getFile();
	    				//$output->writeln($imageUrl);
	    				//perfrom second check to see if image url isnt empty. if isnt save it.
	    				if(!empty($image) && (is_null($image) !== true)){
							$product->setThumbnail($imageUrl);
							try {
		    					$product->save();
		    					$output->write('<fg=green;>.</>');
		    					$counts['success']++;
		    					
	    					} catch (\Exception $e) {
	    						$output->write('<fg=red;>.</>');
	    						$counts['fails']++;
	    					}

	    				}else{
	    					$output->write('<fg=red;>.</>');
	    					$counts['fails']++;
	    				}
	    				
	    			}else{
	    				$output->write('<fg=red;>.</>');
	    				$counts['fails']++;
	    			}

				}else{
					if(empty($thumb)){
						if(count($images) > 0){
	    				$image = $images->getFirstItem();
	    				$imageUrl = $image->getFile();
	    				//$output->writeln($imageUrl);
	    				//perfrom second check to see if image url isnt empty. if isnt save it.
		    				if(!empty($image) && (is_null($image) !== true)){
								$product->setThumbnail($imageUrl);
								try {
			    					$product->save();
				    				$output->write('<fg=green;>.</>');
			    					$counts['success']++;
			    					
		    					} catch (\Exception $e) {
		    						$output->write('<fg=red;>.</>');
		    						$counts['fails']++;
		    					}

		    				}else{
		    					$output->write('<fg=red;>.</>');
		    					$counts['fails']++;
		    				}
		    				
		    			}else{
		    				$output->write('<fg=red;>.</>');
		    				$counts['fails']++;
		    			}
					}else{
						$output->write('<fg=yellow;>.</>');
		    			$counts['skips']++;
					}
					

				}

    			
    			
    	}

    	//final messages
    	$output->writeln('');
    	$output->writeln('');
    	$output->writeln('<fg=green;>Complete!</>');
    	$output->writeln('');
    	$output->writeln('Thumbnails Updated: <fg=green;>'.$counts['success'].'</>');
    	$output->writeln('Skipped Products: <fg=yellow;>'.$counts['skips'].'</>');
    	$output->writeln('No Images found: <fg=red;>'.$counts['fails'].'</>');
    	 $output->writeln('Hello World!');
        
        
    }

    protected function getProductData()
	{

	    $this->filterGroup->setFilters([
	        $this->filterBuilder
	            ->setField('status')
	            ->setConditionType('in')
	            ->setValue($this->productStatus->getVisibleStatusIds())
	            ->create(),
	        $this->filterBuilder
	            ->setField('visibility')
	            ->setConditionType('in')
	            ->setValue($this->productVisibility->getVisibleInSiteIds())
	            ->create(),
	    ]);

	    $this->searchCriteria->setFilterGroups([$this->filterGroup]);
	    $products = $this->productRepository->getList($this->searchCriteria);
	    $productItems = $products->getItems();

	    return $productItems;
	}
 
}


?>