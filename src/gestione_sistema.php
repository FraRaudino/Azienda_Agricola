<?php
include 'config.php';

// 1. AGGIUNTA CATEGORIA (Con controllo duplicati come da traccia)
if (isset($_POST['add_cat'])) {
    $nome = mysqli_real_escape_string($conn, trim($_POST['nome_cat']));
    if (!empty($nome)) {
        // Verifichiamo se esiste già
        $check = mysqli_query($conn, "SELECT id_categoria FROM Categorie WHERE nome = '$nome'");
        if (mysqli_num_rows($check) == 0) {
            mysqli_query($conn, "INSERT INTO Categorie (nome) VALUES ('$nome')");
        }
    }
    header("Location: gestione_sistema.php"); // Evita il reinvio del form con F5
    exit();
}

// 2. AGGIUNTA SEDE
if (isset($_POST['add_sede'])) {
    $nome = mysqli_real_escape_string($conn, trim($_POST['nome_sede']));
    if (!empty($nome)) {
        $check = mysqli_query($conn, "SELECT id_sede FROM Sedi WHERE nome_sede = '$nome'");
        if (mysqli_num_rows($check) == 0) {
            mysqli_query($conn, "INSERT INTO Sedi (nome_sede) VALUES ('$nome')");
        }
    }
    header("Location: gestione_sistema.php");
    exit();
}

// 3. ELIMINAZIONE PRODOTTO
if (isset($_GET['del_prod'])) {
    $id = intval($_GET['del_prod']);
    // Usiamo @ per ignorare l'errore se ci sono vincoli di integrità (es. vendite collegate)
    // In alternativa, il database bloccherà l'azione se il prodotto è usato altrove
    mysqli_query($conn, "DELETE FROM Prodotti WHERE id_prodotto = $id");
    header("Location: gestione_sistema.php");
    exit();
}

// 4. RECUPERO DATI PER LA VISUALIZZAZIONE
$categorie = mysqli_query($conn, "SELECT * FROM Categorie ORDER BY nome ASC");
$sedi = mysqli_query($conn, "SELECT * FROM Sedi ORDER BY nome_sede ASC");
$prodotti = mysqli_query($conn, "SELECT id_prodotto, nome FROM Prodotti ORDER BY nome ASC");
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Impostazioni di Sistema</title>
</head>
<body>
    <h1>Impostazioni di Sistema</h1>
    <nav>
        <a href="index.php"><b>← Torna alla Dashboard</b></a>
    </nav>
    <hr>

    <
   <table style="border-collapse: collapse; width: 100%;">
        <tr>
            <td width="50%" valign="top">
                <h3>Nuova Categoria</h3>
                <form method="POST">
                    <input type="text" name="nome_cat" placeholder="Es: Conserve" required>
                    <button type="submit" name="add_cat">Aggiungi</button>
                </form>
                <h4>Elenco Categorie:</h4>
                <ul>
                    <?php while($c = mysqli_fetch_assoc($categorie)) { ?>
                        <li><?php echo $c['nome']; ?></li>
                    <?php } ?>
                </ul>
            </td>

            <td width="50%" valign="top">
                <h3>Nuova Sede / Luogo</h3>
                <form method="POST">
                    <input type="text" name="nome_sede" placeholder="Es: Laboratorio" required>
                    <button type="submit" name="add_sede">Aggiungi</button>
                </form>
                <h4>Elenco Sedi:</h4>
                <ul>
                    <?php while($s = mysqli_fetch_assoc($sedi)) { ?>
                        <li><?php echo $s['nome_sede']; ?></li>
                    <?php } ?>
                </ul>
            </td>
        </tr>
    </table>

    <hr>

    <h3>Gestione Prodotti (Eliminazione)</h3>
    <p><small><i>Nota: Non puoi eliminare prodotti che hanno già delle vendite registrate.</i></small></p>
    
   <table style="border-collapse: collapse; width: 50%;">
        <tr style="background-color: #f2f2f2;">
            <th style="padding: 8px;">Nome Prodotto</th>
            <th style="padding: 8px;">Azione</th>
        </tr>
        <?php while($p = mysqli_fetch_assoc($prodotti)) { ?>
            <tr>
                <td style="padding: 8px;"><?php echo $p['nome']; ?></td>
                <td style="padding: 8px; text-align: center;">
                    <a href="?del_prod=<?php echo $p['id_prodotto']; ?>" 
                       style="color: red; text-decoration: none;"
                       onclick="return confirm('Sei sicuro di voler eliminare questo prodotto?');">
                       <b>[Elimina]</b>
                    </a>
                </td>
            </tr>
        <?php } ?>
    </table>

</body>
</html>