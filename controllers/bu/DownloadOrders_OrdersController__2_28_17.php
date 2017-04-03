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


class DownloadOrders_OrdersController extends BaseController
{	
	
	protected $allowAnonymous = true;

	/*
	:: custom fields need to be added ::
		Date to be shipped
		Best By Date
		Case No

		Order Number
		Qty
		Name
		Address
		City
		State 
		Zip
		Message
	*/
	protected $headers = array(
		'billingAddressId',
		'shippingAddressId',
		'paymentMethodId',
		'customerId',
		'id',
		'orderStatusId',
		'number',
		'couponCode',
		'itemTotal',
		'baseDiscount',
		'baseShippingCost',
		'totalPrice',
		'totalPaid',
		'email',
		'isCompleted',
		'dateOrdered',
		'datePaid',
		'currency',
		'paymentCurrency',
		'lastIp',
		'orderLocale',
		'message',
		'returnUrl',
		'cancelUrl',
		'shippingMethod',
		'dateCreated',
		'dateUpdated',
		'uid',
		'qty',
		'sku',
		'firstName',
		'lastName'
	);

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
			case 'export-orders-all':
				$orderStatus = 'orderStatusId = 1 OR orderStatusId = 2';
			break;
		}

		// actual db query
		$orders = \Craft\craft()->db->createCommand()
	        ->select('orders.*, lineitems.qty, purchasables.sku, addresses.firstName, addresses.lastName')
	        ->from('commerce_orders orders') // get orders
	        ->join('commerce_lineitems lineitems', 'lineitems.orderId = orders.id') // get lineItems that match order.id
	        ->join('commerce_purchasables purchasables', 'purchasables.id = lineitems.purchasableId') // get the products that match lineitems.purchasableId
	        ->join('commerce_addresses addresses', 'addresses.id = orders.id') // get first and last names
	        ->where('dateOrdered >= :dBegin AND dateOrdered <= :dEnd', array(':dBegin' => $start, ':dEnd' => $end ) )
	        ->andWhere($orderStatus)
	        ->order('id DESC')
	        ->queryAll();

	    // modify the header to be CSV format
	    header('Content-Disposition: attachement; filename="' . $filename . '";');
	    header("Content-Transfer-Encoding: UTF-8");
	    header("content-type: application/force-download");
	    header('Content-Type: application/csv');
	    header('Pragma: no-cache');

	    $tmp = fopen('php://output','w');

	    // write table headers
	    fputcsv($tmp, $this->headers);

	    // write table fields
	    foreach( $orders as $fields ){
	        
	        fputcsv($tmp, $fields);
	    }

	    // output the file to be downloaded
	    fclose($tmp);

	    die();
	}
}