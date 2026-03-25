<?php
include 'config.php';

$messaggio = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_prodotto = isset($_POST['id_prodotto']) ? (int)$_POST['id_prodotto'] : 0;
    $tipo_lavorazione = isset($_POST['tipo_lavorazione']) ? trim($_POST['tipo_lavorazione']) : '';
    $data = date('Y-m-d');

    if ($id_prodotto <= 0 || $tipo_lavorazione === '') {
        $messaggio = "<p style='color:red;'>Dati non validi.</p>";
    } else {
        try {
            $sql = "INSERT INTO Lavorazioni (id_prodotto, tipo_lavorazione, data_lavorazione)
                    VALUES (?, ?, ?)";

            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "iss", $id_prodotto, $tipo_lavorazione, $data);
            mysqli_stmt_execute($stmt);

            $messaggio = "<p style='color:green;'>Lavorazione registrata con successo!</p>";

        } catch (Exception $e) {
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
    <title>Registra lavorazione</title>
</head>
<body>
    <h1>Registra lavorazione</h1>
    <p><a href="index.php">Torna alla dashboard</a></p>

    <?php echo $messaggio; ?>

    <form method="POST" action="lavorazione.php">
        <input type="number" name="id_prodotto" placeholder="ID prodotto" required><br><br>

        <input type="text" name="tipo_lavorazione" placeholder="Tipo lavorazione (es. essiccazione)" required><br><br>

        <button type="submit">Registra lavorazione</button>
    </form>
</body>
</html>