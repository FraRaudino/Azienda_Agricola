<?php
include 'config.php';

$messaggio = "";
$prodotti = mysqli_query($conn, "SELECT id_prodotto, nome, tipo FROM Prodotti ORDER BY nome");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_prodotto     = intval($_POST['id_prodotto'] ?? 0);
    $tipo_lavorazione = trim($_POST['tipo_lavorazione'] ?? '');

    if ($id_prodotto <= 0) {
        $messaggio = "<div class='message error'>Seleziona un prodotto valido.</div>";
    } elseif ($tipo_lavorazione == '') {
        $messaggio = "<div class='message error'>Inserisci il tipo di lavorazione.</div>";
    } elseif (mb_strlen($tipo_lavorazione) > 100) {
        $messaggio = "<div class='message error'>Il tipo lavorazione è troppo lungo (max 100 caratteri).</div>";
    } else {
        // Verifica che il prodotto esista
        $stmt_check = mysqli_prepare($conn, "SELECT id_prodotto FROM Prodotti WHERE id_prodotto = ?");
        mysqli_stmt_bind_param($stmt_check, 'i', $id_prodotto);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);
        $prodotto_ok = mysqli_stmt_num_rows($stmt_check) > 0;
        mysqli_stmt_close($stmt_check);

        if (!$prodotto_ok) {
            $messaggio = "<div class='message error'>Prodotto non trovato.</div>";
        } else {
            $data = date('Y-m-d');
            $stmt = mysqli_prepare($conn, "INSERT INTO Lavorazioni (id_prodotto, tipo_lavorazione, data_lavorazione) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'iss', $id_prodotto, $tipo_lavorazione, $data);

            if (mysqli_stmt_execute($stmt)) {
                $messaggio = "<div class='message success'>Lavorazione registrata con successo.</div>";
            } else {
                $messaggio = "<div class='message error'>Errore durante il salvataggio della lavorazione.</div>";
            }
            mysqli_stmt_close($stmt);
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
                            <option value="<?php echo $p['id_prodotto']; ?>"><?php echo htmlspecialchars($p['nome']); ?> (<?php echo htmlspecialchars($p['tipo']); ?>)</option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group">

                <label>Tipo lavorazione</label>
                <select name="tipo_lavorazione" required>
                    <option value="">Seleziona lavorazione</option>
                    <option value="Essiccazione">Essiccazione</option>
                    <option value="Mielatura">Mielatura</option>
                    <option value="Estrazione">Estrazione</option>
                    <option value="Filtraggio">Filtraggio</option>
                    <option value="Maturazione">Maturazione</option>
                </select>
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
