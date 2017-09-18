<?php
namespace App\FrontModule\Presenters;

use Nette\Application\UI\Multiplier;
use Nette\Utils\Strings;
use Nette\Http;
use Nette\Application\UI;
use DbTable;
use PeterVojtech;

/**
 * Zakladny presenter pre vsetky presentery vo FRONT module
 * 
 * Posledna zmena(last change): 13.07.2017
 *
 *	Modul: FRONT
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright Copyright (c) 2012 - 2017 Ing. Peter VOJTECH ml.
 * @license
 * @link      http://petak23.echo-msz.eu
 * @version 1.3.5
 */
\Nette\Forms\Container::extensionMethod('addDatePicker', function (\Nette\Forms\Container $container, $name, $label = NULL) {
    return $container[$name] = new \JanTvrdik\Components\DatePicker($label);
});

abstract class BasePresenter extends UI\Presenter {

  // -- DB
  /** @var DbTable\Dokumenty @inject */
	public $dokumenty;
  /** @var DbTable\Druh @inject */
	public $druh;
  /** @var DbTable\Hlavne_menu @inject */
	public $hlavne_menu;
  /** @var DbTable\Hlavne_menu_cast @inject */
  public $hlavne_menu_cast;
  /** @var DbTable\Hlavne_menu_lang @inject*/
  public $hlavne_menu_lang;
  /** @var DbTable\Lang @inject*/
	public $lang;
  /** @var DbTable\User_roles @inject */
	public $user_roles;
  /** @var DbTable\Udaje @inject */
	public $udaje;
  /** @var DbTable\User_main @inject */
	public $user_main;
  /** @var DbTable\Verzie @inject */
	public $verzie;

  /** @var mix */
  public $texty_presentera;
  
  // -- Komponenty
  /** @var \App\FrontModule\Components\Oznam\IAktualneOznamyControl @inject */
  public $aktualneOznamyControlFactory;
  /** @var \App\FrontModule\Components\Slider\ISliderControl @inject */
  public $sliderControlFactory;
  /** @var \App\FrontModule\Components\User\IUserLangMenuControl @inject */
  public $userLangMenuControlFactory;
  /** @var \App\FrontModule\Components\Clanky\OdkazNaClanky\IOdkazNaClankyControl @inject */
  public $odkazNaClankyControlFactory;
  /** @var \App\FrontModule\Components\News\INewsControl @inject */
  public $newsControlFactory;


  // -- Premenne z o stareho CommonBasePresentera
  /** @persistent */
  public $language = 'sk';
  /** @persistent */
  public $backlink = '';
	
  /** @var Http\Request @inject*/
  public $httpRequest;
  
  /** @var \WebLoader\Nette\LoaderFactory @inject */
  public $webLoader;

  /** @var string kmenovy nazov stranky pre rozne ucely typu www.neco.sk*/
  public $nazov_stranky;
  /** @var int Uroven registracie uzivatela  */
	public $id_reg;
  /** @var int Maximalna uroven registracie uzivatela */
	public $max_id_reg = 0;
  
  /** @var array Pole s hlavnymi udajmi webu */
  public $udaje_webu;

  /** @var int */
  public $language_id = 1;
  
  /** @var array nastavenie z config-u */
  public $nastavenie;
	/** @var string - relatívna cesta pre avatar poloziek menu */
	public $avatar_path = "files/menu/";
  /** @var int Maximalna velkost suboru pre upload */
  public $upload_size = 0;
  
  /** Vratenie textu pre dany kluc a jazyk
   * @param string $key - kluc daneho textu
   * @return string - hodnota pre dany text
   */
  public function trLang($key) {
    return ($this->texty_presentera == NULL) ? $key : $this->texty_presentera->trText($key);
  }

