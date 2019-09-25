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
$eintrag = "DELETE FROM `alias_del_requests` WHERE DATEDIFF(NOW(), `created`) > :datediff";
$sth = $dbh->prepare($eintrag);
$sth->execute(array(':datediff' => $config['deletedelrequestdaydiff']));
if (isset($_GET['token'])) {
    $abfrage = "SELECT `alias_id`, `destination_username`, `destination_domain` FROM `alias_del_requests` WHERE `token` LIKE :token";
    $result = $dbh->prepare($abfrage);
    $result->execute(array(':token' => $_GET['token']));
    if ($result->rowCount() > 0) {
        $daten = $result->fetch();
        $aliasid = $daten['alias_id'];
        $destination_username = $daten['destination_username'];
        $destination_domain = $daten['destination_domain'];
        $eintrag = "DELETE FROM `aliases` WHERE `alias_id` LIKE :aliasid AND `destination_username` LIKE :destination_username AND `destination_domain` LIKE :destination_domain";
        $sth = $dbh->prepare($eintrag);
        $sth->execute(array(':aliasid' => $aliasid, ':destination_username' => $destination_username, ':destination_domain' => $destination_domain));

        // Destinationszeile neu generieren (würde bestimmt einfacher gehen)
        $abfrage2 = "SELECT `destination_username`, `destination_domain` FROM `aliases` WHERE `alias_id` LIKE :aliasid";
        $result2 = $dbh->prepare($abfrage2);
        $result2->execute(array(':aliasid' => $aliasid));
        $listdestinations = "";
        while ($listdestination = $result2->fetch()) {
            $listdestinations = $listdestinations . $listdestination['destination_username'] . '@' . $listdestination['destination_domain'] . ' ';
        }
        $eintrag = "UPDATE `alias_details` SET `destinations` = :destinations WHERE `id` LIKE :aliasid";
        $sth = $dbh->prepare($eintrag);
        $sth->execute(array(':destinations' => substr($listdestinations, 0, -1), ':aliasid' => $aliasid));
        $eintrag = "DELETE FROM `alias_del_requests` WHERE `token` LIKE :token";
        $sth = $dbh->prepare($eintrag);
        $sth->execute(array(':token' => $_GET['token']));
        print_r($_GET['token']);
        header("Location: ../unsub.php?success=1");
        exit;
    } else {
        header("Location: ../unsub.php?unknowntoken=1");
        exit;
    }
} else {
    header("Location: ../index.php");
    exit;
}
?>