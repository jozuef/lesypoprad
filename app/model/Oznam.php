<?php
namespace DbTable;
use Nette;

/**
 * Model, ktory sa stara o tabulku oznam
 * 
 * Posledna zmena 21.06.2017
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2017 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.5
 */
class Oznam extends Table {
  /** @var string */
  protected $tableName = 'oznam';
  
  /** Vypisanie vsetkych aktualnych oznamov
   * @param boolean $usporiadanie Urcuje usporiadane podla datumu platnosti
   * @return \Nette\Database\Table\Selection */
  public function aktualne($usporiadanie = FALSE) {
  	return $this->findBy(["datum_platnosti >= '".StrFTime("%Y-%m-%d",strtotime("0 day"))."'"])
                ->order('datum_platnosti '.($usporiadanie ? 'ASC' : 'DESC'));
	}

  /** Vrati uz neaktualne oznamy
   * @return \Nette\Database\Table\Selection */
	public function neaktualne() {
  	return $this->findBy(["datum_platnosti < '".StrFTime("%Y-%m-%d",strtotime("0 day"))."'"])->order('datum_platnosti DESC');
	}
  
  /** Vypisanie vsetkych oznamov aj s priznakom aktualnosti
   * @param boolean $usporiadanie Urcuje usporiadane podla datumu platnosti
   * @return array */
  public function vsetky($usporiadanie = FALSE) {
  	$oznamy = $this->findAll()->order('datum_platnosti '.($usporiadanie ? 'ASC' : 'DESC'));
    $out = [];
    foreach ($oznamy as $o) {
      $temp = ["oznam" => $o, "aktualny" => $o->datum_platnosti >= StrFTime("%Y-%m-%d",strtotime("0 day"))];
      $out[] = $temp;
    }
    return $out;
	}
  
  /** Vymazanie oznamu
   * @param int $id Id oznamu
   * @return type
   * @throws Database\DriverException */
  public function vymazOznam($id) {
    try {
      return $this->find($id)->delete();
    } catch (Exception $e) {
      throw new Database\DriverException('Chyba ulozenia: '.$e->getMessage());
    } 
  }
  
  /** Funkcia pre ulozenie oznamu
   * @param Nette\Utils\ArrayHash $values
   * @return Nette\Database\Table\ActiveRow|FALSE
   * @throws Database\DriverException */
  public function ulozOznam(Nette\Utils\ArrayHash $values) {
    $val = clone $values;
    $id = isset($val->id) ? $val->id : 0;
    unset($val->id, $val->posli_news);
    try {
      return $this->uloz($val, $id);
    } catch (Exception $e) {
      throw new Database\DriverException('Chyba ulozenia: '.$e->getMessage());
    }
  }
  
  /**
   * Ulozenie titulneho obrazku alebo ikonky alebo fa_class-u
   * @param Nette\Utils\ArrayHash $values
   * @param string $title_image_path
   * @param string $www_dir
   * @throws Database\DriverException */
  public function zmenTitleImage($values, $title_image_path, $www_dir) {
    $save_data = ['id_ikonka' => NULL, 'title_image' => NULL, 'title_fa_class' => NULL];
    if (!$values->title_image->error) {
      if ($values->title_image->isImage()){ 
        $values->title_image = $this->_uploadTitleImage($values->title_image, $www_dir."/www/".$title_image_path);
        $this->uloz(array_merge($save_data, ['title_image'=>$values->title_image]), $values->id);
      } else {
        throw new Database\DriverException('Pre titulný obrázok nebol použitý obrázok a tak nebol uložený!'.$e->getMessage());
      }
    } elseif ($values->id_ikonka){
      $this->_delAvatar($values->old_title_image, $title_image_path, $www_dir);
      $this->uloz(array_merge($save_data, ['id_ikonka'=>$values->id_ikonka]), $values->id);
    } elseif ($values->title_fa_class) {
      $this->_delAvatar($values->old_title_image, $title_image_path, $www_dir);
      $this->uloz(array_merge($save_data, ['title_fa_class'=>$values->title_fa_class]), $values->id);
    } else { 
      throw new Database\DriverException('Pri pokuse o uloženie došlo k chybe! Pravdepodobná príčina je č.'.$values->title_image->error.". ".$e->getMessage());
    }
  }
  
  /**
   * Zmazanie titulneho obrazku a/alebo ikonky
   * @param type $id
   * @param string $title_image_path
   * @param string $www_dir
   * @return Nette\Database\Table\ActiveRow|FALSE */
  public function zmazTitleImage($id, $title_image_path, $www_dir) {
    $hl = $this->find($id);
    $this->_delTitleImage($hl->title_image, $title_image_path, $www_dir);
    return $this->uloz(["ikonka"=>NULL, "avatar"=>NULL], $id);
  }
  
  /**
   * @param Nette\Http\FileUpload $title_image
   * @param string $path
   * @return string */
  private function _uploadTitleImage(Nette\Http\FileUpload $title_image, $path) {
    $pi = pathinfo($title_image->getSanitizedName());
    $ext = $pi['extension'];
    $title_image_name = Random::generate(15).".".$ext;
    $title_image->move($path.$title_image_name);
    $image = Image::fromFile($path.$title_image_name);
    $image->save($path.$title_image_name, 75);
    return $title_image_name;
  }
  
  /**
   * @param string $title_image_name
   * @param string $title_image_path
   * @param string $www_dir */
  private function _delTitleImage($title_image_name, $title_image_path, $www_dir) {
    if ($title_image_name !== NULL && is_file("www/".$title_image_path.$title_image_name)) { 
      unlink($www_dir."/www/".$title_image_path.$title_image_name);
    }
  }
}