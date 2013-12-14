<?php

class SessionException extends Exception
{
}

class Session
{
	private static $key = 'go0dk3y';
	private static $wait_seconds = 60;
	private static $bonus = 10;
	private static $message_cost = 5;
	
	private static $database = "qctf_web";
	private static $hostname = "localhost";
	private static $username = 'user_web300';
	private static $password = 'cd0886e66e540e4f453cc06b09e92927';
	
	private static function encode($string) {
		$len = strlen($string);
		$klen = strlen(self::$key);
		$result = '';
		for ($i = 0; $i < $len; ++$i) {
			$result .= chr(ord($string[$i]) ^ ord(self::$key[$i % $klen]));
		}
		return implode('', unpack('H*', $result));
	}
	
	private static function decode($hexstring) {
		$string = pack('H*', $hexstring);
		$len = strlen($string);
		$klen = strlen(self::$key);
		$result = '';
		for ($i = 0; $i < $len; ++$i) {
			$result .= chr(ord($string[$i]) ^ ord(self::$key[$i % $klen]));
		}
		return $result;
	}
	
	private $session;
	private $connection;
	
	public function __construct() {
		$this->connection = mysql_connect(self::$hostname, self::$username, self::$password);
		mysql_select_db(self::$database, $this->connection);
		$this->session = array();
		$this->session['sessid'] = md5(rand(1,10000000).'iefhoiwefbncwef'.rand(1,10000000).'asdasdqwergfqoiehqwe'.rand(1,10000000));
		$this->session['money'] = 20;
		$this->session['time'] = time() + self::$wait_seconds;
	}
	
	private function get_money_from_db($sessid) {
		$result = mysql_query("SELECT money FROM sessions WHERE sessid='$sessid'", $this->connection);
		if (mysql_num_rows($result) === 0) {
			throw new SessionException();
		}
		$result = mysql_fetch_assoc($result);
		return $result['money'];
	}
	
	public function get_from_cookie() {
		if (isset($_COOKIE['session'])) {
			$cookie_session = self::decode($_COOKIE['session']);
			// $cookie_session = $_COOKIE['session'];
			$cookie_session = unserialize($cookie_session);
			if (
				(is_array($cookie_session)) and
				(isset($cookie_session['name'])) and
				(isset($cookie_session['time'])) and
				(isset($cookie_session['sessid'])) and
				(is_int($cookie_session['time'])) and
				(is_string($cookie_session['name'])) and
				(mb_strlen($cookie_session['name']) <= 30) and
				(is_string($cookie_session['sessid'])) and
				(preg_match('/^[0-9a-f]{32}$/', $cookie_session['sessid']))
			) {
				$money = $this->get_money_from_db($cookie_session['sessid']); // throw
				$this->session['money'] = $money;
				$this->session['name'] = $cookie_session['name'];
				$this->session['sessid'] = $cookie_session['sessid'];
				$this->session['time'] = $cookie_session['time'];
				$delay = time() - $cookie_session['time'];
				if ($delay < 0) {
					$delay = 0;
				} else {
					$delay += self::$wait_seconds;
				}
				$add_money = floor($delay / self::$wait_seconds) * self::$bonus;
				$add_time = $delay % self::$wait_seconds;
				if ($add_money > 0) {
					$this->session['money'] += $add_money;
					$this->session['time'] = time() + self::$wait_seconds - $add_time;
				}
			} else {
				throw new SessionException();
			}
		} else {
			throw new SessionException();
		}
	}
	
	public function set_username($username) {
		$this->session['name'] = mb_substr((string)($username), 0, 30);
	}
	
	public function get_name() {
		return $this->session['name'];
	}
	
	public function get_money() {
		return $this->session['money'];
	}
	
	public function pay_message() {
		if ($this->session['money'] >= self::$message_cost) {
			$this->session['money'] -= self::$message_cost;
			return true;
		} else {
			return false;
		}
	}
	
	public function flush() {
		$money = $this->session['money'];
		unset($this->session['money']);
		setcookie(
			"session",
			self::encode(serialize($this->session)),
			// serialize($this->session),
			time() + (10 * 365 * 24 * 60 * 60)
		);
		$this->session['money'] = $money;
		$sessid = $this->session['sessid'];
		try {
			$this->get_money_from_db($this->session['sessid']);
			mysql_query("UPDATE sessions SET money=$money WHERE sessid='$sessid'", $this->connection);
		} catch (SessionException $e) {
			mysql_query("INSERT INTO sessions (sessid,money) VALUES ('$sessid',$money)", $this->connection);
		}
	}
}

?>