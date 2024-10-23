<?php
	date_default_timezone_set("Etc/GMT+8");
	require_once 'class.php'; // Make sure this file contains the necessary database connection class
	
	if (isset($_POST['save'])) {
		$db = new db_class(); // Assuming db_class initializes the DB connection
		$loan_id = $_POST['loan_id'];
		$payee = $_POST['payee'];
		$penalty = $_POST['penalty'];
		$payable = str_replace(",", "", $_POST['payable']);
		$payment = $_POST['payment'];
		$month = $_POST['month'];
		
		// Determine overdue status
		$overdue = ($penalty == 0) ? 0 : 1;
		
		// Check if the payment amount matches the payable amount
		if ($payable != $payment) {
			echo "<script>alert('Please enter a correct amount to pay!')</script>";
			echo "<script>window.location.href = 'payment.php';</script>";
		} else {
			// Save payment to the database
			$db->save_payment($loan_id, $payee, $payment, $penalty, $overdue);
			
			// Check if the connection is still active
			if ($db->conn) {
				// Count how many payments have been made for this loan
				$count_pay = $db->conn->query("SELECT * FROM `payment` WHERE `loan_id`='$loan_id'")->num_rows;
				
				// If the number of payments matches the number of months, update loan status to '3' (fully paid)
				if ($count_pay == $month) {
					$db->conn->query("UPDATE `loan` SET `status`='3' WHERE `loan_id`='$loan_id'") or die($db->conn->error);
				}
				
				// Redirect to the payment page
				header("Location: payment.php");
			} else {
				die("Database connection is closed or not available.");
			}
		}
	}
?>
