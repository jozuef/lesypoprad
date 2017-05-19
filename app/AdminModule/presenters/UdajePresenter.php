<?phpnamespace App\AdminModule\Presenters;use DbTable;/** * Prezenter pre administraciu hlavnych udajov webu. * Posledna zmena(last change): 19.05.2017 * @actions default, add, add2, edit * *	Modul: ADMIN * * @author Ing. Peter VOJTECH ml. <petak23@gmail.com> * @copyright  Copyright (c) 2012 - 2017 Ing. Peter VOJTECH ml. * @license * @link       http://petak23.echo-msz.eu * @version 1.0.8 */class UdajePresenter extends \App\AdminModule\Presenters\BasePresenter {    // -- DB  /** @var DbTable\Druh @inject */  public $druh;  /** @var DbTable\Udaje_typ @inject */	public $udaje_typ;      // -- Forms  /** @var Forms\Udaje\AddTypeUdajeFormFactory @inject*/	public $addTypeUdajeForm;  /** @var Forms\Udaje\EditUdajeFormFactory @inject*/	public $editUdajeForm;  /** Akcia pre pridanie udaju prvy krok */	public function actionAdd() {}    /**    * Akcia pre pridanie udaju druhy krok   * @param int $type */  public function actionAdd2($type) {    $this["editUdajeForm"]->setDefaults(['id_udaje_typ'  => $type]);    $this->setView('Edit');  }  /**    * Akcia pre editaciu udaju    * @param int $id */	public function actionEdit($id) {    if (($pol_udaje = $this->udaje->find($id)) === FALSE) {      $this->setView('notFound');    } else {      $this["editUdajeForm"]->setDefaults($pol_udaje);      $this["editUdajeForm"]->setDefaults([        'spravca'   => $pol_udaje->id_user_roles == $this->ur_reg['admin'] ? 0 : 1,        'druh_null' => $pol_udaje->id_druh == NULL ? 1 : 0,      ]);    }	}    public function renderDefault() {    $this->template->udaje_w = $this->udaje->findAll();  }  /**   * Formular pre urcenie typu udaju   * @return \Nette\Application\UI\Form */  protected function createComponentAddTypeUdajeForm() {    $form = $this->addTypeUdajeForm->create($this->udaje_typ->findAll()->fetchPairs('id', 'nazov'));      $form['uloz']->onClick[] = function ($button) {      $values = $button->getForm()->getValues();      $this->redirect('Udaje:add2', $values->id_udaje_typ);		};    $form['cancel']->onClick[] = function () {			$this->redirect('Udaje:');		};		return $this->_vzhladForm($form);  }  /**    * Formular pre editaciu udaov	 * @return Nette\Application\UI\Form */	protected function createComponentEditUdajeForm()	{    $form = $this->editUdajeForm->create($this->user->isInRole("admin"), $this->druh->findAll()->fetchPairs('id', 'popis'), $this->ur_reg);      $form['uloz']->onClick[] = function ($form) {      $this->flashOut(!count($form->errors), 'Udaje:', 'Údaj bol uložený!', 'Došlo k chybe a údaj sa neuložil. Skúste neskôr znovu...');		};    $form['cancel']->onClick[] = function () {			$this->redirect('Udaje:');		};		return $this->_vzhladForm($form);	}   /**     * Funkcia pre spracovanie signálu vymazavania	  * @param int $id - id polozky v hlavnom menu */	function confirmedDelete($id)	{    $this->_ifMessage($this->udaje->zmaz($id) == 1, 'Údaj bol úspešne vymazaný!', 'Došlo k chybe a údaj nebol vymazaný!');    if (!$this->isAjax()) { $this->redirect('Udaje:'); }  }    /**  * Zostavenie otázky pre ConfDialog s parametrom  * @param Nette\Utils\Html $dialog  * @param array $params  * @return string $question  */  public function questionDelete($dialog, $params) {     $dialog->getQuestionPrototype();     return sprintf("Naozaj chceš zmazať údaj: %s?", isset($params['nazov']) ? "'".$params['nazov']."'" : "");  }}