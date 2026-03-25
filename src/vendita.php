<?php
include 'config.php';

$messaggio = "";

$clienti = mysqli_query($conn, "SELECT id_cliente, nome, nickname FROM Clienti ORDER BY nome");
$prodotti = mysqli_query($conn, "SELECT id_prodotto, nome, tipo FROM Prodotti ORDER BY nome");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_cliente = intval($_POST['id_cliente']);
    $id_prodotto = intval($_POST['id_prodotto']);
    $quantita = floatval($_POST['quantita']);

    if ($id_cliente <= 0 || $id_prodotto <= 0 || $quantita <= 0) {
        $messaggio = "<div class='message error'>Controlla i dati inseriti.</div>";
    } else {
        $ok = true;

        $riga_prodotto = mysqli_fetch_assoc(mysqli_query($conn, "SELECT tipo FROM Prodotti WHERE id_prodotto = $id_prodotto"));

        if (!$riga_prodotto) {
            $messaggio = "<div class='message error'>Prodotto non trovato.</div>";
            $ok = false;
        }

        if ($ok) {
            $tipo = $riga_prodotto['tipo'];

            if ($tipo == 'Riserva') {
                $riga_riserva = mysqli_fetch_assoc(mysqli_query($conn, "SELECT peso_totale_disponibile FROM Prodotti_Riserva WHERE id_prodotto = $id_prodotto"));

                if (!$riga_riserva || $riga_riserva['peso_totale_disponibile'] < $quantita) {
                    $messaggio = "<div class='message error'>Riserva insufficiente.</div>";
                    $ok = false;
                } else {
                    mysqli_query($conn, "UPDATE Prodotti_Riserva SET peso_totale_disponibile = peso_totale_disponibile - $quantita WHERE id_prodotto = $id_prodotto");
                }
            }

            if ($tipo == 'Confezionato') {
                $pezzi = intval($quantita);
                $riga_conf = mysqli_fetch_assoc(mysqli_query($conn, "SELECT giacenza_pezzi FROM Prodotti_Confezionati WHERE id_prodotto = $id_prodotto"));

                if (!$riga_conf || $riga_conf['giacenza_pezzi'] < $pezzi) {
                    $messaggio = "<div class='message error'>Prodotto esaurito.</div>";
                    $ok = false;
                } else {
                    mysqli_query($conn, "UPDATE Prodotti_Confezionati SET giacenza_pezzi = giacenza_pezzi - $pezzi WHERE id_prodotto = $id_prodotto");
                    $quantita = $pezzi;
                }
            }
        }

        if ($ok) {
            $riga_prezzo = mysqli_fetch_assoc(mysqli_query($conn, "SELECT prezzo_unitario FROM Listino_Prezzi WHERE id_prodotto = $id_prodotto ORDER BY data_inizio_validita DESC LIMIT 1"));

            if (!$riga_prezzo) {
                $messaggio = "<div class='message error'>Prezzo non trovato.</div>";
            } else {
                $prezzo = floatval($riga_prezzo['prezzo_unitario']);
                $totale = $prezzo * $quantita;

                $sql_vendita = "INSERT INTO Vendite (id_cliente, data_acquisto, totale_calcolato, totale_pagato) VALUES ($id_cliente, NOW(), $totale, $totale)";

                if (mysqli_query($conn, $sql_vendita)) {
                    $id_vendita = mysqli_insert_id($conn);
                    mysqli_query($conn, "INSERT INTO Dettaglio_Vendite (id_vendita, id_prodotto, quantita, prezzo_unitario) VALUES ($id_vendita, $id_prodotto, $quantita, $prezzo)");
                    $messaggio = "<div class='message success'>Vendita completata.</div>";
                } else {
                    $messaggio = "<div class='message error'>Errore durante il salvataggio della vendita.</div>";
                }
            }
        }
    }

    $clienti = mysqli_query($conn, "SELECT id_cliente, nome, nickname FROM Clienti ORDER BY nome");
    $prodotti = mysqli_query($conn, "SELECT id_prodotto, nome, tipo FROM Prodotti ORDER BY nome");
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registra vendita</title>
    <link rel="stylesheet" href="app.css">
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1>Registra vendita</h1>
            <p>Scegli cliente, prodotto e quantità.</p>
        </div>

        <?php echo $messaggio; ?>

        <div class="panel footer-space">
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Cliente</label>
                        <select name="id_cliente" required>
                            <option value="">Seleziona cliente</option>
                            <?php while ($c = mysqli_fetch_assoc($clienti)) { ?>
                                <option value="<?php echo $c['id_cliente']; ?>"><?php echo $c['nome']; ?><?php if ($c['nickname'] != '') { echo ' - ' . $c['nickname']; } ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Prodotto</label>
                        <select name="id_prodotto" required>
                            <option value="">Seleziona prodotto</option>
                            <?php while ($p = mysqli_fetch_assoc($prodotti)) { ?>
                                <option value="<?php echo $p['id_prodotto']; ?>"><?php echo $p['nome']; ?> (<?php echo $p['tipo']; ?>)</option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Quantità</label>
                    <input type="number" step="0.01" min="0.01" name="quantita" required>
                </div>

                <div class="actions">
                    <button type="submit">Salva vendita</button>
                    <a class="btn btn-light" href="index.php">Torna alla dashboard</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
