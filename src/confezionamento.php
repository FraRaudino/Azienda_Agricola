<?php
include 'config.php';

$messaggio = "";
$riserve = mysqli_query($conn, "SELECT p.id_prodotto, p.nome, r.peso_totale_disponibile, r.unita_misura FROM Prodotti p JOIN Prodotti_Riserva r ON p.id_prodotto = r.id_prodotto ORDER BY p.nome");
$confezionati = mysqli_query($conn, "SELECT p.id_prodotto, p.nome, c.giacenza_pezzi FROM Prodotti p JOIN Prodotti_Confezionati c ON p.id_prodotto = c.id_prodotto ORDER BY p.nome");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_riserva = intval($_POST['id_prodotto_riserva']);
    $id_confezionato = intval($_POST['id_prodotto_confezionato']);
    $quantita = floatval($_POST['quantita_utilizzata']);
    $num_confezioni = intval($_POST['numero_confezioni']);
    $data = date('Y-m-d');

    if ($id_riserva <= 0 || $id_confezionato <= 0 || $quantita <= 0 || $num_confezioni <= 0) {
        $messaggio = "<div class='message error'>Controlla i dati inseriti.</div>";
    } else {
        $riga_riserva = mysqli_fetch_assoc(mysqli_query($conn, "SELECT peso_totale_disponibile FROM Prodotti_Riserva WHERE id_prodotto = $id_riserva"));
        $riga_conf = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id_prodotto FROM Prodotti_Confezionati WHERE id_prodotto = $id_confezionato"));

        if (!$riga_riserva) {
            $messaggio = "<div class='message error'>Prodotto di riserva non trovato.</div>";
        } elseif (!$riga_conf) {
            $messaggio = "<div class='message error'>Prodotto confezionato non trovato.</div>";
        } elseif ($riga_riserva['peso_totale_disponibile'] < $quantita) {
            $messaggio = "<div class='message error'>Quantità insufficiente in riserva.</div>";
        } else {
            mysqli_query($conn, "UPDATE Prodotti_Riserva SET peso_totale_disponibile = peso_totale_disponibile - $quantita WHERE id_prodotto = $id_riserva");
            mysqli_query($conn, "UPDATE Prodotti_Confezionati SET giacenza_pezzi = giacenza_pezzi + $num_confezioni WHERE id_prodotto = $id_confezionato");
            mysqli_query($conn, "INSERT INTO Confezionamenti (id_prodotto_riserva, id_prodotto_confezionato, data_confezionamento, quantita_utilizzata, numero_confezioni) VALUES ($id_riserva, $id_confezionato, '$data', $quantita, $num_confezioni)");
            $messaggio = "<div class='message success'>Confezionamento completato con successo.</div>";
        }
    }

    $riserve = mysqli_query($conn, "SELECT p.id_prodotto, p.nome, r.peso_totale_disponibile, r.unita_misura FROM Prodotti p JOIN Prodotti_Riserva r ON p.id_prodotto = r.id_prodotto ORDER BY p.nome");
    $confezionati = mysqli_query($conn, "SELECT p.id_prodotto, p.nome, c.giacenza_pezzi FROM Prodotti p JOIN Prodotti_Confezionati c ON p.id_prodotto = c.id_prodotto ORDER BY p.nome");
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confezionamento</title>
    <link rel="stylesheet" href="app.css">
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1>Confeziona prodotti</h1>
            <p>Preleva una quantità dalla riserva e trasformala in confezioni.</p>
        </div>

        <?php echo $messaggio; ?>

        <div class="panel">
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Prodotto in riserva</label>
                        <select name="id_prodotto_riserva" required>
                            <option value="">Seleziona riserva</option>
                            <?php while ($r = mysqli_fetch_assoc($riserve)) { ?>
                                <option value="<?php echo $r['id_prodotto']; ?>"><?php echo $r['nome']; ?> - <?php echo $r['peso_totale_disponibile']; ?> <?php echo $r['unita_misura']; ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Prodotto confezionato</label>
                        <select name="id_prodotto_confezionato" required>
                            <option value="">Seleziona prodotto</option>
                            <?php while ($c = mysqli_fetch_assoc($confezionati)) { ?>
                                <option value="<?php echo $c['id_prodotto']; ?>"><?php echo $c['nome']; ?> - giacenza <?php echo $c['giacenza_pezzi']; ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Quantità usata</label>
                        <input type="number" step="0.01" min="0.01" name="quantita_utilizzata" required>
                    </div>

                    <div class="form-group">
                        <label>Numero confezioni</label>
                        <input type="number" min="1" name="numero_confezioni" required>
                    </div>
                </div>

                <div class="actions">
                    <button type="submit">Salva confezionamento</button>
                    <a class="btn btn-light" href="index.php">Torna alla dashboard</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
