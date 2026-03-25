<?php
include 'config.php';

if (isset($_POST['add_cat'])) {
    $nome = mysqli_real_escape_string($conn, trim($_POST['nome_cat']));
    if ($nome != '') {
        $check = mysqli_query($conn, "SELECT id_categoria FROM Categorie WHERE nome = '$nome'");
        if (mysqli_num_rows($check) == 0) {
            mysqli_query($conn, "INSERT INTO Categorie (nome) VALUES ('$nome')");
        }
    }
    header("Location: gestione_sistema.php");
    exit();
}

if (isset($_POST['add_sede'])) {
    $nome = mysqli_real_escape_string($conn, trim($_POST['nome_sede']));
    if ($nome != '') {
        $check = mysqli_query($conn, "SELECT id_sede FROM Sedi WHERE nome_sede = '$nome'");
        if (mysqli_num_rows($check) == 0) {
            mysqli_query($conn, "INSERT INTO Sedi (nome_sede) VALUES ('$nome')");
        }
    }
    header("Location: gestione_sistema.php");
    exit();
}

if (isset($_GET['del_prod'])) {
    $id = intval($_GET['del_prod']);
    mysqli_query($conn, "DELETE FROM Prodotti WHERE id_prodotto = $id");
    header("Location: gestione_sistema.php");
    exit();
}

$categorie = mysqli_query($conn, "SELECT * FROM Categorie ORDER BY nome ASC");
$sedi = mysqli_query($conn, "SELECT * FROM Sedi ORDER BY nome_sede ASC");
$prodotti = mysqli_query($conn, "SELECT id_prodotto, nome FROM Prodotti ORDER BY nome ASC");
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

        <div class="card-grid footer-space">
            <div class="panel">
                <h2>Nuova categoria</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>Nome categoria</label>
                        <input type="text" name="nome_cat" required>
                    </div>
                    <button type="submit" name="add_cat">Aggiungi categoria</button>
                </form>
                <h3>Elenco categorie</h3>
                <ul class="list-clean">
                    <?php while ($c = mysqli_fetch_assoc($categorie)) { ?>
                        <li><?php echo $c['nome']; ?></li>
                    <?php } ?>
                </ul>
            </div>

            <div class="panel">
                <h2>Nuova sede</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>Nome sede</label>
                        <input type="text" name="nome_sede" required>
                    </div>
                    <button type="submit" name="add_sede">Aggiungi sede</button>
                </form>
                <h3>Elenco sedi</h3>
                <ul class="list-clean">
                    <?php while ($s = mysqli_fetch_assoc($sedi)) { ?>
                        <li><?php echo $s['nome_sede']; ?></li>
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
                        <td><?php echo $p['nome']; ?></td>
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