	protected function startup() {
    parent::startup();
    // Sprava uzivatela
    $user = $this->getUser(); //Nacitanie uzivatela
    // Kontrola prihlasenia a nacitania urovne registracie
    $this->id_reg = ($user->isLoggedIn()) ? $user->getIdentity()->id_user_roles : 0;
    // Nastavenie z config-u
    $this->nastavenie = $this->context->parameters;
    $modul_presenter = explode(":", $this->name);
    // Skontroluj ci je nastaveny jazyk a ci pozadovany jazyk existuje ak ano akceptuj
    if (!isset($this->language)) {//Prednastavim hodnotu jazyka
      $lang_temp = $this->lang->find(1);
      $this->language = $lang_temp->skratka; 
      $this->language_id = $lang_temp->id;
    }
    if (isset($this->params['language'])) {
      $lang_temp = $this->lang->findOneBy(['skratka'=>$this->params['language']]);
      if(isset($lang_temp->skratka) && $lang_temp->skratka == $this->params['language']) {
        $this->language = $this->params['language'];
        $this->language_id = $lang_temp->id;
      } else { //Inak nastav Slovencinu
        $this->language = 'sk';
        $this->language_id = 1;
      }
    } 
    //Nacitanie a spracovanie hlavnych udajov webu
    $this->udaje_webu = $this->udaje->findAll()->fetchPairs('nazov', 'text');
    $vysledok = [];
    //Nacitanie len tych premennych, ktore platia pre danu jazykovu mutaciu
    foreach ($this->udaje_webu as $key => $value) { 
      $kluc = explode("-", $key);
      if (count($kluc) == 2 && $kluc[1] == $this->language) { $vysledok[substr($key, 0, strlen($key)-strlen($this->language)-1)] = $value; } 
      if (count($kluc) == 1) {$vysledok[$key] = $value;}
    }
    $this->udaje_webu = $vysledok;
    // Nacitanie pomocnych premennych
    $this->udaje_webu['meno_presentera'] = strtolower($modul_presenter[1]); //Meno aktualneho presentera
    $httpR = $this->httpRequest->getUrl();
    $this->nazov_stranky = $httpR->host.$httpR->scriptPath; // Nazov stranky v tvare www.nieco.sk
    $this->nazov_stranky = substr($this->nazov_stranky, 0, strlen($this->nazov_stranky)-1);
    // Priradenie hlavnych parametrov a udajov
    $this->max_id_reg = $this->user_roles->findAll()->max('id');//Najdi max. ur. reg.
    //Najdi info o druhu
    $tmp_druh = $this->druh->findBy(["druh.presenter"=>ucfirst($this->udaje_webu['meno_presentera'])])
                           ->where("druh.modul IS NULL OR druh.modul = ?", $modul_presenter[0])->limit(1)->fetch();
    if ($tmp_druh !== FALSE) {
      if ($tmp_druh->je_spec_naz) { //Ak je spec_nazov pozadovany a mam id
        $hl_udaje = $this->hlavne_menu->hladaj_id(isset($this->params['id']) ? (int)trim($this->params['id']) : 0, $this->id_reg);
      } else {//Ak nie je spec_nazov pozadovany
        $hl_udaje = $this->hlavne_menu->findOneBy(["id_druh"=>$tmp_druh->id]);
      }
    } else { $hl_udaje = FALSE; }
    if ($hl_udaje !== FALSE) { //Ak sa hl. udaje nasli
      //Nacitanie textov hl_udaje pre dany jazyk 
      $lang_hl_udaje = $this->hlavne_menu_lang->findOneBy(['id_lang'=>$this->language_id, 
                                                           'id_hlavne_menu'=>$hl_udaje->id]);
      if ($lang_hl_udaje !== FALSE){ //Nasiel som udaje a tak aktualizujem
        $this->udaje_webu["nazov"] = $lang_hl_udaje->menu_name;
        $this->udaje_webu["h1part2"] = $lang_hl_udaje->h1part2;
        $this->udaje_webu["description"] = $lang_hl_udaje->view_name;
      } else { //Len preto aby tam nieco bolo
        $this->udaje_webu["nazov"] = "Error nazov";
        $this->udaje_webu["h1part2"] = "Error h1part2";
        $this->udaje_webu["description"] = "Error description";
      }
      $this->udaje_webu['hl_udaje'] = $hl_udaje->toArray();
    } else { //Len preto aby tam nieco bolo
      $this->udaje_webu["description"] = "Nenájdená stránka";
      $this->udaje_webu['hl_udaje'] = FALSE;
    }
    //Vypocet max. velkosti suboru pre upload
    $ini_v = trim(ini_get("upload_max_filesize"));
    $s = ['g'=> 1<<30, 'm' => 1<<20, 'k' => 1<<10];
    $this->upload_size =  intval($ini_v) * ($s[strtolower(substr($ini_v,-1))] ?: 1);
    // -- Povodny: 
    $this->texty_presentera->setLanguage($this->language); //Nastavenie textov podla jazyka
	}

