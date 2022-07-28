<div class='search_similar_products_ '>
<label>Select Similar Products</label>	
	<select class='search_similar_products'>
		<option value=''>Add Product</option>
	</select>	
</div>$Encryption->encode($product_id)



<?php
include_once(__DIR__ . '/common_header.php');

if (empty($_SESSION['ki_webuser']['customer_id'])) {
	if (!empty($_COOKIE['kingit_webmanagement_cookie'])) {
		$result = send_rest(array(
			"table" => "ki_customers_info",
			"function" => "get_details_login",
			"key" => "remember_me_cookie",
			"value" => safe_str($_COOKIE['kingit_webmanagement_cookie'])
		));
		if (!empty($result)) {
			$Encryption =   new Encryption();
			$cookiehash = md5(sha1("kingit_webcookie_" . $Encryption->encode($result['customer_id'])));
			setcookie('kingit_webmanagement_cookie', $cookiehash, time() + (86400 * 30), "/");
			$in_fields["last_login_time"] = date("Y-m-d H:i:s");
			$in_fields["remember_me_cookie"] = $cookiehash;
			$result_p = send_rest(array(
				"table" => "ki_customers_info",
				"function" => "update",
				"fields_data" => $in_fields,
				"key" => "customer_id",
				"value" => $result['customer_id']
			));
			$_SESSION['ki_webuser']['customer_id'] = $result['customer_id'];
			$_SESSION['ki_webuser']['location_type'] = $result['home_store_type'];
			$_SESSION['ki_webuser']['location_id'] = $result['home_store_id'];
		}
	}
}
$categories = array();
$request = $_GET;
$category_list = send_rest(array(
	"function" => "KitWebApi/publish_categories_all_tree",
	"is_child" => 0
));
// print_r($category_list);
if ($category_list['status'] == 1 && !empty($category_list['data'])) {
	$categories = $category_list['data'];
}
/* $ticket_types = array();
$ticket_types = send_rest(array(
	"function" => "get_device_wizard_dropdown_for_jobs",
	"type" => 1,
	"website" => 1
)); */
$ticket_types = array(
	array(
		"ticket_type_id" => "2",
		"ticket_type_name" => "Console",
		"icon_picture" => "/uploads/93879265fc5ca779a663.jpg",
		"slugs" => "Console-Repair",
	),
	array(
		"ticket_type_id" => "3",
		"ticket_type_name" => "Drone",
		"icon_picture" => "/uploads/40853645fc5cad35a179.jpg",
		"slugs" => "Drone-Repair",
	),
	array(
		"ticket_type_id" => "1",
		"ticket_type_name" => "Mac",
		"icon_picture" => "/uploads/6463513115fc5ca331fad0.jpg",
		"slugs" => "Mac-Repair",
	),
	array(
		"ticket_type_id" => "7",
		"ticket_type_name" => "Mobile",
		"icon_picture" => "/uploads/2116491485fc5cbc89961d.jpg",
		"slugs" => "Mobile-Repair",
	),
	array(
		"ticket_type_id" => "8",
		"ticket_type_name" => "PC",
		"icon_picture" => "/uploads/7276733645fc5cc6dcf92f.jpg",
		"slugs" => "PC-Repair",
	),
	array(
		"ticket_type_id" => "4",
		"ticket_type_name" => "Storage Device",
		"icon_picture" => "/uploads/3496219745fc5cb2414efa.jpg",
		"slugs" => "Storage-Device-Repair",
	),
	array(
		"ticket_type_id" => "5",
		"ticket_type_name" => "Tablet",
		"icon_picture" => "/uploads/7755507465fc5cb758ad0d.jpg",
		"slugs" => "Tablet-Repair",
	),
	array(
		"ticket_type_id" => "14",
		"ticket_type_name" => "Watch",
		"icon_picture" => "/uploads/579516585fc5c9359113b.jpg",
		"slugs" => "Watch-Repair",
	)
);
$landing_pages = array();
$landing_pages = send_rest(array(
	"function" => "get_device_wizard_dropdown_for_jobs",
	"type" => 5
));
$cart_item_count = 0;
if(isset($_SESSION['ki_webuser']) && !empty($_SESSION['ki_webuser']['customer_id'])){
	$get_customer_details = send_rest(array(
		"table" => "ki_customers_info",
		"function" => "get_details",
		"key" => "customer_id",
		"value" => $_SESSION['ki_webuser']['customer_id']
	));
	if($get_customer_details['delete_flag']==1){
		unset($_SESSION['ki_webuser']);
	}
}
if (isset($_SESSION['ki_webuser']) && !empty($_SESSION['ki_webuser'])) {
	$get_user_merge_details = send_rest(array(
		"table" => "ki_customers_info",
		"function" => "merge_details",
		"value" => $_SESSION['ki_webuser']['customer_id']
	));
	if($get_user_merge_details["status"]===1 && $get_user_merge_details['customer_id']!==$_SESSION['ki_webuser']['customer_id']){
		$_SESSION['ki_webuser']['customer_id']=$get_user_merge_details['customer_id'];
	}
}
if (isset($_SESSION['ki_webuser']) && !empty($_SESSION['ki_webuser']['customer_id'])) {
	$cart_item_list = send_rest(array(
		"function" => "KitWebApi/cart_items_list",
		"customer_id" => $_SESSION['ki_webuser']['customer_id']
	));
	if ($cart_item_list['status'] == 1 && !empty($cart_item_list['data'])) {
		$cart_list = $cart_item_list['data'];
		$cart_item_count = count($cart_list);
	}
} else if (isset($_SESSION['cart_items']) && !isset($_SESSION['ki_webuser'])) {
	$cart_item_count = count($_SESSION['cart_items']);
}
if (isset($_SESSION['latitude']) && !empty($_SESSION['latitude']) && isset($_SESSION['longitude']) && !empty($_SESSION['longitude']) && isset($_SESSION['nearest_store'])) {
	//get all list of the stores by current location
	if($_SESSION['nearest_store']==1){
		$searchArray['lat'] = $_SESSION['latitude'];
		$searchArray['lng'] = $_SESSION['longitude'];
		$get_all_stores = send_rest(array(
			"function" => "Common/GetStoresList",
			"session_cords" => $searchArray,
		));
		if ($get_all_stores['status'] == 1 && !empty($get_all_stores['list'])) {
			$allStores  = $get_all_stores['list'];
			$nearest_store = $allStores[0]['store_name'];
			$nearest_storeId = $allStores[0]['store_id'];
			$_SESSION['location_id']=$nearest_storeId;
			if(isset($_SESSION['ki_webuser'])){
				$_SESSION['ki_webuser']['location_id']=$nearest_storeId;
			}
		}
	}
}

