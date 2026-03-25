<?php
include 'config.php';

$messaggio = "";

$sql_clienti = "SELECT id_cliente, nome, nickname FROM Clienti ORDER BY nome";
$ris_clienti = mysqli_query($conn, $sql_clienti);

$sql_prodotti = "SELECT id_prodotto, nome, tipo FROM Prodotti ORDER BY nome";
$ris_prodotti = mysqli_query($conn, $sql_prodotti);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_cliente = (int)$_POST['id_cliente'];
    $id_prodotto = (int)$_POST['id_prodotto'];
    $quantita = (float)$_POST['quantita'];

    if ($id_cliente <= 0 || $id_prodotto <= 0 || $quantita <= 0) {
        $messaggio = "<p style='color:red;'>Dati non validi.</p>";
    } else {

        mysqli_begin_transaction($conn);

        try {

            $sql = "SELECT tipo FROM Prodotti WHERE id_prodotto = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $id_prodotto);
            mysqli_stmt_execute($stmt);
            $res = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

            if (!$res) {
                throw new Exception("Prodotto non trovato.");
            }

            $tipo = $res['tipo'];

            if ($tipo === 'Riserva') {

                $sql = "SELECT peso_totale_disponibile FROM Prodotti_Riserva WHERE id_prodotto = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "i", $id_prodotto);
                mysqli_stmt_execute($stmt);
                $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

                if (!$row || $row['peso_totale_disponibile'] < $quantita) {
                    throw new Exception("Riserva insufficiente.");
                }

                $sql = "UPDATE Prodotti_Riserva 
                        SET peso_totale_disponibile = peso_totale_disponibile - ?
                        WHERE id_prodotto = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "di", $quantita, $id_prodotto);
                mysqli_stmt_execute($stmt);
            }

            elseif ($tipo === 'Confezionato') {

                $sql = "SELECT giacenza_pezzi FROM Prodotti_Confezionati WHERE id_prodotto = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "i", $id_prodotto);
                mysqli_stmt_execute($stmt);
                $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

                if (!$row || $row['giacenza_pezzi'] < $quantita) {
                    throw new Exception("Prodotto esaurito.");
                }

                $sql = "UPDATE Prodotti_Confezionati 
                        SET giacenza_pezzi = giacenza_pezzi - ?
                        WHERE id_prodotto = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ii", $quantita, $id_prodotto);
                mysqli_stmt_execute($stmt);
            }


            $sql = "SELECT prezzo_unitario 
                    FROM Listino_Prezzi 
                    WHERE id_prodotto = ? 
                    ORDER BY data_inizio_validita DESC 
                    LIMIT 1";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $id_prodotto);
            mysqli_stmt_execute($stmt);
            $prezzoRow = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

            if (!$prezzoRow) {
                throw new Exception("Prezzo non trovato.");
            }

            $prezzo = (float)$prezzoRow['prezzo_unitario'];
            $totale = $prezzo * $quantita;

            $sql = "INSERT INTO Vendite (id_cliente, data_acquisto, totale_calcolato, totale_pagato)
                    VALUES (?, NOW(), ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "idd", $id_cliente, $totale, $totale);
            mysqli_stmt_execute($stmt);

            $id_vendita = mysqli_insert_id($conn);

            $sql = "INSERT INTO Dettaglio_Vendite (id_vendita, id_prodotto, quantita, prezzo_unitario)
                    VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "iidd", $id_vendita, $id_prodotto, $quantita, $prezzo);
            mysqli_stmt_execute($stmt);

            mysqli_commit($conn);

            $messaggio = "<p style='color:green;'>Vendita completata!</p>";

        } catch (Exception $e) {
            mysqli_rollback($conn);
            $messaggio = "<p style='color:red;'>Errore: " . $e->getMessage() . "</p>";
        }
    }

    $ris_clienti = mysqli_query($conn, $sql_clienti);
    $ris_prodotti = mysqli_query($conn, $sql_prodotti);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Vendita</title>
</head>
<body>

<h1>Registra Vendita</h1>

<?php echo $messaggio; ?>

<form method="POST">

    <label>Cliente:</label><br>
    <select name="id_cliente" required>
        <option value="">-- Seleziona cliente --</option>
        <?php while ($c = mysqli_fetch_assoc($ris_clienti)) { ?>
            <option value="<?php echo $c['id_cliente']; ?>">
                <?php echo $c['nome']; ?>
            </option>
        <?php } ?>
    </select>
    <br><br>

    <label>Prodotto:</label><br>
    <select name="id_prodotto" required>
        <option value="">-- Seleziona prodotto --</option>
        <?php while ($p = mysqli_fetch_assoc($ris_prodotti)) { ?>
            <option value="<?php echo $p['id_prodotto']; ?>">
                <?php echo $p['nome'] . " (" . $p['tipo'] . ")"; ?>
            </option>
        <?php } ?>
    </select>
    <br><br>

    <label>Quantità:</label><br>
    <input type="number" step="0.01" name="quantita" required>
    <br><br>

    <button type="submit">Vendi</button>

</form>

</body>
</html>