-- phpMyAdmin SQL Dump
-- version 4.6.6deb4
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Erstellungszeit: 30. Jan 2020 um 11:19
-- Server-Version: 10.4.10-MariaDB-1:10.4.10+maria~stretch
-- PHP-Version: 7.0.33-0+deb9u6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `datenschutzdiepholzintern`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ad_personen`
--

CREATE TABLE `ad_personen` (
  `person_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `strasse` varchar(512) NOT NULL,
  `plz` varchar(8) NOT NULL,
  `ort` varchar(255) NOT NULL,
  `telefon` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `internet` varchar(255) NOT NULL,
  `organisation_id` int(11) NOT NULL,
  `gruppe_id` int(11) NOT NULL,
  `type` enum('verantwortlich','adsb','empfaenger_intern','empfaenger_extern','empfaenger_drittland') NOT NULL,
  `ad_username` varchar(50) NOT NULL,
  `added` int(11) NOT NULL,
  `dn` varchar(512) NOT NULL,
  `anrede` varchar(10) NOT NULL,
  `vorname` varchar(255) NOT NULL,
  `nachname` varchar(55) NOT NULL,
  `fachdienst` varchar(255) NOT NULL,
  `raum` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `datenkategorie`
--

CREATE TABLE `datenkategorie` (
  `datenkategorie_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `beschreibung` varchar(255) NOT NULL,
  `gruppe_id` int(11) NOT NULL,
  `organisation_id` int(11) NOT NULL,
  `loeschfristen` varchar(255) NOT NULL,
  `besondere_kategorie` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `datenkategorie_personengruppe`
--

CREATE TABLE `datenkategorie_personengruppe` (
  `dp_id` int(11) NOT NULL,
  `verfahren_datenkategorie_id` int(11) NOT NULL,
  `verfahren_personengruppe_id` int(11) NOT NULL,
  `vorgang_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `dokumente`
--

CREATE TABLE `dokumente` (
  `dokument_id` int(11) NOT NULL,
  `name` text NOT NULL,
  `filename` varchar(255) NOT NULL,
  `extension` varchar(3) NOT NULL,
  `typ` varchar(10) NOT NULL,
  `object_id` int(11) NOT NULL,
  `deleted` int(11) NOT NULL,
  `size` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `gruppe`
--

CREATE TABLE `gruppe` (
  `gruppe_id` int(11) NOT NULL,
  `organisation_id` int(11) NOT NULL,
  `bezeichnung` varchar(55) NOT NULL,
  `alt_bezeichnung` varchar(255) NOT NULL,
  `ad_name` varchar(100) NOT NULL,
  `id_fdl` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `massnahmen`
--

CREATE TABLE `massnahmen` (
  `massnahme_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `organisation_id` int(11) NOT NULL,
  `gruppe_id` int(11) NOT NULL,
  `type` enum('technisch','organisatorisch') NOT NULL,
  `shortcode` varchar(10) NOT NULL,
  `adv_vertrag` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `organisation`
--

CREATE TABLE `organisation` (
  `organisation_id` int(11) NOT NULL,
  `bezeichnung` varchar(55) NOT NULL,
  `ad_name` varchar(255) NOT NULL,
  `adsb_id` int(11) NOT NULL,
  `default_verantwortliche_person` int(11) NOT NULL,
  `upload_enabled` int(11) NOT NULL,
  `pronomen` varchar(10) NOT NULL DEFAULT 'der',
  `anschrift` varchar(50) NOT NULL,
  `plz` varchar(7) NOT NULL,
  `ort` varchar(20) NOT NULL,
  `pdf_portal` varchar(250) NOT NULL,
  `beschwerde_email` varchar(100) NOT NULL,
  `imagepathpng` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `personen`
--

CREATE TABLE `personen` (
  `person_id` int(11) NOT NULL,
  `name` varchar(70) NOT NULL,
  `strasse` varchar(70) NOT NULL,
  `plz` varchar(8) NOT NULL,
  `ort` varchar(90) NOT NULL,
  `telefon` varchar(40) NOT NULL,
  `email` varchar(50) NOT NULL,
  `internet` varchar(70) NOT NULL,
  `organisation_id` int(11) NOT NULL,
  `gruppe_id` int(11) NOT NULL,
  `type` enum('verantwortlich','adsb','empfaenger_intern','empfaenger_extern','empfaenger_drittland','ansprechpartner') NOT NULL,
  `ad_username` varchar(50) NOT NULL,
  `added` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `personengruppen`
--

CREATE TABLE `personengruppen` (
  `personengruppe_id` int(11) NOT NULL,
  `bezeichnung` varchar(255) NOT NULL,
  `anzahl_personen` varchar(55) NOT NULL,
  `gruppe_id` int(11) NOT NULL,
  `organisation_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `protokoll`
--

CREATE TABLE `protokoll` (
  `protokoll_id` int(11) NOT NULL,
  `time_created` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `object_id` int(11) NOT NULL,
  `object_typ` enum('verfahren','person') NOT NULL,
  `old_value` varchar(255) NOT NULL,
  `new_value` varchar(255) NOT NULL,
  `value_key` varchar(55) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `software`
--

CREATE TABLE `software` (
  `software_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `anbieter` varchar(200) NOT NULL,
  `software_id_extern` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `software_gruppe`
--

CREATE TABLE `software_gruppe` (
  `sg_id` int(11) NOT NULL,
  `software_id` int(11) NOT NULL,
  `gruppe_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `software_massnahmen`
--

CREATE TABLE `software_massnahmen` (
  `sm_id` int(11) NOT NULL,
  `software_id` int(11) NOT NULL,
  `massnahme_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `username` varchar(55) NOT NULL,
  `default_organisation_id` int(11) NOT NULL,
  `default_gruppe_id` int(11) NOT NULL,
  `right_can_delete` int(11) NOT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `verfahren`
--

CREATE TABLE `verfahren` (
  `verfahren_id` int(11) NOT NULL,
  `bezeichnung` varchar(255) NOT NULL,
  `beschreibung` text NOT NULL,
  `gruppe_id` int(11) NOT NULL,
  `datum_einfuehrung` date NOT NULL,
  `id_verantwortlich` int(11) NOT NULL,
  `id_mitverantwortlich` int(11) NOT NULL,
  `id_verantwortlichstellv` int(11) NOT NULL,
  `id_adsb` int(11) NOT NULL,
  `bezeichnung_verfahren` varchar(255) NOT NULL,
  `rechtliche_grundlage` varchar(255) NOT NULL,
  `id_ansprechpartner` int(11) NOT NULL,
  `vollstaendig` tinyint(1) NOT NULL,
  `beispiel` tinyint(1) NOT NULL DEFAULT 1,
  `upload` int(11) NOT NULL,
  `docs_manual` int(1) NOT NULL,
  `docs_auto` int(11) NOT NULL DEFAULT 1,
  `art6_1` int(1) NOT NULL,
  `art6_2` int(1) NOT NULL,
  `art6_3` int(1) NOT NULL,
  `art6_4` int(1) NOT NULL,
  `art6_5` int(1) NOT NULL,
  `art6_6` int(1) NOT NULL,
  `art1314` int(11) NOT NULL,
  `art14_unternehmen` text NOT NULL,
  `upload_enabled` int(1) NOT NULL,
  `artderverarbeitung` text NOT NULL,
  `infoschreiben` int(11) NOT NULL,
  `last_rendering_infoblatt` int(11) NOT NULL,
  `last_rendering_einwilligung` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `verfahren_datenkategorie`
--

CREATE TABLE `verfahren_datenkategorie` (
  `vd_id` int(11) NOT NULL,
  `verfahren_id` int(11) NOT NULL,
  `datenkategorie_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `verfahren_massnahmen`
--

CREATE TABLE `verfahren_massnahmen` (
  `vm_id` int(11) NOT NULL,
  `verfahren_id` int(11) NOT NULL,
  `massnahme_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `verfahren_personengruppe`
--

CREATE TABLE `verfahren_personengruppe` (
  `vp_id` int(11) NOT NULL,
  `verfahren_id` int(11) NOT NULL,
  `personengruppe_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `verfahren_software`
--

CREATE TABLE `verfahren_software` (
  `vs_id` int(11) NOT NULL,
  `verfahren_id` int(11) NOT NULL,
  `software_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `verfahren_weitergabe`
--

CREATE TABLE `verfahren_weitergabe` (
  `vw_id` int(11) NOT NULL,
  `verfahren_id` int(11) NOT NULL,
  `weitergabe_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `weitergabe`
--

CREATE TABLE `weitergabe` (
  `weitergabe_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` enum('fachdienst','extern','drittland') NOT NULL,
  `organisation_id` int(11) NOT NULL,
  `gruppe_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `ad_personen`
--
ALTER TABLE `ad_personen`
  ADD PRIMARY KEY (`person_id`);

--
-- Indizes für die Tabelle `datenkategorie`
--
ALTER TABLE `datenkategorie`
  ADD PRIMARY KEY (`datenkategorie_id`);

--
-- Indizes für die Tabelle `datenkategorie_personengruppe`
--
ALTER TABLE `datenkategorie_personengruppe`
  ADD PRIMARY KEY (`dp_id`),
  ADD UNIQUE KEY `verfahren_datenkategorie_id` (`verfahren_datenkategorie_id`,`verfahren_personengruppe_id`,`vorgang_id`);

--
-- Indizes für die Tabelle `dokumente`
--
ALTER TABLE `dokumente`
  ADD PRIMARY KEY (`dokument_id`);

--
-- Indizes für die Tabelle `gruppe`
--
ALTER TABLE `gruppe`
  ADD PRIMARY KEY (`gruppe_id`);

--
-- Indizes für die Tabelle `massnahmen`
--
ALTER TABLE `massnahmen`
  ADD PRIMARY KEY (`massnahme_id`);

--
-- Indizes für die Tabelle `organisation`
--
ALTER TABLE `organisation`
  ADD PRIMARY KEY (`organisation_id`);

--
-- Indizes für die Tabelle `personen`
--
ALTER TABLE `personen`
  ADD PRIMARY KEY (`person_id`);

--
-- Indizes für die Tabelle `personengruppen`
--
ALTER TABLE `personengruppen`
  ADD PRIMARY KEY (`personengruppe_id`);

--
-- Indizes für die Tabelle `protokoll`
--
ALTER TABLE `protokoll`
  ADD PRIMARY KEY (`protokoll_id`);

--
-- Indizes für die Tabelle `software`
--
ALTER TABLE `software`
  ADD PRIMARY KEY (`software_id`);

--
-- Indizes für die Tabelle `software_gruppe`
--
ALTER TABLE `software_gruppe`
  ADD PRIMARY KEY (`sg_id`);

--
-- Indizes für die Tabelle `software_massnahmen`
--
ALTER TABLE `software_massnahmen`
  ADD PRIMARY KEY (`sm_id`);

--
-- Indizes für die Tabelle `user`
--
ALTER TABLE `user`
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indizes für die Tabelle `verfahren`
--
ALTER TABLE `verfahren`
  ADD PRIMARY KEY (`verfahren_id`);

--
-- Indizes für die Tabelle `verfahren_datenkategorie`
--
ALTER TABLE `verfahren_datenkategorie`
  ADD PRIMARY KEY (`vd_id`);

--
-- Indizes für die Tabelle `verfahren_massnahmen`
--
ALTER TABLE `verfahren_massnahmen`
  ADD PRIMARY KEY (`vm_id`);

--
-- Indizes für die Tabelle `verfahren_personengruppe`
--
ALTER TABLE `verfahren_personengruppe`
  ADD PRIMARY KEY (`vp_id`),
  ADD UNIQUE KEY `vp_id` (`vp_id`,`verfahren_id`,`personengruppe_id`);

--
-- Indizes für die Tabelle `verfahren_software`
--
ALTER TABLE `verfahren_software`
  ADD PRIMARY KEY (`vs_id`);

--
-- Indizes für die Tabelle `verfahren_weitergabe`
--
ALTER TABLE `verfahren_weitergabe`
  ADD PRIMARY KEY (`vw_id`);

--
-- Indizes für die Tabelle `weitergabe`
--
ALTER TABLE `weitergabe`
  ADD PRIMARY KEY (`weitergabe_id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `ad_personen`
--
ALTER TABLE `ad_personen`
  MODIFY `person_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=442850;
--
-- AUTO_INCREMENT für Tabelle `datenkategorie`
--
ALTER TABLE `datenkategorie`
  MODIFY `datenkategorie_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=644;
--
-- AUTO_INCREMENT für Tabelle `datenkategorie_personengruppe`
--
ALTER TABLE `datenkategorie_personengruppe`
  MODIFY `dp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2410;
--
-- AUTO_INCREMENT für Tabelle `dokumente`
--
ALTER TABLE `dokumente`
  MODIFY `dokument_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=222;
--
-- AUTO_INCREMENT für Tabelle `gruppe`
--
ALTER TABLE `gruppe`
  MODIFY `gruppe_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;
--
-- AUTO_INCREMENT für Tabelle `massnahmen`
--
ALTER TABLE `massnahmen`
  MODIFY `massnahme_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;
--
-- AUTO_INCREMENT für Tabelle `organisation`
--
ALTER TABLE `organisation`
  MODIFY `organisation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;
--
-- AUTO_INCREMENT für Tabelle `personen`
--
ALTER TABLE `personen`
  MODIFY `person_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=322;
--
-- AUTO_INCREMENT für Tabelle `personengruppen`
--
ALTER TABLE `personengruppen`
  MODIFY `personengruppe_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=888;
--
-- AUTO_INCREMENT für Tabelle `protokoll`
--
ALTER TABLE `protokoll`
  MODIFY `protokoll_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9710;
--
-- AUTO_INCREMENT für Tabelle `software`
--
ALTER TABLE `software`
  MODIFY `software_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=385;
--
-- AUTO_INCREMENT für Tabelle `software_gruppe`
--
ALTER TABLE `software_gruppe`
  MODIFY `sg_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1209;
--
-- AUTO_INCREMENT für Tabelle `software_massnahmen`
--
ALTER TABLE `software_massnahmen`
  MODIFY `sm_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;
--
-- AUTO_INCREMENT für Tabelle `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=151;
--
-- AUTO_INCREMENT für Tabelle `verfahren`
--
ALTER TABLE `verfahren`
  MODIFY `verfahren_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=676;
--
-- AUTO_INCREMENT für Tabelle `verfahren_datenkategorie`
--
ALTER TABLE `verfahren_datenkategorie`
  MODIFY `vd_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=936;
--
-- AUTO_INCREMENT für Tabelle `verfahren_massnahmen`
--
ALTER TABLE `verfahren_massnahmen`
  MODIFY `vm_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=615;
--
-- AUTO_INCREMENT für Tabelle `verfahren_personengruppe`
--
ALTER TABLE `verfahren_personengruppe`
  MODIFY `vp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1154;
--
-- AUTO_INCREMENT für Tabelle `verfahren_software`
--
ALTER TABLE `verfahren_software`
  MODIFY `vs_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=980;
--
-- AUTO_INCREMENT für Tabelle `verfahren_weitergabe`
--
ALTER TABLE `verfahren_weitergabe`
  MODIFY `vw_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1441;
--
-- AUTO_INCREMENT für Tabelle `weitergabe`
--
ALTER TABLE `weitergabe`
  MODIFY `weitergabe_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=775;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