//get get_capabilities
$capabilities = send_rest(array(
	"function" => "Marketing/get_capabilities",
));
$capabilitiesList=$capabilities["details"];
//get store list
$storelist = array();
$get_storelist = send_rest(array(
	"function" => "Location/get_store_list",
));
if ($get_storelist['status'] == 1 && !empty($get_storelist['list'])) {
	$storelist = $get_storelist['list'];
	if (!empty($storelist) && isset($_SESSION['ki_webuser']) && (empty($_SESSION['ki_webuser']['location_type']) || empty($_SESSION['ki_webuser']['location_id']) || $_SESSION['ki_webuser']['location_type'] != 1 || !in_array($_SESSION['ki_webuser']['location_id'], array_column($storelist, "store_id")))) {
		$_SESSION['ki_webuser']['location_type'] = LOCATION_TYPE;
		$_SESSION['ki_webuser']['location_id'] = LOCATION_ID;
	}
}
$storename = $store_id = $location_id = '';
/*
if(!empty($_SESSION['latitude']) && !empty($_SESSION['longitude'])){
	$location_id=get_store_id($_SESSION['latitude'],$_SESSION['longitude']);
	if(empty($location_id)){
		$location_id=LOCATION_ID;
	}
	if(!isset($_SESSION['ki_webuser'])){
		$_SESSION['location_id']=$location_id;
	}
}
$storename=$store_id=''; 
if(isset($_SESSION['ki_webuser'])){
	if(!empty($_SESSION['ki_webuser']['location_id'])){
		$store_id=$_SESSION['ki_webuser']['location_id'];
	}else if(isset($_SESSION['location_id'])){
		$store_id=$_SESSION['location_id'];
	}else{
		$store_id=LOCATION_ID;
	}
}else{
	if(isset($_SESSION['location_id'])){
		$store_id=$_SESSION['location_id'];
	}else{
		$store_id=LOCATION_ID;
	}
}
*/
if (!empty($_GET['store_id'])) {
	$store_id = $Enc->decode($_GET['store_id']);
} else if (isset($_SESSION['ki_webuser'])) {
	if (!empty($_SESSION['ki_webuser']['location_id'])) {
		/* $get_user_details = send_rest(array(
			"table" => "ki_customers_info",
			"function" => "get_details",
			"key" => "customer_id",
			"value" => $_SESSION['ki_webuser']['customer_id']
		));
		if(!empty($get_user_details)){
			$store_id=$get_user_details['home_store_id'];
		} */
		$store_id = $_SESSION['ki_webuser']['location_id'];
	} else if (isset($_SESSION['location_id'])) {
		$store_id = $_SESSION['location_id'];
	} else {
		$store_id = LOCATION_ID;
	}
} else {
	if (isset($_SESSION['latitude']) && isset($_SESSION['longitude']) && !empty($_SESSION['latitude']) && !empty($_SESSION['longitude']) && !isset($_SESSION['location_id'])) {
		$location_id = get_store_id($_SESSION['latitude'], $_SESSION['longitude']);
	}
	if (!empty($location_id)) {
		$store_id = $location_id;
	} else if (empty($store_id) && isset($_SESSION['location_id'])) {
		$store_id = $_SESSION['location_id'];
	} else {
		$store_id = LOCATION_ID;
	}
}
$get_location = send_rest(array(
	"function" => "Location/get_store_by_postcode",
	"postcode" => '',
	"store_id" => $store_id
));
if ($get_location['status'] == 1 && !empty($get_location['list'])) {
	$storename = $get_location['list']['store_name'];
}


function recursive_cat_links($cats){
	foreach ($cats as $cat) {
		$c = $cat['category_info'];
		echo $c['slug_url']."<br>";
		if (!empty($cat['childrens'])) {
			recursive_cat_links($cat['childrens']);
		}
	}
}
recursive_cat_links($categories);
