<?php
include_once(__DIR__ . "/../autoload.php");

class Common
{
	function get_detail_from_job_id($inputdata){
		/* 
		input params - 
			"job_id" => $job_id,
			"type" => $type,
			"detail_for" => $detail_for
		function is used to get details of a particular job_id
		output - 
			$data = array(
				"status" => 0,
				"details" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"details" => array(),
			"errors" => array()
		);
		if($inputdata['detail_for']==1) {
			$sql = "SELECT * FROM `ki_estimates_info` WHERE `job_id`='" . safe_str($inputdata['job_id']) . "' AND `delete_flag`=0 and estimate_type='".safe_str($inputdata['type'])."'";
		}
		if($inputdata['detail_for']==2) {
			$sql = "SELECT * FROM `ki_job_deposits_info` WHERE `job_id`='" . safe_str($inputdata['job_id']) . "' AND `delete_flag`=0";
		}
		$result = $con->query($sql);
		if ($result) {
			$data['status'] = 1;
			$data['details'] = $result->fetch_assoc();
		} else {
			$data['errors'][] = $con->error();
		}
		return $data;
	}
	
	function CheckIfUserIsBDM($inputdata)
	{
		/* 
		input params - 
			user_id, location_type, location_id
		function is used to check if user is BDM of the location or not.
		output -  
			$data = array(
				"status" => 0,
				"details" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"details" => array(
				"is_BDM" => 0
			),
			"errors" => array()
		);
		if (!empty($inputdata['user_id']) && !empty($inputdata['location_type']) && !empty($inputdata['location_id'])) {
			if ($inputdata['location_type'] == 1) {
				$table = 'ki_stores_info';
				$key = 'store_id';
			} elseif ($inputdata['location_type'] == 2) {
				$table = 'ki_distribution_branches_info';
				$key = 'distribution_branch_id';
			} elseif ($inputdata['location_type'] == 3) {
				$table = 'ki_production_info';
				$key = 'production_id';
			}
			$sql = "SELECT `business_development_manager_id`, `bdm_commission` FROM `" . $table . "` WHERE `" . $key . "`='" . safe_str($inputdata['location_id']) . "' AND `delete_flag`=0";
			$res = $con->query($sql);
			if ($res) {
				$row = $res->fetch_assoc();
				if (!empty($row) && $row['business_development_manager_id'] == $inputdata['user_id']) {
					$data['details']['is_BDM'] = 1;
					$data['details']['bdm_commission'] = $row['bdm_commission'];
				}
			} else {
				$data['errors'][] = $con->error;
			}
		} else {
			$data['errors'][] = "Failed to check if user is a BDM";
		}
		if (empty($data['errors'])) {
			$data['status'] = 1;
		}
		return $data;
	}
	function get_jobs_work_to_complete($inputdata)
	{
		/* 
		input params - 
			job_id
		function is used to save of trading hours for a location
		output -  
			$data = array(
				"status" => 0,
				"list" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"list" => array(),
			"errors" => array()
		);
		$sql =  "SELECT AA.*, work_to_complete, BB.`onboarding_notice` FROM `ki_job_work_to_complete_info` AA LEFT JOIN `ki_work_to_complete_info` BB ON AA.`work_to_complete_id`=BB.`work_to_complete_id` WHERE AA.`job_id`='" . safe_str($inputdata['job_id']) . "' AND AA.`delete_flag`='0'";
		$res = $con->query($sql);
		if ($res) {
			while ($row = $res->fetch_assoc()) {
				$data['list'][$row['work_to_complete_id']] = $row;
			}
		} else {
			$data['errors'][] = $con->error;
		}
		return $data;
	}
	function update_trading_hours_for_location($inputdata)
	{
		/* 
		input params - 
			location_type, location_id, post
		function is used to save of trading hours for a location
		output -  
			$data = array(
				"status" => 0,
				"list" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"errors" => array()
		);
		$sql =  "DELETE FROM `ki_location_trading_hours_info` WHERE `location_type`='" . safe_str($inputdata['location_type']) . "' AND `location_id`='" . safe_str($inputdata['location_id']) . "'";
		$res = $con->query($sql);
		if ($res) {
			if (!empty($inputdata['trading_hours'])) {
				$query = array();
				foreach ($inputdata['trading_hours'] as $day => $trading_hours) {
					$query[] = "('" . safe_str($inputdata['location_type']) . "', '" . safe_str($inputdata['location_id']) . "', '" . safe_str($day) . "', '" . safe_str($trading_hours['start_time']) . "', '" . safe_str($trading_hours['end_time']) . "', '" . safe_str(date("Y-m-d H:i:s")) . "')";
				}
				$qry = "INSERT INTO `ki_location_trading_hours_info`(`location_type`, `location_id`, `day`, `start_time`, `end_time`, `created_on`) VALUES " . implode(", ", $query);
				$res = $con->query($qry);
				if (!$res) {
					$data['errors'][] = $con->error;
				}
			}
		} else {
			$data['errors'][] = $con->error;
		}
		if (empty($data['errors'])) {
			$data['status'] = 1;
		}
		return $data;
	}
	function get_trading_hours_for_location($inputdata)
	{
		/* 
		input params - 
			location_type, location_id
		function is used to get list of trading hours for a location
		output -  
			$data = array(
				"status" => 0,
				"list" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"list" => array(),
			"errors" => array()
		);
		$sql = "SELECT * FROM `ki_location_trading_hours_info` WHERE `location_type`='" . safe_str($inputdata['location_type']) . "' AND `location_id`='" . safe_str($inputdata['location_id']) . "' AND `delete_flag`=0";
		$res = $con->query($sql);
		if ($res) {
			while ($row = $res->fetch_assoc()) {
				$data['list'][$row['day']] = $row;
			}
		} else {
			$data['errors'][] = $con->error;
		}
		if (empty($data['errors'])) {
			$data['status'] = 1;
		}
		return $data;
	}
	function get_duplicate_customers_list($inputdata)
	{
		/* 
		input params - 
			first_name, last_name, email, phone and customer_id
		function is used to get list of duplicate customers by email, phone and name
		output -  
			$data = array(
				"status" => 0,
				"list" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"list" => array(),
			"errors" => array()
		);
		$where = array();
		if (!empty($inputdata['first_name']) || !empty($inputdata['last_name'])) {
			$where[] = "CONCAT(COALESCE(`first_name`, ''),' ',COALESCE(`last_name`, ''))='" . safe_str($inputdata['first_name']) . " " . safe_str($inputdata['last_name']) . "'";
		}
		if (!empty($inputdata['email'])) {
			$where[] = "`email`='" . safe_str($inputdata['email']) . "'";
		}
		if (!empty($inputdata['phone'])) {
			$where[] = "REPLACE(REPLACE(`phone`, '-', ''), ' ', '')='" . safe_str(str_replace(' ', '', str_replace('-', '', $inputdata['phone']))) . "'";
		}
		if (!empty($where)) {
			$where = " AND (" . implode(" OR ", $where) . ") ";
		} else {
			$where = '';
		}
		$sql = "SELECT * FROM `ki_customers_info` WHERE `customer_id`!='" . safe_str($inputdata['customer_id']) . "' AND `delete_flag`=0" . $where;
		$res = $con->query($sql);
		if ($res) {
			while ($row = $res->fetch_assoc()) {
				$data['list'][] = $row;
			}
		} else {
			$data['errors'][] = $con->error;
		}
		if (empty($data['errors'])) {
			$data['status'] = 1;
		}
		return $data;
	}
	function GetAvailableStockAndCostPriceForProduct($inputdata)
	{
		/* 
		input params - 
			location_type, location_id, product_id
		function is used to get list of remaining_stock_on_hand and cost price.
		output -  
			$data = array(
				"status" => 0,
				"list" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"list" => array(),
			"errors" => array()
		);
		$list = array();
		$sql = "SELECT * FROM `ki_inventory_cost_price_valuation_info` WHERE `location_type`='" . safe_str($inputdata['location_type']) . "' AND `location_id`='" . safe_str($inputdata['location_id']) . "' AND `product_id`='" . safe_str($inputdata['product_id']) . "' AND `remaining_stock_on_hand`>=0 AND `delete_flag`=0 ORDER BY `created_on` ASC";
		if ($res = $con->query($sql)) {
			$i = 0;
			while ($row = $res->fetch_assoc()) {
				$list[$i]['remaining_stock_on_hand'] = $row['remaining_stock_on_hand'];
				$list[$i]['cost_price'] = $row['cost_price'];
				$i++;
			}
		} else {
			$data['errors'][] = $con->error;
		}
		if (empty($data['errors'])) {
			$data['status'] = 1;
			$data['list'] = $list;
		}
		return $data;
	}
	function UpdateInventoryCostPriceValuation($inputdata)
	{
		/* 
		input params - 
			location_type, location_id, product_id, soh_before_update, soh_after_update
		function is used to update the stock on hand and cost price in valuation info.
		output -  
			$data = array(
				"status" => 0,
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"stock_cost_price_valuation" => array(),
			"errors" => array()
		);
		$cp_array = array();
		$cost_price = 0;
		if (!isset($inputdata['cost_price'])) {
			$sql = "SELECT * FROM `ki_product_prices_info` WHERE `product_id`='" . safe_str($inputdata['product_id']) . "' AND `delete_flag`=0 ORDER BY `created_on` ASC LIMIT 1";
			if ($res = $con->query($sql)) {
				$row = $res->fetch_assoc();
				if (!empty($row)) {
					$cost_price = ($inputdata['location_type'] == 1) ? $row['distribution_price'] : $row['cost_price'];
				}
			} else {
				$data['errors'][] = $con->error;
			}
		} else {
			$cost_price = $inputdata['cost_price'];
		}
		if (empty($data['errors'])) {
			if ($inputdata['soh_after_update'] > $inputdata['soh_before_update']) {
				$diff = $inputdata['soh_after_update'] - $inputdata['soh_before_update'];
				$flag = 0;
				while ($flag == 0 && $diff > 0) {
					$sql = "SELECT * FROM `ki_inventory_cost_price_valuation_info` WHERE `location_type`='" . safe_str($inputdata['location_type']) . "' AND `location_id`='" . safe_str($inputdata['location_id']) . "' AND `product_id`='" . safe_str($inputdata['product_id']) . "' AND `remaining_stock_on_hand`<0 AND `delete_flag`=0 ORDER BY `created_on` ASC LIMIT 1";
					if ($res = $con->query($sql)) {
						$row = $res->fetch_assoc();
						if (!empty($row)) {
							$remaining_stock_on_hand = $row['remaining_stock_on_hand'];
							if (abs($remaining_stock_on_hand) > $diff) {
								$new_remaining_stock_on_hand = $remaining_stock_on_hand + $diff;
							} else {
								$new_remaining_stock_on_hand = 0;
							}
							$qry1 = "UPDATE `ki_inventory_cost_price_valuation_info` SET `remaining_stock_on_hand`='" . safe_str($new_remaining_stock_on_hand) . "', `modified_on`='" . date('Y-m-d H:i:s') . "' WHERE `valuation_id`='" . safe_str($row['valuation_id']) . "' AND `delete_flag`=0";
							if ($con->query($qry1)) {
								if (empty($cp_array[$row['cost_price']])) {
									$cp_array[$row['cost_price']] = abs($remaining_stock_on_hand);
								} else {
									$cp_array[$row['cost_price']] = $cp_array[$row['cost_price']] + abs($remaining_stock_on_hand);
								}
								$diff = $diff + $remaining_stock_on_hand;
							}
						} else {
							$flag = 1;
						}
					} else {
						$data['errors'][] = $con->error;
					}
				}
				if ($diff > 0) {
					$sql = "SELECT * FROM `ki_inventory_cost_price_valuation_info` WHERE `location_type`='" . safe_str($inputdata['location_type']) . "' AND `location_id`='" . safe_str($inputdata['location_id']) . "' AND `product_id`='" . safe_str($inputdata['product_id']) . "' AND `delete_flag`=0 ORDER BY `created_on` DESC LIMIT 1";
					if ($res = $con->query($sql)) {
						$row = $res->fetch_assoc();
						if (!empty($row)) {
							if ($row['cost_price'] == $cost_price) {
								$qry1 = "UPDATE `ki_inventory_cost_price_valuation_info` SET `remaining_stock_on_hand`=`remaining_stock_on_hand`+" . safe_str($diff) . ", `modified_on`='" . date('Y-m-d H:i:s') . "' WHERE `valuation_id`='" . safe_str($row['valuation_id']) . "' AND `delete_flag`=0";
							} else {
								$qry1 = "INSERT INTO `ki_inventory_cost_price_valuation_info` (`product_id`, `location_type`, `location_id`, `stock_on_hand`, `cost_price`, `remaining_stock_on_hand`, `created_on`) VALUES ('" . safe_str($inputdata['product_id']) . "', '" . safe_str($inputdata['location_type']) . "', '" . safe_str($inputdata['location_id']) . "', '" . safe_str($diff) . "', '" . safe_str($cost_price) . "', '" . safe_str($diff) . "', '" . date("Y-m-d H:i:s") . "')";
							}
							if (!($con->query($qry1))) {
								$data['errors'][] = $con->error;
							} else {
								if (empty($cp_array[$cost_price])) {
									$cp_array[$cost_price] = $diff;
								} else {
									$cp_array[$cost_price] = $cp_array[$cost_price] + $diff;
								}
							}
						} else {
							$qry1 = "INSERT INTO `ki_inventory_cost_price_valuation_info` (`product_id`, `location_type`, `location_id`, `stock_on_hand`, `cost_price`, `remaining_stock_on_hand`, `created_on`) VALUES ('" . safe_str($inputdata['product_id']) . "', '" . safe_str($inputdata['location_type']) . "', '" . safe_str($inputdata['location_id']) . "', '" . safe_str($inputdata['soh_after_update']) . "', '" . safe_str($cost_price) . "', '" . safe_str($inputdata['soh_after_update']) . "', '" . date("Y-m-d H:i:s") . "')";
							if (!($con->query($qry1))) {
								$data['errors'][] = $con->error;
							} else {
								if (empty($cp_array[$cost_price])) {
									$cp_array[$cost_price] = $inputdata['soh_after_update'];
								} else {
									$cp_array[$cost_price] = $cp_array[$cost_price] + $inputdata['soh_after_update'];
								}
							}
						}
					} else {
						$data['errors'][] = $con->error;
					}
				}
			} elseif ($inputdata['soh_before_update'] > $inputdata['soh_after_update']) {
				$diff = $inputdata['soh_before_update'] - $inputdata['soh_after_update'];
				while ($diff > 0) {
					$sql = "SELECT * FROM `ki_inventory_cost_price_valuation_info` WHERE `location_type`='" . safe_str($inputdata['location_type']) . "' AND `location_id`='" . safe_str($inputdata['location_id']) . "' AND `product_id`='" . safe_str($inputdata['product_id']) . "' AND `remaining_stock_on_hand`>0  AND `delete_flag`=0 ORDER BY `created_on` DESC LIMIT 1";
					if ($res = $con->query($sql)) {
						$row = $res->fetch_assoc();
						if (!empty($row)) {
							if ($row['remaining_stock_on_hand'] > $diff) {
								$quantity = $diff;
							} else {
								$quantity = $row['remaining_stock_on_hand'];
							}
							$qry1 = "UPDATE `ki_inventory_cost_price_valuation_info` SET `remaining_stock_on_hand`=`remaining_stock_on_hand`-" . safe_str($quantity) . ", `modified_on`='" . date('Y-m-d H:i:s') . "' WHERE `valuation_id`='" . safe_str($row['valuation_id']) . "' AND `delete_flag`=0";
							if ($con->query($qry1)) {
								if (empty($cp_array[$row['cost_price']])) {
									$cp_array[$row['cost_price']] = $quantity;
								} else {
									$cp_array[$row['cost_price']] = $cp_array[$row['cost_price']] + $quantity;
								}
								$diff = $diff - $quantity;
							}
						} else {
							$quantity = $diff;
							$sql = "SELECT * FROM `ki_inventory_cost_price_valuation_info` WHERE `location_type`='" . safe_str($inputdata['location_type']) . "' AND `location_id`='" . safe_str($inputdata['location_id']) . "' AND `product_id`='" . safe_str($inputdata['product_id']) . "' AND `delete_flag`=0 ORDER BY `created_on` DESC LIMIT 1";
							if ($res = $con->query($sql)) {
								$row = $res->fetch_assoc();
								if (!empty($row)) {
									$qry1 = "UPDATE `ki_inventory_cost_price_valuation_info` SET `remaining_stock_on_hand`=`remaining_stock_on_hand`-" . safe_str($quantity) . ", `modified_on`='" . date('Y-m-d H:i:s') . "' WHERE `valuation_id`='" . safe_str($row['valuation_id']) . "' AND `delete_flag`=0";
									if ($con->query($qry1)) {
										if (empty($cp_array[$row['cost_price']])) {
											$cp_array[$row['cost_price']] = $quantity;
										} else {
											$cp_array[$row['cost_price']] = $cp_array[$row['cost_price']] + $quantity;
										}
										$diff = $diff - $quantity;
									} else {
										$data['errors'][] = $con->error;
									}
								} else {
									$qry1 = "INSERT INTO `ki_inventory_cost_price_valuation_info` (`product_id`, `location_type`, `location_id`, `stock_on_hand`, `cost_price`, `remaining_stock_on_hand`, `created_on`) VALUES ('" . safe_str($inputdata['product_id']) . "', '" . safe_str($inputdata['location_type']) . "', '" . safe_str($inputdata['location_id']) . "', 0, '" . safe_str($cost_price) . "', '-" . safe_str($quantity) . "', '" . date("Y-m-d H:i:s") . "')";
									if ($con->query($qry1)) {
										if (empty($cp_array[$cost_price])) {
											$cp_array[$cost_price] = $quantity;
										} else {
											$cp_array[$cost_price] = $cp_array[$cost_price] + $quantity;
										}
										$diff = 0;
									} else {
										$data['errors'][] = $con->error;
									}
								}
							} else {
								$data['errors'][] = $con->error;
							}
						}
					} else {
						$data['errors'][] = $con->error;
					}
				}
			}
		}
		if (empty($data['errors'])) {
			$data['status'] = 1;
			$data['stock_cost_price_valuation'] = $cp_array;
		}
		// print_r($data);
		return $data;
	}
	function update_user_locations($inputdata)
	{
		/* 
		input params - 
			user_id, concatenated location_type and id
		function is used to update the locations associated to user
		output -  
			$data = array(
				"status" => 0,
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"errors" => array()
		);
		$sql = "UPDATE `ki_user_locations_info` SET `delete_flag`=1 WHERE `user_id`='" . safe_str($inputdata['user_id']) . "' AND CONCAT(`location_type`,' ',`location_id`) IN ('" . implode("', '", $inputdata['locations']) . "') AND `delete_flag`=0";
		$res = $con->query($sql);
		if ($res) {
			$data['status'] = 1;
		} else {
			$data['errors'][] = $con->error;
		}
		return $data;
	}
	function update_supplier_locations($inputdata)
	{
		/* 
		input params - 
			supplier_id, concatenated location_type and id
		function is used to update the locations associated to supplier
		output -  
			$data = array(
				"status" => 0,
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"errors" => array()
		);
		$sql = "UPDATE `ki_supplier_location_info` SET `delete_flag`=1 WHERE `supplier_id`='" . safe_str($inputdata['supplier_id']) . "' AND CONCAT(`location_type`,' ',`location_id`) IN ('" . implode("', '", $inputdata['locations']) . "') AND `delete_flag`=0";
		$res = $con->query($sql);
		if ($res) {
			$data['status'] = 1;
		} else {
			$data['errors'][] = $con->error;
		}
		return $data;
	}
	function get_supplier_invoice_generation_locations_info($inputdata)
	{
		/* 
		input params - 
			supplier_id
		function is used to get the locations who can invoice the store deliveries for this supplier
		output -  
			$data = array(
				"status" => 0,
				"list" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"count" => 0,
			"list" => array(),
			"errors" => array()
		);
		$sql = "SELECT *, CONCAT(`location_type`,' ',`location_id`) AS location FROM `ki_supplier_invoice_generation_locations_info` WHERE `supplier_id`='" . safe_str($inputdata['supplier_id']) . "' AND `delete_flag`=0";
		$res = $con->query($sql);
		if ($res) {
			$data['status'] = 1;
			while ($row = $res->fetch_assoc()) {
				$data['list'][] = $row;
			}
		} else {
			$data['errors'][] = $con->error;
		}
		return $data;
	}
	function get_recommended_estimate_non_stock_count($inputdata)
	{
		/* 
		input params - 
			job_id
		function is used to get count of non stock products and list of all products in a recommended estimate
		output -  
			$data = array(
				"status" => 0,
				"list" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"count" => 0,
			"list" => array(),
			"errors" => array()
		);
		$sql = "SELECT 
					ELI.*, `product_type`
				FROM 
					`ki_estimate_line_items_info` ELI 
				LEFT JOIN `ki_estimates_info` EI ON 
					ELI.`estimate_id`=EI.`estimate_id` AND EI.`delete_flag`=0 
				LEFT JOIN `ki_products_info` PI ON 
					ELI.`product_id`=PI.`product_id` AND PI.`delete_flag`=0 
				WHERE 
					EI.`job_id`='" . safe_str($inputdata['job_id']) . "' AND EI.`estimate_type`=2 AND PI.`status`!=3 AND ELI.`delete_flag`=0";
		$res = $con->query($sql);
		if ($res) {
			$data['status'] = 1;
			while ($row = $res->fetch_assoc()) {
				$data['list'][] = $row;
				if (!empty($row['product_id']) && empty($row['product_type'])) {
					$data['count']++;
				}
			}
		} else {
			$data['errors'][] = $con->error;
		}
		return $data;
	}
	function get_ticket_type_recommended_products_list($inputdata)
	{
		/* 
		input params - 
			ticket_type_id
		function is used to get list of all selected products for a ticket type
		output -  
			$data = array(
				"status" => 0,
				"list" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"list" => array(),
			"errors" => array()
		);
		$sql = "SELECT AA.*, BB.`product_name`, BB.`SKU`, BB.`product_type` FROM `ki_ticket_type_recommended_products_info` AA LEFT JOIN `ki_products_info` BB ON AA.`product_id`=BB.`product_id` AND BB.`delete_flag`=0 WHERE AA.`ticket_type_id`='" . safe_str($inputdata['ticket_type_id']) . "' AND AA.`delete_flag`=0 ORDER BY BB.`product_name` ASC";
		$res = $con->query($sql);
		if ($res) {
			$data['status'] = 1;
			while ($row = $res->fetch_assoc()) {
				$data['list'][] = $row;
			}
		} else {
			$data['errors'][] = $con->error;
		}
		return $data;
	}
	function GetBrandsList($inputdata)
	{
		/* 
		input params - 
			brand_id (optional)
		function is used to get list of all brands
		output -  
			$data = array(
				"status" => 0,
				"list" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"list" => array(),
			"errors" => array()
		);
		$query = "`is_enabled`=1";
		if (!empty($inputdata['brand_id'])) {
			$query = " (`is_enabled`=1 OR `brand_id` IN (" . implode(',', $inputdata['brand_id']) . ")) ";
		}
		$sql = "SELECT * FROM `ki_brands_info` WHERE " . $query . " AND `delete_flag`=0 ORDER BY `brand_name` ASC";
		$res = $con->query($sql);
		if ($res) {
			$data['status'] = 1;
			while ($row = $res->fetch_assoc()) {
				$data['list'][] = $row;
			}
		} else {
			$data['errors'][] = $con->error;
		}
		return $data;
	}
	function get_product_upload_queue_child_details($inputdata)
	{
		/* 
		input params - 
			product_queue_id
		function is used to get details of product_upload_queue_child
		output -  
			$data = array(
				"status" => 0,
				"details" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"details" => array(),
			"errors" => array()
		);
		$sql = 'SELECT
                	GROUP_CONCAT(`inserted_row_numbers`,",") AS `inserted_row_numbers`,
                	GROUP_CONCAT(`updated_row_numbers`,",") AS `updated_row_numbers`,
                	GROUP_CONCAT(`deleted_row_numbers`,",") AS `deleted_row_numbers`,
                	GROUP_CONCAT(`skipped_deleted`,",") AS `skipped_deleted`,
                	GROUP_CONCAT(`skipped_empty_req`,",") AS `skipped_empty_req`,
                	GROUP_CONCAT(`skipping_existing_barcode`,",") AS `skipping_existing_barcode`,
                	GROUP_CONCAT(`skipping_invalid_barcode`,",") AS `skipping_invalid_barcode`,
                	GROUP_CONCAT(`skipping_distribution_tax_error`,",") AS `skipping_distribution_tax_error`,
                	GROUP_CONCAT(`skipping_retail_tax_error`,",") AS `skipping_retail_tax_error`,
                	GROUP_CONCAT(`skipping_supplier_error`,",") AS `skipping_supplier_error`,
                	GROUP_CONCAT(`skipping_category_error`,",") AS `skipping_category_error`,
                	GROUP_CONCAT(`skipping_non_decimal_fields`,",") AS `skipping_non_decimal_fields`,
                	GROUP_CONCAT(`skipping_non_integer_fields`,",") AS `skipping_non_integer_fields`
                FROM 
                	`ki_product_upload_queue_child_info` 
                WHERE 
                	`product_queue_id`=' . $inputdata['product_queue_id'] . " AND `delete_flag`=0";
		$res = $con->query($sql);
		if ($res) {
			$data['status'] = 1;
			$data['details'] = $res->fetch_assoc();
		} else {
			$data['errors'][] = $con->error;
		}
		return $data;
	}
	function get_product_upload_queue_details($inputdata)
	{
		/* 
		input params - 
			product_queue_id
		function is used to get details of product_upload_queue
		output -  
			$data = array(
				"status" => 0,
				"details" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"details" => array(),
			"errors" => array()
		);
		$sql = "SELECT * FROM `ki_product_upload_queue_info` WHERE `product_queue_id`='" . safe_str($inputdata['product_queue_id']) . "' AND `delete_flag`=0";
		$res = $con->query($sql);
		if ($res) {
			$data['status'] = 1;
			$data['details'] = $res->fetch_assoc();
		} else {
			$data['errors'][] = $con->error;
		}
		return $data;
	}
	function get_product_queue_to_be_processed($inputdata)
	{
		/* 
		input params - 
			"function" => "get_product_queue_to_be_processed"
		function to get current queue to be processed for product import 
			$data = array(
				"status" => 0,
				"errors" => array(),
				"details" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"details" => array(),
			"errors" => array()
		);
		$qry = "SELECT * FROM `ki_product_upload_queue_info` WHERE (`status`=1 OR `status`=0) AND `rows_assigned`<`total_rows` ORDER BY `status` DESC,`created_on` LIMIT 1";
		$result = $con->query($qry);
		if ($result) {
			$data["status"] = 1;
			$data["details"] = $result->fetch_assoc();
		} else {
			$data["errors"][] = $con->error;
		}
		return $data;
	}
	function ProductLogPaggingList($inputdata)
	{
		/* 
		input params - 
			"page_no" => $request["PageNumber"],
			"row_size" => $request["RowSize"],
			"sort_on" => $request["SortOn"],
			"sort_type" => $request["SortType"],
			"location_type" => $_SESSION['ki_user']['location_type'],
			"location_id" => $_SESSION['ki_user']['location_id']
		returns log of product according to passed parameters.
		output - 
			$data = array(
				"status" => 0,
				"total_records" => 0,
				"total_pages" => 0,
				"pagging_list" => array(),
				"errors" => array()
			); 
		*/
		// print_r($inputdata);
		global $con;
		$data = array(
			"status" => 0,
			"total_records" => 0,
			"total_pages" => 0,
			"pagging_list" => array(),
			"errors" => array()
		);
		$page_no = $inputdata['page_no'];
		$row_size = $inputdata['row_size'];
		$sort_on = $inputdata['sort_on'];
		$sort_type = $inputdata['sort_type'];
		$pcount_qry = "SELECT 
							COUNT(*) AS total_count 
						FROM 
							`ki_inventory_stock_valuation_info` AA
						LEFT JOIN `ki_inventory_stock_cost_price_valuation_info` BB ON
							AA.`stock_valuation_id`=BB.`stock_valuation_id` AND BB.`delete_flag`=0
						LEFT JOIN `ki_users_info` UI ON
							AA.`user_id`=UI.`user_id`
						LEFT JOIN `ki_stocktakes_info` STI ON
							AA.`action_type`=3 AND AA.`type_id`=STI.`stocktake_id`
						LEFT JOIN `ki_invoices_info` II ON
							AA.`action_type` IN (4,5,6,7,8,9,10,11,12,19,20,21,22,24) AND AA.`type_id`=II.`invoice_id`
						LEFT JOIN `ki_store_delivery_info` SDI ON
							AA.`action_type` IN (13,14,15,16,17,18) AND AA.`type_id`=SDI.`store_delivery_id`
						LEFT JOIN `ki_jobs_info` JI ON
							AA.`action_type`=23 AND AA.`type_id`=JI.`job_id`
						WHERE 
							AA.`product_id`='" . safe_str($inputdata['product_id']) . "' AND AA.`location_type`='" . safe_str($inputdata['location_type']) . "' AND AA.`location_id`='" . safe_str($inputdata['location_id']) . "' AND AA.`delete_flag`=0
						GROUP BY 
							AA.`stock_valuation_id`";
		$pcount_result = $con->query($pcount_qry);
		if ($pcount_result) {
			$pcount_row = $pcount_result->fetch_assoc();
			$total_records = $pcount_row["total_count"];
			@$total_pages = ceil($total_records / $row_size);
			if ($total_pages == 0) {
				$total_pages = 1;
			}
			if ($page_no > $total_pages) {
				$page_no = $total_pages;
			}
			$limit_from = ($row_size * $page_no) - $row_size;
			$pagg_qry = "SELECT 
							SI.store_delivery_prefix,AA.*, 
							CASE
								WHEN AA.`action_type` IN (1,2) THEN ''
								WHEN AA.`action_type`=3 THEN 'Stocktake'
								WHEN AA.`action_type` IN (4,5,6,7,8,9,10,11,12,19,20,21,22,24) THEN 'Invoice'
								WHEN AA.`action_type` IN (13,14,15,16,17,18) THEN 'Store Delivery'
								WHEN AA.`action_type`=23 THEN 'Job'
							END AS event_type, 
							CASE
								WHEN AA.`action_type`=3 THEN STI.`delete_flag`
								WHEN AA.`action_type` IN (4,5,6,7,8,9,10,11,12,19,20,21,22,24) THEN II.`delete_flag`
								WHEN AA.`action_type` IN (13,14,15,16,17,18) THEN SDI.`delete_flag`
								WHEN AA.`action_type`=23 THEN JI.`delete_flag`
							END AS event_type_delete_flag, 
							CASE
								WHEN AA.`action_type`=3 THEN STI.`stocktake_list_name`
								WHEN AA.`action_type` IN (4,5,6,7,8,9,10,11,12,19,20,21,22,24) THEN II.`invoice_number`
								WHEN AA.`action_type` IN (13,14,15,16,17,18) THEN SDI.`store_delivery_number`
								WHEN AA.`action_type`=23 THEN JI.`job_number`
							END AS event_type_number, 
							CASE
								WHEN AA.`action_type`=3 THEN STI.`stocktake_list_name`
								WHEN AA.`action_type` IN (4,5,6,7,8,9,10,11,12,19,20,21,22,24) THEN II.`invoice_number`
								WHEN AA.`action_type` IN (13,14,15,16,17,18) THEN SDI.`store_delivery_number`
								WHEN AA.`action_type`=23 THEN JI.`job_number`
							END AS event_type_number,
							CONCAT(COALESCE(UI.`first_name`, ''),' ',COALESCE(UI.`last_name`, '')) AS user_name, sell_price,
							SUM((CASE WHEN AA.`soh_after_update`>AA.`soh_before_update` THEN ABS(BB.`quantity`) ELSE 0-ABS(BB.`quantity`) END) *BB.`cost_price`) AS cost_price,
							GROUP_CONCAT( CASE WHEN AA.`soh_after_update`>AA.`soh_before_update` THEN '+' ELSE '-' END, ABS(BB.`quantity`), ' x $', CAST(BB.`cost_price` AS DECIMAL(10, 2)) ) AS cp_tooltip
						FROM 
							`ki_inventory_stock_valuation_info` AA
						LEFT JOIN `ki_inventory_stock_cost_price_valuation_info` BB ON
							AA.`stock_valuation_id`=BB.`stock_valuation_id` AND BB.`delete_flag`=0
						LEFT JOIN `ki_users_info` UI ON
							AA.`user_id`=UI.`user_id`
						LEFT JOIN `ki_stocktakes_info` STI ON
							AA.`action_type`=3 AND AA.`type_id`=STI.`stocktake_id`
						LEFT JOIN `ki_invoices_info` II ON
							AA.`action_type` IN (4,5,6,7,8,9,10,11,12,19,20,21,22,24) AND AA.`type_id`=II.`invoice_id`
						LEFT JOIN `ki_store_delivery_info` SDI ON
							AA.`action_type` IN (13,14,15,16,17,18) AND AA.`type_id`=SDI.`store_delivery_id`
						LEFT JOIN ki_stores_info SI ON
							SI.store_id=SDI.store_id AND SI.delete_flag=0
						LEFT JOIN `ki_jobs_info` JI ON
							AA.`action_type`=23 AND AA.`type_id`=JI.`job_id`
						WHERE 
							AA.`product_id`='" . safe_str($inputdata['product_id']) . "' AND AA.`location_type`='" . safe_str($inputdata['location_type']) . "' AND AA.`location_id`='" . safe_str($inputdata['location_id']) . "' AND AA.`delete_flag`=0
						GROUP BY 
							AA.`stock_valuation_id`
						ORDER BY 
							" . safe_str($sort_on) . " " . safe_str($sort_type) . " 
						LIMIT 
							" . $limit_from . ", " . $row_size;
			$pagg_result = $con->query($pagg_qry);
			if ($pagg_result) {
				$pagg_count = $pagg_result->num_rows;
				$pagging_list = array();
				if ($pagg_count > 0) {
					$i = 0;
					while ($row = $pagg_result->fetch_assoc()) {
						$pagging_list[$i] = $row;
						$i++;
					}
				}
			} else {
				$data['errors'][] = $con->error;
			}
		} else {
			$data['errors'][] = $con->error;
		}
		if (empty($data['errors'])) {
			$data["total_records"] = $total_records;
			$data["total_pages"] = $total_pages;
			$data["pagging_list"] = $pagging_list;
		}
		return $data;
	}
	function GetProductsList($inputdata)
	{
		/* 
		input params - 
			product_id (optional)
		function is used to get list of all products that are not archived
		output -  
			$data = array(
				"status" => 0,
				"list" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"list" => array(),
			"errors" => array()
		);
		$query = "`status`=3";
		if (!empty($inputdata['product_id'])) {
			$query = " (`status`=3 OR `product_id` IN (" . implode(',', $inputdata['product_id']) . ")) ";
		}
		$sql = "SELECT * FROM `ki_products_info` WHERE " . $query . " AND `delete_flag`=0 ORDER BY `product_name` ASC";
		$res = $con->query($sql);
		if ($res) {
			$data['status'] = 1;
			while ($row = $res->fetch_assoc()) {
				$data['list'][] = $row;
			}
		} else {
			$data['errors'][] = $con->error;
		}
		return $data;
	}
	function GetUserCoachingMetricsDetails($inputdata)
	{
		/* 
		input params - 
			user_id
		function is used to get list of all coaching metrics checked for a user
		output -  
			$data = array(
				"status" => 0,
				"list" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"list" => array(),
			"errors" => array()
		);
		$sql = "SELECT * FROM `ki_user_coaching_metrics_info` WHERE `user_id`='" . safe_str($inputdata['user_id']) . "' AND `delete_flag`=0";
		$res = $con->query($sql);
		if ($res) {
			$data['status'] = 1;
			while ($row = $res->fetch_assoc()) {
				$data['list'][$row['type']] = $row['value'];
			}
		} else {
			$data['errors'][] = $con->error;
		}
		return $data;
	}
	function CheckIfUserIsManagerForAnyLocationOrAdmin($inputdata)
	{
		/* 
		input params - 
			locations
		function is used to check if user is manager of any location
		output -  
			$data = array(
				"status" => 0,
				"list" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"list" => array(),
			"errors" => array()
		);
		$stores = implode(',', $inputdata['stores']);
		$distributions = implode(',', $inputdata['distributions']);
		$productions = implode(',', $inputdata['productions']);
		$sql = "SELECT 'Admin' AS type, 0 AS location_type, 0 AS location_id FROM `ki_users_info` WHERE `user_id`='" . safe_str($inputdata['user_id']) . "' AND `is_admin`=1 AND `is_enabled`=1 AND `delete_flag`=0
		UNION
		SELECT CASE WHEN `store_manager_id`='" . safe_str($inputdata['user_id']) . "' THEN 'Location Manager' ELSE 'District Manager' END AS type, 1 AS location_type, `store_id` AS location_id FROM `ki_stores_info` WHERE (`store_manager_id`='" . safe_str($inputdata['user_id']) . "' || `district_manager_id`='" . safe_str($inputdata['user_id']) . "') AND `store_id` IN (" . $stores . ") AND `is_enabled`=1 AND `delete_flag`=0
		UNION
		SELECT 'Location Manager' AS type, 2 AS location_type, `distribution_branch_id` AS location_id FROM `ki_distribution_branches_info` WHERE `manager_id`='" . safe_str($inputdata['user_id']) . "' AND `distribution_branch_id` IN (" . $distributions . ") AND `is_enabled`=1 AND `delete_flag`=0
		UNION
		SELECT 'Location Manager' AS type, 3 AS location_type, `production_id` AS location_id FROM `ki_production_info` WHERE `manager_id`='" . safe_str($inputdata['user_id']) . "' AND `production_id` IN (" . $productions . ") AND `is_enabled`=1 AND `delete_flag`=0";
		$res = $con->query($sql);
		if ($res) {
			$data['status'] = 1;
			while ($row = $res->fetch_assoc()) {
				$data['list'][] = $row;
			}
		} else {
			$data['errors'][] = $con->error;
		}
		return $data;
	}
	function GetUsersDirectReportUsersList($inputdata)
	{
		/* 
		input params - 
			locations
		function is used to get list of all users who can be coached by user for selected locations.
		output -  
			$data = array(
				"status" => 0,
				"list" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"list" => array(),
			"errors" => array()
		);
		$stores = implode(',', $inputdata['stores']);
		$distributions = implode(',', $inputdata['distributions']);
		$productions = implode(',', $inputdata['productions']);
		$sql = "SELECT 
					DISTINCT ULI.`user_id`, CONCAT(COALESCE(`first_name`,''), ' ', COALESCE(`last_name`,'')) AS user_name 
				FROM 
					`ki_user_locations_info` ULI 
				INNER JOIN `ki_users_info` UI ON 
					ULI.`user_id`=UI.`user_id` 
					AND (
						(`location_type`=1 AND `location_id` IN (" . $stores . "))
						OR (`location_type`=2 AND `location_id` IN (" . $distributions . "))
						OR (`location_type`=3 AND `location_id` IN (" . $productions . "))
					)
					AND UI.`is_enabled`=1 
					AND UI.`delete_flag`=0 
					AND ULI.`delete_flag`=0 
					AND ULI.`user_id` NOT IN (
						SELECT `user_id` FROM `ki_users_info` WHERE `is_admin`=1
						UNION
						SELECT COALESCE(`district_manager_id`,0) FROM `ki_stores_info` WHERE (`store_manager_id`='" . safe_str($inputdata['user_id']) . "' OR `district_manager_id`!='" . safe_str($inputdata['user_id']) . "' OR `district_manager_id` IS NULL) AND `store_id` IN (" . $stores . ") AND `is_enabled`=1 AND `delete_flag`=0
						UNION
						SELECT COALESCE(`store_manager_id`,0) FROM `ki_stores_info` WHERE (`store_manager_id`!='" . safe_str($inputdata['user_id']) . "' OR `store_manager_id` IS NULL) AND `district_manager_id`!='" . safe_str($inputdata['user_id']) . "' AND `store_id` IN (" . $stores . ") AND `is_enabled`=1 AND `delete_flag`=0
						UNION
						SELECT COALESCE(`manager_id`,0) FROM `ki_distribution_branches_info` WHERE (`manager_id`!='" . safe_str($inputdata['user_id']) . "'  OR `manager_id` IS NULL) AND `distribution_branch_id` IN (" . $distributions . ") AND `is_enabled`=1 AND `delete_flag`=0
						UNION
						SELECT COALESCE(`manager_id`,0) FROM `ki_production_info` WHERE (`manager_id`!='" . safe_str($inputdata['user_id']) . "' OR `manager_id` IS NULL) AND `production_id` IN (" . $productions . ") AND `is_enabled`=1 AND `delete_flag`=0
						UNION
						SELECT 
							ULI.`user_id` 
						FROM 
							`ki_user_locations_info` ULI 
						INNER JOIN `ki_users_info` UI ON 
							ULI.`user_id`=UI.`user_id` 
							AND (`is_admin`!=1 OR `is_admin` IS NULL)
							AND (`location_type`,`location_id`) IN (
								SELECT  1 AS `location_type`, `store_id` AS location_id FROM `ki_stores_info` WHERE (`store_manager_id`!='" . safe_str($inputdata['user_id']) . "' OR `store_manager_id` IS NULL) AND (`district_manager_id`!='" . safe_str($inputdata['user_id']) . "' OR `district_manager_id` IS NULL) AND `store_id` IN (" . $stores . ") AND `is_enabled`=1 AND `delete_flag`=0
								UNION
								SELECT 2 AS `location_type`, `distribution_branch_id` AS location_id FROM `ki_distribution_branches_info` WHERE (`manager_id`!='" . safe_str($inputdata['user_id']) . "' OR `manager_id` IS NULL) AND `distribution_branch_id` IN (" . $distributions . ") AND `is_enabled`=1 AND `delete_flag`=0
								UNION
								SELECT 3 AS `location_type`, `production_id` AS location_id FROM `ki_production_info` WHERE (`manager_id`!='" . safe_str($inputdata['user_id']) . "' OR `manager_id` IS NULL) AND `production_id` IN (" . $productions . ") AND `is_enabled`=1 AND `delete_flag`=0
							)
							AND ULI.`user_id` NOT IN (
								SELECT `user_id` FROM `ki_user_locations_info` WHERE `delete_flag`=0 AND (`location_type`,`location_id`) IN (
									SELECT  1 AS `location_type`, `store_id` AS location_id FROM `ki_stores_info` WHERE (`store_manager_id`='" . safe_str($inputdata['user_id']) . "' OR `district_manager_id`='" . safe_str($inputdata['user_id']) . "') AND `store_id` IN (" . $stores . ") AND `is_enabled`=1 AND `delete_flag`=0
									UNION
									SELECT 2 AS `location_type`, `distribution_branch_id` AS location_id FROM `ki_distribution_branches_info` WHERE `manager_id`='" . safe_str($inputdata['user_id']) . "' AND `distribution_branch_id` IN (" . $distributions . ") AND `is_enabled`=1 AND `delete_flag`=0
									UNION
									SELECT 3 AS `location_type`, `production_id` AS location_id FROM `ki_production_info` WHERE `manager_id`='" . safe_str($inputdata['user_id']) . "' AND `production_id` IN (" . $productions . ") AND `is_enabled`=1 AND `delete_flag`=0
								)
							)
					)
				ORDER BY
					user_name";
		// echo "<pre>";
		$res = $con->query($sql);
		if ($res) {
			$data['status'] = 1;
			while ($row = $res->fetch_assoc()) {
				$data['list'][] = $row;
			}
		} else {
			$data['errors'][] = $con->error;
		}
		return $data;
	}
	function CreateLocationTargetsHistory($inputdata)
	{
		/* 
		input params -
			"targets", location_type, location_id
		Function is used to create entry in 
		output - 
			$data = array(
				"status" => 0,
				"id" => 0,
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"id" => 0,
			"errors" => array()
		);
		$in_fields = array();
		foreach ($inputdata['targets'] as $field_key => $field_data) {
			$in_fields[] = "(" . $inputdata['location_type'] . ", " . $inputdata['location_id'] . ", " . $field_key . ", " . $field_data . ", '" . date("Y-m-d H:i:s") . "')";
		}
		$in_query = "INSERT INTO `ki_location_targets_history_info` (`location_type`, `location_id`, `target_type`, `target_value`, `created_on`) VALUES " . implode(", ", $in_fields);
		$in_result = $con->query($in_query);
		if ($in_result) {
			$data["status"] = 1;
			$data["id"] = $con->insert_id;
		} else {
			$data["errors"][] = $con->error;
		}
		return $data;
	}
	function GetLatestTargetValueForLocation($inputdata)
	{
		/* 
		input params - 
			location_type, location_id, target_type
		function is used to get list of all daily_report_users for a user
		output -  
			$data = array(
				"status" => 0,
				"list" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"list" => array(),
			"errors" => array()
		);
		$sql = "SELECT
					`target_type`,
					`target_value`
				FROM
					`ki_location_targets_history_info` A
				WHERE
					`location_type`='" . safe_str($inputdata['location_type']) . "' AND `location_id`='" . safe_str($inputdata['location_id']) . "' AND `delete_flag`=0 AND `created_on`=(
						SELECT
							MAX(`created_on`)
						FROM
							`ki_location_targets_history_info`
						GROUP BY
							`target_type`
						HAVING
							`target_type`=A.`target_type`
					)";
		$res = $con->query($sql);
		if ($res) {
			$data['status'] = 1;
			while ($row = $res->fetch_assoc()) {
				$data['list'][$row['target_type']] = $row['target_value'];
			}
		} else {
			$data['list'][] = $con->error;
		}
		return $data;
	}
	function GetSelectedDirectReportUsersList($inputdata)
	{
		/* 
		input params - 
			user_id
		function is used to get list of all daily_report_users for a user
		output -  
			$data = array(
				"status" => 0,
				"list" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"list" => array(),
			"errors" => array()
		);
		$sql = "SELECT * FROM `ki_users_direct_report_users_info` WHERE `user_id`='" . safe_str($inputdata['user_id']) . "' AND `delete_flag`=0";
		$res = $con->query($sql);
		if ($res) {
			$data['status'] = 1;
			while ($row = $res->fetch_assoc()) {
				$data['list'][] = $row;
			}
		} else {
			$data['errors'][] = $con->error;
		}
		return $data;
	}
	function GetDailyStatsMailInfo($inputdata)
	{
		/* 
		input params - 
			location_type, location_id, user_id, from_date, to_date, traffic_count
		function is used to get fields info for which daily_update_mail is to be sent 
		output -  
			$data = array(
				"status" => 0,
				"details" => array(),
				"allowed_users" => array(),
				"errors" => array(),
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"details" => array(
				"store" => array(
					"store_GP" => 0,
					"store_conversion_rate" => 0,
					"store_gp_percent" => 0,
					"store_avg_sale" => 0,
					"store_repeat_rate" => 0,
					"store_recovery_per_hour" => 0,
					"jobs_finished" => 0,
					"team_recovered_time" => 0,
					"time_to_resolve_tickets" => 0,
					"comments_count" => 0,
					"canned_comments_count" => 0
				),
				"personal" => array()
			),
			"allowed_users" => array(),
			"errors" => array()
		);
		// echo "<pre>";
		/* STORE ENABLED ROSTERED USERS */
		$sql = "SELECT 
					UI.*, A.`user_id`, SUM(rostered_time) AS rostered_time 
				FROM (
					SELECT *, CASE WHEN ((TIMESTAMPDIFF(MINUTE, `start_time`, `finish_time`)/60)>5) THEN (TIMESTAMPDIFF(MINUTE, `start_time`, `finish_time`)-30) ELSE TIMESTAMPDIFF(MINUTE, `start_time`, `finish_time`) END AS rostered_time FROM `ki_roster_data_info`
				) A 
				INNER JOIN `ki_rosters_info` B ON 
					A.`roster_id`=B.`roster_id` AND B.`location_type`='" . safe_str($inputdata['location_type']) . "' AND B.`location_id`='" . safe_str($inputdata['location_id']) . "' AND B.`delete_flag`=0 
				INNER JOIN `ki_users_info` UI ON
					A.`user_id`=UI.`user_id` AND UI.`is_enabled`=1 AND UI.`delete_flag`=0
				WHERE 
					(A.`status`=1 OR A.`status`=4) AND `date` BETWEEN DATE('" . safe_str($inputdata['from_date']) . "') AND DATE('" . safe_str($inputdata['to_date']) . "') AND A.`delete_flag`=0 
				GROUP BY 
					A.`user_id`";
		$res = $con->query($sql);
		if ($res) {
			while ($row = $res->fetch_assoc()) {
				$data['allowed_users'][] = $row;
			}
		}
		/* CONVERSION RATE */
		if (!empty($inputdata['traffic_count'])) {
			$sql = "SELECT
						(MAX(invoice_count)+ MAX(estimate_count)) AS invoice_estimate_count 
					FROM (
						SELECT 
							COUNT(*) AS invoice_count, 0 AS estimate_count 
						FROM 
							`ki_invoices_info` 
						WHERE 
							`is_draft`=0 AND (`estimate_id`=0 OR `estimate_id`='' OR `estimate_id` IS NULL) AND (`job_id`=0 OR `job_id`='' OR `job_id` IS NULL) AND `home_store_type`='" . safe_str($inputdata['location_type']) . "' AND `home_store_id`='" . safe_str($inputdata['location_id']) . "' AND `created_on` BETWEEN '" . safe_str($inputdata['from_date']) . "' AND '" . safe_str($inputdata['to_date']) . "' AND `delete_flag`=0 
						
						UNION 
						
						SELECT 
							0 AS invoice_count, COUNT(*) AS estimate_count 
						FROM 
							`ki_estimates_info` EI 
						LEFT JOIN `ki_jobs_info` JI ON 
							EI.`job_id`=JI.`job_id` AND JI.`delete_flag`=0 
						WHERE 
							(JI.`status`=0 OR JI.`status`='' OR JI.`status` IS NULL OR (JI.`status`!=10 AND JI.`status`!=11 AND JI.`is_cancelled`!=1)) AND EI.`status`=2 AND EI.`home_store_type`='" . safe_str($inputdata['location_type']) . "' AND EI.`home_store_id`='" . safe_str($inputdata['location_id']) . "' AND EI.`created_on` BETWEEN '" . safe_str($inputdata['from_date']) . "' AND '" . safe_str($inputdata['to_date']) . "' AND EI.`delete_flag`=0 
					) AA";
			$res = $con->query($sql);
			if ($res) {
				$row = $res->fetch_assoc();
				$data['details']['store']['store_conversion_rate'] = ($row['invoice_estimate_count'] / $inputdata['traffic_count']) * 100;
			}
		} else {
			$data['details']['store']['store_conversion_rate'] = 0;
		}
		$sql = "SELECT 
					SUM(AT1.sales) AS sales, AT1.`user_id`, AT1.`first_name`, AT1.`email`, SUM(AT1.gross_profit) AS gross_profit, (SUM(AT1.gross_profit)/SUM(AT1.sales))*100 AS gross_profit_margin, SUM(AT1.sales_count) AS sales_count, SUM(quantity_count) AS quantity_count  
				FROM (
					SELECT 
						SUM(`total_including_GST`-`GST`) AS sales, UI.`user_id`, UI.`first_name`, UI.`email`, SUM(II.`profit`) AS gross_profit, COUNT(*) AS sales_count, SUM(quantity_count) AS quantity_count 
					FROM
						`ki_invoices_info` II
					INNER JOIN (
						SELECT `invoice_id`, SUM(`quantity`) AS quantity_count FROM `ki_invoice_line_items_info` WHERE `delete_flag`=0 GROUP BY `invoice_id`
					) ILI ON
						II.`invoice_id`=ILI.`invoice_id`
					INNER JOIN `ki_users_info` UI ON 
						II.`user_id`=UI.`user_id` AND UI.`delete_flag`=0
					WHERE 
						II.`is_draft`=0 AND (`estimate_id` IS NULL OR `estimate_id`='') AND (`certificate_of_work_id` IS NULL OR `certificate_of_work_id`='') AND II.`home_store_type`='" . safe_str($inputdata['location_type']) . "' AND II.`home_store_id`='" . safe_str($inputdata['location_id']) . "' AND II.`created_on` BETWEEN '" . safe_str($inputdata['from_date']) . "' AND '" . safe_str($inputdata['to_date']) . "' AND II.`delete_flag`=0 
					GROUP BY 
						II.`user_id`
					
					UNION 
					
					SELECT 
						SUM(price_ex_gst*quantity) AS sales, UI.`user_id`, UI.`first_name`, UI.`email`, SUM(ILI.`profit`)-(((SUM(ILI.`profit`)*100)/(profit_ex_loyalty_disc+`used_loyalty_credits`))*`used_loyalty_credits`) AS gross_profit, COUNT(DISTINCT `invoice_id`) AS sales_count, SUM(quantity) AS quantity_count 
					FROM (
						SELECT
							II.`job_id`, II.`profit` as profit_ex_loyalty_disc, II.`used_loyalty_credits`, ILI1.`invoice_id`, 
							CASE 
								WHEN ILI1.`estimate_id` IS NOT NULL AND ILI1.`estimate_id`!=0 THEN EI.`user_id`
								ELSE II.`user_id`
							END AS `user_id`, 
							CASE 
								WHEN II.`home_store_type`=1 AND II.`amounts_include_gst`=1 THEN ((100*`sell_price`)/(100+`line_retail_tax`))
								ELSE `sell_price` 
							END AS price_ex_gst, `quantity`,
							CASE 
								WHEN II.`home_store_type`=1 AND II.`amounts_include_gst`=1 THEN CAST((((100*`sell_price`)/(100+`line_retail_tax`))-`line_distribution_price`)*`quantity` AS DECIMAL(10, 2)) 
								WHEN II.`home_store_type`=1 AND II.`amounts_include_gst`=0 THEN CAST(((`sell_price`)-`line_distribution_price`)*`quantity` AS DECIMAL(10, 2)) 
								ELSE (`sell_price`-`line_cost_price`)*`quantity` 
							END AS profit 
						FROM 
							`ki_invoice_line_items_info` ILI1 
						INNER JOIN `ki_invoices_info` II ON
							ILI1.`invoice_id`=II.`invoice_id` AND II.`delete_flag`=0
						LEFT JOIN `ki_estimates_info` EI ON
							ILI1.`estimate_id`=EI.`estimate_id` AND EI.`delete_flag`=0
						WHERE
							II.`is_draft`=0 AND ((II.`estimate_id` IS NOT NULL AND II.`estimate_id`!='' AND II.`estimate_id`!=0) OR (II.`certificate_of_work_id` IS NOT NULL AND II.`certificate_of_work_id`!='' AND II.`certificate_of_work_id`!=0)) AND II.`home_store_type`='" . safe_str($inputdata['location_type']) . "' AND II.`home_store_id`='" . safe_str($inputdata['location_id']) . "' AND II.`created_on` BETWEEN '" . safe_str($inputdata['from_date']) . "' AND '" . safe_str($inputdata['to_date']) . "' AND II.`delete_flag`=0 
					) ILI
					INNER JOIN `ki_users_info` UI ON 
						ILI.`user_id` = UI.`user_id` AND UI.`delete_flag`=0 
					LEFT JOIN `ki_jobs_info` JI ON 
						ILI.`job_id` = JI.`job_id`
					WHERE 
						ILI.`job_id`=0 OR ILI.`job_id` IS NULL OR (JI.`delete_flag`=0 AND JI.`job_type`=1 AND (JI.`status`=0 OR JI.`status`='' OR JI.`status` IS NULL OR (JI.`status`!=10 AND JI.`status`!=11 AND JI.`is_cancelled`!=1)))
					GROUP BY 
						ILI.`user_id`
					
				) AT1 
				GROUP BY 
					AT1.`user_id`";
		$res = $con->query($sql);
		$sales = $gp = $sales_count = 0;
		if ($res) {
			while ($row = $res->fetch_assoc()) {
				$data['details']['personal'][$row['user_id']]['user_id'] = $row['user_id'];
				$data['details']['personal'][$row['user_id']]['first_name'] = $row['first_name'];
				$data['details']['personal'][$row['user_id']]['email'] = $row['email'];
				$data['details']['personal'][$row['user_id']]['personal_gp'] = $row['gross_profit'];
				$data['details']['personal'][$row['user_id']]['personal_gp_percent'] = $row['gross_profit_margin'];
				$data['details']['personal'][$row['user_id']]['personal_avg_sale'] = (!empty($row['sales_count'])) ? ($row['sales'] / $row['sales_count']) : 0;
				$sales += $row['sales'];
				$gp += $row['gross_profit'];
				$sales_count += $row['sales_count'];
			}
			$data['details']['store']['store_GP'] = $gp;
			$data['details']['store']['store_gp_percent'] = (!empty($sales)) ? ($gp / $sales) * 100 : 0;
			$data['details']['store']['store_avg_sale'] = (!empty($sales_count)) ? ($sales / $sales_count) : 0;
		}
		$sql = "SELECT 
					AT1.`user_id`, SUM(AT1.sales_count) AS sales_count 
				FROM (
					SELECT 
						`user_id`, COUNT(*) AS sales_count
					FROM
						`ki_invoices_info`
					WHERE 
						`is_draft`=0 AND (`estimate_id` IS NULL OR `estimate_id`='') AND (`certificate_of_work_id` IS NULL OR `certificate_of_work_id`='') AND `home_store_type`='" . safe_str($inputdata['location_type']) . "' AND `home_store_id`='" . safe_str($inputdata['location_id']) . "' AND `created_on` BETWEEN '" . safe_str(date('Y-m-d H:i:s', strtotime('-30 days', strtotime($inputdata['from_date'])))) . "' AND '" . safe_str($inputdata['to_date']) . "' AND `delete_flag`=0 
					GROUP BY 
						`user_id`
					
					UNION ALL
					
					SELECT 
						ILI.`user_id`, COUNT(DISTINCT `invoice_id`) AS sales_count
					FROM (
						SELECT
							II.`job_id`, ILI1.`invoice_id`, 
							CASE 
								WHEN ILI1.`estimate_id` IS NOT NULL AND ILI1.`estimate_id`!=0 THEN EI.`user_id`
								ELSE II.`user_id`
							END AS `user_id`
						FROM 
							`ki_invoice_line_items_info` ILI1 
						INNER JOIN `ki_invoices_info` II ON
							ILI1.`invoice_id`=II.`invoice_id` AND II.`delete_flag`=0
						LEFT JOIN `ki_estimates_info` EI ON
							ILI1.`estimate_id`=EI.`estimate_id` AND EI.`delete_flag`=0
						WHERE
							II.`is_draft`=0 AND ((II.`estimate_id` IS NOT NULL AND II.`estimate_id`!='' AND II.`estimate_id`!=0) OR (II.`certificate_of_work_id` IS NOT NULL AND II.`certificate_of_work_id`!='' AND II.`certificate_of_work_id`!=0)) AND II.`home_store_type`='" . safe_str($inputdata['location_type']) . "' AND II.`home_store_id`='" . safe_str($inputdata['location_id']) . "' AND II.`created_on` BETWEEN '" . safe_str(date('Y-m-d H:i:s', strtotime('-30 days', strtotime($inputdata['from_date'])))) . "' AND '" . safe_str($inputdata['to_date']) . "' AND II.`delete_flag`=0 
					) ILI
					LEFT JOIN `ki_jobs_info` JI ON 
						ILI.`job_id` = JI.`job_id`
					WHERE 
						ILI.`job_id`=0 OR ILI.`job_id` IS NULL OR (JI.`delete_flag`=0 AND JI.`job_type`=1 AND (JI.`status`=0 OR JI.`status`='' OR JI.`status` IS NULL OR (JI.`status`!=10 AND JI.`status`!=11 AND JI.`is_cancelled`!=1)))
					GROUP BY 
						ILI.`user_id`
					
				) AT1 
				GROUP BY 
					AT1.`user_id`";
		$res = $con->query($sql);
		$store_sales_30_days = 0;
		$sales_30_days = array();
		if ($res) {
			while ($row = $res->fetch_assoc()) {
				$sales_30_days[$row['user_id']] = $row['sales_count'];
				$store_sales_30_days = $store_sales_30_days + $row['sales_count'];
			}
		}
		/* $sql = "SELECT 
					`user_id`, COUNT(*) AS returning_clients 
				FROM (
					SELECT 
						DISTINCT `user_id`, customer_id 
					FROM 
						ki_invoices_info 
					WHERE 
						customer_id!=0 AND customer_id IS NOT NULL AND `home_store_type`='".safe_str($inputdata['location_type'])."' AND `home_store_id`='".safe_str($inputdata['location_id'])."' AND `created_on` BETWEEN '".safe_str(date('Y-m-d H:i:s',strtotime('-30 days',strtotime($inputdata['from_date']))))."' AND '".safe_str($inputdata['to_date'])."' AND `delete_flag`=0 
					
					UNION ALL 
					
					SELECT 
						DISTINCT `user_id`, customer_id 
					FROM 
						ki_invoices_info 
					WHERE 
						`home_store_type`='".safe_str($inputdata['location_type'])."' AND `home_store_id`='".safe_str($inputdata['location_id'])."' AND (customer_id IS NULL OR customer_id=0) AND `created_on` BETWEEN '".safe_str(date('Y-m-d H:i:s',strtotime('-30 days',strtotime($inputdata['from_date']))))."' AND '".safe_str($inputdata['to_date'])."' AND `delete_flag`=0 
				) AA
				GROUP BY 
					AA.`user_id`"; */
		$sql = "SELECT 
					AT1.`user_id`, AT1.user_name, SUM(AT1.customer_count) AS customer_count 
				FROM (
					SELECT 
						`user_id`, `user_name`, COUNT(`customer_id`) AS customer_count
					FROM (
						SELECT 
							UI.`user_id`, CONCAT(COALESCE(UI.`first_name`, ''), ' ', COALESCE(UI.`last_name`, '')) AS user_name, II.`customer_id`
						FROM
							`ki_invoices_info` II
						INNER JOIN `ki_users_info` UI ON 
							II.`user_id`=UI.`user_id` AND UI.`delete_flag`=0
						WHERE 
							II.`is_draft`=0 AND (`estimate_id` IS NULL OR `estimate_id`='') AND (`certificate_of_work_id` IS NULL OR `certificate_of_work_id`='') AND II.`customer_id`!=0 AND II.`customer_id` IS NOT NULL AND II.`home_store_type`='" . safe_str($inputdata['location_type']) . "' AND II.`home_store_id`='" . safe_str($inputdata['location_id']) . "' AND II.`created_on` BETWEEN '" . safe_str(date('Y-m-d H:i:s', strtotime('-30 days', strtotime($inputdata['from_date'])))) . "' AND '" . safe_str($inputdata['to_date']) . "' AND II.`delete_flag`=0 
						
						UNION 
						
						SELECT 
							UI.`user_id`, CONCAT(COALESCE(UI.`first_name`, ''), ' ', COALESCE(UI.`last_name`, '')) AS user_name, ILI.`customer_id`
						FROM (
							SELECT
								DISTINCT II.`job_id`, ILI1.`invoice_id`, II.`customer_id`, 
								CASE 
									WHEN ILI1.`estimate_id` IS NOT NULL AND ILI1.`estimate_id`!=0 AND EI.`user_id` IS NOT NULL AND EI.`user_id`!=0 THEN EI.`user_id`
									ELSE II.`user_id`
								END AS `user_id` 
							FROM 
								`ki_invoice_line_items_info` ILI1 
							INNER JOIN `ki_invoices_info` II ON
								ILI1.`invoice_id`=II.`invoice_id` AND II.`delete_flag`=0
							LEFT JOIN `ki_estimates_info` EI ON
								ILI1.`estimate_id`=EI.`estimate_id` AND EI.`delete_flag`=0
							WHERE
								II.`is_draft`=0 AND ((II.`estimate_id` IS NOT NULL AND II.`estimate_id`!='' AND II.`estimate_id`!=0) OR (II.`certificate_of_work_id` IS NOT NULL AND II.`certificate_of_work_id`!='' AND II.`certificate_of_work_id`!=0)) AND II.`customer_id`!=0 AND II.`customer_id` IS NOT NULL AND II.`home_store_type`='" . safe_str($inputdata['location_type']) . "' AND II.`home_store_id`='" . safe_str($inputdata['location_id']) . "' AND II.`created_on` BETWEEN '" . safe_str(date('Y-m-d H:i:s', strtotime('-30 days', strtotime($inputdata['from_date'])))) . "' AND '" . safe_str($inputdata['to_date']) . "' AND ILI1.`delete_flag`=0
						) ILI
						INNER JOIN `ki_users_info` UI ON 
							ILI.`user_id` = UI.`user_id` AND UI.`delete_flag`=0 
						LEFT JOIN `ki_jobs_info` JI ON 
							ILI.`job_id` = JI.`job_id`
						WHERE 
							ILI.`job_id`=0 OR ILI.`job_id` IS NULL OR (JI.`delete_flag`=0 AND JI.`job_type`=1 AND (JI.`status`=0 OR JI.`status`='' OR JI.`status` IS NULL OR (JI.`status`!=10 AND JI.`status`!=11 AND JI.`is_cancelled`!=1)))
					) AA
					GROUP BY 
						AA.`user_id`
					
					UNION
					
					SELECT 
						`user_id`, `user_name`, COUNT(`customer_id`) AS customer_count
					FROM (
						SELECT 
							UI.`user_id`, CONCAT(COALESCE(UI.`first_name`, ''), ' ', COALESCE(UI.`last_name`, '')) AS user_name, II.`customer_id`
						FROM
							`ki_invoices_info` II
						INNER JOIN `ki_users_info` UI ON 
							II.`user_id`=UI.`user_id` AND UI.`delete_flag`=0
						WHERE 
							II.`is_draft`=0 AND (`estimate_id` IS NULL OR `estimate_id`='') AND (`certificate_of_work_id` IS NULL OR `certificate_of_work_id`='') AND (II.`customer_id`=0 OR II.`customer_id` IS NULL) AND II.`home_store_type`='" . safe_str($inputdata['location_type']) . "' AND II.`home_store_id`='" . safe_str($inputdata['location_id']) . "' AND II.`created_on` BETWEEN '" . safe_str(date('Y-m-d H:i:s', strtotime('-30 days', strtotime($inputdata['from_date'])))) . "' AND '" . safe_str($inputdata['to_date']) . "' AND II.`delete_flag`=0 
						
						UNION 
						
						SELECT 
							UI.`user_id`, CONCAT(COALESCE(UI.`first_name`, ''), ' ', COALESCE(UI.`last_name`, '')) AS user_name, ILI.`customer_id`
						FROM (
							SELECT
								DISTINCT II.`job_id`, ILI1.`invoice_id`, II.`customer_id`, 
								CASE 
									WHEN ILI1.`estimate_id` IS NOT NULL AND ILI1.`estimate_id`!=0 AND EI.`user_id` IS NOT NULL AND EI.`user_id`!=0 THEN EI.`user_id`
									ELSE II.`user_id`
								END AS `user_id`
							FROM 
								`ki_invoice_line_items_info` ILI1 
							INNER JOIN `ki_invoices_info` II ON
								ILI1.`invoice_id`=II.`invoice_id` AND II.`delete_flag`=0
							LEFT JOIN `ki_estimates_info` EI ON
								ILI1.`estimate_id`=EI.`estimate_id` AND EI.`delete_flag`=0
							WHERE
								II.`is_draft`=0 AND ((II.`estimate_id` IS NOT NULL AND II.`estimate_id`!='' AND II.`estimate_id`!=0) OR (II.`certificate_of_work_id` IS NOT NULL AND II.`certificate_of_work_id`!='' AND II.`certificate_of_work_id`!=0)) AND (II.`customer_id`=0 OR II.`customer_id` IS NULL) AND II.`home_store_type`='" . safe_str($inputdata['location_type']) . "' AND II.`home_store_id`='" . safe_str($inputdata['location_id']) . "' AND II.`created_on` BETWEEN '" . safe_str(date('Y-m-d H:i:s', strtotime('-30 days', strtotime($inputdata['from_date'])))) . "' AND '" . safe_str($inputdata['to_date']) . "' AND ILI1.`delete_flag`=0
						) ILI
						INNER JOIN `ki_users_info` UI ON 
							ILI.`user_id` = UI.`user_id` AND UI.`delete_flag`=0 
						LEFT JOIN `ki_jobs_info` JI ON 
							ILI.`job_id` = JI.`job_id`
						WHERE 
							ILI.`job_id`=0 OR ILI.`job_id` IS NULL OR (JI.`delete_flag`=0 AND JI.`job_type`=1 AND (JI.`status`=0 OR JI.`status`='' OR JI.`status` IS NULL OR (JI.`status`!=10 AND JI.`status`!=11 AND JI.`is_cancelled`!=1)))
					) BB
					GROUP BY 
						BB.`user_id`
				) AT1 
				GROUP BY 
					AT1.`user_id`";
		$res = $con->query($sql);
		$store_returning_clients = 0;
		if ($res) {
			while ($row = $res->fetch_assoc()) {
				$data['details']['personal'][$row['user_id']]['personal_repeat_rate'] = (!empty($row['customer_count']) && !empty($sales_30_days[$row['user_id']])) ? p_round($sales_30_days[$row['user_id']] / $row['customer_count']) : 0;
				$store_returning_clients = $store_returning_clients + $row['customer_count'];
			}
			$data['details']['store']['store_repeat_rate'] = (!empty($store_returning_clients) && !empty($store_sales_30_days)) ? p_round($store_sales_30_days / $store_returning_clients) : 0;
		}
		/* RECOVERY PER HOUR */
		$sql = "SELECT 
					CASE 
						WHEN II.`home_store_type`=1 AND `amounts_include_gst`=1 THEN SUM((((100*`sell_price`)/(100+ILI.`line_retail_tax`))-ILI.`line_distribution_price`)*ILI.`quantity`)/rostered_time
						WHEN II.`home_store_type`=1 AND `amounts_include_gst`=0 THEN SUM(((ILI.`sell_price`)-ILI.`line_distribution_price`)*ILI.`quantity`)/rostered_time
						ELSE SUM((ILI.`sell_price`-ILI.`line_cost_price`)*ILI.`quantity`)/rostered_time
					END AS `recovery_per_hour`
				FROM 
					`ki_invoice_line_items_info` ILI
				INNER JOIN `ki_invoices_info` II ON
					ILI.`invoice_id`=II.`invoice_id` AND II.`delete_flag`=0
				INNER JOIN `ki_jobs_info` JI ON
					II.`job_id` = JI.`job_id` AND JI.`home_store_type`='" . safe_str($inputdata['location_type']) . "' AND JI.`home_store_id`='" . safe_str($inputdata['location_id']) . "' AND JI.`status`!=10 AND JI.`status`!=11 AND JI.`is_cancelled`!=1 AND JI.`delete_flag`=0
				INNER JOIN `ki_job_certificate_of_work_items_info` CWLI ON
					ILI.`certificate_item_id`=CWLI.`certificate_item_id` AND CWLI.`delete_flag`=0 AND CWLI.`created_on` BETWEEN '" . safe_str($inputdata['from_date']) . "' AND '" . safe_str($inputdata['to_date']) . "' 
				LEFT JOIN ( 
					SELECT 
						A.`user_id`, SUM(rostered_time)/60 AS rostered_time 
					FROM (
						SELECT *, CASE WHEN ((TIMESTAMPDIFF(MINUTE, `start_time`, `finish_time`)/60)>5) THEN (TIMESTAMPDIFF(MINUTE, `start_time`, `finish_time`)-30) ELSE TIMESTAMPDIFF(MINUTE, `start_time`, `finish_time`) END AS rostered_time FROM `ki_roster_data_info` WHERE `delete_flag`=0
					) A 
					INNER JOIN `ki_rosters_info` B ON 
						A.`roster_id`=B.`roster_id` AND B.`location_type`='" . safe_str($inputdata['location_type']) . "' AND B.`location_id`='" . safe_str($inputdata['location_id']) . "' AND B.`delete_flag`=0 
					WHERE 
						A.`delete_flag`=0 AND `date` BETWEEN DATE('" . safe_str($inputdata['from_date']) . "') AND DATE('" . safe_str($inputdata['to_date']) . "')
					GROUP BY 
						A.`user_id` 
				) RDI ON 
					CWLI.`user_id` = RDI.`user_id` 
				WHERE
					II.`is_draft`=0 AND ILI.`delete_flag`=0";
		$res = $con->query($sql);
		if ($res) {
			$row = $res->fetch_assoc();
			$data['details']['store']['store_recovery_per_hour'] = $row['recovery_per_hour'];
		}
		/* JOBS THAT WENT OVERDUE */
		$sql = "SELECT 
					`assigned_tech` AS user_id, UI.`first_name`, UI.`email`, COUNT(*) AS jobs_that_went_overdue 
				FROM 
					`ki_jobs_info` JI 
				LEFT JOIN (
					SELECT `job_id`,`created_on` FROM `ki_job_certificate_of_work_info` WHERE `delete_flag`=0 GROUP BY `job_id`	
				) CWI ON 
					JI.`job_id`=CWI.`job_id`
				INNER JOIN `ki_users_info` UI ON 
					JI.`assigned_tech`=UI.`user_id` AND UI.`delete_flag`=0 
				WHERE 
					CWI.`created_on`>JI.`due_date` AND `home_store_type`='" . safe_str($inputdata['location_type']) . "' AND `home_store_id`='" . safe_str($inputdata['location_id']) . "' AND `status`!=10 AND `status`!=11 AND `is_cancelled`!=1 AND JI.`delete_flag`=0 AND JI.`created_on` BETWEEN '" . safe_str($inputdata['from_date']) . "' AND '" . safe_str($inputdata['to_date']) . "'
				GROUP BY 
					`assigned_tech`";
		$res = $con->query($sql);
		if ($res) {
			while ($row = $res->fetch_assoc()) {
				$data['details']['personal'][$row['user_id']]['user_id'] = $row['user_id'];
				$data['details']['personal'][$row['user_id']]['first_name'] = $row['first_name'];
				$data['details']['personal'][$row['user_id']]['email'] = $row['email'];
				$data['details']['personal'][$row['user_id']]['tickets_overdue'] = $row['jobs_that_went_overdue'];
			}
		}
		/* TIME TO COMPLETE TICKETS */
		$sql = "SELECT 
					`assigned_tech` AS user_id, COUNT(JI.`job_id`) AS total_jobs_to_resolve, SUM(TIMESTAMPDIFF(MINUTE, JI.`created_on`, CWI.`created_on`)/60) AS total_time_to_resolve
				FROM 
					`ki_jobs_info` JI 
				LEFT JOIN `ki_job_certificate_of_work_info` CWI ON 
					JI.`job_id`=CWI.`job_id` AND CWI.`delete_flag`=0 
				WHERE 
					`home_store_type`='" . safe_str($inputdata['location_type']) . "' AND `home_store_id`='" . safe_str($inputdata['location_id']) . "' AND `status`!=10 AND `status`!=11 AND `is_cancelled`!=1 AND JI.`delete_flag`=0 AND JI.`created_on` BETWEEN '" . safe_str(date('Y-m-d H:i:s', strtotime('-30 days', strtotime($inputdata['from_date'])))) . "' AND '" . safe_str($inputdata['to_date']) . "'
				GROUP BY 
					`assigned_tech`";
		$res = $con->query($sql);
		$total_jobs_to_resolve = $total_time_to_resolve = 0;
		if ($res) {
			while ($row = $res->fetch_assoc()) {
				$total_jobs_to_resolve = $total_jobs_to_resolve + $row['total_jobs_to_resolve'];
				$total_time_to_resolve = $total_time_to_resolve + $row['total_time_to_resolve'];
			}
		}
		$data['details']['store']['time_to_resolve_tickets'] = (!empty($total_jobs_to_resolve)) ? p_round($total_time_to_resolve / $total_jobs_to_resolve) : 0;
		/* RECOVERED TIME */
		$sql = "SELECT 
					COALESCE(rostered_time, 0) as rostered_time, 
					CASE 
						WHEN rostered_time=0 OR rostered_time IS NULL THEN 0
						ELSE COALESCE((total_allocated/rostered_time),0) * 100
					END AS recovered_time 
				FROM (
					SELECT 
						JI1.`job_id`, XX.`user_id`, `home_store_id`, COALESCE(SUM(XX.`allocated_time`)+10,0) AS total_allocated 
					FROM 
						`ki_jobs_info` JI1
					INNER JOIN (
						SELECT 
							DISTINCT `job_id`, `user_id`, SUM(PI.`minutes_to_complete`*CWLI.`quantity`) AS allocated_time
						FROM 
							`ki_job_certificate_of_work_items_info` CWLI
						INNER JOIN `ki_job_certificate_of_work_info` JCI ON
							CWLI.`certificate_of_work_id`=JCI.`certificate_of_work_id` AND JCI.`delete_flag`=0
						INNER JOIN `ki_products_info` PI ON 
							CWLI.`product_id`=PI.`product_id` AND PI.`delete_flag`=0
						WHERE
							CWLI.`delete_flag`=0 AND CWLI.`created_on` BETWEEN '" . safe_str($inputdata['from_date']) . "' AND '" . safe_str($inputdata['to_date']) . "' 
						GROUP BY 
							`job_id`, `user_id`
					) XX ON 
						XX.`job_id`=JI1.`job_id` 
					WHERE 
						`home_store_type`='" . safe_str($inputdata['location_type']) . "' AND `home_store_id`='" . safe_str($inputdata['location_id']) . "' AND `status`!=10 AND `status`!=11 AND `is_cancelled`!=1 AND JI1.`delete_flag`=0 
					GROUP BY 
						XX.`user_id`
				) JI 
				LEFT JOIN ( 
					SELECT 
						A.`user_id`, SUM(rostered_time)-30 AS rostered_time 
					FROM (
						SELECT *, CASE WHEN ((TIMESTAMPDIFF(MINUTE, `start_time`, `finish_time`)/60)>5) THEN (TIMESTAMPDIFF(MINUTE, `start_time`, `finish_time`)-30) ELSE TIMESTAMPDIFF(MINUTE, `start_time`, `finish_time`) END AS rostered_time FROM `ki_roster_data_info` WHERE `delete_flag`=0
					) A 
					INNER JOIN `ki_rosters_info` B ON 
						A.`roster_id`=B.`roster_id` AND B.`location_type`='" . safe_str($inputdata['location_type']) . "' AND B.`location_id`='" . safe_str($inputdata['location_id']) . "' AND B.`delete_flag`=0 
					WHERE 
						A.`delete_flag`=0 AND `date` BETWEEN DATE('" . safe_str($inputdata['from_date']) . "') AND DATE('" . safe_str($inputdata['to_date']) . "') 
					GROUP BY 
						A.`user_id` 
				) RDI ON 
					JI.`user_id` = RDI.`user_id`";
		$res = $con->query($sql);
		if ($res) {
			$row = $res->fetch_assoc();
			$data['details']['store']['team_recovered_time'] = $row['recovered_time'];
		}
		/* JOBS FINISHED */
		$sql = "SELECT 
					COUNT(*) AS jobs_finished 
				FROM 
					`ki_jobs_info` JI 
				INNER JOIN ( 
					SELECT `job_id`,`created_on` FROM `ki_job_certificate_of_work_info` WHERE `delete_flag`=0 GROUP BY `job_id`	
				) CWI ON 
					JI.`job_id`=CWI.`job_id` 
				WHERE 
					`home_store_type`='" . safe_str($inputdata['location_type']) . "' AND `home_store_id`='" . safe_str($inputdata['location_id']) . "' AND `status`!=10 AND `status`!=11 AND `is_cancelled`!=1 AND JI.`delete_flag`=0 AND JI.`delete_flag`=0 AND JI.`created_on` BETWEEN '" . safe_str($inputdata['from_date']) . "' AND '" . safe_str($inputdata['to_date']) . "'";
		$res = $con->query($sql);
		if ($res) {
			$row = $res->fetch_assoc();
			$data['details']['store']['jobs_finished'] = $row['jobs_finished'];
		}
		/* COMMENT COUNT */
		$sql = "SELECT 
					COUNT(*) AS comment_count, COALESCE(SUM(CASE WHEN `canned_response_id`!=0 AND `canned_response_id` IS NOT NULL THEN 1 ELSE 0 END),0) AS canned_comment_count
				FROM 
					`ki_job_comments_info` CI 
				LEFT JOIN `ki_jobs_info` JI ON 
					JI.`job_id`=CI.`job_id` AND CI.`delete_flag`=0 
				WHERE 
					`home_store_type`='" . safe_str($inputdata['location_type']) . "' AND `home_store_id`='" . safe_str($inputdata['location_id']) . "' AND `status`!=10 AND `status`!=11 AND `is_cancelled`!=1 AND JI.`delete_flag`=0 AND JI.`delete_flag`=0 AND JI.`created_on` BETWEEN '" . safe_str($inputdata['from_date']) . "' AND '" . safe_str($inputdata['to_date']) . "'";
		$res = $con->query($sql);
		if ($res) {
			$row = $res->fetch_assoc();
			$data['details']['store']['comments_count'] = $row['comment_count'];
			$data['details']['store']['canned_comments_count'] = $row['canned_comment_count'];
		}
		// echo "<pre>";print_r($data);echo"</pre>";die;
		return $data;
	}
	function get_tils_to_update($inputdata)
	{
		/* 
		input params - 
			"function"=>"get_tils_to_update"
		function is used to get tils for which daily_update_mail is to be sent 
		output -  
			$data = array(
				"status" => 0,
				"list" => array(),
				"errors" => array(),
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"list" => array(),
			"errors" => array()
		);
		$prev_10_mins = date('Y-m-d H:i:s', strtotime('- 10 min'));
		$next_10_mins = date('Y-m-d H:i:s', strtotime('+ 10 min'));
		// $prev_10_mins = '2020-02-18 08:40:00';
		// $next_10_mins = '2020-02-18 09:00:00';
		$sql = "SELECT
					TI.*, SI.`lower_goal`, SI.`middle_goal`, SI.`upper_goal`, COALESCE(SI.`store_name`, DBI.`distribution_name`,PI.`production_name`) AS location_name, COALESCE(SI.`timezone`, DBI.`timezone`,PI.`timezone`) AS location_timezone
				FROM
					`ki_tils_info` TI
				INNER JOIN (
					SELECT `location_type`, `location_id`, DATE(`modified_on`), MAX(`modified_on`) AS max_modified_on FROM `ki_tils_info` GROUP BY `location_type`, `location_id`, DATE(`modified_on`) 
				) TI1 ON 
					TI.`modified_on`=TI1.`max_modified_on`
				LEFT JOIN `ki_stores_info` SI ON
					TI.`location_type`=1 AND TI.`location_id`=SI.`store_id` AND SI.`delete_flag`=0
				LEFT JOIN `ki_distribution_branches_info` DBI ON
					TI.`location_type`=2 AND TI.`location_id`=DBI.`distribution_branch_id` AND DBI.`delete_flag`=0
				LEFT JOIN `ki_production_info` PI ON
					TI.`location_type`=3 AND TI.`location_id`=PI.`production_id` AND PI.`delete_flag`=0
				WHERE
					TI.`location_type`=1 AND TI.`is_closed`=1 AND TI.`daily_mail_update`=0 AND (
						TI.`daily_mail_update_date` BETWEEN '" . $prev_10_mins . "' AND '" . $next_10_mins . "'
					) AND (TI.`location_type`, TI.`location_id`, DATE(TI.`modified_on`)) NOT IN (
						SELECT DISTINCT `location_type`, `location_id`, DATE(`modified_on`) FROM `ki_tils_info` WHERE `daily_mail_update`=10 AND `is_closed`=1 AND `delete_flag`=0 ORDER BY `modified_on` DESC
					) AND TI.`delete_flag`=0";
		// echo "<pre>";
		$result = $con->query($sql);
		// die;
		if ($result->num_rows) {
			while ($row = $result->fetch_assoc()) {
				if (empty(trim($row['location_timezone']))) {
					$row['location_timezone'] = get_meta_value(23);
				}
				$date = new DateTime($row['daily_mail_update_date']);
				$date->setTimezone(new DateTimeZone($row['location_timezone']));
				$daily_mail_update_date = $date->format('Y-m-d H:i:s');
				$qry = "SELECT `user_id`, CONCAT(`date`, ' ', `finish_time`) as rostered_out_time FROM `ki_roster_data_info` RDI INNER JOIN `ki_rosters_info` RI ON RDI.`roster_id`=RI.`roster_id` AND RI.`delete_flag`=0 WHERE RDI.`date`=DATE('" . $row['modified_on'] . "') AND RI.`location_type`='" . safe_str($row['location_type']) . "' AND RI.`location_id`='" . safe_str($row['location_id']) . "' AND RDI.`delete_flag`=0 AND `finish_time`>TIME('" . $daily_mail_update_date . "')";
				// $qry = "SELECT * FROM `ki_time_clock_info` WHERE `location_type`='".safe_str($row['location_type'])."' AND `location_id`='".safe_str($row['location_id'])."' AND `in_date`=DATE('".$row['modified_on']."') AND `time_out` IS NULL AND `delete_flag`=0";
				$res = $con->query($qry);
				if ($res->num_rows) {
					// update til scheduled time
					$update = "UPDATE `ki_tils_info` SET `daily_mail_update_date`=DATE_ADD(`daily_mail_update_date`, INTERVAL 30 MINUTE) WHERE `til_id`='" . $row['til_id'] . "'";
				} else {
					// update til status to 10 
					$data['list'][] = $row;
					$update = "update ki_tils_info set daily_mail_update=10 where til_id='" . $row['til_id'] . "'";
				}
				$res1 = $con->query($update);
			}
		}
		return $data;
	}
	function GetStoresList($inputdata)
	{
		/* 
		input params - 
			store_id (optional)
		function is used to get list of all stores
		output -  
			$data = array(
				"status" => 0,
				"list" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"list" => array(),
			"errors" => array()
		);
		$concatQuery = "";
		$concatOrderBy = "";
		if (!empty($inputdata['coordinates'])) {
			if (!empty($inputdata['coordinates']['lat'])) {
				$lat = $inputdata['coordinates']['lat'];
			}
			if (!empty($inputdata['coordinates']['lng'])) {
				$long = $inputdata['coordinates']['lng'];
			}
			$concatQuery = ", (6371 * acos( cos( radians($lat) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians($long) ) + sin( radians($lat) ) * sin( radians( latitude ) ) ) ) AS distance ";
			$concatOrderBy = "HAVING distance >= 0 ORDER BY distance ASC";
		}
		if (!empty($inputdata['session_cords'])) {
			if (!empty($inputdata['session_cords']['lat'])) {
				$lat = $inputdata['session_cords']['lat'];
			}
			if (!empty($inputdata['session_cords']['lng'])) {
				$long = $inputdata['session_cords']['lng'];
			}
			$concatQuery = ", (6371 * acos( cos( radians($lat) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians($long) ) + sin( radians($lat) ) * sin( radians( latitude ) ) ) ) AS distance ";
			$concatOrderBy = "HAVING distance >= 0 ORDER BY distance ASC";
		}
		$query = "`is_enabled`=1 AND `delete_flag`=0 AND latitude IS NOT NULL AND longitude IS NOT NULL";
		if (!empty($inputdata['store_id'])) {
			$query = " (`is_enabled`=1 OR `store_id` IN (" . implode(',', $inputdata['store_id']) . ")) ";
		}
		if (!empty($concatOrderBy)) {
			$orderBY = $concatOrderBy;
		} else {
			$orderBY = " ORDER BY `store_name` ASC";
		}
		$sql = "SELECT *" . $concatQuery . " FROM `ki_stores_info` WHERE " . $query . " " . $orderBY;
		$res = $con->query($sql);
		if ($res) {
			$data['status'] = 1;
			while ($row = $res->fetch_assoc()) {
				$data['list'][] = $row;
			}
		} else {
			$data['errors'][] = $con->error;
		}
		return $data;
	}
	function GetStoresForUserBasedOnPanel($inputdata)
	{
		/* 
		input params - 
			location_type, location_id, user_id
		function is used to get location details of user
		output -  
			$data = array(
				"status" => 0,
				"list" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"list" => array(),
			"errors" => array()
		);
		$sql = "SELECT * FROM `ki_stores_info` WHERE `store_id` IN ( SELECT `location_id` FROM `ki_user_locations_info` WHERE `user_id`='" . $inputdata['user_id'] . "' AND `location_type`=1 AND `delete_flag`=0) AND `delete_flag`=0 ORDER BY `store_name` ASC";
		$res = $con->query($sql);
		if ($res) {
			$data['status'] = 1;
			while ($row = $res->fetch_assoc()) {
				$data['list'][] = $row;
			}
		} else {
			$data['errors'][] = $con->error;
		}
		return $data;
	}
	function get_managers_for_location($inputdata)
	{
		/* 
		input params - 
			location_type, location_id
		function is used to get list of all managers for a location
		output - 
			$data = array(
				"status" => 0,
				"list" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"list" => array(
				"Default Manager" => array(),
				"District Manager" => array(),
				"Location Manager" => array()
			),
			"errors" => array()
		);
		$qry = "";
		if ($inputdata['location_type'] == 1) {
			$table = "ki_stores_info";
			$id_key = 'store_id';
			$column = "store_manager_id";
			$qry = "UNION 
					SELECT 'District Manager' AS manager_type, `district_manager_id` AS user_id FROM `ki_stores_info` WHERE `store_id` = '" . safe_str($inputdata['location_id']) . "' AND `delete_flag` = 0";
		} elseif ($inputdata['location_type'] == 2) {
			$table = 'ki_distribution_branches_info';
			$id_key = 'distribution_branch_id';
			$column = "manager_id";
		} elseif ($inputdata['location_type'] == 3) {
			$table = 'ki_production_info';
			$id_key = 'production_id';
			$column = "manager_id";
		}
		$sql = "SELECT 
					DISTINCT AA.`manager_type`, UI.*, CONCAT(COALESCE(UI.`first_name`, ''),' ',COALESCE(UI.`last_name`, '')) AS user_name 
				FROM (
					SELECT 'Default Manager' AS manager_type, " . get_meta_value(27) . " AS `user_id`
					UNION
					SELECT 'Location Manager' AS manager_type, `" . safe_str($column) . "` AS user_id FROM `" . safe_str($table) . "` WHERE `" . safe_str($id_key) . "` = '" . safe_str($inputdata['location_id']) . "' AND `delete_flag` = 0 
					" . $qry . "
				) AA 
				INNER JOIN `ki_users_info` UI ON 
					AA.`user_id` = UI.`user_id` AND UI.`is_enabled` = 1 AND UI.`delete_flag` = 0";
		$res = $con->query($sql);
		if ($res) {
			$data['status'] = 1;
			while ($row = $res->fetch_assoc()) {
				$data['list'][$row['manager_type']] = $row;
			}
		} else {
			$data['errors'][] = $con->error;
		}
		return $data;
	}
	function get_avg_of_column($inputdata)
	{
		/* 
		input params - 
			table, keys, values, columns
		function is used to get average of a column
		output - 
			$data = array(
				"status" => 0,
				"avg" => 0,
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"avg" => 0,
			"errors" => array()
		);
		$table = $inputdata['table'];
		$keys = $inputdata['keys'];
		$values = $inputdata['values'];
		$column = $inputdata['column'];
		$condition_qry = '';
		if ($keys != "" && $values != "") {
			for ($i = 0; $i < count($keys); $i++) {
				$condition_qry .= " AND " . safe_str($keys[$i]) . " = " . safe_str($values[$i]) . "";
			}
		};
		$query = "SELECT AVG(`" . safe_str($column) . "`) AS avg FROM `" . safe_str($table) . "` WHERE `delete_flag`=0" . $condition_qry . "";
		$result = $con->query($query);
		if ($result) {
			$row = $result->fetch_assoc();
			$data['status'] = 1;
			$data['avg'] = $row['avg'];
		} else {
			$data['errors'][] = $con->error;
		}
		return $data;
	}
	function SearchCategories($inputdata)
	{
		/* 
		input params - 
			"function"=>"SearchCategories",
			"search_cat"=>$_POST['q']
		function is used to get child categories along with parent 
		output - 
			$data = array(
				"status" => 0,
				"list" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"list" => array(),
			"errors" => array()
		);
		$categories = array();
		$sql = 'select * from ki_categories_info where category_name like "%' . safe_str($inputdata['search_cat']) . '%" and delete_flag=0 order by is_child';
		$res = $con->query($sql);
		if ($res->num_rows) {
			while ($row = $res->fetch_assoc()) {
				$parent_found = 0;
				// echo $row['ids_path'].' ';
				// split ids path 
				$ids_path_cats = explode(",", $row['ids_path']);
				if (!empty($categories)) {
					foreach ($categories as $cats) {
						// echo $cats;
						// print_r($ids_path_cats);
						if (in_array($cats, $ids_path_cats) && count($ids_path_cats) > 1) {
							// means parent found having matched search criteria, hence do not include this category 
							$parent_found = 1;
						}
					}
				}
				$categories[] = $row['category_id'];
				if ($parent_found == 0) {
					$data['list'][] = $row;
				}
			}
		}
		return $data;
	}
	function check_if_nps_followup_already_sent($inputdata)
	{
		/* 
		input params - 
			follow_up_id
		function is used to check if nps followup request mail has been already sent for the passed parameter.
		output - 
			$data = array(
				"status" => 0,
				"details" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"errors" => array(),
			"details" => array()
		);
		$sql = "SELECT * FROM `ki_follow_ups_info` WHERE `type`=1 AND `email_type`=6 AND `status`!=2 AND `parent_followup_id`='" . $inputdata['follow_up_id'] . "' AND `delete_flag`=0";
		$result = $con->query($sql);
		if ($result) {
			$data["status"] = 1;
			$data['details'] = $result->fetch_assoc();
		} else {
			$data["errors"][] = $con->error;
		}
		return $data;
	}
	function get_nps_followup_invoices($inputdata)
	{
		/* 
		input params - 
			// none
		function is used to get list of sent nps mails for which no feedback has been received within a week
		output - 
			$data = array(
				"status" => 0,
				"list" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"list" => array(),
			"errors" => array()
		);
		$sql = "SELECT 
					FUI.*, CFI. `feedback_id`, II.`invoice_number`, II.`home_store_type`, II.`job_id`, JI.`job_number`, 
					CASE JI.`job_type`
						WHEN 1 THEN 'Ticket'
						WHEN 2 THEN 'Project'
						WHEN 3 THEN 'Call Out'
						WHEN 4 THEN 'Task'
						WHEN 5 THEN 'Client Check In'
						WHEN 6 THEN 'Device Refurbishment'
					END AS `job_type`, 
					CASE 
						WHEN UI.`user_id`!='' AND UI.`user_id` IS NOT NULL THEN UI.`user_id` 
						WHEN II.`home_store_type`=1 AND UI1.`user_id`!='' AND UI1.`user_id` IS NOT NULL THEN UI1.`user_id` 
						WHEN II.`home_store_type`=2 AND UI2.`user_id`!='' AND UI2.`user_id` IS NOT NULL THEN UI2.`user_id` 
						WHEN II.`home_store_type`=3 AND UI3.`user_id`!='' AND UI3.`user_id` IS NOT NULL THEN UI3.`user_id` 
						ELSE UID.`user_id` 
					END AS last_invoice_user_id, 
					CASE 
						WHEN UI.`user_id`!='' AND UI.`user_id` IS NOT NULL THEN CONCAT(COALESCE(UI.`first_name`, ''),' ',COALESCE(UI.`last_name`, '')) 
						WHEN II.`home_store_type`=1 AND UI1.`user_id`!='' AND UI1.`user_id` IS NOT NULL THEN CONCAT(COALESCE(UI1.`first_name`, ''),' ',COALESCE(UI1.`last_name`, '')) 
						WHEN II.`home_store_type`=2 AND UI2.`user_id`!='' AND UI2.`user_id` IS NOT NULL THEN CONCAT(COALESCE(UI2.`first_name`, ''),' ',COALESCE(UI2.`last_name`, '')) 
						WHEN II.`home_store_type`=3 AND UI3.`user_id`!='' AND UI3.`user_id` IS NOT NULL THEN CONCAT(COALESCE(UI3.`first_name`, ''),' ',COALESCE(UI3.`last_name`, '')) 
						ELSE CONCAT(COALESCE(UID.`first_name`, ''),' ',COALESCE(UID.`last_name`, '')) 
					END AS last_invoice_user_name, 
					CASE 
						WHEN UI.`user_id`!='' AND UI.`user_id` IS NOT NULL THEN UI.`email` 
						WHEN II.`home_store_type`=1 AND UI1.`user_id`!='' AND UI1.`user_id` IS NOT NULL THEN UI1.`email` 
						WHEN II.`home_store_type`=2 AND UI2.`user_id`!='' AND UI2.`user_id` IS NOT NULL THEN UI2.`email` 
						WHEN II.`home_store_type`=3 AND UI3.`user_id`!='' AND UI3.`user_id` IS NOT NULL THEN UI3.`email` 
						ELSE UID.`email` 
					END AS last_invoice_user_email, 
					CONCAT(COALESCE(UI4.`first_name`, ''),' ',COALESCE(UI4.`last_name`, '')) AS `district_manager`, UI4.`email` AS district_manager_email, CI.`is_unsubscribed_to_marketing`, CONCAT(COALESCE(CI.`first_name`, ''),' ',COALESCE(CI.`last_name`, ''),' ',COALESCE(CI.`business_name`, '')) AS customer_name, CI.`first_name` AS customer_first_name, CI.`last_name` AS customer_last_name, CI.`business_name` AS customer_business_name, CI.`phone` AS customer_phone, CI.`email` AS customer_email, CI.`address` AS customer_address, CI.`suburb_town` AS customer_suburb, CI.`state` AS customer_state, 
					COALESCE(SI.`store_name`, DBI.`distribution_name`,PI.`production_name`) AS location_name, 
					CASE 
						WHEN II.`home_store_type`=1 AND UI1.`user_id`!='' AND UI1.`user_id` IS NOT NULL THEN UI1.`user_id` 
						WHEN II.`home_store_type`=2 AND UI2.`user_id`!='' AND UI2.`user_id` IS NOT NULL THEN UI2.`user_id` 
						WHEN II.`home_store_type`=3 AND UI3.`user_id`!='' AND UI3.`user_id` IS NOT NULL THEN UI3.`user_id` 
						ELSE UID.`user_id`
					END AS location_manager_id, 
					CASE 
						WHEN II.`home_store_type`=1 AND UI1.`user_id`!='' AND UI1.`user_id` IS NOT NULL THEN CONCAT(COALESCE(UI1.`first_name`, ''),' ',COALESCE(UI1.`last_name`, '')) 
						WHEN II.`home_store_type`=2 AND UI2.`user_id`!='' AND UI2.`user_id` IS NOT NULL THEN CONCAT(COALESCE(UI2.`first_name`, ''),' ',COALESCE(UI2.`last_name`, '')) 
						WHEN II.`home_store_type`=3 AND UI3.`user_id`!='' AND UI3.`user_id` IS NOT NULL THEN CONCAT(COALESCE(UI3.`first_name`, ''),' ',COALESCE(UI3.`last_name`, '')) 
						ELSE CONCAT(COALESCE(UID.`first_name`, ''),' ',COALESCE(UID.`last_name`, '')) 
					END AS location_manager, 
					CASE 
						WHEN II.`home_store_type`=1 AND UI1.`user_id`!='' AND UI1.`user_id` IS NOT NULL THEN UI1.`email` 
						WHEN II.`home_store_type`=2 AND UI2.`user_id`!='' AND UI2.`user_id` IS NOT NULL THEN UI2.`email` 
						WHEN II.`home_store_type`=3 AND UI3.`user_id`!='' AND UI3.`user_id` IS NOT NULL THEN UI3.`email` 
						ELSE UID.`email`
					END AS location_manager_email, 
					COALESCE(SI.`email`, DBI.`email`, PI.`email`) AS location_email, 
					COALESCE(SI.`phone_number`, DBI.`phone_number`, PI.`phone_number`) AS location_phone_number, 
					COALESCE(SI.`facebook_link`, DBI.`facebook_link`, PI.`facebook_link`) AS location_facebook, 
					COALESCE(SI.`google_link`, DBI.`google_link`, PI.`google_link`) AS location_google, 
					COALESCE(SI.`address`,DBI.`address`,PI.`address`) AS location_address, 
					COALESCE(SI.`suburb`,DBI.`suburb`,PI.`suburb`) AS location_suburb, 
					COALESCE(SI.`postcode`,DBI.`postcode`,PI.`postcode`) AS location_postcode, 
					COALESCE(SI.`state`,DBI.`state`,PI.`state`) AS location_state, 
					COALESCE(SI.`directions`,DBI.`directions`,PI.`directions`) AS location_directions, 
					COALESCE(SI.`country`,DBI.`country`,PI.`country`) AS location_country, 
					COALESCE(SI.`ABN`, DBI.`ABN`, PI.`ABN`) AS location_ABN, 
					COALESCE(SI.`BSB`, DBI.`BSB`, PI.`BSB`) AS location_BSB, 
					COALESCE(SI.`account_number`, DBI.`account_number`, PI.`account_number`) AS location_account_number, 
					TTI.`ticket_type_name`, BI.`brand_name`, MI.`model_name` 
				FROM 
					`ki_follow_ups_info` FUI 
				LEFT JOIN `ki_customer_feedback_info` CFI ON 
					FUI.`follow_up_id`=CFI.`follow_up_id` AND CFI.`delete_flag`=0 
				LEFT JOIN `ki_invoices_info` II ON 
					FUI.`type_id`=II.`invoice_id` AND II.`delete_flag`=0 
				LEFT JOIN `ki_customers_info` CI ON 
					FUI.`customer_id`=CI.`customer_id` AND CI.`delete_flag`=0 
				LEFT JOIN `ki_users_info` UI ON 
					FUI.`user_id` = UI.`user_id` AND UI.`is_enabled`=1 AND UI.`delete_flag`=0 
				LEFT JOIN `ki_stores_info` SI ON 
					II.`home_store_type`=1 AND II.`home_store_id`=SI.`store_id` AND SI.`delete_flag`=0 
				LEFT JOIN `ki_users_info` UI1 ON 
					SI.`store_manager_id`=UI1.`user_id` AND UI1.`is_enabled`=1 AND UI1.`delete_flag`=0 
				LEFT JOIN `ki_users_info` UI4 ON 
					SI.`district_manager_id`=UI4.`user_id` AND UI4.`is_enabled`=1 AND UI4.`delete_flag`=0 
				LEFT JOIN `ki_users_info` UID ON 
					UID.`user_id`='" . get_meta_value(27) . "' AND UID.`is_enabled`=1 AND UID.`delete_flag`=0 
				LEFT JOIN `ki_distribution_branches_info` DBI ON 
					II.`home_store_type`=2 AND II.`home_store_id`=DBI.`distribution_branch_id` AND DBI.`delete_flag`=0 
				LEFT JOIN `ki_users_info` UI2 ON 
					DBI.`manager_id`=UI2.`user_id` AND UI2.`is_enabled`=1 AND UI2.`delete_flag`=0 
				LEFT JOIN `ki_production_info` PI ON 
					II.`home_store_type`=3 AND II.`home_store_id`=PI.`production_id` AND PI.`delete_flag`=0 
				LEFT JOIN `ki_users_info` UI3 ON 
					PI.`manager_id`=UI3.`user_id` AND UI3.`is_enabled`=1 AND UI3.`delete_flag`=0 
				LEFT JOIN `ki_jobs_info` JI ON
					II.`job_id`=JI.`job_id` AND JI.`status`!=10 AND JI.`status`!=11 AND JI.`is_cancelled`!=1 AND JI.`delete_flag`=0
				LEFT JOIN `ki_ticket_types_info` TTI ON 
					JI.`ticket_type_id`=TTI.`ticket_type_id` AND TTI.`delete_flag`=0 
				LEFT JOIN `ki_brands_info` BI ON 
					JI.`brand_id`=BI.`brand_id` AND BI.`delete_flag`=0 
				LEFT JOIN `ki_models_info` MI ON 
					JI.`model_id`=MI.`model_id` AND MI.`delete_flag`=0 
				WHERE 
					II.`is_draft`=0 AND (CI.`is_unsubscribed_to_marketing`=0 OR CI.`is_unsubscribed_to_marketing` IS NULL) AND FUI.`follow_up_id` NOT IN (SELECT DISTINCT `follow_up_id` FROM `ki_customer_feedback_info` WHERE `delete_flag`=0) AND (`parent_followup_id` IS NULL OR `parent_followup_id`=0 OR (`parent_followup_id` IS NOT NULL AND `parent_followup_id`!=0 AND FUI.`type`=1 AND FUI.`status`=2)) AND FUI.`created_on`< NOW() - INTERVAL 1 WEEK AND FUI.`type`=1 AND FUI.`email_type`=6 AND FUI.`status`=1 AND FUI.`delete_flag`=0";
		$result = $con->query($sql);
		if ($result) {
			$data['status'] = 1;
			while ($row = $result->fetch_assoc()) {
				$address = [$row['location_address'], $row['location_directions'], $row['location_suburb'], $row['location_state'], $row['location_country'], $row['location_postcode']];
				$row['concatenated_address'] = implode(", ", array_filter($address));
				$data['list'][] = $row;
			}
		} else {
			$data['errors'][] = $con->error();
		}
		return $data;
	}
	function check_if_can_be_parent($inputdata)
	{
		/* 
		input params - 
			"function"=>"check_if_can_be_parent",
			"category_id"=>$category_id,
			"parent_category_id"=>$parent_category_id,
		function is used to check if selected parent is not the child of selected category
		output - 
			$data = array(
				"status" => 0,
				"list" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"list" => array(),
			"errors" => array()
		);
		$sql = "select * from ki_categories_info where FIND_IN_SET('" . safe_str($inputdata['category_id']) . "',ids_path) > 0 and category_id='" . safe_str($inputdata['parent_category_id']) . "' and delete_flag=0 and ids_path!=category_id";
		$res = $con->query($sql);
		if ($res->num_rows) {
			$data['status'] = 0;
		} else {
			$data['status'] = 1;
		}
		return $data;
	}
	function create_nps_customer_feedback($inputdata)
	{
		/* 
		input params -
			"fields_data" => $in_fields
		Function is used to create entry in ki_customer_feedback_info table
		output - 
			$data = array(
				"status" => 0,
				"id" => 0,
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"id" => 0,
			"errors" => array()
		);
		$in_fields = array();
		foreach ($inputdata['fields_data'] as $field_key => $field_data) {
			$in_fields[safe_str($field_key)] = "'" . safe_str($field_data) . "'";
		}
		$in_query = "INSERT INTO `ki_customer_feedback_info` (`" . implode("`, `", array_keys($in_fields)) . "`) VALUES (" . implode(", ", $in_fields) . ")";
		$in_result = $con->query($in_query);
		if ($in_result) {
			$data["status"] = 1;
			$data["id"] = $con->insert_id;
		} else {
			$data["errors"][] = $con->error;
		}
		return $data;
	}
	function get_follow_up_details($inputdata)
	{
		/* 
		input params - 
			"follow_up_id" => $follow_up_id
		function is used to get details of a particular follow_up_id
		output - 
			$data = array(
				"status" => 0,
				"details" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"details" => array(),
			"errors" => array()
		);
		$sql = "SELECT 
					FUI.*, CFI. `feedback_id`, II.`invoice_number`, II.`home_store_type`, II.`job_id`, JI.`job_number`, 
					CASE JI.`job_type`
						WHEN 1 THEN 'Ticket'
						WHEN 2 THEN 'Project'
						WHEN 3 THEN 'Call Out'
						WHEN 4 THEN 'Task'
						WHEN 5 THEN 'Client Check In'
						WHEN 6 THEN 'Device Refurbishment'
					END AS `job_type`, 
					CASE 
						WHEN UI.`user_id`!='' AND UI.`user_id` IS NOT NULL THEN UI.`user_id` 
						WHEN II.`home_store_type`=1 AND UI1.`user_id`!='' AND UI1.`user_id` IS NOT NULL THEN UI1.`user_id` 
						WHEN II.`home_store_type`=2 AND UI2.`user_id`!='' AND UI2.`user_id` IS NOT NULL THEN UI2.`user_id` 
						WHEN II.`home_store_type`=3 AND UI3.`user_id`!='' AND UI3.`user_id` IS NOT NULL THEN UI3.`user_id` 
						ELSE UID.`user_id` 
					END AS last_invoice_user_id, 
					CASE 
						WHEN UI.`user_id`!='' AND UI.`user_id` IS NOT NULL THEN CONCAT(COALESCE(UI.`first_name`, ''),' ',COALESCE(UI.`last_name`, '')) 
						WHEN II.`home_store_type`=1 AND UI1.`user_id`!='' AND UI1.`user_id` IS NOT NULL THEN CONCAT(COALESCE(UI1.`first_name`, ''),' ',COALESCE(UI1.`last_name`, '')) 
						WHEN II.`home_store_type`=2 AND UI2.`user_id`!='' AND UI2.`user_id` IS NOT NULL THEN CONCAT(COALESCE(UI2.`first_name`, ''),' ',COALESCE(UI2.`last_name`, '')) 
						WHEN II.`home_store_type`=3 AND UI3.`user_id`!='' AND UI3.`user_id` IS NOT NULL THEN CONCAT(COALESCE(UI3.`first_name`, ''),' ',COALESCE(UI3.`last_name`, '')) 
						ELSE CONCAT(COALESCE(UID.`first_name`, ''),' ',COALESCE(UID.`last_name`, '')) 
					END AS last_invoice_user_name, 
					CASE 
						WHEN UI.`user_id`!='' AND UI.`user_id` IS NOT NULL THEN UI.`email` 
						WHEN II.`home_store_type`=1 AND UI1.`user_id`!='' AND UI1.`user_id` IS NOT NULL THEN UI1.`email` 
						WHEN II.`home_store_type`=2 AND UI2.`user_id`!='' AND UI2.`user_id` IS NOT NULL THEN UI2.`email` 
						WHEN II.`home_store_type`=3 AND UI3.`user_id`!='' AND UI3.`user_id` IS NOT NULL THEN UI3.`email` 
						ELSE UID.`email` 
					END AS last_invoice_user_email, 
					CONCAT(COALESCE(UI4.`first_name`, ''),' ',COALESCE(UI4.`last_name`, '')) AS `district_manager`, UI4.`email` AS district_manager_email, CI.`is_unsubscribed_to_marketing`, CONCAT(COALESCE(CI.`first_name`, ''),' ',COALESCE(CI.`last_name`, ''),' ',COALESCE(CI.`business_name`, '')) AS customer_name, CI.`first_name` AS customer_first_name, CI.`last_name` AS customer_last_name, CI.`business_name` AS customer_business_name, CI.`phone` AS customer_phone, CI.`email` AS customer_email, CI.`address` AS customer_address, CI.`suburb_town` AS customer_suburb, CI.`state` AS customer_state,
					COALESCE(SI.`store_name`, DBI.`distribution_name`,PI.`production_name`) AS location_name, 
					CASE 
						WHEN II.`home_store_type`=1 AND UI1.`user_id`!='' AND UI1.`user_id` IS NOT NULL THEN UI1.`user_id` 
						WHEN II.`home_store_type`=2 AND UI2.`user_id`!='' AND UI2.`user_id` IS NOT NULL THEN UI2.`user_id` 
						WHEN II.`home_store_type`=3 AND UI3.`user_id`!='' AND UI3.`user_id` IS NOT NULL THEN UI3.`user_id` 
						ELSE UID.`user_id`
					END AS location_manager_id, 
					CASE 
						WHEN II.`home_store_type`=1 AND UI1.`user_id`!='' AND UI1.`user_id` IS NOT NULL THEN CONCAT(COALESCE(UI1.`first_name`, ''),' ',COALESCE(UI1.`last_name`, '')) 
						WHEN II.`home_store_type`=2 AND UI2.`user_id`!='' AND UI2.`user_id` IS NOT NULL THEN CONCAT(COALESCE(UI2.`first_name`, ''),' ',COALESCE(UI2.`last_name`, '')) 
						WHEN II.`home_store_type`=3 AND UI3.`user_id`!='' AND UI3.`user_id` IS NOT NULL THEN CONCAT(COALESCE(UI3.`first_name`, ''),' ',COALESCE(UI3.`last_name`, '')) 
						ELSE CONCAT(COALESCE(UID.`first_name`, ''),' ',COALESCE(UID.`last_name`, '')) 
					END AS location_manager, 
					CASE 
						WHEN II.`home_store_type`=1 AND UI1.`user_id`!='' AND UI1.`user_id` IS NOT NULL THEN UI1.`email` 
						WHEN II.`home_store_type`=2 AND UI2.`user_id`!='' AND UI2.`user_id` IS NOT NULL THEN UI2.`email` 
						WHEN II.`home_store_type`=3 AND UI3.`user_id`!='' AND UI3.`user_id` IS NOT NULL THEN UI3.`email` 
						ELSE UID.`email`
					END AS location_manager_email, 
					COALESCE(SI.`email`, DBI.`email`, PI.`email`) AS location_email, 
					COALESCE(SI.`phone_number`, DBI.`phone_number`, PI.`phone_number`) AS location_phone_number, 
					COALESCE(SI.`facebook_link`, DBI.`facebook_link`, PI.`facebook_link`) AS location_facebook, 
					COALESCE(SI.`google_link`, DBI.`google_link`, PI.`google_link`) AS location_google, 
					COALESCE(SI.`address`,DBI.`address`,PI.`address`) AS location_address, 
					COALESCE(SI.`suburb`,DBI.`suburb`,PI.`suburb`) AS location_suburb, 
					COALESCE(SI.`postcode`,DBI.`postcode`,PI.`postcode`) AS location_postcode, 
					COALESCE(SI.`state`,DBI.`state`,PI.`state`) AS location_state, 
					COALESCE(SI.`directions`,DBI.`directions`,PI.`directions`) AS location_directions, 
					COALESCE(SI.`country`,DBI.`country`,PI.`country`) AS location_country, 
					COALESCE(SI.`ABN`, DBI.`ABN`, PI.`ABN`) AS location_ABN, 
					COALESCE(SI.`BSB`, DBI.`BSB`, PI.`BSB`) AS location_BSB, 
					COALESCE(SI.`account_number`, DBI.`account_number`, PI.`account_number`) AS location_account_number, 
					TTI.`ticket_type_name`, BI.`brand_name`, MI.`model_name` 
				FROM 
					`ki_follow_ups_info` FUI 
				LEFT JOIN `ki_customer_feedback_info` CFI ON 
					FUI.`follow_up_id`=CFI.`follow_up_id` AND CFI.`delete_flag`=0 
				LEFT JOIN `ki_invoices_info` II ON 
					FUI.`type_id`=II.`invoice_id` AND II.`delete_flag`=0 
				LEFT JOIN `ki_customers_info` CI ON 
					FUI.`customer_id`=CI.`customer_id` AND CI.`delete_flag`=0 
				LEFT JOIN `ki_users_info` UI ON 
					FUI.`user_id` = UI.`user_id` AND UI.`is_enabled`=1 AND UI.`delete_flag`=0 
				LEFT JOIN `ki_stores_info` SI ON 
					II.`home_store_type`=1 AND II.`home_store_id`=SI.`store_id` AND SI.`delete_flag`=0 
				LEFT JOIN `ki_users_info` UI1 ON 
					SI.`store_manager_id`=UI1.`user_id` AND UI1.`is_enabled`=1 AND UI1.`delete_flag`=0 
				LEFT JOIN `ki_users_info` UI4 ON 
					SI.`district_manager_id`=UI4.`user_id` AND UI4.`is_enabled`=1 AND UI4.`delete_flag`=0 
				LEFT JOIN `ki_users_info` UID ON 
					UID.`user_id`='" . get_meta_value(27) . "' AND UID.`is_enabled`=1 AND UID.`delete_flag`=0 
				LEFT JOIN `ki_distribution_branches_info` DBI ON 
					II.`home_store_type`=2 AND II.`home_store_id`=DBI.`distribution_branch_id` AND DBI.`delete_flag`=0 
				LEFT JOIN `ki_users_info` UI2 ON 
					DBI.`manager_id`=UI2.`user_id` AND UI2.`is_enabled`=1 AND UI2.`delete_flag`=0 
				LEFT JOIN `ki_production_info` PI ON 
					II.`home_store_type`=3 AND II.`home_store_id`=PI.`production_id` AND PI.`delete_flag`=0 
				LEFT JOIN `ki_users_info` UI3 ON 
					PI.`manager_id`=UI3.`user_id` AND UI3.`is_enabled`=1 AND UI3.`delete_flag`=0 
				LEFT JOIN `ki_jobs_info` JI ON
					II.`job_id`=JI.`job_id` AND JI.`status`!=10 AND JI.`status`!=11 AND JI.`is_cancelled`!=1 AND JI.`delete_flag`=0
				LEFT JOIN `ki_ticket_types_info` TTI ON 
					JI.`ticket_type_id`=TTI.`ticket_type_id` AND TTI.`delete_flag`=0 
				LEFT JOIN `ki_brands_info` BI ON 
					JI.`brand_id`=BI.`brand_id` AND BI.`delete_flag`=0 
				LEFT JOIN `ki_models_info` MI ON 
					JI.`model_id`=MI.`model_id` AND MI.`delete_flag`=0 
				WHERE 
					II.`is_draft`=0 AND FUI.`follow_up_id`='" . safe_str($inputdata['follow_up_id']) . "' AND FUI.`delete_flag`=0";
		$result = $con->query($sql);
		if ($result) {
			$data['status'] = 1;
			$row = $result->fetch_assoc();
			$address = [$row['location_address'], $row['location_directions'], $row['location_suburb'], $row['location_state'], $row['location_country'], $row['location_postcode']];
			$row['concatenated_address'] = implode(", ", array_filter($address));
			$data['details'] = $row;
		} else {
			$data['errors'][] = $con->error();
		}
		return $data;
	}
	function update_nps_follow_up($inputdata)
	{
		/* 
		input params -
			"fields_data" => $in_fields,
			"follow_up_id" => $follow_up_id
		Function is used to update entry in follow ups table
		output - 
			$data = array(
				"status" => 0,
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"errors" => array()
		);
		$up_fields = array();
		foreach ($inputdata['fields_data'] as $ifield => $ival) {
			if ($ival == '' || $ival == "null") {
				$up_fields[] = "`" . safe_str($ifield) . "` = null";
			} else {
				$up_fields[] = "`" . safe_str($ifield) . "` = '" . safe_str($ival) . "'";
			}
		}
		$up_qry = "UPDATE `ki_follow_ups_info` SET " . implode(", ", $up_fields) . " WHERE `follow_up_id` = '" . safe_str($inputdata['follow_up_id']) . "' AND `delete_flag`=0";
		$up_result = $con->query($up_qry);
		if ($up_result) {
			$data["status"] = 1;
		} else {
			$data["errors"][] = $con->error;
		}
		return $data;
	}
	function create_nps_follow_up($inputdata)
	{
		/* 
		input params -
			"fields_data" => $in_fields
		Function is used to create entry in follow ups table
		output - 
			$data = array(
				"status" => 0,
				"id" => 0,
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"id" => 0,
			"errors" => array()
		);
		$in_fields = array();
		foreach ($inputdata['fields_data'] as $field_key => $field_data) {
			$in_fields[safe_str($field_key)] = "'" . safe_str($field_data) . "'";
		}
		$in_query = "INSERT INTO `ki_follow_ups_info` (`" . implode("`, `", array_keys($in_fields)) . "`) VALUES (" . implode(", ", $in_fields) . ")";
		$in_result = $con->query($in_query);
		if ($in_result) {
			$data["status"] = 1;
			$data["id"] = $con->insert_id;
		} else {
			$data["errors"][] = $con->error;
		}
		return $data;
	}
	function check_if_feedback_already_sent($inputdata)
	{
		/* 
		input params - 
			customer_id, type_id
		function is used to check if followup feedback mail has been already sent for the passed parameters.
		output - 
			$data = array(
				"status" => 0,
				"details" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"errors" => array(),
			"details" => array()
		);
		$sql = "SELECT * FROM `ki_follow_ups_info` WHERE `type`=1 AND `email_type`=6 AND `status`!=2 AND (`type_id`='" . safe_str($inputdata['type_id']) . "' OR (`customer_id`='" . safe_str($inputdata['customer_id']) . "' AND `created_on`> DATE_SUB('" . date("Y-m-d H:i:s") . "', INTERVAL 3 MONTH))) AND `delete_flag`=0";
		$result = $con->query($sql);
		if ($result) {
			$data["status"] = 1;
			$data['details'] = $result->fetch_assoc();
		} else {
			$data["errors"][] = $con->error;
		}
		return $data;
	}
	function get_nps_invoices($inputdata)
	{
		/* 
		input params - 
			// none
		function is used to get list of all invoices for which follow up is to be sent.
		output - 
			$data = array(
				"status" => 0,
				"list" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"list" => array(),
			"errors" => array()
		);
		$sql = "SELECT * FROM ( 
					SELECT 
						CASE 
							WHEN JI.`job_id`!=0 AND JI.`job_id` IS NOT NULL THEN 1
							ELSE 2
						END AS type, II.`invoice_id`, II.`invoice_number`, II.`customer_id`, II.`home_store_type`, II.`created_on`, II.`job_id`, JI.`job_number`, 
						CASE JI.`job_type`
							WHEN 1 THEN 'Ticket'
							WHEN 2 THEN 'Project'
							WHEN 3 THEN 'Call Out'
							WHEN 4 THEN 'Task'
							WHEN 5 THEN 'Client Check In'
							WHEN 6 THEN 'Device Refurbishment'
						END AS `job_type`, 
						CASE 
							WHEN UI.`user_id`!='' AND UI.`user_id` IS NOT NULL THEN UI.`user_id` 
							WHEN II.`home_store_type`=1 AND UI1.`user_id`!='' AND UI1.`user_id` IS NOT NULL THEN UI1.`user_id` 
							WHEN II.`home_store_type`=2 AND UI2.`user_id`!='' AND UI2.`user_id` IS NOT NULL THEN UI2.`user_id` 
							WHEN II.`home_store_type`=3 AND UI3.`user_id`!='' AND UI3.`user_id` IS NOT NULL THEN UI3.`user_id` 
							ELSE UID.`user_id` 
						END AS last_invoice_user_id, 
						CASE 
							WHEN UI.`user_id`!='' AND UI.`user_id` IS NOT NULL THEN CONCAT(COALESCE(UI.`first_name`, ''),' ',COALESCE(UI.`last_name`, '')) 
							WHEN II.`home_store_type`=1 AND UI1.`user_id`!='' AND UI1.`user_id` IS NOT NULL THEN CONCAT(COALESCE(UI1.`first_name`, ''),' ',COALESCE(UI1.`last_name`, '')) 
							WHEN II.`home_store_type`=2 AND UI2.`user_id`!='' AND UI2.`user_id` IS NOT NULL THEN CONCAT(COALESCE(UI2.`first_name`, ''),' ',COALESCE(UI2.`last_name`, '')) 
							WHEN II.`home_store_type`=3 AND UI3.`user_id`!='' AND UI3.`user_id` IS NOT NULL THEN CONCAT(COALESCE(UI3.`first_name`, ''),' ',COALESCE(UI3.`last_name`, '')) 
							ELSE CONCAT(COALESCE(UID.`first_name`, ''),' ',COALESCE(UID.`last_name`, '')) 
						END AS last_invoice_user_name, 
						CASE 
							WHEN UI.`user_id`!='' AND UI.`user_id` IS NOT NULL THEN UI.`email` 
							WHEN II.`home_store_type`=1 AND UI1.`user_id`!='' AND UI1.`user_id` IS NOT NULL THEN UI1.`email` 
							WHEN II.`home_store_type`=2 AND UI2.`user_id`!='' AND UI2.`user_id` IS NOT NULL THEN UI2.`email` 
							WHEN II.`home_store_type`=3 AND UI3.`user_id`!='' AND UI3.`user_id` IS NOT NULL THEN UI3.`email` 
							ELSE UID.`email` 
						END AS last_invoice_user_email, 
						CONCAT(COALESCE(CI.`first_name`, ''),' ',COALESCE(CI.`last_name`, ''),' ',COALESCE(CI.`business_name`, '')) AS customer_name, CI.`first_name` AS customer_first_name, CI.`last_name` AS customer_last_name, CI.`business_name` AS customer_business_name, CI.`phone` AS customer_phone, CI.`email` AS customer_email, CI.`address` AS customer_address, CI.`suburb_town` AS customer_suburb, CI.`state` AS customer_state, 
						COALESCE(SI.`store_name`, DBI.`distribution_name`,PI.`production_name`) AS location_name, 
						CASE 
							WHEN II.`home_store_type`=1 AND UI1.`first_name`!='' AND UI1.`first_name` IS NOT NULL THEN CONCAT(COALESCE(UI1.`first_name`, ''),' ',COALESCE(UI1.`last_name`, '')) 
							WHEN II.`home_store_type`=1 AND (UI1.`first_name`='' OR UI1.`first_name` IS NULL) THEN CONCAT(COALESCE(UID.`first_name`, ''),' ',COALESCE(UID.`last_name`, '')) 
							WHEN II.`home_store_type`=2 THEN CONCAT(COALESCE(UI2.`first_name`, ''),' ',COALESCE(UI2.`last_name`, '')) 
							WHEN II.`home_store_type`=3 THEN CONCAT(COALESCE(UI3.`first_name`, ''),' ',COALESCE(UI3.`last_name`, '')) 
						END AS location_manager, 
						COALESCE(SI.`email`, DBI.`email`, PI.`email`) AS location_email, 
						COALESCE(SI.`phone_number`, DBI.`phone_number`, PI.`phone_number`) AS location_phone_number, 
						COALESCE(SI.`facebook_link`, DBI.`facebook_link`, PI.`facebook_link`) AS location_facebook, 
						COALESCE(SI.`google_link`, DBI.`google_link`, PI.`google_link`) AS location_google, 
						COALESCE(SI.`address`,DBI.`address`,PI.`address`) AS location_address, 
						COALESCE(SI.`suburb`,DBI.`suburb`,PI.`suburb`) AS location_suburb, 
						COALESCE(SI.`postcode`,DBI.`postcode`,PI.`postcode`) AS location_postcode, 
						COALESCE(SI.`state`,DBI.`state`,PI.`state`) AS location_state, 
						COALESCE(SI.`directions`,DBI.`directions`,PI.`directions`) AS location_directions, 
						COALESCE(SI.`country`,DBI.`country`,PI.`country`) AS location_country, 
						COALESCE(SI.`ABN`, DBI.`ABN`, PI.`ABN`) AS location_ABN, 
						COALESCE(SI.`BSB`, DBI.`BSB`, PI.`BSB`) AS location_BSB, 
						COALESCE(SI.`account_number`, DBI.`account_number`, PI.`account_number`) AS location_account_number, 
						TTI.`ticket_type_name`, BI.`brand_name`, MI.`model_name` 
					FROM ( 
						SELECT 
							IP.*,IT.type,IT.iuser_id 
						FROM ( 
							SELECT 
								*, MAX(AA.invoice_id) as max_invoice_id 
							FROM ( 
								SELECT 
									XX.*, 1 AS type, COALESCE(ZZ.`user_id`, XX.`user_id`) AS iuser_id 
								FROM 
									`ki_invoices_info` XX 
								LEFT JOIN `ki_jobs_info` YY ON 
									XX.`job_id` = YY.`job_id` AND YY.`delete_flag`=0 
								LEFT JOIN `ki_estimates_info` ZZ ON 
									YY.`job_id` = ZZ.`job_id` AND ZZ.`status`=2 AND ZZ.`delete_flag`=0 
								WHERE 
									XX.`is_draft`=0 AND (TIMESTAMPDIFF(MINUTE, XX.`created_on`, '" . date("Y-m-d H:i:s") . "')/60)>" . (float)get_meta_value(45) . " AND XX.`created_on`>'2020-02-12 09:00:00' AND XX.`customer_id`!=0 AND XX.`customer_id` IS NOT NULL AND XX.`job_id`!=0 AND XX.`job_id` IS NOT NULL AND XX.`total_including_GST`>=100 AND XX.`delete_flag`=0 
								UNION
								SELECT *, 2 AS type, `user_id` AS iuser_id FROM `ki_invoices_info` WHERE `is_draft`=0 AND (TIMESTAMPDIFF(MINUTE, `created_on`, '" . date("Y-m-d H:i:s") . "')/60)>" . (float)get_meta_value(45) . " AND `created_on`>'2020-04-10 00:00:00' AND `customer_id`!=0 AND `customer_id` IS NOT NULL AND (`job_id`=0 OR `job_id` IS NULL) AND `total_including_GST`>=10 AND `delete_flag`=0 
							) AA
							GROUP BY `customer_id` ORDER BY `created_on` DESC 
						) IT 
						INNER JOIN `ki_invoices_info` IP ON 
							IP.`invoice_id`=IT.`max_invoice_id` 
					) II 
					INNER JOIN `ki_customers_info` CI ON 
						II.`customer_id`=CI.`customer_id` AND (CI.`is_unsubscribed_to_marketing`=0 OR CI.`is_unsubscribed_to_marketing` IS NULL) AND CI.`delete_flag`=0 
					LEFT JOIN ( 
						SELECT 
							T1.`invoice_id`,COALESCE(T2.amt_paid,0)+T1.`used_store_credit`+T1.`used_loyalty_credits`+T1.`used_deposit` AS amount_paid 
						FROM 
							`ki_invoices_info` T1 
						LEFT JOIN ( 
							SELECT `invoice_id`, SUM(`amount` - `change`) AS amt_paid FROM `ki_invoice_payment_info` WHERE `undo_datetime` IS NULL AND `delete_flag`=0 GROUP BY `invoice_id` ORDER BY `created_on`
						) T2 ON 
							T1.`invoice_id`=T2.`invoice_id`
					) IPI ON 
						II.`invoice_id`=IPI.`invoice_id` 
					LEFT JOIN `ki_users_info` UI ON 
						II.`iuser_id` = UI.`user_id` AND UI.`is_enabled`=1 AND UI.`delete_flag` = 0 
					LEFT JOIN `ki_stores_info` SI ON 
						II.`home_store_type`=1 AND II.`home_store_id`=SI.`store_id` AND SI.`delete_flag`=0 
					LEFT JOIN `ki_users_info` UI1 ON 
						SI.`store_manager_id`=UI1.`user_id` AND UI1.`is_enabled`=1 AND UI1.`delete_flag`=0 
					LEFT JOIN `ki_users_info` UID ON 
						UID.`user_id`='" . get_meta_value(27) . "' AND UID.`is_enabled`=1 AND UID.`delete_flag`=0 
					LEFT JOIN `ki_distribution_branches_info` DBI ON 
						II.`home_store_type`=2 AND II.`home_store_id`=DBI.`distribution_branch_id` AND DBI.`delete_flag`=0 
					LEFT JOIN `ki_users_info` UI2 ON 
						DBI.`manager_id`=UI2.`user_id` AND UI2.`is_enabled`=1 AND UI2.`delete_flag`=0 
					LEFT JOIN `ki_production_info` PI ON 
						II.`home_store_type`=3 AND II.`home_store_id`=PI.`production_id` AND PI.`delete_flag`=0 
					LEFT JOIN `ki_users_info` UI3 ON 
						PI.`manager_id`=UI3.`user_id` AND UI3.`is_enabled`=1 AND UI3.`delete_flag`=0 
					LEFT JOIN `ki_jobs_info` JI ON
						II.`job_id`=JI.`job_id` AND JI.`status`!=10 AND JI.`status`!=11 AND JI.`is_cancelled`!=1 AND JI.`delete_flag`=0
					LEFT JOIN `ki_ticket_types_info` TTI ON 
						JI.`ticket_type_id`=TTI.`ticket_type_id` AND TTI.`delete_flag`=0 
					LEFT JOIN `ki_brands_info` BI ON 
						JI.`brand_id`=BI.`brand_id` AND BI.`delete_flag`=0 
					LEFT JOIN `ki_models_info` MI ON 
						JI.`model_id`=MI.`model_id` AND MI.`delete_flag`=0 
					WHERE 
						II.`invoice_id` NOT IN ( 
							SELECT DISTINCT `type_id` FROM `ki_follow_ups_info` WHERE `type`=1 AND `email_type`=6 AND (`status`=1 OR `status`=10) AND (`parent_followup_id`=0 OR `parent_followup_id` IS NULL) AND `delete_flag`=0
						) AND II.`customer_id` NOT IN ( 
							SELECT DISTINCT `customer_id` FROM `ki_follow_ups_info` WHERE `type`=1 AND `email_type`=6 AND (`status`=1 OR `status`=10) AND (`parent_followup_id`=0 OR `parent_followup_id` IS NULL) AND `created_on`> DATE_SUB('" . date("Y-m-d H:i:s") . "', INTERVAL 3 MONTH) AND `delete_flag`=0
						) AND ((II.`total_including_GST`>0 AND IPI.`amount_paid`>=II.`total_including_GST`) OR (II.`total_including_GST`<0 AND IPI.`amount_paid`<=II.`total_including_GST`))
					ORDER BY 
						II.`customer_id`
				) A";
		$res = $con->query($sql);
		if ($res->num_rows) {
			$data['status'] = 1;
			while ($row = $res->fetch_assoc()) {
				$address = [$row['location_address'], $row['location_directions'], $row['location_suburb'], $row['location_state'], $row['location_country'], $row['location_postcode']];
				$row['concatenated_address'] = implode(", ", array_filter($address));
				$data['list'][] = $row;
			}
		} else {
			$data['errors'][] = $con->error;
		}
		return $data;
	}
	function get_onboard_questions_for_job($inputdata)
	{
		/* 
		input params - 
			// none
		function is used to get list of required on board questions for job
		output - 
			$data = array(
				"status" => 0,
				"list" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"list" => array(),
			"errors" => array()
		);
		$qry = "";
		if (!empty($inputdata['website'])) {
			$qry = " AND `question_val`!='Is this Fix or Free?'";
		}
		$sql = "SELECT A.`ques_id`,`question_val`,`option_id` FROM `ki_onboarding_options_info` A INNER JOIN `ki_onboarding_ques_info` B ON A.`ques_id`=B.`ques_id` AND B.`is_enabled`=1 AND A.`delete_flag`=0 AND B.`delete_flag`=0" . $qry;
		$res = $con->query($sql);
		if ($res->num_rows) {
			$data['status'] = 1;
			while ($row = $res->fetch_assoc()) {
				$data['list'][$row['ques_id']]['question'] = $row['question_val'];
				$data['list'][$row['ques_id']]['options'][] = $row['option_id'];
			}
		} else {
			$data['errors'][] = $con->error;
		}
		return $data;
	}
	function get_required_custom_fields_for_job($inputdata)
	{
		/* 
		input params - 
			"job_id" => $job_id
		function is used to get list of required custom fields of a particular job_id
		output - 
			$data = array(
				"status" => 0,
				"list" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"list" => array(),
			"errors" => array()
		);
		$job_details = $this->get_job_details(array("key" => "job_id", "value" => $inputdata['job_id']));
		$sql = "SELECT * FROM `ki_custom_fields_mapping_info` WHERE ((`mapping_type`=1 AND `mapping_id`='" . safe_str($job_details['ticket_type_id']) . "') OR (`mapping_type`=2 AND `mapping_id`='" . safe_str($job_details['brand_id']) . "') OR (`mapping_type`=3 AND `mapping_id` IN ( SELECT `work_to_complete_id` FROM `ki_job_work_to_complete_info` WHERE `job_id`='" . safe_str($inputdata['job_id']) . "' AND `delete_flag`=0 ))) AND `custom_field_type`=1 AND `custom_is_required`=1 AND `delete_flag`=0";
		$res = $con->query($sql);
		if ($res->num_rows) {
			$data['status'] = 1;
			while ($row = $res->fetch_assoc()) {
				$data['list'][$row['custom_field_id']] = $row['custom_field_name'];
			}
		} else {
			$data['errors'][] = $con->error;
		}
		return $data;
	}
	function get_work_to_complete_details($inputdata)
	{
		/* 
		input params - 
			"work_to_complete_id" => $work_to_complete_id
		function is used to get details of a particular work_to_complete_id
		output - 
			$data = array(
				"status" => 0,
				"details" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"details" => array(),
			"errors" => array()
		);
		$sql = "SELECT * FROM `ki_work_to_complete_info` WHERE `work_to_complete_id`='" . safe_str($inputdata['work_to_complete_id']) . "' AND `delete_flag`=0";
		$result = $con->query($sql);
		if ($result) {
			$data['status'] = 1;
			$data['details'] = $result->fetch_assoc();
		} else {
			$data['errors'][] = $con->error();
		}
		return $data;
	}
	function get_model_detail($inputdata)
	{
		/* 
		input params - 
			"model_id" => $model_id
		function is used to get details of a particular model
		output - 
			$data = array(
				"status" => 0,
				"details" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"details" => array(),
			"errors" => array()
		);
		$sql = "SELECT * FROM `ki_models_info` WHERE `model_id`='" . safe_str($inputdata['model_id']) . "' AND `delete_flag`=0";
		$result = $con->query($sql);
		if ($result) {
			$data['status'] = 1;
			$data['details'] = $result->fetch_assoc();
		} else {
			$data['errors'][] = $con->error();
		}
		return $data;
	}
	function get_brand_details($inputdata)
	{
		/* 
		input params - 
			"brand_id" => $brand_id
		function is used to get details of a particular brand
		output - 
			$data = array(
				"status" => 0,
				"details" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"details" => array(),
			"errors" => array()
		);
		$sql = "SELECT * FROM `ki_brands_info` WHERE `brand_id`='" . safe_str($inputdata['brand_id']) . "' AND `delete_flag`=0";
		$result = $con->query($sql);
		if ($result) {
			$data['status'] = 1;
			$data['details'] = $result->fetch_assoc();
		} else {
			$data['errors'][] = $con->error();
		}
		return $data;
	}
	function get_ticket_type_details($inputdata)
	{
		/* 
		input params - 
			"ticket_type_id" => $ticket_type_id
		function is used to get details of a particular ticket_type
		output - 
			$data = array(
				"status" => 0,
				"details" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"details" => array(),
			"errors" => array()
		);
		$sql = "SELECT * FROM `ki_ticket_types_info` WHERE `ticket_type_id`='" . safe_str($inputdata['ticket_type_id']) . "' AND `delete_flag`=0";
		$result = $con->query($sql);
		if ($result) {
			$data['status'] = 1;
			$data['details'] = $result->fetch_assoc();
		} else {
			$data['errors'][] = $con->error();
		}
		return $data;
	}
	function get_landing_details($inputdata)
	{
		/* 
		input params - 
			"ticket_type_id" => $ticket_type_id
		function is used to get details of a landing_id
		output - 
			$data = array(
				"status" => 0,
				"details" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"details" => array(),
			"errors" => array()
		);
		$sql = "SELECT * FROM `ki_landing_page_info` WHERE `landing_id`='" . safe_str($inputdata['landing_id']) . "' AND `delete_flag`=0";
		$result = $con->query($sql);
		if ($result) {
			$data['status'] = 1;
			$data['details'] = $result->fetch_assoc();
		} else {
			$data['errors'][] = $con->error();
		}
		return $data;
	}
	function get_customer_details($inputdata)
	{
		/* 
		input params - 
			"customer_id" => $customer_id
		function is used to get details of a particular customer
		output - 
			$data = array(
				"status" => 0,
				"details" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"details" => array(),
			"errors" => array()
		);
		$sql = "SELECT *, CONCAT(COALESCE(`first_name`, ''),' ',COALESCE(`last_name`, ''),' ',COALESCE(`business_name`, '')) AS customer_name, CONCAT(COALESCE(`first_name`, ''),' ',COALESCE(`last_name`, '')) AS customer_name_wo_business FROM `ki_customers_info` WHERE `customer_id`='" . safe_str($inputdata['customer_id']) . "' AND `delete_flag`=0";
		$result = $con->query($sql);
		if ($result) {
			$data['status'] = 1;
			$data['details'] = $result->fetch_assoc();
		} else {
			$data['errors'][] = $con->error();
		}
		return $data;
	}
	function get_nominated_customer_details($inputdata)
	{
		global $con;
		$data = array(
			"status" => 0,
			"details" => array(),
			"errors" => array()
		);
		$sql = "SELECT *,CONCAT(COALESCE(`first_name`, ''),' ',COALESCE(`last_name`, '')) AS nominated_customer_name FROM `ki_customer_nominated_contacts_info` WHERE `nominated_contact_id` = '" . safe_str($inputdata['nominated_customer_id']) . "' AND `delete_flag`=0";
		$result = $con->query($sql);
		if ($result) {
			$data['status'] = 1;
			$data['details'] = $result->fetch_assoc();
		} else {
			$data['errors'][] = $con->error();
		}
		return $data;
	}
	function delete_estimate_wizard_line_items($inputdata)
	{
		/* 
		input params - 
			none
		function is used to delete value adds pdt from line items table of estimates
		output params - 
			$data = array(
				"status" => 0,
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"errors" => array()
		);
		$sql = "UPDATE `ki_estimate_line_items_info` SET `delete_flag`=1 WHERE `estimate_id`='" . safe_str($inputdata['estimate_id']) . "' AND `is_va_pdt`=1";
		$res = $con->query($sql);
		if ($res) {
			$data['status'] = 1;
		} else {
			$data['errors'][] = $con->error;
		}
		return $data;
	}
	function check_if_referral_source_exists($inputdata)
	{
		/* 
		input params - 
			none
		function is used to get check whether referral source exists in same category
		output params - 
			$data = array(
				"status" => 0,
				"list" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$where = "";
		if (!empty($inputdata['source_id'])) {
			$where .= " AND `source_id`!=" . safe_str($inputdata['source_id']);
		}
		$query = " (`category_id`=0 OR `category_id` IS NULL) AND ";
		if (!empty($inputdata['category_id'])) {
			$query = " `category_id`='" . safe_str($inputdata['category_id']) . "' AND ";
		}
		$que = "SELECT * FROM `ki_referral_sources_info` WHERE " . $query . " `delete_flag`=0 AND `source_name`='" . safe_str(trim(preg_replace('/\s+/', ' ', $inputdata['source_name']))) . "'" . $where;
		$pcount_result = $con->query($que);
		$pcount_row = $pcount_result->fetch_assoc();
		return $pcount_row;
	}
	function get_referral_categories_list($inputdata)
	{
		/* 
		input params - 
			none
		function is used to get list of referral categories
		output params - 
			$data = array(
				"status" => 0,
				"list" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"list" => array(),
			"errors" => array()
		);
		$sql = "SELECT * FROM `ki_referral_categories_info` WHERE `is_enabled`=1 AND `delete_flag`=0 ORDER BY `category_name`";
		$res = $con->query($sql);
		if ($res->num_rows) {
			$data['status'] = 1;
			while ($row = $res->fetch_assoc()) {
				$data['list'][] = $row;
			}
		} else {
			$data['errors'][] = $con->error;
		}
		return $data;
	}
	function get_referral_categories_pagging_list($inputdata)
	{
		/*
		input - 
			"function" => "get_referral_categories_pagging_list",
			"page_no" => $request["PageNumber"],
			"row_size" => $request["RowSize"],
			"sort_on" => $request["SortOn"],
			"sort_type" => $request["SortType"],
			"search" => $search
		return pagging list of referral category
		output - 
			$data = array(
				"total_records" => 0,
				"total_pages" => 0,
				"pagging_list"=>array()
			);
		*/
		global $con;
		$data = array(
			"total_records" => 0,
			"total_pages" => 0,
			"pagging_list" => array()
		);
		$table = safe_str("ki_referral_categories_info");
		$page_no = safe_str($inputdata['page_no']);
		$row_size = safe_str($inputdata['row_size']);
		$sort_on = safe_str($inputdata['sort_on']);
		$sort_type = safe_str($inputdata['sort_type']);
		$query = '';
		if (!empty($inputdata['search'])) {
			$query .= "AND (category_name LIKE '%" . safe_str($inputdata['search']) . "%' )";
		}
		$pcount_qry = "SELECT COUNT(*) AS total_count FROM `" . safe_str($table) . "` WHERE `delete_flag`=0 " . $query;
		$pcount_result = $con->query($pcount_qry);
		$pcount_row = $pcount_result->fetch_assoc();
		$total_records = $pcount_row["total_count"];
		@$total_pages = ceil($total_records / $row_size);
		if ($total_pages == 0) {
			$total_pages = 1;
		}
		if ($page_no > $total_pages) {
			$page_no = $total_pages;
		}
		$limit_from = ($row_size * $page_no) - $row_size;
		$pagg_qry = "SELECT * FROM `" . safe_str($table) . "` WHERE `delete_flag`=0 " . $query . " ORDER BY " . safe_str($sort_on) . " " . safe_str($sort_type) . " LIMIT " . $limit_from . ", " . $row_size;
		$pagg_result = $con->query($pagg_qry);
		$pagg_count = $pagg_result->num_rows;
		$pagging_list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}
		$data["total_records"] = $total_records;
		$data["total_pages"] = $total_pages;
		$data["pagging_list"] = $pagging_list;

		return $data;
	}
	function GetInvoiceLastUserID($inputdata)
	{
		/* 
		input params - 
			none
		function is used to get last user who has served this client for passed location.
		output params - 
			$data = array(
				"status" => 0,
				"details" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"details" => array(),
			"errors" => array()
		);
		$qry = "SELECT `user_id` FROM `ki_invoices_info` II INNER JOIN (SELECT MAX(`created_on`) AS MaxCreatedOn FROM `ki_invoices_info` WHERE `customer_id`='" . safe_str($inputdata['customer_id']) . "' AND `home_store_type`='" . safe_str($inputdata['home_store_type']) . "' AND `home_store_id`='" . safe_str($inputdata['home_store_id']) . "' AND `delete_flag`=0) AA ON II.`created_on`=AA.`MaxCreatedOn`";
		$result = $con->query($qry);
		if ($result) {
			$data['status'] = 1;
			if ($result->num_rows) {
				$data['details'] = $result->fetch_assoc();
			}
		} else {
			$data['errors'][] = $con->error;
		}
		return $data;
	}
	function get_work_to_complete_list($inputdata)
	{
		/* 
		input params - 
			none
		function is used to get list of work_to_complete
		output params - 
			$data = array(
				"status" => 0,
				"list" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"list" => array(),
			"errors" => array()
		);
		$sql = "SELECT * FROM `ki_work_to_complete_info` WHERE `is_enabled`=1 AND `delete_flag`=0 ORDER BY `work_to_complete`";
		$res = $con->query($sql);
		if ($res->num_rows) {
			$data['status'] = 1;
			while ($row = $res->fetch_assoc()) {
				$data['list'][] = $row;
			}
		} else {
			$data['errors'][] = $con->error;
		}
		return $data;
	}
	function get_selectable_stores($inputdata)
	{
		/* 
		input params - 
			"function"=>"get_selectable_stores",
		function is used to get selectable number of stores in reports 
		output params - 
			$data = array(
				"status" => 0,
				"details" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"details" => array(),
			"errors" => array()
		);
		$sql = "select * from ki_meta_info where meta_id=32";
		$res = $con->query($sql);
		if ($res->num_rows) {
			$row = $res->fetch_assoc();
			$data['status'] = 1;
			$data['details'] = $row;
		} else {
			$data['errors'][] = "Failed to get details.";
		}
		return $data;
	}
	function GetEstimatesList($inputdata)
	{
		/* 
		input params - 
			array of keys and values. : "where" => array("key" => $value ..)
		function is used to get list of estimates based on passed parameters
		output params - 
			$data = array(
				"status" => 0,
				"list" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"list" => array(),
			"errors" => array()
		);
		$query = "";
		if (!empty($inputdata["where"])) {
			foreach ($inputdata["where"] as $key => $value) {
				if (is_null($value)) {
					$query .= " AND `" . safe_str($key) . "` IS NULL";
				} else {
					$query .= " AND `" . safe_str($key) . "`='" . safe_str($value) . "'";
				}
			}
		}
		if (!empty($inputdata["not"])) {
			foreach ($inputdata["not"] as $key => $value) {
				if (is_null($value)) {
					$query .= " AND `" . safe_str($key) . "` IS NOT NULL";
				} else {
					$query .= " AND `" . safe_str($key) . "`!='" . safe_str($value) . "'";
				}
			}
		}
		$sql = "SELECT * FROM `ki_estimates_info` WHERE `delete_flag`=0" . $query;
		$result = $con->query($sql);
		if ($result) {
			$data['status'] = 1;
			while ($row = $result->fetch_assoc()) {
				$data['list'][] = $row;
			}
		} else {
			$data['errors'][] = $con->error();
		}
		return $data;
	}
	function GetRosterDetailedInfo($inputdata)
	{
		/* 
		input params - 
			roster_id, location_type, location_id, from_date, to_date
		function is used to get detailed data of roster based on roster_id
		output params - 
			$data = array(
				"status" => 0,
				"list" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"list" => array(),
			"errors" => array()
		);
		$non_trade_days = $this->get_roster_non_trade_days(array(
			"roster_id" => $inputdata['roster_id'],
			"location_id" => $inputdata['location_id'],
			"location_type" => $inputdata['location_type']
		));
		$non_trade_day = 0;
		if (in_array(date("l", strtotime($inputdata['from_date'])), $non_trade_days)) {
			$non_trade_day = 1;
		}
		$data['list'][$inputdata['from_date']] = array("non_trade_day" => $non_trade_day, "users" => array());
		while ($inputdata['from_date'] != $inputdata['to_date']) {
			$non_trade_day = 0;
			$inputdata['from_date'] = date('Y-m-d', strtotime($inputdata['from_date'] . " +1 day"));
			if (in_array(date("l", strtotime($inputdata['from_date'])), $non_trade_days)) {
				$non_trade_day = 1;
			}
			$data['list'][$inputdata['from_date']] = array("non_trade_day" => $non_trade_day, "users" => array());
		}
		$sql = "SELECT 
					RI.*, RDI.*, weekly_total_hours, 
					CASE 
						WHEN ((TO_SECONDS(finish_time)-TO_SECONDS(start_time))/3600>5) THEN ((TO_SECONDS(finish_time)-TO_SECONDS(start_time))/3600 - 0.5) 
						ELSE (TO_SECONDS(finish_time) - TO_SECONDS(start_time))/3600 
					END AS total_hours 
				FROM 
					`ki_roster_data_info` RDI 
				INNER JOIN `ki_rosters_info` RI ON 
					RDI.`roster_id` = RI.`roster_id` AND RI.`roster_id` = '" . safe_str($inputdata['roster_id']) . "' AND RI.`delete_flag` = 0 
				INNER JOIN ( 
					SELECT 
						`roster_id`, `user_id`, SUM(AA.total_hours) AS weekly_total_hours 
					FROM ( 
						SELECT 
							`roster_id`, `user_id`, 
							CASE 
								WHEN ((TO_SECONDS(finish_time)-TO_SECONDS(start_time))/3600>5) THEN ((TO_SECONDS(finish_time)-TO_SECONDS(start_time))/3600 - 0.5) 
								ELSE (TO_SECONDS(finish_time) - TO_SECONDS(start_time))/3600 
							END AS total_hours 
						FROM 
							`ki_roster_data_info` 
						WHERE 
							`delete_flag` = 0 
					) AA 
					GROUP BY 
						`roster_id`,`user_id` 
				) HOURS ON 
					RDI.`roster_id` = HOURS.`roster_id` AND RDI.`user_id` = HOURS.`user_id` 
				WHERE 
					RDI.`delete_flag` = 0 
				ORDER BY 
					`date`, RDI.`created_on`";
		$result = $con->query($sql);
		if ($result) {
			$data['status'] = 1;
			while ($row = $result->fetch_assoc()) {
				if (!empty($row['user_id'])) $data['list'][$row['date']]['users'][] = $row;
			}
		} else {
			$data['errors'][] = $con->error();
		}
		return $data;
	}
	function GetActiveStockTakeLocations($inputdata)
	{
		/* 
		input - 
			no params required
		function is used to get list of all the locations for which stocktake is going on.
		output:-
			$data = array(
				"status" => 0,
				"list" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"list" => array(),
			"errors" => array()
		);
		$where = "";
		if (!empty($inputdata['location_type']) && !empty($inputdata['location_id'])) {
			$where = " AND `location_type`='" . safe_str($inputdata['location_type']) . "' AND `location_id`='" . safe_str($inputdata['location_id']) . "'";
		}
		$sql = "SELECT
					DISTINCT CONCAT(`location_type`, '-', `location_id`) AS location, `location_type`, `location_id`, COALESCE(SI.`store_name`, DBI.`distribution_name`, PI.`production_name`) AS location_name
				FROM
					`ki_stocktakes_info` STI
				LEFT JOIN `ki_stores_info` SI ON
					SI.`store_id` = STI.`location_id` AND STI.location_type = 1 AND SI.`delete_flag` = 0
				LEFT JOIN `ki_distribution_branches_info` DBI ON
					DBI.`distribution_branch_id` = STI.`location_id` AND STI.location_type = 2 AND DBI.`delete_flag` = 0
				LEFT JOIN `ki_production_info` PI ON
					PI.`production_id` = STI.`location_id` AND STI.location_type = 3 AND PI.`delete_flag` = 0
				WHERE
					STI.`status` = 1 AND STI.`delete_flag` = 0" . $where;
		$result = $con->query($sql);
		if ($result) {
			$data['status'] = 1;
			while ($row = $result->fetch_assoc()) {
				$data['list'][$row['location_type'] . "-" . $row['location_id']] = $row;
			}
		} else {
			$data['errors'][] = $con->error();
		}
		return $data;
	}
	function UpdateStockTake($inputdata)
	{
		/* 
		input - 
			"location_type" => $location_type,
			"product_id" => $product_id,
			"location_id" => $location_id,
			"quantity" => $quantity , positive integer
			"action_type" => $action_type  ,1 - increment, 2 - decrement
		output:-
			$data = array(
				"status" => 0,
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"errors" => array()
		);
		$Stocktake = new Stocktake();
		$stocktake_mode = $Stocktake->CheckIfProductExistInActiveStocktake(array(
			"product_id" => $inputdata['product_id'],
			"location_type" => $inputdata['location_type'],
			"location_id" => $inputdata['location_id']
		))['details'];
		if (!empty($stocktake_mode)) {
			// print_r($stocktake_mode);
			if ($inputdata['action_type'] == 1) {
				$stock_on_hand = " `quantity`=`quantity`-" . safe_str($inputdata['quantity']) . ", `stock_on_hand`=`stock_on_hand`-" . safe_str($inputdata['quantity']);
			} elseif ($inputdata['action_type'] == 2) {
				$stock_on_hand = " `quantity`=`quantity`+" . safe_str($inputdata['quantity']) . ", `stock_on_hand`=`stock_on_hand`+" . safe_str($inputdata['quantity']);
			}
			$sql = "UPDATE `ki_stocktake_products_info` SET " . $stock_on_hand . ", `is_verified`=0 WHERE `stocktake_product_id`='" . $stocktake_mode['stocktake_product_id'] . "' AND `delete_flag`=0";
			$result = $con->query($sql);
			if (!$result) {
				$data['errors'][] = $con->error();
			}
		}
		if (empty($data["errors"])) {
			$data['status'] = 1;
		}
		return $data;
	}
	function get_roster_data_details_in_time_clock($inputdata)
	{
		/* 
		input params - 
			"date" => $date,
			"user_id" => $members['user_id'],
			"location_id" => $location_id,
			"location_type" => $location_type
		function is used to get details of a particular roster_data in time clock
		output - 
			$data = array(
				"status" => 0,
				"details" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"details" => array(),
			"errors" => array()
		);
		$sql = "SELECT RDI.* FROM `ki_roster_data_info` RDI INNER JOIN `ki_rosters_info` RI ON RDI.`roster_id` = RI.`roster_id` AND RI.`delete_flag`=0 WHERE `user_id`='" . safe_str($inputdata['user_id']) . "' AND `location_type`='" . safe_str($inputdata['location_type']) . "' AND `location_id`='" . safe_str($inputdata['location_id']) . "' AND `date`='" . safe_str($inputdata['date']) . "' AND RDI.`delete_flag`=0";
		$result = $con->query($sql);
		if ($result) {
			$data['status'] = 1;
			$data['details'] = $result->fetch_assoc();
		} else {
			$data['errors'][] = $con->error();
		}
		return $data;
	}
	function get_roster_data_details($inputdata)
	{
		/* 
		input params - 
			"roster_data_id" => $roster_data_id
		function is used to get details of a particular roster_data
		output - 
			$data = array(
				"status" => 0,
				"details" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"details" => array(),
			"errors" => array()
		);
		$query = "";
		if (!empty($inputdata["where"])) {
			foreach ($inputdata["where"] as $key => $value) {
				$query .= " AND `" . safe_str($key) . "`='" . safe_str($value) . "'";
			}
		}
		if (isset($inputdata["roster_data_id"]) && !empty($inputdata["roster_data_id"])) {
			$query .= " AND `roster_data_id`='" . safe_str($inputdata['roster_data_id']) . "'";
		}
		$sql = "SELECT * FROM `ki_roster_data_info` WHERE `delete_flag`=0" . $query;
		$result = $con->query($sql);
		if ($result) {
			$data['status'] = 1;
			$data['details'] = $result->fetch_assoc();
		} else {
			$data['errors'][] = $con->error();
		}
		return $data;
	}
	function get_time_clock_details($inputdata)
	{
		/* 
		input params - 
			"time_clock_id" => $time_clock_id
		function is used to get details of a particular time_clock
		output - 
			$data = array(
				"status" => 0,
				"details" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"details" => array(),
			"errors" => array()
		);
		$sql = "SELECT * FROM `ki_time_clock_info` WHERE `time_clock_id`='" . safe_str($inputdata['time_clock_id']) . "' AND `delete_flag`=0";
		$result = $con->query($sql);
		if ($result) {
			$data['status'] = 1;
			$data['details'] = $result->fetch_assoc();
		} else {
			$data['errors'][] = $con->error();
		}
		return $data;
	}
	function VerifyTimeClockReport($inputdata)
	{
		/* 
		input params - 
			"post" => $_POST,
			"user_id" => $_SESSION['ki_user']['user_id'],
			"location_type" => $_SESSION['ki_user']['location_type'],
			"location_id" => $_SESSION['ki_user']['location_id']
		function is used to create new entry or modify on check and uncheck of verified in End of day balance report.
		output -  
			$data = array(
				"status" => 0,							// value is set as 1 if no error is there
				"errors" => array()						// array of errors - validation errors
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"errors" => array()
		);
		$report_id = '';
		if (isset($inputdata['post']['report_id']) && !empty($inputdata['post']['report_id'])) {
			$sql = "SELECT * FROM `ki_time_clock_report_info` WHERE `report_id`='" . safe_str($inputdata['post']['report_id']) . "'";
			$res = $con->query($sql);
			if ($res) {
				$row = $res->fetch_assoc();
				if (empty($row) || $row['delete_flag'] == 1) {
					$data['errors'][] = "Failed to get details.";
				} else {
					$report_id = $row['report_id'];
				}
			} else {
				$data['errors'][] = $con->error;
			}
		}
		if (empty($data['errors'])) {
			if (isset($inputdata['post']['user_id']) && !empty($inputdata['post']['user_id'])) {
				$user_details = $this->get_user_details(array("user_id" => $inputdata['post']['user_id']));
				if ($user_details['status'] == 1 && !empty($user_details['details'])) {
					if (isset($inputdata['post']['value']) && ($inputdata['post']['value'] == 0 || $inputdata['post']['value'] == 1)) {
						if (isset($inputdata['post']['from_date']) && !empty($inputdata['post']['from_date']) && isset($inputdata['post']['to_date']) && !empty($inputdata['post']['to_date'])) {
							if (validate_date($inputdata['post']['from_date']) && validate_date($inputdata['post']['to_date'])) {
								$date = new DateTime($inputdata['post']['from_date']);
								$from_date = $date->format('Y-m-d');
								$date1 = new DateTime($inputdata['post']['to_date']);
								$to_date = $date1->format('Y-m-d');
								if (isset($inputdata['post']['location']) && !empty($inputdata['post']['location'])) {
									$location = explode(" ", $inputdata['post']['location']);
									$location_id = array_pop($location);
									$location_type = implode('', $location);
									if (!empty($location_type) && !empty($location_id)) {
										$CheckIfLocationExists = $this->CheckIfLocationExists(array("location_type" => $location_type, "location_id" => $location_id));
										if ($CheckIfLocationExists['status'] == 1) {
											$in_fields = array(
												"home_user_id" => $inputdata["user_id"],
												"home_location_type" => $inputdata["location_type"],
												"home_location_id" => $inputdata["location_id"],
												"is_verified" => $inputdata['post']['value'],
											);
											if (empty($report_id)) {
												$sql = "SELECT * FROM `ki_time_clock_report_info` WHERE `location_type`='" . safe_str($location_type) . "' AND `location_id`='" . safe_str($location_id) . "' AND `from_date`='" . safe_str($from_date . ' 00:00:00') . "' AND `to_date`='" . safe_str($to_date . ' 23:59:59') . "' AND `user_id`='" . safe_str($inputdata['post']['user_id']) . "' AND `delete_flag`=0";
												$res = $con->query($sql);
												if ($res->num_rows > 0) {
													$row = $res->fetch_assoc();
													$report_id = $row['report_id'];
												}
											}
											if (empty($report_id)) {
												/* create */
												$in_fields["from_date"] = "" . $from_date . " 00:00:00";
												$in_fields["to_date"] = $to_date . " 23:59:59";
												$in_fields["location_type"] = $location_type;
												$in_fields["location_id"] = $location_id;
												$in_fields["user_id"] = $inputdata['post']['user_id'];
												$in_fields["created_on"] = date("Y-m-d H:i:s");
												foreach ($in_fields as $field_key => $field_data) {
													$in_fields[safe_str($field_key)] = "'" . safe_str($field_data) . "'";
												}
												$up_qry = "INSERT INTO `ki_time_clock_report_info` (`" . implode("`, `", array_keys($in_fields)) . "`) VALUES (" . implode(", ", $in_fields) . ")";
											} else {
												/* update. */
												$in_fields["modified_on"] = date("Y-m-d H:i:s");
												foreach ($in_fields as $ifield => $ival) {
													if (empty($ival) || $ival == "null") {
														$up_fields[] = "`" . safe_str($ifield) . "` = null";
													} else {
														$up_fields[] = "`" . safe_str($ifield) . "` = '" . safe_str($ival) . "'";
													}
												}
												$up_qry = "UPDATE `ki_time_clock_report_info` SET " . implode(", ", $up_fields) . " WHERE `report_id` = '" . safe_str($report_id) . "'";
											}
											$up_result = $con->query($up_qry);
											if ($up_result) {
												$data['status'] = 1;
											} else {
												$data['errors'][] = $con->error;
											}
										} elseif ($CheckIfLocationExists['status'] == 2) {
											$data['errors'][] = "Location is disabled.";
										} elseif ($CheckIfLocationExists['status'] == 3) {
											$data['errors'][] = "Location doesn't exist.";
										} else {
											$data['errors'] = $CheckIfLocationExists['errors'];
										}
									} else {
										$data['errors'][] = "Failed to get location details.";
									}
								} else {
									$data['errors'][] = "Failed to get location details.";
								}
							} else {
								$data['errors'][] = "Invalid date.";
							}
						} else {
							$data['errors'][] = "Action cannot be performed as date is empty.";
						}
					} else {
						$data['errors'][] = "Invalid value.";
					}
				} else {
					$data['errors'][] = "Failed to get user details.";
				}
			} else {
				$data['errors'][] = "Failed to get user details.";
			}
		}
		if (empty($data['errors'])) {
			$data['status'] = 1;
		}
		return $data;
	}
	function get_eod_previous_traffic_count($inputdata)
	{
		/* 
		input params - 
			"location_type" => $location_type,
			"location_id" => $location_id,
			"date" => $created_on
		function is used to get traffic count of previous closed end of day balance on same day.
		output - 
			$data = array(
				"status" => 0,
				"details" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"details" => array("traffic_count" => ''),
			"errors" => array()
		);
		$qry = "SELECT `traffic_count` FROM `ki_tils_info` WHERE `location_type`='" . safe_str($inputdata['location_type']) . "' AND `location_id`='" . safe_str($inputdata['location_id']) . "' AND DATE(`modified_on`)=DATE('" . safe_str($inputdata['date']) . "') AND `is_closed`=1 AND `delete_flag`=0 ORDER BY `modified_on` DESC";
		$res = $con->query($qry);
		if ($res) {
			$data['status'] = 1;
			$data['details'] = $res->fetch_assoc();
		} else {
			$data['errors'][] = $con->error();
		}
		return $data;
	}
	function CheckIfLocationExists($inputdata)
	{
		/* 
		input params - 
			"location_type" => $location_type,
			"location_id" => $location_id
		function is used to check whether a specific location exists or not.
		output - 
			$data = array(
				"status" => 0,					// 0 - error in qry, 1 - no error, 2 - location is disabled, 3 - location doesn't exist or is deleted.
				"details" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"details" => array(),
			"errors" => array()
		);
		if ($inputdata['location_type'] == 1) {
			$table = "ki_stores_info";
			$key = "store_id";
			$name = "store_name";
			$manager_id = "store_manager_id";
		} elseif ($inputdata['location_type'] == 2) {
			$table = "ki_distribution_branches_info";
			$key = "distribution_branch_id";
			$name = "distribution_name";
			$manager_id = "manager_id";
		} elseif ($inputdata['location_type'] == 3) {
			$table = "ki_production_info";
			$key = "production_id";
			$name = "production_name";
			$manager_id = "manager_id";
		}
		$qry = "SELECT *, `" . $name . "` AS location_name,`" . $manager_id . "` AS manager_id FROM `" . $table . "` WHERE `" . $key . "`='" . $inputdata['location_id'] . "'";
		$res = $con->query($qry);
		if ($res) {
			$row = $res->fetch_assoc();
			if (empty($row) || $row['delete_flag'] == 1) {
				/* location doesn't exist or has been deleted. */
				$data['status'] = 3;
			} elseif ($row['is_enabled'] == 0) {
				/* location is disabled */
				$data['status'] = 2;
				$data['details'] = $row;
			} else {
				/* no error */
				$data['status'] = 1;
				$data['details'] = $row;
			}
		} else {
			$data['errors'][] = $con->error();
		}
		return $data;
	}
	function get_eod_report_actual_expected_amount($inputdata)
	{
		/* 
		input params - 
			"location_type" => $location_type,
			"location_id" => $location_id,
			"start_datetime" => $start_datetime,
			"end_datetime" => $end_datetime,
		function is used to get list of til on the basis of start_datetime, end_datetime and location
		output - 
			$data = array(
				"status" => 0,
				"details" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"details" => array(),
			"errors" => array()
		);
		$tender_totals = array();
		$to_date = '';
		$qry = "SELECT 
					SUM(`open_amount`) AS open_amount, SUM(`cash_count`) AS cash_count, SUM(`eftpos_settlement`) AS eftpos_settlement, SUM(`direct_deposit_settlement`) AS direct_deposit_settlement, SUM(`pay_advantage_settlement`) AS pay_advantage_settlement, SUM(`zip_pay_settlement`) AS zip_pay_settlement, MIN(`created_on`) AS from_date, MAX(`created_on`) AS max_created_on 
				FROM 
					`ki_tils_info` 
				WHERE 
					`location_type`='" . safe_str($inputdata['location_type']) . "' AND `location_id`='" . safe_str($inputdata['location_id']) . "' AND `created_on` BETWEEN '" . safe_str($inputdata['start_datetime']) . "' AND '" . safe_str($inputdata['end_datetime']) . "' AND `is_closed`=1 AND `delete_flag`=0";
		$res = $con->query($qry);
		if ($res) {
			$row = $res->fetch_assoc();
			if (!empty($row)) {
				$qry1 = "SELECT * FROM `ki_tils_info` WHERE `location_type`='" . safe_str($inputdata['location_type']) . "' AND `location_id`='" . safe_str($inputdata['location_id']) . "' AND `created_on`>'" . $row['max_created_on'] . "' AND `delete_flag`=0";
				$res1 = $con->query($qry1);
				if ($res1) {
					$row1 = $res1->fetch_assoc();
					$to_date = $row1['created_on'];
				} else {
					$data['errors'][] = $con->error();
				}
			}
			$open_amount = $row["open_amount"];
			$from_date = $row["from_date"];
			// $to_date = $row["to_date"];
			$tender_totals["cash"]["actual"] = $row["cash_count"];
			$tender_totals["eftpos"]["actual"] = $row["eftpos_settlement"];
			$tender_totals["direct_deposit"]["actual"] = $row["direct_deposit_settlement"];
			$tender_totals["pay_advantage"]["actual"] = $row["pay_advantage_settlement"];
			$tender_totals["zip_pay"]["actual"] = $row["zip_pay_settlement"];
			$sql = "SELECT 
						TTI.`tender_type_id`, REPLACE(LOWER(TRIM(TTI.`tender_name`)), ' ', '_') AS `tender_name`, COALESCE(SUM(`amount`),0) AS amount, EOD.`report_id`, EOD.`is_verified` 
					FROM 
						`ki_tender_type_info` TTI
					LEFT JOIN 
						(
							SELECT `tender_type_id`, `tender_name`, SUM(`deposit`) AS amount FROM `ki_estimate_deposits_info` WHERE `tender_type_id` != 0 AND `tender_type_id` IS NOT NULL AND `location_type`='" . safe_str($inputdata['location_type']) . "' AND `location_id`='" . safe_str($inputdata['location_id']) . "' AND `created_on` BETWEEN '" . safe_str($from_date) . "' AND '" . safe_str($to_date) . "' AND `delete_flag`=0 GROUP BY `tender_type_id`
						UNION
							SELECT `tender_type_id`, `tender_name`, SUM(`deposit`) AS amount FROM `ki_job_deposits_info` WHERE `tender_type_id` != 0 AND `tender_type_id` IS NOT NULL AND `location_type`='" . safe_str($inputdata['location_type']) . "' AND `location_id`='" . safe_str($inputdata['location_id']) . "' AND `created_on` BETWEEN '" . safe_str($from_date) . "' AND '" . safe_str($to_date) . "' AND `delete_flag`=0 GROUP BY `tender_type_id`
						UNION
							SELECT `tender_type_id`, `tender_name`, SUM(`amount`-`change`) AS amount FROM `ki_invoice_payment_info` WHERE `tender_type_id` != 0 AND `tender_type_id` IS NOT NULL AND (`off_board_id` IS NULL OR `off_board_id`=0) AND `location_type`='" . safe_str($inputdata['location_type']) . "' AND `location_id`='" . safe_str($inputdata['location_id']) . "' AND `created_on` BETWEEN '" . safe_str($from_date) . "' AND '" . safe_str($to_date) . "' AND `delete_flag`=0 GROUP BY `tender_type_id`
						UNION
							SELECT `tender_type_id`, `tender_name`, SUM(`amount`-`change`) AS amount FROM `ki_invoice_payment_info` WHERE `tender_type_id` != 0 AND `tender_type_id` IS NOT NULL AND (`off_board_id` IS NOT NULL AND `off_board_id`!=0) AND `location_type`='" . safe_str($inputdata['location_type']) . "' AND `location_id`='" . safe_str($inputdata['location_id']) . "' AND `created_on` BETWEEN '" . safe_str($from_date) . "' AND '" . safe_str($to_date) . "' AND `delete_flag`=0 GROUP BY `tender_type_id`
						) AA ON
						TTI.`tender_type_id` = AA.`tender_type_id`
					LEFT JOIN `ki_end_of_day_balance_report_info` EOD ON 
						TTI.`tender_type_id` = EOD.`tender_type_id` AND `date`=DATE('" . safe_str($inputdata['end_datetime']) . "') AND `location_type`='" . safe_str($inputdata['location_type']) . "' AND `location_id`='" . safe_str($inputdata['location_id']) . "' AND EOD.`delete_flag`=0
					WHERE 
						TTI.`is_enabled`=1 AND TTI.`delete_flag`=0
					GROUP BY 
						`tender_type_id` 
					ORDER BY 
						`tender_name`";
			$result = $con->query($sql);
			if ($result) {
				while ($row = $result->fetch_assoc()) {
					if (array_key_exists($row['tender_name'], $tender_totals)) {
						$tender_totals[$row['tender_name']]['report_id'] = $row['report_id'];
						$tender_totals[$row['tender_name']]['is_verified'] = $row['is_verified'];
						$tender_totals[$row['tender_name']]['id'] = $row['tender_type_id'];
						$tender_totals[$row['tender_name']]['expected'] = $row['amount'];
					} else {
						$tender_totals[$row['tender_name']]['report_id'] = $row['report_id'];
						$tender_totals[$row['tender_name']]['is_verified'] = $row['is_verified'];
						$tender_totals[$row['tender_name']]['id'] = $row['tender_type_id'];
						$tender_totals[$row['tender_name']]['expected'] = $row['amount'];
						$tender_totals[$row['tender_name']]['actual'] = 0;
					}
				}
			} else {
				$data['errors'][] = $con->error();
			}
		} else {
			$data['errors'][] = $con->error();
		}
		if (empty($data['errors'])) {
			$data['status'] = 1;
			$tender_totals['cash']['expected'] = $tender_totals['cash']['expected'] + $open_amount;
			// $tender_totals['direct_deposit']['actual'] = $tender_totals['direct_deposit']['expected'];
			$data['details']['tender_totals'] = $tender_totals;
			$data['details']['open_amount'] = $open_amount;
			$data['details']['from_date'] = $from_date;
			$data['details']['to_date'] = $to_date;
		}
		return $data;
	}
	function get_location_til_closed_dates($inputdata)
	{
		/* 
		input params - none
		function is used to get dates on which til was closed for a specific location
				"status" => 0,
				"details" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"details" => array(),
			"errors" => array()
		);
		// $sql = "SELECT DISTINCT DATE_FORMAT(DATE(`created_on`), '%d-%m-%Y') AS date FROM `ki_tils_info` WHERE `location_type`='".safe_str($inputdata['location_type'])."' AND `location_id`='".safe_str($inputdata['location_id'])."' AND `is_closed`=1 AND `delete_flag` = 0 ORDER BY `created_on` DESC";
		$sql = "SELECT `created_on` AS date FROM `ki_tils_info` WHERE `location_type`='" . safe_str($inputdata['location_type']) . "' AND `location_id`='" . safe_str($inputdata['location_id']) . "' AND `is_closed`=1 AND `delete_flag` = 0 ORDER BY `created_on` DESC";
		$result = $con->query($sql);
		if ($result) {
			$data['status'] = 1;
			while ($row = $result->fetch_assoc()) {
				$date = date("d-m-Y", strtotime($row['date']));
				if (!in_array($date, $data['details'])) {
					$data['details'][] = $date;
				}
			}
		} else {
			$data['errors'][] = $con->error;
		}
		return $data;
	}
	function get_product_details_based_on_location($inputdata)
	{
		/* 
		input params - 
			location_type
			location_id
			product_id
			array of keys and values.
		function is used to get details of particular product from all the tables of products based on its location and passed keys and values.
		output - 
			$data = array(
				"status" => 0,
				"details"=>array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"list" => array(),
			"errors" => array()
		);
		// WHEN (PCI.`core_range`= 0 OR PCI.`core_range` IS NULL) AND (PQI.`stock_on_hand`<=0 OR PQI.`stock_on_hand` IS NULL) THEN 1
		// WHEN PCI.`core_range` = 1 THEN 1
		// only Special order and soh=0 only then special order 
		$default_distribution_id = $this->get_default_distribution_details(array())['distribution_branch_id'];
		$sql = "SELECT 
					PI.*, PCI.*, PLI.*, PQI.*, PPI.*, 
					PI.`product_id`,
					CASE 
						WHEN PCI.`core_range` = 1 AND (PQI.`stock_on_hand`<=0 OR PQI.`stock_on_hand` IS NULL) THEN 1
						ELSE 0 
					END AS `is_special_order`, 
					CASE PI.`status` 
						WHEN 1 THEN 'Active' 
						WHEN 2 THEN 'Inactive' 
						WHEN 3 THEN 'Archived' 
					END AS pdt_status, 
					0 AS economic_order_quantity, 
					COALESCE(PI.`MOQ_units_delivered`,0) AS MOQ_units_delivered, 
					COALESCE(PQI1.`stock_on_hand`,0) AS stock_in_distribution, 
					COALESCE(PPI.`distribution_margin`,0) AS distribution_margin, 
					COALESCE(PPI.`freight_charge`,0) AS freight_charge, 
					COALESCE(MTI1.`tax_value`,MTI3.`tax_value`) AS retail_tax, 
					COALESCE(MTI3.`tax_value`,0.00) AS distribution_tax, 
					COALESCE(MTI3.`tax_value`,0.00) AS default_tax 
				FROM 
					`ki_products_info` PI 
				LEFT JOIN `ki_product_consumption_info` PCI ON 
					PI.`product_id`=PCI.`product_id` AND PCI.`location_type`='" . safe_str($inputdata['location_type']) . "' AND PCI.`location_id`='" . safe_str($inputdata['location_id']) . "' AND PCI.`delete_flag`=0 
				LEFT JOIN `ki_product_logistics_info` PLI ON 
					PI.`product_id`=PLI.`product_id` AND PLI.`location_type`='" . safe_str($inputdata['location_type']) . "' AND PLI.`location_id`='" . safe_str($inputdata['location_id']) . "' AND PLI.`delete_flag`=0 
				LEFT JOIN `ki_product_quantites_info` PQI ON 
					PI.`product_id`=PQI.`product_id` AND PQI.`location_type`='" . safe_str($inputdata['location_type']) . "' AND PQI.`location_id`='" . safe_str($inputdata['location_id']) . "' AND PQI.`delete_flag`=0 
				LEFT JOIN `ki_product_quantites_info` PQI1 ON 
					PI.`product_id`=PQI1.`product_id` AND PQI1.`location_type`='2' AND PQI1.`location_id`='" . safe_str($default_distribution_id) . "' AND PQI1.`delete_flag`=0 
				LEFT JOIN `ki_product_prices_info` PPI ON 
					PI.`product_id`=PPI.`product_id` AND PPI.`delete_flag`=0
				LEFT JOIN `ki_meta_taxes_info` MTI1 ON 
					PPI.`retail_tax`=MTI1.`tax_id` AND MTI1.`is_enabled`=1 AND MTI1.`delete_flag`=0 
				LEFT JOIN `ki_meta_taxes_info` MTI2 ON 
					PPI.`distribution_tax`=MTI2.`tax_id` AND MTI2.`is_enabled`=1 AND MTI2.`delete_flag`=0 
				LEFT JOIN `ki_meta_taxes_info` MTI3 ON 
					MTI3.`is_default`=1 AND MTI3.`delete_flag`=0 
				WHERE 
					PI.`product_id`='" . safe_str($inputdata['product_id']) . "' AND PI.`delete_flag`=0";
		$result = $con->query($sql);
		if ($result) {
			$data['status'] = 1;
			$data['details'] = $result->fetch_assoc();
		} else {
			$data['errors'][] = $con->error;
		}
		return $data;
	}
	function get_location_wise_product_tab_list_for_logistics($inputdata)
	{
		global $con;
		$pagg_qry = "SELECT `location_type`, `location_id`, IJ.`company_name`, `is_kingit_distribution`, COALESCE(CD.`store_name`, EF.`distribution_name`, GH.`production_name`) AS location_name, AB.* FROM `ki_product_logistics_info` AB  LEFT JOIN `ki_stores_info` CD ON AB.`location_type` = 1 AND AB.`location_id` = CD.`store_id` AND CD.`delete_flag`=0 LEFT JOIN `ki_distribution_branches_info` EF ON AB.`location_type` = 2 AND AB.`location_id` = EF.`distribution_branch_id` AND EF.`delete_flag`=0 LEFT JOIN `ki_production_info` GH ON AB.`location_type` = 3 AND AB.`location_id` = GH.`production_id` AND GH.`delete_flag`=0 LEFT JOIN `ki_suppliers_info` IJ ON AB.`supplier_id` = IJ.`supplier_id` WHERE `product_id` = '" . safe_str($inputdata['product_id']) . "' AND AB.`delete_flag`=0";
		$pagg_result = $con->query($pagg_qry);
		$pagg_count = $pagg_result->num_rows;
		$pagging_list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}
		return $pagging_list;
	}
	function get_categories_dropdown_list($inputdata)
	{
		/* 
		input params - 
			"function" => "get_categories_dropdown_list",
			"enabled"=>0,
			"category_id"=>$category_id
		function is used to get list of all enabled categories to show in dropdown.
		output params - 
		    array(
				"status" => 0,
				"list" => array(),
				"errors" => array(),
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"list" => array(),
			"errors" => array()
		);
		$where = " and `is_enabled`=1";
		$go = 0;
		if (isset($inputdata['enabled'])) {
			// enabled 0 set if need to get disabled categories also
			if ($inputdata['enabled'] == 0) {
				$go = 1;
				$where = "";
			}
		}
		if ($go == 0) {
			if (!empty($inputdata['category_id'])) {
				$where = " and (`is_enabled`=1 or (is_enabled=0 and category_id='" . safe_str($inputdata['category_id']) . "'))";
			}
		}
		$sql = "SELECT 
                    `category_id`, `category_name`, getpath(`category_id`) AS path, get_path_dashes(`category_id`) AS dash_hierarchy 
                FROM 
                    `ki_categories_info` 
                WHERE 
                    `delete_flag`=0" . $where . "
                ORDER BY path";
		$result = $con->query($sql);
		if ($result) {
			$data['status'] = 1;
			while ($row = $result->fetch_assoc()) {
				$data['list'][] = $row;
			}
		} else {
			$data['errors'][] = $con->error;
		}
		return $data;
	}
	function get_products_upload_queue_pagging_list($inputdata)
	{
		/*
		input - 
			"function"=>"get_products_upload_queue_pagging_list",
		output - 
			$data = array(
				"total_records" => 0,
				"total_pages" => 0,
				"pagging_list"=>array() return pagging list of products upload queue
			);
		*/
		global $con;
		$data = array(
			"total_records" => 0,
			"total_pages" => 0,
			"pagging_list" => array()
		);
		$page_no = $inputdata['page_no'];
		$row_size = $inputdata['row_size'];
		$sort_on = $inputdata['sort_on'];
		$sort_type = $inputdata['sort_type'];
		$pcount_qry = "select count(*) as total_count from ki_product_upload_queue_info where delete_flag=0";
		$pcount_result = $con->query($pcount_qry);
		$pcount_row = $pcount_result->fetch_assoc();
		$total_records = $pcount_row["total_count"];

		@$total_pages = ceil($total_records / $row_size);
		if ($total_pages == 0) {
			$total_pages = 1;
		}
		if ($page_no > $total_pages) {
			$page_no = $total_pages;
		}
		$limit_from = ($row_size * $page_no) - $row_size;

		$pagg_qry = "select *, CASE `status` WHEN 0 THEN 'Pending' WHEN 1 THEN 'Processing' ELSE 'Complete' END AS status_name from ki_product_upload_queue_info where delete_flag=0 order by " . safe_str($sort_on) . " " . safe_str($sort_type) . " LIMIT " . $limit_from . ", " . $row_size;
		$pagg_result = $con->query($pagg_qry);
		echo $con->error;
		$pagg_count = $pagg_result->num_rows;
		$pagging_list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}
		$data["total_records"] = $total_records;
		$data["total_pages"] = $total_pages;
		$data["pagging_list"] = $pagging_list;

		return $data;
	}

	/*
		input - 
			"function"=>"get_product_stock_on_hand_by_pdt",
			"product_id"=>$product_id,
			"location_type" => $values['location_type'],
			"location_id" => $values['location_id'],
		output - 
			$data = array(
				"status" => 0,
				"errors" => array(),
				"details"=>array()
			);
	*/
	function get_product_stock_on_hand_by_pdt($inputdata)
	{
		global $con;
		$data = array(
			"status" => 0,
			"errors" => array(),
			"details" => array()
		);
		$sql = "select * from ki_product_quantites_info where delete_flag=0 and product_id='" . safe_str($inputdata['product_id']) . "' and  location_type='" . safe_str($inputdata['location_type']) . "' and location_id='" . safe_str($inputdata['location_id']) . "'";
		$result = $con->query($sql);
		if ($result->num_rows) {
			$data['details'] = $result->fetch_assoc();
		}
		return $data;
	}

	/* table ->            ki_modal_upload_queue_child_info table
	   input parameter->   modal_queue_id
	   output parameter -> return all fields of table
	*/
	function get_list_modal_upload_queue($inputdata)
	{
		global $con;
		$row = array();
		$qry = 'SELECT
                	GROUP_CONCAT(`inserted_row_numbers`,",") AS `inserted_row_numbers`,
                	GROUP_CONCAT(`updated_row_numbers`,",") AS `updated_row_numbers`,
                	GROUP_CONCAT(`skipped_empty_req`,",") AS `skipped_empty_req`,
                	GROUP_CONCAT(`skipped_ticket_type`,",") AS `skipped_ticket_type`,
                	GROUP_CONCAT(`skipped_ticket_type_inactive`,",") AS `skipped_ticket_type_inactive`,
                	GROUP_CONCAT(`skipped_brand`,",") AS `skipped_brand`,
                	GROUP_CONCAT(`skipped_brand_inactive`,",") AS `skipped_brand_inactive`,
                	GROUP_CONCAT(`skipped_ticket_type_brand`,",") AS `skipped_ticket_type_brand`,
                	GROUP_CONCAT(`skipped_work_to_complete`,",") AS `skipped_work_to_complete`,
                	GROUP_CONCAT(`skipped_recommended_soln`,",") AS `skipped_recommended_soln`,
                	GROUP_CONCAT(`skipped_budget_solution`,",") AS `skipped_budget_solution`,
                	GROUP_CONCAT(`skipped_canned_response`,",") AS `skipped_canned_response`,
                	GROUP_CONCAT(`skipped_value_add_products`,",") AS `skipped_value_add_products`,
                	GROUP_CONCAT(`skipped_deleted`,",") AS `skipped_deleted`,
                	GROUP_CONCAT(`deleted_row_numbers`,",") AS `deleted_row_numbers`
                FROM 
                	`ki_modal_upload_queue_child_info` 
                WHERE 
                	`modal_queue_id`=' . $inputdata['modal_queue_id'];
		$result = $con->query($qry);
		if ($result->num_rows > 0) {
			$row = $result->fetch_assoc();
		} else {
			echo $con->error;
		}
		return $row;
	}
	/* table ->            ki_modal_upload_queue_info table
	   input parameter->   modal_queue_id
	   output parameter -> return path of the file uploaded
	*/
	function get_modal_upload_queue_file_path($inputdata)
	{
		global $con;
		$pcount_qry = "select file_path from ki_modal_upload_queue_info where delete_flag=0 and modal_queue_id=" . $inputdata['modal_queue_id'];
		$pcount_result = $con->query($pcount_qry);
		if ($con->query($pcount_qry)) {
			$pcount_row = $pcount_result->fetch_assoc();
		} else {
			echo $con->error;
		}
		return $pcount_row;
	}

	function get_model_queue_to_be_processed($inputdata)
	{
		/* 
		input params - 
			"estimate_id" => $estimate_id
		function deletes follow up status info associated with estimate_id.
			$data = array(
				"status" => 0,
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"details" => array(),
			"errors" => array()
		);
		$qry = "SELECT * FROM `ki_modal_upload_queue_info` WHERE (`status`=1 OR `status`=0) AND `rows_assigned`<`total_rows` ORDER BY `status` DESC,`created_on` LIMIT 1";
		$result = $con->query($qry);
		if ($result) {
			$data["status"] = 1;
			$data["details"] = $result->fetch_assoc();
		} else {
			$data["errors"][] = $con->error;
		}
		return $data;
	}
	function delete_est_follow_up_status_info($inputdata)
	{
		/* 
		input params - 
			"estimate_id" => $estimate_id
		function deletes follow up status info associated with estimate_id.
			$data = array(
				"status" => 0,
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"errors" => array()
		);
		$d_qry = "UPDATE `ki_estimate_follow_up_status_info` A INNER JOIN `ki_estimate_follow_ups_info` B ON A.`follow_up_id`=B.`follow_up_id` AND B.`estimate_id`='" . safe_str($inputdata['estimate_id']) . "' AND A.`delete_flag`=0 SET A.`delete_flag`=1 WHERE A.`delete_flag`=0";
		$d_result = $con->query($d_qry);
		if ($d_result) {
			$data["status"] = 1;
		} else {
			$data["errors"][] = $con->error;
		}
		return $data;
	}
	function get_default_distribution_supplier_id($inputdata)
	{
		/* 
		input params - none
		function is used to get details of default distribution supplier 
		output - 
			$data = array();
		*/
		global $con;
		$data = array();
		$sql = "SELECT * FROM `ki_suppliers_info` WHERE `company_name`='" . DISTRIBUTION_SUPPLIER . "' and delete_flag=0 and is_enabled=1";
		$result = $con->query($sql);
		if ($result) {
			$data = $result->fetch_assoc();
		} else {
			echo $con->error();
		}
		return $data;
	}
	function get_tender_type_list($inputdata)
	{
		/* 
		input params - none
		function is used to get_tender_type_list
				"status" => 0,
				"list" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"list" => array(),
			"errors" => array()
		);
		$enabled = ' `is_enabled`=1 AND ';
		if (!empty($inputdata['all'])) {
			$enabled = '';
		}
		$sql = "SELECT 
					* 
				FROM 
					`ki_tender_type_info`
				WHERE 
					" . $enabled . "`delete_flag` = 0
				ORDER BY 
					`tender_name` ASC";
		$result = $con->query($sql);
		if ($result) {
			$data['status'] = 1;
			while ($row = $result->fetch_assoc()) {
				$data['list'][] = $row;
			}
		} else {
			$data['errors'][] = $con->error;
		}
		return $data;
	}
	function get_end_of_day_balance_report($inputdata)
	{
		/* 
		input params - 
			"page_no" => $request["PageNumber"],
			"row_size" => $request["RowSize"],
			"sort_on" => $request["SortOn"],
			"sort_type" => $request["SortType"],
			"location_type" => $location_type,
			"location_id" => $location_id,
			"start_datetime" => $start_date_to_check,
			"end_datetime" => date('Y-m-d H:i:s'),
			"search" => $search,
			"TenderType" => $TenderType
		function is used to get list of payments for a location to show in end_of_day_balance_report in invoices and reports/financial reports
			$data = array(
				"total_records" => 0,
				"total_pages" => 0,
				"pagging_list" => array()
			);
		*/
		global $con;
		$data = array(
			"total_records" => 0,
			"total_pages" => 0,
			"pagging_list" => array()
		);
		$page_no = safe_str($inputdata['page_no']);
		$row_size = safe_str($inputdata['row_size']);
		$sort_on = safe_str($inputdata['sort_on']);
		$sort_type = safe_str($inputdata['sort_type']);
		$TenderType_query = '';
		if (!empty($inputdata['TenderType'])) {
			$TenderType_query = " AND `tender_type_id`='" . safe_str($inputdata['TenderType']) . "' ";
		}
		$location1 = '';
		$location2 = '';
		$location3 = '';
		if (!isset($inputdata['is_admin_tab']) || $inputdata['is_admin_tab'] == 0) {
			$location1 = " AND EDI.`location_type`='" . $inputdata['location_type'] . "' AND EDI.`location_id`=" . $inputdata['location_id'];
			$location2 = " AND JDI.`location_type`='" . $inputdata['location_type'] . "' AND JDI.`location_id`=" . $inputdata['location_id'];
			$location3 = " AND IPI.`location_type`='" . $inputdata['location_type'] . "' AND IPI.`location_id`=" . $inputdata['location_id'];
		}

		$search_query = '';
		if (!empty($inputdata['search'])) {
			$search_query .= " AND (CONCAT('#',`number`) LIKE '%" . safe_str($inputdata['search']) . "%' OR `type` LIKE '%" . safe_str($inputdata['search']) . "%' OR `tender_name` LIKE '%" . safe_str($inputdata['search']) . "%' OR user_name LIKE '%" . safe_str($inputdata['search']) . "%') ";
		}
		$pcount_qry = "SELECT 
					count(*) AS total_count 
				FROM 
					( 
						SELECT 
							'Estimate' AS type, EI.`estimate_id` AS type_id, CONCAT(COALESCE(`first_name`, ''),' ',COALESCE(`last_name`, '')) AS user_name, `estimate_number` AS number, `tender_type_id`, `tender_name`, EDI.`deposit` AS amount, 0 AS `change`,EDI.`created_on`, `location_type` ,`location_id`
						FROM 
							`ki_estimate_deposits_info` EDI 
						INNER JOIN `ki_users_info` UI ON 
							EDI.`user_id` = UI.`user_id` AND UI.`delete_flag` = 0 
						INNER JOIN `ki_estimates_info` EI ON 
							EDI.`estimate_id`=EI.`estimate_id` AND EI.`delete_flag`=0 
						WHERE 
							`tender_type_id` != 0 AND `tender_type_id` IS NOT NULL AND EDI.`location_type`='" . $inputdata['location_type'] . "' AND EDI.`location_id`='" . $inputdata['location_id'] . "' AND EDI.`delete_flag` = 0 
						UNION ALL 
						SELECT 
							'Job' AS type, JI.`job_id` AS type_id, CONCAT(COALESCE(`first_name`, ''),' ',COALESCE(`last_name`, '')) AS user_name, `job_number` AS number, `tender_type_id`, `tender_name`, `deposit` AS amount, 0 AS `change`,JDI.`created_on`, `location_type` ,`location_id` 
						FROM 
							`ki_job_deposits_info` JDI 
						INNER JOIN `ki_users_info` UI ON 
							JDI.`user_id` = UI.`user_id` AND UI.`delete_flag` = 0 
						INNER JOIN `ki_jobs_info` JI ON 
							JDI.`job_id`=JI.`job_id` AND JI.`delete_flag`=0 
						WHERE 
							`tender_type_id` != 0 AND `tender_type_id` IS NOT NULL AND JDI.`location_type`='" . $inputdata['location_type'] . "' AND JDI.`location_id`='" . $inputdata['location_id'] . "' AND JDI.`delete_flag` = 0 
						UNION ALL 
						SELECT 
							'Invoice' AS type, II.`invoice_id` AS type_id, CONCAT(COALESCE(`first_name`, ''),' ',COALESCE(`last_name`, '')) AS user_name, `invoice_number` AS number, `tender_type_id`, `tender_name`, `amount`, `change`, IPI.`payment_datetime` AS created_on, `location_type` ,`location_id` 
						FROM 
							`ki_invoice_payment_info` IPI 
						INNER JOIN `ki_users_info` UI ON 
							IPI.`user_id` = UI.`user_id` AND UI.`delete_flag` = 0 
						INNER JOIN `ki_invoices_info` II ON 
							IPI.`invoice_id`=II.`invoice_id` AND II.`delete_flag`=0 
						WHERE 
							II.`is_draft`=0 AND `tender_type_id` != 0 AND `tender_type_id` IS NOT NULL AND (IPI.`off_board_id` IS NULL OR IPI.`off_board_id`=0) AND IPI.`location_type`='" . $inputdata['location_type'] . "' AND IPI.`location_id`='" . $inputdata['location_id'] . "' AND IPI.`delete_flag` = 0 
						UNION ALL 
						SELECT 
							'Off-Board' AS type, JOB.`off_board_id` AS type_id, CONCAT(COALESCE(`first_name`, ''),' ',COALESCE(`last_name`, '')) AS user_name, `off_board_number` AS number, `tender_type_id`, `tender_name`, `amount`, `change`, IPI.`payment_datetime` AS created_on, `location_type` ,`location_id` 
						FROM 
							`ki_invoice_payment_info` IPI 
						INNER JOIN `ki_users_info` UI ON 
							IPI.`user_id` = UI.`user_id` AND UI.`delete_flag` = 0 
						INNER JOIN `ki_job_off_boarding_info` JOB ON 
							IPI.`off_board_id`=JOB.`off_board_id` AND JOB.`delete_flag`=0 
						WHERE 
							`tender_type_id` != 0 AND `tender_type_id` IS NOT NULL AND (IPI.`off_board_id` IS NOT NULL AND IPI.`off_board_id`!=0) AND IPI.`location_type`='" . $inputdata['location_type'] . "' AND IPI.`location_id`='" . $inputdata['location_id'] . "' AND IPI.`delete_flag` = 0 
					) AA 
				LEFT JOIN `ki_stores_info` SI ON 
					SI.`store_id`=AA.`location_id` AND AA.location_type=1 AND SI.`delete_flag`=0
				LEFT JOIN `ki_distribution_branches_info` DBI ON 
					DBI.`distribution_branch_id`=AA.`location_id` AND AA.location_type=2 AND DBI.`delete_flag`=0
				LEFT JOIN `ki_production_info` PI ON 
					PI.`production_id`=AA.`location_id` AND AA.location_type=3 AND PI.`delete_flag`=0
				WHERE 
					AA.`created_on` BETWEEN '" . $inputdata['start_datetime'] . "' AND '" . $inputdata['end_datetime'] . "' " . $search_query . $TenderType_query;
		$pcount_result = $con->query($pcount_qry);
		echo $con->error;
		$pcount_row = $pcount_result->fetch_assoc();
		$total_records = $pcount_row["total_count"];
		@$total_pages = ceil($total_records / $row_size);
		if ($total_pages == 0) {
			$total_pages = 1;
		}
		if ($page_no > $total_pages) {
			$page_no = $total_pages;
		}
		$limit_from = ($row_size * $page_no) - $row_size;
		$sql = "SELECT 
					AA.*, COALESCE(SI.`store_name`, DBI.`distribution_name`, PI.`production_name`) AS location_name 
				FROM 
					( 
						SELECT 
							'Estimate' AS type, EI.`estimate_id` AS type_id, CONCAT(COALESCE(`first_name`, ''),' ',COALESCE(`last_name`, '')) AS user_name, estimate_number AS number, `tender_type_id`, `tender_name`, EDI.`deposit` AS amount, '0.00' AS `change`,EDI.`created_on`, `location_type` ,`location_id`
						FROM 
							`ki_estimate_deposits_info` EDI 
						INNER JOIN `ki_users_info` UI ON 
							EDI.`user_id` = UI.`user_id` AND UI.`delete_flag` = 0 
						INNER JOIN `ki_estimates_info` EI ON 
							EDI.`estimate_id`=EI.`estimate_id` AND EI.`delete_flag`=0 
						WHERE 
							`tender_type_id` != 0 AND `tender_type_id` IS NOT NULL AND EDI.`location_type`='" . $inputdata['location_type'] . "' AND EDI.`location_id`='" . $inputdata['location_id'] . "' AND EDI.`delete_flag` = 0 
						UNION ALL 
						SELECT 
							'Job' AS type, JI.`job_id` AS type_id, CONCAT(COALESCE(`first_name`, ''),' ',COALESCE(`last_name`, '')) AS user_name, job_number AS number, `tender_type_id`, `tender_name`, `deposit` AS amount, '0.00' AS `change`,JDI.`created_on`, `location_type` ,`location_id` 
						FROM 
							`ki_job_deposits_info` JDI 
						INNER JOIN `ki_users_info` UI ON 
							JDI.`user_id` = UI.`user_id` AND UI.`delete_flag` = 0 
						INNER JOIN `ki_jobs_info` JI ON 
							JDI.`job_id`=JI.`job_id` AND JI.`delete_flag`=0 
						WHERE 
							`tender_type_id` != 0 AND `tender_type_id` IS NOT NULL AND JDI.`location_type`='" . $inputdata['location_type'] . "' AND JDI.`location_id`='" . $inputdata['location_id'] . "' AND JDI.`delete_flag` = 0 
						UNION ALL 
						SELECT 
							'Invoice' AS type, II.`invoice_id` AS type_id, CONCAT(COALESCE(`first_name`, ''),' ',COALESCE(`last_name`, '')) AS user_name, `invoice_number` AS number, `tender_type_id`, `tender_name`, `amount`, `change`, IPI.`payment_datetime` AS created_on, `location_type` ,`location_id` 
						FROM 
							`ki_invoice_payment_info` IPI 
						INNER JOIN `ki_users_info` UI ON 
							IPI.`user_id` = UI.`user_id` AND UI.`delete_flag` = 0 
						INNER JOIN `ki_invoices_info` II ON 
							IPI.`invoice_id`=II.`invoice_id` AND II.`delete_flag`=0 
						WHERE 
							II.`is_draft`=0 AND `tender_type_id` != 0 AND `tender_type_id` IS NOT NULL AND (IPI.`off_board_id` IS NULL OR IPI.`off_board_id`=0) AND IPI.`location_type`='" . $inputdata['location_type'] . "' AND IPI.`location_id`='" . $inputdata['location_id'] . "' AND IPI.`delete_flag` = 0 
						UNION ALL 
						SELECT 
							'Off-Board' AS type, JOB.`off_board_id` AS type_id, CONCAT(COALESCE(`first_name`, ''),' ',COALESCE(`last_name`, '')) AS user_name, `off_board_number` AS number, `tender_type_id`, `tender_name`, `amount`, `change`, IPI.`payment_datetime` AS created_on, `location_type` ,`location_id` 
						FROM 
							`ki_invoice_payment_info` IPI 
						INNER JOIN `ki_users_info` UI ON 
							IPI.`user_id` = UI.`user_id` AND UI.`delete_flag` = 0 
						INNER JOIN `ki_job_off_boarding_info` JOB ON 
							IPI.`off_board_id`=JOB.`off_board_id` AND JOB.`delete_flag`=0 
						WHERE 
							`tender_type_id` != 0 AND `tender_type_id` IS NOT NULL AND (IPI.`off_board_id` IS NOT NULL AND IPI.`off_board_id`!=0) AND IPI.`location_type`='" . $inputdata['location_type'] . "' AND IPI.`location_id`='" . $inputdata['location_id'] . "' AND IPI.`delete_flag` = 0 
					) AA 
				LEFT JOIN `ki_stores_info` SI ON 
					SI.`store_id`=AA.`location_id` AND AA.location_type=1 AND SI.`delete_flag`=0
				LEFT JOIN `ki_distribution_branches_info` DBI ON 
					DBI.`distribution_branch_id`=AA.`location_id` AND AA.location_type=2 AND DBI.`delete_flag`=0
				LEFT JOIN `ki_production_info` PI ON 
					PI.`production_id`=AA.`location_id` AND AA.location_type=3 AND PI.`delete_flag`=0
				WHERE 
					AA.`created_on` BETWEEN '" . $inputdata['start_datetime'] . "' AND '" . $inputdata['end_datetime'] . "' " . $search_query . $TenderType_query . " 
				ORDER BY 
					`" . safe_str($sort_on) . "` " . safe_str($sort_type) . " LIMIT " . $limit_from . ", " . $row_size;
		// echo "<pre>".$sql."</pre>";
		$result = $con->query($sql);
		if ($result) {
			$data['status'] = 1;
			while ($row = $result->fetch_assoc()) {
				$data['pagging_list'][] = $row;
			}
			$data["total_records"] = $total_records;
			$data["total_pages"] = $total_pages;
		} else {
			$data['errors'][] = $con->error;
		}
		return $data;
	}
	function check_trade_day_for_location($inputdata)
	{
		/* 
		input params - 
			"date" => date("Y-m-d"),
			"location_type" => $location_type,
			"location_id" => $location_id
		function check whether passed date is trade day or not and returns same date if it is in details array else returns next trade day.
			$data = array(
				"status" => 0,
				"details" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"details" => array(),
			"errors" => array()
		);
		$days = array();
		$qry = "SELECT 
					DISTINCT `day` 
				FROM 
					`ki_non_trade_recurring_days_info` 
				WHERE 
					`location_type`='" . $inputdata['location_type'] . "' AND `location_id`='" . $inputdata['location_id'] . "' AND `is_enabled`=1 AND `delete_flag`=0 
				ORDER BY 
					`day` ASC";
		$res = $con->query($qry);
		if ($res) {
			if ($res->num_rows != 7) {
				$sql = "SELECT 
							* 
						FROM 
							`ki_non_trade_days_info` 
						WHERE 
							`location_id`='" . $inputdata['location_id'] . "' AND `location_type`='" . $inputdata['location_type'] . "' AND `delete_flag`=0 AND (
								`day`='" . date('Y-m-d', strtotime($inputdata['date'])) . "' OR (
									`is_recurring`=1 AND CONCAT(EXTRACT(MONTH FROM `day`),'-',EXTRACT(DAY FROM `day`))='" . date('n-j', strtotime($inputdata['date'])) . "'
								) OR '" . date('N', strtotime($inputdata['date'])) . "' IN (
									SELECT 
										DISTINCT `day` 
									FROM 
										`ki_non_trade_recurring_days_info` 
									WHERE 
										`location_type`='" . $inputdata['location_type'] . "' AND `location_id`='" . $inputdata['location_id'] . "' AND `is_enabled`=1 AND `delete_flag`=0
								)
							)";
				$result = $con->query($sql);
				if ($result) {
					$row = $result->fetch_assoc();
					if (empty($row)) {
						$data['status'] = 1;
						$data['details']['date'] = $inputdata['date'];
					} else {
						$data = $this->check_trade_day_for_location(array(
							"date" => date('Y-m-d', strtotime($inputdata['date'] . " + 1 days")),
							"location_type" => $inputdata['location_type'],
							"location_id" => $inputdata['location_id']
						));
					}
				} else {
					$data['errors'][] = $con->error;
				}
			} else {
				$data['status'] = 1;
				$data['details']['date'] = '';
			}
		} else {
			$data['errors'][] = $con->error;
		}
		return $data;
	}
	function get_balance_due_for_statement_of_account($inputdata)
	{
		/* 
		input params - due_date, invoice_id
		function is used to get details of balance due <30 Days Past Due, <60 Days Past Due, <90 Days Past Due and 90+ Days Past Due
		output - 
			$data = array(
				"status" => 0,
				"details" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"details" => array(),
			"errors" => array()
		);
		$sql = "SELECT 
					AA.invoice_id, 
					CONCAT('$',CAST(COALESCE(total-amt_paid_till_past_30,0.00) AS DECIMAL(10, 2))) AS balance_due_till_past_30, 
					CASE 
						WHEN DATEDIFF('" . date("Y-m-d") . "', '" . $inputdata['due_date'] . "')>30 THEN CONCAT('$',CAST(COALESCE(total-amt_paid_till_past_30-amt_paid_till_past_60,0.00) AS DECIMAL(10, 2)))
						ELSE '-'
					END AS balance_due_till_past_60, 
					CASE 
						WHEN DATEDIFF('" . date("Y-m-d") . "', '" . $inputdata['due_date'] . "')>60 THEN CONCAT('$',CAST(COALESCE(total-amt_paid_till_past_30-amt_paid_till_past_60-amt_paid_till_past_90,0.00) AS DECIMAL(10, 2)))
						ELSE '-'
					END AS balance_due_till_past_90, 
					CASE 
						WHEN DATEDIFF('" . date("Y-m-d") . "', '" . $inputdata['due_date'] . "')>90 THEN CONCAT('$',CAST(COALESCE(total-amt_paid_till_past_30-amt_paid_till_past_60-amt_paid_till_past_90-amt_paid_past_90,0.00) AS DECIMAL(10, 2)))
						ELSE '-'
					END AS balance_due_past_90
				FROM 
					(
						SELECT 
							'" . $inputdata['invoice_id'] . "' AS `invoice_id`, COALESCE(`total_including_GST`,0) AS total
						FROM 
							`ki_invoices_info`
						WHERE 
							`is_draft`=0 AND `invoice_id`='" . $inputdata['invoice_id'] . "' AND `delete_flag`=0 
					) AA 
				LEFT JOIN 
					(
						SELECT 
							'" . $inputdata['invoice_id'] . "' AS `invoice_id`, COALESCE(SUM(`amount`-`change`),0) AS amt_paid_till_past_30
						FROM 
							`ki_invoice_payment_info`
						WHERE 
							`undo_datetime` IS NULL AND `invoice_id`='" . $inputdata['invoice_id'] . "' AND `created_on` BETWEEN '" . $inputdata['due_date'] . "' AND '" . date('Y-m-d', strtotime($inputdata['due_date'] . ' + 30 days')) . "' AND `delete_flag`=0 
					) BB ON 
					AA.`invoice_id`=BB.`invoice_id` 
				LEFT JOIN 
					(
						SELECT 
							'" . $inputdata['invoice_id'] . "' AS `invoice_id`, COALESCE(SUM(`amount`-`change`),0) AS amt_paid_till_past_60
						FROM 
							`ki_invoice_payment_info`
						WHERE 
							`undo_datetime` IS NULL AND `invoice_id`='" . $inputdata['invoice_id'] . "' AND `created_on` BETWEEN '" . date('Y-m-d', strtotime($inputdata['due_date'] . ' + 31 days')) . "' AND '" . date('Y-m-d', strtotime($inputdata['due_date'] . ' + 60 days')) . "' AND `delete_flag`=0 
					) CC ON 
					AA.`invoice_id`=CC.`invoice_id` 
				LEFT JOIN 
					(
						SELECT 
							'" . $inputdata['invoice_id'] . "' AS `invoice_id`, COALESCE(SUM(`amount`-`change`),0) AS amt_paid_till_past_90
						FROM 
							`ki_invoice_payment_info`
						WHERE 
							`undo_datetime` IS NULL AND `invoice_id`='" . $inputdata['invoice_id'] . "' AND `created_on` BETWEEN '" . date('Y-m-d', strtotime($inputdata['due_date'] . ' + 61 days')) . "' AND '" . date('Y-m-d', strtotime($inputdata['due_date'] . ' + 90 days')) . "' AND `delete_flag`=0 
					) DD ON 
					AA.`invoice_id`=DD.`invoice_id` 
				LEFT JOIN 
					(
						SELECT 
							'" . $inputdata['invoice_id'] . "' AS `invoice_id`, COALESCE(SUM(`amount`-`change`),0) AS amt_paid_past_90
						FROM 
							`ki_invoice_payment_info`
						WHERE 
							`undo_datetime` IS NULL AND `invoice_id`='" . $inputdata['invoice_id'] . "' AND `created_on`>'" . date('Y-m-d', strtotime($inputdata['due_date'] . ' + 91 days')) . "' AND `delete_flag`=0 
					) EE ON AA.`invoice_id`=EE.`invoice_id`";
		$result = $con->query($sql);
		if ($result) {
			$data['status'] = 1;
			$data['details'] = $result->fetch_assoc();
		} else {
			$data['errors'][] = $con->error;
		}
		return $data;
	}
	function get_king_it_distribution_supplier($inputdata)
	{
		/* 
		input params - none
		function is used to get details of unlisted supplier
		output - 
			$data = array(
				"status" => 0,
				"details" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"details" => array(),
			"errors" => array()
		);
		$sql = "SELECT * FROM `ki_suppliers_info` WHERE `company_name` LIKE '%King IT Distribution Pty Ltd%' AND `delete_flag`=0";
		$result = $con->query($sql);
		if ($result) {
			$data['status'] = 1;
			$data['details'] = $result->fetch_assoc();
		} else {
			$data['errors'][] = $con->error;
		}
		return $data;
	}

	/*
		input - 
			"function"=>"get_product_stock_on_hand",
			"quantity_id"=>$quantity_id
		output - 
			$data = array(
				"status" => 0,
				"errors" => array(),
				"details"=>array()
			);
	*/
	function get_product_stock_on_hand($inputdata)
	{
		global $con;
		$data = array(
			"status" => 0,
			"errors" => array(),
			"details" => array()
		);
		$sql = "select * from ki_product_quantites_info where delete_flag=0 and quantity_id='" . safe_str($inputdata['quantity_id']) . "'";
		$result = $con->query($sql);
		if ($result->num_rows) {
			$data['details'] = $result->fetch_assoc();
		}
		return $data;
	}
	/*
		input - 
			"function"=>"UpdatePriceValuation",
			"location_type"=>$location_type,
			"product_id"=>$product_id,
			"location_id"=>$location_id,
			"quantity"=>$quantity , positive integer
			"action_type"=>$action_type  ,1-decrement, 2-increment 
		output - 
			$data = array(
				"status" => 0,
				"errors" => array()
			);
	*/
	function UpdatePriceValuation($inputdata)
	{
		// print_r($inputdata);die;
		global $con;
		$data = array(
			"status" => 0,
			"errors" => array()
		);
		// get latest cost price, sell price for this product
		$cost_price = 0;
		$sell_price = 0;
		if ($inputdata['location_type'] == 1) {
			$cost_price1 = 'distribution_price';
			$sell_price1 = 'retail_price';
		} elseif ($inputdata['location_type'] == 2 || $inputdata['location_type'] == 3) {
			$cost_price1 = 'cost_price';
			$sell_price1 = 'distribution_price';
		}
		$sql = "select " . $cost_price1 . " as cost_price," . $sell_price1 . " as sell_price from ki_product_prices_info where product_id='" . safe_str($inputdata['product_id']) . "' and delete_flag=0";
		$result = $con->query($sql);
		if ($result->num_rows) {
			$row = $result->fetch_assoc();
			$cost_price = $row['cost_price'];
			$sell_price = $row['sell_price'];
		}
		$create_new_entry = 0;
		if ($inputdata['action_type'] == 1) {
			// means decrement 
			// hence check for any entry using FIFO method 
			$quantity_check = 1;
			$deducted_quantity = 0;
			$deduct = $inputdata['quantity'];
			while ($quantity_check == 1) {
				// check if any entry already exists
				$sql = 'select * from ki_products_price_valuation_info where delete_flag=0 and product_id="' . safe_str($inputdata['product_id']) . '" and location_type="' . safe_str($inputdata['location_type']) . '" and location_id="' . safe_str($inputdata['location_id']) . '" and quantity>0 order by created_on asc limit 1';
				$result = $con->query($sql);
				if ($result->num_rows) {
					$row = $result->fetch_assoc();
					$quantity_to_deduct = $inputdata['quantity'] - $deducted_quantity;
					if (($row['quantity'] - $quantity_to_deduct) < 0) {
						$quantity_to_deduct = $row['quantity'];
					} else {
						$quantity_check = 0;  // means no more deduction 
					}
					$new_quantity = $row['quantity'] - $quantity_to_deduct;
					$sql = "update ki_products_price_valuation_info set quantity = '" . $new_quantity . "' where price_valuation_id='" . $row['price_valuation_id'] . "'";
					$result = $con->query($sql);
					if ($result) {
						// ok
						$deducted_quantity = $quantity_to_deduct;
						$deduct	-= 	$deducted_quantity;
					} else {
						$data['errors'][] = $con->error;
					}
				} else {
					$quantity_check = 0;
					// check if any entry already exists 
					$sql = 'select * from ki_products_price_valuation_info where delete_flag=0 and product_id="' . safe_str($inputdata['product_id']) . '" and location_type="' . safe_str($inputdata['location_type']) . '" and location_id="' . safe_str($inputdata['location_id']) . '" order by created_on desc limit 1';   // means negative quantity row 
					$result = $con->query($sql);
					if ($result->num_rows) {
						$row = $result->fetch_assoc();
						$new_quantity = $row['quantity'] - $deduct;
						$sql = "update ki_products_price_valuation_info set quantity = '" . $new_quantity . "' where price_valuation_id='" . $row['price_valuation_id'] . "'";
						$result = $con->query($sql);
						if ($result) {
							// ok			
						} else {
							$data['errors'][] = $con->error;
						}
					} else {
						// create an entry 
						$create_new_entry = 1;
					}
				}
			}
		} elseif ($inputdata['action_type'] == 2) {
			$sql = 'select * from ki_products_price_valuation_info where delete_flag=0 and product_id="' . safe_str($inputdata['product_id']) . '" and location_type="' . safe_str($inputdata['location_type']) . '" and location_id="' . safe_str($inputdata['location_id']) . '" order by created_on desc limit 1';
			$result = $con->query($sql);
			if ($result->num_rows) {
				$row = $result->fetch_assoc();
				if ($row['cost_price'] == $cost_price && $row['sell_price'] == $sell_price) {
					$new_quantity = $row['quantity'] + $inputdata['quantity'];
					$sql = "update ki_products_price_valuation_info set quantity = '" . $new_quantity . "' where price_valuation_id='" . $row['price_valuation_id'] . "'";
					$result = $con->query($sql);
					if ($result) {
						// ok			
					} else {
						$data['errors'][] = $con->error;
					}
				} else {
					$create_new_entry = 1;
				}
			} else {
				$create_new_entry = 1;
			}
		}
		if ($create_new_entry == 1) {
			if ($inputdata['action_type'] == 1) {
				$quantity = - ($inputdata['quantity']);
			} else {
				$quantity = ($inputdata['quantity']);
			}
			$sql = "insert into ki_products_price_valuation_info(product_id,location_id,location_type,quantity,cost_price,sell_price,created_on)values('" . safe_str($inputdata['product_id']) . "','" . safe_str($inputdata['location_id']) . "','" . safe_str($inputdata['location_type']) . "','" . $quantity . "','" . $cost_price . "','" . $sell_price . "','" . date('Y-m-d H:i:s') . "')";
			$result = $con->query($sql);
			if ($result) {
				// ok 
			} else {
				$data["errors"][] = $con->error;
			}
		}
		if (empty($data["errors"])) {
			$data['status'] = 1;
		}
		return $data;
	}
	/* function UpdatePriceValuation($inputdata){
		global $con;
		$data = array(
			"status" => 0,
			"errors" => array()
		);
		// get latest cost price, sell price for this product
		if($inputdata['location_type']==1){
			$cost_price = 'distribution_price';
			$sell_price = 'retail_price';
		}elseif($inputdata['location_type']==2 || $inputdata['location_type']==3){
			$cost_price = 'cost_price';
			$sell_price = 'distribution_price';
		}
		$sql="select ".$cost_price." as cost_price,".$sell_price." as sell_price from ki_product_prices_info where product_id='".safe_str($inputdata['product_id'])."' and delete_flag=0";
		$result=$con->query($sql);
		if($result->num_rows){
			$row=$result->fetch_assoc();
			$cost_price = $row['cost_price'];
			$sell_price = $row['sell_price'];
			// check if any entry already exists
			$sql='select * from ki_products_price_valuation_info where delete_flag=0 and product_id="'.safe_str($inputdata['product_id']).'" and location_type="'.safe_str($inputdata['location_type']).'" and location_id="'.safe_str($inputdata['location_id']).'" and cost_price="'.$cost_price.'" and sell_price="'.$sell_price.'" and modified_on is null';
			$result=$con->query($sql);
			if($result->num_rows){
				// update this row 
				$row=$result->fetch_assoc();
				$update = "update ki_products_price_valuation_info set quantity='".safe_str($inputdata['stock_on_hand'])."' where price_valuation_id='".$row['price_valuation_id']."'";
				$result=$con->query($update);
				if($result){
					// ok 
				}else{
					$data["errors"][] = $con->error;
				}
			}else{
				// create new entry and update previous entry  
				$sql="update ki_products_price_valuation_info set modified_on='".date('Y-m-d H:i:s')."' where modified_on is null and delete_flag=0 and product_id='".safe_str($inputdata['product_id'])."' and location_type='".safe_str($inputdata['location_type'])."' and location_id='".safe_str($inputdata['location_id'])."'";
				$result=$con->query($sql);
				if($result){
					$sql="insert into ki_products_price_valuation_info(product_id,location_id,location_type,quantity,cost_price,sell_price,created_on)values('".safe_str($inputdata['product_id'])."','".safe_str($inputdata['location_id'])."','".safe_str($inputdata['location_type'])."','".safe_str($inputdata['stock_on_hand'])."','".$cost_price."','".$sell_price."','".date('Y-m-d H:i:s')."')";
					$result=$con->query($sql);
					if($result){
						// ok 
					}else{
						$data["errors"][] = $con->error;
					}
				}else{
					$data['errors'] = $con->error;
				}
			}
		}else{
			$data['errors'] = 'Failed to get Product details.';
		}
		return $data;
	} */
	/*
		input - 
			"function"=>"get_accounts_email"
		output - 
			$data = array(
				"status" => 0,
				"details" => array(),
				"errors" => array()
			);
	*/
	function get_accounts_email($inputdata)
	{
		global $con;
		$data = array(
			"status" => 0,
			"details" => array(),
			"errors" => array()
		);
		$sql = "select * from ki_meta_info where meta_id=29 and delete_flag=0";
		$result = $con->query($sql);
		if ($result->num_rows) {
			$data['details'] = $result->fetch_assoc();
		}
		$data['status'] = 1;
		return $data;
	}
	/*
		input - 
			"function"=>"get_unsecured_invoices"
		output - 
			$data = array(
				"status" => 0,
				"list" => array(),
				"errors" => array()
			);
	*/
	function get_unsecured_invoices($inputdata)
	{
		global $con;
		$data = array(
			"status" => 0,
			"list" => array(),
			"errors" => array()
		);
		$sql = "select * from(select
		case  
			when i.home_store_type=1 then s.phone_number
			when i.home_store_type=2 then d.phone_number
			else p.phone_number
		end as phone, 
		case  
			when i.home_store_type=1 then s.email
			when i.home_store_type=2 then d.email
			else p.email
		end as email,
		case  
			when i.home_store_type=1 and u1.`user_id`!=0 and u1.`user_id` is not null then 'Store Manager'
			when i.home_store_type=2 and u11.`user_id`!=0 and u11.`user_id` is not null then 'Distribution Manager'
			when i.home_store_type=3 and u111.`user_id`!=0 and u111.`user_id` is not null then 'Production Manager'
			else 'Manager'
		end as regards,
		case  
			when i.home_store_type=1 and u1.`user_id`!=0 and u1.`user_id` is not null then CONCAT(COALESCE(u1.first_name,''),' ', COALESCE(u1.last_name,''))
			when i.home_store_type=2 and u11.`user_id`!=0 and u11.`user_id` is not null then CONCAT(COALESCE(u11.first_name,''),' ', COALESCE(u11.last_name,''))
			when i.home_store_type=3 and u111.`user_id`!=0 and u111.`user_id` is not null then CONCAT(COALESCE(u111.first_name,''),' ', COALESCE(u111.last_name,''))
			else CONCAT(COALESCE(DEF.first_name,''),' ', COALESCE(DEF.last_name,''))
		end as manager_name,
		case  
			when i.home_store_type=1 and u1.`user_id`!=0 and u1.`user_id` is not null then u1.email
			when i.home_store_type=2 and u11.`user_id`!=0 and u11.`user_id` is not null then u11.email
			when i.home_store_type=3 and u111.`user_id`!=0 and u111.`user_id` is not null then u111.email
			else DEF.email
		end as manager_email,DATEDIFF('" . date('Y-m-d') . "', i.due_date) as days, 
		COALESCE(s.`address`,d.`address`,p.`address`) AS location_address, 
		COALESCE(s.`suburb`,d.`suburb`,p.`suburb`) AS location_suburb, 
		COALESCE(s.`postcode`,d.`postcode`,p.`postcode`) AS location_postcode, 
		COALESCE(s.`state`,d.`state`,p.`state`) AS location_state, 
		COALESCE(s.`directions`,d.`directions`,p.`directions`) AS location_directions, 
		COALESCE(s.`country`,d.`country`,p.`country`) AS location_country, c.email as customer_email, i.invoice_id, i.due_date, 
		i.home_store_type, i.invoice_number, c.customer_id, c.first_name, i.used_deposit, i.used_store_credit, i.used_loyalty_credits, IFNULL(SUM(a.amount-a.change), 0) as amount_paid, u1.email as store_manager_email from ki_invoices_info i inner join ki_customers_info c on c.customer_id=i.customer_id and c.delete_flag=0 left join ki_invoice_payment_info a on a.invoice_id=i.invoice_id and a.delete_flag=0 left join ki_stores_info s on s.store_id=i.home_store_id and s.delete_flag=0 and i.home_store_type=1 left join ki_users_info u1 on u1.user_id=s.store_manager_id and u1.is_enabled=1 and u1.delete_flag=0 left join ki_distribution_branches_info d on d.distribution_branch_id=i.home_store_id and d.delete_flag=0 and i.home_store_type=2 left join ki_users_info u11 on u11.user_id=d.manager_id and u11.is_enabled=1 and u11.delete_flag=0 left join ki_production_info p on p.production_id=i.home_store_id and p.delete_flag=0 and i.home_store_type=3 left join ki_users_info u111 on u111.user_id=p.manager_id and u111.is_enabled=1 and u111.delete_flag=0 LEFT JOIN `ki_users_info` DEF ON DEF.`user_id`='" . get_meta_value(27) . "' AND DEF.`is_enabled`=1 AND DEF.`delete_flag`=0  where i.`is_draft`=0 AND i.delete_flag=0 and i.is_outstanding_invoice=1 and i.credit_type=2 and i.due_date<='" . date('Y-m-d') . "' and (DATEDIFF('" . date('Y-m-d') . "', i.due_date)=1 or DATEDIFF('" . date('Y-m-d') . "', i.due_date)=14 or DATEDIFF('" . date('Y-m-d') . "', i.due_date)=30 or DATEDIFF('" . date('Y-m-d') . "', i.due_date)=60 or DATEDIFF('" . date('Y-m-d') . "', i.due_date)=90) group by i.invoice_id) at where (at.amount_paid+at.used_store_credit+at.used_loyalty_credits+at.used_deposit)=0";
		$result = $con->query($sql);
		if ($result->num_rows) {
			while ($row = $result->fetch_assoc()) {
				$address = [$row['location_address'], $row['location_directions'], $row['location_suburb'], $row['location_state'], $row['location_country'], $row['location_postcode']];
				$row['concatenated_address'] = implode(", ", array_filter($address));
				$data['list'][] = $row;
			}
		}
		if (empty($data['errors'])) {
			$data['status'] = 1;
		}
		return $data;
	}
	function get_unlisted_supplier_details($inputdata)
	{
		/* 
		input params - none
		function is used to get details of unlisted supplier
		output - 
			$data = array(
				"status" => 0,
				"details" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"details" => array(),
			"errors" => array()
		);
		$sql = "SELECT * FROM `ki_suppliers_info` WHERE `company_name` LIKE '%Unlisted%' AND `delete_flag`=0";
		$result = $con->query($sql);
		if ($result) {
			$data['status'] = 1;
			$data['details'] = $result->fetch_assoc();
		} else {
			$data['errors'][] = $con->error;
		}
		return $data;
	}
	function GetSupplierForProduct($inputdata)
	{
		/* 
		input params - 
			"product_id" => $product_id,
			"location_type" => $location_type,
			"location_id" => $location_id,
		Function is used to get preferred supplier of a product according to location.
		output -
			$data = array(
				"status" => 0,
				"id" => 0,
				"errors" => array()
			);
		*/
		// print_r($inputdata);
		global $con;
		$data = array(
			"status" => 0,
			"id" => "",
			"errors" => array()
		);
		$supplier_id = '';
		$products_details = $row = array();
		$pdt_qry = "SELECT * FROM `ki_products_info` WHERE `product_id`='" . safe_str($inputdata['product_id']) . "' AND `delete_flag`=0";
		$pdt_res = $con->query($pdt_qry);
		if ($pdt_res) {
			if ($pdt_res->num_rows > 0) {
				$products_details = $pdt_res->fetch_assoc();
			} else {
				$data['errors'][] = "Failed to get product details.";
			}
		} else {
			$data['errors'][] = $con->error;
		}
		if (empty($data['errors'])) {
			$find_qry = "SELECT * FROM `ki_product_logistics_info` WHERE `product_id`='" . safe_str($inputdata['product_id']) . "' AND `location_type`='" . safe_str($inputdata['location_type']) . "' AND `location_id`='" . safe_str($inputdata['location_id']) . "' AND `delete_flag`=0";
			$find_res = $con->query($find_qry);
			if ($find_res->num_rows > 0) {
				$row = $find_res->fetch_assoc();
			}
			if (!empty($row) && $row['is_kingit_distribution'] == 1) {
				$supplier_id = $this->get_king_it_distribution_supplier(array())['details']['supplier_id'];
			} elseif (!empty($row) && $row['is_diff_supplier'] == 1) {
				$supplier_id = $row['supplier_id'];
			} elseif ($products_details['is_same_for_all_stores'] == 1 && $inputdata['location_type'] == 1) {
				$supplier_id = $products_details['stores_supplier_id'];
			} elseif ($products_details['is_same_for_all_loc'] == 1) {
				$supplier_id = $products_details['loc_supplier_id'];
			}
			if (!empty($supplier_id)) {
				$query = "SELECT * FROM `ki_supplier_location_info` WHERE `supplier_id`='" . safe_str($supplier_id) . "' AND `delete_flag`=0";
				$result = $con->query($query);
				if (!$result || $result->num_rows == 0) {
					$supplier_id = '';
				}
			}
		}
		if (empty($data['errors'])) {
			$data['status'] = 1;
			$data['id'] = $supplier_id;
		}
		return $data;
	}
	function get_product_details($inputdata)
	{
		/* 
		input params - 
			"where" => array of keys and values				// optional
			"product_id" => $product_id						// optional
		function is used to get details of a particular product
		output - 
			$data = array(
				"status" => 0,
				"details" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"details" => array(),
			"errors" => array()
		);
		$query = "";
		if (!empty($inputdata["where"])) {
			foreach ($inputdata["where"] as $key => $value) {
				$query .= " AND `" . safe_str($key) . "`='" . safe_str($value) . "'";
			}
		}
		if (isset($inputdata["product_id"]) && !empty($inputdata["product_id"])) {
			$query .= " AND `product_id`='" . safe_str($inputdata['product_id']) . "'";
		}
		$sql = "SELECT * FROM `ki_products_info` WHERE `delete_flag`=0" . $query;
		$result = $con->query($sql);
		if ($result) {
			$data['status'] = 1;
			$data['details'] = $result->fetch_assoc();
		} else {
			$data['errors'][] = $con->error();
		}
		return $data;
	}
	function get_files_details($inputdata)
	{
		/* 
		input params - 
			"where" => array of keys and values		// optional
			"file_id" => $file_id					// optional
		function is used to get file details
		output - 
			$data = array(
				"status" => 0,
				"details" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"details" => array(),
			"errors" => array()
		);
		$query = "";
		if (!empty($inputdata["where"])) {
			foreach ($inputdata["where"] as $key => $value) {
				$query .= " AND `" . safe_str($key) . "`='" . safe_str($value) . "'";
			}
		}
		if (isset($inputdata["file_id"]) && !empty($inputdata["file_id"])) {
			$query .= " AND `file_id`='" . safe_str($inputdata['file_id']) . "'";
		}
		$sql = "SELECT * FROM `ki_files_info` WHERE `delete_flag`=0" . $query;
		$result = $con->query($sql);
		if ($result) {
			$data['status'] = 1;
			$data['details'] = $result->fetch_assoc();
		} else {
			$data['errors'][] = $con->error();
		}
		return $data;
	}
	function get_store_details($inputdata)
	{
		/* 
		input params - 
			"store_id" => $store_id
		function is used to get details of a particular store
		output - 
			$data = array(
				"status" => 0,
				"details" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"details" => array(),
			"errors" => array()
		);
		$sql = "SELECT * FROM `ki_stores_info` WHERE `store_id`='" . safe_str($inputdata['store_id']) . "' AND `delete_flag`=0";
		$result = $con->query($sql);
		if ($result) {
			$data['status'] = 1;
			$data['details'] = $result->fetch_assoc();
		} else {
			$data['errors'][] = $con->error();
		}
		return $data;
	}
	function update_stock_on_hand_while_receiving($inputdata)
	{
		/* 
		input params - 
			"product_id" => $inputdata['product_id'],
			"quantity" => $inputdata['quantity'],
			"location_type" => $inputdata['location_type'],
			"location_id" => $inputdata['location_id']
			"action_type" => $action
		function is used to update stock on hand of particular location and return errors if any
		output - 
			$data = array(
				"status" => 0,					// value is set as 1 if no error is there
				"errors" => array()				// array of errors - validation errors or error in updation
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"errors" => array()
		);
		// check whether product type of product = 1, i.e., `stock` or not
		$query = "SELECT `product_type` FROM `ki_products_info` WHERE `product_id`='" . safe_str($inputdata['product_id']) . "' AND `delete_flag`=0";
		$result = $con->query($query);
		$prod_row = $result->fetch_assoc();
		if ($prod_row['product_type'] == 1) {
			$stock = $new_stock = 0;
			$up_qry = "";
			$action_type = 0;
			if ($inputdata['action_type'] == 1) {
				$action_type = 2;
				$up_stock_on_hand = " `stock_on_hand`=`stock_on_hand`+" . safe_str($inputdata['quantity']) . " ";				// increment
				$new_stock = $inputdata['quantity'];
			} elseif ($inputdata['action_type'] == 2) {
				$action_type = 1;
				$up_stock_on_hand = " `stock_on_hand`=`stock_on_hand`-" . safe_str($inputdata['quantity']) . " ";				// decrement
				$new_stock = 0 - $inputdata['quantity'];
			}
			// check whether entry already exists or not
			$find_qry = "SELECT *, COALESCE(`stock_on_hand`,0) AS stock_on_hand FROM `ki_product_quantites_info` WHERE `product_id`='" . safe_str($inputdata['product_id']) . "' AND `location_type`='" . safe_str($inputdata['location_type']) . "' AND `location_id`='" . safe_str($inputdata['location_id']) . "' AND `delete_flag`=0";
			$find_result = $con->query($find_qry);
			$find_count = $find_result->num_rows;
			$row = $find_result->fetch_assoc();
			if ($find_count > 0) {
				$stock = $row['stock_on_hand'];
				if ($inputdata['action_type'] == 1) {
					$new_stock = $stock + $inputdata['quantity'];
				} elseif ($inputdata['action_type'] == 2) {
					$new_stock = $stock - $inputdata['quantity'];
				}
				$up_qry = "UPDATE `ki_product_quantites_info` SET " . $up_stock_on_hand . ", `modified_on`='" . date("Y-m-d H:i:s") . "' WHERE `product_id`='" . safe_str($inputdata['product_id']) . "' AND `location_type`='" . safe_str($inputdata['location_type']) . "' AND `location_id`='" . safe_str($inputdata['location_id']) . "' AND `delete_flag`=0";
			} elseif ($inputdata['action_type'] == 1) {
				$up_qry = "INSERT INTO `ki_product_quantites_info` (`product_id`,`location_type`,`location_id`,`stock_on_hand`,`created_on`) VALUES ('" . safe_str($inputdata['product_id']) . "','" . safe_str($inputdata['location_type']) . "','" . safe_str($inputdata['location_id']) . "'," . safe_str($new_stock) . ",'" . date("Y-m-d H:i:s") . "')";
			}
			if (!empty($up_qry)) {
				$up_result = $con->query($up_qry);
				// echo $inputdata['action_type']."----".$new_stock;
				if ($up_result) {
					if (empty($data['errors']) && isset($inputdata['cost_price'])) {
						$update_stocktake = $this->UpdateInventoryCostPriceValuation(array(
							"product_id" => $inputdata['product_id'],
							"location_type" => $inputdata['location_type'],
							"location_id" => $inputdata['location_id'],
							"soh_before_update" => $stock,
							"soh_after_update" => $new_stock,
							"cost_price" => $inputdata['cost_price']
						));
						if ($update_stocktake['status'] != 1) {
							$data['errors'] = $update_stocktake['errors'];
						} else {
							$inputdata['stock_cost_price_valuation'] = $update_stocktake['stock_cost_price_valuation'];
						}
					}
					$UpdateProductValuationStock = send_rest(array(
						"function" => "UpdateProductValuationStock",
						"product_id" => $inputdata['product_id'],
						"location_type" => $inputdata['location_type'],
						"location_id" => $inputdata['location_id'],
						"stock_on_hand" => $new_stock,
						"user_id" => $inputdata["user_id"],
						"home_store_type" => $inputdata["home_store_type"],
						"home_store_id" => $inputdata["home_store_id"],
						"event_type" => $inputdata["event_type"],
						"type_id" => $inputdata["type_id"],
						"soh_before_update" => $stock,
						"soh_after_update" => $new_stock,
						"qty_before_update" => $inputdata["qty_before_update"],
						"qty_after_update" => $inputdata["qty_after_update"],
						"stock_cost_price_valuation" => (!empty($inputdata['stock_cost_price_valuation'])) ? $inputdata['stock_cost_price_valuation'] : [],
						"sell_price" => (!empty($inputdata['sell_price'])) ? $inputdata['sell_price'] : ''
					));
					if ($UpdateProductValuationStock['status'] != 1) {
						$data['errors'] = $UpdateProductValuationStock['errors'];
					} else {
						$update_price_valuation = $this->UpdatePriceValuation(array(
							"product_id" => $inputdata['product_id'],
							"location_type" => $inputdata['location_type'],
							"location_id" => $inputdata['location_id'],
							"quantity" => $inputdata['quantity'],
							"action_type" => $action_type
						));
						if ($update_price_valuation['status'] != 1) {
							$data['errors'] = $update_price_valuation['errors'];
						}
					}
					if (empty($data['errors'])) {
						$update_stocktake = $this->UpdateStockTake(array(
							"product_id" => $inputdata['product_id'],
							"location_type" => $inputdata['location_type'],
							"location_id" => $inputdata['location_id'],
							"quantity" => $inputdata['quantity'],
							"action_type" => $action_type
						));
						if ($update_stocktake['status'] != 1) {
							$data['errors'] = $update_stocktake['errors'];
						}
					}
				} else {
					$data["errors"][] = $con->error;
				}
			}
		}
		if (!empty($inputdata['repeat'])) {
			$repeat = 0;
		} else {
			$repeat = 1;
		}
		if (empty($data['errors']) && !empty($repeat) && (($inputdata['location_type'] == 1 && $inputdata['location_id'] == ONLINE_STORE_ID) || ($inputdata['location_type'] == 2 && $inputdata['location_id'] == KINGIT_DISTRIBUTION_ID))) {
			if ($inputdata['location_type'] == 1 && $inputdata['location_id'] == ONLINE_STORE_ID) {
				$loc_type = 2;
				$loc_id = KINGIT_DISTRIBUTION_ID;
			} else {
				$loc_type = 1;
				$loc_id = ONLINE_STORE_ID;
			}
			$up_result = $this->update_stock_on_hand_while_receiving(array(
				"product_id" => $inputdata['product_id'],
				"quantity" => $inputdata['quantity'],
				"location_type" => $loc_type,
				"location_id" => $loc_id,
				"action_type" => $inputdata['action_type'],
				"event_type" => $inputdata['event_type'],
				"type_id" => $inputdata['type_id'],
				"qty_before_update" => $inputdata['qty_before_update'],
				"qty_after_update" => $inputdata['qty_after_update'],
				"user_id" => $inputdata['user_id'],
				"home_store_type" => $inputdata['home_store_type'],
				"home_store_id" => $inputdata['home_store_id'],
				"stock_cost_price_valuation" => (!empty($inputdata['stock_cost_price_valuation'])) ? $inputdata['stock_cost_price_valuation'] : [],
				"sell_price" => (!empty($inputdata['sell_price'])) ? $inputdata['sell_price'] : '',
				"repeat" => $repeat
			));
			$data['errors'] = $up_result['errors'];
		}
		if (empty($data['errors'])) {
			$data['status'] = 1;
		}
		return $data;
	}
	function get_user_details($inputdata)
	{
		/* 
		input params - 
			"user_id" => $user_id
		function is used to get details of a particular user
		output - 
			$data = array(
				"status" => 0,
				"details" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"details" => array(),
			"errors" => array()
		);
		$sql = "SELECT *, CONCAT(COALESCE(`first_name`,''),' ', COALESCE(`last_name`,'')) AS user_name FROM `ki_users_info` WHERE `user_id`='" . safe_str($inputdata['user_id']) . "' AND `delete_flag`=0";
		$result = $con->query($sql);
		if ($result) {
			$data['status'] = 1;
			$data['details'] = $result->fetch_assoc();
		} else {
			$data['errors'][] = $con->error();
		}
		return $data;
	}
	function get_default_production_details($inputdata)
	{
		/* 
		input params - none
		function is used to get details of default production
		output - 
			$data = array();
		*/
		global $con;
		$data = array();
		$sql = "SELECT * FROM `ki_production_info` WHERE `production_id`='1' AND `delete_flag`=0";
		$result = $con->query($sql);
		if ($result) {
			$data = $result->fetch_assoc();
		} else {
			echo $con->error();
		}
		return $data;
	}
	function get_default_distribution_details($inputdata)
	{
		/* 
		input params - none
		function is used to get details of default distribution branch
		output - 
			$data = array();
		*/
		global $con;
		$data = array();
		$sql = "SELECT 
					DBI.*, CONCAT(COALESCE(`first_name`,''),' ', COALESCE(`last_name`,'')) AS manager_name 
				FROM 
					`ki_distribution_branches_info` DBI 
				LEFT JOIN `ki_users_info` UI ON 
					DBI.`manager_id`=UI.`user_id` AND UI.`delete_flag`=0 
				WHERE 
					`distribution_branch_id`='1' AND DBI.`delete_flag`=0";
		$result = $con->query($sql);
		if ($result) {
			$data = $result->fetch_assoc();
		} else {
			echo $con->error();
		}
		return $data;
	}
	/*
		input - 
			"function" => "get_distribution_shipment_details",
			"distribution_shipment_id" => $distribution_shipment_id
		output - 
			$row
	*/
	function get_distribution_shipment_details($inputdata)
	{
		global $con;
		$sql = "select * from ki_distribution_shipment_info where distribution_shipment_id='" . safe_str($inputdata['distribution_shipment_id']) . "' and delete_flag=0";
		$result = $con->query($sql);
		$row = $result->fetch_assoc();
		return $row;
	}
	/*
		input - 
			"function" => "get_supplier_details",
			"supplier_id" => $d_row['supplier_id']
		output - 
			$row
	*/
	function get_supplier_details($inputdata)
	{
		global $con;
		$sql = "select * from ki_suppliers_info where supplier_id='" . safe_str($inputdata['supplier_id']) . "' and delete_flag=0";
		$result = $con->query($sql);
		$row = $result->fetch_assoc();
		return $row;
	}
	function get_pending_special_orders_list($inputdata)
	{
		// params - 
		// "page_no" => $request["PageNumber"],
		// "row_size" => $request["RowSize"],
		// "sort_on" => $request["SortOn"],
		// "sort_type" => $request["SortType"],
		// "is_admin_tab" => $is_admin_tab,
		// "location_type" =>$_SESSION['ki_user']['location_type'],
		// "location_id" =>$_SESSION['ki_user']['location_id'],
		// "search" => $search
		// returns tickets or invoices having special orders not yet finalised
		// output - 
		// $data = array(
		// "total_records" => 0,
		// "total_pages" => 0,
		// "pagging_list" => array()
		// );
		global $con;
		$data = array(
			"total_records" => 0,
			"total_pages" => 0,
			"pagging_list" => array()
		);

		$page_no = $inputdata['page_no'];
		$row_size = $inputdata['row_size'];
		$sort_on = "at." . $inputdata['sort_on'];
		$sort_type = $inputdata['sort_type'];

		$where = "";
		if (!empty($inputdata['search'])) {
			$where .= " where (at.number like '%" . safe_str($inputdata['search']) . "%' or at.customer_name like '%" . safe_str($inputdata['search']) . "%' or at.user_name like '%" . safe_str($inputdata['search']) . "%')";
		}
		if (empty($inputdata['is_admin_tab'])) {
			// means not admin 
			if (empty($inputdata['search'])) {
				$where .= " where ";
			} else {
				$where .= " and ";
			}
			$where .= " at.location_id='" . safe_str($inputdata['location_id']) . "' and at.location_type='" . safe_str($inputdata['location_type']) . "' ";
		}


		$pcount_qry = "SELECT 
							COUNT(*) AS total_count 
						FROM (
							SELECT 
								j.home_store_type AS location_type, j.home_store_id AS location_id, j.job_id AS id, job_number AS number, CONCAT( COALESCE(u.`first_name`, ''), ' ', COALESCE(u.`last_name`, '') ) AS user_name, CONCAT( COALESCE(c.`first_name`, ''), ' ', COALESCE(c.`last_name`, '') ) AS customer_name, j.created_on, 1 AS type 
							FROM 
								ki_jobs_info j 
							INNER JOIN ki_job_parts_order_info jpo ON 
								jpo.job_id=j.job_id AND jpo.order_id NOT IN (
									SELECT 
										`type_id` 
									FROM 
										`ki_store_delivery_line_items_mapping_info` 
									WHERE 
										`type`=2 AND `delete_flag`=0
								) AND jpo.delete_flag = 0 
							LEFT JOIN ki_products_info p ON 
								p.product_id=jpo.product_id AND p.delete_flag=0 
							INNER JOIN ki_users_info u ON 
								u.user_id=j.user_id AND u.delete_flag=0 
							LEFT JOIN ki_customers_info c ON 
								c.customer_id=j.customer_id AND c.delete_flag=0 
							WHERE 
								j.delete_flag=0 AND j.job_type=1
							UNION 
							SELECT 
								i.home_store_type AS location_type, i.home_store_id AS location_id, i.invoice_id AS id, invoice_number AS number, CONCAT( COALESCE(u.`first_name`, ''), ' ', COALESCE(u.`last_name`, '') ) AS user_name, CONCAT( COALESCE(c.`first_name`, ''), ' ', COALESCE(c.`last_name`, '') ) AS customer_name, i.created_on, 2 AS type 
							FROM 
								ki_invoices_info i 
							INNER JOIN ki_invoice_line_items_info ii ON 
								ii.invoice_id=i.invoice_id AND ii.is_special_order=1 AND ii.invoice_line_item_id NOT IN (
									SELECT 
										`type_id` 
									FROM 
										`ki_store_delivery_line_items_mapping_info` 
									WHERE 
										`type`=1 AND `delete_flag`=0
									) 
								AND ii.delete_flag=0 
							LEFT JOIN ki_products_info p ON 
								p.product_id=ii.product_id AND p.delete_flag=0 
							INNER JOIN ki_users_info u ON 
								u.user_id=i.user_id AND u.delete_flag=0 
							LEFT JOIN ki_customers_info c ON 
								c.customer_id=i.customer_id AND c.delete_flag=0 WHERE i.`is_draft`=0 AND i.delete_flag=0
							) at" . $where;
		$pcount_result = $con->query($pcount_qry);
		$pcount_row = $pcount_result->fetch_assoc();
		$total_records = $pcount_row["total_count"];

		@$total_pages = ceil($total_records / $row_size);
		if ($total_pages == 0) {
			$total_pages = 1;
		}
		if ($page_no > $total_pages) {
			$page_no = $total_pages;
		}
		$limit_from = ($row_size * $page_no) - $row_size;

		$pagg_qry = "SELECT 
						* 
					FROM ( 
						SELECT 
							j.home_store_type AS location_type, j.home_store_id AS location_id, j.job_id AS id,job_number AS number, CONCAT( COALESCE(u.`first_name`, ''), ' ', COALESCE(u.`last_name`, '') ) AS user_name, CONCAT( COALESCE(c.`first_name`, ''), ' ', COALESCE(c.`last_name`, '') ) AS customer_name, j.created_on, 1 AS type 
						FROM 
							ki_jobs_info j 
						INNER JOIN ki_job_parts_order_info jpo ON 
							jpo.job_id=j.job_id AND jpo.order_id NOT IN (
								SELECT 
									`type_id` 
								FROM 
									`ki_store_delivery_line_items_mapping_info` 
								WHERE 
									`type`=2 AND `delete_flag`=0
							) AND jpo.delete_flag = 0
						LEFT JOIN ki_products_info p ON 
							p.product_id=jpo.product_id AND p.delete_flag=0 
						INNER JOIN ki_users_info u ON 
							u.user_id=j.user_id AND u.delete_flag=0 
						LEFT JOIN ki_customers_info c ON 
							c.customer_id=j.customer_id AND c.delete_flag=0 
						WHERE 
							j.delete_flag=0 AND j.job_type=1
						UNION 
						SELECT 
							i.home_store_type AS location_type, i.home_store_id AS location_id, i.invoice_id AS id, invoice_number AS number,CONCAT( COALESCE(u.`first_name`, ''), ' ', COALESCE(u.`last_name`, '') ) AS user_name, CONCAT( COALESCE(c.`first_name`, ''), ' ', COALESCE(c.`last_name`, '') ) AS customer_name, i.created_on, 2 AS type 
						FROM 
							ki_invoices_info i 
						INNER JOIN ki_invoice_line_items_info ii ON 
							ii.invoice_id=i.invoice_id AND ii.is_special_order=1 AND ii.invoice_line_item_id NOT IN (
								SELECT 
									`type_id` 
								FROM 
									`ki_store_delivery_line_items_mapping_info` 
								WHERE 
									`type`=1 AND `delete_flag`=0 
								) 
							AND ii.delete_flag=0 
						LEFT JOIN ki_products_info p ON 
							p.product_id=ii.product_id AND p.delete_flag=0 
						INNER JOIN ki_users_info u ON 
							u.user_id=i.user_id AND u.delete_flag=0 
						LEFT JOIN ki_customers_info c ON 
							c.customer_id=i.customer_id AND c.delete_flag=0 
						WHERE 
							i.`is_draft`=0 AND i.delete_flag=0
					) at 
					" . $where . " 
					order by 
						" . safe_str($sort_on) . " " . safe_str($sort_type) . " 
					LIMIT 
						" . $limit_from . ", " . $row_size;
		// $pagg_qry = "select * from `".safe_str($table)."` where delete_flag=0 ".$query." order by ".safe_str($sort_on)." ".safe_str($sort_type)." LIMIT ".$limit_from.", ".$row_size;
		$pagg_result = $con->query($pagg_qry);
		$pagg_count = $pagg_result->num_rows;
		$pagging_list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}

		$data["total_records"] = $total_records;
		$data["total_pages"] = $total_pages;
		$data["pagging_list"] = $pagging_list;

		return $data;
	}
	function check_if_location_already_exists($inputdata)
	{
		// params - location_id, location_type (1-store,2-distribution,3-production), location_name
		// check if location name already exists 
		// output - 1 if already exists, 0 if not exists 
		global $con;
		$store_where = "";
		$distribution_where = "";
		$production_where = "";
		if (!empty($inputdata['location_id'])) {
			if ($inputdata['location_type'] == 1) {
				$store_where = " and store_id!='" . safe_str($inputdata['location_id']) . "'";
			} elseif ($inputdata['location_type'] == 2) {
				$distribution_where = " and distribution_branch_id!='" . safe_str($inputdata['location_id']) . "'";
			} elseif ($inputdata['location_type'] == 3) {
				$production_where = " and production_id!='" . safe_str($inputdata['location_id']) . "'";
			}
		}
		$inputdata['location_name'] = trim($inputdata['location_name']);
		$inputdata['location_name'] = preg_replace('/\s+/', ' ', $inputdata['location_name']);
		$sql = "select * from ki_stores_info where store_name='" . safe_str($inputdata['location_name']) . "' and delete_flag=0" . $store_where;
		$result = $con->query($sql);
		if ($result->num_rows) {
			// means found 
			return 1;
		} else {
			$sql = "select * from ki_distribution_branches_info where distribution_name='" . safe_str($inputdata['location_name']) . "' and delete_flag=0" . $distribution_where;
			$result = $con->query($sql);
			if ($result->num_rows) {
				// means found 
				return 1;
			} else {
				$sql = "select * from ki_production_info where production_name='" . safe_str($inputdata['location_name']) . "' and delete_flag=0" . $production_where;
				$result = $con->query($sql);
				if ($result->num_rows) {
					// means found 
					return 1;
				} else {
					return 0;
				}
			}
		}
	}
	function execute_follow_ups($inputdata)
	{
		// params - no parameter required
		// get follow ups to follow within next 10 minutes 
		// output - execute follow ups, return  $data['status'] = 1 if no error found, else $data['status'] = 0 and $data['error'] = Error
		$data = array(
			"status" => 0,
			"errors" => array()
		);
		$current_date = date('Y-m-d H:i:s', strtotime('- 10 min'));
		$next_10_mins = date('Y-m-d H:i:s', strtotime('+ 10 min'));
		global $con;
		$sql = "select * from ki_estimate_follow_ups_info a inner join ki_estimates_info e on e.estimate_id=a.estimate_id and e.delete_flag=0 where (a.followup_type=2 or a.followup_type=3) and (((a.followup_date between '" . $current_date . "' and '" . $next_10_mins . "') and a.followup_complete=0) or a.followup_complete=10) and  a.is_cancelled=0 and a.delete_flag=0";
		$result = $con->query($sql);
		// print_r($result);
		if ($result->num_rows) {
			while ($row = $result->fetch_assoc()) {
				// check if estimate location has trade day or non-trade day  
				$sql1 = "select * from ki_non_trade_days_info where location_id='" . $row['home_store_id'] . "' and location_type='" . $row['home_store_type'] . "' and delete_flag=0 and (day='" . date('Y-m-d') . "' or (is_recurring=1 and CONCAT(EXTRACT(MONTH from day),'-',EXTRACT(DAY from day))='" . date('n-j') . "')  OR '" . date('N') . "' IN (SELECT DISTINCT `day` FROM `ki_non_trade_recurring_days_info` WHERE `location_type`='" . $inputdata['location_type'] . "' AND `location_id`='" . $inputdata['location_id'] . "' AND `is_enabled`=1 AND `delete_flag`=0))";
				$result1 = $con->query($sql1);
				if ($result1->num_rows) {
					// means it is a non trade day 
					// update followup date in db 
					$update = send_rest(
						array(
							"function" => "update",
							"table" => "ki_estimate_follow_ups_info",
							"fields_data" => array("followup_date" => date('Y-m-d H:i:s', strtotime('+ 1 day')), "modified_on" => date('Y-m-d H:i:s')),
							"key" => "follow_up_id",
							"value" => $row['follow_up_id']
						)
					);
					continue;
				}
				// update status to 10 so that if any error in this follow up, it is executed in next cron job
				$update = send_rest(
					array(
						"function" => "update",
						"table" => "ki_estimate_follow_ups_info",
						"fields_data" => array("followup_complete" => 10, "modified_on" => date('Y-m-d H:i:s')),
						"key" => "follow_up_id",
						"value" => $row['follow_up_id']
					)
				);
				// print_r($update);die;
				if ($update['status'] == 1) {
					$res = send_follow_up($row['follow_up_id'], $row['estimate_id']);
					if ($res == 1) {
						// ok 
					} else {
						$data['errors'] = $res;
					}
				} else {
					$data['errors'][] = "Error in updating follow up status to 10 for followup-id - " . $row['follow_up_id'];
				}
			}
			if (empty($data['errors'])) {
				// everything done without error
				$data['status'] = 1;
			}
		} else {
			$data['errors'][] = 'No follow up to Follow:)';
		}
		return $data;
	}
	function cancel_followup($inputdata)
	{
		// params - followup_id
		// before cancel, check if already cancelled and if not, cancel the follow up 
		// output - 1 on success, error on failure
		global $con;
		$follow_up_id = $inputdata['followup_id'];
		$get_details = send_rest(
			array(
				"function" => "get_details",
				"table" => "ki_estimate_follow_ups_info",
				"key" => "follow_up_id",
				"value" => $follow_up_id
			)
		);
		if (!empty($get_details)) {
			$is_cancelled = $get_details['is_cancelled'];
			if ($is_cancelled == 1) {
				// already cancelled 
				return "Follow Up already cancelled.";
			} else {
				// update is_cancelled to 1 
				$update = send_rest(
					array(
						"function" => "update",
						"table" => "ki_estimate_follow_ups_info",
						"fields_data" => array("is_cancelled" => 1),
						"key" => "follow_up_id",
						"value" => $follow_up_id
					)
				);
				if ($update['status'] == 1) {
					return 1;
				} else {
					return "Error in updation";
				}
			}
		} else {
			return "Failed to get details.";
		}
	}
	function get_follow_up_email_details($inputdata)
	{
		// params - estimate_id
		// get details to be used in follow up customer email for this estimate 
		// output - $data['status'] = 1 and $data['data'] = details if no error found, else $data['status'] = 0 and $data['errors'] = Error
		global $con;
		$data = array(
			"status" => 0,
			"data" => array(),
			"errors" => ''
		);

		$sql = "select u.email as user_email,u.first_name,u.last_name,c.customer_id,c.email as customer_email,c.phone as customer_phone,c.first_name as customer_first_name, c.is_unsubscribed_to_marketing, DATE(e.created_on) as created_on,e.home_store_type,e.home_store_id from ki_estimates_info e inner join ki_users_info u on u.user_id=e.user_id and u.delete_flag=0 inner join ki_customers_info c on c.customer_id=e.customer_id and c.delete_flag=0 where e.estimate_id='" . safe_str($inputdata['estimate_id']) . "' and e.delete_flag=0";
		$result = $con->query($sql);
		if ($result->num_rows) {
			$row = $result->fetch_assoc();
			// get location phone number 
			if ($row['home_store_type'] == 1) {
				$table = "ki_stores_info";
				$field = "store_id";
				$field1 = "store_name";
			} elseif ($row['home_store_type'] == 2) {
				$table = "ki_distribution_branches_info";
				$field = "distribution_branch_id";
				$field1 = "distribution_name";
			} elseif ($row['home_store_type'] == 3) {
				$table = "ki_production_info";
				$field = "production_id";
				$field1 = "production_name";
			} else {
				$data['status'] = 0;
				$data['errors'] = 'Failed to get Location details';
			}
			if (empty($data['errors'])) {
				$sql1 = "select * from " . $table . " where " . $field . "='" . $row['home_store_id'] . "' and delete_flag=0";
				$result1 = $con->query($sql1);
				if ($result1->num_rows) {
					$row1 = $result1->fetch_assoc();
					$data['status'] = 1;
					$address = [$row1['address'], $row1['directions'], $row1['suburb'], $row1['state'], $row1['country'], $row1['postcode']];
					$address = implode(", ", array_filter($address));
					$data['data']['user_email'] = $row['user_email'];
					$data['data']['user_first_name'] = $row['first_name'];
					$data['data']['user_last_name'] = $row['last_name'];
					$data['data']['customer_email'] = $row['customer_email'];
					$data['data']['customer_phone'] = $row['customer_phone'];
					$data['data']['customer_first_name'] = $row['customer_first_name'];
					$data['data']['customer_id'] = $row['customer_id'];
					$data['data']['is_unsubscribed_to_marketing'] = $row['is_unsubscribed_to_marketing'];
					$data['data']['estimate_creation_date'] = $row['created_on'];
					$data['data']['location_phone_number'] = $row1['phone_number'];
					$data['data']['location_type'] = $row['home_store_type'];
					$data['data']['location_name'] = $row1[$field1];
					$data['data']['address'] = $address;
					$data['data']['location_email'] = $row1['email'];
				} else {
					$data['status'] = 0;
					$data['errors'] = 'Failed to get details';
				}
			}
		} else {
			$data['status'] = 0;
			$data['errors'] = 'Failed to get details';
		}
		return $data;
	}
	function create_follow_up($inputdata)
	{
		// params - estimate_id, type - (2-follow up now,3-follow up in 24 hrs,4-follow up in 1 week)
		// if type=1, send email,sms,missed call now, else create follow up in db 
		// output - 1 on success, error on failure 
		$data = array(
			"status" => 0,
			"errors" => array()
		);
		global $con;
		if (!empty($inputdata['estimate_id'])) {
			$estimate_id = $inputdata['estimate_id'];
			$estimate_details = $this->get_estimate_details(array(
				"estimate_id" => $estimate_id
			));
			if (!empty($estimate_details)) {
				// if($estimate_details['is_unsubscribed_to_marketing']!=1){
				if (check_if_valid_customer_email($estimate_details['cust_email'])) {
					if (!empty($inputdata['type']) && ($inputdata['type'] == 1 || $inputdata['type'] == 2 || $inputdata['type'] == 3 || $inputdata['type'] == 4)) {
						$follow_up_type = $inputdata['type'];
						// create entry in db 
						// set followup date 
						if ($follow_up_type == 2) {
							$followup_date = date('Y-m-d H:i:s');
							$type = 1;
						} elseif ($follow_up_type == 3) {
							$followup_date = date('Y-m-d H:i:s', strtotime('+24 hour'));
							$type = 2;
						} elseif ($follow_up_type == 4) {
							$followup_date = date('Y-m-d H:i:s', strtotime('+7 days'));
							$type = 3;
						}
						$create = send_rest(
							array(
								"function" => "create",
								"table" => "ki_estimate_follow_ups_info",
								"fields_data" => array(
									"estimate_id" => $estimate_id,
									"followup_type" => $type,
									"followup_complete" => 0,
									"followup_date" => $followup_date,
									"created_on" => date('Y-m-d H:i:s')
								)
							)
						);
						if ($create['status'] == 1) {
							if ($follow_up_type == 2) {
								// call function to create missed call, sms and email and save in db
								$res = send_follow_up($create['id'], $estimate_id);
								if ($res != 1) {
									// ok 
									$data['errors'] = $res;
								}
							}
						} else {
							$data['errors'] = $create['errors'];
						}
					} else {
						$data['errors'][] = "Invalid type of follow-up.";
					}
				} else {
					$data['errors'][] = "Follow-up cannot be done as customer email is invalid.";
				}
				// }else{
				// $data['errors'][] = "Follow-up cannot be done as customer is unsubscribed to mails.";
				// }
			} else {
				$data['errors'][] = "Failed to get estimate details.";
			}
		} else {
			$data['errors'][] = "Failed to get estimate details.";
		}
		if (empty($data['errors'])) {
			$data['status'] = 1;
		}
		return $data;
	}
	function check_if_time_clock_entry_exists($inputdata)
	{
		global $con;
		$query = "";
		if (!empty($inputdata['time_clock_id'])) {
			$query = " and time_clock_id='" . safe_str($inputdata['time_clock_id']) . "'";
		} else {
			$query = " and in_date = '" . date('Y-m-d') . "'";
		}
		$sql = "select * from ki_time_clock_info where delete_flag=0 and location_id='" . safe_str($inputdata['location_id']) . "' and location_type='" . safe_str($inputdata['location_type']) . "' and user_id='" . safe_str($inputdata['user_id']) . "' and (out_date is null or out_date='')" . $query;
		$result = $con->query($sql);
		if ($result->num_rows) {
			return 1;
		} else {
			return 0;
		}
	}
	function get_max_roster_name($inputdata)
	{
		global $con;
		$sql = "select max(roster_name) as max_roster_name from ki_rosters_info where location_id='" . safe_str($inputdata['location_id']) . "' and location_type='" . safe_str($inputdata['location_type']) . "' and delete_flag=0";
		$result = $con->query($sql);
		$row = $result->fetch_assoc();
		return $row['max_roster_name'];
	}
	function create_rosters_in_advance($inputdata)
	{
		global $con;
		// get roster details 
		$default_roster_details = send_rest(
			array(
				"function" => "get_details",
				"table" => "ki_rosters_info",
				"key" => "roster_id",
				"value" => $inputdata['roster_id']
			)
		);
		//print_r($default_roster_details);
		// get list of data for this roster from ki_roster_data_info 
		$get_roster_data = send_rest(
			array(
				"table" => "ki_roster_data_info",
				"function" => "get_list",
				"key" => "roster_id",
				"value" => $inputdata['roster_id']
			)
		);
		if (!empty($default_roster_details)) {
			// get next 4 weeks from date
			$i = 1;
			$date = $default_roster_details['from_date'];
			while ($i <= 4) {
				// echo $i;
				// Create a new DateTime object
				$date = new DateTime($date);
				// Modify the date it contains
				$date->modify('next monday');
				// Output
				$date = $date->format('Y-m-d');
				// check if roster present for this week 
				$get_details = send_rest(
					array(
						"function" => "get_details",
						"table" => "ki_rosters_info",
						"key" => "from_date",
						"value" => $date,
						"key_ad" => "location_id",
						"value_ad" => $default_roster_details['location_id'],
						"key_pg" => "location_type",
						"value_pg" => $default_roster_details['location_type'],
					)
				);
				// print_r($get_details);
				if (empty($get_details) || empty($get_details['is_temporary'])) {
					if (!empty($get_details)) {
						$max_roster_name = send_rest(
							array(
								"function" => "delete_records",
								"table" => "ki_roster_data_info",
								"key" => "roster_id",
								"value" => $get_details['roster_id']
							)
						);
						$created_roster_id = $get_details['roster_id'];
					} else {
						// get max roster name for this location 
						$max_roster_name = send_rest(
							array(
								"function" => "get_max_roster_name",
								"location_id" => $default_roster_details['location_id'],
								"location_type" => $default_roster_details['location_type'],
							)
						);
						//echo $max_roster_name;
						// means roster is not present. Hence, need to create roster 
						$in_fields = array();
						$in_fields = $get_details;
						// print_r($in_fields);
						// remove roster_id
						unset($in_fields['roster_id']);
						$in_fields['roster_name'] = $max_roster_name + 1;
						$in_fields['from_date'] = $date;
						$in_fields['to_date'] = date("Y-m-d", strtotime('+ 6 days', strtotime($date)));
						$in_fields['is_default'] = 0;
						$in_fields['created_on'] = date('Y-m-d H:i:s');
						$in_fields['modified_on'] = "null";
						$in_fields['created_by_id'] = $default_roster_details['created_by_id'];
						$in_fields['location_id'] = $default_roster_details['location_id'];
						$in_fields['location_type'] = $default_roster_details['location_type'];
						// print_r($in_fields);
						// create the roster 
						$create = send_rest(
							array(
								"table" => "ki_rosters_info",
								"function" => "create",
								"fields_data" => $in_fields
							)
						);
						$created_roster_id = $create['id'];
					}
					// create roster data 
					//print_r($create);
					$day_1 = $date;
					$day_2 = date('Y-m-d', strtotime($day_1 . '+1 day'));
					$day_3 = date('Y-m-d', strtotime($day_2 . '+1 day'));
					$day_4 = date('Y-m-d', strtotime($day_3 . '+1 day'));
					$day_5 = date('Y-m-d', strtotime($day_4 . '+1 day'));
					$day_6 = date('Y-m-d', strtotime($day_5 . '+1 day'));
					$day_7 = date('Y-m-d', strtotime($day_6 . '+1 day'));
					if (!empty($get_roster_data['list'])) {
						foreach ($get_roster_data['list'] as $roster_data) {
							if ($roster_data['day'] == 1) {
								$date1 = $day_1;
							} elseif ($roster_data['day'] == 2) {
								$date1 = $day_2;
							} elseif ($roster_data['day'] == 3) {
								$date1 = $day_3;
							} elseif ($roster_data['day'] == 4) {
								$date1 = $day_4;
							} elseif ($roster_data['day'] == 5) {
								$date1 = $day_5;
							} elseif ($roster_data['day'] == 6) {
								$date1 = $day_6;
							} elseif ($roster_data['day'] == 7) {
								$date1 = $day_7;
							}
							$up_fields = array();
							$up_fields['roster_id'] = $created_roster_id;
							$up_fields['day'] = $roster_data['day'];
							$up_fields['date'] = $date1;
							$up_fields['user_id'] = $roster_data['user_id'];
							$up_fields['start_time'] = $roster_data['start_time'];
							$up_fields['finish_time'] = $roster_data['finish_time'];
							$up_fields['status'] = 1;
							$up_fields['created_on'] = date('Y-m-d H:i:s');
							$create = send_rest(
								array(
									"table" => "ki_roster_data_info",
									"function" => "create",
									"fields_data" => $up_fields
								)
							);
							// print_r($create);
						}
					}
				}
				$i++;
			}
		}
	}
	function get_print_pdt_details($inputdata)
	{
		global $con;
		$join = '';
		if (isset($inputdata['location_type']) && !empty($inputdata['location_type']) && isset($inputdata['location_id']) && !empty($inputdata['location_id'])) {
			$join = " LEFT JOIN `ki_product_quantites_info` PQI ON p.`product_id`=PQI.`product_id` AND PQI.`location_type`='" . safe_str($inputdata['location_type']) . "' AND PQI.`location_id`='" . safe_str($inputdata['location_id']) . "' AND PQI.`delete_flag`=0 ";
		}
		$sql = 'select * from ki_products_info p' . $join . ' left join ki_product_prices_info pp on pp.product_id=p.product_id and pp.delete_flag=0 where p.delete_flag=0 and p.product_id="' . safe_str($inputdata['product_id']) . '"';
		$result = $con->query($sql);
		$data[0] = $result->fetch_assoc();
		return $data;
	}
	function add_searched_pdt_to_price_list($inputdata)
	{
		global $con;
		$data = array(
			"status" => 0,
			"errors" => ''
		);
		// $sql='select * from ki_products_info where product_id="'.safe_str($inputdata['product_id']).'" and delete_flag';
		$sql = "INSERT INTO ki_price_list_products_info (price_list_id, product_id, created_on)
		SELECT * FROM (SELECT '" . safe_str($inputdata['price_list_id']) . "', '" . safe_str($inputdata['product_id']) . "', '" . date('Y-m-d H:i:s') . "') AS tmp
		WHERE NOT EXISTS (
			SELECT price_list_id,product_id FROM ki_price_list_products_info WHERE price_list_id = '" . safe_str($inputdata['price_list_id']) . "' and product_id='" . safe_str($inputdata['product_id']) . "'
		) LIMIT 1;";
		$result = $con->query($sql);
		if ($result) {
			// ok
			$data['status'] = 1;
		} else {
			$data['errors'] = $con->error;
		}
		return $data;
	}
	function get_matched_products_list($inputdata)
	{
		global $con;
		$data = array();
		// $search_words = array_unique(array_filter(explode(" ", $inputdata['search'])));
		// foreach($search_words as $search){
		// $query[] = "`product_name` LIKE '%".safe_str($search)."%' OR `SKU` LIKE '%".safe_str($search)."%' OR `barcode` LIKE '%".safe_str($search)."%'";
		// }
		// $query = implode(" OR ", $query);
		// $sql = "SELECT `product_id`, `product_name`, `SKU` FROM `ki_products_info` WHERE `delete_flag`=0 AND (".$query.") AND `status`!=3 ORDER BY LOCATE('searchstring', word)";
		// Improve product search functionality in Invoice and Estimate Creation EG the search fields support “IPhone 8 Touch Screen LCD” but if user types in “IPhone 8 LCD” it is not found.
		$searching_terms = explode(" ", $inputdata['search']);
		$count_terms = count($searching_terms);
		$product_search_query = "";
		for ($i = 0; $i < $count_terms; $i++) {
			if ($i == 0) {
				$product_search_query = ' or (';
			}
			if ($i == ($count_terms - 1)) {
				$product_search_query .= ' product_name like "%' . safe_str(($searching_terms[$i])) . '%"';
				$product_search_query .= ' )';
			} else {
				$product_search_query .= ' product_name like "%' . safe_str(($searching_terms[$i])) . '%" and';
			}
		}
		// echo $product_search_query;
		$query =  ' and ((SKU like "%' . safe_str(($inputdata['search'])) . '%") or (barcode like "%' . safe_str(($inputdata['search'])) . '%") ' . $product_search_query . ')';
		// $sql = "SELECT *, MATCH (`product_name`, `SKU`, `barcode`) AGAINST ('*".implode("* *", array_unique(array_filter(explode(" ", $inputdata['search']))))."*' IN BOOLEAN MODE) as score FROM `ki_products_info` WHERE `delete_flag`=0 AND `status`!=3 AND MATCH (`product_name`, `SKU`, `barcode`) AGAINST ('*".implode("* *", array_unique(array_filter(explode(" ", $inputdata['search']))))."*' IN BOOLEAN MODE)>0 ORDER BY score DESC, `product_name`, `SKU` ";
		$sql = 'SELECT * FROM `ki_products_info` WHERE `delete_flag`=0 AND `status`!=3 ' . $query . ' ORDER BY `product_name`, `SKU` ';
		$result = $con->query($sql);
		if ($result->num_rows) {
			$i = 0;
			while ($row = $result->fetch_assoc()) {
				$data[$i] = $row;
				$i++;
			}
		}
		return $data;
	}
	function add_products_to_price_list($inputdata)
	{
		// print_r($inputdata);die;
		global $con;
		$data = array(
			"status" => 0,
			"errors" => ''
		);
		$error = 0;
		$Encryption = new Encryption();
		$price_list_id = output($Encryption->decode($inputdata['price_list_id']));
		// first delete products and then insert new 
		// delete all 
		$delete_records = send_rest(
			array(
				"function" => "delete_records",
				"table" => "ki_price_list_products_info",
				"key" => "price_list_id",
				"value" => $price_list_id
			)
		);
		if ($delete_records['status'] == 1) {
			// now insert 
			if (!empty($inputdata['products'])) {
				$products = explode(",", $inputdata['products']);
				$copies = explode(",", $inputdata['copies']);
				// print_r($products);die;
				foreach ($products as $key => $product_id) {
					$pdt = output($Encryption->decode($product_id));
					$in_fields = array();
					$in_fields['price_list_id'] = $price_list_id;
					$in_fields['product_id'] = $pdt;
					$in_fields['copies'] = $copies[$key];
					$in_fields['created_on'] = date('Y-m-d H:i:s');
					$create = send_rest(
						array(
							"function" => "create",
							"table" => "ki_price_list_products_info",
							"fields_data" => $in_fields
						)
					);
					if ($create['status'] != 1) {
						$error = 1;
					}
				}
			}
		} else {
			$error = 1;
			$data['errors'] = 'Error';
		}
		if ($error == 0) {
			$data['status'] = 1;
		}
		return $data;
	}
	function get_price_list_products_list($inputdata)
	{
		global $con;
		$Encryption = new Encryption();
		$data = array(
			"pagging_list" => array()
		);

		$query = '';
		$inputdata['query'] = mysqli_real_escape_string($con, $inputdata['query']);
		if (!empty($inputdata['category_id'])) {
			$categoryId = output($Encryption->decode($inputdata['category_id']));
			$query .= ' and PI.category_id = ' . $categoryId;
		}
		if (!empty($inputdata['query'])) {
			$query .= ' and (product_name like "%' . safe_str($inputdata['query']) . '%" or product_type like "%' . safe_str($inputdata['query']) . '%" or SKU like "%' . safe_str($inputdata['query']) . '%" )';
		}

		$subQryStr = '';
		$location_type  = '';
		$location_id    = '';
		if (!empty($inputdata['is_admin'])) {
			// echo 'dsdsd';
			$qry01 = $con->query("SELECT * FROM ki_user_locations_info WHERE user_id = " . safe_str($inputdata['user_id']) . " and is_default=1");
			if ($qry01->num_rows) {
				$row0 = $qry01->fetch_assoc();
				$location_type  = $row0['location_type'];
				$location_id    = $row0['location_id'];
			} else {
				$location_type  = $inputdata['location_type'];
				$location_id    = $inputdata['location_id'];
			}
		} else {
			$location_type  = $inputdata['location_type'];
			$location_id    = $inputdata['location_id'];
		}

		$join = "";
		$select = "";
		$where = "";
		$sort_on = "";
		$sort_type = "";
		if (!empty($inputdata['sort_on'])) {
			$sort_on = $inputdata['sort_on'];
		}
		if (!empty($inputdata['sort_type'])) {
			$sort_type = $inputdata['sort_type'];
		}
		if (!empty($inputdata['price_list_id'])) {
			if (empty($inputdata['type'])) {
				// join with ki_price_list_products_info
				$join = " inner join ki_price_list_products_info pip on pip.product_id=PI.product_id and pip.price_list_id='" . safe_str($Encryption->decode($inputdata['price_list_id'])) . "' and pip.delete_flag=0";
				if ($inputdata['sort_on'] == 'created_on') {
					$sort_on = "pip." . $inputdata['sort_on'];
				}
				$select = "pip.*,";
			} else {
				$sort_on = "PI.created_on";
				$where = " and PI.product_id not in(select pip.product_id from ki_price_list_products_info pip where pip.price_list_id='" . safe_str($Encryption->decode($inputdata['price_list_id'])) . "' and pip.delete_flag=0)";
			}
		}

		$subQryStr_type = 'location_type = ' . $location_type;
		$subQryStr_id = 'location_id = ' . $location_id;

		if ($subQryStr_type && $subQryStr_id) {
			$pagg_qry = "SELECT IFNULL(distribution_stock_amount+store_total_stock+production_total_stock,0) as total_stock,at1.distribution_stock_amount,at1.distribution_breakdown," . $select . "
	    PI.*,
	    PP.retail_price, PP.distribution_price, PP.distribution_margin,PP.retail_margin,PP.cost_price,
	    PC.core_range, PC.forecasted_daily_consumption_rate AS fdcr, PC.is_over_stocked, PC.is_SLOB,
	    PQI.stock_on_hand, PQI.desired_stock_level, PQI.override_desired_stock_level ,PQI.reorder_level
	    FROM `ki_products_info` AS PI
	    LEFT JOIN `ki_product_prices_info` AS PP ON PP.product_id = PI.product_id
	    LEFT JOIN `ki_product_consumption_info` AS PC ON PC.product_id = PI.product_id AND PC." . $subQryStr_type . " AND PC." . $subQryStr_id . "
	    LEFT JOIN `ki_product_quantites_info` AS PQI ON PQI.product_id = PI.product_id AND PQI." . $subQryStr_type . " AND PQI." . $subQryStr_id . " " . $join . "
		left join 
		(select sum(stock_on_hand) as distribution_stock_amount,pq.product_id,group_concat('D: ',db.distribution_name,' : ',stock_on_hand) as distribution_breakdown from ki_product_quantites_info pq inner join ki_distribution_branches_info db on db.distribution_branch_id=pq.location_id and db.is_enabled=1 and db.delete_flag=0 where pq.location_type=2 and pq.delete_flag=0 group by pq.product_id) at1 on 
		at1.product_id=PI.product_id
		left join 
		(select sum(stock_on_hand) as store_total_stock,pq.product_id from ki_product_quantites_info pq inner join ki_stores_info db1 on db1.store_id=pq.location_id where  pq.delete_flag=0 and db1.is_enabled=1 and db1.delete_flag=0 and pq.location_type=1 group by pq.product_id) at2 on 
		at2.product_id=PI.product_id
		left join 
		(select sum(stock_on_hand) as production_total_stock,pq.product_id from ki_product_quantites_info pq inner join ki_production_info db2 on db2.production_id=pq.location_id where pq.delete_flag=0 and db2.is_enabled=1 and db2.delete_flag=0 and pq.location_type=3 group by pq.product_id) at3
		on at3.product_id=PI.product_id 
	    WHERE PI.delete_flag = 0 and PI.status!=3 $query $where
	    ORDER BY " . $sort_on . " " . $sort_type;
		} else {
			$pagg_qry = "SELECT IFNULL(distribution_stock_amount+store_total_stock+production_total_stock,0) as total_stock,at1.distribution_stock_amount,at1.distribution_breakdown," . $select . "
	    PI.*,
	    PP.retail_price, PP.distribution_price, PP.distribution_margin,PP.retail_margin,
	    PC.core_range, PC.forecasted_daily_consumption_rate AS fdcr, PC.is_over_stocked, PC.is_SLOB,
	    PQI.stock_on_hand, PQI.desired_stock_level, PQI.reorder_level
	    FROM `ki_products_info` AS PI
	    LEFT JOIN `ki_product_prices_info` AS PP ON PP.product_id = PI.product_id
	    LEFT JOIN `ki_product_consumption_info` AS PC ON PC.product_id = PI.product_id 
	    LEFT JOIN `ki_product_quantites_info` AS PQI ON PQI.product_id = PI.product_id " . $join . "
		left join 
		(select sum(stock_on_hand) as distribution_stock_amount,pq.product_id,group_concat('D: ',db.distribution_name,' : ',stock_on_hand) as distribution_breakdown from ki_product_quantites_info pq inner join ki_distribution_branches_info db on db.distribution_branch_id=pq.location_id and db.is_enabled=1 and db.delete_flag=0 where pq.location_type=2 and pq.delete_flag=0 group by pq.product_id) at1 on 
		at1.product_id=PI.product_id
		left join 
		(select sum(stock_on_hand) as store_total_stock,pq.product_id from ki_product_quantites_info pq inner join ki_stores_info db1 on db1.store_id=pq.location_id where  pq.delete_flag=0 and db1.is_enabled=1 and db1.delete_flag=0 and pq.location_type=1 group by pq.product_id) at2 on 
		at2.product_id=PI.product_id
		left join 
		(select sum(stock_on_hand) as production_total_stock,pq.product_id from ki_product_quantites_info pq inner join ki_production_info db2 on db2.production_id=pq.location_id where pq.delete_flag=0 and db2.is_enabled=1 and db2.delete_flag=0 and pq.location_type=3 group by pq.product_id) at3
		on at3.product_id=PI.product_id 
	    WHERE PI.delete_flag = 0 and PI.status!=3 $query $where
	    ORDER BY " . $sort_on . " " . $sort_type;
		}
		// echo $pagg_qry;
		$pagg_result = $con->query($pagg_qry);
		// 		echo $con->error;
		if (!$pagg_qry) {
			return $con->error;
			die;
		}

		$pagg_count = $pagg_result->num_rows;
		$pagging_list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}

		$data["pagging_list"] = $pagging_list;

		return $data;
	}
	function get_price_list_details($inputdata)
	{
		global $con;
		$pcount_qry = "select * from `ki_price_lists_info` where price_list_id='" . safe_str($inputdata['price_list_id']) . "' and location_id='" . safe_str($inputdata['location_id']) . "' and location_type='" . safe_str($inputdata['location_type']) . "' and delete_flag=0";
		$pcount_result = $con->query($pcount_qry);
		$pcount_row = $pcount_result->fetch_assoc();
		return $pcount_row;
	}
	function check_price_list_name_exists($inputdata)
	{
		global $con;
		$where = "";
		if (!empty($inputdata['price_list_id'])) {
			$where = " and price_list_id!='" . safe_str($inputdata['price_list_id']) . "'";
		}

		$que = "select * from ki_price_lists_info where delete_flag=0 and price_list_name='" . safe_str($inputdata['price_list_name']) . "' and location_id='" . safe_str($inputdata['location_id']) . "' and location_type='" . safe_str($inputdata['location_type']) . "'" . $where;
		$pcount_result = $con->query($que);
		$pcount_row = $pcount_result->fetch_assoc();
		return $pcount_row;
	}
	function get_price_lists_pagging_list($inputdata)
	{
		global $con;
		$data = array(
			"total_records" => 0,
			"total_pages" => 0,
			"pagging_list" => array()
		);

		$table = 'ki_price_lists_info';
		$page_no = $inputdata['page_no'];
		$row_size = $inputdata['row_size'];
		$sort_on = $inputdata['sort_on'];
		$sort_type = $inputdata['sort_type'];
		$query = " and location_id='" . safe_str($inputdata['location_id']) . "' and location_type='" . safe_str($inputdata['location_type']) . "'";

		$pcount_qry = "select count(*) as total_count from `" . safe_str($table) . "` where delete_flag=0 " . $query;
		$pcount_result = $con->query($pcount_qry);
		$pcount_row = $pcount_result->fetch_assoc();
		$total_records = $pcount_row["total_count"];

		@$total_pages = ceil($total_records / $row_size);
		if ($total_pages == 0) {
			$total_pages = 1;
		}
		if ($page_no > $total_pages) {
			$page_no = $total_pages;
		}
		$limit_from = ($row_size * $page_no) - $row_size;

		$pagg_qry = "select * from `" . safe_str($table) . "` where delete_flag=0 " . $query . " order by " . safe_str($sort_on) . " " . safe_str($sort_type) . " LIMIT " . $limit_from . ", " . $row_size;
		$pagg_result = $con->query($pagg_qry);
		$pagg_count = $pagg_result->num_rows;
		$pagging_list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}

		$data["total_records"] = $total_records;
		$data["total_pages"] = $total_pages;
		$data["pagging_list"] = $pagging_list;

		return $data;
	}
	function add_category_products_to_price_list($inputdata)
	{
		global $con;
		$data = array(
			"status" => 0,
			"errors" => array()
		);
		// get category products
		$category_products = send_rest(
			array(
				"function" => "get_list",
				"table" => "ki_products_info",
				"key" => "category_id",
				"value" => $inputdata['category_id'],
				"key_ad" => "status",
				"value_ad" => 1
			)
		);
		if (!empty($category_products['list'])) {
			foreach ($category_products['list'] as $product) {
				// insert in ki_price_list_products_info if not exists 
				$sql = "INSERT INTO ki_price_list_products_info (price_list_id, product_id, created_on)
				SELECT * FROM (SELECT '" . safe_str($inputdata['price_list_id']) . "', '" . $product['product_id'] . "', '" . date('Y-m-d H:i:s') . "') AS tmp
				WHERE NOT EXISTS (
					SELECT price_list_id,product_id FROM ki_price_list_products_info WHERE price_list_id = '" . safe_str($inputdata['price_list_id']) . "' and product_id='" . $product['product_id'] . "'
				) LIMIT 1;";
				$result = $con->query($sql);
				if ($result) {
					// ok
				} else {
					$data['errors'][] = $con->error;
				}
			}
			if (empty($data['errors'])) {
				$data['status'] = 1;
			}
		} else {
			$data['status'] = 2;
		}

		return $data;
	}
	function delete_price_list_rows($inputdata)
	{
		global $con;
		$data = array(
			"status" => 0,
			"errors" => array()
		);
		$d_qry = "delete from `ki_price_list_products_info` where product_id='" . safe_str($inputdata['product_id']) . "' and price_list_id='" . safe_str($inputdata['price_list_id']) . "'";
		$d_result = $con->query($d_qry);
		if ($d_result) {
			$data["status"] = 1;
		} else {
			$data["errors"][] = $con->error;
		}
		return $data;
	}
	function get_price_list_products($inputdata)
	{
		global $con;
		$data = array();
		$join = '';
		if (isset($inputdata['location_type']) && !empty($inputdata['location_type']) && isset($inputdata['location_id']) && !empty($inputdata['location_id'])) {
			$join = " LEFT JOIN `ki_product_quantites_info` PQI ON p.`product_id`=PQI.`product_id` AND PQI.`location_type`='" . $inputdata['location_type'] . "' AND PQI.`location_id`='" . safe_str($inputdata['location_id']) . "' AND PQI.`delete_flag`=0 ";
		}
		$sql = 'select * from ki_products_info p' . $join . ' inner join ki_price_list_products_info pip on pip.product_id=p.product_id and pip.delete_flag=0 inner join ki_price_lists_info pl on pl.price_list_id=pip.price_list_id and pl.price_list_id="' . safe_str($inputdata['price_list_id']) . '" and pl.delete_flag=0 left join ki_product_prices_info pp on pp.product_id=p.product_id and pp.delete_flag=0 where p.delete_flag=0';
		$result = $con->query($sql);
		if ($result->num_rows) {
			$i = 0;
			while ($row = $result->fetch_assoc()) {
				$copies = $row['copies'];
				if (empty($copies)) {
					$copies = 1;
				}
				$copies = $i + $copies;
				$j = $i;
				for ($j = $i; $j < $copies; $j++) {
					$data[$i] = $row;
					$i++;
				}
			}
		}
		return $data;
	}
	function get_time_clock_report_data($inputdata)
	{
		global $con;
		$data = array();
		$query = $rd_query = $tcr_query = "";
		if (!empty($inputdata['from_date']) && !empty($inputdata['to_date'])) {
			$query = " AND TCI.`in_date` between '" . safe_str($inputdata['from_date']) . "' AND '" . safe_str($inputdata['to_date']) . "'";
			$rd_query = " AND RDI.`date` between '" . safe_str($inputdata['from_date']) . "' AND '" . safe_str($inputdata['to_date']) . "'";
			$tcr_query = " AND TCRI.`from_date`='" . safe_str($inputdata['from_date']) . " 00:00:00' AND TCRI.`to_date`='" . safe_str($inputdata['to_date']) . " 23:59:59'";
		} elseif (!empty($inputdata['from_date'])) {
			$query = " AND TCI.`in_date` between '" . safe_str($inputdata['from_date']) . "' AND '" . date('Y-m-d') . "'";
			$rd_query = " AND RDI.`date` between '" . safe_str($inputdata['from_date']) . "' AND '" . date('Y-m-d') . "'";
			$tcr_query = " AND TCRI.`from_date`='" . safe_str($inputdata['from_date']) . " 00:00:00' AND TCRI.`to_date`='" . date('Y-m-d') . " 23:59:59'";
		} elseif (!empty($inputdata['to_date'])) {
			$query = " AND TCI.`in_date`<= '" . safe_str($inputdata['to_date']) . "'";
			$rd_query = " AND RDI.`date`<= '" . safe_str($inputdata['to_date']) . "'";
			$tcr_query = " AND TCRI.`from_date`='0000-00-00 00:00:00' AND TCRI.`to_date`='" . safe_str($inputdata['to_date']) . " 23:59:59'";
		} else {
			$rd_query = " AND RDI.`date`<='" . date('Y-m-d') . "'";
			$tcr_query = " AND TCRI.`from_date`='0000-00-00 00:00:00' AND TCRI.`to_date`='" . date('Y-m-d') . " 23:59:59'";
		}
		$sql = "SELECT 
					AT.*, CONCAT(COALESCE(UI.`first_name`, ''),' ',COALESCE(UI.`last_name`, '')) AS user_name, report_id, COALESCE(is_verified,0) AS is_verified 
				FROM (
					SELECT 
						`time_clock_id`, `roster_data_id`, `user_id`,
						CASE
							WHEN (`in_date`!='' AND `in_date` IS NOT NULL) THEN `in_date` 
							ELSE `out_date` 
						END AS in_date, `time_in`, `actual_time_in`, `time_out`, `actual_time_out`, `created_on`
					FROM 
						`ki_time_clock_info` TCI 
					WHERE 
						TCI.`delete_flag`=0 AND TCI.`location_id`='" . safe_str($inputdata['location_id']) . "' AND TCI.`location_type`='" . safe_str($inputdata['location_type']) . "' " . $query . " AND ((`in_date`!='' AND `in_date` IS NOT NULL) OR (`out_date`!='' AND `out_date` IS NOT NULL)) 
					UNION 
					SELECT 
						0 AS `time_clock_id`, RDI.`roster_data_id`, RDI.`user_id`, RDI.`date` AS in_date, NULL AS time_in, NULL AS actual_time_in, NULL AS time_out, NULL AS actual_time_out, RDI.`created_on` 
					FROM 
						`ki_roster_data_info` RDI 
					INNER JOIN `ki_rosters_info` RI ON 
						RI.`roster_id`=RDI.`roster_id` AND RI.`location_id`='" . safe_str($inputdata['location_id']) . "' AND RI.`location_type`='" . safe_str($inputdata['location_type']) . "' AND RI.`delete_flag`=0 
					WHERE 
						RDI.delete_flag=0 and RDI.`status`!=0 " . $rd_query . " AND (RI.`location_type`, RI.`location_id`, RDI.`user_id`, RDI.`date`) NOT IN (
							SELECT 
								DISTINCT `location_type`, `location_id`, `user_id`, `in_date` 
							FROM 
								`ki_time_clock_info` TCI 
							WHERE 
								TCI.`delete_flag`=0 AND TCI.`location_id`='" . safe_str($inputdata['location_id']) . "' AND TCI.`location_type`='" . safe_str($inputdata['location_type']) . "' " . $query . " AND ((`in_date`!='' AND `in_date` IS NOT NULL) OR (`out_date`!='' AND `out_date` IS NOT NULL))
						)
				) AT 
				INNER JOIN `ki_users_info` UI ON
					UI.`user_id`=AT.`user_id`
				LEFT JOIN `ki_time_clock_report_info` TCRI ON
					AT.`user_id`=TCRI.`user_id` AND TCRI.`delete_flag`=0 AND TCRI.`location_id`='" . safe_str($inputdata['location_id']) . "' AND TCRI.`location_type`='" . safe_str($inputdata['location_type']) . "' " . $tcr_query . "
				ORDER BY 
					CONCAT(COALESCE(UI.`first_name`, ''),' ',COALESCE(UI.`last_name`, '')), AT.in_date DESC, AT.created_on DESC";
		// echo "<pre>";
		$result = $con->query($sql);
		// echo "</pre>";
		if ($result->num_rows) {
			while ($row = $result->fetch_assoc()) {
				$row['p_time_in'] = date("H:i:s", strtotime($row['time_in']));
				$row['p_time_out'] = date("H:i:s", strtotime($row['time_out']));
				$data[$row['user_id']]['report_id'] = $row['report_id'];
				$data[$row['user_id']]['is_verified'] = $row['is_verified'];
				$data[$row['user_id']]['user_name'] = $row['user_name'];
				$data[$row['user_id']]['list'][] = $row;
			}
			// array_multisort($data, SORT_ASC);
		}
		// echo"<pre>";print_r($data);die;
		return $data;
	}
	function get_tech_daily_metrics($inputdata)
	{
		global $con;
		$data = array(
			"recovery_rate" => '',
			"comments" => '',
			"jobs_completed" => '',
			"productivity" => '',
			"utilisation" => '',
		);
		// get public comments 
		$public_comments = 0;
		$sql = 'select IFNULL(count(job_comment_id),0) as public_comments from ki_job_comments_info where created_by="' . safe_str($inputdata['user_id']) . '" and date(created_on)="' . safe_str($inputdata['date_p']) . '" and delete_flag=0 and type=2 group by created_by';
		$result = $con->query($sql);
		if ($result->num_rows) {
			$row = $result->fetch_assoc();
			$public_comments = $row['public_comments'];
		}
		// get private comments 
		$private_comments = 0;
		$sql = 'select IFNULL(count(job_comment_id),0) as private_comments from ki_job_comments_info where created_by="' . safe_str($inputdata['user_id']) . '" and date(created_on)="' . safe_str($inputdata['date_p']) . '" and delete_flag=0 and type=1 group by created_by';
		$result = $con->query($sql);
		if ($result->num_rows) {
			$row = $result->fetch_assoc();
			$private_comments = $row['private_comments'];
		}
		$data['comments'] = $public_comments . '/' . $private_comments;

		// get utilisation 
		// get allocated time 
		$allocated_time = 0;
		$sql = 'select sum(allocated_time) as allocated_time from ki_jobs_info j where j.assigned_tech="' . safe_str($inputdata['user_id']) . '" and j.delete_flag=0 and (date(j.job_tracker_start_time)<="' . safe_str($inputdata['date_p']) . '" and j.job_tracker_start_time is not null and j.job_tracker_start_time!="" and (j.job_tracker_end_time is null or j.job_tracker_end_time=""))';
		$result = $con->query($sql);
		if ($result->num_rows) {
			$row = $result->fetch_assoc();
			$allocated_time = $row['allocated_time'];
		}
		// get rostered time
		$rostered_time = 0;
		$sql = 'select IFNULL(sum(TIMESTAMPDIFF(MINUTE, start_time, finish_time)),0) as rostered_time from ki_roster_data_info rd inner join ki_rosters_info r on r.roster_id=rd.roster_id and r.location_id="' . safe_str($inputdata['location_id']) . '" and r.location_type="' . safe_str($inputdata['location_type']) . '" and r.from_date<=' . safe_str($inputdata['date']) . ' and r.to_date>=' . safe_str($inputdata['date']) . ' and r.delete_flag=0 where rd.day="' . safe_str($inputdata['day']) . '" and user_id="' . safe_str($inputdata['user_id']) . '" and rd.delete_flag=0 group by rd.user_id';
		$result = $con->query($sql);
		if ($result->num_rows) {
			$row = $result->fetch_assoc();
			$rostered_time = $row['rostered_time'];
		}
		if (!empty($rostered_time)) {
			$data['utilisation'] = ($allocated_time / $rostered_time) * 100;
		}

		return $data;
	}
	function get_time_clock_filter_users_list($inputdata)
	{
		global $con;
		$data = array();
		$sql = 'select * from ki_time_clock_info tc inner join ki_users_info u on u.user_id=tc.user_id where tc.location_id="' . safe_str($inputdata['location_id']) . '" and tc.location_type="' . safe_str($inputdata['location_type']) . '" and tc.delete_flag=0 group by u.user_id order by u.first_name,u.last_name';
		$result = $con->query($sql);
		if ($result->num_rows) {
			$i = 0;
			while ($row = $result->fetch_assoc()) {
				$data[$i] = $row;
				$i++;
			}
		}
		return $data;
	}
	function get_time_clock_users_list($inputdata)
	{
		global $con;
		$data = array();
		$user_ids = array();
		$sql = 'select * from ki_users_info u inner join ki_user_locations_info ul on ul.user_id=u.user_id and ul.delete_flag=0 and ul.location_id="' . safe_str($inputdata['location_id']) . '" and ul.location_type="' . safe_str($inputdata['location_type']) . '" where u.is_enabled=1 and u.delete_flag=0 group by u.user_id order by u.first_name, u.last_name';
		$result = $con->query($sql);
		if ($result->num_rows) {
			$i = 0;
			while ($row = $result->fetch_assoc()) {
				$data[$i] = $row;
				$user_ids[$i] = $row['user_id'];
				$i++;
			}
		}
		// print_r($user_ids);
		// check if selected user is in the active users list, if no, that means user is inactive 
		if (!empty($inputdata['user_id'])) {
			if (in_array($inputdata['user_id'], $user_ids)) {
				// ok
			} else {
				$sql = 'select * from ki_users_info where user_id="' . safe_str($inputdata['user_id']) . '" and delete_flag=0';
				$result = $con->query($sql);
				if ($result->num_rows) {
					$row = $result->fetch_assoc();
					$data[$i] = $row;
				}
				// print_r($data);
				// now sort rows by user name 
				# get a list of sort columns and their data to pass to array_multisort
				$sort = array();
				foreach ($data as $k => $v) {
					$sort['last_name'][$k] = $v['last_name'];
					$sort['first_name'][$k] = $v['first_name'];
				}
				array_multisort($sort['first_name'], SORT_ASC, $sort['last_name'], SORT_ASC, $data);
			}
		}
		return $data;
	}
	function get_time_clock_pagging_list($inputdata)
	{
		global $con;
		$data = array(
			"total_records" => 0,
			"total_pages" => 0,
			"pagging_list" => array()
		);

		$page_no = $inputdata['page_no'];
		$row_size = $inputdata['row_size'];
		$sort_on = $inputdata['sort_on'];
		$sort_type = $inputdata['sort_type'];

		$query = '';
		if (!empty($inputdata['user_id'])) {
			$query = ' and a.user_id="' . safe_str($inputdata['user_id']) . '"';
		}

		$pcount_qry = "select count(*) as total_count from `ki_time_clock_info` a where a.delete_flag=0 and a.location_id='" . safe_str($inputdata['location_id']) . "' and a.location_type='" . safe_str($inputdata['location_type']) . "'" . $query;
		$pcount_result = $con->query($pcount_qry);
		$pcount_row = $pcount_result->fetch_assoc();
		$total_records = $pcount_row["total_count"];

		@$total_pages = ceil($total_records / $row_size);
		if ($total_pages == 0) {
			$total_pages = 1;
		}
		if ($page_no > $total_pages) {
			$page_no = $total_pages;
		}
		$limit_from = ($row_size * $page_no) - $row_size;

		$pagg_qry = "select a.*,CONCAT( COALESCE(u.`first_name`, ''), ' ', COALESCE(u.`last_name`, '')) AS user_name from `ki_time_clock_info` a left join ki_users_info u on u.user_id=a.user_id where a.delete_flag=0 and a.location_id='" . safe_str($inputdata['location_id']) . "' and a.location_type='" . safe_str($inputdata['location_type']) . "'" . $query . " order by " . safe_str($sort_on) . " " . safe_str($sort_type) . " LIMIT " . $limit_from . ", " . $row_size;
		$pagg_result = $con->query($pagg_qry);
		$pagg_count = $pagg_result->num_rows;
		$pagging_list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}

		$data["total_records"] = $total_records;
		$data["total_pages"] = $total_pages;
		$data["pagging_list"] = $pagging_list;

		return $data;
	}
	function check_time_clock_entry($inputdata)
	{
		global $con;
		$data = '';
		$sql = 'select * from ki_time_clock_info where location_id="' . safe_str($inputdata['location_id']) . '" and location_type="' . safe_str($inputdata['location_type']) . '" and user_id="' . safe_str($inputdata['user_id']) . '" and delete_flag=0 and (time_in!="" AND time_in!="null" and time_in is not null) and (time_out="" OR time_out is null OR time_out="null") and in_date="' . safe_str($inputdata['in_date']) . '"';
		$result = $con->query($sql);
		if ($result->num_rows) {
			$data = $result->fetch_assoc();
		}
		return $data;
	}
	function check_user_roster_details($inputdata)
	{
		global $con;
		$data = '';
		// Get today day  
		$day = date('N');
		$sql = 'select * from ki_rosters_info r inner join ki_roster_data_info rd on rd.roster_id=r.roster_id and rd.day="' . $day . '" and rd.user_id="' . safe_str($inputdata['user_id']) . '" and rd.delete_flag=0 where r.location_id="' . safe_str($inputdata['location_id']) . '" and r.location_type="' . $inputdata['location_type'] . '" and r.delete_flag=0 and r.from_date<="' . date('Ymd') . '" and "' . date('Ymd') . '" <=r.to_date';
		$result = $con->query($sql);
		if ($result->num_rows) {
			$data = $result->fetch_assoc();
		}
		return $data;
	}
	function get_job_work_to_complete($inputdata)
	{
		global $con;
		$data = array();
		$pagg_qry = "select b.work_to_complete from ki_job_work_to_complete_info a inner join ki_work_to_complete_info b on b.work_to_complete_id=a.work_to_complete_id and b.delete_flag=0 where a.job_id='" . safe_str($inputdata['job_id']) . "' and a.delete_flag=0 ";
		//echo $pagg_qry;
		$pagg_result = $con->query($pagg_qry);
		$pagg_count = $pagg_result->num_rows;
		$list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$data[$i] = $row['work_to_complete'];
				$i++;
			}
		}

		// $data = $list;

		return $data;
	}
	function get_roster_pagging_list($inputdata)
	{
		global $con;
		$data = array(
			"total_records" => 0,
			"total_pages" => 0,
			"pagging_list" => array()
		);

		$page_no = $inputdata['page_no'];
		$row_size = $inputdata['row_size'];
		$sort_on = $inputdata['sort_on'];
		$sort_type = $inputdata['sort_type'];
		$query = '';
		if (!empty($inputdata['search'])) {
			$query .= "and (CONCAT('Roster #',roster_name) Like '%" . safe_str($inputdata['search']) . "%' or date_format(from_date, '%d-%m-%Y') Like '%" . safe_str($inputdata['search']) . "%' or date_format(to_date, '%d-%m-%Y') Like '%" . safe_str($inputdata['search']) . "%')";
		}
		if (!empty($inputdata['is_default'])) {
			$query .= " and is_default = 1";
		}

		$pcount_qry = "select count(*) as total_count from `ki_rosters_info` where delete_flag=0 and location_id =" . safe_str($inputdata['location_id']) . " and location_type =" . safe_str($inputdata['location_type']) . " " . $query;
		$pcount_result = $con->query($pcount_qry);
		$pcount_row = $pcount_result->fetch_assoc();
		$total_records = $pcount_row["total_count"];

		@$total_pages = ceil($total_records / $row_size);
		if ($total_pages == 0) {
			$total_pages = 1;
		}
		if ($page_no > $total_pages) {
			$page_no = $total_pages;
		}
		$limit_from = ($row_size * $page_no) - $row_size;

		$pagg_qry = "select *,(CASE 
		WHEN from_date<='" . date('Y-m-d') . "' and to_date>='" . date('Y-m-d') . "' THEN 'Current' 
		WHEN from_date<'" . date('Y-m-d') . "' and to_date<'" . date('Y-m-d') . "' THEN 'Past' 
		ELSE 'Future' END) as status from `ki_rosters_info` where delete_flag=0 and location_id =" . safe_str($inputdata['location_id']) . " and location_type =" . safe_str($inputdata['location_type']) . " " . $query . " order by " . safe_str($sort_on) . " " . safe_str($sort_type) . " LIMIT " . $limit_from . ", " . $row_size;

		$pagg_result = $con->query($pagg_qry);
		$pagg_count = $pagg_result->num_rows;
		$pagging_list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}

		$data["total_records"] = $total_records;
		$data["total_pages"] = $total_pages;
		$data["pagging_list"] = $pagging_list;

		return $data;
	}

	function check_if_archived($inputdata)
	{
		global $con;
		$sql = "select IFNULL(sum(pq.stock_on_hand),0) as total_stock from ki_product_quantites_info pq inner join ki_products_info p on p.product_id=pq.product_id and p.delete_flag=0 where pq.product_id='" . safe_str($inputdata['product_id']) . "' and pq.delete_flag=0";
		$result = $con->query($sql);
		$row = $result->fetch_assoc();
		return $row['total_stock'];
	}

	function get_total_invoice_amount($inputdata)
	{
		global $con;
		$tender_name = implode(",", END_BALANCE_TENDER_TYPES);
		if (!empty($inputdata['type'])) {
			if ($inputdata['type'] == 1) {
				// cash 
				$tender_name = "'CASH'";
			} elseif ($inputdata['type'] == 2) {
				// eftpos 
				$tender_name = "'EFTPOS'";
			} elseif ($inputdata['type'] == 3) {
				// direct deposit 
				$tender_name = "'Direct Deposit'";
			} elseif ($inputdata['type'] == 4) {
				// pay advantage 
				$tender_name = "'Pay Advantage'";
			} elseif ($inputdata['type'] == 5) {
				// zip pay 
				$tender_name = "'Zip Pay'";
			}
		}
		// check payments
		$sql = "select IFNULL(sum(ip.amount-ip.change),0) as invoice_total from ki_invoice_payment_info ip where ip.location_id='" . safe_str($inputdata['location_id']) . "' and location_type='" . safe_str($inputdata['location_type']) . "' and UPPER(ip.tender_name) in (" . $tender_name . ") and (ip.payment_datetime between '" . safe_str($inputdata['start_datetime']) . "' and '" . safe_str($inputdata['end_datetime']) . "') and `undo_datetime` IS NULL and ip.delete_flag=0";
		$result = $con->query($sql);
		$row = $result->fetch_assoc();
		$payments_total = $row['invoice_total'];
		// check job deposits as well 
		$sql = "select IFNULL(sum(ip.deposit),0) as invoice_deposits_total from ki_job_deposits_info ip where ip.location_id='" . safe_str($inputdata['location_id']) . "' and location_type='" . safe_str($inputdata['location_type']) . "' and ip.negative_flag=0 and UPPER(ip.tender_name) in (" . $tender_name . ") and (ip.created_on between '" . safe_str($inputdata['start_datetime']) . "' and '" . safe_str($inputdata['end_datetime']) . "') and ip.delete_flag=0";
		$result = $con->query($sql);
		$row = $result->fetch_assoc();
		$invoice_deposits_total = $row['invoice_deposits_total'];
		// check estimate deposits as well 
		$sql = "select IFNULL(sum(ip.deposit),0) as estimate_deposits_total from ki_estimate_deposits_info ip where ip.location_id='" . safe_str($inputdata['location_id']) . "' and location_type='" . safe_str($inputdata['location_type']) . "' and ip.negative_flag=0 and UPPER(ip.tender_name) in (" . $tender_name . ") and (ip.created_on between '" . safe_str($inputdata['start_datetime']) . "' and '" . safe_str($inputdata['end_datetime']) . "') and `undo_datetime` IS NULL and ip.delete_flag=0";
		$result = $con->query($sql);
		$row = $result->fetch_assoc();
		$estimate_deposits_total = $row['estimate_deposits_total'];

		return ($payments_total + $invoice_deposits_total + $estimate_deposits_total);
	}

	function get_roster_non_trade_days($inputdata)
	{
		global $con;
		$data = array();
		$sql = "SELECT * FROM `ki_rosters_info` WHERE `roster_id`='" . safe_str($inputdata['roster_id']) . "'";
		$result = $con->query($sql);
		$row = $result->fetch_assoc();
		$from_date = $row['from_date'];
		$to_date = $row['to_date'];
		// check if any date between from and end date is a non trade date for the roster 
		$sql = "SELECT 
					CASE
						WHEN CONCAT(EXTRACT(MONTH FROM `day`),'-',EXTRACT(DAY FROM `day`))='" . date("n-j", strtotime($from_date)) . "' THEN DAYNAME('" . date("Y-m-d", strtotime($from_date)) . "')
						WHEN CONCAT(EXTRACT(MONTH FROM `day`),'-',EXTRACT(DAY FROM `day`))='" . date("n-j", strtotime($from_date . '+1 day')) . "' THEN DAYNAME('" . date("Y-m-d", strtotime($from_date . '+1 day')) . "')
						WHEN CONCAT(EXTRACT(MONTH FROM `day`),'-',EXTRACT(DAY FROM `day`))='" . date("n-j", strtotime($from_date . '+2 day')) . "' THEN DAYNAME('" . date("Y-m-d", strtotime($from_date . '+2 day')) . "')
						WHEN CONCAT(EXTRACT(MONTH FROM `day`),'-',EXTRACT(DAY FROM `day`))='" . date("n-j", strtotime($from_date . '+3 day')) . "' THEN DAYNAME('" . date("Y-m-d", strtotime($from_date . '+3 day')) . "')
						WHEN CONCAT(EXTRACT(MONTH FROM `day`),'-',EXTRACT(DAY FROM `day`))='" . date("n-j", strtotime($from_date . '+4 day')) . "' THEN DAYNAME('" . date("Y-m-d", strtotime($from_date . '+4 day')) . "')
						WHEN CONCAT(EXTRACT(MONTH FROM `day`),'-',EXTRACT(DAY FROM `day`))='" . date("n-j", strtotime($from_date . '+5 day')) . "' THEN DAYNAME('" . date("Y-m-d", strtotime($from_date . '+5 day')) . "')
						WHEN CONCAT(EXTRACT(MONTH FROM `day`),'-',EXTRACT(DAY FROM `day`))='" . date("n-j", strtotime($to_date)) . "' THEN DAYNAME('" . date("Y-m-d", strtotime($to_date)) . "')
					END AS day_name
				FROM 
					`ki_non_trade_days_info` 
				WHERE 
					`location_id`='" . safe_str($inputdata['location_id']) . "' AND `location_type`='" . safe_str($inputdata['location_type']) . "' AND (
						(`day` BETWEEN '" . $from_date . "' AND '" . $to_date . "') OR (`is_recurring`=1 AND CONCAT(EXTRACT(MONTH FROM `day`),'-',EXTRACT(DAY FROM `day`)) IN ('" . date("n-j", strtotime($from_date)) . "', '" . date("n-j", strtotime($from_date . '+1 day')) . "', '" . date("n-j", strtotime($from_date . '+2 day')) . "', '" . date("n-j", strtotime($from_date . '+3 day')) . "', '" . date("n-j", strtotime($from_date . '+4 day')) . "', '" . date("n-j", strtotime($from_date . '+5 day')) . "', '" . date("n-j", strtotime($to_date)) . "'))
					) AND `delete_flag`=0
				UNION
				SELECT 
					CASE `day`
						WHEN 1 THEN 'Monday'
						WHEN 2 THEN 'Tuesday'
						WHEN 3 THEN 'Wednesday'
						WHEN 4 THEN 'Thursday'
						WHEN 5 THEN 'Friday'
						WHEN 6 THEN 'Saturday'
						WHEN 7 THEN 'Sunday'
					END AS day_name
				FROM 
					`ki_non_trade_recurring_days_info` 
				WHERE 
					`location_type`='" . $inputdata['location_type'] . "' AND `location_id`='" . $inputdata['location_id'] . "' AND `is_enabled`=1 AND `delete_flag`=0";
		$result = $con->query($sql);
		if ($result->num_rows) {
			$i = 0;
			while ($row = $result->fetch_assoc()) {
				// get day from the date 
				$data[$i] = $row['day_name'];
				$i++;
			}
		}
		return $data;
	}

	function get_days_user_active_roster($inputdata)
	{
		global $con;
		$data = array(
			"list" => array()
		);
		$where = '';
		if (!empty($inputdata['user_id'])) {
			$where = ' and AB.user_id=' . safe_str($inputdata['user_id']);
		}
		$pagg_qry = "select CD.user_id,CD.first_name,CD.last_name,AB.status,SUM(TO_SECONDS(finish_time) - TO_SECONDS(start_time))/3600 AS weekly_total_hours, GROUP_CONCAT(CASE  WHEN day = 1 THEN concat(date_format(start_time, '%H:%i'),'-',date_format(finish_time, '%H:%i')) END) as day_1,
		GROUP_CONCAT(CASE  WHEN day = 2 THEN concat(date_format(start_time, '%H:%i'),'-',date_format(finish_time, '%H:%i')) END) as day_2, 
		GROUP_CONCAT(CASE  WHEN day = 3 THEN concat(date_format(start_time, '%H:%i'),'-',date_format(finish_time, '%H:%i')) END) as day_3,
		GROUP_CONCAT(CASE  WHEN day = 4 THEN concat(date_format(start_time, '%H:%i'),'-',date_format(finish_time, '%H:%i')) END) as day_4,
		GROUP_CONCAT(CASE  WHEN day = 5 THEN concat(date_format(start_time, '%H:%i'),'-',date_format(finish_time, '%H:%i')) END) as day_5, 
		GROUP_CONCAT(CASE  WHEN day = 6 THEN concat(date_format(start_time, '%H:%i'),'-',date_format(finish_time, '%H:%i')) END) as day_6,
		GROUP_CONCAT(CASE  WHEN day = 7 THEN concat(date_format(start_time, '%H:%i'),'-',date_format(finish_time, '%H:%i')) END) as day_7,
		GROUP_CONCAT(CASE  WHEN day = 1 THEN status END) as day_1_status,
		GROUP_CONCAT(CASE  WHEN day = 2 THEN status END) as day_2_status, 
		GROUP_CONCAT(CASE  WHEN day = 3 THEN status END) as day_3_status,
		GROUP_CONCAT(CASE  WHEN day = 4 THEN status END) as day_4_status,
		GROUP_CONCAT(CASE  WHEN day = 5 THEN status END) as day_5_status, 
		GROUP_CONCAT(CASE  WHEN day = 6 THEN status END) as day_6_status,
		GROUP_CONCAT(CASE  WHEN day = 7 THEN status END) as day_7_status
		from `ki_roster_data_info` AB inner join `ki_users_info` CD on CD.user_id = AB.user_id where AB.delete_flag=0 and roster_id=" . safe_str($inputdata['roster_id']) . $where . "    group by AB.user_id order by CD.first_name";
		$pagg_result = $con->query($pagg_qry);
		$pagg_count = $pagg_result->num_rows;
		$list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$list[$i] = $row;
				$i++;
			}
		}

		$data["list"] = $list;

		return $data;
	}

	function get_location_specific_users_list($inputdata)
	{
		global $con;
		$data = array(
			"list" => array()
		);
		if ($inputdata['location_type'] == 1) {
			$table = "ki_stores_info";
			$field = "store_id";
		} elseif ($inputdata['location_type'] == 2) {
			$table = "ki_distribution_branches_info";
			$field = "distribution_branch_id";
		} elseif ($inputdata['location_type'] == 3) {
			$table = "ki_production_info";
			$field = "production_id";
		}
		$pagg_qry = "select *, CONCAT( COALESCE(u.`first_name`, ''), ' ', COALESCE(u.`last_name`, '') ) as title, u.is_enabled as user_is_enabled from `ki_users_info` u inner join ki_user_locations_info ul on ul.user_id=u.user_id and ul.location_id='" . safe_str($inputdata['location_id']) . "' and ul.location_type='" . safe_str($inputdata['location_type']) . "' and ul.delete_flag=0 inner join " . $table . " t on t." . $field . "=ul.location_id and t.is_enabled=1 and t.delete_flag=0 where u.delete_flag=0 order by title";
		$pagg_result = $con->query($pagg_qry);
		$pagg_count = $pagg_result->num_rows;
		$pagging_list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$pagging_list[$i]['name'] = $row['first_name'] . " " . $row['last_name'];
				$pagging_list[$i]['id'] = $row['user_id'];
				$pagging_list[$i]['user_id'] = $row['user_id'];
				$pagging_list[$i]['user_name'] = $row['title'];
				$pagging_list[$i]['is_enabled'] = $row['user_is_enabled'];
				$i++;
			}
		}
		$data["list"] = $pagging_list;

		return $data;
	}

	function get_selected_category_details($inputdata)
	{
		global $con;

		$pcount_qry = "select * from `ki_categories_info` where category_id='" . safe_str($inputdata['category_id']) . "'";
		$pcount_result = $con->query($pcount_qry);
		$pcount_row = $pcount_result->fetch_assoc();
		return $pcount_row;
		// check if parent or child 
		/* if(empty($pcount_row['parent_category_id'])){
			// means parent itself
			
		 }else{
			// means child, need to check if parents enabled or not 
			// if one of the parents disabled, set is_enabled=0 for returning row 
			$is_child=1;
			$parent_id = $pcount_row['parent_category_id'];
			while($is_child=1){
				$pcount_qry = "select * from `ki_categories_info` where category_id='".$parent_id."'";
				$pcount_result = $con->query($pcount_qry);
				$pcount_row1 = $pcount_result->fetch_assoc();
				// check if enabled 
				$is_enabled = $pcount_row1['is_enabled'];
				if($is_enabled==0){
					$pcount_row['is_enabled']=0;
					return $pcount_row;break;
				}else{
					// check if have parent 
					$parent_id = $pcount_row1['parent_category_id'];
					if(empty($parent_id)){
						$is_child=0;
						return $pcount_row;break;
					}
				}
			}
		} */
	}

	function get_problem_jobs_list($inputdata)
	{
		global $con;
		$data = array(
			"1" => array(),
			"2" => array(),
			"3" => array(),
			"4" => array()
		);

		// get date before 4 days 
		$date = new DateTime();
		$date->modify('-4 days');
		$date_before_4_days = $date->format('Y-m-d');
		// get date before 15 days 
		$date = new DateTime();
		$date->modify('-15 days');
		$date_before_15_days = $date->format('Y-m-d');
		// get date before 10 days 
		$date = new DateTime();
		$date->modify('-10 days');
		$date_before_10_days = $date->format('Y-m-d');
		// get date before 7 days 
		$date = new DateTime();
		$date->modify('-7 days');
		$date_before_7_days = $date->format('Y-m-d');
		$type = array(1, 2, 3, 4);
		$current_date = date('Y-m-d H:i:s');
		foreach ($type as $job_type) {
			$i = 0;
			if ($job_type == 1) {
				// Very Overdue Jobs - These are tickets, projects, call outs, device refurbishments and client check in’s gone past due date more than 48 hours.
				$query = "  TIMESTAMPDIFF(SECOND,at.due_time,'" . date('Y-m-d H:i:s') . "')>=172800 and (at.status!=7 and at.status!=8 and at.status!=9 and at.status!=10 and at.status!=11)";  // 172800 means 2 days i.e. 48 hours 
			} elseif ($job_type == 2) {
				// Overdue Jobs - These are jobs that went more than 24 hours overdue. 
				$query = "  TIMESTAMPDIFF(SECOND,due_time,'" . date('Y-m-d H:i:s') . "')>=86400 and TIMESTAMPDIFF(SECOND,at.due_time,'" . date('Y-m-d H:i:s') . "')<172800 and (at.status!=7 and at.status!=8 and at.status!=9 and at.status!=10 and at.status!=11)";  // 172800 means 2 days i.e. 48 hours and 86400 means 1 day i.e 24 hours 
			} elseif ($job_type == 3) {
				// Very Stale Jobs - These are tickets that have not had any progress for 4 days and are not set to the status “waiting on parts” or “waiting on customer”, projects, device refurbishments or tickets that are set to “waiting on parts” or “waiting on customer” that have not had any progress for 15 days, and client check ins that have not had progress for 10 days. 
				$query = " (
				(at.job_type=1 and (at.status=1 or at.status=2 or at.status=5 or at.status=6) and '" . $current_date . "' >= DATE_ADD(at.last_activity_date, INTERVAL at.stale_hours+24 HOUR))
				or
				(at.job_type=5 and (at.status=3 or at.status=4)  and '" . $current_date . "' >= DATE_ADD(at.last_activity_date, INTERVAL 120 HOUR))
				or 
				(at.job_type!=5 and (at.status=3 or at.status=4)  and '" . $current_date . "' >= DATE_ADD(at.last_activity_date, INTERVAL 264 HOUR))
				)";
			} elseif ($job_type == 4) {
				// Jobs to Invoice or Off Board - These will include any ticket that has had a status of “Completed” or “Invoiced” for more than 7 days.
				$query = "  at.job_type=1 and (at.status=8 or at.status=7) and at.status_last_activity<='" . $date_before_7_days . "'";
			}
			$qry = "select * from(SELECT AB.job_type,AB.due_date as due_time,AB.job_id,AB.status,AB.job_number,CONCAT( COALESCE(CD.`first_name`, ''), ' ', COALESCE(CD.`last_name`, '') ) as assigned_tech_name,AB.status_last_activity,AB.due_date,tt.stale_hours,AB.created_on,AB.job_tracker_end_time,AB.last_activity_date FROM `ki_jobs_info` AB INNER JOIN `ki_users_info` CD ON AB.`assigned_tech` = CD.`user_id` INNER JOIN `ki_customers_info` EF ON AB.`customer_id` = EF.`customer_id` left join ki_ticket_types_info tt on tt.ticket_type_id=AB.ticket_type_id WHERE  AB.home_store_id='" . safe_str($inputdata['store_id']) . "' and AB.home_store_type=1 and AB.`is_cancelled` = 0 and AB.`delete_flag` = 0) at where " . $query . " order by at.created_on";
			$result = $con->query($qry);
			if ($result->num_rows) {
				while ($row = $result->fetch_assoc()) {
					$data[$job_type][$i]['type'] = $job_type;
					$data[$job_type][$i]['job_number'] = $row['job_number'];
					$data[$job_type][$i]['assigned_tech'] = $row['assigned_tech_name'];
					$data[$job_type][$i]['status'] = $row['status'];
					$data[$job_type][$i]['job_id'] = $row['job_id'];
					$i++;
				}
			}
		}

		return $data;
	}

	function get_users_job_list($inputdata)
	{
		global $con;
		$data = array();

		$query = '';
		// show assigned tech jobs for logged in location only since it will become lumsy and complicated to show assigned tech jobs of all locations 
		if (!empty($inputdata['location_id'])) {
			$query .= " and j.home_store_id='" . safe_str($inputdata['location_id']) . "'";
		}
		if (!empty($inputdata['location_type'])) {
			$query .= " and j.home_store_type='" . safe_str($inputdata['location_type']) . "'";
		}
		if (!empty($inputdata['user_id'])) {
			$query .= " and j.assigned_tech='" . safe_str($inputdata['user_id']) . "'";
		}
		if (!empty($inputdata['start_time']) && !empty($inputdata['end_time'])) {
			//$query .= " and ((j.job_tracker_start_time between '".$inputdata['start_time']."' and '".$inputdata['end_time']."') or (j.job_tracker_start_time<'".$inputdata['start_time']."' and j.job_tracker_start_time is not null and j.job_tracker_start_time!='' and (j.job_tracker_end_time is null or j.job_tracker_end_time='')))";
			// consider jobs for present day only 
			$query .= " and ((j.job_tracker_start_time between '" . safe_str($inputdata['start_time']) . "' and '" . safe_str($inputdata['end_time']) . "'))";
		}

		$pagg_qry = "select j.job_id as id,u.user_id as resourceId, CONCAT( '#', j.`job_id`,'\n',SUBSTRING(CONCAT( COALESCE(c.`first_name`, ''), ' ', COALESCE(c.`last_name`, '') ),1,40)) as title,DATE_FORMAT(j.job_tracker_start_time, '%Y-%m-%dT%H:%i:%s') as start,DATE_FORMAT(DATE_ADD(j.job_tracker_start_time, INTERVAL allocated_time MINUTE), '%Y-%m-%dT%H:%i:%s') as end from `ki_users_info` u inner join `ki_jobs_info` j on j.assigned_tech=u.user_id " . $query . " and j.delete_flag=0 left join ki_customers_info c on c.customer_id=j.customer_id where u.delete_flag=0 order by title";
		// echo $pagg_qry;
		$pagg_result = $con->query($pagg_qry);
		$pagg_count = $pagg_result->num_rows;
		$list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$list[$i] = $row;
				$i++;
			}
		}
		$data = $list;

		return $data;
	}

	function get_rostered_users_list($inputdata)
	{
		global $con;
		$data = array();

		$query = '';
		if (!empty($inputdata['location_id'])) {
			$query .= " and j.home_store_id='" . safe_str($inputdata['location_id']) . "'";
		}
		if (!empty($inputdata['location_type'])) {
			$query .= " and j.home_store_type='" . safe_str($inputdata['location_type']) . "'";
		}
		if (!empty($inputdata['user_id']) && !empty($inputdata['user_filter'])) {
			if ($inputdata['user_filter'] == 1) {
				$query .= " and j.user_id='" . safe_str($inputdata['user_id']) . "'";
			}
		}
		if (!empty($inputdata['date'])) {
			// this query will be modified when rosters are made 
			$query .= " and ((date(j.start_time) = '" . safe_str($inputdata['date']) . "') or (date(j.job_tracker_start_time)<='" . safe_str($inputdata['date']) . "' and j.job_tracker_start_time is not null and j.job_tracker_start_time!='' and (j.job_tracker_end_time is null or j.job_tracker_end_time='')))";
		}

		$pagg_qry = "select u.user_id as id, CONCAT( COALESCE(u.`first_name`, ''), ' ', COALESCE(u.`last_name`, '') ) as title from `ki_users_info` u inner join `ki_jobs_info` j on j.assigned_tech=u.user_id " . $query . " and j.delete_flag=0 inner join ki_roster_data_info rd on rd.user_id=j.assigned_tech and rd.day='" . safe_str($inputdata['day']) . "' and rd.delete_flag=0 inner join ki_rosters_info r on r.roster_id=rd.roster_id and r.location_id='" . safe_str($inputdata['location_id']) . "' and r.location_type='" . safe_str($inputdata['location_type']) . "' and r.delete_flag=0 and r.from_date<=" . date('Ymd') . " and r.to_date>=" . date('Ymd') . " where u.delete_flag=0 group by u.user_id order by title";
		// echo $pagg_qry;
		$pagg_result = $con->query($pagg_qry);
		$pagg_count = $pagg_result->num_rows;
		$list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$list[$i] = $row;
				$i++;
			}
		}
		$data = $list;

		return $data;
	}

	function check_if_manager_or_lead_tech($inputdata)
	{
		global $con;
		$where = "";
		// print_r($inputdata);
		if ($inputdata['location_type'] == 1) {
			$table = 'ki_stores_info';
			$key = 'store_id';
			$where = " and (lead_tech_id='" . safe_str($inputdata['user_id']) . "' or store_manager_id='" . safe_str($inputdata['user_id']) . "')";
		} elseif ($inputdata['location_type'] == 2) {
			$table = 'ki_distribution_branches_info';
			$key = 'distribution_branch_id';
			$where = " and manager_id='" . safe_str($inputdata['user_id']) . "'";
		} elseif ($inputdata['location_type'] == 3) {
			$table = 'ki_production_info';
			$key = 'production_id';
			$where = " and manager_id='" . safe_str($inputdata['user_id']) . "'";
		}
		$pcount_qry = "select * from `" . safe_str($table) . "` where `" . safe_str($key) . "`='" . safe_str($inputdata['location_id']) . "' " . $where;
		$pcount_result = $con->query($pcount_qry);
		$pcount_row = $pcount_result->fetch_assoc();
		return $pcount_row;
	}

	function get_jobs_pagging_list($inputdata)
	{
		global $con;
		$data = array(
			"total_records" => 0,
			"total_pages" => 0,
			"pagging_list" => array()
		);
		$page_no = $inputdata['page_no'];
		$row_size = $inputdata['row_size'];
		$sort_on = $inputdata['sort_on'];
		$sort_type = $inputdata['sort_type'];
		$query = ''; $jobArr = array();

		$current_date = date('Y-m-d H:i:s');
		$date = new DateTime();
		$date->modify('+2 days');
		$date_after_2_days = $date->format('Y-m-d');
		// get date after 4 days 
		$date = new DateTime();
		$date->modify('+4 days');
		$date_after_4_days = $date->format('Y-m-d');
		// get date after 4 days 
		$date = new DateTime();
		$date->modify('+10 days');
		$date_after_10_days = $date->format('Y-m-d');
		// get date before 2 days 
		$date = new DateTime();
		$date->modify('-2 days');
		$date_before_2_days = $date->format('Y-m-d');
		// get date before 10 days 
		$date = new DateTime();
		$date->modify('-10 days');
		$date_before_10_days = $date->format('Y-m-d');
		// get date before 4 days 
		$date = new DateTime();
		$date->modify('-4 days');
		$date_before_4_days = $date->format('Y-m-d');

		if (!empty($inputdata['type']) && empty($inputdata['cancelled'])) {
			if ($inputdata['type'] == 2) {
				// get tasks only whose due date is for today
				// $query = " and date(AB.due_date)='".date('Y-m-d')."'";
				$query = " and (AB.status!=7) and  (AB.status!=8) and (AB.status!=9) and (AB.status!=10) and ((AB.due_date between '" . date('Y-m-d') . " 00:00:00' and '" . date('Y-m-d') . " 23:59:59')
				or ((AB.job_type=1 and '" . $current_date . "' between DATE_SUB(AB.due_date, INTERVAL tt.close_to_due_hours HOUR) and AB.due_date)
				or (AB.job_type=2 and date(AB.due_date) between '" . date('Y-m-d') . "' and '" . $date_after_10_days . "')
				or (AB.job_type=3 and date(AB.due_date) between '" . date('Y-m-d') . "' and '" . $date_after_2_days . "')
				or (AB.job_type=6 and date(AB.due_date) between '" . date('Y-m-d') . "' and '" . $date_after_4_days . "')
				or (AB.job_type=5 and date(AB.due_date) between '" . date('Y-m-d') . "' and '" . $date_after_2_days . "')))	";
			} elseif ($inputdata['type'] == 3) {
				// get tickets, projects, call outs, device refurbishments, client check in’s and tasks gone past due date
				$query = " and AB.due_date<'" . date('Y-m-d H:i:s') . "' and (AB.status!=7) and (AB.status!=8) and (AB.status!=9) and (AB.status!=10)";
			} elseif ($inputdata['type'] == 4) {
				// get all tickets due today, any tickets that are not phone and tablet due within two days, any tickets that are not phone, tablet, laptop, or desktop due within four days, projects due within 10 days, call outs due within 2 days, device refurbishments due within four days, client check ins due within two days. 
				// get date after 2 days 
				$query = " and (AB.status!=7) and (AB.status!=8) and (AB.status!=9) and (AB.status!=10) and ((AB.job_type=1 and '" . $current_date . "' between DATE_SUB(AB.due_date, INTERVAL tt.close_to_due_hours HOUR) and AB.due_date)
				or (AB.job_type=2 and date(AB.due_date) between '" . date('Y-m-d') . "' and '" . $date_after_10_days . "')
				or (AB.job_type=3 and date(AB.due_date) between '" . date('Y-m-d') . "' and '" . $date_after_2_days . "')
				or (AB.job_type=6 and date(AB.due_date) between '" . date('Y-m-d') . "' and '" . $date_after_4_days . "')
				or (AB.job_type=5 and date(AB.due_date) between '" . date('Y-m-d') . "' and '" . $date_after_2_days . "')
				)";
			} elseif ($inputdata['type'] == 5) {
				// These are tickets that have not had any progress for 2 days and are not set to the status “waiting on parts” or “waiting on customer”, projects, device refurbishments or tickets that are set to “waiting on parts” or “waiting on customer” that have not had any progress for 10 days, and client check ins that have not had progress for 4 days. 
				// $query=" and ((('".$current_date."' >= DATE_ADD(AB.last_activity_date, INTERVAL tt.stale_hours HOUR)) and AB.job_type=1 and AB.status!=3 and AB.status!=4) or ((AB.job_type=1 or AB.job_type=2 or AB.job_type=6) and (AB.status=3 or AB.status=4) and '".$current_date."' >= DATE_ADD(AB.last_activity_date, INTERVAL tt.stale_hours HOUR)) or (AB.job_type=5 and '".$current_date."' >= DATE_ADD(AB.last_activity_date, INTERVAL tt.stale_hours HOUR)))";
				// new description
				$query = " and (
				(AB.job_type=1 and (AB.status=1 or AB.status=2 or AB.status=5 or AB.status=6) and '" . $current_date . "' >= DATE_ADD(AB.last_activity_date, INTERVAL tt.stale_hours HOUR))
				or
				(AB.job_type=5 and (AB.status=3 or AB.status=4)  and '" . $current_date . "' >= DATE_ADD(AB.last_activity_date, INTERVAL 96 HOUR))
				or 
				(AB.job_type!=5 and (AB.status=3 or AB.status=4)  and '" . $current_date . "' >= DATE_ADD(AB.last_activity_date, INTERVAL 240 HOUR))
				)";
			} elseif ($inputdata['type'] == 6) {
				// get any ticket that has a status of “Completed” or “Invoiced”.
				$query = " and AB.job_type=1 and (AB.status=8 or AB.status=7)";
			} elseif ($inputdata['type'] == 7) {
				// get other jobs 
				$query = " and AB.job_id not in(
					SELECT AB1.job_id FROM `ki_jobs_info` AB1 INNER JOIN `ki_users_info` CD1 ON AB1.`user_id` = CD1.`user_id` LEFT JOIN `ki_customers_info` EF1 ON AB1.`customer_id` = EF1.`customer_id` left join ki_ticket_types_info tt1 on tt1.ticket_type_id=AB1.ticket_type_id where 
					(AB.job_type=4 and date(AB.due_date)='" . date('Y-m-d') . "') 
					or
					(AB.due_date<'" . date('Y-m-d H:i:s') . "' and (AB.status!=9)) 
					or 
					((AB.job_type=1 and '" . $current_date . "' between DATE_SUB(AB.due_date, INTERVAL tt.close_to_due_hours HOUR) and AB.due_date) 
					or (AB.job_type=2 and date(AB.due_date) between '" . date('Y-m-d') . "' and '" . $date_after_10_days . "')
					or (AB.job_type=3 and date(AB.due_date) between '" . date('Y-m-d') . "' and '" . $date_after_2_days . "')
					or (AB.job_type=6 and date(AB.due_date) between '" . date('Y-m-d') . "' and '" . $date_after_4_days . "')
					or (AB.job_type=5 and date(AB.due_date) between '" . date('Y-m-d') . "' and '" . $date_after_2_days . "')
					) 
					or 
					(
					(AB.job_type=1 and (AB.status=1 or AB.status=2 or AB.status=5 or AB.status=6) and '" . $current_date . "' >= DATE_ADD(AB.last_activity_date, INTERVAL tt.stale_hours HOUR))
					or
					(AB.job_type=5 and (AB.status=3 or AB.status=4)  and '" . $current_date . "' >= DATE_ADD(AB.last_activity_date, INTERVAL 96 HOUR))
					or 
					(AB.job_type!=5 and (AB.status=3 or AB.status=4)  and '" . $current_date . "' >= DATE_ADD(AB.last_activity_date, INTERVAL 240 HOUR))
					)
					or 
					(AB.job_type=1 and (AB.status=8 or AB.status=7))
				)";
			}
		}
		$job_view_query = "";

		if (!empty($inputdata['search'])) {
			$query .= " and (AB.job_number like '%" . safe_str($inputdata['search']) . "%' or CONCAT( COALESCE(CD.`first_name`, ''), ' ', COALESCE(CD.`last_name`, '') ) like '%" . safe_str($inputdata['search']) . "%' or CONCAT( COALESCE(EF.`first_name`, ''), ' ', COALESCE(EF.`last_name`, ''), ' ', COALESCE(EF.`business_name`, '') ) like '%" . safe_str($inputdata['search']) . "%' OR REPLACE(REPLACE(EF.`phone`, '-', ''), ' ', '') LIKE '%" . safe_str(str_replace('-', '', $inputdata['search'])) . "%') ";
		}

		if (!empty($inputdata['cancelled'])) {
			$query .= " and AB.is_cancelled='1'";
		} else {
			$query .= " and AB.is_cancelled='0'";

			if (!empty($inputdata['tab']) && $inputdata['tab'] == 1) {
				$query .= " AND AB.`status`!=9 ";
			}

			if (!empty($inputdata['job_type'])) {
				$query .= " and AB.job_type='" . safe_str($inputdata['job_type']) . "'";
			}

			if (!empty($inputdata['status'])) {
				$query .= " and AB.status='" . safe_str($inputdata['status']) . "'";
			}
			if (!empty($inputdata['user_id']) && !empty($inputdata['user_filter'])) {
				if ($inputdata['user_filter'] == 1) {
					$query .= " and (AB.user_id='" . safe_str($inputdata['user_id']) . "' or AB.assigned_tech='" . safe_str($inputdata['user_id']) . "')";
					$job_view_query .= " and JV.user_id='" . safe_str($inputdata['user_id']) . "'";
				} elseif ($inputdata['user_filter'] == 3) {
					// show only that jobs to admin that are specifically for that user only or assigned to the admin 
					//$query .= " and (AB.user_id='".$inputdata['user_id']."' or AB.assigned_tech='".$inputdata['user_id']."')";
				}
			}
		}
		$location='';
		if (!empty($inputdata['location_id'])) {
			$location .= " and AB.home_store_id='" . safe_str($inputdata['location_id']) . "'";
			$job_view_query .= " and JV.location_id='" . safe_str($inputdata['location_id']) . "'";
		}

		if (!empty($inputdata['location_type'])) {
			$location .= " and AB.home_store_type='" . safe_str($inputdata['location_type']) . "'";
			$job_view_query .= " and JV.location_type='" . safe_str($inputdata['location_type']) . "'";
		}


		$pcount_qry = "SELECT COUNT(*) AS total_count FROM `ki_jobs_info` AB LEFT JOIN `ki_users_info` CD ON AB.`assigned_tech` = CD.`user_id` LEFT JOIN `ki_customers_info` EF ON AB.`customer_id` = EF.`customer_id` left join ki_ticket_types_info tt on tt.ticket_type_id=AB.ticket_type_id WHERE AB.`delete_flag` = 0 and AB.status!=11 " . $query . $location;
		$pcount_result = $con->query($pcount_qry);
		$pcount_row = $pcount_result->fetch_assoc();
		$total_records = $pcount_row["total_count"];

		@$total_pages = ceil($total_records / $row_size);
		if ($total_pages == 0) {
			$total_pages = 1;
		}
		if ($page_no > $total_pages) {
			$page_no = $total_pages;
		}
		$limit_from = ($row_size * $page_no) - $row_size;
		$pagg_qry = "SELECT M.model_name,JV.job_view_id,AB.*, CONCAT( COALESCE(CD.`first_name`, ''), ' ', COALESCE(CD.`last_name`, '') ) AS user_name, CONCAT( COALESCE(EF.`first_name`, ''), ' ', COALESCE(EF.`last_name`, ''), ' ', COALESCE(EF.`business_name`, '') ) AS customer_name,
		case AB.status
			when 1 then 'In queue'
			when 2 then 'On bench'
			when 3 then 'Waiting on parts'
			when 4 then 'Waiting on customer'
			when 5 then 'Away'
			when 6 then 'Requires attention'
			when 7 then 'Work complete'
			when 8 then 'Invoiced'
			when 9 then 'Finished'
			when 10 then 'Draft'
		end as p_status,
		case AB.job_type
			when 1 then 'Ticket'
			when 2 then 'Project'
			when 3 then 'Call Out'
			when 4 then 'Task'
			when 5 then 'Client Check In'
			when 6 then 'Device Refurbishment'
		end as p_job_type
		FROM `ki_jobs_info` AB LEFT JOIN `ki_users_info` CD ON AB.`assigned_tech` = CD.`user_id` LEFT JOIN `ki_customers_info` EF ON AB.`customer_id` = EF.`customer_id` left join ki_ticket_types_info tt on tt.ticket_type_id=AB.ticket_type_id left join ki_job_views_info JV on JV.job_id=AB.job_id and JV.job_type=AB.job_type " . $job_view_query . " left join ki_models_info M on M.model_id=AB.model_id WHERE AB.`delete_flag` = 0 and AB.status!=11 " . $query ;
		
		if(!empty($inputdata['search_all'])){
			$qqry = $con->query($pagg_qry." group by AB.job_id order by " . safe_str($sort_on) . " " . safe_str($sort_type)); 
			if($qqry->num_rows) {
				$ii = 0;
				while ($row1 = $qqry->fetch_assoc()) {
					$jobArr[$ii] = $row1;
					$ii++;
				}
			}
		} 
		$pagg_qry .= $location . " group by AB.job_id order by " . safe_str($sort_on) . " " . safe_str($sort_type) . " LIMIT " . $limit_from . ", " . $row_size;
		
		$pagg_result = $con->query($pagg_qry);
		$pagg_count = $pagg_result->num_rows;
		$pagging_list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}
		$data["total_records"] = $total_records;
		$data["total_pages"] = $total_pages;
		$data["pagging_list"] = $pagging_list;
		$data["jobArr"] = $jobArr;
		return $data;
	}

	function get_unviewed_jobs_list($inputdata)
	{
		$data = array(
			"today_tasks" => 0,
			"overdue" => 0,
			"close_to_due" => 0,
			"stale" => 0,
			"jobs_to_invoice_off_board" => 0
		);
		global $con;
		$date = new DateTime();
		$date->modify('+2 days');
		$date_after_2_days = $date->format('Y-m-d');
		// get date after 4 days 
		$date = new DateTime();
		$date->modify('+4 days');
		$date_after_4_days = $date->format('Y-m-d');
		// get date after 4 days 
		$date = new DateTime();
		$date->modify('+10 days');
		$date_after_10_days = $date->format('Y-m-d');
		// get date before 2 days 
		$date = new DateTime();
		$date->modify('-2 days');
		$date_before_2_days = $date->format('Y-m-d');
		// get date before 10 days 
		$date = new DateTime();
		$date->modify('-10 days');
		$date_before_10_days = $date->format('Y-m-d');
		// get date before 4 days 
		$date = new DateTime();
		$date->modify('-4 days');
		$date_before_4_days = $date->format('Y-m-d');
		$current_date = date('Y-m-d H:i:s');

		$query = "";
		$job_view_query = "";
		if (!empty($inputdata['location_id'])) {
			$query .= " and AB.home_store_id='" . safe_str($inputdata['location_id']) . "'";
			$job_view_query .= " and JV.location_id='" . safe_str($inputdata['location_id']) . "'";
		}

		if (!empty($inputdata['location_type'])) {
			$query .= " and AB.home_store_type='" . safe_str($inputdata['location_type']) . "'";
			$job_view_query .= " and JV.location_type='" . safe_str($inputdata['location_type']) . "'";
		}

		if (!empty($inputdata['user_id']) && !empty($inputdata['user_filter'])) {
			if ($inputdata['user_filter'] == 1) {
				$query .= " and (AB.user_id='" . safe_str($inputdata['user_id']) . "' or AB.assigned_tech='" . safe_str($inputdata['user_id']) . "')";
				$job_view_query .= " and JV.user_id='" . safe_str($inputdata['user_id']) . "'";
			} else {
				// can view all jobs based on location
			}
		}
		if (!empty($inputdata['search'])) {
			$query .= " and (AB.job_number like '%" . safe_str($inputdata['search']) . "%' or CONCAT( COALESCE(CD.`first_name`, ''), ' ', COALESCE(CD.`last_name`, '') ) like '%" . safe_str($inputdata['search']) . "%' or CONCAT( COALESCE(EF.`first_name`, ''), ' ', COALESCE(EF.`last_name`, ''), ' ', COALESCE(EF.`business_name`, '') ) like '%" . safe_str($inputdata['search']) . "%')";
		}

		if (!empty($inputdata['job_type'])) {
			$query .= " and AB.job_type='" . safe_str($inputdata['job_type']) . "'";
		}

		if (!empty($inputdata['status'])) {
			$query .= " and AB.status='" . safe_str($inputdata['status']) . "'";
		}
		if (!empty($inputdata['cancelled'])) {
			$query .= " and AB.is_cancelled='1'";
		} else {
			$query .= " and AB.is_cancelled='0'";
		}

		// today_tasks
		$queryy = $query . " and (AB.status!=7) and (AB.status!=8) and (AB.status!=9) and (AB.status!=10) and ((AB.due_date between '" . date('Y-m-d') . " 00:00:00' and '" . date('Y-m-d') . " 23:59:59') or ((AB.job_type=1 and '" . $current_date . "' between DATE_SUB(AB.due_date, INTERVAL tt.close_to_due_hours HOUR) and AB.due_date)
				or (AB.job_type=2 and date(AB.due_date) between '" . date('Y-m-d') . "' and '" . $date_after_10_days . "')
				or (AB.job_type=3 and date(AB.due_date) between '" . date('Y-m-d') . "' and '" . $date_after_2_days . "')
				or (AB.job_type=6 and date(AB.due_date) between '" . date('Y-m-d') . "' and '" . $date_after_4_days . "')
				or (AB.job_type=5 and date(AB.due_date) between '" . date('Y-m-d') . "' and '" . $date_after_2_days . "')))   ";
		$pcount_qry = "SELECT COUNT(*) AS total_count FROM (select AB.*,IFNULL(JV.job_view_id,0) as job_view_id from `ki_jobs_info` AB INNER JOIN `ki_users_info` CD ON AB.`user_id` = CD.`user_id` LEFT JOIN `ki_customers_info` EF ON AB.`customer_id` = EF.`customer_id` left join ki_ticket_types_info tt on tt.ticket_type_id=AB.ticket_type_id left join ki_job_views_info JV on JV.job_id=AB.job_id and JV.job_type=AB.job_type " . $job_view_query . " WHERE AB.`delete_flag` = 0 and AB.status!=11" . $queryy . " group by AB.job_id) at where 1";
		$pcount_result = $con->query($pcount_qry);
		$pcount_row = $pcount_result->fetch_assoc();
		$data['today_tasks'] = $pcount_row["total_count"];

		// overdue 
		$queryy = $query . " and AB.due_date<'" . date('Y-m-d H:i:s') . "' and (AB.status!=7) and (AB.status!=8) and (AB.status!=9) and (AB.status!=10)";
		$pcount_qry = "SELECT COUNT(*) AS total_count FROM (select AB.*,IFNULL(JV.job_view_id,0) as job_view_id from `ki_jobs_info` AB INNER JOIN `ki_users_info` CD ON AB.`user_id` = CD.`user_id` LEFT JOIN `ki_customers_info` EF ON AB.`customer_id` = EF.`customer_id` left join ki_ticket_types_info tt on tt.ticket_type_id=AB.ticket_type_id left join ki_job_views_info JV on JV.job_id=AB.job_id and JV.job_type=AB.job_type " . $job_view_query . " WHERE AB.`delete_flag` = 0 and AB.status!=11" . $queryy . " group by AB.job_id) at where 1";
		$pcount_result = $con->query($pcount_qry);
		$pcount_row = $pcount_result->fetch_assoc();
		$data['overdue'] = $pcount_row["total_count"];

		// close_to_due
		$queryy = $query . " and (AB.status!=7) and (AB.status!=8) and (AB.status!=9) and (AB.status!=10) and ((AB.job_type=1 and '" . $current_date . "' between DATE_SUB(AB.due_date, INTERVAL tt.close_to_due_hours HOUR) and AB.due_date)
				or (AB.job_type=2 and date(AB.due_date) between '" . date('Y-m-d') . "' and '" . $date_after_10_days . "')
				or (AB.job_type=3 and date(AB.due_date) between '" . date('Y-m-d') . "' and '" . $date_after_2_days . "')
				or (AB.job_type=6 and date(AB.due_date) between '" . date('Y-m-d') . "' and '" . $date_after_4_days . "')
				or (AB.job_type=5 and date(AB.due_date) between '" . date('Y-m-d') . "' and '" . $date_after_2_days . "'))";
		$pcount_qry = "SELECT COUNT(*) AS total_count FROM (select AB.*,IFNULL(JV.job_view_id,0) as job_view_id from `ki_jobs_info` AB INNER JOIN `ki_users_info` CD ON AB.`user_id` = CD.`user_id` LEFT JOIN `ki_customers_info` EF ON AB.`customer_id` = EF.`customer_id` left join ki_ticket_types_info tt on tt.ticket_type_id=AB.ticket_type_id left join ki_job_views_info JV on JV.job_id=AB.job_id and JV.job_type=AB.job_type " . $job_view_query . " WHERE AB.`delete_flag` = 0 and AB.status!=11" . $queryy . " group by AB.job_id) at where 1";
		$pcount_result = $con->query($pcount_qry);
		$pcount_row = $pcount_result->fetch_assoc();
		$data['close_to_due'] = $pcount_row["total_count"];

		// stale 
		$queryy = $query . " and (
		(AB.job_type=1 and (AB.status=1 or AB.status=2 or AB.status=5 or AB.status=6) and '" . $current_date . "' >= DATE_ADD(AB.last_activity_date, INTERVAL tt.stale_hours HOUR))
		or
		(AB.job_type=5 and (AB.status=3 or AB.status=4)  and '" . $current_date . "' >= DATE_ADD(AB.last_activity_date, INTERVAL 96 HOUR))
		or 
		(AB.job_type!=5 and (AB.status=3 or AB.status=4)  and '" . $current_date . "' >= DATE_ADD(AB.last_activity_date, INTERVAL 240 HOUR))
		)";
		$pcount_qry = "SELECT COUNT(*) AS total_count FROM (select AB.*,IFNULL(JV.job_view_id,0) as job_view_id from `ki_jobs_info` AB INNER JOIN `ki_users_info` CD ON AB.`user_id` = CD.`user_id` LEFT JOIN `ki_customers_info` EF ON AB.`customer_id` = EF.`customer_id` left join ki_ticket_types_info tt on tt.ticket_type_id=AB.ticket_type_id left join ki_job_views_info JV on JV.job_id=AB.job_id and JV.job_type=AB.job_type " . $job_view_query . " WHERE AB.`delete_flag` = 0 and AB.status!=11" . $queryy . " group by AB.job_id) at where 1";
		$pcount_result = $con->query($pcount_qry);
		$pcount_row = $pcount_result->fetch_assoc();
		$data['stale'] = $pcount_row["total_count"];

		// jobs_to_invoice_off_board
		$queryy = $query . " and AB.job_type=1 and (AB.status=8 or AB.status=7)";
		$pcount_qry = "SELECT COUNT(*) AS total_count FROM (select AB.*,IFNULL(JV.job_view_id,0) as job_view_id from `ki_jobs_info` AB INNER JOIN `ki_users_info` CD ON AB.`user_id` = CD.`user_id` LEFT JOIN `ki_customers_info` EF ON AB.`customer_id` = EF.`customer_id` left join ki_ticket_types_info tt on tt.ticket_type_id=AB.ticket_type_id left join ki_job_views_info JV on JV.job_id=AB.job_id and JV.job_type=AB.job_type " . $job_view_query . " WHERE AB.`delete_flag` = 0 and AB.status!=11" . $queryy . " group by AB.job_id) at where 1";
		$pcount_result = $con->query($pcount_qry);
		$pcount_row = $pcount_result->fetch_assoc();
		$data['jobs_to_invoice_off_board'] = $pcount_row["total_count"];

		return $data;
	}

	function get_location_inventory($inputdata)
	{
		global $con;
		$sql = 'SELECT 
					CASE
						WHEN ' . safe_str($inputdata['location_type']) . '=1 THEN COALESCE(`distribution_price`,0) 
						ELSE COALESCE(`cost_price`,0) 
					END AS cost_price 
				FROM 
					`ki_product_prices_info` 
				WHERE 
					`product_id`="' . safe_str($inputdata['product_id']) . '" AND `delete_flag`=0';
		$result = $con->query($sql);
		$row = $result->fetch_assoc();
		return $row['cost_price'];
	}

	function get_total_stock_amount($inputdata)
	{
		global $con;
		$total_stock = 0;
		$sql = 'select sum(at.total_stock) as total_stock from(select sum(stock_on_hand) as total_stock from ki_product_quantites_info pq inner join ki_distribution_branches_info db on db.distribution_branch_id=pq.location_id where pq.product_id="' . safe_str($inputdata['product_id']) . '" and pq.delete_flag=0 and db.is_enabled=1 and db.delete_flag=0 and pq.location_type=2
		union
		select sum(stock_on_hand) as total_stock from ki_product_quantites_info pq inner join ki_stores_info db1 on db1.store_id=pq.location_id where pq.product_id="' . safe_str($inputdata['product_id']) . '" and pq.delete_flag=0 and db1.is_enabled=1 and db1.delete_flag=0 and pq.location_type=1
		union
		select sum(stock_on_hand) as total_stock from ki_product_quantites_info pq inner join ki_production_info db2 on db2.production_id=pq.location_id where pq.product_id="' . safe_str($inputdata['product_id']) . '" and pq.delete_flag=0 and db2.is_enabled=1 and db2.delete_flag=0 and pq.location_type=3) at';
		$result = $con->query($sql);
		if ($result->num_rows) {
			$row = $result->fetch_assoc();
			$total_stock = $row['total_stock'];
		}
		return $total_stock;
	}

	function get_customer_rating($inputdata)
	{
		global $con;
		$sql = 'select IFNULL(sum(points),0) as customer_rating from ki_customer_feedback_info where customer_id="' . safe_str($inputdata['customer_id']) . '" and delete_flag=0';
		$result = $con->query($sql);
		$row = $result->fetch_assoc();
		return $row['customer_rating'];
	}

	function get_customer_loyalty_points($inputdata)
	{
		global $con;
		$sql = 'select IFNULL(sum(credit),0) as customer_loyalty_points from ki_customer_rewards_info where customer_id="' . safe_str($inputdata['customer_id']) . '" and delete_flag=0 and type=1';
		$result = $con->query($sql);
		$row = $result->fetch_assoc();
		return remove_zeros($row['customer_loyalty_points']);
	}

	function get_multiple_distribution_stock_amount($inputdata)
	{
		global $con;
		$data = array(
			"list" => array()
		);
		$pagging_list = array();
		$sql = 'select db.distribution_name,pq.stock_on_hand from ki_product_quantites_info pq inner join ki_distribution_branches_info db on db.distribution_branch_id=pq.location_id and db.is_enabled=1 and db.delete_flag=0 where pq.product_id="' . safe_str($inputdata['product_id']) . '" and pq.location_type=2 and pq.delete_flag=0';
		$result = $con->query($sql);
		if ($result->num_rows) {
			$i = 0;
			while ($row = $result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}
		$data["list"] = $pagging_list;
		return $data;
	}

	function get_distribution_stock_amount($inputdata)
	{
		global $con;
		$distribution_stock = 0;
		$sql = 'select sum(stock_on_hand) as distribution_stock from ki_product_quantites_info pq inner join ki_distribution_branches_info db on db.distribution_branch_id=pq.location_id and db.is_enabled=1 and db.delete_flag=0 where pq.product_id="' . safe_str($inputdata['product_id']) . '" and pq.location_type=2 and pq.delete_flag=0';
		$result = $con->query($sql);
		if ($result->num_rows) {
			$row = $result->fetch_assoc();
			$distribution_stock = $row['distribution_stock'];
		}
		return $distribution_stock;
	}

	function update_setDefault_pp($inputdata)
	{
		global $con;
		$data = array(
			"status" => 0,
			"errors" => array()
		);
		// $table = $inputdata['table'];
		$up_fields = array();
		$up_qry1 = "UPDATE `ki_product_pictures` SET is_default_img=0 WHERE `product_id`='" . safe_str($inputdata['product_id']) . "'";
		$up_result1 = $con->query($up_qry1);

		$up_qry = "UPDATE `ki_product_pictures` SET is_default_img=1 WHERE `" . safe_str($inputdata['key']) . "` = '" . safe_str($inputdata['value']) . "'";
		$up_result = $con->query($up_qry);
		if ($up_result) {
			$data["status"] = 1;
		} else {
			$data["errors"][] = $con->error;
		}
		return $data;
	}
	// function addded by Prince Garg 3/11/2018  // 

	// Checking if any record exixt in DB or not for a particular table
	function check_if_record_exist($inputdata)
	{
		global $con;
		$table = $inputdata['table'];
		$where = '';

		if (!empty($inputdata['id'])) {
			$where .= " and " . safe_str($inputdata['id_key']) . "!='" . safe_str($inputdata['id']) . "'";
		}

		if (!empty($inputdata['record_value'])) {
			$where .= " and " . safe_str($inputdata['record_key']) . "='" . safe_str($inputdata['record_value']) . "'";
		}

		$pcount_qry = "select * from `" . safe_str($table) . "` where delete_flag=0" . $where;
		$pcount_result = $con->query($pcount_qry);
		$pcount_row = $pcount_result->fetch_assoc();
		return $pcount_row;
	}
	// function addded by Prince Garg 3/11/2018  ends // 
	//Function added 26-10-2018  by Prince Garg
	// Fetching list of saved customers and sending data for pagination
	function get_customers_pagging_list($inputdata)
	{

		global $con;
		$data = array(
			"total_records" => 0,
			"total_pages" => 0,
			"pagging_list" => array()
		);

		$table = 'ki_customers_info';
		$page_no = $inputdata['page_no'];
		$row_size = $inputdata['row_size'];
		$sort_on = $inputdata['sort_on'];
		$sort_type = $inputdata['sort_type'];
		$query = '';
		$inputdata['query'] = mysqli_real_escape_string($con, $inputdata['query']);
		if (!empty($inputdata['contact_status'])) {
			$query .= ' and contact_status =' . safe_str($inputdata['contact_status']);
		}
		if (!empty($inputdata['opportunity_id'])) {
			$query .= ' and opportunity_id =' . safe_str($inputdata['opportunity_id']) . ' and GH.delete_flag = 0';
		}
		if (!empty($inputdata['tag_id'])) {
			$query .= ' and tag_id =' . safe_str($inputdata['tag_id']) . ' and IJ.delete_flag = 0';
		}
		if (!empty($inputdata['vertical_option_id'])) {
			$query .= ' and vertical_option_id =' . safe_str($inputdata['vertical_option_id']) . ' and KL.delete_flag = 0';
		}
		if (!empty($inputdata['query'])) {
			$query .= ' and (CONCAT(COALESCE(`first_name`,"")," ", COALESCE(`last_name`,"")) like "%' . safe_str($inputdata['query']) . '%" or business_name like "%' . safe_str($inputdata['query']) . '%" or AB.email like "%' . safe_str($inputdata['query']) . '%" or AB.mobile_no like "%' . safe_str($inputdata['query']) . '%"  or REPLACE(REPLACE(`phone`, "-", ""), " ", "") like "%' . safe_str($inputdata['query']) . '%"
				or CONCAT(COALESCE(AB.`address`,"")," ", COALESCE(AB.`suburb_town`,"")," ", COALESCE(AB.`state`,"")) like "%' . safe_str($inputdata['query']) . '%")';
		}

		$pcount_qry = "SELECT 
							COUNT(DISTINCT(AB.customer_id)) as total_count 
						FROM 
							`" . safe_str($table) . "` AB 
						LEFT JOIN `ki_invoices_info` CD ON 
							CD.`customer_id` = AB.`customer_id` AND CD.`is_draft`=0 AND CD.`delete_flag` = 0 
						LEFT JOIN `ki_estimates_info` EF ON 
							EF.`customer_id` = AB.`customer_id` AND EF.`delete_flag` = 0 
						LEFT JOIN `ki_customer_opportunities_mapping_info` GH on 
							AB.`customer_id` = GH.`customer_id` AND GH.`delete_flag` = 0 
						LEFT JOIN `ki_customer_tags_mapping_info` IJ ON 
							AB.`customer_id` = IJ.`customer_id` AND IJ.`delete_flag` = 0 
						LEFT JOIN `ki_customer_vertical_mapping_info` KL ON 
							AB.`customer_id` = KL.`customer_id` AND KL.`delete_flag` = 0 
						LEFT JOIN `ki_customer_feedback_info` CF ON 
							AB.`customer_id` = CF.`customer_id` AND CF.`delete_flag` = 0 
						LEFT JOIN (
							SELECT AA.`customer_id`, (COALESCE(credit,0) - COALESCE(debit,0)) AS loyalty_points FROM ( SELECT `customer_id`, COALESCE(SUM(`credit`), 0) AS credit FROM `ki_customer_rewards_info` WHERE `type`=1 AND `negative_flag` = 0 AND `delete_flag` = 0 GROUP BY `customer_id` ) AA LEFT JOIN ( SELECT `customer_id`, COALESCE(SUM(`credit`), 0) AS debit FROM `ki_customer_rewards_info` WHERE `type`=1 AND `negative_flag` = 1 AND `delete_flag` = 0 GROUP BY `customer_id` ) BB ON AA.`customer_id`=BB.`customer_id`
						) CR ON 
							AB.`customer_id` = CR.`customer_id` 
						LEFT JOIN `ki_stores_info` SI ON 
							AB.`home_store_type` = 1 AND AB.`home_store_id` = SI.`store_id` AND SI.`delete_flag` = 0 
						LEFT JOIN `ki_distribution_branches_info` DBI ON 
							AB.`home_store_type` = 2 AND AB.`home_store_id` = DBI.`distribution_branch_id` AND DBI.`delete_flag` = 0 
						LEFT JOIN `ki_production_info` PI ON 
							AB.`home_store_type` = 3 AND AB.`home_store_id` = PI.`production_id` AND PI.`delete_flag` = 0 
						WHERE 
							AB.delete_flag=0" . $query;

		$pcount_result = $con->query($pcount_qry);
		$pcount_row = $pcount_result->fetch_assoc();
		$total_records = $pcount_row["total_count"];

		@$total_pages = ceil($total_records / $row_size);
		if ($total_pages == 0) {
			$total_pages = 1;
		}
		if ($page_no > $total_pages) {
			$page_no = $total_pages;
		}
		$limit_from = ($row_size * $page_no) - $row_size;

		$pagg_qry = "SELECT 
						CASE 
							WHEN COUNT(DISTINCT(CD.`invoice_id`)) = 0 AND COUNT(DISTINCT(EF.`estimate_id`)) = 0 THEN 'Suspect' 
							WHEN COUNT(DISTINCT(CD.`invoice_id`)) = 0 AND COUNT(DISTINCT(EF.`estimate_id`)) > 0 THEN 'Prospect' 
							WHEN COUNT(DISTINCT(CD.`invoice_id`)) = 1 THEN 'Shopper' 
							WHEN COUNT(DISTINCT(CD.`invoice_id`))>1 AND 0>8 AND date_format(CD.`created_on`, '%Y%m%d')<= date_format(AB.`created_on`, '%Y%m%d')+90 THEN 'Advocate' 
							WHEN COUNT(DISTINCT(CD.`invoice_id`))>1 AND 0>6 AND date_format(CD.`created_on`, '%Y%m%d')<= date_format(AB.`created_on`, '%Y%m%d')+90 THEN 'Member' 
							WHEN COUNT(DISTINCT(CD.`invoice_id`))>1 THEN 'Customer' 
							ELSE '-'
						END AS status_formatted, AB.*, CONCAT(COALESCE(AB.`address`,''),' ', COALESCE(AB.`suburb_town`,''),' ', COALESCE(AB.`state`,'')) AS full_address, CONCAT(COALESCE(AB.`first_name`,''),' ', COALESCE(AB.`last_name`,'')) AS name, COALESCE(AVG(`points`),0) AS customer_rating, COALESCE(loyalty_points,0) AS loyalty_points, COALESCE(SI.`store_name`, DBI.`distribution_name`,PI.`production_name`) AS home_store 
					FROM 
						`" . safe_str($table) . "` AB 
					LEFT JOIN `ki_invoices_info` CD ON 
						AB.`customer_id` = CD.`customer_id` AND CD.`is_draft`=0 AND CD.`delete_flag` = 0 
					LEFT JOIN `ki_estimates_info` EF ON 
						AB.`customer_id` = EF.`customer_id` AND EF.`delete_flag` = 0 
					LEFT JOIN `ki_customer_opportunities_mapping_info` GH on 
						AB.`customer_id` = GH.`customer_id` AND GH.`delete_flag` = 0 
					LEFT JOIN `ki_customer_tags_mapping_info` IJ ON 
						AB.`customer_id` = IJ.`customer_id` AND IJ.`delete_flag` = 0 
					LEFT JOIN `ki_customer_vertical_mapping_info` KL ON 
						AB.`customer_id` = KL.`customer_id` AND KL.`delete_flag` = 0 
					LEFT JOIN `ki_customer_feedback_info` CF ON 
						AB.`customer_id` = CF.`customer_id` AND CF.`delete_flag` = 0 
					LEFT JOIN (
						SELECT AA.`customer_id`, (COALESCE(credit,0) - COALESCE(debit,0)) AS loyalty_points FROM ( SELECT `customer_id`, COALESCE(SUM(`credit`), 0) AS credit FROM `ki_customer_rewards_info` WHERE `type`=1 AND `negative_flag` = 0 AND `delete_flag` = 0 GROUP BY `customer_id` ) AA LEFT JOIN ( SELECT `customer_id`, COALESCE(SUM(`credit`), 0) AS debit FROM `ki_customer_rewards_info` WHERE `type`=1 AND `negative_flag` = 1 AND `delete_flag` = 0 GROUP BY `customer_id` ) BB ON AA.`customer_id`=BB.`customer_id`
					) CR ON 
						AB.`customer_id` = CR.`customer_id` 
					LEFT JOIN `ki_stores_info` SI ON 
						AB.`home_store_type` = 1 AND AB.`home_store_id` = SI.`store_id` AND SI.`delete_flag` = 0 
					LEFT JOIN `ki_distribution_branches_info` DBI ON 
						AB.`home_store_type` = 2 AND AB.`home_store_id` = DBI.`distribution_branch_id` AND DBI.`delete_flag` = 0 
					LEFT JOIN `ki_production_info` PI ON 
						AB.`home_store_type` = 3 AND AB.`home_store_id` = PI.`production_id` AND PI.`delete_flag` = 0 
					WHERE
						AB.delete_flag = 0" . $query . " 
					GROUP BY 
						AB.`customer_id` 
					ORDER BY 
						" . safe_str($sort_on) . " " . safe_str($sort_type) . " 
					LIMIT 
						" . $limit_from . ", " . $row_size;

		$pagg_result = $con->query($pagg_qry);
		// die;
		$pagg_count = $pagg_result->num_rows;
		$pagging_list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}

		$data["total_records"] = $total_records;
		$data["total_pages"] = $total_pages;
		$data["pagging_list"] = $pagging_list;

		return $data;
	}

	// For formatting phone number
	function format_phone_number($inputdata)
	{
		$data = array(
			"status" => -1,
			"message" => "",
			"format_number" => ""
		);

		$number = $inputdata['value'];
		$field = 'Phone';
		if (isset($inputdata['field'])) {
			$field = safe_str($inputdata['field']);
		}
		if (strlen($number) != 10) {
			$data['message'] = $field . " Number must be of 10 digits";
		} else {
			if (substr($number, 0, 2) == "02" || substr($number, 0, 2) == "03" || substr($number, 0, 2) == "07" || substr($number, 0, 2) == "08") {
				$formatted_number = substr($number, 0, 2) . "-" . substr($number, 2, 4) . "-" . substr($number, 6);
				$data['status'] = 1;
				$data['format_number'] = $formatted_number;
			} else if (substr($number, 0, 2) == "04" || substr($number, 0, 4) == "1800" || substr($number, 0, 4) == "1300" || substr($number, 0, 4) == "1900") {
				$formatted_number = substr($number, 0, 4) . "-" . substr($number, 4);
				$data['status'] = 1;
				$data['format_number'] = $formatted_number;
			} else {
				$data['message'] = "Initials should be 02, 03, 04, 07, 08, 1800, 1300, 1900 for " . $field . " number";
			}
		}
		return $data;
	}

	// for getting sum of particular column
	function get_sum_of_column($inputdata)
	{
		global $con;

		$data = array(
			"status" => -1,
			"message" => '',
			"sum" => ''
		);

		$table = $inputdata['table'];
		$keys = $inputdata['keys'];
		$values = $inputdata['values'];
		$column = $inputdata['column'];
		$condition_qry = '';
		if ($keys != "" && $values != "") {

			for ($i = 0; $i < count($keys); $i++) {
				$condition_qry .= " and " . safe_str($keys[$i]) . " = '" . safe_str($values[$i]) . "'";
			}
		};

		$sum_query = "select sum(`" . safe_str($column) . "`) as sum from `" . safe_str($table) . "` where delete_flag=0" . $condition_qry . "";

		$sum_query_res = $con->query($sum_query);
		$result = $sum_query_res->fetch_assoc();

		if ($result) {
			$data['status'] = 1;
			$data['sum'] = $result['sum'];
		}

		return $data;
	}
	// Functions Added by Prince Garg ends here
	// COMMON FUNCTIONS

	function check_if_can_access_location($inputdata)
	{
		global $con;
		$join = '';
		// also check if location enabled 
		if ($inputdata['location_type'] == 1) {
			// store 
			$join = ' inner join ki_stores_info s on s.store_id=ul.location_id and s.is_enabled=1 and s.delete_flag=0';
		} elseif ($inputdata['location_type'] == 2) {
			// distribution  
			$join = ' inner join ki_distribution_branches_info s on s.distribution_branch_id=ul.location_id and s.is_enabled=1 and s.delete_flag=0';
		} elseif ($inputdata['location_type'] == 3) {
			// production  
			$join = ' inner join ki_production_info s on s.production_id=ul.location_id and s.is_enabled=1 and s.delete_flag=0';
		}
		$pcount_qry = "select * from ki_user_locations_info ul " . $join . " where ul.user_id='" . safe_str($inputdata['user_id']) . "' and ul.location_type='" . safe_str($inputdata['location_type']) . "' and ul.location_id='" . safe_str($inputdata['location_id']) . "' and ul.delete_flag=0";
		$pcount_result = $con->query($pcount_qry);
		if ($pcount_result->num_rows) {
			return 1;
			// means can access same location 
		} else {
			// check if any default location 
			$pcount_qry = "select ULI.*, COALESCE(SI.`timezone`,DBI.`timezone`,PI.`timezone`) AS timezone from ki_user_locations_info ULI LEFT JOIN `ki_stores_info` SI ON ULI.`location_type`=1 AND ULI.`location_id`=SI.`store_id` LEFT JOIN `ki_distribution_branches_info` DBI ON ULI.`location_type`=2 AND ULI.`location_id`=DBI.`distribution_branch_id` LEFT JOIN `ki_production_info` PI ON ULI.`location_type`=3 AND ULI.`location_id`=PI.`production_id` where ULI.user_id='" . safe_str($inputdata['user_id']) . "' and ULI.delete_flag=0 and ULI.user_location_id not in (select user_location_id from ki_user_locations_info ul where ul.user_id='" . safe_str($inputdata['user_id']) . "' and ul.location_type='" . safe_str($inputdata['location_type']) . "' and ul.location_id='" . safe_str($inputdata['location_id']) . "' and ul.delete_flag=0) order by is_default desc,location_type desc,location_name";
			$pcount_result = $con->query($pcount_qry);
			if ($pcount_result->num_rows) {
				$loc_found = 0;
				$loc_row = array();
				// means redirect to default location 
				while ($row = $pcount_result->fetch_assoc()) {
					// if($loc_found==1){
					// echo 'dssdsd';die;
					// return $loc_row; exit();
					// }else{
					if ($row['location_type'] == 1) {
						$table = 'ki_stores_info';
						$key = 'store_id';
					} elseif ($row['location_type'] == 2) {
						$table = 'ki_distribution_branches_info';
						$key = 'distribution_branch_id';
					} elseif ($row['location_type'] == 3) {
						$table = 'ki_production_info';
						$key = 'production_id';
					}
					$result_p2 = send_rest(array(
						"function" => "check_enability",
						"table" => $table,
						"key" => $key,
						"id" => $row['location_id']
					));
					// echo $result_p2;die;
					if ($result_p2 == 1) {
						$loc_found = 1;
						return $row;
						exit();
					}
					// }
				}
				if ($loc_found == 0) {
					// means no enabled location found 
					return 0;
				}
			} else {
				return 0;
				// means logout user 
			}
		}
	}

	function get_selected_user_skills($inputdata)
	{
		global $con;
		$data = array();
		$i = 0;
		$pagging_list = array();
		$pcount_qry = "select * from ki_user_skills_info where user_id='" . safe_str($inputdata['user_id']) . "' and delete_flag=0";
		$pcount_result = $con->query($pcount_qry);
		if ($pcount_result->num_rows) {
			while ($row = $pcount_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}
		$data = $pagging_list;

		return $data;
	}

	function get_skills_list($inputdata)
	{
		global $con;
		$data = array(
			"list" => array()
		);
		$table1 = 'ki_skills_info';
		$table2 = 'ki_skill_categories_info';
		$where = '';
		if (!empty($inputdata['category_id'])) {
			$where .= "T1. skill_category_id='" . safe_str($inputdata['category_id']) . "' and ";
		}
		// if(!empty($inputdata['skill_name'])){
		// $where.="skill_name like '%".$inputdata['skill_name']."%' and ";
		// }
		$pagg_qry = "select * from `" . safe_str($table1) . "` AS  T1 INNER JOIN  `" . safe_str($table2) . "` AS  T2 ON T1.`skill_category_id`=T2.`skill_category_id` WHERE " . $where . " T1.`is_enabled`=1 AND T2.`is_enabled`=1 AND  T1.`delete_flag`=0 AND  T2.`delete_flag`=0 order by `skill_name`";
		// echo $pagg_qry;
		$pagg_result = $con->query($pagg_qry);
		$pagg_count = $pagg_result->num_rows;
		$list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$list[$i] = $row;
				$i++;
			}
		}

		$data["list"] = $list;

		return $data;
	}

	function get_selected_user_locations($inputdata)
	{
		global $con;
		$data = array();
		$i = 0;
		$pagging_list = array();
		$where = '';
		if (!empty($inputdata['location_id']) && !empty($inputdata['location_type'])) {
			$where = ' user_location_id not in (select user_location_id from ki_user_locations_info where location_id="' . safe_str($inputdata['location_id']) . '" and location_type="' . safe_str($inputdata['location_type']) . '" and delete_flag=0 and user_id="' . safe_str($inputdata['user_id']) . '") and ';
		}
		$sort_on = ' order by location_type_name desc, at.location_name asc';
		if (!empty($inputdata['sort_on'])) {
			$sort_on = ' order by at.location_name asc';
		}
		$pcount_qry = "select * from(select user_location_id,'Store' AS location_type_name,u.location_id,u.location_type,u.is_default,i.store_name as location_name, CONCAT(u.location_type,' ',u.location_id) AS location, i.timezone from ki_user_locations_info u inner join ki_stores_info i on i.store_id=u.location_id and i.is_enabled=1 and i.delete_flag=0 where " . $where . " u.user_id='" . safe_str($inputdata['user_id']) . "' and u.delete_flag=0 and u.location_type=1 
		UNION
		select user_location_id,'Distribution' AS location_type_name,u.location_id,u.location_type,u.is_default,i.distribution_name as location_name,CONCAT(u.location_type,' ',u.location_id) AS location, i.timezone from ki_user_locations_info u inner join ki_distribution_branches_info i on i.distribution_branch_id=u.location_id and i.is_enabled=1 and i.delete_flag=0 where " . $where . " u.user_id='" . safe_str($inputdata['user_id']) . "' and u.delete_flag=0 and u.location_type=2 
		UNION
		select user_location_id,'Production' AS location_type_name,u.location_id,u.location_type,u.is_default,i.production_name as location_name,CONCAT(u.location_type,' ',u.location_id) AS location, i.timezone from ki_user_locations_info u inner join ki_production_info i on i.production_id=u.location_id and i.is_enabled=1 and i.delete_flag=0 where " . $where . " u.user_id='" . safe_str($inputdata['user_id']) . "' and u.delete_flag=0 and u.location_type=3) at " . $sort_on . "
		";
		$pcount_result = $con->query($pcount_qry);
		if ($pcount_result->num_rows) {
			while ($row = $pcount_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}
		$data = $pagging_list;

		return $data;
	}

	function get_stores($inputdata)
	{
		global $con;
		$data = array();
		$i = 0;
		$pagging_list = array();
		$pcount_qry = "select * from ((select 1 as location_type,store_id as id,concat('Store - ',store_name) as name from ki_stores_info where is_enabled=1 and delete_flag=0)) as i order by location_type, name";
		$pcount_result = $con->query($pcount_qry);
		if ($pcount_result->num_rows) {
			while ($row = $pcount_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}
		$data = $pagging_list;

		return $data;
	}

	function get_locations($inputdata)
	{
		global $con;
		$data = array();
		$i = 0;
		$pagging_list = array();
		$pcount_qry = "select * from ((select 1 as location_type,store_id as id,concat('Store - ',store_name) as name from ki_stores_info where is_enabled=1 and delete_flag=0) union (select 2 as location_type,distribution_branch_id as id,concat('Distribution - ',distribution_name) as name from ki_distribution_branches_info where is_enabled=1 and delete_flag=0 ) union (select 3 as location_type,production_id as id,concat('Production - ',production_name) as name from ki_production_info where is_enabled=1 and delete_flag=0 order by production_name)) as i order by location_type, name";
		$pcount_result = $con->query($pcount_qry);
		if ($pcount_result->num_rows) {
			while ($row = $pcount_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}
		$data = $pagging_list;

		return $data;
	}

	function get_front_user_locations($inputdata)
	{
		global $con;
		$data = array();
		$i = 0;
		$pagging_list = array();
		$pcount_qry = "select distinct location_type from ki_user_locations_info where user_id='" . safe_str($inputdata['user_id']) . "' and delete_flag=0";
		$pcount_result = $con->query($pcount_qry);
		if ($pcount_result->num_rows) {
			while ($row = $pcount_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}
		$data = $pagging_list;

		return $data;
	}

	function get_users_pagging_list($inputdata)
	{
		global $con;
		$loc_query = '';
		$user_id = 'user_id';
		if (!empty($inputdata['location_id'])) {
			$Encryption = new Encryption();
			$location = $Encryption->decode($inputdata['location_id']);
			$words = explode(' ', $location);
			$location_id = array_pop($words);
			$location_type = implode('', $words);
			$loc_query = "ULI.location_type=" . $location_type . " and ULI.location_id=" . $location_id . " and";
			$user_id = 'UI.user_id';
		}
		$data = array(
			"total_records" => 0,
			"total_pages" => 0,
			"pagging_list" => array()
		);

		$table = 'ki_users_info';
		$page_no = $inputdata['page_no'];
		$row_size = $inputdata['row_size'];
		$sort_on = $inputdata['sort_on'];
		$sort_type = $inputdata['sort_type'];
		$query = '';
		$inputdata['query'] = mysqli_real_escape_string($con, $inputdata['query']);
		if (!empty($inputdata['query'])) {
			$query = ' and (CONCAT(COALESCE(`first_name`,"")," ", COALESCE(`last_name`,"")) like "%' . safe_str($inputdata['query']) . '%" or email like "%' . safe_str($inputdata['query']) . '%"  or ' . $user_id . ' like "%' . safe_str($inputdata['query']) . '%" or phone_number like "%' . safe_str($inputdata['query']) . '%" or phone_number like "%+' . safe_str($inputdata['query']) . '%")';
		}
		if (!empty($inputdata['location_id'])) {
			$pcount_qry = "select count(*) as total_count from `" . safe_str($table) . "`as UI INNER JOIN `ki_user_locations_info` as ULI ON UI.`user_id`=ULI.`user_id` where " . $loc_query . " UI.delete_flag=0 and UI.is_enabled=" . safe_str($inputdata['enabled_users']) . " " . $query;
		} else {
			$pcount_qry = "select count(*) as total_count from `" . safe_str($table) . "` where delete_flag=0 and is_enabled=" . safe_str($inputdata['enabled_users']) . " " . $query;
		}
		// echo $pcount_qry;
		$pcount_result = $con->query($pcount_qry);
		echo $con->error;
		$pcount_row = $pcount_result->fetch_assoc();
		$total_records = $pcount_row["total_count"];

		@$total_pages = ceil($total_records / $row_size);
		if ($total_pages == 0) {
			$total_pages = 1;
		}
		if ($page_no > $total_pages) {
			$page_no = $total_pages;
		}
		$limit_from = ($row_size * $page_no) - $row_size;

		if (!empty($inputdata['location_id'])) {
			$pagg_qry = "select *,CONCAT(COALESCE(`first_name`,''),' ', COALESCE(`last_name`,'')) AS name from `" . safe_str($table) . "` as UI INNER JOIN `ki_user_locations_info` as ULI ON UI.`user_id`=ULI.`user_id` where " . $loc_query . " UI.delete_flag=0 and UI.is_enabled=" . safe_str($inputdata['enabled_users']) . " " . $query . " order by " . safe_str($sort_on) . " " . safe_str($sort_type) . " LIMIT " . $limit_from . ", " . $row_size;
		} else {
			$pagg_qry = "select *,CONCAT(COALESCE(`first_name`,''),' ', COALESCE(`last_name`,'')) AS name from `" . safe_str($table) . "` where delete_flag=0 and is_enabled=" . safe_str($inputdata['enabled_users']) . " " . $query . " order by " . safe_str($sort_on) . " " . safe_str($sort_type) . " LIMIT " . $limit_from . ", " . $row_size;
		}
		// echo $pagg_qry;

		$pagg_result = $con->query($pagg_qry);
		echo $con->error;
		$pagg_count = $pagg_result->num_rows;
		$pagging_list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}

		$data["total_records"] = $total_records;
		$data["total_pages"] = $total_pages;
		$data["pagging_list"] = $pagging_list;

		return $data;
	}

	function get_user_locations($inputdata)
	{
		global $con;
		$table = 'ki_user_locations_info';
		$pcount_qry = "SELECT ULI.*, COALESCE(SI.`timezone`,DBI.`timezone`,PI.`timezone`) AS timezone from `ki_user_locations_info` ULI LEFT JOIN `ki_stores_info` SI ON ULI.`location_type`=1 AND ULI.`location_id`=SI.`store_id` LEFT JOIN `ki_distribution_branches_info` DBI ON ULI.`location_type`=2 AND ULI.`location_id`=DBI.`distribution_branch_id` LEFT JOIN `ki_production_info` PI ON ULI.`location_type`=3 AND ULI.`location_id`=PI.`production_id` where ULI.`" . safe_str($inputdata['key']) . "`='" . safe_str($inputdata['value']) . "' AND ULI.`delete_flag`=0 and ULI.`is_default`=1";
		$pcount_result = $con->query($pcount_qry);
		if ($pcount_result->num_rows == 0) {
			$pcount_qry = "SELECT  ULI.*, COALESCE(SI.`timezone`,DBI.`timezone`,PI.`timezone`) AS timezone from `ki_user_locations_info` ULI LEFT JOIN `ki_stores_info` SI ON ULI.`location_type`=1 AND ULI.`location_id`=SI.`store_id` LEFT JOIN `ki_distribution_branches_info` DBI ON ULI.`location_type`=2 AND ULI.`location_id`=DBI.`distribution_branch_id` LEFT JOIN `ki_production_info` PI ON ULI.`location_type`=3 AND ULI.`location_id`=PI.`production_id` where ULI.`" . safe_str($inputdata['key']) . "`='" . safe_str($inputdata['value']) . "' AND ULI.delete_flag=0 order by ULI.location_type desc, ULI.location_name asc";
			$pcount_result = $con->query($pcount_qry);
		}
		$pcount_row = $pcount_result->fetch_assoc();
		return $pcount_row;
	}

	function get_users_list($inputdata)
	{
		/* 
		input params - 
			user_id => $user_id 		// optional.
		function is used to get list of all enabled users and also returns user of passed user_id
		output - 
			$ list - array of list of all users.
		*/
		global $con;
		$list = array();
		$query = "`is_enabled`=1";
		if (isset($inputdata['user_id']) && !empty($inputdata['user_id'])) {
			$query = " (`is_enabled`=1 OR `user_id`='" . $inputdata['user_id'] . "') ";
		}
		if (isset($inputdata['users']) && !empty($inputdata['users'])) {
			$query = " (`is_enabled`=1 OR `user_id` IN (" . implode(',', $inputdata['users']) . ")) ";
		}
		$pagg_qry = "SELECT *, CONCAT(COALESCE(`first_name`,''),' ', COALESCE(`last_name`,'')) AS user_name FROM `ki_users_info` WHERE " . $query . " AND `delete_flag`=0 ORDER BY user_name ASC";
		$pagg_result = $con->query($pagg_qry);
		$pagg_count = $pagg_result->num_rows;
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$list[$i] = $row;
				$i++;
			}
		}
		return $list;
	}

	function email_already_exists($inputdata)
	{
		global $con;
		$table = $inputdata['table'];
		//echo "fdsas";
		$where = '';
		if (!empty($inputdata['id'])) {
			$where .= " and " . safe_str($inputdata['id_key']) . "!='" . safe_str($inputdata['id']) . "'";
		}
		if (!empty($inputdata['email'])) {
			$where .= " and " . safe_str($inputdata['email_key']) . "='" . safe_str($inputdata['email']) . "'";
		}
		$pcount_qry = "select * from `" . safe_str($table) . "` where delete_flag=0" . $where;
		//	var_dump($pcount_qry);
		$pcount_result = $con->query($pcount_qry);
		$pcount_row = $pcount_result->fetch_assoc();
		return $pcount_row;
	}

	function get_advanced_list($inputdata)
	{
		global $con;
		$data = array(
			"list" => array()
		);
		$table = $inputdata['table'];
		// echo $table;
		$create_where = "";

		if (!empty($inputdata["key"]) && !empty($inputdata['value'])) {
			// $total_k = count($inputdata["key"])-1; 
			// foreach($inputdata["key"] as $k => $v_k) {
			$create_where .= "`" . safe_str($inputdata["key"]) . "`='" . safe_str($inputdata['value']) . "' and";
			// if($k < $total_k){
			// $create_where .= " and ";
			// }
			// }
		}
		$order_by = "";
		if (!empty($inputdata['sort_on'])) {
			$order_by .= "order by " . safe_str($inputdata['sort_on']) . " ";
			if (!empty($inputdata['sort_type'])) {
				$order_by .= safe_str($inputdata['sort_type']) . " ";
			} else {
				$order_by .= 'ASC ';
			}
		}
		if (!empty($inputdata['limit'])) {
			$order_by .= " limit " . safe_str($inputdata['limit']);
		}
		$pagg_qry = "select * from `" . safe_str($table) . "` where " . $create_where . " delete_flag=0 and is_enabled=1 " . $order_by;
		// echo $pagg_qry;
		$pagg_result = $con->query($pagg_qry);
		$pagg_count = $pagg_result->num_rows;
		$list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$list[$i] = $row;
				$i++;
			}
		}

		$data["list"] = $list;

		return $data;
	}

	function update_user_email($inputdata)
	{
		global $con;
		$data = array(
			"status" => 0,
			"errors" => ''
		);
		$table = $inputdata['table'];
		$up_fields = array();
		foreach ($inputdata['fields_data'] as $ifield => $ival) {
			if (empty($ival) || $ival == "null") {
				$up_fields[] = "`" . safe_str($ifield) . "` = null";
			} else {
				$up_fields[] = "`" . safe_str($ifield) . "` = '" . safe_str($ival) . "'";
			}
		}
		$user_type = 2;
		if (!empty($inputdata['user_type'])) {   // user type set when updating contact details in company details 
			$user_type = safe_str($inputdata['user_type']);   // 3 for company
		}
		$up_qry = "UPDATE `" . safe_str($table) . "` SET " . implode(", ", $up_fields) . " WHERE user_type='" . $user_type . "' and `" . safe_str($inputdata['key']) . "` = '" . safe_str($inputdata['value']) . "'";
		$up_result = $con->query($up_qry);
		if ($up_result) {
			$data["status"] = 1;
		} else {
			$data["errors"] = $con->error;
		}
		return $data;
	}

	function delete_records($inputdata)
	{
		global $con;
		$data = array(
			"status" => 0,
			"errors" => array()
		);
		$table = $inputdata['table'];
		$query = "";
		if (!empty($inputdata["key_ad"]) && !empty($inputdata['value_ad'])) {
			$query = " AND `" . safe_str($inputdata['key_ad']) . "`='" . safe_str($inputdata['value_ad']) . "'";
		}
		$d_qry = "delete from `" . safe_str($table) . "` where `" . safe_str($inputdata['key']) . "`='" . safe_str($inputdata['value']) . "'" . $query;
		$d_result = $con->query($d_qry);
		if ($d_result) {
			$data["status"] = 1;
		} else {
			$data["errors"][] = $con->error;
		}
		return $data;
	}

	function get_list($inputdata)
	{
		global $con;
		$data = array(
			"list" => array()
		);
		$table = $inputdata['table'];
		// echo $table;
		$create_where = "";
		if (!empty($inputdata["key"]) && isset($inputdata['value'])) {
			// $total_k = count($inputdata["key"])-1; 
			// foreach($inputdata["key"] as $k => $v_k) {
			$create_where .= "`" . safe_str($inputdata["key"]) . "`='" . safe_str($inputdata['value']) . "' and";
			// if($k < $total_k){
			// $create_where .= " and ";
			// }
			// }
		}
		if (!empty($inputdata["key_ad"]) && isset($inputdata['value_ad'])) {
			$create_where .= "`" . safe_str($inputdata['key_ad']) . "`='" . safe_str($inputdata['value_ad']) . "' AND ";
		}
		if (!empty($inputdata['enabled']) && $inputdata['enabled'] == 1) {
			$create_where .= "`is_enabled`='1' and";
		}
		$order_by = "";
		if (!empty($inputdata['sort_on'])) {
			$order_by .= "order by " . safe_str($inputdata['sort_on']) . " ";
			if (!empty($inputdata['sort_type'])) {
				$order_by .= safe_str($inputdata['sort_type']) . " ";
			} else {
				$order_by .= 'ASC ';
			}
		}
		if (!empty($inputdata['limit'])) {
			$order_by .= " limit " . safe_str($inputdata['limit']);
		}
		$pagg_qry = "select * from `" . safe_str($table) . "` where " . $create_where . " delete_flag=0 " . $order_by;
		//echo $pagg_qry;
		$pagg_result = $con->query($pagg_qry);
		$pagg_count = $pagg_result->num_rows;
		$list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$list[$i] = $row;
				$i++;
			}
		}

		$data["list"] = $list;

		return $data;
	}

	function get_pagging_list($inputdata)
	{
		global $con;
		$data = array(
			"total_records" => 0,
			"total_pages" => 0,
			"pagging_list" => array()
		);

		$table = $inputdata['table'];
		$page_no = $inputdata['page_no'];
		$row_size = $inputdata['row_size'];
		$sort_on = $inputdata['sort_on'];
		$sort_type = $inputdata['sort_type'];
		$query = '';
		if (!empty($inputdata['query'])) {
			$query = $inputdata['query'];
		}
		if (!empty($inputdata['key']) && !empty($inputdata['value'])) {
			$query .= " and " . $inputdata['key'] . "=" . $inputdata['value'];
		}
		if (!empty($inputdata['key2']) && !empty($inputdata['value2'])) {
			$query .= " and " . $inputdata['key2'] . "=" . $inputdata['value2'];
		}
		if (!empty($inputdata['sort_key']) && !empty($inputdata['sort_value'])) {
			if ($inputdata['sort_key'] == "paid") {
				if ($inputdata['sort_value'] == 1) {
					$query .= " and date_paid IS NOT NULL";
				} else {
					$query .= " and date_paid IS NULL";
				}
			} else {
				$query .= " and " . $inputdata['sort_key'] . "=" . $inputdata['sort_value'];
			}
		}


		$pcount_qry = "select count(*) as total_count from `" . safe_str($table) . "` where delete_flag=0 " . $query;
		$pcount_result = $con->query($pcount_qry);
		$pcount_row = $pcount_result->fetch_assoc();
		$total_records = $pcount_row["total_count"];

		@$total_pages = ceil($total_records / $row_size);
		if ($total_pages == 0) {
			$total_pages = 1;
		}
		if ($page_no > $total_pages) {
			$page_no = $total_pages;
		}
		$limit_from = ($row_size * $page_no) - $row_size;

		$pagg_qry = "select * from `" . safe_str($table) . "` where delete_flag=0 " . $query . " order by " . safe_str($sort_on) . " " . safe_str($sort_type) . " LIMIT " . $limit_from . ", " . $row_size;
		$pagg_result = $con->query($pagg_qry);
		$pagg_count = $pagg_result->num_rows;
		$pagging_list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}

		$data["total_records"] = $total_records;
		$data["total_pages"] = $total_pages;
		$data["pagging_list"] = $pagging_list;

		return $data;
	}

	function get_details($inputdata)
	{
		global $con;
		$table = $inputdata['table'];
		//echo "fdsas";
		$create_where = "";
		if (!empty($inputdata["where"])) {
			$total_k = count($inputdata["where"]) - 1;
			foreach ($inputdata["where"] as $key => $value) {
				$create_where .= "`" . safe_str($key) . "`='" . safe_str($value) . "' and";
			}
		}
		$query = "";
		if (!empty($inputdata["key_ad"]) && !empty($inputdata['value_ad'])) {
			$query .= " AND `" . safe_str($inputdata['key_ad']) . "`='" . safe_str($inputdata['value_ad']) . "'";
		}
		if (!empty($inputdata["key_pg"]) && !empty($inputdata['value_pg'])) {
			$query .= " AND `" . safe_str($inputdata['key_pg']) . "`='" . safe_str($inputdata['value_pg']) . "'";
		}
		$pcount_qry = "select * from `" . safe_str($table) . "` where " . $create_where . " `" . safe_str($inputdata['key']) . "`='" . safe_str($inputdata['value']) . "'" . $query;
		//	var_dump($pcount_qry);
		$pcount_result = $con->query($pcount_qry);
		$pcount_row = $pcount_result->fetch_assoc();
		return $pcount_row;
	}
	function merge_details($inputdata){
      global $con;
       $data = array(
			"status" => 0
		);
      $table = $inputdata['table'];
        $merge_qry="SELECT customer_id from ". safe_str($table) ." WHERE FIND_IN_SET(".safe_str($inputdata['value']).",merged_customers)";
            $result=$con->query($merge_qry);
        if($result==true && $result->num_rows > 0){
               $row = $result->fetch_assoc();
              $data["status"] = 1;
               $data['customer_id']=$row['customer_id'];
            }
      return $data;
   }
    function create_card($inputdata)
	{ 	
		global $con;
		$response = array(
			"status" => 0,
			"errors" => array()
		);
		$User= new User();
		$PayAdvantage = new PayAdvantage();
		$table = $inputdata['table'];
		$in_fields=$inputdata["fields_data"];
		
		if(!empty($in_fields['customer_id'])){
			$customer_details = $this->get_customer_details(array(
				"customer_id" => safe_str($in_fields['customer_id'])
			))['details'];
		}
		if ($_FILES && (empty($_FILES['user_id_photo']['error']) || $_FILES['user_id_photo']['error'] == 0)) {
			$target_dir0 = BASE_DIR . "/uploads/userId-photo/";
			$name0 = pathinfo($_FILES["user_id_photo"]["name"], PATHINFO_FILENAME);
			$extension0 = pathinfo($_FILES["user_id_photo"]["name"], PATHINFO_EXTENSION);
			$name0 = (strlen($name0) > 150) ? substr($name0, 0, 150) : $name0;
			$increment0 = "";
			$rand_name = uniqid(rand());
			$new_name_large = $rand_name . "." . $extension0;
			while (file_exists($target_dir0 . $new_name_large)) {
				$rand_name = uniqid(rand());
				$new_name_large = $rand_name . "." . $extension0;
			}
			$target_file0_large = "/uploads/userId-photo/" . $new_name_large;
			$target_file_large = $target_dir0 . $new_name_large;
			$imageFileType = pathinfo($target_file_large, PATHINFO_EXTENSION);
			$check = getimagesize($_FILES["user_id_photo"]["tmp_name"]);
			if ($check === false) {
				$response["errors"][] = "File is not an image.";
			}
			// Check file size
			if ($_FILES["user_id_photo"]["size"] > MAX_FILE_SIZE) {
				$response["errors"][] = "Sorry, Media Path file size can't be greater than " . bytesToSize(MAX_FILE_SIZE) . ".";
			}
			// Allow certain file formats
			$allowed_extentions = json_decode(IMAGE_EXTENSIONS);
			$allowed_extensions_string = implode(" , ", $allowed_extentions);
			if (!in_array(strtolower($imageFileType), $allowed_extentions)) {
				$response["errors"][] = "Sorry, only " . $allowed_extensions_string . " files are allowed for Media Path file.\n";
			}
			if (!is_dir($target_dir0) && !mkdir($target_dir0, 0777, true)) {
				$response["errors"][] = "Error creating folder $target_dir0";
			}
			if (empty($response["errors"])) {
				$userId_image = $target_file0_large;
				$file = $_FILES["user_id_photo"]["tmp_name"];
				move_uploaded_file($file, $target_file_large);
			}
		} elseif ($_FILES && !empty($_FILES['user_id_photo']['error'])) {
			$response['errors'][] = "Choose an Image.";
		}
		if ($_FILES && (empty($_FILES['credit_card_photo']['error']) || $_FILES['user_id_photo']['error'] == 0)) {
			$target_dir0 = BASE_DIR . "/uploads/credit-card/";
			$name0 = pathinfo($_FILES["credit_card_photo"]["name"], PATHINFO_FILENAME);
			$extension0 = pathinfo($_FILES["credit_card_photo"]["name"], PATHINFO_EXTENSION);
			$name0 = (strlen($name0) > 150) ? substr($name0, 0, 150) : $name0;
			$increment0 = "";
			$rand_name = uniqid(rand());
			$new_name_large = $rand_name . "." . $extension0;
			while (file_exists($target_dir0 . $new_name_large)) {
				$rand_name = uniqid(rand());
				$new_name_large = $rand_name . "." . $extension0;
			}
			$target_file0_large = "/uploads/credit-card/" . $new_name_large;
			$target_file_large = $target_dir0 . $new_name_large;
			$imageFileType = pathinfo($target_file_large, PATHINFO_EXTENSION);
			$check = getimagesize($_FILES["credit_card_photo"]["tmp_name"]);
			if ($check === false) {
				$response["errors"][] = "File is not an image.";
			}
			// Check file size
			if ($_FILES["credit_card_photo"]["size"] > MAX_FILE_SIZE) {
				$response["errors"][] = "Sorry, Media Path file size can't be greater than " . bytesToSize(MAX_FILE_SIZE) . ".";
			}
			// Allow certain file formats
			$allowed_extentions = json_decode(IMAGE_EXTENSIONS);
			$allowed_extensions_string = implode(" , ", $allowed_extentions);
			if (!in_array(strtolower($imageFileType), $allowed_extentions)) {
				$response["errors"][] = "Sorry, only " . $allowed_extensions_string . " files are allowed for Media Path file.\n";
			}
			if (!is_dir($target_dir0) && !mkdir($target_dir0, 0777, true)) {
				$response["errors"][] = "Error creating folder $target_dir0";
			}
			if (empty($response["errors"])) {
				$credit_image = $target_file0_large;
				$file = $_FILES["credit_card_photo"]["tmp_name"];
				move_uploaded_file($file, $target_file_large);
			}
		} elseif ($_FILES && !empty($_FILES['credit_card_photo']['error'])) {
			$response['errors'][] = "Choose an Image.";
		}	
		// print_r($customer_details);
		if(isset($in_fields) && !empty($customer_details) && empty($response['errors'])){
			/* Get access_token for the api integration */
			$result =  $PayAdvantage->callAPI(array(
				"method" => "POST",
				"url" => PAY_ADVANTAGE_URL . "/token",
				"header" => array(
					'Content-Type: application/json',
				),
				"data" => json_encode(array(
					"grant_type" => "password",
					"username" => PAY_ADVANTAGE_USERNAME,
					"password" => PAY_ADVANTAGE_PASSWORD
				))
			));
			$response['errors'] = $result['errors'];
			if (empty($response['errors'])) {
				$access_token = $result['response']['access_token'];
				$customer_data = array(
					"FirstName" => $customer_details['first_name'],
					"LastName" => $customer_details['last_name'],
					"Email" => $customer_details['email'],
					"MobileNumber" => array(
						"CountryISO" => "AU",
						"Number" => str_replace('-', '', output($customer_details['phone']))
					)
				);
				if (!empty($customer_details['business_name'])) {
					$customer_data['Name'] = $customer_details['business_name'];
				}
				if (empty($customer_details['customer_code'])) {
					/* Create the customer in Pay Advantage */
					if (empty($customer_details['business_name'])) {
						$customer_data['IsConsumer'] = true;
					}else {
						$customer_data['IsConsumer'] = false;
					}
					$result = $PayAdvantage->callAPI(array(
						"method" => "POST",
						"url" => PAY_ADVANTAGE_URL . "/customers",
						"header" => array(
							'Content-Type: application/json',
							'Authorization: Bearer ' . $access_token
						),
						"data" => json_encode($customer_data)
					));
					//print_r($result);
					if (empty($response['errors'])) {
						$customer_code = $result['response']['Code'];
						// $customer_code = "Y1pJNFVKcWZabUlZaytKWCtzdEF6QT09";die;
						$sql = "UPDATE `ki_customers_info` SET `customer_code`='" . safe_str(encode_entity($customer_code)) . "' WHERE `customer_id`='" . safe_str($customer_details['customer_id']) . "' AND `delete_flag`=0";
						if (!($con->query($sql))) {
							$response['errors'][] = "Something went wrong. Please try again later.";
						}
					}
				} else {
					/* Update the customer in Pay Advantage */
					$customer_code = decode_entity($customer_details['customer_code']);
					// $customer_code = decode_entity("Y1pJNFVKcWZabUlZaytKWCtzdEF6QT09");
					$result = $PayAdvantage->callAPI(array(
						"method" => "PUT",
						"url" => PAY_ADVANTAGE_URL . "/customers/" . $customer_code,
						"header" => array(
							'Content-Type: application/json',
							'Authorization: Bearer ' . $access_token
						),
						"data" => json_encode($customer_data)
					));
					$response['errors'] = $result['errors'];
				}
					// duplicacy insertion validation check
					if (empty($response['errors'])){
						$get_card_list= $User->get_card_list(array(
							"customer_id" => safe_str($in_fields['customer_id'])
						)); 
						// print_r($get_card_list);
						if (!empty($get_card_list['data']['pagging_list']) && $get_card_list['status'] == 1) {
							foreach ($get_card_list['data']['pagging_list'] as $card) {
								if(!empty($card['card_code'])){
									$Payadvantage_card = $this->payadvantage_card(array(
										"card_code" => $card['card_code']
									));
									if(empty($Payadvantage_card['errors']) && isset($Payadvantage_card)){
										$hashed_card1= substr($Payadvantage_card['data']['DisplayHash'],-4);
										//$card_type1=$Payadvantage_card['data']['CardType'];
										$card_holder1= $Payadvantage_card['data']['CardHolder'];
										$expiry_date1= $Payadvantage_card['data']['Expiry'];
										// my new card
										$hashed_card2= substr(decode_entity($in_fields['card_number']),-4);
										//$card_type2=decode_entity($in_fields['card_type']);
										$card_holder2=decode_entity($in_fields['cardholder_name']);
										$expiry_Date2= decode_entity($in_fields['expiry_date']);
										$date = str_replace('-"', '/', $expiry_Date2);  
										$expiry_date2 = date("m/Y", strtotime($date)); 
										if($hashed_card1==$hashed_card2  && $card_holder1==$card_holder2 && $expiry_date1==$expiry_date2){
											$response['errors'][] = "Card already Exist.";
											break;
										}
									}
								}
							}
						}
					}
				if (empty($response['errors'])) {
					/* create card */
					$expiry_date = decode_entity($in_fields['expiry_date']);
					$month = date("n", strtotime($expiry_date));
					$year = date("Y", strtotime($expiry_date));
					$card_data = array(
						"CardHolder" => decode_entity($in_fields['cardholder_name']),
						"CardNumber" => decode_entity($in_fields['card_number']),
						"CVN" => $in_fields['cvv_no'],
						"ExpiryMonth" => $month,
						"ExpiryYear" => $year,
						"Customer" => array(
							"Code" => $customer_code
						)
					);
					/* Create card in pay advantage account. */
					// echo $access_token;
					// echo json_encode($card_data);
					$result1 = $PayAdvantage->callAPI(array(
						"method" => "POST",
						"url" => PAY_ADVANTAGE_URL . "/credit_cards",
						"header" => array(
							'Content-Type: application/json',
							'Authorization: Bearer ' . $access_token
						),
						"data" => json_encode($card_data)
					));
					// print_r($card_data);
					// print_r($result1);
					// $card_code = "Y1pJNFVKcWZabUlZaytKWCtzdEF6QT09";
					$response['errors'] = $result1['errors'];
					// print_r($response['errors']);
					if (empty($response['errors']) && !empty($result1['response']['Code'])) {
						$card_code = $result1['response']['Code'];
						// echo $card_code;
						//insert card_code into db.
						$In_sql = "INSERT INTO ki_saved_cards_info (customer_id, card_code, is_enabled, is_primary, created_on, delete_flag,user_id_photo,credit_card_photo) VALUES ('".safe_str($in_fields['customer_id'])."', '".safe_str(encode_entity($card_code))."', '".safe_str($in_fields['is_enabled'])."', '".safe_str($in_fields['is_primary'])."', '".safe_str($in_fields["created_on"])."','".safe_str($in_fields['delete_flag'])."','".safe_str($userId_image)."','".safe_str($credit_image)."')";
					
						$in_result = $con->query($In_sql);
						
						if($in_result){
							$response['status']=1;
						}else{
							$response['errors'][]="Something went wrong"; 
						}
					}else{
						$response['errors'][]="Something went wrong"; 
					}
					
					//send mail to store manager 
					if(empty($response['errors']) && $response['status']==1){
						$customer_id=$customer_details['customer_id'];
						$store_manager=$this->get_store_manager(array(
						"customer_id"=>$customer_id
						));
						 if(isset($store_manager['data']) && $store_manager['status']==1){
							$manager_mail=$store_manager['data']['email'];
							$manager_name=$store_manager['data']['first_name'];
							$cus_profile_link=SITE_URL."/management/customers/customers/edit-customer.php/?customer_id=".$customer_id;
							$msg1="<br><p>Hi ".$manager_name.",</p>
							<p>Please be advised that <a href='".$cus_profile_link."'".">created credit card</p>
							<p>Regards</p>
							<p>Kia</p> ";
							 
							$to1=$manager_mail;
						
							$sub1="Card Verification";
						
							$mail1= p_mail($to1, $sub1, $msg1, '', '', '', '','','', 0);
					
						}
					}
				}
			}
		}
		return $response;
		// foreach ($inputdata['fields_data'] as $field_key => $field_data) {
			// $in_fields[safe_str($field_key)] = "'" . safe_str($field_data) . "'";
		// }
		// $up_qry = "INSERT INTO `" . safe_str($table) . "` (`" . implode("`, `", array_keys($in_fields)) . "`) VALUES (" . implode(", ", $in_fields) . ")";
		// $up_result = $con->query($up_qry);
		// // print_r($up_result);
		// if ($up_result) {
			// $data["status"] = 1;
			// $data["id"] = $con->insert_id;
		// } else {
			// $data["errors"][] = $con->error;
		// }
		// return $data;
	}
	function get_store_manager($inputdata){
	// function is used for getting store manager email.
	// inputfield $customer_id
	global $con;
	$response=array(
	"status"=>0,
	"errors"=>array(),
	"data"=>array()
	);
	if(isset($inputdata)){
		$customer_id=$inputdata['customer_id'];
		$sql="SELECT ki_users_info.email,ki_users_info.first_name FROM `ki_customers_info`INNER JOIN ki_stores_info ON ki_customers_info.home_store_id=ki_stores_info.store_id INNER JOIN ki_users_info ON ki_users_info.user_id=ki_stores_info.store_manager_id AND ki_customers_info.customer_id=".safe_str($customer_id);
		$result=$con->query($sql);
		if($result){
			$row= $result->fetch_assoc();
			$response['data']=$row;
		}
		else{
			$response['errors']="Something went wrong";
		}
	}
	if(empty($response['errors'])){
		$response['status']=1;
	}
	return $response;
}
	function payadvantage_card($inputdata){
		// function is used for getting card details from payadvantage. 
		// input $card_code
		//echo "<pre>"; print_r($inputdata);
		global $con;
		$response = array(
			"status" => 0,
			"errors" => array(),
			"data"=>array()
		);
		$PayAdvantage = new PayAdvantage();
		if(isset($inputdata) && !empty($inputdata['card_code'])){
			$card_code= decode_entity($inputdata['card_code']);
			if(isset($card_code)){
				/* Get access_token for the api integration */
				$result =  $PayAdvantage->callAPI(array(
					"method" => "POST",
					"url" => PAY_ADVANTAGE_URL . "/token",
					"header" => array(
						'Content-Type: application/json',
					),
					"data" => json_encode(array(
						"grant_type" => "password",
						"username" => PAY_ADVANTAGE_USERNAME,
						"password" => PAY_ADVANTAGE_PASSWORD
					))
				));
				$response['errors'] = $result['errors'];
				if (empty($response['errors'])) {
					$access_token = $result['response']['access_token'];
					//fetch the card from payadvantage
						$result = $PayAdvantage->callAPI(array(
						"method" => "GET",
						"url" => PAY_ADVANTAGE_URL . "/credit_cards/" . $card_code,
						"header" => array(
							'Authorization: Bearer ' . $access_token
						)
					));
					$response['errors'] = $result['errors'];
					if (empty($response['errors'])) {
						$response['status']=1;
						$response['data']= $result['response'];
					}
				}
			}
		}
		return $response;
	}
	
	function create($inputdata)
	{
		global $con;
		$data = array(
			"status" => 0,
			"errors" => array()
		);
		$table = $inputdata['table'];
		$in_fields = array();
		foreach ($inputdata['fields_data'] as $field_key => $field_data) {
			$in_fields[safe_str($field_key)] = "'" . safe_str($field_data) . "'";
		}
		$up_qry = "INSERT INTO `" . safe_str($table) . "` (`" . implode("`, `", array_keys($in_fields)) . "`) VALUES (" . implode(", ", $in_fields) . ")";
		$up_result = $con->query($up_qry);
		// print_r($up_result);
		if ($up_result) {
			$data["status"] = 1;
			$data["id"] = $con->insert_id;
		} else {
			$data["errors"][] = $con->error;
		}
		return $data;
	}

	function update($inputdata)
	{
		global $con;
		$data = array(
			"status" => 0,
			"errors" => array()
		);
		$table = $inputdata['table'];
		$up_fields = array();
		$query = "";
		if (!empty($inputdata["key_ad"]) && !empty($inputdata['value_ad'])) {
			$query = " AND `" . safe_str($inputdata['key_ad']) . "`='" . safe_str($inputdata['value_ad']) . "'";
		}
		if (!empty($inputdata["not_key"]) && !empty($inputdata['not_value'])) {
			$query = " AND `" . safe_str($inputdata['not_key']) . "`!='" . safe_str($inputdata['not_value']) . "'";
		}
		foreach ($inputdata['fields_data'] as $ifield => $ival) {
			if (empty($ival) || $ival == "null") {
				$up_fields[] = "`" . safe_str($ifield) . "` = null";
			} else {
				$up_fields[] = "`" . safe_str($ifield) . "` = '" . safe_str($ival) . "'";
			}
		}
		$up_qry = "UPDATE `" . safe_str($table) . "` SET " . implode(", ", $up_fields) . " WHERE `" . safe_str($inputdata['key']) . "` = '" . safe_str($inputdata['value']) . "'" . $query;
		$up_result = $con->query($up_qry);
		if ($up_result) {
			$data["status"] = 1;
		} else {
			$data["errors"][] = $con->error;
		}
		return $data;
	}

	function delete_rows($inputdata)
	{
		global $con;
		$data = array(
			"status" => 0,
			"errors" => array()
		);
		$table = $inputdata['table'];
		$up_fields = array();
		foreach ($inputdata['values'] as $ival) {
			$up_fields[] = "'" . safe_str($ival) . "'";
		}
		$d_qry = "update `" . safe_str($table) . "` set `delete_flag`=1 where `" . safe_str($inputdata['key']) . "` in (" . implode(",", $up_fields) . ")";
		$d_result = $con->query($d_qry);
		if ($d_result) {
			$data["status"] = 1;
		} else {
			$data["errors"][] = $con->error;
		}
		return $data;
	}

	// CUSTOM FUNCTIONS

	function get_specific_detail_order($inputdata)
	{
		global $con;
		$table = $inputdata['table'];
		$pcount_qry = "select " . safe_str($inputdata['column_name']) . " from `" . safe_str($table) . "` where `" . safe_str($inputdata['key']) . "`='" . safe_str($inputdata['value']) . "' ORDER BY " . safe_str($inputdata['order_by']) . "";
		// return $pcount_qry;
		$pcount_result = $con->query($pcount_qry);
		$i = 0;
		while ($pcount_row = $pcount_result->fetch_assoc()) {
			$values[$i] = $pcount_row;
			$i++;
		}
		//print_r($values);
		return $values;
	}

	function get_specific_detail($inputdata)
	{
		global $con;
		$table = $inputdata['table'];
		$create_where = '';
		if (!empty($inputdata["key"]) && isset($inputdata['value'])) {
			$create_where .= "`" . safe_str($inputdata["key"]) . "`='" . safe_str($inputdata['value']) . "' and";
		}
		$pcount_qry = "select " . safe_str($inputdata['column_name']) . " from `" . safe_str($table) . "` where " . $create_where . " delete_flag=0 ";
		// return $pcount_qry;
		$pcount_result = $con->query($pcount_qry);
		$i = 0;
		while ($pcount_row = $pcount_result->fetch_assoc()) {
			$values[$i] = $pcount_row;
			$i++;
		}
		//print_r($values);
		return $values;
	}

	function get_details_login($inputdata)
	{
		global $con;
		$table = $inputdata['table'];
		$pcount_qry = "select * from `" . safe_str($table) . "` where `" . safe_str($inputdata['key']) . "`='" . safe_str($inputdata['value']) . "' AND delete_flag=0";
		$pcount_result = $con->query($pcount_qry);
		$pcount_row = $pcount_result->fetch_assoc();
		return $pcount_row;
	}

	function check_if_email_exists($inputdata)
	{
		global $con;
		$table = $inputdata['table'];
		$pcount_qry = "select * from `" . safe_str($table) . "` where delete_flag =0 and is_enabled=1 and `email_address`='" . safe_str($inputdata['email']) . "'";
		$pcount_result = $con->query($pcount_qry);
		$pcount_row = $pcount_result->fetch_assoc();
		return $pcount_row;
	}

	function check_if_meta_exists($inputdata)
	{
		global $con;
		$table = $inputdata['table'];
		$pcount_qry = "select * from `" . safe_str($table) . "` where delete_flag =0 and meta_id=" . safe_str($inputdata['meta_id']);
		$pcount_result = $con->query($pcount_qry);
		$pcount_row = $pcount_result->fetch_assoc();
		return $pcount_row;
	}

	function meta_update($inputdata)
	{
		global $con;
		$data = array(
			"status" => 0,
			"errors" => array()
		);
		$table = $inputdata['table'];
		$up_fields = array();
		foreach ($inputdata['fields_data'] as $ifield => $ival) {
			$up_fields[] = "`" . safe_str($ifield) . "` = '" . safe_str($ival) . "'";
		}
		$up_qry = "UPDATE `" . safe_str($table) . "` SET " . implode(", ", $up_fields) . " WHERE `" . safe_str($inputdata['key']) . "` = '" . safe_str($inputdata['value']) . "'";
		$up_result = $con->query($up_qry);
		if ($up_result) {
			$data["status"] = 1;
		} else {
			$data["errors"][] = $con->error;
		}
		return $data;
	}

	// DEEPSHIKHA code START.....

	function get_user_skills_pagging_list($inputdata)
	{
		global $con;
		$data = array(
			"total_records" => 0,
			"total_pages" => 0,
			"pagging_list" => array()
		);

		$table1 = 'ki_skills_info';
		$table2 = 'ki_skill_categories_info';
		$page_no = $inputdata['page_no'];
		$row_size = $inputdata['row_size'];
		$sort_on = $inputdata['sort_on'];
		$sort_type = $inputdata['sort_type'];
		$query = '';
		if (!empty($inputdata['query'])) {
			$query = $inputdata['query'];
		}
		if (!empty($inputdata['search'])) {
			$query .= "and (SI.skill_name Like '%" . safe_str($inputdata['search']) . "%' or SCI.`category_name` Like '%" . safe_str($inputdata['search']) . "%')";
		}


		$pcount_qry = "select count(*) as total_count from `" . safe_str($table1) . "`AS SI INNER JOIN `" . safe_str($table2) . "` AS SCI ON SI.`skill_category_id`=SCI.`skill_category_id` where SI.delete_flag=0 " . $query;
		$pcount_result = $con->query($pcount_qry);
		$pcount_row = $pcount_result->fetch_assoc();
		$total_records = $pcount_row["total_count"];

		@$total_pages = ceil($total_records / $row_size);
		if ($total_pages == 0) {
			$total_pages = 1;
		}
		if ($page_no > $total_pages) {
			$page_no = $total_pages;
		}
		$limit_from = ($row_size * $page_no) - $row_size;

		$pagg_qry = "select SI.colour,SI.skill_id, SI.skill_name, SCI.`category_name`, SI.`created_on`, SI.`is_enabled` from `" . safe_str($table1) . "`AS SI INNER JOIN `" . safe_str($table2) . "` AS SCI ON SI.`skill_category_id`=SCI.`skill_category_id` where SI.delete_flag=0 " . $query . " order by " . safe_str($sort_on) . " " . safe_str($sort_type) . " LIMIT " . $limit_from . ", " . $row_size;
		$pagg_result = $con->query($pagg_qry);
		$pagg_count = $pagg_result->num_rows;
		$pagging_list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}
		$data["total_records"] = $total_records;
		$data["total_pages"] = $total_pages;
		$data["pagging_list"] = $pagging_list;

		return $data;
	}

	function get_user_skill_details($inputdata)
	{
		global $con;
		$table1 = $inputdata['table1'];
		$table2 = $inputdata['table2'];
		//echo "fdsas";
		$pcount_qry = "select SI.*, SCI.`category_name`, SCI.`is_enabled` as enabled from `" . safe_str($table1) . "`AS SI INNER JOIN `" . safe_str($table2) . "` AS SCI ON SI.`skill_category_id`=SCI.`skill_category_id` where `" . safe_str($inputdata['key']) . "`='" . safe_str($inputdata['value']) . "'";
		//	var_dump($pcount_qry);
		$pcount_result = $con->query($pcount_qry);
		if ($con->query($pcount_qry)) {
			$pcount_row = $pcount_result->fetch_assoc();
		} else {
			echo $con->error;
		}
		return $pcount_row;
	}

	function get_brands_pagging_list($inputdata)
	{
		global $con;
		$data = array(
			"total_records" => 0,
			"total_pages" => 0,
			"pagging_list" => array()
		);

		$table1 = 'ki_brands_info';
		$table2 = 'ki_ticket_types_info';
		$page_no = $inputdata['page_no'];
		$row_size = $inputdata['row_size'];
		$sort_on = $inputdata['sort_on'];
		$sort_type = $inputdata['sort_type'];
		$query = '';
		if (!empty($inputdata['query'])) {
			$query = $inputdata['query'];
		}

		$pcount_qry = "select count(*) as total_count from `" . safe_str($table1) . "`AS BI INNER JOIN `" . safe_str($table2) . "` AS TTI ON BI.`ticket_type_id`=TTI.`ticket_type_id` where BI.delete_flag=0 " . $query;
		$pcount_result = $con->query($pcount_qry);
		$pcount_row = $pcount_result->fetch_assoc();
		$total_records = $pcount_row["total_count"];

		@$total_pages = ceil($total_records / $row_size);
		if ($total_pages == 0) {
			$total_pages = 1;
		}
		if ($page_no > $total_pages) {
			$page_no = $total_pages;
		}
		$limit_from = ($row_size * $page_no) - $row_size;

		$pagg_qry = "select BI.brand_id, BI.brand_name, TTI.`ticket_type_name`, BI.`created_on`, BI.`is_enabled` from `" . safe_str($table1) . "`AS BI INNER JOIN `" . safe_str($table2) . "` AS TTI ON BI.`ticket_type_id`=TTI.`ticket_type_id` where BI.delete_flag=0 " . $query . " order by " . safe_str($sort_on) . " " . safe_str($sort_type) . " LIMIT " . $limit_from . ", " . $row_size;
		$pagg_result = $con->query($pagg_qry);
		$pagg_count = $pagg_result->num_rows;
		$pagging_list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}
		$data["total_records"] = $total_records;
		$data["total_pages"] = $total_pages;
		$data["pagging_list"] = $pagging_list;

		return $data;
	}

	function get_all_table_data($inputdata)
	{
		global $con;
		$details = array();
		$table = $inputdata['table'];
		$sort = '';
		if (!empty($inputdata['sort'])) {
			$sort = ' ORDER BY `' . $inputdata['sort'] . '`';
		}
		$is_enabled = '';
		if (!empty($inputdata['all_data'])) {
			if ($inputdata['all_data'] != 1) {
				$is_enabled = "and `is_enabled`=1";
			}
		} else {
			$is_enabled = "and `is_enabled`=1";
		}
		$query = "SELECT * FROM `" . safe_str($table) . "` where delete_flag=0 " . $is_enabled . " " . $sort;
		$query_result = $con->query($query);
		if ($con->query($query)) {
			$i = 0;
			while ($row = $query_result->fetch_assoc()) {
				$details[$i] = $row;
				$i++;
			}
		} else {
			echo $con->error;
		}
		return $details;
	}

	function check_enability($inputdata)
	{
		global $con;
		$table = $inputdata['table'];
		$key = $inputdata['key'];
		$id = $inputdata['id'];
		$is_enabled = NULL;
		$query = "SELECT `is_enabled` FROM `" . safe_str($table) . "` WHERE delete_flag=0 and `" . safe_str($key) . "`=" . $id;
		$result = $con->query($query);
		echo $con->error;
		$row = $result->fetch_assoc();
		$is_enabled = $row['is_enabled'];
		return $is_enabled;
	}

	function validate_user_skills($inputdata)
	{
		global $con;
		$table = $inputdata['table'];
		$where = "";
		if (!empty($inputdata['skill_id'])) {
			$where .= " and `skill_id` != " . safe_str($inputdata['skill_id']);
		}
		$query = "SELECT * FROM `" . safe_str($table) . "` where `skill_name`='" . safe_str($inputdata['skill_name']) . "' AND `skill_category_id`=" . safe_str($inputdata['skill_category_id']) . " AND `delete_flag`=0" . $where;
		$result = $con->query($query);
		return $result->fetch_assoc();
	}

	function check_if_category_exists_p($inputdata)
	{
		global $con;
		$where = "";
		$inputdata['table'] = 'ki_categories_info';
		if (!empty($inputdata['category_id'])) {
			$where .= " and category_id != " . safe_str($inputdata['category_id']);
		}
		// trim extra spaces between words 
		//$inputdata['value'] = trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $inputdata['value'])));	
		$query = "(parent_category_id = '" . safe_str($inputdata['parent_category_id']) . "' or  (category_id = '" . safe_str($inputdata['parent_category_id']) . "' )) and ";
		if (empty($inputdata['parent_category_id'])) {
			$query = " (parent_category_id=0 or parent_category_id is null) and ";
		}
		$que = "select * from " . safe_str($inputdata['table']) . " where " . $query . " delete_flag=0 and category_name='" . safe_str($inputdata['category_name']) . "'" . $where;
		$pcount_result = $con->query($que);
		$pcount_row = $pcount_result->fetch_assoc();
		return $pcount_row;
	}

	function check_data_existance($inputdata)
	{
		global $con;
		$where = "";
		if (!empty($inputdata['id_value'])) {
			$where .= " and " . safe_str($inputdata['id_key']) . " != " . safe_str($inputdata['id_value']);
		}
		// trim extra spaces between words 
		// $inputdata['value'] = trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $inputdata['value'])));	
		$inputdata['value'] = safe_str(trim(preg_replace('/\s+/', ' ', $inputdata['value'])));
		$que = "select * from " . safe_str($inputdata['table']) . " where delete_flag=0 and `" . safe_str($inputdata['key']) . "`='" . safe_str($inputdata['value']) . "'" . $where;
		$pcount_result = $con->query($que);
		$pcount_row = $pcount_result->fetch_assoc();
		return $pcount_row;
	}

	function get_models_pagging_list($inputdata)
	{
		global $con;
		$data = array(
			"total_records" => 0,
			"total_pages" => 0,
			"pagging_list" => array()
		);

		$table1 = 'ki_models_info';
		$table2 = 'ki_ticket_types_info';
		$table3 = 'ki_brands_info';
		$table4 = 'ki_work_to_complete_info';
		$page_no = $inputdata['page_no'];
		$row_size = $inputdata['row_size'];
		$sort_on = $inputdata['sort_on'];
		$sort_type = $inputdata['sort_type'];
		$query = '';
		if (!empty($inputdata['query'])) {
			$query = $inputdata['query'];
		}

		if (!empty($inputdata['search'])) {

			$query .= "and (MI.`model_name` Like '%" . safe_str($inputdata['search']) . "%' or  TTI.`ticket_type_name` Like '%" . safe_str($inputdata['search']) . "%' or  BI.`brand_name` Like '%" . safe_str($inputdata['search']) . "%')";
		}

		$query .= " and MI.is_enabled=" . safe_str($inputdata['is_enabled']);

		$pcount_qry = "select count(*) as total_count from (((`" . safe_str($table1) . "`AS MI INNER JOIN `" . safe_str($table2) . "` AS TTI ON MI.`ticket_type_id`=TTI.`ticket_type_id`) INNER JOIN `" . safe_str($table3) . "` AS BI ON MI.`brand_id`=BI.`brand_id`) ) where MI.delete_flag=0 " . $query;
		$pcount_result = $con->query($pcount_qry);
		$pcount_row = $pcount_result->fetch_assoc();
		$total_records = $pcount_row["total_count"];

		@$total_pages = ceil($total_records / $row_size);
		if ($total_pages == 0) {
			$total_pages = 1;
		}
		if ($page_no > $total_pages) {
			$page_no = $total_pages;
		}
		$limit_from = ($row_size * $page_no) - $row_size;

		$pagg_qry = "select MI.`model_id`, MI.`model_name`, MI.`is_draft`, TTI.`ticket_type_name`, BI.`brand_name`, MI.`created_on`, MI.`is_enabled` from ((`" . safe_str($table1) . "`AS MI INNER JOIN `" . safe_str($table2) . "` AS TTI ON MI.`ticket_type_id`=TTI.`ticket_type_id`) INNER JOIN `" . safe_str($table3) . "` AS BI ON MI.`brand_id`=BI.`brand_id`) where MI.delete_flag=0 " . $query . " order by " . safe_str($sort_on) . " " . safe_str($sort_type) . " LIMIT " . $limit_from . ", " . $row_size;
		$pagg_result = $con->query($pagg_qry);
		echo $con->error;
		$pagg_count = $pagg_result->num_rows;
		$pagging_list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}
		$data["total_records"] = $total_records;
		$data["total_pages"] = $total_pages;
		$data["pagging_list"] = $pagging_list;

		return $data;
	}

	function get_models_upload_queue_pagging_list($inputdata)
	{
		global $con;
		$data = array(
			"total_records" => 0,
			"total_pages" => 0,
			"pagging_list" => array()
		);
		//print_r($inputdata);
		$table1 = 'ki_modal_upload_queue_info';
		$page_no = $inputdata['page_no'];
		$row_size = $inputdata['row_size'];
		$sort_on = $inputdata['sort_on'];
		$sort_type = $inputdata['sort_type'];
		/*$query='';
		if(!empty($inputdata['query'])){
			$query = $inputdata['query'];
		}
		
	    if(!empty($inputdata['search'])){
			
			$query .="and (MI.`model_name` Like '%".safe_str($inputdata['search'])."%' or  TTI.`ticket_type_name` Like '%".safe_str($inputdata['search'])."%' or  BI.`brand_name` Like '%".safe_str($inputdata['search'])."%')";
		}
		
		$query .= " and MI.is_enabled=".safe_str($inputdata['is_enabled']);*/

		$pcount_qry = "select count(*) as total_count from " . safe_str($table1) . " where delete_flag=0";
		$pcount_result = $con->query($pcount_qry);
		$pcount_row = $pcount_result->fetch_assoc();
		$total_records = $pcount_row["total_count"];

		@$total_pages = ceil($total_records / $row_size);
		if ($total_pages == 0) {
			$total_pages = 1;
		}
		if ($page_no > $total_pages) {
			$page_no = $total_pages;
		}
		$limit_from = ($row_size * $page_no) - $row_size;

		$pagg_qry = "select * from " . safe_str($table1) . " where delete_flag=0 order by " . safe_str($sort_on) . " " . safe_str($sort_type) . " LIMIT " . $limit_from . ", " . $row_size;
		$pagg_result = $con->query($pagg_qry);
		echo $con->error;
		$pagg_count = $pagg_result->num_rows;
		$pagging_list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}
		$data["total_records"] = $total_records;
		$data["total_pages"] = $total_pages;
		$data["pagging_list"] = $pagging_list;

		return $data;
	}

	function get_model_details($inputdata)
	{
		global $con;
		$table1 = $inputdata['table1'];
		$table2 = $inputdata['table2'];
		$table3 = $inputdata['table3'];
		$table4 = $inputdata['table4'];
		//echo "fdsas";
		$pcount_qry = "select MI.*, TTI.`ticket_type_name`, BI.`brand_name`, MI.`created_on`, MI.`modified_on`, MI.`is_enabled` from (((`" . safe_str($table1) . "`AS MI INNER JOIN `" . safe_str($table2) . "` AS TTI ON MI.`ticket_type_id`=TTI.`ticket_type_id`) INNER JOIN `" . safe_str($table3) . "` AS BI ON MI.`brand_id`=BI.`brand_id`)) where `" . safe_str($inputdata['key']) . "`='" . safe_str($inputdata['value']) . "'";
		//	var_dump($pcount_qry);
		$pcount_result = $con->query($pcount_qry);
		if ($con->query($pcount_qry)) {
			$pcount_row = $pcount_result->fetch_assoc();
		} else {
			echo $con->error;
		}
		return $pcount_row;
	}

	function validate_models($inputdata)
	{
		global $con;
		$table = $inputdata['table'];
		$where = "";
		if (!empty($inputdata['model_id'])) {
			$where .= " and `model_id` != " . safe_str($inputdata['model_id']);
		}
		$inputdata['model_name'] = safe_str(trim(preg_replace('/\s+/', ' ', $inputdata['model_name'])));
		$query = "SELECT * FROM `" . safe_str($table) . "` where `model_name`='" . safe_str($inputdata['model_name']) . "' AND `ticket_type_id`=" . safe_str($inputdata['ticket_type_id']) . " AND `brand_id`=" . safe_str($inputdata['brand_id']) . " AND `delete_flag`=0" . $where;
		$result = $con->query($query);
		return $result->fetch_assoc();
	}

	function get_soln_mapping_info($inputdata)
	{
		global $con;
		$table = '';
		if ($inputdata['soln'] == 1) {
			$table = "ki_model_recommended_soln_mapping_info";
		} elseif ($inputdata['soln'] == 2) {
			$table = "ki_model_budget_soln_mapping_info";
		}
		$query = "SELECT * FROM `" . $table . "` WHERE `model_id`='" . safe_str($inputdata['model_id']) . "' AND `delete_flag`=0";
		$result = $con->query($query);
		$i = 0;
		echo $con->error;
		while ($row = $result->fetch_assoc()) {
			$list[$i] = $row;
			$i++;
		}
		$final_list = array();
		if (!empty($list)) {
			foreach ($list as $row) {
				$wtc = $row['work_to_complete_id'];
				$final_list[$wtc][] = $row;
			}
		}
		// echo"<pre>";print_r($final_list);die;
		return $final_list;
	}

	function get_model_canned_resp_mapping_info($inputdata)
	{
		global $con;
		$table = 'ki_model_canned_response_mapping_info';
		$query = "SELECT * FROM `" . $table . "` WHERE `model_id`='" . safe_str($inputdata['model_id']) . "' AND `delete_flag`=0";
		$result = $con->query($query);
		$i = 0;
		echo $con->error;
		while ($row = $result->fetch_assoc()) {
			$list[$i] = $row;
			$i++;
		}
		$final_list = array();
		if (!empty($list)) {
			foreach ($list as $row) {
				$wtc = $row['work_to_complete_id'];
				$final_list[$wtc][] = $row;
			}
		}
		// echo"<pre>";print_r($final_list);die;
		return $final_list;
	}

	function get_model_value_adds_product($inputdata)
	{
		global $con;
		$table = 'ki_model_value_adds_product_mapping_info';
		$query = "SELECT * FROM `" . $table . "` WHERE `model_id`='" . safe_str($inputdata['model_id']) . "' AND `delete_flag`=0";
		$result = $con->query($query);
		$i = 0;
		echo $con->error;
		while ($row = $result->fetch_assoc()) {
			$list[$i] = $row;
			$i++;
		}
		$final_list = array();
		if (!empty($list)) {
			foreach ($list as $row) {
				$value_adds_id = $row['value_adds_id'];
				$final_list[$value_adds_id][] = $row;
			}
		}
		return $final_list;
	}

	function get_parent_categories_list($inputdata)
	{
		global $con;
		$table = $inputdata['table'];
		$list = array();
		$enabled = " `is_enabled`=1 AND ";
		if (isset($inputdata['enabled']) && $inputdata['enabled'] == 0) {
			$enabled = '';
		}
		$query = "SELECT * FROM `" . safe_str($table) . "` WHERE " . $enabled . " `is_child`=0 AND `delete_flag`=0 ORDER BY category_name";
		$result = $con->query($query);
		$i = 0;
		echo $con->error;
		while ($row = $result->fetch_assoc()) {
			$list[$i] = $row;
			$i++;
		}
		return $list;
	}

	function get_child_category_list($inputdata)
	{
		global $con;
		$table = 'ki_categories_info';
		$parent_category_id = $inputdata['parent_category_id'];
		$list = array();
		$enabled = " `is_enabled`=1 AND ";
		if (isset($inputdata['enabled']) && $inputdata['enabled'] == 0) {
			$enabled = '';
		}
		$publish_on_website = "";
		if (!empty($inputdata['publish_on_website'])) {
			$publish_on_website = ' `publish_on_website`=1 AND ';
		}
		$query = "SELECT * FROM `" . safe_str($table) . "` WHERE " . $enabled . $publish_on_website . " `is_child`=1 AND `parent_category_id`=" . $parent_category_id . " AND `delete_flag`=0 ORDER BY category_name";
		$result = $con->query($query);
		$i = 0;
		echo $con->error;
		while ($row = $result->fetch_assoc()) {
			$list[$i] = $row;
			$i++;
		}
		return $list;
	}

	function get_categories_details($inputdata)
	{
		global $con;
		$table = $inputdata['table'];
		$query = "SELECT * FROM `" . safe_str($table) . "` WHERE `" . safe_str($inputdata['key']) . "`='" . safe_str($inputdata['value']) . "'";
		$res = $con->query($query);
		$row = $res->fetch_assoc();
		echo $con->error;
		if ($row['is_child'] == 0) {
			return $row;
		} else {
			$parent_query = "SELECT * FROM `" . safe_str($table) . "` WHERE `category_id`=" . $row['parent_category_id'] . "";
			$result = $con->query($parent_query);
			$roww = $result->fetch_assoc();
			$row["parent_category_name"] = $roww['category_name'];
			echo $con->error;
			return $row;
		}
	}

	function delete_categories($inputdata)
	{
		global $con;
		$data = array(
			"status" => 0,
			"errors" => array()
		);
		$table = $inputdata['table'];
		$category_id = safe_str($inputdata['category_id']);
		$child = send_rest(array(
			"table" => "ki_categories_info",
			"function" => "get_child_category_list",
			"parent_category_id" => $category_id
		));
		if (!empty($child)) {
			// print_r($child);
			/* $get_uncategorised_id = "SELECT category_id FROM `".safe_str($table)."` WHERE `is_child`=0 AND `category_name`='Uncategorised Category'";
			$res = $con->query($get_uncategorised_id);
			echo $con->error;
			$row = $res->fetch_assoc();
			$id = $row['category_id'];
			$update_child = "UPDATE `".safe_str($table)."` SET `parent_category_id`=".$id." WHERE `parent_category_id`=".$category_id;
			if($con->query($update_child)===true){
				foreach($child as $cat){
					$update_path = "UPDATE `ki_categories_info` AB INNER JOIN (SELECT `category_id`, getpath(`category_id`) AS path FROM ki_categories_info WHERE `category_id`='".$cat['category_id']."') CD ON AB.`category_id`=CD.`category_id` SET AB.`path`=CD.`path` WHERE AB.`category_id`='".$cat['category_id']."'";
					$up_result = $con->query($update_path);
					update_path_on_delete($cat['category_id'], 0);
				}
				$delete_query="UPDATE `".safe_str($table)."` SET `delete_flag`=1 WHERE `category_id`=".$category_id;
				if($con->query($delete_query)===true){
					$data["status"]=1;
				}
				else{
					$data["error"][]=$con->error;
				}
			}
			else{
				$data["error"][]=$con->error;
			} */
		} else {
			$delete_query = "UPDATE `" . safe_str($table) . "` SET `delete_flag`=1 WHERE `category_id`=" . $category_id;
			if ($con->query($delete_query) == true) {
				$data["status"] = 1;
			} else {
				$data["error"][] = $con->error;
			}
		}
		return $data;
	}

	function get_suppliers_pagging_list($inputdata)
	{
		global $con;
		$data = array(
			"total_records" => 0,
			"total_pages" => 0,
			"pagging_list" => array()
		);

		$table = 'ki_suppliers_info';
		$page_no = $inputdata['page_no'];
		$row_size = $inputdata['row_size'];
		$sort_on = $inputdata['sort_on'];
		$sort_type = $inputdata['sort_type'];
		$query = '';
		$inputdata['query'] = mysqli_real_escape_string($con, $inputdata['query']);
		if (!empty($inputdata['query'])) {
			$query = ' and (company_name like "%' . safe_str($inputdata['query']) . '%" or preference like "%' . safe_str($inputdata['query']) . '%" or CONCAT(COALESCE(`contact_first_name`,"")," ", COALESCE(`contact_last_name`,"")) like "%' . safe_str($inputdata['query']) . '%" or office_phone like "%' . safe_str($inputdata['query']) . '%" or office_email like "%' . safe_str($inputdata['query']) . '%" or contact_first_name like "%' . safe_str($inputdata['query']) . '%" or contact_last_name like "%' . safe_str($inputdata['query']) . '%" or contact_mobile like "%' . safe_str($inputdata['query']) . '%")';
		}

		$pcount_qry = "select count(*) as total_count from `" . safe_str($table) . "` where delete_flag=0 " . $query;
		$pcount_result = $con->query($pcount_qry);
		$pcount_row = $pcount_result->fetch_assoc();
		$total_records = $pcount_row["total_count"];

		@$total_pages = ceil($total_records / $row_size);
		if ($total_pages == 0) {
			$total_pages = 1;
		}
		if ($page_no > $total_pages) {
			$page_no = $total_pages;
		}
		$limit_from = ($row_size * $page_no) - $row_size;

		$pagg_qry = "select *,CONCAT(COALESCE(`contact_first_name`,''),' ', COALESCE(`contact_last_name`,'')) AS supplier_name from `" . safe_str($table) . "` where delete_flag=0 " . $query . " order by " . safe_str($sort_on) . " " . safe_str($sort_type) . " LIMIT " . $limit_from . ", " . $row_size;
		$pagg_result = $con->query($pagg_qry);
		$pagg_count = $pagg_result->num_rows;
		$pagging_list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}

		$data["total_records"] = $total_records;
		$data["total_pages"] = $total_pages;
		$data["pagging_list"] = $pagging_list;

		return $data;
	}

	function get_inventory_suppliers_pagging_list($inputdata)
	{
		global $con;
		$location_type = $inputdata['location_type'];
		$location_id = $inputdata['location_id'];
		$data = array(
			"total_records" => 0,
			"total_pages" => 0,
			"pagging_list" => array()
		);

		$table = 'ki_suppliers_info';
		$page_no = $inputdata['page_no'];
		$row_size = $inputdata['row_size'];
		$sort_on = $inputdata['sort_on'];
		$sort_type = $inputdata['sort_type'];
		$query = '';
		$inputdata['query'] = mysqli_real_escape_string($con, $inputdata['query']);
		if (!empty($inputdata['query'])) {
			$query = ' and (company_name like "%' . safe_str($inputdata['query']) . '%" or preference like "%' . safe_str($inputdata['query']) . '%" or CONCAT(COALESCE(`contact_first_name`,"")," ", COALESCE(`contact_last_name`,"")) like "%' . safe_str($inputdata['query']) . '%" or office_phone like "%' . safe_str($inputdata['query']) . '%" or office_email like "%' . safe_str($inputdata['query']) . '%" or contact_first_name like "%' . safe_str($inputdata['query']) . '%" or contact_last_name like "%' . safe_str($inputdata['query']) . '%" or contact_mobile like "%' . safe_str($inputdata['query']) . '%")';
		}

		$pcount_qry = "select count(*) as total_count from `" . safe_str($table) . "` as SI INNER JOIN `ki_supplier_location_info` as SLI ON SI.`supplier_id`=SLI.`supplier_id` WHERE `location_type`=" . $location_type . " AND `location_id`=" . $location_id . " AND SI.delete_flag=0 AND SLI.delete_flag=0" . $query;
		$pcount_result = $con->query($pcount_qry);
		$pcount_row = $pcount_result->fetch_assoc();
		$total_records = $pcount_row["total_count"];

		@$total_pages = ceil($total_records / $row_size);
		if ($total_pages == 0) {
			$total_pages = 1;
		}
		if ($page_no > $total_pages) {
			$page_no = $total_pages;
		}
		$limit_from = ($row_size * $page_no) - $row_size;

		$pagg_qry = "SELECT SI.*,CONCAT(COALESCE(`contact_first_name`,''),' ', COALESCE(`contact_last_name`,'')) AS supplier_name FROM `" . safe_str($table) . "` as SI INNER JOIN `ki_supplier_location_info` as SLI ON SI.`supplier_id`=SLI.`supplier_id` WHERE `location_type`=" . $location_type . " AND `location_id`=" . $location_id . " AND SI.delete_flag=0 AND SLI.delete_flag=0 " . $query . " order by " . safe_str($sort_on) . " " . safe_str($sort_type) . " LIMIT " . $limit_from . ", " . $row_size;
		$pagg_result = $con->query($pagg_qry);
		$pagg_count = $pagg_result->num_rows;
		$pagging_list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}

		$data["total_records"] = $total_records;
		$data["total_pages"] = $total_pages;
		$data["pagging_list"] = $pagging_list;

		return $data;
	}

	function get_all_suppliers_list($inputdata)
	{
		global $con;
		$table = $inputdata['table'];
		$list = array();
		$query = "SELECT *,CONCAT(COALESCE(`contact_first_name`,''),' ', COALESCE(`contact_last_name`,'')) AS supplier_name FROM `" . safe_str($table) . "` WHERE `is_enabled`=1 AND `delete_flag`=0 ORDER BY `company_name`";
		$result = $con->query($query);
		$i = 0;
		echo $con->error;
		while ($row = $result->fetch_assoc()) {
			$list[$i] = $row;
			$i++;
		}
		return $list;
	}

	function get_alternative_suppliers_list($inputdata)
	{
		global $con;
		$list = array();
		$table = $inputdata['table'];
		//echo "fdsas";
		$pcount_qry = "select * from `" . safe_str($table) . "` where `" . safe_str($inputdata['key']) . "`='" . safe_str($inputdata['value']) . "' AND `delete_flag`=0";
		//	var_dump($pcount_qry);
		$pcount_result = $con->query($pcount_qry);
		echo $con->error;
		while ($row = $pcount_result->fetch_assoc()) {
			$list[$row['alternative_supplier_id']] = $row;
		}
		return $list;
	}

	function create_alternative_suppliers($inputdata)
	{
		global $con;
		$data = array(
			"status" => 0,
			"errors" => array()
		);
		$table = $inputdata['table'];
		$supplier_id = $inputdata['supplier_id'];
		$created_on = $inputdata['created_on'];
		$in_fields = array();
		foreach ($inputdata['fields_data'] as $alt_suppliers) {
			$in_fields[] =  "(" . $supplier_id . "," . $alt_suppliers . ",'" . $created_on . "',0)";
		}
		$insert = implode(", ", $in_fields);
		$up_qry = "INSERT INTO `" . safe_str($table) . "` (supplier_id, alternative_supplier_id, created_on, delete_flag) VALUES " . $insert;
		$up_result = $con->query($up_qry);
		if ($up_result) {
			$data["status"] = 1;
			$data["id"] = $con->insert_id;
		} else {
			$data["errors"][] = $con->error;
		}
		return $data;
	}

	function update_alternative_suppliers($inputdata)
	{
		global $con;
		$data = array(
			"status" => 0,
			"errors" => array()
		);
		$table = $inputdata['table'];
		$supplier_id = $inputdata['supplier_id'];
		$created_on = $inputdata['created_on'];
		$in_fields = array();

		$delete_query = "DELETE FROM `" . safe_str($table) . "` WHERE `supplier_id`=" . $supplier_id;
		if ($con->query($delete_query) == true) {
			if (!empty($inputdata['fields_data'])) {
				foreach ($inputdata['fields_data'] as $alt_suppliers) {
					$in_fields[] =  "(" . $supplier_id . "," . $alt_suppliers . ",'" . $created_on . "',0)";
				}
				$insert = implode(", ", $in_fields);
				$up_qry = "INSERT INTO `" . safe_str($table) . "` (supplier_id, alternative_supplier_id, created_on, delete_flag) VALUES " . $insert;
				$up_result = $con->query($up_qry);
				if ($up_result) {
					$data["status"] = 1;
					$data["id"] = $con->insert_id;
				} else {
					$data["errors"][] = $con->error;
				}
			} else {
				$data["status"] = 1;
			}
		} else {
			$data["errors"][] = $con->error;
		}
		return $data;
	}

	function get_stores_pagging_list($inputdata)
	{
		global $con;
		$data = array(
			"total_records" => 0,
			"total_pages" => 0,
			"pagging_list" => array()
		);
		$page_no = $inputdata['page_no'];
		$row_size = $inputdata['row_size'];
		$sort_on = $inputdata['sort_on'];
		$sort_type = $inputdata['sort_type'];

		$query = '';
		if (!empty($inputdata['search'])) {

			$query .= "and (store_name Like '%" . safe_str($inputdata['search']) . "%' or CONCAT(COALESCE(UI.`first_name`,''),' ', COALESCE(UI.`last_name`,'')) Like '%" . safe_str($inputdata['search']) . "%' or SI.phone_number Like '%" . safe_str($inputdata['search']) . "%' or CONCAT(COALESCE(CONCAT(SI.`address`, ','),''),' ',COALESCE(CONCAT(SI.`suburb`, ','),''),' ',COALESCE(CONCAT(SI.`state`, ','),''),' ',COALESCE(CONCAT(SI.`postcode`, ','),''),' ',COALESCE(SI.`country`,'')) Like '%" . safe_str($inputdata['search']) . "%')";
		}


		$pcount_qry = "select count(*) as total_count FROM `ki_stores_info` AS SI LEFT JOIN `ki_users_info` AS UI ON SI.`store_manager_id`=UI.`user_id` where SI.delete_flag=0 " . $query;
		$pcount_result = $con->query($pcount_qry);
		//	echo $con->error;
		$pcount_row = $pcount_result->fetch_assoc();
		$total_records = $pcount_row["total_count"];

		@$total_pages = ceil($total_records / $row_size);
		if ($total_pages == 0) {
			$total_pages = 1;
		}
		if ($page_no > $total_pages) {
			$page_no = $total_pages;
		}
		$limit_from = ($row_size * $page_no) - $row_size;

		$pagg_qry = "SELECT CONCAT(COALESCE(CONCAT(SI.`address`, ','),''),' ',COALESCE(CONCAT(SI.`suburb`, ','),''),' ',COALESCE(CONCAT(SI.`state`, ','),''),' ',COALESCE(CONCAT(SI.`postcode`, ','),''),' ',COALESCE(SI.`country`,'')) AS concat_address, SI.*,SI.`address`,SI.`suburb`,SI.`state`,SI.`postcode`,SI.`country`, CONCAT(COALESCE(UI.`first_name`,''),' ', COALESCE(UI.`last_name`,'')) AS store_manager, UI.`is_enabled` as user_enabled FROM `ki_stores_info` AS SI LEFT JOIN `ki_users_info` AS UI ON SI.`store_manager_id`=UI.`user_id` WHERE SI.delete_flag=0 " . $query . " ORDER BY " . safe_str($sort_on) . " " . safe_str($sort_type) . " LIMIT " . $limit_from . ", " . $row_size;
		$pagg_result = $con->query($pagg_qry);
		echo $con->error;
		$pagg_count = $pagg_result->num_rows;
		$pagging_list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}

		$data["total_records"] = $total_records;
		$data["total_pages"] = $total_pages;
		$data["pagging_list"] = $pagging_list;

		return $data;
	}

	function get_user_name_details($inputdata)
	{
		global $con;
		$location_type = safe_str($inputdata['location_type']);
		$id = safe_str($inputdata['location_id']);
		$list = array();
		$query = "SELECT t1.`user_id`, CONCAT(COALESCE(t1.`first_name`,''),' ', COALESCE(t1.`last_name`,'')) AS name FROM `ki_users_info` as t1 INNER JOIN `ki_user_locations_info` as t2 ON t1.`user_id`=t2.`user_id` WHERE t1.`is_enabled`=1 AND t1.`delete_flag`=0 AND t2.`location_type`=" . $location_type . " AND t2.`location_id`=" . $id . " ORDER BY name";
		$result = $con->query($query);
		echo $con->error;
		while ($row = $result->fetch_assoc()) {
			$list[$row['user_id']] = $row;
		}
		return $list;
	}

	function get_non_trade_days_list($inputdata)
	{
		global $con;
		$list = array();
		$count_query = "SELECT count(*) as total_count FROM `ki_non_trade_days_info` WHERE `location_type`=" . safe_str($inputdata['location_type']) . " AND `location_id`=" . safe_str($inputdata['location_id']) . " AND `delete_flag`=0";
		$res = $con->query($count_query);
		echo $con->error;
		$roww = $res->fetch_assoc();
		$list['total_count'] = $roww['total_count'];
		$query = "SELECT * FROM `ki_non_trade_days_info` WHERE `location_type`=" . safe_str($inputdata['location_type']) . " AND `location_id`=" . safe_str($inputdata['location_id']) . " AND `delete_flag`=0 ORDER BY day";
		$result = $con->query($query);
		$i = 0;
		echo $con->error;
		while ($row = $result->fetch_assoc()) {
			$list[$i] = $row;
			$i++;
		}
		// echo "<pre>";print_r($list);
		return $list;
	}

	function update_non_trade_days_stores($inputdata)
	{
		global $con;
		$data = array(
			"status" => 0,
			"errors" => array()
		);
		$list = array();
		$qryError = false;
		$table = $inputdata['table'];
		$created_on = $inputdata['created_on'];
		$checkAllDaysQry = array();


		$arrayTradeDays = array();
		$arrayFullTradeDays = array();
		$firstMatchDaysList = array();
		foreach ($inputdata['fields_data']['non_trade_days'] as $key => $val) {
			$arrayTradeDays[] =  date("d-m", strtotime($val));
			$arrayFullTradeDays[] =  date("d-m-Y", strtotime($val));

			if ($inputdata['fields_data']['is_recurring'][$key] == 1) {
				foreach ($inputdata['fields_data']['non_trade_days'] as $key2 => $val2) {
					$sVal2 = date("d-m", strtotime($val2));
					$sValFull2 = date("d-m-Y", strtotime($val2));

					if (($sVal2 == $arrayTradeDays[$key]) && ($sValFull2 != $arrayFullTradeDays[$key])) {
						$firstMatchDaysList[] = $sValFull2;
					}
				}
			}
		}
		if ($firstMatchDaysList) {
			$qryError = true;
			$data["status"] = false;
			$data["errors"][] = "There are exists recurring dates:-";
			$data["errors"][] = $firstMatchDaysList;
		}
		foreach ($inputdata['fields_data']['non_trade_days'] as $val) {
			$checkAllDaysQry[] = " (DATE_FORMAT(STR_TO_DATE(`day`, '%Y-%m-%d'), '%d-%m') = DATE_FORMAT(STR_TO_DATE('$val','%d-%m-%Y'), '%d-%m')) ";
		}
		if ($checkAllDaysQry && !$qryError) {
			$checkRecurringExists = "SELECT DATE_FORMAT(day , '%d-%m') as matchDay, DATE_FORMAT(day , '%d-%m-%Y') as day FROM `" . safe_str($table) . "` WHERE `location_type`=" . safe_str($inputdata['location_type']) . " AND `location_id`=" . safe_str($inputdata['location_id']) . " AND `is_recurring`=1 AND  `delete_flag`=0 AND (" . implode(' OR ', $checkAllDaysQry) . ")";
			$qryCheck = $con->query($checkRecurringExists);
			if ($qryCheck) {
				if ($qryCheck->num_rows) {
					$dbDaysMList = array(
						'matchDay' => array(),
						'day' => array()
					);
					while ($row = $qryCheck->fetch_assoc()) {
						$dbDaysMList['matchDay'][] = $row['matchDay'];
						$dbDaysMList['day'][] = $row['day'];
					}
					$matchDaysList = array();
					foreach ($arrayTradeDays as $key1 => $nonTradeDaysMonth) {
						foreach ($dbDaysMList['matchDay'] as $key2 => $dayDB) {
							if (($dayDB == $nonTradeDaysMonth)
								&& ($dbDaysMList['day'][$key2] != $arrayFullTradeDays[$key1])
								&& ($inputdata['fields_data']['is_recurring'][$key1] == 1)
							) {
								$matchDaysList[] = $arrayFullTradeDays[$key1];
							}
						}
					}
					if ($matchDaysList) {
						$qryError = true;
						$data["status"] = false;
						$data["errors"][] = "There are exists recurring dates:-";
						$data["errors"][] = $matchDaysList;
					}
				}
			}
		}
		if (!$qryError) {
			$find = "SELECT DATE_FORMAT(day , '%d-%m-%Y') as day FROM `" . safe_str($table) . "` WHERE `location_type`=" . safe_str($inputdata['location_type']) . " AND `location_id`=" . safe_str($inputdata['location_id']) . " AND `delete_flag`=0";
			$result = $con->query($find);
			$i = 0;
			$data["errors"][] = $con->error;
			while ($row = $result->fetch_assoc()) {
				$list[$i] = $row['day'];
				$i++;
			}
			if ($inputdata['fields_data']['non_trade_days']) {
				$insertQryArray = array();
				foreach ($inputdata['fields_data']['non_trade_days'] as $key0 => $non_trade_days) {
					if (in_array($non_trade_days, $list)) {
						$key = array_search($non_trade_days, $list);
						$is_recur = 1;
						if (empty($inputdata['fields_data']['is_recurring'][$key0])) {
							$is_recur = 0;
						}
						$update0 = $con->query("UPDATE `" . safe_str($table) . "` SET `is_recurring`=" . $is_recur . " WHERE `location_type`=" . safe_str($inputdata['location_type']) . " AND `location_id`=" . safe_str($inputdata['location_id']) . " AND `day`='" . mysql_date($non_trade_days) . "'");
						if (!$update0) {
							$qryError = true;
							$data["errors"][] = $con->error;
						}
						unset($list[$key]);
					} else {
						$insertQryArray[] = "(" . safe_str($inputdata['location_type']) . ", " . safe_str($inputdata['location_id']) . ", '" . mysql_date($non_trade_days) . "', '" . safe_str($inputdata['fields_data']['is_recurring'][$key0]) . "', '" . $created_on . "', 0)";
					}
				}
				if ($insertQryArray) {
					$insert0 = $con->query("INSERT INTO `" . safe_str($table) . "`( `location_type`, `location_id`, `day`, `is_recurring`, `created_on`, `delete_flag`) VALUES " . implode(', ', $insertQryArray));
					if (!$insert0) {
						$qryError = true;
						$data["errors"][] = $con->error;
					}
				}
			}
			if ($list) {
				$qryStr = array();
				foreach ($list as $del) {
					$del = date("Y-m-d", strtotime($del));
					if ($del >= date("Y-m-d")) {
						$qryStr[] = " `day`='" . $del . "' ";
					}
				}
				if (!empty($qryStr)) {
					// echo "UPDATE `".safe_str($table)."` SET `delete_flag`=1 WHERE `location_type`=".$inputdata['location_type']." AND `location_id`=".$inputdata['location_id']." AND (".implode(' OR ',$qryStr).")";
					$deleteQry = $con->query("UPDATE `" . safe_str($table) . "` SET `delete_flag`=1 WHERE `location_type`=" . safe_str($inputdata['location_type']) . " AND `location_id`=" . safe_str($inputdata['location_id']) . " AND (" . implode(' OR ', $qryStr) . ")");
					if (!$deleteQry) {
						$qryError = true;
						$data["errors"][] = $con->error;
					}
				}
			}
			if (!$qryError) {
				$data["status"] = 1;
			}
		}
		return $data;
	}

	function get_distribution_branches_pagging_list($inputdata)
	{
		global $con;
		// print_r($inputdata);
		$data = array(
			"total_records" => 0,
			"total_pages" => 0,
			"pagging_list" => array()
		);
		$type = "distribution";
		if (isset($inputdata['production']) && $inputdata['production'] == 1) {
			$type = "production";
		}
		$page_no = $inputdata['page_no'];
		$row_size = $inputdata['row_size'];
		$sort_on = $inputdata['sort_on'];
		$sort_type = $inputdata['sort_type'];
		$query = '';
		if (!empty($inputdata['search'])) {
			$query .= "and (CONCAT(COALESCE(UI.`first_name`,''),' ', COALESCE(UI.`last_name`,'')) Like '%" . safe_str($inputdata['search']) . "%' or " . $type . "_name Like '%" . safe_str($inputdata['search']) . "%' or DI.email Like '%" . safe_str($inputdata['search']) . "%' or DI.phone_number Like '%" . safe_str($inputdata['search']) . "%')";
		}
		$pcount_qry = "select count(*) as total_count FROM `" . safe_str($inputdata['table']) . "` AS DI LEFT JOIN `ki_users_info` AS UI ON DI.`manager_id`=UI.`user_id` where DI.delete_flag=0 " . $query;
		$pcount_result = $con->query($pcount_qry);
		echo $con->error;
		$pcount_row = $pcount_result->fetch_assoc();
		$total_records = $pcount_row["total_count"];

		@$total_pages = ceil($total_records / $row_size);
		if ($total_pages == 0) {
			$total_pages = 1;
		}
		if ($page_no > $total_pages) {
			$page_no = $total_pages;
		}
		$limit_from = ($row_size * $page_no) - $row_size;

		$pagg_qry = "SELECT DI.*, CONCAT(COALESCE(UI.`first_name`,''),' ', COALESCE(UI.`last_name`,'')) AS distribution_manager, UI.`is_enabled` as user_enabled FROM `" . safe_str($inputdata['table']) . "` AS DI LEFT JOIN `ki_users_info` AS UI ON DI.`manager_id`=UI.`user_id` WHERE DI.delete_flag=0 " . $query . " ORDER BY " . safe_str($sort_on) . " " . safe_str($sort_type) . " LIMIT " . $limit_from . ", " . $row_size;
		$pagg_result = $con->query($pagg_qry);
		echo $con->error;
		$pagg_count = $pagg_result->num_rows;
		$pagging_list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}

		$data["total_records"] = $total_records;
		$data["total_pages"] = $total_pages;
		$data["pagging_list"] = $pagging_list;

		return $data;
	}

	function get_products_pagging_list($inputdata)
	{
		global $con;
		$Encryption = new Encryption();
		$data = array(
			"total_records" => 0,
			"total_pages" => 0,
			"pagging_list" => array()
		);
		$page_no = safe_str($inputdata['page_no']);
		$row_size = safe_str($inputdata['row_size']);
		$sort_on = safe_str($inputdata['sort_on']);
		$sort_type = safe_str($inputdata['sort_type']);

		$query = '';
		$inputdata['query'] = mysqli_real_escape_string($con, $inputdata['query']);
		if (!empty($inputdata['category_id'])) {
			$categoryId = safe_str(($Encryption->decode($inputdata['category_id'])));
			$child = $this->get_child_category_list(array(
				"table" => "ki_categories_info",
				"parent_category_id" => $categoryId
			));
			$child_ids = '';
			$child_ids = $categoryId;
			$path = '';
			if (!empty($child)) {
				$i = 0;
				foreach ($child as $row) {
					$child_ids .= "," . $row['category_id'];
					$path .= $row['path'] . ",";
					$grand_child = get_child_ids($row['category_id']);
					$child_ids .= $grand_child;
					$i++;
				}
			}
			if (substr($child_ids, 0, 1) === ",") {
				$child_ids = substr($child_ids, 1);
			}
			if (substr($child_ids, -1) === ",") {
				$child_ids = substr($child_ids, 0, -1);
			}
			$query .= ' and PI.category_id IN(' . $child_ids . ')';
		}
		if (!empty($inputdata['is_archived_list']) && $inputdata['is_archived_list'] == 1) {
			$query .= ' and PI.status = 3';
		} else {
			$query .= ' and PI.status != 3';
		}
		$order_by = "";
		if (!empty($inputdata['query'])) {
			$searching_terms = explode(" ", $inputdata['query']);
			$count_terms = count($searching_terms);
			$product_search_query = "";
			for ($i = 0; $i < $count_terms; $i++) {
				if ($i == 0) {
					$product_search_query = ' or (';
				}
				if ($i == ($count_terms - 1)) {
					$product_search_query .= ' product_name like "%' . safe_str(($searching_terms[$i])) . '%"';
					$product_search_query .= ' )';
				} else {
					$product_search_query .= ' product_name like "%' . safe_str(($searching_terms[$i])) . '%" and';
				}
			}
			// echo $product_search_query;die;
			$query .=  ' and ((SKU like "%' . safe_str(($inputdata['query'])) . '%") or (barcode like "%' . safe_str(($inputdata['query'])) . '%") ' . $product_search_query . ')';
			// echo $query;
			//$query .= " and (MATCH (`product_name`, `SKU`, `barcode`) AGAINST ('*".implode("* *", array_unique(array_filter(explode(" ", $inputdata['query']))))."*' IN BOOLEAN MODE))";
			//$order_by = "(MATCH (`product_name`, `SKU`, `barcode`) AGAINST ('*".implode("* *", array_unique(array_filter(explode(" ", $inputdata['query']))))."*' IN BOOLEAN MODE)) DESC, ";
			// $query .= ' and (product_name like "%'.safe_str(($inputdata['query'])).'%" or product_type like "%'.safe_str(($inputdata['query'])).'%" or barcode like "%'.safe_str(($inputdata['query'])).'%" or SKU like "%'.safe_str(($inputdata['query'])).'%" )';
		}

		$subQryStr = '';
		$location_type  = '';
		$location_id    = '';
		if (!empty($inputdata['is_admin'])) {
			// echo 'dsdsd';
			$qry01 = $con->query("SELECT * FROM ki_user_locations_info WHERE user_id = " . safe_str($inputdata['user_id']) . " and is_default=1");
			if ($qry01->num_rows) {
				$row0 = $qry01->fetch_assoc();
				$location_type  = $row0['location_type'];
				$location_id    = $row0['location_id'];
			} else {
				$location_type  = safe_str($inputdata['location_type']);
				$location_id    = safe_str($inputdata['location_id']);
			}
		} else {
			$location_type  = safe_str($inputdata['location_type']);
			$location_id    = safe_str($inputdata['location_id']);
		}

		// if($location_type==1){
		// $subQryStr = 'store_id = '.$location_id;
		// }
		// elseif($location_type==2){
		// $subQryStr = 'distribution_id = '.$location_id;
		// }
		$join = "";
		$select = "";
		if (!empty($inputdata['price_list_id'])) {
			// join with ki_price_list_products_info
			$join = " inner join ki_price_list_products_info pip on pip.product_id=PI.product_id and pip.price_list_id='" . safe_str($Encryption->decode($inputdata['price_list_id'])) . "' and pip.delete_flag=0";
			if ($inputdata['sort_on'] == 'created_on') {
				$sort_on = "pip." . safe_str($inputdata['sort_on']);
			}
			$select = "pip.*,";
		}

		$subQryStr_type = 'location_type = ' . $location_type;
		$subQryStr_id = 'location_id = ' . $location_id;

		// if($subQryStr_type && $subQryStr_id){
		$pcount_qry = "select count(*) as total_count FROM `ki_products_info` AS PI LEFT JOIN `ki_product_prices_info` AS PP ON PP.product_id = PI.product_id LEFT JOIN `ki_product_consumption_info` AS PC ON PC.product_id = PI.product_id AND PC." . $subQryStr_type . " AND PC." . $subQryStr_id . " LEFT JOIN `ki_product_quantites_info` AS PQI ON PQI.product_id = PI.product_id AND PQI." . $subQryStr_type . " AND PQI." . $subQryStr_id . " " . $join . " WHERE PI.delete_flag = 0 $query";
		// } else{
		// $pcount_qry = "select count(*) as total_count FROM `ki_products_info` AS PI LEFT JOIN `ki_product_prices_info` AS PP ON PP.product_id = PI.product_id LEFT JOIN `ki_product_consumption_info` AS PC ON PC.product_id = PI.product_id LEFT JOIN `ki_product_quantites_info` AS PQI ON PQI.product_id = PI.product_id ".$join." WHERE PI.delete_flag = 0 $query";
		// }
		// echo $pcount_qry;
		$pcount_result = $con->query($pcount_qry);
		if (!$pcount_result) {
			return $con->error;
			die;
		}
		$pcount_row = $pcount_result->fetch_assoc();


		$total_records = $pcount_row["total_count"];

		@$total_pages = ceil($total_records / $row_size);
		if ($total_pages == 0) {
			$total_pages = 1;
		}
		if ($page_no > $total_pages) {
			$page_no = $total_pages;
		}
		$limit_from = ($row_size * $page_no) - $row_size;

		// if($subQryStr_type && $subQryStr_id){
		// echo 'first';
		$pagg_qry = "SELECT IFNULL(IFNULL(distribution_stock_amount,0)+IFNULL(store_total_stock,0)+IFNULL(production_total_stock,0),0) as total_stock,at1.distribution_stock_amount,at1.distribution_breakdown," . $select . "
	    PI.*,
	    PP.retail_price, PP.distribution_price, PP.distribution_margin,PP.retail_margin,PP.cost_price,
	    PC.core_range, PC.forecasted_daily_consumption_rate AS fdcr, PC.is_over_stocked, PC.is_SLOB,
	    PQI.stock_on_hand, PQI.desired_stock_level, PQI.override_desired_stock_level ,PQI.reorder_level
	    FROM `ki_products_info` AS PI
	    LEFT JOIN `ki_product_prices_info` AS PP ON PP.product_id = PI.product_id
	    LEFT JOIN `ki_product_consumption_info` AS PC ON PC.product_id = PI.product_id AND PC." . $subQryStr_type . " AND PC." . $subQryStr_id . "
	    LEFT JOIN `ki_product_quantites_info` AS PQI ON PQI.product_id = PI.product_id AND PQI." . $subQryStr_type . " AND PQI." . $subQryStr_id . " " . $join . "
		left join 
		(select IFNULL(sum(stock_on_hand),0) as distribution_stock_amount,pq.product_id,group_concat('D: ',db.distribution_name,' : ',stock_on_hand) as distribution_breakdown from ki_product_quantites_info pq inner join ki_distribution_branches_info db on db.distribution_branch_id=pq.location_id and db.is_enabled=1 and db.delete_flag=0 where pq.location_type=2 and pq.delete_flag=0 group by pq.product_id) at1 on 
		at1.product_id=PI.product_id
		left join 
		(select IFNULL(sum(stock_on_hand),0) as store_total_stock,pq.product_id from ki_product_quantites_info pq inner join ki_stores_info db1 on db1.store_id=pq.location_id where  pq.delete_flag=0 and db1.is_enabled=1 and db1.delete_flag=0 and pq.location_type=1 group by pq.product_id) at2 on 
		at2.product_id=PI.product_id
		left join 
		(select IFNULL(sum(stock_on_hand),0) as production_total_stock,pq.product_id from ki_product_quantites_info pq inner join ki_production_info db2 on db2.production_id=pq.location_id where pq.delete_flag=0 and db2.is_enabled=1 and db2.delete_flag=0 and pq.location_type=3 group by pq.product_id) at3
		on at3.product_id=PI.product_id 
	    WHERE PI.delete_flag = 0 $query
		GROUP BY PI.product_id
	    ORDER BY " . $order_by . safe_str($sort_on) . " " . safe_str($sort_type) . " 
	    LIMIT " . $limit_from . ", " . $row_size;
		// } 
		// else{
		// echo 'second';
		// $pagg_qry = "SELECT ".$select."
		// PI.*,
		// PP.retail_price, PP.distribution_price, PP.distribution_margin,PP.retail_margin,
		// PC.core_range, PC.forecasted_daily_consumption_rate AS fdcr, PC.is_over_stocked, PC.is_SLOB,
		// PQI.stock_on_hand, PQI.desired_stock_level, PQI.reorder_level
		// FROM `ki_products_info` AS PI
		// LEFT JOIN `ki_product_prices_info` AS PP ON PP.product_id = PI.product_id
		// LEFT JOIN `ki_product_consumption_info` AS PC ON PC.product_id = PI.product_id 
		// LEFT JOIN `ki_product_quantites_info` AS PQI ON PQI.product_id = PI.product_id ".$join."
		// WHERE PI.delete_flag = 0 $query
		// ORDER BY ".safe_str($sort_on)." ".safe_str($sort_type)." 
		// LIMIT ".$limit_from.", ".$row_size;
		// }
		// echo $pagg_qry;
		$pagg_result = $con->query($pagg_qry);
		if (!$pagg_qry) {
			return $con->error;
			die;
		}

		$pagg_count = $pagg_result->num_rows;
		$pagging_list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}

		$data["total_records"] = $total_records;
		$data["total_pages"] = $total_pages;
		$data["pagging_list"] = $pagging_list;

		return $data;
	}

	function get_products_tab_details($inputdata)
	{
		global $con;
		$table = safe_str($inputdata['table']);
		$product_id = safe_str($inputdata['product_id']);
		// if(!empty($inputdata['store_id'])){
		// $store = "`store_id`=".$inputdata['store_id'];
		// }
		// else{
		// $store = "`store_id` IS NULL";
		// }
		// if(!empty($inputdata['distribution_id'])){
		// $distribution = "`distribution_id`=".$inputdata['distribution_id'];
		// }
		// else{
		// $distribution = "`distribution_id` IS NULL";
		// }

		$query = "select * from `" . safe_str($table) . "` where `product_id`=" . $product_id . " AND `location_type`='" . safe_str($inputdata['location_type']) . "' AND `location_id`='" . safe_str($inputdata['location_id']) . "' AND `delete_flag`=0";

		$result = $con->query($query);
		echo $con->error;
		$row = $result->fetch_assoc();
		// print_r($row);
		return $row;
	}

	function get_selected_product_locations($inputdata)
	{
		global $con;
		$list = array();
		$table = safe_str($inputdata['table']);
		$product_id = safe_str($inputdata['product_id']);
		$query = "SELECT `store_id`,`distribution_id` FROM `" . safe_str($table) . "` WHERE `product_id`=" . $product_id;
		$result = $con->query($query);
		$i = 0;
		while ($row = $result->fetch_assoc()) {
			$list[$i] = $row;
			$i++;
		}
		return $list;
	}

	function get_historical_daily_consumption_rate($inputdata)
	{
		// Products - consumption tab
		global $con;
		$historical_daily_consumption_rate = '';
		$units_sold = 0;
		// get units sold
		$sql = "select IFNULL(sum(ili.quantity),0) as units_sold from ki_invoice_line_items_info ili inner join ki_invoices_info i on i.invoice_id=ili.invoice_id and i.home_store_id='" . safe_str($inputdata['location_id']) . "' and i.home_store_type='" . safe_str($inputdata['location_type']) . "' and (date(i.created_on) between '" . safe_str($inputdata['start_date']) . "' AND '" . safe_str($inputdata['end_date']) . "') and i.delete_flag=0 where ili.product_id='" . safe_str($inputdata['product_id']) . "' and ili.delete_flag=0";
		$result = $con->query($sql);
		if ($result->num_rows) {
			$row = $result->fetch_assoc();
			$units_sold = $row['units_sold'];
		}
		// $current_date = date('Y-m-d H:i:s');
		$n_query = "select count(*) as non_trade_days from(select all_dates.selected_date from (
			select * from (
				select adddate('" . safe_str($inputdata['start_date']) . "', t2*100 + t1*10 + t0) selected_date from
				(select 0 t0 union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t0,
				(select 0 t1 union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t1,
				(select 0 t2 union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t2
			) v where selected_date between '" . safe_str($inputdata['start_date']) . "' and '" . safe_str($inputdata['end_date']) . "'
		) all_dates
		INNER JOIN
		ki_non_trade_recurring_days_info ntr 
		where (
			(	(all_dates.selected_date >= ntr.created_on and (all_dates.selected_date <= ntr.modified_on or ntr.modified_on is null)) and (WEEKDAY(all_dates.selected_date)+1) = ntr.day and ntr.location_id = '" . safe_str($inputdata['location_id']) . "' and ntr.location_type='" . safe_str($inputdata['location_type']) . "')
			or 
			(all_dates.selected_date in (select nt.day from ki_non_trade_days_info nt where nt.delete_flag=0 and nt.location_id = '" . safe_str($inputdata['location_id']) . "' and nt.location_type='" . safe_str($inputdata['location_type']) . "'))
		)) at";

		//$n_query = "select distinct count(*) as non_trade_days from(SELECT day FROM `ki_non_trade_days_info` WHERE `day` BETWEEN '".$inputdata['start_date']."' AND '".$inputdata['end_date']."' AND `location_type`=".$inputdata['location_type']." AND `location_id`=".$inputdata['location_id']." AND `delete_flag`=0 
		//UNION
		//SELECT DATE(ADDDATE(date(created_on), INTERVAL `day` DAY)) AS date FROM ki_non_trade_recurring_days_info where DATE(ADDDATE(date(created_on), INTERVAL `day` DAY)) between '".$inputdata['start_date']."' AND '".$inputdata['end_date']."' and location_id=".$inputdata['location_id']." and location_type=".$inputdata['location_type']." and delete_flag=0) at";
		$n_res = $con->query($n_query);
		// echo $con->error;
		$n_row = $n_res->fetch_assoc();
		$non_trade_days = $n_row['non_trade_days'];
		$z_query = "select count(*) as zero_quant_days from(select all_dates.selected_date from (
			select * from (
				select adddate('" . safe_str($inputdata['start_date']) . "', t2*100 + t1*10 + t0) selected_date from
				(select 0 t0 union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t0,
				(select 0 t1 union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t1,
				(select 0 t2 union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t2
			) v where selected_date between '" . safe_str($inputdata['start_date']) . "' and '" . safe_str($inputdata['end_date']) . "'
		) all_dates
		INNER JOIN
		ki_zero_qty_records_info ntr 
		where (
			(	(all_dates.selected_date >= ntr.date_qty_zero and (all_dates.selected_date <= ntr.date_qty_increased or ntr.date_qty_increased is null)) and ntr.location_id = '" . safe_str($inputdata['location_id']) . "' and ntr.location_type='" . safe_str($inputdata['location_type']) . "' AND `product_id`='" . safe_str($inputdata['product_id']) . "' AND `delete_flag`=0)
		)) at";
		// echo $z_query = "SELECT IFNULL(sum(DATEDIFF(date_qty_increased, date_qty_zero)),0) AS zero_quant_days FROM `ki_zero_qty_records_info` WHERE `date_qty_zero` BETWEEN '".safe_str($inputdata['start_date'])."' AND '".safe_str($inputdata['end_date'])."' AND `product_id`='".safe_str($inputdata['product_id'])."' AND `location_type`=".safe_str($inputdata['location_type'])." AND `location_id`=".safe_str($inputdata['location_id'])." AND `delete_flag`=0";
		$z_res = $con->query($z_query);
		// echo $con->error;
		$z_row = $z_res->fetch_assoc();
		$zero_quant_days = $z_row['zero_quant_days'];
		// echo $units_sold.' '.$non_trade_days.' '.$zero_quant_days;
		if ((90 - $non_trade_days - $zero_quant_days) != 0) {
			$historical_daily_consumption_rate = p_round($units_sold / (90 - $non_trade_days - $zero_quant_days));
		} else {
			$historical_daily_consumption_rate = 0;
		}
		return $historical_daily_consumption_rate;
	}

	/* function get_average_rate_of_change($inputdata){
		// Products - consumption tab
		global $con;
		$av_rate = 0;
		$v = send_rest(array(
			"function" => "get_historical_daily_consumption_rate",
			"product_id" => $inputdata['product_id'],
			"location_type" => $inputdata['location_type'],
			"location_id" => $inputdata['location_id'],
			"start_date" => date("Y-m-d",strtotime("-90 days")),
			"end_date" => date("Y-m-d")
		));
		$w = send_rest(array(
			"function" => "get_historical_daily_consumption_rate",
			"product_id" => $inputdata['product_id'],
			"location_type" => $inputdata['location_type'],
			"location_id" => $inputdata['location_id'],
			"start_date" => date("Y-m-d",strtotime("-180 days")),
			"end_date" => date("Y-m-d",strtotime("-91 days"))
		));
		$x = send_rest(array(
			"function" => "get_historical_daily_consumption_rate",
			"product_id" => $inputdata['product_id'],
			"location_type" => $inputdata['location_type'],
			"location_id" => $inputdata['location_id'],
			"start_date" => date("Y-m-d",strtotime("-270 days")),
			"end_date" => date("Y-m-d",strtotime("-181 days"))
		));
		$y = send_rest(array(
			"function" => "get_historical_daily_consumption_rate",
			"product_id" => $inputdata['product_id'],
			"location_type" => $inputdata['location_type'],
			"location_id" => $inputdata['location_id'],
			"start_date" => date("Y-m-d",strtotime("-360 days")),
			"end_date" => date("Y-m-d",strtotime("-271 days"))
		));
		$m = 0;
		if(!empty((float)$w)){
			$m = p_round((($v/$w)-1)*100);
		}
		$n = 0;
		if(!empty((float)$x)){
			$n = p_round((($w/$x)-1)*100);
		}
		$o = 0;
		if(!empty((float)$y)){
			$o = p_round((($x/$y)-1)*100);
		}
		$av_rate = p_round(($m+$n+$o)/3);
		return $av_rate;
	} */

	function get_forecasted_daily_consumption_rate($inputdata)
	{
		// Products - consumption tab
		// $av_rate = send_rest(array(
		// "function" => "get_average_rate_of_change",
		// "product_id" => $inputdata['product_id'],
		// "location_type" => $inputdata['location_type'],
		// "location_id" => $inputdata['location_id']
		// ));
		$data = array(
			"v" => 0,
			"w" => 0,
			"x" => 0,
			"y" => 0,
			"forecasted_daily_consumption_rate" => ''
		);

		$y = send_rest(array(
			"function" => "get_historical_daily_consumption_rate",
			"product_id" => $inputdata['product_id'],
			"location_type" => $inputdata['location_type'],
			"location_id" => $inputdata['location_id'],
			"start_date" => date("Y-m-d", strtotime("-360 days")),
			"end_date" => date("Y-m-d", strtotime("-271 days"))
		));
		$data['y'] = $y;
		$x = send_rest(array(
			"function" => "get_historical_daily_consumption_rate",
			"product_id" => $inputdata['product_id'],
			"location_type" => $inputdata['location_type'],
			"location_id" => $inputdata['location_id'],
			"start_date" => date("Y-m-d", strtotime("-270 days")),
			"end_date" => date("Y-m-d", strtotime("-181 days"))
		));
		$data['x'] = $x;
		$w = send_rest(array(
			"function" => "get_historical_daily_consumption_rate",
			"product_id" => $inputdata['product_id'],
			"location_type" => $inputdata['location_type'],
			"location_id" => $inputdata['location_id'],
			"start_date" => date("Y-m-d", strtotime("-180 days")),
			"end_date" => date("Y-m-d", strtotime("-91 days"))
		));
		$data['w'] = $w;
		$v = send_rest(array(
			"function" => "get_historical_daily_consumption_rate",
			"product_id" => $inputdata['product_id'],
			"location_type" => $inputdata['location_type'],
			"location_id" => $inputdata['location_id'],
			"start_date" => date("Y-m-d", strtotime("-90 days")),
			"end_date" => date("Y-m-d")
		));
		$data['v'] = $v;
		// print_r($data);
		// calculate linear regression for next 90 days 
		if (!($y == 0 && $x == 0 && $w == 0 && $v == 0)) {
			$forecasted_daily_consumption_rate = linear_regression_input($y, $x, $w, $v);
			$data['forecasted_daily_consumption_rate'] = $forecasted_daily_consumption_rate;
		}
		// print_r($data);
		// $forecasted_daily_consumption_rate = p_round(($inputdata['historical_daily_consumption_rate']*$av_rate)/100);
		return $data;
	}

	function get_forecasted_annual_demand($inputdata)
	{
		// Products - consumption tab
		$forecasted_annual_demand = '';
		// $av_rate = send_rest(array(
		// "function" => "get_average_rate_of_change",
		// "product_id" => $inputdata['product_id'],
		// "location_type" => $inputdata['location_type'],
		// "location_id" => $inputdata['location_id']
		// ));
		$f = send_rest(array(
			"function" => "get_forecasted_daily_consumption_rate",
			"product_id" => $inputdata['product_id'],
			"location_type" => $inputdata['location_type'],
			"location_id" => $inputdata['location_id'],
		));
		if (empty($f['forecasted_daily_consumption_rate'])) {
			$f['forecasted_daily_consumption_rate'] = 0;
		}
		$last_90_days = $f['v'];
		$last_180_days = $f['w'];
		$last_270_days = $f['x'];
		$y = $next_90_days = $f['forecasted_daily_consumption_rate'];
		// calculate linear regression for next 180 days 
		$x = $next_180_days = linear_regression_input($last_270_days, $last_180_days, $last_90_days, $next_90_days);
		// calculate linear regression for next 270 days 
		$w = $next_270_days = linear_regression_input($last_180_days, $last_90_days, $next_90_days, $next_180_days);
		// calculate linear regression for next 360 days 
		$v = $next_360_days = linear_regression_input($last_90_days, $next_90_days, $next_180_days, $next_270_days);
		// calculate forcasted annual demand
		// echo $v;
		if (!($y == 0 && $x == 0 && $w == 0 && $v == 0)) {
			$forecasted_annual_demand = round(($y + $x + $w + $v) * 90);
		}

		// $d = p_round(((($f*$av_rate)*$av_rate)*$av_rate)*$av_rate);
		return $forecasted_annual_demand;
	}

	function get_suppliers_list_for_products($inputdata)
	{
		global $con;
		$list = array();
		$qry = $supp_qry = '';
		if ($inputdata['location_type'] == 1) {
			$qry .= ' AND (`country`=2 OR B.`supplier_id`="' . REWA_INTERNATIONAL_ID . '")';
			// $qry .= ' AND `country`=2';
		}
		if (!empty($inputdata['enabled'])) {
			$qry .= ' AND `is_enabled`=1';
		}
		if (isset($inputdata['supplier_id']) && !empty($inputdata['supplier_id'])) {
			$supp_qry = "OR A.`supplier_id`='" . safe_str($inputdata['supplier_id']) . "'";
		}
		$query = "SELECT DISTINCT CONCAT(COALESCE(A.`contact_first_name`,''),' ', COALESCE(A.`contact_last_name`,'')) AS supplier_name, A.* FROM `ki_suppliers_info` AS A LEFT JOIN `ki_supplier_location_info` AS B ON A.`supplier_id`=B.`supplier_id` WHERE (`location_type`=" . safe_str($inputdata['location_type']) . " AND `location_id`=" . safe_str($inputdata['location_id']) . " AND A.`delete_flag`=0 AND B.`delete_flag`=0 " . $qry . ")" . $supp_qry . " ORDER BY A.preference,A.company_name ASC";
		// if($inputdata['location_type']==1 && $inputdata['location_id']==39){echo $query;}
		$result = $con->query($query);
		$i = 0;
		echo $con->error;
		while ($row = $result->fetch_assoc()) {
			$list[$i] = $row;
			$i++;
		}
		return $list;
	}

	function get_logistic_suppliers_list($inputdata)
	{
		global $con;
		$list = array();
		$query = "SELECT T1.*,company_name FROM `ki_product_logistic_suppliers_info` as T1 INNER JOIN `ki_suppliers_info` as T2 ON T1.`supplier_id`=T2.`supplier_id` WHERE T1.`product_id`=" . safe_str($inputdata['product_id']) . " AND T1.`delete_flag`=0 ORDER BY T1.`created_on`";
		$result = $con->query($query);
		$i = 0;
		echo $con->error;
		while ($row = $result->fetch_assoc()) {
			$list[$i] = $row;
			$i++;
		}
		return $list;
	}

	function get_selected_supplier_location($inputdata)
	{
		global $con;
		$data = array();
		$i = 0;
		$pagging_list = array();
		$where = '';
		if (!empty($inputdata['location_id']) && !empty($inputdata['location_type'])) {
			$where = ' supplier_location_id not in (select supplier_location_id from ki_supplier_location_info where location_id="' . safe_str($inputdata['location_id']) . '" and location_type="' . safe_str($inputdata['location_type']) . '" and delete_flag=0 and supplier_id="' . safe_str($inputdata['supplier_id']) . '") and ';
		}
		$sort_on = '';
		if (!empty($inputdata['sort_on'])) {
			$sort_on = ' order by location_name asc';
		}
		$pcount_qry = "select *, CONCAT(`location_type`, ' ', `location_id`) AS location from ki_supplier_location_info where " . $where . " supplier_id='" . safe_str($inputdata['supplier_id']) . "' and delete_flag=0 " . $sort_on;
		$pcount_result = $con->query($pcount_qry);
		if ($pcount_result->num_rows) {
			while ($row = $pcount_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}
		$data = $pagging_list;

		return $data;
	}

	function get_products_reorder_level($inputdata)
	{
		global $con;
		$reorder_level = '';
		$historical_daily_consumption_rate = send_rest(array(
			"function" => "get_historical_daily_consumption_rate",
			"product_id" => $inputdata['product_id'],
			"location_type" => $inputdata['location_type'],
			"location_id" => $inputdata['location_id'],
			"start_date" => date("Y-m-d", strtotime("-90 days")),
			"end_date" => date("Y-m-d")
		));
		$forecasted_daily_consumption_rate = send_rest(array(
			"function" => "get_forecasted_daily_consumption_rate",
			"product_id" => $inputdata['product_id'],
			"location_type" => $inputdata['location_type'],
			"location_id" => $inputdata['location_id'],
			"historical_daily_consumption_rate" => $historical_daily_consumption_rate
		));
		if (!isset($forecasted_daily_consumption_rate['forecasted_daily_consumption_rate']) || empty($forecasted_daily_consumption_rate['forecasted_daily_consumption_rate'])) {
			$forecasted_daily_consumption_rate = array('forecasted_daily_consumption_rate' => 0);
		}
		if ($inputdata['location_type'] == 1) {
			$store_id = safe_str($inputdata['location_id']);
		} elseif ($inputdata['location_type'] == 2) {
			$distribution_id = safe_str($inputdata['location_id']);
		} elseif ($inputdata['location_type'] == 3) {
			$production_id = safe_str($inputdata['location_id']);
		}
		$details = send_rest(array(
			"table" => "ki_product_logistics_info",
			"function" => "get_products_tab_details",
			"product_id" => $inputdata['product_id'],
			"location_type" => $inputdata['location_type'],
			"location_id" => $inputdata['location_id']
		));
		if (!empty($details) && !empty($details['manufacture_lead_time'])) {
			$manufacture_lead_time = $details['manufacture_lead_time'];
		} else {
			$manufacture_lead_time = 0;
		}
		if ($inputdata['location_type'] == 1) {
			$get_details = send_rest(array(
				"table" => "ki_stores_info",
				"function" => "get_details",
				"key" => "store_id",
				"value" => $store_id
			));

			$reorder_level = round(($get_details['avg_delivery_time'] * $forecasted_daily_consumption_rate['forecasted_daily_consumption_rate']) + ($forecasted_daily_consumption_rate['forecasted_daily_consumption_rate'] * 8));
		} else {
			if ($inputdata['location_type'] == 2) {
				$get_details = send_rest(array(
					"table" => "ki_distribution_branches_info",
					"function" => "get_details",
					"key" => "distribution_branch_id",
					"value" => $distribution_id
				));
			} elseif ($inputdata['location_type'] == 3) {
				$get_details = send_rest(array(
					"table" => "ki_production_info",
					"function" => "get_details",
					"key" => "production_id",
					"value" => $production_id
				));
			}
			// print_r($get_details);
			$avg_shipping_time = 0;
			if (!empty($get_details['avg_shipping_time'])) {
				$avg_shipping_time = $get_details['avg_shipping_time'];
			}
			// echo $forecasted_daily_consumption_rate['forecasted_daily_consumption_rate'].'/'.$manufacture_lead_time.'/'.$avg_shipping_time.'/';die;
			$reorder_level = round(($manufacture_lead_time + 20 + $avg_shipping_time) * $forecasted_daily_consumption_rate['forecasted_daily_consumption_rate']);
		}
		return $reorder_level;
	}

	function validate_tax_name($inputdata)
	{
		global $con;
		$where = "";
		if (!empty($inputdata['id_value'])) {
			$where .= " and " . safe_str($inputdata['id_key']) . " != " . safe_str($inputdata['id_value']);
		}
		$query = "SELECT * FROM `ki_meta_taxes_info` WHERE `tax_name`='" . safe_str($inputdata['tax_name']) . "' AND `tax_value`=" . safe_str($inputdata['tax_value']) . " AND `delete_flag`=0 " . $where;
		$pcount_result = $con->query($query);
		$pcount_row = $pcount_result->fetch_assoc();
		return $pcount_row;
	}

	function get_taxes_list($inputdata)
	{
		global $con;
		$details = array();
		$query = "SELECT *,CONCAT(COALESCE(`tax_name`,''),' ', COALESCE(`tax_value`,'')) AS tax FROM `ki_meta_taxes_info` WHERE `is_enabled`=1 AND `delete_flag`=0 ORDER BY `tax_name`,`tax_value`";
		$query_result = $con->query($query);
		if ($con->query($query)) {
			$i = 0;
			while ($row = $query_result->fetch_assoc()) {
				$details[$i] = $row;
				$i++;
			}
		} else {
			echo $con->error;
		}
		return $details;
	}

	function find_next_login_location($inputdata)
	{
		$result = array();
		$all_location = send_rest(array(
			"function" => "get_selected_user_locations",
			"user_id" => $inputdata['user_id']
		));
		if (sizeof($all_location) > 1) {
			$count = 0;
			foreach ($all_location as $row) {
				/* if($row['location_type']==1){
					$table = "ki_stores_info";
					$key = "store_id";
				}elseif($row['location_type']==2){
					$table = "ki_distribution_branches_info";
					$key = "distribution_branch_id";
				}elseif($row['location_type']==3){
					$table = "ki_production_info";
					$key = "production_id";
				}
				$is_enabled = send_rest(array(
					"table" => $table,
					"function" => "check_enability",
					"key" => $key,
					"id" => $row['location_id']
				));
				if($is_enabled!=1){
					unset($all_location[$count]);
				} */
				$count++;
			}
			if (!empty($all_location)) {
				$type = "";
				if (in_array(3, array_column($all_location, 'location_type'))) {
					$type = 3;
				} elseif (in_array(2, array_column($all_location, 'location_type'))) {
					$type = 2;
				} elseif (in_array(1, array_column($all_location, 'location_type'))) {
					$type = 1;
				}
				$result = send_rest(array(
					"function" => "get_location_info",
					"user_id" => $inputdata['user_id'],
					"location_type" => $type
				));
			}
		}
		return $result;
	}

	function get_location_info($inputdata)
	{
		global $con;
		$data = array();
		$pcount_qry = "SELECT ULI.*, COALESCE(SI.`timezone`,DBI.`timezone`,PI.`timezone`) AS timezone FROM ki_user_locations_info ULI LEFT JOIN `ki_stores_info` SI ON ULI.`location_type`=1 AND ULI.`location_id`=SI.`store_id` LEFT JOIN `ki_distribution_branches_info` DBI ON ULI.`location_type`=2 AND ULI.`location_id`=DBI.`distribution_branch_id` LEFT JOIN `ki_production_info` PI ON ULI.`location_type`=3 AND ULI.`location_id`=PI.`production_id` WHERE `location_type`='" . safe_str($inputdata['location_type']) . "' AND `user_id`='" . safe_str($inputdata['user_id']) . "' AND ULI.`delete_flag`=0 ORDER BY `location_name` ASC";
		$pcount_result = $con->query($pcount_qry);
		echo $con->error;
		$data = $pcount_result->fetch_assoc();

		return $data;
	}

	function get_enabled_selected_user_skills($inputdata)
	{
		global $con;
		$all_selected_skills = send_rest(array(
			"function" => "get_selected_user_skills",
			"user_id" => $inputdata['user_id']
		));
		$disabled_category = array();
		foreach ($all_selected_skills as $row) {
			$skill_info = send_rest(array(
				"function" => "get_user_skill_details",
				"table1" => "ki_skills_info",
				"table2" => "ki_skill_categories_info",
				"key" => "skill_id",
				"value" => $row['skill_id']
			));
			if ($skill_info['enabled'] == 0) {
				if (!in_array($skill_info['category_name'], $disabled_category)) {
					$disabled_category[] = $skill_info['category_name'];
				}
			}
		}
		$msg =  '';
		if (!empty($disabled_category)) {
			$last = '';
			$is = ' is ';
			$aaa = ' this category ';
			$size = sizeof($disabled_category);
			if ($size > 1) {
				$last = ' and ' . $disabled_category[$size - 1];
				$aaa = " these categories ";
				$is = " are ";
				unset($disabled_category[$size - 1]);
			}
			$msg = "* " . implode(', ', $disabled_category) . '' . $last . " " . $is . "disabled by admin. Skills listed under" . $aaa . "" . $is . "automatically disabled.";
		}
		return $msg;
	}

	function get_production_purchase_pagging_list($inputdata)
	{
		global $con;
		$data = array(
			"total_records" => 0,
			"total_pages" => 0,
			"pagging_list" => array()
		);
		$page_no = safe_str($inputdata['page_no']);
		$row_size = safe_str($inputdata['row_size']);
		$sort_on = safe_str($inputdata['sort_on']);
		$sort_type = safe_str($inputdata['sort_type']);
		$pcount_qry = "SELECT COUNT(*) AS total_count FROM `ki_production_purchase_info` as a INNER JOIN `ki_suppliers_info` AS b ON a.`supplier_id`=b.`supplier_id` WHERE a.`delete_flag`=0 ";
		$pcount_result = $con->query($pcount_qry);
		$pcount_row = $pcount_result->fetch_assoc();
		$total_records = $pcount_row["total_count"];

		@$total_pages = ceil($total_records / $row_size);
		if ($total_pages == 0) {
			$total_pages = 1;
		}
		if ($page_no > $total_pages) {
			$page_no = $total_pages;
		}
		$limit_from = ($row_size * $page_no) - $row_size;

		$pagg_qry = "SELECT a.*,company_name,CONCAT(COALESCE(b.`contact_first_name`,''),' ', COALESCE(b.`contact_last_name`,'')) AS supplier_name FROM `ki_production_purchase_info` as a INNER JOIN `ki_suppliers_info` AS b ON a.`supplier_id`=b.`supplier_id` WHERE a.`delete_flag`=0 order by " . safe_str($sort_on) . " " . safe_str($sort_type) . " LIMIT " . $limit_from . ", " . $row_size;
		$pagg_result = $con->query($pagg_qry);
		$pagg_count = $pagg_result->num_rows;
		$pagging_list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}

		$data["total_records"] = $total_records;
		$data["total_pages"] = $total_pages;
		$data["pagging_list"] = $pagging_list;

		return $data;
	}

	function nominated_contacts_email_already_exists($inputdata)
	{
		global $con;
		$table = safe_str($inputdata['table']);
		//echo "fdsas";
		$where = '';
		if (!empty($inputdata['id'])) {
			$where .= " and " . safe_str($inputdata['id_key']) . "!='" . safe_str($inputdata['id']) . "'";
		}
		if (!empty($inputdata['email'])) {
			$where .= " and " . safe_str($inputdata['email_key']) . "='" . safe_str($inputdata['email']) . "'";
		}
		$pcount_qry = "select * from `" . safe_str($table) . "` where delete_flag=0 and `customer_id`='" . safe_str($inputdata['customer_id']) . "'" . $where;
		//	var_dump($pcount_qry);
		$pcount_result = $con->query($pcount_qry);
		$pcount_row = $pcount_result->fetch_assoc();
		return $pcount_row;
	}

	function phone_number_already_exists($inputdata)
	{
		global $con;
		$table = safe_str($inputdata['table']);
		//echo "fdsas";
		$where = '';
		if (!empty($inputdata['id'])) {
			$where .= " and " . safe_str($inputdata['id_key']) . "!='" . safe_str($inputdata['id']) . "'";
		}
		if (!empty($inputdata['ph_no'])) {
			$where .= " and " . safe_str($inputdata['phno_key']) . "='" . safe_str($inputdata['ph_no']) . "'";
		}
		$pcount_qry = "select * from `" . safe_str($table) . "` where delete_flag=0" . $where;
		//	var_dump($pcount_qry);
		$pcount_result = $con->query($pcount_qry);
		$pcount_row = $pcount_result->fetch_assoc();
		return $pcount_row;
	}

	function nominated_contacts_phno_already_exists($inputdata)
	{
		global $con;
		$table = safe_str($inputdata['table']);
		//echo "fdsas";
		$where = '';
		if (!empty($inputdata['id'])) {
			$where .= " and " . safe_str($inputdata['id_key']) . "!='" . safe_str($inputdata['id']) . "'";
		}
		if (!empty($inputdata['ph_no'])) {
			$where .= " and " . safe_str($inputdata['phno_key']) . "='" . safe_str($inputdata['ph_no']) . "'";
		}
		$pcount_qry = "select * from `" . safe_str($table) . "` where delete_flag=0 and `customer_id`='" . safe_str($inputdata['customer_id']) . "'" . $where;
		//	var_dump($pcount_qry);
		$pcount_result = $con->query($pcount_qry);
		$pcount_row = $pcount_result->fetch_assoc();
		return $pcount_row;
	}

	function update_product_logistics_info($inputdata)
	{
		global $con;
		$data = array(
			"status" => 0,
			"errors" => array()
		);
		//	$table = $inputdata['table'];
		$result = send_rest(array(
			"table" => "ki_products_info",
			"function" => "update",
			"fields_data" => $inputdata['fields_data'],
			"key" => "product_id",
			"value" => $inputdata['product_id']
		));
		$associated_locations_list = send_rest(array(
			"table" => "ki_supplier_location_info",
			"function" => "get_list",
			"key" => "supplier_id",
			"value" => $inputdata['supplier_id']
		));
		$associated_locations = $associated_locations_list['list'];
		// echo "<pre>";print_r($associated_locations);echo "</pre>";
		// $error = 0;
		if ($inputdata['action'] == 'same_for_all_loc') {
			foreach ($associated_locations as $loc) {
				// echo 1;
				$up_qry = "UPDATE `ki_product_logistics_info` SET `is_diff_supplier`=0, `supplier_id`=NULL, `modified_on`='" . date("Y-m-d H:i:s") . "' WHERE `product_id`='" . safe_str($inputdata['product_id']) . "' AND `location_type`='" . $loc['location_type'] . "' AND `location_id`='" . $loc['location_id'] . "'";
				$up_result = $con->query($up_qry);
				if ($up_result) {
					// $data["status"]=1;
				} else {
					$data["errors"][] = $con->error;
				}
			}
		} elseif ($inputdata['action'] == 'same_for_all_stores') {
			foreach ($associated_locations as $loc) {
				// echo 2;
				$up_qry = "UPDATE `ki_product_logistics_info` SET `is_diff_supplier`=0, `supplier_id`=NULL, `modified_on`='" . date("Y-m-d H:i:s") . "' WHERE `product_id`='" . safe_str($inputdata['product_id']) . "' AND`location_type`=1 AND `location_id`='" . $loc['location_id'] . "'";
				$up_result = $con->query($up_qry);
				if ($up_result) {
					// $data["status"]=1;
				} else {
					$data["errors"][] = $con->error;
				}
			}
		}
		if (empty($data["errors"])) {
			$data["status"] = 1;
		}
		return $data;
	}

	function update_onboarding_question($inputdata)
	{
		global $con;
		$data = array(
			"status" => 0,
			"errors" => array()
		);
		$list = array();
		$created_on = safe_str($inputdata['created_on']);
		$find = "SELECT option_id, option_val FROM `ki_onboarding_options_info` WHERE `ques_id`=" . safe_str($inputdata['ques_id']) . " AND `delete_flag`=0";
		$result = $con->query($find);
		$data["errors"][] = $con->error;
		$i = 0;
		while ($row = $result->fetch_assoc()) {
			$list[$i] = $row;
			$i++;
		}
		$qryError = false;
		if ($inputdata['options_data']) {
			$insertQryArray = array();
			$updateQryArray = array();
			$i = 0;
			foreach ($inputdata['options_data'] as $option) {
				// echo $option;
				// echo $inputdata['terms_data'][$i];
				if (in_array($option, array_column($list, 'option_val'))) {
					$key = array_search($option, array_column($list, 'option_val'));
					$updateQryArray = $con->query("UPDATE ki_onboarding_options_info SET `option_val`='" . safe_str($option) . "', `term`='" . safe_str($inputdata['terms_data'][$i]) . "', `modified_on`='" . date("Y-m-d H:i:s") . "' WHERE `option_id`='" . $list[$key]['option_id'] . "'");
					if (!$updateQryArray) {
						$qryError = true;
						$data["errors"][] = $con->error;
					}
					unset($list[$key]);
					$list = array_values($list);
				} else {
					$insertQryArray[] = "(" . safe_str($inputdata['ques_id']) . ", '" . safe_str($option) . "', '" . safe_str($inputdata['terms_data'][$i]) . "', '" . $created_on . "')";
				}
				$i++;
			}
			if ($insertQryArray) {
				$insert0 = $con->query("INSERT INTO `ki_onboarding_options_info` ( `ques_id`, `option_val`, `term`, `created_on`) VALUES " . implode(', ', $insertQryArray));
				if (!$insert0) {
					$qryError = true;
					$data["errors"][] = $con->error;
				}
			}
		}
		if (!empty($list)) {
			$qryStr = array();
			foreach ($list as $opt) {
				$qryStr[] = " `option_id`='" . $opt['option_id'] . "' ";
			}
			$deleteQry = $con->query("UPDATE `ki_onboarding_options_info` SET `delete_flag`=1, `modified_on`='" . date("Y-m-d H:i:s") . "' WHERE `ques_id`=" . safe_str($inputdata['ques_id']) . " AND (" . implode(' OR ', $qryStr) . ")");
			if (!$deleteQry) {
				$qryError = true;
				$data["errors"][] = $con->error;
			}
		}
		if (!$qryError) {
			$data["status"] = 1;
		}
		return $data;
	}

	/* function get_jobs_pagging_list($inputdata){
		global $con;
		$data = array(
			"total_records" => 0,
			"total_pages" => 0,
			"pagging_list" => array()
		);
		$page_no = $inputdata['page_no'];
		$row_size = $inputdata['row_size'];
		$sort_on = $inputdata['sort_on'];
		$sort_type = $inputdata['sort_type'];
		$query='';
		if(!empty($inputdata['query'])){
			$query = $inputdata['query'];
		}
		
		$pcount_qry = "SELECT COUNT(*) AS total_count FROM `ki_jobs_info` AB INNER JOIN `ki_users_info` CD ON AB.`user_id` = CD.`user_id` AND `is_enabled`=1 AND CD.`delete_flag` = 0 INNER JOIN `ki_customers_info` EF ON AB.`customer_id` = EF.`customer_id` AND EF.`delete_flag` = '0' WHERE AB.`delete_flag` = 0".$query;
		$pcount_result = $con->query($pcount_qry);
		$pcount_row = $pcount_result->fetch_assoc();
		$total_records = $pcount_row["total_count"];
		
		@$total_pages = ceil($total_records/$row_size);
		if($total_pages==0){ $total_pages = 1; }
		if($page_no > $total_pages){ $page_no = $total_pages; }
		$limit_from = ($row_size * $page_no)-$row_size;
		
		$pagg_qry = "SELECT AB.*, CONCAT( COALESCE(CD.`first_name`, ''), ' ', COALESCE(CD.`last_name`, '') ) AS user_name, CONCAT( COALESCE(EF.`first_name`, ''), ' ', COALESCE(EF.`last_name`, ''), ' ', COALESCE(EF.`business_name`, '') ) AS customer_name FROM `ki_jobs_info` AB INNER JOIN `ki_users_info` CD ON AB.`user_id` = CD.`user_id` AND `is_enabled`=1 AND CD.`delete_flag` = 0 INNER JOIN `ki_customers_info` EF ON AB.`customer_id` = EF.`customer_id` AND EF.`delete_flag` = '0' WHERE AB.`delete_flag` = 0".$query." order by ".safe_str($sort_on)." ".safe_str($sort_type)." LIMIT ".$limit_from.", ".$row_size;
		$pagg_result = $con->query($pagg_qry);
		$pagg_count = $pagg_result->num_rows;
		$pagging_list = array();
		if($pagg_count > 0){
            $i=0;
			while($row = $pagg_result->fetch_assoc()){
		        $pagging_list[$i] = $row;
		    	$i++;
		    }
		}
		$data["total_records"] = $total_records;
		$data["total_pages"] = $total_pages;
		$data["pagging_list"] = $pagging_list;
		
		return $data;
	} */

	function get_job_details($inputdata)
	{
		global $con;
		$pcount_qry = "SELECT 
							JI.*, 
							EI.`estimate_number` AS parent_estimate_number, EI.`status` AS parent_estimate_status, EI.`total_amount` AS parent_estimate_total_amount, 
							CONCAT(COALESCE(UI1.`first_name`, ''),' ',COALESCE(UI1.`last_name`, '')) AS assigned_technician, CONCAT(COALESCE(UI2.`first_name`, ''),' ',COALESCE(UI2.`last_name`, '')) AS created_by, 
							CASE
								WHEN SI.`lead_tech_id` IS NOT NULL AND SI.`lead_tech_id`!=0 THEN CONCAT(COALESCE(UI5.`first_name`, ''),' ',COALESCE(UI5.`last_name`, ''))
								ELSE CONCAT(COALESCE(UID.`first_name`, ''),' ',COALESCE(UID.`last_name`, ''))
							END AS lead_technician, 
							CASE
								WHEN SI.`lead_tech_id` IS NOT NULL AND SI.`lead_tech_id`!=0 THEN UI5.`email`
								ELSE UID.`email`
							END AS lead_technician_email, CI.`business_name` AS cust_business_name, CI.`first_name` AS cust_first_name, CI.`last_name` AS cust_last_name, CI.`phone` AS cust_phone_no, CI.`email` AS cust_email, CONCAT(COALESCE(CI.`first_name`, ''),' ',COALESCE(CI.`last_name`, ''),' ',COALESCE(CI.`business_name`, '')) AS customer_name, CI.`address` AS customer_address, CI.`suburb_town` AS customer_suburb, CI.`state` AS customer_state, CI.`is_loyalty_rewards_registered`, CI.`credit_terms`, CI.`is_unsubscribed_to_marketing`, CI.`delete_flag` AS cust_delete_flag, 
							COALESCE(SI.`mobile_repair_supplier`, DBI.`mobile_repair_supplier`,PI.`mobile_repair_supplier`) AS mobile_repair_supplier, 
							COALESCE(SI.`drone_repair_supplier`, DBI.`drone_repair_supplier`,PI.`drone_repair_supplier`) AS drone_repair_supplier, 
							COALESCE(SI.`store_name`, DBI.`distribution_name`,PI.`production_name`) AS location_name, COALESCE(SI.`email`,DBI.`email`,PI.`email`) AS location_email, COALESCE(SI.`phone_number`,DBI.`phone_number`,PI.`phone_number`) AS location_phone_number, COALESCE(SI.`address`,DBI.`address`,PI.`address`) AS location_address, COALESCE(SI.`suburb`,DBI.`suburb`,PI.`suburb`) AS location_suburb, COALESCE(SI.`postcode`,DBI.`postcode`,PI.`postcode`) AS location_postcode, COALESCE(SI.`state`,DBI.`state`,PI.`state`) AS location_state, COALESCE(SI.`directions`,DBI.`directions`,PI.`directions`) AS location_directions, COALESCE(SI.`country`,DBI.`country`,PI.`country`) AS location_country, COALESCE(SI.`ABN`, DBI.`ABN`, PI.`ABN`) AS location_ABN, COALESCE(SI.`BSB`, DBI.`BSB`, PI.`BSB`) AS location_BSB, COALESCE(SI.`account_number`, DBI.`account_number`, PI.`account_number`) AS location_account_number, COALESCE(SI.`bdm_commission`, DBI.`bdm_commission`, PI.`bdm_commission`) AS location_bdm_commission, 
							COALESCE(SI.`facebook_link`, DBI.`facebook_link`, PI.`facebook_link`) AS location_facebook, 
							COALESCE(SI.`google_link`, DBI.`google_link`, PI.`google_link`) AS location_google, 
							TTI.`ticket_type_name`, TTI.`recommended_solution_threshold`, TTI.`icon_picture` AS ticket_icon_picture, BI.`brand_name`, MI.`model_name`, TTI.`onboarding_notice` as ticket_type_notice, BI.`onboarding_notice` as brand_notice, MI.`onboarding_notice` as model_notice, 
							CONCAT(COALESCE(UI3.`first_name`, ''),' ',COALESCE(UI3.`last_name`, '')) AS pre_test_user, 
							CONCAT(COALESCE(UI4.`first_name`, ''),' ',COALESCE(UI4.`last_name`, '')) AS post_test_user, 
							CASE 
								WHEN JI.`job_type`=1 AND JI.`due_date`<'" . date("Y-m-d H:i:s") . "' AND (JI.`status`=1 OR JI.`status`=2 OR JI.`status`=5 OR JI.`status`=6) AND '" . date("Y-m-d H:i:s") . "' >= DATE_ADD(JI.`last_activity_date`, INTERVAL TTI.`stale_hours` HOUR) THEN 'Overdue & Stale' 
								WHEN JI.`due_date`<'" . date("Y-m-d H:i:s") . "' AND JI.`status`!=7 AND JI.`status`!=8 AND JI.`status`!=9 AND JI.`status`!=10 THEN 'Overdue' 
								WHEN JI.`job_type`=1  AND JI.`status`!=7 AND JI.`status`!=8 AND JI.`status`!=9 AND JI.`status`!=10 AND '" . date("Y-m-d H:i:s") . "' BETWEEN DATE_SUB(JI.`due_date`, INTERVAL TTI.`close_to_due_hours` HOUR) AND JI.`due_date` THEN 'Close to Due' 
								WHEN JI.`job_type`=1 AND (JI.`status`=1 OR JI.`status`=2 OR JI.`status`=5 OR JI.`status`=6) AND '" . date("Y-m-d H:i:s") . "' >= DATE_ADD(JI.`last_activity_date`, INTERVAL TTI.`stale_hours` HOUR) THEN 'Stale' 
								WHEN JI.`job_type`=1 AND (JI.`status`=8 OR JI.`status`=7) THEN 'Invoice/Offboard'
								WHEN JI.`job_type`=1 AND JI.`status`=9 THEN 'Finished'
							END AS considered 
						FROM 
							`ki_jobs_info` JI 
						LEFT JOIN `ki_estimates_info` EI ON 
							JI.`estimate_id` = EI.`estimate_id` AND EI.`delete_flag` = 0 
						LEFT JOIN `ki_users_info` UI1 ON 
							JI.`assigned_tech` = UI1.`user_id` AND UI1.`delete_flag` = 0 
						LEFT JOIN `ki_users_info` UI2 ON 
							JI.`user_id` = UI2.`user_id` AND UI2.`delete_flag` = 0 
						LEFT JOIN `ki_customers_info` CI ON 
							JI.`customer_id` = CI.`customer_id` 
						LEFT JOIN `ki_stores_info` SI ON 
							JI.`home_store_type` = 1 AND JI.`home_store_id` = SI.`store_id` AND SI.`delete_flag` = 0 
						LEFT JOIN `ki_users_info` UI5 ON 
							SI.`lead_tech_id` = UI5.`user_id` AND UI5.`delete_flag` = 0 
						LEFT JOIN `ki_users_info` UID ON 
							UID.`user_id`='" . get_meta_value(27) . "' AND UID.`is_enabled`=1 AND UID.`delete_flag`=0 
						LEFT JOIN `ki_distribution_branches_info` DBI ON 
							JI.`home_store_type` = 2 AND JI.`home_store_id` = DBI.`distribution_branch_id` AND DBI.`delete_flag` = 0 
						LEFT JOIN `ki_production_info` PI ON 
							JI.`home_store_type` = 3 AND JI.`home_store_id` = PI.`production_id` AND PI.`delete_flag` = 0 
						LEFT JOIN `ki_ticket_types_info` TTI ON 
							JI.`ticket_type_id` = TTI.`ticket_type_id` AND TTI.`delete_flag` = 0 
						LEFT JOIN `ki_brands_info` BI ON 
							JI.`brand_id` = BI.`brand_id` AND BI.`delete_flag` = 0 
						LEFT JOIN `ki_models_info` MI ON 
							JI.`model_id` = MI.`model_id` AND MI.`delete_flag` = 0 
						LEFT JOIN `ki_users_info` UI3 ON 
							JI.`pre_test_done_by` = UI3.`user_id` AND UI3.`delete_flag` = 0 
						LEFT JOIN `ki_users_info` UI4 ON 
							JI.`post_test_done_by` = UI4.`user_id`  AND UI4.`delete_flag` = 0 
						WHERE 
							JI.`delete_flag` = 0 AND JI.`" . safe_str($inputdata['key']) . "`='" . safe_str($inputdata['value']) . "'";
		//	var_dump($pcount_qry);
		$pcount_result = $con->query($pcount_qry);
		if ($con->query($pcount_qry)) {
			$row = $pcount_result->fetch_assoc();
			if (!empty($row)) {
				$address = [$row['location_address'], $row['location_directions'], $row['location_suburb'], $row['location_state'], $row['location_country'], $row['location_postcode']];
				$row['concatenated_address'] = implode(", ", array_filter($address));
			}
		} else {
			echo $con->error;
		}
		// print_r($row);
		return $row;
	}

	function get_selected_canned_responses($inputdata)
	{
		global $con;
		$details = array();
		$query = "SELECT * FROM `ki_canned_responses_mapping_info` WHERE `mapping_type`='" . safe_str($inputdata['type']) . "' AND `mapping_type_id`='" . safe_str($inputdata['id']) . "' AND `delete_flag`=0";
		$query_result = $con->query($query);
		if ($con->query($query)) {
			$i = 0;
			while ($row = $query_result->fetch_assoc()) {
				$details[$i] = $row;
				$i++;
			}
		} else {
			echo $con->error;
		}
		return $details;
	}

	function get_selected_model_canned_responses($inputdata)
	{
		global $con;
		$details = array();
		$query = "SELECT * FROM `ki_model_canned_response_mapping_info` WHERE `model_id`='" . safe_str($inputdata['model_id']) . "' AND `work_to_complete_id`='" . safe_str($inputdata['work_to_complete_id']) . "' AND `delete_flag`=0";
		$query_result = $con->query($query);
		if ($con->query($query)) {
			$i = 0;
			while ($row = $query_result->fetch_assoc()) {
				$details[$i] = $row;
				$i++;
			}
		} else {
			echo $con->error;
		}
		return $details;
	}

	function get_customers_list($inputdata)
	{
		global $con;
		$pagg_qry = "select *,CONCAT(COALESCE(`address`,''),' ', COALESCE(`suburb_town`,''),' ', COALESCE(`state`,'')) AS full_address,CONCAT(COALESCE(`first_name`,''),' ', COALESCE(`last_name`,'')) AS name from `ki_customers_info` where delete_flag=0 order by name";
		$pagg_result = $con->query($pagg_qry);
		$pagg_count = $pagg_result->num_rows;
		$pagging_list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}
		return $pagging_list;
	}

	function delete_model_canned_responses($inputdata)
	{
		global $con;
		$data = array(
			"status" => 0,
			"errors" => array()
		);
		$wtc = '';
		foreach ($inputdata['work_to_complete'] as $work_to_complete) {
			$wtc .= ' AND `work_to_complete_id`!="' . safe_str($work_to_complete) . '" ';
		}
		$qry = 'DELETE FROM `ki_model_canned_response_mapping_info` WHERE `model_id`="' . safe_str($inputdata['model_id']) . '" AND `delete_flag`=0 ' . $wtc;
		if ($con->query($qry) == true) {
			$data["status"] = 1;
		} else {
			$data["error"][] = $con->error;
		}
		return $data;
	}

	function get_wtc_associated_model_details($inputdata)
	{
		global $con;
		$pagg_qry = "SELECT A.*,B.`work_to_complete` FROM `ki_model_worktocomplete_mapping_info` AS A INNER JOIN `ki_work_to_complete_info` AS B ON A.`work_to_complete_id`=B.`work_to_complete_id` WHERE A.`model_id`='" . safe_str($inputdata['model_id']) . "' AND A.`delete_flag`=0 AND B.`is_enabled`=1 AND B.`delete_flag`=0";
		$pagg_result = $con->query($pagg_qry);
		$pagg_count = $pagg_result->num_rows;
		$pagging_list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}
		return $pagging_list;
	}

	function get_model_value_adds_details($inputdata)
	{
		global $con;
		// $list = array();
		// $value_adds = send_rest(array(
		// "function" => "get_list",
		// "table" => "ki_models_value_adds_info",
		// "key" => "model_id",
		// "value" => $inputdata['model_id']
		// ));
		// $value_adds_list = $value_adds['list'];
		// $index=0;
		// foreach($value_adds_list as $value_add){
		// $list[$index]['value_adds_type'] = $value_add['value_adds_type'];
		// }
		$pagg_qry = "SELECT AB.`value_adds_id`,AB.`value_adds_type`, EF.`product_id`, EF.`product_name` FROM `ki_models_value_adds_info` AB INNER JOIN `ki_model_value_adds_product_mapping_info` CD ON AB.`value_adds_id`=CD.`value_adds_id` AND CD.`delete_flag`=0 INNER JOIN `ki_products_info` EF ON CD.`product_id`=EF.`product_id` AND EF.`status`!=3 AND EF.`delete_flag`=0 WHERE AB.`model_id`='" . safe_str($inputdata['model_id']) . "' AND AB.`delete_flag`=0";
		$pagg_result = $con->query($pagg_qry);
		$pagg_count = $pagg_result->num_rows;
		$pagging_list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$key = array_search($row['value_adds_type'], array_column($pagging_list, 'value_adds_type'));
				// var_dump($key);
				if ($key === FALSE) {
					$pagging_list[$i]['value_adds_id'] = $row['value_adds_id'];
					$pagging_list[$i]['value_adds_type'] = $row['value_adds_type'];
					$array = array('product_id' => $row['product_id'], 'product_name' => $row['product_name']);
					$pagging_list[$i]['products'] = array();
					array_push($pagging_list[$i]['products'], $array);
					$i++;
				} else {
					$array = array('product_id' => $row['product_id'], 'product_name' => $row['product_name']);
					array_push($pagging_list[$key]['products'], $array);
				}
			}
		}
		return $pagging_list;
	}

	function get_model_value_adds_products_details($inputdata)
	{
		global $con;
		$pagg_qry = "SELECT A.`product_id`,B.`product_name` FROM `ki_model_value_adds_product_mapping_info` AS A INNER JOIN `ki_products_info` AS B ON A.`product_id`=B.`product_id` WHERE A.`model_id`='" . safe_str($inputdata['model_id']) . "' AND A.`value_adds_id`='" . safe_str($inputdata['value_adds_id']) . "' AND A.`delete_flag`=0 AND B.`delete_flag`=0 ORDER BY B.`product_name`";
		$pagg_result = $con->query($pagg_qry);
		$pagg_count = $pagg_result->num_rows;
		$pagging_list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}
		return $pagging_list;
	}

	function get_onboarding_ques_option_details($inputdata)
	{
		global $con;
		$find = "SELECT * FROM `ki_onboarding_options_info` WHERE `delete_flag`=0 ORDER BY `option_val`";
		$result = $con->query($find);
		$i = 0;
		$data["errors"][] = $con->error;
		while ($row = $result->fetch_assoc()) {
			$list[$i] = $row;
			$i++;
		}
		$final_list = array();
		if (!empty($list)) {
			foreach ($list as $row) {
				$ques_id = $row['ques_id'];
				$final_list[$ques_id][] = $row;
			}
		}
		// print_r($final_list);
		return $final_list;
	}

	function create_job_custom_field($inputdata)
	{
		global $con;
		$data = array(
			"status" => 0,
			"errors" => array()
		);
		$Encryption = new Encryption();
		$job_details = send_rest(array(
			"function" => "get_details",
			"table" => "ki_jobs_info",
			"key" => "job_id",
			"value" => $inputdata['job_id']
		));
		$job_wtc_details = send_rest(array(
			"function" => "get_list",
			"table" => "ki_job_work_to_complete_info",
			"key" => "job_id",
			"value" => $inputdata['job_id']
		));
		$wtc = array();
		foreach ($job_wtc_details['list'] as $detail) {
			$wtc[] = "(`mapping_type`='3' AND `mapping_id`='" . $detail['work_to_complete_id'] . "')";
		}
		$wtc = " OR " . implode(' OR ', $wtc);
		$pagg_qry = "SELECT * FROM ki_custom_fields_mapping_info WHERE `delete_flag`=0 AND (`mapping_type`='1' AND `mapping_id`='" . $job_details['ticket_type_id'] . "') OR (`mapping_type`='2' AND `mapping_id`='" . $job_details['brand_id'] . "') " . $wtc;
		$pagg_result = $con->query($pagg_qry);
		$pagg_count = $pagg_result->num_rows;
		$cf_list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$cf_list[$i] = $row;
				$i++;
			}
		}
		foreach ($cf_list as $cust_field) {
			$key = $Encryption->encode($cust_field['custom_field_id']);
			if (isset($inputdata['custom_fields'][$key])) {
				$query[] = "('" . safe_str($inputdata['job_id']) . "', '" . safe_str($cust_field['mapping_type']) . "', '" . safe_str($cust_field['mapping_id']) . "', '" . safe_str($cust_field['custom_field_name']) . "', '" . safe_str($cust_field['custom_field_type']) . "', '" . safe_str($inputdata['custom_fields'][$key]) . "', '" . date('Y-m-d H:i:s') . "', '0')";
			} else {
				$value = '';
				if ($cust_field['custom_field_type'] == 2) {
					$value = 0;
				}
				$query[] = "('" . safe_str($inputdata['job_id']) . "', '" . safe_str($cust_field['mapping_type']) . "', '" . safe_str($cust_field['mapping_id']) . "', '" . safe_str($cust_field['custom_field_name']) . "', '" . safe_str($cust_field['custom_field_type']) . "', '" . safe_str($value) . "', '" . date('Y-m-d H:i:s') . "', '0')";
			}
		}
		// die;
		/* foreach($inputdata['custom_fields'] as $key => $value){
			$custom_field_id  = $Encryption->decode($key);
			$get_mapping_info = send_rest(array(
				"table" => "ki_custom_fields_mapping_info",
				"function" => "get_details",
				"key" => "custom_field_id",
				"value" => $custom_field_id
			));
			$query[] = "('".$inputdata['job_id']."', '".$get_mapping_info['mapping_type']."', '".$get_mapping_info['mapping_id']."', '".$get_mapping_info['custom_field_name']."', '".$get_mapping_info['custom_field_type']."', '".$value."', '".date('Y-m-d H:i:s')."', '0')";
		} */
		if (!empty($query)) {
			$insert_val = implode(", ", $query);
			$up_qry = "INSERT INTO `" . safe_str($inputdata['table']) . "` (`job_id`,`mapping_type`,`mapping_id`,`custom_field_name`,`custom_field_type`,`custom_field_value`,`created_on`,`delete_flag`) VALUES " . $insert_val;
			$up_result = $con->query($up_qry);
			if ($up_result) {
				$data["status"] = 1;
			} else {
				$data["errors"][] = $con->error;
			}
		} else {
			$data["status"] = 1;
		}
		return $data;
	}

	function get_two_table_join_list($inputdata)
	{
		global $con;
		$sort_on = '';
		if (isset($inputdata['sort_on'])) {
			$sort_on = " ORDER BY " . safe_str($inputdata['sort_on']);
		}
		$enabled = '';
		if (isset($inputdata['enabled']) && $inputdata['enabled'] == 1) {
			$enabled = " AND AB.`is_enabled`='1' AND CD.`is_enabled`='1'";
		}
		$create_where = "";
		if (!empty($inputdata["key"]) && !empty($inputdata['value'])) {
			$create_where .= " and `" . safe_str($inputdata["key"]) . "`='" . safe_str($inputdata['value']) . "'";
		}
		$pagg_qry = "SELECT * FROM `" . safe_str($inputdata['table1']) . "` AS AB INNER JOIN `" . safe_str($inputdata['table2']) . "` AS CD ON AB.`" . safe_str($inputdata['on_key']) . "`=CD.`" . safe_str($inputdata['on_key']) . "` WHERE AB.`delete_flag`='0' AND CD.`delete_flag`='0' " . $enabled . $create_where . $sort_on;
		$pagg_result = $con->query($pagg_qry);
		echo $con->error;
		$pagg_count = $pagg_result->num_rows;
		$pagging_list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}
		return $pagging_list;
	}

	function get_two_table_join_details($inputdata)
	{
		global $con;
		$enabled = '';
		if (isset($inputdata['enabled'])) {
			$enabled = " AND AB.`is_enabled`='1' AND CD.`is_enabled`='1'";
		}
		$pagg_qry = "SELECT * FROM `" . safe_str($inputdata['table1']) . "` AS AB INNER JOIN `" . safe_str($inputdata['table2']) . "` AS CD ON AB.`" . safe_str($inputdata['on_key']) . "`=CD.`" . safe_str($inputdata['on_key']) . "` WHERE AB.`" . safe_str($inputdata['key']) . "`='" . safe_str($inputdata['value']) . "' AND AB.`delete_flag`='0' AND CD.`delete_flag`='0' " . $enabled;
		$pagg_result = $con->query($pagg_qry);
		if ($con->query($pagg_qry)) {
			$pagg_row = $pagg_result->fetch_assoc();
		} else {
			echo $con->error;
		}
		return $pagg_row;
	}

	function get_location_wise_product_tab_list($inputdata)
	{
		global $con;
		if ($inputdata['table'] == 'ki_product_logistics_info') {
			$pagg_qry = "SELECT `location_type`, `location_id`, IJ.`company_name`, `is_kingit_distribution`, COALESCE(CD.`store_name`, EF.`distribution_name`, GH.`production_name`) AS location_name, AB.* FROM `" . safe_str($inputdata['table']) . "` AB LEFT JOIN `ki_stores_info` CD ON AB.`location_type` = 1 AND AB.`location_id` = CD.`store_id` AND CD.`delete_flag`=0 LEFT JOIN `ki_distribution_branches_info` EF ON AB.`location_type` = 2 AND AB.`location_id` = EF.`distribution_branch_id` AND EF.`delete_flag`=0 LEFT JOIN `ki_production_info` GH ON AB.`location_type` = 3 AND AB.`location_id` = GH.`production_id` AND GH.`delete_flag`=0 LEFT JOIN `ki_suppliers_info` IJ ON AB.`supplier_id` = IJ.`supplier_id` WHERE `product_id` = '" . safe_str($inputdata['product_id']) . "' AND AB.`delete_flag`=0";
		} else {
			$pagg_qry = "SELECT `location_type`, `location_id`, COALESCE(CD.`store_name`, EF.`distribution_name`, GH.`production_name`) AS location_name, AB.* FROM `" . safe_str($inputdata['table']) . "` AB LEFT JOIN `ki_stores_info` CD ON AB.`location_type` = 1 AND AB.`location_id` = CD.`store_id` AND CD.`delete_flag`=0 LEFT JOIN `ki_distribution_branches_info` EF ON AB.`location_type` = 2 AND AB.`location_id` = EF.`distribution_branch_id` AND EF.`delete_flag`=0 LEFT JOIN `ki_production_info` GH ON AB.`location_type` = 3 AND AB.`location_id` = GH.`production_id` AND GH.`delete_flag`=0 WHERE `product_id` = '" . safe_str($inputdata['product_id']) . "' AND AB.`delete_flag`=0";
		}
		$pagg_result = $con->query($pagg_qry);
		$pagg_count = $pagg_result->num_rows;
		$pagging_list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}
		return $pagging_list;
	}

	function get_jobs_disabled_skills_list($inputdata)
	{
		global $con;
		$disabled = array();
		$query = "SELECT CD.`skill_name`,AB.* FROM `ki_work_to_complete_skills_info` AS AB INNER JOIN `ki_skills_info` AS CD ON AB.`skill_id`=CD.`skill_id` INNER JOIN `ki_skill_categories_info` AS EF ON CD.`skill_category_id`=EF.`skill_category_id` WHERE `work_to_complete_id`='" . safe_str($inputdata['work_to_complete_id']) . "' AND CD.`is_enabled`=0";
		$pagg_result = $con->query($query);
		$disabled_skill = array();
		while ($row = $pagg_result->fetch_assoc()) {
			$disabled_skill[] = $row['skill_name'];
		}
		if (!empty($disabled_skill)) {
			$msg = 'is';
			$last = '';
			$size = sizeof($disabled_skill);
			if ($size > 1) {
				$msg = 'are';
				$last = ' and ' . $disabled_skill[$size - 1];
				unset($disabled_skill[$size - 1]);
			}
			$disabled['skill'] = "* SKILLS: " . implode(', ', $disabled_skill) . $last . ' ' . $msg . " disabled by admin.";
		}
		$query = "SELECT DISTINCT EF.`category_name` FROM `ki_work_to_complete_skills_info` AS AB INNER JOIN `ki_skills_info` AS CD ON AB.`skill_id`=CD.`skill_id` INNER JOIN `ki_skill_categories_info` AS EF ON CD.`skill_category_id`=EF.`skill_category_id` WHERE `work_to_complete_id`='" . safe_str($inputdata['work_to_complete_id']) . "' AND EF.`is_enabled`=0";
		$pagg_result = $con->query($query);
		$disabled_category = array();
		while ($row = $pagg_result->fetch_assoc()) {
			$disabled_category[] = $row['category_name'];
		}
		if (!empty($disabled_category)) {
			$last = '';
			$is = ' is ';
			$aaa = ' this category ';
			$size = sizeof($disabled_category);
			if ($size > 1) {
				$last = ' and ' . $disabled_category[$size - 1];
				$aaa = " these categories ";
				$is = " are ";
				unset($disabled_category[$size - 1]);
			}
			$disabled['skill_category'] = "* SKILL CATEGORY: " . implode(', ', $disabled_category) . '' . $last . " " . $is . "disabled by admin. Skills listed under" . $aaa . "" . $is . "automatically disabled.";
		}
		return $disabled;
	}

	function update_products_tab_details($inputdata)
	{
		global $con;
		$data = array(
			"status" => 0,
			"errors" => array()
		);
		$table = safe_str($inputdata['table']);
		$find_qry = "SELECT * FROM `" . safe_str($table) . "` WHERE `product_id`='" . safe_str($inputdata['fields_data']['product_id']) . "' AND `location_type`='" . safe_str($inputdata['fields_data']['location_type']) . "' AND `location_id`='" . safe_str($inputdata['fields_data']['location_id']) . "' AND `delete_flag`=0";
		$result = $con->query($find_qry);
		if ($con->query($find_qry)) {
			$row = $result->fetch_assoc();
		}
		if (empty($row)) {
			$in_fields = array();
			foreach ($inputdata['fields_data'] as $field_key => $field_data) {
				$in_fields[safe_str($field_key)] = "'" . safe_str($field_data) . "'";
			}
			$in_fields['created_on'] = "'" . date("Y-m-d H:i:s") . "'";
			$up_qry = "INSERT INTO `" . safe_str($table) . "` (`" . implode("`, `", array_keys($in_fields)) . "`) VALUES (" . implode(", ", $in_fields) . ")";
		} else {
			if ($table == 'ki_product_consumption_info') {
				$inputdata['key'] = 'consumption_id';
				$inputdata['value'] = $row['consumption_id'];
			} elseif ($table == 'ki_product_logistics_info') {
				$inputdata['key'] = 'logistic_id';
				$inputdata['value'] = $row['logistic_id'];
			} elseif ($table == 'ki_product_quantites_info') {
				$inputdata['key'] = 'quantity_id';
				$inputdata['value'] = $row['quantity_id'];
				$allow_updation = $this->GetActiveStockTakeLocations(array(
					"location_type" => $inputdata['fields_data']['location_type'],
					"location_id" => $inputdata['fields_data']['location_id']
				));
				if (!empty($allow_updation['list'])) {
					$data["status"] = 1;
					return $data;
				}
			}
			$up_fields = array();
			foreach ($inputdata['fields_data'] as $ifield => $ival) {
				$up_fields[] = "`" . safe_str($ifield) . "` = '" . safe_str($ival) . "'";
			}
			$up_fields[] = "`modified_on` = '" . date("Y-m-d H:i:s") . "'";
			$up_qry = "UPDATE `" . safe_str($table) . "` SET " . implode(", ", $up_fields) . " WHERE `" . safe_str($inputdata['key']) . "` = '" . safe_str($inputdata['value']) . "'";
		}
		$up_result = $con->query($up_qry);
		if ($up_result) {
			$data["status"] = 1;
			$data["id"] = $con->insert_id;
		} else {
			$data["errors"][] = $con->error;
		}
		return $data;
	}

	function create_estimate_line_items($inputdata)
	{
		global $con;
		$data = array(
			"cost" => 0,
			"profit" => 0,
			"margin" => 0,
			"spiff" => 0,
			"sub_total" => 0,
			"gst" => 0,
			"total_amt" => 0
		);
		$Encryption = new Encryption();
		$tax_details = send_rest(array(
			"table" => "ki_estimates_info",
			"function" => "get_details",
			"key" => "estimate_id",
			"value" => $inputdata['estimate_id']
		));
		$query = array();
		$work_to_complete = explode(',', $inputdata['work_to_complete']);
		if(!empty($inputdata['data']['work_to_complete_list'])){
			$tba_work_to_complete=explode(',',$inputdata['data']['work_to_complete_list']);
			$work_to_complete=array_diff($work_to_complete, $tba_work_to_complete);
		}
		foreach ($work_to_complete as $wtc) {
			$query[] = "`work_to_complete_id`='" . $Encryption->decode($wtc) . "'";
		}
		if (!empty($query)) {
			$query = implode(' OR ', $query);
			$query = ' AND (' . $query . ')';
		}
		if ($inputdata['location_type'] == 1) {
			$price = "retail_price";
			// $tax = 0;
			// $tax = "(".($tax_details['default_tax']/100)."*`".$price."`)";
			$tax = "((COALESCE(MTI1.`tax_value`," . $tax_details['default_tax'] . ")/(COALESCE(MTI1.`tax_value`," . $tax_details['default_tax'] . ")+100))*`retail_price`)";
			// $tax = "(".($tax_details['default_tax']/($tax_details['default_tax']+100))."*`retail_price`)";
			$margin = " `retail_margin` AS margin ";
		} else {
			$price = 'distribution_price';
			$tax = "(" . ($tax_details['default_tax'] / 100) . "*`" . $price . "`)";
			$margin = "`distribution_margin` AS margin ";
		}
		// WHEN (PCI.`core_range`= 0 OR PCI.`core_range` IS NULL) AND (PQI.`stock_on_hand`<=0 OR PQI.`stock_on_hand` IS NULL) THEN 1
		// it is commented because of new requirement to add special order only if item is special order and SOH=0
		// WHEN PCI.`core_range` = 1 THEN 1
		$product_list = array();
		$i = 0;
		if(!empty($query)){
			$qry = "SELECT 
						DISTINCT CD.`product_id`, `product_name`, `description`, AB.`is_va_pdt`, 1 AS quantity,
						CASE 
							WHEN PCI.`core_range` = 1 AND (PQI.`stock_on_hand`<=0 OR PQI.`stock_on_hand` IS NULL) THEN 1
							ELSE 0
						END AS `is_special_order`, `" . $price . "` AS rate, ROUND(" . $tax . ",2) AS tax, " . $margin . ", retail_price, freight_charge, retail_tax, retail_margin, distribution_price, distribution_tax, distribution_margin, cost_price, brand_saving, spiff, MTI1.`tax_value` as retail_tax_val 
					FROM 
						(
							SELECT 
								DISTINCT `" . safe_str($inputdata['key']) . "` AS `product_id`, 0 AS is_va_pdt 
							FROM 
								`" . safe_str($inputdata['table']) . "` 
							WHERE 
								`model_id` = '" . safe_str($inputdata['model_id']) . "' " . $query . " AND `delete_flag`=0
							UNION 
							SELECT 
								DISTINCT `product_id`, 1 AS is_va_pdt 
							FROM 
								`ki_job_model_value_adds_product_mapping_info` 
							WHERE 
								`job_id`='" . safe_str($inputdata['job_id']) . "' AND `delete_flag`=0 AND `product_id` NOT IN (
									SELECT 
										DISTINCT `" . safe_str($inputdata['key']) . "` AS `product_id`
									FROM 
										`" . safe_str($inputdata['table']) . "` 
									WHERE 
										`model_id` = '" . safe_str($inputdata['model_id']) . "' " . $query . " AND `delete_flag`=0
								)
						) AB 
					INNER JOIN `ki_products_info` CD ON 
						AB.`product_id` = CD.`product_id` AND CD.`status`!=3 AND CD.`delete_flag` = 0 AND CD.`product_id` NOT IN ( 
							SELECT DISTINCT `product_id` FROM `ki_estimate_line_items_info` WHERE `estimate_id`='" . safe_str($inputdata['estimate_id']) . "' AND `delete_flag`=0 
						) 
					LEFT JOIN `ki_product_consumption_info` PCI ON 
						AB.`product_id` = PCI.`product_id` AND PCI.`location_type`='" . $tax_details['home_store_type'] . "' AND PCI.`location_id`='" . $tax_details['home_store_id'] . "' AND PCI.`delete_flag` = 0 
					LEFT JOIN `ki_product_quantites_info` PQI ON 
						AB.`product_id` = PQI.`product_id` AND PQI.`location_type`='" . $tax_details['home_store_type'] . "' AND PQI.`location_id`='" . $tax_details['home_store_id'] . "' AND PQI.`delete_flag` = 0 
					LEFT JOIN `ki_product_prices_info` GH ON 
						CD.`product_id` = GH.`product_id` AND GH.`delete_flag` = 0 
					LEFT JOIN `ki_meta_taxes_info` MTI1 ON 
						GH.`retail_tax`=MTI1.`tax_id` AND MTI1.`is_enabled`=1 AND MTI1.`delete_flag`=0 ";
			$pagg_result = $con->query($qry);
			$pagg_count = $pagg_result->num_rows;
			if ($pagg_count > 0) {
				while ($row = $pagg_result->fetch_assoc()) {
					$product_list[$i] = $row;
					$i++;
				}
			}		
		}
		if(!empty($tba_work_to_complete)){
			$tba_work_products=array();
			if($inputdata['key']=="budget_soln"){
				foreach ($tba_work_to_complete as $tba_work) {
					$work_to_complete_id=$Encryption->decode($tba_work);
					if(!empty($inputdata['data']["budget_solution_".$tba_work])){
						//get all budget solutions from user selection in modal
						$tba_budget_soln=$inputdata['data']["budget_solution_".$tba_work];
						foreach($tba_budget_soln as $tba_budget_prod){
							$tba_work_products[]=$Encryption->decode($tba_budget_prod);
						}
					}
				}
			}
			else if($inputdata['key']=="recommended_soln"){
				foreach ($tba_work_to_complete as $tba_work) {
					$work_to_complete_id=$Encryption->decode($tba_work);
					if(!empty($inputdata['data']["recommended_solution_".$tba_work])){
						//get all recommended solutions from user selection in modal
						$tba_recommended_soln=$inputdata['data']["recommended_solution_".$tba_work];
						foreach($tba_recommended_soln as $tba_rec_prod){
							$tba_work_products[]=$Encryption->decode($tba_rec_prod);
						}
					}
				}
			}
			if(!empty($tba_work_products)){
				$fields=array();
				foreach ($tba_work_products as $p_index => $p_id) {
					$fields[safe_str($p_index)]="'".safe_str($p_id)."'";
				}
				$sql2="SELECT 
							DISTINCT CD.`product_id`, `product_name`, `description`, AB.`is_va_pdt`, 1 AS quantity,
							CASE 
								WHEN PCI.`core_range` = 1 AND (PQI.`stock_on_hand`<=0 OR PQI.`stock_on_hand` IS NULL) THEN 1
								ELSE 0
							END AS `is_special_order`, `".$price."` AS rate, ROUND(".$tax.",2) AS tax, ".$margin.", retail_price, freight_charge, retail_tax, retail_margin, distribution_price, distribution_tax, distribution_margin, cost_price, brand_saving, spiff, MTI1.`tax_value` as retail_tax_val 
						FROM 
							(
								SELECT 
									DISTINCT `product_id`, 0 AS is_va_pdt 
								FROM 
									ki_products_info 
								WHERE 
									product_id in(".implode(",",$fields).") AND `delete_flag`=0
								UNION 
								SELECT 
									DISTINCT `product_id`, 1 AS is_va_pdt 
								FROM 
									`ki_job_model_value_adds_product_mapping_info` 
								WHERE 
									`job_id`='".safe_str($inputdata['job_id'])."' AND `delete_flag`=0 AND `product_id` NOT IN (
										SELECT 
											DISTINCT `product_id`
										FROM 
											ki_products_info 
										WHERE 
											product_id in(".implode(",",$fields).") AND `delete_flag`=0
									)
							) AB 
						INNER JOIN `ki_products_info` CD ON 
							AB.`product_id` = CD.`product_id` AND CD.`status`!=3 AND CD.`delete_flag` = 0 AND CD.`product_id` NOT IN ( 
								SELECT DISTINCT `product_id` FROM `ki_estimate_line_items_info` WHERE `estimate_id`='".safe_str($inputdata['estimate_id'])."' AND `delete_flag`=0 
							) 
						LEFT JOIN `ki_product_consumption_info` PCI ON 
							AB.`product_id` = PCI.`product_id` AND PCI.`location_type`='".$tax_details['home_store_type']."' AND PCI.`location_id`='".$tax_details['home_store_id']."' AND PCI.`delete_flag` = 0 
						LEFT JOIN `ki_product_quantites_info` PQI ON 
							AB.`product_id` = PQI.`product_id` AND PQI.`location_type`='".$tax_details['home_store_type']."' AND PQI.`location_id`='".$tax_details['home_store_id']."' AND PQI.`delete_flag` = 0 
						LEFT JOIN `ki_product_prices_info` GH ON 
							CD.`product_id` = GH.`product_id` AND GH.`delete_flag` = 0 
						LEFT JOIN `ki_meta_taxes_info` MTI1 ON 
							GH.`retail_tax`=MTI1.`tax_id` AND MTI1.`is_enabled`=1 AND MTI1.`delete_flag`=0";

				$res=$con->query($sql2);
				if($res->num_rows>0){
					while($row2=$res->fetch_assoc()){
						$product_list[$i] = $row2;
				    	$i++;
					}
				}
			}
		}
		// echo"<pre>";print_r($product_list);
		$retail_ex_gst = $total_dist = $total_cost = $total_spiff = $profit = $margin = 0;
		foreach ($product_list as $product) {
			if (empty($product['rate'])) {
				$product['rate'] = 0.00;
			}
			if (empty($product['tax'])) {
				$product['tax'] = 0.00;
			}
			if (empty($product['retail_tax_val'])) {
				$product['retail_tax_val'] = $tax_details['default_tax'];
			}
			if (!empty($inputdata['quantities'][$product['product_id']])) {
				$product['quantity'] = $inputdata['quantities'][$product['product_id']];
			}
			$data['sub_total'] = $data['sub_total'] + ($product['rate'] * $product['quantity']);
			$data['gst'] = $data['gst'] + ($product['tax'] * $product['quantity']);
			$qry = "INSERT INTO `ki_estimate_line_items_info` 
						(`estimate_id`, `product_id`, `product_name`, `description`, `is_va_pdt`, `is_special_order`, `quantity`, `line_retail_price`, `line_retail_tax`, `line_distribution_price`, `line_cost_price`, `line_spiff`, `rate`, `margin`, `tax`, `created_on`, `delete_flag`) 
					VALUES 
						('" . safe_str($inputdata['estimate_id']) . "', '" . $product['product_id'] . "', '" . safe_str($product['product_name']) . "', '" . safe_str($product['description']) . "', '" . $product['is_va_pdt'] . "', '" . $product['is_special_order'] . "', '" . $product['quantity'] . "', '" . safe_str($product['retail_price']) . "', '" . safe_str($product['retail_tax_val']) . "', '" . safe_str($product['distribution_price']) . "', '" . safe_str($product['cost_price']) . "', '" . safe_str($product['spiff']) . "', '" . safe_str($product['rate']) . "', '" . safe_str($product['margin']) . "', '" . safe_str($product['tax'] * $product['quantity']) . "', '" . date("Y-m-d H:i:s") . "','0')";
			$result = $con->query($qry);
			if ($result) {
				if ($inputdata['location_type'] == 1) {
					if (!empty($product['distribution_price'])) {
						$total_cost = $total_cost + ($product['distribution_price'] * $product['quantity']);
					}
					$retail_ex_gst = $retail_ex_gst + ((($product['retail_price'] * 100) / (100 + $product['retail_tax_val'])) * $product['quantity']);
					$profit = $profit + (((($product['retail_price'] * 100) / (100 + $product['retail_tax_val'])) - $product['distribution_price']) * $product['quantity']);
				} else {
					if (!empty($product['cost_price'])) {
						$total_cost = $total_cost + ($product['cost_price'] * $product['quantity']);
					}
					$total_dist = $total_dist + ($product['distribution_price'] * $product['quantity']);
					$profit = $profit + (($product['distribution_price'] - $product['cost_price']) * $product['quantity']);
				}
				$total_spiff = $total_spiff + ($product['spiff'] * $product['quantity']);
			}
		}
		if ($inputdata['location_type'] == 1) {
			$data['total_amt'] = p_round($data['sub_total']);
			if ($retail_ex_gst > 0 && $profit >= 0) {
				$margin = ($profit / $retail_ex_gst) * 100;
			} else {
				$margin = 0;
			}
		} else {
			$data['total_amt'] = p_round($data['sub_total'] + $data['gst']);
			if ($total_dist > 0 && $profit >= 0) {
				$margin = ($profit / $total_dist) * 100;
			} else {
				$margin = 0;
			}
		}
		$data['cost'] = $total_cost;
		$data['profit'] = $profit;
		$data['margin'] = $margin;
		$data['spiff'] = $total_spiff;
		return $data;
	}

	function get_assigned_tech_list($inputdata)
	{
		global $con;
		$Encryption = new Encryption();
		$pagging_list = array();
		$query = array();
		$work_to_complete = explode(',', safe_str($inputdata['work_to_complete']));
		foreach ($work_to_complete as $wtc) {
			if ($inputdata['type'] == 1) {
				$query[] = $Encryption->decode($wtc);
			} elseif ($inputdata['type'] == 2) {
				$query[] = "AB.`work_to_complete_id`='" . $Encryption->decode($wtc) . "'";
			}
		}
		// print_r($query);
		if (!empty($query)) {
			if ($inputdata['type'] == 1) {
				$query = implode(' , ', $query);
			} elseif ($inputdata['type'] == 2) {
				$query = implode(' OR ', $query);
				$query = ' AND  (' . $query . ')';
			}
			// $query = ' AND ('.$query.')';
		}
		// echo $query;
		// 1. to get users with all matching skills
		// print_r($inputdata);
		if ($inputdata['type'] == 1) {
			$sql = "SELECT 
					GROUP_CONCAT(DISTINCT `skill_id` SEPARATOR ',') AS ws_skills, count(*) AS count_ws_skills 
				FROM 
					ki_work_to_complete_skills_info 
				WHERE 
					`work_to_complete_id` IN (" . $query . ")";
			$result = $con->query($sql);
			$row = $result->fetch_assoc();
			// echo $row['count_ws_skills'];
			$skills_array = explode(',', $row['ws_skills']);
			array_walk($skills_array, 'trim');
			$skills_count = count($skills_array);
			if ($skills_count > 0 && !empty($row['ws_skills'])) {
				$pagg_qry = "SELECT 
								DISTINCT CONCAT(COALESCE(UI.`first_name`,''),' ',COALESCE(UI.`last_name`,'')) AS user_name, UI.`user_id`, UI.`is_enabled` 
							FROM 
								ki_users_info UI 
							INNER JOIN (
								SELECT `user_id` FROM ki_user_locations_info WHERE `location_type`='" . safe_str($inputdata['location_type']) . "' AND `location_id`='" . safe_str($inputdata['location_id']) . "' AND `delete_flag`=0
							) UL ON 
								UI.user_id = UL.user_id
							INNER JOIN ki_user_skills_info US ON 
								UI.user_id = US.user_id AND US.delete_flag=0
							INNER JOIN ki_skills_info SI ON 
								SI.skill_id = US.skill_id AND SI.`is_enabled` = 1 AND SI.`delete_flag` = 0 
							INNER JOIN `ki_skill_categories_info` SK ON 
								SI.`skill_category_id`=SK.`skill_category_id` AND SK.`is_enabled` = 1 AND SK.`delete_flag` = 0 
							WHERE 
								US.skill_id IN (" . $row['ws_skills'] . ") AND UI.is_enabled=1 AND UI.delete_flag=0 
							GROUP BY 
								US.user_id 
							HAVING 
								COUNT(US.skill_id) = " . $skills_count;
				//$pagg_qry = "SELECT DISTINCT CONCAT(COALESCE(EF.`first_name`,''),' ',COALESCE(EF.`last_name`,'')) AS user_name, EF.`user_id` FROM `ki_work_to_complete_skills_info` AB INNER JOIN `ki_user_skills_info` CD ON AB.`skill_id`=CD.`skill_id` AND CD.`delete_flag`=0 INNER JOIN `ki_users_info` EF ON CD.`user_id`=EF.`user_id` AND EF.`delete_flag`=0 AND EF.`is_enabled`=1 INNER JOIN `ki_skills_info` GH ON CD.`skill_id`=GH.`skill_id` AND GH.`delete_flag` = 0 AND GH.`is_enabled` = 1 INNER JOIN `ki_skill_categories_info` IJ ON GH.`skill_category_id`=IJ.`skill_category_id` AND IJ.`delete_flag` = 0 AND IJ.`is_enabled` = 1 WHERE AB.`delete_flag`=0 ".$query;
				$pagg_result = $con->query($pagg_qry);
				$pagg_count = $pagg_result->num_rows;
				$pagging_list = array();
				if ($pagg_count > 0) {
					$i = 0;
					while ($row = $pagg_result->fetch_assoc()) {
						$pagging_list[$i] = $row;
						$i++;
					}
				}
			}
		} elseif ($inputdata['type'] == 2) {
			$pagg_qry = "SELECT 
							DISTINCT CONCAT(COALESCE(EF.`first_name`,''),' ',COALESCE(EF.`last_name`,'')) AS user_name, EF.`user_id`, EF.`is_enabled` 
						FROM 
							`ki_work_to_complete_skills_info` AB 
						INNER JOIN `ki_user_skills_info` CD ON 
							AB.`skill_id`=CD.`skill_id` AND CD.`delete_flag`=0 
						INNER JOIN `ki_users_info` EF ON 
							CD.`user_id`=EF.`user_id` AND EF.`delete_flag`=0 AND EF.`is_enabled`=1 
						INNER JOIN (
							SELECT `user_id` FROM ki_user_locations_info WHERE `location_type`='" . safe_str($inputdata['location_type']) . "' AND `location_id`='" . safe_str($inputdata['location_id']) . "' AND `delete_flag`=0
						) UL ON 
							EF.user_id = UL.user_id
						INNER JOIN `ki_skills_info` GH ON 
							CD.`skill_id`=GH.`skill_id` AND GH.`delete_flag` = 0 AND GH.`is_enabled` = 1 
						INNER JOIN `ki_skill_categories_info` IJ ON 
							GH.`skill_category_id`=IJ.`skill_category_id` AND IJ.`delete_flag` = 0 AND IJ.`is_enabled` = 1 
						WHERE 
							AB.`delete_flag`=0 " . $query;
			$pagg_result = $con->query($pagg_qry);
			$pagg_count = $pagg_result->num_rows;
			$pagging_list = array();
			if ($pagg_count > 0) {
				$i = 0;
				while ($row = $pagg_result->fetch_assoc()) {
					$pagging_list[$i] = $row;
					$i++;
				}
			}
		}
		// echo"<pre>";print_r($pagging_list);b
		return $pagging_list;
	}

	function get_device_wizard_dropdown_for_jobs($inputdata)
	{
		global $con;
		$details = array();
		$type = $inputdata['type'];		// 1 - ticket_types, 2 - brands, 3 - models
		if ($type == 1) {
			$query = "SELECT DISTINCT AB.*, WSI.`slugs` FROM `ki_ticket_types_info` AB INNER JOIN( SELECT DISTINCT CD.* FROM `ki_brand_ticket_types_mapping_info` CD INNER JOIN( SELECT DISTINCT model_info.* FROM `ki_brands_info` EF INNER JOIN( SELECT DISTINCT GH.* FROM `ki_models_info` GH INNER JOIN `ki_model_worktocomplete_mapping_info` IJ ON GH.`model_id` = IJ.`model_id` AND GH.`is_enabled`=1 AND GH.`delete_flag`=0 AND IJ.`delete_flag`=0) model_info ON EF.`brand_id` = model_info.`brand_id` AND EF.`is_enabled`=1 AND EF.`delete_flag`=0 AND model_info.`delete_flag`=0) brand_info ON CD.`brand_id` = brand_info.`brand_id` AND CD.`ticket_type_id`=brand_info.`ticket_type_id` AND CD.`delete_flag`=0 AND brand_info.`delete_flag`=0) ticket_info ON AB.`ticket_type_id` = ticket_info.`ticket_type_id` AND AB.`is_enabled`=1" . ((!empty($inputdata['website'])) ? " AND AB.`publish_on_website`=1" : "") . " AND AB.`delete_flag`=0 AND ticket_info.`delete_flag`=0 INNER JOIN `ki_models_info` AS models ON AB.`ticket_type_id`=models.`ticket_type_id` AND ticket_info.`brand_id`=models.`brand_id` LEFT JOIN `ki_website_slugs_info` WSI ON WSI.`slug_type`=4 AND WSI.`type_id`=AB.`ticket_type_id` ORDER BY `ticket_type_name`";
		} elseif ($type == 2) {
			$query = "SELECT DISTINCT brand_info.* FROM `ki_brand_ticket_types_mapping_info` AB INNER JOIN( SELECT DISTINCT CD.* FROM `ki_brands_info` CD INNER JOIN( SELECT DISTINCT EF.* FROM `ki_models_info` EF INNER JOIN `ki_model_worktocomplete_mapping_info` GH ON EF.`model_id` = GH.`model_id` AND EF.`is_enabled`=1 AND EF.`delete_flag`=0 AND GH.`delete_flag`=0 AND EF.`ticket_type_id`='" . safe_str($inputdata['ticket_type_id']) . "') model_info ON CD.`brand_id` = model_info.`brand_id` AND CD.`is_enabled`=1 AND CD.`delete_flag`=0 AND model_info.`delete_flag`=0) brand_info ON AB.`brand_id` = brand_info.`brand_id` AND AB.`delete_flag`=0 AND brand_info.`delete_flag`=0 WHERE AB.`ticket_type_id`='" . safe_str($inputdata['ticket_type_id']) . "' ORDER BY `brand_name`";
		} elseif ($type == 3) {
			$query = "SELECT DISTINCT AB.* FROM `ki_models_info` AB INNER JOIN `ki_model_worktocomplete_mapping_info` CD ON AB.`model_id` = CD.`model_id` AND AB.`is_enabled` = 1 AND AB.`delete_flag` = 0 AND CD.`delete_flag` = 0 WHERE AB.`ticket_type_id`='" . safe_str($inputdata['ticket_type_id']) . "' AND AB.`brand_id`='" . safe_str($inputdata['brand_id']) . "' and AB.`is_enabled`=1" . ((!empty($inputdata['website'])) ? " AND AB.`is_draft`=0" : "") . " AND AB.`delete_flag`=0 ORDER BY AB.`model_name`";
		} elseif ($type == 4) {
			$query = "SELECT DISTINCT IJ.`work_to_complete_id` FROM `ki_work_to_complete_skills_info` EF INNER JOIN `ki_user_skills_info` GH ON EF.`skill_id` = GH.`skill_id` AND EF.`delete_flag` = 0 AND GH.`delete_flag` = 0 INNER JOIN `ki_users_info` UI ON UI.`user_id`=GH.`user_id` AND UI.`is_enabled`=1 AND UI.`delete_flag`=0 INNER JOIN ( SELECT `user_id` FROM ki_user_locations_info WHERE `location_type`='" . safe_str($inputdata['location_type']) . "' AND `location_id`='" . safe_str($inputdata['location_id']) . "' AND `delete_flag`=0 ) UL ON UI.user_id = UL.user_id INNER JOIN `ki_work_to_complete_info` IJ ON EF.`work_to_complete_id`=IJ.`work_to_complete_id` AND IJ.`is_enabled` = 1 AND IJ.`delete_flag` = 0 INNER JOIN( SELECT AB.* FROM `ki_skills_info` AB INNER JOIN `ki_skill_categories_info` CD ON AB.`skill_category_id` = CD.`skill_category_id` AND AB.`is_enabled` = 1 AND AB.`delete_flag` = 0 AND CD.`is_enabled` = 1 AND CD.`delete_flag` = 0 ) skill_info ON EF.`skill_id` = skill_info.`skill_id` AND GH.`skill_id` = skill_info.`skill_id`";
		} else if ($type == 5) {
			$query = "SELECT LPI.`title`, LPI.`landing_id`, LPI.`background_image_path`, WSI.`slugs` FROM `ki_landing_page_info` LPI INNER JOIN `ki_website_slugs_info` WSI ON LPI.`landing_id` = WSI.`type_id` WHERE WSI.`slug_type` = $type AND LPI.`is_enabled` = 1  AND LPI.`publish_on_website` = 1 AND LPI.`delete_flag` = 0 ORDER BY `title`";
		}
		// ECHO $query;
		$query_result = $con->query($query);
		if ($con->query($query)) {
			$i = 0;
			while ($row = $query_result->fetch_assoc()) {
				$details[$i] = $row;
				$i++;
			}
		} else {
			echo $con->error;
		}
		return $details;
	}

	function get_job_value_add_details($inputdata)
	{
		global $con;
		$table = 'ki_job_model_value_adds_product_mapping_info';
		$query = "SELECT * FROM `" . $table . "` WHERE `job_id`='" . safe_str($inputdata['job_id']) . "' AND `delete_flag`=0";
		$result = $con->query($query);
		$i = 0;
		echo $con->error;
		while ($row = $result->fetch_assoc()) {
			$list[$i] = $row;
			$i++;
		}
		$final_list = array();
		if (!empty($list)) {
			foreach ($list as $row) {
				$value_adds_id = $row['value_adds_id'];
				$final_list[$value_adds_id][] = $row;
			}
		}
		return $final_list;
	}

	function get_estimated_product_solution_list($inputdata)
	{
		global $con;
		$pagg_qry = "SELECT 
						PI.`product_id`, PI.`product_name`, " . $inputdata['location_type'] . " AS `location_type`, " . $inputdata['location_id'] . " AS `location_id`, PCI.`core_range`, PQI.`stock_on_hand`, PQI.`override_desired_stock_level`, PQI.`desired_stock_level`, PPI.`retail_price`, PPI.`distribution_price`, PPI.`cost_price`, PPI.`retail_margin`, PPI.`distribution_margin`, PPI.`spiff`, MTI1.`tax_value` as retail_tax_val, MTI2.`tax_value` as dist_tax_val, ELI.* 
					FROM 
						`ki_estimate_line_items_info` ELI 
					LEFT JOIN `ki_products_info` PI ON 
						ELI.`product_id`=PI.`product_id` AND PI.`delete_flag`=0 
					LEFT JOIN `ki_product_consumption_info` PCI ON 
						PI.`product_id`=PCI.`product_id` AND PCI.`location_type`='" . safe_str($inputdata['location_type']) . "' AND PCI.`location_id`='" . safe_str($inputdata['location_id']) . "' AND PCI.`delete_flag`=0 
					LEFT JOIN `ki_product_quantites_info` PQI ON 
						PI.`product_id`=PQI.`product_id` AND PQI.`location_type`='" . safe_str($inputdata['location_type']) . "' AND PQI.`location_id`='" . safe_str($inputdata['location_id']) . "' AND PQI.`delete_flag`=0 
					LEFT JOIN `ki_product_prices_info` PPI ON 
						PI.`product_id`=PPI.`product_id` AND PPI.`delete_flag`=0 
					LEFT JOIN `ki_meta_taxes_info` MTI1 ON 
						PPI.`retail_tax`=MTI1.`tax_id` AND MTI1.`is_enabled`=1 AND MTI1.`delete_flag`=0 
					LEFT JOIN `ki_meta_taxes_info` MTI2 ON 
						PPI.`distribution_tax`=MTI2.`tax_id` AND MTI2.`is_enabled`=1 AND MTI2.`delete_flag`=0 
					WHERE 
						ELI.`estimate_id`='" . safe_str($inputdata['estimate_id']) . "' AND ELI.`delete_flag`=0 
					ORDER BY 
						ELI.`product_name`";
		$pagg_result = $con->query($pagg_qry);
		// echo $con->error;
		$pagg_count = $pagg_result->num_rows;
		$pagging_list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}
		// echo"<pre>";print_r($pagging_list);
		return $pagging_list;
	}

	function get_job_onboarding_details($inputdata)
	{
		global $con;
		$find = "SELECT * FROM `ki_job_onboarding_info` WHERE `job_id`='" . safe_str($inputdata['job_id']) . "' AND `delete_flag`=0";
		$result = $con->query($find);
		$list = array();
		while ($row = $result->fetch_assoc()) {
			$list[$row['ques_id']] = $row['option_id'];
		}
		return $list;
	}

	function get_job_wtc_associated_list($inputdata)
	{
		global $con;
		$pagg_qry = "SELECT AB.`guide_url`, CD.* FROM `ki_model_worktocomplete_mapping_info` AB INNER JOIN `ki_work_to_complete_info` CD ON AB.`work_to_complete_id` = CD.`work_to_complete_id` AND CD.`delete_flag`=0 INNER JOIN `ki_job_work_to_complete_info` EF ON CD.`work_to_complete_id` = EF.`work_to_complete_id` AND EF.`delete_flag`=0 WHERE AB.`model_id`='" . safe_str($inputdata['model_id']) . "' AND EF.`job_id`='" . safe_str($inputdata['job_id']) . "' AND AB.`delete_flag`=0 ORDER BY CD.`work_to_complete`";
		$pagg_result = $con->query($pagg_qry);
		$pagg_count = $pagg_result->num_rows;
		$pagging_list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}
		return $pagging_list;
	}

	function get_job_onboarding_ans_list($inputdata)
	{
		global $con;
		$pagg_qry = "SELECT AB.*, CD.`question_val`, EF.`option_val` FROM `ki_job_onboarding_info` AB INNER JOIN `ki_onboarding_ques_info` CD ON AB.`ques_id` = CD.`ques_id` AND CD.`is_enabled` = 1 AND CD.`delete_flag` = 0 INNER JOIN `ki_onboarding_options_info` EF ON AB.`option_id` = EF.`option_id` AND EF.`delete_flag` = 0 WHERE `job_id` = '" . safe_str($inputdata['job_id']) . "'";
		$pagg_result = $con->query($pagg_qry);
		$pagg_count = $pagg_result->num_rows;
		$pagging_list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}
		return $pagging_list;
	}

	function get_associated_estimates_for_jobs($inputdata)
	{
		global $con;
		if ($inputdata['type'] == 1) {
			$pagg_qry = "SELECT * FROM `ki_estimates_info` WHERE `job_id`='" . safe_str($inputdata['job_id']) . "' AND `created_on`<='" . safe_str($inputdata['created_date']) . "' AND `delete_flag`=0";
		} elseif ($inputdata['type'] == 2) {
			$pagg_qry = "SELECT * FROM `ki_estimates_info` WHERE `job_id`='" . safe_str($inputdata['job_id']) . "' AND `created_on`>'" . safe_str($inputdata['created_date']) . "' AND `delete_flag`=0";
		}
		$pagg_result = $con->query($pagg_qry);
		$pagg_count = $pagg_result->num_rows;
		$pagging_list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}
		return $pagging_list;
	}

	function get_value_adds_for_job_details($inputdata)
	{
		global $con;
		$query = "SELECT AB.`job_id`,AB.`value_adds_id`,CD.`value_adds_type`,AB.`product_id`, EF.`product_name` FROM `ki_job_model_value_adds_product_mapping_info` AB INNER JOIN `ki_models_value_adds_info` CD ON AB.`value_adds_id`=CD.`value_adds_id` AND CD.`delete_flag`=0 INNER JOIN `ki_products_info` EF ON AB.`product_id`=EF.`product_id` AND EF.`delete_flag`=0 WHERE AB.`job_id`='" . safe_str($inputdata['job_id']) . "' AND AB.`delete_flag`=0 ORDER BY EF.`product_name`";
		$result = $con->query($query);
		$final_list = array();
		while ($row = $result->fetch_assoc()) {
			$value_adds_id = $row['value_adds_id'];
			$final_list[$value_adds_id][] = $row;
		}
		return $final_list;
	}

	function get_product_suppliers_list_for_jobs($inputdata)
	{
		global $con;
		$query = "SELECT EF.`company_name` AS supplier_name, GH.`company_name` AS loc_supplier_name, IJ.`company_name` AS store_supplier_name, `is_same_for_all_loc`, `loc_supplier_id`, `is_same_for_all_stores`, `stores_supplier_id`, AB.*,CD.* FROM `ki_product_logistics_info` AB INNER JOIN `ki_products_info` CD ON AB.`product_id` = CD.`product_id` AND CD.`delete_flag` = 0 LEFT JOIN `ki_suppliers_info` EF ON AB.`supplier_id` = EF.`supplier_id` AND EF.`delete_flag` = 0 LEFT JOIN `ki_suppliers_info` GH ON CD.`loc_supplier_id` = GH.`supplier_id` AND CD.`is_same_for_all_loc` = 1 AND GH.`delete_flag` = 0 LEFT JOIN `ki_suppliers_info` IJ ON CD.`stores_supplier_id` = IJ.`supplier_id` AND CD.`is_same_for_all_stores` = 1 AND IJ.`delete_flag` = 0 WHERE AB.`product_id` ='" . safe_str($inputdata['product_id']) . "' AND AB.`delete_flag` = 0";
		$result = $con->query($query);
		$final_list = array();
		while ($row = $result->fetch_assoc()) {
			$final_list[] = $row;
		}
		// print_r($final_list);
		return $final_list;
	}

	function get_job_parts_ordered_details($inputdata)
	{
		global $con;
		$query = "SELECT 
					CONCAT( COALESCE(EF.`contact_first_name`, ''), ' ', COALESCE(EF.`contact_last_name`, '') ) AS supplier_name, company_name, AB.*,CD.`product_name`,SI.store_delivery_prefix, SDM.quantity AS mapping_qty, SDI.`store_delivery_id`, SDI.`store_delivery_number`  
				FROM 
					`ki_job_parts_order_info` AB 
				LEFT JOIN `ki_products_info` CD ON 
					AB.`product_id` = CD.`product_id` AND CD.`delete_flag` = 0 
				LEFT JOIN `ki_suppliers_info` EF ON 
					AB.`supplier_id` = EF.`supplier_id` AND EF.`delete_flag` = 0 
				LEFT JOIN `ki_store_delivery_line_items_mapping_info` SDM ON 
					SDM.`type`=2 AND SDM.`type_id` = AB.`order_id` AND SDM.`delete_flag` = 0 
				LEFT JOIN `ki_store_delivery_line_items_info` SDLI ON 
					SDM.`store_delivery_line_item_id` = SDLI.`store_delivery_line_item_id` AND SDLI.`delete_flag` = 0 
				LEFT JOIN `ki_store_delivery_info` SDI ON 
					SDLI.`store_delivery_id` = SDI.`store_delivery_id` AND SDI.`delete_flag` = 0 
				LEFT JOIN ki_stores_info SI ON
					SI.store_id=SDI.store_id AND SI.delete_flag=0
				WHERE 
					AB.`job_id` ='" . safe_str($inputdata['job_id']) . "' AND AB.`delete_flag` = 0
				ORDER BY
					AB.`created_on`";
		$result = $con->query($query);
		$final_list = array();
		while ($row = $result->fetch_assoc()) {
			$final_list[] = $row;
		}
		// print_r($final_list);
		return $final_list;
	}

	function get_job_wtc_canned_responses_list($inputdata)
	{
		$wtc_details = send_rest(array(
			"function" => "get_job_wtc_associated_list",
			"job_id" => $inputdata['job_id'],
			"model_id" => $inputdata['model_id']
		));
		$work = array();
		// 		print_r($wtc_details);
		foreach ($wtc_details as $wtc) {
			if (!in_array($wtc['work_to_complete_id'], $work)) {
				$work[] = "work_to_complete_id=" . $wtc['work_to_complete_id'];
			}
		}
		$work1 = '';
		if (!empty($work)) {
			$work1 = implode(" OR ", $work);
			$work1 = " AND (" . $work1 . ")";
		}
		global $con;
		$query = "SELECT DISTINCT CD.* FROM `ki_model_canned_response_mapping_info` AB INNER JOIN `ki_canned_responses_info` CD ON AB.`canned_response_id`=CD.`canned_response_id` AND CD.`is_enabled`=1 AND CD.`delete_flag`=0 WHERE AB.`model_id`='" . safe_str($inputdata['model_id']) . "' AND AB.`delete_flag`=0 " . $work1 . " ORDER BY `title`";
		$result = $con->query($query);
		$final_list = array();
		while ($row = $result->fetch_assoc()) {
			$final_list[] = $row;
		}
		return $final_list;
	}

	function get_job_comments_list($inputdata)
	{
		// params - job_id
		// returns count of private and public comments and list of all comments.
		global $con;
		$data = array(
			"private_count" => 0,
			"public_count" => 0,
			"list" => array()
		);
		$count_query = "SELECT * FROM (SELECT COUNT(*) as private_count FROM `ki_job_comments_info` WHERE `job_id`='" . safe_str($inputdata['job_id']) . "' AND `type`=1 AND `delete_flag`=0) AA, (SELECT COUNT(*) as public_count FROM `ki_job_comments_info` WHERE `job_id`='" . safe_str($inputdata['job_id']) . "' AND `type`!=1 AND `delete_flag`=0) BB";
		$result1 = $con->query($count_query);
		$row1 = $result1->fetch_assoc();
		$private_count = $row1["private_count"];
		$public_count = $row1["public_count"];
		$query = "SELECT CONCAT(COALESCE(`first_name`,''),' ', COALESCE(`last_name`,'')) AS created_by_user, AB.* FROM `ki_job_comments_info` AB LEFT JOIN `ki_users_info` CD ON AB.`created_by`=CD.`user_id` AND CD.`delete_flag`=0 WHERE AB.`job_id`='" . safe_str($inputdata['job_id']) . "' AND AB.`delete_flag`=0 ORDER BY AB.`created_on` DESC";
		$result = $con->query($query);
		$final_list = array();
		while ($row = $result->fetch_assoc()) {
			$final_list[] = $row;
		}
		$data['private_count'] = $private_count;
		$data['public_count'] = $public_count;
		$data['list'] = $final_list;
		return $data;
	}

	function update_job_view_detail($inputdata)
	{
		// params - job_type, job_id, location_type, location_id, user_id
		// creates an entry in ki_job_views_info table whenever user views a job.
		global $con;
		$view_details = send_rest(array(
			"function" => "get_details",
			"table" => "ki_job_views_info",
			"key" => "job_id",
			"value" => $inputdata['job_id'],
			"where" => array("job_type" => $inputdata['job_type'], "location_id" => $inputdata['location_id'], "location_type" => $inputdata['location_type'], "user_id" => $inputdata['user_id'])
		));
		if (empty($view_details)) {
			$in_fields = array(
				"job_id" => $inputdata['job_id'],
				"job_type" => $inputdata['job_type'],
				"location_id" => $inputdata['location_id'],
				"location_type" => $inputdata['location_type'],
				"user_id" => $inputdata['user_id']
			);
			$result = send_rest(array(
				"table" => "ki_job_views_info",
				"function" => "create",
				"fields_data" => $in_fields
			));
		}
	}

	function get_new_certificate_of_work_line_item_list($inputdata)
	{
		// params - job_id, location_type, location_id
		// returns list of items of approved estimates and data related to it when user creates a new certificate of work
		global $con;
		$pagg_qry = "SELECT 
						EI.`estimate_number`, EI.`amounts_include_gst`, EI.`default_tax`, ELI.*,
						PI.`product_id`, COALESCE(PI.`product_name`,ELI.`product_name`) AS product_name, PI.`minutes_to_complete`, " . $inputdata['location_type'] . " AS `location_type`, " . $inputdata['location_id'] . " AS `location_id`, PCI.`core_range`, PQI.`stock_on_hand`, PQI.`override_desired_stock_level`, PQI.`desired_stock_level`, PPI.`retail_price`, PPI.`distribution_price`, PPI.`cost_price`, PPI.`retail_margin`, PPI.`distribution_margin`, COALESCE(MTI1.`tax_value`, MTI3.`tax_value`) as retail_tax_val, MTI2.`tax_value` as dist_tax_val,
						CASE 
							WHEN (ELI.`product_id`=0 OR ELI.`product_id` IS NULL) AND " . $inputdata['location_type'] . "=1 THEN ELI.`line_distribution_price` 
							WHEN ELI.`product_id`=0 OR ELI.`product_id` IS NULL THEN ELI.`line_cost_price` 
						END AS manual_cost_price
					FROM 
						`ki_estimate_line_items_info` ELI 
					INNER JOIN (
						SELECT * FROM `ki_estimates_info` WHERE `job_id`='" . safe_str($inputdata['job_id']) . "' AND `status`=2 AND `delete_flag`=0 
						UNION
						SELECT A.* FROM `ki_estimates_info` A INNER JOIN `ki_jobs_info` B ON A.`estimate_id`=B.`estimate_id` AND B.`job_id`='" . safe_str($inputdata['job_id']) . "' AND B.`delete_flag`=0 WHERE A.`status`=2 AND A.`delete_flag`=0
					) EI ON 
						ELI.`estimate_id`=EI.`estimate_id` 
					LEFT JOIN `ki_products_info` PI ON 
						ELI.`product_id`=PI.`product_id` AND PI.`delete_flag`=0 
					LEFT JOIN `ki_product_consumption_info` PCI ON 
						PI.`product_id`=PCI.`product_id` AND PCI.`location_type`='" . safe_str($inputdata['location_type']) . "' AND PCI.`location_id`='" . safe_str($inputdata['location_id']) . "' AND PCI.`delete_flag`=0 
					LEFT JOIN `ki_product_quantites_info` PQI ON 
						PI.`product_id`=PQI.`product_id` AND PQI.`location_type`='" . safe_str($inputdata['location_type']) . "' AND PQI.`location_id`='" . safe_str($inputdata['location_id']) . "' AND PQI.`delete_flag`=0 
					LEFT JOIN `ki_product_prices_info` PPI ON 
						PI.`product_id`=PPI.`product_id` AND PPI.`delete_flag`=0 
					LEFT JOIN `ki_meta_taxes_info` MTI1 ON 
						PPI.`retail_tax`=MTI1.`tax_id` AND MTI1.`is_enabled`=1 AND MTI1.`delete_flag`=0 
					LEFT JOIN `ki_meta_taxes_info` MTI2 ON 
						PPI.`distribution_tax`=MTI2.`tax_id` AND MTI2.`is_enabled`=1 AND MTI2.`delete_flag`=0 
					LEFT JOIN `ki_meta_taxes_info` MTI3 ON 
						MTI3.`is_default`=1 AND MTI3.`delete_flag`=0 
					WHERE 
						ELI.`delete_flag`=0 
					ORDER BY 
						product_name";
		$pagg_result = $con->query($pagg_qry);
		echo $con->error;
		$pagg_count = $pagg_result->num_rows;
		$pagging_list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}
		return $pagging_list;
	}

	function get_certificate_line_items_list($inputdata)
	{
		// params - estimate_id, certificate_of_work_id
		// returns list of saved certificate line items and its related data to show on certificate of work view page
		global $con;
		$data = array();
		// $estimates = array();
		// if(!empty($inputdata['estimate_id'])){
		// foreach($inputdata['estimate_id'] as $est){
		// $estimates[] = "EI.`estimate_id`='".$est."'";
		// }
		// }
		// if(!empty($estimates)){
		// $estimates = " AND (".implode(" OR ", $estimates).")";
		// }else{
		// $estimates = '';
		// }
		$qry = "SELECT 
					EI.`estimate_number`, EI.`estimate_id`, ELI.`quantity` AS org_estimate_qty, EI.`amounts_include_gst`, PI.`minutes_to_complete`, PI.`product_type`, PPI.*, COALESCE(PPI.`spiff`,0) AS spiff, COALESCE(MLI.`tax_value`,MLI1.`tax_value`) AS retail_tax, PQI.`stock_on_hand`, CLI.*, CLI.`cost_price` AS manual_cost_price, COALESCE(PI.`category_id`,CLI.`category_id`,ELI.`category_id`) AS category_id 
				FROM 
					`ki_job_certificate_of_work_items_info` CLI 
				INNER JOIN `ki_job_certificate_of_work_info` CI ON 
					CLI.`certificate_of_work_id`=CI.`certificate_of_work_id` AND CI.`delete_flag`=0 
				INNER JOIN `ki_jobs_info` JI ON 
					CI.`job_id`=JI.`job_id` AND JI.`delete_flag`=0 
				LEFT JOIN `ki_estimate_line_items_info` ELI ON 
					CLI.`estimate_line_item_id`=ELI.`estimate_line_item_id` AND ELI.`delete_flag`=0 
				LEFT JOIN `ki_estimates_info` EI ON 
					ELI.`estimate_id`=EI.`estimate_id` AND EI.`delete_flag`=0 AND EI.`estimate_id` IN (
						SELECT DISTINCT `estimate_id` FROM `ki_job_certificate_of_work_estimates_mapping_info` WHERE `certificate_of_work_id`='" . safe_str($inputdata['certificate_of_work_id']) . "' AND `delete_flag`=0
					)
				LEFT JOIN `ki_products_info` PI ON 
					CLI.`product_id`=PI.`product_id` AND PI.`delete_flag`=0 
				LEFT JOIN `ki_product_quantites_info` PQI ON 
					PI.`product_id`=PQI.`product_id` AND JI.`home_store_type`=PQI.`location_type` AND JI.`home_store_id`=PQI.`location_id` AND PQI.`delete_flag`=0 
				LEFT JOIN `ki_product_prices_info` PPI ON 
					PI.`product_id`=PPI.`product_id` AND PPI.`delete_flag`=0 
				LEFT JOIN `ki_meta_taxes_info` MLI ON 
					PPI.`retail_tax`=MLI.`tax_id` AND MLI.`delete_flag`=0 
				LEFT JOIN `ki_meta_taxes_info` MLI1 ON 
					MLI1.`is_default`=1 AND MLI1.`delete_flag`=0 
				WHERE 
					CLI.`certificate_of_work_id`='" . safe_str($inputdata['certificate_of_work_id']) . "' AND CLI.`delete_flag`=0";
		$result = $con->query($qry);
		while ($row = $result->fetch_assoc()) {
			$data[] = $row;
		}
		// echo"<pre>";print_r($data);die;
		return $data;
	}

	function get_certificate_of_work_estimates_mapping_list($inputdata)
	{
		// params - certificate_of_work_id
		// returns list of estimates associated to single certificate of work
		global $con;
		$data = array();
		$query = "SELECT DISTINCT * FROM `ki_job_certificate_of_work_estimates_mapping_info` AB INNER JOIN `ki_estimates_info` CD ON AB.`estimate_id`=CD.`estimate_id` AND CD.`delete_flag`=0 WHERE AB.`certificate_of_work_id`='" . safe_str($inputdata['certificate_of_work_id']) . "' AND AB.`delete_flag`=0";
		$result = $con->query($query);
		while ($row = $result->fetch_assoc()) {
			$data[] = $row;
		}
		return $data;
	}

	function get_invoice_amount_paid_info($inputdata)
	{
		// params - invoice_id
		// returns the total amount paid (includes tender payment, deposit and store credit payments)
		global $con;
		$query = "SELECT 
					IPI.`payment_id`, 
					CASE
						WHEN `total_including_GST`<0 AND (COALESCE(SUM(`amount`-`change`),0)+II.`used_store_credit`+II.`used_loyalty_credits`+II.`used_deposit`)<=`total_including_GST` THEN 1
						WHEN `total_including_GST`>0 AND (COALESCE(SUM(`amount`-`change`),0)+II.`used_store_credit`+II.`used_loyalty_credits`+II.`used_deposit`)>=`total_including_GST` THEN 1
						WHEN `total_including_GST`=0 AND IPI.`payment_id` IS NOT NULL AND ((COALESCE(SUM(`amount`-`change`),0)+II.`used_store_credit`+II.`used_loyalty_credits`+II.`used_deposit`)=0 || (COALESCE(SUM(`amount`-`change`),0)+II.`used_store_credit`+II.`used_loyalty_credits`+II.`used_deposit`) IS NULL) THEN 1
						ELSE 0
					END AS is_fully_paid, (COALESCE(SUM(`amount`-`change`),0)+II.`used_store_credit`+II.`used_loyalty_credits`+II.`used_deposit`) AS amount_paid 
				FROM 
					`ki_invoice_payment_info` IPI 
				INNER JOIN `ki_invoices_info` II ON 
					II.`invoice_id`='" . safe_str($inputdata['invoice_id']) . "' AND II.`delete_flag`=0 
				WHERE 
					`undo_datetime` IS NULL AND IPI.`invoice_id`='" . safe_str($inputdata['invoice_id']) . "' AND IPI.`delete_flag`=0";
		$result = $con->query($query);
		echo $con->error;
		$row = $result->fetch_assoc();
		return $row;
	}

	function get_invoice_details($inputdata)
	{
		// params - invoice_id
		// returns the details of invoice to show on invoice view page
		global $con;
		$qry = "SELECT 
					COALESCE(KL.`store_name`, MN.`distribution_name`, OP.`production_name`) AS location_name, 
					CONCAT(COALESCE(CD.`first_name`,''),' ', COALESCE(CD.`last_name`,'')) AS created_by_user, 
					CONCAT(COALESCE(WX.`first_name`,''),' ', COALESCE(WX.`last_name`,'')) AS assigned_tech_name, 
					CONCAT(COALESCE(EF.`first_name`,''),' ', COALESCE(EF.`last_name`,'')) AS customer_name, 
					EF.`first_name` as cust_first_name, EF.`phone` as cust_phone_no, EF.`email` as cust_email, EF.`is_loyalty_rewards_registered`, EF.`credit_terms`, EF.`is_unsubscribed_to_marketing`, COALESCE(GH.`source_name`,'') AS referral_source_name, EF.`referral_source_id`, EF.`suburb_town`, EF.`delete_flag` AS cust_delete_flag, 
					QR.`store_name`, QR.`phone_number` AS store_phone_no, QR.`email` AS store_email,BC.`nominated_contact_id` AS nominated_id, BC.`first_name` AS nom_first_name,BC.`last_name` AS nom_last_name, CONCAT(COALESCE(CONCAT(QR.`address`, ','),''),' ',COALESCE(CONCAT(QR.`suburb`, ','),''),' ',COALESCE(CONCAT(QR.`state`, ','),''),' ',COALESCE(CONCAT(QR.`postcode`, ','),''),' ',COALESCE(QR.`country`,'')) AS store_address, 
					COALESCE(KL.`email`, MN.`email`, OP.`email`) AS location_email, COALESCE(KL.`bdm_commission`, MN.`bdm_commission`, OP.`bdm_commission`) AS location_bdm_commission, 
					COALESCE(KL.`phone_number`, MN.`phone_number`, OP.`phone_number`) AS location_phone_number, 
					COALESCE(KL.`address`,MN.`address`,OP.`address`) AS location_address, COALESCE(KL.`suburb`,MN.`suburb`,OP.`suburb`) AS location_suburb, COALESCE(KL.`postcode`,MN.`postcode`,OP.`postcode`) AS location_postcode, COALESCE(KL.`state`,MN.`state`,OP.`state`) AS location_state, COALESCE(KL.`country`,MN.`country`,OP.`country`) AS location_country, COALESCE(KL.`directions`,MN.`directions`,OP.`directions`) AS location_directions, 
					IJ.`store_delivery_id`, IJ.`store_delivery_number`, IJ.`delete_flag` as sd_delete_flag, 
					AB.`due_date`, UV.`invoice_id` AS bdm_invoice_id1, UV.`invoice_number` AS bdm_invoice_number, (UV.`total_including_GST`-UV.`GST`) AS BDM_total_excluding_GST, YZ.`invoice_id` AS parent_invoice_id_created_by_bdm, YZ.`invoice_number` AS parent_invoice_number_created_by_bdm, ST.`invoice_number` AS parent_invoice_number, ST.`recurring_type` AS parent_recurring_type, ST.`delete_flag` AS parent_delete_flag, AB.*, 
					CASE 
						WHEN YZ.`invoice_id` IS NOT NULL AND YZ.`invoice_id`!=0 THEN 'BDM Invoice'
						WHEN (AB.`customer_id` IS NULL OR AB.`customer_id`=0) AND AB.`store_id` IS NOT NULL AND AB.`store_id`!=0 THEN 'Store Delivery Invoice'
						WHEN AB.`home_store_type`=1 THEN 'Retail Invoice'
						ELSE
							CASE 
								WHEN AB.`customer_id` IS NOT NULL AND AB.`customer_id`!=0 AND (AB.`store_id` IS NULL OR AB.`store_id`=0) THEN 'Distribution Customer Invoice'
							END
					END AS `invoice_type` 
				FROM 
					`ki_invoices_info` AB 
				LEFT JOIN `ki_users_info` CD ON 
					AB.`user_id`=CD.`user_id`
				LEFT JOIN `ki_customer_nominated_contacts_info` BC ON
    				AB.`customer_id` = BC.`customer_id` 
				LEFT JOIN `ki_customers_info` EF ON 
					AB.`customer_id`=EF.`customer_id` 
				LEFT JOIN `ki_referral_sources_info` GH ON 
					EF.`referral_source_id`=GH.`source_id` 
				LEFT JOIN `ki_store_delivery_info` IJ ON
					AB.`invoice_id` = IJ.`invoice_id` 
				LEFT JOIN `ki_stores_info` KL ON 
					AB.`home_store_type` = 1 AND AB.`home_store_id`=KL.`store_id` AND KL.`delete_flag`=0 
				LEFT JOIN `ki_distribution_branches_info` MN ON 
					AB.`home_store_type` = 2 AND AB.`home_store_id` = MN.`distribution_branch_id` AND MN.`delete_flag`=0 
				LEFT JOIN `ki_production_info` OP ON 
					AB.`home_store_type` = 3 AND AB.`home_store_id` = OP.`production_id` AND OP.`delete_flag`=0 
				LEFT JOIN `ki_stores_info` QR ON 
					AB.`store_id` = QR.`store_id` AND QR.`delete_flag`=0 
				LEFT JOIN `ki_invoices_info` ST ON 
					AB.`parent_invoice_id`=ST.`invoice_id`
				LEFT JOIN `ki_invoices_info` UV ON 
					AB.`bdm_invoice_id`=UV.`invoice_id` AND UV.`delete_flag`=0
				LEFT JOIN `ki_users_info` WX ON 
					AB.`assigned_tech`=WX.`user_id` AND WX.`delete_flag`=0
				LEFT JOIN `ki_invoices_info` YZ ON 
					AB.`invoice_id`=YZ.`bdm_invoice_id` AND YZ.`delete_flag`=0
				WHERE 
					AB.`invoice_id`='" . safe_str($inputdata['invoice_id']) . "'";
		$result = $con->query($qry);
		echo $con->error;
		$row = $result->fetch_assoc();
		// echo "<pre>";print_r($row);echo "</pre>";
		if (!empty($row)) {
			$address = [$row['location_address'], $row['location_directions'], $row['location_suburb'], $row['location_state'], $row['location_country'], $row['location_postcode']];
			$row['concatenated_address'] = implode(", ", array_filter($address));
			$select = "SELECT * FROM `ki_invoices_info` WHERE `parent_recurring_product_invoice_id`='".$row['invoice_id']."'";
			$res = $con->query($select);
			echo $con->error;
			$i = 0;
			while($row1 = $res->fetch_assoc()){
				$row['recurring_invoices'][$row1['invoice_id']] = $row1['invoice_number'];
			}
		}

		return $row;
	}

	function get_recurring_invoice_line_items_list($inputdata)
	{
		// params - invoice_id
		// returns the list of invoice line items to show on invoice view page
		global $con;
		$data = array();
		$qry = "SELECT 
					DISTINCT CI.`category_name`, SDM.quantity AS mapping_qty, SDI.`store_delivery_id`, SDI.`store_delivery_number`, SDI.`status` AS sd_status, SDLI.`status` AS sdli_status, ILI.*, CASE ILI.status
						WHEN 1 THEN 'To Order'
						WHEN 2 THEN 'Ordered'
						WHEN 3 THEN 'Back Ordered'
						WHEN 4 THEN 'Arrived'
						WHEN 5 THEN 'Finished'
						WHEN 6 THEN 'Invoiced'
						WHEN 7 THEN 'Special Ordered'
						WHEN 8 THEN 'Pre Sold'
					END AS invoice_status,
					CASE 
						WHEN P.product_id is not null THEN P.product_name 
						ELSE ILI.product_name 
					END as product_name,
					CASE 
						WHEN P.product_id is not null THEN P.SKU 
						ELSE ILI.SKU 
					END as SKU,
					P.product_type
				FROM 
					`ki_invoice_line_items_info` ILI 
				INNER JOIN `ki_invoices_info` II ON 
					ILI.`invoice_id`=II.`invoice_id` AND II.`delete_flag` = 0 
				LEFT JOIN ki_products_info P ON 
					P.product_id=ILI.product_id 
				LEFT JOIN `ki_categories_info` CI ON 
					ILI.`category_id` = CI.`category_id` AND CI.`delete_flag` = 0 
				LEFT JOIN `ki_store_delivery_line_items_mapping_info` SDM ON 
					SDM.`type`=1 AND SDM.`type_id` = ILI.`invoice_line_item_id` AND SDM.`delete_flag` = 0 
				LEFT JOIN `ki_store_delivery_line_items_info` SDLI ON 
					SDM.`store_delivery_line_item_id` = SDLI.`store_delivery_line_item_id` AND SDLI.`delete_flag` = 0 
				LEFT JOIN `ki_store_delivery_info` SDI ON 
					SDLI.`store_delivery_id` = SDI.`store_delivery_id` AND SDI.`delete_flag` = 0 
				WHERE 
					ILI.`invoice_id` = '" . safe_str($inputdata['invoice_id']) . "' AND ILI.`delete_flag` = 0 
				ORDER BY 
					ILI.`product_name`";
		$result = $con->query($qry);
		echo $con->error;
		while ($row = $result->fetch_assoc()) {
			$data[] = $row;
		}
		return $data;
	}

	function get_invoice_line_items_list($inputdata)
	{
		// params - invoice_id
		// returns the list of invoice line items to show on invoice view page
		global $con;
		$data = array();
		$qry = "SELECT 
					DISTINCT CI.`category_name`, SDM.quantity AS mapping_qty, SDI.`store_delivery_id`, SDI.`store_delivery_number`, SDI.`status` AS sd_status,SI.store_delivery_prefix, SDLI.`status` AS sdli_status, ILI.*, ILIM.`invoice_line_item_id` as mapping_invoice_line_item_id, fifo_cp, CASE ILI.status
						WHEN 1 THEN 'To Order'
						WHEN 2 THEN 'Ordered'
						WHEN 3 THEN 'Back Ordered'
						WHEN 4 THEN 'Arrived'
						WHEN 5 THEN 'Finished'
						WHEN 6 THEN 'Invoiced'
						WHEN 7 THEN 'Special Ordered'
						WHEN 8 THEN 'Pre Sold'
					END AS invoice_status 
				FROM 
					`ki_invoice_line_items_info` ILI 
				LEFT JOIN (
					SELECT `invoice_line_item_id`, SUM(`quantity`*`cost_price`) AS fifo_cp FROM `ki_invoice_line_items_cost_price_mapping_info` WHERE `delete_flag` = 0 GROUP BY `invoice_line_item_id`
				)ILIM ON
					ILIM.`invoice_line_item_id`=ILI.`invoice_line_item_id`
				LEFT JOIN `ki_invoices_info` II ON 
					ILI.`invoice_id`=II.`invoice_id` AND II.`delete_flag` = 0 
				LEFT JOIN `ki_categories_info` CI ON 
					ILI.`category_id` = CI.`category_id` AND CI.`delete_flag` = 0 
				LEFT JOIN `ki_store_delivery_line_items_mapping_info` SDM ON 
					SDM.`type`=1 AND SDM.`type_id` = ILI.`invoice_line_item_id` AND SDM.`delete_flag` = 0 
				LEFT JOIN `ki_store_delivery_line_items_info` SDLI ON 
					SDM.`store_delivery_line_item_id` = SDLI.`store_delivery_line_item_id` AND SDLI.`delete_flag` = 0 
				LEFT JOIN `ki_store_delivery_info` SDI ON 
					SDLI.`store_delivery_id` = SDI.`store_delivery_id` AND SDI.`delete_flag` = 0 
				LEFT JOIN ki_stores_info SI ON
					SI.store_id=SDI.store_id AND SI.delete_flag=0
				WHERE 
					ILI.`invoice_id` = '" . safe_str($inputdata['invoice_id']) . "' AND ILI.`delete_flag` = 0 
				ORDER BY 
					ILI.`product_name`";
		$result = $con->query($qry);
		echo $con->error;
		while ($row = $result->fetch_assoc()) {
			$data[] = $row;
		}
		return $data;
	}

	function get_invoice_payment_history($inputdata)
	{
		// params - invoice_id
		// returns list of all tender payments and also checks for end of day balance status to provide option to undo the payment
		global $con;
		$data = array();
		$query = "SELECT 
					DISTINCT IPI.*,til_id,is_closed 
				FROM 
					`ki_invoice_payment_info` IPI 
				LEFT JOIN 
					( 
						SELECT 
							AA.* 
						FROM 
							`ki_tils_info` AA 
						INNER JOIN 
							( 
								SELECT 
								`location_type`, `location_id`, MAX(`created_on`) AS max_created 
								FROM 
								`ki_tils_info` 
								GROUP BY 
								`location_type`, `location_id` 
							) BB ON 
							AA.`created_on` = BB.max_created AND AA.`location_type` = BB.`location_type` AND AA.`location_id` = BB.`location_id` 
					) TI ON 
					IPI.`location_type` = TI.`location_type` AND IPI.`location_id` = TI.`location_id` AND IPI.`created_on`>=TI.`created_on` 
				WHERE 
					`is_off_board_payment`=0 AND `invoice_id`='" . safe_str($inputdata['invoice_id']) . "' 
				ORDER BY 
					`payment_datetime` DESC";
		$result = $con->query($query);
		while ($row = $result->fetch_assoc()) {
			$data[] = $row;
		}
		return $data;
	}

	function get_stock_on_hand_0_products_list($inputdata)
	{
		// params - location_type, location_id
		// returns list of all products that has stock on hand = 0 for a particular location
		global $con;
		$data = array();
		$query = "SELECT DISTINCT PI.* FROM `ki_products_info` PI INNER JOIN `ki_product_quantites_info` PQI ON PI.`product_id`=PQI.`product_id` AND PQI.`stock_on_hand`=0 AND PQI.`location_type`='" . safe_str($inputdata['location_type']) . "' AND PQI.`location_id`='" . safe_str($inputdata['location_id']) . "' AND PQI.`delete_flag`=0 WHERE PI.`status`=1 AND PI.`delete_flag`=0";
		$result = $con->query($query);
		echo $con->error;
		while ($row = $result->fetch_assoc()) {
			$data[] = $row;
		}
		return $data;
	}

	function check_for_special_order_product($inputdata)
	{
		// params - product_id, location_type, location_id
		// returns the data of product according to conditions to satisfy for special order
		global $con;
		$data = array();
		$query = "SELECT * FROM (
					SELECT
						PCI.`product_id`,
						COALESCE(
							PCI.`location_type`,
							PQI.`location_type`
						) AS location_type,
						COALESCE(
							PCI.`location_id`,
							PQI.`location_id`
						) AS location_id,
						PCI.`core_range`,
						COALESCE(PQI.`stock_on_hand`,0) AS stock_on_hand
					FROM
						`ki_product_consumption_info` PCI
					LEFT JOIN `ki_product_quantites_info` PQI ON
						PCI.`product_id` = PQI.`product_id` AND PCI.`location_type` = PQI.`location_type` AND PCI.`location_id` = PQI.`location_id` AND PQI.`delete_flag` = 0
					WHERE
						PCI.`product_id` = '" . safe_str($inputdata['product_id']) . "' AND PCI.`location_type` = '" . safe_str($inputdata['location_type']) . "' AND PCI.`location_id` = '" . safe_str($inputdata['location_id']) . "' AND PCI.`delete_flag` = 0
					UNION
					SELECT
						PQI.`product_id`,
						COALESCE(
							PCI.`location_type`,
							PQI.`location_type`
						) AS location_type,
						COALESCE(
							PCI.`location_id`,
							PQI.`location_id`
						) AS location_id,
						COALESCE(PCI.`core_range`,0) AS core_range,
						PQI.`stock_on_hand`
					FROM
						`ki_product_consumption_info` PCI
					RIGHT JOIN `ki_product_quantites_info` PQI ON
						PCI.`product_id` = PQI.`product_id` AND PCI.`location_type` = PQI.`location_type` AND PCI.`location_id` = PQI.`location_id` AND PQI.`delete_flag` = 0
					WHERE
						PQI.`product_id` = '" . safe_str($inputdata['product_id']) . "' AND PQI.`location_type` = '" . safe_str($inputdata['location_type']) . "' AND PQI.`location_id` = '" . safe_str($inputdata['location_id']) . "' AND PQI.`delete_flag` = 0
				) AA";
		$result = $con->query($query);
		echo $con->error;
		while ($row = $result->fetch_assoc()) {
			$data = $row;
		}
		if (empty($data)) {
			$data['product_id'] = $inputdata['product_id'];
			$data['location_type'] = $inputdata['location_type'];
			$data['location_id'] = $inputdata['location_id'];
			$data['core_range'] = 0;
			$data['stock_on_hand'] = 0;
			$data['is_special_order'] = 1;
		} else {
			// if($data['core_range']==0 && $data['stock_on_hand']<=0){
			// $data['is_special_order'] = 1;
			// }
			// special order only if special order product and soh<=0
			if ($data['core_range'] == 1 && $data['stock_on_hand'] <= 0) {
				$data['is_special_order'] = 1;
			} else {
				$data['is_special_order'] = 0;
			}
		}
		// print_r($data); die;
		return $data;
	}

	function get_new_off_board_line_items($inputdata)
	{
		// params - array of certificate_of_work_id
		// return items of specified certificate of work while creating new off board
		global $con;
		$data = array();
		$certificate_of_work = array();
		foreach ($inputdata['certificate_of_work_id'] as $est) {
			$certificate_of_work[] = "CLI.`certificate_of_work_id`='" . $est . "'";
		}
		$certificate_of_work = " (" . implode(" OR ", $certificate_of_work) . ") AND ";
		$query = "SELECT CI.`certificate_number`, EI.`estimate_number`, CONCAT(COALESCE(UI.`first_name`,''),' ', COALESCE(UI.`last_name`,'')) AS completed_by_user, ELI.`product_name`, ELI.`quantity`, ELI.`tax`, PI.`minutes_to_complete`, PI.`category_id`, CLI.* FROM `ki_job_certificate_of_work_items_info` CLI INNER JOIN `ki_job_certificate_of_work_info` CI ON CLI.`certificate_of_work_id`=CI.`certificate_of_work_id` AND CI.`delete_flag`=0 INNER JOIN `ki_users_info` UI ON CLI.`user_id`=UI.`user_id` AND UI.`delete_flag`=0 INNER JOIN `ki_estimate_line_items_info` ELI ON CLI.`estimate_line_item_id`=ELI.`estimate_line_item_id` AND ELI.`delete_flag`=0 INNER JOIN `ki_estimates_info` EI ON EI.`estimate_id`=ELI.`estimate_id` AND EI.`delete_flag`=0 INNER JOIN `ki_products_info` PI ON CLI.`product_id`=PI.`product_id` AND PI.`delete_flag`=0 WHERE " . $certificate_of_work . " CLI.`delete_flag`=0 ORDER BY CI.`certificate_number`, EI.`estimate_number`, ELI.`product_name` ASC";
		$result = $con->query($query);
		while ($row = $result->fetch_assoc()) {
			$data[] = $row;
		}
		return $data;
	}

	function get_off_board_line_items($inputdata)
	{
		// params - off_board_id
		// returns list of saved off-board line items and its related data to show on off-board view page
		global $con;
		$data = array();
		$qry = "SELECT 
					CI.`certificate_number`, EI.`estimate_number`, CONCAT(COALESCE(UI.`first_name`,''),' ', COALESCE(UI.`last_name`,'')) AS completed_by_user, ELI.`product_name` ,OBLI.* ,CLI.* 
				FROM 
					`ki_job_off_boarding_items_info` OBLI 
				LEFT JOIN `ki_job_certificate_of_work_items_info` CLI ON 
					OBLI.`certificate_item_id`=CLI.`certificate_item_id` AND CLI.`delete_flag`=0 
				LEFT JOIN `ki_job_certificate_of_work_info` CI ON 
					CI.`certificate_of_work_id`=CLI.`certificate_of_work_id` AND CI.`delete_flag`=0 
				LEFT JOIN `ki_users_info` UI ON 
					CLI.`user_id`=UI.`user_id` AND UI.`delete_flag`=0 
				LEFT JOIN `ki_estimate_line_items_info` ELI ON 
					CLI.`estimate_line_item_id`=ELI.`estimate_line_item_id` AND ELI.`delete_flag`=0 
				LEFT JOIN `ki_estimates_info` EI ON 
					ELI.`estimate_id`=EI.`estimate_id` AND EI.`delete_flag`=0 
				WHERE 
					`off_board_id`='" . safe_str($inputdata['off_board_id']) . "' AND OBLI.`delete_flag`=0 
				ORDER BY 
					CI.`certificate_number`, EI.`estimate_number`, ELI.`product_name` ASC";
		$result = $con->query($qry);
		echo $con->error;
		while ($row = $result->fetch_assoc()) {
			$data[] = $row;
		}
		return $data;
	}

	function get_certificate_of_work_estimates_mapping_list_offboard($inputdata)
	{
		// params - array of certificate_of_work_id
		// returns list of estimates associated to multiple certificate of work
		global $con;
		$data = array();
		$certificate_of_work = array();
		foreach ($inputdata['certificate_of_work_id'] as $est) {
			$certificate_of_work[] = "AB.`certificate_of_work_id`='" . safe_str($est) . "'";
		}
		$certificate_of_work = " (" . implode(" OR ", $certificate_of_work) . ") AND ";
		$query = "SELECT DISTINCT * FROM `ki_job_certificate_of_work_estimates_mapping_info` AB INNER JOIN `ki_estimates_info` CD ON AB.`estimate_id`=CD.`estimate_id` AND CD.`delete_flag`=0 WHERE " . $certificate_of_work . " AB.`delete_flag`=0";
		$result = $con->query($query);
		echo $con->error;
		while ($row = $result->fetch_assoc()) {
			$data[] = $row;
		}
		return $data;
	}

	function get_estimates_list_for_offboard($inputdata)
	{
		// params - array of certificate_of_work_id
		// returns list of estimates associated to all certificate of work of off-board
		global $con;
		$data = array();
		$certificate_of_work = array();
		foreach ($inputdata['certificate_of_work_id'] as $est) {
			$certificate_of_work[] = "AB.`certificate_of_work_id`='" . safe_str($est) . "'";
		}
		$certificate_of_work = " (" . implode(" OR ", $certificate_of_work) . ") AND ";
		$query = "SELECT DISTINCT CD.* FROM `ki_job_certificate_of_work_estimates_mapping_info` AB INNER JOIN `ki_estimates_info` CD ON AB.`estimate_id`=CD.`estimate_id` AND CD.`delete_flag`=0 WHERE " . $certificate_of_work . " AB.`delete_flag`=0";
		$result = $con->query($query);
		while ($row = $result->fetch_assoc()) {
			$data[] = $row;
		}
		return $data;
	}

	function get_invoices_list_for_offboard($inputdata)
	{
		// params - array of certificate_of_work_id
		// returns list of invoices associated to all certificate of work of off-board
		global $con;
		$data = array();
		$certificate_of_work = array();
		foreach ($inputdata['certificate_of_work_id'] as $est) {
			$certificate_of_work[] = "`certificate_of_work_id`='" . safe_str($est) . "'";
		}
		$certificate_of_work = " (" . implode(" OR ", $certificate_of_work) . ") AND ";
		$query = "SELECT DISTINCT * FROM `ki_invoices_info` WHERE " . $certificate_of_work . " `delete_flag`=0";
		$result = $con->query($query);
		while ($row = $result->fetch_assoc()) {
			$data[] = $row;
		}
		return $data;
	}

	function calculate_total_balance_owing_for_off_board($inputdata)
	{
		// params - array of certificate_of_work_id
		// returns total balance owing of all invoices associated to specified certificate of work 
		global $con;
		$data = 0;
		$certificate_of_work = array();
		foreach ($inputdata['certificate_of_work_id'] as $est) {
			$certificate_of_work[] = "`certificate_of_work_id`='" . safe_str($est) . "'";
		}
		$certificate_of_work = " (" . implode(" OR ", $certificate_of_work) . ") AND ";
		$query = "SELECT 
					COALESCE(GREATEST(SUM(`total_including_GST` - COALESCE(AB.amount_paid,0) - `used_store_credit` - `used_loyalty_credits`- `used_deposit`),0),0) AS balance_owing 
				FROM 
					`ki_invoices_info` II 
				LEFT JOIN( 
					SELECT 
						SUM(`amount`-`change`) AS amount_paid, `invoice_id` 
					FROM 
						`ki_invoice_payment_info` 
					WHERE 
						`undo_datetime` IS NULL AND `delete_flag`=0 
					GROUP BY 
						`invoice_id` 
				) AB ON 
					AB.`invoice_id`=II.`invoice_id` 
				WHERE 
					" . $certificate_of_work . "  II.`delete_flag`=0";
		$result = $con->query($query);
		while ($row = $result->fetch_assoc()) {
			$data = $row['balance_owing'];
		}
		return $data;
	}

	function calculate_remaining_balance_for_each_invoices($inputdata)
	{
		// params - array of certificate_of_work_id
		// returns balance owing of each invoice associated to all certificate of work of off-board
		global $con;
		$data = array();
		$certificate_of_work = array();
		foreach ($inputdata['certificate_of_work_id'] as $est) {
			$certificate_of_work[] = "`certificate_of_work_id`='" . safe_str($est) . "'";
		}
		$certificate_of_work = " (" . implode(" OR ", $certificate_of_work) . ") AND ";
		$query = "SELECT 
					* 
				FROM 
					(
						SELECT 
							II.`invoice_id`, II.`job_id`, II.`certificate_of_work_id`, II.`home_store_type`, CI.`customer_id`, CI.`is_loyalty_rewards_registered`, CI.`is_unsubscribed_to_marketing`, II.`used_deposit`, (`total_including_GST` - COALESCE(AB.amount_paid,0) - `used_store_credit`- `used_loyalty_credits`- `used_deposit`) AS remaining_balance 
						FROM 
							`ki_invoices_info` II 
						LEFT JOIN `ki_customers_info` CI ON 
							II.`customer_id`=CI.`customer_id` AND CI.`delete_flag`=0
						LEFT JOIN 
							( 
								SELECT 
									SUM(`amount`-`change`) AS amount_paid, `invoice_id` 
								FROM 
									`ki_invoice_payment_info` 
								WHERE 
									`undo_datetime` IS NULL AND `delete_flag`=0 
								GROUP BY 
									`invoice_id` 
							) AB 
							ON AB.`invoice_id`=II.`invoice_id` 
						WHERE 
							" . $certificate_of_work . " II.`delete_flag`=0 
						ORDER BY 
							II.`created_on` ASC
					) AA 
				WHERE 
					remaining_balance!=0";
		$result = $con->query($query);
		while ($row = $result->fetch_assoc()) {
			$data[] = $row;
		}
		return $data;
	}

	function get_off_board_payment_history($inputdata)
	{
		// params - off_board_id and array of certificate_of_work_id
		// returns list of all tender payments and also checks for end of day balance status to provide option to undo the payment
		global $con;
		$data = array();
		$certificate_of_work = array();
		foreach ($inputdata['certificate_of_work_id'] as $est) {
			$certificate_of_work[] = "IPI.`certificate_of_work_id`='" . safe_str($est) . "'";
		}
		$certificate_of_work = " (" . implode(" OR ", $certificate_of_work) . ") AND ";
		$query = "SELECT 
					II.`invoice_number`,IPI.*,`til_id`,`is_closed` 
				FROM 
					`ki_invoice_payment_info` IPI 
				INNER JOIN `ki_invoices_info` II ON 
					IPI.`invoice_id`=II.`invoice_id` AND II.`delete_flag`=0 
				LEFT JOIN 
					( 
						SELECT 
							AA.* 
						FROM 
							`ki_tils_info` 
						AA INNER JOIN 
							( 
								SELECT 
									`location_type`, `location_id`, MAX(`created_on`) AS max_created 
								FROM 
									`ki_tils_info` 
								GROUP BY 
									`location_type`, `location_id` 
							) BB ON 
							AA.`created_on` = BB.max_created AND AA.`location_type` = BB.`location_type` AND AA.`location_id` = BB.`location_id` 
					) TI ON 
					IPI.`location_type` = TI.`location_type` AND IPI.`location_id` = TI.`location_id` AND IPI.`created_on`>=TI.`created_on` 
				WHERE 
					" . $certificate_of_work . " `is_off_board_payment`=1 AND IPI.`off_board_id`='" . safe_str($inputdata['off_board_id']) . "' 
				ORDER BY 
					`payment_datetime` DESC";
		$result = $con->query($query);
		while ($row = $result->fetch_assoc()) {
			$data[] = $row;
		}
		return $data;
	}

	function get_job_available_deposit_info($inputdata)
	{
		// params - job_id
		// returns available deposit for a job
		global $con;
		$qry = "SELECT 
					COALESCE(`credit`, 0) AS credit, COALESCE(`debit`, 0) AS debit, ( COALESCE(`credit`, 0) - COALESCE(`debit`, 0) ) AS available_deposit 
				FROM ( 
					( 
						SELECT 
							SUM(`deposit`) AS credit 
						FROM `ki_job_deposits_info` WHERE `job_id` = '" . safe_str($inputdata['job_id']) . "' AND `negative_flag` = 0 AND `delete_flag` = 0 
					) AA, 
					( 
						SELECT 
							SUM(`deposit`) AS debit 
						FROM `ki_job_deposits_info` WHERE `negative_flag` = 1 AND`job_id` = '" . safe_str($inputdata['job_id']) . "' AND `delete_flag` = 0 
					) BB 
				)";
		/*
		$qry = "SELECT 
					COALESCE(`credit`, 0) AS credit, COALESCE(`debit`, 0) AS debit, ( COALESCE(`credit`, 0) - COALESCE(`debit`, 0) ) AS available_deposit 
				FROM ( 
					( 
						SELECT 
							SUM(`deposit`) AS credit 
						FROM `ki_job_deposits_info` WHERE `tender_type_id`!=0 AND `tender_type_id` IS NOT NULL AND `job_id` = '" . safe_str($inputdata['job_id']) . "' AND `negative_flag` = 0 AND `delete_flag` = 0 
					) AA, 
					( 
						SELECT 
							SUM(`deposit`) AS debit
						FROM `ki_job_deposits_info` WHERE ( `tender_type_id`=0 OR `tender_type_id` IS NULL OR `negative_flag` = 1) AND`job_id` ='". safe_str($inputdata['job_id'])."' AND `delete_flag` = 0 
					) BB 
				)";
		*/
		$result = $con->query($qry);
		echo $con->error;
		$row = $result->fetch_assoc();
		return $row;
	}

	function check_technician_availability($inputdata)
	{
		// params - job_id, assigned_tech
		// returns a row if technician is already assigned to an ongoing job
		global $con;
		$query = "SELECT * FROM `ki_jobs_info` WHERE `job_id`!='" . safe_str($inputdata['job_id']) . "' AND `assigned_tech`='" . safe_str($inputdata['assigned_tech']) . "' AND `job_tracker_start_time` IS NOT NULL AND `job_tracker_end_time` IS NULL AND `delete_flag`=0";
		$result = $con->query($query);
		echo $con->error;
		$row = $result->fetch_assoc();
		return $row;
	}

	function get_invoice_pagging_list($inputdata)
	{
		// params - page_no, row_size, sort_on, sort_type, status, search, is_admin_tab, location_type, location_id
		// returns pagging information of invoices for listing
		global $con;
		$data = array(
			"total_records" => 0,
			"total_pages" => 0,
			"pagging_list" => array()
		);
		$status_query = $credit_type_query = ''; $invoiceArr = array();
		if (!empty($inputdata['status']) && $inputdata['status'] == 1) {
			$status_query = " AND (`payment_id` IS NOT NULL OR AA.amount_paid!=0) AND ((total_including_GST>0 AND amount_paid<total_including_GST) OR (total_including_GST<0 AND amount_paid>total_including_GST)) AND ((is_outstanding_invoice=1 AND date_paid is NULL)) ";
		} elseif (!empty($inputdata['status']) && $inputdata['status'] == 2) {
			$status_query = " AND (((`payment_id` IS NOT NULL OR AA.amount_paid!=0) AND ((total_including_GST>=0 AND amount_paid>=total_including_GST) OR (total_including_GST<0 AND amount_paid<=total_including_GST))) OR ((is_outstanding_invoice=0 OR date_paid is NOT NULL))) ";
		} elseif (!empty($inputdata['status']) && $inputdata['status'] == 3) {
			$status_query = " AND `payment_id` IS NULL AND AA.amount_paid=0 AND ((is_outstanding_invoice=1 AND date_paid is NULL)) ";
		}
		$page_no = safe_str($inputdata['page_no']);
		$row_size = safe_str($inputdata['row_size']);
		$sort_on = safe_str($inputdata['sort_on']);
		$sort_type = safe_str($inputdata['sort_type']);
		$invoice_type = '';
		if (!empty($inputdata['invoice_type']) && $inputdata['invoice_type'] == 1) {
			$invoice_type = ' AND (II.`user_id` IS NULL OR II.`user_id`="" OR II.`user_id`=0) AND II.`shipping_type`!=3 AND `is_draft`=0 ';
		} elseif (!empty($inputdata['invoice_type']) && $inputdata['invoice_type'] == 2) {
			$invoice_type = ' AND II.`user_id` IS NOT NULL AND II.`user_id`!="" AND II.`user_id`!=0 AND `recurring_type`=1 AND II.`shipping_type`=0 AND `is_draft`=0 ';
		} elseif (!empty($inputdata['invoice_type']) && $inputdata['invoice_type'] == 3) {
			$invoice_type = ' AND (II.`user_id` IS NULL OR II.`user_id`="" OR II.`user_id`=0) AND II.`shipping_type`=3 AND `is_draft`=0 ';
		} elseif (!empty($inputdata['invoice_type']) && $inputdata['invoice_type'] == 4) {
			$invoice_type = ' AND `recurring_type`!=1 AND `is_draft`=0 ';
		} elseif (!empty($inputdata['invoice_type']) && $inputdata['invoice_type'] == 5) {
			$invoice_type = ' AND `is_draft`= 1 ';
		} elseif (!empty($inputdata['invoice_type']) && $inputdata['invoice_type'] == 6) {
			$invoice_type = ' AND SC.`is_card_verified`=0 ';
		}
		$location = '';
		if (!empty($inputdata['is_report']) && $inputdata['is_report'] == 1) {
			// for unsecured invoices
			if (!empty($inputdata['credit_type']) && $inputdata['credit_type'] == 1) {
				$credit_type_query .=  " AND (II.`credit_type`=1 OR II.`credit_type`=0 OR II.`credit_type` IS NULL) ";
			} elseif (!empty($inputdata['credit_type']) && $inputdata['credit_type'] == 2) {
				$credit_type_query .= " AND II.`credit_type`=2 ";
			}
			$credit_type_query .= " AND II.`is_draft`=0 AND II.`due_date`<='" . safe_str(date("Y-m-d")) . "' ";
			$status_query .= " AND II.`total_including_GST`!=0 AND ((`payment_id` IS NULL AND AA.amount_paid=0) OR ((`payment_id` IS NOT NULL OR AA.amount_paid!=0) AND ((total_including_GST>0 AND amount_paid<total_including_GST) OR (total_including_GST<0 AND amount_paid>total_including_GST)))) ";
			$invoice_type = '';
			if (!empty($inputdata['filter_location_type']) && !empty($inputdata['filter_location_id'])) {
				$location = " AND II.`home_store_type`='" . safe_str($inputdata['filter_location_type']) . "' AND II.`home_store_id`='" . safe_str($inputdata['filter_location_id']) . "' ";
			}
		} elseif ($inputdata['is_admin_tab'] == 0) {
			$location = " AND II.`home_store_type`='" . safe_str($inputdata['location_type']) . "' AND II.`home_store_id`='" . safe_str($inputdata['location_id']) . "' ";
		}
		$query = '';
		if (!empty($inputdata['search'])) {
			$query .= " AND (CONCAT('#',II.`invoice_number`) LIKE '%" . safe_str($inputdata['search']) . "%' OR SI.`store_name` LIKE '%" . safe_str($inputdata['search']) . "%' OR CONCAT(COALESCE(CI.`first_name`,''),' ', COALESCE(CI.`last_name`,'')) LIKE '%" . safe_str($inputdata['search']) . "%' OR REPLACE(REPLACE(CI.`phone`, '-', ''), ' ', '') LIKE '%" . safe_str($inputdata['search']) . "%' OR CONCAT(COALESCE(UI.`first_name`,''),' ', COALESCE(UI.`last_name`,'')) LIKE '%" . safe_str($inputdata['search']) . "%') ";
		}
		if (!empty($inputdata['customer_id'])) {
			$query .= " AND II.`customer_id`='" . safe_str($inputdata['customer_id']) . "' ";
			$invoice_type = '';
		}
		$pcount_qry = "SELECT 
						COUNT(*) AS total_count, SUM(`total_cost`+`deleted_cost`) AS total_cost_price, SUM(`total_including_GST`-`gst`) AS total_sell_price, 
						CASE
							WHEN SC.`is_card_verified`=0 THEN 6
							WHEN II.`is_draft`=1 THEN 5
							WHEN II.`shipping_type`=3 THEN 3
							WHEN II.`user_id` IS NULL OR II.`user_id`='' OR II.`user_id`=0 THEN 1
							WHEN `recurring_type`!=1 THEN 4
							ELSE 2
						END AS invoice_type, 
						CASE
							WHEN (`payment_id` IS NOT NULL OR AA.amount_paid!=0) AND (total_including_GST>=0 AND amount_paid>=total_including_GST) OR (total_including_GST<0 AND amount_paid<=total_including_GST) THEN '2'
							WHEN is_outstanding_invoice=0 OR date_paid is NOT NULL THEN '2'
							WHEN `payment_id` IS NOT NULL OR AA.amount_paid!=0 THEN '1'
							ELSE '3'
						END AS status 
					FROM 
						`ki_invoices_info` II 
					LEFT JOIN `ki_customers_info` CI ON 
						II.`customer_id`=CI.`customer_id`  
					LEFT JOIN `ki_stores_info` SI ON 
						II.`store_id`=SI.`store_id` AND SI.`delete_flag`=0 
					LEFT JOIN `ki_users_info` UI ON 
						II.`user_id`=UI.`user_id` AND UI.`delete_flag`=0
					LEFT JOIN `ki_saved_cards_info` SC ON 
						II.`card_id`=SC.`saved_card_id` AND SC.`delete_flag`=0
					LEFT JOIN 
						(
							SELECT 
								T1.`invoice_id`, `payment_id`, ROUND(COALESCE(COALESCE(T2.amt_paid,0)+T1.`used_store_credit`+T1.`used_loyalty_credits`+T1.`used_deposit`,0),2) AS amount_paid 
							FROM 
								`ki_invoices_info` T1 
							LEFT JOIN 
								(
									SELECT 
										`payment_id`, `invoice_id`, SUM(`amount` - `change`) AS amt_paid 
									FROM 
										`ki_invoice_payment_info` 
									WHERE 
										`undo_datetime` IS NULL AND `delete_flag`=0 
									GROUP BY 
										`invoice_id`
								) T2 ON 
								T1.`invoice_id`=T2.`invoice_id`
						) AA ON 
						II.`invoice_id`=AA.`invoice_id` 
					WHERE 
						II.`delete_flag`=0 " . $invoice_type . $location . $query . $status_query . $credit_type_query;
		$pcount_result = $con->query($pcount_qry);
		echo $con->error;
		$pcount_row = $pcount_result->fetch_assoc();
		$total_records = $pcount_row["total_count"];
		$data['total_cost_price'] = $pcount_row["total_cost_price"];
		$data['total_sell_price'] = $pcount_row["total_sell_price"];
		@$total_pages = ceil($total_records / $row_size);
		if ($total_pages == 0) {
			$total_pages = 1;
		}
		if ($page_no > $total_pages) {
			$page_no = $total_pages;
		}
		$limit_from = ($row_size * $page_no) - $row_size;
		
		$pagg_qry = "SELECT 
						AA.amount_paid, II.`invoice_id`, II.`invoice_number`,SC.`is_card_verified`, II.`total_including_GST`, COALESCE(SI.`store_name`, CONCAT(COALESCE(CI.`first_name`,''),' ', COALESCE(CI.`last_name`,''))) AS customer,  CONCAT(COALESCE(UI.`first_name`,''),' ', COALESCE(UI.`last_name`,'')) AS created_by, 
						CASE
							WHEN SC.`is_card_verified`=0 THEN 6
							WHEN II.`is_draft`=1 THEN 5
							WHEN II.`shipping_type`=3 THEN 3
							WHEN II.`is_draft` = 1 AND II.`delete_flag` = 0 THEN 5
							WHEN II.`user_id` IS NULL OR II.`user_id`='' OR II.`user_id`=0 THEN 1
							WHEN `recurring_type`!=1 THEN 4
							ELSE 2
						END AS invoice_type,
						CASE
							WHEN SC.`is_card_verified`=0 THEN 'Pending Verification'
							WHEN (`payment_id` IS NOT NULL OR AA.amount_paid!=0) AND (total_including_GST>=0 AND amount_paid>=total_including_GST) OR (total_including_GST<0 AND amount_paid<=total_including_GST) THEN 'Paid'
							WHEN is_outstanding_invoice=0 OR date_paid is NOT NULL THEN 'Paid'
							WHEN `payment_id` IS NOT NULL OR AA.amount_paid!=0 THEN 'Partially Paid'
							ELSE 'Unpaid'
						END AS status_name, 
						CASE
							WHEN SC.`is_card_verified`=0 THEN 4
							WHEN (`payment_id` IS NOT NULL OR AA.amount_paid!=0) AND (total_including_GST>=0 AND amount_paid>=total_including_GST) OR (total_including_GST<0 AND amount_paid<=total_including_GST) THEN '2'
							WHEN is_outstanding_invoice=0 OR date_paid is NOT NULL THEN '2'
							WHEN`payment_id` IS NOT NULL OR AA.amount_paid!=0 THEN '1'
							ELSE '3'
						END AS status, II.`due_date`, II.`created_on` 
					FROM 
						`ki_invoices_info` II 
					LEFT JOIN `ki_customers_info` CI ON 
						II.`customer_id`=CI.`customer_id`  
					LEFT JOIN `ki_stores_info` SI ON 
						II.`store_id`=SI.`store_id` AND SI.`delete_flag`=0  
					LEFT JOIN `ki_users_info` UI ON 
						II.`user_id`=UI.`user_id` AND UI.`delete_flag`=0
					LEFT JOIN `ki_saved_cards_info` SC ON 
						II.`card_id`=SC.`saved_card_id` AND SC.`delete_flag`=0
					LEFT JOIN 
						(
							SELECT 
								T1.`invoice_id`, `payment_id`, ROUND(COALESCE(COALESCE(T2.amt_paid,0)+T1.`used_store_credit`+T1.`used_loyalty_credits`+T1.`used_deposit`,0),2) AS amount_paid 
							FROM 
								`ki_invoices_info` T1 
							LEFT JOIN 
								(
									SELECT 
										`payment_id`, `invoice_id`, SUM(`amount` - `change`) AS amt_paid 
									FROM 
										`ki_invoice_payment_info` 
									WHERE 
										`undo_datetime` IS NULL AND `delete_flag`=0 
									GROUP BY 
										`invoice_id`
								) T2 ON 
								T1.`invoice_id`=T2.`invoice_id`
						) AA ON 
						II.`invoice_id`=AA.`invoice_id` 
					WHERE 
						II.delete_flag=0 " . $invoice_type;
		if(!empty($inputdata['search_all'])) {
			$qqry = $con->query($pagg_qry . $query . $status_query . $credit_type_query . " 
					ORDER BY 
						" . safe_str($sort_on) . " " . safe_str($sort_type));
			
			if($qqry->num_rows) {
				$ii = 0;
				while ($row1 = $qqry->fetch_assoc()) {
					$invoiceArr[$ii] = $row1;
					$ii++;
				}
			}
		}
		$pagg_qry .=  $location . $query . $status_query . $credit_type_query . " 
					ORDER BY 
						" . safe_str($sort_on) . " " . safe_str($sort_type) . " 
					LIMIT 
						" . $limit_from . ", " . $row_size;
		// echo htmlspecialchars($pagg_qry); die;
		$pagg_result = $con->query($pagg_qry);
		$pagg_count = $pagg_result->num_rows;
		$pagging_list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}
		$data["total_records"] = $total_records;
		$data["total_pages"] = $total_pages;
		$data["pagging_list"] = $pagging_list;
		$data['invoiceArr'] = $invoiceArr;

		return $data;
	}

	function get_off_board_payment_history_for_invoice($inputdata)
	{
		// params - invoice_id
		// returns list of all tender payments for invoice that are done in off-board and also checks for end of day balance status to provide option to undo the payment
		global $con;
		$data = array();
		$query = "SELECT 
					OB.`off_board_id`, OB.`off_board_number`, IPI.*, `til_id`, `is_closed` 
				FROM 
					`ki_invoice_payment_info` IPI 
				INNER JOIN `ki_job_off_boarding_info` OB ON 
					IPI.`off_board_id`=OB.`off_board_id` AND OB.`delete_flag`=0 
				LEFT JOIN 
					( 
						SELECT 
							AA.* 
						FROM 
							`ki_tils_info` AA 
						INNER JOIN 
						( 
							SELECT 
								`location_type`, `location_id`, MAX(`created_on`) AS max_created 
							FROM 
								`ki_tils_info` 
							GROUP BY 
								`location_type`, `location_id` 
						) BB ON 
						AA.`created_on` = BB.max_created AND AA.`location_type` = BB.`location_type` AND AA.`location_id` = BB.`location_id` 
					) TI ON 
					IPI.`location_type` = TI.`location_type` AND IPI.`location_id` = TI.`location_id` AND IPI.`created_on`>=TI.`created_on` 
				WHERE 
					`is_off_board_payment`=1 AND IPI.`invoice_id`='" . safe_str($inputdata['invoice_id']) . "' 
				ORDER BY 
					`payment_datetime`";
		$result = $con->query($query);
		while ($row = $result->fetch_assoc()) {
			$data[] = $row;
		}
		return $data;
	}

	function update_stock_on_hand_while_creating_invoice_line_item($inputdata)
	{
		// print_r($inputdata);die;
		// params - quantity, product_id, location_type, location_id
		// updates stock on hand when an item is added, updated or deleted from invoice
		global $con;
		$data = array(
			"status" => 0,
			"errors" => array()
		);
		$action_type = 0;
		if ($inputdata['action_type'] == 1) {
			$action_type = 2;
			//increment
			$stock_on_hand = " `stock_on_hand`=`stock_on_hand`+" . safe_str($inputdata['quantity']) . " ";
		} elseif ($inputdata['action_type'] == 2) {
			$action_type = 1;
			//decrement
			$stock_on_hand = " `stock_on_hand`=`stock_on_hand`-" . safe_str($inputdata['quantity']) . " ";
		}
		$up_qry = "";

		$find_qry = "SELECT *, COALESCE(`stock_on_hand`,0) AS stock_on_hand FROM `ki_product_quantites_info` WHERE `product_id`='" . safe_str($inputdata['product_id']) . "' AND `location_type`='" . safe_str($inputdata['location_type']) . "' AND `location_id`='" . safe_str($inputdata['location_id']) . "' AND `delete_flag`=0";
		$find_result = $con->query($find_qry);
		$find_count = $find_result->num_rows;
		$row = $find_result->fetch_assoc();
		$stock = $new_stock = 0;
		if ($find_count > 0) {
			$stock = $row['stock_on_hand'];
			if ($inputdata['action_type'] == 1) {
				$new_stock = $stock + $inputdata['quantity'];
			} elseif ($inputdata['action_type'] == 2) {
				$new_stock = $stock - $inputdata['quantity'];
			}
			$up_qry = "UPDATE `ki_product_quantites_info` SET " . $stock_on_hand . ", `modified_on`='" . date("Y-m-d H:i:s") . "' WHERE `product_id`='" . safe_str($inputdata['product_id']) . "' AND `location_type`='" . safe_str($inputdata['location_type']) . "' AND `location_id`='" . safe_str($inputdata['location_id']) . "' AND `delete_flag`=0";
		} elseif ($inputdata['action_type'] == 2) {
			$new_stock = 0 - $inputdata['quantity'];
			$up_qry = "INSERT INTO `ki_product_quantites_info` (`product_id`,`location_type`,`location_id`,`stock_on_hand`,`created_on`) VALUES ('" . safe_str($inputdata['product_id']) . "','" . safe_str($inputdata['location_type']) . "','" . safe_str($inputdata['location_id']) . "',-" . safe_str($inputdata['quantity']) . ",'" . date("Y-m-d H:i:s") . "')";
		}
		// echo $up_qry;
		if (!empty($up_qry)) {
			$up_result = $con->query($up_qry);
			// echo $inputdata['action_type']."----".$new_stock;
			// print_r($up_result);
			if ($up_result) {
				$UpdateProductValuationStock = send_rest(array(
					"function" => "UpdateProductValuationStock",
					"product_id" => $inputdata['product_id'],
					"location_type" => $inputdata['location_type'],
					"location_id" => $inputdata['location_id'],
					"stock_on_hand" => $new_stock,
					"user_id" => $inputdata["user_id"],
					"home_store_type" => $inputdata["home_store_type"],
					"home_store_id" => $inputdata["home_store_id"],
					"event_type" => $inputdata["event_type"],
					"type_id" => $inputdata["type_id"],
					"soh_before_update" => $stock,
					"soh_after_update" => $new_stock,
					"qty_before_update" => $inputdata["qty_before_update"],
					"qty_after_update" => $inputdata["qty_after_update"],
					"stock_cost_price_valuation" => (!empty($inputdata['stock_cost_price_valuation'])) ? $inputdata['stock_cost_price_valuation'] : [],
					"sell_price" => (!empty($inputdata['sell_price'])) ? $inputdata['sell_price'] : ''
				));
				if ($UpdateProductValuationStock['status'] != 1) {
					$data['errors'] = $UpdateProductValuationStock['errors'];
				} else {
					$update_price_valuation = $this->UpdatePriceValuation(array(
						"product_id" => $inputdata['product_id'],
						"location_type" => $inputdata['location_type'],
						"location_id" => $inputdata['location_id'],
						"quantity" => $inputdata['quantity'],
						"action_type" => $action_type
					));
					if ($update_price_valuation['status'] != 1) {
						$data['errors'] = $update_price_valuation['errors'];
					}
				}
			} else {
				$data["errors"][] = $con->error;
			}
			
			if (empty($data['errors'])) {
				$update_stocktake = $this->UpdateStockTake(array(
					"product_id" => $inputdata['product_id'],
					"location_type" => $inputdata['location_type'],
					"location_id" => $inputdata['location_id'],
					"quantity" => $inputdata['quantity'],
					"action_type" => $action_type
				));
				if ($update_stocktake['status'] != 1) {
					$data['errors'] = $update_stocktake['errors'];
				}
			}
		}
		if (!empty($inputdata['repeat'])) {
			$repeat = 0;
		} else {
			$repeat = 1;
		}
		if (empty($data['errors']) && !empty($repeat) && (($inputdata['location_type'] == 1 && $inputdata['location_id'] == ONLINE_STORE_ID) || ($inputdata['location_type'] == 2 && $inputdata['location_id'] == KINGIT_DISTRIBUTION_ID))) {
			if ($inputdata['location_type'] == 1 && $inputdata['location_id'] == ONLINE_STORE_ID) {
				$loc_type = 2;
				$loc_id = KINGIT_DISTRIBUTION_ID;
			} else {
				$loc_type = 1;
				$loc_id = ONLINE_STORE_ID;
			}
			$up_result = $this->update_stock_on_hand_while_creating_invoice_line_item(array(
				"product_id" => $inputdata['product_id'],
				"quantity" => $inputdata['quantity'],
				"location_type" => $loc_type,
				"location_id" => $loc_id,
				"action_type" => $inputdata['action_type'],
				"event_type" => $inputdata['event_type'],
				"type_id" => $inputdata['type_id'],
				"qty_before_update" => $inputdata['qty_before_update'],
				"qty_after_update" => $inputdata['qty_after_update'],
				"user_id" => $inputdata['user_id'],
				"home_store_type" => $inputdata['home_store_type'],
				"home_store_id" => $inputdata['home_store_id'],
				"stock_cost_price_valuation" => (!empty($inputdata['stock_cost_price_valuation'])) ? $inputdata['stock_cost_price_valuation'] : [],
				"sell_price" => (!empty($inputdata['sell_price'])) ? $inputdata['sell_price'] : '',
				"repeat" => $repeat
			));
			$data['errors'] = $up_result['errors'];
			if (empty($data['errors']) && !empty($inputdata['stock_cost_price_valuation'])){
				foreach($inputdata['stock_cost_price_valuation'] as $cost_price=>$quantity){
					if($inputdata['action_type']==1){
						/* Increment */
						$soh_before_update = 0;
						$soh_after_update = $quantity;
					}else{
						/* Decrement */
						$soh_before_update = $quantity;
						$soh_after_update = 0;
					}
					$update_stocktake = $this->UpdateInventoryCostPriceValuation(array(
						"product_id" => $inputdata['product_id'],
						"location_type" => $loc_type,
						"location_id" => $loc_id,
						"soh_before_update" => $soh_before_update,
						"soh_after_update" => $soh_after_update,
						"cost_price" => $cost_price
					));
					if ($update_stocktake['status'] != 1) {
						$data['errors'] = $update_stocktake['errors'];
					} else {
						$inputdata['stock_cost_price_valuation'] = $update_stocktake['stock_cost_price_valuation'];
					}
				}
			}
		}
		if (empty($data['errors'])) {
			$data['status'] = 1;
		}
		return $data;
	}

	function get_all_recurring_invoices_due_for_today($inputdata)
	{
		// return list of all invoices that are due for recurring today
		global $con;
		$data = array();
		$query = "SELECT *, IF( recurring_type = 2, DATE_ADD( max_created_on, INTERVAL recurring_time MONTH ), IF( recurring_type = 3, DATE_ADD( max_created_on, INTERVAL recurring_time WEEK ), IF( recurring_type = 4, DATE_ADD( max_created_on, INTERVAL recurring_time DAY ), '' ) ) ) new_date FROM ( SELECT j.*,MAX(i.created_on) AS max_created_on FROM `ki_invoices_info` i left join `ki_invoices_info` j on j.invoice_id=i.parent_invoice_id and j.delete_flag=0 WHERE j.`is_draft`=0 AND i.`is_draft`=0 AND i.`parent_invoice_id` IS NOT NULL and i.delete_flag=0 GROUP BY i.`parent_invoice_id` UNION SELECT j.*,j.created_on AS max_created_on FROM `ki_invoices_info` j WHERE j.`is_draft`=0 AND j.parent_invoice_id IS NULL and j.delete_flag=0 AND j.invoice_id NOT IN(SELECT DISTINCT parent_invoice_id FROM `ki_invoices_info` where `is_draft`=0 AND parent_invoice_id is not null)) II WHERE `recurring_type` != 1 HAVING DATE(new_date) = '" . date('Y-m-d') . "'";
		$result = $con->query($query,1);
		// echo $con->error;
		while ($row = $result->fetch_assoc()) {
			$data[] = $row;
		}
		return $data;
	}

	function get_ticket_brands_list_for_models($inputdata)
	{
		// params - ticket_type_id
		// returns list of all brands associated to specified ticket type to show in models view page when a ticket type is selected
		global $con;
		$data = array();
		if ($inputdata['type'] == 1) {
			$query = "SELECT DISTINCT TTI.* FROM `ki_brand_ticket_types_mapping_info` BTMI INNER JOIN `ki_ticket_types_info` TTI ON BTMI.`ticket_type_id`=TTI.`ticket_type_id` AND TTI.`is_enabled`=1 AND TTI.`delete_flag`=0 WHERE BTMI.`delete_flag`=0 ORDER BY TTI.`ticket_type_name`";
		} else {
			$query = "SELECT DISTINCT BI.* FROM `ki_brand_ticket_types_mapping_info` BTMI INNER JOIN `ki_brands_info` BI ON BTMI.`brand_id`=BI.`brand_id` AND BI.`is_enabled`=1 AND BI.`delete_flag`=0 WHERE BTMI.`ticket_type_id`='" . safe_str($inputdata['ticket_type_id']) . "' AND BTMI.`delete_flag`=0 ORDER BY BI.`brand_name`";
		}
		$result = $con->query($query);
		// echo $con->error;
		while ($row = $result->fetch_assoc()) {
			$data[] = $row;
		}
		return $data;
	}

	function get_non_invoiced_certificate_of_work_list($inputdata)
	{
		// params - job_id
		// returns list of all certificate of work for job that are not converted to invoice
		global $con;
		$data = array();
		$query = "SELECT * FROM `ki_job_certificate_of_work_info` WHERE `job_id`='" . safe_str($inputdata['job_id']) . "' AND `delete_flag`=0 AND `certificate_of_work_id` NOT IN (SELECT DISTINCT `certificate_of_work_id` FROM `ki_invoices_info` WHERE `job_id`='" . safe_str($inputdata['job_id']) . "' AND `certificate_of_work_id` IS NOT NULL AND `delete_flag`=0)";
		$result = $con->query($query);
		// echo $con->error;
		while ($row = $result->fetch_assoc()) {
			$data[] = $row;
		}
		return $data;
	}

	function check_whether_till_is_open($inputdata)
	{
		// params - location_type, location_id
		// returns a row if till is open for specified location_type, location_id
		global $con;
		$query = "SELECT 
					* 
				FROM 
					`ki_tils_info` 
				WHERE 
					`created_on`=(
						SELECT 
							MAX(`created_on`) 
						FROM 
							`ki_tils_info` 
						WHERE 
							`location_type`='" . safe_str($inputdata['location_type']) . "' AND `location_id`='" . safe_str($inputdata['location_id']) . "' 
						GROUP BY 
							`location_type`,`location_id`
					)";
		$result = $con->query($query);
		echo $con->error;
		$row = $result->fetch_assoc();
		return $row;
	}

	function get_invoice_items_to_create_recurring_invoice($inputdata)
	{
		// params - invoice_id
		// returns list of all invoice line items for which a new one is to be created.
		global $con;
		$data = array();
		$query = "SELECT ILI.*, PI.`product_type` FROM `ki_invoice_line_items_info` ILI LEFT JOIN `ki_products_info` PI ON ILI.`product_id`=PI.`product_id` AND PI.`delete_flag`=0 WHERE ILI.`invoice_id`='" . safe_str($inputdata['invoice_id']) . "' AND ILI.`delete_flag`=0 ORDER BY ILI.`product_name`";
		$result = $con->query($query);
		// echo $con->error;
		while ($row = $result->fetch_assoc()) {
			$data[] = $row;
		}
		return $data;
	}

	function get_estimate_pagging_list($inputdata)
	{
		// params - page_no, row_size, sort_on, sort_type, status, search, is_admin_tab, location_type, location_id
		// returns pagging information of estimates for listing
		global $con;
		$data = array(
			"total_records" => 0,
			"total_pages" => 0,
			"pagging_list" => array()
		);
		$page_no = safe_str($inputdata['page_no']);
		$row_size = safe_str($inputdata['row_size']);
		$sort_on = safe_str($inputdata['sort_on']);
		$sort_type = safe_str($inputdata['sort_type']);
		$status_query = ''; $estimateArr=array();
		if (!empty($inputdata['status'])) {
			$status_query = " AND EI.`status`='" . safe_str($inputdata['status']) . "' ";
		}
		$location = '';
		if ($inputdata['is_admin_tab'] == 0) {
			$location = " AND EI.`home_store_type`='" . safe_str($inputdata['location_type']) . "' AND EI.`home_store_id`='" . safe_str($inputdata['location_id']) . "' ";
		}
		$query = '';
		if (!empty($inputdata['search'])) {
			$query .= " AND (CONCAT('#',EI.`estimate_number`) LIKE '%" . safe_str($inputdata['search']) . "%' OR CONCAT(COALESCE(CI.`first_name`,''),' ', COALESCE(CI.`last_name`,'')) LIKE '%" . safe_str($inputdata['search']) . "%' OR REPLACE(REPLACE(CI.`phone`, '-', ''), ' ', '') LIKE '%" . safe_str($inputdata['search']) . "%' OR CONCAT(COALESCE(UI.`first_name`,''),' ', COALESCE(UI.`last_name`,'')) LIKE '%" . safe_str($inputdata['search']) . "%') ";
		}
		if (!empty($inputdata['customer_id'])) {
			$query .= " AND EI.`customer_id`='" . safe_str($inputdata['customer_id']) . "' ";
		}
		$pcount_qry = "SELECT 
							COUNT(*) AS total_count
						FROM 
							`ki_estimates_info` EI 
						LEFT JOIN `ki_customers_info` CI ON 
							EI.`customer_id`=CI.`customer_id` AND CI.`delete_flag`=0 
						LEFT JOIN `ki_jobs_info` JI ON 
							EI.`job_id`=JI.`job_id` AND JI.`delete_flag`=0 
						INNER JOIN `ki_users_info` UI ON 
							EI.`user_id`=UI.`user_id` AND UI.`delete_flag`=0 
						WHERE 
							EI.`delete_flag`=0 " . $location . $status_query . $query;
		$pcount_result = $con->query($pcount_qry);
		$con->error;
		$pcount_row = $pcount_result->fetch_assoc();
		$total_records = $pcount_row["total_count"];
		@$total_pages = ceil($total_records / $row_size);
		if ($total_pages == 0) {
			$total_pages = 1;
		}
		if ($page_no > $total_pages) {
			$page_no = $total_pages;
		}
		$limit_from = ($row_size * $page_no) - $row_size;

		$pagg_qry = "SELECT 
						EI.`estimate_id`,EI.`status`, EI.`estimate_number`, EI.`total_amount`, CONCAT(COALESCE(CI.`first_name`,''),' ', COALESCE(CI.`last_name`,'')) AS customer_name, CONCAT(COALESCE(UI.`first_name`,''),' ', COALESCE(UI.`last_name`,'')) AS created_by, 
						CASE EI.status
							WHEN 1 THEN 'Published'
							WHEN 2 THEN 'Approved'
							WHEN 3 THEN 'Declined'
						END AS status_name, EI.`created_on`, JI.`status` AS job_status 
					FROM 
						`ki_estimates_info` EI 
					LEFT JOIN `ki_customers_info` CI ON 
						EI.`customer_id`=CI.`customer_id` AND CI.`delete_flag`=0 
					LEFT JOIN `ki_jobs_info` JI ON 
						EI.`job_id`=JI.`job_id` AND JI.`delete_flag`=0 
					INNER JOIN `ki_users_info` UI ON 
						EI.`user_id`=UI.`user_id` AND UI.`delete_flag`=0 
					WHERE 
						EI.delete_flag=0 " ;
						
		if(!empty($inputdata['search_all'])) {
			$qqry = $con->query($pagg_qry . $status_query . $query . " ORDER BY " . safe_str($sort_on) . " " . safe_str($sort_type));
			if($qqry->num_rows) {
				$ii = 0;
				while ($row1 = $qqry->fetch_assoc()) {
					$estimateArr[$ii] = $row1;
					$ii++;
				}
			}
		}
						
		$pagg_qry .= $location . $status_query . $query . " 
					ORDER BY 
						" . safe_str($sort_on) . " " . safe_str($sort_type) . " 
					LIMIT 
						" . $limit_from . ", " . $row_size;
						
		// echo htmlspecialchars($pagg_qry);
		$pagg_result = $con->query($pagg_qry);
		echo $con->error;
		$pagg_count = $pagg_result->num_rows;
		$pagging_list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}
		$data["total_records"] = $total_records;
		$data["total_pages"] = $total_pages;
		$data["pagging_list"] = $pagging_list;
		$data["estimateArr"] = $estimateArr;
		
		return $data;
	}

	function get_estimate_details($inputdata)
	{
		// params - estimate_id
		// returns the details of estimate to show on estimate view page
		global $con;
		$query = "SELECT COALESCE(SI.`store_name`, DBI.`distribution_name`, PI.`production_name`) AS location_name, COALESCE(SI.`phone_number`,DBI.`phone_number`,PI.`phone_number`) AS location_phone_number, COALESCE(SI.`email`,DBI.`email`,PI.`email`) AS location_email, COALESCE(SI.`address`,DBI.`address`,PI.`address`) AS location_address, COALESCE(SI.`suburb`,DBI.`suburb`,PI.`suburb`) AS location_suburb, COALESCE(SI.`postcode`,DBI.`postcode`,PI.`postcode`) AS location_postcode, COALESCE(SI.`state`,DBI.`state`,PI.`state`) AS location_state, COALESCE(SI.`directions`,DBI.`directions`,PI.`directions`) AS location_directions, COALESCE(SI.`country`,DBI.`country`,PI.`country`) AS location_country, COALESCE(SI.`ABN`, DBI.`ABN`, PI.`ABN`) AS location_ABN, COALESCE(SI.`BSB`, DBI.`BSB`, PI.`BSB`) AS location_BSB, COALESCE(SI.`account_number`, DBI.`account_number`, PI.`account_number`) AS location_account_number, CONCAT(COALESCE(UI.`first_name`,''),' ', COALESCE(UI.`last_name`,'')) AS created_by_user, UI.`first_name` AS user_first_name, UI.`email` AS user_email, CONCAT(COALESCE(CI.`first_name`,''),' ', COALESCE(CI.`last_name`,'')) AS customer_name, CI.`first_name` AS cust_first_name, CI.`phone` as cust_phone_no, CI.`email` as cust_email, CI.`is_loyalty_rewards_registered`, CI.`credit_terms`, CI.`is_unsubscribed_to_marketing`, COALESCE(RSI.`source_name`,'') AS referral_source_name, CI.`referral_source_id`, CI.`suburb_town`, CI.`delete_flag` AS cust_delete_flag, JI.`job_number`, JI.`status` AS job_status, EI.*,
		CASE EI.`status`
			WHEN 1 THEN 'Published'
			WHEN 2 THEN 'Approved'
			WHEN 3 THEN 'Declined'
		END AS status_name FROM `ki_estimates_info` EI LEFT JOIN `ki_users_info` UI ON EI.`user_id`=UI.`user_id` AND UI.`delete_flag`=0 LEFT JOIN `ki_jobs_info` JI ON EI.`job_id`=JI.`job_id` AND JI.`delete_flag`=0 LEFT JOIN `ki_customers_info` CI ON EI.`customer_id`=CI.`customer_id` LEFT JOIN `ki_referral_sources_info` RSI ON CI.`referral_source_id`=RSI.`source_id` AND RSI.`delete_flag`=0 LEFT JOIN `ki_stores_info` SI ON EI.`home_store_type` = 1 AND EI.`home_store_id`=SI.`store_id` AND SI.`delete_flag`=0 LEFT JOIN `ki_distribution_branches_info` DBI ON EI.`home_store_type` = 2 AND EI.`home_store_id` = DBI.`distribution_branch_id` AND DBI.`delete_flag`=0 LEFT JOIN `ki_production_info` PI ON EI.`home_store_type` = 3 AND EI.`home_store_id` = PI.`production_id` AND PI.`delete_flag`=0 WHERE EI.`estimate_id`='" . safe_str($inputdata['estimate_id']) . "' AND EI.`delete_flag`=0";
		$result = $con->query($query);
		echo $con->error;
		$row = $result->fetch_assoc();
		if (!empty($row)) {
			$address = [$row['location_address'], $row['location_directions'], $row['location_suburb'], $row['location_state'], $row['location_country'], $row['location_postcode']];
			$row['concatenated_address'] = implode(", ", array_filter($address));
		}
		// echo "<pre>";print_r($row);echo "</pre>";
		return $row;
	}

	function get_estimate_line_items($inputdata)
	{
		// params - estimate_id, location_type, location_id
		// returns the list of estimate line items to show on estimate view page
		global $con;
		$data = array();
		$query = "SELECT 
						DISTINCT EI.`default_tax`, COALESCE(PCI.`core_range`,0) AS core_range, COALESCE(PQI.`stock_on_hand`,0) AS stock_on_hand,  COALESCE(PPI.`retail_price`,0) AS retail_price, COALESCE(MLI.`tax_value`,MLI1.`tax_value`) AS retail_tax, COALESCE(PPI.`retail_margin`,0) AS retail_margin, COALESCE(PPI.`distribution_price`,0) AS distribution_price, COALESCE(PPI.`distribution_margin`,0) AS distribution_margin, COALESCE(PPI.`cost_price`,0) AS cost_price, COALESCE(PPI.`spiff`,0) AS spiff, ELI.`cost_price` as manual_cost_price, ELI.*, 
						CASE 
							WHEN ELI.`product_id` IS NULL OR ELI.`product_id`='' OR ELI.`product_id`=0 THEN ELI.`category_id` 
							ELSE PI.`category_id` 
						END AS `category_id` 
					FROM 
						`ki_estimate_line_items_info` ELI 
					LEFT JOIN `ki_estimates_info` EI ON 
						ELI.`estimate_id`=EI.`estimate_id` AND EI.`delete_flag`=0 
					LEFT JOIN `ki_product_prices_info` PPI ON 
						ELI.`product_id`=PPI.`product_id` AND PPI.`delete_flag`=0 
					LEFT JOIN `ki_meta_taxes_info` MLI ON 
						PPI.`retail_tax`=MLI.`tax_id` AND MLI.`delete_flag`=0 
					LEFT JOIN `ki_meta_taxes_info` MLI1 ON 
						MLI1.`is_default`=1 AND MLI1.`delete_flag`=0 
					LEFT JOIN `ki_products_info` PI ON 
						PI.`product_id`=ELI.`product_id` AND PI.`delete_flag`=0 
					LEFT JOIN `ki_product_consumption_info` PCI ON 
						PCI.`product_id`=PI.`product_id` AND PCI.`location_type`='" . safe_str($inputdata['location_type']) . "' AND PCI.`location_id`='" . safe_str($inputdata['location_id']) . "' AND PCI.`delete_flag`=0 
					LEFT JOIN `ki_product_quantites_info` PQI ON 
						PQI.`product_id`=PI.`product_id` AND PQI.`location_type`='" . safe_str($inputdata['location_type']) . "' AND PQI.`location_id`='" . safe_str($inputdata['location_id']) . "' AND PQI.`delete_flag`=0 
					WHERE 
						ELI.`estimate_id` = '" . safe_str($inputdata['estimate_id']) . "' AND ELI.`delete_flag` = 0 
					ORDER BY 
						ELI.`product_name`";
		// $query = "SELECT DISTINCT COALESCE(PCI.`core_range`,0) AS core_range, COALESCE(PQI.`stock_on_hand`,0) AS stock_on_hand,  COALESCE(PPI.`retail_price`,0) AS retail_price, COALESCE(PPI.`retail_margin`,0) AS retail_margin, COALESCE(PPI.`distribution_price`,0) AS distribution_price, COALESCE(PPI.`distribution_margin`,0) AS distribution_margin, ELI.`cost_price` as manual_cost_price, ELI.*, COALESCE(PPI.`cost_price`,0) AS cost_price FROM `ki_estimate_line_items_info` ELI LEFT JOIN `ki_product_prices_info` PPI ON ELI.`product_id`=PPI.`product_id` AND PPI.`delete_flag`=0 LEFT JOIN `ki_products_info` PI ON PI.`product_id`=ELI.`product_id` AND PI.`delete_flag`=0 LEFT JOIN `ki_product_consumption_info` PCI ON PCI.`product_id`=PI.`product_id` AND PCI.`location_type`='".$inputdata['location_type']."' AND PCI.`location_id`='".$inputdata['location_id']."' AND PCI.`delete_flag`=0 LEFT JOIN `ki_product_quantites_info` PQI ON PQI.`product_id`=PI.`product_id` AND PQI.`location_type`='".$inputdata['location_type']."' AND PQI.`location_id`='".$inputdata['location_id']."' AND PQI.`delete_flag`=0 WHERE ELI.`estimate_id` = '".$inputdata['estimate_id']."' AND ELI.`delete_flag` = 0 ORDER BY ELI.`product_name`";
		$result = $con->query($query);
		echo $con->error;
		while ($row = $result->fetch_assoc()) {
			$data[] = $row;
		}
		// echo "<pre>";print_r($data);echo "</pre>";
		return $data;
	}

	function validate_tax($inputdata)
	{
		// params - value (tax)
		// returns a row if tax is found in DB
		global $con;
		if (empty($inputdata['value'])) {
			$query = "SELECT * FROM `ki_meta_taxes_info` WHERE `is_default`=1 AND `delete_flag`=0";
		} else {
			$query = "SELECT * FROM `ki_meta_taxes_info` WHERE CONCAT(COALESCE(`tax_name`,''),' ', COALESCE(`tax_value`,''))='" . safe_str($inputdata['value']) . "' AND `is_enabled`=1 AND `delete_flag`=0";
		}
		$result = $con->query($query);
		$row = $result->fetch_assoc();
		return $row;
	}

	function get_paid_invoice_return_to_customer($inputdata)
	{
		// params - array of decoded certificate_of_work_id
		// returns list of fully paid invoices to show link in off-board on click of return to customer
		global $con;
		$list = array();
		$query = "SELECT 
					AA.* 
				FROM 
					(
						SELECT 
							II.`invoice_id`, II.`invoice_number`, (COALESCE(II.`used_deposit`,0)+COALESCE(II.`used_store_credit`,0)+COALESCE(II.`used_loyalty_credits`,0)+COALESCE(AB.amount_paid,0)) as amount_paid, II.`total_including_GST` 
						FROM 
							`ki_invoices_info` II 
						LEFT JOIN 
							(
								SELECT 
									COALESCE(SUM(`amount` - `change`), 0) AS amount_paid, `invoice_id` 
								FROM 
									`ki_invoice_payment_info` 
								WHERE 
									`undo_datetime` IS NULL 
								GROUP BY 
									`invoice_id`
							) AB ON 
							AB.`invoice_id` = II.`invoice_id` 
						WHERE job_id='" . $inputdata['job_id'] . "' AND II.`delete_flag` = 0
					) AA 
				WHERE 
					(total_including_GST>=0 AND AA.amount_paid>=total_including_GST) OR (total_including_GST<0 AND AA.amount_paid<=total_including_GST)";
		$result = $con->query($query);
		echo $con->error;
		while ($row = $result->fetch_assoc()) {
			$list[] = $row;
		}
		return $list;
	}

	function get_product_details_add_new_item_invoices($inputdata)
	{
		// params - product_id
		// returns required details of product when new item is added in invoice
		global $con;
		$query = "SELECT *, COALESCE(MTI1.`tax_value`,MTI2.`tax_value`,0.00) AS line_retail_tax FROM `ki_products_info` AB LEFT JOIN `ki_categories_info` CD ON AB.`category_id`=CD.`category_id` AND CD.`delete_flag`=0 LEFT JOIN `ki_product_prices_info` EF ON AB.`product_id`=EF.`product_id` AND EF.`delete_flag`=0 LEFT JOIN `ki_meta_taxes_info` MTI1 ON EF.`retail_tax`=MTI1.`tax_id` AND MTI1.`is_enabled`=1 AND MTI1.`delete_flag`=0 LEFT JOIN `ki_meta_taxes_info` MTI2 ON MTI2.`is_default`=1 AND MTI2.`delete_flag`=0 WHERE AB.`product_id`=" . safe_str($inputdata['product_id']);
		$result = $con->query($query);
		echo $con->error;
		return $result->fetch_assoc();
	}

	function get_product_details_add_new_item_estimates($inputdata)
	{
		// params - product_id, location_type, location_id, default_tax
		// returns required details of product when new item is added in estimate
		global $con;
		$data = array();
		if ($inputdata['location_type'] == 1) {
			$rate = "retail_price";
			// $tax = 0;
			$tax = "(" . ($inputdata['default_tax'] / ($inputdata['default_tax'] + 100)) . "*`retail_price`)";
			$cost_price = "distribution_price";
			$margin = "retail_margin";
		} else {
			$rate = "distribution_price";
			$tax = "(" . ($inputdata['default_tax'] / 100) . "*`" . $rate . "`)";
			$cost_price = "cost_price";
			$margin = "distribution_margin";
		}
		$line_item = array();
		$query = "SELECT 
					1 AS quantity, PI.*, PCI.`location_type`, PCI.`location_id`, PCI.`core_range`, PQI.`stock_on_hand`, PQI.`override_desired_stock_level`, PQI.`desired_stock_level`, PPI.`" . $rate . "` AS rate, " . $tax . " AS tax, " . $margin . " AS margin," . $cost_price . " AS cost_price, PPI.`retail_price` AS line_retail_price, PPI.`distribution_price` AS line_distribution_price, PPI.`cost_price` AS line_cost_price, PPI.`spiff` AS line_spiff, PPI.`retail_margin`, PPI.`distribution_margin`, COALESCE(MTI1.`tax_value`, " . $inputdata['default_tax'] . ") AS retail_tax_val, MTI2.`tax_value` AS dist_tax_val 
				FROM 
					`ki_products_info` PI 
				LEFT JOIN `ki_product_consumption_info` PCI ON 
					PI.`product_id`=PCI.`product_id` AND PCI.`location_type`='" . $inputdata['location_type'] . "' AND PCI.`location_id`='" . $inputdata['location_id'] . "' AND PCI.`delete_flag`=0 
				LEFT JOIN `ki_product_quantites_info` PQI ON 
					PI.`product_id`=PQI.`product_id` AND PQI.`location_type`='" . $inputdata['location_type'] . "' AND PQI.`location_id`='" . $inputdata['location_id'] . "' AND PQI.`delete_flag`=0 
				LEFT JOIN `ki_product_prices_info` PPI ON 
					PI.`product_id`=PPI.`product_id` AND PPI.`delete_flag`=0 
				LEFT JOIN `ki_meta_taxes_info` MTI1 ON 
					PPI.`retail_tax`=MTI1.`tax_id` AND MTI1.`is_enabled`=1 AND MTI1.`delete_flag`=0 
				LEFT JOIN `ki_meta_taxes_info` MTI2 ON 
					PPI.`distribution_tax`=MTI2.`tax_id` AND MTI2.`is_enabled`=1 AND MTI2.`delete_flag`=0 
				WHERE 
					PI.`product_id`='" . $inputdata['product_id'] . "' AND PI.`delete_flag`=0 
				ORDER BY 
					PI.`product_name`";
		$pcount_result = $con->query($query);
		if ($con->query($query)) {
			return $pcount_result->fetch_assoc();
		}
		return $data;
	}

	function UpdateProductValuationStock($inputdata)
	{
		/* 
		input params - 
			"product_id" => $product_id,
			"location_type" => $location_type,
			"location_id" => $location_id,
			"stock_on_hand" => $stock_on_hand
		updates previous entry and create a new one if stock on hand is changed as per location
		output - 
			$data = array(
				"status" => 0,
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"errors" => array()
		);
		$no_change = 0;
		if (isset($inputdata['product_id'])) {
			$product_details = $this->get_product_details(array(
				"product_id" => $inputdata['product_id']
			));
			if ($product_details['status'] == 1 && !empty($product_details['details'])) {
				if (isset($inputdata['stock_on_hand'])) {
					$find = "SELECT * FROM `ki_products_inventory_valuation_info` WHERE `product_id`='" . safe_str($inputdata['product_id']) . "' AND `location_type`='" . safe_str($inputdata['location_type']) . "' AND `location_id`='" . safe_str($inputdata['location_id']) . "' AND `delete_flag`=0 ORDER BY `created_on` DESC";
					$result = $con->query($find);
					if ($result && $result->num_rows > 0) {
						$row = $result->fetch_assoc();
						if ($inputdata['stock_on_hand'] != $row['stock_on_hand']) {
							$up_qry = "UPDATE `ki_products_inventory_valuation_info` SET `modified_on`='" . date("Y-m-d H:i:s") . "' WHERE `inventory_valuation_id`='" . safe_str($row['inventory_valuation_id']) . "'";
							$up_result = $con->query($up_qry);
							if (!$up_result) {
								$data['errors'][] = $con->error;
							}
						} else {
							$no_change = 1;
						}
					}
					if (empty($data['errors']) && $no_change == 0) {
						$in_qry = "INSERT INTO `ki_products_inventory_valuation_info` (`product_id`, `location_type`, `location_id`, `stock_on_hand`, `created_on`) VALUES ('" . safe_str($inputdata['product_id']) . "', '" . safe_str($inputdata['location_type']) . "', '" . safe_str($inputdata['location_id']) . "', '" . safe_str($inputdata['stock_on_hand']) . "', '" . date("Y-m-d H:i:s") . "')";
						$in_result = $con->query($in_qry);
						if (!$in_result) {
							$data['errors'][] = $con->error;
						}
					}
					if (empty($data['errors'])) {
						if (KEEP_SOH_LOG == 1 && $inputdata['soh_before_update'] != $inputdata['soh_after_update']) {
							$UpdateInventoryStockValuation = $this->UpdateInventoryStockValuation(array(
								"product_id" => $inputdata["product_id"],
								"location_type" => $inputdata["location_type"],
								"location_id" => $inputdata["location_id"],
								"user_id" => $inputdata["user_id"],
								"home_store_type" => $inputdata["home_store_type"],
								"home_store_id" => $inputdata["home_store_id"],
								"event_type" => $inputdata['event_type'],
								"type_id" => (!empty($inputdata['type_id'])) ? $inputdata['type_id'] : 0,
								"soh_before_update" => $inputdata['soh_before_update'],
								"soh_after_update" => $inputdata["soh_after_update"],
								"qty_before_update" => (!empty($inputdata['qty_before_update'])) ? $inputdata['qty_before_update'] : 0,
								"qty_after_update" => (!empty($inputdata['qty_after_update'])) ? $inputdata['qty_after_update'] : 0,
								"stock_cost_price_valuation" => (!empty($inputdata['stock_cost_price_valuation'])) ? $inputdata['stock_cost_price_valuation'] : [],
								"sell_price" => (!empty($inputdata['sell_price'])) ? $inputdata['sell_price'] : ''
							));
							$data['errors'] = $UpdateInventoryStockValuation['errors'];
						}
					}
				} else {
					$data['errors'][] = "Failed to get stock on hand.";
				}
			} else {
				$data['errors'][] = "Failed to get product details.";
			}
		} else {
			$data['errors'][] = "Failed to get product details.";
		}
		if (empty($data['errors'])) {
			$data['status'] = 1;
		}
		return $data;
	}

	function UpdateInventoryStockValuation($inputdata)
	{
		/* 
		input params - 
			product_id, location_type, location_id, user_id, home_store_type, home_store_id, action_type, soh_before_update, soh_after_update
			type_id, qty_before_update, qty_after_update - optional
		updates previous entry and create a new one if stock on hand is changed as per location
		output - 
			$data = array(
				"status" => 0,
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"errors" => array()
		);
		$no_change = 0;
		if (isset($inputdata['product_id'])) {
			$product_details = $this->get_product_details(array(
				"product_id" => $inputdata['product_id']
			));
			if ($product_details['status'] == 1 && !empty($product_details['details'])) {
				if (empty($inputdata['sell_price'])) {
					$sql = "SELECT * FROM `ki_product_prices_info` WHERE `product_id`='" . safe_str($inputdata['product_id']) . "' AND `delete_flag`=0 ORDER BY `created_on` DESC";
					$res = $con->query($sql);
					if ($res) {
						$row = $res->fetch_assoc();
						if (!empty($row)) {
							$sell_price = $row['retail_price'];
						} else {
							$sell_price = 0;
						}
					}
				} else {
					$sell_price = $inputdata['sell_price'];
				}
				$find = "SELECT * FROM `ki_inventory_stock_valuation_info` WHERE `product_id`='" . safe_str($inputdata['product_id']) . "' AND `location_type`='" . safe_str($inputdata['location_type']) . "' AND `location_id`='" . safe_str($inputdata['location_id']) . "' AND `delete_flag`=0 ORDER BY `created_on` DESC";
				$result = $con->query($find);
				if ($result && $result->num_rows > 0) {
					$row = $result->fetch_assoc();
					if ($inputdata['soh_after_update'] != $row['soh_after_update']) {
						$up_qry = "UPDATE `ki_inventory_stock_valuation_info` SET `modified_on`='" . date("Y-m-d H:i:s") . "' WHERE `stock_valuation_id`='" . safe_str($row['stock_valuation_id']) . "'";
						$up_result = $con->query($up_qry);
						if (!$up_result) {
							$data['errors'][] = $con->error;
						}
					} else {
						$no_change = 1;
					}
				}
				if (empty($data['errors']) && $no_change == 0) {
					$in_qry = "INSERT INTO `ki_inventory_stock_valuation_info` (`product_id`, `location_type`, `location_id`, `user_id`, `home_store_type`, `home_store_id`, `action_type`, `type_id`, `soh_before_update`, `soh_after_update`, `qty_before_update`, `qty_after_update`, `sell_price`, `created_on`) VALUES ('" . safe_str($inputdata['product_id']) . "', '" . safe_str($inputdata['location_type']) . "', '" . safe_str($inputdata['location_id']) . "', '" . safe_str($inputdata['user_id']) . "', '" . safe_str($inputdata['home_store_type']) . "', '" . safe_str($inputdata['home_store_id']) . "', '" . safe_str($inputdata['event_type']) . "', '" . safe_str($inputdata['type_id']) . "', '" . safe_str($inputdata['soh_before_update']) . "', '" . safe_str($inputdata['soh_after_update']) . "', '" . safe_str($inputdata['qty_before_update']) . "', '" . safe_str($inputdata['qty_after_update']) . "', '" . safe_str($sell_price) . "', '" . date("Y-m-d H:i:s") . "')";
					$in_result = $con->query($in_qry);
					if ($in_result) {
						$stock_valuation_id = $con->insert_id;
					} else {
						$data['errors'][] = $con->error;
					}
					if (empty($data['errors']) && !empty($inputdata['stock_cost_price_valuation']) && !empty($stock_valuation_id)) {
						foreach ($inputdata['stock_cost_price_valuation'] as $cost_price => $quantity) {
							$in_qry = "INSERT INTO `ki_inventory_stock_cost_price_valuation_info` (`stock_valuation_id`, `quantity`, `cost_price`, `created_on`) VALUES ('" . safe_str($stock_valuation_id) . "', '" . safe_str($quantity) . "', '" . safe_str($cost_price) . "', '" . date("Y-m-d H:i:s") . "')";
							$in_result = $con->query($in_qry);
							if (!$in_result) {
								$data['errors'][] = $con->error;
							}
						}
					}
				}
			} else {
				$data['errors'][] = "Failed to get product details.";
			}
		} else {
			$data['errors'][] = "Failed to get product details.";
		}
		if (empty($data['errors'])) {
			$data['status'] = 1;
		}
		return $data;
	}

	function get_invoice_80mm_receipt_details($inputdata)
	{
		// params - invoice_id
		// returns required details of invoice to show in 80mm tax receipt
		global $con;
		$data = array();
		$query = "SELECT 
			COALESCE(SI.`store_name`,DBI.`distribution_name`,PI.`production_name`) AS location_name, 
			COALESCE(SI.`phone_number`,DBI.`phone_number`,PI.`phone_number`) AS location_phone_number, 
			COALESCE(SI.`email`,DBI.`email`,PI.`email`) AS location_email, 
			COALESCE(SI.`address`, '') AS location_address, 
			COALESCE(SI.`suburb`, '') AS location_suburb, 
			COALESCE(SI.`postcode`, '') AS location_postcode, 
			COALESCE(SI.`state`, '') AS location_state, 
			COALESCE(SI.`directions`, '') AS location_directions, 
			COALESCE(SI.`ABN`, DBI.`ABN`, PI.`ABN`) AS location_ABN, 
			COALESCE(SI.`BSB`, DBI.`BSB`, PI.`BSB`) AS location_BSB, 
			COALESCE(SI.`account_number`, 
			DBI.`account_number`, PI.`account_number`) AS location_account_number, 
			CASE 
				WHEN II.`home_store_type`=1 AND UI1.`first_name`!='' AND UI1.`first_name` IS NOT NULL THEN CONCAT(COALESCE(UI1.`first_name`, ''),' ',COALESCE(UI1.`last_name`, '')) 
				WHEN II.`home_store_type`=1 AND (UI1.`first_name`='' OR UI1.`first_name` IS NULL) THEN CONCAT(COALESCE(UID.`first_name`, ''),' ',COALESCE(UID.`last_name`, '')) 
				WHEN II.`home_store_type`=2 THEN CONCAT(COALESCE(UI2.`first_name`, ''),' ',COALESCE(UI2.`last_name`, '')) 
				WHEN II.`home_store_type`=3 THEN CONCAT(COALESCE(UI3.`first_name`, ''),' ',COALESCE(UI3.`last_name`, '')) 
			END AS location_manager, 
			CI.`customer_id`, CI.`business_name` AS customer_business_name, CONCAT(COALESCE(CI.`first_name`, ''),' ',COALESCE(CI.`last_name`, '')) AS customer_name, CI.`phone` AS customer_phone_number, CI.`address` AS customer_address, CI.`suburb_town` AS customer_suburb, CI.`state` AS customer_state, CI.`first_name` AS cust_first_name, CI.`email` as cust_email, CI.`is_loyalty_rewards_registered`, CI.`credit_terms`, CI.`is_unsubscribed_to_marketing`, 
			SI1.`store_name` AS store_name, SI1.`phone_number` AS store_phone_number, COALESCE(SI1.`address`, '') AS store_address, COALESCE(SI1.`suburb`, '') AS store_suburb, COALESCE(SI1.`postcode`, '') AS store_postcode, COALESCE(SI1.`state`, '') AS store_state, 
			COALESCE(CLPI.positive_pts - COALESCE(CLNI.negative_pts,0),0) AS total_loyalty_points, 
			CONCAT(COALESCE(UI.`first_name`, ''),' ',COALESCE(UI.`last_name`, '')) AS user_name, 
			II.*, II.`created_on` AS invoice_datetime,CSCI.`nominated_contact_id`,CONCAT(COALESCE(CSCI.`first_name`, ''),' ',COALESCE(CSCI.`last_name`, '')) AS nom_customer_name,CSCI.`mobile` as nom_phone_number,CSCI.`address` as nom_customer_address , CSCI.`state` as nom_customer_suburb , CSCI.`state` as nom_customer_state
		FROM 
			`ki_invoices_info` II 
		LEFT JOIN `ki_stores_info` SI ON 
			II.`home_store_type` = 1 AND II.`home_store_id` = SI.`store_id` AND SI.`delete_flag` = 0 
		LEFT JOIN `ki_users_info` UI1 ON 
			SI.`store_manager_id`=UI1.`user_id` AND UI1.`delete_flag`=0 
		LEFT JOIN `ki_users_info` UID ON 
			UID.`user_id`='" . get_meta_value(27) . "' AND UID.`delete_flag`=0 
		LEFT JOIN `ki_distribution_branches_info` DBI ON 
			II.`home_store_type` = 2 AND II.`home_store_id` = DBI.`distribution_branch_id` AND DBI.`delete_flag` = 0 
		LEFT JOIN `ki_users_info` UI2 ON 
			DBI.`manager_id`=UI2.`user_id` AND UI2.`delete_flag`=0 
		LEFT JOIN `ki_production_info` PI ON 
			II.`home_store_type` = 3 AND II.`home_store_id` = PI.`production_id` AND PI.`delete_flag` = 0 
		LEFT JOIN `ki_users_info` UI3 ON 
			PI.`manager_id`=UI3.`user_id` AND UI3.`delete_flag`=0 
		LEFT JOIN `ki_customers_info` CI ON 
			II.`customer_id` = CI.`customer_id` AND CI.`delete_flag` = 0 
		LEFT JOIN `ki_stores_info` SI1 ON 
			II.`store_id` = SI1.`store_id` AND SI1.`delete_flag` = 0
		LEFT JOIN `ki_customer_nominated_contacts_info` CSCI ON
			II.`customer_id` = CSCI.`customer_id` AND CSCI.`delete_flag` = 0 
		LEFT JOIN
			(
				SELECT 
					COALESCE(SUM(`credit`),0) AS positive_pts, `customer_id` 
				FROM 
					`ki_customer_rewards_info` 
				WHERE 
					`type` = 1 AND `negative_flag` = 0 AND `delete_flag` = 0 
				GROUP BY 
					`customer_id` 
			) AS CLPI ON 
			CLPI.`customer_id` = II.`customer_id` 
		LEFT JOIN 
			(
				SELECT 
					COALESCE(SUM(`credit`),0) AS negative_pts, `customer_id` 
				FROM 
					`ki_customer_rewards_info` 
				WHERE 
					`type` = 1 AND `negative_flag` = 1 AND `delete_flag` = 0 
				GROUP BY 
					`customer_id`
				) AS CLNI ON 
			CLNI.`customer_id` = II.`customer_id` 
		LEFT JOIN `ki_users_info` UI ON 
			II.`user_id` = UI.`user_id` AND UI.`delete_flag` = 0 
		WHERE 
			II.`invoice_id` = '" . safe_str($inputdata['invoice_id']) . "' AND II.`delete_flag` = 0";
		$pcount_result = $con->query($query);
		if ($pcount_result) {
			// echo "<pre>";print_r($pcount_result->fetch_assoc());
			$data = $pcount_result->fetch_assoc();
		}
		return $data;
	}

	function get_invoice_80mm_receipt_terms($inputdata)
	{
		// returns list of receipt and voucher terms
		$data = array(
			"receipt_terms" => array(),
			"voucher_terms" => array()
		);
		$receipt_terms = send_rest(array(
			"table" => "ki_invoice_terms_info",
			"function" => "get_list",
			"key" => "term_type",
			"value" => "1"
		));
		if (!empty($receipt_terms['list'])) {
			$data['receipt_terms'] = $receipt_terms['list'];
		}
		$voucher_terms = send_rest(array(
			"table" => "ki_invoice_terms_info",
			"function" => "get_list",
			"key" => "term_type",
			"value" => "2"
		));
		if (!empty($voucher_terms['list'])) {
			$data['voucher_terms'] = $voucher_terms['list'];
		}
		return $data;
	}

	function get_a4_tax_invoice_line_items($inputdata)
	{
		// params - invoice_id
		// returns required details of invoice line items to show in A4 invoice
		global $con;
		$data = array("list" => array());
		$query = "SELECT COALESCE(ILI.`SKU`,PI.`SKU`) AS item_SKU, ILI.* FROM `ki_invoice_line_items_info` ILI LEFT JOIN `ki_products_info` PI ON ILI.`product_id`=PI.`product_id` AND PI.`delete_flag`=0 WHERE `invoice_id` = '" . safe_str($inputdata['invoice_id']) . "' AND ILI.`delete_flag`=0";
		$result = $con->query($query);
		echo $con->error;
		$list = array();
		while ($row = $result->fetch_assoc()) {
			$list[] = $row;
		}
		$data['list'] = $list;
		return $data;
	}

	function get_estimate_available_deposit($inputdata)
	{
		// params - estimate_id
		// returns available deposit of estimate
		global $con;
		$query = "SELECT COALESCE(`credit`, 0) AS credit, COALESCE(`debit`, 0) AS debit, ( COALESCE(`credit`, 0) - COALESCE(`debit`, 0) ) AS available FROM ( ( SELECT SUM(`deposit`) AS credit FROM `ki_estimate_deposits_info` WHERE `estimate_id` = '" . safe_str($inputdata['estimate_id']) . "' AND `negative_flag` = 0 AND `delete_flag` = 0 ) AA, ( SELECT SUM(`deposit`) AS debit FROM `ki_estimate_deposits_info` WHERE `estimate_id` = '" . safe_str($inputdata['estimate_id']) . "' AND `negative_flag` = 1 AND `delete_flag` = 0 ) BB )";
		$pcount_result = $con->query($query);
		if ($con->query($query)) {
			// echo "<pre>";print_r($pcount_result->fetch_assoc());
			return $pcount_result->fetch_assoc();
		}
		return $data;
	}

	function get_a4_estimate_details($inputdata)
	{
		// params - estimate_id
		// returns required details of estimate to show in A4 print
		global $con;
		$data = array();
		$query = "SELECT COALESCE(SI.`store_name`,DBI.`distribution_name`,PI.`production_name`) AS location_name, COALESCE(SI.`phone_number`,DBI.`phone_number`,PI.`phone_number`) AS location_phone_number, COALESCE(SI.`address`, '') AS location_address, COALESCE(SI.`suburb`, '') AS location_suburb, COALESCE(SI.`postcode`, '') AS location_postcode, COALESCE(SI.`state`, '') AS location_state, COALESCE(SI.`ABN`, DBI.`ABN`, PI.`ABN`) AS location_ABN, COALESCE(SI.`BSB`, DBI.`BSB`, PI.`BSB`) AS location_BSB, COALESCE(SI.`account_number`, DBI.`account_number`, PI.`account_number`) AS location_account_number, CI.`customer_id`, CI.`business_name` AS customer_business_name, CONCAT(COALESCE(CI.`first_name`, ''),' ',COALESCE(CI.`last_name`, '')) AS customer_name, CI.`phone` AS customer_phone_number, CI.`address` AS customer_address, CI.`suburb_town` AS customer_suburb, CI.`state` AS customer_state, COALESCE(CLPI.positive_pts - COALESCE(CLNI.negative_pts,0),0) AS total_loyalty_points, CONCAT(COALESCE(UI.`first_name`, ''),' ',COALESCE(UI.`last_name`, '')) AS user_name, EI.*, EI.`created_on` AS estimate_datetime FROM `ki_estimates_info` EI LEFT JOIN `ki_stores_info` SI ON EI.`home_store_type` = 1 AND EI.`home_store_id` = SI.`store_id` AND SI.`delete_flag` = 0 LEFT JOIN `ki_distribution_branches_info` DBI ON EI.`home_store_type` = 2 AND EI.`home_store_id` = DBI.`distribution_branch_id` AND DBI.`delete_flag` = 0 LEFT JOIN `ki_production_info` PI ON EI.`home_store_type` = 3 AND EI.`home_store_id` = PI.`production_id` AND PI.`delete_flag` = 0 LEFT JOIN `ki_customers_info` CI ON EI.`customer_id` = CI.`customer_id` AND CI.`delete_flag` = 0 LEFT JOIN(SELECT COALESCE(SUM(`credit`),0) AS positive_pts, `customer_id` FROM `ki_customer_rewards_info` WHERE `type` = 1 AND `negative_flag` = 0 AND `delete_flag` = 0 GROUP BY `customer_id`) AS CLPI ON CLPI.`customer_id` = EI.`customer_id` LEFT JOIN (SELECT COALESCE(SUM(`credit`),0) AS negative_pts, `customer_id` FROM `ki_customer_rewards_info` WHERE `type` = 1 AND `negative_flag` = 1 AND `delete_flag` = 0 GROUP BY `customer_id`) AS CLNI ON CLNI.`customer_id` = EI.`customer_id` INNER JOIN `ki_users_info` UI ON EI.`user_id` = UI.`user_id` AND UI.`delete_flag` = 0 WHERE EI.`estimate_id` = '" . safe_str($inputdata['estimate_id']) . "' AND EI.`delete_flag` = 0";
		$pcount_result = $con->query($query);
		if ($con->query($query)) {
			// echo "<pre>";print_r($pcount_result->fetch_assoc());
			return $pcount_result->fetch_assoc();
		}
		return $data;
	}

	function get_a4_estimate_line_items($inputdata)
	{
		// params -  estimate_id
		// returns list of all estimate line items and also SKU of each product from products table.
		global $con;
		$data = array("list" => array());
		$query = "SELECT COALESCE(ELI.`SKU`,PI.`SKU`) AS item_SKU, ELI.* FROM `ki_estimate_line_items_info` ELI LEFT JOIN `ki_products_info` PI ON ELI.`product_id`=PI.`product_id` AND PI.`delete_flag`=0 WHERE `estimate_id` = '" . safe_str($inputdata['estimate_id']) . "' AND ELI.`delete_flag`=0";
		$result = $con->query($query);
		echo $con->error;
		$list = array();
		while ($row = $result->fetch_assoc()) {
			$list[] = $row;
		}
		$data['list'] = $list;
		return $data;
	}

	function get_estimate_deposit_history($inputdata)
	{
		// params - estimate_id
		// returns list of all collected deposit from customer and also checks for end of day balance status to provide option to undo the payment
		global $con;
		$data = array();
		$query = "SELECT 
					DISTINCT EDI.*, til_id, is_closed 
				FROM 
					`ki_estimate_deposits_info` EDI 
				LEFT JOIN 
					( 
						SELECT 
							AA.* 
						FROM 
							`ki_tils_info` AA 
						INNER JOIN 
							( 
								SELECT 
									`location_type`, `location_id`, MAX(`created_on`) AS max_created 
								FROM 
									`ki_tils_info` 
								GROUP BY 
									`location_type`, `location_id` 
							) BB ON 
							AA.`created_on` = BB.max_created AND AA.`location_type` = BB.`location_type` AND AA.`location_id` = BB.`location_id` 
					) 
					TI ON EDI.`location_type` = TI.`location_type` AND EDI.`location_id` = TI.`location_id` AND EDI.`created_on`>=TI.`created_on` 
				WHERE 
					`estimate_id`='" . safe_str($inputdata['estimate_id']) . "' AND `negative_flag` = 0 
				ORDER BY 
					`created_on` DESC";
		// $query = "SELECT * FROM `ki_estimate_deposits_info` WHERE `negative_flag`=0";
		$result = $con->query($query);
		while ($row = $result->fetch_assoc()) {
			$data[] = $row;
		}
		return $data;
	}

	// DEEPSHIKHA code CLOSE

	#####################################################################################

	//Tanvi code
	function get_supplier_all_detail($inputdata)
	{
		global $con;
		$values = '';
		if (isset($inputdata['key'])) {
			$query =  "AB." . safe_str($inputdata['key']) . " = " . safe_str($inputdata['value']) . " and ";
		}
		$pcount_qry = "select * from `ki_product_logistics_info` AB 
        LEFT JOIN `ki_products_info` CD on AB.product_id = CD.product_id and CD.`delete_flag`='0'
        LEFT JOIN `ki_product_quantites_info` EF on AB.product_id = EF.product_id and EF.`delete_flag`='0' and EF.location_type=AB.location_type and EF.location_id=AB.location_id
        LEFT JOIN `ki_product_prices_info` GH on AB.product_id = GH.product_id and GH.`delete_flag`='0'
        where " . $query . "AB.`delete_flag`='0'";
		//return $pcount_qry;
		$pcount_result = $con->query($pcount_qry);
		$i = 0;
		while ($pcount_row = $pcount_result->fetch_assoc()) {
			$values[$i] = $pcount_row;
			$i++;
		}
		return $values;
		//print_r($values);
	}

	function currency_convertor($inputdata)
	{
		$file_content = file_get_contents("http://free.currencyconverterapi.com/api/v5/convert?q=" . safe_str($inputdata['convertor_val']) . "&compact=y");
		$file_content = json_decode($file_content);
		$converted_val = $file_content->$inputdata['convertor_val']->val;
		return $inputdata['value'] * $converted_val;
	}

	function get_max_value($inputdata)
	{
		global $con;
		$table = safe_str($inputdata['table']);
		$where = '';
		$field_output = '';
		if (!empty($inputdata['id'])) {
			$where .= " where " . safe_str($inputdata['key']) . "!='" . safe_str($inputdata['value']) . "'";
		}
		$max_value = $inputdata['max_value'];

		foreach ($max_value as $field_value) {
			$field_output .= "MAX(" . $field_value . ") as " . $field_value . ",";
		}
		$field_output = rtrim($field_output, ",");
		$pcount_qry = "select " . $field_output . " from `" . safe_str($table) . "` " . $where;

		$pcount_result = $con->query($pcount_qry);
		$pcount_row = $pcount_result->fetch_assoc();
		return $pcount_row;
	}

	function common_check_data_existance($inputdata)
	{
		global $con;
		$where = "";
		$where_fields = safe_str($inputdata['where_fields']);

		foreach ($where_fields as $field_key => $field_value) {
			$where .= " and " . safe_str($field_key) . "='" . $field_value . "'";
		}

		// trim extra spaces between words 

		$que = "select * from " . safe_str($inputdata['table']) . " where delete_flag=0 " . $where;
		$pcount_result = $con->query($que);
		$pcount_row = $pcount_result->fetch_assoc();
		return $pcount_row;
	}

	function get_customer_status($inputdata)
	{
		global $con;
		$pcount_qry = "select ( CASE WHEN COUNT(DISTINCT(CD.invoice_id)) = 0 and COUNT(DISTINCT(EF.estimate_id)) = 0 THEN 'Suspect' WHEN COUNT(DISTINCT(CD.invoice_id)) = 0 and COUNT(DISTINCT(EF.estimate_id)) > 0 THEN 'Prospect' WHEN COUNT(DISTINCT(CD.invoice_id)) = 1  THEN 'Shopper' WHEN COUNT(DISTINCT(CD.invoice_id)) > 1 and 0 > 8 and date_format(CD.created_on,'%Y%m%d') <= date_format(AB.created_on,'%Y%m%d')+90 THEN 'Advocate' WHEN COUNT(DISTINCT(CD.invoice_id)) > 1 and 0 > 6 and date_format(CD.created_on,'%Y%m%d') <= date_format(AB.created_on,'%Y%m%d')+90 THEN 'Member' WHEN COUNT(DISTINCT(CD.invoice_id)) > 1  THEN 'Customer' ELSE '-'END ) AS status_formatted from `ki_customers_info` as AB LEFT JOIN `ki_invoices_info` as CD  on CD.`customer_id` = AB.`customer_id`  LEFT JOIN `ki_estimates_info` as EF  on EF.`customer_id` = AB.`customer_id`  LEFT JOIN `ki_customer_opportunities_mapping_info` as GH  on GH.`customer_id` = AB.`customer_id` LEFT JOIN `ki_customer_tags_mapping_info` as IJ  on IJ.`customer_id` = AB.`customer_id` LEFT JOIN `ki_customer_vertical_mapping_info` as KL on KL.`customer_id` = AB.`customer_id` WHERE
		AB.delete_flag = 0 and AB.`" . safe_str($inputdata['key']) . "`='" . safe_str($inputdata['value']) . "'";
		//	var_dump($pcount_qry);
		$pcount_result = $con->query($pcount_qry);
		$pcount_row = $pcount_result->fetch_assoc();
		return $pcount_row;
	}

	function get_customer_categories($inputdata)
	{
		global $con;
		$pcount_qry = "select GH.*,GROUP_CONCAT(DISTINCT( CD.`product_name` ) separator ',') as product_name from `ki_invoices_info` as AB INNER JOIN `ki_invoice_line_items_info` as CD ON CD.`invoice_id` = AB.`invoice_id` INNER JOIN `ki_products_info` as EF ON EF.`product_id` = CD.`product_id`  INNER JOIN `ki_categories_info` as GH ON GH.`category_id` = EF.`category_id` LEFT JOIN `ki_categories_info` as IJ ON IJ.`category_id` = GH.`parent_category_id` and  GH.`is_child` = 1 WHERE
		AB.delete_flag = 0 and AB.`" . safe_str($inputdata['key']) . "`='" . safe_str($inputdata['value']) . "' group by GH.`category_id`";
		//var_dump($pcount_qry);
		$pcount_result = $con->query($pcount_qry);
		//$pcount_row = $pcount_result->fetch_assoc();
		$final_list = array();
		while ($row = $pcount_result->fetch_assoc()) {
			$final_list[] = $row;
		}
		return $final_list;
	}

	function get_active_trade_days($inputdata)
	{
		global $con;
		$location_id = safe_str($inputdata['location_id']);
		$location_type = safe_str($inputdata['location_type']);

		$pcount_qry = "select CD.start_time, CD.end_time, AB.non_trade_day_recurring_id as id,AB.day from ki_non_trade_recurring_days_info as AB LEFT JOIN ki_trade_days_info as CD on CD.non_trade_day_recurring_id = AB.non_trade_day_recurring_id and CD.is_enabled =1 WHERE location_id=" . $location_id . " and location_type =" . $location_type . " and AB.is_enabled =1";
		//var_dump($pcount_qry);
		$pcount_result = $con->query($pcount_qry);
		//$pcount_row = $pcount_result->fetch_assoc();
		$final_list = array('day' => array(), 'day_id' => array(), 'day_hours' => array());
		while ($row = $pcount_result->fetch_assoc()) {
			$final_list['day'][] = $row['day'];
			$final_list['day_id'][] = array('id' => $row['id'], 'day' => $row['day']);
			if (!empty($row['start_time']) && $row['end_time']) {
				$final_list['day_hours'][$row['day']][] = array('start_time' => $row['start_time'], 'end_time' => $row['end_time']);
			}
		}
		return $final_list;
	}

	function create_update_non_trade_day($inputdata)
	{
		global $con;
		$data = array(
			"status" => 0,
			"errors" => array()
		);
		$checked_days = $inputdata['checked_days'];
		if (empty($checked_days)) {
			return;
		}
		//print_r($inputdata);
		$days_list = array();
		$location_id = safe_str($inputdata['location_id']);
		$location_type = safe_str($inputdata['location_type']);
		$pcount_qry = "select day from ki_non_trade_recurring_days_info WHERE location_id=" . $location_id . " and location_type =" . $location_type . " and is_enabled =1";
		$pcount_result = $con->query($pcount_qry);
		while ($row = $pcount_result->fetch_assoc()) {
			$days_list[] = $row['day'];
		}

		$create_where = '';
		if ($checked_days != $days_list) {
			foreach ($checked_days as $checked_day) {
				$create_where .= ' and day != ' . $checked_day;
			}

			$pcount_qry = "UPDATE `ki_non_trade_recurring_days_info` SET is_enabled = 0, `modified_on`='" . date("Y-m-d H:i:s") . "' WHERE is_enabled = 1 AND location_id=" . $location_id . " and location_type =" . $location_type . " " . $create_where;
			// $pcount_qry = "UPDATE `ki_non_trade_recurring_days_info` as AB LEFT JOIN ki_trade_days_info as CD ON  CD.non_trade_day_recurring_id = AB.non_trade_day_recurring_id SET CD .is_enabled = 0, AB.is_enabled = 0 WHERE location_id=".$location_id." and location_type =".$location_type." ".$create_where;
			$pcount_result = $con->query($pcount_qry);
			if ($pcount_result) {
				/* $trade_days_delete = send_rest(array(
					"table" => "ki_trade_days_info",
					"function" => "delete_records",
					"key" => "is_enabled",
					"value" => 0
					
				)); */
				$array = array_diff($checked_days, $days_list);
				if (!empty($array)) {
					$in_fields = array(
						"location_id" => $location_id,
						"location_type" => $location_type,
						"is_enabled" => 1,
						"created_on" => date("Y-m-d H:i:s"),
					);
					$error = 0;
					foreach ($array as $array_value) {
						$in_fields['day'] = $array_value;
						$up_qry = "INSERT INTO `ki_non_trade_recurring_days_info` (`" . implode("`, `", array_keys($in_fields)) . "`) VALUES ('" . implode("', '", $in_fields) . "')";
						$up_result = $con->query($up_qry);
						if (!$up_result) {
							$error = 1;
						}
					}
					if ($error == 0) {
						$data["status"] = 1;
					}
				}
			}
		} else {
			$data["status"] = 1;
		}

		return $data;
	}

	function get_work_to_complete_pagging_list($inputdata)
	{
		global $con;
		$data = array(
			"total_records" => 0,
			"total_pages" => 0,
			"pagging_list" => array()
		);

		$table = safe_str($inputdata['table']);
		$page_no = safe_str($inputdata['page_no']);
		$row_size = safe_str($inputdata['row_size']);
		$sort_on = safe_str($inputdata['sort_on']);
		$sort_type = safe_str($inputdata['sort_type']);
		$query = '';


		if (!empty($inputdata['search'])) {
			//$query .="AND work_to_complete_id Like '%".safe_str($inputdata['search'])."%' ";
			$query .= ' and ( work_to_complete like "%' . safe_str($inputdata['search']) . '%")';
		}


		$pcount_qry = "select count(*) as total_count from `" . safe_str($table) . "` where delete_flag=0 " . $query;
		$pcount_result = $con->query($pcount_qry);
		$pcount_row = $pcount_result->fetch_assoc();
		$total_records = $pcount_row["total_count"];

		@$total_pages = ceil($total_records / $row_size);
		if ($total_pages == 0) {
			$total_pages = 1;
		}
		if ($page_no > $total_pages) {
			$page_no = $total_pages;
		}
		$limit_from = ($row_size * $page_no) - $row_size;

		$pagg_qry = "select * from `" . safe_str($table) . "` where delete_flag=0 " . $query . " order by " . safe_str($sort_on) . " " . safe_str($sort_type) . " LIMIT " . $limit_from . ", " . $row_size;
		$pagg_result = $con->query($pagg_qry);
		$pagg_count = $pagg_result->num_rows;
		$pagging_list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}

		$data["total_records"] = $total_records;
		$data["total_pages"] = $total_pages;
		$data["pagging_list"] = $pagging_list;

		return $data;
	}

	function get_tender_type_pagging_list($inputdata)
	{
		global $con;
		$data = array(
			"total_records" => 0,
			"total_pages" => 0,
			"pagging_list" => array()
		);

		$table = safe_str($inputdata['table']);
		$page_no = safe_str($inputdata['page_no']);
		$row_size = safe_str($inputdata['row_size']);
		$sort_on = safe_str($inputdata['sort_on']);
		$sort_type = safe_str($inputdata['sort_type']);
		$query = '';


		if (!empty($inputdata['search'])) {

			$query .= " and (tender_name Like '%" . safe_str($inputdata['search']) . "%') ";
		}


		$pcount_qry = "select count(*) as total_count from `" . safe_str($table) . "` where delete_flag=0 " . $query;
		$pcount_result = $con->query($pcount_qry);
		$pcount_row = $pcount_result->fetch_assoc();
		$total_records = $pcount_row["total_count"];

		@$total_pages = ceil($total_records / $row_size);
		if ($total_pages == 0) {
			$total_pages = 1;
		}
		if ($page_no > $total_pages) {
			$page_no = $total_pages;
		}
		$limit_from = ($row_size * $page_no) - $row_size;

		$pagg_qry = "select * from `" . safe_str($table) . "` where delete_flag=0 " . $query . " order by " . safe_str($sort_on) . " " . safe_str($sort_type) . " LIMIT " . $limit_from . ", " . $row_size;
		$pagg_result = $con->query($pagg_qry);
		$pagg_count = $pagg_result->num_rows;
		$pagging_list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}

		$data["total_records"] = $total_records;
		$data["total_pages"] = $total_pages;
		$data["pagging_list"] = $pagging_list;

		return $data;
	}

	function get_brand_pagging_list($inputdata)
	{
		global $con;
		$data = array(
			"total_records" => 0,
			"total_pages" => 0,
			"pagging_list" => array()
		);

		$table = safe_str($inputdata['table']);
		$page_no = safe_str($inputdata['page_no']);
		$row_size = safe_str($inputdata['row_size']);
		$sort_on = safe_str($inputdata['sort_on']);
		$sort_type = safe_str($inputdata['sort_type']);
		$query = '';


		if (!empty($inputdata['search'])) {

			$query .= "and (brand_name Like '%" . safe_str($inputdata['search']) . "%' )";
		}


		$pcount_qry = "select count(*) as total_count from `" . safe_str($table) . "` where delete_flag=0 " . $query;
		$pcount_result = $con->query($pcount_qry);
		$pcount_row = $pcount_result->fetch_assoc();
		$total_records = $pcount_row["total_count"];

		@$total_pages = ceil($total_records / $row_size);
		if ($total_pages == 0) {
			$total_pages = 1;
		}
		if ($page_no > $total_pages) {
			$page_no = $total_pages;
		}
		$limit_from = ($row_size * $page_no) - $row_size;

		$pagg_qry = "select * from `" . safe_str($table) . "` where delete_flag=0 " . $query . " order by " . safe_str($sort_on) . " " . safe_str($sort_type) . " LIMIT " . $limit_from . ", " . $row_size;
		$pagg_result = $con->query($pagg_qry);
		$pagg_count = $pagg_result->num_rows;
		$pagging_list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}

		$data["total_records"] = $total_records;
		$data["total_pages"] = $total_pages;
		$data["pagging_list"] = $pagging_list;

		return $data;
	}

	function get_ticket_type_pagging_list($inputdata)
	{
		global $con;
		$data = array(
			"total_records" => 0,
			"total_pages" => 0,
			"pagging_list" => array()
		);

		$table = safe_str($inputdata['table']);
		$page_no = safe_str($inputdata['page_no']);
		$row_size = safe_str($inputdata['row_size']);
		$sort_on = safe_str($inputdata['sort_on']);
		$sort_type = safe_str($inputdata['sort_type']);
		$query = '';


		if (!empty($inputdata['search'])) {

			$query .= "and (ticket_type_name Like '%" . safe_str($inputdata['search']) . "%' )";
		}


		$pcount_qry = "select count(*) as total_count from `" . safe_str($table) . "` where delete_flag=0 " . $query;
		$pcount_result = $con->query($pcount_qry);
		$pcount_row = $pcount_result->fetch_assoc();
		$total_records = $pcount_row["total_count"];

		@$total_pages = ceil($total_records / $row_size);
		if ($total_pages == 0) {
			$total_pages = 1;
		}
		if ($page_no > $total_pages) {
			$page_no = $total_pages;
		}
		$limit_from = ($row_size * $page_no) - $row_size;

		$pagg_qry = "select * from `" . safe_str($table) . "` where delete_flag=0 " . $query . " order by " . safe_str($sort_on) . " " . safe_str($sort_type) . " LIMIT " . $limit_from . ", " . $row_size;
		$pagg_result = $con->query($pagg_qry);
		$pagg_count = $pagg_result->num_rows;
		$pagging_list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}

		$data["total_records"] = $total_records;
		$data["total_pages"] = $total_pages;
		$data["pagging_list"] = $pagging_list;

		return $data;
	}

	function get_skill_category_pagging_list($inputdata)
	{
		global $con;
		$data = array(
			"total_records" => 0,
			"total_pages" => 0,
			"pagging_list" => array()
		);

		$table = safe_str($inputdata['table']);
		$page_no = safe_str($inputdata['page_no']);
		$row_size = safe_str($inputdata['row_size']);
		$sort_on = safe_str($inputdata['sort_on']);
		$sort_type = safe_str($inputdata['sort_type']);
		$query = '';


		if (!empty($inputdata['search'])) {

			$query .= "and (category_name Like '%" . safe_str($inputdata['search']) . "%' )";
		}


		$pcount_qry = "select count(*) as total_count from `" . safe_str($table) . "` where delete_flag=0 " . $query;
		$pcount_result = $con->query($pcount_qry);
		$pcount_row = $pcount_result->fetch_assoc();
		$total_records = $pcount_row["total_count"];

		@$total_pages = ceil($total_records / $row_size);
		if ($total_pages == 0) {
			$total_pages = 1;
		}
		if ($page_no > $total_pages) {
			$page_no = $total_pages;
		}
		$limit_from = ($row_size * $page_no) - $row_size;

		$pagg_qry = "select * from `" . safe_str($table) . "` where delete_flag=0 " . $query . " order by " . safe_str($sort_on) . " " . safe_str($sort_type) . " LIMIT " . $limit_from . ", " . $row_size;
		$pagg_result = $con->query($pagg_qry);
		$pagg_count = $pagg_result->num_rows;
		$pagging_list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}

		$data["total_records"] = $total_records;
		$data["total_pages"] = $total_pages;
		$data["pagging_list"] = $pagging_list;

		return $data;
	}

	function get_refferal_source_pagging_list($inputdata)
	{
		global $con;
		$data = array(
			"total_records" => 0,
			"total_pages" => 0,
			"pagging_list" => array()
		);

		$table = safe_str($inputdata['table']);
		$page_no = safe_str($inputdata['page_no']);
		$row_size = safe_str($inputdata['row_size']);
		$sort_on = safe_str($inputdata['sort_on']);
		$sort_type = safe_str($inputdata['sort_type']);
		$query = '';


		if (!empty($inputdata['search'])) {
			$query .= "and (`source_name` LIKE '%" . safe_str($inputdata['search']) . "%'  OR `category_name` LIKE '%" . safe_str($inputdata['search']) . "%' )";
		}
		if (!empty($inputdata['category'])) {
			$query .= "AND RCI.`category_id`='" . safe_str($inputdata['category']) . "' ";
		}


		$pcount_qry = "SELECT COUNT(*) AS total_count FROM `" . safe_str($table) . "` RSI LEFT JOIN `ki_referral_categories_info` RCI ON RSI.`category_id`=RCI.`category_id` AND RCI.`delete_flag`=0 WHERE RSI.`delete_flag`=0 " . $query;
		$pcount_result = $con->query($pcount_qry);
		$pcount_row = $pcount_result->fetch_assoc();
		$total_records = $pcount_row["total_count"];

		@$total_pages = ceil($total_records / $row_size);
		if ($total_pages == 0) {
			$total_pages = 1;
		}
		if ($page_no > $total_pages) {
			$page_no = $total_pages;
		}
		$limit_from = ($row_size * $page_no) - $row_size;

		$pagg_qry = "SELECT RSI.*, RCI.`category_name` FROM `" . safe_str($table) . "` RSI LEFT JOIN `ki_referral_categories_info` RCI ON RSI.`category_id`=RCI.`category_id` AND RCI.`delete_flag`=0 WHERE RSI.`delete_flag`=0 " . $query . " ORDER BY " . safe_str($sort_on) . " " . safe_str($sort_type) . " LIMIT " . $limit_from . ", " . $row_size;
		$pagg_result = $con->query($pagg_qry);
		$pagg_count = $pagg_result->num_rows;
		$pagging_list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}

		$data["total_records"] = $total_records;
		$data["total_pages"] = $total_pages;
		$data["pagging_list"] = $pagging_list;

		return $data;
	}

	function get_job_tools_pagging_list($inputdata)
	{
		global $con;
		$data = array(
			"total_records" => 0,
			"total_pages" => 0,
			"pagging_list" => array()
		);

		$table = safe_str($inputdata['table']);
		$page_no = safe_str($inputdata['page_no']);
		$row_size = safe_str($inputdata['row_size']);
		$sort_on = safe_str($inputdata['sort_on']);
		$sort_type = safe_str($inputdata['sort_type']);
		$query = '';


		if (!empty($inputdata['search'])) {

			$query .= "and (tool_name Like '%" . safe_str($inputdata['search']) . "%' or tool_url Like '%" . safe_str($inputdata['search']) . "%' )";
		}


		$pcount_qry = "select count(*) as total_count from `" . safe_str($table) . "` where delete_flag=0 " . $query;
		$pcount_result = $con->query($pcount_qry);
		$pcount_row = $pcount_result->fetch_assoc();
		$total_records = $pcount_row["total_count"];

		@$total_pages = ceil($total_records / $row_size);
		if ($total_pages == 0) {
			$total_pages = 1;
		}
		if ($page_no > $total_pages) {
			$page_no = $total_pages;
		}
		$limit_from = ($row_size * $page_no) - $row_size;

		$pagg_qry = "select * from `" . safe_str($table) . "` where delete_flag=0 " . $query . " order by " . safe_str($sort_on) . " " . safe_str($sort_type) . " LIMIT " . $limit_from . ", " . $row_size;
		$pagg_result = $con->query($pagg_qry);
		$pagg_count = $pagg_result->num_rows;
		$pagging_list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}

		$data["total_records"] = $total_records;
		$data["total_pages"] = $total_pages;
		$data["pagging_list"] = $pagging_list;

		return $data;
	}

	function get_canned_pagging_list($inputdata)
	{
		global $con;
		$data = array(
			"total_records" => 0,
			"total_pages" => 0,
			"pagging_list" => array()
		);

		$table = safe_str($inputdata['table']);
		$page_no = safe_str($inputdata['page_no']);
		$row_size = safe_str($inputdata['row_size']);
		$sort_on = safe_str($inputdata['sort_on']);
		$sort_type = safe_str($inputdata['sort_type']);
		$query = '';

		if (!empty($inputdata['search'])) {

			$query .= "and (title Like '%" . safe_str($inputdata['search']) . "%')";
		}


		$pcount_qry = "select count(*) as total_count from `" . safe_str($table) . "` where delete_flag=0 " . $query;
		$pcount_result = $con->query($pcount_qry);
		$pcount_row = $pcount_result->fetch_assoc();
		$total_records = $pcount_row["total_count"];

		@$total_pages = ceil($total_records / $row_size);
		if ($total_pages == 0) {
			$total_pages = 1;
		}
		if ($page_no > $total_pages) {
			$page_no = $total_pages;
		}
		$limit_from = ($row_size * $page_no) - $row_size;

		$pagg_qry = "select * from `" . safe_str($table) . "` where delete_flag=0 " . $query . " order by " . safe_str($sort_on) . " " . safe_str($sort_type) . " LIMIT " . $limit_from . ", " . $row_size;
		$pagg_result = $con->query($pagg_qry);
		$pagg_count = $pagg_result->num_rows;
		$pagging_list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$pagging_list[$i] = $row;
				$i++;
			}
		}

		$data["total_records"] = $total_records;
		$data["total_pages"] = $total_pages;
		$data["pagging_list"] = $pagging_list;

		return $data;
	}

	function get_user_list_roster($inputdata)
	{
		global $con;
		$data = array(
			"list" => array()
		);
		$pagg_qry = "select *,
		CASE 
			WHEN ((TO_SECONDS(finish_time) - TO_SECONDS(start_time))/3600 > 5) THEN ((TO_SECONDS(finish_time) - TO_SECONDS(start_time))/3600 - 0.5) 
		ELSE 
			(TO_SECONDS(finish_time) - TO_SECONDS(start_time))/3600
		END
		AS total_hours  from `ki_roster_data_info` where  delete_flag=0 and roster_id=" . safe_str($inputdata['roster_id']) . " and day=" . safe_str($inputdata['day']);

		$pagg_result = $con->query($pagg_qry);
		$pagg_count = $pagg_result->num_rows;
		$list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$list[$i] = $row;
				$i++;
			}
		}

		$data["list"] = $list;

		return $data;
	}

	function get_user_weekly_total_roster($inputdata)
	{
		global $con;
		$data = '';
		$pagg_qry = "select sum(at.total_hours) as weekly_total_hours from(select 
		CASE 
			WHEN ((TO_SECONDS(finish_time) - TO_SECONDS(start_time))/3600 > 5) THEN ((TO_SECONDS(finish_time) - TO_SECONDS(start_time))/3600 - 0.5) 
		ELSE 
			(TO_SECONDS(finish_time) - TO_SECONDS(start_time))/3600
		END
		AS total_hours
		from `ki_roster_data_info` where delete_flag=0 and roster_id=" . safe_str($inputdata['roster_id']) . " and user_id=" . safe_str($inputdata['user_id']) . ") at";
		$pagg_result = $con->query($pagg_qry);
		$pagg_count = $pagg_result->num_rows;
		$row = $pagg_result->fetch_assoc();
		$data = $row['weekly_total_hours'];

		return $data;
	}

	function get_users_list_select2($inputdata)
	{
		global $con;
		$data = array(
			"list" => array()
		);
		$pagg_qry = "select * from `ki_users_info` where is_enabled = 1 and delete_flag=0";
		$pagg_result = $con->query($pagg_qry);
		$pagg_count = $pagg_result->num_rows;
		$pagging_list = array();
		if ($pagg_count > 0) {
			$i = 0;
			while ($row = $pagg_result->fetch_assoc()) {
				$pagging_list[$i]['name'] = $row['first_name'] . " " . $row['last_name'];
				$pagging_list[$i]['id'] = $row['user_id'];
				$i++;
			}
		}
		$data["list"] = $pagging_list;

		return $data;
	}

	function get_suppliers_list_for_location($inputdata)
	{
		/* 
			input params - 
				"location_type" => $location_type,
				"location_id" => $location_id,
				"supplier_id" => $supplier_id		// optional - covers cases where supplier is either disabled or no more associated to the location.
			function is used to get list of all suppliers of passed location type and id  
			output - 
				$data - array that contains list  of all suppliers of passed location type and id  
		*/
		global $con;
		$data = array();
		$kingit_supplier_id = $this->get_king_it_distribution_supplier(array())['details']['supplier_id'];
		$unlisted_supplier_id = $this->get_unlisted_supplier_details(array())['details']['supplier_id'];
		$supplier = " OR SI.supplier_id='" . $kingit_supplier_id . "' OR SI.supplier_id='" . $unlisted_supplier_id . "'";
		if (!empty($inputdata['supplier_id'])) {
			$supplier .= " OR SI.supplier_id='" . $inputdata['supplier_id'] . "' ";
		}
		$query = "SELECT DISTINCT SI.* FROM `ki_suppliers_info` SI LEFT JOIN `ki_supplier_location_info` SLI ON SLI.`supplier_id` = SI.`supplier_id` AND SLI.`delete_flag` = 0 WHERE ((`location_type`='" . $inputdata['location_type'] . "' AND`location_id`='" . $inputdata['location_id'] . "')" . $supplier . ") AND SI.`is_enabled` = 1 AND SI.`delete_flag` = 0 ORDER BY `company_name` ASC";
		$result = $con->query($query);
		while ($row = $result->fetch_assoc()) {
			$data[] = $row;
		}
		return $data;
	}

	function get_status_for_special_order($inputdata)
	{
		/* 
				input params - 
					"invoice_id" => $inputdata['invoice_id'],
					"prod_name" => $inputdata['prod_name'],
					"status" => $inputdata['status'],	
					"invoice_line_item_id" => $inputdata['invoice_line_item_id']				
				function is used to get the status for special order items  
				output - 
					$response - return array having status as parameter 
		*/
		global $con;
		$response = array(
			"status" => 0,
		);

		$select = "select store_delivery_id, store_delivery_line_item_id from ki_store_delivery_line_items_mapping_info where type=1 and type_id=" . $inputdata['invoice_line_item_id'];
		$result = $con->query($select);
		if ($result->num_rows > 0) {
			$row = $result->fetch_assoc();
			$store_delivery_id = $row['store_delivery_id'];
			$store_delivery_line_item_id = $row['store_delivery_line_item_id'];

			$select1 = "select status from ki_store_delivery_line_items_info where store_delivery_line_item_id=" . $store_delivery_line_item_id;
			$result1 = $con->query($select1);
			if ($result1->num_rows > 0) {
				$row1 = $result1->fetch_assoc();
				if (empty($row1['status'])) {

					$select2 = "select status from ki_store_delivery_info where store_delivery_id=" . $store_delivery_id;
					$result2 = $con->query($select2);
					if ($result->num_rows > 0) {
						$row2 = $result2->fetch_assoc();
						if ($row2['status'] < 2) {
							$response['status'] = "to be Ordered";
						} else {
							$response['status'] = "Ordered";
						}
					}
				} else if ($row1['status'] == 1) {
					$response['status'] = "Back Ordered";
				} else if ($row1['status'] == 2) {
					$response['status'] = "Received";
				} else {
					$response['status'] = "Receive Undone";
				}
			}
		} else {
			$response['status'] = "to be Ordered";
		}
		//echo $store_delivery_id;
		return $response;
	}
	/*--------------------------Gourav Garg Code starts here---------------------*/
	function addChooseUs($inputdata)
	{
		global $con;
		$data = array(
			"status" => 0,
			"errors" => array()
		);
		$date = date("Y-m-d H:i:s");
		$table = "ki_meta_info";
		$in_fields = array();
		if (empty($inputdata['fields_data']['meta_key'] != 77)) {
			$data['erros'][] = "Something went wrong! Please try again later.";
		}
		if (empty($inputdata['fields_data']['meta_value'])) {
			$data['errors'][] = "Required fields cannot be empty.";
		}
		if (empty($inputdata['fields_data']['meta_id'])) {
			$data['erros'][] = "Something went wrong! Please try again later.";
		}

		if (empty($data['errors'])) {
			$meta_id	= $inputdata['fields_data']['meta_id'];
			$meta_value = $inputdata['fields_data']['meta_value'];
			$meta_key	= $inputdata['fields_data']['meta_key'];

			$in_fields['meta_id']		= "'" . safe_str($meta_id) . "'";
			$in_fields['meta_key']		= "'" . safe_str($meta_key) . "'";
			$in_fields['meta_value']	= "'" . safe_str($meta_value) . "'";
			$in_fields['created_on']	= "'" . safe_str($date) . "'";
			$in_fields['delete_flag']	= 0;
			$up_qry = "INSERT INTO `" . safe_str($table) . "` (`" . implode("`, `", array_keys($in_fields)) . "`) VALUES (" . implode(", ", $in_fields) . ")";
			$up_result = $con->query($up_qry);
			if ($up_result) {
				$data["status"] = 1;
			} else {
				$data["errors"][] = "Something went wrong! Please try again later.";
			}
		}
		return $data;
	}

	function updateChooseUs($inputdata)
	{
		global $con;
		$data = array(
			"status" => 0,
			"errors" => array()
		);
		$date = date("Y-m-d H:i:s");
		if (empty($inputdata['fields_data']['meta_key'] != 77)) {
			$data['erros'][] = "Something went wrong! Please try again later.";
		}
		if (empty($inputdata['fields_data']['meta_value'])) {
			$data['errors'][] = "Required fields cannot be empty.";
		}
		if (empty($inputdata['fields_data']['meta_id'])) {
			$data['erros'][] = "Something went wrong! Please try again later.";
		}
		if (empty($data['errors'])) {
			$meta_id	= $inputdata['fields_data']['meta_id'];
			$meta_value = $inputdata['fields_data']['meta_value'];
			$meta_key	= $inputdata['fields_data']['meta_key'];

			$up_qry = "update ki_meta_info set meta_value='" . safe_str($meta_value) . "',meta_key='" . safe_str($meta_key) . "',modified_on='" . safe_str($date) . "' where meta_id='" . safe_str($meta_id) . "';";

			$up_result = $con->query($up_qry);
			if ($up_result) {
				$data["status"] = 1;
			} else {
				$data["errors"][] = "Something went wrong! Please try again later.";
			}
		}
		return $up_qry;
	}

	function get_slug_url($inputdata)
	{
		/* 
		input params - 
			type, type_id
		function is used to get the slug url for paroduct listing and detail page.
		output - 
			$data = array(
				"status" => 0,
				"details" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"details" => array(),
			"errors" => array()
		);
		if (!empty($inputdata['type']) && !empty($inputdata['type_id'])) {
			$sql = "SELECT * FROM `ki_website_slugs_info` WHERE `slug_type`='" . safe_str($inputdata['type']) . "' AND `type_id`='" . safe_str($inputdata['type_id']) . "' AND `delete_flag`=0";
			$res = $con->query($sql);
			if ($res) {
				$row = $res->fetch_assoc();
				$data['details']['slug_url'] = WEBSITE_URL . "/products/";
				if (!empty($row['slug_path'])) {
					$data['details']['slug_url'] .= $row['slug_path'] . "/";
				}
				if (!empty($row['slugs'])) {
					$data['details']['slug_url'] .= $row['slugs'];
				}
				$data['status'] = 1;
			} else {
				$data['errors'][] = $con->error;
			}
		} else {
			$data['errors'][] = "Invalid input.";
		}
		return $data;
	}
	function get_slug_info($inputdata)
	{
		/* 
		input params - 
			page_slug
		function is used to get the detail of a slug present in url
		output - 
			$data = array(
				"status" => 0,
				"details" => array(),
				"errors" => array(),
				"count"	=>0
			);
		*/
		global $con;
		$response = array(
			"status" => 0,
			"errors" => array(),
			"data"	=> array(),
			"count"	=> 0
		);
		$concat_query = "";
		if (!empty($inputdata['slug_type'])) {
			$concat_query = "`slug_type`= '" . $inputdata['slug_type'] . "' AND";
		}
		if (empty($inputdata['page_slug'])) {
			$response["errors"] = "Something went wrong! Please try again later.";
		}
		if (empty($response['errors'])) {
			$sql = "select * from ki_website_slugs_info where $concat_query slugs='" . safe_str($inputdata['page_slug']) . "' and delete_flag='0'";
			if ($res = $con->query($sql)) {
				$response['count'] = $res->num_rows;
				$response['data'] = $res->fetch_assoc();
				$response['status'] = 1;
			} else {
				$response["errors"] = "Something went wrong! Please try again later.";
			}
		}
		return $response;
	}
	function save_customer_code($inputdata)
	{
		/* 
		input params - customer id, customer code
		function is to set the customer code of a particular customer
		output - 
			$response = array(
				"status" => 0,

				"errors" => array(),
			);
		*/
		global $con;

		$Encryption = new Encryption();
		$response = array(
			"status" => 0,

			"errors" => array()
		);
		if (empty($inputdata['customer_id'])) {
			$response["errors"][] = "Customer detail is required";
		} else if (empty($inputdata['customer_code'])) {
			$response["errors"][] = "Something went wrong! Please try again later.";
		}
		if (empty($response['errors'])) {
			$sql = "UPDATE `ki_customers_info` SET `customer_code`='" . safe_str($Encryption->encode($inputdata['customer_code'])) . "' WHERE `customer_id`='" . safe_str($inputdata['customer_id']) . "' AND `delete_flag`=0";
			if ($con->query($sql)) {
				$response['status'] = 1;
			} else {
				$response['errors'][] = "Something went wrong. Please try again later.";
			}
		}
		return $response;
	}

	function save_card_code($inputdata)
	{
		/* 
		input params - save card id, card code
		function is to set the card code of saved card
		output - 
			$response = array(
				"status" => 0,
				"errors" => array(),
			);
		*/
		global $con;
		$Encryption = new Encryption();
		$response = array(
			"status" => 0,
			"errors" => array()
		);
		if (empty($inputdata['saved_card_id'])) {
			$response["errors"][] = "Card detail is required";
		} else if (empty($inputdata['card_code'])) {
			$response["errors"][] = "Something went wrong! Please try again later.";
		}
		if (empty($response['errors'])) {
			$sql = "UPDATE `ki_saved_cards_info` SET `card_code`='" . safe_str($Encryption->encode($inputdata['card_code'])) . "' WHERE `saved_card_id`='" . safe_str($inputdata['saved_card_id']) . "' AND `delete_flag`=0";
			if ($con->query($sql)) {
				$response['status'] = 1;
			} else {
				$response['errors'][] = "Something went wrong. Please try again later.";
			}
		}
		return $response;
	}

	function save_payment_info($inputdata)
	{
		/* 
		function is to save payment info of invoice
		output - 
			$response = array(
				"status" => 0,

				"errors" => array(),
			);
		*/
		global $con;
		$response = array(
			"status" => 0,

			"errors" => array()
		);
		$in_fields = array();
		foreach ($inputdata['data'] as $field_key => $field_data) {
			$in_fields[safe_str($field_key)] = "'" . safe_str($field_data) . "'";
		}
		$up_qry = "INSERT INTO ki_invoice_payment_info (`" . implode("`, `", array_keys($in_fields)) . "`) VALUES (" . implode(", ", $in_fields) . ")";
		if ($up_result = $con->query($up_qry)) {
			$response["status"] = 1;
			$response["id"] = $con->insert_id;
		} else {
			$response["errors"][] = "Something went wrong. Please try again later.";
		}
		return $response;
	}
	function set_invoice_as_fully_paid($inputdata)
	{
		/* 
		input- invoice id
		function is used to set invoice as fully paid
		output - 
			$response = array(
				"status" => 0,
				"errors" => array(),
			);
		*/
		global $con;
		$response = array(
			"status" => 0,
			"errors" => array()
		);
		if (empty($inputdata['invoice_id'])) {
			$response["errors"][] = "Something went wrong. Please try again later.";
		}
		if (empty($response['errors'])) {
			$sql = "update ki_invoices_info set is_outstanding_invoice='0' and date_paid='" . safe_str(date("Y-m-d H:i:s")) . "' and modified_on='" . safe_str(date("Y-m-d H:i:s")) . "' where invoice_id='" . safe_str($inputdata['invoice_id']) . "'";
			if ($result = $con->query($sql)) {
				$response["status"] = 1;
			} else {
				$response["errors"][] = "Something went wrong. Please try again later.";
			}
		}
		return $response;
	}
	function get_ticket_line_items_list($inputdata){
		/* 
		input- "job_id"=>job id
		function is used to get the list of product added in the ticket
		output - 
			$response = array(
				"status" => 0,
				"details"=>array(),
				"errors" => array()
			);
		*/
		global $con;
		$response = array(
			"status" => 0,
			"details"=>array(),
			"errors" => array()
		);
		if(!empty($inputdata['job_id']) && !empty($inputdata['certificate_of_work_id'])){
			$qry="select 
						JP.*,PI.description,PI.SKU,PI.product_type,PI.product_name, PI.`minutes_to_complete`, PI.`product_type`, PPI.*, COALESCE(PPI.`spiff`,0) AS spiff, COALESCE(MLI.`tax_value`,MLI1.`tax_value`) AS retail_tax,MLI1.tax_value as default_tax,PPI.`retail_price` AS line_retail_price, PPI.`distribution_price` AS line_distribution_price, PPI.`cost_price` AS line_cost_price,PQI.`stock_on_hand`,COALESCE(PI.`category_id`,JP.`category_id`) AS category_id 

				FROM(
				select 
					JPOI.order_id,JPOI.supplier_id,JPOI.is_kingit_distribution,JPOI.item_url,JPOI.sell_price,JPOI.job_id,JPOI.category_id,JPOI.item_description,JCWI.quantity,JPOI.product_id,(sum(JPOI.quantity)-( CASE When JCWI.delete_flag=0 then JCWI.quantity else 0 end)+COALESCE(AA.mapping_qty,0)) as org_job_qty,JPOI.item_description as product_name,JPOI.`cost_price` AS manual_cost_price,sum(JPOI.quantity) AS total_qty
				    from ki_job_parts_order_info JPOI 
				LEFT JOIN 
					`ki_job_certificate_of_work_items_info` JCWI 
				ON 
					JPOI.product_id=JCWI.product_id AND JCWI.`certificate_of_work_id`='".safe_str(
					$inputdata['certificate_of_work_id'])."'
				LEFT JOIN (
                    	SELECT 
                    		JPOI.product_id,JPOI.item_description,sum(SDM.quantity) as mapping_qty 
                        FROM 
                        	ki_store_delivery_line_items_mapping_info SDM 
                        left join 
                        	ki_job_parts_order_info JPOI
                        on 	
                        	SDM.type_id=JPOI.order_id and SDM.type=2 and JPOI.product_id!=0
                        where 
                        	SDM.delete_flag=0 and JPOI.delete_flag=0 and SDM.quantity<0 and JPOI.job_id='".safe_str($inputdata['job_id'])."'
                        GROUP by JPOI.product_id
                    ) as AA
                ON JPOI.product_id=AA.product_id
				where
						job_id='".safe_str($inputdata['job_id'])."' and  
						JPOI.delete_flag=0 and JPOI.estimate_id!=0 
						and JPOI.product_id!=0 
				group by 
					JPOI.product_id HAVING org_job_qty>0
				union
				select 
					JPOI.order_id,JPOI.supplier_id,JPOI.is_kingit_distribution,JPOI.item_url,JPOI.sell_price,JPOI.job_id,JPOI.category_id,JPOI.item_description,COALESCE(JCWI.quantity,0) AS quantity,JPOI.product_id,(sum(JPOI.quantity)-COALESCE(JCWI.quantity,0)+COALESCE(BB.mapping_qty,0)) as org_job_qty,JPOI.item_description as product_name,JPOI.`cost_price` AS manual_cost_price,sum(JPOI.quantity) AS total_qty
				from 
				    	ki_job_parts_order_info JPOI 
				LEFT JOIN 
					`ki_job_certificate_of_work_items_info` JCWI 
				ON 
					JPOI.item_description=JCWI.product_name and JCWI.certificate_of_work_id='".safe_str($inputdata['certificate_of_work_id'])."' and JCWI.product_id=0
				LEFT JOIN(
                	SELECT 
                		JPOI.product_id,JPOI.item_description,COALESCE(sum(SDM.quantity),0) as mapping_qty 
                    FROM 
                    	ki_job_parts_order_info JPOI
                    left join  
                    	ki_store_delivery_line_items_mapping_info SDM 
                    on
                    	SDM.type_id=JPOI.order_id and SDM.type=2 and SDM.delete_flag=0 and SDM.quantity<0
                    where  
                    	JPOI.delete_flag=0  and JPOI.job_id='".safe_str($inputdata['job_id'])."' and JPOI.product_id=0
                    GROUP by JPOI.item_description
                ) as BB
                ON 
                    JPOI.item_description=BB.item_description
				where 		
						job_id='".safe_str($inputdata['job_id'])."' and  
						JPOI.delete_flag=0 and JPOI.product_id=0
				GROUP by JPOI.item_description HAVING org_job_qty>0
				) as JP
				LEFT JOIN ki_jobs_info JI on JP.job_id=JI.job_id
				LEFT JOIN `ki_products_info` PI ON 
						JP.`product_id`=PI.`product_id` AND PI.`delete_flag`=0 
				LEFT JOIN `ki_product_quantites_info` PQI ON 
						PI.`product_id`=PQI.`product_id` AND JI.`home_store_type`=PQI.`location_type` AND 
				        JI.`home_store_id`=PQI.`location_id` AND 	PQI.`delete_flag`=0 
				LEFT JOIN `ki_product_prices_info` PPI ON 
					PI.`product_id`=PPI.`product_id` AND PPI.`delete_flag`=0 
				LEFT JOIN `ki_meta_taxes_info` MLI ON 
						PPI.`retail_tax`=MLI.`tax_id` AND MLI.`delete_flag`=0 
				LEFT JOIN `ki_meta_taxes_info` MLI1 ON 
						MLI1.`is_default`=1 AND MLI1.`delete_flag`=0";
			/*
			$qry="select 
						JP.*,PI.description,PI.SKU,PI.product_type,PI.product_name, PI.`minutes_to_complete`, PI.`product_type`, PPI.*, COALESCE(PPI.`spiff`,0) AS spiff, COALESCE(MLI.`tax_value`,MLI1.`tax_value`) AS retail_tax,MLI1.tax_value as default_tax,PPI.`retail_price` AS line_retail_price, PPI.`distribution_price` AS line_distribution_price, PPI.`cost_price` AS line_cost_price,PQI.`stock_on_hand`,COALESCE(PI.`category_id`,JP.`category_id`) AS category_id
				FROM(
				select 
					JPOI.order_id,JPOI.supplier_id,JPOI.is_kingit_distribution,JPOI.item_url,JPOI.sell_price,JPOI.job_id,JPOI.category_id,JPOI.item_description,JCWI.quantity,JPOI.product_id,(sum(JPOI.quantity)-( CASE When JCWI.delete_flag=0 then JCWI.quantity else 0 end)) as org_job_qty,JPOI.item_description as product_name,JPOI.`cost_price` AS manual_cost_price,sum(JPOI.quantity) AS total_qty
				    from ki_job_parts_order_info JPOI 
				LEFT JOIN 
					`ki_job_certificate_of_work_items_info` JCWI 
				ON 
					JPOI.product_id=JCWI.product_id AND JCWI.`certificate_of_work_id`='".safe_str(
					$inputdata['certificate_of_work_id'])."'
				where 		 
						COALESCE(JCWI.is_special_order,1)
						and job_id='".safe_str($inputdata['job_id'])."' and  
						JPOI.delete_flag=0 and JPOI.estimate_id!=0 
						and JPOI.product_id!=0 
				group by 
					JPOI.product_id HAVING total_qty>COALESCE(JCWI.quantity,0)
				union
				select 
					JPOI.order_id,JPOI.supplier_id,JPOI.is_kingit_distribution,JPOI.item_url,JPOI.sell_price,JPOI.job_id,JPOI.category_id,JPOI.item_description,COALESCE(JCWI.quantity,0) AS quantity,JPOI.product_id,(sum(JPOI.quantity)-COALESCE(JCWI.quantity,0)) as org_job_qty,JPOI.item_description as product_name,JPOI.`cost_price` AS manual_cost_price,sum(JPOI.quantity) AS total_qty
				from 
				    	ki_job_parts_order_info JPOI 
				LEFT JOIN 
					`ki_job_certificate_of_work_items_info` JCWI 
				ON 
					JPOI.item_description=JCWI.product_name and JCWI.certificate_of_work_id='".safe_str($inputdata['certificate_of_work_id'])."' and JCWI.product_id=0
				where 		
						job_id='".safe_str($inputdata['job_id'])."' and  
						JPOI.delete_flag=0 and JPOI.product_id=0
				GROUP by JPOI.item_description HAVING total_qty>quantity
				) as JP
				LEFT JOIN ki_jobs_info JI on JP.job_id=JI.job_id
				LEFT JOIN `ki_products_info` PI ON 
						JP.`product_id`=PI.`product_id` AND PI.`delete_flag`=0 
				LEFT JOIN `ki_product_quantites_info` PQI ON 
						PI.`product_id`=PQI.`product_id` AND JI.`home_store_type`=PQI.`location_type` AND 
				        JI.`home_store_id`=PQI.`location_id` AND 	PQI.`delete_flag`=0 
				LEFT JOIN `ki_product_prices_info` PPI ON 
					PI.`product_id`=PPI.`product_id` AND PPI.`delete_flag`=0 
				LEFT JOIN `ki_meta_taxes_info` MLI ON 
						PPI.`retail_tax`=MLI.`tax_id` AND MLI.`delete_flag`=0 
				LEFT JOIN `ki_meta_taxes_info` MLI1 ON 
						MLI1.`is_default`=1 AND MLI1.`delete_flag`=0";
			*/
			/*
			$qry="SELECT 
				JPOI.order_id,JPOI.`quantity` AS org_job_qty,PI.SKU,PI.description,PI.product_type,PI.product_name, PI.`minutes_to_complete`, PI.`product_type`, PPI.*, COALESCE(PPI.`spiff`,0) AS spiff, COALESCE(MLI.`tax_value`,MLI1.`tax_value`) AS retail_tax,MLI1.tax_value as default_tax,PPI.`retail_price` AS line_retail_price, PPI.`distribution_price` AS line_distribution_price, PPI.`cost_price` AS line_cost_price,PQI.`stock_on_hand`, JPOI.*, JPOI.`cost_price` AS manual_cost_price, COALESCE(PI.`category_id`,JPOI.`category_id`) AS category_id 
			FROM 
				`ki_job_parts_order_info` JPOI 
			INNER JOIN `ki_jobs_info` JI ON 
				JPOI.`job_id`=JI.`job_id` AND JI.`delete_flag`=0 AND JI.`job_id`='".safe_str($inputdata['job_id'])."' 
			LEFT JOIN `ki_job_certificate_of_work_items_info` JCII ON 
				JPOI.`product_id`= JCII.`product_id` AND JCII.`certificate_of_work_id`='".safe_str($inputdata['certificate_of_work_id'])."' AND JCII.`delete_flag`=0 
			LEFT JOIN `ki_estimate_line_items_info` ELI ON 
				JCII.`estimate_line_item_id`=ELI.`estimate_line_item_id` AND ELI.`delete_flag`=0
			LEFT JOIN `ki_products_info` PI ON 
				JPOI.`product_id`=PI.`product_id` AND PI.`delete_flag`=0 
			LEFT JOIN `ki_product_quantites_info` PQI ON 
				PI.`product_id`=PQI.`product_id` AND JI.`home_store_type`=PQI.`location_type` AND JI.`home_store_id`=PQI.`location_id` AND 	PQI.`delete_flag`=0 
			LEFT JOIN `ki_product_prices_info` PPI ON 
				PI.`product_id`=PPI.`product_id` AND PPI.`delete_flag`=0 
			LEFT JOIN `ki_meta_taxes_info` MLI ON 
				PPI.`retail_tax`=MLI.`tax_id` AND MLI.`delete_flag`=0 
			LEFT JOIN `ki_meta_taxes_info` MLI1 ON 
				MLI1.`is_default`=1 AND MLI1.`delete_flag`=0 
			WHERE 
				(JPOI.`estimate_id` IS NULL OR JPOI.`estimate_id`!=ELI.`estimate_id`) AND JPOI.`delete_flag`=0
			GROUP BY
				JPOI.order_id";
			*/
			if ($result = $con->query($qry)) {
				while($row = $result->fetch_assoc()){
					$response['details'][] = $row;
				}
				$response["status"] = 1;
			} else {
				$response["errors"][] = "Something went wrong. Please try again later.";
			}
		}
		else{
			$response["errors"][] ="Something went wrong. Please try again later.";
		}
		return $response;
	}
	function get_suppliers_list($inputdata){
		/* 
		input- location id ,location type
		function is used to get the list of suppliers exist in location
		output - 
			$response = array(
				"status" => 0,
				"details"=>array(),
				"errors" => array()
			);
		*/
		global $con;
		$response = array(
			"status" => 0,
			"details"=>array(),
			"errors" => array()
		);
		if(empty($inputdata['location_id']) || empty($inputdata['location_type'])){
			$response['errors']="location detail is required";
		}
		if(empty($response['errors'])){
			$sql="select * from ki_suppliers_info SI INNER join ki_supplier_location_info SLI on SI.supplier_id=SLI.supplier_id where SLI.location_id='".safe_str($inputdata['location_id'])."' and SLI.location_type='".safe_str($inputdata['location_type'])."'";
			if($res=$con->query($sql)){
				while($row = $res->fetch_assoc()){
					$response['details'][] = $row;
				}
				$response["status"] = 1;
			}
			else{
				$response["errors"][] ="Something went wrong. Please try again later.";
			}
		}
		return $response;
	}
	function get_suppliers_list_for_ticket_type($inputdata){
		/* 
		function is used to get the supplier list with details
		output - 
			$list
		*/
		global $con;
		$list = array();
		$query = "SELECT DISTINCT CONCAT(COALESCE(A.`contact_first_name`,''),' ', COALESCE(A.`contact_last_name`,'')) AS supplier_name, A.* FROM `ki_suppliers_info` AS A LEFT JOIN `ki_supplier_location_info` AS B ON A.`supplier_id`=B.`supplier_id` WHERE  A.`delete_flag`=0 AND B.`delete_flag`=0 AND is_enabled=1 ORDER BY A.preference,A.company_name ASC";
		$result = $con->query($query);
		$i=0;
		echo $con->error;
		while($row = $result->fetch_assoc()){
			$list[$i] = $row;
			$i++;
		}
		return $list;
	}
	function get_invoice_from_certificate_info($inputdata){
		/* 
		input- certificate of work id
		function is used to get the invoice count generated from certificate
		output - 
			$response = array(
				"status" => 0,
				"total"=>0,
				"errors" => array()
			);
		*/
		global $con;
		$response = array(
			"status" => 0,
			"total"=>0,
			"errors" => array()
		);
		if(empty($inputdata['certificate_of_work_id'])){
			$response['errors']="Action cannot be performed.";
		}
		if(empty($response['errors'])){
			$sql="select count(*) as total from ki_invoices_info where certificate_of_work_id='".safe_str($inputdata['certificate_of_work_id'])."' and delete_flag=0";
			if($res=$con->query($sql)){
				if($res->num_rows>0){	
					$row=$res->fetch_assoc();
					$response['total']=$row['total'];
				}
				$response['status']=1;
			}
			else{
				$response["errors"][] ="Something went wrong. Please try again later.";
			}
		}
		return $response;
	}

	function DeleteCertificateOfWork($inputdata){
		/* 
		input- certificate of work id
		function is used to delete the certificate of work
		output - 
			$response = array(
				"status" => 0,
				"job_id"=>'',
				"errors" => array()
			);
		*/
		global $con;
		$Encryption=new Encryption();
		$response = array(
			"status" => 0,
			"job_id"=>'',
			"errors" => array()
		);
		if(!empty($inputdata['certificate_of_work_id'])){
			$cert_details=$this->get_details(array(
				"table" => "ki_job_certificate_of_work_info",
				"key" => "certificate_of_work_id",
				"value" => $inputdata['certificate_of_work_id']
			));
			//check if certificate of work is exist or not
			if(!empty($cert_details) && empty($cert_details['delete_flag'])){
				$cert_invoice_details=$this->get_invoice_from_certificate_info(array(
					"certificate_of_work_id"=>$inputdata['certificate_of_work_id']
				));
				//check wheather the certificate belong to any invoice or not 
				if(empty($cert_invoice_details['errors']) && empty($cert_invoice_details['total'])){
					//delete certificate line items
					$sql1="Update ki_job_certificate_of_work_items_info set delete_flag=1 where certificate_of_work_id=
					'".safe_str($inputdata['certificate_of_work_id'])."'";
					if($con->query($sql1)){
						//delete mapping with estimate
						$sql2="update ki_job_certificate_of_work_estimates_mapping_info set delete_flag=1 where certificate_of_work_id='".safe_str($inputdata['certificate_of_work_id'])."'";
						if($con->query($sql2)){
							//Delete certificate of work
							$sql3="update ki_job_certificate_of_work_info set delete_flag=1 where
							certificate_of_work_id='".safe_str($inputdata['certificate_of_work_id'])."'";
							if($con->query($sql3)){
								$response['status']=1;
								$response['job_id']=$Encryption->encode($cert_details['job_id']);
							}
							else{
								$response["errors"][] ="Something went wrong. Please try again later.";
							}
						}
						else{
							$response["errors"][] ="Something went wrong. Please try again later.";
						}
					}
					else{
						$response["errors"][] ="Something went wrong. Please try again later.";
					}
				}
				else{
					if(!empty($cert_invoice_details['errors'])){
						$response['errors']=$cert_invoice_details['errors'];
					}
					else{
						$response['errors'][]="Certificate Cannot be Deleted As Its Belong To Some Invoice";
					}
				}
			}
			else{
				$response['errors'][] = "Failed to get Certificate details.";
			}
		}
		else{
			$response['errors'][] = "Failed to get Certificate details.";
		}
		return $response;
	}
	function get_price_list_suppliers($inputdata){
		/* 
		input- price list id
		function is used to get the suppliers for price list location
		output - 
			$response = array(
				"status" => 0,
				"details"=>array(),
				"errors" => array()
			);
		*/
		global $con;
		$Encryption=new Encryption();
		$response = array(
			"status" => 0,
			"details"=>array(),
			"errors" => array()
		);
		if(!empty($inputdata['price_list_id'])){
			$sql1="select * from ki_price_lists_info where price_list_id='".safe_str($inputdata['price_list_id'])."' and delete_flag=0";
			if($res1=$con->query($sql1)){
				if($res1->num_rows>0){
					$sql2="SELECT 
								SI.* 
							FROM  
								ki_price_lists_info PLI
							INNER JOIN 
								ki_supplier_location_info SLI 
							ON 
								PLI.location_id=SLI.location_id AND PLI.location_type=SLI.location_type 
							LEFT JOIN 
								ki_suppliers_info SI on SLI.supplier_id=SI.supplier_id
							WHERE 
								PLI.price_list_id='".safe_str($inputdata['price_list_id'])."' AND PLI.delete_flag=0 and SLI.delete_flag=0 and SI.is_enabled=1 and SI.delete_flag=0 group by SI.supplier_id";
					if($res2=$con->query($sql2)){
						while($row=$res2->fetch_assoc()){
							$response['details'][]=$row;
						}
						$response['status']=1;
					}
					else{
						$response["errors"][] ="Something went wrong. Please try again later.";
					}
				}
				else{
					$response['errors'][] = "Failed to get Price list details.";
				}
			}
			else{
				$response["errors"][] ="Something went wrong. Please try again later.";
			}
		}
		else{
			$response['errors'][] = "Failed to get Price list details.";
		}

		return $response;
	}
	function add_supplier_products_to_price_list($inputdata){
		/* 
		input- supplier id,price list id
		function is used to add supplier products in the price list
		output - 
			$response = array(
				"status" => 0,
				"errors" => array()
			);
		*/
		global $con;
		$Encryption=new Encryption();
		$response = array(
			"status" => 0,
			"errors" => array()
		);
		if(!empty($inputdata['price_list_id'])){
			$sql1="select * from ki_price_lists_info where price_list_id='".safe_str($inputdata['price_list_id'])."' and delete_flag=0";
			if($res1=$con->query($sql1)){
				if($res1->num_rows>0){
					$row1=$res1->fetch_assoc();

					$pcount="SELECT 
								COUNT(DISTINCT PLGI.product_id) AS total
							FROM 
								ki_product_logistics_info PLGI 
							INNER JOIN 
								ki_products_info PI 
							ON 
								PLGI.product_id=PI.product_id 
							WHERE 
								PLGI.supplier_id='".safe_str($inputdata['supplier_id'])."' AND PLGI.location_id='".safe_str($row1['location_id'])."' AND PLGI.location_type='".safe_str($row1['location_type'])."' AND PI.status=1 AND PI.delete_flag=0 AND PLGI.delete_flag=0";

					if($result=$con->query($pcount)){
						$row_count=$result->fetch_assoc();
						if($row_count['total']>0){
							$sql2="SELECT 
										DISTINCT PLGI.product_id 
									FROM 
										ki_product_logistics_info PLGI 
									INNER JOIN 
										ki_products_info PI 
									ON 
										PLGI.product_id=PI.product_id 
									WHERE 
										PLGI.supplier_id='".safe_str($inputdata['supplier_id'])."' AND PLGI.location_id='".safe_str($row1['location_id'])."' AND PLGI.location_type='".safe_str($row1['location_type'])."' AND PI.status=1 AND PI.delete_flag=0 AND PLGI.delete_flag=0 AND PLGI.product_id 
									NOT IN(
										SELECT 
											DISTINCT product_id 
										FROM 
											ki_price_list_products_info 
										WHERE price_list_id='".safe_str($inputdata['price_list_id'])."' and delete_flag=0)";
							if($res2=$con->query($sql2)){
								if($res2->num_rows>0){
									while($row2=$res2->fetch_assoc()){
										$sql3="INSERT INTO `ki_price_list_products_info` (`price_list_id`, `product_id`, `copies`, `created_on`, `delete_flag`) VALUES ('".safe_str($inputdata['price_list_id'])."', '".safe_str($row2['product_id'])."', '0', '".safe_str(date("Y-m-d H:i:s"))."', '0')";
											if(!$res3=$con->query($sql3)){
												$response["errors"][] ="Something went wrong. Please try again later.";
											}
									}
								}
							}
							else{
								$response["errors"][] ="Something went wrong. Please try again later.";
							}
							if(empty($response['errors'])){
								$response['status']=1;
							}
						}
						else{
							$response['status']=2;
						}
					}
					else{
						$response['errors'][] = "Something went wrong. Please try again later.";
					}
				}
				else{
					$response['errors'][] = "Failed to get Price list details.";
				}
			}
			else{
				$response["errors"][] ="Something went wrong. Please try again later.";
			}
		}
		else{
			$response['errors'][] = "Failed to get Price list details.";
		}
		return $response;
	}
	function get_estimate_manual_product_supplier($inputdata){
		/* 
		input- estimate_line_item_id
		function is used to get the supplier detail of estimate manual product
		output - 
			$response = array(
				"status" => 0,
				"supplier_id"=>"",
				"errors" => array()
			);
		*/
		global $con;
		$Encryption=new Encryption();
		$response = array(
			"status" => 0,
			"supplier_id"=>"",
			"errors" => array()
		);
		if(!empty($inputdata['estimate_line_item_id'])){
			$sql="select * from ki_estimate_line_items_info where estimate_line_item_id='".safe_str($inputdata['estimate_line_item_id'])."' and delete_flag=0";
			if($res=$con->query($sql)){
				if($res->num_rows>0){
					$row=$res->fetch_assoc();
					$response['supplier_id']=$Encryption->encode($row['supplier_id']);
					$response['status']=1;
				}
				else{
					$response['errors'][]="Failed to get Product details.";
				}
			}
			else{
				$response["errors"][] ="Something went wrong. Please try again later.";
			}
		}
		else{
			$response['errors'][]="Failed to get Product Supplier details.";
		}
		return $response;
	}
	function save_sales_health_url($inputdata){
		/* 
		input- url,from date,to date,show sales,filter by store
		function is used to save the url as favourite
		output - 
			$response = array(
				"status" => 0,
				"errors" => array()
			);
		*/
		global $con;
		$Encryption=new Encryption();
		$response = array(
			"status" => 0,
			"errors" => array()
		);
		$data=$inputdata['data'];
		if(empty($inputdata['user_id']) || empty($data['url'])){
			$response['errors'][]="Action cannot be performed.";
		}
		$url="";
		if(empty($response['errors'])){
			$url=$data['url'];
			if(!empty($data['from_date'])){
				$url.="from_date=".$data['from_date'];
			}
			if(!empty($data['to_date'])){
				$url.="&to_date=".$data['to_date'];
			}
			if(!empty($data['filter_by_store'])){
				$url.="&filter_by_store=".$data['filter_by_store'];
			}
			if(!empty($data['show_sales'])){
				$url.="&show_sales=".$data['show_sales'];
			}
		}
		if(empty($response['errors'])){
			$sql="UPDATE ki_users_info set sales_health_url='".safe_str($url)."',sales_url_type='".safe_str($data['shortcut_active'])."' where user_id='".safe_str($inputdata['user_id'])."' and delete_flag=0";
			if($con->query($sql)){
				$response['status']=1;
			}
			else{
				$response["errors"][] ="Something went wrong. Please try again later.";
			}
		}
		return $response;
	}
	function get_sales_health_url($inputdata){
		/* 
		input- user id
		function is used to get sales health url
		output - 
			$response = array(
				"data"=>array(),
				"status" => 0,
				"errors" => array()
			);
		*/
		global $con;
		$Encryption=new Encryption();
		$response = array(
			"data"=>array(),
			"status" => 0,
			"errors" => array()
		);
		$sql="select * from ki_users_info where user_id='".safe_str($inputdata['user_id'])."' and delete_flag=0";
		if($res=$con->query($sql)){
			if($res->num_rows>0){
				$row=$res->fetch_assoc();
				if(!empty($row['sales_health_url'])){
					$url=$row['sales_health_url'];
					if(!empty($row['sales_url_type']) && ($row['sales_url_type']==1 || $row['sales_url_type']==2 || $row['sales_url_type']==3) ){
						// 1-rolling 30 days,2-prev month,3-today
						$from_date=$to_date='';
						if($row['sales_url_type']==1){
							$from_date=date('d-m-Y', strtotime('- 30 days'));
							$to_date=date('d-m-Y');
						}
						else if($row['sales_url_type']==2){
							$from_date=date('d-m-Y', strtotime('first day of last month'));
							$to_date=date('d-m-Y', strtotime('last day of last month'));
						}
						else if($row['sales_url_type']==3){
							$from_date=date('d-m-Y');
							$to_date=date('d-m-Y');
						}
						$from_str="from_date=".$from_date;
						$to_str="to_date=".$to_date;
						//$pattern ="/from_date=[\d-]+/";
						$url=preg_replace("/from_date=[\d-]+/",$from_str, $url);
						$url=preg_replace("/to_date=[\d-]+/",$to_str, $url);
						$response['data']=$url;
						$response['status']=1;
					}
				}
			}
			else{
				$response["errors"][] ="Failed to get user details";
			}
		}
		else{
			$response["errors"][] ="Something went wrong. Please try again later.";
		}
		return $response;
	}
	function save_jobs_overview_url($inputdata){
		/* 
		input- url,from date,to date,filter by store
		function is used to save the url as favourite
		output - 
			$response = array(
				"status" => 0,
				"errors" => array()
			);
		*/
		global $con;
		$Encryption=new Encryption();
		$response = array(
			"status" => 0,
			"errors" => array()
		);
		$data=$inputdata['data'];
		if(empty($inputdata['user_id']) || empty($data['url'])){
			$response['errors'][]="Action cannot be performed.";
		}
		$url="";
		if(empty($response['errors'])){
			$url=$data['url'];
			if(!empty($data['from_date'])){
				$url.="from_date=".$data['from_date'];
			}
			if(!empty($data['to_date'])){
				$url.="&to_date=".$data['to_date'];
			}
			if(!empty($data['filter_by_store'])){
				$url.="&filter_by_store=".$data['filter_by_store'];
			}
		}
		if(empty($response['errors'])){
			$sql="UPDATE ki_users_info set jobs_overview_url='".safe_str($url)."',jobs_url_type='".safe_str($data['shortcut_active'])."' where user_id='".safe_str($inputdata['user_id'])."' and delete_flag=0";
			if($con->query($sql)){
				$response['status']=1;
			}
			else{
				$response["errors"][] ="Something went wrong. Please try again later.";
			}
		}
		return $response;
	}
	function get_jobs_overview_url($inputdata){
		/* 
		input- user id
		function is used to get jobs overview url
		output - 
			$response = array(
				"data"=>array(),
				"status" => 0,
				"errors" => array()
			);
		*/
		global $con;
		$Encryption=new Encryption();
		$response = array(
			"data"=>array(),
			"status" => 0,
			"errors" => array()
		);
		$sql="select * from ki_users_info where user_id='".safe_str($inputdata['user_id'])."' and delete_flag=0";
		if($res=$con->query($sql)){
			if($res->num_rows>0){
				$row=$res->fetch_assoc();
				if(!empty($row['jobs_overview_url'])){
					$url=$row['jobs_overview_url'];
					if(!empty($row['jobs_url_type']) && ($row['jobs_url_type']==1 || $row['jobs_url_type']==2 || $row['jobs_url_type']==3) ){
						// 1-rolling 30 days,2-prev month,3-today
						$from_date=$to_date='';
						if($row['jobs_url_type']==1){
							$from_date=date('d-m-Y', strtotime('- 30 days'));
							$to_date=date('d-m-Y');
						}
						else if($row['jobs_url_type']==2){
							$from_date=date('d-m-Y', strtotime('first day of last month'));
							$to_date=date('d-m-Y', strtotime('last day of last month'));
						}
						else if($row['jobs_url_type']==3){
							$from_date=date('d-m-Y');
							$to_date=date('d-m-Y');
						}
						$from_str="from_date=".$from_date;
						$to_str="to_date=".$to_date;
						//$pattern ="/from_date=[\d-]+/";
						$url=preg_replace("/from_date=[\d-]+/",$from_str, $url);
						$url=preg_replace("/to_date=[\d-]+/",$to_str, $url);
						$response['data']=$url;
						$response['status']=1;
					}
				}
			}
			else{
				$response["errors"][] ="Failed to get user details";
			}
		}
		else{
			$response["errors"][] ="Something went wrong. Please try again later.";
		}
		return $response;
	}
	/*---------------------------------Gourav Garg Code ends here----------------------------------*/



	function mail_new_customers($inputdata)
	{
		/* 
		@author Savneet Kaur
		function is used to send joining mail to customers within 60 minutes of their creation
		output - 
			$data = array(
				"status" => 0,
				"detail" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$Customer = new Customer();
		$data = array(
			"status" => 0,
			"detail" => array(),
			"errors" => array()
		);
		$get_value = get_meta_value(80);
		$time = $get_value + 5;
		$current_date = date('Y-m-d H:i:s', strtotime('- ' . $time . ' min'));
		$prev_60_mins = date('Y-m-d H:i:s', strtotime('- ' . $get_value . ' min'));


		$sql = "SELECT * FROM `ki_customers_info` WHERE `delete_flag`=0 and `is_joining_mail_sent` = 0 
					and ( `is_unsubscribed_to_marketing` = 0 or `is_unsubscribed_to_marketing` is NULL ) and (`created_on` between '" . $current_date . "' and '" . $prev_60_mins . "') ; ";
		//echo $sql;
		$result = $con->query($sql);
		if ($result->num_rows > 0) {
			while ($row = $result->fetch_assoc()) {
				if (check_if_valid_customer_email($row['email'])) {
					$data['detail'][] = $row;
					$data['status'] = 1;
				}
			}
		}

		return $data;
	}

	function sent_mail_confirm_new_customers($inputdata)
	{
		/* 
		@author Savneet Kaur
		function is used to set is_joining_mail_sent= 1 if mail has been sent successfully
		output - 
			$data = array(
				"status" => 0
			);
		*/
		global $con;
		$data = array(
			"status" => 0
		);
		$sql = "Update ki_customers_info set is_joining_mail_sent = 1 where customer_id = '" . safe_str($inputdata['customer_id']) . "'; ";
		if ($con->query($sql)) {
			$data['status'] = 1;
		}
	}



	function feedback_records_60_min($inputdata)
	{
		/* 
		$author Savneet Kaur
		function is used to select records from ki_customer_feedback_info within 60 minutes of their creation
		output - 
			$data = array(
				"status" => 0,
				"detail" => array(),
				"errors" => array()
			);
		*/
		global $con;
		$data = array(
			"status" => 0,
			"detail" => array(),
			"errors" => array()
		);
		$get_value = 0;
		if (get_meta_value(81) !== NULL) {
			$get_value = get_meta_value(81);
		}
		$time = $get_value + 5;
		$current_date = date('Y-m-d H:i:s', strtotime('- ' . $time . ' min'));
		$prev_60_mins = date('Y-m-d H:i:s', strtotime('- ' . $get_value . ' min'));

		$sql = "SELECT * FROM `ki_customer_feedback_info` WHERE `delete_flag`=0 and points>=8 and `is_feedback_mail_sent` = 0 and (`created_on` between '" . $current_date . "' and '" . $prev_60_mins . "') ; ";
		//echo $sql;
		$result = $con->query($sql);
		if ($result->num_rows > 0) {
			while ($row = $result->fetch_assoc()) {
				$data['detail'][] = $row;
				$data['status'] = 1;
			}
		}

		return $data;
	}
	function feedback_mail_sent_confirmation($inputdata)
	{
		/* 
		@author Savneet Kaur
		function is used to set is_feedback_mail_sent= 1 if mail has been sent successfully
		output - 
			$data = array(
				"status" => 0
			);
		*/
		global $con;
		$data = array(
			"status" => 0
		);
		$sql = "Update ki_customer_feedback_info set is_feedback_mail_sent = 1 where feedback_id = '" . safe_str($inputdata['feedback_id']) . "'; ";
		if ($con->query($sql)) {
			$data['status'] = 1;
		}
	}
}
