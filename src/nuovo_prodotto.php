<?php
include 'config.php';

$messaggio = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome       = trim($_POST['nome'] ?? '');
    $id_categoria = intval($_POST['id_categoria'] ?? 0);
    $id_sede    = intval($_POST['id_sede'] ?? 0);
    $tipo       = trim($_POST['tipo'] ?? '');
    $prezzo     = floatval($_POST['prezzo'] ?? 0);
    $um         = trim($_POST['um'] ?? '');
    $qta        = floatval($_POST['quantita'] ?? 0);
    $data_prod  = trim($_POST['data_produzione'] ?? '');
    $peso_netto = floatval($_POST['peso_netto'] ?? 0);

    $tipi_validi = ['Fresco', 'Riserva', 'Confezionato'];
    $um_valide   = ['kg', 'litro', 'grammo', 'pezzo'];

    if ($nome == '') {
        $messaggio = "<div class='message error'>Inserisci il nome del prodotto.</div>";
    } elseif (mb_strlen($nome) > 100) {
        $messaggio = "<div class='message error'>Il nome è troppo lungo (max 100 caratteri).</div>";
    } elseif ($id_categoria <= 0) {
        $messaggio = "<div class='message error'>Seleziona una categoria valida.</div>";
    } elseif ($id_sede <= 0) {
        $messaggio = "<div class='message error'>Seleziona una sede valida.</div>";
    } elseif (!in_array($tipo, $tipi_validi)) {
        $messaggio = "<div class='message error'>Tipo prodotto non valido.</div>";
    } elseif ($prezzo <= 0 || $prezzo > 99999) {
        $messaggio = "<div class='message error'>Inserisci un prezzo valido (tra 0.01 e 99999).</div>";
    } elseif (!in_array($um, $um_valide)) {
        $messaggio = "<div class='message error'>Unità di misura non valida.</div>";
    } elseif ($qta < 0) {
        $messaggio = "<div class='message error'>La quantità non può essere negativa.</div>";
    } elseif ($tipo == 'Riserva' && $data_prod != '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_prod)) {
        $messaggio = "<div class='message error'>Data produzione non valida.</div>";
    } elseif ($tipo == 'Confezionato' && $peso_netto < 0) {
        $messaggio = "<div class='message error'>Il peso netto non può essere negativo.</div>";
    } else {
        // Verifica che categoria e sede esistano
        $stmt_cat = mysqli_prepare($conn, "SELECT id_categoria FROM Categorie WHERE id_categoria = ?");
        mysqli_stmt_bind_param($stmt_cat, 'i', $id_categoria);
        mysqli_stmt_execute($stmt_cat);
        mysqli_stmt_store_result($stmt_cat);
        $cat_ok = mysqli_stmt_num_rows($stmt_cat) > 0;
        mysqli_stmt_close($stmt_cat);

        $stmt_sede = mysqli_prepare($conn, "SELECT id_sede FROM Sedi WHERE id_sede = ?");
        mysqli_stmt_bind_param($stmt_sede, 'i', $id_sede);
        mysqli_stmt_execute($stmt_sede);
        mysqli_stmt_store_result($stmt_sede);
        $sede_ok = mysqli_stmt_num_rows($stmt_sede) > 0;
        mysqli_stmt_close($stmt_sede);

        if (!$cat_ok) {
            $messaggio = "<div class='message error'>Categoria non trovata.</div>";
        } elseif (!$sede_ok) {
            $messaggio = "<div class='message error'>Sede non trovata.</div>";
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO Prodotti (nome, id_categoria, id_sede, tipo) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'siis', $nome, $id_categoria, $id_sede, $tipo);

            if (mysqli_stmt_execute($stmt)) {
                $id_p = mysqli_insert_id($conn);
                mysqli_stmt_close($stmt);

                $stmt_lp = mysqli_prepare($conn, "INSERT INTO Listino_Prezzi (id_prodotto, prezzo_unitario) VALUES (?, ?)");
                mysqli_stmt_bind_param($stmt_lp, 'id', $id_p, $prezzo);
                mysqli_stmt_execute($stmt_lp);
                mysqli_stmt_close($stmt_lp);

                if ($tipo == 'Fresco') {
                    $stmt_f = mysqli_prepare($conn, "INSERT INTO Prodotti_Freschi (id_prodotto, unita_misura, quantita_disponibile) VALUES (?, ?, ?)");
                    mysqli_stmt_bind_param($stmt_f, 'isd', $id_p, $um, $qta);
                    mysqli_stmt_execute($stmt_f);
                    mysqli_stmt_close($stmt_f);
                }

                if ($tipo == 'Riserva') {
                    $data_prod_val = ($data_prod != '') ? $data_prod : date('Y-m-d');
                    $stmt_r = mysqli_prepare($conn, "INSERT INTO Prodotti_Riserva (id_prodotto, peso_totale_disponibile, unita_misura, data_produzione) VALUES (?, ?, ?, ?)");
                    mysqli_stmt_bind_param($stmt_r, 'idss', $id_p, $qta, $um, $data_prod_val);
                    mysqli_stmt_execute($stmt_r);
                    mysqli_stmt_close($stmt_r);
                }

                if ($tipo == 'Confezionato') {
                    $pezzi = intval($qta);
                    $stmt_c = mysqli_prepare($conn, "INSERT INTO Prodotti_Confezionati (id_prodotto, giacenza_pezzi, peso_netto_confezione) VALUES (?, ?, ?)");
                    mysqli_stmt_bind_param($stmt_c, 'iid', $id_p, $pezzi, $peso_netto);
                    mysqli_stmt_execute($stmt_c);
                    mysqli_stmt_close($stmt_c);
                }

                header("Location: index.php");
                exit();
            } else {
                mysqli_stmt_close($stmt);
                $messaggio = "<div class='message error'>Errore durante il salvataggio del prodotto.</div>";
            }
        }
    }
}

$res_cat  = mysqli_query($conn, "SELECT * FROM Categorie ORDER BY nome");
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
                        <input type="text" name="nome" maxlength="100" required>
                    </div>

                    <div class="form-group">
                        <label>Categoria</label>
                        <select name="id_categoria" required>
                            <?php while ($c = mysqli_fetch_assoc($res_cat)) { ?>
                                <option value="<?php echo $c['id_categoria']; ?>"><?php echo htmlspecialchars($c['nome']); ?></option>
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
                        <input type="number" step="0.01" min="0.01" max="99999" name="prezzo" required>
                    </div>

                    <div class="form-group">
                        <label>Luogo di conservazione</label>
                        <select name="id_sede" required>
                            <?php while ($s = mysqli_fetch_assoc($res_sedi)) { ?>
                                <option value="<?php echo $s['id_sede']; ?>"><?php echo htmlspecialchars($s['nome_sede']); ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Unità di misura</label>
                        <select name="um">
                            <option value="kg">kg</option>
                            <option value="litro">litro</option>
                            <option value="grammo">grammo</option>
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
