<?php
include 'config.php';

$messaggio = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_riserva = isset($_POST['id_prodotto_riserva']) ? (int)$_POST['id_prodotto_riserva'] : 0;
    $id_confezionato = isset($_POST['id_prodotto_confezionato']) ? (int)$_POST['id_prodotto_confezionato'] : 0;
    $quantita = isset($_POST['quantita_utilizzata']) ? (float)$_POST['quantita_utilizzata'] : 0;
    $num_confezioni = isset($_POST['numero_confezioni']) ? (int)$_POST['numero_confezioni'] : 0;
    $data = date('Y-m-d');

    if ($id_riserva <= 0 || $id_confezionato <= 0 || $quantita <= 0 || $num_confezioni <= 0) {
        $messaggio = "<p style='color:red;'>Dati non validi.</p>";
    } else {

        mysqli_begin_transaction($conn);

        try {
            $sql_check = "SELECT peso_totale_disponibile 
                          FROM Prodotti_Riserva 
                          WHERE id_prodotto = ?";
            $stmt = mysqli_prepare($conn, $sql_check);
            mysqli_stmt_bind_param($stmt, "i", $id_riserva);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);

            if (!$row) {
                throw new Exception("Prodotto riserva non trovato.");
            }

            if ($row['peso_totale_disponibile'] < $quantita) {
                throw new Exception("Quantità insufficiente in riserva.");
            }

            $sql1 = "UPDATE Prodotti_Riserva 
                     SET peso_totale_disponibile = peso_totale_disponibile - ? 
                     WHERE id_prodotto = ?";
            $stmt1 = mysqli_prepare($conn, $sql1);
            mysqli_stmt_bind_param($stmt1, "di", $quantita, $id_riserva);
            mysqli_stmt_execute($stmt1);

            $sql2 = "UPDATE Prodotti_Confezionati 
                     SET giacenza_pezzi = giacenza_pezzi + ? 
                     WHERE id_prodotto = ?";
            $stmt2 = mysqli_prepare($conn, $sql2);
            mysqli_stmt_bind_param($stmt2, "ii", $num_confezioni, $id_confezionato);
            mysqli_stmt_execute($stmt2);

            if (mysqli_stmt_affected_rows($stmt2) == 0) {
                throw new Exception("Prodotto confezionato non trovato.");
            }

            $sql3 = "INSERT INTO Confezionamenti 
                    (id_prodotto_riserva, id_prodotto_confezionato, data_confezionamento, quantita_utilizzata, numero_confezioni)
                    VALUES (?, ?, ?, ?, ?)";
            $stmt3 = mysqli_prepare($conn, $sql3);
            mysqli_stmt_bind_param($stmt3, "iisdi", $id_riserva, $id_confezionato, $data, $quantita, $num_confezioni);
            mysqli_stmt_execute($stmt3);

            mysqli_commit($conn);
            $messaggio = "<p style='color:green;'>Confezionamento completato con successo!</p>";

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
    <title>Confezionamento Prodotti</title>
</head>
<body>
    <h1>Confeziona prodotti</h1>
    <p><a href="index.php">Torna alla dashboard</a></p>

    <?php echo $messaggio; ?>

    <form method="POST" action="confezionamento.php">
        <input type="number" name="id_prodotto_riserva" placeholder="ID prodotto riserva" required><br><br>

        <input type="number" name="id_prodotto_confezionato" placeholder="ID prodotto confezionato" required><br><br>

        <input type="number" step="0.01" name="quantita_utilizzata" placeholder="Quantità usata (kg/l)" required><br><br>

        <input type="number" name="numero_confezioni" placeholder="Numero confezioni" required><br><br>

        <button type="submit">Confeziona</button>
    </form>
</body>
</html>