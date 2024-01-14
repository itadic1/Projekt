<?php //konekcija na bazu
require_once('db_credential.php');
require_once('poruke.php');

function f_get_database()
{
    $db = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
    if ($db->connect_errno) {
        throw new Exception("Neuspješna konekcija na bazu");
    }
    return $db;
}

function f_login($db, $in_obj)
{
    global $wrong_login;
    $sql = "SELECT * FROM korisnik WHERE EMAIL='" . $in_obj->username . "' AND PASSWORD='" . $in_obj->password . "'";

    $rows = f_get_rows($db, $sql);

    if (!empty($rows)) {
        $_SESSION = $rows[0];
        echo json_encode($rows);
    } else {
        echo $wrong_login;
    }
}

function f_get_korisnik($db, $in_obj)
{
    if (isset($in_obj->ID)) {
        $sql = "SELECT * FROM korisnik where ID = " . $in_obj->ID;
    } else {
        $offset = $in_obj->perPage * ($in_obj->page - 1);
        $sql = "SELECT * FROM korisnik WHERE deleted = 0 LIMIT $in_obj->perPage OFFSET $offset";
        $output['count'] = f_get_count($db, "SELECT count(1) from korisnik;");
    }
    $output['data'] = f_get_rows($db, $sql);
    echo json_encode($output);
}

function f_get_pizza($db, $in_obj)
{
    if (isset($in_obj->ID)) {
        $sql = "SELECT * FROM pizza where ID = " . $in_obj->ID;
    } else {
        $offset = $in_obj->perPage * ($in_obj->page - 1);
        $sql = "SELECT * FROM pizza WHERE deleted = 0 LIMIT $in_obj->perPage OFFSET $offset";
        $output['count'] = f_get_count($db, "SELECT count(1) from pizza;");
    }
    $output['data'] = f_get_rows($db, $sql);
    echo json_encode($output);
}

function f_get_orders($db, $in_obj)
{
    $sql = "SELECT o.ID, p.NAZIV, o.QUANTITY, p.CIJENA
FROM common.orderstable o
JOIN common.pizza p ON o.ITEMID = p.ID
WHERE o.USERID =" . $in_obj->ID;
    $output['data'] = f_get_rows($db, $sql);
    echo json_encode($output);
}

function f_get_order($db, $in_obj)
{
    $sql = "SELECT o.ID, p.NAZIV, o.QUANTITY, p.CIJENA
FROM common.orderstable o
JOIN common.pizza p ON o.ITEMID = p.ID
WHERE o.ID =" . $in_obj->ID;

    $output['data'] = f_get_rows($db, $sql);
    echo json_encode($output);
}


function f_save_korisnik($db, $in_obj)
{
    global $insert_error;
    global $insert_pass;
    global $update_error;
    global $update_pass;

    $poruka = f_kontrole_korisnik($in_obj);

    if (isset($poruka)) {
        echo $poruka;
        return;
    }

    if (isset($in_obj->ID)) {
        //prepared statement je bolja opcija
        $sql = "UPDATE korisnik SET "
            . "ime = '" . $in_obj->ime . "', "
            . "prezime = '" . $in_obj->prezime . "', "
            . "BRMOB = " . $in_obj->BRMOB . ", "
            . "OIB = " . $in_obj->OIB . ", "
            . "email = '" . $in_obj->email . "', "
            . "SPOL = " . $in_obj->SPOL
            . " WHERE ID = " . $in_obj->ID;
        //echo $sql;
        $db->set_charset("utf8");
        if ($db->query($sql) === TRUE) {
            echo $update_pass;
        } else {
            echo $update_error;
        }
    } else {
        $sql = "INSERT INTO korisnik (ime, prezime, BRMOB, OIB, email, PASSWORD, SPOL) VALUES "
            . "( "
            . "'" . $in_obj->ime . "'" . ","
            . "'" . $in_obj->prezime . "'" . ","
            . $in_obj->BRMOB . ","
            . $in_obj->OIB . ","
            . "'" . $in_obj->email . "'" . ","
            . "'" . $in_obj->PASSWORD . "'" . ","
            . $in_obj->SPOL
            . ")";
        //echo $sql;

        $db->set_charset("utf8");
        if ($db->query($sql) === TRUE) {
            echo $insert_pass;
        } else {
            echo $insert_error;
        }
    }
}

function f_save_pizza($db, $in_obj)
{
    global $insert_error;
    global $insert_pass;
    global $update_error;
    global $update_pass;

    $poruka = f_kontrole_pizza($in_obj);

    if (isset($poruka)) {
        echo $poruka;
        return;
    }

    if (isset($in_obj->ID)) {
        $sql = "UPDATE pizza SET "
            . "NAZIV = '" . $in_obj->NAZIV . "', "
            . "SASTOJCI = '" . $in_obj->SASTOJCI . "', "
            . "CIJENA = " . $in_obj->CIJENA
            . " WHERE ID = " . $in_obj->ID;
        echo $sql;
        $db->set_charset("utf8");
        if ($db->query($sql) === TRUE) {
            echo $update_pass;
        } else {
            echo $update_error;
        }
    } else {
        $sql = "INSERT INTO pizza (TYPEID, NAZIV, SASTOJCI, CIJENA) VALUES "
            . "( "
            . $in_obj->TYPEID
            . "'" . $in_obj->NAZIV . "'" . ","
            . "'" . $in_obj->SASTOJCI . "'" . ","
            . $in_obj->CIJENA
            . ")";
        //echo $sql;

        $db->set_charset("utf8");
        if ($db->query($sql) === TRUE) {
            echo $insert_pass;
        } else {
            echo $insert_error;
        }
    }
}

