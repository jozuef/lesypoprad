<?php

namespace App;

use Nette;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;
use DbTable;


class RouterFactory {

  /** @var DbTable\Hlavne_menu */
  public $hlavne_menu;
  
  /** @param DbTable\Hlavne_menu $hlavne_menu */
  public function __construct(DbTable\Hlavne_menu $hlavne_menu) {
    $this->hlavne_menu = $hlavne_menu;
  }
  
	/**
	 * @return Nette\Application\IRouter */
	public function createRouter() {
    $servis = $this;
		$router = new RouteList;

    $router[] = new Route('index.php', 'Front:Homepage:default', Route::ONE_WAY);
    $router[] = new Route('urllist.txt', 'Mapa:Mapa:urllist', Route::ONE_WAY);
    $router[] = new Route('sitemap.xml', 'Mapa:Mapa:sitemap', Route::ONE_WAY);
    $router[] = new Route('clanky/domov', 'Front:Homepage:default', Route::ONE_WAY);
    $router[] = new Route('clanky/home', 'Front:Homepage:default', Route::ONE_WAY);

    $router[] = $adminRouter = new RouteList('Admin');
    $adminRouter[] = new Route('admin/<presenter>/<action>', 'Homepage:default');

    $router[] = $frontRouter = new RouteList('Front');
    $frontRouter[] = new Route('clanky[/<id>]', [
      'presenter' => 'Clanky',
      'action' => 'default',
      'id' => [ Route::FILTER_IN => function ($id) use ($servis) {
                    if (is_numeric($id)) {
                      return $id;
                    } else {
                      $hh = $servis->hlavne_menu->findOneBy(array('spec_nazov'=>$id));
                      return $hh ? $hh->id : 0;
                    }
                },
                Route::FILTER_OUT => function ($id) use ($servis) {
                    if (!is_numeric($id)) {
                      return $id;
                    } else {
                      $hh = $servis->hlavne_menu->find($id);
                      return $hh ? $hh->spec_nazov : "";
                    }
                }
            ],
    ]);
    $frontRouter[] = new Route('forgottenPassword', 'User:forgottenPassword');
    $frontRouter[] = new Route('profile', 'UserLog:default');
    $frontRouter[] = new Route('registration', 'User:registracia');
    $frontRouter[] = new Route('login', 'User:default');
    $frontRouter[] = new Route('user[/<action>]', 'User:default');
    $frontRouter[] = new Route('userlog[/<action>]/<id>', 'UserLog:default');
    $frontRouter[] = new Route('oznam[/<action>]', 'Oznam:default');
    $frontRouter[] = new Route('error[/<action>]', 'Error:default');
    $frontRouter[] = new Route('<presenter>/<action>[/cokolvek]', 'Homepage:default');
    $frontRouter[] = new Route('[<presenter>][/<action>][/<spec_nazov><? \.html?|\.php|>]', 'Homepage:default', Route::ONE_WAY);
    
		return $router;
	}

}
