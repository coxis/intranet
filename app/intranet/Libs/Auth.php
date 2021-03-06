<?php
namespace Asgard\Intranet\Libs;

class Auth {
	public static function isConnected() {
		return (boolean)\Session::get(array('auth', 'id')) && static::user();
	}

	public static function isGuest() {
		return !static::isConnected();
	}

	public static function check() {
		if(!static::isConnected())
			throw new NotAuthenticatedException();
	}

	public static function attempt($username, $password) {
		$user = User::where(array('username'=>$username, 'password'=>static::hash($password)))->first();
		if($user)
			static::connect($user->id);
		return $user;
	}

	public static function attemptRemember() {
		if(\Asgard\Core\App::get('cookie')->has('remember')) {
			$user = User::where(array('SHA1(CONCAT(\''.\Config::get('key').'\', id))=\''.\Cookie::get('remember').'\''))->first();
			if($user)
				static::connect($user->id);
			return (boolean)$user;
		}
		return false;
	}

	public static function remember($id) {
		\Asgard\Core\App::get('cookie')->set('remember', static::hash($id));
	}

	public static function connect($id) {
		\Asgard\Core\App::get('session')->set(array('auth', 'id'), $id);
	}

	public static function disconnect() {
		\Asgard\Core\App::get('session')->remove(array('auth', 'id'));
		\Asgard\Core\App::get('cookie')->remove('remember');
	}

	public static function user() {
		return User::load(\Session::get(array('auth', 'id')));
	}

	public static function hash($val) {
		return sha1(Config::get('key').$val);
	}
}