<?php
namespace App\AdminModule\Presenters;

use Nette\Forms\Container;
use Nette\Application\UI\Multiplier;
use DbTable;
use PeterVojtech;

/**
 * Prezenter pre spravu oznamov.
 * 
 * Posledna zmena(last change): 21.06.2017
 *
 * Modul: ADMIN
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2017 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.1.6
 */

Container::extensionMethod('addDatePicker', function (Container $container, $name, $label = NULL) {
    return $container[$name] = new \JanTvrdik\Components\DatePicker($label);
});

class OznamPresenter extends \App\AdminModule\Presenters\BasePresenter {
	/** 
   * @inject
   * @var DbTable\Oznam */
	public $oznam;
  
  // -- Komponenty
  /** @var \App\AdminModule\Components\Oznam\TitleOznam\ITitleOznamControl @inject */
  public $titleOznamControlFactory;
  /** @var PeterVojtech\Email\IEmailControl @inject */
  public $emailControl;


  // -- Formulare
  /** @var Forms\Oznam\EditOznamFormFactory @inject*/
	public $editOznamForm;
  /** @var \App\AdminModule\Components\Oznam\TitleOznam\EditTitleImageFormFactory @inject*/
  public $editTitleImageFormFactory;
  
	/** @var boolean|FALSE */
	private $oznam_usporiadanie = FALSE;

  /** Vychodzie nastavenia */
	protected function startup() {
    parent::startup();
    //Z DB zisti ako budu oznamy usporiadane
    if (($pomocna = $this->udaje->getKluc("oznam_usporiadanie")) !== FALSE) {
      $this->oznam_usporiadanie = (boolean)$pomocna->text;
    }
    if (($pomocna = $this->udaje->getKluc("oznam_komentare")) !== FALSE) {
      $oznam_komentare = (boolean)$pomocna->text;
    } else {$oznam_komentare = FALSE; }
    $this->template->oznam_komentare = $oznam_komentare;
    $this->template->oznam_title_image_en = (boolean)$this->udaje->getUdajInt("oznam_title_image_en");
	}

  /** Render pre vypis oznamov */
  public function renderDefault() {
		$this->template->oznamy = $this->oznam->vsetky($this->oznam_usporiadanie, $this->id_reg);
  }

  /** Akcia pre pridanie oznamu */
	public function actionAdd() {
    $this["oznamEditForm"]->setDefaults([ //Nastav vychodzie hodnoty
      'id'							=> 0,
      'id_user_main'    => $this->getUser()->getId(),
      'id_user_roles'   => 0,
      'id_ikonka'				=> 0,
      'datum_platnosti'	=> StrFTime("%Y-%m-%d", Time()),
      'datum_zadania'   => StrFTime("%Y-%m-%d %H:%M:%S", Time()),
      'potvrdenie'			=> 0,
    ]);
    $this->setView('edit');
    $this->template->h2 = 'Pridanie oznamu';
	}

  /** Akcia pre editaciu oznamu
   * @param int $id Id oznamu
   */
	public function actionEdit($id) {
    if (($oznam = $this->oznam->hladaj_id($id, $this->id_reg)) === FALSE) {
      $this->setView('notFound');
    } else { //Ak je vsetko OK priprav premenne
      $this["oznamEditForm"]->setDefaults($oznam);
      $this["oznamEditForm"]->setDefaults([ //Nastav vychodzie hodnoty
        'datum_zadania'   => StrFTime("%Y-%m-%d %H:%M:%S", Time()),
      ]);
      $this->template->h2 = sprintf('Editácia oznamu: %s', $oznam->nazov);
    }
    
	}
  
  /** Render pre pridanie a editaciu oznamu */
  public function renderEdit() {
    $this->template->bezp_kod	= '4RanoS5689q6-498'; //Bezp. kod pre CKEditor$
    $this->template->CKtoolbar= "OznamToolbar".(($this->user->isInRole('admin')) ? "" : "_Spravca");
  }
  
