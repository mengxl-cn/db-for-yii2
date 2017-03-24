<?php

namespace mengxl;

/**
 * Created by PhpStorm.
 * User: wangli
 * Date: 17/3/24
 * Time: 18:25
 */
class MengxlApp
{
	/**
	 * @var \yii\db\Connection
	 */
	public $db;

	/**
	 * MengxlApp constructor.
	 */
	public function __construct()
	{

	}

	/**
	 * Returns the database connection component.
	 * @return \yii\db\Connection the database connection.
	 */
	public function getDb()
	{
		return $this->db;
	}
}