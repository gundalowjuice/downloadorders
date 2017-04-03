<?php
/**
 * test plugin for Craft CMS
 *
 * Test_AmazonPayments Controller
 *
 * --snip--
 * Generally speaking, controllers are the middlemen between the front end of the CP/website and your plugin’s
 * services. They contain action methods which handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering post data, saving it on a model,
 * passing the model off to a service, and then responding to the request appropriately depending on the service
 * method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what the method does (for example,
 * actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
 * --snip--
 *
 * @author    kevin douglass
 * @copyright Copyright (c) 2017 kevin douglass
 * @link      kevindouglass.io
 * @package   DownloadOrders
 * @since     1
 */

namespace Craft;

Use Commerce\Services\Commerce_OrdersService;

/*
 * Order Fields: https://craftcommerce.com/docs/craft-commerce-orders
 * Guest addresses are not stored in the database
 */

class DownloadOrders_OrdersController extends BaseController
{	
	
	protected $allowAnonymous = true;
   
   // formatted headers for CSV
	protected $headers = array(
		'Date to be Shipped', // date ordered
		'Order ID', // order number
		'Quantity',
		'Best By Date', // hard coded
		'Case Number', // product title
		'Name', // fname and lname combined
		'Address',
		'Attention',
		'Address Cont.',
		'City',
		'State',
		'Zip Code',
		'Message'
	);

