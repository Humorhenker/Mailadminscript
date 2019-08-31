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
    echo '<html>
    <head>
    <title>Maillist Einstellung</title>
    </head>
    <body>
    <h2>Maillinglisten Einstellungen</h2>
    <a href="../admin.php"><h3>Zurück zur Adminoberfläche</h3></a><br>
    <form name="addmaillist" method=POST action="addmaillist.php">
    <label>Listenname:<input name="maillistname" type="text" placeholder="Listenname"/></label>
    <label>Listenadresse:<input name="maillistsource" type="text" placeholder="Listenadresse"/></label>
    <label>Listenbesitzer:<select name="maillistownerid">';
    $abfrage = "SELECT `id`, `email` FROM `virtual_users`";
    $result = $dbh->query($abfrage);
    while ($emails = $result->fetch()) {
        echo '<option value="' . $emails['id'] . '">' . $emails['email'] . '</option>';
    }
    echo '</select></label><br>
    <label>Listenempfänger (durch Leerzeichen getrennt):<br><textarea rows="4" cols="50" name="maillistadresses"></textarea></label>
    <label>Listensicherheitseinstellungen:<select name="listprivate">
    <option value="0">0 (Jeder kann Mails an die Liste schicken)</option>
    <option value="1">1 (Mitglieder der Liste können Mails an die Liste schicken)</option>
    <option value="2">2 (Der Besitzer der Liste kann Mails an die Liste schicken)</option>
    </select></label><br>
    <input type="submit" name="submit" value="Hinzufügen"/>
    <br>
    <h3>Bestehende Listen:</h3>
    ';
    $abfrage = "SELECT `id`, `source`, `destination`, `owner`, `private`, `name` FROM `virtual_aliases`";
    $result = $dbh->query($abfrage);
    echo '<table style="text-align: center; vertical-align: middle;"><tr><th>Listenname</th><th>Listenadresse</th><th>Listenempfänger</th><th>Listenbesitzer</th><th>Listensicherheit</th><th>Optionen</th></tr>';
    while ($lists = $result->fetch()) {
        $abfrage2 = "SELECT `email` FROM `virtual_users` WHERE `id` LIKE :ownerid";
        $sth = $dbh->prepare($abfrage2);
        $sth->execute(array('ownerid' => $lists['owner']));
        $result2 = $sth->fetchAll();
        echo '<tr><td>' . $lists['name'] . '</td><td>' . $lists['source'] . '</td><td>' . $lists['destination'] . '</td><td>' . $result2[0]['email'] . '</td><td>' . $lists['private'] . '</td><td><a href="dellist.php?dellistid=' . $lists['id'] . '">Löschen</a><br><a href="editlistpre.php?editlistid=' . $lists['id'] . '">Editieren</a></td></tr>';
    }
    echo '</table>';
    echo '</body>
    </html>';
}
?>