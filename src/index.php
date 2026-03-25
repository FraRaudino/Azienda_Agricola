<?php
include 'config.php';

$sql = "SELECT p.id_prodotto, p.nome, p.tipo, c.nome AS categoria,
               pr.peso_totale_disponibile, pr.unita_misura AS um_riserva,
               pc.giacenza_pezzi,
               (SELECT prezzo_unitario FROM Listino_Prezzi WHERE id_prodotto = p.id_prodotto ORDER BY data_inizio_validita DESC LIMIT 1) AS prezzo_attuale
        FROM Prodotti p
        LEFT JOIN Categorie c ON p.id_categoria = c.id_categoria
        LEFT JOIN Prodotti_Riserva pr ON p.id_prodotto = pr.id_prodotto
        LEFT JOIN Prodotti_Confezionati pc ON p.id_prodotto = pc.id_prodotto
        ORDER BY p.nome ASC";

$risultato = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Azienda Agricola</title>
    <link rel="stylesheet" href="app.css">
</head>
<body>
    <div class="hero">
        <div class="container">
            <h1>Gestione Azienda Agricola</h1>
            <p>Produzione, confezionamento, vendite e giacenze in un unico pannello.</p>
        </div>
    </div>

    <div class="container">
        <div class="nav-grid footer-space">
            <a class="card menu-card" href="nuovo_prodotto.php"><strong>Aggiungi prodotto</strong><span>Inserisci un nuovo prodotto e il prezzo iniziale.</span></a>
            <a class="card menu-card" href="vendita.php"><strong>Registra vendita</strong><span>Salva una vendita e aggiorna automaticamente le quantità.</span></a>
            <a class="card menu-card" href="clienti.php"><strong>Gestione clienti</strong><span>Aggiungi clienti e consulta l'anagrafica.</span></a>
            <a class="card menu-card" href="report_vendite.php"><strong>Archivio vendite</strong><span>Visualizza tutte le vendite e filtra per cliente.</span></a>
            <a class="card menu-card" href="lavorazione.php"><strong>Lavorazioni</strong><span>Registra una lavorazione eseguita su un prodotto.</span></a>
            <a class="card menu-card" href="confezionamento.php"><strong>Confezionamento</strong><span>Trasforma un prodotto di riserva in confezioni vendibili.</span></a>
            <a class="card menu-card" href="gestione_sistema.php"><strong>Impostazioni</strong><span>Gestisci categorie, sedi e prodotti del sistema.</span></a>
        </div>

        <div class="panel table-wrap">
            <h2>Inventario prodotti e giacenze</h2>
            <table>
                <thead>
                    <tr>
                        <th>Prodotto</th>
                        <th>Categoria</th>
                        <th>Tipo</th>
                        <th>Prezzo attuale</th>
                        <th>Giacenza o peso</th>
                        <th>Stato</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($risultato) > 0) { ?>
                        <?php while ($row = mysqli_fetch_assoc($risultato)) { ?>
                            <tr>
                                <td><?php echo $row['nome']; ?></td>
                                <td><?php echo $row['categoria']; ?></td>
                                <td><?php echo $row['tipo']; ?></td>
                                <td>€ <?php echo number_format($row['prezzo_attuale'], 2, ',', '.'); ?></td>
                                <td>
                                    <?php
                                    if ($row['tipo'] == 'Fresco') {
                                        echo 'Disponibilità variabile';
                                    }
                                    if ($row['tipo'] == 'Riserva') {
                                        echo $row['peso_totale_disponibile'] . ' ' . $row['um_riserva'];
                                    }
                                    if ($row['tipo'] == 'Confezionato') {
                                        echo $row['giacenza_pezzi'] . ' pezzi';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    if ($row['tipo'] == 'Fresco') {
                                        echo "<span class='badge soft'>In stagione</span>";
                                    } else {
                                        $qta = 0;
                                        if ($row['tipo'] == 'Riserva') {
                                            $qta = $row['peso_totale_disponibile'];
                                        }
                                        if ($row['tipo'] == 'Confezionato') {
                                            $qta = $row['giacenza_pezzi'];
                                        }

                                        if ($qta <= 0) {
                                            echo "<span class='badge stop'>Esaurito</span>";
                                        } else {
                                            echo "<span class='badge ok'>Disponibile</span>";
                                        }
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="6">Nessun prodotto trovato.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