  /** Komponenta pre vykreslenie menu
   * @return \App\FrontModule\Components\Menu\Menu
   */
  public function createComponentMenu() {
    $menu = new \App\FrontModule\Components\Menu\Menu;
    $menu->setTextTitleImage($this->trLang("base_text_title_image"));
    $hl_m = $this->hlavne_menu->getMenuFront($this->id_reg, $this->language_id);
    if ($hl_m !== FALSE) {
      $servise = $this;
      $menu->fromTable($hl_m, function($node, $row) use($servise) {
        $poll = ["id", "name", "tooltip", "view_name", "avatar", "anotacia", "novinka", "node_class", "poradie_podclankov"];
        foreach ($poll as $v) { $node->$v = $row['node']->$v; }
        // Nasledujuca cast priradi do $node->link odkaz podla kriteria:
        // Ak $rna == NULL - vytvori link ako odkaz do aplikacie
        // Ak $rna zacina "http" - pouzije sa absolutna adresa
        // Ak $rna obsahuje text "Clanky:default 2" - vytvorí sa odkaz do aplikácie na clanok s id 2 - moze byt aj bez casti ":2" odkazu ale musí byť aj default
        $rna = $row['node']->absolutna;
        if ($rna !== NULL) {
          $node->link = strpos($rna, 'http') !== FALSE ? $rna 
                                                       : (count($p = explode(" ", $rna)) == 2 ? $servise->link($p[0], ["id"=>$p[1]]) 
                                                                                              : $servise->link($p[0]));
        } else {
          $node->link = is_array($row['node']->link) ? $servise->link($row['node']->link[0], ["id"=>$row['node']->id]) 
                                                     : $servise->link($row['node']->link);
        }
        return $row['nadradena'] ? $row['nadradena'] : null;
      });
    }
    return $menu;
  }
    
  /** Naplnenie spolocnych udajov pre sablony */
  public function beforeRender() {
    $this->getComponent('menu')->selectByUrl($this->link('this'));
    $this->template->udaje = $this->udaje_webu;
		$this->template->verzia = $this->verzie->posledna();
		$this->template->urovregistr = $this->id_reg;
    $this->template->maxurovregistr = $this->max_id_reg;
    $this->template->language = $this->language;
    $this->template->user_admin = $this->user_main->findOneBy(['user_roles.role'=>'admin']);
    $this->template->user_spravca = $this->user_main->findOneBy(['user_roles.role'=>'manager']);
    $this->template->nazov_stranky = $this->nazov_stranky;
		$this->template->avatar_path = $this->avatar_path;
    $this->template->text_title_image = $this->trLang("base_text_title_image");
		$this->template->article_avatar_view_in = $this->nastavenie["article_avatar_view_in"];
    $this->template->omrvinky_enabled = $this->nastavenie["omrvinky_enabled"];
    $this->template->view_log_in_link_in_header = $this->nastavenie['user_panel']["view_log_in_link_in_header"];
	}
  
