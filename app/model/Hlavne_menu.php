<?phpnamespace DbTable;use Nette;use Nette\Utils\Random;use Nette\Utils\Image;/** * Model, ktory sa stara o tabulku hlavne_menu a hlavne_menu_lang *  * Posledna zmena 23.06.2017 *  * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com> * @copyright  Copyright (c) 2012 - 2017 Ing. Peter VOJTECH ml. * @license * @link       http://petak23.echo-msz.eu * @version    1.1.0 */class Hlavne_menu extends Table {  /** @var string */  protected $tableName = 'hlavne_menu';    /** @var Nette\Database\Table\Selection */	protected $hlavne_menu_lang;    /** @var Nette\Database\Table\Selection */	protected $hlavne_menu_cast;         public function __construct(Nette\Database\Context $db)  {    parent::__construct($db);		$this->hlavne_menu_lang = $this->connection->table("hlavne_menu_lang");    $this->hlavne_menu_cast = $this->connection->table("hlavne_menu_cast");	}    /**    * Funkcia hlada ci polozka s Id ma podradene polozky   * @param int $id Id polozky, pre ktoru hladam podradenu   * @return boolean */  public function maPodradenu($id) {    return count($this->findBy(["id_nadradenej"=>$id])) ? TRUE : FALSE;  }	  /** Vypis menu pre Front modul   * @param int $id_reg Min. uroven registrácie   * @param int $lang_id Id zobrazovaneho jazyka   * @return array|FALSE   */	public function getMenuFront($id_reg = 0, $lang_id = 1) {	    $h = clone $this->hlavne_menu_lang;		$s = $h->where("hlavne_menu.id_user_roles <= ?", $id_reg)                          ->where("id_lang", $lang_id)                          ->where("hlavne_menu.druh.modul IS NULL OR hlavne_menu.druh.modul = ?", "Front");    $polozky = $s->order('hlavne_menu.id_hlavne_menu_cast, hlavne_menu.uroven, hlavne_menu.poradie ASC');    return ($polozky !== FALSE && count($polozky)) ? $this->_getMenuFront($polozky) : FALSE;  }    /**    * Vytvorenie menu pre front   * @param Nette\Database\Table\Selection $polozky Vyber poloziek hl. menu   * @param string $abs_link Absolutna cast odkazu   * @return array|FALSE */  private function _getMenuFront($polozky, $abs_link = "") {    $out = [];		$cislo_casti = 0;    foreach ($polozky as $ja) {      $v = $ja->hlavne_menu;      //Mam taku istu cast ako pred tym? Ak nie nastav cislo casti, ale len ak je to dovolene cez $casti      if ($cislo_casti !== $v->id_hlavne_menu_cast) { //Len jeden prechod cez toto a to na začiatku				$cislo_casti = $v->id_hlavne_menu_cast;        $temp_pol = new \App\FrontModule\Components\Menu\MenuNode;        $temp_pol->name = $v->hlavne_menu_cast->nazov;        $temp_pol->link = $abs_link."Homepage:";        $temp_pol->id = -1*$v->hlavne_menu_cast->id;        $out[] = ["node"=>$temp_pol, "nadradena"=>FALSE];        unset($temp_pol);      }      $for_link = $abs_link.($v->druh->presenter == "Menu" ? "Clanky" : $v->druh->presenter).":";      $temp_pol = new \App\FrontModule\Components\Menu\MenuNode;      $temp_pol->name = $ja->menu_name;      $temp_pol->tooltip = $ja->h1part2;      $temp_pol->view_name = $ja->view_name;      $temp_pol->avatar = $v->avatar;      $temp_pol->anotacia = ($v->druh->presenter == "Clanky" && isset($ja->clanok_lang->anotacia)) ? $ja->clanok_lang->anotacia : FALSE;      $temp_pol->node_class = ($v->ikonka !== NULL && strlen($v->ikonka)>2) ? "fa fa-".$v->ikonka : NULL;      $temp_pol->link = $v->druh->je_spec_naz ? [$for_link] : $for_link;      $temp_pol->absolutna = $v->absolutna;      $temp_pol->novinka = $v->id_dlzka_novinky > 1 ? $v->modified->add(new \DateInterval('P'.$v->dlzka_novinky->dlzka.'D')) : NULL;      $temp_pol->id = $v->id;      $temp_pol->poradie_podclankov = $v->poradie_podclankov;      $out[] = ["node"=>$temp_pol, "nadradena"=>isset($v->id_nadradenej) ? $v->id_nadradenej : -1*$v->hlavne_menu_cast->id];      unset($temp_pol);    }    return $out;  }    /**    * Vypis menu pre mapu webu   * @param int $lang_id Id zobrazovaneho jazyka   * @return array|FALSE */  public function getMenuMapa($lang_id) {			$polozky = $this->hlavne_menu_lang->where("hlavne_menu.id_user_roles", 0)                    ->where("id_lang", $lang_id)                    ->order('hlavne_menu.id_hlavne_menu_cast, hlavne_menu.uroven, hlavne_menu.poradie ASC');    return ($polozky !== FALSE && count($polozky)) ? $this->_getMenuMapa($polozky) : FALSE;  }  /**    * Vytvorenie menu pre mapu   * @param Nette\Database\Table\Selection $polozky Vyber poloziek hl. menu   * @return array|FALSE */  private function _getMenuMapa($polozky) {    $out = [];		$cislo_casti = 0;    foreach ($polozky as $ja) {      $v = $ja->hlavne_menu;      //Mam taku istu cast ako pred tym? Ak nie nastav cislo casti, ale len ak je to dovolene cez $casti      if ($cislo_casti !== $v->id_hlavne_menu_cast) { //Len jeden prechod cez toto a to na začiatku				$cislo_casti = $v->id_hlavne_menu_cast;        $temp_pol = new \App\FrontModule\Components\Menu\MenuNode;        $temp_pol->link = "//:Front:Homepage:";        $temp_pol->id = -1*$v->hlavne_menu_cast->id;        $out[] = ["node"=>$temp_pol, "nadradena"=>FALSE];        unset($temp_pol);      }      $for_link = "//:Front:".($v->druh->presenter == "Menu" ? "Clanky" : $v->druh->presenter).":";      $temp_pol = new \App\FrontModule\Components\Menu\MenuNode;      $temp_pol->link = $v->druh->je_spec_naz ? [$for_link] : $for_link;      $temp_pol->absolutna = $v->absolutna;      $temp_pol->id = $v->id;      $out[] = ["node"=>$temp_pol, "nadradena"=>isset($v->id_nadradenej) ? $v->id_nadradenej : -1*$v->hlavne_menu_cast->id];      unset($temp_pol);    }    return $out;  }  /** Vypis menu pre Admin modul   * @param int $id_reg         Min. id registrácie   * @param type $lang_id       Id jazyka   * @return array|FALSE   */  public function getMenuAdmin($id_reg, $lang_id = 1) {	    $polozky = $this->hlavne_menu_lang->where("hlavne_menu.id_user_roles <= ?", $id_reg)                          ->where("id_lang", $lang_id)                          ->where("hlavne_menu.druh.modul IS NULL OR hlavne_menu.druh.modul = ?", "Admin")                          ->order('hlavne_menu.id_hlavne_menu_cast, hlavne_menu.uroven, hlavne_menu.poradie ASC');    return ($polozky !== FALSE && count($polozky)) ? $this->_getMenuAdmin($polozky) : FALSE;  }    /** Vytvorenie menu pre administraciu   * @param Nette\Database\Table\Selection $polozky Vyber poloziek hl. menu   * @return array|FALSE   */  private function _getMenuAdmin($polozky) {    $cislo_casti = 0; //aktualne cislo casti    $casti = [];    $out = [];    foreach ($polozky as $ja) {      $v = $ja->hlavne_menu;      //Mam taku istu cast ako pred tym? Ak nie nastav cislo casti, ale len ak je to dovolene cez $casti      if ($cislo_casti !== $v->id_hlavne_menu_cast) { //Mam taku istu cast ako pred tym? Ak nie nastav cislo casti        $cislo_casti = $v->id_hlavne_menu_cast;        $casti[] = $cislo_casti;        $temp_pol = new \App\AdminModule\Components\Menu\MenuNode;        $temp_pol->name = $v->hlavne_menu_cast->nazov;        $temp_pol->link = ["Homepage:"];        $temp_pol->id = -1*$v->hlavne_menu_cast->id;        $out[] = ["node"=>$temp_pol, "nadradena"=>FALSE];        unset($temp_pol);      }      $temp_pol = new \App\AdminModule\Components\Menu\MenuNode;      $temp_pol->name = $ja->menu_name;      $temp_pol->tooltip = $ja->h1part2;      $temp_pol->view_name = $ja->view_name;      $temp_pol->avatar = $v->avatar;      $temp_pol->anotacia = ($v->druh->presenter == "Clanky" && isset($ja->clanok_lang->anotacia)) ? $ja->clanok_lang->anotacia : FALSE;      $temp_pol->node_class = ($v->ikonka !== NULL && strlen($v->ikonka)>2) ? "fa fa-".$v->ikonka : NULL;      $temp_pol->link = $v->druh->je_spec_naz ? [$v->druh->presenter.":"] : $v->druh->presenter.":";      $temp_pol->id = $v->id;      $temp_pol->poradie_podclankov = $v->poradie_podclankov;      $temp_pol->datum_platnosti = $v->datum_platnosti;      $out[] = ["node"=>$temp_pol, "nadradena"=>isset($v->id_nadradenej) ? $v->id_nadradenej : -1*$v->hlavne_menu_cast->id];      unset($temp_pol);    }    $c = $this->hlavne_menu_cast->fetchPairs("id");    if (count($casti) !== count($c)) {      foreach ($c as $v) {        if (array_search($v->id, $casti) === FALSE) {          $temp_pol = new \App\AdminModule\Components\Menu\MenuNode;          $temp_pol->name = $v->nazov;          $temp_pol->link = ["Homepage:"];          $temp_pol->id = -1*$v->id;          $out[] = ["node"=>$temp_pol, "nadradena"=>FALSE];          unset($temp_pol);        }      }     }    return $out;  }    /**   * Funkcia pre zmenu vlastníka   * @param Nette\Utils\ArrayHash $values udaje   * @return Nette\Database\Table\ActiveRow|FALSE */  public function zmenVlastnika($values) {    return $this->uloz(["id_user_main" => $values->id_user_main], $values->id);  }    /**   * Funkcia pre zmenu urovne registracie polozky   * @param Nette\Utils\ArrayHash $values udaje   * @return Nette\Database\Table\ActiveRow|FALSE   */  public function zmenUrovenRegistracie($values) {    return $this->uloz(["id_user_roles" => $values->id_user_roles], $values->id);  }    /**   * Funkcia pre zmenu urovne registracie polozky   * @param Nette\Utils\ArrayHash $values udaje   * @return Nette\Database\Table\ActiveRow|FALSE */  public function zmenDatumPlatnosti($values) {    return $this->uloz(["datum_platnosti" => $values->platnost == 1 ? $values->datum_platnosti : NULL], $values->id);  }    /**   * Funkcia pre zmenu dlzky sledovania ako novinky polozky   * @param Nette\Utils\ArrayHash $values udaje   * @return Nette\Database\Table\ActiveRow|FALSE */  public function zmenDlzkuNovinky($values) {    return $this->uloz(["id_dlzka_novinky" => $values->id_dlzka_novinky], $values->id);  }    /**   * Funkcia pre zmenu opravnenia nevlastnikov polozky   * @param Nette\Utils\ArrayHash $values udaje   * @return Nette\Database\Table\ActiveRow|FALSE */  public function zmenOpravnenieNevlastnikov($values) {    return $this->uloz(["id_hlavne_menu_opravnenie" => $values->id_hlavne_menu_opravnenie], $values->id);  }    /**   * Ulozenie titulneho obrazku alebo ikonky   * @param Nette\Utils\ArrayHash $values   * @param string $avatar_path   * @param string $www_dir   * @throws Database\DriverException */  public function zmenTitleImage($values, $avatar_path, $www_dir) {    if (!$values->avatar->error) {      if ($values->avatar->isImage()){         $values->avatar = $this->_uploadTitleImage($values->avatar, $www_dir."/www/".$avatar_path);        $this->uloz(["ikonka"=>NULL, "avatar"=>$values->avatar], $values->id);      } else {        throw new Database\DriverException('Pre titulný obrázok nebol použitý obrázok a tak nebol uložený!'.$e->getMessage());      }    } elseif ($values->ikonka){      $this->_delAvatar($values->old_avatar, $avatar_path, $www_dir);      $this->uloz(["ikonka"=>$values->ikonka, "avatar"=>NULL], $values->id);    } else {       throw new Database\DriverException('Pri pokuse o uloženie došlo k chybe! Pravdepodobná príčina je č.'.$values->avatar->error.". ".$e->getMessage());    }  }    /**   * Zmazanie titulneho obrazku a/alebo ikonky   * @param type $id   * @param string $avatar_path   * @param string $www_dir   * @return Nette\Database\Table\ActiveRow|FALSE */  public function zmazTitleImage($id, $avatar_path, $www_dir) {    $hl = $this->find($id);    $this->_delAvatar($hl->avatar, $avatar_path, $www_dir);    return $this->uloz(["ikonka"=>NULL, "avatar"=>NULL], $id);  }    /**   * @param Nette\Http\FileUpload $avatar   * @param string $path   * @return string */  private function _uploadTitleImage(Nette\Http\FileUpload $avatar, $path) {    $pi = pathinfo($avatar->getSanitizedName());    $ext = $pi['extension'];    $avatar_name = Random::generate(15).".".$ext;    $avatar->move($path.$avatar_name);    $image = Image::fromFile($path.$avatar_name);    $image->save($path.$avatar_name, 75);    return $avatar_name;  }    /**   * @param string $avatar_name   * @param string $avatar_path   * @param string $www_dir */  private function _delAvatar($avatar_name, $avatar_path, $www_dir) {    if ($avatar_name !== NULL && is_file("www/".$avatar_path.$avatar_name)) {       unlink($www_dir."/www/".$avatar_path.$avatar_name);    }  }    /** Upravy hodnoty a ulozi polozku   * @param \Nette\Utils\ArrayHash $values   * @return \Nette\Database\Table\ActiveRow|FALSE */  public function ulozPolozku($values) {    return $this->uloz([      'id_druh'             => $values->id_druh,      'id_user_main'        => $values->id_user_main,      'spec_nazov'          => $values->id ? $values->spec_nazov : $this->najdiSpecNazov($values->sk_menu_name),      'id_hlavne_menu_cast' => $values->id_hlavne_menu_cast,      'uroven'              => $values->uroven,      'id_nadradenej'       => isset($values->id_nadradenej) && (int)$values->id_nadradenej > 0 ? $values->id_nadradenej : NULL,      'nazov_ul_sub'        => isset($values->nazov_ul_sub) && strlen($values->nazov_ul_sub) > 1 ? $values->nazov_ul_sub : NULL,      'uroven'              => $values->uroven,      'id_hlavicka'         => $values->id_hlavicka,      'poradie'             => $values->poradie,      'absolutna'           => isset($values->absolutna) && strlen($values->absolutna) > 7 ? $values->absolutna : NULL,    ], $values->id);  }}