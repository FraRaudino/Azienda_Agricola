<?php
include 'config.php';

$messaggio = "";

// Gestione del Salvataggio Vendita
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_cliente = intval($_POST['id_cliente']);
    $id_prodotto = intval($_POST['id_prodotto']);
    $quantita = floatval($_POST['quantita']);
    $prezzo_fissato = floatval($_POST['prezzo_unitario']);
    $pagato = floatval($_POST['totale_pagato']);
    $note = mysqli_real_escape_string($conn, $_POST['note']);

    $totale_calcolato = $quantita * $prezzo_fissato;

    // 1. Registriamo la vendita nella tabella Vendite
    $sql_v = "INSERT INTO Vendite (id_cliente, totale_calcolato, totale_pagato, note) 
              VALUES ($id_cliente, $totale_calcolato, $pagato, '$note')";
    
    if (mysqli_query($conn, $sql_v)) {
        $id_v = mysqli_insert_id($conn);

        // 2. Registriamo il dettaglio del prodotto venduto
        mysqli_query($conn, "INSERT INTO Dettaglio_Vendite (id_vendita, id_prodotto, quantita, prezzo_unitario) 
                             VALUES ($id_v, $id_prodotto, $quantita, $prezzo_fissato)");

        // 3. SCARICO MAGAZZINO (Logica semplificata)
        // Proviamo a scalarlo sia dai Confezionati che dalle Riserve (SQL ignorerà se l'ID non esiste nella tabella)
        mysqli_query($conn, "UPDATE Prodotti_Confezionati SET giacenza_pezzi = giacenza_pezzi - $quantita WHERE id_prodotto = $id_prodotto");
        mysqli_query($conn, "UPDATE Prodotti_Riserva SET peso_totale_disponibile = peso_totale_disponibile - $quantita WHERE id_prodotto = $id_prodotto");

        $messaggio = "<div style='color:green; font-weight:bold;'>Vendita registrata con successo!</div>";
    } else {
        $messaggio = "<div style='color:red;'>Errore nel salvataggio: " . mysqli_error($conn) . "</div>";
    }
}

// Caricamento dati per il form
$res_clienti = mysqli_query($conn, "SELECT * FROM Clienti ORDER BY nome ASC");
$res_prodotti = mysqli_query($conn, "SELECT p.id_prodotto, p.nome, MAX(l.prezzo_unitario) as prezzo_unitario 
                                     FROM Prodotti p 
                                     LEFT JOIN Listino_Prezzi l ON p.id_prodotto = l.id_prodotto 
                                     GROUP BY p.id_prodotto, p.nome");
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Registra Vendita</title>
</head>
<body>
    <h1>Nuova Vendita</h1>
    <a href="index.php">Torna alla Dashboard</a>
    <hr>
    
    <?php echo $messaggio; ?>

    <form method="POST">
        <p>Cliente: 
            <select name="id_cliente" required>
                <?php while($c = mysqli_fetch_assoc($res_clienti)) { ?>
                    <option value="<?php echo $c['id_cliente']; ?>"><?php echo $c['nome']; ?> (<?php echo $c['nickname']; ?>)</option>
                <?php } ?>
            </select>
        </p>

        <p>Prodotto: 
            <select name="id_prodotto" required>
                <?php while($p = mysqli_fetch_assoc($res_prodotti)) { ?>
                    <option value="<?php echo $p['id_prodotto']; ?>">
                        <?php echo $p['nome']; ?> - Prezzo: €<?php echo $p['prezzo_unitario']; ?>
                    </option>
                <?php } ?>
            </select>
        </p>

        <p>Prezzo Unitario confermato (€): 
            <input type="number" step="0.01" name="prezzo_unitario" placeholder="Es: 10.50" required>
        </p>

        <p>Quantità (Pezzi o Kg): 
            <input type="number" step="0.1" name="quantita" required>
        </p>

        <p>Totale Pagato (€): 
            <input type="number" step="0.01" name="totale_pagato" placeholder="Se diverso dal calcolato" required>
        </p>

        <p>Note (Sconti, Omaggi, ecc.): <br>
            <textarea name="note" rows="3"></textarea>
        </p>

        <button type="submit">Concludi Vendita</button>
    </form>
</body>
</html>