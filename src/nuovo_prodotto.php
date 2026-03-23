<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = mysqli_real_escape_string($conn, $_POST['nome']);
    $id_categoria = intval($_POST['id_categoria']);
    $tipo = $_POST['tipo'];
    $prezzo = floatval($_POST['prezzo']);

    // 1. Inserimento nel database PADRE (Prodotti)
    $sql_prod = "INSERT INTO Prodotti (nome, id_categoria, tipo) VALUES ('$nome', $id_categoria, '$tipo')";
    
    if (mysqli_query($conn, $sql_prod)) {
        $id_p = mysqli_insert_id($conn);

        // 2. Salviamo il prezzo nel Listino (Storicizzazione)
        mysqli_query($conn, "INSERT INTO Listino_Prezzi (id_prodotto, prezzo_unitario) VALUES ($id_p, $prezzo)");

        // 3. Salviamo nelle sottotabelle in base al tipo
        if ($tipo == 'Fresco') {
            $um = $_POST['um_fresco'];
            mysqli_query($conn, "INSERT INTO Prodotti_Freschi (id_prodotto, unita_misura) VALUES ($id_p, '$um')");
        } 
        elseif ($tipo == 'Riserva') {
            $peso = floatval($_POST['peso_iniziale']);
            $um = $_POST['um_riserva'];
            $data = $_POST['data_p'];
            mysqli_query($conn, "INSERT INTO Prodotti_Riserva (id_prodotto, peso_totale_disponibile, unita_misura, data_produzione) 
                                 VALUES ($id_p, $peso, '$um', '$data')");
        }
        elseif ($tipo == 'Confezionato') {
            $pezzi = intval($_POST['pezzi']);
            $peso_n = floatval($_POST['peso_n']);
            $data = $_POST['data_c'];
            mysqli_query($conn, "INSERT INTO Prodotti_Confezionati (id_prodotto, giacenza_pezzi, peso_netto_confezione, data_confezionamento) 
                                 VALUES ($id_p, $pezzi, $peso_n, '$data')");
        }
        echo "<script>alert('Prodotto salvato con successo!'); window.location='index.php';</script>";
    }
}

$categorie = mysqli_query($conn, "SELECT * FROM Categorie");
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Aggiungi Prodotto</title>
    <style>
        body { font-family: sans-serif; padding: 20px; line-height: 1.6; }
        .form-group { margin-bottom: 15px; border: 1px solid #eee; padding: 10px; border-radius: 5px; }
        label { display: block; font-weight: bold; }
        .hidden { display: none; }
    </style>
    <script>
        function cambiaCampi() {
            const tipo = document.getElementById('tipo').value;
            document.getElementById('fresco').className = (tipo === 'Fresco') ? '' : 'hidden';
            document.getElementById('riserva').className = (tipo === 'Riserva') ? '' : 'hidden';
            document.getElementById('confezionato').className = (tipo === 'Confezionato') ? '' : 'hidden';
        }
    </script>
</head>
<body>
    <h1>Nuovo Prodotto Aziendale</h1>
    <form method="POST">
        <div class="form-group">
            <label>Nome Prodotto:</label>
            <input type="text" name="nome" required placeholder="es. Miele di Castagno">
        </div>

        <div class="form-group">
            <label>Categoria:</label>
            <select name="id_categoria">
                <?php while($c = mysqli_fetch_assoc($categorie)): ?>
                    <option value="<?= $c['id_categoria'] ?>"><?= $c['nome'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Prezzo Unitario (€):</label>
            <input type="number" step="0.01" name="prezzo" required>
        </div>

        <div class="form-group">
            <label>Tipo di Prodotto:</label>
            <select name="tipo" id="tipo" onchange="cambiaCampi()" required>
                <option value="">-- Seleziona --</option>
                <option value="Fresco">Fresco (Vendita immediata)</option>
                <option value="Riserva">Riserva (Sfuso/Peso)</option>
                <option value="Confezionato">Confezionato (Vasetti/Pezzi)</option>
            </select>
        </div>

        <div id="fresco" class="hidden">
            <div class="form-group">
                <label>Unità di Misura:</label>
                <select name="um_fresco"><option>kg</option><option>pezzo</option></select>
            </div>
        </div>

        <div id="riserva" class="hidden">
            <div class="form-group">
                <label>Peso/Volume Iniziale:</label>
                <input type="number" step="0.1" name="peso_iniziale">
                <select name="um_riserva"><option>kg</option><option>litro</option></select>
                <label>Data Produzione:</label>
                <input type="date" name="data_p">
            </div>
        </div>

        <div id="confezionato" class="hidden">
            <div class="form-group">
                <label>Pezzi in Giacenza:</label>
                <input type="number" name="pezzi">
                <label>Peso Netto per pezzo (g/kg):</label>
                <input type="number" step="0.1" name="peso_n">
                <label>Data Confezionamento:</label>
                <input type="date" name="data_c">
            </div>
        </div>

        <button type="submit" style="padding: 10px 20px; background: #28a745; color: white; border: none; cursor: pointer;">Salva Prodotto</button>
        <a href="index.php">Annulla</a>
    </form>
</body>
</html>