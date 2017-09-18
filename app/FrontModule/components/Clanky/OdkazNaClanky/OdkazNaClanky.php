<?php
namespace App\FrontModule\Components\Clanky\OdkazNaClanky;
use Nette;
use DbTable;
use Language_support;

/**
 * Komponenta pre zobrazenie odkazu na iny clanok
 * Posledna zmena(last change): 26.06.2017
 * 
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com> 
 * @copyright Copyright (c) 2012 - 2016 Ing. Peter VOJTECH ml.
 * @license
 * @link http://petak23.echo-msz.eu
 * @version 1.0.5
 */
class OdkazNaClankyControl extends Nette\Application\UI\Control {
  
  /** @var int Id aktualneho jazyka  */
  private $language_id = "Front";
  /** @var Language_support\Clanky */
	public $texts;
  
  /** @var DbTable\Hlavne_menu_lang */
	public $hlavne_menu_lang;
  /** @var DbTable\Lang */
	public $lang;
  
  protected $article;
  
  /** @param DbTable\Hlavne_menu_lang $hlavne_menu_lang */   
  public function __construct(DbTable\Hlavne_menu_lang $hlavne_menu_lang, DbTable\Lang $lang, Language_support\Clanky $texts) {
    parent::__construct();
    $this->hlavne_menu_lang = $hlavne_menu_lang;
    $this->lang = $lang;
    $this->texts = $texts;
  }

  /** 
   * Nastavenie zobrazovaneho clanku
   * @param int $id zobrazovaneho clanku
   * @param int $id_lang id zobrazovaneho jazyka
   * @return \App\FrontModule\Components\Clanky\OdkazNaClanky\OdkazNaClankyControl */
  public function setArticle($id, $id_lang = 1) {
    $this->texts->setLanguage($this->lang->find($id_lang)->skratka);
    $this->article = $this->hlavne_menu_lang->findOneBy(["id_lang"=>$id_lang, "id_hlavne_menu"=>$id]);
    return $this;
  }

  /** 
   * Render funkcia pre vypisanie odkazu na clanok 
   * @param array $p Parametre: id_hlavne_menu - id odkazovaneho clanku, template - pouzita sablona
   * @see Nette\Application\Control#render() */
  public function render($p = []) {
//    if ($p["id_hlavne_menu"]) { //Mam id_clanok
//      $pom_hlm = $this->hlavne_menu_lang->findOneBy(["id_lang"=>$this->language_id, "id_hlavne_menu"=>$p["id_hlavne_menu"]]);
//      if ($pom_hlm === FALSE) { $chyba = "hlavne_menu_lang = ".$p["id_hlavne_menu"]; }
//    } else { //Nemam id_clanok
//      $chyba = "id_hlavne_menu";
//    }
    if (isset($chyba)) { //Je nejaka chyba
      $this->template->setFile(__DIR__ . '/OdkazNaClanky_error.latte');
      $this->template->text = sprintf($this->texty['not_found'], $chyba);
    } else { //Vsetko je OK
			$p_hlm = $this->article->hlavne_menu; //Pre skratenie zapisu
      $this->template->setFile(__DIR__ . "/OdkazNaClanky".(isset($p["template"]) && strlen($p["template"]) ? "_".$p["template"] : "_default").".latte");
      $this->template->article = $this->article;
      $this->template->nazov = $this->article->menu_name;
      $this->template->datum_platnosti = $p_hlm->datum_platnosti;
      $this->template->avatar = $p_hlm->avatar;
      $this->template->anotacia = isset($this->article->id_clanok_lang) ? $this->article->clanok_lang->anotacia : NULL;
      $this->template->texty = $this->texts;
			$this->template->link_presenter = $p_hlm->druh->presenter == "Menu" ? "Clanky:" : $p_hlm->druh->presenter.":";
      $this->template->id_hlavne_menu = $this->article->id_hlavne_menu;;
      $this->template->absolutna = $p_hlm->absolutna;
    }
    $this->template->render();
  }
}

interface IOdkazNaClankyControl {
  /** @return OdkazNaClankyControl */
  function create();
}