<?php

/*
----------------------------------------------------------------------
FusionInventory
Copyright (C) 2010-2011 by the FusionInventory Development Team.

http://www.fusioninventory.org/ http://forge.fusioninventory.org/
----------------------------------------------------------------------

LICENSE

This file is part of FusionInventory.

FusionInventory is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

FusionInventory is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with FusionInventory. If not, see <http://www.gnu.org/licenses/>.

------------------------------------------------------------------------
Original Author of file: David DURIEUX
Co-authors of file:
Purpose of file:
Traduct by : Mattia VICARI
----------------------------------------------------------------------
*/

$title="FusionInventory";
$version="2.3.4";

$LANG['plugin_fusioninventory']['title'][0] ="$title";
$LANG['plugin_fusioninventory']['title'][1] ="FusInv";
$LANG['plugin_fusioninventory']['title'][5] ="luchetti";

$LANG['plugin_fusioninventory']['config'][0] = "Frequenza dell'inventario (in ore)";

$LANG['plugin_fusioninventory']['profile'][0] = "Gestione dei diritti";
$LANG['plugin_fusioninventory']['profile'][2] = "Agenti";
$LANG['plugin_fusioninventory']['profile'][3] = "Comando � distanza dei agenti";
$LANG['plugin_fusioninventory']['profile'][4] = "Impostazioni";
$LANG['plugin_fusioninventory']['profile'][5] = "WakeOnLan";
$LANG['plugin_fusioninventory']['profile'][6] = "Materiale sconosciuto";
$LANG['plugin_fusioninventory']['profile'][7] = "Tasks";

$LANG['plugin_fusioninventory']['setup'][16] = "Documentazione";
$LANG['plugin_fusioninventory']['setup'][17] = "Gli altri plugin FusionInventory (fusinv. ..) devono essere disinstallati prima di disinstallare il plugin FusionInventory.";

$LANG['plugin_fusioninventory']['functionalities'][0] = "Caratteristiche";
$LANG['plugin_fusioninventory']['functionalities'][2] = "Impostazioni generali";
$LANG['plugin_fusioninventory']['functionalities'][6] = "Legenda";
$LANG['plugin_fusioninventory']['functionalities'][8] = "Numero di porta dell'agente";
$LANG['plugin_fusioninventory']['functionalities'][9] = "Conservazione in giorni";
$LANG['plugin_fusioninventory']['functionalities'][16] = "Memorizazione dell'login SNMP";
$LANG['plugin_fusioninventory']['functionalities'][17] = "Database";
$LANG['plugin_fusioninventory']['functionalities'][18] = "Files";
$LANG['plugin_fusioninventory']['functionalities'][19] = "Si prega di configurare la memorizazione dell'login SNMP nella configurazione del plugin";
$LANG['plugin_fusioninventory']['functionalities'][27] = "SSL unicamente per l'agente";
$LANG['plugin_fusioninventory']['functionalities'][29] = "Lista dei champi da storicizzare";
$LANG['plugin_fusioninventory']['functionalities'][32] = "Cancellare i compiti completati dopo (mn)";
$LANG['plugin_fusioninventory']['functionalities'][60] = "Pulizie della cronologia";
$LANG['plugin_fusioninventory']['functionalities'][73] = "Campo";
$LANG['plugin_fusioninventory']['functionalities'][74] = "Valore";
$LANG['plugin_fusioninventory']['functionalities'][75] = "luchetti";
$LANG['plugin_fusioninventory']['functionalities'][76] = "Extra-debug";

$LANG['plugin_fusioninventory']['errors'][22] = "Voce inaspettata in";
$LANG['plugin_fusioninventory']['errors'][50] = "La versione di GLPI non � compatibile, � necessaria la versione 0.78";
$LANG['plugin_fusioninventory']['errors'][1] = "PHP allow_url_fopen � disattivata, Sveglia del agente impossibile per fare l'inventario";
$LANG['plugin_fusioninventory']['errors'][2] = "PHP allow_url_fopen � disattivata, il modulo push non pu� funzionare";

