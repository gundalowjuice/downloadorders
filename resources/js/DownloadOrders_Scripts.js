
window.onload = function() {

	/*----------------------------------------
		Download Orders
	----------------------------------------*/
	// get url
	var url = window.location.toString();
	// remove last "/"
	var url = url.replace(/\/$/, "");
	// split paths
	var segments = url.split('/');
	// get last path
	//var lastsegment = segments[segments.length-1];
	var path = segments[segments.length-2] + '/' + segments[segments.length-1];

	// make sure we're on orders page
	if( path.indexOf('commerce/orders') !== -1 ) {

		// Download Order's GUI
		var exportContainer       = document.createElement('div');
		exportContainer.id        = 'order-exports';
		exportContainer.classname = 'row';
		exportContainer.innerHTML = '' +
				'<div class="heading">Export Orders' +
					'<div class="instructions">' +
						'<p>Please select the Dates below and whether you’d like to export All Orders, Processed Orders or Shipped Orders</p>' +
					'</div>' +
				'</div>' +
				'<div class="row">' +
					'<div class="col">' +
						'<div class="radiofield">' +
							'<input type="radio" name="order-status" value="export-orders-all" id="export-orders-all" checked>' +
							'<label for="export-orders-all">All Orders</label>' +
						'</div>' +
						'<div class="radiofield">' +
							'<input type="radio" name="order-status" value="export-orders-processed" id="export-orders-processed">' +
							'<label for="export-orders-processed">Processed Orders</label>' +
						'</div>' +
						'<div class="radiofield">' +
							'<input type="radio" name="order-status" value="export-orders-shipped" id="export-orders-shipped">' +
							'<label for="export-orders-shipped">Shipped Orders</label>' +
						'</div>' +
						'<div class="radiofield">' +
							'<input type="radio" name="order-status" value="export-orders-distributor" id="export-orders-distributor">' +
							'<label for="export-orders-distributor">Orders at Distributor</label>' +
						'</div>' +
					'</div>' +
					'<div class="col">' +
						'<a href="#" target="_top" id="export-orders-btn" class="btn" download>Export Orders</a>' + 
					'</div>' +
				'</div>';


		// add Download Orders GUI to dashbaord
		var elements = document.querySelector('#main .main');
		elements.insertBefore(exportContainer, elements.childNodes[2]);

		// export orders btn listner
		document.querySelector('#export-orders-btn').addEventListener('click', function( event ) {

			event.preventDefault();

			// get dates
			var dateRange1 = document.querySelector('.date-range .datewrapper:first-child input');
			var dateRange2 = document.querySelector('.date-range .datewrapper:nth-of-type(2) input');

			// get status
			var status = document.querySelector('#order-exports .radiofield input[name="order-status"]:checked');
			
			// https://craftcms.com/docs/plugins/controllers
			var craftActionUrl = Craft.getActionUrl('downloadOrders/orders/ExportOrders', { 
				startDate:   dateRange1.value, 
				endDate:     dateRange2.value, 
				orderStatus: status.value 
			});

			// call controller
			// ajax calls were not working due to multiple header request
			window.location.href = craftActionUrl;
		});
	

		/*----------------------------------------
			Change Status of Orders
		----------------------------------------*/
	
		// create status buttons
		var bulkSelectBtnsContainer       = document.createElement('div');
		bulkSelectBtnsContainer.id        = 'bulk-select-btns';
		bulkSelectBtnsContainer.innerHTML = '<div class="heading"><span>Update Order Statuses</span></div>';
		bulkSelectBtnsContainer.innerHTML += '<a data-status_id="1" href="javascript:void(0)" id="btn-status-to-processing" class="">To Processing</a>';
		bulkSelectBtnsContainer.innerHTML += '<a data-status_id="2" href="javascript:void(0)" id="btn-status-to-shipped" class="">To Shipped</a>';
		bulkSelectBtnsContainer.innerHTML += '<a data-status_id="3" href="javascript:void(0)" id="btn-status-to-distributor" class="">To Distributor</a>';
	
		// get sidebar
		var ordersSideBar = document.querySelector('.commerceorders #main #sidebar');
		var placeBefore = ordersSideBar.querySelector('.customize-sources');
		// add btns to sidebar
		ordersSideBar.insertBefore(bulkSelectBtnsContainer, placeBefore.previousElementSibling);
	
		document.querySelector('#btn-status-to-processing').addEventListener('click', updateStauts);
		document.querySelector('#btn-status-to-shipped').addEventListener('click', updateStauts);
		document.querySelector('#btn-status-to-distributor').addEventListener('click', updateStauts);
	
		function updateStauts( event ) {
	
			// get status id
			var statusId = event.target.getAttribute('data-status_id');
	
			// get all selected rows / orders
			var activeEls = document.querySelectorAll('.elements .tableview .data tr.sel');
			var order = '';
			var data = {};
	
			for( var i = 0; i < activeEls.length; i++ ) {
	
				var dataID = activeEls[i].getAttribute('data-id');
				
				// create long query string so we can get all the orders
				order += (( i != 0 ) ? '|' : '')+dataID+','+statusId;
	
				data = { orders: order };
			}
			
			// postActionRequest seems to have an error when reference,
			// https://craftcms.com/docs/plugins/controllers#posting-to-controller-actions-with-javaScript
			// work aroudn is to first use getActionUrl to format the url then call the post action
			var craftActionUrl = Craft.getActionUrl('downloadOrders/orders/ChangeOrderStatus', data );
			
			// call controller function
			Craft.postActionRequest(craftActionUrl, null, function(response) {
				// reload the page to update appears
				location.reload();
			});
			
		};
	}
};

            