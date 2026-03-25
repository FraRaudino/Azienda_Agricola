<?php
include 'config.php';

$sql = "SELECT p.id_prodotto, p.nome, p.tipo, c.nome AS categoria,
               pr.peso_totale_disponibile, pr.unita_misura AS um_riserva,
               pc.giacenza_pezzi,
               (SELECT prezzo_unitario FROM Listino_Prezzi 
                WHERE id_prodotto = p.id_prodotto 
                ORDER BY data_inizio_validita DESC LIMIT 1) AS prezzo_attuale
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
    <title>Dashboard Azienda Agricola</title>
</head>
<body>

    <h1>Gestione Azienda Agricola</h1>

    <nav>
        <ul>
            <li><a href="nuovo_prodotto.php">Aggiungi Nuovo Prodotto</a></li>
            <li><a href="vendita.php">Registra Vendita</a></li>
            <li><a href="clienti.php">Gestione Clienti</a></li>
            <li><a href="report_vendite.php">Archivio Vendite</a></li>
            <li><a href="lavorazione.php">Lavorazione Riserve</a></li>
            <li><a href="gestione_sistema.php">Impostazioni (Categorie/Sedi)</a></li>
        </ul>
    </nav>

    <hr>

    <h2>Inventario Prodotti e Giacenze</h2>

   <table style="border-collapse: collapse; width: 100%;">
    <thead>
        <tr style="background-color: #f2f2f2;">
            <th style="border: 1px solid #ccc; padding: 8px;">Prodotto</th>
            <th style="border: 1px solid #ccc; padding: 8px;">Categoria</th>
            <th style="border: 1px solid #ccc; padding: 8px;">Tipo</th>
            <th style="border: 1px solid #ccc; padding: 8px;">Prezzo Attuale</th>
            <th style="border: 1px solid #ccc; padding: 8px;">Giacenza / Peso</th>
            <th style="border: 1px solid #ccc; padding: 8px;">Stato</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        if (mysqli_num_rows($risultato) > 0) {
            while($row = mysqli_fetch_assoc($risultato)) {
        ?>
                <tr>
                    <td style="border: 1px solid #ccc; padding: 8px;"><?php echo $row['nome']; ?></td>
                    <td style="border: 1px solid #ccc; padding: 8px;"><?php echo $row['categoria']; ?></td>
                    <td style="border: 1px solid #ccc; padding: 8px;"><?php echo $row['tipo']; ?></td>
                    <td style="border: 1px solid #ccc; padding: 8px;">€ <?php echo number_format($row['prezzo_attuale'], 2, ',', '.'); ?></td>
                    <td style="border: 1px solid #ccc; padding: 8px;">
                        <?php 
                            if ($row['tipo'] == 'Fresco') {
                                echo "Disponibilità variabile";
                            } elseif ($row['tipo'] == 'Riserva') {
                                echo $row['peso_totale_disponibile'] . " " . $row['um_riserva'];
                            } elseif ($row['tipo'] == 'Confezionato') {
                                echo $row['giacenza_pezzi'] . " pezzi";
                            }
                        ?>
                    </td>
                    <td style="border: 1px solid #ccc; padding: 8px;">
                        <?php 
                            if ($row['tipo'] == 'Fresco') {
                                echo "In Stagione";
                            } else {
                                $qta = ($row['tipo'] == 'Riserva') ? $row['peso_totale_disponibile'] : $row['giacenza_pezzi'];
                                if ($qta <= 0) {
                                    echo "<strong style='color:red;'>ESAURITO</strong>";
                                } else {
                                    echo "Disponibile";
                                }
                            }
                        ?>
                    </td>
                </tr>
        <?php 
            } 
        } else { 
        ?>
            <tr>
                <td colspan="6" style="border: 1px solid #ccc; padding: 8px; text-align: center;">
                    Nessun prodotto trovato. <a href="nuovo_prodotto.php">Inserisci il primo!</a>
                </td>
            </tr>
        <?php 
        } 
        ?>
    </tbody>
</table>