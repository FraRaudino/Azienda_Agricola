<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = mysqli_real_escape_string($conn, $_POST['nome']);
    $id_categoria = intval($_POST['id_categoria']);
    $tipo = $_POST['tipo'];
    $prezzo = floatval($_POST['prezzo']);
    $um = $_POST['um'];
    $qta = floatval($_POST['quantita']);
    $data_prod = $_POST['data_produzione'];
    $peso_netto = floatval($_POST['peso_netto']);
    $id_sede = intval($_POST['id_sede']); 

    $sql = "INSERT INTO Prodotti (nome, id_categoria, tipo) VALUES ('$nome', $id_categoria, '$tipo')";
    
    if (mysqli_query($conn, $sql)) {
        $id_p = mysqli_insert_id($conn);
        
        mysqli_query($conn, "INSERT INTO Listino_Prezzi (id_prodotto, prezzo_unitario) VALUES ($id_p, $prezzo)");

        if ($tipo == 'Fresco') {
            mysqli_query($conn, "INSERT INTO Prodotti_Freschi (id_prodotto, unita_misura) VALUES ($id_p, '$um')");
        } 
        
        if ($tipo == 'Riserva') {
            mysqli_query($conn, "INSERT INTO Prodotti_Riserva (id_prodotto, peso_totale_disponibile, unita_misura, data_produzione) 
                                 VALUES ($id_p, $qta, '$um', '$data_prod')");
        }
        
        if ($tipo == 'Confezionato') {
            mysqli_query($conn, "INSERT INTO Prodotti_Confezionati (id_prodotto, giacenza_pezzi, peso_netto_confezione, data_confezionamento) 
                                 VALUES ($id_p, $qta, $peso_netto, '$data_prod')");
        }
        
        header("Location: index.php");
    }
}

$res_cat = mysqli_query($conn, "SELECT * FROM Categorie");
$res_sedi = mysqli_query($conn, "SELECT * FROM Sedi"); 
?>

<!DOCTYPE html>
<html>
<head>
    <title>Nuovo Prodotto</title>
</head>
<body>
    <h1>Inserimento Nuovo Prodotto</h1>
    <form method="POST">
        <fieldset>
            <legend>Dati Base</legend>
            <p>Nome Prodotto: <input type="text" name="nome" required></p>
            
            <p>Categoria: 
                <select name="id_categoria">
                    <?php while($c = mysqli_fetch_assoc($res_cat)) { ?>
                        <option value="<?php echo $c['id_categoria']; ?>"><?php echo $c['nome']; ?></option>
                    <?php } ?>
                </select>
            </p>

            <p>Tipo Prodotto (Fresco / Riserva sfusa / Confezionato): 
                <select name="tipo">
                    <option value="Fresco">Fresco (Vendita senza lavorazione)</option>
                    <option value="Riserva">Riserva (Da conservare sfuso in grandi contenitori)</option>
                    <option value="Confezionato">Confezionato (Vasetti, Bottiglie, ecc.)</option>
                </select>
            </p>

            <p>Prezzo Unitario al momento dell'inserimento (€): <input type="number" step="0.01" name="prezzo" required></p>
            
            <p>Luogo di conservazione:
                <select name="id_sede">
                    <?php while($s = mysqli_fetch_assoc($res_sedi)) { ?>
                        <option value="<?php echo $s['id_sede']; ?>"><?php echo $s['nome_sede']; ?></option>
                    <?php } ?>
                </select>
            </p>
        </fieldset>

        <fieldset>
            <legend>Dettagli Quantità e Produzione (Compilare in base al tipo)</legend>
            
            <p>Unità di Misura (per Fresco o Riserva): 
                <select name="um">
                    <option value="kg">Chilogrammi (kg)</option>
                    <option value="litro">Litri (L)</option>
                    <option value="pezzo">Pezzo</option>
                </select>
            </p>

            <p>Quantità Iniziale / Giacenza Pezzi: <br>
                <small><i>(Inserire il peso totale per le Riserve, o il numero di vasetti per i Confezionati)</i></small><br>
                <input type="number" step="0.1" name="quantita" value="0">
            </p>

            <p>Data Lavorazione / Confezionamento: <br>
                <input type="date" name="data_produzione" value="<?php echo date('Y-m-d'); ?>">
            </p>

            <p>Peso Netto Confezione (Solo per prodotti confezionati - es. 0.5 kg): <br>
                <input type="number" step="0.01" name="peso_netto" value="0">
            </p>
        </fieldset>

        <br>
        <button type="submit">Salva nel Database</button>
        <a href="index.php">Torna alla Dashboard</a>
    </form>
</body>
</html>