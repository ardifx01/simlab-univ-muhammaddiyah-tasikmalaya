<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->addRedirect('/', 'login');
$routes->addRedirect('/admin', 'login');
$routes->group('', ['filter' => 'login'], function($routes)
{
	$routes->get('/profile', 'Profile::index');
	$routes->post('/profile', 'Profile::index');
	$routes->post('/profile/changePhoto', 'Profile::changePhoto');
	$routes->get('/profile/changePassword', 'Profile::changePassword');
	$routes->post('/profile/changePassword', 'Profile::changePassword');
	
	$routes->get('/dashboard', 'Dashboard::index');
	
	$routes->get('/data/labs', 'Data::labs');
	$routes->post('/data/labList', 'Data::labList');
	$routes->get('/data/labAdd', 'Data::labAdd');
	$routes->post('/data/labAdd', 'Data::labAdd');
	$routes->get('/data/labEdit/(:any)', 'Data::labEdit/$1');
	$routes->post('/data/labEdit/(:any)', 'Data::labEdit/$1');
	$routes->post('/data/labDelete', 'Data::labDelete');
	
	$routes->get('/data/rooms/(:any)', 'Data::rooms/$1');
	$routes->post('/data/roomList', 'Data::roomList');
	$routes->get('/data/roomAdd/(:any)', 'Data::roomAdd/$1');
	$routes->post('/data/roomAdd/(:any)', 'Data::roomAdd/$1');
	$routes->get('/data/roomEdit/(:any)/(:any)', 'Data::roomEdit/$1/$2');
	$routes->post('/data/roomEdit/(:any)/(:any)', 'Data::roomEdit/$1/$2');
	$routes->post('/data/roomDelete', 'Data::roomDelete');
	
	$routes->get('/data/inventaris/(:any)', 'Data::inventaris/$1');
	$routes->post('/data/inventarisList', 'Data::inventarisList');
	$routes->get('/data/inventarisAdd/(:any)', 'Data::inventarisAdd/$1');
	$routes->post('/data/inventarisAdd/(:any)', 'Data::inventarisAdd/$1');
	$routes->get('/data/inventarisEdit/(:any)/(:any)', 'Data::inventarisEdit/$1/$2');
	$routes->post('/data/inventarisEdit/(:any)/(:any)', 'Data::inventarisEdit/$1/$2');
	$routes->post('/data/inventarisDelete', 'Data::inventarisDelete');
	
	$routes->post('/data/inventarisTab', 'Data::inventarisTab');
	
	$routes->post('/data/itemList', 'Data::itemList');
	$routes->get('/data/itemAdd/(:any)', 'Data::itemAdd/$1');
	$routes->post('/data/itemAdd/(:any)', 'Data::itemAdd/$1');
	$routes->get('/data/itemEdit/(:any)', 'Data::itemEdit/$1');
	$routes->post('/data/itemEdit/(:any)', 'Data::itemEdit/$1');
	$routes->post('/data/itemDelete', 'Data::itemDelete');
	$routes->post('/data/itemsDelete', 'Data::itemsDelete');
	$routes->post('/data/itemGetInventaris', 'Data::itemGetInventaris');
	$routes->add('/data/generateItemBarcode', 'Data::generateItemBarcode');
	$routes->add('/data/generateKotakBarcode', 'Data::generateKotakBarcode');
	
	$routes->post('/data/kotakPenyimpananList', 'Data::kotakPenyimpananList');
	$routes->get('/data/kotakPenyimpananAdd/(:any)', 'Data::kotakPenyimpananAdd/$1');
	$routes->post('/data/kotakPenyimpananAdd/(:any)', 'Data::kotakPenyimpananAdd/$1');
	$routes->get('/data/kotakPenyimpananEdit/(:any)/(:any)', 'Data::kotakPenyimpananEdit/$1/$2');
	$routes->post('/data/kotakPenyimpananEdit/(:any)/(:any)', 'Data::kotakPenyimpananEdit/$1/$2');
	$routes->post('/data/kotakPenyimpananDelete', 'Data::kotakPenyimpananDelete');
	
	$routes->get('/data/isiKotak/(:any)', 'Data::isiKotak/$1');
	$routes->post('/data/isiKotakList', 'Data::isiKotakList');
	$routes->post('/data/isiKotakAdd', 'Data::isiKotakAdd');
	$routes->post('/data/isiKotakDelete', 'Data::isiKotakDelete');
	$routes->get('/data/isiKotakItem/(:any)', 'Data::isiKotakItem/$1');
	$routes->post('/data/isiKotakItemList', 'Data::isiKotakItemList');
	
	$routes->post('/data/daftarKotakPenyimpananList', 'Data::daftarKotakPenyimpananList');
	$routes->get('/data/daftarKotakPenyimpananAdd/(:any)', 'Data::daftarKotakPenyimpananAdd/$1');
	$routes->post('/data/daftarKotakPenyimpananAdd/(:any)', 'Data::daftarKotakPenyimpananAdd/$1');
	$routes->get('/data/daftarKotakPenyimpananEdit/(:any)', 'Data::daftarKotakPenyimpananEdit/$1');
	$routes->post('/data/daftarKotakPenyimpananEdit/(:any)', 'Data::daftarKotakPenyimpananEdit/$1');
	$routes->post('/data/daftarKotakPenyimpananDelete', 'Data::daftarKotakPenyimpananDelete');
	$routes->post('/data/daftarKotakPenyimpanansDelete', 'Data::daftarKotakPenyimpanansDelete');
	$routes->post('/data/daftarKotakPenyimpananGetInventaris', 'Data::daftarKotakPenyimpananGetInventaris');
	
	$routes->get('/circulation/loan', 'Circulation::loan');
	$routes->post('/circulation/lend', 'Circulation::lend');
	$routes->get('/circulation/loaned', 'Circulation::loaned');
	$routes->post('/circulation/loanedMhsList', 'Circulation::loanedMhsList');
	$routes->post('/circulation/loanedMhsItemList', 'Circulation::loanedMhsItemList');
	$routes->post('/circulation/loanedMhsKotakList', 'Circulation::loanedMhsKotakList');
	$routes->post('/circulation/loanedDosenList', 'Circulation::loanedDosenList');
	$routes->post('/circulation/loanedDosenItemList', 'Circulation::loanedDosenItemList');
	$routes->post('/circulation/loanedDosenKotakList', 'Circulation::loanedDosenKotakList');
	$routes->post('/circulation/getItem', 'Circulation::getItem');
	$routes->get('/circulation/return', 'Circulation::return');
	$routes->post('/circulation/returnAction', 'Circulation::returnAction');
	$routes->get('/circulation/loanHistory', 'Circulation::loanHistory');
	$routes->get('/circulation/historyDetail/mhs/(:any)', 'Circulation::loanHistoryDetailMhs/$1');
	$routes->get('/circulation/historyDetail/dosen/(:any)', 'Circulation::loanHistoryDetailDosen/$1');
	
	$routes->get('/data/labManagers/(:any)', 'Data::labManagers/$1');
	$routes->post('/data/labManagerList', 'Data::labManagerList');
	$routes->post('/data/labManagerAdd', 'Data::labManagerAdd');
	$routes->post('/data/labManagerDelete', 'Data::labManagerDelete');
	
	$routes->get('/masterdata/students', 'MasterData::student');
	$routes->post('/masterdata/studentList', 'MasterData::studentList');
	$routes->get('/masterdata/studentsSync', 'MasterData::studentSync');
	$routes->get('/masterdata/dosen', 'MasterData::dosen');
	$routes->post('/masterdata/dosenList', 'MasterData::dosenList');
	$routes->get('/masterdata/dosenSync', 'MasterData::dosenSync');
	
	$routes->get('/admin/users', 'Administrator::user');
	$routes->post('/admin/userList', 'Administrator::userList');
	$routes->get('/admin/userAdd', 'Administrator::userAdd');
	$routes->post('/admin/userAdd', 'Administrator::userAdd');
	$routes->get('/admin/userEdit/(:any)', 'Administrator::userEdit/$1');
	$routes->post('/admin/userEdit/(:any)', 'Administrator::userEdit/$1');
	$routes->post('/admin/userActive', 'Administrator::userActive');
	$routes->get('/admin/userPasswordReset/(:any)', 'Administrator::userPasswordReset/$1');
	$routes->post('/admin/userPasswordReset/(:any)', 'Administrator::userPasswordReset/$1');
	$routes->post('/admin/userDelete', 'Administrator::userDelete');
	
	$routes->get('/admin/groups', 'Administrator::group');
	$routes->post('/admin/groupList', 'Administrator::groupList');
	$routes->get('/admin/groupAdd', 'Administrator::groupAdd');
	$routes->post('/admin/groupAdd', 'Administrator::groupAdd');
	$routes->get('/admin/groupEdit/(:any)', 'Administrator::groupEdit/$1');
	$routes->post('/admin/groupEdit/(:any)', 'Administrator::groupEdit/$1');
	$routes->post('/admin/groupDelete', 'Administrator::groupDelete');
	
	$routes->get('/admin/menus', 'Administrator::menu');
	$routes->post('/admin/menuList', 'Administrator::menuList');
	$routes->get('/admin/menuAdd', 'Administrator::menuAdd');
	$routes->post('/admin/menuAdd', 'Administrator::menuAdd');
	$routes->get('/admin/menuEdit/(:any)', 'Administrator::menuEdit/$1');
	$routes->post('/admin/menuEdit/(:any)', 'Administrator::menuEdit/$1');
	$routes->post('/admin/menuDelete', 'Administrator::menuDelete');
	
	$routes->get('/admin/permissions', 'Administrator::permission');
	$routes->post('/admin/permissionList', 'Administrator::permissionList');
	$routes->get('/admin/permissionAdd', 'Administrator::permissionAdd');
	$routes->post('/admin/permissionAdd', 'Administrator::permissionAdd');
	$routes->get('/admin/permissionEdit/(:any)', 'Administrator::permissionEdit/$1');
	$routes->post('/admin/permissionEdit/(:any)', 'Administrator::permissionEdit/$1');
	$routes->post('/admin/permissionDelete', 'Administrator::permissionDelete');
	$routes->get('/admin/siteSettings', 'Administrator::siteSettings');
	$routes->post('/admin/siteSettings', 'Administrator::siteSettings');

	$routes->get('/activity', 'Activity::index');
	$routes->get('/activity/getRooms/(:num)', 'Activity::getRooms/$1');
	$routes->post('/activityList', 'Activity::activityList');
	$routes->add('/activityAdd', 'Activity::activityAdd');

});
