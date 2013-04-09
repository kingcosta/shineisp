<?php

/**
 * Products
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
class Products extends BaseProducts {
	
	/**
	 * grid
	 * create the configuration of the grid
	 */
	public static function grid($rowNum = 10, $locale = null) {
		$translator = Zend_Registry::getInstance ()->Zend_Translate;
		
		if ( $locale === null ) {
			$Session = new Zend_Session_Namespace ( 'Admin' );
			$locale = $Session->langid;
		}
		
		$translator = Zend_Registry::getInstance ()->Zend_Translate;
		$config ['datagrid'] ['columns'] [] = array ('label' => null, 'field' => 'p.product_id', 'alias' => 'product_id', 'type' => 'selectall' );
		$config ['datagrid'] ['columns'] [] = array ('label' => $translator->translate ( 'ID' ), 'field' => 'p.product_id', 'alias' => 'product_id', 'sortable' => true, 'searchable' => true, 'type' => 'string' );
		$config ['datagrid'] ['columns'] [] = array ('label' => $translator->translate ( 'Name' ), 'field' => 'pd.name', 'alias' => 'name', 'sortable' => true, 'searchable' => true, 'type' => 'string' );
		$config ['datagrid'] ['columns'] [] = array ('label' => $translator->translate ( 'Type' ), 'field' => 'p.type', 'alias' => 'type', 'type' => 'string', 'searchable' => true );
		$config ['datagrid'] ['columns'] [] = array ('label' => $translator->translate ( 'Group' ), 'field' => 'pag.name', 'alias' => 'groupname', 'sortable' => true, 'searchable' => true, 'type' => 'string' );
		$config ['datagrid'] ['columns'] [] = array ('label' => $translator->translate ( 'Inserted at' ), 'field' => 'p.inserted_at', 'sortable' => true, 'searchable' => false, 'alias' => 'insertedat', 'type' => 'date' );
		$config ['datagrid'] ['columns'] [] = array ('label' => $translator->translate ( 'Updated at' ), 'field' => 'p.updated_at', 'sortable' => true, 'searchable' => false, 'alias' => 'updatedat', 'type' => 'date' );
		$config ['datagrid'] ['columns'] [] = array ('label' => $translator->translate ( 'Enabled' ), 'field' => 'p.enabled', 'sortable' => true, 'searchable' => false, 'alias' => 'enabled', 'type' => 'string' );
		
		$config ['datagrid'] ['fields'] = "p.product_id, 
											pd.name as name, 
											p.type as type, 
											pag.name as groupname, 
											DATE_FORMAT(p.inserted_at, '%d/%m/%Y %H:%i:%s') as insertedat,  
											DATE_FORMAT(p.updated_at, '%d/%m/%Y %H:%i:%s') as updatedat,  
											IF( p.enabled = 1, 'Yes', 'No' ) as enabled";
		
		$config ['datagrid'] ['dqrecordset'] = Doctrine_Query::create ()->select ( $config ['datagrid'] ['fields'] )
																		->from ( 'Products p' )
																		->leftJoin ( 'p.ProductsAttributesGroups pag' )
																		->leftJoin ( "p.ProductsData pd WITH pd.language_id = $locale" );
		
		$config ['datagrid'] ['basepath'] = "/admin/products/";
		$config ['datagrid'] ['index'] = "product_id";
		$config ['datagrid'] ['rownum'] = $rowNum;
		$config ['datagrid'] ['rowlist'] = array ('10', '50', '100', '1000' );
		
		$config ['datagrid'] ['buttons'] ['edit'] ['label'] = $translator->translate ( 'Edit' );
		$config ['datagrid'] ['buttons'] ['edit'] ['cssicon'] = "edit";
		$config ['datagrid'] ['buttons'] ['edit'] ['action'] = "/admin/products/edit/id/%d";
		
		$config ['datagrid'] ['buttons'] ['delete'] ['label'] = $translator->translate ( 'Delete' );
		$config ['datagrid'] ['buttons'] ['delete'] ['cssicon'] = "delete";
		$config ['datagrid'] ['buttons'] ['delete'] ['action'] = "/admin/products/delete/id/%d";
		$config ['datagrid'] ['massactions'] = array ('bulk_delete'=>'Mass Delete');
		
		return $config;
	}
	
	/**
	 * Enadisable
	 * Set a record as disabled or enabled
	 * @param $parameters
	 * @return Void
	 */
	public static function Enadisable($parameters) {
		
		$items = !empty($parameters['item']) && is_array($parameters['item']) ? $parameters['item'] : array();
		if(!empty($items)){
			foreach($items as $item){
				$product = Doctrine::getTable ( 'Products' )->find ( $item );
				$product->enabled = ($product->enabled == 0) ? 1 : 0;
				$product->save ();
			}
			return true;
		}
		return false;
	}
	
	/**
	 * setStatus
	 * Set a record with a status
	 * @param $id, $status
	 * @return Void
	 */
	public static function setNewStatus($parameters) {
		$items = !empty($parameters['item']) && is_array($parameters['item']) ? $parameters['item'] : array();
		$status = !empty($parameters['status']) ? $parameters['status'] : null;
		
		foreach($items as $item){
			$product = Doctrine::getTable ( 'Products' )->find ( $item );
			$product->status_id = $status;
			$product->save ();
		}

		return true;
	}
	

	/**
	 * Add a category to a group of products
	 * 
	 * @param integer $productId
	 * @param integer $categoryIds
	 */
	public static function add2category($categoryId, array $products){
		
		$allproducts = self::getAll();
		
		// Check if the selected category is already set in all the products
		foreach ($allproducts as $product) {
			
			$categories = explode("/", $product['categories']);  // Get the categories
			
			// Check if the selected category is already set 
			if(in_array($categoryId, $categories)){
				// Get the index of the category to delete
				$idx = array_search($categoryId, $categories);

				// Delete the category from the array
				unset($categories[$idx]);
				
				// Load the product details
				$p = Doctrine::getTable('Products')->find($product['product_id']);

				// Create the category string list
				$categories = implode("/", $categories);
				
				// Update the product
				$p['categories'] = $categories;
				$p->save();
			}
			
		}

		// Now we have to add the category selected in all the product selected
		foreach ($products as $id) {
			$product = Doctrine::getTable('Products')->find($id);

			// Get the product categories
			$categories = explode("/", $product['categories']);
		
			// Check if the category has been already set in the product
			if(!in_array($categoryId, $categories)){
				$categories[] = $categoryId;
				$categories = implode("/", $categories);
				$product['categories'] = $categories;
				$product->save();
			}
		}
		return true;
	}
	
	
	/**
	 * Save all the data
	 * 
	 * 
	 * @param unknown_type $data
	 * @param unknown_type $locale
	 */
	public static function saveAll($id, $params, $locale = 1) {
		$products = new Products ();
		
		// Set the new values
		if (is_numeric ( $id )) {
			$products = self::find($id, null, false, $locale);
			if($products[0]){
				$products = $products[0];
			}
		}else{
			$products->inserted_at = date('Y-m-d H:i:s');
		}

		// Product UUID is missing, generate a new one		
		if ( empty($products->uuid) ) {
			$products->uuid = Shineisp_Commons_Uuid::generate();
		}
		
		try {
			if(!empty($_FILES ['attachments'])){
				$file = $_FILES ['attachments'];
				if (! empty ( $file )) {
					if (! is_dir ( PUBLIC_PATH . "/media/products/" )) {
						@mkdir ( PUBLIC_PATH . "/media" );
						@mkdir ( PUBLIC_PATH . "/media/products" );
					}
				}
			}
			if(is_array($params)){
				$products->updated_at    = date('Y-m-d H:i:s');
				$products->categories    = ! empty ( $params ['categories'] ) ? $params ['categories'] : null;
				$products->uri           = ! empty ( $params ['uri'] ) ? Shineisp_Commons_UrlRewrites::format ( $params ['uri'] ) : Shineisp_Commons_UrlRewrites::format ( $params ['name'] );
				$products->sku           = ! empty ( $params ['sku'] ) ? $params ['sku'] : '';
				$products->cost          = $params ['cost'];
				$products->price_1       = $params ['price_1'];
				$products->setupfee      = $params ['setupfee'];
				$products->enabled       = $params ['enabled'] == 1 ? 1 : 0;
				$products->iscomparable  = !empty($params ['iscomparable']) ? 1 : 0;
				$products->tax_id        = !empty($params ['tax_id']) ? $params ['tax_id'] : NULL;
				$products->type          = !empty($params ['type']) ? $params ['type'] : "generic";
				$products->blocks        = !empty($params ['blocks']) ? $params ['blocks'] : NULL;
				$products->group_id      = !empty($params ['group_id']) ? $params ['group_id'] : NULL;
				$products->position      = !empty($params ['position']) ? $params ['position'] : NULL;
				$products->setup         = !empty($params ['setup']) ? $params ['setup'] : NULL;
				$products->ishighlighted = !empty($params ['ishighlighted']) ? 1 : 0;
				$products->showonrss     = !empty($params ['showonrss']) ? 1 : 0;
				$products->external_id   = !empty($params ['external_id']) ? $params ['external_id'] : NULL;
				$products->downgradable  = !empty($params['downgradable']) ? 1: 0;
				
				// Save the data
				$products->save ();
				$product_id = $products->product_id;
				
				// Save the product attributes
				ProductsAttributesIndexes::saveAll ( $params, $product_id );
				
				$Pdata = ProductsData::findbyProductID ( $product_id, $locale );
				if (empty ( $Pdata )) {
					$Pdata    = new ProductsData ();
				}
				
				//* Product name can not be changed if product is sold
				if ( ! (bool)OrdersItems::CheckIfProductExist($product_id) ) {
					$Pdata->name = $params ['name'];	
				}
				
				$Pdata->nickname         = $params ['nickname'];
				$Pdata->shortdescription = $params ['shortdescription'];
				$Pdata->description      = $params ['description'];
				$Pdata->metakeywords     = $params ['metakeywords'];
				$Pdata->metadescription  = $params ['metadescription'];
				$Pdata->product_id       = $product_id;
				$Pdata->language_id      = $locale;
				$Pdata->save ();
				
				// Create the price tranches
				if (! empty ( $params ['tranche_qty'] ) && ! empty ( $params ['tranche_measure'] ) && ! empty ( $params ['tranche_price'] )) {
					$params['tranche_setupfee'] = (isset($params['tranche_setupfee'])) ? $params['tranche_setupfee'] : 0;
					ProductsTranches::saveAll($id, $params ['tranche_billing_cycle_id'], $params ['tranche_qty'], $params ['tranche_measure'], $params ['tranche_price'], $params['tranche_setupfee']);
				}
				
				// Attach the wiki pages to a product
				if(!empty($params['wikipages'])){
					Wikilinks::addWikiPages2Products ( $product_id, $params['wikipages'] );
				}
				
				// Add the related products
				if(!empty($params ['related'])){
					self::AddRelatedProducts ( $product_id, $params ['related'] );
				}
				
				// Add the upgrade products
				if(!empty($params ['upgrade'])){
					self::AddUpgradeProducts ( $product_id, $params ['upgrade'] );
				}
				
				
				// Before to get the Values of the form I upload the files in the folders
				if (! empty ( $file )) {
					if ($_FILES ['attachments'] ['error'] == 0) {
						
						// Uploading the file
						$filename = mt_rand ( 10, 999 ) . '_' . $_FILES ['attachments'] ['name'];
						$retval = move_uploaded_file ( $_FILES ['attachments'] ['tmp_name'], PUBLIC_PATH . "/media/products/" . $filename );
						if ($retval) {
							$media = new ProductsMedia ();
							$media->filename = $filename;
							$media->path = "/media/products/$filename";
							$media->product_id = $product_id;
							$media->description = $params ['filedescription'];
							$media->enabled = 1;
							$media->save ();
						}
					}
				}
			
				return $product_id;
			}else{
				throw new Exception('Parameters data are not correct.');
			}
		} catch ( Exception $e ) {
			echo $e->getMessage ();
			die ();
			return false;
		}
	}
	
	/**
	 * Get the price
	 * @param integer $productid
	 * @param boolean $taxincluded
	 */
	public static function getPrice($productid, $taxincluded=false) {
		
		$prices = self::getPrices($productid);
		if($prices['type'] == "multiple"){
			if($taxincluded){
				return $prices['minvaluewithtaxes'];
			}else{
				return $prices['minvalue'];
			}
		}else{
			if($taxincluded){
				return $prices['taxincluded'];
			}else{
				return $prices['value'];
			}
		}
	}
	
	/**
	 * Get the suggested price
	 * @param integer $productid
	 * @param boolean $taxincluded
	 */
	public static function getPriceSuggested($productid, $taxincluded=false) {
		$currency = new Zend_Currency();
		$price = 0;
		
		if (is_numeric ( $productid )) {
			$product = self::getAllInfo ( $productid );
			
			// Get the tax percentage
			$tax = Taxes::getTaxbyProductID($productid);
			if (! empty ( $product )) {
				if (! empty ( $product ['price_1'] ) && $product ['price_1'] > 0) {
					// Taxes calculation
					if(!empty($tax['percentage']) && is_numeric($tax['percentage'])){
						$price = ($product ['price_1'] * ($tax['percentage'] + 100) / 100);
						$price = $currency->toCurrency($price, array('currency' => Settings::findbyParam('currency')));
					}else{
						$price = $currency->toCurrency($product ['price_1'], array('currency' => Settings::findbyParam('currency')));
					}
				}else{
					$tranches = ProductsTranches::getSuggestedTranche($productid);
					if (!empty($tranches[0])) {
						$price = $tranches[0]['price'] * $tranches[0]['BillingCycle']['months'];
					}else{
						if(!empty($product['ProductsTranches'])){
							$price = $product['ProductsTranches'][0]['price'] * $product['ProductsTranches'][0]['BillingCycle']['months'];
						}
					}
				}
			}
			
			// Taxes calculation
			if($taxincluded && ($price > 0) && !empty($tax['percentage']) && is_numeric($tax['percentage'])){
				$price = ($price * ($tax['percentage'] + 100) / 100);
				$price = $currency->toCurrency($price, array('currency' => Settings::findbyParam('currency')));
			}
			
			return $price;
		}
	}
	
	/**
	 * getPrices
	 * Get the price of the product
	 * @param integer $productid
	 */
	public static function getPrices($productid) {
		$prices = array ();
		
		if (is_numeric ( $productid )) {
			$product = self::getAllInfo ( $productid );
			
			// Get the tax percentage
			$tax = Taxes::getTaxbyProductID($productid);
			
			if (! empty ( $product )) {
				if (! empty ( $product ['price_1'] ) && $product ['price_1'] > 0) {
					
					// Taxes calculation
					if(!empty($tax['percentage']) && is_numeric($tax['percentage'])){
						$taxincluded = ($product ['price_1'] * ($tax['percentage'] + 100) / 100);
					}else{
						$taxincluded = $product ['price_1'];
					}
					
					return array ('type' => 'flat', 'value' => $product ['price_1'], 'taxincluded' => $taxincluded, 'taxes' => $tax );
				} else {

					// Get the price min & max interval tranches
					$tranches = ProductsTranches::getMinMaxTranches ( $productid );
					if (!empty($tranches[1])) {
						
						// Taxes calculation
						if(!empty($tax['percentage']) && is_numeric($tax['percentage'])){
							$minvaluewithtaxes = ($tranches[0]['price'] * ($tax['percentage'] + 100) / 100);
							$maxvaluewithtaxes = ($tranches[1]['price'] * ($tax['percentage'] + 100) / 100);
						}else{
							$minvaluewithtaxes = $tranches[0]['price'];
							$maxvaluewithtaxes = $tranches[1]['price'];
						}
						$discount = floatval($tranches[1]['price']) - floatval($tranches[0]['price']);
						
						$discount = money_format("%.2n", $discount);
						$minvalue = $tranches[0]['price'];
						$maxvalue = $tranches[1]['price'];
						
						return array ('type' => 'multiple', 'measurement' => $tranches[0]['measurement'], 'tranches' => $tranches, 'minvalue' => $minvalue, 'maxvalue' => $maxvalue, 'minvaluewithtaxes' => $minvaluewithtaxes, 'maxvaluewithtaxes' => $maxvaluewithtaxes, 'discount' => $discount, 'taxes' => $tax );
					}else{
						// Taxes calculation
						
						if(!empty($tax['percentage']) && is_numeric($tax['percentage'])){
							$minvaluewithtaxes = ($tranches['price'] * ($tax['percentage'] + 100) / 100);
						}else{
							$minvaluewithtaxes = $tranches['price'];
						}
						
						$price = $tranches['price'];
						
						return array ('type' => 'multiple', 'measurement' => $tranches['measurement'], 'minvalue' => $price, 'maxvalue' => $price, 'taxes' => $tax, 'minvaluewithtaxes' => $minvaluewithtaxes, 'maxvaluewithtaxes' => 0, 'discount' => 0);
					}
				}
			}
		}
		return 0;
	}
	
	/*
     * AddRelatedProducts
     * add the related products 
     */
	private static function AddRelatedProducts($id, $relatedproducts) {
		$i = 0;
		
		// Delete all the products related before adding the new one
		ProductsRelated::delItemsbyProductID ( $id );
		
		$related = new Doctrine_Collection ( 'ProductsRelated' );
		if(!empty($relatedproducts)){
			foreach ( $relatedproducts as $item ) {
				$related [$i]->related_product_id = $item;
				$related [$i]->product_id = $id;
				$i ++;
			}
			$related->save ();
		}
	}
	
	/*
     * AddUpgradeProducts
     * add the related products 
     */
	private static function AddUpgradeProducts($id, $upgradeproducts) {
		$i = 0;
		
		// Delete all the products related before adding the new one
		ProductsUpgrades::delItemsbyProductID ( $id );
		
		$upgrade = new Doctrine_Collection ( 'ProductsUpgrades' );
		if(!empty($upgradeproducts)){
			foreach ( $upgradeproducts as $item ) {
				$upgrade [$i]->upgrade_product_id = $item;
				$upgrade [$i]->product_id = $id;
				$i ++;
			}
			$upgrade->save ();
		}
	}	
	
	/**
	 * getProductbyUriID
	 * Get a list ready of products by URI
	 * @return array
	 */
	public static function getProductbyUriID($uri, $fields = "*", $locale = 1) {
		$data = array ();
		
		$product = Doctrine_Query::create ()
		->select ( $fields )
		->from ( 'Products p' )
		->leftJoin ( 'p.ProductsAttributesGroups pag' )
		->leftJoin ( "p.ProductsData pd WITH pd.language_id = $locale" )
		->leftJoin ( 'p.Taxes t' )
		->leftJoin ( 'p.ProductsAttributesIndexes pai' )
		->where ( 'uri = ?', $uri )
		->execute ( array (), Doctrine_Core::HYDRATE_ARRAY );
		
		if (count ( $product ) > 0) {
			
			// Check the product data translation text
			$product [0] = ProductsData::checkTranslation ( $product [0] );
			
			// Get the categories
			$product [0] ['cleancategories'] = ProductsCategories::getCategoriesInfo ( $product [0] ['categories'] );
			
			// Get the media information
			$product [0] ['media'] = ProductsMedia::getMediabyProductId ( $product [0] ['product_id'] );
			
			return $product [0];
		} else {
			return array ();
		}
	}
	
	/**
	 * getProductsbyText
	 * Get a list of products by text
	 * @return array
	 */
	public static function getProductsbyText($text, $locale=1) {
		$text = htmlspecialchars($text);
		$text = addslashes($text);
		
		$items = explode("-", $text);
		$dq = Doctrine_Query::create ()
				->select ( 'p.product_id, p.uri as uri, pd.name as name, pd.shortdescription as shortdescription' )
				->from ( 'Products p' )
				->leftJoin ( "p.ProductsData pd WITH pd.language_id = $locale" )
				->where("p.type <> 'domain'");
				
		foreach ($items as $item){
			$dq->andWhere ( '(pd.name like ? OR pd.shortdescription like ? OR pd.metakeywords like ?)', array("%".$item."%", "%".$item."%", "%".$item."%") );	
		}
		
		$dq->andWhere('p.enabled = ?', 1)->limit(10);
				
		$records = $dq->execute ( array (), Doctrine_Core::HYDRATE_ARRAY );
		
		return $records;
	}
	
	/**
	 * expiringProducts
	 * Get all the expiring products
	 * @param $customerid
	 * @return ARRAY Record
	 */
	public static function getExpiringProducts($customerid = "", $locale = 1) {
		$dq = Doctrine_Query::create ()->select ( "oi.detail_id as detail_id, o.order_id as order_id, pd.name as Products, DATE_FORMAT(oi.date_end, '%d/%m/%Y') as Termination" )->from ( 'Orders o' )->leftJoin ( 'o.OrdersItems oi' )->leftJoin ( 'oi.Products p' )->leftJoin ( "p.ProductsData pd WITH pd.language_id = $locale" )->where ( "p.type <> ?", 'domain' )->addWhere ( 'DATEDIFF(oi.date_end, CURRENT_DATE) <= 31' )->addWhere ( 'oi.status_id = ?', Statuses::id("complete", "orders") ); // Complete

		if (is_numeric ( $customerid )) {
			$dq->addWhere ( "o.customer_id = ?", $customerid );
		}
		return $dq->execute ( array (), Doctrine::HYDRATE_ARRAY );
	}
	
	/**
	 * getProductType
	 * Get the product type 
	 * @param $id
	 * @return ARRAY Record
	 */
	public static function getProductType($id) {
		$dq = Doctrine_Query::create ()->select ( 'p.type' )->from ( 'Products p' )->where ( 'p.product_id = ?', $id );
		$product = $dq->execute ( array (), Doctrine_Core::HYDRATE_ARRAY );
		if (isset ( $product [0] ['type'] )) {
			return $product [0] ['type'];
		} else {
			return null;
		}
	}
	
	/**
	 * Get the product by ExternalId 
	 * @param $id
	 * @return ARRAY Record
	 */
	public static function getProductByExternalId($id) {
		$dq = Doctrine_Query::create ()->from ( 'Products p' )->where ( 'p.external_id = ?', $id );
		$product = $dq->execute ( array (), Doctrine_Core::HYDRATE_ARRAY );
		if (isset ( $product [0]  )) {
			return $product [0] ;
		} else {
			return null;
		}
	}
	
	/**
	 * Check if the product is a hosting plan  
	 * @param $id
	 * @return ARRAY Record
	 */
	public static function isHosting($id) {
		$dq = Doctrine_Query::create ()->select ( 'p.type' )->from ( 'Products p' )->where ( 'p.product_id = ?', $id );
		$product = $dq->execute ( array (), Doctrine_Core::HYDRATE_ARRAY );
		if (!empty ( $product [0] ['type'] ) && $product [0] ['type'] == "hosting") {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * check if the product has a payment recurring profile
	 * @param $id
	 * @return boolean
	 */
	public static function isRecurring($id) {
		$record = Doctrine_Query::create ()
								->select('p.product_id, pag.isrecurring as isrecurring')
								->from ( 'Products p' )
								->leftJoin ( 'p.ProductsAttributesGroups pag' )
								->where ( 'p.product_id = ?', $id )
								->execute ( array (), Doctrine_Core::HYDRATE_ARRAY );

		if($record[0]['isrecurring']){
			return true;
		}else{
			return false;
		}
	}
	
	/**
	 * Get a record by ID
	 * 
	 * 
	 * @param $id
	 * @return ARRAY Record
	 */
	public static function getAllInfo($id, $locale = 1) {
		
		$dq = Doctrine_Query::create ()
								->from ( 'Products p' )
								->leftJoin ( 'p.ProductsAttributesGroups pag' )
								->leftJoin ( "p.ProductsData pd WITH pd.language_id = $locale" )
								->leftJoin ( 'p.Taxes t' )
								->leftJoin ( 'p.ProductsAttributesIndexes pai' )
								->leftJoin ( 'p.ProductsTranches pt' )
								->leftJoin(  'pt.BillingCycle bc')
								->where ( 'p.product_id = ?', $id );
								
		$product = $dq->execute ( array (), Doctrine_Core::HYDRATE_ARRAY );
		
		if (isset ( $product [0] )) {
			
			// Handle the Attributes Values
			if (! empty ( $product [0] ['ProductsAttributesIndexes'] )) {
				$attributes = $product [0] ['ProductsAttributesIndexes'];
				foreach ( $attributes as $attribute ) {
					$attr = ProductsAttributes::find ( $attribute ['attribute_id'] );
					$code = $attr ['code'];
					$product [0] [$code] = $attribute ['value'];
				}
				unset ( $product [0] ['ProductsAttributesIndexes'] );
			}
			
			return $product [0];
		} else {
			return array ();
		}
	}
	
	/**
	 * getGroupAttributes
	 * Get all the products attributes by product id
	 * @param integer $product_id
	 * @return array
	 */
	public static function getAttributes($product_id, $locale = 1) {
		$records = Doctrine_Query::create ()
					->from ( 'ProductsAttributesIndexes pai' )
					->leftJoin ( 'pai.ProductsAttributes pa' )
					->leftJoin ( 'pa.ProductsAttributesData pad WITH pad.language_id = ' . $locale )
					->where ( 'pai.product_id = ?', $product_id )
					->execute ( array (), Doctrine_Core::HYDRATE_ARRAY );
		
//								print_r($records);
//								die;
		if (! empty ( $records [0] ['ProductsAttributesGroups'] ) && is_array ( $records [0] ['ProductsAttributesGroups'] )) {
			return $records [0] ['ProductsAttributesGroups'];
		}
		return array ();
	}

	/**
	 * getHostings
	 * Get all hosting products. The group doesn't include the domains.
	 * @return ARRAY Record
	 */
	public static function getHostings($fields = "*", $locale = 1) {
		$tlds = array ();
		$dq = Doctrine_Query::create ()->select ( $fields )->from ( 'Products p' )
			->leftJoin ( "p.ProductsData pd WITH pd.language_id = $locale" )
			->where ( 'p.type = ?', 'hosting' );
		$items = $dq->execute ( array (), Doctrine_Core::HYDRATE_ARRAY );
		return $items;
	}
	
	/**
	 * getServicesandHostings
	 * Get all hosting products. The group doesn't include the domains.
	 * @return ARRAY Record
	 */
	public static function getServicesandHostings($fields = "*", $locale = 1) {
		$tlds = array ();
		$dq = Doctrine_Query::create ()->select ( $fields )->from ( 'Products p' )
		->leftJoin ( "p.ProductsData pd WITH pd.language_id = $locale" )
		->where ( 'p.type <> ?', 'domain' )
		->andWhere('p.enabled = ?', 1);
		$items = $dq->execute ( array (), Doctrine_Core::HYDRATE_ARRAY );
		return $items;
	}
	
	/**
	 * Get a record by ID
	 * 
	 * @param $id
	 * @return Doctrine Record
	 */
	public static function find($id, $fields = "*", $retarray = false, $locale = 1) {
		$dq = Doctrine_Query::create ()->select ( $fields )
									->from ( 'Products p' )
									->leftJoin ( "p.ProductsData pd WITH pd.language_id = $locale" )
									->where ( "p.product_id = $id" )
									->limit ( 1 );
		
		$retarray = $retarray ? Doctrine_Core::HYDRATE_ARRAY : null;
		$record = $dq->execute ( array (), $retarray );
		return $record;
	}
	
	/**
	 * getCost
	 * Get the cost of a particular product
	 * @param $id
	 * @return array
	 */
	public static function getCost($id) {
		$record = Doctrine_Query::create ()->select ( 'cost' )->from ( 'Products p' )->where ( "p.product_id = ?", $id )->limit ( 1 )->execute ( array (), Doctrine_Core::HYDRATE_ARRAY );
		
		return ! empty ( $record [0] ['cost'] ) ? $record [0] ['cost'] : 0;
	}
	
	/**
	 * findbyName
	 * Get a record by name
	 * @param $id
	 * @return Doctrine Record
	 */
	public static function findbyName($name, $fields = "*", $retarray = false, $locale = 1) {
		$dq = Doctrine_Query::create ()->select ( $fields )->from ( 'Products p' )
		->leftJoin ( "p.ProductsData pd WITH pd.language_id = $locale" )
		->where ( "pd.name = ?", $name )->limit ( 1 );
		
		$retarray = $retarray ? Doctrine_Core::HYDRATE_ARRAY : null;
		$record = $dq->execute ( array (), $retarray );
		return $record;
	}
	
	/**
	 * Search products  
	 * 
	 * @param string $needed
	 * @param integer $locale
	 * @return ArrayObject
	 */
	public static function search($needed, $locale = 1, $retarray=TRUE) {
		$data = array();
		
		$dq = Doctrine_Query::create ()->select('p.*, pag.code as groupcode, pd.name as name, pd.shortdescription as shortdescription, pd.metakeywords as keywords, pm.path as imgpath')
										->from ( 'Products p' )
										->leftJoin ( "p.ProductsData pd WITH pd.language_id = $locale" )
										->leftJoin ( "p.ProductsMedia pm" )
										->leftJoin ( "p.ProductsAttributesGroups pag" )
										->where ( "pd.name like ? and p.enabled = ?", array("%$needed%", 1) )
										->orWhere("pd.shortdescription like ?", "%$needed%")
										->orWhere("pd.description like ?", "%$needed%")
										->orWhere("pd.metakeywords like ?", "%$needed%");
										
		if($retarray){
			
			$data = $dq->execute ( array (), Doctrine_Core::HYDRATE_ARRAY );
			
		}else{
			
			// Create the pagination of the records
			$dq = self::pager($dq, 5);

			// Execute the query
			$products = $dq->execute ( array (), Doctrine_Core::HYDRATE_ARRAY );
			
			foreach ( $products as $product ) {
				$product['reviews'] = Reviews::countItems($product['product_id']);  // Add the total of the reviews for each product
				$data ['records'][] = ProductsData::checkTranslation($product);  // Check the product data translation text
			}
				
			// Get the pager object
			$data['pager'] = $dq->display ( null, true );
		}
		
		return $data;
	}
	
	/**
	 *  Set the paging tool
	 */
	public static function pager($dq, $rows=10){
		$module = Zend_Controller_Front::getInstance ()->getRequest ()->getModuleName ();
		$controller = Zend_Controller_Front::getInstance ()->getRequest ()->getControllerName ();
		$page = Zend_Controller_Front::getInstance ()->getRequest ()->getParam ( 'page' );
		$search = Zend_Controller_Front::getInstance ()->getRequest ()->getParam ( 'q' );
		$page = ! empty ( $page ) && is_numeric ( $page ) ? $page : 1;

		$pagerLayout = new Doctrine_Pager_Layout ( new Doctrine_Pager ( $dq, $page, $rows ), 
					   new Doctrine_Pager_Range_Sliding ( array ('chunk' => 10 ) ), "/$module/$controller/index/q/$search/page/{%page_number}");
		
		$pagerLayout->setTemplate ( '<a href="{%url}">{%page}</a> ' );
		$pagerLayout->setSelectedTemplate ( '<a class="active" href="{%url}">{%page}</a> ' );
		
		return $pagerLayout;
	}
	
	/**
	 * Get a record by category_id
	 * @param category_id
	 * @return Doctrine Record
	 */
	public static function findbyCategoryID($category_id, $fields = "*", $retarray = false, $locale = 1) {
		$dq = Doctrine_Query::create ()->select ( $fields )->from ( 'Products p' )
															->leftJoin ( "p.ProductsData pd WITH pd.language_id = $locale" )
															->where ( "p.categories = ?", $category_id );
		
		$retarray = $retarray ? Doctrine_Core::HYDRATE_ARRAY : null;
		$record = $dq->execute ( array (), $retarray );
		return $record;
	}
	
	/**
	 * getAll
	 * Get alla active products
	 * @param $locale
	 * @return Doctrine Record
	 */
	public static function getAll($locale = 1) {
		return Doctrine_Query::create ()->from ( 'Products p' )
										->leftJoin ( "p.ProductsData pd WITH pd.language_id = $locale" )
										->leftJoin ( "p.ProductsMedia pm" )
										->addWhere ( "p.enabled = ?", 1)
										->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
	}
	
	/**
	 * getAllHighlighted
	 * Get alla active and highlighted products
	 * @param $locale
	 * @return Doctrine Record
	 */
	public static function getAllHighlighted($locale = 1) {
		return Doctrine_Query::create ()->from ( 'Products p' )
										->leftJoin ( "p.ProductsData pd WITH pd.language_id = $locale" )
										->leftJoin ( "p.ProductsMedia pm" )
										->addWhere ( "p.enabled = ?", 1)
										->addWhere ( "p.ishighlighted = ?", 1)
										->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
	}
	
	/**
	 * getAllRss
	 * Get alla active products set as published in the RSS feed
	 * @param $locale
	 * @return Doctrine Record
	 */
	public static function getAllRss($locale = 1) {
		return Doctrine_Query::create ()->from ( 'Products p' )
										->leftJoin ( "p.ProductsData pd WITH pd.language_id = $locale" )
										->leftJoin ( "p.ProductsMedia pm" )
										->addWhere ( "p.enabled = ?", 1)
										->addWhere ( "p.showonrss = ?", 1)
										->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
	}
	
	/**
	 * getList
	 * Get a list ready for the html select object
	 * @return array
	 */
	public static function getList($empty = false, $locale = 1) {
		$items = array ();
		$registry = Zend_Registry::getInstance ();
		$translations = $registry->Zend_Translate;
		
		$dq = Doctrine_Query::create ()->select ( 'product_id, pd.name' )->from ( 'Products p' )->leftJoin ( "p.ProductsData pd WITH pd.language_id = $locale" );
		
		if ($empty) {
			$items [] = $translations->translate ( 'Select ...' );
		}
		
		$record = $dq->orderBy ( 'name' )->execute ( array (), Doctrine_Core::HYDRATE_ARRAY );
		
		if (! empty ( $record ) && count ( $record ) > 0) {
			foreach ( $record as $c ) {
				if (! empty ( $c ['ProductsData'] [0] ['name'] )) {
					$items [$c ['product_id']] = $c ['ProductsData'] [0] ['name'];
				} else {
					$items [$c ['product_id']] = $c ['product_id'] . " -> Not translated";
				}
			}
		}
		return $items;
	}
	
	/**
	 * getTlds
	 * Get a list ready for the html select object
	 * @return array
	 */
	public static function getTlds($empty = false, $locale = 1) {
		$items = array ();
		$dq = Doctrine_Query::create ()->select ( "product_id, pd.name as name" )
							->from ( 'Products p' )
							->leftJoin ( "p.ProductsData pd WITH pd.language_id = $locale" )
							->where ( "p.type = 'domain'" );
		$domains = $dq->execute ( array (), Doctrine_Core::HYDRATE_ARRAY );
		if ($empty) {
			$items [] = "";
		}
		
		foreach ( $domains as $domain ) {
			$items [$domain ['product_id']] = "." . $domain ['name']; // Don't delete the DOT in this string. IT'S Important!
		}
		return $items;
	}
	
	/**
	 * Delete a product
	 * 
	 * @param integer $id
	 * @return boolean
	 */
	public static function del($id){
			
		// Check if the product has been already attached to one or more orders
		if (0 < OrdersItems::CheckIfProductExist ( $id )) {

			// If the product is locked by an order disable it
			$product = Doctrine::getTable ( 'Products' )->find ( $id );
			$product->enabled = 0;
			$product->save ();
			return false;
		}

		// Delete the images of the product selected
		ProductsMedia::getMediabyProductId($id);

		// Delete the relationship between the products
		ProductsRelated::delItemsbyProductID($id);

		// Delete the product record
		self::find($id)->delete();
		
		return true;
	}
	
	/**
	 * Check if the product is a domain  
	 * 
	 * @param $id
	 * @return Array
	 */
	public static function CheckIfProductIsTLDDomain($id, $locale = 1) {
		$dq = Doctrine_Query::create ()->select ( "count(*) as result" )->from ( 'Products p' )->leftJoin ( "p.ProductsData pd WITH pd.language_id = $locale" )->addWhere ( "p.type = ?", 'domain' )->addWhere ( "p.product_id = ?", $id );
		
		$items = $dq->execute ( array (), Doctrine_Core::HYDRATE_ARRAY );
		return $items [0] ['result'] ? true : false;
	}
	
	/**
	 * GetDomainProduct
	 * Get a domain product  
	 * @param $tld [com, it, net, org]
	 * @return Array
	 */
	public static function GetDomainProduct($tld = "", $fields = "*", $locale = 1) {
		$dq = Doctrine_Query::create ()->select ( $fields )
									->from ( 'Products p' )
									->leftJoin ( 'p.ProductsData pd' )
									->where ( "p.type = ?", 'domain' );
		
		if (! empty ( $tld )) {
			$dq->where ( "pd.name = ?", trim ( strtolower ( $tld ) ) );
		}
		
		$items = $dq->execute ( array (), Doctrine_Core::HYDRATE_ARRAY );
		return $items;
	}
	
	/**
	 * GetAttributeGroupByProductID
	 * Get the product attribute group by the product ID
	 * @param integer $product_id
	 */
	public static function GetAttributeGroupByProductID($product_id) {
		$record = Doctrine_Query::create ()
									->select('product_id, pag.*')
									->from ( 'Products p' )
									->leftJoin ( 'p.ProductsAttributesGroups pag' )
									->where ( "p.product_id = ?", $product_id )
									->execute ( array (), Doctrine_Core::HYDRATE_ARRAY );

		if(!empty($record[0]['ProductsAttributesGroups'])){
			return $record[0]['ProductsAttributesGroups'];
		}
		
		return null;
	}
	
	/**
	 * GetProductsByGroupCode
	 * Get all the products by the Product Group Code  
	 * @param $code
	 * @return Array
	 */
	public static function GetProductsByGroupCode($code, $locale = 1) {
		
		$items['attributes'] = Doctrine_Query::create ()
									->from ( 'ProductsAttributes pa' )
									->leftJoin ( 'pa.ProductsAttributesGroupsIndexes pagi' )
									->leftJoin ( 'pagi.ProductsAttributesGroups pag' )
									->leftJoin ( "pa.ProductsAttributesData pad WITH pad.language_id = $locale" )
									->where ( "pag.code = ?", $code )
									->addWhere ( "pag.iscomparable = ?", 1 )
									->orderBy('pa.position')
									->execute ( array (), Doctrine_Core::HYDRATE_ARRAY );
			
								
		$items['attributes_values'] = Doctrine_Query::create ()
									->select('pai.*')
									->from ( 'ProductsAttributesIndexes pai' )
									->leftJoin ( 'pai.ProductsAttributes pa' )
									->leftJoin ( 'pa.ProductsAttributesGroupsIndexes pagi' )
									->leftJoin ( 'pagi.ProductsAttributesGroups pag' )
									->where ( "pag.code = ?", $code )
									->execute ( array (), Doctrine_Core::HYDRATE_ARRAY );
		
		$items['products'] = Doctrine_Query::create ()
									->from ( 'Products p' )
									->leftJoin ( 'p.ProductsAttributesGroups pag' )
									->leftJoin ( "p.ProductsData pd WITH pd.language_id = $locale" )
									->where ( "pag.code = ?", $code )
									->addWhere('p.enabled = ?', 1)
									->addWhere('p.iscomparable = ?', 1)
									->orderBy('p.position')
									->execute ( array (), Doctrine_Core::HYDRATE_ARRAY );

		return $items;
	}
	
	/**
	 * Get all services subscribed by a customer using his identifier 
	 * @param $id
	 * @param $fields
	 * @return Array
	 */
	public static function getAllServicesByCustomerID($id, $fields = "*", $locale = 1) {
		$items = array ();
		
		if (is_numeric ( $id )) {
			$dq = Doctrine_Query::create ()->select ( $fields )
			->from ( 'Orders o' )
			->leftJoin ( 'o.Customers c' )
			->leftJoin ( 'o.OrdersItems oi' )
			->leftJoin ( 'oi.Products p' )
			->leftJoin ( "p.ProductsData pd WITH pd.language_id = $locale" )
			->where ( "p.type <> ?", 'domain' )
			->addWhere ( "o.customer_id = ? OR c.parent_id = ?", array($id, $id) );

			return $dq->execute ( array (), Doctrine_Core::HYDRATE_ARRAY  );
			
		}
		
		return array();
	}
	
	/**
	 * Get all active services subscribed by a customer using his identifier 
	 * @param $id
	 * @param $fields
	 * @return Array
	 */
	public static function getAllActiveServicesByCustomerID($id, $fields = "*", $locale = 1) {
		$items = array ();
		
		if (is_numeric ( $id )) {
			$dq = Doctrine_Query::create ()->select ( $fields )
			->from ( 'Orders o' )
			->leftJoin ( 'o.Customers c' )
			->leftJoin ( 'o.OrdersItems oi' )
			->leftJoin ( 'oi.Products p' )
			->leftJoin ( "p.ProductsData pd WITH pd.language_id = $locale" )
			->where ( "p.type <> ?", 'domain' )
			->addWhere ( "o.customer_id = ? OR c.parent_id = ?", array($id, $id) )
			->addWhere ( '? BETWEEN oi.date_start AND oi.date_end', date('Y-m-d'));
			

			return $dq->execute ( array (), Doctrine_Core::HYDRATE_ARRAY  );
			
		}
		
		return array();
	}
	
	
	/**
	 * delete the products selected 
	 * @param array
	 * @return Boolean
	 */
	public static function massdelete($ids) {
		foreach ($ids as $id) {
			Products::del($id);
		}
		return true;
	}
	
	/**
	 * Get the html list of the categories
	 *  
	 * @param integer $index
	 */
	public static function get_categories($categories){
		$items = array();
		
		$categories = ProductsCategories::getCategoriesInfo ( $categories );
		
		foreach ($categories as $category) {
			$items[] = "<a href='/admin/productscategories/edit/id/".$category['id']."'>" . $category['name'] . "</a>";
		}
		return implode(", ", $items);
	}	
	
	/**
	 * Get the text list of the categories
	 *  
	 * @param integer $index
	 */
	public static function get_text_categories($categories){
		$items = array();
		
		$categories = ProductsCategories::getCategoriesInfo ( $categories );
		
		foreach ($categories as $category) {
			$items[] = $category['name'];
		}
		return implode(" > ", $items);
	}	
	
	######################################### BULK ACTIONS ############################################
	
	
	/**
	 * massdelete
	 * delete the customer selected 
	 * @param array
	 * @return Boolean
	 */
	public static function bulk_delete($items) {
		if(!empty($items)){
			return self::massdelete($items);
		}
		return false;
	}	
	
}