	/** Formular pre editaciu a pridanie oznamu
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentOznamEditForm() {
    $form = $this->editOznamForm->create($this->udaje_webu['oznam_ucast'], $this->template->oznam_title_image_en, $this->nazov_stranky); 
    $form['uloz']->onClick[] = function ($button) { 
      $form_val = $button->getForm();
      $this->flashOut(!count($form_val->errors), 'Oznam:', 'Oznam bol uložený!', 'Došlo k chybe a oznam sa neuložil. Skúste neskôr znovu...');
		};
    $form['cancel']->onClick[] = function () {
			$this->redirect('Oznam:');
		};
		return $this->_vzhladForm($form);
	}
  
  /** Odoslanie info e-mailu 
   * @param int $id
   */
	protected function _sendOznamyEmail($id) {
    $values = $this->oznam->find($id);
    $params = [ "site_name"   => $this->nazov_stranky,
                "nazov"       => $values->nazov,
                "text"        => $values->text,
                "odkaz"       => $this->link("//:Front:Oznam:default"),
                "datum_platnosti" => $values->datum_platnosti,
                "oznam_ucast" => $this->user->isAllowed('Admin:Oznam', 'ucast') && $this->udaje->getKluc("oznam_ucast") && $values->potvrdenie,
                "oznam_id"    => $values->id,
                "volby"       => [],
              ];
    $send = $this->emailControl->create()->nastav(__DIR__.'/templates/Oznam/email_oznamy_html.latte', 1, $values->id_user_roles);
    try {
      $this->flashMessage('E-mail bol odoslany v poriadku na emaily: '.$send->send($params, 'Nový oznam na stránke '.$this->nazov_stranky), 'success');
    } catch (Exception $e) {
      $this->flashMessage($e->getMessage(), 'danger');
    }
	}

  /** Signal pre odoslanie informacneho emailu */
  public function handlePosliEmail($id) {
    $this->_sendOznamyEmail($id);
		$this->redirect('this');
	}
  
  /** Signal pre zmenu presmerovania v pripade, ze nemam aktualne oznamy */
  public function handleOznPresmerovanie() {
    //$this->udaje->opravKluc('oznam_usporiadanie', "1");
		$this->redirect('this');
	}
  
  /** Komponenta pre tvorbu titulku oznamov.
   * @return \App\AdminModule\Components\Oznam\TitleOznam */
  public function createComponentTitleOznam() {
    $title = $this->titleOznamControlFactory->create();
    return $title;
  }
  
  /** Komponenta pre tvorbu titulku oznamov.
   * @return \App\AdminModule\Components\Oznam\TitleOznam */
  public function createComponentTitleImage() {
    $servise = $this;
		return new Multiplier(function ($id) use ($servise) {
			$odkaz = New \App\AdminModule\Components\Oznam\TitleOznam\EditTitleImageFormFactory($this->oznam);
      $odkaz->create($servise->avatar_path, $servise->wwwDir);
			return $odkaz;
		});
  }
  
  /** Funkcia pre spracovanie signálu vymazavania
	  * @param int $id Id oznamu */
	function confirmedDelete($id)	{
    if ($this->oznam->vymazOznam($id) == 1) { $this->flashMessage('Aktualita(oznam) bola úspešne vymazaná!', 'success'); } 
    else { $this->flashMessage('Došlo k chybe a aktualita(oznam) nebola vymazaná!', 'danger'); }
    if (!$this->isAjax()) { $this->redirect('Oznam:'); }
  }
  
  /**
   * Vytvorenie spolocnych helperov pre sablony
   * @param type $class
   * @return type */
  protected function createTemplate($class = NULL) {
    $servise = $this;
    $template = parent::createTemplate($class);
    $template->addFilter('vlastnik', function ($id_user_profiles = 0, $action = 'edit') use($servise) {
      $user = $servise->user;
      // Vrati true ak: si prihlaseny && si admin || (mas opravnenie a si valstnik)
      $out = $user->isLoggedIn() ? ($user->isInRole('admin') ? TRUE : 
                                          ($user->isAllowed($servise->name , $action) ? ($id_user_profiles ? $user->getIdentity()->id == $id_user_profiles : FALSE) : FALSE)) : FALSE;
      return $out;
    });
    return $template;
	}
  
}
