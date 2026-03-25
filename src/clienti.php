<?php
include 'config.php';

$messaggio = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = mysqli_real_escape_string($conn, trim($_POST['nome']));
    $nickname = mysqli_real_escape_string($conn, trim($_POST['nickname']));
    $contatto = mysqli_real_escape_string($conn, trim($_POST['contatto']));

    if ($nome == '') {
        $messaggio = "<div class='message error'>Inserisci il nome del cliente.</div>";
    } else {
        $sql = "INSERT INTO Clienti (nome, nickname, contatto) VALUES ('$nome', '$nickname', '$contatto')";
        if (mysqli_query($conn, $sql)) {
            $messaggio = "<div class='message success'>Cliente registrato con successo.</div>";
        } else {
            $messaggio = "<div class='message error'>Errore durante il salvataggio del cliente.</div>";
        }
    }
}

$res = mysqli_query($conn, "SELECT * FROM Clienti ORDER BY nome ASC");
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clienti</title>
    <link rel="stylesheet" href="app.css">
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1>Anagrafica clienti</h1>
            <p>Aggiungi nuovi clienti e consulta l'elenco completo.</p>
        </div>

        <?php echo $messaggio; ?>

        <div class="card-grid footer-space">
            <div class="panel">
                <h2>Aggiungi cliente</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>Nome e cognome</label>
                        <input type="text" name="nome" required>
                    </div>
                    <div class="form-group">
                        <label>Nickname</label>
                        <input type="text" name="nickname">
                    </div>
                    <div class="form-group">
                        <label>Contatto</label>
                        <input type="text" name="contatto">
                    </div>
                    <div class="actions">
                        <button type="submit">Registra cliente</button>
                        <a class="btn btn-light" href="index.php">Torna alla dashboard</a>
                    </div>
                </form>
            </div>

            <div class="panel table-wrap">
                <h2>Elenco clienti</h2>
                <table>
                    <tr>
                        <th>Nome</th>
                        <th>Nickname</th>
                        <th>Contatto</th>
                    </tr>
                    <?php while ($row = mysqli_fetch_assoc($res)) { ?>
                        <tr>
                            <td><?php echo $row['nome']; ?></td>
                            <td><?php echo $row['nickname']; ?></td>
                            <td><?php echo $row['contatto']; ?></td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
