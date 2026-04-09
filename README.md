Sistema di Gestione - Azienda Agricola

Questo progetto permette di automatizzare e gestire a 360 gradi i processi di un'azienda agricola, dalla produzione alla conservazione, fino alla vendita e alla reportistica avanzata.

PREREQUISITI
- Avere installato Docker Desktop.
- Avere un account di GitHub (per scaricare il repository).

COME AVVIARE IL PROGETTO
1. Scarica il progetto come file ZIP dal link del repository GitHub fornito ed estrailo manualmente sul tuo computer.
2. Apri il terminale (o CMD) e naviga all'interno della cartella principale del progetto (dove è presente il file docker-compose.yaml).
3. Esegui i seguenti comandi per costruire e avviare i container: docker compose build e poi docker compose up -d
4. Controlla che tutto sia avviato correttamente controllando l'interfaccia di Docker Desktop, oppure esegui da terminale il comando: docker ps

AL PRIMO AVVIO
- Il database viene creato automaticamente.
- Viene eseguito lo script di inizializzazione.
- Vengono generate tutte le tabelle necessarie per gestire prodotti, categorie, magazzino, sedi e vendite (inclusi eventuali Trigger per lo storico dei prezzi).

COME TESTARE IL SISTEMA
A differenza di altri sistemi, non ci sono ruoli, password o schermate di login. Il sistema è immediatamente accessibile per testare le dinamiche aziendali. Ecco le funzionalità principali da provare:

Gestione Prodotti e Categorie:
- Prova ad aggiungere a run-time nuove categorie o nuovi prodotti, verificando che il sistema blocchi eventuali duplicati.
- Prodotti Freschi: Registra la vendita di un prodotto fresco (es. frutta) calcolando il prezzo a peso o a pezzo, senza che il sistema richieda la giacenza in dispensa.
- Prodotti Lavorati (Subito confezionati): Inserisci un prodotto come la marmellata, dove la data di lavorazione coincide con quella di confezionamento.
- Prodotti "Riserva": Inserisci una grande quantità di prodotto sfuso (es. olio o frutta secca). Prova poi a venderlo sfuso (calcolo prezzo in base a kg/litri) o confezionarlo in un secondo momento (il sistema salverà la data di produzione originale e la nuova data di confezionamento).

Gestione Magazzino e Giacenze:
- Verifica che le giacenze si aggiornino in automatico ad ogni nuova produzione o confezionamento.
- Effettua una vendita e controlla che la quantità venga sottratta.
- Prova a vendere un prodotto esaurito per visualizzare l'avviso di "prodotto non disponibile" e il blocco.

Gestione Sedi e Spostamenti:
- Di default i prodotti finiscono in "dispensa". Prova a spostare dei prodotti in una sede diversa.

Vendite e Clienti:
- Registra una vendita per un cliente specifico oppure per un cliente occasionale ("clienteX").
- Crea uno scontrino misto inserendo prodotti freschi, confezionati e riserva sfusi.
- Applica uno sconto al totale o inserisci dei prodotti in omaggio. Controlla che il totale pagato differisca da quello calcolato e che i prodotti regalati vengano scalati dal magazzino.

Modifica Prezzi e Reportistica:
- Modifica il prezzo di un prodotto: il sistema (tramite Trigger) terrà traccia della data di variazione per calcoli futuri.
- Usa la funzione di interrogazione per generare tabelle archivio. Prova ad applicare filtri incrociati.

DOCUMENTAZIONE
All'interno della cartella docs è presente un file PDF contenente l'analisi dettagliata del programma e delle sue logiche di database (entità, relazioni e trigger). Per visualizzarlo all'interno dell'editor, potrebbe essere necessaria l'installazione di un'estensione per i PDF.