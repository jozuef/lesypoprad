<?php

namespace DbTable;
use Nette\Security\Passwords;

/**
 * Model, ktory sa stara o tabulku user_main
 * 
 * Posledna zmena 06.06.2017
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2017 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.1
 */
class User_main extends Table {
  const
		COLUMN_ID = 'id',
    COLUMN_ID_USER_ROLES = 'id_user_roles',
    COLUMN_ID_USER_PROFILES = 'id_user_profiles',
    COLUMN_PASSWORD_HASH = 'password',
		COLUMN_MENO = 'meno',
    COLUMN_PRIEZVISKO = 'priezvisko',
		COLUMN_EMAIL = 'email',
    COLUMN_ACTIVATED = 'activated',
    COLUMN_BANNED = 'banned',
    COLUMN_BAN_REASON = 'ban_reason',
    COLUMN_LAST_IP = 'last_ip',
    COLUMN_CREATED = 'created';  
  
  /** @var string */
  protected $tableName = 'user_main';

  /** Test existencie emailu
   * @param string $email
   * @return boolean */
  public function testEmail($email) {
    return $this->findBy([self::COLUMN_EMAIL=>$email])->count() > 0 ? TRUE : FALSE;
  }
  
  /** Aktualizuje IP adresu posledneho prihlasenia
   * @param int $id
   * @param string $ip
   * @return boolean */
  public function logLastIp($id, $ip) {
    return $this->find($id)->update([self::COLUMN_LAST_IP => $ip]);
  }
  
	/** Adds new user.
   * @param string $meno
   * @param string $priezvisko
   * @param string $email
   * @param string $password
   * @param int $activated
   * @param int $role
   * @return void
   * @throws DuplicateNameEmailException */
	public function add($meno, $priezvisko, $email, $password, $activated = 0, $role = 0)	{
		try {
			$user_profiles = $this->connection->table('user_profiles')->insert([]); 
      $this->pridaj([
        self::COLUMN_MENO             => $meno,
        self::COLUMN_PRIEZVISKO       => $priezvisko,
				self::COLUMN_PASSWORD_HASH    => Passwords::hash($password),
				self::COLUMN_EMAIL            => $email,
        self::COLUMN_ID_USER_PROFILES => $user_profiles->id,
        self::COLUMN_ACTIVATED        => $activated,
        self::COLUMN_ID_USER_ROLES    => $role,
        self::COLUMN_CREATED          => StrFTime("%Y-%m-%d %H:%M:%S", Time()),
			]);
		} catch (Nette\Database\UniqueConstraintViolationException $e) {
//      $message = explode("key", $e->getMessage());
      throw new DuplicateEmailException($e->getMessage());//$message[1]);
		}
	}
  
  /**
   * @param Nette\Utils\ArrayHash $values
   * @return Nette\Database\Table\ActiveRow|FALSE
   * @throws Nette\Database\DriverException */
  public function saveUser($values) {
    try {
      $id = $values->id;
      if (!$values->banned) {
        $values->offsetSet("ban_reason", NULL);
      }
      unset($values->id);
      return $this->uloz($values, $id);
    } catch (Exception $e) {
      throw new Database\DriverException('Chyba ulozenia: '.$e->getMessage());
    }
  }
  
  /**
   * Funkcia pre formulár na zostavenie zoznamu všetkých užívateľov
   * @return array Pole uzivatelov vo formate: id => "meno priezvisko" */
  public function uzivateliaForm() {
    $u = $this->findAll();
    $out = [];
    foreach ($u as $v) {
    $out[$v->{self::COLUMN_ID}] = $v->{self::COLUMN_MENO}." ".$v->{self::COLUMN_PRIEZVISKO};
    }
    return $out;
  }
  
  /**
   * Funkcia na zostavenie ratazca emailov podla urovne registracie pre odoslanie info mailu. 
   * @param int $id_user_roles Minimalna uroven registracie
   * @return sring Retazec emailov oddelených ciarkami */
  public function emailUsersListStr($id_user_roles = 5) {
    $cl = $this->findBy(['id_user_roles >='.$id_user_roles, 'user_profiles.news'=>'A']);
    $out = "";
    $sum = count($cl); $iter = 0;
    foreach ($cl as $c) {
      $iter++;
      $out .= $sum == $iter ? $c->email : $c->email.', '; 
    }
    return $out;
  }
  
}

class DuplicateEmailException extends \Exception
{}