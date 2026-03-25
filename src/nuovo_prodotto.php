<?php
include 'config.php';

$messaggio = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = mysqli_real_escape_string($conn, trim($_POST['nome']));
    $id_categoria = intval($_POST['id_categoria']);
    $tipo = mysqli_real_escape_string($conn, $_POST['tipo']);
    $prezzo = floatval($_POST['prezzo']);
    $um = mysqli_real_escape_string($conn, $_POST['um']);
    $qta = floatval($_POST['quantita']);
    $data_prod = mysqli_real_escape_string($conn, $_POST['data_produzione']);
    $peso_netto = floatval($_POST['peso_netto']);

    if ($nome == '' || $id_categoria <= 0 || $prezzo <= 0) {
        $messaggio = "<div class='message error'>Controlla i dati inseriti.</div>";
    } else {
        $sql = "INSERT INTO Prodotti (nome, id_categoria, tipo) VALUES ('$nome', $id_categoria, '$tipo')";

        if (mysqli_query($conn, $sql)) {
            $id_p = mysqli_insert_id($conn);
            mysqli_query($conn, "INSERT INTO Listino_Prezzi (id_prodotto, prezzo_unitario) VALUES ($id_p, $prezzo)");

            if ($tipo == 'Fresco') {
                mysqli_query($conn, "INSERT INTO Prodotti_Freschi (id_prodotto, unita_misura) VALUES ($id_p, '$um')");
            }

            if ($tipo == 'Riserva') {
                mysqli_query($conn, "INSERT INTO Prodotti_Riserva (id_prodotto, peso_totale_disponibile, unita_misura, data_produzione) VALUES ($id_p, $qta, '$um', '$data_prod')");
            }

            if ($tipo == 'Confezionato') {
                $pezzi = intval($qta);
                mysqli_query($conn, "INSERT INTO Prodotti_Confezionati (id_prodotto, giacenza_pezzi, peso_netto_confezione) VALUES ($id_p, $pezzi, $peso_netto)");
            }

            header("Location: index.php");
            exit();
        } else {
            $messaggio = "<div class='message error'>Errore durante il salvataggio del prodotto.</div>";
        }
    }
}

$res_cat = mysqli_query($conn, "SELECT * FROM Categorie ORDER BY nome");
$res_sedi = mysqli_query($conn, "SELECT * FROM Sedi ORDER BY nome_sede");
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuovo prodotto</title>
    <link rel="stylesheet" href="app.css">
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1>Nuovo prodotto</h1>
            <p>Inserisci un prodotto fresco, di riserva o confezionato.</p>
        </div>

        <?php echo $messaggio; ?>

        <div class="panel">
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nome prodotto</label>
                        <input type="text" name="nome" required>
                    </div>

                    <div class="form-group">
                        <label>Categoria</label>
                        <select name="id_categoria" required>
                            <?php while ($c = mysqli_fetch_assoc($res_cat)) { ?>
                                <option value="<?php echo $c['id_categoria']; ?>"><?php echo $c['nome']; ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Tipo prodotto</label>
                        <select name="tipo" required>
                            <option value="Fresco">Fresco</option>
                            <option value="Riserva">Riserva</option>
                            <option value="Confezionato">Confezionato</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Prezzo unitario</label>
                        <input type="number" step="0.01" min="0.01" name="prezzo" required>
                    </div>

                    <div class="form-group">
                        <label>Luogo di conservazione</label>
                        <select name="id_sede">
                            <?php while ($s = mysqli_fetch_assoc($res_sedi)) { ?>
                                <option value="<?php echo $s['id_sede']; ?>"><?php echo $s['nome_sede']; ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Unità di misura</label>
                        <select name="um">
                            <option value="kg">kg</option>
                            <option value="litro">litro</option>
                            <option value="pezzo">pezzo</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Quantità iniziale o giacenza</label>
                        <input type="number" step="0.01" min="0" name="quantita" value="0">
                    </div>

                    <div class="form-group">
                        <label>Data produzione o lavorazione</label>
                        <input type="date" name="data_produzione" value="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="form-group">
                        <label>Peso netto confezione</label>
                        <input type="number" step="0.01" min="0" name="peso_netto" value="0">
                    </div>
                </div>

                <div class="actions">
                    <button type="submit">Salva prodotto</button>
                    <a class="btn btn-light" href="index.php">Torna alla dashboard</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
