<?php
include 'config.php';
$messaggio = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_riserva = intval($_POST['id_riserva']);
    $id_confezionato = intval($_POST['id_confezionato']);
    $quantita_da_togliere = floatval($_POST['quantita_kg_l']); // Quanti kg/L togliamo dalla riserva
    $pezzi_prodotti = intval($_POST['pezzi_prodotti']); // Quante confezioni otteniamo

    // 1. Controllo disponibilità nella Riserva
    $res = mysqli_query($conn, "SELECT peso_totale_disponibile FROM Prodotti_Riserva WHERE id_prodotto = $id_riserva");
    $row = mysqli_fetch_assoc($res);

    if ($row['peso_totale_disponibile'] >= $quantita_da_togliere) {
        // 2. Scaliamo dalla Riserva
        mysqli_query($conn, "UPDATE Prodotti_Riserva SET peso_totale_disponibile = peso_totale_disponibile - $quantita_da_togliere WHERE id_prodotto = $id_riserva");
        
        // 3. Aggiungiamo ai Confezionati (e aggiorniamo la data di confezionamento)
        mysqli_query($conn, "UPDATE Prodotti_Confezionati SET giacenza_pezzi = giacenza_pezzi + $pezzi_prodotti, data_confezionamento = NOW() WHERE id_prodotto = $id_confezionato");
        
        $messaggio = "<span style='color:green;'>Lavorazione completata! Magazzino aggiornato.</span>";
    } else {
        $messaggio = "<span style='color:red;'>Errore: Non hai abbastanza prodotto sfuso in riserva!</span>";
    }
}

$riserve = mysqli_query($conn, "SELECT p.id_prodotto, p.nome, r.peso_totale_disponibile, r.unita_misura FROM Prodotti p JOIN Prodotti_Riserva r ON p.id_prodotto = r.id_prodotto");
$confezionati = mysqli_query($conn, "SELECT p.id_prodotto, p.nome FROM Prodotti p JOIN Prodotti_Confezionati c ON p.id_prodotto = c.id_prodotto");
?>

<!DOCTYPE html>
<html>
<head><title>Lavorazione Prodotti</title></head>
<body>
    <h1>Lavorazione: da Riserva a Confezionato</h1>
    <a href="index.php">Torna alla Dashboard</a>
    <p><?php echo $messaggio; ?></p>

    <form method="POST">
        <fieldset>
            <legend>Scegli i prodotti</legend>
            <p>Prodotto Riserva (Sorgente): 
                <select name="id_riserva">
                    <?php while($r = mysqli_fetch_assoc($riserve)) { ?>
                        <option value="<?php echo $r['id_prodotto']; ?>"><?php echo $r['nome']; ?> (Disp: <?php echo $r['peso_totale_disponibile']." ".$r['unita_misura']; ?>)</option>
                    <?php } ?>
                </select>
            </p>

            <p>Prodotto Confezionato (Destinazione): 
                <select name="id_confezionato">
                    <?php while($c = mysqli_fetch_assoc($confezionati)) { ?>
                        <option value="<?php echo $c['id_prodotto']; ?>"><?php echo $c['nome']; ?></option>
                    <?php } ?>
                </select>
            </p>

            <p>Quantità di sfuso utilizzata (kg/L): <input type="number" step="0.01" name="quantita_kg_l" required></p>
            <p>Numero di confezioni prodotte: <input type="number" name="pezzi_prodotti" required></p>
            
            <button type="submit">Registra Confezionamento</button>
        </fieldset>
    </form>
</body>
</html>