  /** Signal pre odhlasenie sa */
	public function handleSignOut() {
		$this->getUser()->logout(TRUE);
    $this->id_reg = 0;
		$this->flashRedirect('Homepage:', $this->trLang('base_log_out_mess'), 'success');
	}

  /** 
   * Signal prepinania jazykov
   * @param string $language skratka noveho jazyka */
  public function handleSetLang($language) {
    if ($this->language != $language) { //Cokolvek rob len ak sa meni
      //Najdi v DB pozadovany jazyk
      $la_tmp = $this->lang->findOneBy(['skratka'=>$language]);
      //Ak existuje tak akceptuj
      if (isset($la_tmp->skratka) && $la_tmp->skratka == $language) { $this->language = $language; }
    }
    $this->redirect('this');
	}
  
  /** @return CssLoader */
  protected function createComponentCss(){
    return $this->webLoader->createCssLoader('front');
  }

  /** @return JavaScriptLoader */
  protected function createComponentJsBefore(){
    return $this->webLoader->createJavaScriptLoader('frontBefore');
  }
  
  /** @return JavaScriptLoader */
  protected function createComponentJsAfter(){
    return $this->webLoader->createJavaScriptLoader('frontAfter');
  }
  
  /** Komponenta pre výpis css a js súborov
   * @return \PeterVojtech\Base\CssJsFilesControl */
  public function createComponentFiles() {
    return new PeterVojtech\Base\CssJsFilesControl($this->nastavenie['web_files'], $this->name, $this->action);
  }
  
  /**
   * Vytvorenie komponenty pre menu uzivatela a zaroven panel jazykov
   * @return \App\FrontModule\Components\User\UserLangMenu */
  public function createComponentUserLangMenu() {
    return $this->userLangMenuControlFactory->create();
  }

  /** 
   * Vytvorenie komponenty pre potvrdzovaci dialog
   * @return Nette\Application\UI\Form */
  public function createComponentConfirmForm() {
    $form = new \PeterVojtech\Confirm\ConfirmationDialog($this->getSession('news'));
    $form->addConfirmer(
        'delete', // názov signálu bude confirmDelete!
        [$this, 'confirmedDelete'], // callback na funkciu pri kliknutí na YES
        [$this, 'questionDelete'] // otázka
    );
    return $form;
  }
  
  /**
   * Zostavenie otázky pre ConfDialog s parametrom
   * @param Nette\Utils\Html $dialog
   * @param array $params
   * @return string $question
   */
  public function questionDelete($dialog, $params) {
     $dialog->getQuestionPrototype();
     return sprintf($this->trLang('base_delete_text'),
                    isset($params['zdroj_na_zmazanie']) ? $params['zdroj_na_zmazanie'] : "položku",
                    isset($params['nazov']) ? $params['nazov'] : '');
  }
  
  /** Vytvorenie komponenty slideru
   * @return \App\FrontModule\Components\Slider\Slider
   */
	public function createComponentSlider() {
    $slider = $this->sliderControlFactory->create();
    $slider->setNastavenie($this->nastavenie["slider"]);
    return $slider;
	}
  
  /** 
   * Komponenta pre vykreslenie odkazu na clanok s anotaciou
   * @return \App\FrontModule\Components\Clanky\OdkazNaClankyControl */
  public function createComponentOdkazNaClanky() {
    $servise = $this;
		return new Multiplier(function ($id) use ($servise) {
			$odkaz = $servise->odkazNaClankyControlFactory->create();
      $odkaz->setArticle($id, $servise->language_id);
			return $odkaz;
		});
  }
  
