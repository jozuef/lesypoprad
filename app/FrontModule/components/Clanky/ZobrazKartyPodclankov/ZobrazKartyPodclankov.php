<?php
namespace App\FrontModule\Components\Clanky\ZobrazKartyPodclankov;
use Nette;
use DbTable;
use Language_support;

/**
 * Komponenta pre zobrazenie odkazu na iny clanok
 * Posledna zmena(last change): 06.07.2017
 * 
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com> 
 * @copyright Copyright (c) 2012 - 2016 Ing. Peter VOJTECH ml.
 * @license
 * @link http://petak23.echo-msz.eu
 * @version 1.0.6
 */
class ZobrazKartyPodclankovControl extends Nette\Application\UI\Control {

  /** @var Language_support\Clanky */
	public $texts;
  /** @var DbTable\Hlavne_menu_lang */
	public $hlavne_menu_lang;
    /** @var DbTable\Dokumenty */
	public $dokumenty;
  /** @var Nette\Database\Table\Selection */
  protected $articles;
  private $kotva = "";


  /**
   * @param DbTable\Hlavne_menu_lang $hlavne_menu_lang
   * @param Language_support\Clanky $texts */
  public function __construct(DbTable\Hlavne_menu_lang $hlavne_menu_lang, DbTable\Dokumenty $dokumenty, Language_support\Clanky $texts) {
    parent::__construct();
    $this->hlavne_menu_lang = $hlavne_menu_lang;
    $this->dokumenty = $dokumenty;
    $this->texts = $texts;
  }

  /** 
   * Nacitanie zobrazovanych podclankov
   * @param int $id nadradeneho clanku
   * @param int $id_lang id zobrazovaneho jazyka
   * @return \App\FrontModule\Components\Clanky\ZobrazKartyPodclankov\ZobrazKartyPodclankovControl */
  public function setArticle($id, $id_lang = 1, $kotva = "") {
    $this->texts->setLanguage($id_lang);
    $this->articles = $this->hlavne_menu_lang->findBy(["id_lang"=>$id_lang, "hlavne_menu.id_nadradenej"=>$id]);
    $this->kotva = $kotva;
    return $this;
  }

  /** 
   * Render
   * @param array $p Parametre: template - pouzita sablona
   * @see Nette\Application\Control#render() */
  public function render($p = []) {
    $this->template->setFile(__DIR__ . "/ZobrazKartyPodclankov".(isset($p["template"]) && strlen($p["template"]) ? "_".$p["template"] : "_default").".latte");
    $this->template->texty = $this->texts;
    $this->template->articles = $this->articles;
    $this->template->kotva = $this->kotva;
    $this->template->dokumenty = $this->dokumenty;
    $this->template->render();
  }
}

interface IZobrazKartyPodclankovControl {
  /** @return ZobrazKartyPodclankovControl */
  function create();
}