function f_save_orders($db, $in_obj)
{
    global $update_error;
    global $update_pass;

    $sql = "UPDATE orderstable SET "
        . "QUANTITY = " . $in_obj->QUANTITY
        . " WHERE ID = " . $in_obj->ID;
    echo $sql;
    $db->set_charset("utf8");
    if ($db->query($sql) === TRUE) {
        echo $update_pass;
    } else {
        echo $update_error;
    }
}
function f_add_order($db, $in_obj)
{
    global $insert_error;
    global $insert_pass;
    global $update_error;
    global $update_pass;

    $count_query = "SELECT COUNT(*) as count
                FROM orderstable
                WHERE USERID =" . $in_obj->UID . " AND ITEMID =" . $in_obj->ID;

    $count_result = $db->query($count_query);

    $row = $count_result->fetch_assoc();
    $count = $row['count'];

    echo $count;

    if ($count > 0) {
        $sql = "UPDATE orderstable SET "
            . "QUANTITY = QUANTITY + 1"
            . " WHERE USERID = " . $in_obj->UID
            . " AND ITEMID =" . $in_obj->ID;
        $db->set_charset("utf8");
        echo $sql;
        if ($db->query($sql) === TRUE) {
            echo $update_pass;
        } else {
            echo $update_error;
        }
    } else {
        $sql = "INSERT INTO orderstable (USERID, ITEMID, QUANTITY) VALUES "
            . "( "
            . $in_obj->UID . ","
            . $in_obj->ID . ","
            . 1
            . ")";
        $db->set_charset("utf8");
        echo $sql;
        if ($db->query($sql) === TRUE) {
            echo $insert_pass;
        } else {
            echo $insert_error;
        }
    }

    $count_result->free_result();
}


function f_delete_korisnik($db, $in_obj)
{
    global $delete_error;
    global $delete_pass;

    $sql = "UPDATE korisnik SET "
        . "deleted = 1 WHERE ID = " . $in_obj->ID;

    if ($db->query($sql) === TRUE) {
        echo $delete_pass;
    } else {
        echo $delete_error;
    }
}
function f_delete_pizza($db, $in_obj)
{
    global $delete_error;
    global $delete_pass;

    $sql = "UPDATE pizza SET "
        . "deleted = 1 WHERE ID = " . $in_obj->ID;

    if ($db->query($sql) === TRUE) {
        echo $delete_pass;
    } else {
        echo $delete_error;
    }
}

function f_delete_order($db, $in_obj)
{
    global $delete_error;
    global $delete_pass;

    $sql = "DELETE FROM orderstable WHERE ID = " . $in_obj->ID;

    if ($db->query($sql) === TRUE) {
        echo $delete_pass;
    } else {
        echo $delete_error;
    }
}

function f_kontrole_korisnik($in_obj)
{
    global $empty_ime, $empty_prezime, $empty_phone_number, $incorrect_phone_number,
    $empty_oib, $incorrect_oib, $empty_email, $invalid_email, $empty_spol, $invalid_spol;

    $errors = array();

    if (empty($in_obj->ime)) {
        $errors['ime'] = $empty_ime;
    }

    if (empty($in_obj->prezime)) {
        $errors['prezime'] = $empty_prezime;
    }

    if (empty($in_obj->BRMOB)) {
        $errors['BRMOB'] = $empty_phone_number;
    } elseif (!is_numeric($in_obj->BRMOB) || strlen($in_obj->BRMOB) !== 10) {
        $errors['BRMOB'] = $incorrect_phone_number;
    }

    if (empty($in_obj->OIB)) {
        $errors['OIB'] = $empty_oib;
    } elseif (!is_numeric($in_obj->OIB) || strlen($in_obj->OIB) !== 11) {
        $errors['OIB'] = $incorrect_oib;
    }

    if (empty($in_obj->email)) {
        $errors['email'] = $empty_email;
    } elseif (!filter_var($in_obj->email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = $invalid_email;
    }

    if (!isset($in_obj->SPOL)) {
        $errors['SPOL'] = $empty_spol;
    } /*elseif ($in_obj->SPOL !== 0 && $in_obj->SPOL !== 1) {
        $errors['SPOL'] = $invalid_spol;
    }*/

    if (!empty($errors)) {
        return reset($errors);
    }

    return null;
}

function f_kontrole_pizza($in_obj)
{
    global $empty_TypeID, $invalid_TypeID, $empty_naziv, $empty_sastojci, $empty_cijena, $invalid_cijena;

    $errors = array();

    if (!isset($in_obj->TYPEID)) {
        $errors['TYPEID'] = $empty_TypeID;
    } /*elseif ($in_obj->TYPEID !== 0 && $in_obj->TYPEID !== 1) {
        $errors['TYPEID'] = $invalid_TypeID;
    }*/

    if (empty($in_obj->NAZIV)) {
        $errors['NAZIV'] = $empty_naziv;
    }

    if (empty($in_obj->SASTOJCI)) {
        $errors['SASTOJCI'] = $empty_sastojci;
    }

    if (empty($in_obj->CIJENA)) {
        $errors['CIJENA'] = $empty_cijena;
    } elseif (!is_numeric($in_obj->CIJENA)) {
        $errors['CIJENA'] = $invalid_cijena;
    }

    if (!empty($errors)) {
        return reset($errors);
    }

    return null;
}

function f_get_count($db, $sql)
{
    $result = $db->query($sql);
    $row = mysqli_fetch_assoc($result);
    return $row['count(1)'];
}

function f_get_rows($db, $sql)
{
    $db->set_charset("utf8");
    $result = $db->query($sql);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

//echo $poruka;
?>