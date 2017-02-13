<?phpnamespace App\AdminModule\Components\Article\TitleArticle;use Nette\Application\UI\Form;use DbTable;/** * Formular a jeho spracovanie pre zmenu vlastnika polozky. * Posledna zmena 01.03.2016 *  * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com> * @copyright  Copyright (c) 2012 - 2016 Ing. Peter VOJTECH ml. * @license * @link       http://petak23.echo-msz.eu * @version    1.0.1 */class ZmenVlastnikaFormFactory {  /** @var DbTable\Hlavne_menu */	private $hlavne_menu;    /** @var DbTable\User_profiles */	private $user_profiles;    /**   * @param DbTable\Hlavne_menu $hlavne_menu   * @param DbTable\User_profiles $user_profiles */  public function __construct(DbTable\Hlavne_menu $hlavne_menu, DbTable\User_profiles $user_profiles) {		$this->hlavne_menu = $hlavne_menu;    $this->user_profiles = $user_profiles;	}    /**   * Formular pre zmenu vlastnika polozky.   * @param int $id Id polozky v hlavnom menu   * @param int $id_user_profiles Id sucasneho vlastnika polozky   * @return Nette\Application\UI\Form */    public function create($id, $id_user_profiles)  {		$form = new Form();		$form->addProtection();    $form->addHidden("id", $id);    $form->addRadioList('id_user_profiles', 'Nový vlastník:', $this->user_profiles->uzivateliaForm())         ->setDefaultValue($id_user_profiles);    $form->addSubmit('uloz', 'Zmeň')         ->setAttribute('class', 'btn btn-success')         ->onClick[] = [$this, 'zmenVlastnikaFormSubmitted'];    $form->addSubmit('cancel', 'Cancel')         ->setAttribute('class', 'btn btn-default')         ->setAttribute('data-dismiss', 'modal')         ->setAttribute('aria-label', 'Close')         ->setValidationScope(FALSE);		return $form;	}    /**    * Spracovanie formulara pre zmenu vlastnika clanku.   * @param Nette\Forms\Controls\SubmitButton $button Data formulara */  public function zmenVlastnikaFormSubmitted($button) {		$values = $button->getForm()->getValues(); 	//Nacitanie hodnot formulara    try {			$this->hlavne_menu->zmenVlastnika($values);		} catch (Database\DriverException $e) {			$button->addError($e->getMessage());		}  }}