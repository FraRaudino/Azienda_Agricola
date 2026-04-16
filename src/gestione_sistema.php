<?php
include 'config.php';

if (isset($_POST['add_cat'])) {

     $id = intval($_POST['del_cat']);

    if ($id > 0) {
        $stmt = mysqli_prepare($conn, "DELETE FROM Categorie WHERE id_categoria = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    header("Location: gestione_sistema.php");
    exit();


    if (isset($_POST['del_sede'])) {
    $id = intval($_POST['del_sede']);

    if ($id > 0) {
        $stmt = mysqli_prepare($conn, "DELETE FROM Sedi WHERE id_sede = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    header("Location: gestione_sistema.php");
    exit();
}


    $nome = trim($_POST['nome_cat'] ?? '');

    if ($nome == '') {
        header("Location: gestione_sistema.php?err=nome_cat_vuoto");
        exit();
    } elseif (mb_strlen($nome) > 80) {
        header("Location: gestione_sistema.php?err=nome_cat_lungo");
        exit();
    } else {
        $check = mysqli_prepare($conn, "SELECT id_categoria FROM Categorie WHERE nome = ?");
        mysqli_stmt_bind_param($check, 's', $nome);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);
        $esiste = mysqli_stmt_num_rows($check) > 0;
        mysqli_stmt_close($check);

        if (!$esiste) {
            $stmt = mysqli_prepare($conn, "INSERT INTO Categorie (nome) VALUES (?)");
            mysqli_stmt_bind_param($stmt, 's', $nome);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    header("Location: gestione_sistema.php");
    exit();
}

if (isset($_POST['add_sede'])) {
    $nome = trim($_POST['nome_sede'] ?? '');

    if ($nome == '') {
        header("Location: gestione_sistema.php?err=nome_sede_vuoto");
        exit();
    } elseif (mb_strlen($nome) > 80) {
        header("Location: gestione_sistema.php?err=nome_sede_lungo");
        exit();
    } else {
        $check = mysqli_prepare($conn, "SELECT id_sede FROM Sedi WHERE nome_sede = ?");
        mysqli_stmt_bind_param($check, 's', $nome);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);
        $esiste = mysqli_stmt_num_rows($check) > 0;
        mysqli_stmt_close($check);

        if (!$esiste) {
            $stmt = mysqli_prepare($conn, "INSERT INTO Sedi (nome_sede) VALUES (?)");
            mysqli_stmt_bind_param($stmt, 's', $nome);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    header("Location: gestione_sistema.php");
    exit();
}

if (isset($_GET['del_prod'])) {
    $id = intval($_GET['del_prod']);
    if ($id > 0) {
        $stmt = mysqli_prepare($conn, "DELETE FROM Prodotti WHERE id_prodotto = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header("Location: gestione_sistema.php");
    exit();
}

// Messaggi di errore da redirect
$errori = [
    'nome_cat_vuoto'  => "Il nome della categoria non può essere vuoto.",
    'nome_cat_lungo'  => "Il nome della categoria è troppo lungo (max 80 caratteri).",
    'nome_sede_vuoto' => "Il nome della sede non può essere vuoto.",
    'nome_sede_lungo' => "Il nome della sede è troppo lungo (max 80 caratteri).",
];
$messaggio = "";
if (isset($_GET['err']) && array_key_exists($_GET['err'], $errori)) {
    $messaggio = "<div class='message error'>" . $errori[$_GET['err']] . "</div>";
}

$categorie = mysqli_query($conn, "SELECT * FROM Categorie ORDER BY nome ASC");
$sedi      = mysqli_query($conn, "SELECT * FROM Sedi ORDER BY nome_sede ASC");
$prodotti  = mysqli_query($conn, "SELECT id_prodotto, nome FROM Prodotti ORDER BY nome ASC");
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impostazioni di sistema</title>
    <link rel="stylesheet" href="app.css">
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1>Impostazioni di sistema</h1>
            <p>Gestisci categorie, sedi e prodotti registrati.</p>
        </div>

        <?php echo $messaggio; ?>

        <div class="card-grid footer-space">
            <div class="panel">
                <h2>Nuova categoria</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>Nome categoria</label>
                        <input type="text" name="nome_cat" maxlength="80" required>
                    </div>
                    <button type="submit" name="add_cat">Aggiungi categoria</button>
                </form>
                <h3>Elenco categorie</h3>
                <ul class="list-clean">
                    <?php while ($c = mysqli_fetch_assoc($categorie)) { ?>
                        <li>
                            <?php echo htmlspecialchars($c['nome']); ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="del_cat" value="<?php echo $c['id_categoria']; ?>">
                                    <button class="btn btn-danger" onclick="return confirm('Eliminare questa categoria?');">
                                        Elimina
                                    </button>
                                </form>
                        </li>
                    <?php } ?>
                </ul>
            </div>

            <div class="panel">
                <h2>Nuova sede</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>Nome sede</label>
                        <input type="text" name="nome_sede" maxlength="80" required>
                    </div>
                    <button type="submit" name="add_sede">Aggiungi sede</button>
                </form>
                <h3>Elenco sedi</h3>
                <ul class="list-clean">
                    <?php while ($s = mysqli_fetch_assoc($sedi)) { ?>
                        <li>
                            <?php echo htmlspecialchars($s['nome_sede']); ?>
                        
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="del_sede" value="<?php echo $s['id_sede']; ?>">
                                <button class="btn btn-danger" onclick="return confirm('Eliminare questa sede?');">
                                    Elimina
                                </button>
                            </form>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>

        <div class="panel table-wrap">
            <h2>Elimina prodotto</h2>
            <p class="small">Se un prodotto è collegato ad altre operazioni, il database potrebbe impedirne l'eliminazione.</p>
            <table>
                <tr>
                    <th>Prodotto</th>
                    <th>Azione</th>
                </tr>
                <?php while ($p = mysqli_fetch_assoc($prodotti)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($p['nome']); ?></td>
                        <td><a class="btn btn-danger" href="?del_prod=<?php echo $p['id_prodotto']; ?>" onclick="return confirm('Vuoi eliminare questo prodotto?');">Elimina</a></td>
                    </tr>
                <?php } ?>
            </table>
            <div class="actions" style="margin-top:18px;">
                <a class="btn btn-light" href="index.php">Torna alla dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>
