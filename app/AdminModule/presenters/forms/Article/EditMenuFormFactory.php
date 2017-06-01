<?phpnamespace App\AdminModule\Presenters\Forms\Article;use Nette\Application\UI\Form;use Nette\Security\User;use DbTable;/** * Formular pre editaciu poloziek menu * Posledna zmena 01.06.2017 *  * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com> * @copyright  Copyright (c) 2012 - 2017 Ing. Peter VOJTECH ml. * @license * @link       http://petak23.echo-msz.eu * @version    1.0.7 */class EditMenuFormFactory {    /** @var Nette\Security\User */  protected $user;  /** @var DbTable\Lang */  public $lang;    /** @var array Hodnoty id=>nazov pre formulare z tabulky hlavicka */  protected $hlavickaForm;  /**   * @param User $user   * @param DbTable\User_roles $user_roles   * @param DbTable\Lang $lang   * @param DbTable\Hlavicka $hlavicka */  public function __construct(User $user, DbTable\Lang $lang, DbTable\Hlavicka $hlavicka) {    $this->user = $user;    $this->lang = $lang;    $this->hlavickaForm = $hlavicka->hlavickaForm();  }    /**   * Edit hlavne menu form component factory.   * @param int $uroven Uroven polozky v menu   * @param string $uloz Text tlacitka uloz   * @return Form */  public function form($uroven, $uloz)  {		$form = new Form();		$form->addProtection();    $form->addGroup();    $form->addHidden("id");    $form->addHidden("id_druh");$form->addHidden("id_user_main");$form->addHidden("spec_nazov");    $form->addHidden("langtxt");$form->addHidden("id_hlavne_menu_cast");    $form->addHidden("uroven");$form->addHidden("id_nadradenej");$form->addHidden("modified");    if ($this->user->isInRole("admin")) {      $form->addText('nazov_ul_sub', 'Názov alternatívneho vzhľadu:', 20, 20);      if ($uroven) {        $form->addHidden('id_hlavicka');      } else {	         $form->addSelect('id_hlavicka', 'Druh priradenej hlavičky:', $this->hlavickaForm)            ->addRule(Form::FILLED, 'Je nutné vybrať hlavičku.');      }    }    $form->addText('poradie', 'Poradie položky v časti:', 3, 3)				 ->addRule(Form::RANGE, 'Poradie musí byť v rozsahu od %d do %d!', array(1, 9999))				 ->setRequired('Poradie musí byť zadané!');    $form->addText('absolutna', 'Absolútna adresa:', 90, 50);		// Cast textov ----------------- 		foreach ($this->lang->findAll() as $j) {      $form->addText($j->skratka.'_nazov', 'Názov položky pre jazyk'.$j->nazov.":", 30, 100)           ->addRule(Form::MIN_LENGTH, 'Názov musí mať spoň %d znaky!', 2)           ->setRequired('Názov  pre jazyk "'.$j->nazov.'" musí byť zadaný!');      $form->addText($j->skratka.'_h1part2', 'Druhá časť nadpisu pre jazyk'.$j->nazov.":", 90, 100);      $form->addText($j->skratka.'_description', 'Podrobnejší popis položky pre jazyk'.$j->nazov.":", 90, 255)           ->addRule(Form::MIN_LENGTH, 'Popis musí mať spoň %d znaky!', 2)           ->setOption('description', 'Podrobnejší popis položky slúži pre vyhľadávače a zároveň ako pomôcka pre užívateľa, keď príde ukazovateľom myši nad odkaz(bublinová nápoveda).')           ->setRequired('Popis pre jazyk "'.$j->nazov.'" musí byť zadaný!');		}		// Cast textov koniec -----------------    $form->addSubmit('uloz', $uloz)->setAttribute('class', 'btn btn-success');    $form->addSubmit('cancel', 'Cancel')->setAttribute('class', 'btn btn-default')->setValidationScope(FALSE);		return $form;	}}interface IEditMenuFormFactory {  /** @return EditMenuFormFactory */  function create();}