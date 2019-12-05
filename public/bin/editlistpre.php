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
if ($_SESSION['log']) {
    if (!$_SESSION['admin']) {
        $abfrage = "SELECT `alias_id` FROM `alias_owner` WHERE `owner_username` LIKE :owner_username AND `owner_domain` LIKE :owner_domain AND alias_id LIKE :editlistid";
        $result = $dbh->prepare($abfrage);
        $result->execute(array(':owner_username' => $_SESSION['username'], ':owner_domain' => $_SESSION['domain'], ':editlistid' => $_GET['editlistid']));
        if ($result->rowCount() <= 0) {
            header("Location: maillistsettings.php");
            exit;
        }
    }
    echo '<html>
    <head>
    <title>Mailliste editieren</title>
    </head>
    <body>
    <a href="maillistsettings.php"><h3>Zurück zur Maillistoberfläche (Editieren abbrechen)</h3></a><br>';
    $abfrage = "SELECT `name`, `owners`, `security`, `islist` FROM `alias_details` WHERE `id` LIKE :editlistid";
    $result = $dbh->prepare($abfrage);
    $result->execute(array(':editlistid' => $_GET['editlistid']));
    while ($lists = $result->fetch()) {
        $abfrage2 = "SELECT `source_username`, `source_domain` FROM `aliases` WHERE `alias_id` LIKE :aliasid";
        $result2 = $dbh->prepare($abfrage2);
        $result2->execute(array(':aliasid' => $_GET['editlistid']));
        $listdetails = $result2->fetch();
        echo'
        <form name="editlist" method=POST action="editlist.php">
        <label>Listenname:<input name="newlistname" type="text" placeholder="Listenname" value="' . $lists['name'] . '"/></label>
        <label>Listenadresse:<input name="newlistsource" type="text" placeholder="Listenadresse" value="' . $listdetails['source_username'] . '@' . $listdetails['source_domain'] . '"/></label>
        <label>Listenbesitzer:<textarea rows="1" cols="50" name="newlistowners">' . $lists['owners'] . '</textarea></label><br>
        <label>Listenempfänger (durch Leerzeichen getrennt):<br><textarea rows="4" cols="50" name="newlistdestinations">';
        $abfrage3 = "SELECT `destination_username`, `destination_domain` FROM `aliases` WHERE `alias_id` LIKE :aliasid";
        $result3 = $dbh->prepare($abfrage3);
        $result3->execute(array(':aliasid' => $_GET['editlistid']));
        $listdestinations = "";
        while ($listdestination = $result3->fetch()) {
            $listdestinations = $listdestinations . $listdestination['destination_username'] . '@' . $listdestination['destination_domain'] . ' ';
        }
        echo substr($listdestinations, 0, -1) . '</textarea></label>
        <label>Listensicherheitseinstellungen:<select name="newlistsecurity">
        <option value="0"';
        if ($lists['security'] == 0) echo ' selected';
        echo '>0 (Jeder kann Mails an die Liste schicken)</option>
        <option value="1"';
        if ($lists['security'] == 1) echo ' selected';
        echo '>1 (Mitglieder der Liste können Mails an die Liste schicken)</option>
        <option value="2"';
        if ($lists['security'] == 2) echo ' selected';
        echo '>2 (Der Besitzer der Liste kann Mails an die Liste schicken)</option>
        </select></label><br>
        <label>Ist eine Maillingliste, keine einfache Umleitung<input type="checkbox" name="newlistislist" value="1"';
        if ($lists['islist'] == 1) echo ' checked';
        echo '/></label>
        <input type="hidden" name="editlistid" value="' . $_GET['editlistid'] . '"/>
        <input type="submit" name="submit" value="Editieren"/>
        </body>
        </html>';
    }
} else {
    header("Location: ../index.php");
    exit;
}
?>