	/*
	 * Exports orders from the DB based on the Commerce Orders DatePicker and the Status selected
	 */
	public function actionExportOrders( $startDate, $endDate, $orderStatus )
	{


		// reformat dates from JS
		$start    = date("Y-m-d", strtotime($startDate));
		$end      = date("Y-m-d", strtotime($endDate));
		$filename = 'orders-' . date('m-d-y') . '.csv';

		// query by status
		switch( $orderStatus ) {
			case 'export-orders-processed':
				$orderStatus = 'orderStatusId = 1';
			break;
			case 'export-orders-shipped':
				$orderStatus = 'orderStatusId = 2';
			break;
			case 'export-orders-distributor':
				$orderStatus = 'orderStatusId = 3';
			break;
			case 'export-orders-all':
				$orderStatus = 'orderStatusId = 1 OR orderStatusId = 2 OR orderStatusId = 3';
			break;
		}

		// db query
		$orders = \Craft\craft()->db->createCommand()
		     ->select([
		     	'orders.*', 
		     	'products.id as prod_id', // prevent overriting same col names
		     	'content.elementId',
		     	'content.field_productType',
		     	'lineitems.qty', 
		     	'lineitems.snapshot', 
		     	'purchasables.sku', 
		     	'purchasables.id as pur_id', // prevent overriting same col names
		     	'products.defaultSku',
		     	'addresses.firstName', 
		     	'addresses.lastName', 
		     	'addresses.stateId', 
		     	'addresses.address1', 
		     	'addresses.address2', 
		     	'addresses.city', 
		     	'addresses.zipCode',
		     	'addresses.attention',
		     	'states.abbreviation',
		     	])
		     // query orders table
		     ->from('commerce_orders orders')
		     // get qty of lineitem
		     ->leftJoin('commerce_lineitems lineitems', 'lineitems.orderId = orders.id') 
		     // get products sku from purchasables table
		     ->leftJoin('commerce_purchasables purchasables', 'purchasables.id = lineitems.purchasableId') 
		     // the products actually have two different entries within the database. 1) commerce variant 2) product custom fields
		     // the entries have different ID's
		     // I couldn't find an ID cross tables to get a custom field
		     // only the sku seemed to carry over between certain tables
		     ->leftJoin('commerce_products products', 'products.defaultSku = purchasables.sku') 
		     // get fieldType ( individual/pack )
		     ->leftJoin('content content', 'content.elementId = products.id') 
		     // get shipping address
		     ->leftJoin('commerce_addresses addresses', 'addresses.id = orders.shippingAddressId') 
		      // get correct state id
		     ->leftJoin('commerce_states states', 'states.id = addresses.stateId')
		     // select orders between dates
		     ->where('dateOrdered >= :dBegin AND dateOrdered <= :dEnd', array(':dBegin' => $start . ' 00:00:00', ':dEnd' => $end . ' 24:00:00' ) )
		      // query based on order status
		     ->andWhere($orderStatus)
		     // order the csv on the most recent orders
		     ->order('orders.dateOrdered DESC, orders.id DESC') 
		     ->queryAll(); 


		$phpExcel = new \PHPExcel();
		$sheet = $phpExcel->getActiveSheet();

		// Set defaults
		$phpExcel->getProperties()
		   ->setCreator('Gundalow Juice')
		   ->setTitle('Orders')
		   ->setLastModifiedBy('Gundalow Juice')
		   ->setDescription('')
		   ->setSubject('')
		   ->setKeywords('')
		   ->setCategory('');
		
		// Set first "empty" row
		$sheet->setCellValue('A1', 'GUNDALOW Nutrifresh- PACKAGE ORDER ALWAYS SEND TO TRANS');

		// Set headers
		$sheet->setCellValue('A2', 'Date Ordered'); // date ordered
		$sheet->setCellValue('B2', 'Order ID'); // order number
		$sheet->setCellValue('C2', 'Quantity');
		$sheet->setCellValue('D2', 'Best By Date'); // hard coded
		$sheet->setCellValue('E2', 'Case Number'); // product title
		$sheet->setCellValue('F2', 'Name'); // fname and lname combined
		$sheet->setCellValue('G2', 'Address');
		$sheet->setCellValue('H2', 'Attention');
		$sheet->setCellValue('I2', 'Address Cont.');
		$sheet->setCellValue('J2', 'City');
		$sheet->setCellValue('K2', 'State');
		$sheet->setCellValue('L2', 'Zip Code');
		$sheet->setCellValue('M2', 'Message');

		// Start row at 2 since row 1 is empty and row 2 are the headers
		$row = 3;

		// Add all rows from query
		foreach( $orders as $fields ) {	
		 
			$sheet->setCellValue('A'. $row, date('m/d/Y', strtotime($fields['dateOrdered'])));  // the date the order was completed.
			$sheet->setCellValue('B'. $row, $fields['id']); // order id
			$sheet->setCellValue('C'. $row, ( strtolower($fields['field_productType']) == 'pack' ) ? $fields['qty'] : intval( $fields['qty'] * 7 ));
			$sheet->setCellValue('D'. $row, 'FIFO');
			$sheet->setCellValue('E'. $row, strtr($fields['sku'],'-', ' '));
			$sheet->setCellValue('F'. $row, $fields['firstName'] . " " . $fields['lastName']);
			$sheet->setCellValue('G'. $row, $fields['address1']);
			$sheet->setCellValue('H'. $row, $fields['attention']);
			$sheet->setCellValue('I'. $row, $fields['address2']);
			$sheet->setCellValue('J'. $row, $fields['city']);
			$sheet->setCellValue('K'. $row, $fields['abbreviation']);
			$sheet->setCellValue('L'. $row, $fields['zipCode']);
			$sheet->setCellValue('M'. $row, $fields['message']);

			$row++;
		}

		header("Content-Type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=\"filename.xls\"");
		header("Cache-Control: max-age=0");
		$objWriter = \PHPExcel_IOFactory::createWriter($phpExcel, "Excel5");
		$objWriter->save("php://output");




		/*----------------------------------------
		 * CSV Way
		
		// modify the header to be CSV format
		header('Content-Disposition: attachement; filename="' . $filename . '";');
		header("Content-Transfer-Encoding: UTF-8");
		header("content-type: application/force-download");
		//header('Content-Type: application/csv');
		header('Pragma: no-cache');
		header("Content-Type: application/octet-stream"); 

		$tmp = fopen('php://output','w');

		// add empty row to csv
		$emptyRows = array('GUNDALOW Nutrifresh- PACKAGE ORDER ALWAYS SEND TO TRANS');
		fputcsv($tmp, $emptyRows, ',');

		// write table headers
		fputcsv($tmp, $this->headers);

	    // write table fields
		foreach( $orders as $fields ){

			//$snapshot = json_decode($fields['snapshot']);
			//echo gettype($snapshot);
			//print_r($snapshot);
			//echo '.....' . $snapshot->product->id . '......';

			fputcsv($tmp, array( 
		    		date('m/d/Y', strtotime($fields['dateOrdered'])),  // the date the order was completed.
		    		$fields['id'], // order id
		    		( strtolower($fields['field_productType']) == 'pack' ) ? $fields['qty'] : intval( $fields['qty'] * 7 ),
		    		'FIFO',
		    		strtr($fields['sku'],'-', ' '),
		    		$fields['firstName'] . " " . $fields['lastName'],
		    		$fields['address1'],
		    		$fields['attention'],
		    		$fields['address2'],
		    		$fields['city'],
					$fields['abbreviation'],
		    		$fields['zipCode'],
		    		$fields['message']
		    		)
		    	);
		}

	   // output the file to be downloaded
		fclose($tmp);

		* End CSV Way
		----------------------------------------*/

		die();
	}
   
   /*
    * Bulk select and change order status
    */
	public function actionChangeOrderStatus( $orders )
	{
		// break string values into array
		$orders = explode('|', $orders);

		foreach( $orders as $order ) {

			// 0 => order id, 1 => status
			$data = explode(',', $order);

			$orders = \Craft\craft()->db->createCommand()
			     ->update('commerce_orders', array('orderStatusId' => $data[1]), 'id = :id', array(':id' => $data[0]));
		}

		die();
	}
}