$LANG['plugin_fusioninventory']['rules'][2] = "Regole d'importo e di associazione dei materiali";
$LANG['plugin_fusioninventory']['rules'][3] = "Cerca hardware GLPI con lo stato";
$LANG['plugin_fusioninventory']['rules'][4] = "Ente di destinazione dell'computer";
$LANG['plugin_fusioninventory']['rules'][5] = "Link FusionInventory";
$LANG['plugin_fusioninventory']['rules'][6] = "Link se possible, se no importo rifutato";
$LANG['plugin_fusioninventory']['rules'][7] = "Link se possible";
$LANG['plugin_fusioninventory']['rules'][8] = "Inviato";
$LANG['plugin_fusioninventory']['rules'][9] = "esiste";
$LANG['plugin_fusioninventory']['rules'][10] = "non esiste";
$LANG['plugin_fusioninventory']['rules'][11] = "� gia presente in GLPI";
$LANG['plugin_fusioninventory']['rules'][12] = "� vuoto";
$LANG['plugin_fusioninventory']['rules'][13] = "Numero di serie dell'disco";
$LANG['plugin_fusioninventory']['rules'][14] = "Numero di serie della partitione disco";
$LANG['plugin_fusioninventory']['rules'][15] = "uuid";
$LANG['plugin_fusioninventory']['rules'][16] = "Etiquette FusionInventory";

$LANG['plugin_fusioninventory']['rulesengine'][152] = "Materiale d� importare";

$LANG['plugin_fusioninventory']['choice'][0] = "No";
$LANG['plugin_fusioninventory']['choice'][1] = "Si";
$LANG['plugin_fusioninventory']['choice'][2] = "Dove";
$LANG['plugin_fusioninventory']['choice'][3] = "e";

$LANG['plugin_fusioninventory']['processes'][1]="PID";
$LANG['plugin_fusioninventory']['processes'][38]="Numero di processo";

$LANG['plugin_fusioninventory']['menu'][1]="Gestione dei agenti";
$LANG['plugin_fusioninventory']['menu'][3]="Menu";
$LANG['plugin_fusioninventory']['menu'][4]="Materiale sconosciuto";
$LANG['plugin_fusioninventory']['menu'][7]="Azioni in corso di esecuzione";

$LANG['plugin_fusioninventory']['discovery'][5]="Numero di materiali importati";
$LANG['plugin_fusioninventory']['discovery'][9]="Numero di materiali non importati per causa di typo sconoscuto";

$LANG['plugin_fusioninventory']['agents'][4]="Ultimo Contatto dell'agente";
$LANG['plugin_fusioninventory']['agents'][6]="Off";
$LANG['plugin_fusioninventory']['agents'][15]="Stato dell'agente";
$LANG['plugin_fusioninventory']['agents'][17]="L'agente � in esecuzione";
$LANG['plugin_fusioninventory']['agents'][22]="in attesa";
$LANG['plugin_fusioninventory']['agents'][23]="Legato al computer";
$LANG['plugin_fusioninventory']['agents'][24]="gettone";
$LANG['plugin_fusioninventory']['agents'][25]="Versione";
$LANG['plugin_fusioninventory']['agents'][27]="Modulo dei agenti";
$LANG['plugin_fusioninventory']['agents'][28]="Agente";
$LANG['plugin_fusioninventory']['agents'][30]="Impossibile contattare l'agente!";
$LANG['plugin_fusioninventory']['agents'][31]="Forza inventario";
$LANG['plugin_fusioninventory']['agents'][32]="Auto gestione dinamica dei agenti";
$LANG['plugin_fusioninventory']['agents'][33]="Auto gestione dinamica dei agenti (stessa sottorete)";
$LANG['plugin_fusioninventory']['agents'][34]="Attiva (impostazione predefinita)";
$LANG['plugin_fusioninventory']['agents'][35]="Login";
$LANG['plugin_fusioninventory']['agents'][36]="Modulo dell'agente";
$LANG['plugin_fusioninventory']['agents'][37]="bloccato";
$LANG['plugin_fusioninventory']['agents'][38]="Disponibile";
$LANG['plugin_fusioninventory']['agents'][39]="In corso di eseguzione";
$LANG['plugin_fusioninventory']['agents'][40]="Computer senza IP conosciuto";

