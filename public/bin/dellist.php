<?php
/*  Mailadminscript
    Copyright (C) 2019  Paul Schürholz contact AT roteserver . de

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>. */
$config = parse_ini_file('../../private/config.ini');
try {
    $dbh = new PDO('mysql:host=' . $config['dbservername'] . ';dbname=' . $config['dbname'], $config['dbusername'], $config['dbpassword'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
} catch (PDOException $e) {
    //echo 'Connection failled: '. $e->getMessage(); // Errormessage kann Sicherheitsrelevantes enthalen
    echo 'Connection failed';
}
session_start();
if ($_SESSION['log'] == 1) {
    if (!$_SESSION['admin']) {
        $abfrage = "SELECT `alias_id` FROM `alias_owner` WHERE `owner_username` LIKE :owner_username AND `owner_domain` LIKE :owner_domain AND alias_id LIKE :editlistid";
        $result = $dbh->prepare($abfrage);
        $result->execute(array(':owner_username' => $_SESSION['username'], ':owner_domain' => $_SESSION['domain'], ':editlistid' => $_GET['editlistid']));
        if ($result->rowCount() <= 0) {
            header("Location: maillistsettings.php");
            exit;
        }
    }
    $eintrag = "DELETE FROM `aliases` WHERE `alias_id` LIKE :aliasid;  DELETE FROM `alias_owner` WHERE `alias_id` LIKE :aliasid; DELETE FROM `alias_details` WHERE `id` LIKE :aliasid";
    $sth = $dbh->prepare($eintrag);
    $sth->execute(array(':aliasid' => $_GET['dellistid']));
    header("Location: maillistsettings.php");
    exit;
} else {
    header("Location: ../index.php");
    exit;
}
?>