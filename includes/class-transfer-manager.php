<?php
class Flipnzee_Transfer_Manager {

	/**
	 * Default transfer steps.
	 *
	 * @return array
	 */
	public static function get_default_steps() {

		return array(

			array(
				'label'     => 'Payment Confirmed',
				'completed' => true,
			),

			array(
				'label'     => 'Website Files Delivered',
				'completed' => false,
			),

			array(
				'label'     => 'Database Delivered',
				'completed' => false,
			),

			array(
				'label'     => 'Domain Transfer Completed',
				'completed' => false,
			),

			array(
				'label'     => 'Buyer Verification',
				'completed' => false,
			),

			array(
				'label'     => 'Purchase Completed',
				'completed' => false,
			),

		);

	}

	/**
	 * Default transfer status.
	 *
	 * @return array
	 */
	public static function get_default_status() {

		return array(

			'payment' => 'Completed',

			'files'    => 'Pending',

			'database' => 'Pending',

			'domain'   => 'Pending',

			'buyer'    => 'Pending',

		);

	}

	/**
	 * Status badge classes.
	 *
	 * @return array
	 */
	public static function get_status_badges() {

		return array(

			'Completed'   => 'flipnzee-success',

			'Pending'     => 'flipnzee-pending',

			'In Progress' => 'flipnzee-progress',

		);

	}

}