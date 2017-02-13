<?phpnamespace App\AdminModule\Components\Clanky\PrilohyClanok;use Nette\Application\UI\Form;use Nette\Utils\Strings;use Nette\Utils\Image;use Nette\Security\User;use Nette\Database;use DbTable;/** * Formular a jeho spracovanie pre pridanie a editaciu prilohy polozky. * Posledna zmena 13.05.2016 *  * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com> * @copyright  Copyright (c) 2012 - 2016 Ing. Peter VOJTECH ml. * @license * @link       http://petak23.echo-msz.eu * @version    1.0.3 */class EditPrilohyFormFactory {    /** @var DbTable\Dokumenty */	private $dokumenty;  /** @var string */  private $prilohy_adresar;  /** @var array */  private $prilohy_images;  /** @var int */  private $id_user_profiles;  /** @var string */  private $wwwDir;  /**   * @param DbTable\Dokumenty $dokumenty   * @param User $user   * @param string $wwwDir  */  public function __construct(DbTable\Dokumenty $dokumenty, User $user, $wwwDir = "") {    $this->dokumenty = $dokumenty;    $this->id_user_profiles = $user->getId();    $this->wwwDir = $wwwDir;	}    /**   * Formular pre pridanie prilohy a editaciu polozky.   * @param int $upload_size   * @param string $prilohy_adresar   * @param array $prilohy_images   * @return Form  */  public function create($upload_size, $prilohy_adresar, $prilohy_images)  {    $this->prilohy_adresar = $prilohy_adresar;    $this->prilohy_images = $prilohy_images;    $form = new Form();		$form->addProtection();    $form->addHidden("id");$form->addHidden("id_hlavne_menu");$form->addHidden("id_registracia");    $form->addUpload('priloha', 'Pridaj prílohu')         ->setOption('description', sprintf('Max veľkosť prílohy v bytoch %s kB', $upload_size/1024))         ->addCondition(Form::FILLED)          ->addRule(Form::MAX_FILE_SIZE, 'Max veľkosť obrázka v bytoch %d B', $upload_size);    $form->addText('nazov', 'Nadpis prílohy:', 55, 255)         ->setOption('description', sprintf('Nadpis by mal mať aspoň %s znakov. Inak nebude akceptovaný a bude použitý názov súboru!', 2));    $form->addText('popis', 'Podrobnejší popis prílohy:', 55, 255)         ->setOption('description', sprintf('Popis by mal mať aspoň %s znakov. Inak nebude akceptovaný!', 2));		$form->addSubmit('uloz', 'Ulož')         ->setAttribute('class', 'btn btn-success')         ->onClick[] = [$this, 'editPrilohaFormSubmitted'];    $form->addSubmit('cancel', 'Cancel')         ->setAttribute('class', 'btn btn-default')         ->setAttribute('data-dismiss', 'modal')         ->setAttribute('aria-label', 'Close')         ->setValidationScope(FALSE);		return $form;	}    /**    * Spracovanie formulara pre pridanie a editaciu prilohy polozky.   * @param Nette\Forms\Controls\SubmitButton $button Data formulara    * @throws Database\DriverException   */  public function editPrilohaFormSubmitted($button) {		$values = $button->getForm()->getValues(); 	//Nacitanie hodnot formulara    try {      $uloz = [ 			'id_hlavne_menu'	 	=> $values->id_hlavne_menu,			'id_user_profiles' 	=> $this->id_user_profiles,			'id_registracia'		=> $values->id_registracia,			'popis'							=> isset($values->popis) && strlen($values->popis)>2 ? $values->popis : NULL,			'zmena'							=> StrFTime("%Y-%m-%d %H:%M:%S", Time()),      ];      $nazov = isset($values->nazov) ? $values->nazov : "";      if ($values->priloha && $values->priloha->name != "") {        $priloha_info = $this->_uploadPriloha($values);        $uloz = array_merge($uloz, [          'nazov'				=> strlen($nazov)>2 ? $nazov : $priloha_info['finalFileName'],          'spec_nazov'	=> Strings::webalize($priloha_info['finalFileName']),          'pripona'			=> $priloha_info['pripona'],          'subor'				=> $this->prilohy_adresar.$priloha_info['finalFileName'],          'thumb'				=> $priloha_info['thumb'],    		]);      } else {        $uloz = array_merge($uloz, ['nazov' => strlen($nazov)>2 ? $nazov : ""]);      }      $vysledok = $this->dokumenty->uloz($uloz, $values->id);      if (!empty($vysledok) && isset($priloha_info['is_image']) && $priloha_info['is_image']) { $this->dokumenty->oprav($vysledok['id'], ['znacka'=>'#I-'.$vysledok['id'].'#']);}		} catch (Database\DriverException $e) {			$button->addError($e->getMessage());		}  }    /**   * Upload prilohy   * @param \Nette\Http\FileUpload $values   * @return array */  private function _uploadPriloha($values) {     $pr = $this->dokumenty->find($values->id);//Zmazanie starej prílohy    if ($pr !== FALSE) {      if (is_file($pr->subor)) { unlink($this->wwwDir."/".$pr->subor);}      if (in_array(strtolower($pr->pripona), ['png', 'gif', 'jpg']) && is_file($pr->thumb)) { unlink($this->wwwDir."/".$pr->thumb);}    }    $fileName = $values->priloha->getSanitizedName();		$pi = pathinfo($fileName);		$file = $pi['filename'];		$ext = $pi['extension'];		$additionalToken = 0;		//Najdi meno suboru		if (file_exists($this->prilohy_adresar.$fileName)) {			do { $additionalToken++;			} while (file_exists($this->prilohy_adresar.$file.$additionalToken.".".$ext));    }		$finalFileName = ($additionalToken == 0) ? $fileName : $file.$additionalToken.".". $ext;		//Presun subor na finalne miesto a ak je to obrazok tak vytvor nahlad		$values->priloha->move($this->prilohy_adresar.$finalFileName);		if ($values->priloha->isImage()) {			$image_name = $this->prilohy_adresar.$finalFileName;			$thumb_name = $this->prilohy_adresar.'tb_'.$finalFileName;			$image = Image::fromFile($image_name);      $image->resize($this->prilohy_images['x'], $this->prilohy_images['y'], Image::SHRINK_ONLY);      $image->save($image_name, 80);			copy($image_name, $thumb_name);			$thumb = Image::fromFile($thumb_name);			$thumb->resize($this->prilohy_images['tx'], $this->prilohy_images['ty'], Image::SHRINK_ONLY | Image::EXACT);			$thumb->save($thumb_name, 80);		}    //Vytvorenie nazvu pre thumb    $thumb_image = isset($thumb_name) ? $this->prilohy_adresar.'tb_'.$finalFileName :                     ("www/ikonky/Free-file-icons-master/48px/".                     (is_file("www/ikonky/Free-file-icons-master/48px/".$ext.".png") ? $ext : "_page").".png");    		$uloz = [			'finalFileName' => $finalFileName,			'pripona'				=> $ext,			'thumb'					=> $thumb_image,      'is_image'      => $values->priloha->isImage()  		];    return $uloz;  }}