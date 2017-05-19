<?phpnamespace App\AdminModule\Presenters\Forms\User;use Nette\Application\UI\Form;use Nette\Security\User;use DbTable;/** * Tovarnicka pre formular na editaciu uzivatela * Posledna zmena 19.05.2017 *  * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com> * @copyright  Copyright (c) 2012 - 2017 Ing. Peter VOJTECH ml. * @license * @link       http://petak23.echo-msz.eu * @version    1.0.2 */class EditUserFormFactory {  /** @var DbTable\User_main */	private $user_main;  /** @var DbTable\User_profiles */	private $users_profiles;    /**   * @param DbTable\User_main $user_main   * @param DbTable\User_profiles $users_profiles   * @param DbTable\User_roles $user_roles   * @param \App\AdminModule\Presenters\Forms\User\User $user */  public function __construct(DbTable\User_main $user_main, DbTable\User_profiles $users_profiles, DbTable\User_roles $user_roles, User $user) {		$this->user_main = $user_main;    $this->users_profiles = $users_profiles;    $this->urovneReg = $user_roles->urovneReg(($user->isLoggedIn()) ? $user->getIdentity()->id_user_roles : 0); //Hodnoty id=>nazov pre formulare z tabulky user_roles	}  /**   * Edit hlavne menu form component factory.   * @return Nette\Application\UI\Form   */    public function create($user_view_fields)  {    $form = new Form();		$form->addProtection();    $form->addHidden('id');$form->addHidden('id_user_main');$form->addHidden('created');$form->addHidden('modified');		$form->addText('meno', 'Meno:', 50, 80)				 ->addRule(Form::MIN_LENGTH, 'Meno musí mať spoň %d znakov!', 3)				 ->setRequired('Meno musí byť zadané!');    $form->addText('priezvisko', 'Priezvisko:', 50, 80)				 ->addRule(Form::MIN_LENGTH, 'Priezvisko musí mať spoň %d znakov!', 3)				 ->setRequired('Priezvisko musí byť zadané!');    $form->addText('username', 'Užívateľské meno', 50, 50)				 ->addRule(Form::MIN_LENGTH, 'Užívateľské meno musí mať aspoň %s znakov', 3)				 ->setRequired('Užívateľské meno musí byť zadané!');    $form->addText('email', 'E-mailová adresa', 50, 50)         ->setType('email')				 ->addRule(Form::EMAIL, 'Musí byť zadaná korektná e-mailová adresa(napr. janko@hrasko.sk)')				 ->setRequired('E-mailová adresa musí byť zadaná!');    $form->addSelect('id_user_roles', 'Úroveň registrácie člena:', $this->urovneReg);    if ($user_view_fields["rok"]) {      $form->addText('rok', 'Rok narodenia:', 4, 5)           ->addRule(Form::RANGE, 'Rok narodenia musí byť v rozsahu od %d do %d', [1900, StrFTime("%Y", Time())]);    }    if ($user_view_fields["telefon"]) {      $form->addText('telefon', 'Telefón:', 20, 20);    }    if ($user_view_fields["poznamka"]) {      $form->addText('poznamka', 'Poznámka:', 50, 250);    }    if ($user_view_fields["pohl"]) {      $form->addSelect('pohl', 'Pohlavie:', ['M'=>'Muž','Z'=>'Žena']);    }    $form->onValidate[] = [$this, 'validateEditUserForm'];    $form->addSubmit('uloz', 'Ulož')         ->setAttribute('class', 'btn btn-success')         ->onClick[] = [$this, 'editUserFormSubmitted'];;    $form->addSubmit('cancel', 'Cancel')->setAttribute('class', 'btn btn-default')         ->setValidationScope(FALSE);		return $form;	}    /** Vlastná validácia   * @param Nette\Application\UI\Form $button   */  public function validateEditUserForm($button) {    $values = $button->getForm()->getValues();    if ($button->isSubmitted()->name == 'uloz') {      $user = $this->user_main->find($values->id_user_main);      // Over, ci dane username uz existuje.      $uu = $this->user_main->findOneBy(['username'=>$values->username]);      if ($uu && $user->id <> $uu->id) {        $button->addError(sprintf('Zadané užívateľské meno %s už existuje! Zvolte prosím iné!', $values->username));      }      // Over, ci dany email uz existuje.      $ue = $this->user_main->findOneBy(['email'=>$values->email]);      if ($ue && $user->id <> $ue->id) {        $button->addError(sprintf('Zadaný e-mail %s už existuje! Zvolte prosím iný!', $values->email));      }    }  }    /**    * Spracovanie vstupov z formulara   * @param Nette\Forms\Controls\SubmitButton $button Data formulara */	public function editUserFormSubmitted($button)	{    $values = $button->getForm()->getValues();    try {			$this->user_profiles->saveUser($values);		} catch (Database\DriverException $e) {			$button->addError($e->getMessage());		}	}}