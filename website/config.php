<?php


define("STATUS",1); // -1 close, 0 in maintenance, 1 open 
define("BETA_MODE","");

/* --------------- MYSQL INFOS --------------- */
define('SQL_DNS', 'mysql:host=127.0.0.1;dbname=yetigrooaeyeti');
define('SQL_USER', 'root');
define('SQL_PASS', '');


define('PASSWORD_LENGTH',5);
define("SHOUT_TIME_LIMIT",3600);

$_SMILEYS = array(
	':yeti:' => 'yetiemo.png',
	';)' => 'blink.png',
	';-)' => 'blink.png',
	':p' => 'tongue.png',
	':-p' => 'tongue.png',
	':(' => 'bad.png',
	':-(' => 'bad.png',
	':)' => 'smile.png',
	':-)' => 'smile.png',
	':D' => 'bigsmile.png',
	':-D' => 'bigsmile.png'
	);

DEFINE("ADMIN_GROUP_ID",1);


?>