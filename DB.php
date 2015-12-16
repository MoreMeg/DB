<?php

/**
 *	Priklad quericka
 *
 *	Prva varianta:
 *
 *	$arrayName = array("female", 23);
 *	$db->query("SELECT id, name, age FROM users WHERE sex = ? AND age = ?", $arrayName)->result();
 *
 *	Druha varianta:
 *
 *	$arrayName = array(":sex" => "female", ":age" => 23);
 *	$db->query("SELECT id, name, age FROM users WHERE sex = :sex AND age = :age", $arrayName)->result();
 */

class DB
{

	//------------------------------------------------------------------------------
	// CONFIG
	//------------------------------------------------------------------------------

	// error log file
	// private static $error_log_file = 'system/data/logs/sql_errors.log';
	// private static $error_log_file = '';

	// private static $show_sqls = false;

	//------------------------------------------------------------------------------
	//
	//------------------------------------------------------------------------------

	// Handled PDO Object
	private static $handler = false;

	// SQL Error list
	private static $errors = array();

	// Query list
	// private static $queries = array();

	// Last Query result
	// private $result = array();

	// Last Query Affected rows
	// private $affectedRows = false;

	// Last Query Inserted ID
	// private $lastInsertId = false;

	//------------------------------------------------------------------------------
	// CONSTRUCT
	//------------------------------------------------------------------------------

	private static function connect($info)
	{
		if (self::$handler === false)
		{
			try
			{
				$options = array(
					PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES ".$info['charset'] ?: 'utf8'
				);

				self::$handler = new PDO('mysql:'.$info['host'].'; port='.$info['port'].'; dbname='.$info['database'], $info['username'], $info['password'], $options);

				// set pdo error mode silent
				self::$handler->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT );
				// If you want to Show Class exceptions on Screen, Uncomment below code
				self::$handler->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
				// Use this setting to force PDO to either always emulate prepared statements (if TRUE),
				// or to try to use native prepared statements (if FALSE).
				self::$handler->setAttribute( PDO::ATTR_EMULATE_PREPARES, true );
				// set default pdo fetch mode as fetch assoc
				self::$handler->setAttribute( PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC );
			}
			catch (PDOException $e)
			{
				self::printError("ERROR in establish connection: ".$e->getMessage());
			}
		}
		else
		{
			self::printError('Already connected to DB!');
		}
	}

	//------------------------------------------------------------------------------
	// DESTRUCT AND SHOW ERRORS
	//------------------------------------------------------------------------------
/*
	public function __destruct()
	{
		// Show mysql errors
		if (count(self::$errors) > 0)
		{
			$html = '<ul style="position:absolute; top:0px; right:0px; z-index:2147483647; background:white; padding:0px; margin:20px; list-style:none; font:13px Consolas; color: #015A84; opacity:1; ">';

			foreach (self::$errors AS $msg)
			{
				$html .= '<li style="padding:5px; margin:3px; border:1px #EB7A00 dashed; background:#FFC688; ">MySQL Error: '.$msg.'</li>';

				if (self::$error_log_file)
				{
					file_put_contents(self::$error_log_file, strip_tags($msg).PHP_EOL, FILE_APPEND);
				}
			}

			$html .= '</ul>';

			echo $html;
		}

		if (self::$show_sqls)
		{
			echo '<ul style="background: white;">';
			foreach (self::$queries AS $query) {
				echo '<li>'.$query[0].' - <strong>'.$query[1].'</strong>'.$query[2].'</li>';
			}
			echo '</ul>';
		}

	}
*/

	public static function addConnection($info)
	{
		self::connect($info);
	}

	public static function selectAll($query, $params = null)
	{
		$_query = self::query($query, $params);
		return $_query->fetchAll();
	}

	public static function select($query, $params = null)
	{
		$_query = self::query($query, $params);
		return $_query->fetch();
	}

	public static function insert($query, $params = null)
	{
		$_query = self::query($query, $params);
		return self::$handler->lastInsertId();
	}

	public static function update($query, $params = null)
	{
		$_query = self::query($query, $params);
		return $_query->rowCount();
	}

	public static function delete($query, $params = null)
	{
		$_query = self::query($query, $params);
		return $_query->rowCount();
	}

	public static function statement($query, $params = null)
	{
		self::query($query, $params);
	}

	//------------------------------------------------------------------------------
	// QUERY
	//------------------------------------------------------------------------------

	private static function query($query, $params)
	{
		// debug_time();
		try
		{
			$_query = self::$handler->prepare($query);
			$_query->execute($params);
		}
		catch (PDOException $e)
		{
			// self::$errors[] = $e->getMessage().$this->getSQLCallerInfo().'<br><strong>SQL</strong>: '.$queryString;
			self::printError($e->getMessage(), $query);
		}

		// self::$queries[] = [$query, debug_time(), $this->getSQLCallerInfo()];

		return $_query;
	}

	private static function printError($error, $query = '')
	{
		echo '<div style="color: #3a7ead; background: #E5E5E5; border: 1px #D3DCE3 solid; padding: 5px; font: 14px Consolas, \'Droid Sans Mono\', Arial">'.
		$error.self::backtrace().($query ? '<br>SQL: '.$query : '')
		.'</div>';
	}

	//------------------------------------------------------------------------------
	// GET ONE ROW
	//------------------------------------------------------------------------------

	// public function result($i = 0)
	// {
	// 	return ($this->result[$i]) ?: false;
	// }

	//------------------------------------------------------------------------------
	// GET ALL ROWS
	//------------------------------------------------------------------------------

	// public function results($type = 'array')
	// {
	// 	switch ($type)
	// 	{
	// 		case 'json':
	// 			return json_encode($this->result);
	// 		break;

	// 		default:
	// 			return $this->result;
	// 		break;
	// 	}
	// }

	//------------------------------------------------------------------------------
	// GET AFFECTED ROWS
	//------------------------------------------------------------------------------

	// public function affectedRows()
	// {
	// 	return $this->affectedRows;
	// }

	//------------------------------------------------------------------------------
	// GET LAST INSERTED ID
	//------------------------------------------------------------------------------

	// public function lastInsertId()
	// {
	// 	return $this->lastInsertId;
	// }






	//------------------------------------------------------------------------------
	// BACKUP DB
	//------------------------------------------------------------------------------

	// public function backup()
	// {
	// 	$file = PATH_DATA.'/backup/db/'.date('Y-m-d').'/'.date('H-i-s').'.sql';

	// 	if (is_dir(dirname($file)) == false)
	// 	{
	// 		mkdir(dirname($file), 0777, true);
	// 	}

	// 	$dump   = MYSQL_DUMP;
	// 	$user   = '--user '.DB_USER;
	// 	$pass   = (DB_PASS) ? '--password '.DB_PASS : '';
	// 	$host   = '--host '.DB_HOST;
	// 	$dbname = DB_NAME;

	// 	$cmd  = "\"$dump\" $user $pass $host $dbname > \"$file\"";

	// 	$out = system($cmd);

	// 	if (is_file($file)) alert('MySql dump created. ('.round(filesize($file) / 1024, 2).' kB)');
	// }


	//------------------------------------------------------------------------------
	// GET FILE AND LINE, WHERE SQL IS CALLED
	//------------------------------------------------------------------------------

	private static function backtrace()
	{
		$backtrace = array_slice(debug_backtrace(), 3);
		return ' IN <strong>'.($backtrace[0]['file']).'</strong> ON LINE <strong>'.$backtrace[0]['line'].'</strong> ';
	}

}