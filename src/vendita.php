<?php
include 'config.php';

$messaggio = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_prodotto = isset($_POST['id_prodotto']) ? (int)$_POST['id_prodotto'] : 0;
    $tipo = isset($_POST['tipo_prodotto']) ? trim($_POST['tipo_prodotto']) : '';
    $quantita = isset($_POST['quantita']) ? (float)$_POST['quantita'] : 0;

    if ($id_prodotto <= 0 || $quantita <= 0 || !in_array($tipo, ['fresco', 'riserva', 'confezionato'])) {
        $messaggio = "<p style='color:red;'>Dati non validi.</p>";
    } else {

        mysqli_begin_transaction($conn);

        try {

            if ($tipo === 'riserva') {
                $sql = "SELECT peso_totale_disponibile
                        FROM Prodotti_Riserva
                        WHERE id_prodotto = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "i", $id_prodotto);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $res = mysqli_fetch_assoc($result);

                if (!$res) {
                    throw new Exception("Prodotto riserva non trovato.");
                }

                if ($res['peso_totale_disponibile'] < $quantita) {
                    throw new Exception("Riserva insufficiente.");
                }

                $sql = "UPDATE Prodotti_Riserva
                        SET peso_totale_disponibile = peso_totale_disponibile - ?
                        WHERE id_prodotto = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "di", $quantita, $id_prodotto);
                mysqli_stmt_execute($stmt);
            }

            elseif ($tipo === 'confezionato') {
                $sql = "SELECT giacenza_pezzi
                        FROM Prodotti_Confezionati
                        WHERE id_prodotto = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "i", $id_prodotto);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $res = mysqli_fetch_assoc($result);

                if (!$res) {
                    throw new Exception("Prodotto confezionato non trovato.");
                }

                if ($res['giacenza_pezzi'] < $quantita) {
                    throw new Exception("Prodotto esaurito.");
                }

                $sql = "UPDATE Prodotti_Confezionati
                        SET giacenza_pezzi = giacenza_pezzi - ?
                        WHERE id_prodotto = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ii", $quantita, $id_prodotto);
                mysqli_stmt_execute($stmt);
            }

            // Per i freschi non controlli la giacenza

            // Registra vendita: usa data_acquisto, non data_vendita
            $sql = "INSERT INTO Vendite (data_acquisto, totale_calcolato, totale_pagato, note)
                    VALUES (NOW(), NULL, NULL, NULL)";
            mysqli_query($conn, $sql);

            $id_vendita = mysqli_insert_id($conn);

            // Recupera prezzo attuale dal listino
            $sql = "SELECT prezzo_unitario
                    FROM Listino_Prezzi
                    WHERE id_prodotto = ?
                    ORDER BY data_inizio_validita DESC
                    LIMIT 1";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $id_prodotto);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $prezzoRow = mysqli_fetch_assoc($result);

            if (!$prezzoRow) {
                throw new Exception("Prezzo non trovato nel listino.");
            }

            $prezzo_unitario = (float)$prezzoRow['prezzo_unitario'];
            $totale = $prezzo_unitario * $quantita;

            // Aggiorna la vendita con i totali
            $sql = "UPDATE Vendite
                    SET totale_calcolato = ?, totale_pagato = ?
                    WHERE id_vendita = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ddi", $totale, $totale, $id_vendita);
            mysqli_stmt_execute($stmt);

            // Registra dettaglio vendita
            $sql = "INSERT INTO Dettaglio_Vendite (id_vendita, id_prodotto, quantita, prezzo_unitario)
                    VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "iidd", $id_vendita, $id_prodotto, $quantita, $prezzo_unitario);
            mysqli_stmt_execute($stmt);

            mysqli_commit($conn);
            $messaggio = "<p style='color:green;'>Vendita completata!</p>";

        } catch (Exception $e) {
            mysqli_rollback($conn);
            $messaggio = "<p style='color:red;'>Errore: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registra Vendita</title>
</head>
<body>
    <h1>Registra vendita</h1>
    <p><a href="index.php">Torna alla dashboard</a></p>

    <?php echo $messaggio; ?>

    <form method="POST" action="vendita.php">
        <input type="number" name="id_prodotto" placeholder="ID prodotto" required><br><br>

        <select name="tipo_prodotto" required>
            <option value="">-- Seleziona tipo --</option>
            <option value="fresco">Fresco</option>
            <option value="riserva">Riserva</option>
            <option value="confezionato">Confezionato</option>
        </select><br><br>

        <input type="number" step="0.01" min="0.01" name="quantita" placeholder="Quantità" required><br><br>

        <button type="submit">Vendi</button>
    </form>
</body>
</html>