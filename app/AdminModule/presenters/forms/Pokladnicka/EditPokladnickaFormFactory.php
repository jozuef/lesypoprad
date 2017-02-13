<?phpnamespace App\AdminModule\Presenters\Forms\Pokladnicka;use Nette\Application\UI\Form;use DbTable;/** * Formular a jeho spracovanie pre pridanie a aditaciu poloziek pokladnicky. * Posledna zmena 01.06.2016 *  * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com> * @copyright  Copyright (c) 2012 - 2016 Ing. Peter VOJTECH ml. * @license * @link       http://petak23.echo-msz.eu * @version    1.0.0 */class EditPokladnickaFormFactory {  /** @var DbTable\Pokladnicka */	private $pokladnicka;    /**   * @param DbTable\Pokladnicka $pokladnicka   * @param DbTable\User_profiles $user_profiles */  public function __construct(DbTable\Pokladnicka $pokladnicka) {		$this->pokladnicka = $pokladnicka;	}    /**   * Formular pre pridanie a aditaciu poloziek pokladnicky.   * @param int $id Id polozky   * @return Nette\Application\UI\Form */    public function create($id = 0)  {		$form = new Form();		$form->addProtection();    $form->addHidden("id"); $form->addHidden("created");		$form->addText('suma', 'Suma:', 50, 80)				 ->addRule(Form::FILLED, 'Suma musí byť zadaná!');		$form->addText('ucel', 'Účel:', 50, 80)         ->addRule(Form::FILLED, 'Účel musí byť zadaný!');    $form->addRadioList('vklad', 'Pohyb:', ['1' => 'vklad', '-1' => 'výber']);		$form->addSubmit('uloz', 'Ulož')         ->setAttribute('class', 'btn btn-success')		     ->onClick[] = [$this, 'pokladnickaFormSubmitted'];    $form->addSubmit('cancel', 'Cancel')         ->setAttribute('class', 'btn btn-default')         ->setAttribute('data-dismiss', 'modal')         ->setAttribute('aria-label', 'Close')         ->setValidationScope(FALSE);    if ($id) {      $p = $this->pokladnicka->find($id);    } else {      $p = [        'id'    => 0,        'vklad' => 1,        'created' => StrFTime("%Y-%m-%d %H:%M:%S", Time()),      ];    }    $form->setDefaults($p);		return $form;	}    /**    * Spracovanie formulara.   * @param Nette\Forms\Controls\SubmitButton $button Data formulara */  public function pokladnickaFormSubmitted($button) {		$values = $button->getForm()->getValues(); 	//Nacitanie hodnot formulara    try {			$this->pokladnicka->ulozPokladnicka($values);		} catch (Database\DriverException $e) {			$button->addError($e->getMessage());		}	}}