  /** Komponenta pre zobrazenie clanku
   * @return \App\FrontModule\Components\Clanky\ZobrazClanok\ZobrazClanokControl
   */
  public function createComponentUkazClanok() {
    $servise = $this;
		return new Multiplier(function ($id) use ($servise) {
      if (is_numeric($id)) {
        $clanok = $servise->hlavne_menu_lang->getOneArticleId($id, $servise->language_id, 0);
      } else {
        $clanok = $servise->hlavne_menu_lang->getOneArticleSp($id, $servise->language_id, 0);
      }
      $ukaz_clanok = New \App\FrontModule\Components\Clanky\ZobrazClanok\ZobrazClanokControl($clanok);
      $ukaz_clanok->setTexts([
        "not_found"         =>$servise->trLang('base_not_found'),
        "platnost_do"       =>$servise->trLang('base_platnost_do'),
        "zadal"             =>$servise->trLang('base_zadal'),
        "zobrazeny"         =>$servise->trLang('base_zobrazeny'),  
        "anotacia"          =>$servise->trLang('base_anotacia'),
        "viac"              =>$servise->trLang('base_viac'),
        "text_title_image"  =>$servise->trLang("base_text_title_image"),
        ]);
      $ukaz_clanok->setClanokHlavicka($servise->udaje_webu['clanok_hlavicka']);
      return $ukaz_clanok;
    });
  }
  
  /** 
   * Vytvorenie komponenty pre vypisanie aktualnych oznamov
   * @return \App\FrontModule\Components\Oznam\AktualneOznamyControl */
	public function createComponentAktualne() {
    $aktualne = $this->aktualneOznamyControlFactory->create();
    $aktualne->setNastavenie($this->context->parameters['oznam']);
    return $aktualne;
	}
  
  /** 
   * Komponenta pre zobrazenie noviniek
   * @return \App\FrontModule\Components\News\INewsControl */
  public function createComponentNews() {
    return $this->newsControlFactory->create();
  }
  
  /** Komponenta pre vypis kontaktneho formulara
   * @return \App\FrontModule\Components\User\Kontakt
   */
	public function createComponentKontakt() {
		$kontakt = New \App\FrontModule\Components\User\Kontakt();
    $kontakt->setSablona([
        'h4'        => $this->trLang('komponent_kontakt_h4'),
        'uvod'      => $this->trLang('komponent_kontakt_uvod'),
        'meno'      => $this->trLang('komponent_kontakt_meno'),
        'email'     => $this->trLang('komponent_kontakt_email'),
        'email_ar'	=> $this->trLang('komponent_kontakt_email_ar'),
        'email_sr'	=> $this->trLang('komponent_kontakt_email_sr'),
        'text'      => $this->trLang('komponent_kontakt_text'),
        'text_sr'   => $this->trLang('komponent_kontakt_text_sr'),
        'uloz'      => $this->trLang('komponent_kontakt_uloz'),
        'send_ok'   => $this->trLang('komponent_kontakt_send_ok'),
        'send_er'   => $this->trLang('komponent_kontakt_send_er') 
      ]);
    $spravca = $this->user_main->findOneBy(["user_roles.role" => "manager"]);
		$kontakt->setSpravca($spravca->email);
    $kontakt->setNazovStranky($this->nazov_stranky);
		return $kontakt;	
	}
  
  /** Funkcia pre zjednodusenie vypisu flash spravy a presmerovania
   * @param array|string $redirect Adresa presmerovania
   * @param string $text Text pre vypis hlasenia
   * @param string $druh - druh hlasenia
   */
  public function flashRedirect($redirect, $text = "", $druh = "info") {
		$this->flashMessage($text, $druh);
    if (is_array($redirect)) {
      if (count($redirect) > 1) {
        if (!$this->isAjax()) {
          $this->redirect($redirect[0], $redirect[1]);
        } else {
          $this->redrawControl();
        }
      } elseif (count($redirect) == 1) { $this->redirect($redirect[0]);}
    } else { 
      if (!$this->isAjax()) { 
        $this->redirect($redirect); 
      } else {
        $this->redrawControl();
      }
    }
	}
  /**
   * Funkcia pre zjednodusenie vypisu flash spravy a presmerovania aj pre chybovy stav
   * @param boolean $ok Podmienka
   * @param array|string $redirect Adresa presmerovania
   * @param string $textOk Text pre vypis hlasenia ak je podmienka splnena
   * @param string $textEr Text pre vypis hlasenia ak NIE je podmienka splnena
   */
  public function flashOut($ok, $redirect, $textOk = "", $textEr = "") {
    if ($ok) {
      $this->flashRedirect($redirect, $textOk, "success");
    } else {
      $this->flashMessage($textEr, 'danger');
    }
  }
  
