<?php
require_once('database.php');

//error_reporting(E_ERROR | E_PARSE);
header('Access-Control-Allow-Origin:http://localhost');
header('Access-Control-Allow-Credentials: true');

session_start();

//dozvoljavam samo POST i GET metodu u svakom slučaju prepunjava se $injson varijabla
switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        $injson = json_encode($_POST);
        break;
    case 'GET':
        $injson = json_encode($_GET);
        break;
    default:
        echo $request_err;
        return;
}

$in_obj = json_decode($injson);

//provjera podataka koji su došli s inputa
//print_r($in_obj);

//logout, pozivam prije konekcije na bazu jer je ne trebam, radim samo sa SESSIONom na serveru
if ($in_obj->procedura == "p_logout") {
    session_destroy();
    echo $logout;
    return;
}
//ako session nije kreiran samo p_login mogu zvati
if (!isset($_SESSION['ID']) && $in_obj->procedura != "p_login") {
    echo $login_err;
    return;
}

//refresh, vraćam podatke iz SESSIONa i to napravim prije konekcije na bazu, ne treba mi baza za ovo
if (isset($_SESSION['ID']) && $in_obj->procedura == "p_refresh") {
    echo json_encode($_SESSION);
    return;
}


try {
    $db = f_get_database();
} catch (Exception $e) {
    echo $database_error;
    return;
}


//raspoređujem pozive prema funkcijama
switch ($in_obj->procedura) {
    case 'p_login':
        f_login($db, $in_obj);
        break;
    case 'p_get_korisnik':
        f_get_korisnik($db, $in_obj);
        break;
    case 'p_get_pizza':
        f_get_pizza($db, $in_obj);
        break;
    case 'p_get_orders':
        f_get_orders($db, $in_obj);
        break;
    case 'p_add_order':
        f_add_order($db, $in_obj);
        break;
    case 'p_get_order':
        f_get_order($db, $in_obj);
        break;
    case 'p_save_korisnik':
        f_save_korisnik($db, $in_obj);
        break;
    case 'p_save_pizza':
        f_save_pizza($db, $in_obj);
        break;
    case 'p_save_orders':
        f_save_orders($db, $in_obj);
        break;
    case 'p_delete_korisnik':
        f_delete_korisnik($db, $in_obj);
        break;
    case 'p_delete_pizza':
        f_delete_pizza($db, $in_obj);
        break;
    case 'p_delete_order':
        f_delete_order($db, $in_obj);
        break;
    default:
        echo $request_err;
        return;
}

?>