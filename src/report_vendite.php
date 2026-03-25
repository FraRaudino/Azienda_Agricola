<?php
include 'config.php';

// Prendiamo l'ID del cliente se qualcuno lo ha scelto, altrimenti 0
$id_scelto = 0;
if (isset($_GET['filtra_cliente'])) {
    $id_scelto = intval($_GET['filtra_cliente']);
}

// 1. QUERY SEMPLICE: Se non ho scelto un cliente (0), prendo TUTTO. 
// Se ho scelto un cliente, prendo solo le sue vendite.
if ($id_scelto == 0) {
    $sql = "SELECT v.*, c.nome as cliente, p.nome as prodotto, dv.quantita 
            FROM Vendite v
            JOIN Clienti c ON v.id_cliente = c.id_cliente
            JOIN Dettaglio_Vendite dv ON v.id_vendita = dv.id_vendita
            JOIN Prodotti p ON dv.id_prodotto = p.id_prodotto
            ORDER BY v.data_acquisto DESC";
} else {
    $sql = "SELECT v.*, c.nome as cliente, p.nome as prodotto, dv.quantita 
            FROM Vendite v
            JOIN Clienti c ON v.id_cliente = c.id_cliente
            JOIN Dettaglio_Vendite dv ON v.id_vendita = dv.id_vendita
            JOIN Prodotti p ON dv.id_prodotto = p.id_prodotto
            WHERE v.id_cliente = $id_scelto
            ORDER BY v.data_acquisto DESC";
}

$risultato = mysqli_query($conn, $sql);
$lista_clienti = mysqli_query($conn, "SELECT * FROM Clienti");
?>

<!DOCTYPE html>
<html>
<head><title>Report Vendite</title></head>
<body>
    <h1>Archivio Vendite</h1>
    <a href="index.php">Torna alla Dashboard</a>
    <hr>

    <form method="GET">
        Mostra vendite di: 
        <select name="filtra_cliente">
            <option value="0">-- Tutti i Clienti --</option>
            <?php while($cl = mysqli_fetch_assoc($lista_clienti)) { ?>
                <option value="<?php echo $cl['id_cliente']; ?>">
                    <?php echo $cl['nome']; ?>
                </option>
            <?php } ?>
        </select>
        <button type="submit">Filtra</button>
    </form>

    <br>

    <table style="border-collapse: collapse; width: 100%;">
        <tr style="background-color: #eee;">
            <th>Data</th>
            <th>Cliente</th>
            <th>Prodotto</th>
            <th>Quantità</th>
            <th>Incasso (€)</th>
            <th>Note</th>
        </tr>
        <?php while($row = mysqli_fetch_assoc($risultato)) { ?>
            <tr>
                <td><?php echo $row['data_acquisto']; ?></td>
                <td><?php echo $row['cliente']; ?></td>
                <td><?php echo $row['prodotto']; ?></td>
                <td><?php echo $row['quantita']; ?></td>
                <td><b><?php echo $row['totale_pagato']; ?></b></td>
                <td><?php echo $row['note']; ?></td>
            </tr>
        <?php } ?>
    </table>
</body>
</html>