## Developer Notes

----

### Download Orders
The list below contains the table columns in the exported CSV. Not all the data lives in the same table and mulitple 
queries are used to join the data. 

- Date to be Shipped
- Order ID
- Quantity
- Best By Date
- Case Number
- Name
- Address
- Attention
- Address Cont.
- City
- State
- Zip Code
- Messag

### Custom Field for Product Type*
We need to add a custom field so we can properly multiply the order qty on individual products. \
Make sure to make this field required when adding it to a Tab in the settings. \
Please refer to screenshot "productType" for naming conventsion. \

- Custom field name
	- productType
- Add radio button with two options:
	- individual
	- pack


#### Notes
Customers that check out as Guest will not have their address stored in the database. \
Screenshot example of updated dashboard layout is located in the screenshots folder. \

----

### Bulk Select Update Status
Creat orders statuses of Prcessing, Shipped and At Distributor on the "admin/commerce/settings/orderstatuses" page.
Changing the status of an Order will trigger an email if the Order Statuses setting "Has Email?" is selected.

- Prcessing ID   = 1
- Shipped ID     = 2
- Distributor ID = 3

#### Notes
Bulk select does not contain a feature to update the Message field. \
Screenshot of settings is located in the screenshots folder.


----

# Vendors
`cd` into downloadorders plugin
run `composer update` to install vendor plugins, if they do not already exist