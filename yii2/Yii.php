<?php

require 'MengxlApp.php';
require 'MengxlYii.php';

class Yii extends \mengxl\MengxlYii
{
}

spl_autoload_register(['Yii', 'autoload'], true, true);
Yii::$classMap = require(__DIR__ . '/classes.php');
Yii::$app = new \mengxl\MengxlApp();
Yii::$container = new yii\di\Container();