<?phpnamespace App\AdminModule\Components\Article\TitleImage;use Nette\Application\UI\Form;use DbTable;/** * Formular a jeho spracovanie pre zmenu vlastnika polozky. * Posledna zmena 23.06.2017 *  * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com> * @copyright  Copyright (c) 2012 - 2017 Ing. Peter VOJTECH ml. * @license * @link       http://petak23.echo-msz.eu * @version    1.0.3 */class ZmenNadpisFormFactory {  /** @var DbTable\Hlavne_menu_lang */	private $hlavne_menu_lang;  /** @var DbTable\Hlavne_menu */	private $hlavne_menu;  /** @var DbTable\Lang */  public $lang;     /**   * @param DbTable\Hlavne_menu $hlavne_menu   * @param DbTable\Hlavne_menu_lang $hlavne_menu_lang */  public function __construct(DbTable\Hlavne_menu $hlavne_menu, DbTable\Hlavne_menu_lang $hlavne_menu_lang, DbTable\Lang $lang) {		$this->hlavne_menu = $hlavne_menu;    $this->hlavne_menu_lang = $hlavne_menu_lang;    $this->lang = $lang;	}    /**   * Formular pre zmenu vlastnika polozky.   * @param int $id Id polozky v hlavnom menu   * @return Nette\Application\UI\Form */    public function create($id)  {    $vychodzie_pre_form = array_merge($this->hlavne_menu_lang, ['langtxt' => ""]);		foreach ($this->jaz as $j) { //Pridanie vychodzich hodnot pre jazyky      $vychodzie_pre_form = array_merge($vychodzie_pre_form, [        $j->skratka.'_menu_name'=>"",        $j->skratka.'_h1part2'=>"",        $j->skratka.'_view_name'=>"",      ]);      $vychodzie_pre_form["langtxt"] .= " ".$j->skratka;    }    $vychodzie_pre_form["langtxt"] = trim($vychodzie_pre_form["langtxt"]);		$form = new Form();		$form->addProtection();    $form->addHidden("id", $id);    foreach ($this->lang->findAll() as $j) {      $form->addText($j->skratka.'_menu_name', 'Názov položky pre jazyk'.$j->nazov.":", 30, 100)           ->addRule(Form::MIN_LENGTH, 'Názov musí mať spoň %d znaky!', 2)           ->setRequired('Názov  pre jazyk "'.$j->nazov.'" musí byť zadaný!');      $form->addText($j->skratka.'_h1part2', 'Druhá časť nadpisu pre jazyk'.$j->nazov.":", 90, 100);      $form->addText($j->skratka.'_view_name', 'Podrobnejší popis položky pre jazyk'.$j->nazov.":", 90, 255)           ->addRule(Form::MIN_LENGTH, 'Popis musí mať spoň %d znaky!', 2)           ->setOption('description', 'Podrobnejší popis položky slúži pre vyhľadávače a zároveň ako pomôcka pre užívateľa, keď príde ukazovateľom myši nad odkaz(bublinová nápoveda).')           ->setRequired('Popis pre jazyk "'.$j->nazov.'" musí byť zadaný!');		}    $form->addSubmit('uloz', 'Zmeň')         ->setAttribute('class', 'btn btn-success')         ->onClick[] = [$this, 'zmenVlastnikaFormSubmitted'];    $form->addSubmit('cancel', 'Cancel')         ->setAttribute('class', 'btn btn-default')         ->setAttribute('data-dismiss', 'modal')         ->setAttribute('aria-label', 'Close')         ->setValidationScope(FALSE);		return $form;	}    /**    * Spracovanie formulara pre zmenu vlastnika clanku.   * @param Nette\Forms\Controls\SubmitButton $button Data formulara */  public function zmenVlastnikaFormSubmitted($button) {		$values = $button->getForm()->getValues(); 	//Nacitanie hodnot formulara    try {			$this->hlavne_menu->zmenVlastnika($values);		} catch (Database\DriverException $e) {			$button->addError($e->getMessage());		}  }}