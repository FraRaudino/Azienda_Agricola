-- Creazione Tabelle Base
CREATE TABLE IF NOT EXISTS Sedi (
    id_sede INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS Categorie (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE
);

-- Inserimento dati iniziali (Anagrafiche)
INSERT IGNORE INTO Sedi (nome) VALUES ('Dispensa Centrale'), ('Punto Vendita Aziendale');
INSERT IGNORE INTO Categorie (nome) VALUES ('Frutta Fresca'), ('Olio'), ('Miele'), ('Marmellate');

-- Tabella Prodotti (Padre)
CREATE TABLE IF NOT EXISTS Prodotti (
    id_prodotto INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    id_categoria INT,
    tipo ENUM('Fresco', 'Riserva', 'Confezionato') NOT NULL,
    FOREIGN KEY (id_categoria) REFERENCES Categorie(id_categoria)
);

-- Sottotabelle (Specializzazione)
CREATE TABLE IF NOT EXISTS Prodotti_Freschi (
    id_prodotto INT PRIMARY KEY,
    unita_misura ENUM('kg', 'pezzo') NOT NULL,
    FOREIGN KEY (id_prodotto) REFERENCES Prodotti(id_prodotto) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS Prodotti_Riserva (
    id_prodotto INT PRIMARY KEY,
    peso_totale_disponibile DECIMAL(10,2) DEFAULT 0.00,
    unita_misura ENUM('kg', 'litro', 'grammo') NOT NULL,
    data_produzione DATE NOT NULL,
    FOREIGN KEY (id_prodotto) REFERENCES Prodotti(id_prodotto) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS Prodotti_Confezionati (
    id_prodotto INT PRIMARY KEY,
    giacenza_pezzi INT DEFAULT 0,
    peso_netto_confezione DECIMAL(10,2),
    data_confezionamento DATE NOT NULL,
    FOREIGN KEY (id_prodotto) REFERENCES Prodotti(id_prodotto) ON DELETE CASCADE
);

-- Tabella Prezzi (Storicizzazione)
CREATE TABLE IF NOT EXISTS Listino_Prezzi (
    id_prezzo INT AUTO_INCREMENT PRIMARY KEY,
    id_prodotto INT,
    prezzo_unitario DECIMAL(10,2) NOT NULL,
    data_inizio_validita TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_prodotto) REFERENCES Prodotti(id_prodotto)
);


-- Tabella Clienti (con gestione Cliente Occasionale)
CREATE TABLE IF NOT EXISTS Clienti (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    nominativo VARCHAR(100) NOT NULL, -- es. 'ClienteX' o 'Mario Rossi'
    nickname VARCHAR(50),             -- es. 'Amico', 'Collega'
    contatti TEXT
);

-- Inserimento Cliente Generico (come da traccia)
INSERT IGNORE INTO Clienti (nominativo) VALUES ('ClienteX');

-- Tabella Acquisti (Testata)
CREATE TABLE IF NOT EXISTS Acquisti (
    id_acquisto INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT,
    data_acquisto TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    totale_calcolato DECIMAL(10,2),
    totale_pagato DECIMAL(10,2), -- Diverso dal calcolato in caso di sconti/omaggi
    note TEXT,
    FOREIGN KEY (id_cliente) REFERENCES Clienti(id_cliente)
);

-- Dettaglio Acquisto (Righe dell'acquisto)
CREATE TABLE IF NOT EXISTS Dettaglio_Acquisti (
    id_dettaglio INT AUTO_INCREMENT PRIMARY KEY,
    id_acquisto INT,
    id_prodotto INT,
    quantita DECIMAL(10,2), -- Peso o Pezzi
    prezzo_applicato DECIMAL(10,2),
    FOREIGN KEY (id_acquisto) REFERENCES Acquisti(id_acquisto),
    FOREIGN KEY (id_prodotto) REFERENCES Prodotti(id_prodotto)
);