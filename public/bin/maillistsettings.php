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
        $aliasids = "";
        $abfrage = "SELECT `alias_id` FROM `alias_owner` WHERE `owner_username` LIKE :owner_username AND `owner_domain` LIKE :owner_domain";
        $result = $dbh->prepare($abfrage);
        $result->execute(array(':owner_username' => $_SESSION['username'], ':owner_domain' => $_SESSION['domain']));
        if ($result->rowCount() <= 0) {
            header("Location: ../settings.php");
            exit;
        }
        while ($aliases = $result->fetch()) {
            $aliasids = $aliasids . $aliases['alias_id'] . '|';
        }
    }
    echo '<html>
    <head>
    <title>Maillist Einstellung</title>
    </head>
    <body>
    <h2>Maillinglisten Einstellungen</h2>';
    if ($_SESSION['admin']) {
        echo '<a href="../admin.php"><h3>Zurück zur Adminoberfläche</h3></a><br>
        <h3>Mailliste hinzufügen</h3>
        <form name="addmaillist" method=POST action="addmaillist.php">
        <label>Listenname:<input name="newlistname" type="text" placeholder="Listenname"/></label>
        <label>Listenadresse:<input name="newlistsourceadress" type="text" placeholder="Listenadresse"/>@<select name="newlistsourcedomain">';
        $abfrage = "SELECT `id`, `domain` FROM `domains`";
        $result = $dbh->query($abfrage);
        while ($domains = $result->fetch()) {
            echo '<option value="' . $domains['domain'] . '">' . $domains['domain'] . '</option>';
        }
        echo '</select></label>
        <label>Listenbesitzer:<textarea rows="1" cols="50" name="newlistowners"></textarea></label><br>
        <label>Listenempfänger (durch Leerzeichen getrennt):<br><textarea rows="4" cols="50" name="newlistdestinations"></textarea></label>
        <label>Listensicherheitseinstellungen:<select name="newlistsecurity">
        <option value="0">0 (Jeder kann Mails an die Liste schicken)</option>
        <option value="1">1 (Mitglieder und Besitzer der Liste können Mails an die Liste schicken)</option>
        <option value="2">2 (Nur Besitzer der Liste können Mails an die Liste schicken)</option>
        </select></label><br>
        <input type="submit" name="submit" value="Hinzufügen"/></form>
        <br><h3>Bestehende Listen:</h3>';
    }
    else {
        echo '<a href="../settings.php"><h3>Zurück</h3></a><br><h3>Meine bestehenden Listen:</h3>';
    }
    if ($_SESSION['admin']) {
        $abfrage = "SELECT `id`, `name`, `owners`, `destinations`, `security` FROM `alias_details`";
        $result = $dbh->query($abfrage);
    }
    else {
        $abfrage = "SELECT `id`, `name`, `owners`, `destinations`, `security` FROM `alias_details` WHERE `id` REGEXP :aliasid";
        $result = $dbh->prepare($abfrage);
        $result->execute(array(':aliasid' => substr($aliasids, 0, -1)));
    }
    echo '<table border="1" style="text-align: center; vertical-align: middle;"><tr><th>Listenname</th><th>Listenadresse</th><th>Listenempfänger</th><th>Listenbesitzer</th><th>Listensicherheit</th><th>Optionen</th></tr>';
    while ($lists = $result->fetch()) {
        $abfrage2 = "SELECT `source_username`, `source_domain` FROM `aliases` WHERE `alias_id` LIKE :aliasid";
        $result2 = $dbh->prepare($abfrage2);
        $result2->execute(array(':aliasid' => $lists['id']));
        $listdetails = $result2->fetch();
        echo '<tr><td>' . $lists['name'] . '</td><td>' . $listdetails['source_username'] . '@' . $listdetails['source_domain'] . '</td><td>';
        foreach (explode(' ', $lists['destinations']) as $destination) {
            echo $destination . '<br>';
        }
        echo '</td><td>';
        foreach (explode(' ', $lists['owners']) as $owner) {
            echo $owner . '<br>';
        }
        echo '</td><td>' . $lists['security'] . '</td><td><a href="dellist.php?dellistid=' . $lists['id'] . '">Löschen</a><br><a href="editlistpre.php?editlistid=' . $lists['id'] . '">Editieren</a></td></tr>';
    }
    echo '</table>';
    echo '</body>
    </html>';
}
else {
    header("Location: ../index.php");
    exit;
}
?>