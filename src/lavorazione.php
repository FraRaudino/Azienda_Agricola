<?php
include 'config.php';

$messaggio = "";
$prodotti = mysqli_query($conn, "SELECT id_prodotto, nome, tipo FROM Prodotti ORDER BY nome");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_prodotto = intval($_POST['id_prodotto']);
    $tipo_lavorazione = mysqli_real_escape_string($conn, trim($_POST['tipo_lavorazione']));
    $data = date('Y-m-d');

    if ($id_prodotto <= 0 || $tipo_lavorazione == '') {
        $messaggio = "<div class='message error'>Controlla i dati inseriti.</div>";
    } else {
        $sql = "INSERT INTO Lavorazioni (id_prodotto, tipo_lavorazione, data_lavorazione) VALUES ($id_prodotto, '$tipo_lavorazione', '$data')";

        if (mysqli_query($conn, $sql)) {
            $messaggio = "<div class='message success'>Lavorazione registrata con successo.</div>";
        } else {
            $messaggio = "<div class='message error'>Errore durante il salvataggio della lavorazione.</div>";
        }
    }

    $prodotti = mysqli_query($conn, "SELECT id_prodotto, nome, tipo FROM Prodotti ORDER BY nome");
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lavorazione</title>
    <link rel="stylesheet" href="app.css">
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1>Registra lavorazione</h1>
            <p>Salva la lavorazione eseguita su un prodotto.</p>
        </div>

        <?php echo $messaggio; ?>

        <div class="panel">
            <form method="POST">
                <div class="form-group">
                    <label>Prodotto</label>
                    <select name="id_prodotto" required>
                        <option value="">Seleziona prodotto</option>
                        <?php while ($p = mysqli_fetch_assoc($prodotti)) { ?>
                            <option value="<?php echo $p['id_prodotto']; ?>"><?php echo $p['nome']; ?> (<?php echo $p['tipo']; ?>)</option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Tipo lavorazione</label>
                    <input type="text" name="tipo_lavorazione" placeholder="Esempio: essiccazione" required>
                </div>

                <div class="actions">
                    <button type="submit">Salva lavorazione</button>
                    <a class="btn btn-light" href="index.php">Torna alla dashboard</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