    /**
   * Vytvorenie spolocnych helperov pre sablony
   * @param type $class
   * @return type
   */
  protected function createTemplate($class = NULL) {
    $servise = $this;
    $template = parent::createTemplate($class);
    $template->addFilter('obr_v_txt', function ($text) use($servise){
      $rozloz = explode("#", $text);
      $serv = $servise->presenter;
      $vysledok = '';
      $cesta = 'http://'.$serv->nazov_stranky."/";
      foreach ($rozloz as $k=>$cast) {
        if (substr($cast, 0, 2) == "I-") {
          $obr = $serv->dokumenty->find((int)substr($cast, 2));
					if ($obr !== FALSE) {
            $cast = \Nette\Utils\Html::el('a class="fotky" rel="fotky"')->href($cesta.$obr->subor)->title($obr->nazov)
                                  ->setHtml(\Nette\Utils\Html::el('img')->src($cesta.$obr->thumb)->alt($obr->nazov));
					}
        }
        $vysledok .= $cast;
      }
      return $vysledok;
    });
    $template->addFilter('koncova_znacka', function ($text) use($servise){
      $rozloz = explode("{end}", $text);
      $vysledok = $text;
			if (count($rozloz)>1) {		 //Ak som nasiel znacku
				$vysledok = $rozloz[0].\Nette\Utils\Html::el('a class="cely_clanok"')->href($servise->link("this"))->title($servise->trLang("base_title"))
                ->setHtml('&gt;&gt;&gt; '.$servise->trLang("base_viac")).'<div class="ostatok">'.$rozloz[1].'</div>';
			}
      return $vysledok;
    });
    $template->addFilter('hlmenuclass', function ($id, $id_user_roles, $hl_udaje) {
    	$polozka_class = $id_user_roles>2 ? 'adminPol' : '';
      //TODO $classPol .= ' zvyrazni';
      if ($id == $hl_udaje) { $polozka_class .= ' active'; }
      return $polozka_class;
    });
    $template->addFilter('nahodne', function ($max) { //Generuje nahodne cislo do template v rozsahu od 0 do max
      return (int)rand(0, $max);
    });
    $template->addFilter('uprav_email', function ($email) { //Upravi email aby sa nedal pouzit ako nema

      return Strings::replace($email, ['~@~' => '[@]', '~\.~' => '[dot]']);
    });
    $template->addFilter('textreg', function ($text, $id_user_roles, $max_id_reg) {
      for ($i = $max_id_reg; $i>=0; $i--) {
        $z_zac = "#REG".$i."#"; //Pociatocna znacka
        $z_alt = "#REG-A".$i."#"; //Alternativna znacka
        $z_kon = "#/REG".$i."#";//Koncova znacka
        if (($p_zac = strpos($text, $z_zac)) !== FALSE && ($p_kon = strpos($text, $z_kon)) !== FALSE && $p_zac < $p_kon) { //Ak som našiel začiatok a koniec a sú v správnom poradí
          $text = substr($text, 0, $p_zac) //Po zaciatocnu zancku
                  .(($p_alt = strpos($text, $z_alt)) === FALSE ? // Je alternativa
                   ($i < $id_user_roles ? substr($text, $p_zac+strlen($z_zac), $p_kon-$p_zac-strlen($z_zac)) : '') : // Bez alternativy
                   ($i < $id_user_roles ? substr($text, $p_zac+strlen($z_zac), $p_alt-$p_zac-strlen($z_zac)) : substr($text, $p_alt+strlen($z_alt), $p_kon-$p_alt-strlen($z_alt))))// S alternativou
                  .substr($text, $p_kon+strlen($z_kon)); //Od koncovej znacky
			  } 
      }
      return $text;
    });
    $template->addFilter('vytvor_odkaz', function ($row) use($servise){
      return isset($row->absolutna) ? $row->absolutna :
                          (isset($row->spec_nazov) ? $servise->link($row->druh->presenter.':default',$row->spec_nazov)
                                                   : $servise->link($row->druh->presenter.':default'));
    });
    $template->addFilter('menu_mutacia_nazov', function ($id) use($servise){
      $pom = $servise->hlavne_menu_lang->findOneBy(['id_hlavne_menu'=>$id, 'id_lang'=>$servise->language_id]);
      return $pom !== FALSE ? $pom->nazov : $id;
    });
    $template->addFilter('menu_mutacia_title', function ($id) use($servise){
      $pom = $servise->hlavne_menu_lang->findOneBy(['id_hlavne_menu'=>$id, 'id_lang'=>$servise->language_id]);
      return $pom !== FALSE ? ((isset($pom->view_name) && strlen ($pom->view_name)) ? $pom->view_name : $pom->menu_name) : $id;
    });
    $template->addFilter('menu_mutacia_h1part2', function ($id) use($servise){
      $pom = $servise->hlavne_menu_lang->findOneBy(['id_hlavne_menu'=>$id, 'id_lang'=>$servise->language_id]);
      return $pom !== FALSE ? $pom->h1part2 : $id;
    });
    $template->addFilter('trLang', function ($key) use($servise){
      if ($servise->texty_presentera == NULL) { return $key; }
      return ($servise->user->isInRole("Admin")) ? $key."-".$servise->texty_presentera->trText($key) : $servise->texty_presentera->trText($key);
    });
    $template->addFilter('nadpisH1', function ($key){
      $out = "";
      foreach (explode(" ", $key) as $v) {
        $out .= "<div>".$v." </div>";
      }
      return $out;
    }); 
    return $template;
	}
  
