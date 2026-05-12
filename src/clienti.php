<?php
include 'config.php';

$messaggio = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
    $nickname = isset($_POST['nickname']) ? trim($_POST['nickname']) : '';
    $contatto = isset($_POST['contatto']) ? trim($_POST['contatto']) : '';
}

    if ($nome == '') {
        $messaggio = "<div class='message error'>Inserisci il nome del cliente.</div>";
    } elseif (mb_strlen($nome) > 100) {
        $messaggio = "<div class='message error'>Il nome è troppo lungo (max 100 caratteri).</div>";
    } elseif (mb_strlen($nickname) > 50) {
        $messaggio = "<div class='message error'>Il nickname è troppo lungo (max 50 caratteri).</div>";
    } elseif (mb_strlen($contatto) > 100) {
        $messaggio = "<div class='message error'>Il contatto è troppo lungo (max 100 caratteri).</div>";
    } else {
        $check = mysqli_prepare($conn, "SELECT id_cliente FROM Clienti WHERE nome = ?");
        mysqli_stmt_bind_param($check, 's', $nome);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $messaggio = "<div class='message error'>Esiste già un cliente con questo nome.</div>";
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO Clienti (nome, nickname, contatto) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'sss', $nome, $nickname, $contatto);

            if (mysqli_stmt_execute($stmt)) {
                $messaggio = "<div class='message success'>Cliente registrato con successo.</div>";
            } else {
                $messaggio = "<div class='message error'>Errore durante il salvataggio del cliente.</div>";
            }
            mysqli_stmt_close($stmt);
        }
        mysqli_stmt_close($check);
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
                        <input type="text" name="nome" maxlength="100" required>
                    </div>
                    <div class="form-group">
                        <label>Nickname</label>
                        <input type="text" name="nickname" maxlength="50">
                    </div>
                    <div class="form-group">
                        <label>Contatto</label>
                        <input type="text" name="contatto" maxlength="100">
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
                            <td><?php echo htmlspecialchars($row['nome']); ?></td>
                            <td><?php echo htmlspecialchars($row['nickname']); ?></td>
                            <td><?php echo htmlspecialchars($row['contatto']); ?></td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
