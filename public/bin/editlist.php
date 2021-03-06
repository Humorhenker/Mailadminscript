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

use phpDocumentor\Reflection\Types\Null_;

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
        $result->execute(array(':owner_username' => $_SESSION['username'], ':owner_domain' => $_SESSION['domain'], ':editlistid' => $_POST['editlistid']));
        if ($result->rowCount() <= 0) {
            header("Location: maillistsettings.php");
            exit;
        }
    }
    $newlistowner = explode('@', $_POST['newlistowners']);
    if (!isset($_POST['newlistislist'])) $islist = 0; // wenn die checkbox nicht ausgewählt wurde ist die Post Variable nicht gesetzt, dass stört die Datenbank, deshalb wird Null eingertragen
    else $islist = $_POST['newlistislist'];
    $eintrag = "UPDATE `alias_details` SET `name` = :newlistname, `owners` = :owners, `destinations` = :destinations, `security` = :security, `islist` = :islist WHERE `id` LIKE :editlistid"; // Aliasdaten in MailServer DB eintragen
    $sth = $dbh->prepare($eintrag);
    $sth->execute(array(':newlistname' => $_POST['newlistname'], ':owners' => $_POST['newlistowners'], ':destinations' => $_POST['newlistdestinations'], ':security' => $_POST['newlistsecurity'], ':islist' => $islist, ':editlistid' => $_POST['editlistid']));
    $eintrag = "DELETE FROM `alias_owner` WHERE `alias_id` LIKE :aliasid";
    $sth = $dbh->prepare($eintrag);
    $sth->execute(array(':aliasid' => $_POST['editlistid']));
    foreach (explode(' ', $_POST['newlistowners']) as $maillistowner) {
        $maillistownerex = explode('@', $maillistowner);
        $eintrag = "INSERT INTO `alias_owner` (`alias_id`, `owner_username`, `owner_domain`) VALUES (:aliasid, :owner_username, :owner_domain)"; // Aliasdaten in MailServer DB eintragen
        $sth = $dbh->prepare($eintrag);
        $sth->execute(array(':aliasid' => $_POST['editlistid'], ':owner_username' => $maillistownerex[0], ':owner_domain' => $maillistownerex[1]));
    }
    if ($_SESSION['admin']) {
        $newlistsource = explode('@', $_POST['newlistsource']);
    } else {
        $abfrage = "SELECT `source_username`, `source_domain` FROM `aliases` WHERE `alias_id` LIKE :alias_id";
        $result = $dbh->prepare($abfrage);
        $result->execute(array(':alias_id' => $_POST['editlistid']));
        $newlistsource = $result->fetch(); //bei fetch() werden im Array ['spaltenname'] und [#Nummer der Spalte] angelegt also ['source_username'] und [0] praktische Sache
    }

    $abfrage = "SELECT `id`, `destination_username`, `destination_domain` FROM `aliases` WHERE `alias_id` LIKE :alias_id";
    $result = $dbh->prepare($abfrage);
    $result->execute(array(':alias_id' => $_POST['editlistid']));
    $oldlistdestinations = array(array(),array(),array(),array());
    while ($row = $result->fetch()) {
        $oldlistdestinations[0][] = $row['id'];
        $oldlistdestinations[1][] = $row['destination_username'];
        $oldlistdestinations[2][] = $row['destination_domain'];
        $oldlistdestinations[3][] = $row['destination_username'] . '@' . $row['destination_domain'];
    }
    $newlistdestinations = array(array(),array(),array());
    foreach (explode(' ', $_POST['newlistdestinations']) as $newlistdestination) {
        $newlistdestinationex = explode('@', $newlistdestination);
        array_push($newlistdestinations[0], $newlistdestinationex[0]);
        array_push($newlistdestinations[1], $newlistdestinationex[1]);
        array_push($newlistdestinations[2], $newlistdestinationex[0] . '@' . $newlistdestinationex[1]);
    }
    $dellistdestinations = array();
    foreach ($oldlistdestinations[3] as $key => $oldlistdestination) {
        if (!in_array($oldlistdestination, $newlistdestinations[2])) {
            array_push($dellistdestinations, $oldlistdestinations[0][$key]);
        }
    }
    $addlistdestinations = array();
    foreach ($newlistdestinations[2] as $key => $newlistdestination) {
        if (!in_array($newlistdestination, $oldlistdestinations[3])) {
            array_push($addlistdestinations, [$newlistdestinations[0][$key], $newlistdestinations[1][$key]]);
        }
    }
    foreach ($dellistdestinations as $dellistdestination) {
        $eintrag = "DELETE FROM `aliases` WHERE `id` LIKE :id";
        $sth = $dbh->prepare($eintrag);
        $sth->execute(array(':id' => $dellistdestination));
    }
    foreach ($addlistdestinations as $addlistdestination) {
        $eintrag = "INSERT INTO `aliases` (`alias_id`, `source_username`, `source_domain`, `destination_username`, `destination_domain`) VALUES (:aliasid, :source_username, :source_domain, :destination_username, :destination_domain)"; // Aliasdaten in MailServer DB eintragen
        $sth = $dbh->prepare($eintrag);
        $sth->execute(array(':aliasid' => $_POST['editlistid'], ':source_username' => $newlistsource[0], ':source_domain' => $newlistsource[1], ':destination_username' => $addlistdestination[0], ':destination_domain' => $addlistdestination[1]));  
    }
    header("Location: maillistsettings.php");
    exit;
} else {
    header("Location: ../index.php");
    exit;
}
?>