  /**
   * Nastavenie vzhľadu formulara
   * @param \Nette\Application\UI\Form $form
   * @return \Nette\Application\UI\Form */
  public function _vzhladForm($form) {
    $renderer = $form->getRenderer();
    $renderer->wrappers['error']['container'] = 'div class="row"';
    $renderer->wrappers['error']['item'] = 'div class="col-md-6 col-md-offset-3 alert alert-danger"';
    $renderer->wrappers['controls']['container'] = NULL;
    $renderer->wrappers['pair']['container'] = 'div class=form-group';
    $renderer->wrappers['pair']['.error'] = 'has-error';
    $renderer->wrappers['control']['container'] = 'div class="col-sm-9 control-field"';
    $renderer->wrappers['label']['container'] = 'div class="col-sm-3 control-label"';
    $renderer->wrappers['control']['description'] = 'span class="help-block alert alert-info"';
    $renderer->wrappers['control']['errorcontainer'] = 'span class="help-block alert alert-danger"';
    // make form and controls compatible with Twitter Bootstrap
    $form->getElementPrototype()->class('form-horizontal');
    foreach ($form->getControls() as $control) {
      if ($control instanceof Controls\Button) {
        $control->getControlPrototype()->addClass(empty($usedPrimary) ? 'btn btn-primary' : 'btn btn-default');
        $usedPrimary = TRUE;
      } elseif ($control instanceof Controls\TextBase || $control instanceof Controls\SelectBox || $control instanceof Controls\MultiSelectBox) {
        $control->getControlPrototype()->addClass('form-control');
      } elseif ($control instanceof Controls\Checkbox || $control instanceof Controls\CheckboxList || $control instanceof Controls\RadioList) {
        $control->getSeparatorPrototype()->setName('div')->addClass($control->getControlPrototype()->type);
      }
    }
    return $form;
  }
}