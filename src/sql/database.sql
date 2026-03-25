-- 1. Creazione Tabelle di Base (Anagrafiche)
CREATE TABLE IF NOT EXISTS Categorie (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS Sedi (
    id_sede INT AUTO_INCREMENT PRIMARY KEY,
    nome_sede VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS Clienti (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    nickname VARCHAR(50),
    contatto VARCHAR(100)
);

-- 2. Tabella Principale Prodotti
CREATE TABLE IF NOT EXISTS Prodotti (
    id_prodotto INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    id_categoria INT,
    tipo ENUM('Fresco', 'Riserva', 'Confezionato') NOT NULL,
    FOREIGN KEY (id_categoria) REFERENCES Categorie(id_categoria)
);

-- 3. Sottotabelle per le tipologie (Specializzazione)
CREATE TABLE IF NOT EXISTS Prodotti_Freschi (
    id_prodotto INT PRIMARY KEY,
    unita_misura VARCHAR(20),
    FOREIGN KEY (id_prodotto) REFERENCES Prodotti(id_prodotto) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS Prodotti_Riserva (
    id_prodotto INT PRIMARY KEY,
    peso_totale_disponibile DECIMAL(10,2) DEFAULT 0,
    unita_misura VARCHAR(20),
    data_produzione DATE,
    FOREIGN KEY (id_prodotto) REFERENCES Prodotti(id_prodotto) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS Prodotti_Confezionati (
    id_prodotto INT PRIMARY KEY,
    giacenza_pezzi INT DEFAULT 0,
    peso_netto_confezione DECIMAL(10,2),
    data_confezionamento DATE,
    FOREIGN KEY (id_prodotto) REFERENCES Prodotti(id_prodotto) ON DELETE CASCADE
);

-- 4. Listino Prezzi (Storico)
CREATE TABLE IF NOT EXISTS Listino_Prezzi (
    id_listino INT AUTO_INCREMENT PRIMARY KEY,
    id_prodotto INT,
    prezzo_unitario DECIMAL(10,2) NOT NULL,
    data_inizio_validita TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_prodotto) REFERENCES Prodotti(id_prodotto) ON DELETE CASCADE
);

-- 5. Tabelle Vendite
CREATE TABLE IF NOT EXISTS Vendite (
    id_vendita INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT,
    data_acquisto TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    totale_calcolato DECIMAL(10,2),
    totale_pagato DECIMAL(10,2),
    note TEXT,
    FOREIGN KEY (id_cliente) REFERENCES Clienti(id_cliente)
);

CREATE TABLE IF NOT EXISTS Dettaglio_Vendite (
    id_dettaglio INT AUTO_INCREMENT PRIMARY KEY,
    id_vendita INT,
    id_prodotto INT,
    quantita DECIMAL(10,2),
    prezzo_unitario DECIMAL(10,2),
    FOREIGN KEY (id_vendita) REFERENCES Vendite(id_vendita),
    FOREIGN KEY (id_prodotto) REFERENCES Prodotti(id_prodotto)
);

-- 6. Dati di Esempio (Per testare subito il sistema)
INSERT INTO Categorie (nome) VALUES ('Frutta Fresca'), ('Olio'), ('Miele'), ('Marmellate');
INSERT INTO Sedi (nome_sede) VALUES ('Dispensa Centrale'), ('Sede Produzione'), ('Negozio');
INSERT INTO Clienti (nome, nickname) VALUES ('Cliente Occasionale', 'ClienteX'), ('Mario Rossi', 'Amico');