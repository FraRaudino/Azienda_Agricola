<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = mysqli_real_escape_string($conn, $_POST['nome']);
    $nickname = mysqli_real_escape_string($conn, $_POST['nickname']);
    $contatto = mysqli_real_escape_string($conn, $_POST['contatto']);

    $sql = "INSERT INTO Clienti (nome, nickname, contatto) VALUES ('$nome', '$nickname', '$contatto')";
    mysqli_query($conn, $sql);
    header("Location: clienti.php");
}

$res = mysqli_query($conn, "SELECT * FROM Clienti ORDER BY nome ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gestione Clienti</title>
</head>
<body>
    <h1>Anagrafica Clienti</h1>
    <a href="index.php">Torna alla Dashboard</a>

    <h3>Aggiungi nuovo cliente</h3>
    <form method="POST">
        Nome/Cognome: <input type="text" name="nome" required>
        Nickname (es. Amico): <input type="text" name="nickname">
        Contatto (Tel/Email): <input type="text" name="contatto">
        <button type="submit">Registra Cliente</button>
    </form>

    <hr>

    <h3>Elenco Clienti</h3>
    <table style="border-collapse: collapse; width: 80%; border: 1px solid #333;">
        <tr>
            <th>Nome</th>
            <th>Nickname</th>
            <th>Contatto</th>
        </tr>
        <?php while($row = mysqli_fetch_assoc($res)) { ?>
            <tr>
                <td><?php echo $row['nome']; ?></td>
                <td><?php echo $row['nickname']; ?></td>
                <td><?php echo $row['contatto']; ?></td>
            </tr>
        <?php } ?>
    </table>
</body>
</html>