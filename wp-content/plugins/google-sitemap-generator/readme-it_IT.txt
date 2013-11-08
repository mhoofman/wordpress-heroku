=== Google Sitemap Generator per WordPress ===

Tag: wordpress, sitemap, google, plugin
Contributi: Arne, Michael, Rodney, Hirosama, James, John, Brad, Christian

Questo generatore crea una mappa del vostro blog WordPress (sitemap) secondo le specifiche di Google. Attualmente vengono gestiti la homepage, gli articoli, le pagine statiche, le categorie e gli archivi. La prioritò degli articoli dipende dai loro commenti. Più commenti, maggiore priorità! Se si hanno pagine esterne che non appartngono al blog, queste pagine possono venir incluse all'elenco. Questo plugin eseguira una notifica automatica a Google ogni qualvolta la sitemap viene rigenerata.

== Installzione ==

1. Caricare tutti i file nella directory wp-content/plugins
2. Rendere la directory del blog scrivibile OPPURE creare due file denominati sitemap.xml e sitemap.xml.gz e renderli scrivibili tramite CHMOD. Nella maggior pate dei casi la direcotry del vostro blog è già scrivibile.
2. Attivare il plugin tramite il pannello di controllo Plugin
3. Modificare o pubblicare un articolo o fare click su Ricostruisci la Sitemap nel pannello di amministrazione Sitemap nella sezione Opzioni

== FAQ ==

= Non ho commenti (o sono disabilitati) e tutti i miei articoli hanno priorità zero! =
A: Disabilitare il calcolo automatico della priorità e definire una priorità statica per gli articoli!

= Devo far sempre click su "Ricostruisci la Sitemap" se modifico un articolo? =
A: No! Quando si modifica/pubblica/cancella un articolo la sitemap viene rigenerata automaticamente!

= Ci sono così tante opzioni… Devo cambiarle per forza? =
A: No! Solo se lo desiderate. I valori predfiniti dovrebbero già essere ok!

= Funziona con tutte le versioni di WordPress? =
A: Spiacente, l'ho provata solo con la 1.5.1.1. Alcuni utenti hanno segnalato problemi con Wordpress 1.5. COnsiderate la possibilità di un aggiornamento del vostro blog alla Versione corrente di WordPress che contiene inoltre anche "un importante aggiornamento di sicurezza".

= Ottengo un fopen error e/o permesso negato =
A: Se ottenete un errore sui permessi assicuratevi che lo script abbia i diritti di scrittura sulla directory del vostro blog. Provate a creare manualmente i file sitemap.xml e sitemap.xml.gz e a caricarli con un programma ftp impostando i diritti di scrittura tramite CHMOD. Quindi riavviate la generazione della sitemap dalla pagina di amministrazione. Un valida guida per modificare i permessi si trova sul Codex di WordPress.

= Quale versione di MySQL è supportata? =
A: MySQL 4 funziona con tutte le versioni, il supporto a MySQL 3 è stato aggiunto nella versione 2.12

= Devo usare per forza questo plugin? =
No se Google conosce bene il vostro blog e lo visita ogni giorno. Se non accade questo è un ottimo metodo per informare Google riguardo alle vostre pagine ed i loro ultimi cambiamenti. Ciò fa si che Google aggiorni le pagine solo se necessario dandovi un certo risparmio di banda.
