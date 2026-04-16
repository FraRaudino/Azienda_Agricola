<?php
include 'config.php';

$id_scelto = 0;
if (isset($_GET['filtra_cliente'])) {
    $id_scelto = intval($_GET['filtra_cliente']);
}

if ($id_scelto > 0) {
    $stmt = mysqli_prepare($conn, "SELECT v.*, c.nome AS cliente, p.nome AS prodotto, dv.quantita
        FROM Vendite v
        LEFT JOIN Clienti c ON v.id_cliente = c.id_cliente
        JOIN Dettaglio_Vendite dv ON v.id_vendita = dv.id_vendita
        JOIN Prodotti p ON dv.id_prodotto = p.id_prodotto
        WHERE v.id_cliente = ?
        ORDER BY v.data_acquisto DESC");
    mysqli_stmt_bind_param($stmt, 'i', $id_scelto);
    mysqli_stmt_execute($stmt);
    $risultato = mysqli_stmt_get_result($stmt);
} else {
    $risultato = mysqli_query($conn, "SELECT v.*, c.nome AS cliente, p.nome AS prodotto, dv.quantita
        FROM Vendite v
        LEFT JOIN Clienti c ON v.id_cliente = c.id_cliente
        JOIN Dettaglio_Vendite dv ON v.id_vendita = dv.id_vendita
        JOIN Prodotti p ON dv.id_prodotto = p.id_prodotto
        ORDER BY v.data_acquisto DESC");
}

$lista_clienti = mysqli_query($conn, "SELECT * FROM Clienti ORDER BY nome");
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archivio vendite</title>
    <link rel="stylesheet" href="app.css">
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1>Archivio vendite</h1>
            <p>Consulta gli acquisti registrati e filtra per cliente.</p>
        </div>

        <div class="panel footer-space">
            <form method="GET">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Mostra vendite di</label>
                        <select name="filtra_cliente">
                            <option value="0">Tutti i clienti</option>
                            <?php while ($cl = mysqli_fetch_assoc($lista_clienti)) { ?>
                                <option value="<?php echo $cl['id_cliente']; ?>" <?php if ($id_scelto == $cl['id_cliente']) { echo 'selected'; } ?>><?php echo htmlspecialchars($cl['nome']); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="actions">
                    <button type="submit">Filtra</button>
                    <a class="btn btn-light" href="index.php">Torna alla dashboard</a>
                </div>
            </form>
        </div>

        <div class="panel table-wrap">
            <table>
                <tr>
                    <th>Data</th>
                    <th>Cliente</th>
                    <th>Prodotto</th>
                    <th>Quantità</th>
                    <th>Incasso</th>
                    <th>Note</th>
                </tr>
                <?php if ($risultato && mysqli_num_rows($risultato) > 0) { ?>
                    <?php while ($row = mysqli_fetch_assoc($risultato)) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['data_acquisto']); ?></td>
                            <td><?php echo $row['cliente'] ? htmlspecialchars($row['cliente']) : 'Cliente non disponibile'; ?></td>
                            <td><?php echo htmlspecialchars($row['prodotto']); ?></td>
                            <td><?php echo htmlspecialchars($row['quantita']); ?></td>
                            <td>€ <?php echo number_format($row['totale_pagato'], 2, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($row['note'] ?? ''); ?></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="6">Nessuna vendita trovata.</td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    </div>
</body>
</html>
