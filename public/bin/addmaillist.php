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
if ($_SESSION['log'] == 1 && $_SESSION['admin']) {
    if (!isset($_POST['newlistislist'])) $islist = 0; // wenn die checkbox nicht ausgewählt wurde ist die Post Variable nicht gesetzt, dass stört die Datenbank, deshalb wird Null eingertragen
    else $islist = $_POST['newlistislist'];
    $eintrag = "INSERT INTO `alias_details` (`name`, `owners`, `destinations`, `security`, `islist`) VALUES (:newlistname, :owners, :destinations, :security, :islist)"; // Aliasdaten in MailServer DB eintragen
    $sth = $dbh->prepare($eintrag);
    $sth->execute(array(':newlistname' => $_POST['newlistname'], ':owners' => $_POST['newlistowners'], ':destinations' => $_POST['newlistdestinations'], ':security' => $_POST['newlistsecurity'], ':islist' => $islist));
    $newlistid = $dbh->lastInsertID();
    foreach (explode(' ', $_POST['newlistowners']) as $maillistowner) {
        $maillistownerex = explode('@', $maillistowner);
        $eintrag = "INSERT INTO `alias_owner` (`alias_id`, `owner_username`, `owner_domain`) VALUES (:aliasid, :owner_username, :owner_domain)"; // Aliasdaten in MailServer DB eintragen
        $sth = $dbh->prepare($eintrag);
        $sth->execute(array(':aliasid' => $newlistid, ':owner_username' => $maillistownerex[0], ':owner_domain' => $maillistownerex[1]));
    }
    foreach (explode(' ', $_POST['newlistdestinations']) as $maillistdestination) {
        $maillistdestinationex = explode('@', $maillistdestination);
        $eintrag = "INSERT INTO `aliases` (`alias_id`, `source_username`, `source_domain`, `destination_username`, `destination_domain`) VALUES (:aliasid, :source_username, :source_domain, :destination_username, :destination_domain)"; // Aliasdaten in MailServer DB eintragen
        $sth = $dbh->prepare($eintrag);
        $sth->execute(array(':aliasid' => $newlistid, ':source_username' => $_POST['newlistsourceadress'], ':source_domain' => $_POST['newlistsourcedomain'], ':destination_username' => $maillistdestinationex[0], ':destination_domain' => $maillistdestinationex[1]));
    }
}
    header("Location: maillistsettings.php");
    exit;
?>