$LANG['plugin_fusioninventory']['unknown'][2]="materiale approvato";
$LANG['plugin_fusioninventory']['unknown'][4]="Hub";
$LANG['plugin_fusioninventory']['unknown'][5]="Materiale sconosciuto da importare nell'inventario";

$LANG['plugin_fusioninventory']['task'][0]="Compito";
$LANG['plugin_fusioninventory']['task'][1]="Gestion des t�ches";
$LANG['plugin_fusioninventory']['task'][2]="Azione";
$LANG['plugin_fusioninventory']['task'][14]="Data d'esecuzione";
$LANG['plugin_fusioninventory']['task'][16]="Nuova azione";
$LANG['plugin_fusioninventory']['task'][17]="periodicit�";
$LANG['plugin_fusioninventory']['task'][18]="Compiti";
$LANG['plugin_fusioninventory']['task'][19]="Compiti in corso";
$LANG['plugin_fusioninventory']['task'][20]="Compiti finiti";
$LANG['plugin_fusioninventory']['task'][21]="Azione su questo materiale";
$LANG['plugin_fusioninventory']['task'][22]="Compiti planificati unicamente";
$LANG['plugin_fusioninventory']['task'][24]="Numero di tentativi";
$LANG['plugin_fusioninventory']['task'][25]="Tempo tra due tentativi (in minuti)";
$LANG['plugin_fusioninventory']['task'][26]="Modulo";
$LANG['plugin_fusioninventory']['task'][27]="Definizione";
$LANG['plugin_fusioninventory']['task'][28]="Azione";
$LANG['plugin_fusioninventory']['task'][29]="Typo";
$LANG['plugin_fusioninventory']['task'][30]="Selezione";
$LANG['plugin_fusioninventory']['task'][31]="Tempo tra l'inizio del compito e l'inizio di questa azione";
$LANG['plugin_fusioninventory']['task'][32]="Forcer lestinzione";
$LANG['plugin_fusioninventory']['task'][33]="Comunicazione";
$LANG['plugin_fusioninventory']['task'][34]="Permanente";
$LANG['plugin_fusioninventory']['task'][35]="minuti";
$LANG['plugin_fusioninventory']['task'][36]="ore";
$LANG['plugin_fusioninventory']['task'][37]="giorno";
$LANG['plugin_fusioninventory']['task'][38]="mese";
$LANG['plugin_fusioninventory']['task'][39]="Impossibile avviare il compito, perch� sono rimaste azioni in corso!";
$LANG['plugin_fusioninventory']['task'][40]="Forzare l'esecuzione";
$LANG['plugin_fusioninventory']['task'][41]="Il server inizia un contatto con l'agente (push)";
$LANG['plugin_fusioninventory']['task'][42]="L'agente inizia un contatto con il server (pull)";

$LANG['plugin_fusioninventory']['taskjoblog'][1]="In esecuzione";
$LANG['plugin_fusioninventory']['taskjoblog'][2]="Ok";
$LANG['plugin_fusioninventory']['taskjoblog'][3]="Errore / riprogrammato";
$LANG['plugin_fusioninventory']['taskjoblog'][4]="Errore";
$LANG['plugin_fusioninventory']['taskjoblog'][5]="Sconosciuto";
$LANG['plugin_fusioninventory']['taskjoblog'][6]="In corso";
$LANG['plugin_fusioninventory']['taskjoblog'][7]="Preparato";

$LANG['plugin_fusioninventory']['update'][0]="I log fanno piu di 300 000 line, bisogna effetuare in commando segente via lo shell : ";

$LANG['plugin_fusioninventory']['xml'][0]="XML";

$LANG['plugin_fusioninventory']['codetasklog'][1]="Gettone non corretto, non pu� agire sull'agente";
$LANG['plugin_fusioninventory']['codetasklog'][2]="Agente interrotto o schiantato";
$LANG['plugin_fusioninventory']['codetasklog'][3]=$LANG['ocsconfig'][11];

$LANG['plugin_fusioninventory']['locks'][0]="Rimuovere i luchetti";
$LANG['plugin_fusioninventory']['locks'][1]="Aggungere i luchetti";

?>